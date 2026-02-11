# ThinkMes SaaS系统安装指南

## 📋 前置要求

- PHP >= 8.1
- MySQL >= 5.7 或 MariaDB >= 10.3
- Apache/Nginx Web服务器
- Composer（用于安装依赖）

## 🚀 安装步骤

### 1. 克隆/下载项目

```bash
cd /www/wwwroot
# 假设项目已经在 thinkmes 目录
cd thinkmes
```

### 2. 安装依赖

```bash
composer install
```

### 3. 配置数据库

编辑 `config/database.php`，配置数据库连接信息：

```php
'hostname' => '127.0.0.1',
'database' => 'thinkmes',
'username' => 'root',
'password' => 'your_password',
'prefix'   => 'fa_',
```

### 4. 初始化数据库

按顺序执行以下SQL文件：

```bash
# 1. 基础表结构
mysql -uroot -p thinkmes < database/init.sql

# 2. 租户和用户表
mysql -uroot -p thinkmes < database/init_tenant_user.sql

# 3. 套餐功能表
mysql -uroot -p thinkmes < database/migrate_add_tenant_package_feature.sql

# 4. 订单表
mysql -uroot -p thinkmes < database/migrate_add_tenant_order.sql

# 5. 初始化权限规则
mysql -uroot -p thinkmes < database/seed_auth_rule.sql

# 6. 初始化套餐数据（可选）
mysql -uroot -p thinkmes < database/seed_tenant_package.sql
```

### 5. 配置Web服务器

#### Apache配置示例

```apache
<VirtualHost *:80>
    ServerName mes.yourdomain.com
    DocumentRoot /www/wwwroot/thinkmes/public
    
    <Directory /www/wwwroot/thinkmes/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx配置示例

```nginx
server {
    listen 80;
    server_name mes.yourdomain.com;
    root /www/wwwroot/thinkmes/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 6. 设置目录权限

```bash
chmod -R 755 /www/wwwroot/thinkmes
chmod -R 777 /www/wwwroot/thinkmes/runtime
chmod -R 777 /www/wwwroot/thinkmes/public/uploads
```

### 7. 访问系统

- **后台管理**：`http://yourdomain.com/admin/index/login`
  - 默认账号：`admin`
  - 默认密码：`123456`

- **前端用户**：`http://yourdomain.com/index/user/login`

## 📝 初始化后的操作

### 1. 配置套餐

1. 登录后台管理系统
2. 进入"套餐管理"
3. 编辑或添加套餐，配置资源限制：
   - 最大管理员数
   - 最大用户数
   - 默认有效期

### 2. 配置套餐功能

1. 在套餐列表中，点击"功能"按钮
2. 为套餐分配功能权限
3. 可用的功能包括：
   - 订单管理
   - 产品管理
   - 库存管理
   - 报表统计
   - 数据导出
   - API接口访问
   - 自定义字段
   - 工作流
   - 消息通知
   - 数据备份

### 3. 创建租户

1. 进入"租户管理"
2. 点击"添加租户"
3. 填写租户信息：
   - 租户名称
   - 绑定域名（可选）
   - 选择套餐
   - 设置到期时间（可选）
4. 提交后，系统会自动创建管理员账号：
   - 账号：`admin`
   - 密码：`123456`

### 4. 租户登录

租户管理员可以通过以下方式登录：

1. **使用绑定域名**：如果配置了域名，直接访问域名即可
2. **使用请求头**：设置 `X-Tenant-Id` 请求头
3. **使用平台域名**：访问平台域名，系统会根据域名自动识别租户

登录后，租户管理员可以：
- 管理自己的管理员账号
- 管理C端用户
- 使用已购买的功能模块

### 5. 创建订单

1. 进入"租户订单管理"
2. 点击"创建订单"
3. 选择租户和套餐
4. 选择订单类型：
   - **购买**：新租户购买套餐
   - **续费**：延长租户到期时间
   - **升级**：更换套餐
5. 填写金额和有效期（续费时）
6. 提交订单
7. 确认支付后，系统自动处理续费或升级

## 🔧 系统配置

### 1. 系统配置

进入"系统配置"，可以配置：
- 站点名称
- 登录验证码
- 文件上传设置
- 其他系统参数

### 2. 权限配置

进入"权限规则"，可以：
- 管理菜单权限
- 配置按钮权限
- 配置接口权限

### 3. 角色管理

进入"角色管理"，可以：
- 创建角色
- 为角色分配权限
- 将角色分配给管理员

## 📊 功能说明

### 资源限制

系统会自动检查租户的资源使用情况：
- **管理员数限制**：创建管理员时检查
- **用户数限制**：用户注册时检查
- 超出限制时会提示升级套餐

### 功能权限

- 如果套餐配置了功能列表，租户只能使用配置的功能
- 如果套餐没有配置功能列表，租户可以使用所有功能
- 平台超管（tenant_id=0）拥有所有功能

### 数据隔离

- 所有数据通过 `tenant_id` 字段隔离
- 租户只能看到自己的数据
- 平台超管可以看到所有数据

## 🐛 常见问题

### 1. 菜单不显示

- 检查权限规则是否正确配置
- 检查管理员是否有对应权限
- 清除缓存：访问"清除缓存"功能

### 2. 租户无法登录

- 检查租户状态是否为"正常"
- 检查租户是否过期
- 检查域名配置是否正确

### 3. 资源限制不生效

- 检查中间件是否正确加载
- 检查套餐配置是否正确
- 清除缓存后重试

### 4. 功能权限不生效

- 检查套餐是否配置了功能列表
- 检查功能代码是否正确
- 检查中间件是否正确加载

## 📞 技术支持

如有问题，请查看：
- `SAAS_DEVELOPMENT_STATUS.md` - 开发状态文档
- `SAAS_COMPLETE_SUMMARY.md` - 功能完成总结

## 🎉 系统已就绪！

完成以上步骤后，SaaS系统就可以正常使用了。祝您使用愉快！
