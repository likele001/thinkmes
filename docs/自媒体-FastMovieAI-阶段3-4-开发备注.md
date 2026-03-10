# 自媒体 · FastMovieAI 阶段 3 / 阶段 4 开发备注

本文档仅作后续开发备忘，不实现具体代码。阶段 1（TTS + 口播成片）与阶段 2（AI 视频生成 API、可选数字人）见主需求与其它文档。

---

## 阶段 3：剧本/分镜结构化与可视化编辑

### 目标
- 剧本与分镜结构化存储，支持多镜头、每镜画面描述与旁白。
- 可选：可视化时间轴/分镜板编辑（拖拽、时长、预览）。

### 要点
- **数据**：可在现有 `fa_wemedia_video_script` 上扩展 JSON 字段（如 `storyboard`），或新增分镜子表（script_id、order、duration、caption、image_path、audio_segment 等）。
- **后台/前端**：脚本编辑页从「单一大文本框」升级为「分镜列表 + 每镜文本/图/时长」，便于与 TTS 分段、口播成片按镜合成对接。
- **与阶段 1 关系**：当前阶段 1 为「整段脚本 → 一条配音 → 一张封面 → 一条口播视频」；阶段 3 后可支持「按镜配音、按镜配图、再合成多镜成片」。

### 建议模块/文件
- 模型：`WemediaVideoScriptModel` 或新 `WemediaStoryboardModel`。
- 控制器：`app/index/controller/wemedia/Video.php` 或单独 `Storyboard.php`。
- 视图：`app/index/view/wemedia/video/edit.html` 或 `storyboard/edit.html`。
- 若用 JSON 存储：迁移中为 `fa_wemedia_video_script` 增加 `storyboard` 等字段即可。

---

## 阶段 4：支付、VIP/积分、微信、插件化

### 目标
- 与 FastMovieAI 类似：用户体系、支付（支付宝/微信）、VIP 或积分、微信（公众号/小程序）对接、插件化扩展。

### 要点
- **支付**：复用或扩展现有 `app/common/lib/payment/`（PaymentService、各 Gateway），增加「自媒体」场景的套餐/单次计费（如按条成片、按分钟 TTS）。
- **VIP/积分**：若系统已有会员或积分表，可挂「自媒体」权益（每日生成条数、可用 TTS 时长、可用 AI 视频条数）；若无则需设计简单积分或 VIP 等级表及与自媒体动作的扣减逻辑。
- **微信**：公众号/小程序登录、绑定、消息推送等，可放在 `app/index` 或独立模块，与现有用户中心统一。
- **插件化**：将「文案 AI、TTS、AI 视频、数字人」等做成可开关、可配置的「能力模块」，便于多租户或不同套餐启用不同能力；配置集中在 `fa_wemedia_config` 或独立配置表，后台「自媒体配置」中按模块展示。

### 建议模块/文件
- 支付/套餐：`app/common/lib/payment/`，后台套餐管理（可参考 `TenantPackage` 等），前端「我的套餐/积分」页。
- VIP/积分：若新建则需 migration、Model、后台配置与前端展示与扣减处（如 `TtsService`/视频生成调用前检查并扣减）。
- 微信：`app/index/controller/wechat/` 或 `app/common/lib/Wechat*.php`，与路由、配置联动。
- 插件化：配置 key 设计（如 `wemedia_module_tts`、`wemedia_module_ai_video`）、各服务内「是否启用」判断；可选插件目录结构（如 `app/wemedia_plugins/`）后续再定。

---

## 与阶段 1 / 2 的衔接
- **阶段 1**：已实现 TTS + 口播成片（配音 + 图 → 视频），为主线能力。
- **阶段 2**：接一家 AI 视频生成 API、可选数字人后，「图/文生成视频」与「口播成片」双线齐全。
- **阶段 3**：在现有脚本/成片基础上增加结构化分镜与可视化编辑，为多镜、更精细成片打基础。
- **阶段 4**：在能力完备后补支付、VIP/积分、微信与插件化，形成完整商业化与扩展能力。

以上为预留备注，具体实现以实际排期与产品为准。
