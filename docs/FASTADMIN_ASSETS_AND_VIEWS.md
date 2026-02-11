# FastAdmin 静态资源与视图、JS 关系说明

本文档基于 `report/application`（及同类 FastAdmin 项目）梳理：静态资源目录、引用方式、视图与 JS 的对应关系，供 ThinkMes 对齐参考。

---

## 一、静态资源目录结构（public/assets）

```
public/assets/
├── css/                    # 后台样式
│   ├── backend.css         # 后台主样式（debug 时用 backend.css，否则 backend.min.css）
│   └── skins/              # 皮肤
│       ├── skin-blue.css
│       └── ...
├── js/
│   ├── require.js          # RequireJS 加载器
│   ├── require.min.js      # 压缩版
│   ├── require-backend.js  # 后台入口（data-main 指向此文件）
│   ├── require-backend.min.js
│   ├── backend.js          # 后台公共逻辑
│   ├── backend-init.js     # 后台初始化（空壳，可扩展）
│   ├── require-table.js    # 表格模块（Bootstrap Table 封装）
│   ├── require-form.js     # 表单模块
│   ├── require-upload.js  # 上传模块
│   ├── fast.js             # Fast 工具（弹窗、API 等）
│   ├── html5shiv.js
│   ├── respond.min.js
│   └── backend/            # 按模块/控制器拆分的页面 JS（与路由一一对应）
│       ├── index.js
│       ├── dashboard.js
│       ├── auth/
│       │   ├── admin.js
│       │   └── ...
│       └── scanwork/
│           ├── traceability.js   # 对应 scanwork/traceability 控制器
│           ├── order.js
│           ├── wage.js
│           └── ...
├── libs/                   # 第三方库（bower/npm 等）
│   ├── jquery/
│   ├── bootstrap/
│   ├── bootstrap-table/
│   ├── fastadmin-layer/
│   ├── fastadmin-addtabs/
│   └── ...
├── less/                   # 源码（可选，编译成 css）
├── img/
├── images/
└── fonts/
```

要点：
- 后台**唯一入口脚本**：`require.min.js` + `data-main="require-backend.js"`，其余 JS 由 RequireJS 按需加载。
- 页面级 JS 放在 `backend/` 下，路径与**控制器命名空间**对应（见下）。

---

## 二、视图中的静态资源引用

### 2.1 占位符（view_replace_str）

- `__CDN__`：资源根 URL，运行时由 `application/common/behavior/Common.php` 的 `moduleInit` 根据 `request()->root()` 自动设置（未配置时等于站点根，如 `https://domain.com` 或 `https://domain.com/public`）。
- `__PUBLIC__`：同根下带尾斜杠，如 `https://domain.com/`。
- `__ROOT__`：去掉 `/public` 的根路径。

配置在 `application/config.php` 的 `view_replace_str`，可为空，由行为自动补全。

### 2.2 公共头部（common/meta.html）

```html
<link rel="shortcut icon" href="__CDN__/assets/img/favicon.ico" />
<link href="__CDN__/assets/css/backend{$Think.config.app_debug?'':'.min'}.css?v={$Think.config.site.version}" rel="stylesheet">
{if $Think.config.fastadmin.adminskin}
<link href="__CDN__/assets/css/skins/{$Think.config.fastadmin.adminskin}.css?v=..." rel="stylesheet">
{/if}
<!--[if lt IE 9]>
  <script src="__CDN__/assets/js/html5shiv.js"></script>
  <script src="__CDN__/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = { config: {$config|json_encode} };
</script>
```

- 所有 CSS、入口前脚本均通过 `__CDN__/assets/...` 引用。
- 将后端 `$config` 注入为 `require.config`，供 RequireJS 使用（含 `site.cdnurl`、`controllername`、`actionname`、`jsname` 等）。

### 2.3 公共底部脚本（common/script.html）

```html
<script src="__CDN__/assets/js/require.min.js" data-main="__CDN__/assets/js/require-backend{$Think.config.app_debug?'':'.min'}.js?v={$site.version}"></script>
```

