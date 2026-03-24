---
name: miniapp-ui-ux-upgrade
overview: 升级小程序全量 UI 设计与功能体验，覆盖列表/详情完整性、性能与屏幕适配，并核查报工相关接口。
design:
  styleKeywords:
    - 浅色
    - 清新
    - 圆角卡片
    - 柔和渐变
    - 骨架屏
    - 一致交互
  fontSystem:
    fontFamily: PingFang SC, -apple-system, Roboto
    heading:
      size: 34rpx
      weight: 600
    subheading:
      size: 30rpx
      weight: 600
    body:
      size: 28rpx
      weight: 400
  colorSystem:
    primary:
      - "#5B7BFF"
      - "#6B8CFF"
      - "#3DC9A7"
    background:
      - "#F5F7FB"
      - "#FFFFFF"
      - "#0F172A"
    text:
      - "#1F2937"
      - "#4B5563"
      - "#9CA3AF"
      - "#FFFFFF"
    functional:
      - "#22C55E"
      - "#F59E0B"
      - "#EF4444"
      - "#3B82F6"
todos:
  - id: audit-scope
    content: 用[subagent:code-explorer]梳理 miniapp 页/组件与全局样式现状，列问题清单
    status: completed
  - id: design-system
    content: 制定并落地小程序设计规范：色板、字阶、间距、组件态、空/骨架/提示
    status: completed
    dependencies:
      - audit-scope
  - id: refactor-core-pages
    content: 重构核心 Tab 页与高频流（首页/报工/工资/我的）UI 与交互
    status: completed
    dependencies:
      - design-system
  - id: refactor-business-pages
    content: 分批重构业务列表/详情/编辑页 UI，统一筛选、表单、状态展示
    status: completed
    dependencies:
      - design-system
  - id: api-ux-hardening
    content: 优化 utils/api.js 请求层：超时、重试、去重、缓存、错误兜底与 loading 策略
    status: completed
    dependencies:
      - audit-scope
  - id: backend-qa
    content: 核查并修复 Scanwork.php、Worker.php 报工链路数据/校验/性能问题
    status: completed
    dependencies:
      - audit-scope
  - id: perf-adaptation
    content: 优化首屏/长列表性能、懒加载、弱网提示与多机型适配回归
    status: completed
    dependencies:
      - refactor-core-pages
      - refactor-business-pages
      - api-ux-hardening
---

## 用户需求

- 全面升级小程序 UI，现代化、简洁美观，统一视觉/布局/交互。
- 梳理并修复现有功能问题，确保各模块数据交互正常、流畅可用。
- 优化性能：页面加载与响应更快，多屏适配。
- 提供完整的 UI 设计方案与功能优化方案。

## 产品概览

- 微信小程序，包含首页/报工/工资/我的等 Tab 及 60+ 业务页面（报工、订单、库存、质检、售后、BI 等）。
- 需保持业务不变，提升体验与稳定性。

## 核心要点

- 视觉：浅色、清新时尚，统一组件（卡片、按钮、表单、列表、状态标签）。
- 交互：一致的导航/返回/加载/错误态；骨架屏、空状态、下拉刷新/上拉加载统一。
- 功能：检查并修复报工链路（Scanwork/Worker 接口）、列表/详情数据完整性。
- 性能：首屏与列表加载优化，缓存/分页/并发与错误兜底，弱网提示。
- 适配：常见安卓/iOS 机型，多分辨率布局。

## 技术栈与方案

- 平台：微信小程序（原生 WXML/WXSS/JS），沿用现有框架。
- 全局样式：基于 app.wxss 优化主题色、组件态、间距栅格、暗色兜底（可选）。
- 组件规范：卡片/按钮/表单/列表/状态徽章/弹窗/骨架屏/空状态/加载与错误提示。
- 状态与数据：
- 复用 utils/api.js 请求封装，细化 loading/error/重试、接口超时与并发控制。
- 请求分级：首屏/列表用并发+缓存，详情兜底本地快照，失败优先提示+重试。
- 性能优化：
- 首屏/Tab 页骨架屏、图片懒加载、列表分页与上拉增量。
- 避免重复请求：键控缓存、去重队列；下拉刷新节流。
- 资源：精简全局样式与图片，合并通用组件。
- 兼容与适配：
- 使用 rpx、flex，自适应安全区；长列表性能与低端机内存控制。
- 统一导航栏/Tab 高度与留白；表单与按钮触达区 ≥ 88rpx。
- 测试与验收：
- 核心流程回归：登录/绑定、报工提交、列表/详情、扫码、工资、质检、库存/出入库。
- 设备：主流 iOS/Android 中低端机；网络：4G/弱网。

## 架构与目录

- /miniapp/app.wxss：全局主题、色板、组件态、暗色兜底（可选）。
- /miniapp/app.js：启动逻辑、租户配置、登录与请求封装的全局 loading/error 优化。
- /miniapp/app.json：Tab 配置、页面分组与分包（可评估拆分加速加载）。
- /miniapp/utils/api.js：请求层超时、重试、缓存、错误码兜底、并发去重。
- /miniapp/utils/config.js：环境与静态资源配置。
- /miniapp/pages/**：页面级 UI/交互重构（首页/报工/工资/我的及业务列表、详情、编辑、扫码等）。
- /app/api/controller/Scanwork.php、Worker.php：报工链路/任务/工资接口核查与修复。

## 设计方案

- 风格：浅色、清新时尚，柔和渐变点缀，圆角卡片、轻阴影，留白充足。
- 版式：统一 12/16/24rpx 间距体系，卡片化分区；列表左信息右状态/操作；详情分段标题+分组信息。
- 组件：主按钮（渐变）、次按钮（描边/浅色）、信息卡、状态徽章（语义色）、表单输入（聚焦高亮）、筛选条、骨架屏、空状态插画、结果提示。
- 交互：统一导航/返回；下拉刷新+上拉加载；提交/长任务用全屏 loading；错误提示含重试；扫码与拍照流程弹层指引。
- 适配：rpx+flex，安全区与底部操作条留白；文字 28/30/32rpx 主体，24rpx 辅助。

## 可用扩展

- **SubAgent: code-explorer**：用于快速全局检索与比对多页面、多模块代码，锁定需改版与问题点。
- **Skill: skill-creator**：如需为团队生成复用的设计/开发规范技能，可用该技能产出可复用指引。