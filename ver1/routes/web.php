<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/





Route::group([
    'prefix' => 'api/v1',
    'namespace' => 'Api'
], function () {
    Route::post('/auth/register', 'AuthController@register');
    Route::post('/auth/register-otp', 'AuthController@registerOTP');
    Route::post('/auth/login', 'AuthController@login');
    Route::post('/auth/recover', 'AuthController@recoverPassword');
    Route::get('/auth/test', 'AuthController@test');
	Route::post('/getUserByToken', 'AuthController@getUserByToken');

});

Route::group([
    'namespace' => 'Api'
], function () {
	Route::post('/contact-us/request-a-payment-card', 'VehicleController@sendRequestForAPaymentCard');
});

Route::group([
    'prefix' => 'api/v2',
    'namespace' => 'Api'
], function () {
    /*Manage Accounts*/
	//eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cL3RheGl6YW1iaWEuY29tXC9hcGlcL3YxXC9hdXRoXC9sb2dpbiIsImlhdCI6MTUwNzIxODgwMSwiZXhwIjoxNTA3MjIyNDAxLCJuYmYiOjE1MDcyMTg4MDEsImp0aSI6IjhjMDM1MzIzMTEzMzA1OTYzZTc5MzM5Y2FkYzU3NTMxIn0.dK-ataSHh_oyvsWbJFvYV2VySz_quwZl83V_kBeuIVE
	Route::post('/accounts/pulllist', 'AccountsController@pulllist');
	Route::post('/getAvailableVehiclesBetweenPoints', 'VehicleController@getAvailableVehiclesBetweenPoints');
	Route::post('/getActiveDrivers', 'VehicleController@getActiveDrivers');
	Route::post('/getSearchByAddress', 'VehicleController@getSearchByAddress');
	Route::post('/getDriverDeal', 'VehicleController@getDriverDeal');
	Route::post('/getDriverDealByToken', 'VehicleController@getDriverDealByToken');
	Route::post('/makeDriverDeal', 'VehicleController@makeDriverDeal');
	Route::post('/sendDriverDeals', 'VehicleController@sendDriverDeals');
	Route::post('/removeDriverDeal', 'VehicleController@removeDriverDeal');
	Route::post('/driverCancelTrip', 'VehicleController@driverCancelTrip');
	Route::post('/getTripById', 'VehicleController@getTripById');
	Route::post('/getTripGoingById', 'VehicleController@getTripGoingById');
	Route::post('/getDriverPosition', 'VehicleController@getDriverPosition');
	Route::post('/verifyProbaseWallet', 'VehicleController@verifyProbaseWallet');
	Route::post('/getTrips', 'VehicleController@getTrips');
	Route::post('/getUncompletedTrips', 'VehicleController@getUncompletedTrips');
	Route::post('/getUncompletedDealByGroupId', 'VehicleController@getUncompletedDealByGroupId');
	Route::post('/sendSupportMessage', 'VehicleController@sendSupportMessage');
	Route::post('/getDealForDriver', 'VehicleController@getDealForDriver');
	Route::post('/getTripFromDeal', 'VehicleController@getTripFromDeal');
	Route::post('/getPassenger', 'VehicleController@getPassenger');
	Route::post('/dropOffPassenger', 'VehicleController@dropOffPassenger');
	Route::post('/acceptJob', 'VehicleController@acceptJob');
	Route::post('/setTripGoingById', 'VehicleController@setTripGoingById');
	Route::post('/getGoingTripById', 'VehicleController@getGoingTripById');
	Route::post('/rateTrip', 'VehicleController@rateTrip');
	Route::post('/getDriverDealStatus', 'VehicleController@getDriverDealStatus');
	Route::post('/cancelPassengerOpenDeals', 'VehicleController@cancelPassengerOpenDeals');
	Route::post('/payTripFeeUsingCard', 'VehicleController@payTripFeeUsingCard');
	Route::post('/payTripFeeUsingCardStepTwo', 'VehicleController@payTripFeeUsingCardStepTwo');
	Route::post('/payTripFeeUsingCash', 'VehicleController@payTripFeeUsingCash');
	Route::post('/getTransactionByTripId', 'VehicleController@getTransactionByTripId');
	Route::post('/getTripsOfDriver', 'VehicleController@getTripsOfDriver');
	Route::post('/getTripsOfDriverForWallet', 'VehicleController@getTripsOfDriverForWallet');
	Route::post('/getTransactionsOfDriver', 'VehicleController@getTransactionsOfDriver');

	Route::post('/updateAvailabilityForJob', 'VehicleController@updateAvailabilityForJob');
	Route::post('/widthDraw', 'VehicleController@widthDraw');
	Route::post('/logout-user', 'AuthController@logoutUser');
	Route::post('/update-vehicle-position', 'VehicleController@updateVehiclePosition');
	Route::post('/update-vehicle-position-batch', 'VehicleController@updateVehiclePositionBatch');
	Route::get('/update-vehicle-position-batch', 'VehicleController@updateVehiclePositionBatch');
	Route::post('/pullRegisterData', 'VehicleController@pullRegisterData');
	Route::post('/getDriverPosition1', 'VehicleController@getDriverPosition1');
	Route::post('/payTripFeeUsingProbaseWallet', 'VehicleController@payTripFeeUsingProbaseWallet');
	Route::post('/payTripFeeUsingProbaseWalletStepTwo', 'VehicleController@payTripFeeUsingProbaseWalletStepTwo');
	Route::post('/authenticate-probasepay-wallet', 'VehicleController@authenticateProbasePayWallet');
	Route::post('/authenticate-probasepay-wallet-with-otp', 'VehicleController@authenticateProbasePayWalletWithOtp');
	Route::post('/updateUserOneSignalId', 'VehicleController@updateUserOneSignalId');
	Route::post('/getDealBySpecificId', 'VehicleController@getDealBySpecificId');
	Route::post('/getPassengerActiveTrip', 'VehicleController@getPassengerActiveTrip');
	Route::post('/receiveCashPayment', 'VehicleController@receiveCashPayment');
	Route::post('/getDriverPositionBatch', 'VehicleController@getDriverPositionBatch');
	Route::post('/getAdvertsRandomly', 'VehicleController@getAdvertsRandomly');
	Route::post('/checkIfMobileNumberExist', 'VehicleController@checkIfMobileNumberExist');
	Route::post('/validateMobileNumber', 'AuthController@validateMobileNumber');
	Route::post('/verifyEmailAddress', 'AuthController@verifyEmailAddress');
	Route::post('/validatePassword', 'AuthController@validatePassword');
	Route::post('/checkPaymentStatus', 'VehicleController@checkPaymentStatus');
	Route::post('/signUpUserProfile', 'AuthController@signUpUserProfile');
	Route::post('/enableTripCodeAction', 'VehicleController@enableTripCodeAction');
	Route::post('/enablePinAction', 'VehicleController@enablePinAction');
	Route::post('/turnOffAdvertsAction', 'VehicleController@turnOffAdvertsAction');
	Route::post('/getUserSettings', 'VehicleController@getUserSettings');
	Route::post('/confirmPassengerCancelTrip', 'VehicleController@confirmPassengerCancelTrip');
	Route::post('/validateBioData', 'AuthController@validateBioData');
	Route::post('/getUserData', 'VehicleController@getUserData');
	Route::post('/updateProfilePix', 'VehicleController@updateProfilePix');
	Route::post('/getSearchPreviousAddress', 'VehicleController@getSearchPreviousAddress');
	Route::post('/setDriverAvailable', 'VehicleController@postSetDriverAvailable');
	Route::post('/sendSos', 'VehicleController@postSendSOS');
	Route::post('/submitTripRating', 'VehicleController@submitTripRating');
	Route::post('/setArrivedForTripByTripId', 'VehicleController@setArrivedForTripByTripId');
	Route::post('/get-wallet-card-balances', 'VehicleController@getWalletCardBalances');
	Route::post('/get-menu-advert', 'VehicleController@getMenuAdvert');
	Route::get('/test', 'VehicleController@getTest');

});






