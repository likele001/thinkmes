<?php
declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use think\Response;

/**
 * API 文档（OpenAPI 3.0 JSON），供 Swagger UI 等使用
 */
class Doc extends BaseController
{
    public function index(): Response
    {
        $host = $this->request->host();
        $scheme = $this->request->scheme();
        $baseUrl = $scheme . '://' . $host . $this->request->root();

        $openapi = [
            'openapi' => '3.0.0',
            'info' => [
                'title'   => 'ThinkMes API',
                'version' => '1.0',
                'description' => 'C端用户注册/登录/找回密码等接口；请求头可带 X-Tenant-Id 指定租户',
            ],
            'servers' => [['url' => rtrim($baseUrl, '/')]],
            'paths' => [
                '/api/index/index' => [
                    'get' => [
                        'summary' => 'API 首页',
                        'tags' => ['默认'],
                        'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => ['code' => ['type' => 'integer'], 'msg' => ['type' => 'string'], 'data' => ['type' => 'object']]]]]]],
                    ],
                ],
                '/api/user/register' => [
                    'post' => [
                        'summary' => '用户注册',
                        'tags' => ['用户'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['username', 'password'],
                                        'properties' => [
                                            'username' => ['type' => 'string', 'description' => '用户名 2-50'],
                                            'password' => ['type' => 'string', 'description' => '密码 6-32'],
                                            'nickname' => ['type' => 'string'],
                                            'mobile' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => '成功返回 token 与用户信息']],
                    ],
                ],
                '/api/user/login' => [
                    'post' => [
                        'summary' => '用户登录',
                        'tags' => ['用户'],
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'username' => ['type' => 'string'],
                                            'mobile' => ['type' => 'string'],
                                            'password' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => '成功返回 token 与用户信息']],
                    ],
                ],
                '/api/user/profile' => [
                    'get' => ['summary' => '个人资料(需登录)', 'tags' => ['用户'], 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => '个人资料(需登录)', 'tags' => ['用户'], 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/user/logout' => [
                    'get' => ['summary' => '登出', 'tags' => ['用户'], 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'OK']]],
                    'post' => ['summary' => '登出', 'tags' => ['用户'], 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'OK']]],
                ],
                '/api/user/forgot' => [
                    'post' => [
                        'summary' => '找回密码-发送验证码',
                        'tags' => ['用户'],
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'mobile' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
                '/api/user/resetPassword' => [
                    'post' => [
                        'summary' => '找回密码-重置',
                        'tags' => ['用户'],
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['code', 'password'],
                                        'properties' => [
                                            'mobile' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                            'code' => ['type' => 'string', 'description' => '6位验证码'],
                                            'password' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Token',
                        'description' => 'Header: Authorization: Bearer <token> 或 query: token=',
                    ],
                ],
            ],
        ];

        return response(json_encode($openapi, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
