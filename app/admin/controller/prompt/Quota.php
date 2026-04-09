<?php
declare(strict_types=1);
namespace app\admin\controller\prompt;

use app\admin\controller\Backend;
use app\admin\model\prompt\QuotaModel;
use think\facade\Db;
use think\Response;

class Quota extends Backend
{
    public function index(): string|Response
    {
        if ($this->request->isAjax()) {
            [$limit, $page] = $this->getPaginationParams();
            $query = QuotaModel::alias('q')
                ->leftJoin('fa_user u', 'u.id = q.user_id')
                ->field('q.*, u.username, u.nickname, u.mobile')
                ->order('q.id desc');
            $keyword = trim((string)$this->request->get('keyword', ''));
            if ($keyword !== '') {
                $query->whereLike('u.username|u.nickname|u.mobile', '%' . $keyword . '%');
            }
            $total = $query->count();
            $list  = $query->page($page, $limit)->select()->toArray();
            return $this->success('', ['total' => $total, 'rows' => $list]);
        }
        return $this->fetchWithLayout('prompt/quota/index');
    }

    /** 手动调整额度 */
    public function adjust(): Response
    {
        $userId   = (int)$this->request->post('user_id', 0);
        $type     = (string)$this->request->post('type', 'paid'); // free|paid
        $amount   = (int)$this->request->post('amount', 0);

        if ($userId <= 0) return $this->error('用户ID无效');
        if ($amount === 0) return $this->error('调整数量不能为0');

        $quota = QuotaModel::where('user_id', $userId)->find();
        if (!$quota) {
            $quota = new QuotaModel();
            $quota->user_id = $userId;
            $quota->free_quota = 0;
            $quota->paid_quota = 0;
            $quota->total_used = 0;
            $quota->create_time = time();
        }

        if ($type === 'free') {
            $quota->free_quota = max(0, (int)$quota->free_quota + $amount);
        } else {
            $quota->paid_quota = max(0, (int)$quota->paid_quota + $amount);
        }
        $quota->update_time = time();
        $quota->save();

        return $this->success('调整成功');
    }
}
