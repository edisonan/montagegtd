@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="text-center" style="
    padding-top: 50px;
    padding-bottom: 50px;
">
            <img src="/img/new/neterror.png" width="200px"/>
            {{ $exception->getMessage() }}
        </div>
    </div>
@endsection

