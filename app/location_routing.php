<?php

require_once 'models/RoleDrivenRouter.php';

if (!RoledRouter::route('/location', function (RoledRouter $r) {
	// $r->role('CharAdminController', [1]);
	$r->role('LocationController', [4, 1]);

	// Instance filtering
	$r->bind('location', function($value, $route) { return Location::where('title', $value)->first(); });

	// Basic functionality

	$r->any('render/{location}');
	$r->any('render');

	$r->any('{location}', 'index');
})) return;
