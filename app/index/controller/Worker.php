<?php
declare(strict_types=1);

namespace app\index\controller;

use app\common\model\UserModel;
use app\admin\model\mes\AllocationModel;
use app\admin\model\mes\ReportModel;
use app\admin\model\mes\WageModel;
use app\admin\model\mes\TraceCodeModel;
use think\facade\View;
use think\facade\Request;
use think\facade\Cache;
use think\facade\Lang;
use think\facade\Db;
use think\Response;
use think\exception\HttpResponseException;

/**
 * 工人端：首页、我的任务、报工、报工记录、工资统计（与 report 项目 worker 端对齐）
 * 身份从 cookie user_token 解析，未登录跳转 /index/user/login
 */
class Worker
{
    /** @var int */
    private $userId = 0;
    /** @var int */
    private $tenantId = 0;
    /** @var array */
    private $userInfo = [];

    /** 按 cookie 设置语言并加载当前控制器语言包 */
    private function ensureLang(): void
    {
        $cookieVar = config('lang.cookie_var', 'think_lang');
        $cookieVal = request()->cookie($cookieVar, '');
        if ($cookieVal !== '' && $cookieVal !== null) {
            $allow = config('lang.allow_lang_list', []);
            if (is_array($allow) && in_array($cookieVal, $allow, true)) {
                Lang::setLangSet($cookieVal);
            }
        }
        $langSet = Lang::getLangSet();
        $ctrl = (new \ReflectionClass($this))->getShortName();
        $path = app()->getAppPath() . 'lang' . DIRECTORY_SEPARATOR . $langSet . DIRECTORY_SEPARATOR . $ctrl . '.php';
        if (is_file($path)) {
            Lang::load($path);
        }
    }

