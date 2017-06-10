@extends('layouts.app')

@section('content')
    <div class="container">
    
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    	新的文章[<a href="{{ url('articles') }}">未读</a>][<a href="{{ url('articles?status=read') }}">已读</a>][<a href="{{ url('articles?status=star') }}">加星</a>]
                    	<div style="float:right">
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
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($articles as $article)
                                    <tr >
                                        <td class="table-text"  width="90%">
                                        	<div class="preprepre">
                                        		<a href="{{ url('article/view/'.$article->id) }}" target="_blank" title="所属订阅：{{ $article->feed->feed_name}}">{{ $article->subject }}</a>
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
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
