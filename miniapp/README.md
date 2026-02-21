# MES 工作台 - 微信小程序（员工端 + 管理端整合）

参考 `/www/wwwroot/report/adminweixin` 的整合方式：**一个小程序**内先选身份（管理员 / 员工），再进入对应端。

## 目录结构

- `app.js`：统一入口，`request()` 为管理端 API，`userRequest()` 为员工端 API；`adminLogin` / `userLogin` 双登录。
- `app.json`：首页为登录页，TabBar 为员工端（首页、报工记录、我的工资、我的）；管理端为普通页面（无 TabBar）。
- `utils/config.js`：`BASE_URL`（接口基础地址）、`TENANT_ID`（租户 ID，员工端登录用）。
- `utils/api.js`：`userApi`（员工端）、`adminApi`（管理端）封装。
- `pages/login`：身份选择（管理员 / 员工）→ 管理员输账号密码，员工微信一键登录。
- `pages/user-index`、`user-task`、`user-reports`、`user-wages`、`user-profile`：员工端 TabBar 页面。
- `pages/index`：管理端首页（入口菜单）。
- `pages/orders`、`order-detail`、`allocations`、`reports`、`active-reports`、`audit`：管理端订单、分工、报工审核等。

## 租户小程序配置（重要）

系统后台有**租户小程序配置**（表 `fa_tenant_miniapp`）：每个租户可配置自己的小程序 **AppID、AppSecret** 等。  
小程序**启动时**会请求接口 `GET /api/miniapp/getConfig?appid=当前小程序AppID`，根据 AppID 查出该小程序所属租户，得到 **tenant_id**，员工端登录时用此 tenant_id 调用 `miniapp/login`。

因此需要：
1. 在**后台管理**中，进入对应租户下的「租户小程序配置」，填写**本小程序的 AppID、AppSecret** 并保存。
2. 这样无需在代码里写死 `TENANT_ID`；若未配置或接口未返回，才使用 `utils/config.js` 里的 `TENANT_ID` 兜底（可为 0）。

## 配置

1. **小程序后台**：在「开发 → 开发管理 → 开发设置」中配置 **request 合法域名** 为你的接口域名（如 `https://your-domain.com`）。
2. **本地配置**：修改 `utils/config.js` 中的 `BASE_URL` 为你的接口基础地址（如 `https://your-domain.com/api`）。`TENANT_ID` 仅作兜底，一般依赖后台租户小程序配置。
3. **项目 AppID**：在 `project.config.json` 中填写你的小程序 AppID；并在后台「租户小程序配置」中为该租户填写同一 AppID 与 AppSecret。

## 后端接口说明

- **管理端**：`/api/scanwork/*`（如 `scanwork/adminLogin`、`scanwork/getOrders`），需管理员 Token（登录后存 `adminToken`）。
- **员工端**：`/api/miniapp/login`（登录）、`/api/worker/*`（工作台、任务、报工、工资、上传图片），需员工 Token（登录后存 `user_token`）。

## 使用流程

1. 打开小程序 → 登录页选择「管理员登录」或「员工登录」。
2. **管理员**：输入账号密码 → 进入管理端首页 → 订单管理、分工管理、报工审核等；可点「切换员工端」或退出后重新选员工登录。
3. **员工**：微信一键登录 → 进入员工端首页（TabBar）→ 查看任务、进入报工页提交报工、查看报工记录与工资；在「我的」可进入管理端（若本机已登录过管理员）。

与参考项目 `adminweixin` 一致：同一应用内通过登录身份区分管理端与员工端，TabBar 仅用于员工端，管理端为独立页面栈。
