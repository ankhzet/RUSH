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
<div id="registraion">
	<h4>User registration:</h4>

	<form action="{{ action('UserController@postRegister') }}" method="POST">
		<div class="field{{isOk('email')}}">
			<input type="email" name="email" value="{{Input::old('email')}}" />
			<label>E-mail:</label>
		</div>
		<div class="field{{isOk('password')}}">
			<input type="password" name="password" value="{{Input::old('password')}}" />
			<label>Password:</label>
		</div>
		<div class="field{{isOk('password2')}}">
			<input type="password" name="password2" value="{{Input::old('password2')}}" />
			<label>Repeat password:</label>
		</div>
		<div class="field{{isOk('name')}}">
			<input type="text" name="name" value="{{Input::old('name')}}" />
			<label>User name:</label>
		</div>
		<div class="field checkbox{{isOk('agreed')}}">
			<input type="checkbox" name="agreed" value="1" @if(Input::old('agreed')) checked="checked" @endif />
			<label>I'm accepting the <a href="">terms of use</a></label>
		</div>
		<div class="field">
		</div>
		<div class="field">
			<input type="submit" value="Register">
		</div>
	</form>

	@foreach ($errors->all() as $field => $error)
		<span class="error-label">Error: {{$error}}</span><br/>
	@endforeach

</div>
@stop
