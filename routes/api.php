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
		Route::prefix('login')->group(function () {
			Route::post('/', 'Api\AuthController@login');
		});
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
			Route::get('/', 'Api\Profile\ProfileController@get');
			Route::put('/', 'Api\Profile\ProfileController@update');
			Route::prefix('change')->group(function () {
				Route::post('/password', 'Api\Profile\ProfileController@changePassword');
				Route::post('/email', 'Api\Profile\ProfileController@changeEmail');
			});
			// Freelancer Profile
			Route::post('/upgrade', 'Api\Profile\FreelancerController@upgrade');
			Route::prefix('freelancer')->group(function () {
				Route::get('/', 'Api\Profile\FreelancerController@get');
				Route::put('/', 'Api\Profile\FreelancerController@update');
				Route::delete('/', 'Api\Profile\FreelancerController@destroy');
			});
			// Publications
			Route::prefix('publications')->group(function () {
				Route::get('/', 'Api\Profile\PublicationController@index');
				Route::post('/', 'Api\Profile\PublicationController@store');
				Route::get('/{publication:id}', 'Api\Profile\PublicationController@show');
				Route::put('/{publication:id}', 'Api\Profile\PublicationController@update');
				Route::delete('/{publication:id}', 'Api\Profile\PublicationController@destroy');
				Route::put('/{publication:id}/activate', 'Api\Profile\PublicationController@activate');
				Route::put('/{publication:id}/deactivate', 'Api\Profile\PublicationController@deactivate');
			});
		});

		// Favorites
		Route::group(['prefix' => 'favorites'], function () {
			Route::get('/', 'Api\Profile\FavoriteController@index');
			Route::post('/{publication:id}', 'Api\Profile\FavoriteController@store');
			Route::delete('/{favorite:id}', 'Api\Profile\FavoriteController@destroy');
		});

		// Users
		Route::group(['prefix' => 'users'], function () {
			Route::get('/', 'Api\UserController@index')->middleware('permission:users.index');
			Route::post('/', 'Api\UserController@store')->middleware('permission:users.create');
			Route::get('/{user:id}', 'Api\UserController@show')->middleware('permission:users.show');
			Route::put('/{user:id}', 'Api\UserController@update')->middleware('permission:users.edit');
			Route::delete('/{user:id}', 'Api\UserController@destroy')->middleware('permission:users.delete');
			Route::put('/{user:id}/activate', 'Api\UserController@activate')->middleware('permission:users.active');
			Route::put('/{user:id}/deactivate', 'Api\UserController@deactivate')->middleware('permission:users.deactive');
		});

		// Categories
		Route::group(['prefix' => 'categories'], function () {
			Route::get('/', 'Api\CategoryController@index')->middleware('permission:categories.index');
			Route::post('/', 'Api\CategoryController@store')->middleware('permission:categories.create');
			Route::get('/{category:id}', 'Api\CategoryController@show')->middleware('permission:categories.show');
			Route::put('/{category:id}', 'Api\CategoryController@update')->middleware('permission:categories.edit');
			Route::delete('/{category:id}', 'Api\CategoryController@destroy')->middleware('permission:categories.delete');
			Route::put('/{category:id}/activate', 'Api\CategoryController@activate')->middleware('permission:categories.active');
			Route::put('/{category:id}/deactivate', 'Api\CategoryController@deactivate')->middleware('permission:categories.deactive');
		});

		// Languages
		Route::group(['prefix' => 'languages'], function () {
			Route::get('/', 'Api\LanguageController@index')->middleware('permission:languages.index');
			Route::post('/', 'Api\LanguageController@store')->middleware('permission:languages.create');
			Route::get('/{language:id}', 'Api\LanguageController@show')->middleware('permission:languages.show');
			Route::put('/{language:id}', 'Api\LanguageController@update')->middleware('permission:languages.edit');
			Route::delete('/{language:id}', 'Api\LanguageController@destroy')->middleware('permission:languages.delete');
			Route::put('/{language:id}/activate', 'Api\LanguageController@activate')->middleware('permission:languages.active');
			Route::put('/{language:id}/deactivate', 'Api\LanguageController@deactivate')->middleware('permission:languages.deactive');
		});

		// Publications
		Route::group(['prefix' => 'publications'], function () {
			Route::get('/', 'Api\PublicationController@index')->middleware('permission:publications.index');
			Route::get('/{publication:id}', 'Api\PublicationController@show')->middleware('permission:publications.show');
			Route::delete('/{publication:id}', 'Api\PublicationController@destroy')->middleware('permission:publications.delete');
		});
	});

	//////////////////////////////////////// DATA ////////////////////////////////////////////////////
Route::get('/countries', 'Api\CountryController@index');
});