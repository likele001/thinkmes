<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\controller\Backend;
use think\facade\Db;
use think\facade\View;
use think\Response;

class CustomerService extends Backend
{
    /**
     * 获取当前租户ID（平台管理员返回0）
     */
    protected function getTenantId(): int
    {
        // 检查是否是平台超级管理员
        if ($this->isPlatformAdmin()) {
            return 0; // 平台管理员使用0
        }
        
        return parent::getTenantId();
    }
    
    /**
     * 客服中心首页
     */
    public function index(): string|Response
    {
        if ($this->getTenantId() !== 0) {
            return $this->error('仅平台超级管理员可访问客服管理');
        }
        
        if ($this->request->isAjax()) {
            return $this->statistics();
        }
        
        View::assign('title', '客服管理');
        return $this->fetchWithLayout('customer_service/index');
    }
    
    /**
     * 统计数据
     */
    protected function statistics(): Response
    {
        $today = strtotime(date('Y-m-d'));
        
        $stats = [
            // 今日统计
            'today_sessions' => Db::name('cs_session')->where('create_time', '>=', $today)->count(),
            'today_messages' => Db::name('cs_message')->where('create_time', '>=', $today)->count(),
            'today_tickets' => Db::name('cs_ticket')->where('create_time', '>=', $today)->count(),
            'today_ai_chats' => Db::name('cs_ai_history')->where('create_time', '>=', $today)->count(),
            
            // 总计
            'total_sessions' => Db::name('cs_session')->count(),
            'total_tickets' => Db::name('cs_ticket')->count(),
            'total_articles' => Db::name('cs_kb_article')->count(),
            'total_faqs' => Db::name('cs_faq')->count(),
            
            // 待处理工单
            'pending_tickets' => Db::name('cs_ticket')->where('status', 0)->count(),
            
            // 今日AI使用量
            'today_tokens' => Db::name('cs_ai_history')->where('create_time', '>=', $today)->sum('tokens_used') ?: 0,
        ];

        return $this->success('', $stats);
    }
    
    /**
     * 会话管理
     */
    public function sessions(): string
    {
        View::assign('title', '会话管理');
        return $this->fetchWithLayout('customer_service/sessions');
    }
    
