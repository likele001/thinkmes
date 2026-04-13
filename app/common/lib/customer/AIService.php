<?php
declare(strict_types=1);

namespace app\common\lib\customer;

use think\facade\Cache;
use think\facade\Db;

/**
 * AI智能问答服务
 * 集成OpenAI API实现智能客服
 */
class AIService
{
    private string $apiKey = '';
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    private string $model = 'gpt-3.5-turbo';
    private float $temperature = 0.7;
    private int $maxTokens = 1000;
    private string $systemPrompt = '';
    private int $cache_ttl = 3600;

    public function __construct()
    {
        $config = $this->getAIConfig();
        $this->apiKey = $config['api_key'] ?? '';
        $this->model = $config['model'] ?? 'gpt-3.5-turbo';
        $this->temperature = (float) ($config['temperature'] ?? 0.7);
        $this->maxTokens = (int) ($config['max_tokens'] ?? 1000);
        $this->systemPrompt = $config['system_prompt'] ?? '你是一个专业的客服助手，帮助用户解答关于系统的问题。';
        
        if (empty($this->apiKey)) {
            throw new \Exception('AI API密钥未配置，请在系统设置中配置OpenAI API密钥');
        }
    }

    /**
     * 获取AI配置
     */
    private function getAIConfig(): array
    {
        $config = Db::name('cs_config')
            ->where('config_key', 'ai_settings')
            ->where('tenant_id', 0)
            ->value('config_value');
        
        if (empty($config)) {
            return [
                'api_key' => '',
                'model' => 'gpt-3.5-turbo',
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'system_prompt' => '你是一个专业的客服助手，帮助用户解答关于系统的问题。'
            ];
        }
        
        return json_decode($config, true) ?? [];
    }

    /**
     * 获取知识库上下文
     */
    private function getKnowledgeBaseContext(string $question, int $tenantId = 0): array
    {
        $context = [
            'faq' => [],
            'articles' => [],
            'system_docs' => []
        ];

        // 1. 搜索相关FAQ
        $faqKeywords = $this->extractKeywords($question);
        $faqs = Db::name('cs_faq')
            ->where('status', 1)
            ->where('tenant_id', 'in', [0, $tenantId])
            ->where(function($q) use ($question, $faqKeywords) {
                $q->where('question', 'like', '%' . $question . '%');
                foreach ($faqKeywords as $keyword) {
                    $q->whereOr('question', 'like', '%' . $keyword . '%');
                }
            })
            ->limit(5)
            ->select()
            ->toArray();
        
        $context['faq'] = array_map(function($item) {
            return [
                'question' => $item['question'] ?? '',
                'answer' => $item['answer'] ?? ''
            ];
        }, $faqs);

        // 2. 搜索相关文章
        $articles = Db::name('cs_kb_article')
            ->alias('a')
            ->join('cs_kb_category c', 'a.category_id = c.id')
            ->where('a.status', 1)
            ->where('a.tenant_id', 'in', [0, $tenantId])
            ->where(function($q) use ($question, $faqKeywords) {
                $q->where('a.title', 'like', '%' . $question . '%')
                  ->whereOr('a.content', 'like', '%' . $question . '%');
                foreach ($faqKeywords as $keyword) {
                    $q->whereOr('a.title', 'like', '%' . $keyword . '%')
                      ->whereOr('a.content', 'like', '%' . $keyword . '%');
                }
            })
            ->field('a.id,a.title,a.summary,a.content,c.name as category_name')
            ->limit(3)
            ->select()
            ->toArray();
        
        $context['articles'] = array_map(function($item) {
            return [
                'id' => $item['id'] ?? 0,
                'title' => $item['title'] ?? '',
                'summary' => $item['summary'] ?? '',
                'category' => $item['category_name'] ?? ''
            ];
        }, $articles);

        return $context;
    }

    /**
     * 提取关键词
     */
    private function extractKeywords(string $text): array
    {
        // 简单的关键词提取（实际项目中可以使用更复杂的NLP算法）
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $words = preg_split('/\s+/', $text);
        $keywords = array_filter($words, function($word) {
            return mb_strlen($word) >= 2;
        });
        return array_unique(array_slice($keywords, 0, 10));
    }

