// 员工端小程序 - 接口基础地址（请改为你的服务器域名，并在小程序后台配置 request 合法域名）
const BASE_URL = 'https://your-domain.com/api';
// 租户ID（若后端通过扫码/链接带 tenant_id 则可不固定；否则每个租户部署时改此值）
const TENANT_ID = 1;

module.exports = {
  BASE_URL,
  TENANT_ID,
};
