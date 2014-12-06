@extends('layouts.structure')

@section('content')

@foreach($chars->get() as $char)
	<div class="charsel">
		<p class="cstat">
			{{$char->makeClickable()}}
			(локация {{$char->location->makeClickable()}})
			<span>[{{$char->level}}]</span>
		</p>
		<p>{{$char->specStr()}}<span class="del">[{{$char->makeClickable('delete', '{$delete}')}}]</span></p>
	</div>
@endforeach

<br />
@stop
