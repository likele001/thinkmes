## 餐饮扫码点餐小程序（独立）

### 目录
- 该小程序源码独立放在 `restaurant_miniapp/`，不与现有 `miniapp/`（报工/Scanwork）混用。

### 依赖后端接口
- 统一前缀：`{BASE_URL}/restaurant/*`，配置在 `utils/config.js`
- 桌码进入方式：
  - 方式1：小程序码携带 `scene=t{tenantId}|tk{tableToken}`（后端已提供生成接口）
  - 方式2：手动打开页面传 `?token=...`（调试用）

### 后端配置
- 后台：餐饮SaaS → 小程序配置
  - AppID / Secret / 页面路径（默认 `pages/menu/index`）

