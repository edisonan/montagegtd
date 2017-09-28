@extends('layouts.app')

@section('title', '蒙太奇MontageGTD-高效你的生活')
@section('description', '专业GTD工具，在这里你可以利用番茄工作法结合我们的待办列表来完成每一件事，每月每周每日均会提供实时统计，你还可以进行RSS阅读，订阅自己喜爱的文章，更可以推送文章到kindle阅读器！')

@section('content')
 <div class="container">
      <div class="jumbotron text-center" style="color: white;    background-position: -1501px -266px;    background-image: url('/img/index_background.jpg');">
        <h3 style="padding-bottom: 20px;">Montage GTD</h3>
        <p class="lead"  style="padding-bottom: 100px;">专业GTD工具，在这里高效完成每一件事情，记录每一个想法，利用碎片化时间，提升一切内容。</p>
        <p>
	        <a class="btn btn-lg btn-default" href="{{url('/login')}}" role="button">现在就开始吧</a>
        </p>
      </div>
      

      <div class="row marketing">
        <div class="col-lg-12">
          <img alt="" src="/img/time-is-money.png" class="col-lg-1">
          <div class="col-lg-2">
	        <h4>番茄工作法</h4>
	        <p>基于番茄工作法，帮助你集中注意力完成每一项待办。试过就知道这是有效的时间管理方法。</p>
          </div>
          <img alt="" src="/img/list.png" class="col-lg-1">
          <div class="col-lg-2">
	        <h4>待办清单</h4>
          	<p>轻量级的待办列表功能，同时通过特殊语法提供 #标签、四象限重要程度、快速置顶等功能。</p>
          </div>
          <img alt="" src="/img/newspaper.png" class="col-lg-1">
          <div class="col-lg-2">
	        <h4>思想广场</h4>
          	<p>从收集想法、规划任务到专注工作、归纳分析，这里提供了完整的工作流效率管理。</p>
          </div>
          <img alt="" src="/img/monitor.png" class="col-lg-1">
          <div class="col-lg-2">
	          <h4>RSS阅读</h4>
          	<p>汇总你的碎片化阅读，在这里高效完成对它的思考与记录！</p>
          </div>
        </div>
      </div>
	  
    </div> <!-- /container -->
@endsection
