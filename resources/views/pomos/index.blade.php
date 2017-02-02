@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">

            <!-- Finish Pomos -->
            @if (count($pomos) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Finish Pomos
                    </div>

                    <div class="panel-body">
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

                                                <button type="submit" id="delete-pomo-{{ $pomo->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
