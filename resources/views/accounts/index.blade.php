@extends('layouts.app')

@section('title', '账户配置 - 蒙太奇')
@section('description', '管理您的第三方账户授权和连接设置')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-user-cog text-primary-color mr-3"></i>
                账户配置
            </h1>
            <p class="text-gray-600 mt-2">管理您的第三方服务授权和账户连接</p>
        </div>

        <!-- 主卡片 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-shield-alt text-gray-500 mr-2"></i>
                    第三方服务授权
                </h2>
                <p class="text-gray-600 text-sm mt-1">连接您的第三方账户以增强功能体验</p>
            </div>

            <div class="p-6">
                <!-- 授权服务列表 -->
                <div id="oauthServiceList" class="space-y-6">
                    <div class="text-sm text-gray-500 py-3">加载授权状态中...</div>
                </div>

                <!-- 授权说明 -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-800 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            授权说明
                        </h4>
                        <ul class="mt-2 space-y-2 text-sm text-blue-700">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-500"></i>
                                <span>授权后，您可以在蒙太奇中使用对应服务的功能</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-500"></i>
                                <span>我们只会获取必要的账户信息，不会访问您的隐私数据</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle mr-2 mt-0.5 text-blue-500"></i>
                                <span>您可以随时取消授权，取消后相关功能将无法使用</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- 其他设置卡片（占位，可根据需求扩展） -->
        <div class="card mt-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-cogs text-gray-500 mr-2"></i>
                    账户安全设置
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 密码修改 -->
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">登录密码</h3>
                                <p class="text-sm text-gray-600 mt-1">定期修改密码保障账户安全</p>
                            </div>
                            <a href="{{ url('/password/reset') }}" class="btn btn-sm btn-outline">
                                <i class="fas fa-key mr-1"></i>
                                修改密码
                            </a>
                        </div>
                    </div>

                    <!-- 双重验证 -->
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">双重验证</h3>
                                <p class="text-sm text-gray-600 mt-1">增强账户登录安全性</p>
                            </div>
                            <button class="btn btn-sm btn-secondary">
                                <i class="fas fa-mobile-alt mr-1"></i>
                                设置2FA
                            </button>
                        </div>
                    </div>

                    <!-- 登录历史 -->
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">登录历史</h3>
                                <p class="text-sm text-gray-600 mt-1">查看最近的登录记录</p>
                            </div>
{{--                            <a href="{{ route('login.history') }}" class="btn btn-sm btn-outline">--}}
{{--                                <i class="fas fa-history mr-1"></i>--}}
{{--                                查看记录--}}
{{--                            </a>--}}
                        </div>
                    </div>

                    <!-- 会话管理 -->
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">活跃会话</h3>
                                <p class="text-sm text-gray-600 mt-1">管理当前登录的设备</p>
                            </div>
{{--                            <a href="{{ route('sessions.manage') }}" class="btn btn-sm btn-outline">--}}
{{--                                <i class="fas fa-desktop mr-1"></i>--}}
{{--                                管理会话--}}
{{--                            </a>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 数据管理 -->
        <div class="card mt-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-database text-gray-500 mr-2"></i>
                    数据管理
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900">数据导出</h3>
                            <p class="text-sm text-gray-600">导出您的个人数据备份</p>
                        </div>
                        <button class="btn btn-outline">
                            <i class="fas fa-download mr-2"></i>
                            导出数据
                        </button>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900 text-red-600">删除账户</h3>
                                <p class="text-sm text-gray-600">永久删除账户及所有数据</p>
                            </div>
                            <button class="btn btn-outline text-red-600 border-red-600 hover:bg-red-50" onclick="showDeleteConfirm()">
                                <i class="fas fa-trash-alt mr-2"></i>
                                删除账户
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <script>
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;

            function renderOauthServices(oauths) {
                var listEl = document.getElementById('oauthServiceList');
                if (!listEl) {
                    return;
                }

                var serviceInfo = {
                    github: { name: 'GitHub', icon: 'fab fa-github', color: 'text-gray-900', bgColor: 'bg-gray-900', description: '代码仓库和开发者服务' },
                    google: { name: 'Google', icon: 'fab fa-google', color: 'text-red-500', bgColor: 'bg-red-500', description: '谷歌账户和云服务' },
                    weibo: { name: '微博', icon: 'fab fa-weibo', color: 'text-red-400', bgColor: 'bg-red-400', description: '微博社交账户' },
                    qq: { name: 'QQ', icon: 'fab fa-qq', color: 'text-blue-500', bgColor: 'bg-blue-500', description: 'QQ社交账户' },
                    wechat: { name: '微信', icon: 'fab fa-weixin', color: 'text-green-500', bgColor: 'bg-green-500', description: '微信账户' },
                    microsoft: { name: 'Microsoft', icon: 'fab fa-microsoft', color: 'text-blue-600', bgColor: 'bg-blue-600', description: '微软账户和Office服务' }
                };
                var keys = Object.keys(serviceInfo);

                listEl.innerHTML = keys.map(function(key) {
                    var service = serviceInfo[key];
                    var connected = !!(oauths && oauths[key] && Object.keys(oauths[key]).length > 0);
                    var btnClass = connected ? 'btn-outline' : 'btn-primary';
                    var btnIcon = connected ? 'fas fa-sync-alt' : 'fas fa-link';
                    var btnText = connected ? '重新授权' : '立即连接';
                    var statusHtml = connected
                        ? '<div class="flex items-center mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>已授权</span></div>'
                        : '';

                    return ''
                        + '<div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">'
                        + '<div class="flex items-center space-x-4">'
                        + '<div class="w-12 h-12 rounded-lg ' + service.bgColor + ' bg-opacity-10 flex items-center justify-center">'
                        + '<i class="' + service.icon + ' text-xl ' + service.color + '"></i></div>'
                        + '<div><h3 class="font-semibold text-gray-900">' + service.name + '</h3>'
                        + '<p class="text-sm text-gray-600">' + service.description + '</p>' + statusHtml + '</div></div>'
                        + '<div><a href="/login/third/' + key + '" class="btn ' + btnClass + ' flex items-center" title="' + btnText + '">'
                        + '<i class="' + btnIcon + ' mr-2"></i>' + btnText + '</a></div>'
                        + '</div>';
                }).join('');
            }

            function loadOauthServicesFromApi() {
                if (!apiRequest) {
                    return;
                }
                apiRequest('GET', '/accounts', {}).then(function(resp) {
                    if (resp.code !== 9999 || !resp.result) {
                        return;
                    }
                    renderOauthServices(resp.result.oauths || {});
                }).catch(function(err) {
                    console.error('load account oauths failed:', err);
                });
            }

            // 删除账户确认
            function showDeleteConfirm() {
                if (confirm('⚠️ 警告：此操作将永久删除您的账户及所有数据，且无法恢复！\n\n确定要删除账户吗？')) {
                    // 这里可以添加实际的删除逻辑
                    alert('账户删除功能需要进一步确认。');
                }
            }

            // 显示提示消息
            function showToast(type, message) {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 5000);
            }

            document.addEventListener('DOMContentLoaded', function() {
                loadOauthServicesFromApi();
            });
        </script>
    @endsection