- 只引一个脚本：RequireJS + `data-main` 指向 `require-backend.js`。
- 页面级 JS 不在此处写死，由 `require-backend.js` 根据 `config.jsname` 动态加载。

### 2.4 布局（layout/default.html）

- 头部：`{include file="common/meta" /}`
- 内容：`{__CONTENT__}`（子视图内容）
- 底部：`{include file="common/script" /}`

即：**所有后台页共用同一套 meta + script**，差异只在传入的 `$config` 和当前控制器/方法决定的 `jsname`/`actionname`。

---

## 三、控制器与 config（jsname / actionname）

在 `application/common/controller/Backend.php` 的初始化中：

```php
$controllername = Loader::parseName($this->request->controller());  // 如 scanwork.Traceability -> scanwork/traceability
$actionname     = strtolower($this->request->action());             // 如 index, add, edit

$config = [
    'site'           => [...],
    'modulename'     => $modulename,      // admin
    'controllername' => $controllername,  // scanwork/traceability
    'actionname'     => $actionname,      // index
    'jsname'         => 'backend/' . str_replace('.', '/', $controllername),  // backend/scanwork/traceability
    'moduleurl'      => ...,
    'language'       => $lang,
    // ...
];
$this->assign('config', $config);
```

要点：
- `jsname` 默认 = `backend/` + 控制器名（点转斜杠），**与 public/assets/js/backend/ 下路径一致**。
- 个别控制器会 `$this->view->assign('js', ['backend/scanwork/wage_export'])` 等，若项目中有把 `$js` 写回 `config.jsname` 的逻辑，则会覆盖默认；否则仍以 `jsname` 为准。

---

## 四、require-backend.js：如何加载页面 JS

```javascript
// 1. baseUrl 使用 config.site.cdnurl + '/assets/js/'
// 2. 启动后执行：
require(['backend', 'backend-init', 'addons'], function (Backend, undefined, Addons) {
    if (Config.jsname) {
        require([Config.jsname], function (Controller) {
            if (Controller[Config.actionname]) {
                Controller[Config.actionname]();
            } else if (Controller._empty) {
                Controller._empty();
            }
        });
    }
});
```

即：
- 用 `Config.jsname`（如 `backend/scanwork/traceability`）加载 AMD 模块。
- 加载完成后，调用该模块的 `Controller[Config.actionname]()`，如 `Controller.index()`。

因此：
- **控制器** `scanwork/Traceability` + **方法** `index`  
- 对应 **JS 模块** `public/assets/js/backend/scanwork/traceability.js`  
- 且该模块需暴露 **与 action 同名的方法**，如 `index`、`add`、`edit`。

---

## 五、视图与 JS 的对应关系（scanwork 示例）

| 控制器（模块/类）     | 方法   | 视图路径                              | JS 模块路径                          |
|----------------------|--------|---------------------------------------|--------------------------------------|
| scanwork/Traceability | index  | admin/view/scanwork/traceability/index.html | js/backend/scanwork/traceability.js |
| scanwork/Traceability | add    | （弹窗或独立模板）                    | 同上，Controller.add()               |
| scanwork/Order        | index  | admin/view/scanwork/order/index.html   | js/backend/scanwork/order.js         |

### 视图（traceability/index.html）做什么

- 只负责结构：工具栏、`#table`、按钮等。
- 不写 `<script src="...">` 引用业务 JS；业务逻辑全部在 `backend/scanwork/traceability.js` 里。

### JS 模块（traceability.js）做什么

- `define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function (...) { ... })`
- 返回对象 `Controller`，包含 `index`、`add`、`edit` 等方法。
- `index` 中：`Table.api.init({ extend: { index_url: 'scanwork/traceability/index', ... } });`，再 `$("#table").bootstrapTable({...})`，并 `Table.api.bindevent(table)`。
- 表格的 URL、列、工具栏事件等都在 JS 里配置，与视图中的 `id="table"`、`class="toolbar"` 等约定一致。

