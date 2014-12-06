@extends('layouts.structure')

@section('content')

<?php
	function withDefault($val, $default) {
		return intval($val) ? intval($val) : $default;
	}

	function check($field, $value, $checker = ' checked="checked"') {
		return (intval($field) == $value) ? $checker : '';
	}

	function select($field) {
		return check($field, 1, 'selected="selected"');
	}

	function hide($field) {
		return 'class="field' . ($field ? ' hidden' : '') . '"';
	}

	$step = withDefault(isset($input['step']) ? $input['step'] : 0, 0);

	$name = Input::get('char.name');
	$frac = withDefault(Input::get('char.frac'), 0);
	$race = withDefault(Input::get('char.race'), 0);
	$spec = withDefault(Input::get('char.spec'), 0);

	$fr = RUSH::fracRaces();
	$f  = RUSH::fracs();
	$r  = RUSH::races();
	$s  = RUSH::specs();
?>
	<form id="creation" action="{{action('CharController@postCreate')}}" method="post" />
		<input type=hidden name="step" value="{{$step + 1}}" />
		
		<div {{hide($name)}}>
			<input type=text id=idname name="char[name]" value="{{$name}}" />
			<label for=idname>Имя персонажа:</label>
		</div>

		@if($name)
			<div {{hide($frac)}}>
				<div>
					<input type=radio id=idalliance name="char[frac]" value="1" {{check($frac, 1)}} />
					<label for=idalliance>{{$f[RUSH::FRAC_ALLIANCE]}}</label>
				</div>
				<div>
					<input type=radio id=idhorde name="char[frac]" value="2" {{check($frac, 2)}} />
					<label for=idhorde>{{$f[RUSH::FRAC_HORDE]}}</label>
				</div>
				<label>Фракция:</label>
			</div>

			@if($frac)
				<div {{hide($race)}}>
					<select id=idrace name="char[race]">
						<option value="0" disabled {{select(!$race)}}>-- {{$f[$frac]}}</option>
					@foreach ($fr[$frac] as $_race)
						<option value="{{$_race}}" {{select($race && ($race == $_race))}}>{{$r[$_race]}}</option>
					@endforeach
					</select>
					<label for=idrace>Раса:</label>
				</div>

				@if($race)
					<div {{hide($spec)}}>
						<div>
							<input type=radio id=idwarrior name="char[spec]" value="1" {{check($spec, 1)}}/>
							<label for=idwarrior>{{$s[RUSH::SPEC_WARRIOR]}}</label>
						</div>
						<div>
							<input type=radio id=idmage name="char[spec]" value="2" {{check($spec, 2)}}/>
							<label for=idmage>{{$s[RUSH::SPEC_MAGE]}}</label>
						</div>
						<label>Специализация:</label>
					</div>

					@if($spec)
						<div>
							<h4>Новый персонаж</h4>
							<p>"<a>{{$name}}</a>", {{$r[$race]}} - {{$s[$spec]}} ({{$f[$frac]}})</p>
							
							<?php $location = Location::get($loc = CharHelper::startLocation($frac, $race)); ?>
							<h4>Стартовая локация:</h4>
							@if($location)
							<p>{{$location->makeClickable()}}</a>: {{$location->description}}</p>
							@else
							<p>{$location.undefined}</p>
							@endif
						</div>
					@endif
				@endif
			@endif
		@endif

		@foreach ($errors->all() as $error)
			<span class="error-label">{$error}: {{$error}}</span><br/>
		@endforeach

		<div class="field"></div>
		<div class="field">
			<input type=submit value=" {$back} " onclick="history.back()"{{$name ? '' : ' disabled'}}/>
			<input type=submit value=" @if($spec) @if($location) {$create} @else {$error} @endif @else {$next} @endif " />
		</div>
	</form>

@stop
