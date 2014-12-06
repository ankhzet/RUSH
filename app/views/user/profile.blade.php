@extends('layouts.common')

@section('content')

<div id="profile">
	<h4>User profile:</h4>

	@if($user->id == Auth::id())
	<div class="field">
		<input type="email" value="{{$user->email}}" />
		<label>E-mail:</label>
	</div>
	@endif
	{{--
	<div class="field">
		<input type="password" name="password" value="{{$user->password}}" />
		<label>Password:</label>
	</div>--}}
	<div class="field">
		<input type="text" value="{{$user->name}}" />
		<label>User name:</label>
	</div>

</div>
@stop
