# CRM 应用开发方案

## 一、定位与目标

### 1.1 产品定位
- **CRM（客户关系管理）**：面向销售与客户全生命周期的应用，支持客户档案、商机、合同、跟进、回款等。
- **双模式**：
  - **独立运行**：在 ThinkMes 基础版 / OA 中单独安装，仅使用 CRM 功能，不依赖 MES。
  - **与 MES 整合**：与已安装的 MES 联动，客户→订单→生产一条线（CRM 客户/商机/合同 可转 MES 订单、客户主数据互通）。

### 1.2 技术约束
- 与现有架构一致：多租户、后台入口（路径式）、应用中心安装/卸载。
- 代码组织：`app/admin/controller/crm/`、`app/admin/model/crm/`、`app/admin/view/crm/`，路由挂载在 `admin` 下如 `/admin/crm/xxx`。
- 权限：使用 `fa_auth_rule` 的 `name` 如 `crm`、`crm/customer` 等，安装时写入菜单与权限规则。

---

## 二、功能模块设计

### 2.1 核心模块（独立运行必备）

| 模块 | 功能要点 | 说明 |
|------|----------|------|
| **客户管理** | 客户档案、联系人、客户分类、客户级别、来源、状态 | 支持列表/筛选/导入导出 |
| **商机管理** | 商机阶段、金额、预期成单日、负责人、关联客户/联系人 | 阶段：线索→需求确认→报价→谈判→赢单/输单 |
| **合同管理** | 合同编号、客户、金额、签订日期、履行状态、收付款计划 | 与商机可关联 |
| **销售跟进** | 跟进记录（拜访/电话/邮件）、下次跟进提醒、关联客户/商机 | 时间线展示 |
| **回款管理** | 回款记录、关联合同、金额、回款日期、发票 | 合同维度汇总已收/待收 |

### 2.2 扩展模块（可选或与 MES 联动时增强）

| 模块 | 功能要点 | 与 MES 联动 |
|------|----------|-------------|
| **产品/报价** | CRM 侧产品库、报价单（品名、数量、单价、金额） | 与 MES 产品/型号同步或引用，下单时带出 BOM/工序 |
| **销售订单** | 销售订单（客户、产品、数量、交期） | **转 MES 生产订单**，状态回写 |
| **客户门户** | 客户登录、查看订单/合同/对账 | 与现有 MES 客户门户统一或扩展 |
| **统计报表** | 销售漏斗、业绩统计、回款统计、客户分布 | 可含 MES 订单交付率、退货率等 |

### 2.3 与 MES 的整合点（整合时才有）

| 整合点 | 说明 |
|--------|------|
| **客户主数据** | MES 的 `mes_customer` 与 CRM 客户可映射（同表或关联表），避免重复录入。 |
| **CRM 销售订单 → MES 订单** | 在 CRM 创建/确认销售订单后，一键「转生产订单」写入 `mes_order`，并回写 CRM 订单状态。 |
| **产品/型号** | CRM 报价/订单中的产品可引用 MES 的 `mes_product`、`mes_product_model`，保证与生产一致。 |
| **订单状态回写** | MES 订单状态（生产中/已完工/发货）回写到 CRM 销售订单或合同履行状态。 |
| **客户门户** | 若已有 MES 客户门户，CRM 的合同、对账可与门户菜单整合。 |

---

## 三、数据模型与表设计（思路）

### 3.1 表前缀与租户
- 所有表带统一前缀（如 `fa_`），与现有一致；租户隔离用 `tenant_id`。
- 安装时通过 SQL 迁移创建表，卸载时仅隐藏菜单，表可保留（与 MES 一致策略）。

### 3.2 核心表（建议）

- **crm_customer**：客户（tenant_id, name, short_name, level, source, status, ...）
- **crm_contact**：联系人（tenant_id, customer_id, name, role, phone, email, ...）
- **crm_opportunity**：商机（tenant_id, customer_id, contact_id, stage, amount, owner_id, expected_date, ...）
- **crm_contract**：合同（tenant_id, customer_id, opportunity_id, contract_no, amount, sign_date, status, ...）
- **crm_follow**：跟进记录（tenant_id, customer_id, opportunity_id, type, content, next_follow_time, ...）
- **crm_payment**：回款（tenant_id, contract_id, amount, pay_date, invoice_no, ...）
- **crm_product**（独立时）：CRM 产品（tenant_id, name, code, unit, price, ...）；与 MES 整合时可改为关联 mes_product 或同步表。
- **crm_sales_order**：销售订单（tenant_id, customer_id, order_no, total_amount, status, mes_order_id 可选）；与 MES 整合时通过 `mes_order_id` 关联。
- **crm_sales_order_item**：销售订单明细（sales_order_id, product_id, qty, price, amount, mes_order_model_id 可选）

菜单与权限表沿用 `fa_auth_rule`，通过 `name` 如 `crm`、`crm/customer` 等挂载；安装时执行 `seed_crm_menu.sql` 插入/更新规则与菜单。

---

## 四、技术实现要点

