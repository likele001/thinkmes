<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\lib\customer\AIService;
use app\common\lib\customer\ZhipuAIService;
use think\facade\Db;
use think\Response;

class Chat extends BaseController
{
    /**
     * 获取当前租户ID（支持平台管理员）
     */
    private function getTenantId(): int
    {
        // 检查是否是平台超级管理员
        if (property_exists($this->request, 'isAdmin') && $this->request->isAdmin === true) {
            // 平台管理员使用tenant_id=0
            return 0;
        }
        
        if (property_exists($this->request, 'tenantId')) {
            return (int) $this->request->tenantId;
        }
        
        // 从请求参数中获取
        $tenantId = (int) $this->request->param('tenant_id', 0);
        return $tenantId;
    }

    /**
     * 获取当前用户ID
     */
    private function getUserId(): int
    {
        if (property_exists($this->request, 'userId')) {
            return (int) $this->request->userId;
        }
        
        return (int) $this->request->param('user_id', 0);
    }

    /**
     * AI问答接口
     */
    public function ask(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $question = trim((string) $this->request->post('question', ''));
        $sessionId = trim((string) $this->request->post('session_id', ''));
        $provider = trim((string) $this->request->post('provider', 'auto')); // auto, openai, zhipu
        
        if (empty($question)) {
            return $this->error('请输入您的问题');
        }

        // 获取租户和用户ID
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        // 生成会话ID
        if (empty($sessionId)) {
            $sessionId = $this->generateSessionId();
        }

        try {
            // 选择AI服务提供商
            if ($provider === 'auto') {
                // 默认使用智谱AI
                $provider = 'zhipu';
            }
            
            if ($provider === 'zhipu') {
                $aiService = new ZhipuAIService();
                $result = $aiService->ask($question, $sessionId, $tenantId, $userId);
            } else {
                $aiService = new AIService();
                $result = $aiService->ask($question, $sessionId, $tenantId, $userId);
            }

            if (!$result['success']) {
                // 如果智谱AI失败，尝试降级到OpenAI
                if ($provider === 'zhipu') {
                    try {
                        $openAIService = new AIService();
                        $result = $openAIService->ask($question, $sessionId, $tenantId, $userId);
                        $result['provider'] = 'openai_fallback';
                    } catch (\Exception $e) {
                        // OpenAI也失败，返回友好提示
                        $result = [
                            'success' => false,
                            'answer' => '抱歉，AI服务暂时不可用。请稍后重试或联系人工客服。',
                            'error' => '所有AI服务均不可用'
                        ];
                    }
                }
            }

            if (!$result['success']) {
                return $this->error($result['answer'] ?? '抱歉，服务暂时不可用');
            }

            return $this->success('回答成功', [
                'answer' => $result['answer'],
                'session_id' => $sessionId,
                'sources' => $result['sources'] ?? [],
                'tokens_used' => $result['tokens_used'] ?? 0,
                'provider' => $result['provider'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            return $this->error('服务异常：' . $e->getMessage());
        }
    }

    /**
     * 获取会话历史
     */
    public function history(): Response
    {
        $sessionId = trim((string) $this->request->get('session_id', ''));
        
        if (empty($sessionId)) {
            return $this->error('会话ID不能为空');
        }

        $history = Db::name('cs_ai_history')
            ->where('session_id', $sessionId)
            ->order('id', 'asc')
            ->limit(20)
            ->select()
            ->toArray();

        return $this->success('', [
            'history' => array_map(function($item) {
                return [
                    'question' => $item['user_message'] ?? '',
                    'answer' => $item['ai_response'] ?? '',
                    'time' => $item['create_time'] ?? 0,
                    'provider' => $item['model'] ?? ''
                ];
            }, $history)
        ]);
    }

    /**
     * 获取常见问题
     */
    public function faq(): Response
    {
        $category = trim((string) $this->request->get('category', ''));
        $keyword = trim((string) $this->request->get('keyword', ''));
        $tenantId = $this->getTenantId();
        
        $query = Db::name('cs_faq')
            ->where('status', 1)
            ->where('tenant_id', 'in', [0, $tenantId]) // 支持全局和租户特定
            ->order('sort', 'asc');

        if (!empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($keyword)) {
            $query->where('question', 'like', '%' . $keyword . '%');
        }

        $list = $query->limit(50)->select()->toArray();

        return $this->success('', [
            'list' => $list
        ]);
    }

    /**
     * 搜索知识库文章
     */
    public function search(): Response
    {
        $keyword = trim((string) $this->request->get('keyword', ''));
        $categoryId = (int) $this->request->get('category_id', 0);
        $page = max(1, (int) $this->request->get('page', 1));
        $limit = max(1, min(50, (int) $this->request->get('limit', 10)));
        $tenantId = $this->getTenantId();

        if (empty($keyword)) {
            return $this->error('请输入搜索关键词');
        }

        $query = Db::name('cs_kb_article')
            ->alias('a')
            ->join('cs_kb_category c', 'a.category_id = c.id')
            ->where('a.status', 1)
            ->where('a.tenant_id', 'in', [0, $tenantId]) // 支持全局和租户特定
            ->where(function($q) use ($keyword) {
                $q->where('a.title', 'like', '%' . $keyword . '%')
                  ->whereOr('a.content', 'like', '%' . $keyword . '%')
                  ->whereOr('a.tags', 'like', '%' . $keyword . '%');
            });

        if ($categoryId > 0) {
            $query->where('a.category_id', $categoryId);
        }

        $result = $query
            ->field('a.id,a.title,a.summary,a.category_id,a.views,a.likes,a.helpful,c.name as category_name')
            ->order('a.views', 'desc')
            ->order('a.helpful', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page])
            ->toArray();

        return $this->success('', $result);
    }

    /**
     * 获取文章详情
     */
    public function article(): Response
    {
        $id = (int) $this->request->get('id', 0);
        $tenantId = $this->getTenantId();
        
        if ($id <= 0) {
            return $this->error('参数错误');
        }

        $article = Db::name('cs_kb_article')
            ->alias('a')
            ->join('cs_kb_category c', 'a.category_id = c.id')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->where('a.tenant_id', 'in', [0, $tenantId]) // 支持全局和租户特定
            ->field('a.*,c.name as category_name')
            ->find();

        if (!$article) {
            return $this->error('文章不存在');
        }

        // 增加浏览次数
        Db::name('cs_kb_article')->where('id', $id)->inc('views', 1)->update();

        // 获取相关文章
        $related = Db::name('cs_kb_article')
            ->where('category_id', $article['category_id'])
            ->where('id', '<>', $id)
            ->where('status', 1)
            ->where('tenant_id', 'in', [0, $tenantId])
            ->field('id,title,summary')
            ->limit(5)
            ->select()
            ->toArray();

        return $this->success('', [
            'article' => $article,
            'related' => $related
        ]);
    }

    /**
     * 文章点赞/有用反馈
     */
    public function helpful(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $id = (int) $this->request->post('id', 0);
        $type = trim((string) $this->request->post('type', 'helpful')); // helpful or like
        
        if ($id <= 0) {
            return $this->error('参数错误');
        }

        if ($type === 'like') {
            Db::name('cs_kb_article')->where('id', $id)->inc('likes', 1)->update();
        } else {
            Db::name('cs_kb_article')->where('id', $id)->inc('helpful', 1)->update();
        }

        return $this->success('操作成功');
    }

    /**
     * 获取知识库分类
     */
    public function categories(): Response
    {
        $tenantId = $this->getTenantId();
        
        $categories = Db::name('cs_kb_category')
            ->where('status', 1)
            ->where('tenant_id', 'in', [0, $tenantId]) // 支持全局和租户特定
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        return $this->success('', [
            'list' => $categories
        ]);
    }

    /**
     * 获取可用AI模型列表
     */
    public function models(): Response
    {
        $zhipuModels = ZhipuAIService::getAvailableModels();
        
        return $this->success('', [
            'zhipu' => $zhipuModels,
            'openai' => [
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                'gpt-4' => 'GPT-4',
                'gpt-4-turbo' => 'GPT-4 Turbo',
                'gpt-4o' => 'GPT-4o'
            ]
        ]);
    }

    /**
     * 生成会话ID
     */
    private function generateSessionId(): string
    {
        return hash('sha256', microtime(true) . random_bytes(32) . uniqid('', true));
    }

    /**
     * 清除会话历史
     */
    public function clearHistory(): Response
    {
        if (!$this->request->isPost()) {
            return $this->error('请求方式错误');
        }

        $sessionId = trim((string) $this->request->post('session_id', ''));
        
        if (empty($sessionId)) {
            return $this->error('会话ID不能为空');
        }

        Db::name('cs_ai_history')
            ->where('session_id', $sessionId)
            ->delete();

        return $this->success('会话历史已清除');
    }
}
