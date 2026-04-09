<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use think\Response;

/**
 * API 文档（OpenAPI 3.0 JSON / Swagger UI）
 */
class Doc extends BaseController
{
    protected function parseRoutes(): array
    {
        $file = root_path() . 'app' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'route' . DIRECTORY_SEPARATOR . 'app.php';
        $content = @file_get_contents($file);
        if ($content === false) {
            return [];
        }

        $routes = [];
        $lines = preg_split('/\R/', $content);
        foreach ($lines as $line) {
            if (!is_string($line)) continue;
            $trim = trim($line);
            if ($trim === '' || str_starts_with($trim, '//') || str_starts_with($trim, '#')) continue;
            if (!preg_match("/Route::(get|post|any)\\(\\s*'([^']+)'\\s*,\\s*'([^']+)'\\s*\\)/i", $trim, $m)) {
                continue;
            }

            $method = strtolower((string) $m[1]);
            $path = (string) $m[2];
            $target = (string) $m[3];
            $methods = $method === 'any' ? ['get', 'post'] : [$method];

            $tag = '默认';
            if (str_starts_with($path, 'scanwork/')) $tag = '管理端(小程序) Scanwork';
            elseif (str_starts_with($path, 'worker/')) $tag = '员工端 Worker';
            elseif (str_starts_with($path, 'miniapp/')) $tag = '小程序 Miniapp';
            elseif (str_starts_with($path, 'user/')) $tag = 'C端用户 User';
            elseif (str_starts_with($path, 'customer/')) $tag = '客户门户 Customer';
            elseif (str_starts_with($path, 'payment/')) $tag = '支付 Payment';
            elseif (str_starts_with($path, 'ai/')) $tag = 'AI';

            $needAuth = str_contains($trim, 'UserAuth::class') || str_contains($trim, 'AdminAuth::class') || str_contains($trim, 'CustomerAuth::class');

            $pathParams = [];
            if (str_contains($path, ':') && preg_match_all('/\:([a-zA-Z_][a-zA-Z0-9_]*)/', $path, $pm)) {
                foreach ($pm[1] as $pname) {
                    $pathParams[] = (string) $pname;
                }
                $path = preg_replace('/\:([a-zA-Z_][a-zA-Z0-9_]*)/', '{$1}', $path);
            }

            $routes[] = [
                'path' => '/' . ltrim($path, '/'),
                'methods' => $methods,
                'target' => $target,
                'tag' => $tag,
                'need_auth' => $needAuth,
                'path_params' => $pathParams,
            ];
        }

        return $routes;
    }

