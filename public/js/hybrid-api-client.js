(function (global) {
    'use strict';

    function toQueryString(params) {
        if (!params) {
            return '';
        }

        var pairs = [];
        Object.keys(params).forEach(function (key) {
            var value = params[key];
            if (value === undefined || value === null) {
                return;
            }
            pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(String(value)));
        });

        return pairs.join('&');
    }

    function createHybridApiClient(options) {
        options = options || {};

        var baseURL = options.baseURL || '/api/v2';
        var accessTokenKey = options.accessTokenKey || 'task_access_token';
        var refreshTokenKey = options.refreshTokenKey || 'task_refresh_token';
        var storage = options.storage || global.localStorage;

        var accessToken = storage.getItem(accessTokenKey) || '';
        var refreshToken = storage.getItem(refreshTokenKey) || '';
        var isRefreshing = false;
        var pendingQueue = [];

        function setAccessToken(token) {
            accessToken = token || '';
            if (accessToken) {
                storage.setItem(accessTokenKey, accessToken);
            } else {
                storage.removeItem(accessTokenKey);
            }
        }

        function setRefreshToken(token) {
            refreshToken = token || '';
            if (refreshToken) {
                storage.setItem(refreshTokenKey, refreshToken);
            } else {
                storage.removeItem(refreshTokenKey);
            }
        }

        function setTokenPair(pair) {
            setAccessToken(pair && pair.access_token ? pair.access_token : '');
            setRefreshToken(pair && pair.refresh_token ? pair.refresh_token : '');
        }

        function clearTokenPair() {
            setAccessToken('');
            setRefreshToken('');
        }

        function joinUrl(path) {
            if (!path) {
                return baseURL;
            }
            if (/^https?:\/\//i.test(path)) {
                return path;
            }
            if (path.charAt(0) !== '/') {
                path = '/' + path;
            }
            return baseURL + path;
        }

        function getCsrfToken() {
            var tokenNode = document.head.querySelector('meta[name="csrf-token"]');
            return tokenNode ? tokenNode.content : '';
        }

        function buildHeaders(extraHeaders, skipAuth) {
            var headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };

            var csrfToken = getCsrfToken();
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            if (!skipAuth && accessToken) {
                headers.Authorization = 'Bearer ' + accessToken;
            }

            if (extraHeaders) {
                Object.keys(extraHeaders).forEach(function (key) {
                    headers[key] = extraHeaders[key];
                });
            }

            return headers;
        }

        function parseResponseBody(response) {
            return response.text().then(function (text) {
                if (!text) {
                    return {};
                }

                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { raw: text };
                }
            });
        }

        function flushPending(error, nextToken) {
            pendingQueue.forEach(function (item) {
                if (error) {
                    item.reject(error);
                    return;
                }
                item.resolve(nextToken);
            });
            pendingQueue = [];
        }

        function refreshAccessToken() {
            if (!refreshToken) {
                return Promise.reject(new Error('No refresh token available'));
            }

            return fetch(joinUrl('/auth/refresh'), {
                method: 'POST',
                credentials: 'same-origin',
                headers: buildHeaders(null, true),
                body: JSON.stringify({ refresh_token: refreshToken })
            }).then(function (response) {
                return parseResponseBody(response).then(function (body) {
                    if (!response.ok || !body || !body.result || !body.result.access_token || !body.result.refresh_token) {
                        throw new Error('Refresh token failed');
                    }
                    setTokenPair(body.result);
                    return body.result.access_token;
                });
            });
        }

        function request(config) {
            config = config || {};

            var method = (config.method || 'GET').toUpperCase();
            var url = joinUrl(config.url || '/');
            var query = toQueryString(config.params);
            if (query) {
                url += (url.indexOf('?') >= 0 ? '&' : '?') + query;
            }

            var headers = buildHeaders(config.headers, config.skipAuth === true);
            var fetchOptions = {
                method: method,
                credentials: 'same-origin',
                headers: headers
            };

            if (config.body !== undefined && config.body !== null && method !== 'GET' && method !== 'HEAD') {
                fetchOptions.body = typeof config.body === 'string' ? config.body : JSON.stringify(config.body);
            }

            return fetch(url, fetchOptions).then(function (response) {
                return parseResponseBody(response).then(function (data) {
                    if (response.ok) {
                        return {
                            status: response.status,
                            data: data,
                            headers: response.headers
                        };
                    }

                    var error = new Error(data && data.msg ? data.msg : 'Request failed');
                    error.status = response.status;
                    error.data = data;
                    error.config = config;
                    throw error;
                });
            }).catch(function (error) {
                var isAuthRoute = config.url === '/auth/login' || config.url === '/auth/refresh';
                if (error.status !== 401 || isAuthRoute || config.__isRetryRequest) {
                    throw error;
                }

                if (!refreshToken) {
                    clearTokenPair();
                    throw error;
                }

                if (isRefreshing) {
                    return new Promise(function (resolve, reject) {
                        pendingQueue.push({ resolve: resolve, reject: reject });
                    }).then(function () {
                        var retryConfig = Object.assign({}, config, { __isRetryRequest: true });
                        return request(retryConfig);
                    });
                }

                isRefreshing = true;

                return refreshAccessToken().then(function (nextAccessToken) {
                    isRefreshing = false;
                    flushPending(null, nextAccessToken);
                    var retryConfig = Object.assign({}, config, { __isRetryRequest: true });
                    return request(retryConfig);
                }).catch(function (refreshError) {
                    isRefreshing = false;
                    clearTokenPair();
                    flushPending(refreshError);
                    throw refreshError;
                });
            });
        }

        function login(payload) {
            return request({
                method: 'POST',
                url: '/auth/login',
                body: payload || {},
                skipAuth: true
            }).then(function (resp) {
                if (resp.data && resp.data.result) {
                    setTokenPair(resp.data.result);
                }
                return resp;
            });
        }

        function logout() {
            return request({
                method: 'POST',
                url: '/auth/logout'
            }).then(function (resp) {
                clearTokenPair();
                return resp;
            }).catch(function (error) {
                clearTokenPair();
                throw error;
            });
        }

        return {
            login: login,
            logout: logout,
            request: request,
            get: function (url, params, config) {
                config = config || {};
                config.method = 'GET';
                config.url = url;
                config.params = params || {};
                return request(config);
            },
            post: function (url, body, config) {
                config = config || {};
                config.method = 'POST';
                config.url = url;
                config.body = body || {};
                return request(config);
            },
            put: function (url, body, config) {
                config = config || {};
                config.method = 'PUT';
                config.url = url;
                config.body = body || {};
                return request(config);
            },
            del: function (url, config) {
                config = config || {};
                config.method = 'DELETE';
                config.url = url;
                return request(config);
            },
            setTokenPair: setTokenPair,
            clearTokenPair: clearTokenPair,
            getAccessToken: function () { return accessToken; },
            getRefreshToken: function () { return refreshToken; }
        };
    }

    function createTaskApiBridge(apiClient) {
        function fallbackRequest(method, payload, fallback) {
            return new Promise(function(resolve, reject) {
                if (!fallback || !fallback.url) {
                    reject(new Error('No fallback route configured'));
                    return;
                }

                var fallbackMethod = (fallback.method || method || 'GET').toUpperCase();
                var data = payload || {};
                var jq = global.jQuery || global.$;
                if (!jq) {
                    reject(new Error('jQuery is required for fallback request'));
                    return;
                }

                if (fallbackMethod === 'GET') {
                    jq.get(fallback.url, data, resolve).fail(reject);
                    return;
                }

                jq.ajax({
                    url: fallback.url,
                    type: fallbackMethod,
                    data: data,
                    success: resolve,
                    error: reject
                });
            });
        }

        function requestWithFallback(method, apiPath, payload, fallback) {
            var upperMethod = (method || 'GET').toUpperCase();
            var hasApiClient = apiClient && typeof apiClient.request === 'function';
            var allowLegacyFallback = global.__TASK_ALLOW_LEGACY_FALLBACK__ === true;
            var bootstrapPromise = global.__taskTokenBootstrapPromise && typeof global.__taskTokenBootstrapPromise.then === 'function'
                ? global.__taskTokenBootstrapPromise
                : Promise.resolve();

            return bootstrapPromise.then(function () {
                if (!hasApiClient) {
                    if (!allowLegacyFallback) {
                        throw new Error('API client is not initialized');
                    }
                    return fallbackRequest(upperMethod, payload, fallback);
                }

                return apiClient.request({
                    method: upperMethod,
                    url: apiPath,
                    body: upperMethod === 'GET' ? null : (payload || {}),
                    params: upperMethod === 'GET' ? (payload || {}) : {}
                }).then(function (resp) {
                    return resp.data;
                }).catch(function (error) {
                    if (global.__TASK_FORCE_API__ === true || !allowLegacyFallback) {
                        throw error;
                    }
                    return fallbackRequest(upperMethod, payload, fallback);
                });
            });
        }

        return {
            requestWithFallback: requestWithFallback
        };
    }

    global.createHybridApiClient = createHybridApiClient;
    global.TaskApiClient = createHybridApiClient({ baseURL: '/api/v2' });
    global.TaskApiBridge = createTaskApiBridge(global.TaskApiClient);
})(window);
