# KeleAdmin - 多租户后台基础框架

基于 ThinkPHP 8 的多租户后台底座，支持**应用中心**按需安装 MES、CRM、AI、设备、人事、财务、支付等业务应用。

---

## 开源协议

本项目（KeleAdmin 基础框架）采用 **MIT License**，详见 [LICENSE](LICENSE)。  
ThinkPHP 框架采用 Apache License 2.0。

---

## 特性

- **用户中心**：C 端登录/注册、会员中心、个人资料、修改密码、找回密码（基础版即含）
- **租户管理**：租户列表、创建/编辑、套餐与到期管理；**租户套餐**不在安装时预置，由部署方在后台「租户套餐」中自行添加（名称、人数限制、有效期等按需填写）
- **套餐与订单**：套餐功能配置、租户订单（购买/续费/升级）
- **权限**：RBAC（角色、权限规则）、数据权限、随机后台入口
- **应用中心**：上传应用包（zip）安装/卸载，不内置业务应用
- **安装向导**：解压后访问 `/install`，分步完成环境检测、数据库配置、管理员设置
- **可扩展**：插件（addon）、打印模板、短信配置、云存储等

---

## 环境要求

- PHP 8.0+
- MySQL 5.7+
- Composer

---

## 快速开始（基础版安装）

### 1. 获取代码

从发布页下载 `thinkmes-base-x.x.zip`（或克隆本仓库后使用 `php build/pack_base.php` 打包）。

### 2. 解压并安装依赖

```bash
unzip thinkmes-base-x.x.zip
cd thinkmes-base-x.x
composer install
```

### 3. 配置 Web 目录

将网站运行目录指向项目下的 **public** 目录。

### 4. 运行安装向导

浏览器访问：**http://你的域名/install**

按步骤完成：同意协议 → 环境检测 → 填写数据库（主机、端口、库名、用户名、密码、表前缀）→ 设置超级管理员 → 执行安装。安装完成后使用页面提示的**随机后台地址**登录。

安装后如需多租户与套餐：进入后台 **租户套餐**，自行添加套餐（如基础版、标准版等），再在 **租户管理** 中创建租户并选择套餐即可。

### 5. 安装业务应用（可选）

登录后台 → **应用中心** → **上传应用包**，选择 MES/CRM/AI 等应用的 zip 包上传安装。安装后文件会合并到项目正常目录（如 `app/admin/controller/crm`），菜单自动显示。

---

## 项目结构（基础版）

```
├── app/
│   ├── admin/          # 后台（租户、套餐、权限、日志、应用中心等）
│   ├── api/            # API（用户、小程序等）
│   ├── index/          # 前台入口
│   ├── install/        # 安装向导
│   └── common/         # 公共库与中间件
├── config/
├── database/
│   └── init.sql        # 安装时执行的底座表结构
├── public/             # Web 根目录指向此目录
├── runtime/
├── .env.example
├── 安装说明.txt
├── LICENSE             # MIT 开源协议
└── README.md
```

---

## 打包与发布

- **基础框架打包**：`php build/pack_base.php [版本号]` → 产出 `dist/thinkmes-base-x.x.zip`
- **应用打包**：`php build/pack_mes.php`、`pack_crm.php`、`pack_ai.php` 等 → 产出 `dist/thinkmes-xxx-x.x.zip`

详见 [docs/基础框架打包说明.md](docs/基础框架打包说明.md)、[docs/应用包制作与发布.md](docs/应用包制作与发布.md)。

---

## 文档

- [基础框架打包说明](docs/基础框架打包说明.md) - 基础版打包与 /install 安装流程
- [应用包制作与发布](docs/应用包制作与发布.md) - 应用 zip 结构、app.json、应用中心安装
- [模块与应用中心对照](docs/模块与应用中心对照.md) - 底座与业务应用发布策略

---

## 技术栈

- 后端：ThinkPHP 8
- 前端：Bootstrap 5 / CoreUI 等（后台）
- 数据库：MySQL 5.7+
- 权限：RBAC（类 FastAdmin 规则表）

---

## 说明

- **KeleAdmin**：本仓库中的多租户后台基础框架，开源、可独立部署，通过应用中心安装业务应用。
- **ThinkMes**：在 KeleAdmin 基础上集成 MES/CRM/AI 等业务模块的完整产品；业务应用亦可单独打包，通过应用中心安装到 KeleAdmin 底座上。

---

**KeleAdmin** - 多租户后台基础框架，按需安装、灵活扩展。
