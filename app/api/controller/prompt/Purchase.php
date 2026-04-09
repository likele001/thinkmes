<?php
declare(strict_types=1);
namespace app\api\controller\prompt;

use app\common\controller\BaseController;
use app\admin\model\prompt\OrderModel;
use app\admin\model\prompt\QuotaModel;
use think\facade\Db;
use think\Response;

/**
 * C端 - 购买额度（需 UserAuth）
 */
class Purchase extends BaseController
{
    private static array $products = [
        'pack_s'     => ['name' => '体验包',   'quota' => 10,  'config' => 'prompt_price_s'],
        'pack_m'     => ['name' => '畅享包',   'quota' => 50,  'config' => 'prompt_price_m'],
        'pack_month' => ['name' => '月度套餐', 'quota' => 100, 'config' => 'prompt_price_month'],
    ];

    /** 获取产品列表 */
    public function products(): Response
    {
        $enablePay = Db::name('config')->where('name', 'prompt_enable_pay')->value('value');
        if ($enablePay !== '1') {
            return $this->error('付费功能暂未开放');
        }
        $list = [];
        foreach (self::$products as $key => $p) {
            $price = Db::name('config')->where('name', $p['config'])->value('value') ?: '0';
            $list[] = ['key' => $key, 'name' => $p['name'], 'quota' => $p['quota'], 'price' => (float)$price];
        }
        return $this->success('', ['list' => $list]);
    }

    /** 创建订单（简单实现，实际对接支付宝/微信时在此扩展） */
    public function create(): Response
    {
        $userId     = (int)($this->request->userId ?? 0);
        $productKey = trim((string)$this->request->post('product', ''));

        if (!isset(self::$products[$productKey])) {
            return $this->error('产品不存在');
        }
        $p     = self::$products[$productKey];
        $price = (float)(Db::name('config')->where('name', $p['config'])->value('value') ?: 0);

        $orderNo = date('YmdHis') . str_pad((string)$userId, 6, '0', STR_PAD_LEFT) . mt_rand(100, 999);
        $now     = time();

        $order = OrderModel::create([
            'user_id'      => $userId,
            'order_no'     => $orderNo,
            'product_name' => $p['name'],
            'quota_count'  => $p['quota'],
            'amount'       => $price,
            'status'       => 0,
            'create_time'  => $now,
            'update_time'  => $now,
        ]);

        if ($price <= 0) {
            $this->settleOrder($order, 'free');
            return $this->success('充值成功', [
                'free'     => 1,
                'order_id' => $order->id,
                'order_no' => $orderNo,
                'amount'   => $price,
                'product'  => $p['name'],
                'quota'    => $p['quota'],
            ]);
        }

        $payUrl = request()->root(true) . '/api/prompt/purchase/notify?order_no=' . urlencode($orderNo);
        return $this->success('订单创建成功', [
            'order_id'  => $order->id,
            'order_no'  => $orderNo,
            'amount'    => $price,
            'product'   => $p['name'],
            'quota'     => $p['quota'],
            'pay_url'   => $payUrl,
        ]);
    }

    /** 支付回调 / 测试直接支付（测试用，正式环境对接支付网关回调） */
    public function notify(): Response
    {
        $orderNo = trim((string)$this->request->param('order_no', ''));
        if ($orderNo === '') return $this->error('订单号不能为空');

        $order = OrderModel::where('order_no', $orderNo)->find();
        if (!$order) return $this->error('订单不存在');
        if ((int)$order->status === 1) return $this->success('已支付');

        $this->settleOrder($order, 'manual');

        return $this->success('支付成功，已充值 ' . $order->quota_count . ' 次');
    }

    /** 我的订单 */
    public function orders(): Response
    {
        $userId = (int)($this->request->userId ?? 0);
        $page   = max(1, (int)$this->request->get('page', 1));
        $list   = OrderModel::where('user_id', $userId)
            ->field('id, order_no, product_name, quota_count, amount, status, pay_time, create_time')
            ->order('id desc')->page($page, 20)->select()->toArray();
        return $this->success('', ['list' => $list]);
    }

    /** 支付方式列表（占位：未接入网关时返回空） */
    public function paymentMethods(): Response
    {
        return $this->success('', ['list' => []]);
    }

    /** 订单状态查询 */
    public function orderStatus(): Response
    {
        $userId  = (int)($this->request->userId ?? 0);
        $orderNo = trim((string)$this->request->get('order_no', ''));
        if ($orderNo === '') return $this->error('订单号不能为空');

        $order = OrderModel::where('order_no', $orderNo)->where('user_id', $userId)->find();
        if (!$order) return $this->error('订单不存在');

        return $this->success('', [
            'order_no' => $orderNo,
            'status'   => (int)$order->status,
            'amount'   => (float)$order->amount,
            'pay_time' => (int)($order->pay_time ?: 0),
        ]);
    }

    private function settleOrder(OrderModel $order, string $payMethod): void
    {
        $now = time();
        Db::startTrans();
        try {
            $order->status = 1;
            $order->pay_time = $now;
            $order->pay_method = $payMethod;
            $order->update_time = $now;
            $order->save();

            $quota = QuotaModel::where('user_id', $order->user_id)->find();
            if (!$quota) {
                $quota = new QuotaModel();
                $quota->user_id = $order->user_id;
                $quota->free_quota = 0;
                $quota->paid_quota = 0;
                $quota->total_used = 0;
                $quota->create_time = $now;
            }
            $quota->paid_quota += (int) $order->quota_count;
            $quota->update_time = $now;
            $quota->save();

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }
}
