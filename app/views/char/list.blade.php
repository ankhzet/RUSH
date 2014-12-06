@extends('layouts.structure')

@section('content')

@if (count($c = $chars->get()))
@foreach($c as $char)
	<div class="charsel">
		<p class="cstat">
			{{$char->makeClickable()}}
			(локация {{$char->location->makeClickable()}})
			<span>[{{$char->level}}]</span>
		</p>
		<p>{{$char->specStr()}}<span class="del">[{{$char->makeClickable('delete', '{$delete}')}}]</span></p>
	</div>
@endforeach
@else
	<p>No chars</p>
@endif
<br />
@stop
