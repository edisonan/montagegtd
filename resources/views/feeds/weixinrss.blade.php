@extends('layouts.app')

@section('title', '订阅微信公众号 - 蒙太奇')
@section('description', '微信公众号订阅功能维护中，稍后恢复服务')

@section('content')
    <div class="fade-in">
        <div class="max-w-3xl mx-auto">
            <!-- 页面标题和导航 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">订阅微信公众号</h1>
                    <div class="flex items-center gap-2 mt-2">
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                        <i class="fas fa-tools mr-1"></i>维护中
                    </span>
                        <p class="text-gray-600">功能维护中，稍后恢复订阅服务</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('feeds/explorer') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-compass mr-2"></i>
                        返回发现
                    </a>
                    <a href="{{ url('articles') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-newspaper mr-2"></i>
                        返回阅读
                    </a>
                </div>
            </div>

            <!-- 成功消息提示 -->
            @include('common.success')

            <!-- 微信公众号订阅卡片 -->
            <div class="card border-2 border-yellow-200 bg-yellow-50">
                <div class="p-6 border-b border-yellow-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-700 rounded-lg flex items-center justify-center">
                            <i class="fab fa-weixin text-white text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-semibold text-gray-900">订阅微信公众号</h2>
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">
                                维护中
                            </span>
                            </div>
                            <p class="text-gray-500 text-sm mt-1">功能正在升级维护，暂时无法订阅新的公众号</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- 维护通知 -->
                    <div class="mb-8">
                        <div class="bg-white border border-yellow-300 rounded-lg p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-tools text-yellow-600 text-xl"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">功能维护通知</h3>
                                    <p class="text-gray-600 mb-4">
                                        由于微信公众号接口升级，订阅功能正在进行技术调整和优化。
                                        在此期间，暂时无法添加新的微信公众号订阅。
                                    </p>
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">现有订阅不受影响</p>
                                                <p class="text-xs text-gray-500">已订阅的公众号会继续正常更新</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-clock text-yellow-500 mt-1"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">预计恢复时间</p>
                                                <p class="text-xs text-gray-500">2024年2月底前完成升级</p>
                                            </div>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <i class="fas fa-bell text-blue-500 mt-1"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">功能恢复通知</p>
                                                <p class="text-xs text-gray-500">恢复后将通过站内信通知用户</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 维护状态下的表单 -->
                    <div class="opacity-50 cursor-not-allowed">
                        <div class="mb-6">
                            <h3 class="text-md font-semibold text-gray-700 mb-4">订阅表单（维护中）</h3>
                            <p class="text-sm text-gray-500 mb-4">
                                以下表单暂时不可用，功能恢复后可正常填写
                            </p>
                        </div>

                        <form action="/api/v2/feeds" method="post" id="weixinSubscriptionForm">
                            {!! csrf_field() !!}

                            <!-- 公众号ID -->
                            <div class="space-y-2 mb-6">
                                <label for="weixin_id" class="block text-sm font-medium text-gray-700">
                                    公众号ID
                                    <span class="text-gray-500 font-normal">（维护中）</span>
                                </label>

                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-hashtag text-gray-400"></i>
                                    </div>
                                    <input type="text"
                                           id="weixin_id"
                                           name="weixin_id"
                                           value=""
                                           disabled
                                           placeholder="请输入公众号ID，例如：rmrbwx"
                                           class="input pl-10 w-full bg-gray-100 cursor-not-allowed">
                                </div>

                                <div class="text-sm text-gray-500 mt-1">
                                    公众号的唯一标识，通常在公众号设置中查找
                                </div>
                            </div>

                            <!-- 分类选择 -->
                            <div class="space-y-2 mb-6">
                                <label for="category_id" class="block text-sm font-medium text-gray-700">
                                    选择分类
                                    <span class="text-gray-500 font-normal">（维护中）</span>
                                </label>

                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-folder text-gray-400"></i>
                                    </div>
                                    <select id="category_id"
                                            name="category_id"
                                            disabled
                                            class="input pl-10 w-full appearance-none bg-gray-100 cursor-not-allowed">
                                        @foreach ($categorys as $category)
                                            <option value="{{ $category->id }}">
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- 隐藏字段 -->
                            <input type="hidden" name="feed_type" value="weixin">
                            <input type="hidden" name="feed_name" value="weixin">
                            <input type="hidden" name="url" value="weixin">

                            <!-- 表单操作 -->
                            <div class="pt-6 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <button type="submit"
                                            disabled
                                            class="btn btn-primary flex-1 sm:flex-none sm:w-auto px-8 bg-gray-300 border-gray-300 cursor-not-allowed">
                                        <i class="fas fa-lock mr-2"></i>
                                        功能维护中
                                    </button>

                                    <button type="button"
                                            onclick="window.location.href='{{ url('feeds/explorer') }}'"
                                            class="btn btn-secondary flex-1 sm:flex-none sm:w-auto">
                                        取消
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 替代方案和建议 -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">在此期间您可以</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- 方案1 -->
                    <div class="card hover:transform hover:-translate-y-1 transition-all duration-200">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-rss text-blue-600"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">订阅其他RSS源</h4>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                探索和订阅其他网站的RSS订阅源，很多网站提供标准的RSS输出。
                            </p>
                            <a href="{{ url('feeds/explorer') }}" class="btn btn-outline btn-sm w-full">
                                <i class="fas fa-compass mr-2"></i>
                                探索发现
                            </a>
                        </div>
                    </div>

                    <!-- 方案2 -->
                    <div class="card hover:transform hover:-translate-y-1 transition-all duration-200">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fab fa-weibo text-purple-600"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">订阅微博用户</h4>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                很多媒体和作者也在微博活跃，可以将微博用户转换为RSS订阅。
                            </p>
                            <a href="{{ url('feeds/weibo') }}" class="btn btn-outline btn-sm w-full">
                                <i class="fab fa-weibo mr-2"></i>
                                订阅微博
                            </a>
                        </div>
                    </div>

                    <!-- 方案3 -->
                    <div class="card hover:transform hover:-translate-y-1 transition-all duration-200">
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bookmark text-green-600"></i>
                                </div>
                                <h4 class="font-medium text-gray-900">稍后阅读管理</h4>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                管理您的稍后阅读列表，整理已收藏的文章和订阅内容。
                            </p>
                            <a href="{{ url('articles?status=later') }}" class="btn btn-outline btn-sm w-full">
                                <i class="fas fa-bookmark mr-2"></i>
                                稍后阅读
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 技术说明 -->
            <div class="mt-8 card">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-code text-gray-600"></i>
                        技术说明
                    </h3>
                    <div class="prose prose-sm max-w-none text-gray-600">
                        <p>
                            由于微信公众号平台的接口变更和技术限制，我们需要对订阅系统进行以下升级：
                        </p>
                        <ul class="space-y-2 mt-3">
                            <li>更新API接口以适应微信官方的最新规范</li>
                            <li>优化内容抓取机制以提高稳定性和速度</li>
                            <li>改进文章解析算法以支持更多内容格式</li>
                            <li>增强错误处理机制以提供更好的用户体验</li>
                        </ul>
                        <p class="mt-4 text-sm">
                            我们正在努力尽快完成升级工作，在此期间给您带来的不便，敬请谅解。
                        </p>
                    </div>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope text-gray-400"></i>
                                <span class="text-sm text-gray-600">需要帮助？</span>
                            </div>
                            <a href="mailto:accacc@126.com?subject=微信公众号订阅问题"
                               class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                联系技术支持
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 进度指示 -->
            <div class="mt-8">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-700">功能恢复进度</span>
                    <span class="text-sm text-gray-500">约70%</span>
                </div>
                <div class="progress h-2">
                    <div class="progress-bar" style="width: 70%"></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                    <span>开始维护</span>
                    <span>测试阶段</span>
                    <span>即将恢复</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 禁用表单提交
            $('#weixinSubscriptionForm').on('submit', function(e) {
                e.preventDefault();

                // 显示维护提示
                showMaintenanceAlert();
                return false;
            });

            // 维护提示
            function showMaintenanceAlert() {
                // 创建提示元素
                const alertDiv = $(`
            <div class="fixed top-4 right-4 z-50 fade-in">
                <div class="card shadow-lg border-l-4 border-yellow-500">
                    <div class="p-4 flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-tools text-yellow-500 text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-800">功能维护中</p>
                            <p class="text-xs text-gray-600 mt-1">微信公众号订阅功能正在升级，请稍后再试。</p>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);

                // 添加到页面
                $('body').append(alertDiv);

                // 点击关闭
                alertDiv.find('button').click(function() {
                    alertDiv.remove();
                });

                // 5秒后自动关闭
                setTimeout(() => {
                    alertDiv.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // 为不可用按钮添加点击反馈
            $('button[disabled]').click(function(e) {
                e.preventDefault();
                showMaintenanceAlert();
            });

            // 添加预约提醒功能
            $('#weixin_id, #category_id').focus(function() {
                showMaintenanceAlert();
            });
        });
    </script>

    <style>
        /* 维护状态样式 */
        .cursor-not-allowed {
            cursor: not-allowed !important;
        }

        .opacity-50 {
            opacity: 0.5;
        }

        /* 进度条标签 */
        .progress-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        /* 卡片悬停效果 */
        .card {
            transition: all 0.2s ease;
        }

        .card:hover:not(.border-yellow-200) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* 维护徽章动画 */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .bg-yellow-100 {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* 替代方案卡片图标效果 */
        .card .w-10.h-10 {
            transition: transform 0.2s ease;
        }

        .card:hover .w-10.h-10 {
            transform: scale(1.1);
        }

        /* 禁用状态下的输入框 */
        input:disabled, select:disabled {
            background-color: var(--gray-100) !important;
            color: var(--gray-500) !important;
            cursor: not-allowed !important;
        }

        /* 表单禁用状态 */
        .form-disabled {
            position: relative;
        }

        .form-disabled::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            z-index: 10;
        }
    </style>
@endsection