    /**
     * 构建对话消息
     */
    private function buildMessages(string $question, array $context = [], array $history = []): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt]
        ];

        // 添加知识库上下文
        if (!empty($context)) {
            $contextText = "以下是相关的知识库信息，请参考这些信息回答用户问题：\n\n";
            
            if (!empty($context['faq'])) {
                $contextText .= "常见问题：\n";
                foreach ($context['faq'] as $faq) {
                    $contextText .= "Q: " . $faq['question'] . "\n";
                    $contextText .= "A: " . mb_substr(strip_tags($faq['answer']), 0, 200) . "\n\n";
                }
            }

            if (!empty($context['articles'])) {
                $contextText .= "相关文章：\n";
                foreach ($context['articles'] as $article) {
                    $contextText .= "- [" . $article['title'] . "] (" . $article['category'] . ")\n";
                    if (!empty($article['summary'])) {
                        $contextText .= "  " . mb_substr(strip_tags($article['summary']), 0, 150) . "\n";
                    }
                    $contextText .= "\n";
                }
            }

            $messages[] = ['role' => 'system', 'content' => $contextText];
        }

        // 添加历史对话
        foreach ($history as $item) {
            if (!empty($item['user_message'])) {
                $messages[] = ['role' => 'user', 'content' => $item['user_message']];
            }
            if (!empty($item['ai_response'])) {
                $messages[] = ['role' => 'assistant', 'content' => $item['ai_response']];
            }
        }

        // 添加当前问题
        $messages[] = ['role' => 'user', 'content' => $question];

        return $messages;
    }

    /**
     * 调用OpenAI API
     */
    private function callOpenAI(array $messages): array
    {
        try {
            $data = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
            ];

            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception('API请求失败: HTTP ' . $httpCode);
            }

            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('API响应解析失败');
            }

            return [
                'success' => true,
                'content' => $result['choices'][0]['message']['content'] ?? '',
                'usage' => $result['usage'] ?? [],
                'model' => $result['model'] ?? $this->model
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 问答接口
     */
    public function ask(string $question, string $sessionId = '', int $tenantId = 0, int $userId = 0): array
    {
        // 1. 检查缓存
        $cacheKey = 'ai_answer:' . md5($question);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        // 2. 获取对话历史
        $history = [];
        if (!empty($sessionId)) {
            $history = Db::name('cs_ai_history')
                ->where('session_id', $sessionId)
                ->order('id', 'desc')
                ->limit(5)
                ->select()
                ->toArray();
            $history = array_reverse($history);
        }

        // 3. 获取知识库上下文
        $context = $this->getKnowledgeBaseContext($question, $tenantId);

        // 4. 构建消息
        $messages = $this->buildMessages($question, $context, $history);

        // 5. 调用AI API
        $result = $this->callOpenAI($messages);

        if (!$result['success']) {
            return [
                'success' => false,
                'answer' => '抱歉，我暂时无法回答这个问题。请联系人工客服。',
                'error' => $result['error'] ?? ''
            ];
        }

        $answer = $result['content'];
        $tokensUsed = $result['usage']['total_tokens'] ?? 0;

        // 6. 保存对话历史
        if (!empty($sessionId)) {
            Db::name('cs_ai_history')->insert([
                'session_id' => $sessionId,
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'user_message' => $question,
                'ai_response' => $answer,
                'context_data' => json_encode($context),
                'sources' => json_encode(array_keys(array_filter($context))),
                'model' => $result['model'] ?? $this->model,
                'tokens_used' => $tokensUsed,
                'create_time' => time()
            ]);
        }

        // 7. 缓存结果
        Cache::set($cacheKey, json_encode([
            'success' => true,
            'answer' => $answer,
            'sources' => $context
        ]), $this->cache_ttl);

        return [
            'success' => true,
            'answer' => $answer,
            'sources' => $context,
            'tokens_used' => $tokensUsed
        ];
    }

    /**
     * 流式问答（用于实时响应）
     */
    public function askStream(string $question, callable $callback, string $sessionId = '', int $tenantId = 0, int $userId = 0): void
    {
        // 获取知识库上下文
        $context = $this->getKnowledgeBaseContext($question, $tenantId);

        // 构建消息
        $messages = $this->buildMessages($question, $context);

        try {
            $data = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
                'stream' => true
            ];

            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use ($callback) {
                $lines = explode("\n", $data);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, 'data: ') === 0) {
                        $json = substr($line, 6);
                        if ($json === '[DONE]') {
                            $callback('[DONE]', null);
                            continue;
                        }
                        $data = json_decode($json, true);
                        if (isset($data['choices'][0]['delta']['content'])) {
                            $content = $data['choices'][0]['delta']['content'];
                            $callback($content, $data);
                        }
                    }
                }
                return strlen($data);
            });

            curl_exec($ch);
            curl_close($ch);

        } catch (\Exception $e) {
            $callback('error: ' . $e->getMessage(), null);
        }
    }
}
