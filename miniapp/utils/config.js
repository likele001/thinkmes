// 接口基础地址（请改为你的服务器域名，并在小程序后台配置 request 合法域名）
const BASE_URL = 'https://your-domain.com/api';
// 租户ID 兜底：当后台「租户小程序配置」未配置本小程序 AppID 时使用（可选）
const TENANT_ID = 0;

module.exports = {
  BASE_URL,
  TENANT_ID,
};
