## OpenClaw Server（最小可用服务端）

用于给餐饮系统提供 OpenClaw 服务端接口（无依赖，PHP 内置 server 可直接跑）。

### 运行

```bash
export OC_API_KEY="your_api_key"
export OC_DATA_DIR="/tmp/openclaw_data"
php -S 0.0.0.0:9501 openclaw_server/router.php
```

餐饮后台 OpenClaw 设置里把：
- API Base 填 `http://你的OpenClaw服务器:9501`
- API Key 填 `your_api_key`

如果你的 OpenClaw 控制台已经占用了 18789，且 `curl http://127.0.0.1:18789/api/ping` 返回 Not Found，说明 18789 不是 API 端口，需要单独跑 API（例如 9501），或用 Nginx 反代把 `/api/` 转到 9501。

OpenClaw Gateway 默认会提供 `GET /health`（返回 `{"ok":true,"status":"live"}`），但不会提供餐饮对接需要的 `/api/restaurant/*`。要让餐饮后台把 API Base 填成 `http://127.0.0.1:18789`，需要用 Nginx 把指定的 `/api/restaurant/*`、`/api/install`、`/api/ingest/summary` 等转发到本服务（9501），且不要劫持 OpenClaw 自己的 `/api/channels/*`。

### 已实现接口

- GET `/api/ping`
- POST `/api/install`
- POST `/api/ingest/summary`
- GET `/api/restaurant/reviews?tenant_id=1&since=2026-03-01&until=2026-03-02`
- POST `/api/restaurant/reviews/reply`
- POST `/api/restaurant/alerts`

### 测试数据写入

```bash
curl -s -X POST "http://127.0.0.1:9501/api/restaurant/reviews/seed" \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{"tenant_id":1,"list":[{"platform":"meituan","external_id":"m1","store_id":1,"rating":2,"content":"出餐慢，太咸","review_time":1710000000}]}'
```

### systemd 模板

见 `openclaw_server/systemd/openclaw-api.service`，端口和 API_KEY 按需改。

### Nginx 反代模板

见 `openclaw_server/nginx/openclaw_api_proxy.conf`，把 `/api/` 转发到 9501。
