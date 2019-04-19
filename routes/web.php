<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('auth.login');
//});
Route::resource('/', 'MainGitController');
Route::get('/store', 'MainGitController@store');
//Route::get('/ajax', 'MainGitController@ajaxCall');
Route::post('/postajax','MainGitController@ajaxCall');
Route::post('/searchWord' , 'MainGitController@search');