<?php

Route::any('/', function() { return Redirect::to('/home'); });

Route::controller('home', 'HomeController');

Route::controller('password', 'RemindersController');


require_once 'location_routing.php';
require_once 'char_routing.php';


Route::get('/password/reset/{hash}', ['uses' => 'RemindersController@getReset', 'as' => 'password.reset']);
Route::post('/password/reset/', ['as' => 'password/reset', 'uses' => 'RemindersController@postReset']);

Route::model('user', 'User', function () { throw new NotFoundHttpException; });

Route::get('/user/id{user}', ['as' => 'user.profile', 'uses' => 'UserController@showProfile']);

Route::controller('user', 'UserController');

