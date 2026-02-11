# MES 制造执行系统实现指南

## 概述

本文档说明如何在 ThinkMes 系统中实现完整的 MES（制造执行系统）功能模块，**所有功能都包含租户隔离**。

## 已完成的工作

### 1. 数据库表结构 ✅

已创建所有核心表的 SQL 文件：`database/migrate_add_mes_tables.sql`

**核心表列表**：
- `fa_mes_product` - 产品表
- `fa_mes_product_model` - 产品型号表
- `fa_mes_customer` - 客户表
- `fa_mes_order` - 订单表
- `fa_mes_order_model` - 订单型号表
- `fa_mes_process` - 工序表
- `fa_mes_process_price` - 工序工价表
- `fa_mes_material` - 物料表
- `fa_mes_supplier` - 供应商表
- `fa_mes_bom` - BOM表
- `fa_mes_bom_item` - BOM明细表
- `fa_mes_allocation` - 分工分配表
- `fa_mes_report` - 报工表
- `fa_mes_order_material` - 订单物料需求表
- `fa_mes_purchase_request` - 采购申请表

**重要**：所有表都包含 `tenant_id` 字段，确保租户数据隔离。

### 2. 模型层 ✅

已创建所有核心模型，位于 `app/admin/model/mes/` 目录：

- `ProductModel.php` - 产品模型
- `ProductModelModel.php` - 产品型号模型
- `CustomerModel.php` - 客户模型
- `OrderModel.php` - 订单模型
- `OrderModelModel.php` - 订单型号模型
- `ProcessModel.php` - 工序模型
- `ProcessPriceModel.php` - 工序工价模型
- `MaterialModel.php` - 物料模型
- `SupplierModel.php` - 供应商模型
- `BomModel.php` - BOM模型
- `BomItemModel.php` - BOM明细模型
- `AllocationModel.php` - 分工分配模型
- `ReportModel.php` - 报工模型
- `OrderMaterialModel.php` - 订单物料需求模型
- `PurchaseRequestModel.php` - 采购申请模型

### 3. 控制器层（部分完成）✅

已创建订单管理控制器：`app/admin/controller/mes/Order.php`

**已实现功能**：
- 订单列表（带租户过滤）
- 添加订单（自动填充租户ID）
- 编辑订单（租户隔离）
- 删除订单（租户隔离）
- 自动计算物料需求
- 自动生成采购申请
- 查看订单物料清单

**租户隔离实现**：
```php
$tenantId = $this->getTenantId();
$query = OrderModel::where('tenant_id', $tenantId)->...
```

### 4. 路由配置 ✅

已添加 MES 模块路由到 `app/admin/route/app.php`：
- 订单管理路由
- 产品管理路由
- BOM管理路由
- 报工管理路由
- 客户管理路由
- 工序管理路由
- 物料管理路由
- 供应商管理路由

### 5. 权限规则 ✅

已创建权限规则 SQL：`database/seed_mes_auth_rules.sql`

包含所有 MES 模块的菜单和操作权限。

## 待完成的工作

### 1. 创建其他控制器

需要创建以下控制器（参考 `Order.php` 的实现方式）：

#### 产品管理控制器 (`app/admin/controller/mes/Product.php`)
- 产品列表（带租户过滤）
- 添加产品（自动填充租户ID）
- 编辑产品（租户隔离）
- 删除产品（租户隔离）
- 产品型号管理

#### BOM管理控制器 (`app/admin/controller/mes/Bom.php`)
- BOM列表（带租户过滤）
- 添加BOM（自动填充租户ID）
- 编辑BOM（租户隔离）
- 删除BOM（租户隔离）
- BOM明细管理（多层级支持）
- BOM审核功能

#### 报工管理控制器 (`app/admin/controller/mes/Report.php`)
- 报工列表（带租户过滤）
- 添加报工（自动填充租户ID）
- 编辑报工（租户隔离）
- 删除报工（租户隔离）
- 报工审核（通过/拒绝）
- 图片/视频上传

#### 其他基础控制器
- `Customer.php` - 客户管理
- `Process.php` - 工序管理
- `Material.php` - 物料管理
- `Supplier.php` - 供应商管理

**控制器模板示例**：
```php
<?php
declare(strict_types=1);

namespace app\admin\controller\mes;

use app\admin\controller\Backend;
use app\admin\model\mes\XxxModel;
use think\facade\View;
use think\Response;

class Xxx extends Backend
{
    public function index(): string|Response
    {
        $limitParam = $this->request->get('limit');
        if (!$this->request->isAjax() && ($limitParam === null || $limitParam === '')) {
            View::assign('title', 'XXX管理');
            return $this->fetchWithLayout('mes/xxx/index');
        }

        $tenantId = $this->getTenantId(); // 获取租户ID
        $query = XxxModel::where('tenant_id', $tenantId)->order('id', 'desc');
        
        // ... 查询逻辑
        
        return $this->success('', ['total' => $total, 'list' => $list]);
    }

    public function add(): string|Response
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            $tenantId = $this->getTenantId();
            $params['tenant_id'] = $tenantId; // 自动填充租户ID
            
            // ... 保存逻辑
            
            return $this->success('添加成功', ['id' => $model->id]);
        }
        
        View::assign('title', '添加XXX');
        return $this->fetchWithLayout('mes/xxx/add');
    }
    
    // ... 其他方法
}
```

