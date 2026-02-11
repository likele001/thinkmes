# ThinkMes - 完整的SaaS多租户管理系统

基于 ThinkPHP 8.1 + FastAdmin 风格的完整SaaS多租户管理系统。

## ✨ 核心特性

### 🏢 多租户架构
- ✅ 完整的数据隔离（tenant_id字段）
- ✅ 域名绑定和自动识别
- ✅ 租户状态和到期时间管理
- ✅ 租户初始化（自动创建管理员）

### 📦 套餐管理系统
- ✅ 套餐CRUD管理
- ✅ 资源限制配置（管理员数、用户数）
- ✅ 功能权限配置
- ✅ 套餐排序和有效期设置

### 🔒 权限和资源控制
- ✅ RBAC权限管理（角色、规则）
- ✅ 资源限制检查（自动检查管理员数、用户数）
- ✅ 功能权限管理（按套餐限制功能访问）
- ✅ 菜单动态过滤（根据权限自动隐藏）

### 💰 订单和续费系统
- ✅ 订单管理（购买、续费、升级）
- ✅ 订单支付和取消
- ✅ 自动续费处理
- ✅ 自动升级处理

### 📊 数据统计
- ✅ 租户统计（总数、正常、禁用、即将到期）
- ✅ 订单统计（总数、已支付、待支付、总金额）
- ✅ 用户统计（注册、登录、活跃度）
- ✅ 控制台可视化展示

### 👥 用户管理
- ✅ C端用户注册、登录
- ✅ 用户资料管理
- ✅ 头像上传
- ✅ 密码修改

## 📁 项目结构

```
thinkmes/
├── app/
│   ├── admin/              # 后台管理模块
│   │   ├── controller/     # 控制器
│   │   ├── model/          # 模型
│   │   └── view/           # 视图
│   ├── api/                # API接口模块
│   │   └── controller/     # API控制器
│   ├── index/              # 前端模块
│   │   └── controller/     # 前端控制器
│   └── common/             # 公共模块
│       ├── controller/     # 基类控制器
│       ├── lib/            # 公共库（Auth等）
│       └── middleware/     # 中间件
├── database/               # 数据库文件
│   ├── init.sql            # 基础表结构
│   ├── init_tenant_user.sql  # 租户和用户表
│   ├── migrate_*.sql       # 迁移文件
│   └── seed_*.sql          # 种子数据
├── public/                 # 公共资源
│   └── assets/            # 静态资源
│       ├── css/           # 样式文件
│       └── js/            # JavaScript文件
└── config/                 # 配置文件
```

## 🚀 快速开始

### 1. 安装依赖

```bash
composer install
```

### 2. 配置数据库

可以通过两种方式配置数据库：

- 直接编辑 `config/database.php`（适合习惯将配置托管在配置文件的场景）。
- 使用环境变量（推荐用于容器化/CI/CD）：仓库已添加 `config/.env.example`，你可以参考并在部署环境中设置对应的 `DB_*` 变量，或者将示例复制到服务器并填入实际值。

示例（在服务器上）：

```bash
# 将示例复制并按需修改（注意：ThinkPHP 环境变量的加载方式依赖于项目设置）
cp config/.env.example .env
# 或者在 shell 中导出变量
export DB_HOST=127.0.0.1
export DB_NAME=thinkmes
export DB_USER=root
export DB_PASS=yourpassword
```

### 3. 初始化数据库

```bash
# 按顺序执行SQL文件
mysql -uroot -p thinkmes < database/init.sql
mysql -uroot -p thinkmes < database/init_tenant_user.sql
mysql -uroot -p thinkmes < database/migrate_add_tenant_package_feature.sql
mysql -uroot -p thinkmes < database/migrate_add_tenant_order.sql
mysql -uroot -p thinkmes < database/seed_auth_rule.sql
mysql -uroot -p thinkmes < database/seed_tenant_package.sql
```

### 4. 访问系统

- **后台管理**：`http://yourdomain.com/admin/index/login`
  - 账号：`admin`
  - 密码：`123456`

详细安装步骤请参考：[INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

## 📚 文档

- [安装指南](INSTALLATION_GUIDE.md) - 详细的安装和配置说明
- [开发状态](SAAS_DEVELOPMENT_STATUS.md) - 功能开发状态和待办事项
- [功能总结](SAAS_COMPLETE_SUMMARY.md) - 已完成功能的详细说明

## 🎯 核心功能说明

### 租户管理

1. **创建租户**
   - 填写租户名称、域名、选择套餐
   - 系统自动创建管理员账号（admin/123456）

2. **租户登录**
   - 使用绑定域名访问
   - 或设置 `X-Tenant-Id` 请求头
   - 系统自动识别租户

### 套餐管理

1. **创建套餐**
   - 配置资源限制（最大管理员数、最大用户数）
   - 设置默认有效期

2. **配置功能权限**
   - 在套餐列表中点击"功能"按钮
   - 为套餐分配可用的功能模块

### 资源限制

系统会自动检查：
- 创建管理员时检查管理员数限制
- 用户注册时检查用户数限制
- 超出限制时提示升级套餐

### 订单管理

1. **创建订单**
   - 选择租户和套餐
   - 选择订单类型（购买/续费/升级）
   - 填写金额和有效期

2. **支付订单**
   - 确认支付后，系统自动处理：
     - 续费：延长租户到期时间
     - 升级：更换租户套餐

## 🔧 技术栈

- **后端框架**：ThinkPHP 8.1
- **前端框架**：AdminLTE 3 + Bootstrap 4（后台完整采用 AdminLTE 3）
- **数据库**：MySQL 5.7+
- **权限系统**：RBAC（基于FastAdmin）
- **多租户**：数据隔离 + 域名识别

## 📝 开发规范

- 遵循 PSR-12 编码规范
- 使用 ThinkPHP 8.1 的命名空间和自动加载
- 视图使用 ThinkPHP 模板引擎
- JavaScript 使用 jQuery + Bootstrap Table

## 🎉 系统特点

1. **完整的SaaS架构**：多租户数据隔离、资源限制、功能权限管理
2. **灵活的套餐系统**：可配置资源限制和功能权限
3. **自动化处理**：租户初始化、续费、升级自动处理
4. **完善的统计**：租户和订单数据统计
5. **易于扩展**：模块化设计，易于添加新功能

## 📞 技术支持

如有问题，请查看相关文档或提交Issue。

## 📄 许可证

本项目采用 MIT 许可证。

---

**ThinkMes** - 让SaaS系统开发更简单！
