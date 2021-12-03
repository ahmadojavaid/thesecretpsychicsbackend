<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// //User Open Routes
// Route::post('/user-signup', 'Api\Auth\UserAuthController@signUp');
// Route::post('/user-login', 'Api\Auth\UserAuthController@login');

// //Agent Open Routes
// Route::post('/agent-signup', 'Api\Auth\AgentAuthController@signUp');
// Route::post('/validate-email', 'Api\Auth\AgentAuthController@ValidateEmail');
// Route::post('/agent-login', 'Api\Auth\AgentAuthController@login');

// //Authenticated Agent Routes
// Route::group(['middleware' => ['api', 'multiauth:agent']], function () {
//    Route::get('agent_aa',function(){
//         dd('agent_login');
//    });
// });

// //Authenticated User Routes
// Route::group(['middleware' => ['api', 'multiauth:user']], function () {
//     Route::get('user_aa',function(){
//         dd('user_login');
//     });
// });
