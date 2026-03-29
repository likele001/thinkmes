# 租户自助购买 - URL路由说明

## 🌐 完整URL地址列表

### 用户访问页面

| 功能 | URL | 说明 |
|------|-----|------|
| 套餐选择页 | `/purchase` | 查看所有套餐，选择购买 |
| 购买表单页 | `/purchase/form?package_id=1` | 填写企业信息、选择支付方式 |
| 支付结果页 | `/api/tenant_purchase/return?order_no=TOxxx` | 支付完成后的跳转页面 |

### API接口（前后端分离调用）

| 功能 | 方法 | URL | 参数 |
|------|------|-----|------|
| 获取套餐列表 | GET | `/api/tenant_purchase/package_list` | - |
| 创建订单 | POST | `/api/tenant_purchase/create_order` | package_id, company_name, contact_name, contact_phone, contact_email, domain, login_account, login_password |
| 获取支付方式 | GET | `/api/tenant_purchase/payment_methods` | - |
| 发起支付 | POST | `/api/tenant_purchase/pay` | order_no, gateway_id |
| 查询订单状态 | GET | `/api/tenant_purchase/order_status` | order_no |
| 支付同步回调 | GET | `/api/tenant_purchase/return` | order_no |
| 支付异步回调 | POST | `/api/tenant_purchase/notify` | gateway_id + 第三方参数 |

---

## 📝 详细使用说明

### 1. 用户访问入口

#### 方式一：直接访问套餐页面
```
http://你的域名/purchase
```
页面展示所有套餐，用户点击"立即购买"进入表单。

#### 方式二：直接访问购买表单（指定套餐）
```
http://你的域名/purchase/form?package_id=1
```
跳过套餐选择，直接购买指定套餐。

---

### 2. API调用示例（AJAX）

#### 获取套餐列表
```javascript
fetch('/api/tenant_purchase/package_list')
    .then(res => res.json())
    .then(data => {
        console.log(data.data); // 套餐列表
    });
```

#### 获取支付方式
```javascript
fetch('/api/tenant_purchase/payment_methods')
    .then(res => res.json())
    .then(data => {
        console.log(data.data); // 支付网关列表
    });
```

#### 创建订单
```javascript
const formData = new FormData();
formData.append('package_id', 1);
formData.append('company_name', '测试企业');
formData.append('contact_name', '张三');
formData.append('contact_phone', '13800138000');
formData.append('contact_email', 'test@example.com');
formData.append('domain', 'test.example.com');
formData.append('login_account', 'admin');
formData.append('login_password', '123456');

fetch('/api/tenant_purchase/create_order', {
    method: 'POST',
    body: formData
})
.then(res => res.json())
.then(data => {
    console.log(data.data.order_no); // 订单号
});
```

#### 发起支付
```javascript
const payData = new URLSearchParams();
payData.append('order_no', 'TO202503251234567890');
payData.append('gateway_id', 1); // 支付网关ID

fetch('/api/tenant_purchase/pay', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: payData
})
.then(res => res.json())
.then(data => {
    if (data.data.pay_url) {
        window.location.href = data.data.pay_url; // 跳转支付
    }
});
```

#### 查询订单状态
```javascript
fetch('/api/tenant_purchase/order_status?order_no=TO202503251234567890')
    .then(res => res.json())
    .then(data => {
        console.log(data.data.status); // 0待支付 1已支付 2已取消 3已退款
        if (data.data.status === 1 && data.data.tenant) {
            console.log(data.data.tenant.login_url); // 登录地址
        }
    });
```

---

## 🔗 后台相关URL

### 套餐管理
| 功能 | URL |
|------|-----|
| 套餐列表 | `/admin/tenant_package/index` |
| 添加套餐 | `/admin/tenant_package/add` |
| 编辑套餐 | `/admin/tenant_package/edit?id=1` |
| 套餐功能配置 | `/admin/tenant_package_feature/index?package_id=1` |

### 订单管理
| 功能 | URL |
|------|-----|
| 订单列表 | `/admin/tenant_order/index` |
| 添加订单 | `/admin/tenant_order/add` |
| 手动支付 | `/admin/tenant_order/pay` |
| 取消订单 | `/admin/tenant_order/cancel` |

