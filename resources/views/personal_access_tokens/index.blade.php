@extends('layouts.app')

@section('title', '访问令牌管理 - 蒙太奇')
@section('description', '管理您的Personal Access Tokens，控制第三方应用的访问权限，确保API安全。')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- 页面标题和操作栏 -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">访问令牌管理</h1>
                    <p class="text-gray-600 mt-1">管理您的API访问令牌和安全凭证</p>
                </div>

                <button type="button"
                        id="createTokenBtn"
                        class="btn btn-primary self-start">
                    <i class="fas fa-plus mr-2"></i>
                    创建新令牌
                </button>
            </div>

            <!-- 面包屑导航 -->
            <div class="flex items-center gap-2 mt-4 text-sm">
                <a href="{{ url('/index') }}" class="text-gray-500 hover:text-gray-700">
                    首页
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <a href="{{ url('/accounts') }}" class="text-gray-500 hover:text-gray-700">
                    账号管理
                </a>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                <span class="text-gray-700 font-medium">访问令牌</span>
            </div>
        </div>

        <!-- 令牌列表卡片 -->
        <div class="card mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Personal Access Tokens</h2>
                        <p class="text-sm text-gray-500 mt-1">共 {{ count($tokens) }} 个访问令牌</p>
                    </div>

                    <div class="flex items-center gap-2">
                    <span class="text-xs px-3 py-1 bg-blue-100 text-blue-600 rounded-full font-medium">
                        <i class="fas fa-key mr-1"></i>API令牌
                    </span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                @if(count($tokens) > 0)
                    <!-- 令牌表格 -->
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">名称</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">权限范围</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">状态</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">创建时间</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">最后使用</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">操作</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @foreach($tokens as $token)
                                <tr id="token-{{ $token->id }}" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-gradient-to-br from-gray-700 to-gray-900 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-key text-white text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $token->name }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ $token->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @if($token->scopes && count($token->scopes) > 0)
                                                @foreach($token->scopes as $scope)
                                                    @php
                                                        $scopeColors = [
                                                            'read' => 'bg-blue-100 text-blue-800',
                                                            'write' => 'bg-green-100 text-green-800',
                                                            'delete' => 'bg-red-100 text-red-800',
                                                            'admin' => 'bg-purple-100 text-purple-800'
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $scopeColors[$scope] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $scope }}
                                            </span>
                                                @endforeach
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            无权限
                                        </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        @if($token->expires_at)
                                            @if($token->isExpired())
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-circle mr-1"></i>已过期
                                        </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>有效
                                        </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-infinity mr-1"></i>永不过期
                                    </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        <div>{{ $token->created_at->format('Y-m-d') }}</div>
                                        <div class="text-xs text-gray-400">{{ $token->created_at->format('H:i:s') }}</div>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        @if($token->last_used_at)
                                            <div>{{ $token->last_used_at->format('Y-m-d') }}</div>
                                            <div class="text-xs text-gray-400">{{ $token->last_used_at->format('H:i:s') }}</div>
                                        @else
                                            <span class="text-gray-400 italic">从未使用</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <button type="button"
                                                data-token-id="{{ $token->id }}"
                                                data-token-name="{{ $token->name }}"
                                                class="delete-token-btn px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash-alt mr-1"></i>删除
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- 空状态 -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-key text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">暂无访问令牌</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">
                            您还没有创建任何访问令牌。访问令牌用于第三方应用通过API访问您的账户数据。
                        </p>
                        <button type="button" id="createEmptyTokenBtn" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            创建第一个令牌
                        </button>
                    </div>
                @endif

                <!-- 安全提示 -->
                <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-shield-alt text-yellow-500 mt-0.5"></i>
                        <div class="text-sm">
                            <h4 class="font-medium text-yellow-800 mb-1">安全提示</h4>
                            <ul class="text-yellow-700 space-y-1">
                                <li>• 访问令牌拥有与您账户相同的权限，请妥善保管</li>
                                <li>• 定期审查和清理不再使用的访问令牌</li>
                                <li>• 为不同用途创建具有最小必要权限的令牌</li>
                                <li>• 令牌泄露时，请立即删除并创建新的令牌</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 创建令牌模态框 -->
    <div id="createTokenModal" class="modal">
        <div class="modal-content max-w-2xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">创建访问令牌</h3>
                    <p class="text-sm text-gray-500 mt-1">为第三方应用创建API访问凭证</p>
                </div>
                <button type="button" class="modal-close p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- 错误提示 -->
                <div id="tokenErrors" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-red-800 mb-1">创建失败</h4>
                            <ul id="tokenErrorList" class="text-sm text-red-700 space-y-1"></ul>
                        </div>
                    </div>
                </div>

                <form id="tokenForm" method="POST" class="space-y-5">
                    {!! csrf_field() !!}

                    <!-- 令牌名称 -->
                    <div class="space-y-2">
                        <label for="tokenName" class="block text-sm font-medium text-gray-700">
                            令牌名称
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="tokenName"
                               name="name"
                               required
                               placeholder="例如：GitHub集成、移动应用、脚本工具..."
                               class="input w-full">
                        <p class="text-xs text-gray-500">建议使用用途或应用名称，方便后续识别</p>
                    </div>

                    <!-- 权限范围 -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            权限范围
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php
                                $scopes = [
                                    'read' => ['label' => '读取权限', 'desc' => '查看数据，但不允许修改', 'color' => 'border-blue-300 peer-checked:bg-blue-50'],
                                    'write' => ['label' => '写入权限', 'desc' => '创建和修改数据', 'color' => 'border-green-300 peer-checked:bg-green-50'],
                                    'delete' => ['label' => '删除权限', 'desc' => '删除数据（危险）', 'color' => 'border-red-300 peer-checked:bg-red-50'],
                                    'admin' => ['label' => '管理权限', 'desc' => '完全控制，包括账户设置', 'color' => 'border-purple-300 peer-checked:bg-purple-50']
                                ];
                            @endphp

                            @foreach($scopes as $scope => $info)
                                <label class="cursor-pointer">
                                    <input type="checkbox"
                                           name="scopes[]"
                                           value="{{ $scope }}"
                                           class="peer sr-only">
                                    <div class="p-4 rounded-lg border-2 border-gray-200 {{ $info['color'] }} transition-all duration-200">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-6 h-6 border-2 border-gray-300 rounded peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:border-none flex items-center justify-center">
                                                    <i class="fas fa-check text-white text-xs hidden peer-checked:block"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-700">{{ $info['label'] }}</div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $info['desc'] }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500">遵循最小权限原则，只授予必要的权限</p>
                    </div>

                    <!-- 过期时间 -->
                    <div class="space-y-2">
                        <label for="expiresAt" class="block text-sm font-medium text-gray-700">
                            过期时间
                            <span class="text-xs text-gray-400">（可选）</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <select id="expiresAt" name="expires_at" class="input">
                                <option value="">永不过期</option>
                                <option value="1">1天后</option>
                                <option value="7">7天后</option>
                                <option value="30">30天后</option>
                                <option value="90">90天后</option>
                                <option value="365">1年后</option>
                                <option value="custom">自定义</option>
                            </select>
                            <input type="date"
                                   id="customExpiryDate"
                                   class="input hidden"
                                   min="{{ date('Y-m-d') }}">
                            <input type="time"
                                   id="customExpiryTime"
                                   class="input hidden"
                                   value="23:59">
                        </div>
                        <p class="text-xs text-gray-500">设置过期时间可以提高安全性，建议为临时用途设置过期时间</p>
                    </div>
                </form>
            </div>

            <div class="flex gap-3 pt-6 mt-6 border-t border-gray-200">
                <button type="button" class="modal-close btn btn-secondary flex-1">
                    <i class="fas fa-times mr-2"></i>取消
                </button>
                <button type="button" id="createTokenSubmit" class="btn btn-primary flex-1">
                    <i class="fas fa-plus mr-2"></i>创建令牌
                </button>
            </div>
        </div>
    </div>

    <!-- 显示令牌模态框 -->
    <div id="showTokenModal" class="modal">
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
                            <h4 class="font-medium text-red-800 mb-1">安全警告</h4>
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
                            <h4 class="font-medium text-blue-800 mb-1">使用说明</h4>
                            <ul class="text-blue-700 space-y-1">
                                <li>• 在请求头中添加：<code class="bg-gray-100 px-1 rounded">Authorization: Bearer 您的令牌</code></li>
                                <li>• 令牌拥有您在创建时指定的权限</li>
                                <li>• 可以在API文档中查看详细的接口说明</li>
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

    <!-- 删除确认模态框 -->
    <div id="deleteTokenModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">确认删除令牌</h3>
                <button type="button" class="modal-close p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
                    <i class="fas fa-trash-alt text-red-500 text-lg"></i>
                </div>
                <p class="text-gray-700 text-center mb-2">
                    确定要删除令牌 <strong id="deleteTokenName"></strong> 吗？
                </p>
                <p class="text-sm text-gray-500 text-center">
                    使用此令牌的所有应用将立即失去访问权限，此操作不可恢复。
                </p>
            </div>

            <div class="flex gap-3">
                <button type="button" class="modal-close btn btn-secondary flex-1">取消</button>
                <button type="button" id="confirmDeleteToken" class="btn btn-danger flex-1">
                    <i class="fas fa-trash-alt mr-2"></i>确认删除
                </button>
            </div>
        </div>
    </div>

    <style>
        /* 表格样式 */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--gray-50);
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
        }

        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--gray-200);
        }

        /* 徽章样式 */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, var(--danger-color));
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        /* 自定义复选框 */
        input[type="checkbox"]:checked + div .fa-check {
            display: block;
        }

        input[type="checkbox"]:checked + div {
            border-color: var(--primary-color);
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 模态框管理
            const modals = {
                create: document.getElementById('createTokenModal'),
                show: document.getElementById('showTokenModal'),
                delete: document.getElementById('deleteTokenModal')
            };

            const modalCloseButtons = document.querySelectorAll('.modal-close');
            let currentDeleteTokenId = null;

            // 打开模态框
            function openModal(modalName) {
                Object.values(modals).forEach(modal => modal.classList.remove('show'));
                if (modals[modalName]) {
                    modals[modalName].classList.add('show');
                }
            }

            // 关闭所有模态框
            function closeModals() {
                Object.values(modals).forEach(modal => modal.classList.remove('show'));
            }

            // 绑定关闭按钮事件
            modalCloseButtons.forEach(button => {
                button.addEventListener('click', closeModals);
            });

            // 点击模态框背景关闭
            Object.values(modals).forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModals();
                    }
                });
            });

            // ESC键关闭模态框
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModals();
                }
            });

            // 创建令牌按钮
            const createTokenBtns = document.querySelectorAll('#createTokenBtn, #createEmptyTokenBtn');
            createTokenBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('tokenErrors').classList.add('hidden');
                    document.getElementById('tokenForm').reset();
                    openModal('create');
                });
            });

            // 过期时间选择器逻辑
            const expiresSelect = document.getElementById('expiresAt');
            const customDateInput = document.getElementById('customExpiryDate');
            const customTimeInput = document.getElementById('customExpiryTime');

            if (expiresSelect) {
                expiresSelect.addEventListener('change', function() {
                    const isCustom = this.value === 'custom';
                    customDateInput.classList.toggle('hidden', !isCustom);
                    customTimeInput.classList.toggle('hidden', !isCustom);

                    if (!isCustom && this.value) {
                        const days = parseInt(this.value);
                        const expiryDate = new Date();
                        expiryDate.setDate(expiryDate.getDate() + days);
                        customDateInput.value = expiryDate.toISOString().split('T')[0];
                    }
                });
            }

            // 创建令牌提交
            const createTokenSubmitBtn = document.getElementById('createTokenSubmit');
            if (createTokenSubmitBtn) {
                createTokenSubmitBtn.addEventListener('click', function() {
                    createToken();
                });
            }

            // 删除令牌功能
            const deleteTokenBtns = document.querySelectorAll('.delete-token-btn');
            deleteTokenBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDeleteTokenId = this.getAttribute('data-token-id');
                    document.getElementById('deleteTokenName').textContent =
                        this.getAttribute('data-token-name');
                    openModal('delete');
                });
            });

            // 确认删除令牌
            const confirmDeleteBtn = document.getElementById('confirmDeleteToken');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    if (!currentDeleteTokenId) return;
                    deleteToken(currentDeleteTokenId);
                });
            }
        });

        // 创建令牌函数
        function createToken() {
            const form = document.getElementById('tokenForm');
            const formData = new FormData(form);

            // 处理自定义过期时间
            const expiresSelect = document.getElementById('expiresAt');
            if (expiresSelect.value === 'custom') {
                const date = document.getElementById('customExpiryDate').value;
                const time = document.getElementById('customExpiryTime').value;
                if (date && time) {
                    const datetime = new Date(`${date}T${time}:00`);
                    formData.set('expires_at', datetime.toISOString());
                }
            }

            const submitBtn = document.getElementById('createTokenSubmit');
            const originalBtnText = submitBtn.innerHTML;

            // 显示加载状态
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>创建中...';

            $.ajax({
                url: '/personal-access-tokens',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.code == 9999) {
                        // 关闭创建模态框
                        document.querySelector('#createTokenModal .modal-close').click();

                        // 显示新令牌
                        document.getElementById('newTokenValue').value = response.result.token;

                        // 打开显示令牌模态框
                        document.getElementById('showTokenModal').classList.add('show');

                        // 3秒后自动刷新页面
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    } else {
                        // 显示错误信息
                        const errorDiv = document.getElementById('tokenErrors');
                        const errorList = document.getElementById('tokenErrorList');

                        errorList.innerHTML = '';
                        if (response.msg) {
                            errorList.innerHTML = `<li>${response.msg}</li>`;
                        } else {
                            errorList.innerHTML = '<li>创建失败，请稍后重试</li>';
                        }
                        errorDiv.classList.remove('hidden');

                        // 滚动到错误提示
                        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                },
                error: function(xhr) {
                    // 显示验证错误
                    const errorDiv = document.getElementById('tokenErrors');
                    const errorList = document.getElementById('tokenErrorList');

                    errorList.innerHTML = '';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            $.each(value, function(index, msg) {
                                errorList.innerHTML += `<li>${msg}</li>`;
                            });
                        });
                    } else {
                        errorList.innerHTML = '<li>创建失败，请检查网络后重试</li>';
                    }
                    errorDiv.classList.remove('hidden');

                    // 滚动到错误提示
                    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                },
                complete: function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        }

        // 删除令牌函数
        function deleteToken(id) {
            const deleteBtn = document.getElementById('confirmDeleteToken');
            const originalBtnText = deleteBtn.innerHTML;

            // 显示加载状态
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>删除中...';

            $.ajax({
                url: `/personal-access-tokens/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.code == 9999) {
                        // 从DOM中移除行
                        const tokenRow = document.getElementById(`token-${id}`);
                        if (tokenRow) {
                            tokenRow.style.opacity = '0';
                            setTimeout(() => {
                                if (tokenRow.parentNode) {
                                    tokenRow.parentNode.removeChild(tokenRow);
                                }
                            }, 300);
                        }

                        // 显示成功消息
                        showNotification('success', response.msg || '令牌删除成功');

                        // 关闭模态框
                        document.querySelector('#deleteTokenModal .modal-close').click();

                        // 更新计数
                        updateTokenCount();
                    } else {
                        showNotification('error', response.msg || '删除失败');
                    }
                },
                error: function(xhr) {
                    showNotification('error', '删除失败，请稍后重试');
                },
                complete: function() {
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = originalBtnText;
                    currentDeleteTokenId = null;
                }
            });
        }

        // 复制令牌函数
        function copyToken() {
            const tokenInput = document.getElementById('newTokenValue');

            // 选择文本
            tokenInput.select();
            tokenInput.setSelectionRange(0, 99999);

            // 复制到剪贴板
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showNotification('success', '令牌已复制到剪贴板');

                    // 关闭模态框
                    setTimeout(() => {
                        document.querySelector('#showTokenModal .modal-close').click();
                    }, 1500);
                } else {
                    showNotification('error', '复制失败，请手动选择复制');
                }
            } catch (err) {
                showNotification('error', '复制失败，请手动选择复制');
            }
        }

        // 辅助函数
        function updateTokenCount() {
            const tokenCount = document.querySelectorAll('tbody tr').length;
            const countElement = document.querySelector('.text-sm.text-gray-500.mt-1');
            if (countElement) {
                countElement.textContent = `共 ${tokenCount} 个访问令牌`;
            }
        }

        function showNotification(type, message) {
            // 移除已有的通知
            document.querySelectorAll('.notification-item').forEach(el => el.remove());

            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

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
    </script>
@endsection