# 餐饮 SaaS（ThinkPHP）作为 KeleAdmin 独立应用（restaurant）开发计划

## Summary
- 目标：把餐饮 SaaS 做成 **KeleAdmin 应用中心可安装的独立业务应用**（`restaurant`），包含后台模块、餐饮 API、数据表迁移与菜单/权限种子，并预留 OpenClaw AI 智能体扩展能力。
- 交付形态：
  - 运行时代码：安装后合并到项目的 `app/`、`public/`、`database/` 目录
  - 安装包目录：`packages/restaurant/`（根目录含 `app.json`，可直接打 zip 上传应用中心）

## Current State Analysis（基于仓库现状）
- 底座能力可直接复用：
  - 多租户：API 入口已挂 `TenantResolve` + `TenantResourceCheck` 全局中间件；后台通过 session 获取 tenant_id。
  - RBAC：后台鉴权按 `fa_auth_rule.name` 节点校验；菜单同源于权限规则。
  - 应用中心：按 `app.json` 执行 SQL 并合并 `app/public/database`，适合交付独立 business app。
  - AI：已有通用 AI 服务 + 可用性检查 + 用量计费中间件，restaurant 可复用其计费/审计链路。
- 需补齐的可扩展点：
  - 后台路由已支持按需 include 多个业务路由文件；restaurant 只需新增 `restaurant.php` 并被 include。
  - API 路由目前集中在单文件，需要在本次引入“可扩展 include 段”，避免未来每个应用覆盖同一文件。

## Scope & Milestones（按可交付版本拆分）
### V1（最小可用：堂食）
- 门店与桌台：门店、区域、桌台、桌码生成；桌台状态流转（空闲/占用/清台中/预定）。
- 菜品：分类、菜品、规格/口味、套餐；上下架与售罄。
- 订单：扫码点餐、加菜、退菜申请（审核）、订单状态流转（待确认→制作中→待上菜→已完成→已结账）。
- 后厨：KDS（订单列表、叫号、超时提醒）；厨打任务先落库（真实打印对接可在 V1.1）。
- 报表：营业概览、菜品销量排行（按天聚合）。

### V1.1（收银 & 支付）
- 收银台：改价/折扣/抹零、交接班对账。
- 支付：优先对接已存在的 payment 能力（如未安装则提供最小支付网关适配层 + notify 回调）。

### V2（外卖/会员/库存/完整报表）
- 外卖：美团/饿了么聚合接入与状态同步。
- 会员营销：储值、积分、券、活动。
- 库存成本：原料、出入库、BOM 扣减、毛利核算。
- 报表：时段/会员/外卖等专题分析。

### V3（OpenClaw AI 智能增强）
- AI 经营日报、评价自动处理、营销触达、库存预测预警、智能客服、营销文案、竞品监控。

## Implementation Details（V1 优先落地的“模块/表/接口”映射）
### 后台模块（app/admin/controller/restaurant）
- 门店与桌台：`Store`、`Area`、`Table`
- 菜品：`Category`、`Item`、`Combo`
- 订单：`Order`（列表/详情/改价/折扣/状态流转/退菜审核）
- 后厨：`Kds`（出餐/叫号/超时提醒）、`PrintJob`（厨打任务列表）
- 报表：`Report`（营业概览/菜品排行）
- AI（先预留入口）：`Ai`（配置检测、经营日报生成、任务列表）

### API（app/api/controller/restaurant）
- 扫码点餐（顾客侧，默认不强制登录，靠桌码 token）：`Table`、`Menu`、`Cart`、`Order`
- 后厨端（店员/后厨登录方式待 V1 内统一）：`Kds`
- 店长端（概览/报表）：`Report`
- AI（预留）：`Ai`

### V1 核心表（database/migrate_add_restaurant_tables.sql）
- `restaurant_store`、`restaurant_area`、`restaurant_table`
- `restaurant_category`、`restaurant_item`、`restaurant_item_spec`、`restaurant_item_sku`
- `restaurant_combo`、`restaurant_combo_item`
- `restaurant_order`、`restaurant_order_item`、`restaurant_order_action_log`
- `restaurant_kitchen_ticket`、`restaurant_print_job`
- `restaurant_stat_day`、`restaurant_stat_item_day`

### 权限节点与路由前缀（seed_restaurant_menu.sql）
- 后台：`restaurant/*`（与控制器一致，按模块分组写入 auth_rule）
- API：以 `/api/restaurant/*` 为统一前缀（由 `app/api/route/restaurant.php` 定义）

## Proposed Changes（实施时会新增/修改哪些文件）
### 1) 应用包目录（可直接打包上传）
- 新增：`packages/restaurant/app.json`
- 新增：`packages/restaurant/database/migrate_add_restaurant_tables.sql`
- 新增：`packages/restaurant/database/seed_restaurant_menu.sql`
- 新增：`packages/restaurant/README.md`

### 2) 安装后合并到项目的运行时代码
- 新增：`app/admin/controller/restaurant/**`
- 新增：`app/admin/model/restaurant/**`
- 新增：`app/admin/view/restaurant/**`
- 新增：`public/assets/js/backend/restaurant/**`
- 新增：`app/admin/route/restaurant.php`
- 修改：`app/admin/route/app.php`（增加 `restaurant.php` 的按需 include）
- 新增：`app/api/controller/restaurant/**`
- 新增：`app/api/route/restaurant.php`
- 修改：`app/api/route/app.php`（增加 include 扩展段，引入 `restaurant.php`）

### 3) 可选：打包辅助
- 新增：`tools/pack_restaurant.php`（从当前仓库收集 restaurant 文件，生成 restaurant.zip）

## Assumptions & Decisions（为保证方案可落地而做的假设/选择）
- 多租户隔离：默认“同库同表 + tenant_id 字段隔离”（与现有中间件与查询方式一致）。
- 组织方式：restaurant 以应用包安装，不直接修改底座业务逻辑；仅对两个路由入口文件做“include 扩展段”的最小改动。
- 权限命名：以 `restaurant/...` 为节点前缀，与路由/控制器一致，便于 RBAC 与菜单加载。
- OpenClaw：先铺“适配层 + 任务表 + 定时命令”的基础设施；业务 AI 能力按模块逐步启用。

## Verification（完成后如何自检）
- 安装验证：应用中心上传 restaurant.zip 后，菜单出现、权限节点生效、数据库表创建成功、重复安装幂等。
- 多租户验证：同一套表结构下，不同 tenant_id 的门店/订单互相不可见（后台与 API 各验证一次）。
- 核心链路验证（V1）：扫码进入→下单→KDS 出单→完结/结账（支付可先走现金记账）。
- AI 链路验证（铺底座）：未配置 AI 时 AICheck 拦截；配置后可产生 ai_log 与 ai_usage 记录。