Route::group([
    'namespace' => 'Web'
], function () {
	Route::get('/', 'ActionController@getIndex');
	Route::get('login', 'AuthController@getLogin');
    Route::get('/auth/login', 'AuthController@getLogin');
	Route::post('/auth/login', 'AuthController@login');
	Route::get('/auth/logout', 'AuthController@logoutUser');
	Route::post('/payments/handle-response-success', 'ActionController@handleSuccessPaymentResponse');
	Route::get('/payments/handle-response-success', 'ActionController@handleSuccessPaymentResponse');
	Route::post('/payments/handle-response-fail', 'ActionController@handleFailPaymentResponse');
	Route::get('/payments/handle-response-fail', 'ActionController@handleFailPaymentResponse');
	Route::get('/payments/redirect-to-success-page/{txnId}', 'ActionController@handleDisplaySucessPage');
	Route::get('/payments/redirect-to-fail-page/{txnId}', 'ActionController@handleDisplayFailPage');
	Route::get('/payments/initiate-payment/{tripId}/{deviceRefNo}/{token}', 'ActionController@handleGetInitiatePayment');
	Route::get('/payments/initiate-deposit-payment/{deviceRefNo}/{token}/{amt}/{type}', 'ActionController@handleGetInitiateDepositPayment');
	Route::get('/payments/initiate-tokenize/{token}/{type}', 'ActionController@handleGetInitiateTokenize');

});


