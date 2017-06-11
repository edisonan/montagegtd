@extends('layouts.app')

@section('content')
    <div class="container">
    
    	@if(count($categorys)>0)
    	<div class="col-sm-offset-0 col-sm-3">
    		<div class="panel panel-default">
                <div class="panel-heading">
                	订阅分类
                </div>

                <div class="panel-body">
		    		<ul class="nav nav-pills nav-stacked">
		    			@foreach($categorys as $category)
		    			<li role="presentation" class="">
		    				{{ $category->name }}
		    				@if(count($category->feeds)>0)
		    					<ul>
		    					@foreach($category->feeds as $feed)
		    						<li>
		    							<a href="{{ url('articles?feed_id='.$feed->id.'&status='.$status) }}">
		    							<span style="display: block;width: 200px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
		    							[
		    							@if($status == 'unread')
		    								{{$feed->unread_count}}
		    							@else
		    								{{$feed->read_count}}
		    							@endif
		    							]
		    							{{ $feed->feed_name,0,10 }}
		    							</span>
		    							
		    							
		    							</a>
		    						</li>
		    					@endforeach
		    					</ul>
		    				@endif
		    			</li>
		    			@endforeach
		    		</ul>
		    	</div>	
    		</div>
    	</div>
    	@endif
    
        <div class="col-sm-offset-0 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的文章[<a href="{{ url('articles') }}">未读</a>][<a href="{{ url('articles?status=read') }}">已读</a>][<a href="{{ url('articles?status=star') }}">加星</a>]
                    	<div style="float:right">
                    		<a href="{{'feed/checkNewFeed'}}">[更新?]</a>
                    		<a href="{{'feeds'}}">[增加订阅]</a>
                    	</div>
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if (count($articles) > 0)
                    <table class="table table-striped task-table">
                            <thead>
                                <th>文章列表</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($articles as $article)
                                    <tr >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre">
                                        		<a href="{{ url('article/view/'.$article->id) }}" target="_blank" title="所属订阅：{{ $article->feed->feed_name}}">
                                        		{{ $article->subject }}
                                        		&nbsp;&nbsp;{{$article->published}}
                                        		</a>
                                        	</pre>
                                        </td>

                                        <!-- Task Delete Button -->
                                        <td  width="1"  align='right'>
                                            <form action="{{url('article/' . $article->id)}}" method="POST"  class=".form-inline">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

												<input type="hidden" name="type"  value="delete"/> 
                                                <button type="submit" id="delete-task-{{ $article->id }}" class="btn btn-link" title="删除!!!">
                                                	<!-- 
                                                    <i class="fa fa-btn fa-trash"></i>
                                                	 -->
                                                	 <span style="color:red">×</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                
                            </tbody>
                        </table>
                        {!! $articles->links() !!}
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