    protected function buildOpenApi(): array
    {
        $host = $this->request->host();
        $scheme = $this->request->scheme();
        $baseUrl = rtrim($scheme . '://' . $host . $this->request->root(), '/');

        $paths = [];
        foreach ($this->parseRoutes() as $r) {
            $path = (string) ($r['path'] ?? '');
            if ($path === '') continue;
            $ops = [];
            foreach ((array) ($r['methods'] ?? []) as $m) {
                $m = strtolower((string) $m);
                if (!in_array($m, ['get', 'post'], true)) continue;
                $op = $this->buildOperation($path, $m, $r);
                $ops[$m] = $op;
            }
            if (!isset($paths[$path])) $paths[$path] = [];
            $paths[$path] = array_merge($paths[$path], $ops);
        }

        return [
            'openapi' => '3.0.0',
            'info' => [
                'title'   => 'ThinkMes API',
                'version' => '1.0',
                'description' => '统一 API 文档：C端用户、员工端(Worker)、管理端小程序(Scanwork)、小程序租户识别(Miniapp)；请求头可带 X-Tenant-Id 指定租户',
            ],
            'servers' => [['url' => $baseUrl]],
            'tags' => [
                ['name' => '管理端(小程序) Scanwork'],
                ['name' => '员工端 Worker'],
                ['name' => '小程序 Miniapp'],
                ['name' => 'C端用户 User'],
                ['name' => '客户门户 Customer'],
                ['name' => '支付 Payment'],
                ['name' => 'AI'],
                ['name' => '默认'],
            ],
            'paths' => $paths,
            'components' => [
                'schemas' => $this->buildSchemas(),
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Token',
                        'description' => 'Header: Authorization: Bearer <token> 或 query: token=（适用于 user / worker / scanwork / customer）',
                    ],
                ],
            ],
        ];
    }

    protected function buildSchemas(): array
    {
        return [
            'ApiResponse' => [
                'type' => 'object',
                'properties' => [
                    'code' => ['type' => 'integer', 'example' => 1],
                    'msg' => ['type' => 'string', 'example' => 'ok'],
                    'data' => ['type' => 'object'],
                ],
            ],
            'Pagination' => [
                'type' => 'object',
                'properties' => [
                    'total' => ['type' => 'integer', 'example' => 0],
                    'list' => ['type' => 'array', 'items' => ['type' => 'object']],
                ],
            ],
            'MiniappConfig' => [
                'type' => 'object',
                'properties' => [
                    'tenant_id' => ['type' => 'integer', 'example' => 1],
                    'name' => ['type' => 'string', 'example' => '默认租户'],
                ],
            ],
            'TokenResult' => [
                'type' => 'object',
                'properties' => [
                    'token' => ['type' => 'string'],
                ],
            ],
            'WorkerTask' => [
                'type' => 'object',
                'properties' => [
                    'allocation_id' => ['type' => 'integer'],
                    'order_no' => ['type' => 'string'],
                    'order_name' => ['type' => 'string'],
                    'product_name' => ['type' => 'string'],
                    'model_name' => ['type' => 'string'],
                    'process_name' => ['type' => 'string'],
                    'assign_qty' => ['type' => 'integer'],
                    'reported_qty' => ['type' => 'integer'],
                    'pending_qty' => ['type' => 'integer'],
                    'status' => ['type' => 'integer'],
                ],
            ],
            'WorkerDashboard' => [
                'type' => 'object',
                'properties' => [
                    'metrics' => [
                        'type' => 'object',
                        'properties' => [
                            'today_task_count' => ['type' => 'integer'],
                            'today_report_quantity' => ['type' => 'integer'],
                            'today_wage' => ['type' => 'number'],
                            'pending_reports' => ['type' => 'integer'],
                            'unread_notices' => ['type' => 'integer'],
                        ],
                    ],
                    'tasks' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/WorkerTask']],
                ],
            ],
            'UserNotice' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                    'level' => ['type' => 'string', 'example' => 'info'],
                    'is_read' => ['type' => 'integer', 'example' => 0],
                    'read_time' => ['type' => 'integer', 'example' => 0],
                    'create_time' => ['type' => 'integer'],
                    'createtime_text' => ['type' => 'string'],
                ],
            ],
        ];
    }

    protected function buildOperation(string $path, string $method, array $route): array
    {
        $target = (string) ($route['target'] ?? '');
        $tag = (string) ($route['tag'] ?? '默认');
        $needAuth = !empty($route['need_auth']);

        $op = [
            'summary' => $this->summaryFor($path, $method, $target),
            'tags' => [$tag],
            'responses' => [
                '200' => [
                    'description' => 'OK',
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ApiResponse'],
                        ],
                    ],
                ],
            ],
        ];

        if ($needAuth) {
            $op['security'] = [['bearerAuth' => []]];
        }

        $params = [];
        foreach ((array) ($route['path_params'] ?? []) as $pname) {
            $params[] = [
                'name' => (string) $pname,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string'],
            ];
        }

        $extra = $this->routeSpec($path, $method);
        if (!empty($extra['parameters'])) {
            $params = array_merge($params, (array) $extra['parameters']);
        }
        if ($params) {
            $op['parameters'] = $params;
        }
        if (!empty($extra['requestBody'])) {
            $op['requestBody'] = $extra['requestBody'];
        } elseif ($method === 'post') {
            $op['requestBody'] = [
                'required' => false,
                'content' => [
                    'application/json' => [
                        'schema' => ['type' => 'object', 'additionalProperties' => true],
                    ],
                ],
            ];
        }
        if (!empty($extra['responses'])) {
            $op['responses'] = $extra['responses'];
        }
        if (!empty($extra['description'])) {
            $op['description'] = $extra['description'];
        }
        return $op;
    }

    protected function summaryFor(string $path, string $method, string $fallback): string
    {
        $key = $method . ' ' . $path;
        $map = [
            'get /miniapp/getConfig' => '根据小程序 AppID 获取租户配置',
            'post /miniapp/getConfig' => '根据小程序 AppID 获取租户配置',
            'post /miniapp/login' => '小程序登录（微信 code2session）',
            'post /miniapp/bindWithEmployee' => '小程序绑定已有员工（用户名密码）',
            'get /worker/dashboard' => '员工工作台数据（任务/统计）',
            'get /worker/taskInfo' => '员工任务详情',
            'post /worker/report' => '员工报工提交',
            'get /worker/reports' => '员工报工列表',
            'get /worker/reportDetail' => '员工报工详情',
            'get /worker/wages' => '员工工资统计',
            'post /worker/uploadImage' => '员工上传图片',
            'get /worker/notifications' => '员工通知列表',
            'post /worker/readNotifications' => '员工通知标记已读',
            'post /scanwork/adminLogin' => '管理端登录（小程序管理端）',
            'get /scanwork/checkToken' => '管理端 token 校验',
            'get /scanwork/getScanworkMenu' => '管理端菜单权限节点',
            'get /scanwork/getDashboardData' => '管理端仪表盘数据',
            'get /scanwork/getReports' => '管理端报工列表',
            'get /scanwork/getActiveReports' => '管理端待审核报工列表',
            'get /scanwork/getReportDetail' => '管理端报工详情',
            'post /scanwork/auditReport' => '管理端审核报工（通过/拒绝）',
            'get /scanwork/getOrders' => '管理端订单列表',
            'get /scanwork/getOrderDetail' => '管理端订单详情',
            'post /scanwork/createOrder' => '管理端创建订单',
            'post /scanwork/updateOrder' => '管理端更新订单',
            'post /scanwork/deleteOrder' => '管理端删除订单',
        ];
        if (isset($map[$key])) {
            return $map[$key];
        }

        $action = '';
        if ($fallback !== '') {
            $parts = explode('/', $fallback);
            $action = (string) (end($parts) ?: '');
        }
        if ($action === '') {
            $action = trim((string) $this->request->get('action', ''));
        }

        $auto = $this->autoSummaryFromAction($action);
        if ($auto !== '') {
            if (str_starts_with($path, '/scanwork/')) return '管理端' . $auto;
            if (str_starts_with($path, '/worker/')) return '员工端' . $auto;
            if (str_starts_with($path, '/miniapp/')) return '小程序' . $auto;
            if (str_starts_with($path, '/user/')) return '用户端' . $auto;
            if (str_starts_with($path, '/customer/')) return '客户端' . $auto;
            return $auto;
        }

        return $fallback !== '' ? $fallback : ($method . ' ' . $path);
    }

    protected function autoSummaryFromAction(string $action): string
    {
        $action = trim($action);
        if ($action === '') return '';

        $rules = [
            '/^get([A-Z][A-Za-z0-9]*)(List)$/',
            '/^get([A-Z][A-Za-z0-9]*)(Detail)$/',
            '/^create([A-Z][A-Za-z0-9]*)$/',
            '/^update([A-Z][A-Za-z0-9]*)$/',
            '/^delete([A-Z][A-Za-z0-9]*)$/',
            '/^del([A-Z][A-Za-z0-9]*)$/',
            '/^batch([A-Z][A-Za-z0-9]*)$/',
            '/^upload([A-Z][A-Za-z0-9]*)$/',
            '/^generate([A-Z][A-Za-z0-9]*)$/',
        ];

        if (preg_match($rules[0], $action, $m)) {
            return '获取' . $this->entityCn($m[1]) . '列表';
        }
        if (preg_match($rules[1], $action, $m)) {
            return '获取' . $this->entityCn($m[1]) . '详情';
        }
        if (preg_match($rules[2], $action, $m)) {
            return '创建' . $this->entityCn($m[1]);
        }
        if (preg_match($rules[3], $action, $m)) {
            return '更新' . $this->entityCn($m[1]);
        }
        if (preg_match($rules[4], $action, $m) || preg_match($rules[5], $action, $m)) {
            return '删除' . $this->entityCn($m[1]);
        }
        if (preg_match($rules[6], $action, $m)) {
            return '批量操作：' . $this->entityCn($m[1]);
        }
        if (preg_match($rules[7], $action, $m)) {
            return '上传' . $this->entityCn($m[1]);
        }
        if (preg_match($rules[8], $action, $m)) {
            return '生成' . $this->entityCn($m[1]);
        }

        $special = [
            'getDashboardData' => '仪表盘数据',
            'getScanworkMenu' => '菜单权限节点',
            'checkToken' => 'token 校验',
            'adminLogin' => '管理员登录',
            'purchaseInbound' => '采购入库',
            'stockIn' => '库存入库',
            'stockOut' => '库存出库',
            'stockCheck' => '库存盘点',
            'approveBom' => 'BOM 审核',
            'generateQrcode' => '生成二维码',
            'generateTraceCode' => '生成追溯码',
            'queryTraceCode' => '查询追溯码',
        ];
        if (isset($special[$action])) {
            return $special[$action];
        }

        return '';
    }

    protected function entityCn(string $entity): string
    {
        $entity = trim($entity);
        if ($entity === '') return '';
        $map = [
            'Order' => '订单',
            'OrderMaterial' => '订单物料',
            'ProductionPlan' => '生产计划',
            'Allocation' => '分工分配',
            'Report' => '报工',
            'ReportStatistics' => '报工统计',
            'Product' => '产品',
            'ProductModel' => '型号',
            'Model' => '型号',
            'Process' => '工序',
            'ProcessPrice' => '工价',
            'Material' => '物料',
            'Warehouse' => '仓库',
            'Stock' => '库存',
            'StockLog' => '库存流水',
            'Bom' => 'BOM',
            'BomItem' => 'BOM明细',
            'Purchase' => '采购单',
            'PurchaseRequest' => '采购申请',
            'Shipment' => '发货单',
            'Quality' => '质检',
            'Wage' => '工资',
            'TraceCode' => '追溯码',
            'AfterSales' => '售后',
            'Customer' => '客户',
            'Supplier' => '供应商',
            'AuditImage' => '审核图片',
            'AuditVideo' => '审核视频',
            'ReportImage' => '报工图片',
            'TaskByScan' => '扫码任务',
            'User' => '用户',
            'Users' => '用户',
        ];
        if (isset($map[$entity])) return $map[$entity];

        $words = preg_split('/(?=[A-Z])/', $entity, -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) return $entity;
        $out = '';
        foreach ($words as $w) {
            $out .= $map[$w] ?? $w;
        }
        return $out;
    }

    protected function routeSpec(string $path, string $method): array
    {
        $key = $method . ' ' . $path;
        $schemas = [
            'ok_worker_dashboard' => [
                'description' => 'OK',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => ['type' => 'integer'],
                                'msg' => ['type' => 'string'],
                                'data' => ['$ref' => '#/components/schemas/WorkerDashboard'],
                            ],
                        ],
                    ],
                ],
            ],
            'ok_pagination_notice' => [
                'description' => 'OK',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => ['type' => 'integer'],
                                'msg' => ['type' => 'string'],
                                'data' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'total' => ['type' => 'integer'],
                                        'list' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/UserNotice']],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $map = [
            'get /miniapp/getConfig' => [
                'parameters' => [
                    ['name' => 'appid', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string'], 'description' => '微信小程序 AppID'],
                ],
                'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'msg' => ['type' => 'string'], 'data' => ['$ref' => '#/components/schemas/MiniappConfig']]]]]]],
            ],
            'post /miniapp/getConfig' => [
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object', 'required' => ['appid'], 'properties' => ['appid' => ['type' => 'string']]],
                        ],
                    ],
                ],
                'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'msg' => ['type' => 'string'], 'data' => ['$ref' => '#/components/schemas/MiniappConfig']]]]]]],
            ],
            'post /miniapp/login' => [
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['code', 'tenant_id'],
                                'properties' => [
                                    'tenant_id' => ['type' => 'integer', 'description' => '租户ID（可先调 getConfig 获取）'],
                                    'code' => ['type' => 'string', 'description' => '微信登录 code'],
                                    'nickname' => ['type' => 'string'],
                                    'avatar' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
                'description' => '成功返回用户信息与 token；若未绑定员工，会返回 need_bind=true。',
            ],
            'post /miniapp/bindWithEmployee' => [
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['tenant_id', 'code', 'username', 'password'],
                                'properties' => [
                                    'tenant_id' => ['type' => 'integer'],
                                    'code' => ['type' => 'string', 'description' => '微信登录 code'],
                                    'username' => ['type' => 'string', 'description' => '员工账号'],
                                    'password' => ['type' => 'string', 'description' => '员工密码'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'get /worker/dashboard' => [
                'responses' => ['200' => $schemas['ok_worker_dashboard']],
            ],
            'get /worker/taskInfo' => [
                'parameters' => [
                    ['name' => 'allocation_id', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
            ],
            'post /worker/report' => [
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['allocation_id', 'quantity'],
                                'properties' => [
                                    'allocation_id' => ['type' => 'integer'],
                                    'quantity' => ['type' => 'integer', 'description' => '报工数量'],
                                    'remark' => ['type' => 'string'],
                                    'images' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    'item_nos' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '计件追溯码/条码（如有）'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'get /worker/reports' => [
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 20]],
                ],
            ],
            'get /worker/reportDetail' => [
                'parameters' => [
                    ['name' => 'report_id', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
            ],
            'get /worker/wages' => [
                'parameters' => [
                    ['name' => 'from', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string'], 'description' => '起始日期 YYYY-MM-DD'],
                    ['name' => 'to', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string'], 'description' => '结束日期 YYYY-MM-DD'],
                ],
            ],
            'post /worker/uploadImage' => [
                'description' => '上传图片（表单上传），成功返回图片 URL。',
            ],
            'get /worker/notifications' => [
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 20]],
                    ['name' => 'is_read', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'enum' => [0, 1]]],
                ],
                'responses' => ['200' => $schemas['ok_pagination_notice']],
            ],
            'post /worker/readNotifications' => [
                'requestBody' => [
                    'required' => false,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => '不传则标记全部未读为已读'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'post /scanwork/adminLogin' => [
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['username', 'password', 'tenant_id'],
                                'properties' => [
                                    'tenant_id' => ['type' => 'integer'],
                                    'username' => ['type' => 'string'],
                                    'password' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
                'description' => '成功返回 admin_token（用于调用 scanwork 接口）。',
            ],
            'get /scanwork/getOrders' => [
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 10]],
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']],
                ],
            ],
            'get /scanwork/getReports' => [
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 20]],
                    ['name' => 'status', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'description' => '0待审/1通过/2拒绝']],
                ],
            ],
            'get /scanwork/getActiveReports' => [
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 1]],
                    ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'default' => 20]],
                ],
            ],
            'get /scanwork/getReportDetail' => [
                'parameters' => [
                    ['name' => 'report_id', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'integer']],
                ],
            ],
            'post /scanwork/auditReport' => [
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['report_id', 'status'],
                                'properties' => [
                                    'report_id' => ['type' => 'integer'],
                                    'status' => ['type' => 'integer', 'enum' => [1, 2], 'description' => '1通过/2拒绝'],
                                    'audit_reason' => ['type' => 'string', 'description' => '拒绝原因（拒绝必填）'],
                                    'audit_notes' => ['type' => 'string'],
                                    'quality_status' => ['type' => 'integer', 'enum' => [0, 1], 'description' => '0不合格/1合格'],
                                    'audit_images' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    'audit_videos' => ['type' => 'array', 'items' => ['type' => 'string']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $map[$key] ?? [];
    }

    public function index(): Response
    {
        $accept = strtolower((string) $this->request->header('accept', ''));
        $format = strtolower(trim((string) $this->request->get('format', '')));
        if ($format === 'json' || $format === 'openapi' || str_contains($accept, 'application/json')) {
            return $this->spec();
        }

        if (!str_contains($accept, 'text/html')) {
            return $this->spec();
        }

        $host = $this->request->host();
        $scheme = $this->request->scheme();
        $baseUrl = rtrim($scheme . '://' . $host . $this->request->root(), '/');
        $specUrl = $baseUrl . '/doc/spec';

        $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>ThinkMes API Doc</title>'
            . '<link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">'
            . '<style>body{margin:0;background:#fafafa}#swagger-ui{max-width:1200px;margin:0 auto}</style>'
            . '</head><body>'
            . '<div id="swagger-ui"></div>'
            . '<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>'
            . '<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>'
            . '<script>window.onload=function(){'
            . 'if(!window.SwaggerUIBundle){document.getElementById("swagger-ui").innerHTML="Swagger UI 资源加载失败，请检查网络或改用 /api/doc?format=json";return;}'
            . 'SwaggerUIBundle({url:' . json_encode($specUrl, JSON_UNESCAPED_UNICODE) . ',dom_id:"#swagger-ui",deepLinking:true,presets:[SwaggerUIBundle.presets.apis,SwaggerUIStandalonePreset],layout:"BaseLayout"});'
            . '};</script>'
            . '</body></html>';

        return Response::create($html, 'html');
    }

    public function spec(): Response
    {
        $openapi = $this->buildOpenApi();
        return response(json_encode($openapi, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
