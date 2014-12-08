<?php

require_once 'models/RoleDrivenRouter.php';

if (!RoledRouter::route('/location', function (RoledRouter $r) {
			$r->role('LocationController', [4], function (RoledRouter $r) {
				// Basic functionality

				$r->any('render/{location}');
				$r->any('render');

				$r->any('{location}', 'index');
				$r->any('', 'index');
			});

			// Instance filtering
			$r->bind('location', function($value, $route) { return Location::where('title', $value)->first(); });
		})
	) return;
