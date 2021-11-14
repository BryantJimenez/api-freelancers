<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function() {
	/////////////////////////////////////// AUTH ////////////////////////////////////////////////////
	Route::group(['prefix' => 'auth'], function() {
		Route::post('/login', 'Api\AuthController@login');
		Route::post('/register', 'Api\AuthController@register');
		Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');

		Route::group(['middleware' => 'auth:api'], function() {
			Route::get('/logout', 'Api\AuthController@logout');
		});
	});

	/////////////////////////////////////// ADMIN ////////////////////////////////////////////////////
	Route::group(['middleware' => 'auth:api'], function () {
		// Profile
		Route::group(['prefix' => 'profile'], function () {
			Route::get('/', 'Api\User\ProfileController@get');
			Route::put('/', 'Api\User\ProfileController@update');
			Route::prefix('change')->group(function () {
				Route::post('/password', 'Api\User\ProfileController@changePassword');
				Route::post('/email', 'Api\User\ProfileController@changeEmail');
			});
		});

		// Users
		Route::group(['prefix' => 'users'], function () {
			Route::get('/', 'Api\User\UserController@index')->middleware('permission:users.index');
			Route::post('/', 'Api\User\UserController@store')->middleware('permission:users.create');
			Route::get('/{user:id}', 'Api\User\UserController@show')->middleware('permission:users.show');
			Route::put('/{user:id}', 'Api\User\UserController@update')->middleware('permission:users.edit');
			Route::delete('/{user:id}', 'Api\User\UserController@destroy')->middleware('permission:users.delete');
			Route::put('/{user:id}/activate', 'Api\User\UserController@activate')->middleware('permission:users.active');
			Route::put('/{user:id}/deactivate', 'Api\User\UserController@deactivate')->middleware('permission:users.deactive');
		});

		// Categories
		Route::group(['prefix' => 'categories'], function () {
			Route::get('/', 'Api\CategoryController@index')->middleware('permission:categories.index');
			Route::post('/', 'Api\CategoryController@store')->middleware('permission:categories.create');
			Route::get('/{category:id}', 'Api\CategoryController@show')->middleware('permission:categories.show');
			Route::put('/{category:id}', 'Api\CategoryController@update')->middleware('permission:categories.edit');
			Route::delete('/{category:id}', 'Api\CategoryController@destroy')->middleware('permission:categories.delete');
			Route::put('/{category:id}/activar', 'Api\CategoryController@activate')->middleware('permission:categories.active');
			Route::put('/{category:id}/desactivar', 'Api\CategoryController@deactivate')->middleware('permission:categories.deactive');
		});
	});
});