# KeleAdmin 系统前端入口URL文档

## 🌐 系统信息

- **系统名称**: KeleAdmin 多租户后台基础框架
- **访问域名**: https://mes.user.023ent.net/
- **技术栈**: ThinkPHP 8 + MySQL 5.7+ + Bootstrap 5
- **部署时间**: 2026年

## 📋 前端入口URL完整清单

### 🏠 系统入口

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/` | 系统根路径 | 未安装跳转安装页，已安装跳转后台 |

### 👤 用户中心 (User)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/user/login` | 用户登录页 | C端用户登录入口 |
| `https://mes.user.023ent.net/index/user/register` | 用户注册页 | 跳转登录页的注册Tab |
| `https://mes.user.023ent.net/index/user/logout` | 用户登出 | 退出登录 |
| `https://mes.user.023ent.net/index/user/index` | 会员中心首页 | 需要登录认证 |
| `https://mes.user.023ent.net/index/user/profile` | 个人资料 | 用户信息管理 |
| `https://mes.user.023ent.net/index/user/changepwd` | 修改密码 | 密码修改功能 |
| `https://mes.user.023ent.net/index/user/forgot` | 找回密码 | 发送验证码 |
| `https://mes.user.023ent.net/index/user/resetpwd` | 重置密码 | 通过验证码重置 |

### 👷 工人端 (Worker) - MES制造执行

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/worker/index` | 工人端首页 | 工人操作主界面 |
| `https://mes.user.023ent.net/index/worker/tasks` | 我的任务 | 查看分配的生产任务 |
| `https://mes.user.023ent.net/index/worker/report` | 报工页面 | 生产报工操作 |
| `https://mes.user.023ent.net/index/worker/records` | 报工记录 | 历史报工记录查看 |
| `https://mes.user.023ent.net/index/worker/wage` | 工资统计 | 工资收入统计 |
| `https://mes.user.023ent.net/index/worker/scan` | 扫码页面 | 扫码操作界面 |

### 🏪 客户门户 (Customer)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/customer/login` | 客户登录 | 客户门户登录 |
| `https://mes.user.023ent.net/index/customer/logout` | 客户登出 | 客户退出登录 |
| `https://mes.user.023ent.net/index/customer/index` | 客户中心 | 客户信息管理 |
| `https://mes.user.023ent.net/index/customer/orders` | 客户订单 | 订单查询和管理 |

### 📊 MES仪表板 (MesDashboard)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/dashboard` | MES仪表板 | 生产数据可视化展示 |

### 🔍 产品追溯 (Trace)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/trace/query` | 追溯查询 | 产品追溯查询入口 |
| `https://mes.user.023ent.net/index/trace/detail` | 追溯详情 | 产品详细信息展示 |
| `https://mes.user.023ent.net/index/trace.html` | 追溯详情 | HTML格式追溯页面 |

### 🛒 应用商店 (Store)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/store` | 应用商店首页 | 浏览可用应用 |
| `https://mes.user.023ent.net/index/store/detail` | 应用详情 | 查看应用详细信息 |
| `https://mes.user.023ent.net/index/store/publish` | 发布应用 | 开发者发布应用 |
| `https://mes.user.023ent.net/index/store/my` | 我的应用 | 查看已安装应用 |

### 💻 开发者中心 (DeveloperCenter)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/developer` | 开发者中心 | 开发者管理主页 |
| `https://mes.user.023ent.net/index/developer/login` | 开发者登录 | 开发者账号登录 |
| `https://mes.user.023ent.net/index/developer/register` | 开发者注册 | 开发者账号注册 |
| `https://mes.user.023ent.net/index/developer/center` | 开发者中心 | 同根路径功能 |

### 💳 租户购买 (Purchase)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/purchase` | 套餐选择页面 | SaaS套餐浏览和选择 |
| `https://mes.user.023ent.net/index/purchase/form` | 租户注册+购买 | 注册并购买套餐 |
| `https://mes.user.023ent.net/index/register` | 租户注册 | 跳转购买页面 |

### 🎯 自媒体工作流 (WeMedia)

