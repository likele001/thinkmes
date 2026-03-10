// ========== 小程序本地配置（必改） ==========
// 接口基础地址：改成你的 MES 后端 API 地址（需 https），并在微信公众平台「开发 - 开发管理 - 开发设置 - 服务器域名」里把该域名加入 request 合法域名
const BASE_URL = 'https://mes.user.023ent.net/api';
// 租户ID 兜底：当后台「租户小程序配置」里还没配置本小程序的 AppID 时，用这个租户ID（0 表示不兜底，必须后台配置）
const TENANT_ID = 0;

module.exports = {
  BASE_URL,
  TENANT_ID,
};
