# 小程序与 PC 端租户/权限对比

## 结论概览

| 维度         | PC 端                     | 小程序端                         | 是否一致     |
|--------------|----------------------------|----------------------------------|--------------|
| 租户隔离     | 按管理员/员工所属租户      | 按 Token 中的 tenant_id          | 一致         |
| 员工端数据   | tenant_id + user_id 双重过滤 | tenant_id + user_id 双重过滤   | 一致         |
| 管理端数据   | tenant_id 过滤 + 角色/节点权限 | 仅 tenant_id 过滤，无角色校验 | 租户一致，权限有差异 |

---

## 一、租户来源（一致）

### PC 端

- **管理端**：登录后 Session 存 `admin_info`（含 `tenant_id`），中间件 `TenantResolve` 每次请求从 Session 取 `admin['tenant_id']` 写入 `request->tenantId`。后台控制器 `getTenantId()` 即用该值，所有 MES 查询带 `where('tenant_id', $tenantId)`。
- **员工端（index/Worker）**：从 Cookie 中的 user_token 解析出 `user_id`、`tenant_id`，查询均带 `tenant_id` + `user_id`。

### 小程序端

- **管理端（scanwork）**：`scanwork/adminLogin` 用管理员账号密码登录，从 `admin->tenant_id` 生成 Token，Token  payload 含 `admin_id`、`tenant_id`。`AdminAuth` 中间件校验 Token 后把 `tenantId` 写入 `request`。`Scanwork` 所有接口用 `getTenantId()`，查询均带 `where('tenant_id', $tenantId)`。  
  → 租户来源与 PC 一致：**当前登录管理员的 tenant_id**。

- **员工端（worker）**：`miniapp/login` 或 `miniapp/bindWithEmployee` 登录后生成 Token，payload 含 `user_id`、`tenant_id`（来自用户表/绑定关系）。`UserAuth` 中间件校验后写入 `request->userId`、`request->tenantId`。`Worker` 控制器所有接口用 `getTenantId()` + `getUserId()`，查询均带 `tenant_id` + `user_id`。  
  → 与 PC 员工端一致：**同一租户下仅能操作本人数据**。

因此，**按租户能看到哪些数据**：PC 与小程序一致，都是“本租户 + 本人（员工端）或本管理员所属租户（管理端）”。

---

## 二、管理端「角色/节点权限」差异

- **PC 管理端**：除租户外，还有：
  - `CheckAuth`：校验节点权限（如 `mes/order/index`），未授权则无菜单、无操作入口。
  - `Auth::check()` / 数据权限：如 `getDataScopeAdminIds()`，限制可管理的主管员等。
  - 即：**同一租户下，不同角色管理员在 PC 上可见、可操作的功能不同**。

- **小程序管理端（scanwork）**：  
  - 仅做 **AdminAuth**（校验是管理员 + 取 token 里的 `tenant_id`）。  
  - **没有**角色、节点、数据权限等校验；只要管理员登录成功，所有 scanwork 接口（订单、报工、审核、产品、计划等）都可调用。  
  - 即：**同一租户下，在小程序里相当于“全权限”**，与 PC 上该管理员角色是否受限无关。

所以：**租户隔离一致；管理端“谁能在 PC 上点哪些菜单/接口”的权限，小程序目前未与 PC 对齐**。

---

## 三、数据与接口层面的对齐情况

- **员工端**  
  - 数据范围：仅本租户、本用户（报工、任务、工资、详情等）。  
  - 与 PC 员工端（index/Worker）逻辑一致。

- **管理端**  
  - 数据范围：仅本租户（与 PC 一致）。  
  - 功能权限：不区分角色，所有已登录管理员在小程序里都能用全部 scanwork 接口。

**已实现与 PC 角色/菜单权限一致**（见下节）。

---

## 四、小程序管理端与 PC 权限一致（已实现）

1. **节点校验**  
   - 配置文件 `config/scanwork_permission.php` 将每个 scanwork 接口 action 映射到 PC 权限节点（如 `getOrders` → `mes/order`）。  
   - 中间件 `app\api\middleware\ScanworkPermission` 在 `AdminAuth` 之后执行，按当前 `adminId` 调用 `Auth::check($node, $adminId)`，与 PC 端 `CheckAuth` 使用同一套 `Auth` 与 `fa_auth_rule`。  
   - 超管（config `auth.super_admin_id`）跳过校验；未配置或无权限返回 403。

2. **小程序菜单显隐**  
   - 接口 `GET scanwork/getScanworkMenu` 返回当前管理员的权限节点列表 `nodes`（与 PC 菜单/角色一致）。  
   - 小程序可根据 `nodes` 显隐 Tab/页面（例如有 `mes/order` 或 `mes/order/index` 再显示「订单」）。

3. **建议**  
   - 仅关心租户隔离：现有逻辑已与 PC 一致。  
   - 已需要与 PC 角色/菜单一致：后端接口已做节点校验；前端建议调用 `getScanworkMenu` 后按 `nodes` 控制菜单显隐。