### 2. 创建视图文件

需要创建以下视图文件（参考 FastAdmin 风格）：

**目录结构**：
```
app/admin/view/mes/
├── order/
│   ├── index.html
│   ├── add.html
│   ├── edit.html
│   └── material_list.html
├── product/
│   ├── index.html
│   ├── add.html
│   └── edit.html
├── bom/
│   ├── index.html
│   ├── add.html
│   ├── edit.html
│   └── items.html
├── report/
│   ├── index.html
│   ├── add.html
│   ├── edit.html
│   └── audit.html
└── ...
```

**视图文件模板示例**（参考 `app/admin/view/member/index.html`）：
```html
<div class="panel panel-default panel-intro">
    <div class="panel-heading">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#t-all" data-toggle="tab">全部</a></li>
        </ul>
    </div>
    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="t-all">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" title="刷新"><i class="fa fa-refresh"></i> </a>
                        <a href="javascript:;" class="btn btn-success btn-add" title="添加"><i class="fa fa-plus"></i> 添加</a>
                        <a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled" title="编辑"><i class="fa fa-pencil"></i> 编辑</a>
                        <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled" title="删除"><i class="fa fa-trash"></i> 删除</a>
                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover table-nowrap"></table>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 3. 创建前端 JS 文件

需要创建以下 JS 文件（参考 FastAdmin 风格）：

**目录结构**：
```
public/assets/js/backend/mes/
├── order.js
├── product.js
├── bom.js
├── report.js
└── ...
```

**JS 文件模板示例**（参考 `public/assets/js/backend/member.js`）：
```javascript
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            var table = $("#table");
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    {field: 'id', title: __('Id'), operate: false},
                    {field: 'name', title: __('名称'), operate: 'LIKE'},
                    // ... 其他列
                    {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                ]
            });
            
            $(document).on('click', '.btn-add', function () {
                Fast.api.open("mes/xxx/add", "添加");
            });
            
            // ... 其他事件处理
        }
    };
    return Controller;
});
```

## 部署步骤

### 1. 执行数据库迁移

```bash
cd /www/wwwroot/thinkmes
mysql -h127.0.0.1 -u用户名 -p密码 数据库名 < database/migrate_add_mes_tables.sql
```

### 2. 添加权限规则

```bash
mysql -h127.0.0.1 -u用户名 -p密码 数据库名 < database/seed_mes_auth_rules.sql
```

### 3. 创建视图和 JS 文件

按照上述目录结构创建视图文件和 JS 文件。

### 4. 测试功能

1. 登录后台
2. 检查菜单中是否出现 "MES制造执行" 菜单
3. 测试各个功能的租户隔离是否正常

## 租户隔离要点

### 1. 控制器中的租户隔离

**必须**在所有查询中添加租户过滤：
```php
$tenantId = $this->getTenantId();
$query = Model::where('tenant_id', $tenantId)->...
```

### 2. 添加/编辑时的租户ID填充

**必须**在创建记录时自动填充租户ID：
```php
$params['tenant_id'] = $this->getTenantId();
Model::create($params);
```

### 3. 关联查询的租户隔离

**必须**在关联查询中也添加租户过滤：
```php
$query = OrderModel::with(['orderModels' => function($q) use ($tenantId) {
    $q->where('tenant_id', $tenantId);
}])->where('tenant_id', $tenantId);
```

### 4. 删除操作的租户隔离

**必须**在删除前验证租户ID：
```php
$model = Model::where('tenant_id', $tenantId)->find($id);
if (!$model) {
    return $this->error('记录不存在');
}
$model->delete();
```

## 参考代码

- 订单管理控制器：`app/admin/controller/mes/Order.php`
- 用户管理控制器：`app/admin/controller/Member.php`（参考租户隔离实现）
- FastAdmin 原项目：`/www/wwwroot/report/application/admin/controller/scanwork/`

## 注意事项

1. **所有数据库操作都必须包含租户ID过滤**
2. **视图文件路径使用 `/public/assets/` 前缀**
3. **JS 文件路径使用 `backend/mes/` 前缀**
4. **权限规则名称必须与路由一致**
5. **所有模型必须继承 `think\Model`**
6. **所有控制器必须继承 `app\admin\controller\Backend`**

## 后续扩展

完成基础功能后，可以继续实现：
- 工资管理
- 库存管理
- 采购管理
- 质量追溯
- 报表统计
- Excel 导入导出
