@extends('layouts.common')

@section('content')

	<div>Homepage</div>

	<div>@if(Auth::guest())<a href="{{URL::to('auth/login')}}">User login</a> @else <a href="{{URL::to('auth/logout')}}">User logout</a> @endif </div>

@stop