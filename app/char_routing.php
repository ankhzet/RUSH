<?php

require_once 'models/RoleDrivenRouter.php';

if (!RoledRouter::route('/char', function (RoledRouter $r) {
	$r->role('CharAdminController', [1]);
	$r->role('CharController', [4, 1]);

	$r->bind('char', function($value, $route) { return Char::where('name', $value)->first(); });

	$r->any('exp/{char}');
	$r->any('relevel/{char}');

	$r->with(['get', 'post'], 'create');
	$r->any('delete/{char}');

	$r->any('select');
	$r->any('pick/{char}');

	$r->any('heartstone');

	$r->any('{char}', 'summary');
})) return;