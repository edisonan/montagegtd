@extends('layouts.app')

<script>

function submitProcess($status){
	document.getElementById('status_id').value = $status;
	document.getElementById('add_note_form').submit();
}
</script>

@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    New note
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New note Form -->
                    <form action="{{ url('note') }}" method="POST" class="form-horizontal" id="add_note_form">
                        {{ csrf_field() }}

                        <!-- note Name -->
                        <div class="form-group">
                            <label for="note-name" class="col-sm-3 control-label">note</label>

                            <div class="col-sm-6">
                            	<textarea class="form-control" rows="3"  name="name" id="note-name" ></textarea>
                            </div>
                        </div>

                        <!-- Add note Button -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                            	<input type="hidden" name="status" value="1" id="status_id">
                            	
                                <button type="button" class="btn btn-default" onclick="submitProcess(1)">
                                    <i class="fa fa-btn fa-plus"></i>私密发布
                                </button>
                            	
                                <button type="button" class="btn btn-primary" onclick="submitProcess(2)">
                                    <i class="fa fa-btn fa-plus"></i>公开发布
                                </button>
                                
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current notes -->
            @if (count($notes) > 0)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Current notes
                    </div>

                    <div class="panel-body">
                        <table class="table table-striped task-table">
                            <thead>
                                <th>Notes</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </thead>
                            <tbody>
                                @foreach ($notes as $note)
                                    <tr>
                                        <td class="table-text"  width="10%"><div><img alt="" width="40px" src="http://gravatar.duoshuo.com/avatar/{{ md5(strtolower(trim($note->user->email))) }}"> {{ $note->user->name }}</div></td>
                                        
                                        <td class="table-text"  width="80%"><pre style="white-space: pre-wrap;word-wrap: break-word;">{{ $note->name }}</pre></td>

                                        <!-- note Delete Button -->
                                        <td  width="10%">
                                        	@if($note->user_id == Auth::user()->id )
                                            <form action="{{url('note/' . $note->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-note-{{ $note->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
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
