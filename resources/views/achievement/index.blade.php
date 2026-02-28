@extends('layouts.app')

@section('title', '成就勋章 - 蒙太奇')
@section('description', '查看和领取您的成就勋章，记录成长历程')

@section('content')
    <div class="max-w-6xl mx-auto">
        <!-- 页面标题 -->
        <div class="mb-8 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-trophy text-white text-2xl"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">成就中心</h1>
            <p class="text-gray-600">记录您的成长足迹，激励更好的自己</p>

            <!-- 统计卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="card">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">已获得成就</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">
                                    {{ $list->where('category', 'achievement')->where('achieved', true)->count() }}
                                    <span class="text-gray-400">/</span>
                                    {{ $list->where('category', 'achievement')->count() }}
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">已领取勋章</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">
                                    {{ $list->where('category', 'badge')->where('achieved', true)->count() }}
                                    <span class="text-gray-400">/</span>
                                    {{ $list->where('category', 'badge')->count() }}
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-medal text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">总获得积分</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">
                                    {{ $list->where('achieved', true)->sum('point_value') }} GP
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-coins text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 成就系统 -->
        <div class="card mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-3"></i>
                    成就系统
                    <span class="ml-2 badge badge-primary">
                    {{ $list->where('category', 'achievement')->where('achieved', true)->count() }} / {{ $list->where('category', 'achievement')->count() }}
                </span>
                </h2>
                <p class="text-gray-600 mt-1">自动完成的成就，记录您的使用历程</p>
            </div>

            <div class="p-6">
                @php
                    $achievementList = $list->where('category', 'achievement');
                @endphp

                @if($achievementList->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-star text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">暂无成就配置</h3>
                        <p class="text-gray-600">成就系统正在开发中，敬请期待</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($achievementList as $item)
                            <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow duration-200
                            {{ $item->achieved ? 'bg-gradient-to-br from-white to-blue-50' : 'bg-white opacity-80' }}">
                                <!-- 成就图标 -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-14 h-14 rounded-xl flex items-center justify-center
                                    {{ $item->achieved ?
                                        'bg-gradient-to-br from-yellow-400 to-orange-500' :
                                        'bg-gray-100' }}">
                                        <i class="fas fa-{{ $item->achieved ? 'crown' : 'star' }}
                                        {{ $item->achieved ? 'text-white' : 'text-gray-400' }} text-xl"></i>
                                    </div>

                                    @if($item->achieved)
                                        <span class="badge badge-success flex items-center">
                                        <i class="fas fa-check-circle mr-1 text-xs"></i>
                                        已获得
                                    </span>
                                    @else
                                        <span class="badge badge-primary">进行中</span>
                                    @endif
                                </div>

                                <!-- 成就信息 -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {{ $item->name }}
                                </h3>
                                <p class="text-gray-600 text-sm mb-4">
                                    {{ $item->description }}
                                </p>

                                <!-- 奖励和状态 -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center">
                                        @if($item->point_value > 0)
                                            <div class="flex items-center text-sm font-medium text-gray-700">
                                                <i class="fas fa-coins text-yellow-500 mr-1"></i>
                                                <span>{{ $item->point_value }} GP</span>
                                            </div>
                                        @endif
                                    </div>

                                    @if($item->achieved)
                                        <div class="text-sm text-gray-500">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            {{ $item->achieved_at }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-400">
                                            <i class="fas fa-clock mr-1"></i>
                                            未完成
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 勋章系统 -->
        <div class="card">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-medal text-purple-500 mr-3"></i>
                    勋章系统
                    <span class="ml-2 badge badge-primary">
                    {{ $list->where('category', 'badge')->where('achieved', true)->count() }} / {{ $list->where('category', 'badge')->count() }}
                </span>
                </h2>
                <p class="text-gray-600 mt-1">手动领取的荣誉勋章，展示您的特殊成就</p>
            </div>

            <div class="p-6">
                @php
                    $badgeList = $list->where('category', 'badge');
                @endphp

                @if($badgeList->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-medal text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">暂无勋章配置</h3>
                        <p class="text-gray-600">勋章系统正在开发中，敬请期待</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($badgeList as $item)
                            <div class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-all duration-200
                            {{ $item->achieved ?
                                'bg-gradient-to-br from-white to-purple-50 border-purple-200' :
                                'bg-white opacity-90' }}">
                                <!-- 勋章图标 -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="relative">
                                        <div class="w-16 h-16 rounded-xl flex items-center justify-center
                                        {{ $item->achieved ?
                                            'bg-gradient-to-br from-purple-500 to-pink-500 shadow-lg' :
                                            'bg-gray-100 border-2 border-dashed border-gray-300' }}">
                                            <i class="fas fa-{{ $item->achieved ? 'medal' : 'award' }}
                                            {{ $item->achieved ? 'text-white' : 'text-gray-400' }} text-2xl"></i>
                                        </div>

                                        @if($item->achieved)
                                            <div class="absolute -top-2 -right-2">
                                                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-sm">
                                                    <i class="fas fa-check text-white text-xs"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($item->achieved)
                                        <span class="badge badge-success">已领取</span>
                                    @endif
                                </div>

                                <!-- 勋章信息 -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {{ $item->name }}
                                </h3>
                                <p class="text-gray-600 text-sm mb-4">
                                    {{ $item->description }}
                                </p>

                                <!-- 奖励和操作 -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div class="flex items-center">
                                        @if($item->point_value > 0)
                                            <div class="flex items-center text-sm font-medium text-gray-700">
                                                <i class="fas fa-coins text-yellow-500 mr-1"></i>
                                                <span>{{ $item->point_value }} GP</span>
                                            </div>
                                        @else
                                            <div class="text-sm text-gray-400">
                                                <i class="fas fa-gift mr-1"></i>
                                                荣誉勋章
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        @if($item->achieved)
                                            <div class="text-sm text-gray-500">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ $item->achieved_at }}
                                            </div>
                                        @else
                                            <form method="POST" action="{{ url('/achievement/claim') }}" class="m-0">
                                                {!! csrf_field() !!}
                                                <input type="hidden" name="achievement_code" value="{{ $item->code }}">
                                                <button type="submit" class="btn btn-primary btn-sm flex items-center">
                                                    <i class="fas fa-gift mr-2"></i>
                                                    领取勋章
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 进度提示 -->
        <div class="mt-8 text-center">
            <div class="inline-block p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-2xl border border-blue-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">成就进度追踪</h3>

                @php
                    $totalItems = $list->count();
                    $achievedItems = $list->where('achieved', true)->count();
                    $percentage = $totalItems > 0 ? round(($achievedItems / $totalItems) * 100) : 0;
                @endphp

                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>整体进度</span>
                        <span>{{ $achievedItems }} / {{ $totalItems }} ({{ $percentage }}%)</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>

                <p class="text-sm text-gray-600">
                    <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                    继续使用蒙太奇的各种功能，解锁更多成就和勋章
                </p>
            </div>
        </div>
    </div>

        <script>
            // 勋章领取成功提示
            @if(session('success'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast('success', '{{ session('success') }}');
            });
            @endif

            @if(session('error'))
            document.addEventListener('DOMContentLoaded', function() {
                showToast('error', '{{ session('error') }}');
            });
            @endif

            // 显示提示消息
            function showToast(type, message) {
                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white animate-fade-in ${
                    type === 'success' ? 'bg-green-500' : 'bg-red-500'
                }`;
                toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'trophy' : 'exclamation-circle'} mr-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
                document.body.appendChild(toast);

                // 添加庆祝动画
                if (type === 'success') {
                    triggerConfetti();
                }

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 5000);
            }

            // 简单的庆祝效果
            function triggerConfetti() {
                const confetti = document.createElement('div');
                confetti.className = 'fixed inset-0 pointer-events-none z-40';
                confetti.innerHTML = `
            <div class="absolute top-0 left-1/4 animate-bounce">
                <i class="fas fa-star text-yellow-400 text-xl opacity-70"></i>
            </div>
            <div class="absolute top-0 right-1/4 animate-bounce" style="animation-delay: 0.2s">
                <i class="fas fa-medal text-purple-400 text-xl opacity-70"></i>
            </div>
            <div class="absolute top-0 left-1/2 animate-bounce" style="animation-delay: 0.4s">
                <i class="fas fa-crown text-orange-400 text-xl opacity-70"></i>
            </div>
        `;
                document.body.appendChild(confetti);

                setTimeout(() => {
                    confetti.remove();
                }, 3000);
            }

            // 成就卡片悬停效果
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.grid > div');
                cards.forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        if (this.querySelector('.btn-primary')) {
                            this.style.transform = 'translateY(-4px)';
                        }
                    });

                    card.addEventListener('mouseleave', function() {
                        this.style.transform = 'translateY(0)';
                    });
                });
            });
        </script>

        <style>
            /* 成就徽章特殊效果 */
            @keyframes pulse-glow {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.8; }
            }

            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            .achievement-earned {
                animation: pulse-glow 2s ease-in-out infinite;
            }

            .badge-earned {
                animation: bounce 1s ease-in-out infinite;
            }

            /* 勋章领取按钮特效 */
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(139, 92, 246, 0.3);
            }
        </style>
    @endsection