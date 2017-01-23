@extends('layouts.app')

<script language="javascript" type="text/javascript"> 
	var interval = 1000; 
	var remain = {{ $runing_pomo_remain }};
	var status = {{ $runing_pomo_status }};
	
	function ShowCountDown(leftsecond, divname) 
	{ 
		var minute=Math.floor(leftsecond/60); 
		var second=Math.floor(leftsecond - minute * 60); 
		
		var cc = document.getElementById(divname); 
		
		remain = remain - 1;
		if(remain < 0){
			document.getElementById('divdown').style.display = "none";
			document.getElementById('formdiv1').style.display = "block";
			document.getElementById('formdiv2').style.display = "block";
			return false;
		}

		var minute_label = (minute >= 10)?minute:"0"+minute ;
		var second_label = (second >= 10)?second:"0"+second ;
		
		cc.innerHTML = "#Remain# " + minute_label +":"+ second_label; 
	}
	
	if(status == 2){
		window.setInterval(function(){ShowCountDown( remain, "divdown" );}, interval); 
	}
</script> 

@section('content')
    <div class="container">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Start Pomo
                </div>

                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')
                    
                    @if($runing_pomo_status == 2)
                    	<a class="btn btn-lg btn-primary btn-shadow btn-block" href="#" role="button" id = "divdown" ></a>
                    @elseif($runing_pomo_status == 1)
                   		 <a class="btn btn-lg btn-primary btn-shadow btn-block" href="{{url('pomos/start')}}" role="button" > start new pomo! </a>
                    @endif
                    
                    <!-- New Task Form -->
                    <form action="{{ url('pomo') }}" method="POST" class="form-horizontal">
                        {{ csrf_field() }}

                        <!-- Pomo Name -->
                        <div class="form-group" @if($runing_pomo_status != 3) style="display:none" @endif id="formdiv1">
                            <label for="pomo-name" class="col-sm-3 control-label">write your pomo!</label>

                            <div class="col-sm-6">
                                <input type="text" name="name" id="pomo-name" class="form-control" value="{{ old('pomo') }}">
                            </div>
                        </div>

                        <!-- Add Pomo Button -->
                        <div class="form-group" @if($runing_pomo_status != 3) style="display:none" @endif id="formdiv2">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-btn fa-plus"></i>Add Pomo
                                </button>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>

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
                                        <td width="20%">
                                            <form action="{{url('pomo/' . $pomo->id)}}" method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('DELETE') }}

                                                <button type="submit" id="delete-pomo-{{ $pomo->id }}" class="btn btn-danger">
                                                    <i class="fa fa-btn fa-trash"></i>Delete
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
