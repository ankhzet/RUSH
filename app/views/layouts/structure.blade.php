<?php

	$c = RUSH::oGet('char');
	$n = RUSH::oGet('npc');

	$char = $c ? Char::get($c) : null;

	$globalMenu = array(
		'home'       => array( 1, 'index')
	, 'char/select' => array( 1, 'char.select')
	);

	$ingameMenu = array(
//	, 'npc'         => array($n, 'npc')
	  'location'    => array($c, 'location')
	, 'char/home'    => array($c, 'heartstone')
	, 'inventory'   => array($c, 'inventory')
	);

	function makeMenu($items) {
		$current = Request::segments(0)[0];

		$t = array();
		foreach ($items as $link => $arr)
			if ($arr[0]) {
				$link = URL::to($link != 'home' ? $link : '');
				$selected = ($current == $link) ? ' class=\"selected\"' : '';
				$t[] = "<li$selected><a href=\"$link\">{\${$arr[1]}}</a></li>";
			}

		return $t;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />

 	  {{ HTML::style('/public/css/style.less', array('rel' => 'stylesheet/less')); }}
 	  {{ HTML::script('/public/js/less.min.js'); }}

 	  {{ HTML::script('jquery.js'); }}

		<title>Homepage - @lang('site')</title>
		<meta name="keywords" content="" />
		<meta name="description" content="" />
	</head>
	<body class="{{Request::segments(0)[0]}}-page">

		@include('layouts.logo')

		<ul class="menu">
			{{join(' | ', makeMenu($globalMenu))}}
			@if ($ingame = makeMenu($ingameMenu))
			<ul class="in-game menu">
				{{join(' | ', $ingame)}}
			</ul>
			@endif
		</ul>

		<hr /> 

		@if (isset($content)) {{$content}} @else
		@yield('content')
		@endif

		@include('layouts.dialogctl')
		
		<footer>
			<ul>
				<li><a href="{{URL::to('/')}}">RUSH</a> &copy; 2014 All rights reserved.</li>
			</ul>
		</footer>
	</body>
</html>