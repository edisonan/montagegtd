@extends('layouts.app')

@section('content')
    <div class="container">
                <div class="card">
                    <div class="card-header">
                        	修改日报
                        	<div style="float:right">
	                    		<a href="{{'/dailysummarys'}}">[返回]</a>
	                    	</div>
                    </div>

                    <div class="card-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Task Form -->
                    <form action="{{ url('dailysummary/'.$dailysummary->id) }}" method="POST" class="form-horizontal">
                        
                        {{ csrf_field() }}

                        <div class="form-group row">
                            <label for="dailysummary-summarydate" class="col-md-3 control-label">总结日期:</label>

                            <div class="col-md-8">
	                                <input type="text" name="summary_date" id="dailysummary-summarydate" class="form-control" value="{{ $dailysummary->summary_date }}">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="dailysummary-workcontent" class="col-md-3 control-label">工作总结:</label>

                            <div class="col-md-8">
	                                <input type="text" name="work_content" id="dailysummary-workcontent" class="form-control" value="{{ $dailysummary->work_content }}">
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label for="dailysummary-lifecontent" class="col-md-3 control-label">生活总结:</label>

                            <div class="col-md-8">
	                                <input type="text" name="life_content" id="dailysummary-lifecontent" class="form-control" value="{{ $dailysummary->life_content }}">
                            </div>
                        </div>
 
                        <div class="form-group row">
                            <div class="col-md-offset-3 col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-plus"></i>提交！
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    
                </div>
                </div>
        </div>
    </div>
@endsection
