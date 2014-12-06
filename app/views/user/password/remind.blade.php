@extends('layouts.common')

@section('content')
<h4>Password remind form:</h4>
<form action="{{ action('RemindersController@postRemind') }}" method="POST">
    <input type="email" name="email">
    <input type="submit" value="Send Reminder">
</form>
@stop
