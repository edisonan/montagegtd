@extends('layouts.app')

@section('title', '关于我们 - 蒙太奇')

@section('content')
<style>
    .about-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    
    .feature-card {
        transition: all 0.3s ease;
        border: 1px solid #eef5e9;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(66, 156, 78, 0.15);
    }
    
    .blockquote {
        border-left: 4px solid #429c4e;
        font-style: italic;
        background-color: #f8fff9;
    }
    
    .img-about {
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .section-title {
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(to right, #429c4e, #5dc568);
        border-radius: 3px;
    }
    
    .tech-stack-item {
        background: linear-gradient(135deg, #f5fcf6, #e8f7ec);
        padding: 15px;
        border-radius: 10px;
        border-left: 4px solid #429c4e;
        margin-bottom: 10px;
    }
    
    .contact-btn {
        background: linear-gradient(135deg, #429c4e, #5dc568);
        border: none;
        padding: 10px 20px;
        border-radius: 30px;
        color: white;
        transition: all 0.3s ease;
    }
    
    .contact-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(66, 156, 78, 0.3);
        color: white;
        text-decoration: none;
    }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm about-card">
                <div class="card-header bg-white border-0">
                    <h2 class="mb-0 text-center text-success">
                        <i class="fa fa-info-circle mr-2"></i>
                        关于蒙太奇 - 知识管理的艺术
                    </h2>
                </div>
                
                <div class="card-body py-4">
                    <div class="text-center mb-5">
                        <img src="/img/index.jpg" alt="蒙太奇" class="img-about" style="max-height: 300px; object-fit: cover; width: 100%;">
                        <p class="mt-3 text-muted">记录生活点滴，编织知识网络</p>
                    </div>
                    
                    <div class="about-content px-xl-4">
                        <h4 class="section-title text-success">
                            <i class="fa fa-star mr-2"></i>项目简介
                        </h4>
                        <div class="lead text-center mb-4 px-xl-5">
                            <p class="mb-3">不止于GTD的WEB应用，除了支持番茄工作法、任务待办，更是支持笔记、RSS阅读、思维导图于一身的知识管理系统</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-lg-10 mx-auto">
                                <div class="blockquote p-4 rounded">
                                    <p class="mb-0">取名 "蒙太奇"，这是个稍显拗口的电影术语，指通过对众多镜头进行不同方式的剪辑，从而展现出各异的立意。即便在疫情期间，不少人断言电影将会走向消亡，但实际上电影或许是最具生命力、最难消亡的产业之一。人类在现实中往往显得渺小脆弱，而电影为我们提供了一个能够主宰想象空间的契机。</p>
                                </div>
                                
                                <div class="blockquote p-4 rounded mt-4">
                                    <p class="mb-0">那么这个平台呢？它期望记录下我们这些小人物在平凡日常与非凡时刻的点点滴滴，如同经历一场 "浮生一日"。在这里，你既是导演，掌控着情节走向；也是编剧，书写着故事内容；更是演员，演绎着自己的人生。衷心希望大家所记录的每一件事、每一段成长历程，最终能够如同精心剪辑的蒙太奇镜头一般，共同拼凑出一部专属于我们生活的宏大电影。</p>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-5">
                        
                        <h4 class="section-title text-success">
                            <i class="fa fa-cogs mr-2"></i>技术栈
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="tech-stack-item">
                                    <h6><i class="fa fa-check text-success mr-2"></i><strong>PHP:</strong> >=7.0.0</h6>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tech-stack-item">
                                    <h6><i class="fa fa-check text-success mr-2"></i><strong>Laravel:</strong> 5.5.*</h6>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tech-stack-item">
                                    <h6><i class="fa fa-check text-success mr-2"></i><strong>MySQL:</strong> >=5.5.*</h6>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="tech-stack-item">
                                    <h6><i class="fa fa-check text-success mr-2"></i><strong>前端:</strong> Bootstrap, jQuery</h6>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-5">
                        
                        <h4 class="section-title text-success">
                            <i class="fa fa-list-alt mr-2"></i>核心功能
                        </h4>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 feature-card border-0">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fa fa-clock-o text-success fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0 text-success">番茄工作法</h5>
                                        </div>
                                        <p class="card-text flex-grow-1">支持番茄工作法时钟自定义，找到自己最高效的时间。完成番茄钟之后，双击待办列表即可添加该番茄钟描述。</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 feature-card border-0">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fa fa-tasks text-success fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0 text-success">任务管理</h5>
                                        </div>
                                        <p class="card-text flex-grow-1">待办事项支持提醒功能，支持deadline之后醒目提醒。支持四象限来管理任务，方便后续归纳及总结。</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 feature-card border-0">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fa fa-rss text-success fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0 text-success">RSS阅读</h5>
                                        </div>
                                        <p class="card-text flex-grow-1">支持RSS订阅，支持拖动管理订阅排序，支持稍后阅读、支持加星、收藏等，支持分享到社交网络。</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 feature-card border-0">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fa fa-sitemap text-success fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0 text-success">思维导图</h5>
                                        </div>
                                        <p class="card-text flex-grow-1">支持快速新增导图，支持快捷键插入新节点、更改节点等，支持思维导图导出为图片。</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 feature-card border-0">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fa fa-lightbulb-o text-success fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0 text-success">想法记录</h5>
                                        </div>
                                        <p class="card-text flex-grow-1">支持标签功能，支持公开或者私密发布，支持语音记录想法功能，支持分享网页到想法。</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6">
                                <div class="card h-100 feature-card border-0">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fa fa-bar-chart text-success fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0 text-success">统计分析</h5>
                                        </div>
                                        <p class="card-text flex-grow-1">按月支持针对阅读、番茄、想法等统计的饼图与柱状图记录，帮助您了解时间分配情况。</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-5">
                        
                        <h4 class="section-title text-success">
                            <i class="fa fa-external-link mr-2"></i>更多信息
                        </h4>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                        <i class="fa fa-rocket fa-3x text-success mb-3"></i>
                                        <h5 class="card-title">快速体验</h5>
                                        <a href="https://task.congcong.us" target="_blank" class="contact-btn mt-3">
                                            <i class="fa fa-external-link mr-1"></i>访问演示站点
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                        <i class="fa fa-code fa-3x text-success mb-3"></i>
                                        <h5 class="card-title">开源地址</h5>
                                        <a href="https://gitee.com/accacc/task" target="_blank" class="contact-btn mt-3">
                                            <i class="fa fa-github mr-1"></i>查看源码
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-5">
                        
                        <h4 class="section-title text-success">
                            <i class="fa fa-heart mr-2"></i>鸣谢
                        </h4>
                        
                        <div class="text-center mb-5">
                            <div class="d-inline-block p-4 bg-light rounded">
                                <img src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.svg" alt="jetbrains" class="img-fluid" style="max-width: 200px;">
                                <p class="mt-2 mb-0">感谢 JetBrains Open Source Support 计划的支持</p>
                            </div>
                        </div>
                        
                        <div class="text-center mt-5 pt-4 border-top">
                            <p class="text-secondary">致力于打造一个高效、易用的知识管理平台</p>
                            <a href="mailto:accacc@126.com?subject=关于蒙太奇的反馈" class="btn btn-outline-success">
                                <i class="fa fa-envelope mr-1"></i>联系我们
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection