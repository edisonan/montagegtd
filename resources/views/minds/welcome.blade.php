@extends('layouts.app')

@section('title', '思维导图 - 蒙太奇')
@section('description', '蒙太奇思维导图这里通过思维导图来总结你的每一个想法，发散思维，认真思考每一个想法！')

<style>
    /* 思维导图页面专用样式 */
    .mindmap-feature-page {
        max-width: 1200px;
    }

    /* 特色卡片 */
    .mindmap-feature-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .mindmap-feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .mindmap-feature-card-header {
        background: linear-gradient(135deg, #f59e0b, #8a6cff);
        padding: 32px 40px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .mindmap-feature-card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        opacity: 0.3;
    }

    .mindmap-feature-card-header::after {
        content: '';
        position: absolute;
        bottom: -60%;
        left: -10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        opacity: 0.2;
    }

    .mindmap-feature-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .mindmap-feature-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-top: 8px;
        position: relative;
        z-index: 1;
    }

    .mindmap-feature-card-body {
        padding: 40px;
    }

    /* 导图可视化示例 */
    .mindmap-visual {
        position: relative;
        margin: 40px 0;
        padding: 40px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(245, 158, 11, 0.05));
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        text-align: center;
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mindmap-nodes {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }

    .central-node {
        background: linear-gradient(135deg, #8a6cff, #f59e0b);
        color: white;
        padding: 20px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.25rem;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
        position: relative;
        z-index: 2;
    }

    .branch {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .branch-line {
        width: 2px;
        height: 40px;
        background: linear-gradient(to bottom, #8a6cff, #f59e0b);
        position: relative;
    }

    .branch-line::before {
        content: '';
        position: absolute;
        top: -1px;
        left: -10px;
        right: -10px;
        height: 2px;
        background: linear-gradient(to right, #8a6cff, #f59e0b);
    }

    .branch-node {
        background: white;
        color: #475569;
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.95rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        min-width: 120px;
        text-align: center;
    }

    .branch-node:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        background: #f8fafc;
    }

    /* 核心优势 */
    .mindmap-advantages {
        margin: 40px 0;
    }

    .advantages-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 32px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .advantages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }

    .advantage-card {
        background: white;
        border-radius: 12px;
        padding: 28px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .advantage-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #8a6cff, #f59e0b);
    }

    .advantage-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .advantage-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(245, 158, 11, 0.1));
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: #8a6cff;
        font-size: 1.5rem;
    }

    .advantage-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .advantage-desc {
        color: #64748b;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    /* 使用场景 */
    .mindmap-scenarios {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.05), rgba(245, 158, 11, 0.05));
        border-radius: 16px;
        padding: 40px;
        margin: 40px 0;
    }

    .scenarios-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 32px;
        text-align: center;
    }

    .scenarios-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }

    .scenario-item {
        background: white;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .scenario-item:hover {
        border-color: #8a6cff;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.1);
        transform: translateY(-3px);
    }

    .scenario-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .scenario-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(245, 158, 11, 0.1));
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #8a6cff;
        font-size: 1.2rem;
    }

    .scenario-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1e293b;
    }

    .scenario-desc {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* 功能特色 */
    .mindmap-features {
        margin: 40px 0;
    }

    .features-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .features-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .feature-item:hover {
        background: white;
        border-color: #8a6cff;
        transform: translateX(5px);
    }

    .feature-item i {
        color: #8a6cff;
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
    }

    .feature-text {
        color: #475569;
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* 图片展示 */
    .mindmap-image-section {
        margin: 40px 0;
        text-align: center;
    }

    .mindmap-image-container {
        max-width: 900px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        transition: transform 0.3s ease;
    }

    .mindmap-image-container:hover {
        transform: translateY(-5px);
    }

    .mindmap-image-container img {
        width: 100%;
        height: auto;
        display: block;
    }

    .mindmap-image-caption {
        padding: 16px;
        background: white;
        color: #64748b;
        font-size: 0.95rem;
    }

    /* 思维发散提示 */
    .mind-expansion {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(139, 92, 246, 0.1));
        border-radius: 16px;
        padding: 32px;
        margin: 40px 0;
        text-align: center;
    }

    .expansion-icon {
        font-size: 3rem;
        color: #f59e0b;
        margin-bottom: 20px;
        display: block;
    }

    .expansion-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
    }

    .expansion-text {
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.7;
        max-width: 700px;
        margin: 0 auto;
    }

    /* 行动按钮 */
    .mindmap-action-section {
        text-align: center;
        margin-top: 48px;
        padding-top: 40px;
        border-top: 1px solid #e2e8f0;
    }

    .mindmap-primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 40px;
        font-size: 1.125rem;
        font-weight: 600;
        background: linear-gradient(135deg, #8a6cff, #f59e0b);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
    }

    .mindmap-primary-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(139, 92, 246, 0.4);
        color: white;
    }

    .mindmap-primary-btn i {
        margin-right: 12px;
        font-size: 1.2rem;
    }

    /* 响应式调整 */
    @media (max-width: 768px) {
        .mindmap-feature-card {
            border-radius: 12px;
            margin: 0 -16px;
        }

        .mindmap-feature-card-header {
            padding: 24px;
        }

        .mindmap-feature-title {
            font-size: 1.5rem;
        }

        .mindmap-feature-card-body {
            padding: 24px;
        }

        .mindmap-visual {
            padding: 24px;
            min-height: auto;
        }

        .mindmap-nodes {
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .branch {
            flex-direction: row;
            gap: 10px;
        }

        .branch-line {
            width: 40px;
            height: 2px;
        }

        .branch-line::before {
            top: -10px;
            bottom: -10px;
            left: -1px;
            right: auto;
            width: 2px;
            height: auto;
        }

        .advantages-grid {
            grid-template-columns: 1fr;
        }

        .scenarios-grid {
            grid-template-columns: 1fr;
        }

        .features-list {
            grid-template-columns: 1fr;
        }

        .mindmap-scenarios {
            padding: 24px;
        }

        .mindmap-primary-btn {
            width: 100%;
            padding: 14px 24px;
        }
    }

    /* 动画效果 */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .animate-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes fadeInScale {
        0% {
            opacity: 0;
            transform: scale(0.9);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-fadeInScale {
        animation: fadeInScale 0.6s ease-out;
    }
</style>

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mindmap-feature-page">
        <!-- 特色卡片 -->
        <div class="mindmap-feature-card animate-fadeInScale">
            <!-- 卡片头部 -->
            <div class="mindmap-feature-card-header">
                <h1 class="mindmap-feature-title">
                    <i class="fas fa-project-diagram"></i>
                    思维导图
                </h1>
                <p class="mindmap-feature-subtitle">
                    可视化思考，结构化知识，激发无限创意
                </p>
            </div>

            <!-- 卡片内容 -->
            <div class="mindmap-feature-card-body">
                <!-- 介绍段落 -->
                <div class="intro-content" style="font-size: 1.125rem; line-height: 1.8; color: #475569; text-align: center; max-width: 800px; margin: 0 auto 40px;">
                    <b>思维导图</b>是蒙太奇的创意工具，通过可视化方式帮助您整理思路、激发创意、构建知识网络。将抽象的想法转化为清晰的图形结构，让思考变得更高效、更深入。
                </div>

                <!-- 思维导图可视化示例 -->
                <div class="mindmap-visual animate-fadeInScale">
                    <div class="mindmap-nodes">
                        <!-- 中心节点 -->
                        <div class="central-node animate-pulse">
                            创意项目规划
                        </div>

                        <!-- 分支 -->
                        <div class="branch">
                            <div class="branch-line"></div>
                            <div class="branch-node">目标设定</div>
                            <div class="branch-node">资源分配</div>
                        </div>

                        <div class="branch">
                            <div class="branch-line"></div>
                            <div class="branch-node">时间安排</div>
                            <div class="branch-node">风险评估</div>
                        </div>

                        <div class="branch">
                            <div class="branch-line"></div>
                            <div class="branch-node">团队协作</div>
                            <div class="branch-node">成果评估</div>
                        </div>
                    </div>
                </div>

                <!-- 核心优势 -->
                <div class="mindmap-advantages">
                    <h2 class="advantages-title">
                        <i class="fas fa-star"></i>
                        思维导图的核心优势
                    </h2>
                    <div class="advantages-grid">
                        <div class="advantage-card">
                            <div class="advantage-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h3 class="advantage-title">激发创意</h3>
                            <p class="advantage-desc">
                                通过放射状结构和自由联想，打破线性思维限制，激发更多创新想法和解决方案。
                            </p>
                        </div>

                        <div class="advantage-card">
                            <div class="advantage-icon">
                                <i class="fas fa-sitemap"></i>
                            </div>
                            <h3 class="advantage-title">结构化思考</h3>
                            <p class="advantage-desc">
                                将复杂信息转化为清晰的层级结构，帮助理解事物之间的关联，建立系统的知识框架。
                            </p>
                        </div>

                        <div class="advantage-card">
                            <div class="advantage-icon">
                                <i class="fas fa-memory"></i>
                            </div>
                            <h3 class="advantage-title">增强记忆</h3>
                            <p class="advantage-desc">
                                视觉化的信息比纯文字更容易记忆，色彩和图像的使用能大幅提升信息留存率。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 思维发散提示 -->
                <div class="mind-expansion">
                    <i class="fas fa-lightbulb expansion-icon"></i>
                    <h2 class="expansion-title">发散思维，深入思考</h2>
                    <p class="expansion-text">
                        思维导图不仅是工具，更是思维方式。它鼓励您从核心概念出发，向各个方向发散思考，捕捉每一个灵感闪现，然后通过层级结构深入分析，最终形成完整、系统的思考成果。
                    </p>
                </div>

                <!-- 使用场景 -->
                <div class="mindmap-scenarios">
                    <h2 class="scenarios-title">适用场景</h2>
                    <div class="scenarios-grid">
                        <div class="scenario-item">
                            <div class="scenario-header">
                                <div class="scenario-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h3 class="scenario-name">学习笔记</h3>
                            </div>
                            <p class="scenario-desc">
                                整理课程内容，建立知识体系，可视化复杂概念，提高学习效率和记忆效果。
                            </p>
                        </div>

                        <div class="scenario-item">
                            <div class="scenario-header">
                                <div class="scenario-icon">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <h3 class="scenario-name">项目规划</h3>
                            </div>
                            <p class="scenario-desc">
                                分解项目任务，明确责任分工，规划时间节点，可视化项目进度和依赖关系。
                            </p>
                        </div>

                        <div class="scenario-item">
                            <div class="scenario-header">
                                <div class="scenario-icon">
                                    <i class="fas fa-pen-fancy"></i>
                                </div>
                                <h3 class="scenario-name">创意构思</h3>
                            </div>
                            <p class="scenario-desc">
                                头脑风暴，收集灵感，建立创意关联，发展完整的故事线或产品概念。
                            </p>
                        </div>

                        <div class="scenario-item">
                            <div class="scenario-header">
                                <div class="scenario-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <h3 class="scenario-name">问题分析</h3>
                            </div>
                            <p class="scenario-desc">
                                拆解复杂问题，分析根本原因，探索解决方案，制定行动计划。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 功能特色 -->
                <div class="mindmap-features">
                    <h2 class="features-title">
                        <i class="fas fa-cogs"></i>
                        蒙太奇思维导图特色功能
                    </h2>
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-edit"></i>
                            <span class="feature-text">拖拽式编辑，操作直观简单</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-palette"></i>
                            <span class="feature-text">丰富的主题和颜色方案</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-expand-alt"></i>
                            <span class="feature-text">无限画布，支持复杂导图</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-file-export"></i>
                            <span class="feature-text">多种格式导出（PNG、PDF、Markdown）</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-sync-alt"></i>
                            <span class="feature-text">实时自动保存，防止数据丢失</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-share-alt"></i>
                            <span class="feature-text">一键分享，团队协作编辑</span>
                        </div>
                    </div>
                </div>

                <!-- 图片展示 -->
                <div class="mindmap-image-section">
                    <div class="mindmap-image-container">
                        <img src="/img/mind.png" alt="蒙太奇思维导图界面"
                             title="蒙太奇思维导图界面 - 可视化思考工具">
                        <div class="mindmap-image-caption">思维导图编辑界面 - 直观的可视化创作体验</div>
                    </div>
                </div>

                <!-- 核心价值 -->
                <div class="mt-10 p-6 bg-gradient-to-r from-orange-50 to-purple-50 rounded-2xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-bullseye text-orange-600"></i>
                        为什么选择蒙太奇思维导图？
                    </h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>专业工具：</strong>专为深度思考设计的专业思维导图工具</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>无缝集成：</strong>与蒙太奇的笔记、任务等功能无缝衔接</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>零学习成本：</strong>直观的操作界面，无需学习即可上手</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3 flex-shrink: 0;"></i>
                            <span><strong>云端同步：</strong>多端数据同步，随时随地继续创作</span>
                        </li>
                    </ul>
                </div>

                <!-- 行动召唤 -->
                <div class="mindmap-action-section">
                    <a href="{{ url('/minds') }}" class="mindmap-primary-btn">
                        <i class="fas fa-play-circle"></i>
                        开始绘制思维导图
                    </a>
                    <p style="color: #64748b; margin-top: 20px; font-size: 0.95rem;">
                        立即体验可视化思考的无限可能
                    </p>
                </div>
            </div>
        </div>

        <!-- 相关功能导航 -->
        <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ url('/notes') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lightbulb text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">记想法</h3>
                <p class="text-gray-600 text-sm">记录灵感碎片，整理思考素材，为导图提供内容</p>
            </a>

            <a href="{{ url('/articles') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-reader text-orange-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">去阅读</h3>
                <p class="text-gray-600 text-sm">获取知识输入，整理阅读心得，转化为思维导图</p>
            </a>

            <a href="{{ url('/tasks') }}" class="card hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tasks text-blue-600 text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">待办管理</h3>
                <p class="text-gray-600 text-sm">将导图分解为具体任务，从思考到执行的完整闭环</p>
            </a>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // 初始化动画
            function animateOnScroll() {
                $('.animate-fadeInScale').each(function() {
                    var elementTop = $(this).offset().top;
                    var elementBottom = elementTop + $(this).outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();

                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $(this).css('opacity', '1');
                        $(this).css('transform', 'scale(1)');
                    }
                });
            }

            // 初始隐藏动画元素
            $('.animate-fadeInScale').css({
                'opacity': '0',
                'transform': 'scale(0.9)',
                'transition': 'opacity 0.6s ease, transform 0.6s ease'
            });

            // 初始检查
            setTimeout(animateOnScroll, 100);

            // 滚动时检查
            $(window).scroll(function() {
                animateOnScroll();
            });

            // 特色卡片悬停
            $('.mindmap-feature-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 分支节点交互
            $('.branch-node').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                    $(this).css('box-shadow', '0 8px 20px rgba(0, 0, 0, 0.12)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                    $(this).css('box-shadow', '0 4px 12px rgba(0, 0, 0, 0.08)');
                }
            );

            // 优势卡片悬停
            $('.advantage-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 场景卡片悬停
            $('.scenario-item').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 功能项悬停
            $('.feature-item').hover(
                function() {
                    $(this).css('transform', 'translateX(5px)');
                    $(this).css('background', 'white');
                },
                function() {
                    $(this).css('transform', 'translateX(0)');
                    $(this).css('background', '#f8fafc');
                }
            );

            // 图片容器悬停
            $('.mindmap-image-container').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 主按钮悬停
            $('.mindmap-primary-btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-3px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // 中心节点脉冲动画
            setInterval(function() {
                $('.central-node').toggleClass('animate-pulse');
            }, 4000);

            // 相关功能卡片悬停
            $('.card').hover(
                function() {
                    $(this).addClass('shadow-lg');
                    $(this).removeClass('shadow');
                },
                function() {
                    $(this).removeClass('shadow-lg');
                    $(this).addClass('shadow');
                }
            );
        });
    </script>
@endsection