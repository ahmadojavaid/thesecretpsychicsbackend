<?php 

Route::group(['namespace' => 'Modules\Client\Http\Controller\Api','prefix' => 'api'], function () {
    Route::post('doLogin', [ 'uses' => 'UserController@doLogin' ]); 
    Route::post('signup', [ 'uses' => 'UserController@store' ]);
    Route::post('userforgotPassword', ['uses' => 'UserController@userforgotPassword']);
    Route::post('user-validate-code', array('uses' => 'UserController@validateCode'));
    Route::post('user-update-password', array('uses' => 'UserController@updateUserPassword'));

    /*** Client Route ***/

    // Route::get('dashboard', array('uses' => 'UserController@dashboard'));
	// Route::get('get-users', array('uses' => 'UserController@getUsers'));
	// Route::get('get-deactivated-users', array('uses' => 'UserController@getDeactivatedUsers'));
	// Route::get('get-single-user', array('uses' => 'UserController@getSingleUser'));
	// Route::post('admin-login', array('uses' => 'AdminController@adminLogin'));
	// Route::post('admin-reset-password', array('uses' => 'AdminController@generateForgotPassword'));
	// Route::post('update-admin-profile', array('uses' => 'AdminController@updateProfile'));
	Route::get('client-logout', array('uses' => 'UserController@logout'));
	// Route::get('advisor-logout', array('uses' => 'advisorController@logout'));


	// Route::post('delete-user', array('uses' => 'UserController@deleteUser'));
	// Route::post('activate-user', array('uses' => 'UserController@activateUser'));
	// Route::post('deactivate-user', array('uses' => 'UserController@deactivateUser'));
	// Route::post('signup', array('uses' => 'UserController@store')); //
	// Route::post('doLogin', array('uses' => 'UserController@doLogin')); //
	// Route::get('getuserInfo', array('uses' => 'UserController@getuserInfo'));
	// // Route::post('userforgotPassword', array('uses' => 'UserController@userforgotPassword')); //
	// Route::post('user-validate-code', array('uses' => 'UserController@validateCode')); //
	// Route::post('user-update-password', array('uses' => 'UserController@updateUserPassword')); //
	// Route::post('user/{id}', array('uses' => 'UserController@update'));
	// Route::post('update-user', array('uses' => 'UserController@updateUser'));
	// Route::get('user/{id}', array('uses' => 'UserController@userInfo'));

	// Route::post('switch', array('uses' => 'UserController@switch'));
	// Route::post('Contact_Us', array('uses' => 'UserController@Contact_Us'));
	// Route::get('getCashback', array('uses' => 'UserController@getCashback'));

    /*** End Client Route ***/


});
Route::group(['middleware' => ['api', 'multiauth:user'],'namespace' =>'Modules\Client\Http\Controller\Api','prefix' => 'api'], function () {
	Route::get('blog-previews', array('uses' => 'blogsController@blogPreview'));
	Route::get('verifyPromo', array('uses' => 'promocodesController@verifyPromo'));
	Route::get('all-help-videos', array('uses' => 'HelpVideosController@getAllVids'));
	Route::get('blog-details', array('uses' => 'blogsController@singleBlogDetails'));
	Route::post('change-user-password', array('uses' => 'userController@changePassword'));

	Route::get('detailedChat', array('uses' => 'chatController@detailedChat'));
	Route::post('chat', array('uses' => 'chatController@store'));
	Route::get('getConversationuser', array('uses' => 'chatController@getConversationuser'));
});
Route::group(['middleware' => ['api', 'multiauth:user'],'namespace' =>'Modules\Advisor\Http\Controller\Api','prefix' => 'api'], function () {
    
    Route::get('category', array('uses' 	=> 'categoriesController@show'));
    Route::post('credit', array('uses' 		=> 'UserController@addCredit'));
	Route::get('getCredit', array('uses' 	=> 'UserController@getCredit'));
	Route::get('showPsychics', array('uses' => 'userController@showPsychics'));
	Route::get('search', array('uses' => 'advisorController@search'));
	Route::get('showUserOrder', array('uses' => 'ordersController@showUserOrder'));
	Route::get('showAdvisorInfo/{id}', array('uses' => 'advisorController@showAdvisorInfo'));
	Route::get('profileLink', array('uses' => 'advisorController@generateProfileLink'));
	Route::post('favourite', array('uses' => 'advisorController@storefavAdvisor'));
	Route::get('favourite', array('uses' => 'advisorController@showfavAdvisor'));
	Route::post('delfavourite', array('uses' => 'advisorController@destroyfavAdvisor'));
	Route::get('searchAdvisor', array('uses' => 'advisorController@searchAdvisor'));
	Route::get('aboutUs/{id}', array('uses' => 'customersupportController@getAbouUs'));
	Route::get('getterms_of_use/{id}', array('uses' => 'customersupportController@getterms_of_use'));
	Route::get('getprivacypolicies/{id}', array('uses' => 'customersupportController@getprivacypolicies'));
	Route::get('showPsychicsByCat', array('uses' => 'categoriesController@showPsychicsByCat'));
	Route::post('updateOnlineStatus/{id}', array('uses' => 'advisorController@updateOnlineStatus'));
	Route::get('getCashback', array('uses' => 'userController@getCashback'));
	Route::post('user/{id}', array('uses' => 'userController@update'));
	Route::post('order', array('uses' => 'ordersController@store'));
	Route::post('addreview', array('uses' => 'advisorreviewController@store'));
	Route::post('clinet-to-advisor-switch', array('uses' => 'userController@switch'));

	Route::get('client-logout', array('uses' => 'userController@logout'));
});