#### 话题管理
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/topic/index` | GET | 话题管理首页 |
| `https://mes.user.023ent.net/index/wemedia/topic/add` | GET | 添加话题 |
| `https://mes.user.023ent.net/index/wemedia/topic/edit` | GET | 编辑话题 |
| `https://mes.user.023ent.net/index/wemedia/topic/del` | GET | 删除话题 |
| `https://mes.user.023ent.net/index/wemedia/topic/list` | GET | 话题列表 |
| `https://mes.user.023ent.net/index/wemedia/topic/save` | POST | 保存话题 |
| `https://mes.user.023ent.net/index/wemedia/topic/del` | POST | 删除话题 |
| `https://mes.user.023ent.net/index/wemedia/topic/generate` | POST | AI生成话题 |

#### 文案管理
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/copy/index` | GET | 文案管理首页 |
| `https://mes.user.023ent.net/index/wemedia/copy/add` | GET | 添加文案 |
| `https://mes.user.023ent.net/index/wemedia/copy/edit` | GET | 编辑文案 |
| `https://mes.user.023ent.net/index/wemedia/copy/list` | GET | 文案列表 |
| `https://mes.user.023ent.net/index/wemedia/copy/save` | POST | 保存文案 |
| `https://mes.user.023ent.net/index/wemedia/copy/del` | POST | 删除文案 |
| `https://mes.user.023ent.net/index/wemedia/copy/generateTitle` | POST | AI生成标题 |
| `https://mes.user.023ent.net/index/wemedia/copy/generateContent` | POST | AI生成内容 |
| `https://mes.user.023ent.net/index/wemedia/copy/generateTags` | POST | AI生成标签 |

#### 素材管理
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/material/index` | GET | 素材管理首页 |
| `https://mes.user.023ent.net/index/wemedia/material/add` | GET | 添加素材 |
| `https://mes.user.023ent.net/index/wemedia/material/edit` | GET | 编辑素材 |
| `https://mes.user.023ent.net/index/wemedia/material/list` | GET | 素材列表 |
| `https://mes.user.023ent.net/index/wemedia/material/save` | POST | 保存素材 |
| `https://mes.user.023ent.net/index/wemedia/material/del` | POST | 删除素材 |
| `https://mes.user.023ent.net/index/wemedia/material/upload` | POST | 上传素材 |

#### 视频管理
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/video/index` | GET | 视频管理首页 |
| `https://mes.user.023ent.net/index/wemedia/video/add` | GET | 添加视频 |
| `https://mes.user.023ent.net/index/wemedia/video/edit` | GET | 编辑视频 |
| `https://mes.user.023ent.net/index/wemedia/video/list` | GET | 视频列表 |
| `https://mes.user.023ent.net/index/wemedia/video/save` | POST | 保存视频 |
| `https://mes.user.023ent.net/index/wemedia/video/del` | POST | 删除视频 |
| `https://mes.user.023ent.net/index/wemedia/video/generateScript` | POST | AI生成脚本 |
| `https://mes.user.023ent.net/index/wemedia/video/generateFromCopy` | POST | 从文案生成视频 |
| `https://mes.user.023ent.net/index/wemedia/video/generateAudio` | POST | AI生成音频 |
| `https://mes.user.023ent.net/index/wemedia/video/generateVideo` | POST | AI生成视频 |
| `https://mes.user.023ent.net/index/wemedia/video/generateCoverImage` | POST | AI生成封面图 |
| `https://mes.user.023ent.net/index/wemedia/video/generateAiVideo` | POST | AI生成视频 |
| `https://mes.user.023ent.net/index/wemedia/video/generateAiVideoFromText` | POST | 从文本生成AI视频 |
| `https://mes.user.023ent.net/index/wemedia/video/generateDigitalHuman` | POST | AI生成数字人 |
| `https://mes.user.023ent.net/index/wemedia/video/videoGenerateTip` | GET | 视频生成提示 |

#### 排程管理
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/schedule/index` | GET | 排程管理首页 |
| `https://mes.user.023ent.net/index/wemedia/schedule/add` | GET | 添加排程 |
| `https://mes.user.023ent.net/index/wemedia/schedule/edit` | GET | 编辑排程 |
| `https://mes.user.023ent.net/index/wemedia/schedule/list` | GET | 排程列表 |
| `https://mes.user.023ent.net/index/wemedia/schedule/save` | POST | 保存排程 |
| `https://mes.user.023ent.net/index/wemedia/schedule/del` | POST | 删除排程 |

