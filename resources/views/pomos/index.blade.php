@extends('layouts.app')

@section('content')
    <div class="container">

            <!-- Finish Pomos -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        	番茄汇总
                    </div>

                    <div class="panel-body">
		            @if (count($pomos) > 0)
                        <table class="table table-striped task-table">
                            <thead>
                                <th>Pomo</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($pomos as $pomo)
                                    <tr>
                                        <td class="table-text" width="80%"><div>{{ $pomo->name }}</div></td>

                                        <!-- Task Delete Button -->
                                        <td width="20%" align="right">
                                            <form action="{{url('pomo/' . $pomo->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-pomo-{{ $pomo->id }}" class="btn btn-link">
                                                    X
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                         {!! $pomos->links() !!}
                    @else
                    	暂时还没有完成哦，快去<a href="{{url('/index')}}">开始第一个番茄</a>吧！
		            @endif
                    </div>
                </div>
    </div>
@endsection
