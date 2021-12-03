<?php 

Route::group(['namespace' => 'Modules\Advisor\Http\Controller\Api','prefix' => 'api'], function () {
    Route::post('advisordoLogin', [ 'uses' => 'AdvisorController@doLogin' ]);
    // Route::post('advisordoLogin', [ 'uses' => 'AdvisorController@doLogin' ]);



    /** as it is copy ***/

    Route::post('advisorsignup', array('uses' => 'advisorController@store'));
	Route::post('advisorforgotPassword', array('uses' => 'advisorController@advisorforgotPassword'));
	Route::post('advisor-validate-code', array('uses' => 'advisorController@validateCode'));
	Route::post('advisor-update-password', array('uses' => 'advisorController@updateAdvisorPassword'));
	// Route::post('advisordoLogin', array('uses' => 'advisorController@doLogin'));
	// Route::post('External_Login', array('uses' => 'userController@External_Login'));
	// Route::post('addTovisitor', array('uses' => 'advisorController@addTovisitor'));
	
	
	// Route::post('updatenotifyStatus/{id}', array('uses' => 'advisorController@updatenotifyStatus'));
	// Route::get('showAdvisorInfo/{id}', array('uses' => 'advisorController@showAdvisorInfo'));
	// Route::get('showAdvisor/{id}', array('uses' => 'advisorController@showAdvisor'));
	// Route::get('searchh', array('uses' => 'advisorController@search'));
	// Route::get('filter', array('uses' => 'advisorController@filter'));
	// Route::get('categoriesController', array('uses' => 'advisorController@advanceSearch'));
	// Route::post('assignCat', array('uses' => 'advisorController@assignCat'));
	// Route::post('uploadVid', array('uses' => 'advisorController@uploadVid'));
	// Route::post('advisorforgotPassword', array('uses' => 'advisorController@advisorforgotPassword'));
	// Route::post('advisor-validate-code', array('uses' => 'advisorController@validateCode'));
	// Route::post('advisor-update-password', array('uses' => 'advisorController@updateAdvisorPassword'));
	// Route::get('searchAdvisorr', array('uses' => 'advisorController@searchAdvisor'));
	// Route::get('profileLinkk', array('uses' => 'advisorController@generateProfileLink'));
	// Route::get('advOrderRecord', array('uses' => 'advisorController@advOrderRecord'));
	//........ From to Earnings of advisor
	// Route::get('get-advisor-earning', array('uses' => 'paymentsController@earningsReport'));
	//..... Deactivate Advisor
	// Route::get('deactivate-advisor/{id}', array('uses' => 'advisorController@deactivateAdvisor'));
	// Route::get('activate-advisor/{id}', array('uses' => 'advisorController@activateAdvisor'));
	// Route::get('delete-advisor/{id}', array('uses' => 'advisorController@deleteAdvisor'));


	

	
    


    
});

Route::group(['middleware' => ['api', 'multiauth:advisor'],'namespace' =>'Modules\Advisor\Http\Controller\Api','prefix' => 'api'], function () {

    /*** order api **/
	Route::get('showAdvisorOrder', array('uses' => 'ordersController@showAdvisorOrder'));
	/**** End order Api **/

	Route::get('show-advisor-info/{id}', array('uses' => 'advisorController@showAdvisorInfo'));

	//.........Payment Routes
	Route::get('payment', array('uses' => 'paymentsController@show'));
	Route::get('withdraw-amount', array('uses' => 'paymentsController@withdrawAmount'));

	Route::get('advisor-category', array('uses' => 'categoriesController@show'));

	Route::post('assignCat', array('uses' => 'advisorController@assignCat'));
	Route::post('uploadVid', array('uses' => 'advisorController@uploadVid'));
	Route::post('update-online-status/{id}', array('uses' => 'advisorController@updateOnlineStatus'));

	Route::post('updateOrderSeen/{id}', array('uses' => 'ordersController@updateOrderSeen'));

	Route::post('updateAdvisorInfo/{id}', array('uses' => 'advisorController@updateAdvisorInfo'));
	Route::post('order/{id}', array('uses' => 'ordersController@update'));


	// Route::post('aboutUs', array('uses' => 'customersupportController@addAbouUs'));
	Route::get('about-us/{id}', array('uses' => 'customersupportController@getAbouUs'));
	Route::get('get-term-of-use/{id}', array('uses' => 'customersupportController@getterms_of_use'));
	Route::get('get-privacy-policies/{id}', array('uses' => 'customersupportController@getprivacypolicies'));
	Route::get('get-credit', array('uses' => 'userController@getCredit'));

	Route::get('getbecomeAdvisor/{id}', array('uses' => 'customersupportController@getBecomingAnAdvisors'));
	Route::get('get-exp/{id}', array('uses' => 'customersupportController@getexperience'));
	Route::get('get-ordering-instruction/{id}', array('uses' => 'customersupportController@getorderingInstruction'));
	Route::get('getprofilesetup/{id}', array('uses' => 'customersupportController@getProfileSetups'));
	Route::get('gettermscondition/{id}', array('uses' => 'customersupportController@gettermsconditions'));


	Route::post('support', array('uses' => 'customersupportController@store'));
	Route::get('get-advisor-support-message', array('uses' => 'customersupportController@getAdvisorSupportMessage'));
	Route::get('get-advisors-web-support', array('uses' => 'customersupportController@getAdvisorsCustomerSupport'));

	Route::post('change-user-password', array('uses' => 'userController@changePassword'));

	Route::get('advisor-detailedChat', array('uses' => 'chatController@detailedChat'));
	Route::post('advisor-chat', array('uses' => 'chatController@store'));
	Route::get('getConversationAdvisor', array('uses' => 'chatController@getConversationAdvisor'));
	Route::post('advisor-to-clinet-switch', array('uses' => 'userController@switch'));
	Route::get('advisor-logout', array('uses' => 'advisorController@logout'));
});