#### 报表管理
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/report/index` | GET | 报表管理首页 |
| `https://mes.user.023ent.net/index/wemedia/report/list` | GET | 报表列表 |
| `https://mes.user.023ent.net/index/wemedia/report/chart` | GET | 报表图表 |
| `https://mes.user.023ent.net/index/wemedia/report/save` | POST | 保存报表 |
| `https://mes.user.023ent.net/index/wemedia/report/del` | POST | 删除报表 |

#### 合规检查
| URL | 方法 | 功能 |
|-----|------|------|
| `https://mes.user.023ent.net/index/wemedia/compliance/index` | GET | 合规检查首页 |
| `https://mes.user.023ent.net/index/wemedia/compliance/list` | GET | 合规列表 |
| `https://mes.user.023ent.net/index/wemedia/compliance/check` | POST | 执行合规检查 |
| `https://mes.user.023ent.net/index/wemedia/compliance/del` | POST | 删除合规记录 |

### 🌐 多语言支持

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/lang/:lang` | 语言切换 | 支持zh-cn, en-us, ko等语言 |

### 🎧 客服中心 (CustomerService)

| URL | 功能 | 说明 |
|-----|------|------|
| `https://mes.user.023ent.net/index/customer-service` | 客服中心 | 客服服务主页 |
| `https://mes.user.023ent.net/index/customer-service/chat` | 客服聊天 | 在线客服对话 |
| `https://mes.user.023ent.net/index/customer-service/knowledge` | 知识库 | 帮助文档 |
| `https://mes.user.023ent.net/index/customer-service/ticket` | 工单系统 | 提交和查看工单 |

## 📊 URL分类统计

| 功能模块 | URL数量 | 主要用途 |
|---------|---------|----------|
| 用户中心 | 7个 | 登录、注册、个人资料管理 |
| 工人端 | 6个 | MES工人操作界面 |
| 客户门户 | 4个 | 客户订单查看和管理 |
| MES仪表板 | 1个 | 生产数据可视化 |
| 产品追溯 | 3个 | 生产追溯查询 |
| 应用商店 | 4个 | 应用浏览和发布 |
| 开发者中心 | 4个 | 开发者账号管理 |
| 租户购买 | 3个 | SaaS套餐购买流程 |
| 自媒体工作流 | 33个 | AI内容生成和管理 |
| 客服中心 | 4个 | 客服支持和工单系统 |
| 多语言支持 | 1个 | 国际化语言切换 |
| 系统入口 | 1个 | 根路径跳转 |

**总计：70个前端入口URL**

## 🔐 认证说明

### 用户认证
- **用户中心**: 使用 `user_token` cookie进行认证
- **工人端**: 使用 `user_token` cookie进行认证
- **客户门户**: 使用 `customer_token` cookie进行认证
- **未认证访问**: 自动跳转到相应的登录页面

### 权限控制
- 所有需要认证的页面都有token验证
- 基于RBAC的权限控制系统
- 租户数据隔离机制

## 🌍 多语言支持

系统支持多语言切换，通过cookie `think_lang` 控制：
- **zh-cn**: 简体中文
- **en-us**: 英文
- **ko**: 韩文
- 其他可配置语言

语言切换后自动跳转回原页面，提升用户体验。

## 📱 移动端适配

所有前端页面都支持移动端访问，采用响应式设计，适配各种设备尺寸。

## 🔧 技术特性

- **框架**: ThinkPHP 8.0+
- **数据库**: MySQL 5.7+
- **前端**: Bootstrap 5 + CoreUI
- **认证**: Token-based认证
- **多租户**: 完整的租户数据隔离
- **国际化**: 支持多语言切换
- **缓存**: Redis缓存支持

## 📞 技术支持

如有问题，请联系：
- **客服中心**: https://mes.user.023ent.net/index/customer-service
- **工单系统**: https://mes.user.023ent.net/index/customer-service/ticket

---

**文档版本**: 1.0  
**更新时间**: 2026-04-10  
**系统版本**: KeleAdmin 基础框架 + MES/CRM/AI等业务应用