    /** 从 cookie 解析当前工人，未登录则跳转登录页 */
    private function ensureWorker(): void
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if ($token === '') {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $cacheKey = \app\api\middleware\UserAuth::CACHE_PREFIX . $token;
        $payload = Cache::get($cacheKey);
        if (!$payload || !is_array($payload)) {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $this->userId   = (int) ($payload['user_id'] ?? 0);
        $this->tenantId = (int) ($payload['tenant_id'] ?? 0);
        if ($this->userId <= 0) {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $user = UserModel::where('id', $this->userId)
            ->where('tenant_id', $this->tenantId)
            ->where('status', 1)
            ->find();
        if (!$user) {
            throw new HttpResponseException($this->redirectToLogin());
        }
        $this->userInfo = $user->toArray();
        View::assign('workerUser', $this->userInfo);
        View::assign('workerUserId', $this->userId);
        View::assign('workerTenantId', $this->tenantId);
    }

    private function redirectToLogin(): Response
    {
        $root = rtrim((string) request()->root(true), '/');
        return redirect($root . '/index/user/login');
    }

    private function fetchWithLayout(string $template): string
    {
        $this->ensureLang();
        $content = View::fetch($template);
        View::assign('__CONTENT__', $content);
        return View::fetch('layout/default');
    }

    /** 工人首页：欢迎 + 今日任务数/今日报工数/今日工资 + 功能入口 */
    public function index(): string|Response
    {
        $this->ensureWorker();
        $this->ensureLang();

        $todayStart = strtotime(date('Y-m-d 00:00:00'));
        $todayEnd   = time();
        $todayDate  = date('Y-m-d');

        $todayTaskCount = (int) AllocationModel::where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->count();

        $todayReportCount = (int) Db::name('mes_report')
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('create_time', '>=', $todayStart)
            ->where('create_time', '<=', $todayEnd)
            ->sum('quantity');

        $todayWage = (float) Db::name('mes_wage')
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('work_date', $todayDate)
            ->sum('total_wage');

        $workerName = $this->userInfo['nickname'] ?? $this->userInfo['username'] ?? Lang::get('welcome_default');
        View::assign('title', Lang::get('worker_center'));
        View::assign('workerWelcome', sprintf(Lang::get('welcome'), $workerName));
        View::assign('workerWelcomeDate', sprintf(Lang::get('welcome_date'), date('Y-m-d')));
        View::assign('todayTaskCount', $todayTaskCount);
        View::assign('todayReportCount', $todayReportCount);
        View::assign('todayWage', number_format($todayWage, 2, '.', ''));
        return $this->fetchWithLayout('worker/index');
    }

    /** 我的任务：当前工人的分配列表 */
    public function tasks(): string|Response
    {
        $this->ensureWorker();
        $this->ensureLang();

        $allocations = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('status', 'asc')
            ->order('id', 'desc')
            ->select();

        $tasks = [];
        if (!$allocations->isEmpty()) {
            $allocationIds = $allocations->column('id');
            $reportedMap = [];
            if ($allocationIds) {
                $rows = Db::name('mes_report')
                    ->where('tenant_id', $this->tenantId)
                    ->where('user_id', $this->userId)
                    ->whereIn('allocation_id', $allocationIds)
                    ->group('allocation_id')
                    ->column('SUM(quantity) as qty', 'allocation_id');
                foreach ($rows as $aid => $qty) {
                    $reportedMap[(int) $aid] = (int) $qty;
                }
            }
            foreach ($allocations as $a) {
                $order = $a->order;
                $model = $a->model;
                $product = $model ? $model->product : null;
                $process = $a->process;
                $assignQty = (int) $a->quantity;
                $reportedQty = (int) ($reportedMap[(int) $a->id] ?? 0);
                $pendingQty = max(0, $assignQty - $reportedQty);
                $tasks[] = [
                    'id'                  => (int) $a->id,
                    'order_no'            => $order->order_no ?? '',
                    'product_name'        => $product ? $product->name : '',
                    'model_name'          => $model ? $model->name : '',
                    'process_name'        => $process ? $process->name : '',
                    'quantity'            => $assignQty,
                    'reported_quantity'   => $reportedQty,
                    'remaining_quantity'  => $pendingQty,
                    'status'               => (int) $a->status,
                    'createtime'           => (int) ($a->create_time ?? 0),
                    'updatetime'           => (int) ($a->update_time ?? 0),
                ];
            }
        }

        View::assign('title', Lang::get('title_my_tasks'));
        View::assign('tasks', $tasks);
        return $this->fetchWithLayout('worker/tasks');
    }

    /** 报工页 GET：按 id（allocation_id）展示任务信息与报工表单 */
    public function report(): string|Response
    {
        $this->ensureWorker();
        $this->ensureLang();

        $id = (int) Request::get('id', 0);
        if ($id <= 0) {
            View::assign('error', Lang::get('error_select_task'));
            View::assign('title', '报工');
            return $this->fetchWithLayout('worker/report');
        }

        $allocation = AllocationModel::with(['order', 'model.product', 'process'])
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->find($id);
        if (!$allocation) {
            View::assign('error', Lang::get('error_task_not_found'));
            View::assign('title', Lang::get('title_report'));
            return $this->fetchWithLayout('worker/report');
        }

        $order = $allocation->order;
        $model = $allocation->model;
        $product = $model ? $model->product : null;
        $process = $allocation->process;
        $reportedQty = (int) Db::name('mes_report')
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('allocation_id', $id)
            ->sum('quantity');
        $assignQty = (int) $allocation->quantity;
        $pendingQty = max(0, $assignQty - $reportedQty);

        $itemNos = TraceCodeModel::where('tenant_id', $this->tenantId)
            ->where('allocation_id', $id)
            ->where('status', 1)
            ->where('report_id', 0)
            ->order('id', 'asc')
            ->column('item_no');

        $allocationView = [
            'id'                  => (int) $allocation->id,
            'order_no'            => $order->order_no ?? '',
            'product_name'        => $product ? $product->name : '',
            'model_name'          => $model ? $model->name : '',
            'process_name'        => $process ? $process->name : '',
            'quantity'            => $assignQty,
            'reported_quantity'   => $reportedQty,
            'remaining_quantity'  => $pendingQty,
        ];
        $productItems = [];
        foreach ($itemNos as $no) {
            $productItems[] = ['item_no' => $no];
        }

        View::assign('title', Lang::get('title_report'));
        View::assign('allocation', $allocationView);
        View::assign('productItems', $productItems);
        View::assign('apiReportUrl', rtrim((string) request()->root(true), '/') . '/api/worker/report');
        View::assign('apiUploadUrl', rtrim((string) request()->root(true), '/') . '/api/worker/uploadImage');
        return $this->fetchWithLayout('worker/report');
    }

    /** 报工记录列表 */
    public function records(): string|Response
    {
        $this->ensureWorker();
        $this->ensureLang();

        $page = max(1, (int) Request::get('page', 1));
        $limit = max(1, min(100, (int) Request::get('limit', 20)));

        $list = ReportModel::with(['allocation.order', 'allocation.model.product', 'allocation.process', 'media'])
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select();
        $total = ReportModel::where('tenant_id', $this->tenantId)->where('user_id', $this->userId)->count();

        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        $records = [];
        foreach ($list as $r) {
            $allocation = $r->allocation;
            $order = $allocation && $allocation->order ? $allocation->order : null;
            $model = $allocation && $allocation->model ? $allocation->model : null;
            $product = $model && $model->product ? $model->product : null;
            $process = $allocation && $allocation->process ? $allocation->process : null;
            $unitPrice = 0;
            if ($r->quantity > 0 && (float) $r->wage > 0) {
                $unitPrice = (float) $r->wage / (int) $r->quantity;
            } elseif ((float) $r->work_hours > 0) {
                $unitPrice = (float) $r->wage / (float) $r->work_hours;
            }
            $images = [];
            if ($r->media && !$r->media->isEmpty()) {
                foreach ($r->media as $m) {
                    $images[] = $m->url;
                }
            }
            $records[] = [
                'id'           => (int) $r->id,
                'create_time'  => (int) $r->create_time,
                'order_no'     => $order ? $order->order_no : '',
                'product_name' => $product ? $product->name : '',
                'model_name'   => $model ? $model->name : '',
                'process_name' => $process ? $process->name : '',
                'quantity'     => (int) $r->quantity,
                'work_hours'   => (float) $r->work_hours,
                'unit_price'   => $unitPrice,
                'wage'         => (float) $r->wage,
                'status'       => (int) $r->status,
                'status_text'   => $statusMap[(int) $r->status] ?? '未知',
                'images'       => $images,
            ];
        }

        View::assign('title', Lang::get('title_records'));
        View::assign('records', $records);
        View::assign('total', $total);
        View::assign('totalCountText', sprintf(Lang::get('total_count'), $total));
        View::assign('page', $page);
        View::assign('limit', $limit);
        return $this->fetchWithLayout('worker/records');
    }

    /** 工资统计：日期筛选 + 汇总 + 明细 */
    public function wage(): string|Response
    {
        $this->ensureWorker();
        $this->ensureLang();

        $startDate = (string) Request::get('start_date', date('Y-m-01'));
        $endDate   = (string) Request::get('end_date', date('Y-m-d'));
        if (strtotime($startDate) > strtotime($endDate)) {
            $startDate = $endDate;
        }

        $wageRows = Db::name('mes_wage')
            ->where('tenant_id', $this->tenantId)
            ->where('user_id', $this->userId)
            ->where('work_date', '>=', $startDate)
            ->where('work_date', '<=', $endDate)
            ->order('work_date', 'desc')
            ->order('id', 'desc')
            ->select()
            ->toArray();

        $totalQuantity = 0;
        $totalWage = 0.0;
        $detailList = [];
        foreach ($wageRows as $row) {
            $totalQuantity += (int) ($row['quantity'] ?? 0);
            $w = (float) ($row['total_wage'] ?? 0);
            $totalWage += $w;
            $detailList[] = [
                'work_date'   => $row['work_date'] ?? '',
                'quantity'    => (int) ($row['quantity'] ?? 0),
                'work_hours'  => (float) ($row['work_hours'] ?? 0),
                'unit_price'  => (float) ($row['unit_price'] ?? 0),
                'total_wage'  => $w,
            ];
        }

        View::assign('title', Lang::get('title_wage'));
        View::assign('start_date', $startDate);
        View::assign('end_date', $endDate);
        View::assign('total_quantity', $totalQuantity);
        View::assign('total_wage', $totalWage);
        View::assign('detail_list', $detailList);
        return $this->fetchWithLayout('worker/wage');
    }

    /** 扫码报工入口（原有） */
    public function scan(): string|Response
    {
        $token = (string) (Request::cookie('user_token') ?? '');
        if ($token === '') {
            return $this->redirectToLogin();
        }
        $this->ensureWorker();
        $this->ensureLang();
        $allocationId = (int) Request::get('allocation_id', 0);
        View::assign('title', Lang::get('scan_title'));
        View::assign('allocation_id', $allocationId);
        return $this->fetchWithLayout('worker/scan');
    }
}