### 支付配置
| 功能 | URL |
|------|-----|
| 支付网关列表 | `/admin/payment/config/index` |
| 添加支付网关 | `/admin/payment/config/add` |
| 官方支付宝配置 | `/admin/payment/config/official_alipay` |
| 官方微信配置 | `/admin/payment/config/official_wechat` |
| 虎皮椒配置 | `/admin/payment/config/xunhupay` |
| 易支付配置 | `/admin/payment/config/epay` |

---

## 🎯 支付回调配置

### 各支付平台回调地址设置

在对应的支付平台后台配置以下回调地址：

#### 官方支付宝
```
异步通知: http://你的域名/api/payment/notify/alipay
同步跳转: http://你的域名/api/payment/return/alipay
```

#### 官方微信支付
```
异步通知: http://你的域名/api/payment/notify/wechat
```

#### 虎皮椒支付
```
异步通知: http://你的域名/api/payment/notify/xunhupay
同步跳转: http://你的域名/api/payment/return/xunhupay
```

#### 易支付(8-pay)
```
异步通知: http://你的域名/api/payment/notify/epay
同步跳转: http://你的域名/api/payment/return/epay
```

---

## 📱 前端集成示例

### 在首页添加"立即购买"按钮
```html
<a href="/purchase" class="btn btn-primary">
    立即购买
</a>
```

### 跳转到指定套餐购买
```html
<a href="/purchase/form?package_id=1" class="btn btn-primary">
    购买基础版
</a>
```

### AJAX异步购买示例
```javascript
// 选择套餐后异步创建订单
function purchasePackage(packageId) {
    const formData = {
        package_id: packageId,
        company_name: $('#company_name').val(),
        contact_name: $('#contact_name').val(),
        contact_phone: $('#contact_phone').val(),
        contact_email: $('#contact_email').val(),
        domain: $('#domain').val(),
        login_account: $('#login_account').val(),
        login_password: $('#login_password').val()
    };

    $.post('/api/tenant_purchase/create_order', formData, function(res) {
        if (res.code === 1) {
            // 订单创建成功，选择支付方式
            showPaymentMethods(res.data.order_no);
        } else {
            alert(res.msg);
        }
    });
}

// 发起支付
function payOrder(orderNo, gatewayId) {
    $.post('/api/tenant_purchase/pay', {
        order_no: orderNo,
        gateway_id: gatewayId
    }, function(res) {
        if (res.code === 1) {
            if (res.data.pay_url) {
                window.location.href = res.data.pay_url;
            } else if (res.data.form_html) {
                document.body.innerHTML = res.data.form_html;
            }
        } else {
            alert(res.msg);
        }
    });
}
```

---

## 🔍 调试技巧

### 1. 查看路由是否生效
```bash
php think route:list
```

### 2. 测试API接口
```bash
# 获取套餐列表
curl http://你的域名/api/tenant_purchase/package_list

# 获取支付方式
curl http://你的域名/api/tenant_purchase/payment_methods
```

### 3. 查看支付日志
后台 → **支付管理** → **回调日志**

### 4. 检查订单状态
```sql
SELECT * FROM fa_tenant_order WHERE order_no = 'TO202503251234567890';
SELECT * FROM fa_payment_order WHERE order_no = 'TO202503251234567890';
```

---

## ⚠️ 注意事项

### URL大小写
- Linux服务器区分大小写，注意URL路径的大小写
- 控制器名首字母大写：`TenantPurchase`
- 文件名：`TenantPurchase.php`

### 伪静态配置
如果URL访问404，检查伪静态规则：

**Nginx配置示例**：
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
        break;
    }
}
```

**Apache配置** (.htaccess)：
```apache
<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?s=$1 [QSA,PT,L]
</IfModule>
```

### 跨域问题
如果前后端分离，需配置CORS：
```php
// 中间件中添加
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET,POST,OPTIONS');
header('Access-Control-Allow-Headers: Content-Type,Authorization');
```

---

## 📞 技术支持

如果URL无法访问，检查：
1. 路由是否正确配置
2. 控制器文件是否存在
3. 文件权限是否正确
4. 伪静态是否生效
5. 查看runtime/log错误日志

---

**更新时间**：2025-03-25
**版本**：v1.0