Route::group([
		'namespace' => 'Web',
		'middleware' => ['auth'],
		'prefix' => 'admin'
], function () {
	Route::get('/dashboard', 'ActionController@getDashboard');

	Route::get('/drivers/all-drivers', 'ActionController@getAllDrivers');
	Route::get('/drivers/all-vehicles', 'ActionController@getAllVehicles');

	Route::get('/passengers/all-passengers', 'ActionController@getAllPassengers');

	Route::get('/trip-request/all-trip-requests', 'ActionController@getAllTripRequests');
	Route::get('/trip-request/accepted-trip-requests', 'ActionController@getAcceptedTripRequests');
	Route::get('/trip-request/canceled-trip-requests', 'ActionController@getCanceledTripRequests');
	Route::get('/trip-request/pending-trip-requests', 'ActionController@getPendingTripRequests');

	Route::get('/trips/all-trips', 'ActionController@getAllTrips');

	Route::get('/trips/completed-trips', 'ActionController@getCompletedTrips');
	Route::get('/trips/ongoing-trips', 'ActionController@getOngoingTrips');
	Route::get('/trips/canceled-trips', 'ActionController@getCanceledTrips');

	Route::get('/financials/all-payments', 'ActionController@getAllPayments');
	Route::get('/financials/pending-payments', 'ActionController@getPendingPayments');
	Route::get('/financials/cash-payments', 'ActionController@getCashPayments');
	Route::get('/financials/card-payments', 'ActionController@getCardPayments');
	Route::get('/financials/wallet-payments', 'ActionController@getWalletPayments');
	Route::get('/financials/withdrawal-requests', 'ActionController@getWithdrawalRequests');
	Route::get('/financials/driver-payouts', 'ActionController@getDriverPayouts');
	Route::get('/financials/driver-deposits', 'ActionController@getDriverDeposits');
	Route::post('/financials/fund-driver-account', 'ActionController@postFundDriverAccount');

	Route::get('/settings/vehicle-types', 'ActionController@getVehicleTypes');
	Route::get('/settings/vehicle-manufacturers', 'ActionController@getVehicleManufacturers');
	Route::get('/settings/vehicle-makers', 'ActionController@getVehicleMakers');
	Route::get('/settings/districts', 'ActionController@getDistricts');
	Route::get('/settings/cities', 'ActionController@getCities');
	Route::get('/settings/traffic-cost', 'ActionController@getTrafficCosts');
	Route::get('/settings/vehicle-traffic-cost', 'ActionController@getVehicleTrafficCosts');

	Route::get('/settings/add-new-vehicle-type', 'ActionController@getAddNewVehicleType');
	Route::get('/settings/add-new-vehicle-manufacturer', 'ActionController@getAddNewVehicleManufacturer');
	Route::get('/settings/add-new-vehicle-maker', 'ActionController@getAddNewVehicleMaker');
	Route::get('/settings/add-new-district', 'ActionController@getAddNewDistrict');
	Route::get('/settings/add-new-city', 'ActionController@getAddNewCity');
	Route::get('/settings/add-new-traffic-cost', 'ActionController@getAddNewTrafficCost');
	Route::get('/settings/add-new-vehicle-traffic-costs', 'ActionController@getNewVehicleTrafficCosts');

	Route::post('/settings/add-new-vehicle-type', 'ActionController@postAddNewVehicleType');
	Route::post('/settings/add-new-vehicle-manufacturer', 'ActionController@postAddNewVehicleManufacturer');
	Route::post('/settings/add-new-vehicle-maker', 'ActionController@postAddNewVehicleMaker');
	Route::post('/settings/add-new-district', 'ActionController@postAddNewDistrict');
	Route::post('/settings/add-new-city', 'ActionController@postAddNewCity');
	Route::post('/settings/add-new-traffic-cost', 'ActionController@postAddNewTrafficCost');
	Route::post('/settings/add-new-vehicle-traffic-cost', 'ActionController@postAddNewVehicleTrafficCost');
	Route::post('/manage-data-items', 'ActionController@manageDataItems');
});