    /**
     * 获取会话列表
     */
    public function getSessionList(): Response
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 15)));
        $status = $this->request->get('status', '');
        $keyword = $this->request->get('keyword', '');
        
        $query = Db::name('cs_session');
        
        if ($status !== '') {
            $query->where('status', $status);
        }
        
        if ($keyword !== '') {
            $query->where('visitor_name|session_id', 'like', '%' . $keyword . '%');
        }
        
        $total = $query->count();
        $list = $query->order('id', 'desc')->page($page, $limit)->select();
        
        return $this->success('', [
            'list' => $list,
            'total' => $total,
        ]);
    }
    
    /**
     * 会话详情
     */
    public function sessionDetail(): string
    {
        $sessionId = $this->request->get('session_id', '');
        
        View::assign('title', '会话详情');
        View::assign('session_id', $sessionId);
        return $this->fetchWithLayout('customer_service/session_detail');
    }
    
    /**
     * 获取会话消息
     */
    public function getSessionMessages(): Response
    {
        $sessionId = $this->request->get('session_id', '');
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 15)));
        
        if (empty($sessionId)) {
            return $this->error('会话ID不能为空');
        }
        
        $query = Db::name('cs_message')->where('session_id', $sessionId);
        $total = $query->count();
        $list = $query->order('id', 'asc')->page($page, $limit)->select();
        
        return $this->success('', [
            'list' => $list,
            'total' => $total,
        ]);
    }
    
    /**
     * 工单管理
     */
    public function tickets(): string
    {
        View::assign('title', '工单管理');
        return $this->fetchWithLayout('customer_service/tickets');
    }
    
    /**
     * 获取工单列表
     */
    public function getTicketList(): Response
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 15)));
        $status = $this->request->get('status', '');
        $category = $this->request->get('category', '');
        $keyword = $this->request->get('keyword', '');
        
        $query = Db::name('cs_ticket');
        
        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($category !== '') {
            $query->where('category', $category);
        }
        
        if ($keyword !== '') {
            $query->where('title|ticket_no', 'like', '%' . $keyword . '%');
        }
        
        $total = $query->count();
        $list = $query->order('id', 'desc')->page($page, $limit)->select();
        
        return $this->success('', [
            'list' => $list,
            'total' => $total,
        ]);
    }
    
    /**
     * 工单详情
     */
    public function ticketDetail(): string|Response
    {
        $ticketNo = (string) $this->request->get('ticket_no', '');
        if ($ticketNo === '') {
            $ticketNo = (string) $this->request->get('ticket_id', '');
        }
        if ($ticketNo === '') {
            $this->error('参数错误');
        }

        $ticket = Db::name('cs_ticket')->where('ticket_no', $ticketNo)->find();
        if (!$ticket) {
            $this->error('工单不存在');
        }

        $replies = Db::name('cs_ticket_reply')
            ->where('ticket_id', (int) ($ticket['id'] ?? 0))
            ->order('id', 'asc')
            ->select()
            ->toArray();

        View::assign('title', '工单详情');
        View::assign('ticket', $ticket);
        View::assign('replies', $replies);
        return $this->fetchWithLayout('customer_service/ticket_detail');
    }
    
    /**
     * 回复工单
     */
    public function replyTicket(): Response
    {
        $ticketNo = (string) $this->request->post('ticket_no', '');
        $content = (string) $this->request->post('content', '');
        
        if ($ticketNo === '' || $content === '') {
            return $this->error('参数错误');
        }
        
        $ticket = Db::name('cs_ticket')->where('ticket_no', $ticketNo)->find();
        if (!$ticket) {
            return $this->error('工单不存在');
        }
        
        Db::name('cs_ticket_reply')->insert([
            'ticket_id' => (int) ($ticket['id'] ?? 0),
            'user_id' => (int) $this->auth->id,
            'user_type' => 1,
            'content' => $content,
            'create_time' => time(),
        ]);

        Db::name('cs_ticket')->where('id', (int) ($ticket['id'] ?? 0))->update([
            'update_time' => time(),
        ]);
        
        return $this->success('回复成功');
    }
    
    /**
     * 更新工单状态
     */
    public function updateTicketStatus(): Response
    {
        $ticketNo = (string) $this->request->post('ticket_no', '');
        $status = (int) $this->request->post('status', 0);
        
        if ($ticketNo === '') {
            return $this->error('参数错误');
        }
        
        Db::name('cs_ticket')->where('ticket_no', $ticketNo)->update([
            'status' => $status,
            'update_time' => time(),
        ]);
        
        return $this->success('更新成功');
    }
    
    /**
     * 知识库管理
     */
    public function knowledge(): string
    {
        View::assign('title', '知识库管理');
        return $this->fetchWithLayout('customer_service/knowledge');
    }
    
    /**
     * 文章编辑
     */
    public function articleEdit(): string
    {
        $id = $this->request->get('id', 0);
        
        $article = [];
        if ($id) {
            $article = Db::name('cs_kb_article')->where('id', $id)->find();
        }
        
        // 获取分类列表
        $categories = Db::name('cs_kb_category')->order('sort', 'asc')->select();
        
        View::assign('title', $id ? '编辑文章' : '新增文章');
        View::assign('article', $article);
        View::assign('categories', $categories);
        return $this->fetchWithLayout('customer_service/article_edit');
    }
    
    /**
     * 保存文章
     */
    public function saveArticle(): Response
    {
        $id = $this->request->post('id', 0);
        $title = $this->request->post('title', '');
        $category_id = $this->request->post('category_id', 0);
        $summary = (string) $this->request->post('summary', '');
        $content = $this->request->post('content', '');
        $tags = (string) $this->request->post('tags', '');
        $keywords = (string) $this->request->post('keywords', '');
        $sort = $this->request->post('sort', 0);
        $status = (int) $this->request->post('status', 1);
        
        if (empty($title)) {
            return $this->error('标题不能为空');
        }
        
        $data = [
            'tenant_id' => 0,
            'title' => $title,
            'category_id' => $category_id,
            'summary' => $summary,
            'content' => $content,
            'tags' => $tags,
            'keywords' => $keywords,
            'sort' => $sort,
            'status' => $status,
            'updated_by' => (int) $this->auth->id,
            'update_time' => time(),
        ];
        
        if ($id) {
            Db::name('cs_kb_article')->where('id', $id)->update($data);
        } else {
            $data['create_time'] = time();
            $data['created_by'] = (int) $this->auth->id;
            Db::name('cs_kb_article')->insert($data);
        }
        
        return $this->success('保存成功');
    }
    
    /**
     * 删除文章
     */
    public function deleteArticle(): Response
    {
        $id = $this->request->post('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        Db::name('cs_kb_article')->where('id', $id)->delete();
        
        return $this->success('删除成功');
    }
    
    /**
     * 获取文章列表
     */
    public function getArticleList(): Response
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 15)));
        $categoryId = $this->request->get('category_id', '');
        $status = $this->request->get('status', '');
        $keyword = $this->request->get('keyword', '');
        
        $query = Db::name('cs_kb_article');
        
        if ($categoryId !== '' && $categoryId !== '0') {
            $query->where('category_id', $categoryId);
        }

        if ($status !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }
        
        if ($keyword !== '') {
            $query->where('title', 'like', '%' . $keyword . '%');
        }
        
        $total = $query->count();
        $list = $query->order('sort', 'asc')->order('id', 'desc')->page($page, $limit)->select();
        
        return $this->success('', [
            'list' => $list,
            'total' => $total,
        ]);
    }
    
    /**
     * 常见问题管理
     */
    public function faq(): string
    {
        View::assign('title', '常见问题');
        return $this->fetchWithLayout('customer_service/faq');
    }
    
    /**
     * 获取FAQ列表
     */
    public function getFaqList(): Response
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 15)));
        $keyword = $this->request->get('keyword', '');
        
        $query = Db::name('cs_faq');
        
        if ($keyword !== '') {
            $query->where('question', 'like', '%' . $keyword . '%');
        }
        
        $total = $query->count();
        $list = $query->order('sort', 'asc')->order('id', 'desc')->page($page, $limit)->select();
        
        return $this->success('', [
            'list' => $list,
            'total' => $total,
        ]);
    }
    
    /**
     * 保存FAQ
     */
    public function saveFaq(): Response
    {
        $id = $this->request->post('id', 0);
        $category = (string) $this->request->post('category', '');
        $question = (string) $this->request->post('question', '');
        $answer = (string) $this->request->post('answer', '');
        $sort = $this->request->post('sort', 0);
        $status = (int) $this->request->post('status', 1);
        
        if (empty($question) || empty($answer)) {
            return $this->error('问题和答案不能为空');
        }
        
        $data = [
            'tenant_id' => 0,
            'category' => $category,
            'question' => $question,
            'answer' => $answer,
            'sort' => $sort,
            'status' => $status,
            'update_time' => time(),
        ];
        
        if ($id) {
            Db::name('cs_faq')->where('id', $id)->update($data);
        } else {
            $data['create_time'] = time();
            Db::name('cs_faq')->insert($data);
        }
        
        return $this->success('保存成功');
    }
    
    /**
     * 删除FAQ
     */
    public function deleteFaq(): Response
    {
        $id = $this->request->post('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        Db::name('cs_faq')->where('id', $id)->delete();
        
        return $this->success('删除成功');
    }
    
    /**
     * 分类管理
     */
    public function categories(): string
    {
        View::assign('title', '分类管理');
        return $this->fetchWithLayout('customer_service/categories');
    }
    
    /**
     * 获取分类列表
     */
    public function getCategoryList(): Response
    {
        $list = Db::name('cs_kb_category')->order('sort', 'asc')->order('id', 'desc')->select();
        
        return $this->success('', [
            'list' => $list,
        ]);
    }
    
    /**
     * 保存分类
     */
    public function saveCategory(): Response
    {
        $id = $this->request->post('id', 0);
        $parentId = (int) $this->request->post('parent_id', 0);
        $name = (string) $this->request->post('name', '');
        $description = (string) $this->request->post('description', '');
        $icon = (string) $this->request->post('icon', '');
        $sort = $this->request->post('sort', 0);
        $status = (int) $this->request->post('status', 1);
        
        if (empty($name)) {
            return $this->error('分类名称不能为空');
        }
        
        $data = [
            'tenant_id' => 0,
            'parent_id' => $parentId,
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'sort' => $sort,
            'status' => $status,
            'update_time' => time(),
        ];
        
        if ($id) {
            Db::name('cs_kb_category')->where('id', $id)->update($data);
        } else {
            $data['create_time'] = time();
            Db::name('cs_kb_category')->insert($data);
        }
        
        return $this->success('保存成功');
    }
    
    /**
     * 删除分类
     */
    public function deleteCategory(): Response
    {
        $id = $this->request->post('id', 0);
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        // 检查是否有文章使用该分类
        $count = Db::name('cs_kb_article')->where('category_id', $id)->count();
        if ($count > 0) {
            return $this->error('该分类下还有文章，无法删除');
        }
        
        Db::name('cs_kb_category')->where('id', $id)->delete();
        
        return $this->success('删除成功');
    }
    
    /**
     * AI历史记录
     */
    public function aiHistory(): string
    {
        View::assign('title', 'AI历史记录');
        return $this->fetchWithLayout('customer_service/ai_history');
    }
    
    /**
     * 获取AI历史列表
     */
    public function getAiHistoryList(): Response
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 15)));
        $keyword = $this->request->get('keyword', '');
        
        $query = Db::name('cs_ai_history');
        
        if ($keyword !== '') {
            $query->where('user_message|ai_response', 'like', '%' . $keyword . '%');
        }
        
        $total = $query->count();
        $list = $query->order('id', 'desc')->page($page, $limit)->select();
        
        return $this->success('', [
            'list' => $list,
            'total' => $total,
        ]);
    }
    
    /**
     * 配置管理
     */
    public function config(): string
    {
        View::assign('title', '配置管理');
        return $this->fetchWithLayout('customer_service/config');
    }
    
    /**
     * 获取配置
     */
    public function getConfig(): Response
    {
        $rows = Db::name('cs_config')
            ->where('tenant_id', 0)
            ->select()
            ->toArray();

        $map = [];
        foreach ($rows as $row) {
            $k = (string) ($row['config_key'] ?? '');
            if ($k === '') {
                continue;
            }
            $raw = (string) ($row['config_value'] ?? '');
            $val = [];
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $val = $decoded;
                }
            }
            $map[$k] = [
                'key' => $k,
                'value' => $val,
                'description' => (string) ($row['description'] ?? ''),
                'update_time' => (int) ($row['update_time'] ?? 0),
            ];
        }

        $defaults = [
            'zhipuai_settings' => [
                'enabled' => true,
                'api_key' => '',
                'model' => 'glm-4-flash',
                'temperature' => 0.7,
                'max_tokens' => 4096,
                'system_prompt' => '你是一个专业的客服助手，帮助用户解答关于ThinkMES系统的问题。',
            ],
            'ai_settings' => [
                'enabled' => true,
                'api_key' => '',
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'system_prompt' => '你是一个专业的客服助手，帮助用户解答关于ThinkMES系统的问题。',
            ],
            'chat_settings' => [
                'welcome_message' => '您好，我是智能客服助手，有什么可以帮助您的吗？',
                'auto_assign' => true,
                'max_wait_time' => 60,
                'default_provider' => 'zhipu',
            ],
            'ticket_settings' => [
                'auto_close_days' => 7,
                'notification_enabled' => true,
                'email_enabled' => false,
            ],
        ];
        foreach ($defaults as $k => $v) {
            if (!isset($map[$k])) {
                $map[$k] = ['key' => $k, 'value' => $v, 'description' => '', 'update_time' => 0];
            }
        }

        return $this->success('', $map);
    }
    
    /**
     * 保存配置
     */
    public function saveConfig(): Response
    {
        $configs = $this->request->post('configs', []);
        if (!is_array($configs)) {
            return $this->error('参数错误');
        }

        $descMap = [
            'zhipuai_settings' => '智谱AI设置',
            'ai_settings' => 'OpenAI设置',
            'chat_settings' => '聊天设置',
            'ticket_settings' => '工单设置',
        ];

        $now = time();
        foreach ($configs as $key => $val) {
            $k = trim((string) $key);
            if ($k === '') {
                continue;
            }
            if (!is_array($val)) {
                $val = [];
            }
            $json = json_encode($val, JSON_UNESCAPED_UNICODE);
            if (!is_string($json) || $json === '') {
                $json = '{}';
            }

            $exists = Db::name('cs_config')->where('tenant_id', 0)->where('config_key', $k)->find();
            $data = [
                'tenant_id' => 0,
                'config_key' => $k,
                'config_value' => $json,
                'description' => (string) ($descMap[$k] ?? ''),
                'update_time' => $now,
            ];
            if ($exists) {
                Db::name('cs_config')->where('id', (int) $exists['id'])->update($data);
            } else {
                $data['create_time'] = $now;
                Db::name('cs_config')->insert($data);
            }
        }

        return $this->success('保存成功');
    }
}
