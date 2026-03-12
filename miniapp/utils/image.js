/**
 * 将接口返回的相对路径转为小程序可用的完整 HTTPS 地址。
 * 小程序 <image src> 必须是网络图片（https）或本地路径，不能是相对路径如 /uploads/...
 * 同时去掉 PC 端/后端可能带上的首尾引号 " '，避免请求 404。
 */
function toFullImageUrl(url) {
  if (url == null || typeof url !== 'string') return url;
  let s = url.trim();
  while (s.length && (s.startsWith('"') || s.startsWith("'"))) s = s.slice(1);
  while (s.length && (s.endsWith('"') || s.endsWith("'"))) s = s.slice(0, -1);
  if (!s) return url;
  if (s.startsWith('http://') || s.startsWith('https://')) return s;
  let base = '';
  try {
    base = getApp().globalData.baseUrl || require('./config.js').BASE_URL || '';
  } catch (e) {}
  const origin = base.replace(/\/api\/?$/, '');
  return origin + (s.startsWith('/') ? s : '/' + s);
}

function toFullImageUrls(arr) {
  if (!Array.isArray(arr)) return arr;
  return arr.map((u) => toFullImageUrl(u)).filter(Boolean);
}

module.exports = {
  toFullImageUrl,
  toFullImageUrls,
};
