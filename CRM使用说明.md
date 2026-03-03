# CRM 使用说明（/www/wwwroot/thinkmes/）

## 一、安装 CRM

CRM 需在后台「应用中心」中安装后，左侧菜单才会显示。

### 步骤

1. **登录后台**
   - 访问：`http://你的域名/admin`（或实际后台入口）
   - 使用超级管理员登录（默认账号 `admin`，密码 `123456`）

2. **进入应用中心**
   - 在左侧菜单找到 **「应用中心」**（图标：方块网格）
   - 若未看到，可能是权限或菜单配置问题，见下方「常见问题」

3. **安装 CRM**
   - 在应用列表中找到 **「CRM 客户关系管理」**
   - 点击 **「安装」** 按钮
   - 等待执行完成（会创建数据库表并添加菜单）

4. **刷新页面**
   - 安装成功后，左侧会出现 **「CRM客户关系」** 菜单
   - 展开后可看到：客户管理、联系人、商机管理、合同管理、跟进记录、回款管理、产品管理、销售订单、CRM报表

---

## 二、直接访问（未安装时）

若应用中心不可用，可先手动执行 SQL 完成安装：

```bash
cd /www/wwwroot/thinkmes
# 按顺序执行（表前缀 fa_ 若不同请替换）
mysql -u用户名 -p 数据库名 < database/migrate_add_crm_tables.sql
mysql -u用户名 -p 数据库名 < database/migrate_add_crm_sales_order.sql
mysql -u用户名 -p 数据库名 < database/seed_crm_menu.sql
```

然后执行以下 SQL 启用 CRM 菜单：

```sql
UPDATE fa_auth_rule SET status = 1 WHERE name = 'crm' OR name LIKE 'crm/%';
```

---

## 三、常见问题

### 1. 左侧没有「应用中心」菜单

- 确认使用 **超级管理员**（tenant_id=0）登录
- 检查 `fa_auth_rule` 表中是否存在 `admin/app_center/index`，且 `status=1`
- 若不存在，可执行：
  ```sql
  INSERT INTO fa_auth_rule (name, title, type, ismenu, status, pid, icon, sort, create_time, update_time)
  VALUES ('admin/app_center/index', '应用中心', 1, 1, 1, 0, 'fas fa-th-large', 65, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
  ```

### 2. 应用中心有 CRM 但点击安装失败

- 检查 `database/` 目录下是否存在：
  - `migrate_add_crm_tables.sql`
  - `migrate_add_crm_sales_order.sql`
  - `seed_crm_menu.sql`
- 检查数据库连接和表前缀配置（`.env` 或 `config/database.php`）

### 3. 安装后仍看不到 CRM 菜单

- 清除浏览器缓存并刷新
- 确认当前登录角色有 CRM 相关权限（超级管理员为 `*` 表示全部）

---

## 四、CRM 功能入口

安装完成后，访问路径示例（以 `/admin` 为后台前缀）：

| 功能     | 路径                          |
|----------|-------------------------------|
| 客户管理 | /admin/crm/customer/index     |
| 联系人   | /admin/crm/contact/index      |
| 商机管理 | /admin/crm/opportunity/index  |
| 合同管理 | /admin/crm/contract/index     |
| 跟进记录 | /admin/crm/follow/index       |
| 回款管理 | /admin/crm/payment/index      |
| 产品管理 | /admin/crm/product/index      |
| 销售订单 | /admin/crm/sales_order/index  |
| CRM 报表 | /admin/crm/report/index       |
