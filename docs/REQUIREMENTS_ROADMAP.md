# ThinkMes 需求与实施对照

## 1 核心需求（用户明确要求 + 补充完善）

### 2.1.1 基础需求

| 需求项 | 状态 | 说明 |
|--------|------|------|
| 前后端分离架构，接口标准化，支持跨域 | ✅ 已做 | BaseController 统一 success/error；API 返回 JSON；`app/api/middleware.php` AllowCrossDomain + TenantResolve |
| 平台超级管理员 + 租户管理员 + 前端 C 端用户，三级账号体系 | ✅ 已做 | fa_admin(tenant_id/pid)、fa_user；超管 tenant_id=0；C 端 api/user 注册/登录/找回 + UserModel |
| SaaS 多租户：数据隔离、租户套餐、租户域名绑定、到期管理 | ✅ 已做 | TenantResolve 按域名/Header 解析；fa_tenant、fa_tenant_package；管理员/日志/用户按 tenant_id 隔离；到期字段 expire_time |
| 权限管理：无限级父子管理员、单管理员多角色、权限继承、数据权限（个人/子级/全部） | ✅ 已做 | pid 父子链；role_ids 多角色；Auth::getRuleIds 继承；data_scope 1/2/3，Auth::getAdminDataScopeIds、Backend::canManageAdminId |
| 效率工具：一键 CRUD 生成、JS/CSS 压缩、CDN 部署、Swagger 接口文档 | 部分 | ✅ CRUD：`php think crud -t 表名`；✅ Swagger：GET `/api/doc` 返回 OpenAPI 3.0；⏳ JS/CSS 压缩、CDN 部署 待做 |
| 文件上传：分片上传、拖拽/粘贴上传、图片压缩、水印、多存储方式（本地/OSS） | 部分 | ✅ 分片：uploadChunk、mergeChunks；✅ 本地 + OSS 配置占位；⏳ 拖拽/粘贴、压缩、水印 待做 |
| 系统配置：网站设置、多语言、缓存管理、安全设置 | ✅ 已做 | Config 分组 base/upload/safe/lang/cache；group/save；登录验证码 safe.login_captcha；seed_config.sql |

### 2.1.2 扩展需求

| 需求项 | 状态 | 说明 |
|--------|------|------|
| 插件扩展：在线安装/卸载/升级、钩子机制、插件配置 | 部分 | ✅ 命令行 install/uninstall；✅ Hook::listen/trigger，login_after/upload_after；addons/demo；⏳ 在线安装/升级、插件配置界面 待做 |
| 前端用户：注册/登录/找回密码、会员中心、等级/积分、第三方登录 | 部分 | ✅ 注册/登录/找回/重置；UserAuth Token；fa_user 含 level/score；⏳ 会员中心接口、第三方登录 待做 |
| 高级表格：固定列/表头、跨页选择、Excel 导入导出、树形表格 | 部分 | ✅ Bootstrap Table 服务端分页；✅ 权限规则树形；⏳ 固定列/表头、跨页选择、Excel 导入导出 待做 |
| 日志审计：操作日志、登录日志、错误日志、接口日志，支持筛选导出 | 部分 | ✅ 操作日志 + 数据权限过滤；✅ log/export CSV；登录记在操作日志；⏳ 独立错误日志、接口日志 待做 |

---

## 2 实施与文件说明

### 2.1 实施顺序建议

1. **数据库**：`database/init.sql` → `database/init_tenant_user.sql` → `database/seed_config.sql`（可选）→ 菜单为空时执行 `database/seed_auth_rule.sql`。
2. **租户与隔离**：`app/common/middleware/TenantResolve.php`，api/admin 已注册；查询统一带 tenant_id。
3. **C 端 API**：`app/api/controller/User.php`，路由见 `app/api/route/app.php`；Token 见 `app/api/middleware/UserAuth.php`。
4. **数据权限**：`app/common/lib/Auth.php::getAdminDataScopeIds`，Admin/Log 已应用。

### 2.2 关键文件/配置

| 功能 | 文件/入口 |
|------|-----------|
| 统一响应 / 跨域 | `app/common/controller/BaseController.php`，`app/api/middleware.php` |
| 租户解析 | `app/common/middleware/TenantResolve.php` |
| C 端用户 API | `app/api/controller/User.php`，`app/common/model/UserModel.php`，`app/api/middleware/UserAuth.php` |
| 权限与数据范围 | `app/common/lib/Auth.php`，`app/admin/controller/Backend.php`（getDataScopeAdminIds/canManageAdminId） |
| 钩子 | `app/common/lib/Hook.php`；Addon 安装加载 `addons/{name}/bootstrap.php` |
| 上传 / 分片 / OSS 占位 | `app/common/lib/Upload.php`，`config/upload.php`，Common/upload、uploadChunk、mergeChunks |
| 系统配置 | `app/admin/controller/Config.php`，分组 tabs + config.js |
| CRUD 生成 | `command/Crud.php`，`template/controller/`、`template/model/` |
| API 文档 | GET `/api/doc` 或 `/api/doc/index`，`app/api/controller/Doc.php` |
| 菜单 | `fa_auth_rule`，`GET /admin/index/menu`；备用菜单在 `public/assets/js/backend-loader.js` |

### 2.4 待完善项（按需迭代）

- **效率工具**：JS/CSS 压缩命令、CDN 静态域名配置（如 Config.site.cdnurl）。
- **文件上传**：前端拖拽/粘贴、图片压缩、水印；OSS 实际对接 SDK。
- **插件**：后台在线安装/升级、插件配置页。
- **前端用户**：会员中心接口、等级/积分规则、第三方登录。
- **高级表格**：固定列、表头固定、跨页勾选、Excel 导入导出。
- **日志**：独立错误日志表/记录、接口日志（如中间件记录请求/响应）。
