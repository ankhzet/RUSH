@if (Request::segments(0)[0] != 'home')
	<a href="{{URL::to('char')}}"><img class="char" src="/models/rush_charplate.php?rid={{rand(11111, 99999)}}" /></a>
@else
	<a href="{{URL::to('/')}}"><img class="char" src="/public/img/logo.png" /></a>
@endif
