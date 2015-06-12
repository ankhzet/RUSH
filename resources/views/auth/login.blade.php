@extends('layouts.common')

@section('content')

<?php
	global $e;
	$e = $errors;
	function isOk($field) {
		global $e;
		return $e->has($field) ? ' error' : '';
	}
?>
<div id="login">
	<h4>User login:</h4>

	<form action="{{ action('Auth\AuthController@postLogin') }}" method="POST">
		<input type="hidden" name="_token" value="{{csrf_token()}}" />
		<div class="field{{isOk('email')}}">
			<input type="email" name="email" value="{{Input::old('email')}}" />
			<label>E-mail:</label>
		</div>
		<div class="field{{isOk('password')}}">
			<input type="password" name="password" value="{{Input::old('password')}}" />
			<label>Password:</label>
		</div>
		<div class="field checkbox{{isOk('remember')}}">
			<input type="checkbox" name="remember" value="1" @if(Input::old('remember')) checked="checked" @endif />
			<label>Remember me</label>
		</div>
		<div class="field">
		</div>
		<div class="field">
			<input type="submit" value="Login">
		</div>
	</form>

	@foreach ($errors->all() as $field => $error)
		<span class="error-label">Error: {{$error}}</span><br/>
	@endforeach

</div>
@stop