### 4.1 应用中心注册（与 MES 平级）
- 在 `AppCenter` 的 `$apps` 中增加 `crm`：
  - `sql_files`：如 `migrate_add_crm_tables.sql`、`seed_crm_menu.sql`
  - `check_table`：如 `crm_customer`
  - `code_path`：`app/admin/controller/crm`
- 安装：执行 SQL，写入 `app_crm_installed` 配置，启用 `crm`、`crm/*` 的 auth_rule 状态。
- 卸载：仅隐藏菜单（auth_rule status=0），表保留。

### 4.2 路由与权限
- 后台路由：在 `app/admin/route/app.php` 中增加 `Route::group('crm', ...)`，下挂 `customer`、`opportunity`、`contract`、`follow`、`payment`、`sales_order` 等，控制器命名空间 `crm.XXX`。
- 权限：所有 CRM 控制器继承 Backend，依赖现有 CheckAuth；规则名 `crm`、`crm/customer` 等与菜单一致。
- **独立运行**：不引用 MES 控制器与 MES 表；若有「转 MES 订单」按钮，通过检测 `app_mes_installed` 或是否存在 `mes_order` 表决定是否显示。

### 4.3 与 MES 的耦合方式（推荐）
- **弱耦合**：CRM 不直接 require MES 类，仅通过配置或数据库判断 MES 是否安装；需要时用 Db 读/写 MES 表或调用统一 Service。
- **客户主数据**：方案 A）CRM 与 MES 共用同一张客户表（如 mes_customer 扩展字段）；方案 B）CRM 自建 crm_customer，通过「同步到 MES」或关联字段（如 crm_customer.mes_customer_id）与 mes_customer 对应。建议先 B，便于独立运行。
- **销售订单转 MES**：提供「转生产订单」接口，在 CRM 中创建一条 MES 订单（写入 mes_order + mes_order_model 等），并回写 crm_sales_order.mes_order_id、状态。

### 4.4 前端与资源
- 视图：`app/admin/view/crm/`，风格与现有后台一致（Bootstrap Table、表单、addtabs）。
- JS：`public/assets/js/backend/crm.js`（可选总入口）、`public/assets/js/backend/crm/customer.js` 等，按需加载。
- 菜单：在 `seed_crm_menu.sql` 中插入一级菜单「CRM」及二级菜单（客户、商机、合同、跟进、回款、销售订单等），与 MES 同级。

### 4.5 API 与移动端（可选）
- 若需移动端/小程序：在 `app/api/controller/` 下增加 Crm 相关接口（如客户列表、跟进提交），路由挂载在 `api`，用现有 UserAuth/租户隔离；与 MES 的 Worker/Scanwork 等并列，通过「应用中心」或配置控制是否加载。

---

## 五、打包与发布

### 5.1 独立打包（类似 MES 应用包）
- 提供 `build/pack_crm.php`，生成 `thinkmes-crm-1.0.zip`，内含：
  - `app/admin/controller/crm/`、`app/admin/model/crm/`、`app/admin/view/crm/`
  - `app/admin/route/app.php`（或仅 CRM 路由片段，由说明文档指导合并）
  - `database/migrate_add_crm_tables.sql`、`seed_crm_menu.sql`
  - `public/assets/js/backend/crm.js` 及 `crm/` 下 JS
  - 安装说明（解压到项目根目录 → 应用中心安装 CRM）
- 基础版/OA 解压合并后，在应用中心看到「CRM」应用，点击安装即可使用；未装 MES 时仅 CRM 功能，不显示「转 MES 订单」等。

### 5.2 与 MES 共存
- 同一项目中先装 MES、再装 CRM（或反之），应用中心内两个应用独立安装状态；CRM 安装后检测到 MES 已安装则显示联动功能（销售订单转生产、客户关联 MES 客户等）。

---

## 六、开发阶段建议

| 阶段 | 内容 |
|------|------|
| **Phase 1** | 表结构设计（migrate_add_crm_tables.sql）、应用中心注册、菜单与权限（seed_crm_menu.sql）、客户管理（CRUD + 联系人）、基础路由与控制器。 |
| **Phase 2** | 商机、合同、跟进、回款 四个模块的 CRUD 与列表筛选；仪表盘或统计（可选）。 |
| **Phase 3** | 销售订单（含明细）、产品/报价（独立表或只做简单产品名+金额）；若与 MES 整合：客户关联、销售订单转 MES 订单接口。 |
| **Phase 4** | 报表（漏斗、业绩、回款）、客户门户扩展（可选）、移动端/API（可选）、打包脚本与文档。 |

---

## 七、总结

- **独立运行**：CRM 仅依赖基础版/OA 的租户、权限、后台框架，不依赖任何 MES 表或菜单。
- **与 MES 整合**：通过「是否安装 MES」动态显示联动功能；数据上客户/销售订单与 MES 客户/订单关联，避免重复录入、实现从销售到生产的闭环。
- 功能上覆盖客户、商机、合同、跟进、回款、销售订单及可选的产品与报表；技术上与现有 MES 应用中心、多租户、路由、权限体系一致，便于维护和二次扩展。
