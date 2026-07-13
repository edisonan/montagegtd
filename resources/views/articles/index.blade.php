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
            background: linear-gradient(135deg, #f4f7fb, #c0bec8);
            padding: 20px;
            color: black;
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

        .view-mode-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .view-mode-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            color: #475569;
            background: white;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .view-mode-tab:hover,
        .view-mode-tab.active {
            color: #4a90e2;
            border-color: #4a90e2;
            background: #f8fbff;
        }

        .ai-profile-badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .ai-profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #eef6ff;
            color: #2764a5;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .ai-profile-badge.muted {
            background: #f1f5f9;
            color: #64748b;
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

        .tool-icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tool-icon-btn:hover {
            border-color: #4a90e2;
            color: #4a90e2;
            background: #f8fbff;
            transform: translateY(-1px);
        }

        .tool-icon-btn.active {
            border-color: #4a90e2;
            color: #4a90e2;
            background: rgba(74, 144, 226, 0.1);
        }

        .mobile-only {
            display: none;
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
            line-height: 1.75;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            max-height: 360px;
            overflow: hidden;
            position: relative;
            transition: max-height 0.5s ease;
        }

        .content-preview.expanded {
            max-height: 5000px !important;
        }

        /* 段落优化 - 增强阅读节奏 */
        .content-preview p {
            margin-bottom: 1.25em;
            text-align: justify;
            text-justify: inter-ideograph;
        }

        /* 标题层级优化 */
        .content-preview h1,
        .content-preview h2,
        .content-preview h3,
        .content-preview h4 {
            font-weight: 600;
            margin-top: 1.5em;
            margin-bottom: 0.75em;
            line-height: 1.4;
            color: #1e293b;
        }

        .content-preview h1 { font-size: 1.6rem; margin-top: 2em; }
        .content-preview h2 { font-size: 1.4rem; }
        .content-preview h3 { font-size: 1.25rem; }
        .content-preview h4 { font-size: 1.15rem; }

        /* 列表优化 */
        .content-preview ul,
        .content-preview ol {
            padding-left: 1.5em;
            margin: 1.25em 0;
            line-height: 1.8;
        }

        .content-preview li {
            margin-bottom: 0.5em;
        }

        /* 引用块优化 */
        .content-preview blockquote {
            border-left: 4px solid #4a90e2;
            padding: 1em 1.5em;
            background-color: #f8fafc;
            margin: 1.5em 0;
            font-style: italic;
            color: #475569;
        }

        /* 代码块优化 */
        .content-preview code {
            background-color: #f1f5f9;
            padding: 0.2em 0.4em;
            border-radius: 4px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.95em;
        }

        .content-preview pre {
            background-color: #f8fafc;
            padding: 1.2em;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1.5em 0;
        }

        /* 图片优化 */
        .content-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5em auto;
            display: block;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .content-preview img:hover {
            transform: scale(1.03);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
            opacity: 1;
        }

        .content-preview.expanded .content-fade {
            opacity: 0;
        }

        .read-more {
            text-align: center;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 1px solid #f1f5f9;
            display: block !important;
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

        .article-filter-bar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 12px;
            padding: 0 0 18px;
            align-items: end;
        }

        .article-filter-group {
            min-width: 0;
        }

        .article-filter-label {
            display: block;
            margin-bottom: 6px;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .article-filter-control {
            width: 100%;
            min-height: 38px;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 13px;
        }

        .article-filter-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .article-footer.scan-collapsed {
            display: none;
        }

        .article-footer {
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end; /* 修改为靠右对齐 */
            align-items: center;
            background: #f8fafc;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-left: auto; /* 确保靠右对齐 */
            justify-content: flex-end; /* 确保内容右对齐 */
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

            .article-filter-bar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .mobile-only {
                display: inline-flex;
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
                margin-left: 0;
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

            .article-filter-bar {
                grid-template-columns: 1fr;
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
        .ai-ask-modal {
            display: none;
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

        .preference-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .preference-label {
            color: #334155;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .preference-input {
            width: 100%;
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 12px;
            color: #1e293b;
            background: white;
            font-size: 0.9rem;
            resize: vertical;
        }

        .preference-input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.12);
        }

        .preference-hint {
            color: #64748b;
            font-size: 0.75rem;
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

        .playaudio {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .playaudio.playing {
            color: var(--primary-color);
            background-color: rgba(var(--primary-color-rgb), 0.1);
        }

        .playaudio:hover {
            transform: scale(1.05);
        }

        /* 语音控制面板 */
        .speech-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 10px;
            background: var(--gray-50);
            border-radius: 8px;
            margin-top: 10px;
        }

        .speech-controls button {
            padding: 6px 12px;
            border: 1px solid var(--gray-300);
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .speech-controls button:hover {
            background: var(--gray-100);
        }

        .speech-controls button.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 reading-page">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- 侧边栏导航 -->
            <div class="lg:col-span-1" id="sidebarColumn">
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
                <div class="reading-content">
                    <!-- 内容头部 -->
                    <div class="content-header">
                        <div>
                            <div class="status-tabs">
                                <a href="#" class="status-tab" data-status="unread">
                                    未读
                                </a>
                                <a href="#" class="status-tab" data-status="read">
                                    已读
                                </a>
                                <a href="#" class="status-tab" data-status="star">
                                    加星
                                </a>
                                <a href="#" class="status-tab" data-status="read_later">
                                    稍后阅读
                                </a>
                            </div>
                            <div class="view-mode-tabs" id="viewModeTabs">
                                <a href="#" class="view-mode-tab" data-view-mode="all"><i class="fas fa-layer-group"></i>全部</a>
                                <a href="#" class="view-mode-tab" data-view-mode="personalized"><i class="fas fa-bolt"></i>为我优先</a>
                                <a href="#" class="view-mode-tab" data-view-mode="tech"><i class="fas fa-code"></i>技术</a>
                                <a href="#" class="view-mode-tab" data-view-mode="product"><i class="fas fa-cube"></i>产品</a>
                                <a href="#" class="view-mode-tab" data-view-mode="read_later_suggest"><i class="far fa-clock"></i>稍后读建议</a>
                                <a href="#" class="view-mode-tab" data-view-mode="low_priority"><i class="fas fa-filter"></i>低优先级</a>
                                <button type="button" class="view-mode-tab" id="readingPreferenceBtnInline"><i class="fas fa-sliders-h"></i>偏好设置</button>
                            </div>
                        </div>

                        <div class="content-tools">
                            <div class="tool-item">
                                <span class="tool-label">一目十行</span>
                                <input type="checkbox" id="unable_desc" class="hidden">
                                <button type="button" id="unable_desc_btn" class="tool-icon-btn" title="一目十行">
                                    <i class="fas fa-align-left"></i>
                                </button>
                            </div>

                            <div class="tool-item">
                                <span class="tool-label">屏蔽图片</span>
                                <input type="checkbox" id="unable_img" class="hidden">
                                <button type="button" id="unable_img_btn" class="tool-icon-btn" title="屏蔽图片">
                                    <i class="fas fa-image"></i>
                                </button>
                            </div>

                            <div class="tool-actions">
                                <a href="{{ url('articles/stream') }}" class="tool-btn" id="streamModeBtn">
                                    <i class="fas fa-mobile-screen-button"></i>
                                    沉浸刷文
                                </a>
                                <a href="{{ url('feeds/explorer') }}" class="tool-btn" id="discoverBtn" style="display: none;">
                                    <i class="fas fa-compass"></i>
                                    发现
                                    <sup style="color: #ef4444; margin-left: 2px;">推荐</sup>
                                </a>
                                <a href="{{ url('feeds') }}" class="tool-btn">
                                    <i class="fas fa-plus"></i>
                                    添加订阅
                                </a>
                                <button type="button" class="tool-btn" id="readingPreferenceBtn">
                                    <i class="fas fa-sliders-h"></i>
                                    阅读偏好
                                </button>
                                <button type="button" class="tool-btn mobile-only" id="toggleCategoryBtn">
                                    <i class="fas fa-folder-tree"></i>
                                    订阅分类
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 文章列表 -->
                    <div class="p-6">
                        <form id="articleFilters" class="article-filter-bar">
                            <div class="article-filter-group">
                                <label class="article-filter-label" for="articleTimeRange">时间</label>
                                <select class="article-filter-control" id="articleTimeRange" name="time_range">
                                    <option value="all">全部时间</option>
                                    <option value="3h">最近 3 小时</option>
                                    <option value="6h">最近 6 小时</option>
                                    <option value="1d">最近 1 天</option>
                                    <option value="3d">最近 3 天</option>
                                    <option value="7d">最近 7 天</option>
                                </select>
                            </div>
                            <div class="article-filter-group">
                                <label class="article-filter-label" for="articleCategoryFilter">分类</label>
                                <select class="article-filter-control" id="articleCategoryFilter" name="category_id">
                                    <option value="">全部分类</option>
                                </select>
                            </div>
                            <div class="article-filter-group">
                                <label class="article-filter-label" for="articleReadDuration">时长</label>
                                <select class="article-filter-control" id="articleReadDuration" name="read_duration">
                                    <option value="all">不限</option>
                                    <option value="short">5 分钟以内</option>
                                    <option value="medium">6-15 分钟</option>
                                    <option value="long">16 分钟以上</option>
                                </select>
                            </div>
                            <div class="article-filter-group">
                                <label class="article-filter-label" for="articlePageCount">数量</label>
                                <input class="article-filter-control" id="articlePageCount" name="page_count" type="number" min="1" max="100" step="1">
                            </div>
                            <div class="article-filter-actions">
                                <button type="submit" class="btn btn-primary px-4 py-2 rounded-lg">
                                    <i class="fas fa-filter mr-1"></i>筛选
                                </button>
                            </div>
                        </form>
                        <div id="articleLoading" class="text-center py-12 text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>加载中...
                        </div>
                        <div id="articleList"></div>
                        <div class="pagination-wrapper" id="articlePagination" style="display: none;"></div>
                        <div class="mt-6 text-center" id="markAllWrap" style="display: none;">
                            <button type="button"
                                    class="btn btn-primary px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-300"
                                    id="marked_all_read"
                                    data-ids="">
                                <i class="fas fa-check-double mr-2"></i>
                                标记全部已读
                            </button>
                        </div>
                        <div class="empty-state" id="articleEmptyState" style="display: none;">
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
    <div id="readingPreferenceModal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" id="readingPreferenceBackdrop"></div>
        <div class="absolute inset-0 p-3 sm:p-6 overflow-y-auto">
            <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">阅读偏好</h3>
                        <p class="text-xs text-gray-500 mt-1">用于“为我优先”和汇合页候选筛选</p>
                    </div>
                    <button type="button" class="action-btn" id="readingPreferenceCloseBtn" title="关闭">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-5 space-y-4">
                    <div id="readingPreferenceLoading" class="text-center py-6 text-gray-500 hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>加载中...
                    </div>
                    <div id="readingPreferenceForm" class="space-y-4">
                        <div class="preference-field">
                            <label class="preference-label" for="preferenceTopics">关注主题</label>
                            <textarea id="preferenceTopics" class="preference-input" rows="2" placeholder="AI, LLM, Agent, Laravel"></textarea>
                            <div class="preference-hint">用逗号、顿号或换行分隔。</div>
                        </div>
                        <div class="preference-field">
                            <label class="preference-label" for="preferenceIncludeKeywords">加权关键词</label>
                            <textarea id="preferenceIncludeKeywords" class="preference-input" rows="2" placeholder="OpenAI, Claude, 提示词, 产品设计"></textarea>
                        </div>
                        <div class="preference-field">
                            <label class="preference-label" for="preferenceExcludeKeywords">排除/降权关键词</label>
                            <textarea id="preferenceExcludeKeywords" class="preference-input" rows="2" placeholder="招聘, 广告, 融资快讯"></textarea>
                        </div>
                        <div class="preference-field">
                            <label class="preference-label" for="preferenceCategories">偏好分类</label>
                            <input id="preferenceCategories" class="preference-input" value="AI, 后端, 前端, 产品">
                            <div class="preference-hint">常用分类：AI、后端、前端、产品。</div>
                        </div>
                    </div>
                    <div id="readingPreferenceError" class="hidden text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="text-xs text-gray-500">保存后会切换到“为我优先”查看。</div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-outline btn-sm" id="readingPreferenceCancelBtn">取消</button>
                        <button type="button" class="btn btn-primary btn-sm" id="readingPreferenceSaveBtn">
                            <i class="fas fa-save mr-1"></i>保存偏好
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="articleMindmapModal" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" id="articleMindmapBackdrop"></div>
        <div class="absolute inset-0 p-3 sm:p-6 overflow-y-auto">
            <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">AI生成思维导图</h3>
                        <p class="text-xs text-gray-500 mt-1" id="articleMindmapTitle">-</p>
                    </div>
                    <button type="button" class="action-btn" id="articleMindmapCloseBtn" title="关闭">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <div class="text-sm text-gray-600" id="articleMindmapStatus">准备生成导图...</div>
                </div>
                <div class="p-5">
                    <div id="articleMindmapLoading" class="text-center py-10 hidden">
                        <i class="fas fa-spinner fa-spin text-gray-500 text-2xl"></i>
                        <p class="text-sm text-gray-500 mt-2">AI正在分析文章并生成结构...</p>
                    </div>
                    <div id="articleMindmapPreviewWrap" class="hidden">
                        <div class="article-mind-root" id="articleMindmapPreview"></div>
                    </div>
                    <div id="articleMindmapError" class="hidden text-red-600 text-sm bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="text-xs text-gray-500">保存后可在思维导图页继续编辑</div>
                    <div class="flex items-center gap-2">
                        <a href="javascript:void(0);" id="articleMindmapEditLink" class="btn btn-outline btn-sm hidden" target="_blank">
                            <i class="fas fa-pen mr-1"></i>打开编辑
                        </a>
                        <button type="button" class="btn btn-outline btn-sm" id="articleMindmapRegenerateBtn">
                            <i class="fas fa-sync-alt mr-1"></i>重新生成
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="articleMindmapSaveBtn">
                            <i class="fas fa-save mr-1"></i>一键保存
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .article-mind-root {
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f8fafc;
            max-height: 60vh;
            overflow: auto;
        }
        .article-mind-node {
            margin: 8px 0 8px 0;
            padding-left: 14px;
            border-left: 2px dashed #cbd5e1;
        }
        .article-mind-topic {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.4;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .article-mind-node.level-0 > .article-mind-topic {
            background: #eff6ff;
            border-color: #93c5fd;
            font-weight: 600;
            font-size: 14px;
        }
        .article-mind-children {
            margin-top: 6px;
            margin-left: 10px;
        }
    </style>

    <script type="text/javascript">
        // 修复AI助手模态框闪动问题
        document.addEventListener('DOMContentLoaded', function() {
            // 确保AI助手模态框初始隐藏
            $('.ai-ask-modal').hide();

            if (localStorage.getItem('scrollToTopAfterMarkAllRead') === 'true') {
                // 延迟确保DOM渲染完成
                setTimeout(() => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'  // 添加平滑滚动效果
                    });
                    localStorage.removeItem('scrollToTopAfterMarkAllRead');
                    console.log('已滚动到顶部');
                }, 300);  // 增加延迟时间确保页面完全加载
            }
        });

        $(document).ready(function () {
            var qs = new URLSearchParams(window.location.search);
            var status = qs.get('status') || 'unread';
            var currentFeedId = qs.get('feed_id') || '';
            var timeRange = qs.get('time_range') || '6h';
            var categoryId = qs.get('category_id') || '';
            var readDuration = qs.get('read_duration') || 'all';
            var pageCount = normalizePageCount(qs.get('page_count') || 20);
            var currentPage = Number(qs.get('page') || 1);
            var viewMode = qs.get('view_mode') || 'all';
            var allowedViewModes = ['all', 'personalized', 'tech', 'product', 'read_later_suggest', 'low_priority'];
            if (allowedViewModes.indexOf(viewMode) === -1) {
                viewMode = 'all';
            }
            var processNavFlag = false;
            var unableDesc = ($.cookie('unable_desc') || 'false') === 'true';
            var unableImg = ($.cookie('unable_img') || 'false') === 'true';
            var readingPreferenceState = {
                isLoading: false,
                isSaving: false
            };

            var apiRequest = window.TaskApiBridge && typeof window.TaskApiBridge.requestWithFallback === 'function'
                ? window.TaskApiBridge.requestWithFallback
                : function() { return Promise.reject(new Error("API客户端未初始化")); };
            var articleMindState = {
                title: '',
                referText: '',
                contentId: '',
                articleSubId: '',
                tree: null,
                isGenerating: false,
                isSaving: false,
                savedMindId: null
            };

            function normalizePageCount(value) {
                var count = parseInt(value, 10);
                if (!count || count < 1) {
                    return 20;
                }
                if (count > 100) {
                    return 100;
                }
                return count;
            }

            function collectArticleFilters() {
                return {
                    time_range: $('#articleTimeRange').val() || timeRange || '6h',
                    category_id: $('#articleCategoryFilter').val() || categoryId || '',
                    read_duration: $('#articleReadDuration').val() || readDuration || 'all',
                    page_count: normalizePageCount($('#articlePageCount').val() || pageCount || 20)
                };
            }

            function syncArticleFilterForm() {
                $('#articleTimeRange').val(timeRange || '6h');
                $('#articleCategoryFilter').val(categoryId || '');
                $('#articleReadDuration').val(readDuration || 'all');
                $('#articlePageCount').val(String(pageCount || 20));
            }

            function setMindmapStatus(message) {
                $('#articleMindmapStatus').text(message || '');
            }

            function showMindmapModal() {
                $('#articleMindmapModal').removeClass('hidden');
            }

            function hideMindmapModal() {
                $('#articleMindmapModal').addClass('hidden');
            }

            function showReadingPreferenceModal() {
                $('#readingPreferenceModal').removeClass('hidden');
            }

            function hideReadingPreferenceModal() {
                $('#readingPreferenceModal').addClass('hidden');
                $('#readingPreferenceError').addClass('hidden').text('');
            }

            function splitPreferenceInput(value) {
                return String(value || '')
                    .split(/[\n,，、;；]+/)
                    .map(function(item) { return item.replace(/\s+/g, ' ').trim(); })
                    .filter(function(item, index, arr) {
                        return item !== '' && arr.indexOf(item) === index;
                    });
            }

            function joinPreferenceItems(items) {
                if (!Array.isArray(items)) {
                    return '';
                }
                return items.filter(function(item) {
                    return String(item || '').trim() !== '';
                }).join(', ');
            }

            function fillReadingPreferenceForm(profile) {
                profile = profile || {};
                $('#preferenceTopics').val(joinPreferenceItems(profile.topics_json || profile.topics || []));
                $('#preferenceIncludeKeywords').val(joinPreferenceItems(profile.include_keywords_json || profile.include_keywords || []));
                $('#preferenceExcludeKeywords').val(joinPreferenceItems(profile.exclude_keywords_json || profile.exclude_keywords || []));
                $('#preferenceCategories').val(joinPreferenceItems(profile.preferred_categories_json || profile.preferred_categories || ['AI', '后端', '前端', '产品']));
            }

            function setReadingPreferenceBusy(isBusy) {
                readingPreferenceState.isLoading = !!isBusy;
                $('#readingPreferenceLoading').toggleClass('hidden', !isBusy);
                $('#readingPreferenceForm').toggleClass('hidden', !!isBusy);
                $('#readingPreferenceSaveBtn').prop('disabled', !!isBusy || readingPreferenceState.isSaving);
            }

            function loadReadingPreference() {
                if (readingPreferenceState.isLoading) return;
                $('#readingPreferenceError').addClass('hidden').text('');
                setReadingPreferenceBusy(true);
                apiRequest('GET', '/digest/profile', {}).then(function(result) {
                    if (!result || result.code !== 9999 || !result.result) {
                        throw new Error((result && result.msg) ? result.msg : '读取偏好失败');
                    }
                    fillReadingPreferenceForm(result.result.profile || {});
                }).catch(function(error) {
                    fillReadingPreferenceForm(null);
                    $('#readingPreferenceError').removeClass('hidden').text((error && error.message) ? error.message : '读取偏好失败');
                }).finally(function() {
                    setReadingPreferenceBusy(false);
                });
            }

            function saveReadingPreference() {
                if (readingPreferenceState.isSaving) return;
                readingPreferenceState.isSaving = true;
                $('#readingPreferenceError').addClass('hidden').text('');
                var $btn = $('#readingPreferenceSaveBtn');
                var originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>保存中');

                var payload = {
                    enabled: true,
                    topics: splitPreferenceInput($('#preferenceTopics').val()),
                    include_keywords: splitPreferenceInput($('#preferenceIncludeKeywords').val()),
                    exclude_keywords: splitPreferenceInput($('#preferenceExcludeKeywords').val()),
                    preferred_categories: splitPreferenceInput($('#preferenceCategories').val()),
                    time_window_days: 7,
                    frequency: 'daily',
                    max_articles: 20,
                    output_style: 'concise'
                };

                apiRequest('POST', '/digest/profile', payload).then(function(result) {
                    if (!result || result.code !== 9999) {
                        throw new Error((result && result.msg) ? result.msg : '保存偏好失败');
                    }
                    showNotification('阅读偏好已保存', 'success');
                    hideReadingPreferenceModal();
                    var params = new URLSearchParams(window.location.search);
                    params.set('status', status);
                    params.set('view_mode', 'personalized');
                    params.set('page', '1');
                    window.location.href = '/articles?' + params.toString();
                }).catch(function(error) {
                    $('#readingPreferenceError').removeClass('hidden').text((error && error.message) ? error.message : '保存偏好失败');
                    showNotification('保存偏好失败', 'error');
                }).finally(function() {
                    readingPreferenceState.isSaving = false;
                    $btn.prop('disabled', false).html(originalHtml);
                });
            }

            function sanitizeTopic(text, fallbackText) {
                var topic = String(text || '').replace(/\s+/g, ' ').trim();
                if (!topic) {
                    topic = String(fallbackText || '导图节点').trim();
                }
                if (topic.length > 36) {
                    topic = topic.slice(0, 36);
                }
                return topic || '导图节点';
            }

            function sanitizeRemark(text) {
                var remark = String(text || '').replace(/\s+/g, ' ').trim();
                if (!remark) return '';
                if (remark.length > 180) {
                    remark = remark.slice(0, 180);
                }
                return remark;
            }

            function normalizeMindTree(input, depth) {
                var currentDepth = Number(depth || 0);
                if (currentDepth > 3) return null;

                if (typeof input === 'string') {
                    return { topic: sanitizeTopic(input), content: '', children: [] };
                }
                if (!input || typeof input !== 'object') return null;

                var topic = sanitizeTopic(input.topic || input.title || input.name || input.text, '导图节点');
                var content = sanitizeRemark(input.content || input.remark || input.note || input.summary || '');
                var rawChildren = input.children || input.nodes || input.items || [];
                var children = [];
                if (Array.isArray(rawChildren)) {
                    for (var i = 0; i < rawChildren.length; i++) {
                        var childNode = normalizeMindTree(rawChildren[i], currentDepth + 1);
                        if (childNode) children.push(childNode);
                        if (children.length >= 8) break;
                    }
                }
                return { topic: topic, content: content, children: children };
            }

            function extractJsonObject(text) {
                var raw = String(text || '').trim();
                if (!raw) return null;
                raw = raw.replace(/```json/ig, '').replace(/```/g, '').trim();
                var start = raw.indexOf('{');
                var end = raw.lastIndexOf('}');
                if (start === -1 || end === -1 || end <= start) return null;
                try {
                    return JSON.parse(raw.slice(start, end + 1));
                } catch (e) {
                    return null;
                }
            }

            function buildFallbackMindTree(title, referText) {
                var cleanTitle = sanitizeTopic(title, '文章导图');
                var text = String(referText || '').replace(/\s+/g, ' ').trim();
                var parts = text.split(/[。！？；\n]/).map(function(item) { return item.trim(); }).filter(Boolean);
                var children = [];
                var maxChildren = Math.min(5, parts.length);
                for (var i = 0; i < maxChildren; i++) {
                    children.push({ topic: sanitizeTopic(parts[i], '要点' + (i + 1)), content: sanitizeRemark(parts[i]), children: [] });
                }
                if (children.length === 0) {
                    children.push({ topic: '核心观点', content: '', children: [] });
                    children.push({ topic: '关键细节', content: '', children: [] });
                    children.push({ topic: '可行动事项', content: '', children: [] });
                }
                return { topic: cleanTitle, content: '', children: children };
            }

            function renderMindTree(node, depth) {
                var level = Number(depth || 0);
                var html = '<div class="article-mind-node level-' + level + '"><div class="article-mind-topic">' + escapeHtml(node.topic || '') + '</div>';
                if (node.content) {
                    html += '<div class="text-xs text-gray-600 mt-1 leading-5">' + escapeHtml(node.content) + '</div>';
                }
                if (Array.isArray(node.children) && node.children.length > 0) {
                    html += '<div class="article-mind-children">';
                    for (var i = 0; i < node.children.length; i++) {
                        html += renderMindTree(node.children[i], level + 1);
                    }
                    html += '</div>';
                }
                html += '</div>';
                return html;
            }

            function updateMindmapPreview(tree) {
                if (!tree) {
                    $('#articleMindmapPreviewWrap').addClass('hidden');
                    $('#articleMindmapPreview').empty();
                    return;
                }
                $('#articleMindmapPreview').html(renderMindTree(tree, 0));
                $('#articleMindmapPreviewWrap').removeClass('hidden');
            }

            async function ensureAiSession() {
                if (typeof window.createNewSession === 'function') {
                    return await window.createNewSession('builtin_common');
                }
                const response = await window.taskApiFetch('/api/v2/llm/sessions', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        agent_id: 'builtin_common',
                        title: '文章导图生成'
                    })
                });
                const result = await response.json();
                if (!response.ok || !result || result.success !== true || !result.data || !result.data.id) {
                    throw new Error((result && result.message) ? result.message : '创建AI会话失败');
                }
                return result.data.id;
            }

            async function requestMindTreeFromAi(title, referText) {
                var query = [
                    '请根据我提供的文章标题和内容，生成思维导图。',
                    '只返回JSON，不要解释，不要markdown。',
                    '格式: {"topic":"根节点","content":"节点备注","children":[{"topic":"一级节点","content":"备注","children":[{"topic":"二级节点","content":"备注","children":[]}]}]}',
                    '要求: 中文；层级不超过3层；一级节点3-6个；topic简洁；关键节点要给content备注（20-80字，可为空字符串）。'
                ].join('\n');

                var sessionId = await ensureAiSession();
                var response = await window.taskApiFetch('/api/v2/llm/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        session_id: sessionId,
                        refer_text: ('文章标题：' + title + '\n\n文章内容：\n' + String(referText || '')).slice(0, 6000),
                        query: query
                    })
                });

                if (!response.ok) {
                    throw new Error('AI请求失败，状态码: ' + response.status);
                }

                var contentType = String(response.headers.get('content-type') || '');
                if (!contentType.includes('text/event-stream')) {
                    throw new Error('AI返回格式异常');
                }

                var reader = response.body.getReader();
                var decoder = new TextDecoder();
                var buffer = '';
                var finalText = '';
                var reasoningText = '';
                var doneSignal = false;

                while (!doneSignal) {
                    var chunk = await reader.read();
                    if (chunk.done) break;
                    buffer += decoder.decode(chunk.value, { stream: true });
                    var lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (var i = 0; i < lines.length; i++) {
                        var line = String(lines[i] || '').trim();
                        if (!line || line.indexOf('data:') !== 0) continue;
                        var payload = line.slice(5).trim();
                        if (payload === '[DONE]') {
                            doneSignal = true;
                            break;
                        }
                        if (!payload || payload.charAt(0) !== '{') continue;
                        try {
                            var data = JSON.parse(payload);
                            var delta = data && data.choices && data.choices[0] && data.choices[0].delta ? data.choices[0].delta : {};
                            var piece = '';
                            if (typeof delta.content === 'string' && delta.content) {
                                piece = delta.content;
                            } else if (typeof data.content === 'string' && data.content) {
                                piece = data.content;
                            } else if (data.message && typeof data.message.content === 'string' && data.message.content) {
                                piece = data.message.content;
                            }
                            if (piece) finalText += piece;
                            if (typeof delta.reasoning === 'string' && delta.reasoning) {
                                reasoningText += delta.reasoning;
                            }
                        } catch (e) {}
                    }
                }

                console.info('[ArticleMindmap] AI raw content output:', finalText);
                if (reasoningText) {
                    console.info('[ArticleMindmap] AI reasoning output:', reasoningText);
                }
                var parsed = extractJsonObject(finalText);
                if (!parsed) {
                    var rawPreview = String(finalText || '').replace(/\s+/g, ' ').trim().slice(0, 800);
                    throw new Error('AI输出无法解析为导图JSON。原始输出预览：' + (rawPreview || '[empty]'));
                }

                var normalized = normalizeMindTree(parsed, 0);
                if (!normalized || !Array.isArray(normalized.children) || normalized.children.length === 0) {
                    throw new Error('AI导图结构为空。请检查AI输出结构是否包含children节点。');
                }
                if (!normalized.topic || normalized.topic === '导图节点') {
                    normalized.topic = sanitizeTopic(title, '文章导图');
                }
                return normalized;
            }

            async function createMindNode(name, parentMindId, content, sourceType, sourceId) {
                var payload = {
                    name: sanitizeTopic(name, '导图节点'),
                    content: sanitizeRemark(content || '')
                };
                if (parentMindId) payload.parent_mind_id = parentMindId;
                if (sourceType) payload.source_type = sourceType;
                if (sourceId) payload.source_id = Number(sourceId);
                var result = await apiRequest('POST', '/minds', payload);
                if (!result || result.code !== 9999 || !result.result || !result.result.id) {
                    throw new Error((result && result.msg) ? result.msg : '保存导图节点失败');
                }
                return Number(result.result.id);
            }

            async function saveMindTreeToServer(tree) {
                var sourceType = 'article';
                var sourceId = Number(articleMindState.articleSubId || 0);
                var rootId = await createMindNode(tree.topic || '文章导图', 0, tree.content || '', sourceType, sourceId);
                async function createChildren(parentId, children) {
                    if (!Array.isArray(children) || children.length === 0) return;
                    for (var i = 0; i < children.length; i++) {
                        var child = children[i];
                        var childId = await createMindNode(child.topic || ('节点' + (i + 1)), parentId, child.content || '', sourceType, sourceId);
                        await createChildren(childId, child.children || []);
                    }
                }
                await createChildren(rootId, tree.children || []);
                return rootId;
            }

            async function generateArticleMindmap() {
                if (articleMindState.isGenerating) return;
                articleMindState.isGenerating = true;
                articleMindState.savedMindId = null;
                $('#articleMindmapEditLink').addClass('hidden').attr('href', 'javascript:void(0);');
                $('#articleMindmapError').addClass('hidden').text('');
                $('#articleMindmapLoading').removeClass('hidden');
                updateMindmapPreview(null);
                setMindmapStatus('AI生成中，请稍候...');

                try {
                    var tree = await requestMindTreeFromAi(articleMindState.title, articleMindState.referText);
                    articleMindState.tree = tree;
                    updateMindmapPreview(tree);
                    setMindmapStatus('导图生成完成，可保存后继续编辑。');
                } catch (error) {
                    var fallbackTree = buildFallbackMindTree(articleMindState.title, articleMindState.referText);
                    articleMindState.tree = fallbackTree;
                    updateMindmapPreview(fallbackTree);
                    $('#articleMindmapError').removeClass('hidden').text('AI生成失败，已使用降级结构。失败原因：' + (error && error.message ? error.message : '未知错误'));
                    setMindmapStatus('已展示降级导图，可直接保存后再补充。');
                } finally {
                    $('#articleMindmapLoading').addClass('hidden');
                    articleMindState.isGenerating = false;
                }
            }

            function escapeHtml(text) {
                return String(text || '').replace(/[&<>"']/g, function(c) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[c];
                });
            }

            function renderStatusTabs() {
                var filters = collectArticleFilters();
                $('.status-tab').each(function() {
                    var tabStatus = $(this).data('status');
                    var url = '/articles?status=' + encodeURIComponent(tabStatus);
                    if (currentFeedId) {
                        url += '&feed_id=' + encodeURIComponent(currentFeedId);
                    }
                    if (viewMode && viewMode !== 'all') {
                        url += '&view_mode=' + encodeURIComponent(viewMode);
                    }
                    if (filters.time_range && filters.time_range !== 'all') {
                        url += '&time_range=' + encodeURIComponent(filters.time_range);
                    }
                    if (filters.category_id) {
                        url += '&category_id=' + encodeURIComponent(filters.category_id);
                    }
                    if (filters.read_duration && filters.read_duration !== 'all') {
                        url += '&read_duration=' + encodeURIComponent(filters.read_duration);
                    }
                    if (filters.page_count && filters.page_count !== 20) {
                        url += '&page_count=' + encodeURIComponent(String(filters.page_count));
                    }
                    $(this).attr('href', url);
                    $(this).toggleClass('active', tabStatus === status);
                });

                $('.view-mode-tab').each(function() {
                    var tabMode = $(this).data('view-mode');
                    var url = '/articles?status=' + encodeURIComponent(status);
                    if (currentFeedId) {
                        url += '&feed_id=' + encodeURIComponent(currentFeedId);
                    }
                    if (tabMode && tabMode !== 'all') {
                        url += '&view_mode=' + encodeURIComponent(tabMode);
                    }
                    if (filters.time_range && filters.time_range !== 'all') {
                        url += '&time_range=' + encodeURIComponent(filters.time_range);
                    }
                    if (filters.category_id) {
                        url += '&category_id=' + encodeURIComponent(filters.category_id);
                    }
                    if (filters.read_duration && filters.read_duration !== 'all') {
                        url += '&read_duration=' + encodeURIComponent(filters.read_duration);
                    }
                    if (filters.page_count && filters.page_count !== 20) {
                        url += '&page_count=' + encodeURIComponent(String(filters.page_count));
                    }
                    $(this).attr('href', url);
                    $(this).toggleClass('active', tabMode === viewMode);
                });

                var streamUrl = '/articles/stream?status=' + encodeURIComponent(status);
                if (currentFeedId) {
                    streamUrl += '&feed_id=' + encodeURIComponent(currentFeedId);
                }
                if (viewMode && viewMode !== 'all') {
                    streamUrl += '&view_mode=' + encodeURIComponent(viewMode);
                }
                if (filters.time_range && filters.time_range !== 'all') {
                    streamUrl += '&time_range=' + encodeURIComponent(filters.time_range);
                }
                if (filters.category_id) {
                    streamUrl += '&category_id=' + encodeURIComponent(filters.category_id);
                }
                if (filters.read_duration && filters.read_duration !== 'all') {
                    streamUrl += '&read_duration=' + encodeURIComponent(filters.read_duration);
                }
                if (filters.page_count && filters.page_count !== 20) {
                    streamUrl += '&page_count=' + encodeURIComponent(String(filters.page_count));
                }
                if (currentPage && currentPage !== 1) {
                    streamUrl += '&page=' + encodeURIComponent(String(currentPage));
                }
                $('#streamModeBtn').attr('href', streamUrl);
            }

            function buildPageUrl(page) {
                var params = new URLSearchParams(window.location.search);
                params.set('status', status);
                if (currentFeedId) {
                    params.set('feed_id', currentFeedId);
                } else {
                    params.delete('feed_id');
                }
                var filters = collectArticleFilters();
                if (filters.time_range && filters.time_range !== 'all') {
                    params.set('time_range', filters.time_range);
                } else {
                    params.delete('time_range');
                }
                if (filters.category_id) {
                    params.set('category_id', filters.category_id);
                } else {
                    params.delete('category_id');
                }
                if (filters.read_duration && filters.read_duration !== 'all') {
                    params.set('read_duration', filters.read_duration);
                } else {
                    params.delete('read_duration');
                }
                params.set('page_count', String(filters.page_count));
                if (viewMode && viewMode !== 'all') {
                    params.set('view_mode', viewMode);
                } else {
                    params.delete('view_mode');
                }
                params.set('page', String(page));
                return '/articles?' + params.toString();
            }

            // 存储的键名
            const NAV_STORAGE_KEY = 'nav_storage_data';
            const NAV_STORAGE_TIMESTAMP_KEY = 'nav_storage_timestamp';
            const STORAGE_EXPIRY_HOURS = 2; // 存储过期时间（小时）

            // 主处理函数 - 优先从localStorage加载，没有则请求远程
            function processNav(status) {
                $('#nav').html('<li class="text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>加载中...</li>');

                // 尝试从localStorage获取缓存数据
                const cachedData = getNavFromStorage(status);

                if (cachedData) {
                    // 使用缓存数据渲染导航
                    renderNav(cachedData, status);
                    // 可选：后台静默更新（不阻塞界面）
                    // setTimeout(() => fetchNavFromRemote(status, true), 100);
                } else {
                    // 没有缓存或已过期，请求远程数据
                    fetchNavFromRemote(status, false);
                }
            }

            // 主动请求文章列表接口，确保页面核心数据走 /api/v2/articles
            function loadArticleListByApi() {
                $('#articleLoading').show();
                $('#articleEmptyState').hide();
                $('#articleList').empty();
                $('#articlePagination').hide().empty();

                var filters = collectArticleFilters();
                timeRange = filters.time_range;
                categoryId = filters.category_id;
                readDuration = filters.read_duration;
                pageCount = filters.page_count;

                var params = {
                    status: status,
                    feed_id: currentFeedId,
                    time_range: timeRange,
                    category_id: categoryId,
                    read_duration: readDuration,
                    page_count: pageCount,
                    page: currentPage,
                    view_mode: viewMode
                };

                apiRequest('GET', '/articles', params).then(function(result_arr) {
                    if (!result_arr || result_arr.code !== 9999 || !result_arr.result) {
                        throw new Error((result_arr && result_arr.msg) ? result_arr.msg : '加载失败');
                    }

                    var articleSubs = result_arr.result.articles || [];
                    renderArticleList(articleSubs);
                    renderPagination(result_arr.result.pagination || null);

                    var ids = [];
                    for (var i = 0; i < articleSubs.length; i++) {
                        if (articleSubs[i] && articleSubs[i].id) {
                            ids.push(articleSubs[i].id);
                        }
                    }

                    if (status === 'unread' && ids.length > 0) {
                        $('#markAllWrap').show();
                        $('#marked_all_read').attr('data-ids', ids.join(','));
                    } else {
                        $('#markAllWrap').hide();
                    }
                    $('#articleLoading').hide();
                }).catch(function() {
                    $('#articleLoading').hide();
                    $('#articleEmptyState').show();
                    showNotification('加载文章列表失败，请稍后重试', 'error');
                });
            }

            function renderArticleList(articleSubs) {
                if (!Array.isArray(articleSubs) || articleSubs.length === 0) {
                    $('#articleEmptyState').show();
                    return;
                }

                var html = '';
                articleSubs.forEach(function(articleSub) {
                    if (!articleSub || !articleSub.article || !articleSub.article.feed) return;
                    var article = articleSub.article;
                    var subId = Number(articleSub.id || 0);
                    var articleId = Number(article.id || 0);
                    var contentHtml = String(article.formatted_content || article.content || '');

                    if (unableImg) {
                        contentHtml = contentHtml.replace(/src="([^"]*)"/g, 'src="/img/unable_img.png" data-original="$1"');
                        contentHtml = contentHtml.replace(/src='([^']*)'/g, "src='/img/unable_img.png' data-original='$1'");
                    }

                    var textLen = $('<div>').html(contentHtml).text().trim().length;
                    var needsCollapse = unableDesc && textLen > 500;
                    var footerClass = unableDesc && needsCollapse ? ' scan-collapsed' : '';
                    var sourceHref = '/articles?status=unread&feed_id=' + encodeURIComponent(article.feed.id);
                    if (timeRange && timeRange !== 'all') {
                        sourceHref += '&time_range=' + encodeURIComponent(timeRange);
                    }
                    if (categoryId) {
                        sourceHref += '&category_id=' + encodeURIComponent(categoryId);
                    }
                    if (readDuration && readDuration !== 'all') {
                        sourceHref += '&read_duration=' + encodeURIComponent(readDuration);
                    }
                    if (pageCount && pageCount !== 20) {
                        sourceHref += '&page_count=' + encodeURIComponent(String(pageCount));
                    }
                    if (viewMode && viewMode !== 'all') {
                        sourceHref += '&view_mode=' + encodeURIComponent(viewMode);
                    }
                    var originUrl = String(article.url || '#');
                    var aiProfile = article.ai_profile || null;
                    var aiBadges = renderAiProfileBadges(aiProfile, articleSub.personalized_score);
                    var streamHref = '/articles/stream?status=' + encodeURIComponent(status) + (currentFeedId ? '&feed_id=' + encodeURIComponent(currentFeedId) : '') + '&page_count=' + encodeURIComponent(String(pageCount)) + '&page=' + encodeURIComponent(String(currentPage)) + '&article_sub_id=' + subId;
                    if (timeRange && timeRange !== 'all') {
                        streamHref += '&time_range=' + encodeURIComponent(timeRange);
                    }
                    if (categoryId) {
                        streamHref += '&category_id=' + encodeURIComponent(categoryId);
                    }
                    if (readDuration && readDuration !== 'all') {
                        streamHref += '&read_duration=' + encodeURIComponent(readDuration);
                    }
                    if (viewMode && viewMode !== 'all') {
                        streamHref += '&view_mode=' + encodeURIComponent(viewMode);
                    }

                    html += ''
                        + '<div class="article-card" id="article-' + subId + '">'
                        + '<div class="article-header">'
                        + '<h3 class="article-title"><a href="/article/view/' + articleId + '" class="text-gray-900 hover:text-blue-600 transition-colors">' + escapeHtml(article.subject || '无标题') + '</a></h3>'
                        + '<div class="article-meta">'
                        + '<div class="meta-item"><i class="fas fa-rss"></i>来源：<a href="' + sourceHref + '" class="source-link" target="_blank">' + escapeHtml(article.feed.feed_name || '') + '</a></div>'
                        + '<div class="meta-item"><i class="far fa-clock"></i>' + escapeHtml(article.published || '') + '</div>'
                        + '<div class="meta-item"><i class="fas fa-external-link-alt"></i><a href="' + escapeHtml(originUrl) + '" class="source-link" target="_blank">原文</a></div>'
                        + (unableDesc ? '<div class="quick-actions"><button type="button" class="quick-btn set_read_later_another ' + (articleSub.status === 'read_later' ? 'active' : '') + '" data-article-id="' + subId + '">稍后阅读</button><button type="button" class="quick-btn expand-btn" data-article-id="' + subId + '">展开/收起</button></div>' : '')
                        + '</div></div>'
                        + aiBadges
                        + '<div class="article-content" id="content' + subId + '"' + (unableDesc ? ' style="display:none"' : '') + '>'
                        + '<div id="desc' + subId + '" class="content-preview ' + (needsCollapse ? '' : 'expanded') + '" data-article-id="' + subId + '">'
                        + contentHtml
                        + (needsCollapse ? '<div class="content-fade" style="opacity:1;"></div>' : '')
                        + '</div>'
                        + (needsCollapse ? '<div class="read-more" style="display:block;"><button type="button" class="read-more-btn" data-article-id="' + subId + '"><i class="fas fa-chevron-down"></i>阅读更多</button></div>' : '')
                        + '</div>'
                        + '<div class="article-footer' + footerClass + '"><div class="action-buttons" style="margin-left:auto;">'
                        + '<a class="action-btn" href="' + streamHref + '" title="沉浸刷文"><i class="fas fa-mobile-screen-button"></i><span class="action-label">沉浸</span></a>'
                        + '<a class="action-btn" href="/article/' + articleId + '/ai-render" title="AI可视化"><i class="fas fa-wand-magic-sparkles"></i><span class="action-label">AI可视化</span></a>'
                        + '<button type="button" class="action-btn ai-assist-btn" data-content-id="desc' + subId + '" data-title="' + escapeHtml(article.subject || '') + '" title="AI助手"><i class="fas fa-robot"></i><span class="action-label">AI助手</span></button>'
                        + '<button type="button" class="action-btn article-mindmap-btn" data-content-id="desc' + subId + '" data-article-sub-id="' + subId + '" data-title="' + escapeHtml(article.subject || '') + '" title="AI生成导图"><i class="fas fa-brain"></i><span class="action-label">导图</span></button>'
                        + '<div class="share-container"><button type="button" class="action-btn share-btn" title="分享"><i class="fas fa-share-alt"></i><span class="action-label">分享</span></button><div class="share-menu"><a href="javascript:void(0);" class="share-option icon-heart" data-id="' + articleId + '"><i class="fas fa-heart"></i><span>记录想法</span></a></div></div>'
                        + '<button type="button" class="action-btn set_read ' + (articleSub.status === 'read' ? 'active' : '') + '" data-article-id="' + subId + '" title="标记已读"><i class="fas fa-check"></i><span class="action-label">已读</span></button>'
                        + '<button type="button" class="action-btn set_read_later ' + (articleSub.status === 'read_later' ? 'active' : '') + '" data-article-id="' + subId + '" title="稍后阅读"><i class="far fa-clock"></i><span class="action-label">稍后</span></button>'
                        + '<button type="button" class="action-btn set_star ' + (articleSub.status === 'star' ? 'active' : '') + '" data-article-id="' + subId + '" title="收藏"><i class="far fa-star"></i><span class="action-label">收藏</span></button>'
                        + '<button type="button" class="action-btn playaudio" data-article-id="' + subId + '" title="语音播放"><i class="fas fa-volume-up"></i><span class="action-label">语音</span></button>'
                        + '</div></div></div>';
                });

                $('#articleList').html(html);
                initExpandButtons();
                initShareButtons();
                if (unableDesc) {
                    applyScanReadingMode();
                }
            }

            function syncArticleFooter($card) {
                if (!$card || !$card.length) {
                    return;
                }
                var $content = $card.find('.article-content');
                var $preview = $card.find('.content-preview');
                var $footer = $card.find('.article-footer');
                if ($content.length && !$content.is(':visible')) {
                    $footer.addClass('scan-collapsed').hide();
                    return;
                }
                if ($('#unable_desc').is(':checked') && $preview.length && !$preview.hasClass('expanded')) {
                    $footer.addClass('scan-collapsed').hide();
                } else {
                    $footer.removeClass('scan-collapsed').show();
                }
            }

            function renderAiProfileBadges(profile, personalizedScore) {
                if (!profile && (personalizedScore === null || personalizedScore === undefined)) {
                    return '';
                }

                var html = '<div class="ai-profile-badges">';
                if (profile && profile.primary_category) {
                    html += '<span class="ai-profile-badge"><i class="fas fa-tag"></i>' + escapeHtml(profile.primary_category) + '</span>';
                }
                if (profile && profile.quality_score !== null && profile.quality_score !== undefined) {
                    html += '<span class="ai-profile-badge muted"><i class="fas fa-tachometer-alt"></i>' + escapeHtml(String(profile.quality_score)) + '</span>';
                }
                if (personalizedScore !== null && personalizedScore !== undefined && personalizedScore !== '') {
                    html += '<span class="ai-profile-badge"><i class="fas fa-bolt"></i>' + escapeHtml(Number(personalizedScore).toFixed(1)) + '</span>';
                }
                var tags = profile && Array.isArray(profile.tags) ? profile.tags.slice(0, 3) : [];
                tags.forEach(function(tag) {
                    html += '<span class="ai-profile-badge muted">' + escapeHtml(tag) + '</span>';
                });
                html += '</div>';
                return html;
            }

            function renderCategoryFilter(navInfos) {
                var html = '<option value="">全部分类</option>';
                var seen = {};
                $.each(navInfos || {}, function (navId, navInfo) {
                    if (!navInfo || !navInfo.category_info) return;
                    var categoryIdValue = String(navInfo.category_info.category_id || navId);
                    if (!categoryIdValue || seen[categoryIdValue]) return;
                    seen[categoryIdValue] = true;
                    html += '<option value="' + escapeHtml(categoryIdValue) + '">' + escapeHtml(navInfo.category_info.category_name || '') + '</option>';
                });
                $('#articleCategoryFilter').html(html).val(categoryId || '');
            }

            function renderPagination(pagination) {
                if (!pagination || (!pagination.next_page_url && !pagination.prev_page_url)) {
                    $('#articlePagination').hide().empty();
                    return;
                }
                var page = Number(pagination.current_page || 1);
                var prev = pagination.prev_page_url
                    ? '<a href="' + buildPageUrl(Math.max(1, page - 1)) + '" class="btn btn-secondary btn-sm"><i class="fas fa-chevron-left mr-1"></i>上一页</a>'
                    : '<span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg"><i class="fas fa-chevron-left mr-1"></i>上一页</span>';
                var next = pagination.next_page_url
                    ? '<a href="' + buildPageUrl(page + 1) + '" class="btn btn-secondary btn-sm">下一页<i class="fas fa-chevron-right ml-1"></i></a>'
                    : '<span class="px-3 py-2 text-gray-400 bg-gray-100 rounded-lg">下一页<i class="fas fa-chevron-right ml-1"></i></span>';
                $('#articlePagination').show().html('<div class="flex items-center justify-between w-full"><div class="text-sm text-gray-500">当前第 ' + page + ' 页</div><div class="flex gap-1">' + prev + next + '</div></div>');
            }

            // 从localStorage获取缓存的导航数据
            function getNavFromStorage(status) {
                try {
                    // 获取缓存数据和时间戳
                    const cacheDataStr = localStorage.getItem(NAV_STORAGE_KEY);
                    const timestampStr = localStorage.getItem(NAV_STORAGE_TIMESTAMP_KEY);

                    if (!cacheDataStr || !timestampStr) {
                        return null;
                    }

                    // 解析缓存数据
                    const cacheData = JSON.parse(cacheDataStr);
                    const cacheTimestamp = parseInt(timestampStr);
                    const currentTime = new Date().getTime();

                    // 检查缓存是否过期
                    const expiryTime = STORAGE_EXPIRY_HOURS * 60 * 60 * 1000;
                    if (currentTime - cacheTimestamp > expiryTime) {
                        // 缓存过期，清除旧数据
                        clearNavStorage();
                        return null;
                    }

                    // 返回对应状态的数据
                    return cacheData[status] || null;

                } catch (error) {
                    console.error('读取localStorage缓存失败:', error);
                    clearNavStorage(); // 解析失败时清除可能损坏的缓存
                    return null;
                }
            }

            // 将导航数据保存到localStorage
            function saveNavToStorage(status, data) {
                try {
                    let storageData = {};
                    const existingStorageStr = localStorage.getItem(NAV_STORAGE_KEY);

                    // 合并现有缓存（如果有）
                    if (existingStorageStr) {
                        storageData = JSON.parse(existingStorageStr);
                    }

                    // 更新对应状态的数据
                    storageData[status] = data;

                    // 保存到localStorage
                    localStorage.setItem(NAV_STORAGE_KEY, JSON.stringify(storageData));
                    localStorage.setItem(NAV_STORAGE_TIMESTAMP_KEY, new Date().getTime().toString());

                    // 触发自定义事件，通知其他组件缓存已更新
                    window.dispatchEvent(new CustomEvent('navStorageUpdated', {
                        detail: { status, timestamp: new Date().getTime() }
                    }));

                    return true;
                } catch (error) {
                    console.error('保存到localStorage失败:', error);

                    // 如果存储失败，可能是存储空间满了，尝试清理
                    if (error.name === 'QuotaExceededError') {
                        console.warn('localStorage存储空间不足，尝试清理...');
                        clearOldStorageData();
                        // 重试一次
                        try {
                            localStorage.setItem(NAV_STORAGE_KEY, JSON.stringify({ [status]: data }));
                            localStorage.setItem(NAV_STORAGE_TIMESTAMP_KEY, new Date().getTime().toString());
                            return true;
                        } catch (retryError) {
                            console.error('重试保存到localStorage也失败:', retryError);
                        }
                    }
                    return false;
                }
            }

            // 清理旧的存储数据（当存储空间不足时）
            function clearOldStorageData() {
                // 保留最近3种状态的缓存
                try {
                    const storageDataStr = localStorage.getItem(NAV_STORAGE_KEY);
                    if (storageDataStr) {
                        const storageData = JSON.parse(storageDataStr);
                        const keys = Object.keys(storageData);

                        // 如果状态超过3个，只保留最新的3个
                        if (keys.length > 3) {
                            // 这里可以根据实际需要修改清理策略
                            // 例如：只保留当前状态和另外两个最常用的状态
                            const newStorageData = {};

                            // 假设我们保留 'unread', 'read', 'star' 这三种状态
                            const keepStatuses = ['unread', 'read', 'star'];
                            keepStatuses.forEach(status => {
                                if (storageData[status]) {
                                    newStorageData[status] = storageData[status];
                                }
                            });

                            localStorage.setItem(NAV_STORAGE_KEY, JSON.stringify(newStorageData));
                        }
                    }
                } catch (error) {
                    console.error('清理旧存储数据失败:', error);
                }
            }

            // 清空导航缓存
            function clearNavStorage() {
                localStorage.removeItem(NAV_STORAGE_KEY);
                localStorage.removeItem(NAV_STORAGE_TIMESTAMP_KEY);

                // 触发自定义事件
                window.dispatchEvent(new CustomEvent('navStorageCleared'));
            }

            // 从远程获取导航数据
            function fetchNavFromRemote(status, isSilentUpdate = false) {
                // 如果是静默更新，不显示加载动画
                if (!isSilentUpdate) {
                    $('#nav').html('<li class="text-center py-4 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>加载中...</li>');
                }

                apiRequest('GET', '/articles/navinfo', {"status": status}).then(function(result_arr) {
                    if (result_arr.code != 9999) {
                        if (!isSilentUpdate) {
                            $('#nav').html('<li class="text-center py-4 text-red-500"><i class="fas fa-exclamation-circle mr-2"></i>加载失败</li>');
                        }
                    } else {
                        // 保存到localStorage
                        saveNavToStorage(status, result_arr.result);

                        // 如果不是静默更新，才更新界面
                        if (!isSilentUpdate) {
                            renderNav(result_arr.result, status);
                        } else {
                            // 静默更新时，可以更新页面上的某些提示
                            updateLastUpdateTime();
                        }
                    }
                }).catch(function(error) {
                    if (!isSilentUpdate) {
                        $('#nav').html('<li class="text-center py-4 text-red-500"><i class="fas fa-exclamation-circle mr-2"></i>网络错误，请刷新重试</li>');
                    }

                    // 如果是静默更新失败，可以记录日志
                    if (isSilentUpdate) {
                        console.warn('静默更新导航数据失败:', error);
                    }
                }).then(function() {
                    // 如果是静默更新，可以在这里做一些清理工作
                    if (isSilentUpdate) {
                        console.log('导航数据静默更新完成');
                    }
                });
            }

            // 渲染导航的通用函数
            function renderNav(data, status) {
                processNavFlag = true;
                $('#nav').html('');
                renderCategoryFilter(data && data.nav_infos ? data.nav_infos : {});

                // 添加最后更新时间提示
                const updateTime = new Date().toLocaleTimeString();
                $('#nav').append(`
                <li class="text-xs text-gray-400 text-center py-2 border-b">
                    <i class="fas fa-clock mr-1"></i>最后更新: ${updateTime}
                    <button class="ml-2 text-blue-500 hover:text-blue-700" onclick="refreshNav('${status}')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </li>
            `);

                $.each(data.nav_infos, function (navId, navInfo) {
                    var itemCount = Object.getOwnPropertyNames(navInfo.list).length;
                    var li = `
                    <li class="category-item">
                        <div class="category-header" data-category-id="${navId}">
                            <div class="category-name">
                                <i class="fas fa-folder"></i>
                                ${navInfo.category_info.category_name}
                            </div>
                            <div class="flex items-center gap-3">
                                <!--<span class="category-count"></span>-->
                                <i class="fas fa-chevron-right category-toggle"></i>
                            </div>
                        </div>
                        <ul class="feed-list" id="category-${navId}">
                `;

                    if (itemCount > 0) {
                        $.each(navInfo.list, function (index, item) {
                            var countInfo = item.feed_count > 99 ? '99+' : item.feed_count;
                            var isActive = String(currentFeedId) === String(item.feed_id) ? 'active' : '';
                            var feedUrl = '/articles?feed_id=' + encodeURIComponent(String(item.feed_id)) + '&status=' + encodeURIComponent(status);
                            if (timeRange && timeRange !== 'all') {
                                feedUrl += '&time_range=' + encodeURIComponent(timeRange);
                            }
                            if (categoryId) {
                                feedUrl += '&category_id=' + encodeURIComponent(categoryId);
                            }
                            if (readDuration && readDuration !== 'all') {
                                feedUrl += '&read_duration=' + encodeURIComponent(readDuration);
                            }
                            if (pageCount && pageCount !== 20) {
                                feedUrl += '&page_count=' + encodeURIComponent(String(pageCount));
                            }
                            if (viewMode && viewMode !== 'all') {
                                feedUrl += '&view_mode=' + encodeURIComponent(viewMode);
                            }

                            li += `
                            <li class="feed-item ${isActive}">
                                <a href="${feedUrl}" class="feed-link">
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

                // 如果没有数据
                if ($.isEmptyObject(data.nav_infos)) {
                    $('#nav').append(`
                    <li class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <div>暂无订阅源</div>
                    </li>
                `);
                }

                // 初始化分类切换
                initCategoryToggle();
            }

            // 更新最后更新时间显示
            function updateLastUpdateTime() {
                const updateTime = new Date().toLocaleTimeString();
                const timeElement = $('#nav').find('.text-xs.text-gray-400');
                if (timeElement.length) {
                    timeElement.html(`
                    <i class="fas fa-clock mr-1"></i>最后更新: ${updateTime}
                    <button class="ml-2 text-blue-500 hover:text-blue-700" onclick="refreshNav('${getCurrentStatus()}')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                `);
                }
            }

            // 获取当前状态
            function getCurrentStatus() {
                return status || 'unread';
            }

            // 清空并重新加载的方法
            function refreshNav(status) {
                // 显示加载状态，添加刷新动画
                const refreshBtn = $('#nav').find('.fa-sync-alt');
                if (refreshBtn.length) {
                    refreshBtn.addClass('fa-spin');
                }

                // 清空缓存
                clearNavStorage();

                // 显示重新加载提示
                $('#nav').html(`
                <li class="text-center py-4 text-blue-500">
                    <i class="fas fa-sync-alt fa-spin mr-2"></i>重新加载中...
                </li>
            `);

                // 强制从远程获取最新数据
                fetchNavFromRemote(status, false);
            }
            window.refreshNav = refreshNav;

            // 检查存储是否可用
            function isStorageAvailable() {
                try {
                    const testKey = '__storage_test__';
                    localStorage.setItem(testKey, testKey);
                    localStorage.removeItem(testKey);
                    return true;
                } catch (e) {
                    return false;
                }
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
            $(document).on('click', ".set_star, .set_read, .set_read_later, .set_read_later_another", function() {
                var article_sub_id = $(this).data('article-id');
                var active = $(this).hasClass("active");
                var button = $(this);
                var nextStatus = '';

                if ($(this).hasClass("set_star")) {
                    nextStatus = active ? "read" : "star";
                } else if ($(this).hasClass("set_read")) {
                    nextStatus = active ? "unread" : "read";
                } else if ($(this).hasClass("set_read_later") || $(this).hasClass("set_read_later_another")) {
                    nextStatus = active ? "unread" : "read_later";
                } else {
                    return '';
                }

                apiRequest('POST', "/articles/status/" + article_sub_id, {"status": nextStatus}).then(function(result_arr) {
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
                }).catch(function() {
                    showNotification('设置失败，请重试', 'error');
                });
            });

            // 一键标记已读
            $("#marked_all_read").on('click', function() {
                var ids = $(this).data('ids');
                var button = $(this);
                var originalText = button.html();

                button.prop('disabled', true);
                button.html('<i class="fas fa-spinner fa-spin mr-2"></i>处理中...');

                apiRequest('POST', '/articles/allstatus', {"ids": ids, "status": "read"}).then(function(result_arr) {
                    if (result_arr.code != 9999) {
                        button.prop('disabled', false);
                        button.html(originalText);
                        showNotification('设置失败，请重试', 'error');
                    } else {
                        showNotification('全部标记为已读成功', 'success');
                        location.href="";
                        // localStorage.setItem('scrollToTopAfterMarkAllRead', 'true');
                        // setTimeout(function() {
                        //     location.reload();
                        // }, 1000);
                    }
                }).catch(function() {
                    button.prop('disabled', false);
                    button.html(originalText);
                    showNotification('设置失败，请重试', 'error');
                });
            });

            // 修复展开收起功能
            function initExpandButtons() {
                $(".expand-btn").off('click').on('click', function(e) {
                    e.stopPropagation();

                    var $button = $(this);
                    var articleId = $button.data('article-id');
                    var $content = $("#content" + articleId);
                    var $card = $button.closest('.article-card');

                    // 切换显示/隐藏
                    $content.toggle();
                    syncArticleFooter($card);
                });
                $(".read-more-btn").off('click').on('click', function(e) {
                    e.stopPropagation();
                    var $button = $(this);
                    var articleId = $button.data('article-id');
                    var $card = $button.closest('.article-card');

                    if (!articleId) {
                        var $card = $button.closest('.article-card');
                        if ($card.length) {
                            articleId = $card.attr('id').replace('article-', '');
                        }
                    }

                    if (articleId) {
                        var $content = $("#desc" + articleId);
                        if ($content.length === 0) {
                            $content = $button.closest('.article-content').find('.content-preview');
                        }

                        $content.toggleClass('expanded');

                        if ($content.hasClass('expanded')) {
                            $content.css('max-height', $content[0].scrollHeight + 'px');
                            $button.html('<i class="fas fa-chevron-up"></i> 收起内容');

                            // 隐藏渐变遮罩
                            $content.find('.content-fade').css('opacity', '0');
                            syncArticleFooter($card);

                            // 触发图片懒加载
                            setTimeout(function() {
                                $content.find('img[data-original]').each(function() {
                                    var $img = $(this);
                                    if ($img.attr('src') === '/img/unable_img.png') {
                                        $img.attr('src', $img.data('original'));
                                    }
                                });
                            }, 100);
                        } else {
                            $content.css('max-height', '360px');
                            $button.html('<i class="fas fa-chevron-down"></i> 阅读更多');

                            // 显示渐变遮罩
                            $content.find('.content-fade').css('opacity', '1');
                            syncArticleFooter($card);
                        }
                    }
                });
            }

            function syncToolButtons() {
                $('#unable_desc_btn').toggleClass('active', $('#unable_desc').is(':checked'));
                $('#unable_img_btn').toggleClass('active', $('#unable_img').is(':checked'));
            }

            // 图片屏蔽功能
            $("#unable_img_btn").on('click', function() {
                var isChecked = !$('#unable_img').is(':checked');
                $('#unable_img').prop('checked', isChecked);
                syncToolButtons();
                $.cookie('unable_img', isChecked, { expires: 365, path: '/' });

                showNotification(isChecked ? '已屏蔽图片' : '已显示图片', 'success');
                setTimeout(function() {
                    location.reload();
                }, 500);
            });

            // 一目十行功能
            $("#unable_desc_btn").on('click', function() {
                var isChecked = !$('#unable_desc').is(':checked');
                $('#unable_desc').prop('checked', isChecked);
                syncToolButtons();
                $.cookie('unable_desc', isChecked, { expires: 365, path: '/' });

                if (isChecked) {
                    showNotification('已开启一目十行', 'success');
                    // 立即应用一目十行效果
                    applyScanReadingMode();
                } else {
                    showNotification('已关闭一目十行', 'success');
                    removeScanReadingMode();
                }

                // 重新初始化展开按钮
                initExpandButtons();
            });

            // 添加一目十行模式应用函数
            function applyScanReadingMode() {
                $('.article-card').each(function() {
                    var $card = $(this);
                    var $articleContent = $card.find('.article-content');
                    var $content = $card.find('.content-preview');
                    var $readMoreBtn = $card.find('.read-more-btn');

                    $articleContent.hide();

                    // 获取文章内容文本
                    var contentText = $content.text().trim();

                    // 判断是否需要折叠（根据内容长度或段落数量）
                    var needsCollapse = contentText.length > 500 || (contentText.split('\n').length > 5);

                    if (needsCollapse) {
                        // 如果不是展开状态，则收起内容
                        if (!$content.hasClass('expanded')) {
                            // 添加折叠效果
                            $content.css('max-height', '360px');
                            $content.removeClass('expanded');

                            // 确保渐变遮罩显示
                            $card.find('.content-fade').css('opacity', '1');

                            // 显示阅读更多按钮（如果不存在则创建）
                            if ($readMoreBtn.length === 0) {
                                var $readMoreDiv = $('<div class="read-more"><button type="button" class="read-more-btn"><i class="fas fa-chevron-down"></i>阅读更多</button></div>');
                                $content.after($readMoreDiv);
                                $readMoreBtn = $readMoreDiv.find('.read-more-btn');
                                $readMoreBtn.data('article-id', $card.attr('id').replace('article-', ''));
                            } else {
                                $card.find('.read-more').show();
                            }
                        }
                        syncArticleFooter($card);
                    } else {
                        // 短内容保持展开
                        $content.addClass('expanded');
                        $content.css('max-height', 'none');
                        $card.find('.read-more').hide();
                        syncArticleFooter($card);
                    }
                });

                // 重新绑定展开按钮事件
                initExpandButtons();
            }

            // 移除一目十行模式
            function removeScanReadingMode() {
                $('.content-preview').each(function() {
                    var $content = $(this);
                    var $card = $content.closest('.article-card');
                    $card.find('.article-content').show();
                    $content.addClass('expanded');
                    $content.css('max-height', 'none');
                    $content.siblings('.read-more').hide();
                    syncArticleFooter($card);
                });
            }

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
                    $("#sidebarColumn").hide();
                    $(".feed-list").hide();
                } else {
                    $("#sidebarColumn").show();
                    processNav(status);
                }

                return isMobile;
            }

            // 快速订阅
            $(document).on('click', ".feed_quick_sub", function() {
                var feed_id = $(this).data('feed-id');
                apiRequest('POST', '/feeds/quickstore', {"feed_id": feed_id}).then(function(result_arr) {
                    if (result_arr.code != 9999) {
                        showNotification(result_arr.msg, 'error');
                    } else {
                        showNotification(result_arr.msg, 'success');
                    }
                }).catch(function() {
                    showNotification('订阅失败，请重试', 'error');
                });
            });

            // 语音播放
            // 全局语音控制
            var speechControl = {
                currentUtterance: null,
                currentButton: null,

                speak: function(text, button) {
                    // 如果正在播放同一个内容，则停止
                    if (this.currentButton && this.currentButton[0] === button[0]) {
                        this.stop();
                        return;
                    }

                    // 停止之前的播放
                    this.stop();

                    // 创建新的语音实例
                    var utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'zh-CN';
                    utterance.rate = 1;
                    utterance.pitch = 1;
                    utterance.volume = 1;

                    // 保存当前状态
                    this.currentUtterance = utterance;
                    this.currentButton = button;

                    // 更新按钮状态
                    button.addClass('playing').find('i')
                        .removeClass('fa-play fa-pause')
                        .addClass('fa-stop');

                    // 事件监听
                    utterance.onend = utterance.onerror = () => this.reset();

                    // 开始播放
                    speechSynthesis.speak(utterance);
                },

                stop: function() {
                    if (this.currentUtterance) {
                        speechSynthesis.cancel();
                        this.reset();
                    }
                },

                pause: function() {
                    if (this.currentUtterance) {
                        speechSynthesis.pause();
                        this.currentButton.find('i')
                            .removeClass('fa-stop')
                            .addClass('fa-pause');
                    }
                },

                resume: function() {
                    if (this.currentUtterance) {
                        speechSynthesis.resume();
                        this.currentButton.find('i')
                            .removeClass('fa-pause')
                            .addClass('fa-stop');
                    }
                },

                reset: function() {
                    if (this.currentButton) {
                        this.currentButton.removeClass('playing').find('i')
                            .removeClass('fa-stop fa-pause')
                            .addClass('fa-play');
                    }
                    this.currentUtterance = null;
                    this.currentButton = null;
                },

                setVoice: function(voiceName) {
                    var voices = speechSynthesis.getVoices();
                    var voice = voices.find(v => v.name === voiceName);
                    if (voice && this.currentUtterance) {
                        this.currentUtterance.voice = voice;
                    }
                }
            };

            $(document).on('click', ".playaudio", function() {
                var $button = $(this);
                var articleId = $button.data('article-id');
                var $content = $("#content" + articleId);

                var textToSpeak = $content.text().trim();
                if (!textToSpeak) {
                    showNotification('没有内容可朗读', 'warning');
                    return;
                }

                // 清理文本
                textToSpeak = textToSpeak
                    .substring(0, 10000) // 限制长度
                    .replace(/\s+/g, ' ')
                    .trim();

                if (!textToSpeak) return;

                speechControl.speak(textToSpeak, $button);
            });

            // 关闭音频播放器
            $("#audioClose").on('click', function() {
                $("#audioPlayer").removeClass('active');
                $("#audio")[0].pause();
            });

            $('#readingPreferenceBtn, #readingPreferenceBtnInline').on('click', function() {
                showReadingPreferenceModal();
                loadReadingPreference();
            });

            $('#readingPreferenceBackdrop, #readingPreferenceCloseBtn, #readingPreferenceCancelBtn').on('click', function() {
                hideReadingPreferenceModal();
            });

            $('#readingPreferenceSaveBtn').on('click', function() {
                saveReadingPreference();
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
            $(document).on('click', ".ai-assist-btn", function() {
                console.log('AI助手 - 获取内容ID')
                var contentId = $(this).data('content-id');

                // 调用全局函数，传递refer_text
                openAskAIModal(contentId);
            });
            $(document).on('click', '.article-mindmap-btn', function() {
                var contentId = String($(this).data('content-id') || '');
                var title = String($(this).data('title') || '文章导图').trim();
                var articleSubId = String($(this).data('article-sub-id') || '');
                var referText = '';

                if (contentId) {
                    var $ref = $('#' + contentId);
                    referText = ($ref.text() || '').replace(/\s+/g, ' ').trim();
                }
                if (!referText) {
                    showNotification('未找到可用于生成导图的文章内容', 'error');
                    return;
                }

                articleMindState.title = title || '文章导图';
                articleMindState.contentId = contentId;
                articleMindState.articleSubId = articleSubId;
                articleMindState.referText = referText.slice(0, 12000);
                articleMindState.tree = null;
                articleMindState.savedMindId = null;

                $('#articleMindmapTitle').text(articleMindState.title);
                showMindmapModal();
                generateArticleMindmap();
            });

            $('#articleMindmapBackdrop, #articleMindmapCloseBtn').on('click', function() {
                hideMindmapModal();
            });

            $('#articleMindmapRegenerateBtn').on('click', function() {
                if (!articleMindState.title || !articleMindState.referText) {
                    showNotification('缺少文章内容，无法生成导图', 'error');
                    return;
                }
                generateArticleMindmap();
            });

            $('#articleMindmapSaveBtn').on('click', async function() {
                if (articleMindState.isSaving) return;
                if (!articleMindState.tree) {
                    showNotification('请先生成导图', 'error');
                    return;
                }
                articleMindState.isSaving = true;
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>保存中');
                setMindmapStatus('正在保存导图节点...');

                try {
                    var rootMindId = await saveMindTreeToServer(articleMindState.tree);
                    articleMindState.savedMindId = rootMindId;
                    $('#articleMindmapEditLink').removeClass('hidden').attr('href', '/mind/' + rootMindId);
                    setMindmapStatus('保存成功，已可继续编辑。');
                    showNotification('导图已保存，可继续编辑', 'success');
                } catch (error) {
                    setMindmapStatus('保存失败，请重试。');
                    showNotification((error && error.message) ? error.message : '保存导图失败', 'error');
                } finally {
                    articleMindState.isSaving = false;
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });

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
            $('#unable_desc').prop('checked', unableDesc);
            $('#unable_img').prop('checked', unableImg);
            syncToolButtons();
            syncArticleFilterForm();
            if (currentFeedId) {
                $('#discoverBtn').show();
            }
            $('#articleFilters').on('submit', function (event) {
                event.preventDefault();
                currentPage = 1;
                var filters = collectArticleFilters();
                timeRange = filters.time_range;
                categoryId = filters.category_id;
                readDuration = filters.read_duration;
                pageCount = filters.page_count;

                var params = new URLSearchParams(window.location.search);
                params.set('status', status);
                if (currentFeedId) {
                    params.set('feed_id', currentFeedId);
                } else {
                    params.delete('feed_id');
                }
                if (timeRange && timeRange !== 'all') {
                    params.set('time_range', timeRange);
                } else {
                    params.delete('time_range');
                }
                if (categoryId) {
                    params.set('category_id', categoryId);
                } else {
                    params.delete('category_id');
                }
                if (readDuration && readDuration !== 'all') {
                    params.set('read_duration', readDuration);
                } else {
                    params.delete('read_duration');
                }
                params.set('page_count', String(pageCount));
                if (viewMode && viewMode !== 'all') {
                    params.set('view_mode', viewMode);
                } else {
                    params.delete('view_mode');
                }
                params.set('page', String(currentPage));
                window.history.replaceState(null, '', '/articles?' + params.toString());
                renderStatusTabs();
                loadArticleListByApi();
            });
            renderStatusTabs();
            loadArticleListByApi();
            checkMobile();

            $('#toggleCategoryBtn').on('click', function() {
                $('#sidebarColumn').stop(true, true).slideToggle(160);
            });

            // 检查是否启用了一目十行
            if (unableDesc) {
                // 延迟执行以确保DOM完全加载
                setTimeout(function() {
                    applyScanReadingMode();
                }, 100);
            }

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
