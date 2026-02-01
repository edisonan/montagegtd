@extends('layouts.app')

@section('title', 'OPML导入 - 蒙太奇')
@section('description', '导入OPML文件以批量添加RSS订阅源')

@section('content')
    <div class="fade-in">
        <div class="max-w-4xl mx-auto">
            <!-- 页面标题和导航 -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">OPML导入</h1>
                    <p class="text-gray-600 mt-2">导入OPML文件以批量添加RSS订阅源到阅读列表</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ url('articles') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-2"></i>
                        返回阅读
                    </a>
                    <a href="{{ url('feeds/explorer') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-compass mr-2"></i>
                        返回发现
                    </a>
                </div>
            </div>

            <!-- 成功消息提示 -->
            @include('common.success')

            <!-- OPML导入卡片 -->
            <div class="card card-elevated">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-import text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">导入OPML文件</h2>
                            <p class="text-gray-500 text-sm">支持从其他RSS阅读器导出的OPML格式文件</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- 导入说明 -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <div>
                                <h3 class="font-medium text-gray-900 mb-1">OPML文件说明</h3>
                                <p class="text-gray-600 text-sm">
                                    OPML（Outline Processor Markup Language）是一种标准的XML格式，
                                    常用于在不同RSS阅读器之间交换订阅列表。您可以从Feedly、Inoreader等工具导出OPML文件。
                                </p>
                                <div class="mt-3 flex items-center gap-4 text-sm text-gray-600">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-file-code"></i>
                                    <span>XML格式文件</span>
                                </span>
                                    <span class="flex items-center gap-2">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span>跨平台兼容</span>
                                </span>
                                    <span class="flex items-center gap-2">
                                    <i class="fas fa-share-alt"></i>
                                    <span>批量导入</span>
                                </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 上传表单 -->
                    <form action="/feeds/importOpml" method="post" enctype="multipart/form-data" class="space-y-6">
                        {!! csrf_field() !!}

                        <div class="space-y-4">
                            <div>
                                <label for="opml_file" class="block text-sm font-medium text-gray-700 mb-2">
                                    选择OPML文件
                                    <span class="text-gray-500 font-normal">（支持 .opml 或 .xml 格式）</span>
                                </label>

                                <div class="mt-1 flex flex-col sm:flex-row gap-4">
                                    <!-- 文件上传区域 -->
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="file"
                                                   name="opml_file"
                                                   id="opml_file"
                                                   accept=".opml,.xml,application/xml,text/xml"
                                                   class="hidden"
                                                   onchange="updateFileName(this)">

                                            <label for="opml_file" class="cursor-pointer">
                                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors bg-gray-50 hover:bg-blue-50">
                                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                                                    <p class="text-gray-600 mb-1">
                                                        <span class="font-medium text-blue-600">点击选择文件</span> 或拖放到此区域
                                                    </p>
                                                    <p class="text-sm text-gray-500">支持 .opml 和 .xml 格式文件</p>
                                                </div>
                                            </label>

                                            <div id="fileNameDisplay" class="mt-3 text-sm text-gray-600 hidden">
                                                <i class="fas fa-file text-gray-400 mr-2"></i>
                                                已选择：<span id="fileName" class="font-medium"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 提交按钮 -->
                                    <div class="sm:w-auto flex items-center">
                                        <button type="submit" class="btn btn-primary w-full sm:w-auto px-8">
                                            <i class="fas fa-upload mr-2"></i>
                                            立即导入
                                        </button>
                                    </div>
                                </div>

                                @error('opml_file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </form>

                    <!-- 使用示例 -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900 mb-4">常见问题</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-question-circle text-blue-600"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-900">如何获取OPML文件？</h4>
                                </div>
                                <p class="text-sm text-gray-600">
                                    在大多数RSS阅读器中，可以在设置或订阅管理中找到"导出订阅"或"导出OPML"选项。
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                    </div>
                                    <h4 class="font-medium text-gray-900">导入注意事项</h4>
                                </div>
                                <p class="text-sm text-gray-600">
                                    导入过程中可能会跳过无法访问的源。建议导入后检查订阅列表。
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 步骤说明 -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold">1</span>
                            </div>
                            <h3 class="font-medium text-gray-900">导出OPML</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            从您当前使用的RSS阅读器中导出OPML文件，通常在设置或账户页面中可以找到。
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 font-bold">2</span>
                            </div>
                            <h3 class="font-medium text-gray-900">上传文件</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            点击上方区域选择导出的OPML文件，或直接拖拽文件到上传区域。
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="text-green-600 font-bold">3</span>
                            </div>
                            <h3 class="font-medium text-gray-900">完成导入</h3>
                        </div>
                        <p class="text-gray-600 text-sm">
                            点击导入按钮，系统会自动解析并添加所有可用的订阅源到您的阅读列表。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 文件拖放功能
            const dropZone = document.querySelector('label[for="opml_file"]');
            const fileInput = document.getElementById('opml_file');

            if (dropZone) {
                // 阻止默认拖放行为
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                // 高亮显示拖放区域
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                // 处理文件拖放
                dropZone.addEventListener('drop', handleDrop, false);
            }

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight() {
                dropZone.classList.add('border-blue-400', 'bg-blue-50');
                dropZone.classList.remove('border-gray-300', 'bg-gray-50');
            }

            function unhighlight() {
                dropZone.classList.remove('border-blue-400', 'bg-blue-50');
                dropZone.classList.add('border-gray-300', 'bg-gray-50');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files.length > 0) {
                    fileInput.files = files;
                    updateFileName(fileInput);
                }
            }
        });

        // 更新文件名显示
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileNameSpan = document.getElementById('fileName');

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                fileNameSpan.textContent = file.name;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        }

        // 表单提交处理
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('opml_file');
            const submitBtn = this.querySelector('button[type="submit"]');

            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('请先选择要导入的OPML文件');
                return false;
            }

            // 显示加载状态
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>正在导入...';
                submitBtn.disabled = true;

                // 恢复按钮状态（防止页面跳转）
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    </script>
@endsection