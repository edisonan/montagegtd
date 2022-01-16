
@extends('layouts.app')

@section('content')
<div class="container">

<div>
<img src="/img/new/neterror.png" width="200px"/>
{{ $exception->getMessage() }}
</div>
</div>
@endsection

