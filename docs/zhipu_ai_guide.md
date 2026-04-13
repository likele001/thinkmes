# 智谱AI客服系统配置指南

## 🎉 已集成智谱AI（GLM-4.7-Flash）

智谱AI提供了免费的API调用额度，非常适合作为客服系统的AI引擎。

## ✨ 智谱AI优势

- **免费额度**：新用户可以获得大量免费调用额度
- **速度快**：GLM-4-Flash模型响应速度极快
- **中文友好**：专门针对中文场景优化
- **能力强**：在中文理解、对话、知识问答等方面表现优秀

## 📋 可用模型

### 免费模型
- **GLM-4 Flash**：推荐使用，速度快，免费
- **GLM-3 Turbo**：基础模型，免费

### 付费模型
- **GLM-4**：标准版，能力强
- **GLM-4 Plus**：最强版本

## 🚀 快速开始

### 1. 获取API密钥

1. 访问智谱AI开放平台：https://open.bigmodel.cn/
2. 注册并登录账号
3. 进入【API Key】页面：https://open.bigmodel.cn/usercenter/apikeys
4. 点击【生成API Key】
5. 复制API Key（格式：id.secret）

### 2. 配置系统

1. 登录后台管理系统
2. 进入【客服管理】->【系统配置】
3. 在【智谱AI】标签页填入：
   - **API Key**：粘贴你的API Key
   - **模型**：选择 `glm-4-flash`（推荐）
   - **温度参数**：0.7（控制随机性）
   - **最大Token数**：4096
   - **系统提示词**：自定义AI助手的角色设定
4. 点击【保存配置】

### 3. 测试

访问前台客服中心：`https://mes.user.023ent.net/index/customer-service`

尝试提问，系统将使用智谱AI进行回答！

## 🔧 配置选项说明

### AI提供商选择
在【聊天设置】标签页中，可以选择默认AI提供商：

- **智谱AI**：优先使用智谱AI（推荐，有免费额度）
- **OpenAI**：使用OpenAI GPT系列
- **自动选择**：智能降级，优先使用智谱AI，失败时自动切换到OpenAI

### 温度参数
- 范围：0-1
- 低值（0.1-0.3）：输出更确定，适合事实性问题
- 中值（0.4-0.7）：平衡创造性和准确性（推荐）
- 高值（0.8-1.0）：输出更随机，适合创意性内容

### 最大Token数
- 控制AI回复的最大长度
- GLM-4-Flash：最大128,000 tokens
- 建议：2048-4096（问答场景）

## 📊 使用统计

在后台【AI对话历史】中可以查看：
- 所有对话记录
- Token消耗统计
- 使用的AI提供商
- 对话时间和会话ID

## 💡 最佳实践

### 1. 系统提示词优化

根据您的需求定制AI助手：

```
你是一个专业的ThinkMES系统客服助手，负责：
- 回答用户关于系统功能的问题
- 提供操作指导
- 解答技术问题

请使用友好、专业的语气，提供准确、简洁的回答。
如果不确定答案，建议用户联系人工客服。
```

### 2. 知识库配合

- 在【知识库管理】中添加产品文档
- 在【FAQ管理】中添加常见问题
- AI会自动参考这些信息回答用户

### 3. 成本控制

- GLM-4-Flash：完全免费，适合日常使用
- 设置缓存时间，减少重复调用
- 监控Token消耗

## 🔒 安全建议

1. **保护API密钥**
   - 不要在前端代码中暴露API密钥
   - 定期轮换API密钥
   - 使用最小权限原则

2. **访问控制**
   - 限制AI功能的使用频率
   - 监控异常调用
   - 设置合理的超时时间

3. **内容过滤**
   - 在系统提示词中添加内容过滤规则
   - 敏感词过滤
   - 回答内容审核

## 🆚 智谱AI vs OpenAI

| 特性 | 智谱AI | OpenAI |
|------|--------|--------|
| 价格 | 免费额度充足 | 需付费 |
| 中文能力 | 专门优化 | 优秀 |
| 响应速度 | 快（国内服务器） | 慢（需翻墙） |
| 稳定性 | 高 | 高 |
| API复杂度 | 简单 | 简单 |
| 模型选择 | 4个模型 | 3个主要模型 |

## 📱 API调用示例

### 前端调用

```javascript
// 使用默认提供商（智谱AI）
fetch('/api/chat/ask', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        question: '如何创建工单？',
        session_id: 'session_123'
    })
}).then(res => res.json());

// 指定使用智谱AI
fetch('/api/chat/ask', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        question: '如何创建工单？',
        session_id: 'session_123',
        provider: 'zhipu'
    })
}).then(res => res.json());
```

### PHP后端调用

```php
use app\common\lib\customer\ZhipuAIService;

$zhipu = new ZhipuAIService();
$result = $zhipu->ask('用户问题', 'session_id', $tenantId, $userId);

if ($result['success']) {
    echo $result['answer'];
}
```

## 🐛 常见问题

### 1. API密钥无效
**问题**：提示"API密钥未配置"或"鉴权失败"

**解决**：
- 检查API Key格式是否正确（id.secret）
- 确认API Key已启用
- 重新生成API Key

### 2. 响应慢
**问题**：AI回答速度慢

**解决**：
- 检查网络连接
- 使用GLM-4-Flash模型（更快）
- 减少max_tokens值

### 3. 回答不准确
**问题**：AI回答不符合预期

**解决**：
- 优化系统提示词
- 添加相关知识库文章
- 调整温度参数

### 4. 超出限额
**问题**：提示"超出调用限额"

**解决**：
- 查看智谱AI控制台的额度使用情况
- 升级到付费版本
- 或切换到OpenAI

## 📈 免费额度说明

智谱AI新用户免费额度：
- GLM-4-Flash：25,000,000 tokens / 天
- GLM-3-Turbo：200,000,000 tokens / 天

一般客服场景完全够用！

## 🔗 相关链接

- 智谱AI官网：https://bigmodel.cn/
- 开发者平台：https://open.bigmodel.cn/
- API文档：https://open.bigmodel.cn/dev/api
- 控制台：https://open.bigmodel.cn/usercenter/apikeys
- 定价说明：https://open.bigmodel.cn/pricing

## 🎓 进阶技巧

### 1. 自定义模型切换策略

在前端实现智能切换：
- 简单问题用GLM-4-Flash（免费）
- 复杂问题用GLM-4（付费）
- 成语理解用GLM-3-Turbo（免费）

### 2. 流式输出

实现打字机效果，提升用户体验：

```php
$zhipu->askStream($question, function($content) {
    echo $content;
    ob_flush();
    flush();
});
```

### 3. 多轮对话优化

保存对话上下文：
- 提取用户意图
- 识别实体信息
- 维护对话状态

---

## ✨ 总结

智谱AI是ThinkMES客服系统的理想选择：

✅ 完全免费，成本可控
✅ 中文能力强，用户体验好
✅ 国内服务，稳定快速
✅ 配置简单，开箱即用

立即配置，开启智能客服新时代！🚀
