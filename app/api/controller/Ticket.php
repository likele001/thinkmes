<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use think\facade\Db;
use think\Response;

class Ticket extends BaseController
{
    /**
     * 获取当前租户ID（支持平台管理员）
     */
    private function getTenantId(): int
    {
        // 检查是否是平台超级管理员
        if (property_exists($this->request, 'isAdmin') && $this->request->isAdmin === true) {
            return 0;
        }
        
        if (property_exists($this->request, 'tenantId')) {
            return (int) $this->request->tenantId;
        }
        
        return (int) $this->request->param('tenant_id', 0);
    }

    /**
     * 获取当前用户ID（支持平台管理员）
     */
    private function getUserId(): int
    {
        // 检查是否是平台超级管理员
        if (property_exists($this->request, 'isAdmin') && $this->request->isAdmin === true) {
            return 0; // 平台管理员用户ID为0
        }
        
        if (property_exists($this->request, 'userId')) {
            return (int) $this->request->userId;
        }
        
        return (int) $this->request->param('user_id', 0);
    }

    /**
     * 创建工单
     */
    public function create(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        // 平台管理员不能创建工单
        if ($tenantId === 0 && $userId === 0) {
            return $this->error('请先登录');
        }

        $category = trim((string) $this->request->post('category', ''));
        $title = trim((string) $this->request->post('title', ''));
        $description = trim((string) $this->request->post('description', ''));
        $priority = (int) $this->request->post('priority', 1);

        if (empty($category) || empty($title)) {
            return $this->error('请填写工单分类和标题');
        }

        if ($priority < 1 || $priority > 4) {
            $priority = 1;
        }

        // 生成工单编号
        $ticketNo = 'TKT' . date('Ymd') . str_pad((string) $tenantId, 4, '0', STR_PAD_LEFT) . str_pad((string) $userId, 6, '0', STR_PAD_LEFT) . mt_rand(1000, 9999);

        $now = time();
        $ticketId = Db::name('cs_ticket')->insertGetId([
            'ticket_no' => $ticketNo,
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'category' => $category,
            'priority' => $priority,
            'title' => $title,
            'description' => $description,
            'status' => 0, // 待处理
            'create_time' => $now,
            'update_time' => $now
        ]);

        return $this->success('工单创建成功', [
            'ticket_id' => $ticketId,
            'ticket_no' => $ticketNo
        ]);
    }

    /**
     * 获取我的工单列表
     */
    public function myTickets(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        // 平台管理员无法查看工单
        if ($tenantId === 0 && $userId === 0) {
            return $this->error('请先登录');
        }

        $status = (int) $this->request->get('status', -1); // -1表示全部
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 10));

        $query = Db::name('cs_ticket')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId);

        if ($status >= 0) {
            $query->where('status', $status);
        }

        $result = $query
            ->order('id', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page])
            ->toArray();

        return $this->success('', $result);
    }

    /**
     * 获取工单详情
     */
    public function detail(): Response
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $ticketNo = trim((string) $this->request->get('ticket_no', '');
        
        if (empty($ticketNo)) {
            return $this->error('工单编号不能为空');
        }

        $ticket = Db::name('cs_ticket')
            ->where('ticket_no', $ticketNo)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find();

        if (!$ticket) {
            return $this->error('工单不存在');
        }

        // 获取回复列表
        $replies = Db::name('cs_ticket_reply')
            ->where('ticket_id', $ticket['id'])
            ->where('is_internal', 0)
            ->order('id', 'asc')
            ->select()
            ->toArray();

        return $this->success('', [
            'ticket' => $ticket,
            'replies' => $replies
        ]);
    }

    /**
     * 回复工单
     */
    public function reply(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $ticketNo = trim((string) $this->request->post('ticket_no', ''));
        $content = trim((string) $this->request->post('content', ''));

        if (empty($ticketNo) || empty($content)) {
            return $this->error('参数错误');
        }

        $ticket = Db::name('cs_ticket')
            ->where('ticket_no', $ticketNo)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find();

        if (!$ticket) {
            return $this->error('工单不存在');
        }

        $now = time();

        // 添加回复
        Db::name('cs_ticket_reply')->insert([
            'ticket_id' => $ticket['id'],
            'user_id' => $userId,
            'user_type' => 0, // 用户
            'content' => $content,
            'create_time' => $now
        ]);

        // 更新工单状态
        if ((int) $ticket['status'] === 3) {
            // 如果已解决，用户回复后改为等待回复
            Db::name('cs_ticket')->where('id', $ticket['id'])->update([
                'status' => 2,
                'update_time' => $now
            ]);
        } else {
            Db::name('cs_ticket')->where('id', $ticket['id'])->update([
                'update_time' => $now
            ]);
        }

        return $this->success('回复成功');
    }

    /**
     * 评价工单
     */
    public function rate(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $ticketNo = trim((string) $this->request->post('ticket_no', ''));
        $satisfaction = (int) $this->request->post('satisfaction', 0);
        $feedback = trim((string) $this->request->post('feedback', ''));

        if (empty($ticketNo) || $satisfaction < 1 || $satisfaction > 5) {
            return $this->error('参数错误');
        }

        $ticket = Db::name('cs_ticket')
            ->where('ticket_no', $ticketNo)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find();

        if (!$ticket) {
            return $this->error('工单不存在');
        }

        Db::name('cs_ticket')->where('id', $ticket['id'])->update([
            'satisfaction' => $satisfaction,
            'feedback' => $feedback,
            'update_time' => time()
        ]);

        return $this->success('评价成功');
    }

    /**
     * 关闭工单
     */
    public function close(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $ticketNo = trim((string) $this->request->post('ticket_no', ''));

        if (empty($ticketNo)) {
            return $this->error('工单编号不能为空');
        }

        $ticket = Db::name('cs_ticket')
            ->where('ticket_no', $ticketNo)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->find();

        if (!$ticket) {
            return $this->error('工单不存在');
        }

        Db::name('cs_ticket')->where('id', $ticket['id'])->update([
            'status' => 4, // 已关闭
            'closed_at' => time(),
            'update_time' => time()
        ]);

        return $this->success('工单已关闭');
    }
}
