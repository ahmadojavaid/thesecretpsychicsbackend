<?php
Route::get('',function(){
	return '<h3>Coming Soon</h3>';
});
Route::group(['middleware' => 'guest:admin_web'], function () {
	
	Route::get('admin/login',[
		'as' 	=> 'ad_login',
		'uses'  => 'Admin\AuthController@loginViewAdmin'
	]); 
	Route::post('admin_login',[
		'as' 	=> 'admin_login',
		'uses'  => 'Admin\AuthController@adminLogin'
	]);
	 
});
Route::group(['middleware' => ['auth:admin_web'],'prefix' => 'admin','as' => 'admin.'], function () {
	Route::get('dashboard',[
		'as' 	=> 'dashboard',
		'uses'  => 'Admin\DashboardController@index'
	]);
	Route::get('admin_logout',[
		'as' 	=> 'admin_logout',
		'uses'  => 'Admin\AuthController@adminLogout'
	]);
});
