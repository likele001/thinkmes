/**
 * 前端 C 端用户：token 存储、API 请求、登录校验
 */
(function () {
    var TOKEN_KEY = 'user_token';
    var USER_KEY = 'user_info';
    var API_BASE = '/api';

    window.Frontend = {
        getToken: function () {
            return localStorage.getItem(TOKEN_KEY) || '';
        },
        setToken: function (token) {
            if (token) localStorage.setItem(TOKEN_KEY, token);
        },
        setUser: function (user) {
            if (user) localStorage.setItem(USER_KEY, JSON.stringify(user));
        },
        getUser: function () {
            try {
                var s = localStorage.getItem(USER_KEY);
                return s ? JSON.parse(s) : null;
            } catch (e) {
                return null;
            }
        },
        clearAuth: function () {
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
        },
        /** 请求 API，自动带 Authorization，返回 Promise */
        api: function (path, options) {
            options = options || {};
            var url = API_BASE + path;
            var headers = options.headers || {};
            var token = this.getToken();
            if (token) headers['Authorization'] = 'Bearer ' + token;
            if (!headers['Content-Type'] && (options.body || options.data)) {
                headers['Content-Type'] = 'application/json';
            }
            var init = {
                method: options.method || 'GET',
                headers: headers
            };
            if (options.body) init.body = options.body;
            else if (options.data) init.body = JSON.stringify(options.data);
            else if (options.formData) {
                init.body = options.formData;
                delete headers['Content-Type'];
            }
            return fetch(url, init).then(function (res) {
                return res.json().then(function (data) {
                    if (res.status === 401) {
                        Frontend.clearAuth();
                        if (options.noRedirect !== true) {
                            window.location.href = '/index/user/login';
                            return Promise.reject(new Error('请先登录'));
                        }
                    }
                    return data;
                });
            });
        },
        /** 未登录则跳转登录页 */
        requireLogin: function () {
            if (!this.getToken()) {
                window.location.href = '/index/user/login';
                return false;
            }
            return true;
        }
    };
})();
