<?php

	Route::any('/', function() { return Redirect::to('/home'); });

	Route::controller('home', 'HomeController');

	Route::controller('password', 'Auth\PasswordController');

	Route::get('/password/email', ['uses' => 'PasswordController@getEmail', 'as' => 'password.email']);
	Route::post('/password/email', ['uses' => 'PasswordController@postEmail', 'as' => 'password.email']);
	Route::get('/password/reset/{token}', ['uses' => 'PasswordController@getReset', 'as' => 'password.reset']);
	Route::post('/password/reset', ['uses' => 'PasswordController@postReset', 'as' => 'password.reset']);


	Route::controller('auth', 'Auth\AuthController');

	Route::get('/auth', function() {
		return Redirect::to('/user');
	});

