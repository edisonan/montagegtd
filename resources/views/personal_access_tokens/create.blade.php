@extends('layouts.app')

@section('title', '创建访问令牌 - 蒙太奇')
@section('description', '创建新的Personal Access Token，为第三方应用或脚本配置API访问权限。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和操作栏 -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">创建访问令牌</h1>
                    <p class="text-gray-600 mt-1">为第三方应用创建API访问凭证</p>
                </div>

                <a href="{{ route('personal-access-tokens.index') }}" class="btn btn-secondary self-start">
                    <i class="fas fa-arrow-left mr-2"></i>
                    返回列表
                </a>
            </div>

            <!-- 面包屑导航 -->
            <div class="flex items-center gap-2 mt-4 text-sm">
                <a href="{{ url('/index') }}" class="text-gray-500 hover:text-gray-700">
                    首页
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <a href="{{ route('personal-access-tokens.index') }}" class="text-gray-500 hover:text-gray-700">
                    访问令牌
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <span class="text-gray-700 font-medium">创建新令牌</span>
            </div>
        </div>

        <div class="max-w-5xl mx-auto">
            <!-- 主表单卡片 -->
            <div class="card mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-gray-700 to-gray-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-key text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">创建 Personal Access Token</h2>
                            <p class="text-sm text-gray-500 mt-1">配置令牌名称、权限范围和过期时间</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- 错误提示 -->
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                                <div>
                                    <h4 class="font-medium text-red-800 mb-1">表单填写有误</h4>
                                    <ul class="text-sm text-red-700 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 成功消息 -->
                    @if (session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                                <div class="text-sm text-green-700">
                                    {{ session('success') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="javascript:void(0)" method="POST" id="tokenForm" class="space-y-6">
                        {!! csrf_field() !!}

                        <!-- 令牌名称 -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                令牌名称
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="例如：GitHub集成、移动应用、脚本工具..."
                                   class="input w-full"
                                   autofocus>
                            <div class="flex items-start gap-2 mt-1">
                                <i class="fas fa-lightbulb text-gray-400 mt-0.5"></i>
                                <p class="text-xs text-gray-500">建议使用用途或应用名称，方便后续识别和管理</p>
                            </div>
                        </div>

                        <!-- 权限范围 -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700">
                                    权限范围
                                    <span class="text-xs text-gray-400">（可多选）</span>
                                </label>
                                <button type="button" id="selectAllScopes" class="text-xs text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-check-double mr-1"></i>全选
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @php
                                    $scopes = [
                                        'read' => [
                                            'label' => '读取权限',
                                            'desc' => '允许查看数据，包括文章、任务、订阅源等',
                                            'icon' => 'fas fa-eye'
                                        ],
                                        'write' => [
                                            'label' => '写入权限',
                                            'desc' => '允许创建和修改数据，如添加文章、更新任务',
                                            'icon' => 'fas fa-edit'
                                        ],
                                        'delete' => [
                                            'label' => '删除权限',
                                            'desc' => '允许删除数据，包括文章、任务、订阅源等',
                                            'icon' => 'fas fa-trash-alt'
                                        ],
                                        'admin' => [
                                            'label' => '管理权限',
                                            'desc' => '完全控制权限，包括账户设置和所有数据',
                                            'icon' => 'fas fa-cog'
                                        ],
                                        'code:execute' => [
                                            'label' => 'Code 执行',
                                            'desc' => '允许调用配置为 PAT 鉴权的 Code 应用',
                                            'icon' => 'fas fa-terminal'
                                        ]
                                    ];

                                    $oldScopes = old('scopes', []);
                                @endphp

                                @foreach($scopes as $scope => $info)
                                    <label class="cursor-pointer block scope-option" data-scope="{{ $scope }}">
                                        <input type="checkbox"
                                               name="scopes[]"
                                               value="{{ $scope }}"
                                               {{ in_array($scope, $oldScopes) ? 'checked' : '' }}
                                               class="scope-checkbox sr-only">
                                        <div class="scope-card p-4 rounded-lg border-2 border-gray-200 h-full transition-all duration-200">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0">
                                                    <div class="scope-icon w-8 h-8 bg-gray-100 rounded flex items-center justify-center transition-colors">
                                                        <i class="{{ $info['icon'] }} text-gray-500"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="scope-title font-medium text-gray-800">
                                                        {{ $info['label'] }}
                                                        <span class="text-xs font-normal text-gray-500">({{ $scope }})</span>
                                                    </div>
                                                    <div class="scope-desc text-xs text-gray-500 mt-2">
                                                        {{ $info['desc'] }}
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0 ml-2">
                                                    <div class="scope-check" aria-hidden="true">
                                                        <span class="scope-check-mark"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="flex items-start gap-2">
                                <i class="fas fa-shield-alt text-gray-400 mt-0.5"></i>
                                <p class="text-xs text-gray-500">
                                    <strong>安全提示：</strong>遵循最小权限原则，只授予必要的权限。避免为不信任的应用授予删除或管理权限。
                                </p>
                            </div>
                        </div>

                        <!-- 过期时间 -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">
                                过期时间
                                <span class="text-xs text-gray-400">（可选）</span>
                            </label>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                <!-- 预设选项 -->
                                <div class="space-y-2 md:col-span-3">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @php
                                            $expiryOptions = [
                                                '' => ['label' => '永不过期', 'color' => 'bg-gray-100 hover:bg-gray-200'],
                                                '1' => ['label' => '1天后', 'color' => 'bg-blue-100 hover:bg-blue-200'],
                                                '7' => ['label' => '7天后', 'color' => 'bg-green-100 hover:bg-green-200'],
                                                '30' => ['label' => '30天后', 'color' => 'bg-yellow-100 hover:bg-yellow-200'],
                                                '90' => ['label' => '90天后', 'color' => 'bg-orange-100 hover:bg-orange-200'],
                                                '365' => ['label' => '1年后', 'color' => 'bg-red-100 hover:bg-red-200'],
                                            ];
                                            $selectedExpiry = old('expiry_preset', '');
                                        @endphp

                                        @foreach($expiryOptions as $value => $option)
                                            <label class="cursor-pointer">
                                                <input type="radio"
                                                       name="expiry_preset"
                                                       value="{{ $value }}"
                                                       {{ $selectedExpiry == $value ? 'checked' : '' }}
                                                       class="peer sr-only expiry-preset">
                                                <span class="block px-3 py-2 text-center text-sm font-medium rounded-lg {{ $option['color'] }} text-gray-700 peer-checked:ring-2 peer-checked:ring-blue-500">
                                            {{ $option['label'] }}
                                        </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- 自定义日期 -->
                                <div class="space-y-2">
                                    <label class="block text-xs font-medium text-gray-700">
                                        自定义日期
                                    </label>
                                    <input type="date"
                                           id="expires_at_date"
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ old('expires_at_date', date('Y-m-d', strtotime('+30 days'))) }}"
                                           class="input w-full">
                                </div>
                            </div>

                            <!-- 自定义时间（仅在自定义日期选择时显示） -->
                            <div id="customTimeGroup" class="hidden">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-medium text-gray-700">
                                            时间
                                        </label>
                                        <input type="time"
                                               id="expires_at_time"
                                               value="{{ old('expires_at_time', '23:59') }}"
                                               class="input w-full">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-xs font-medium text-gray-700">
                                            时区
                                        </label>
                                        <select class="input w-full" disabled>
                                            <option>UTC+8 (北京时间)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- 隐藏字段 -->
                            <input type="hidden"
                                   id="expires_at"
                                   name="expires_at"
                                   value="{{ old('expires_at') }}">

                            <div class="flex items-start gap-2">
                                <i class="fas fa-calendar-alt text-gray-400 mt-0.5"></i>
                                <p class="text-xs text-gray-500">
                                    设置过期时间可以提高安全性。对于临时用途或测试环境，建议设置较短的过期时间。
                                </p>
                            </div>
                        </div>

                        <!-- 表单按钮 -->
                        <div class="pt-6 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <button type="submit" class="btn btn-primary flex-1">
                                    <i class="fas fa-plus mr-2"></i>
                                    创建令牌
                                </button>

                                <div class="flex gap-2">
                                    <a href="{{ route('personal-access-tokens.index') }}" class="btn btn-secondary flex-1">
                                        <i class="fas fa-times mr-2"></i>
                                        取消
                                    </a>

                                    <button type="button"
                                            id="resetForm"
                                            class="btn btn-outline flex-1">
                                        <i class="fas fa-redo mr-2"></i>
                                        重置
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 帮助卡片 -->
            <div class="card">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-question-circle text-white"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">创建说明</h2>
                            <p class="text-sm text-gray-500 mt-1">了解如何正确创建和使用访问令牌</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h4 class="font-medium text-gray-700">
                                <i class="fas fa-lightbulb text-blue-500 mr-2"></i>
                                何时需要创建访问令牌？
                            </h4>
                            <ul class="text-sm text-gray-600 space-y-1 pl-5">
                                <li>• 为第三方应用（如移动应用、浏览器扩展）提供API访问</li>
                                <li>• 自动化脚本需要访问您的账户数据</li>
                                <li>• 集成其他服务，如GitHub Actions、Zapier等</li>
                                <li>• 临时授权给团队成员或合作者</li>
                            </ul>
                        </div>

                        <div class="space-y-2">
                            <h4 class="font-medium text-gray-700">
                                <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                                安全最佳实践
                            </h4>
                            <ul class="text-sm text-gray-600 space-y-1 pl-5">
                                <li>• 为每个应用创建独立的令牌</li>
                                <li>• 使用描述性的名称，便于后续管理</li>
                                <li>• 遵循最小权限原则，只授予必要的权限</li>
                                <li>• 为临时用途设置过期时间</li>
                                <li>• 定期审查并清理不再使用的令牌</li>
                            </ul>
                        </div>

                        <div class="space-y-2">
                            <h4 class="font-medium text-gray-700">
                                <i class="fas fa-code text-purple-500 mr-2"></i>
                                如何使用令牌？
                            </h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>创建成功后，令牌将显示一次。在API请求中使用：</p>
                                <div class="mt-2 p-3 bg-gray-800 text-gray-100 rounded font-mono text-xs overflow-x-auto">
                                    <span class="text-green-400">curl</span> -H <span class="text-yellow-300">"Authorization: Bearer YOUR_TOKEN"</span> \<br>
                                    <span class="ml-4">https://api.yoursite.com/v1/endpoint</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 创建成功模态框 -->
    <div id="successModal" class="modal">
        <div class="modal-content max-w-lg">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">令牌创建成功</h3>
                    <p class="text-sm text-gray-500 mt-1">请立即复制并妥善保存您的令牌</p>
                </div>
                <button type="button" class="modal-close p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- 警告信息 -->
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                        <div class="text-sm">
                            <h4 class="font-medium text-red-800 mb-1">重要提醒</h4>
                            <p class="text-red-700">
                                此令牌仅显示一次，请立即复制并保存到安全的地方。离开此页面后将无法再次查看。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 令牌显示 -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">您的访问令牌</label>
                    <div class="relative">
                        <input type="text"
                               id="newTokenValue"
                               readonly
                               class="input w-full pr-10 bg-gray-50 font-mono text-sm">
                        <button type="button"
                                onclick="copyToken()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 p-1.5 text-gray-400 hover:text-blue-500 rounded">
                            <i class="fas fa-copy text-lg"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">点击右侧图标或使用快捷键 Ctrl+C 复制令牌</p>
                </div>

                <!-- 使用说明 -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                        <div class="text-sm">
                            <h4 class="font-medium text-blue-800 mb-1">下一步操作</h4>
                            <ul class="text-blue-700 space-y-1">
                                <li>• 将令牌安全地存储在密码管理器或安全的地方</li>
                                <li>• 在您的应用中配置令牌，开始使用API</li>
                                <li>• 可以在令牌列表页面管理所有已创建的令牌</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6 mt-6 border-t border-gray-200">
                <button type="button" class="modal-close btn btn-secondary flex-1">
                    <i class="fas fa-times mr-2"></i>关闭
                </button>
                <button type="button" onclick="copyToken()" class="btn btn-primary flex-1">
                    <i class="fas fa-copy mr-2"></i>复制令牌
                </button>
            </div>
        </div>
    </div>

    <style>
        /* ===== 权限范围卡片（自定义选中样式，兼容无法依赖 Tailwind peer 变体的场景）===== */

        /* 右侧复选方框：尺寸与样式写在 CSS 内，不依赖 Tailwind/字体图标 */
        .scope-option .scope-check {
            width: 22px;
            height: 22px;
            border: 2px solid #9ca3af;
            border-radius: 5px;
            background-color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        /* 纯 CSS 绘制的白色对勾（默认隐藏） */
        .scope-option .scope-check .scope-check-mark {
            display: none;
            width: 11px;
            height: 6px;
            border-left: 2.5px solid #ffffff;
            border-bottom: 2.5px solid #ffffff;
            transform: rotate(-45deg);
            margin-top: -3px;
        }

        /* 卡片悬停效果 */
        .scope-option .scope-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* 选中状态：卡片高亮 + 边框 */
        .scope-option .scope-checkbox:checked + .scope-card {
            border-width: 2px;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* 选中后图标圆形底色与图标颜色 */
        .scope-option .scope-checkbox:checked + .scope-card .scope-icon {
            background-color: #dbeafe;
        }
        .scope-option .scope-checkbox:checked + .scope-card .scope-icon i {
            color: #2563eb;
        }

        /* 选中后标题加粗变深 */
        .scope-option .scope-checkbox:checked + .scope-card .scope-title {
            color: #111827;
            font-weight: 600;
        }

        /* 选中后右侧复选方框：填充主题色并显示纯 CSS 对勾 */
        .scope-option .scope-checkbox:checked + .scope-card .scope-check {
            background-color: var(--primary-color, #1744e0);
            border-color: var(--primary-color, #1744e0);
        }
        .scope-option .scope-checkbox:checked + .scope-card .scope-check .scope-check-mark {
            display: block;
        }

        /* 各权限对应的选中主题色（保持原设计的颜色区分） */
        .scope-option[data-scope="read"] .scope-checkbox:checked + .scope-card {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .scope-option[data-scope="read"] .scope-checkbox:checked + .scope-card .scope-check {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .scope-option[data-scope="write"] .scope-checkbox:checked + .scope-card {
            border-color: #22c55e;
            background-color: #f0fdf4;
        }
        .scope-option[data-scope="write"] .scope-checkbox:checked + .scope-card .scope-check {
            background-color: #22c55e;
            border-color: #22c55e;
        }
        .scope-option[data-scope="delete"] .scope-checkbox:checked + .scope-card {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
        .scope-option[data-scope="delete"] .scope-checkbox:checked + .scope-card .scope-check {
            background-color: #ef4444;
            border-color: #ef4444;
        }
        .scope-option[data-scope="admin"] .scope-checkbox:checked + .scope-card {
            border-color: #a855f7;
            background-color: #faf5ff;
        }
        .scope-option[data-scope="admin"] .scope-checkbox:checked + .scope-card .scope-check {
            background-color: #a855f7;
            border-color: #a855f7;
        }
        .scope-option[data-scope="code:execute"] .scope-checkbox:checked + .scope-card {
            border-color: #f97316;
            background-color: #fff7ed;
        }
        .scope-option[data-scope="code:execute"] .scope-checkbox:checked + .scope-card .scope-check {
            background-color: #f97316;
            border-color: #f97316;
        }

        /* 过期时间选项选中效果 */
        .expiry-preset:checked + span {
            background-color: var(--primary-color);
            color: white;
        }

        /* 模态框样式 */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .modal.show .modal-content {
            transform: translateY(0);
        }

        /* 表单过渡效果 */
        .input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : null;

            // 模态框管理
            const successModal = document.getElementById('successModal');
            const modalCloseButtons = document.querySelectorAll('.modal-close');

            // 打开成功模态框
            function openSuccessModal(tokenValue) {
                document.getElementById('newTokenValue').value = tokenValue;
                successModal.classList.add('show');
            }

            // 关闭模态框
            function closeModal() {
                successModal.classList.remove('show');
            }

            // 绑定关闭按钮事件
            modalCloseButtons.forEach(button => {
                button.addEventListener('click', closeModal);
            });

            // 点击模态框背景关闭
            successModal.addEventListener('click', function(e) {
                if (e.target === successModal) {
                    closeModal();
                }
            });

            // ESC键关闭模态框
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && successModal.classList.contains('show')) {
                    closeModal();
                }
            });

            // 权限全选功能
            const selectAllBtn = document.getElementById('selectAllScopes');
            const scopeCheckboxes = document.querySelectorAll('input[name="scopes[]"]');

            if (selectAllBtn) {
                let allSelected = false;

                selectAllBtn.addEventListener('click', function() {
                    allSelected = !allSelected;
                    scopeCheckboxes.forEach(checkbox => {
                        checkbox.checked = allSelected;
                        // 触发change事件以更新样式
                        checkbox.dispatchEvent(new Event('change'));
                    });

                    this.innerHTML = allSelected ?
                        '<i class="fas fa-times mr-1"></i>取消全选' :
                        '<i class="fas fa-check-double mr-1"></i>全选';
                });
            }

            // 过期时间处理
            const expiryPresets = document.querySelectorAll('.expiry-preset');
            const expiresAtDate = document.getElementById('expires_at_date');
            const expiresAtTime = document.getElementById('expires_at_time');
            const customTimeGroup = document.getElementById('customTimeGroup');
            const expiresAtHidden = document.getElementById('expires_at');

            // 初始化日期选择器
            if (expiresAtDate) {
                // 设置最小日期为今天
                expiresAtDate.min = new Date().toISOString().split('T')[0];

                // 监听日期变化
                expiresAtDate.addEventListener('change', function() {
                    updateExpiresAtHidden();
                    customTimeGroup.classList.remove('hidden');

                    // 如果选择了自定义日期，取消选择预设
                    expiryPresets.forEach(preset => {
                        if (preset.checked) {
                            preset.checked = false;
                        }
                    });
                });
            }

            // 监听预设选项变化
            expiryPresets.forEach(preset => {
                preset.addEventListener('change', function() {
                    if (this.checked) {
                        const value = this.value;
                        if (value) {
                            const days = parseInt(value);
                            const expiryDate = new Date();
                            expiryDate.setDate(expiryDate.getDate() + days);

                            // 更新日期选择器
                            if (expiresAtDate) {
                                expiresAtDate.value = expiryDate.toISOString().split('T')[0];
                            }
                            if (expiresAtTime) {
                                expiresAtTime.value = '23:59';
                            }

                            customTimeGroup.classList.remove('hidden');
                            updateExpiresAtHidden();
                        } else {
                            // 永不过期
                            expiresAtHidden.value = '';
                            customTimeGroup.classList.add('hidden');
                        }
                    }
                });
            });

            // 监听时间变化
            if (expiresAtTime) {
                expiresAtTime.addEventListener('change', updateExpiresAtHidden);
            }

            // 更新隐藏字段的值
            function updateExpiresAtHidden() {
                if (expiresAtDate.value && expiresAtTime.value) {
                    const datetime = new Date(`${expiresAtDate.value}T${expiresAtTime.value}:00`);
                    expiresAtHidden.value = datetime.toISOString();
                }
            }

            // 表单提交处理
            const tokenForm = document.getElementById('tokenForm');
            const submitBtn = tokenForm?.querySelector('button[type="submit"]');

            if (tokenForm && submitBtn) {
                tokenForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // 表单验证
                    const nameInput = document.getElementById('name');
                    if (!nameInput.value.trim()) {
                        nameInput.focus();
                        showNotification('warning', '请输入令牌名称');
                        return false;
                    }

                    const selectedScopes = Array.from(scopeCheckboxes).filter(cb => cb.checked);
                    if (selectedScopes.length === 0) {
                        showNotification('warning', '请至少选择一个权限范围');
                        return false;
                    }

                    // 显示加载状态
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>创建中...';

                    // 提交表单
                    const formData = new FormData(tokenForm);

                    // 如果选择永不过期，清空expires_at字段
                    const selectedPreset = document.querySelector('input[name="expiry_preset"]:checked');
                    if (selectedPreset && selectedPreset.value === '') {
                        formData.delete('expires_at');
                    }
                    if (!apiRequest) {
                        showNotification('error', 'API客户端未初始化');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }

                    const payload = {
                        name: formData.get('name') || '',
                        scopes: formData.getAll('scopes[]')
                    };
                    const expiresAt = formData.get('expires_at');
                    if (expiresAt) {
                        payload.expires_at = expiresAt;
                    }

                    apiRequest('POST', '/personal-access-tokens', payload).then(function(data) {
                        if (data && data.code === 9999 && data.result && data.result.token) {
                            // 显示成功模态框
                            openSuccessModal(data.result.token);

                            // 3秒后重定向到列表页
                            setTimeout(() => {
                                window.location.href = "{{ route('personal-access-tokens.index') }}";
                            }, 5000);
                            return;
                        }

                        // 显示错误信息
                        showNotification('error', (data && data.msg) ? data.msg : '创建失败');
                    }).catch(function(error) {
                        console.error('Error:', error);
                        showNotification('error', '网络错误，请稍后重试');
                    }).finally(function() {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                });
            }

            // 重置表单
            const resetBtn = document.getElementById('resetForm');
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (confirm('确定要重置表单内容吗？所有输入的内容都将被清空。')) {
                        tokenForm.reset();

                        // 重置全选按钮状态
                        if (selectAllBtn) {
                            selectAllBtn.innerHTML = '<i class="fas fa-check-double mr-1"></i>全选';
                        }

                        // 重置过期时间
                        customTimeGroup.classList.add('hidden');
                        expiresAtHidden.value = '';

                        // 聚焦到名称输入框
                        document.getElementById('name').focus();

                        showNotification('info', '表单已重置');
                    }
                });
            }

            // 复制令牌函数
            window.copyToken = function() {
                const tokenInput = document.getElementById('newTokenValue');

                // 选择文本
                tokenInput.select();
                tokenInput.setSelectionRange(0, 99999);

                // 复制到剪贴板
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        showNotification('success', '令牌已复制到剪贴板');
                    } else {
                        showNotification('error', '复制失败，请手动选择复制');
                    }
                } catch (err) {
                    showNotification('error', '复制失败，请手动选择复制');
                }
            };

            // 通知函数
            function showNotification(type, message) {
                // 移除已有的通知
                document.querySelectorAll('.notification-item').forEach(el => el.remove());

                const notification = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' :
                    type === 'error' ? 'bg-red-500' :
                        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
                const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

                notification.className = `notification-item fixed top-4 right-4 z-50 fade-in max-w-sm`;
                notification.innerHTML = `
                <div class="card ${bgColor} text-white shadow-xl">
                    <div class="p-4 flex items-center gap-3">
                        <i class="fas ${icon} text-lg"></i>
                        <div class="flex-1">${message}</div>
                        <button class="text-white hover:text-gray-200 close-notification">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

                document.body.appendChild(notification);

                // 点击关闭
                notification.querySelector('.close-notification').addEventListener('click', () => {
                    notification.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.opacity = '0';
                        setTimeout(() => {
                            notification.parentNode.removeChild(notification);
                        }, 300);
                    }
                }, 5000);
            }

            // 依赖 scope-card 的 CSS :checked 规则自动更新选中样式，
            // 无需在 JS 中手动切换 class（也能保证“全选/取消全选”时样式同步）。
        });
    </script>
@endsection
