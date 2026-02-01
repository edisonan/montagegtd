@extends('layouts.app')

@section('title', '去阅读 - 蒙太奇')
@section('description', '阅读您订阅的文章，支持收藏、稍后阅读、标记已读等功能')

@section('content')
<style>
    /* 阅读页面专用样式 - 基于设计规范 */
    .reading-page {
        background: #f8fafc;
        min-height: calc(100vh - 200px);
    }

    /* 通用工具类 */
    .text-truncate-2 {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.4;
    }

    .text-truncate-1 {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* 导航侧边栏 */
    .reading-sidebar {
        position: sticky;
        top: 100px;
        height: calc(100vh - 140px);
        overflow-y: auto;
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .reading-sidebar:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
        background: linear-gradient(135deg, #4a90e2, #8a6cff);
        padding: 20px;
        color: white;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sidebar-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-actions {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .sidebar-action-btn {
        color: white;
        background: rgba(255, 255, 255, 0.15);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .sidebar-action-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }

    .sidebar-body {
        padding: 20px;
    }

    /* 分类导航 */
    .category-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-item {
        margin-bottom: 12px;
    }

    .category-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 12px;
        background: #f8fafc;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
    }

    .category-header:hover {
        background: #f1f5f9;
        border-color: #4a90e2;
    }

    .category-name {
        font-weight: 600;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .category-count {
        background: #4a90e2;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 24px;
        text-align: center;
    }

    .category-toggle {
        color: #94a3b8;
        transition: transform 0.3s ease;
    }

    .category-toggle.expanded {
        transform: rotate(90deg);
    }

    .feed-list {
        list-style: none;
        padding: 0;
        margin: 8px 0 0 0;
        padding-left: 24px;
        display: none;
    }

    .feed-list.expanded {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .feed-item {
        margin-bottom: 6px;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .feed-item:hover {
        background: #f1f5f9;
    }

    .feed-item.active {
        background: rgba(59, 130, 246, 0.1);
        border-left: 3px solid #4a90e2;
    }

    .feed-link {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .feed-link:hover {
        color: #4a90e2;
    }

    .feed-count {
        background: #cbd5e1;
        color: #475569;
        padding: 1px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: auto;
    }

    /* 文章内容区域 */
    .reading-content {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .content-header {
        background: #f8fafc;
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    .status-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-badge {
        background: #4a90e2;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-tabs {
        display: flex;
        gap: 8px;
        background: white;
        padding: 4px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .status-tab {
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        color: #64748b;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .status-tab:hover {
        background: #f1f5f9;
        color: #4a90e2;
    }

    .status-tab.active {
        background: #4a90e2;
        color: white;
    }

    .content-tools {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .tool-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tool-label {
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .tool-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .tool-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .tool-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
        border-radius: 24px;
    }

    .tool-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .tool-slider {
        background-color: #4a90e2;
    }

    input:checked + .tool-slider:before {
        transform: translateX(20px);
    }

    .tool-actions {
        display: flex;
        gap: 12px;
        margin-left: auto;
    }

    .tool-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .tool-btn:hover {
        background: #f1f5f9;
        border-color: #4a90e2;
        color: #4a90e2;
        transform: translateY(-2px);
    }

    /* 文章卡片 */
    .article-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
    }

    .article-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .article-header {
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .article-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .article-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #64748b;
        font-size: 0.875rem;
    }

    .meta-item i {
        color: #94a3b8;
        font-size: 0.8rem;
    }

    .source-link {
        /*color: #4a90e2;*/
        text-decoration: none;
        font-weight: 500;
    }

    .source-link:hover {
        text-decoration: underline;
    }

    .quick-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    .quick-btn {
        padding: 6px 12px;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .quick-btn:hover {
        background: #f1f5f9;
        border-color: #4a90e2;
        color: #4a90e2;
    }

    .article-content {
        padding: 20px;
        position: relative;
    }

    .content-preview {
        color: #475569;
        line-height: 1.7;
        font-size: 0.95rem;
        max-height: 360px;
        overflow: hidden;
        position: relative;
        transition: max-height 0.5s ease;
    }

    .content-preview.expanded {
        max-height: 5000px !important;
    }

    .content-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 12px 0;
        transition: transform 0.3s ease;
    }

    .content-preview img:hover {
        transform: scale(1.02);
    }

    .content-fade {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 100px;
        background: linear-gradient(to bottom, rgba(255,255,255,0), white);
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .content-preview.expanded .content-fade {
        opacity: 0;
    }

    .read-more {
        text-align: center;
        padding-top: 16px;
        margin-top: 16px;
        border-top: 1px solid #f1f5f9;
    }

    .read-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #475569;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .read-more-btn:hover {
        background: #e2e8f0;
        color: #4a90e2;
        border-color: #4a90e2;
    }

    .article-footer {
        padding: 16px 20px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: white;
        border: 1px solid #cbd5e1;
        color: #64748b;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .action-btn:hover {
        background: #f1f5f9;
        transform: translateY(-2px);
    }

    .action-btn.active {
        background: rgba(59, 130, 246, 0.1);
        border-color: #4a90e2;
        color: #4a90e2;
    }

    .action-btn.delete:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: #ef4444;
        color: #ef4444;
    }

    .action-btn i {
        font-size: 1.1rem;
    }

    .action-label {
        position: absolute;
        top: -24px;
        left: 50%;
        transform: translateX(-50%);
        background: #334155;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .action-btn:hover .action-label {
        opacity: 1;
        visibility: visible;
        top: -32px;
    }

    /* 修复分享菜单 - 按钮靠右 */
    .share-container {
        position: relative;
    }

    .share-menu {
        position: absolute;
        bottom: 120%;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        padding: 12px;
        z-index: 100;
        display: none;
        min-width: 200px;
        text-align: left;
    }

    .share-menu.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    .share-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        color: #475569;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        white-space: nowrap;
        width: 100%;
    }

    .share-option:hover {
        background: #f1f5f9;
        color: #4a90e2;
    }

    .share-option i {
        width: 20px;
        text-align: center;
    }

    /* 空状态 */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state img {
        max-width: 200px;
        height: auto;
        margin-bottom: 24px;
        opacity: 0.8;
    }

    .empty-state-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .empty-state-text {
        color: #64748b;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 24px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .empty-state-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 24px;
        font-size: 0.95rem;
        font-weight: 600;
        background: white;
        color: #4a90e2;
        border: 1px solid #4a90e2;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .empty-state-btn:hover {
        background: #4a90e2;
        color: white;
        transform: translateY(-2px);
    }

    .empty-state-btn.primary {
        background: #4a90e2;
        color: white;
    }

    .empty-state-btn.primary:hover {
        background: #2563eb;
    }

    /* 分页 */
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 40px;
        padding-top: 32px;
        border-top: 1px solid #e2e8f0;
    }

    /* 音频播放器 */
    .audio-player {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        border: 1px solid #e2e8f0;
        padding: 16px;
        z-index: 1000;
        display: none;
    }

    .audio-player.active {
        display: block;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* 响应式调整 */
    @media (max-width: 1024px) {
        .reading-sidebar {
            position: static;
            height: auto;
            margin-bottom: 24px;
        }

        .sidebar-body {
            max-height: 400px;
            overflow-y: auto;
        }
    }

    @media (max-width: 768px) {
        .content-header {
            flex-direction: column;
            align-items: stretch;
            gap: 16px;
        }

        .status-tabs {
            order: 1;
        }

        .content-tools {
            order: 2;
            flex-wrap: wrap;
        }

        .tool-actions {
            order: 3;
            width: 100%;
            justify-content: center;
            margin-top: 12px;
        }

        .article-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .quick-actions {
            margin-left: 0;
            width: 100%;
            justify-content: flex-start;
        }

        .article-footer {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }

        .action-buttons {
            justify-content: center;
        }

        .empty-state-actions {
            flex-direction: column;
        }

        .empty-state-btn {
            width: 100%;
        }

        .share-menu {
            right: -50px;
        }
    }

    /* 动画效果 */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    /* 图片屏蔽样式 */
    .image-disabled {
        background: #f1f5f9;
        padding: 40px 20px;
        border-radius: 8px;
        text-align: center;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .image-disabled:hover {
        background: #e2e8f0;
        color: #64748b;
    }

    .image-disabled i {
        font-size: 2rem;
        margin-bottom: 12px;
        display: block;
    }

    /* 修复AI助手模态框闪动问题 */
    /*.ai-ask-modal {*/
    /*    display: none !important;*/
    /*}*/

    /*.ai-ask-modal.show {*/
    /*    display: flex !important;*/
    /*}*/

    .ai-ask-modal {
        display: none; /* 默认隐藏 */
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .ai-ask-modal.show {
        display: flex;
        animation: fadeIn 0.3s ease-out;
    }

    /* 分页样式修复 */
    .pagination {
        justify-content: center !important;
        margin: 20px 0 !important;
    }

    .pagination > li > a,
    .pagination > li > span {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        margin: 0 4px !important;
        padding: 8px 12px !important;
        color: #64748b !important;
        background: white !important;
        transition: all 0.3s ease !important;
    }

    .pagination > li > a:hover {
        background: #f1f5f9 !important;
        color: #4a90e2 !important;
        border-color: #4a90e2 !important;
        transform: translateY(-2px) !important;
    }

    .pagination > .active > span {
        background: linear-gradient(135deg, #4a90e2, #8a6cff) !important;
        color: white !important;
        border-color: transparent !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3) !important;
    }
</style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 reading-page">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- 侧边栏导航 -->
            <div class="lg:col-span-1">
                <div class="reading-sidebar">
                    <div class="sidebar-header">
                        <h3 class="sidebar-title">
                            <i class="fas fa-list-alt"></i>
                            订阅分类
                        </h3>
                        <div class="sidebar-actions">
                            <a href="{{ url('kindles') }}" class="sidebar-action-btn" title="发送到Kindle">
                                <i class="fas fa-tablet-alt"></i>
                            </a>
                            <a href="{{ url('feeds/setting') }}" class="sidebar-action-btn" title="管理订阅">
                                <i class="fas fa-cog"></i>
                            </a>
                        </div>
                    </div>

                    <div class="sidebar-body" id="navBody">
                        <ul class="category-list" id="nav">
                            <!-- 动态加载分类 -->
                            <li class="text-center py-4 text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                加载中...
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 文章内容区域 -->
            <div class="lg:col-span-3">
                <!-- 成功消息 -->
                @include('common.success')

                <!-- 错误消息 -->
                @include('common.errors')

                <div class="reading-content">
                    <!-- 内容头部 -->
                    <div class="content-header">
                        <div>
                            <h2 class="status-title">
                                @if($status == 'unread')
                                    <i class="fas fa-envelope text-red-500"></i>
                                    未读文章
                                @elseif($status == 'read')
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    已读文章
                                @elseif($status == 'star')
                                    <i class="fas fa-star text-yellow-500"></i>
                                    收藏文章
                                @elseif($status == 'read_later')
                                    <i class="fas fa-clock text-blue-500"></i>
                                    稍后阅读
                                @endif
                                <span class="status-badge">{{ count($article_subs) }} 篇</span>
                            </h2>

                            <div class="status-tabs">
                                <a href="{{ url('articles?status=unread&feed_id='.$feed_id) }}"
                                   class="status-tab @if($status == 'unread') active @endif">
                                    未读
                                </a>
                                <a href="{{ url('articles?status=read&feed_id='.$feed_id) }}"
                                   class="status-tab @if($status == 'read') active @endif">
                                    已读
                                </a>
                                <a href="{{ url('articles?status=star&feed_id='.$feed_id) }}"
                                   class="status-tab @if($status == 'star') active @endif">
                                    加星
                                </a>
                                <a href="{{ url('articles?status=read_later&feed_id='.$feed_id) }}"
                                   class="status-tab @if($status == 'read_later') active @endif">
                                    稍后阅读
                                </a>
                            </div>
                        </div>

                        <div class="content-tools">
                            <div class="tool-item">
                                <span class="tool-label">一目十行</span>
                                <label class="tool-switch">
                                    <input type="checkbox" id="unable_desc" {{ $unable_desc == "true" ? 'checked' : '' }}>
                                    <span class="tool-slider"></span>
                                </label>
                            </div>

                            <div class="tool-item">
                                <span class="tool-label">屏蔽图片</span>
                                <label class="tool-switch">
                                    <input type="checkbox" id="unable_img" {{ $unable_img == "true" ? 'checked' : '' }}>
                                    <span class="tool-slider"></span>
                                </label>
                            </div>

                            <div class="tool-actions">
                                @if(!empty($feed_id))
                                    <a href="{{ url('feeds/explorer') }}" class="tool-btn">
                                        <i class="fas fa-compass"></i>
                                        发现
                                        <sup style="color: #ef4444; margin-left: 2px;">推荐</sup>
                                    </a>
                                @endif
                                <a href="{{ url('feeds') }}" class="tool-btn">
                                    <i class="fas fa-plus"></i>
                                    添加订阅
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 文章列表 -->
                    <div class="p-6">
                        @if (count($article_subs) > 0)
                                <?php $article_sub_ids = []; ?>
                            <div id="articleList">
                                @foreach ($article_subs as $articleSub)
                                        <?php
                                        $article = $articleSub->article;
                                        if (empty($article)) { continue; }
                                        $article_sub_ids[] = $articleSub->id;

                                        // 处理内容
                                        $content = $article->content;
                                        if ($unable_img == "true") {
                                            $content = str_replace('src="', 'src="/img/unable_img.png" data-original="', $content);
                                            $content = str_replace("src='", "src='/img/unable_img.png' data-original='", $content);
                                        }
                                        $formattedContent = App\Http\Utils\CommonUtil::formatContentHtml($content);

                                        $contentText = strip_tags($formattedContent);
                                        $needsCollapse = $unable_desc == "true" && (strlen($contentText) > 500 || substr_count($contentText, "\n") > 5);
                                        ?>

                                    <div class="article-card" id="article-{{ $articleSub->id }}">
                                        <!-- 文章头部 -->
                                        <div class="article-header">
                                            @if(!empty($article->subject))
                                                <h3 class="article-title">
                                                    <a href="{{ url('article/view/'.$article->id) }}" class="text-gray-900 hover:text-blue-600 transition-colors">
                                                        {{ $article->subject }}
                                                    </a>
                                                </h3>
                                            @endif

                                            <div class="article-meta">
                                                <div class="meta-item">
                                                    <i class="fas fa-rss"></i>
                                                    来源：
                                                    <a href="{{ url('articles?status=unread&feed_id='.$article->feed->id) }}"
                                                       class="source-link" target="_blank">
                                                        {{ $article->feed->feed_name }}
                                                    </a>
                                                </div>

                                                <div class="meta-item">
                                                    <i class="far fa-clock"></i>
                                                    {{ $article->published }}
                                                </div>

                                                <div class="meta-item">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    <a href="{{ str_replace('www.v2ex.com', 'pretask.congcong.us/article/proxyview?type=v2ex&url=',$article->url) }}"
                                                       class="source-link" target="_blank">
                                                        原文
                                                    </a>
                                                </div>

                                                @if($unable_desc == "true")
                                                    <div class="quick-actions">
                                                        <button type="button"
                                                                class="quick-btn set_read_later_another {{ $articleSub->status == 'read_later' ? 'active' : '' }}"
                                                                data-article-id="{{ $articleSub->id }}">
                                                            稍后阅读
                                                        </button>
                                                        <button type="button"
                                                                class="quick-btn expand-btn"
                                                                data-article-id="{{ $articleSub->id }}">
                                                            展开/收起
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- 文章内容 -->
                                        <div class="article-content">
                                            <div id="desc{{ $articleSub->id }}"
                                                 class="content-preview {{ $unable_desc == "true" && $needsCollapse ? '' : 'expanded' }}"
                                                 data-article-id="{{ $articleSub->id }}">
                                                {!! $formattedContent !!}

                                                @if($unable_desc == "true" && $needsCollapse)
                                                    <div class="content-fade"></div>
                                                @endif
                                            </div>

                                            {{-- 只在需要时显示阅读更多按钮 --}}
                                            @if($unable_desc == "true" && $needsCollapse)
                                                <div class="read-more">
                                                    <button type="button"
                                                            class="read-more-btn"
                                                            data-article-id="{{ $articleSub->id }}">
                                                        <i class="fas fa-chevron-down"></i>
                                                        阅读更多
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- 文章操作 -->
                                        <div class="article-footer">
                                            <div class="action-buttons">
                                                <button type="button"
                                                        class="action-btn ai-assist-btn"
                                                        data-content-id="content{{ $articleSub->id }}"
                                                        data-refer-text="{{ strip_tags($formattedContent) }}"
                                                        data-title="{{ $article->subject }}"
                                                        title="AI助手">
                                                    <i class="fas fa-robot"></i>
                                                    <span class="action-label">AI助手</span>
                                                </button>

                                                <div class="share-container">
                                                    <button type="button" class="action-btn share-btn" title="分享">
                                                        <i class="fas fa-share-alt"></i>
                                                        <span class="action-label">分享</span>
                                                    </button>

                                                    <div class="share-menu">
                                                        <a href="javascript:void(0);"
                                                           class="share-option icon-heart"
                                                           data-title="{{ $article->subject }} From:{{url('/article/view/')}}{{$article->id}}"
                                                           data-url="{{ url('article/view/') }}{{$article->id}}"
                                                           data-id="{{ $article->id }}">
                                                            <i class="fas fa-heart"></i>
                                                            <span>记录想法</span>
                                                        </a>
                                                        <!-- 其他分享选项可以在这里添加 -->
                                                    </div>
                                                </div>

                                                <button type="button"
                                                        class="action-btn set_read {{ $articleSub->status == 'read' ? 'active' : '' }}"
                                                        data-article-id="{{ $articleSub->id }}"
                                                        title="标记已读">
                                                    <i class="fas fa-check"></i>
                                                    <span class="action-label">已读</span>
                                                </button>

                                                <button type="button"
                                                        class="action-btn set_read_later {{ $articleSub->status == 'read_later' ? 'active' : '' }}"
                                                        data-article-id="{{ $articleSub->id }}"
                                                        title="稍后阅读">
                                                    <i class="far fa-clock"></i>
                                                    <span class="action-label">稍后</span>
                                                </button>

                                                <button type="button"
                                                        class="action-btn set_star {{ $articleSub->status == 'star' ? 'active' : '' }}"
                                                        data-article-id="{{ $articleSub->id }}"
                                                        title="收藏">
                                                    <i class="far fa-star"></i>
                                                    <span class="action-label">收藏</span>
                                                </button>
                                            </div>

                                            <div class="audio-control">
                                                <button type="button"
                                                        class="action-btn playaudio"
                                                        data-article-id="{{ $articleSub->id }}"
                                                        title="语音播放">
                                                    <i class="fas fa-volume-up"></i>
                                                    <span class="action-label">语音</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- 分页 -->
                            <div class="pagination-wrapper">
                                {!! $article_subs->appends($page_params)->links() !!}
                            </div>

                            <!-- 一键标记已读 -->
                            @if(!isset($_GET['status']) || $_GET['status'] == 'unread')
                                <div class="mt-6 text-center">
                                    <button type="button"
                                            class="btn btn-primary px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-300"
                                            id="marked_all_read"
                                            data-ids="{{ implode(',', $article_sub_ids) }}">
                                        <i class="fas fa-check-double mr-2"></i>
                                        标记全部已读
                                    </button>
                                </div>
                            @endif

                        @else
                            <!-- 空状态 -->
                            <div class="empty-state">
                                <img src="/img/new/love.png" alt="暂无文章" class="mx-auto">
                                <h3 class="empty-state-title">好像没有更多文章了</h3>
                                <p class="empty-state-text">
                                    您可以阅读一下其他的文章，或者开始新的订阅来获取更多精彩内容。
                                </p>
                                <div class="empty-state-actions">
                                    <a href="/articles" class="empty-state-btn">
                                        <i class="fas fa-newspaper mr-2"></i>
                                        浏览其他文章
                                    </a>
                                    <a href="/feeds/explorer" class="empty-state-btn primary">
                                        <i class="fas fa-compass mr-2"></i>
                                        发现新订阅
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 音频播放器 -->
    <div class="audio-player" id="audioPlayer">
        <div class="flex items-center gap-4">
            <button type="button" class="action-btn" id="audioClose" title="关闭">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex-1">
                <div class="text-sm font-medium text-gray-800 mb-1" id="audioTitle">正在播放</div>
                <audio id="audio" controls class="w-full">
                    <source src="" type="audio/mpeg">
                </audio>
            </div>
        </div>
    </div>

    <!-- 外部脚本 -->
    <link rel="stylesheet" href="/css/share.min.css">
    <script src="/js/jquery.cookie.js"></script>
    <script src="/js/lazyload.min.js"></script>
    <script src="/js/social-share.js"></script>
    <script src="/js/qrcode.js"></script>

    @include('components.ai-ask-modal')

    <script type="text/javascript">
        // 修复AI助手模态框闪动问题
        document.addEventListener('DOMContentLoaded', function() {
            // 确保AI助手模态框初始隐藏
            $('.ai-ask-modal').hide();
        });

        $(document).ready(function () {
            var status = '{{$status}}';
            var processNavFlag = false;
            var unableDesc = {{ $unable_desc == "true" ? 'true' : 'false' }};

            // 原有功能保持
            // 分类导航处理
            function processNav(status) {
                $('#nav').html('<li class="text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>加载中...</li>');

                $.ajax({
                    url: "{{ url('article/navinfo') }}",
                    type: 'GET',
                    data: {"_token": "{{ csrf_token() }}", "status": status},
                    success: function (result_arr) {
                        if (result_arr.code != 9999) {
                            $('#nav').html('<li class="text-center py-4 text-red-500"><i class="fas fa-exclamation-circle mr-2"></i>加载失败</li>');
                        } else {
                            processNavFlag = true;
                            $('#nav').html('');

                            $.each(result_arr.result.nav_infos, function (navId, navInfo) {
                                var itemCount = Object.getOwnPropertyNames(navInfo.list).length;
                                var li = `
                                <li class="category-item">
                                    <div class="category-header" data-category-id="${navId}">
                                        <div class="category-name">
                                            <i class="fas fa-folder"></i>
                                            ${navInfo.category_info.category_name}
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="category-count">${itemCount}</span>
                                            <i class="fas fa-chevron-right category-toggle"></i>
                                        </div>
                                    </div>
                                    <ul class="feed-list" id="category-${navId}">
                            `;

                                if (itemCount > 0) {
                                    $.each(navInfo.list, function (index, item) {
                                        var countInfo = item.feed_count > 99 ? '99+' : item.feed_count;
                                        var isActive = '{{ $feed_id }}' == item.feed_id ? 'active' : '';

                                        li += `
                                        <li class="feed-item ${isActive}">
                                            <a href="{{ url('articles') }}?feed_id=${item.feed_id}&status=${status}" class="feed-link">
                                                <i class="fas fa-rss"></i>
                                                <span class="flex-1 text-truncate-1">${item.feed_name}</span>
                                                <span class="feed-count">${countInfo}</span>
                                            </a>
                                        </li>
                                    `;
                                    });
                                }

                                li += '</ul></li>';
                                $("#nav").append(li);
                            });

                            // 初始化分类切换
                            initCategoryToggle();
                        }
                    },
                    error: function() {
                        $('#nav').html('<li class="text-center py-4 text-red-500"><i class="fas fa-exclamation-circle mr-2"></i>网络错误，请刷新重试</li>');
                    }
                });
            }

            // 分类切换功能
            function initCategoryToggle() {
                $('.category-header').click(function() {
                    var $categoryItem = $(this);
                    var $feedList = $categoryItem.next('.feed-list');
                    var $toggleIcon = $categoryItem.find('.category-toggle');

                    $feedList.toggleClass('expanded');
                    $toggleIcon.toggleClass('expanded');
                });
            }

            // 状态切换功能
            $(".set_star, .set_read, .set_read_later, .set_read_later_another").on('click', function() {
                var article_sub_id = $(this).data('article-id');
                var active = $(this).hasClass("active");
                var button = $(this);

                if ($(this).hasClass("set_star")) {
                    status = active ? "read" : "star";
                } else if ($(this).hasClass("set_read")) {
                    status = active ? "unread" : "star";
                } else if ($(this).hasClass("set_read_later") || $(this).hasClass("set_read_later_another")) {
                    status = active ? "unread" : "read_later";
                } else {
                    return '';
                }

                $.get("{{ url('/articles/status') }}/" + article_sub_id, {"status": status}, function(result_arr) {
                    if (result_arr.code != 9999) {
                        showNotification('设置失败，请重试', 'error');
                    } else {
                        if (active) {
                            button.removeClass("active");
                            showNotification('已取消状态', 'success');
                        } else {
                            button.siblings().removeClass("active");
                            button.addClass("active");
                            showNotification('设置成功', 'success');
                        }
                    }
                });
            });

            // 一键标记已读
            $("#marked_all_read").on('click', function() {
                var ids = $(this).data('ids');
                var button = $(this);
                var originalText = button.html();

                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin mr-2"></i>处理中...');

                $.get("{{ url('/articles/allstatus') }}", {"ids": ids, "status": "read"}, function(result_arr) {
                    if (result_arr.code != 9999) {
                        button.prop('disabled', false);
                        button.html(originalText);
                        showNotification('设置失败，请重试', 'error');
                    } else {
                        showNotification('全部标记为已读成功', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                });
            });

            // 修复展开收起功能
            function initExpandButtons() {
                $(".expand-btn, .read-more-btn").off('click').on('click', function() {
                    var articleId = $(this).data('article-id');
                    var $content = $("#desc" + articleId);
                    var $button = $(this);

                    $content.toggleClass('expanded');

                    if ($content.hasClass('expanded')) {
                        $button.html('<i class="fas fa-chevron-up"></i> 收起内容');
                        $button.find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');

                        // 触发图片懒加载
                        $content.find('img[data-original]').each(function() {
                            var $img = $(this);
                            if ($img.attr('src') === '/img/unable_img.png') {
                                $img.attr('src', $img.data('original'));
                            }
                        });
                    } else {
                        $button.html('<i class="fas fa-chevron-down"></i> 阅读更多');
                        $button.find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                });
            }

            // 图片屏蔽功能
            $("#unable_img").on('change', function() {
                var isChecked = $(this).is(':checked');
                $.cookie('unable_img', isChecked, { expires: 365, path: '/' });

                showNotification(isChecked ? '已屏蔽图片' : '已显示图片', 'success');
                setTimeout(function() {
                    location.reload();
                }, 500);
            });

            // 一目十行功能
            $("#unable_desc").on('change', function() {
                var isChecked = $(this).is(':checked');
                $.cookie('unable_desc', isChecked, { expires: 365, path: '/' });

                showNotification(isChecked ? '已开启一目十行' : '已关闭一目十行', 'success');
                setTimeout(function() {
                    location.reload();
                }, 500);
            });

            // 图片点击恢复功能
            $(document).on('click', '.content-preview img[data-original]', function() {
                var $img = $(this);
                if ($img.attr('src') === '/img/unable_img.png') {
                    $img.attr('src', $img.data('original'));
                    showNotification('已显示原图', 'success');
                }
            });

            // 移动端检测
            function checkMobile() {
                var ua = navigator.userAgent;
                var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);

                if (isMobile) {
                    $("#navBody").hide();
                    $(".feed-list").hide();
                } else {
                    processNav(status);
                }

                return isMobile;
            }

            // 快速订阅
            $(".feed_quick_sub").on('click', function() {
                var feed_id = $(this).data('feed-id');
                $.get("{{ url('/feeds/quickstore') }}", {"feed_id": feed_id}, function(result_arr) {
                    if (result_arr.code != 9999) {
                        showNotification(result_arr.msg, 'error');
                    } else {
                        showNotification(result_arr.msg, 'success');
                    }
                });
            });

            // 语音播放
            $(".playaudio").on('click', function() {
                var article_sub_id = $(this).data('article-id');
                var $audioPlayer = $("#audioPlayer");
                var $audio = $("#audio");

                $audio.attr("src", "/article/record/" + article_sub_id);
                $audioPlayer.addClass('active');

                // 自动播放
                $audio[0].play().catch(function(e) {
                    showNotification('音频播放失败，请点击播放按钮手动播放', 'error');
                });
            });

            // 关闭音频播放器
            $("#audioClose").on('click', function() {
                $("#audioPlayer").removeClass('active');
                $("#audio")[0].pause();
            });

            // 分享功能 - 修复显示和位置问题
            function initShareButtons() {
                $(".share-btn").off('click').on('click', function(e) {
                    e.stopPropagation();
                    var $shareMenu = $(this).siblings('.share-menu');

                    // 关闭其他分享菜单
                    $('.share-menu').not($shareMenu).removeClass('active');

                    // 切换当前分享菜单
                    $shareMenu.toggleClass('active');
                });
            }

            // 点击外部关闭分享菜单
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.share-container').length) {
                    $('.share-menu').removeClass('active');
                }
            });

            // 记录想法
            $(document).on('click', '.icon-heart', function() {
                var title = $(this).data('title');
                var id = $(this).data('id');
                var url = $(this).data('url');
                window.open('/notes?&source_type=2&source_id=' + id);
            });

            // AI助手 - 修复refer_text参数
            $(".ai-assist-btn").on('click', function() {
                var contentId = $(this).data('content-id');
                var referText = $(this).data('refer-text');
                var title = $(this).data('title');

                // 调用全局函数，传递refer_text
                openAskAIModal(contentId, referText, title);
            });

            // 修复AI助手函数
            window.openAskAIModal = function(contentId, referText, title) {
                // 获取内容
                var content = '';
                if (contentId) {
                    content = document.getElementById(contentId) ?
                        document.getElementById(contentId).innerText : '';
                }

                // 优先使用传递的referText参数
                if (!referText && content) {
                    referText = content.substring(0, 1000); // 限制长度
                }

                // 设置表单值
                if (referText) {
                    $('#ask_ai_refer_text').val(referText);
                }

                if (title) {
                    $('#ask_ai_title').val(title);
                }

                // 显示模态框
                $('#aiAskModal').addClass('show').show();

                // 防止模态框闪动
                $('#aiAskModal').css('display', 'flex');
            };

            // 通知函数
            function showNotification(message, type = 'success') {
                var bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

                var notification = $(
                    '<div class="fixed top-4 right-4 z-50 max-w-sm w-full animate-fadeIn">' +
                    '<div class="' + bgColor + ' text-white p-4 rounded-lg shadow-lg flex items-center justify-between transform translate-x-full transition-transform duration-300">' +
                    '<div class="flex items-center">' +
                    '<i class="fas ' + icon + ' mr-3"></i>' +
                    '<span class="text-sm">' + message + '</span>' +
                    '</div>' +
                    '<button class="text-white hover:text-gray-200 ml-4" onclick="$(this).closest(\'.fixed\').remove()">' +
                    '<i class="fas fa-times"></i>' +
                    '</button>' +
                    '</div>' +
                    '</div>'
                );

                $('body').append(notification);

                // 显示通知
                setTimeout(function() {
                    notification.find('div:first').removeClass('translate-x-full');
                }, 10);

                // 3秒后自动隐藏
                setTimeout(function() {
                    notification.find('div:first').addClass('translate-x-full');
                    setTimeout(function() {
                        notification.remove();
                    }, 300);
                }, 3000);
            }

            // 初始化
            checkMobile();
            initExpandButtons();
            initShareButtons();

            // 图片懒加载初始化
            if (typeof $.fn.lazyload === 'function') {
                $("img.lazy").lazyload({
                    effect: "fadeIn",
                    threshold: 200
                });
            }
        });
    </script>
@endsection


