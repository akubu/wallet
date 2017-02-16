<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::group(['middleware' => 'logger'], function() {

    Route::group(['prefix' => 'api'], function()
    {
//        Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
        Route::post('authenticate', 'AuthenticateController@authenticate');
        Route::post('credit','walletController@credit');
        Route::post('debit','walletController@debit');
        Route::post('lock','walletController@lock');
        Route::post('unlock','walletController@unlock');
        Route::get('getWalletDetails','walletController@getWalletDetails');
        Route::get('getCreditDetails','walletController@getCreditDetails');
        Route::get('getDebitDetails','walletController@getDebitDetails');
    });

});

//Route::get('/', function () {
//    return view('welcome');
//});



/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

//Route::group(['middleware' => ['web']], function () {
//    //
//});
