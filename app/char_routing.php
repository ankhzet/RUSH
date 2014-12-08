<?php

require_once 'models/RoleDrivenRouter.php';

if (!RoledRouter::route('/char', function (RoledRouter $r) {
			$r->role('CharAdminController', [1], function (RoledRouter $r) {
				$r->any('exp/{char}');
				$r->any('relevel/{char}');
			});
			$r->role('CharController', [4, 1], function (RoledRouter $r) {
				$r->with(['get', 'post'], 'create');
				$r->any('delete/{char}');

				$r->any('select');
				$r->any('pick/{char}');

				$r->any('heartstone');
			});
			$r->role('CharController', function (RoledRouter $r) {
				$r->any('{char}', 'summary');
			});

			$r->bind('char', function($value, $route) { return Char::where('name', $value)->first(); });
			// Route::model('char', 'Char', function () { 
			// 	throw new NotFoundHttpException;
			// 	// return Redirect::to('/'); 
			// });
		})
	) return;