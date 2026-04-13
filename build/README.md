# 基础框架打包

## 打包命令

1. 在项目根目录**先安装依赖**（若未安装过）：
   ```bash
   composer install --no-dev
   ```
2. 再执行打包：
   ```bash
   php build/pack_base.php
   ```

生成文件：`build/thinkmes-base-1.0.zip`（**已含 ThinkPHP 与 vendor**，用户解压后无需再执行 composer）

## 包内容说明

- **不含 MES**：无制造执行系统相关控制器、模型、视图、路由。
- **不含租户/用户管理**：无租户、套餐、订单、C 端用户管理；仅保留基础后台（管理员、角色、权限、配置、日志、附件、插件）。
- **数据库**：仅包含 `database/init.sql`，安装时只执行该文件。
- **安装**：解压后访问 `/install` 按步骤安装，安装完成后通过**随机路径**访问后台（无单独 .php 文件）。

## 用户使用步骤

1. 解压 `thinkmes-base-1.0.zip` 到站点目录。
2. 配置站点根目录为解压后的 **`public`** 目录，并配置伪静态（见下）。
3. 浏览器访问 `http://您的域名/install`，按向导完成安装。
4. 保存安装完成页展示的**后台地址**（形如 `http://您的域名/随机路径/index/login`），此后仅通过该地址访问后台。

**Nginx 伪静态**（否则 /install、后台路径会 404）：详见 `public/nginx_重写说明.txt`，或使用：
```nginx
location / {
    try_files $uri $uri/ /index.php?s=$uri;
}
```
