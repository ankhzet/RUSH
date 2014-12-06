<?php


// Admin functionality
Route::get('/char/exp/{char}', ['uses' => 'CharAdminController@getExp', 'as' => 'char.exp']);
Route::get('/char/relevel/{char}', ['uses' => 'CharAdminController@getRelevel', 'as' => 'char.relevel']);

// Basic functionality

Route::get('/char/create', ['uses' => 'CharController@getCreate', 'as' => 'char.create']);
Route::get('/char/delete/{char}', ['uses' => 'CharController@getDelete', 'as' => 'char.delete']);

Route::get('/char/select', ['uses' => 'CharController@anySelect', 'as' => 'char.index']);
Route::get('/char/pick/{char}', ['as' => 'char.pick', 'uses' => 'CharController@anyPick']);

Route::get('/char/home', ['uses' => 'CharController@getHome', 'as' => 'char.heartstone']);

Route::get('/char/{char}', ['as' => 'char.summary', 'uses' => 'CharController@getSummary']);

// Instance filtering

Route::bind('char', function($value, $route) { return Char::where('name', $value)->first(); });

//

Route::controller('char', 'CharController');