### 小结

- **视图**：只提供 DOM 结构（表格容器、工具栏、弹窗占位等）。
- **JS**：按控制器名落在 `backend/模块/控制器名.js`，按 action 执行 `Controller[action]()`，负责表格初始化、请求、事件绑定等。
- **约定**：控制器名（parseName 后）与 `backend/` 下路径、以及 JS 文件名一致；action 名与 Controller 方法名一致。

---

## 六、require-table.js 与表格页

- `require-table.js` 封装了 Bootstrap Table + 通用请求（列表、增删改、导出、多选等）。
- 各页面的 `backend/xxx/yyy.js` 通过 `Table.api.init()` 传入当前控制器的 `index_url`、`add_url`、`edit_url`、`del_url` 等，再初始化 `$("#table").bootstrapTable(...)` 并绑定工具栏按钮（.btn-add、.btn-edit、.btn-del 等）。
- 视图里通过 `{:build_toolbar('refresh,add,edit,del,export')}` 等生成工具栏，与 Table 的 config 对应。

---

## 七、ThinkMes 可对齐的要点

1. **静态资源**  
   - 若希望与 FastAdmin 一致：保留 `public/assets` 下 css/js/libs 结构，入口用 RequireJS + 一个 `require-backend.js`，其余 JS 按需加载。

2. **视图引用**  
   - 统一用“根路径”占位符（如 ThinkMes 的 `/assets/` 或等价 __CDN__）引用 css/js，不在每个视图里写死 CDN 或重复引用。

3. **控制器 → 页面 JS**  
   - 约定：控制器名（含子模块）映射到 `backend/模块/控制器名.js`，并在后端把 `controllername`、`actionname`、`jsname` 注入到前端 config；前端入口根据 `config.jsname` 加载对应 AMD 模块并执行 `Controller[actionname]()`。

4. **视图与 JS 分工**  
   - 视图：布局 + 表格/表单的 DOM 结构。  
   - JS：表格/表单初始化、接口 URL、事件绑定，集中在对应 `backend/xxx.js` 中。

按上述方式对齐后，ThinkMes 的静态资源、视图和 JS 的关系即可与 FastAdmin（report/application）保持一致，便于复用或迁移页面逻辑。

---

## 八、ThinkMes 已实现（当前状态）

- **Backend 基类**（`app/admin/controller/Backend.php`）：为所有后台页注入 `config`（含 `controllername`、`actionname`、`jsname`、`site.cdnurl`、`moduleurl`）及 `admin`、`site`。
- **公共布局**：`layout/default.html` 包含 `common/meta`、`common/script`，内容区用 `{__CONTENT__}`；列表页只写内容片段，通过 `fetchWithLayout('xxx/index')` 套上布局。
- **静态引用**：`common/meta.html` 统一引用 AdminLTE/Bootstrap/Bootstrap Table/FontAwesome 的 CSS，并输出 `var Config = {...}`；`common/script.html` 统一引用 jQuery、Bootstrap、AdminLTE、Bootstrap Table、FontAwesome 及 **backend-loader.js**。
- **页面 JS 按控制器加载**：`public/assets/js/backend-loader.js` 根据 `Config.jsname` 动态加载 `backend/xxx.js`，加载完成后执行 `window.__backendController[Config.actionname]()`；同时负责侧栏菜单和“清除缓存”等公共逻辑。
- **约定**：控制器 `Admin` → `jsname=backend/admin` → 加载 `backend/admin.js`，其内 `window.__backendController = { index: function(){...} }`。已按此方式实现：`backend/admin.js`、`backend/role.js`、`backend/log.js`、`backend/config.js`、`backend/auth_rule.js`。
- **列表页**：管理员、角色、日志、配置、权限规则的 index 均改为继承 `Backend`、使用 `fetchWithLayout`，视图只保留卡片+表格/内容，表格初始化和事件在对应 `backend/xxx.js` 中完成。
