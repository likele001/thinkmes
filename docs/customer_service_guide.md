# 智能客服系统使用指南

## 系统概述

智能客服系统是一个基于AI的客户服务平台，提供智能问答、工单系统、在线对话和知识库管理等功能。

## 功能特性

### 1. 智能问答
- 集成OpenAI GPT模型
- 基于知识库的上下文理解
- 支持多轮对话
- 自动保存对话历史

### 2. 工单系统
- 用户提交技术支持工单
- 工单分类和优先级管理
- 工单状态跟踪
- 满意度评价

### 3. 知识库
- 文章分类管理
- 全文搜索
- 文章点赞和有用反馈
- 浏览统计

### 4. 悬浮聊天组件
- 在任意页面快速访问客服
- 快捷问题按钮
- 响应式设计
- 会话持久化

## 安装部署

### 1. 数据库迁移

```bash
# 导入数据库表结构
mysql -u root -p your_database < database/migrate_customer_service.sql
```

### 2. 配置OpenAI API

在系统设置中配置OpenAI API密钥：

```sql
INSERT INTO `fa_cs_config` (`config_key`, `config_value`, `description`) 
VALUES ('ai_settings', 
'{"api_key":"your-openai-api-key","model":"gpt-3.5-turbo","temperature":0.7,"max_tokens":1000}', 
'AI设置');
```

或通过后台管理界面配置。

### 3. 前端集成

#### 方式一：独立页面
直接访问 `/customer-service` 即可使用完整的客服中心。

#### 方式二：悬浮组件

在需要客服功能的页面引入悬浮组件：

```html
<script src="/assets/js/customer-service-widget.js"></script>
```

组件会自动在页面右下角显示聊天按钮。

### 4. 路由配置

系统已自动配置以下路由：

**前端路由**：
- `/customer-service` - 客服中心首页
- `/customer-service/chat` - 智能问答
- `/customer-service/knowledge` - 知识库
- `/customer-service/ticket` - 工单系统

**API路由**：
- `POST /api/chat/ask` - AI问答
- `GET /api/chat/faq` - 获取常见问题
- `GET /api/chat/search` - 搜索知识库
- `POST /api/ticket/create` - 创建工单
- 更多API详见路由配置文件

## API接口文档

### 1. AI问答

**请求**：
```http
POST /api/chat/ask
Content-Type: application/json

{
  "question": "如何开始使用系统？",
  "session_id": "optional_session_id"
}
```

**响应**：
```json
{
  "code": 1,
  "msg": "回答成功",
  "data": {
    "answer": "您可以通过以下步骤开始...",
    "session_id": "chat_1234567890_abc",
    "sources": {...},
    "tokens_used": 250
  }
}
```

### 2. 获取常见问题

**请求**：
```http
GET /api/chat/faq?category=快速入门
```

**响应**：
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "question": "如何开始使用ThinkMES？",
        "answer": "ThinkMES是一款制造执行系统...",
        "category": "快速入门"
      }
    ]
  }
}
```

### 3. 搜索知识库

**请求**：
```http
GET /api/chat/search?keyword=工单管理&page=1&limit=10
```

**响应**：
```json
{
  "code": 1,
  "data": {
    "total": 15,
    "per_page": 10,
    "current_page": 1,
    "data": [...]
  }
}
```

### 4. 创建工单

**请求**：
```http
POST /api/ticket/create
Content-Type: application/json

{
  "category": "技术问题",
  "priority": 2,
  "title": "无法登录系统",
  "description": "详细描述问题..."
}
```

**响应**：
```json
{
  "code": 1,
  "msg": "工单创建成功",
  "data": {
    "ticket_id": 123,
    "ticket_no": "TKT2023121500011234"
  }
}
```

## 配置说明

### AI设置

在 `fa_cs_config` 表中配置：

```json
{
  "enabled": true,
  "model": "gpt-3.5-turbo",
  "temperature": 0.7,
  "max_tokens": 1000,
  "system_prompt": "你是一个专业的客服助手，帮助用户解答关于ThinkMES系统的问题。"
}
```

### 聊天设置

```json
{
  "welcome_message": "您好，我是智能客服助手，有什么可以帮助您的吗？",
  "auto_assign": true,
  "max_wait_time": 60
}
```

### 工单设置

```json
{
  "auto_close_days": 7,
  "notification_enabled": true,
  "email_enabled": false
}
```

## 知识库管理

### 1. 添加文章

可以通过后台管理界面或直接操作数据库添加知识库文章：

```sql
INSERT INTO `fa_cs_kb_article` 
(`category_id`, `title`, `summary`, `content`, `tags`, `status`) 
VALUES 
(1, '文章标题', '文章摘要', '文章内容(Markdown)', '标签1,标签2', 1);
```

### 2. 添加FAQ

```sql
INSERT INTO `fa_cs_faq` 
(`category`, `question`, `answer`, `sort`, `status`) 
VALUES 
('快速入门', '如何开始使用？', '您可以通过以下步骤...', 1, 1);
```

## 自定义开发

### 修改悬浮组件样式

在引入组件脚本前，可以覆盖默认配置：

```html
<script>
window.CS_WIDGET_CONFIG = {
    primaryColor: '#ff6b6b',
    position: 'left',
    autoOpen: true,
    welcomeMessage: '欢迎咨询我们的产品！'
};
</script>
<script src="/assets/js/customer-service-widget.js"></script>
```

### 扩展AI功能

修改 `AIService.php` 可以实现：

1. 对接其他AI模型
2. 自定义知识库检索
3. 添加上下文理解
4. 实现流式响应

## 常见问题

### Q: 如何更换AI模型？
A: 修改数据库配置表中的 `ai_settings`，将 `model` 字段改为所需的模型（如 `gpt-4`）。

### Q: 如何添加更多快捷问题？
A: 编辑前端页面的快捷问题按钮，或修改悬浮组件的配置。

### Q: 对话历史保存多久？
A: 默认永久保存在数据库中，可以通过清理脚本定期删除过期记录。

### Q: 是否支持多租户？
A: 是的，系统完全支持多租户隔离，每个租户的数据是独立的。

### Q: 如何实现人工客服接管？
A: 可以结合WebSocket实现实时对话，系统预留了会话分配机制。

## 性能优化

1. **启用缓存**：AI回答结果会缓存1小时
2. **知识库索引**：确保数据库全文索引已创建
3. **CDN加速**：静态资源可以使用CDN
4. **会话清理**：定期清理过期的AI对话历史

## 安全建议

1. **API密钥保护**：不要在前端暴露OpenAI API密钥
2. **输入验证**：所有用户输入都经过转义处理
3. **访问控制**：根据用户权限显示不同功能
4. **数据加密**：敏感数据传输使用HTTPS

## 技术支持

如遇到问题，请：
1. 查看系统日志
2. 检查API配置
3. 提交技术工单

## 更新日志

### v1.0.0 (2023-12-15)
- 初始版本发布
- 集成OpenAI GPT-3.5
- 实现智能问答
- 添加工单系统
- 创建知识库
- 开发悬浮聊天组件

---

© 2023 ThinkMES 智能客服系统
