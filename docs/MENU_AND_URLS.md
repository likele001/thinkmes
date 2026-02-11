# ThinkMes 后台菜单与页面 URL 一览

**后台根路径**：`/admin`（示例：`http://你的域名/admin`）

---

## 一、侧栏菜单（按 sort 排序）

| 序号 | 菜单名称   | 规则 name               | 页面 URL（相对 /admin） | 说明           |
|------|------------|-------------------------|--------------------------|----------------|
| 1    | 首页       | admin/index/index       | `/admin/index/index`     | 控制台统计     |
| 2    | 管理员管理 | admin/admin/index       | `/admin/admin/index`     | 列表/添加/编辑/删除/重置密码 |
| 3    | 租户管理   | admin/tenant/index      | `/admin/tenant/index`    | 仅平台超管可见；列表/添加/编辑/删除 |
| 4    | 角色管理   | admin/role/index       | `/admin/role/index`      | 列表/添加/编辑/删除 |
| 5    | 权限规则   | admin/auth_rule/index   | `/admin/auth_rule/index` | 树形列表/添加/编辑/删除 |
| 6    | 用户管理   | admin/member/index      | `/admin/member/index`    | **后台**查看/禁用/编辑 C 端用户列表；C 端注册/登录/找回在**前端** `/api/user/*` |
| 7    | 文件管理   | admin/attachment/index  | `/admin/attachment/index`| 上传附件列表/查看/删除 |
| 8    | 系统配置   | admin/config/index      | `/admin/config/index`    | 分组 tabs 表单保存 |
| 9    | 操作日志   | admin/log/index         | `/admin/log/index`       | 列表/导出 CSV  |

---

## 二、所有功能页面 URL（完整列表）

**基础路径**：`http://你的域名/admin`，以下为相对路径，拼在 `/admin` 后。

### 登录 / 首页
| 功能     | 方法 | URL |
|----------|------|-----|
| 登录页   | GET  | `/admin/index/login` |
| 登录提交 | POST | `/admin/index/login` |
| 登出     | GET/POST | `/admin/index/logout` |
| 验证码   | GET  | `/admin/index/captcha` |
| 无权限页 | GET  | `/admin/index/error` |
| 控制台   | GET  | `/admin/index/index` |
| 菜单数据 | GET  | `/admin/index/menu` |
| 清除缓存 | POST | `/admin/index/clearCache` |

### 管理员管理
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/admin/index` |
| 添加页   | GET  | `/admin/admin/add` |
| 添加提交 | POST | `/admin/admin/add` |
| 编辑页   | GET  | `/admin/admin/edit?id=1` |
| 编辑提交 | POST | `/admin/admin/edit` |
| 删除     | POST | `/admin/admin/del` |
| 重置密码 | POST | `/admin/admin/resetPwd` |

### 租户管理（仅平台超管）
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/tenant/index` |
| 添加页   | GET  | `/admin/tenant/add` |
| 添加提交 | POST | `/admin/tenant/add` |
| 编辑页   | GET  | `/admin/tenant/edit?id=1` |
| 编辑提交 | POST | `/admin/tenant/edit` |
| 删除     | POST | `/admin/tenant/del` |

### 角色管理
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/role/index` |
| 添加页   | GET  | `/admin/role/add` |
| 添加提交 | POST | `/admin/role/add` |
| 编辑页   | GET  | `/admin/role/edit?id=1` |
| 编辑提交 | POST | `/admin/role/edit` |
| 删除     | POST | `/admin/role/del` |

### 权限规则
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/auth_rule/index` |
| 添加页   | GET  | `/admin/auth_rule/add` |
| 添加提交 | POST | `/admin/auth_rule/add` |
| 编辑页   | GET  | `/admin/auth_rule/edit?id=1` |
| 编辑提交 | POST | `/admin/auth_rule/edit` |
| 删除     | POST | `/admin/auth_rule/del` |
| 树数据   | GET  | `/admin/auth_rule/tree` |

### 用户管理（C 端）
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/member/index` |
| 添加页   | GET  | `/admin/member/add` |
| 添加提交 | POST | `/admin/member/add` |
| 编辑页   | GET  | `/admin/member/edit?id=1` |
| 编辑提交 | POST | `/admin/member/edit` |
| 删除     | POST | `/admin/member/del` |
| 重置密码 | POST | `/admin/member/resetPwd` |

### 文件管理
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/attachment/index` |
| 删除     | POST | `/admin/attachment/del` |

### 系统配置
| 功能     | 方法 | URL |
|----------|------|-----|
| 配置页   | GET  | `/admin/config/index` |
| 分组列表 | GET  | `/admin/config/group` |
| 保存     | POST | `/admin/config/save` |

### 操作日志
| 功能     | 方法 | URL |
|----------|------|-----|
| 列表页   | GET  | `/admin/log/index` |
| 导出 CSV | GET  | `/admin/log/export` |

### 上传
| 功能     | 方法 | URL |
|----------|------|-----|
| 单文件上传 | POST | `/admin/common/upload` |
| 分片上传   | POST | `/admin/common/uploadChunk` |
| 分片合并   | POST | `/admin/common/mergeChunks` |

---

## 三、前端用户（C 端）与后台「用户管理」区分

- **C 端用户操作**（注册、登录、找回密码、个人资料）：在前端通过 **API** 完成，根路径 `/api`。
- **后台「用户管理」**：仅用于管理员查看/禁用/编辑 C 端用户列表（`/admin/member/index`），不供 C 端用户在此操作。

---

## 四、API（C 端 / 文档）

**API 根路径**：`http://你的域名/api`

| 功能       | 方法 | URL |
|------------|------|-----|
| API 首页   | GET  | `/api/index/index` |
| 用户注册   | POST | `/api/user/register` |
| 用户登录   | POST | `/api/user/login` |
| 个人资料   | GET/POST | `/api/user/profile`（需 Token） |
| 登出       | GET/POST | `/api/user/logout`（需 Token） |
| 找回密码-发验证码 | POST | `/api/user/forgot` |
| 找回密码-重置   | POST | `/api/user/resetPassword` |
| OpenAPI 文档 | GET  | `/api/doc` 或 `/api/doc/index` |

---

## 五、菜单数据来源

- 侧栏菜单由 **GET `/admin/index/menu`** 从表 **`fa_auth_rule`** 读取（`type=1` 且 `status=1`），按 `sort`、`id` 排序。
- 若菜单缺少「租户管理」「用户管理」「文件管理」，在数据库执行：
  ```sql
  INSERT INTO fa_auth_rule (id, name, title, type, status, pid, icon, sort, create_time, update_time) VALUES
  (7, 'admin/tenant/index', '租户管理', 1, 1, 0, 'fas fa-building', 15, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (8, 'admin/member/index', '用户管理', 1, 1, 0, 'fas fa-user', 35, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
  (9, 'admin/attachment/index', '文件管理', 1, 1, 0, 'fas fa-file-alt', 38, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
  ON DUPLICATE KEY UPDATE title=VALUES(title), icon=VALUES(icon), sort=VALUES(sort);
  ```
- 或执行完整菜单初始化：**`database/seed_auth_rule.sql`**。
