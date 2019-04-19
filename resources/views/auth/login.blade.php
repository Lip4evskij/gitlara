@extends('layouts.app')

@section('title', 'Авторизация')

@section('content')
    <div class="login_container">
    <div class="title m-b-md">
        gitHub
    </div>
    @if($access_token != '')
        <p>Logged in</p>
    @else
        <a href="https://github.com/login/oauth/authorize?client_id={{$client_id}}">
                <button type="button" class="btn btn-primary authorization">Авторизация</button>
            </a>
    @endif
    </div>

@endsection