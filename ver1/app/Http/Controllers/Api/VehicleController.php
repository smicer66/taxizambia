<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\PullAccountsByTokenRequest;
use App\Http\Controllers\Controller;
use JWTAuth;

class VehicleController extends Controller
{
    //
	private $jwtauth;
	var $getServiceBaseURL = "payments.probasepay.com";
	
	public function updateUserOneSignalId()
	{

	}

	public function getTest()
	{
		$d = '{"driverId":154,"locations":"[{\"d\":154,\"la\":-15.3765309,\"lo\":28.3128285},{\"d\":154,\"la\":-15.376589999999998,\"lo\":28.312841666666667},{\"d\":154,\"la\":-15.376483333333333,\"lo\":28.312821666666665},{\"d\":154,\"la\":-15.37649,\"lo\":28.312628333333336}]","Authorization":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjE1NCwiaXNzIjoiaHR0cHM6Ly90YXhpemFtYmlhLnByb2Jhc2VwYXkuY29tL2FwaS92MS9hdXRoL2xvZ2luIiwiaWF0IjoxNjI2MTgyMDY1LCJleHAiOjE2MjY3ODY4NjUsIm5iZiI6MTYyNjE4MjA2NSwianRpIjoidER2alZXZjc4RnpKYWVMYSJ9.CYqXDg8USnyxqF01WtMDPgUng3MTjOsQjPRE3mnSJoQ","test":1}';
		$d = json_decode($d);

		$recL[("2323")] = $d;
				$data1['recL'] = $recL;
				$data1['status'] = 1;
				$data1['messageType'] = 'DRIVER PICKUP';
				$data = json_encode($data1);
				$data = 'data='.urlencode($data);
dd(12);
				$url = "http://140.82.52.195:8080/post-driver-arrived-pickup";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
dd($server_output);
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		dd($user);

	}


    public function __construct(JWTAuth $jwtauth)
    {
        //$this->middleware('jwt.auth', ['except' => ['pulllist']]);  // or use 'only' in place of except
		/*$this->middleware('jwt.auth', ['except'=> ['getAvailableVehiclesBetweenPoints', 'getActiveDrivers', 'getSearchByAddress', 'getDriverDeal', 'getSearchPreviousAddress',
			'makeDriverDeal', 'getTripById', 'getTripGoingById', 'getDriverPosition', 'verifyProbaseWallet', 'getTrips', 'getTripsOfDriver', 'sendSupportMessage', 'getTransactionsOfDriver', 
			'getDealForDriver', 'getTripFromDeal', 'getPassenger', 'dropOffPassenger', 'acceptJob', 'setTripGoingById' , 'setArrivedForTripByTripId', 'getDriverDealStatus', 
			'rateTrip', 'cancelPassengerOpenDeals', 'payTripFeeUsingCard', 'payTripFeeUsingCardStepTwo', 'getTransactionByTripId', 'getTripsOfDriverForWallet'
			'updateAvailabilityForJob', 'widthDraw', 'updateVehiclePosition', 'updateVehiclePositionBatch', 'sendRequestForAPaymentCard']]);*/  // or use 'only' in place of except
		$this->middleware('jwt.auth', ['except'=> ['sendRequestForAPaymentCard', 'pullRegisterData', 'authenticateProbasePayWallet', 'getDriverDealByToken', 'getTest',
			'driverCancelTrip', 'authenticateProbasePayWalletWithOtp', 'checkIfMobileNumberExist', 'confirmPassengerCancelTrip']]);
        $this->jwtauth = $jwtauth;
    }
	
	
	
	public function pullRegisterData()
	{
		$vehicleTypes = \App\VehicleType::where('status', '=', 1)->get();//->pluck('name', 'id');
		$vehicleManufacturers = \App\VehicleManufacturer::where('status', '=', 1)->get();//pluck('name', 'id');
		$vehicleMakes = \App\VehicleMake::where('status', '=', 1)->get();//pluck('name', 'id');
		$cities = \App\City::where('status', '=', 1)->get();//pluck('name', 'id');
		$districts = \App\District::all();//pluck('name', 'id');
		return response()->json(['status'=>1, 'vehicleTypes'=>$vehicleTypes, 'vehicleManufacturers'=>$vehicleManufacturers, 
			'vehicleMakes'=>$vehicleMakes, 'cities'=>$cities, 'districts'=>$districts ]);
	}
	
	
	public function sendRequestForAPaymentCard(\App\Http\Requests\BaseTaxiRequest $request){
		$input = $request->all();
		if(isset($input['InputName']) && strlen(trim($input['InputName']))>0 && 
			isset($input['InputEmail']) && strlen(trim($input['InputEmail']))>0 && 
			isset($input['InputPhone']) && strlen(trim($input['InputPhone']))>0 && 
			isset($input['InputMessage']) && strlen(trim($input['InputMessage']))>0 && 
			isset($input['InputCardType']) && 
			isset($input['InputMessage']) && strlen(trim($input['InputMessage']))>0)
		{
			$cardRequest = new \App\CardRequest();
			$cardRequest->name = $input['InputName'];
			$cardRequest->email = $input['InputEmail'];
			$cardRequest->phone = $input['InputPhone'];
			$cardRequest->message = $input['InputMessage'];
			$cardRequest->card_type = $input['InputCardType'];
			$cardRequest->save();
			return \Redirect::to('/#contactform')->with('success', 'Your request for a new card has been sent. Our support staff will reach out to you');
		}
		else
		{
			return \Redirect::back()->with('fail', 'Your request for a new card was not successful. You need to fill the form fields before submitting');
		}
	}



	public function checkIfMobileNumberExist(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$all = $request->all();
		$mobileNumber = $all['mobileNumber'];
		$user = \App\User::where('mobileNumber', '=', $mobileNumber)->first();
		if($user!=null)
		{
			$arr_ = ['status'=>1, 'type'=>$user->role_code];
			return response()->json($arr_);
		}
		
		$otp = mt_rand(1000, 9999);

		$userSignUpRequest = \App\UserSignUpRequest::where('mobile_number', '=', $mobileNumber)->first();
		if($userSignUpRequest==null)
		{
			$userSignUpRequest= new \App\UserSignUpRequest();
		}
		$userSignUpRequest->mobile_number = $mobileNumber;
		$userSignUpRequest->otp = $otp;
		$userSignUpRequest->save();
		

		$msg = "Your Tweende sign-up one-time code is ".$otp;
		try
		{
			send_sms($mobileNumber, $msg, "Bevura");
		}
		catch(\Exception $e)
		{

		}
		return response()->json(['status'=>0, 'message'=>'We have sent you a one-time code. Please enter the code']);
	}

    public function getAvailableVehiclesBetweenPoints(\App\Http\Requests\BaseTaxiRequest $request)
    {
		$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
        
		$all = $request->all();
		$dataReq = new \App\DataRequest();
		$dataReq->data_request = json_encode($all);
		$dataReq->save();
		$accts = [];

		$vehicleTypes = \App\VehicleType::where('status', '=', 1)->get();
        try {
			
			$token = JWTAuth::getToken();

			//originLat:originLat, originLng:originLng, destinationLat:destinationLat, destinationLng:destinationLng
			
			$oLat 	= $request->has('originLat') ? $request->get('originLat') : null;
			$oLon 	= $request->has('originLng') ? $request->get('originLng') : null;
			$dLat 	= $request->has('destinationLat') ? $request->get('destinationLat') : null;
			$dLon 	= $request->has('destinationLng') ? $request->get('destinationLng') : null;
			$locality 	= $request->has('locality') ? $request->get('locality') : null;
			$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$oLat.",".$oLon."&key=".GOOGLE_MAP_KEY."&sensor=true";
			$resp1 = handleCurlGetRequest($url);
			$resp = json_decode($resp1);
			//dd($resp);
			
			$city = 'Lusaka';
			try{
				$len = sizeof($resp->results);
				$city = ($resp->results[($len - 2)]->address_components[0]->long_name);
				
			}
			catch(Exception $e)
			{
			
			}
			
			//Distance between taxi drivers and the passenger at the moment
			$sql = 'SELECT v1.id, v1.vehicle_type, distanceFromUser, v1.vehicle_driver_name, v1.vehicle_driver_photo, v1.vehicle_type_id FROM `vehicle_trackers` v1 INNER JOIN ((SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) '.
			'+ COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 '.
			'AS `distanceFromUser` FROM `vehicle_trackers` WHERE ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) + '.
			'COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 < 100 '.
			'ORDER BY `distanceFromUser`)) v2 ON v1.id = v2.id GROUP BY v1.vehicle_type';
			//dd($sql);
			$vehicles = \DB::select($sql);
			
			$time = 0;
			$distance = '0km';
			$distanceMeters = 0;
			
			
			
			//Distance between Passenger and destination
			if($dLat!=null && $dLon!=null)
			{
				$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$oLat.",".$oLon."&destination=".$dLat.",".$dLon."&key=".GOOGLE_MAP_KEY."&traffic_model=best_guess&departure_time=".time();//1541202457
				$resp = handleCurlGetRequest($url);
				$resp = json_decode($resp);
				if(isset($resp->status) && $resp->status=='OK')
				{
					$routes = isset($resp->routes) ? $resp->routes : null;
					$time = $routes[0]->legs[0]->duration_in_traffic->value;
					$distance = $routes[0]->legs[0]->distance->text;
					$distanceMeters = $routes[0]->legs[0]->distance->value;
				}
			}
			
			
			
			
            /*SELECT v4.*, v3.* from `vehicle_traffic_costs` v3, 
			`traffic_costs` v4 WHERE v3.traffic_cost_id = v4.id AND 
			v3.vehicle_type = 'Sedan' AND v4.district_name = 'Zambia' AND v3.status = 'Active'*/
			
            if(sizeof($vehicles)>0)
            {
				
				$i=0;
				$vehicleTypeKeys = array_keys($vehicleIcons);
				
                foreach ($vehicles as $vehicle) {
					$sql = "SELECT v4.*, v3.*, v5.icon, v5.base_fare_for_vehicle_type from `vehicle_traffic_costs` v3, `traffic_costs` v4, `vehicle_types` v5 WHERE ".
						"v3.traffic_cost_id = v4.id AND v3.vehicle_type_id = '".$vehicle->vehicle_type_id."' AND v3.vehicle_type_id = v5.id ".
						//"AND v4.district_name = '".$city."' ".
						"AND v3.status = 'Active'";
					//dd($sql);
					$tpCosts = \DB::select($sql);
					
					//dd($tpCosts);
					
					$total_cost = 0;
					
					
					
					if($tpCosts!=null && sizeof($tpCosts)>0)
					{
						$baseRate = $tpCosts[0]->base_fare_for_vehicle_type;	//based on city
						$ratePerSecond = $tpCosts[0]->chargePerSecond;	//based on time
						$ratePerMeter = $tpCosts[0]->chargePerMeter;
						$currentDemandIndex = 0;
						$cancellationFee = $tpCosts[0]->cancellationFee;
						$minimumFare = $tpCosts[0]->minimumFare;
						//$amtToPay = ($baseRate + $currentDemandIndex) + ($time*$ratePerSecond);
						//$cost = ($amtToPay  < $minimumFare) ? $minimumFare : $amtToPay;
						$amtToPay =  ($baseRate + $currentDemandIndex) + ($distanceMeters*$ratePerMeter);
						$cost = ($amtToPay  < $minimumFare) ? $minimumFare : $amtToPay;
						//$vehicleTypeIcon = in_array($vehicle->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$vehicle->vehicle_type] : $vehicleIcons['Taxi'];
						$vehicleTypeIcon = $tpCosts[0]->icon;
						$photoURL = "http://192.168.43.136/users/".$vehicle->vehicle_driver_photo;
						$accts[$i++] = ['id'=>$vehicle->id, 'photoURL'=>$photoURL, 'name'=>$vehicle->vehicle_driver_name, 'distanceFromUser'=>$vehicle->distanceFromUser, 'vehicleType'=>$vehicle->vehicle_type, 'vehicleTypeId'=> $tpCosts[0]->vehicle_type_id, 'fee'=>$cost, 'icon'=>$vehicleTypeIcon];
						
						
					}
                }
				
				
				$arr_ = ['status'=>1, 'vehicleTypes' => $vehicleTypes, 'vehicles' => $accts, 'distance'=>$distance, 'currency' => 'ZMW', 'locality'=>$city];
				//$fresp[$locality] = $arr_;
                //return response()->json($fresp);
				return response()->json($arr_);
            }else{
                return response()->json(['err' => 'No airlines were found', 'vehicleTypes' => $vehicleTypes, 'status'=>0], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
		catch(Exception $e)
        {
            $arr_ = ['status'=>0, 'vehicles' => $accts, 'vehicleTypes' => $vehicleTypes, 'distance'=>$distance, 'currency' => 'ZMW', 'locality'=>''];
			//$fresp[$locality] = $arr_;
			//return response()->json($fresp);
			return response()->json($arr_);
        }
    }

    /***getActiveDrivers***/
    public function getActiveDriversOld(\App\Http\Requests\BaseTaxiRequest $request) {
        
		/*getGoingTripById$jk = new \App\Junk();
		$jk->data="get active drivers - ".json_encode($request->all());
		$jk->save();*/

		$locality 	= $request->has('locality') ? $request->get('locality') : null;
		$oLat 	= $request->has('originLat') ? $request->get('originLat') : null;
		$oLon 	= $request->has('originLng') ? $request->get('originLng') : null;
		$vehicleType 	= $request->has('vehicleType') ? $request->get('vehicleType') : null;
		
        try {
			$token = JWTAuth::getToken();
			$sql = 'SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) '.
				'+ COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 '.
				'AS `distanceFromUser` FROM `vehicle_trackers` WHERE vehicle_type_id = "'.$vehicleType.'" AND ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) + '.
				'COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 < 10 '.
				'ORDER BY `distanceFromUser`';
			$vehicles = \DB::select($sql);
			$accts = [];
			$distance = '0km';
			
			if(sizeof($vehicles)>0)
            {
				$i=0;
                foreach ($vehicles as $vehicle) 
				{
					$photoURL = "http://192.168.43.136/users/".$vehicle->vehicle_driver_photo;
					$accts[$i++] = ['id'=>$vehicle->id, 'distanceFromUser'=>$vehicle->distanceFromUser, 
						'vehicleType'=>$vehicle->vehicle_type, 'vehicleTypeId'=>$vehicle->vehicle_type_id, 'lat'=>$vehicle->current_latitude, 'lng'=>$vehicle->current_longitude, 
						'oldLat'=>$vehicle->old_latitude, 'oldLng'=>$vehicle->old_longitude, 'photoURL'=>$photoURL, 'name'=>$vehicle->vehicle_driver_name];
                }
				//$arr_ = ['vehicles' => $accts, 'distance'=>$distance, 'currency' => 'ZMW'];
				//$fresp[$locality] = $arr_;
                //return response()->json($fresp);
				return response()->json($accts);
            }else{
                return response()->json(['err' => 'No airlines were found'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
    }
	
	
	public function getActiveDrivers(\App\Http\Requests\BaseTaxiRequest $request) {
        

		
		$locality 	= $request->has('locality') ? $request->get('locality') : null;
		$oLat 	= $request->has('originLat') ? $request->get('originLat') : null;
		$oLon 	= $request->has('originLng') ? $request->get('originLng') : null;
		$vehicleType 	= $request->has('vehicleType') ? $request->get('vehicleType') : null;
		
        try {
			$token = JWTAuth::getToken();
			$sql = 'SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) '.
				'+ COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 '.
				'AS `distanceFromUser` FROM `vehicle_trackers` WHERE vehicle_type_id = "'.$vehicleType.'" AND status = "Available" AND ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) + '.
				'COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 < 100 '.
				'ORDER BY `distanceFromUser`';
			$vehicles = \DB::select($sql);
			$accts = [];
			$distance = '0km';


        	$jk = new \App\Junk();
		$jk->data="get active drivers - ".json_encode($request->all());
		$jk->save();

        	$jk = new \App\Junk();
		$jk->data=$sql;
		$jk->save();/**/

			
			if(sizeof($vehicles)>0)
            {
				$i=0;
                foreach ($vehicles as $vehicle) 
				{
				    $photoURL = "";
				    $photoURLNull = "";
				    if($vehicle->vehicle_driver_photo!=null)
				    {
					    $photoURL = "http://192.168.43.136/users/".$vehicle->vehicle_driver_photo;
					    $photoURLNull = 0;
				    }
					else
					{
					    $photoURL = "http://192.168.43.136/users/__ydtam.jpg";
					    $photoURLNull = 1;
					}
					    
					$accts[$i++] = ['id'=>$vehicle->id, 'distanceFromUser'=>$vehicle->distanceFromUser, 
						'vehicleType'=>$vehicle->vehicle_type, 'lat'=>$vehicle->current_latitude, 'lng'=>$vehicle->current_longitude, 
						'oldLat'=>$vehicle->old_latitude, 'oldLng'=>$vehicle->old_longitude, 'photoURL'=>$photoURL, 'photoURLNull'=>$photoURLNull, 'name'=>$vehicle->vehicle_driver_name];
                }
				//$arr_ = ['vehicles' => $accts, 'distance'=>$distance, 'currency' => 'ZMW'];
				//$fresp[$locality] = $arr_;
                //return response()->json($fresp);
                $vhList['status'] =1;
                $vhList['listing'] = $accts;
				return response()->json($vhList);
            }else{
                return response()->json(['err' => 'No drivers were found', 'status' => 500, 'sql'=>$sql]);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
            
        }
    }


	/***getSearchByAddress***/
	public function getSearchByAddress(\App\Http\Requests\BaseTaxiRequest $request) 	
	{
		
		$keyword 	= $request->has('keyword') ? $request->get('keyword') : '';
		$oLat 	= $request->has('lat') ? $request->get('lat') : null;
		$oLon 	= $request->has('lng') ? $request->get('lng') : null;

        try {
            $token = JWTAuth::getToken();
			$url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=".GOOGLE_MAP_KEY."&keyword=".$keyword."&location=".$oLat.",".$oLon."&radius=50000";

			$resp1 = handleCurlGetRequest($url);
			$resp = json_decode($resp1);
			
			return response()->json($resp);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	/***getDriverDeals***/
	public function getDriverDeal(\App\Http\Requests\BaseTaxiRequest $request) 
	{
		
		
        try {
            $token = JWTAuth::getToken();
			$vehicleId 	= $request->has('vehicleId') ? $request->get('vehicleId') : null;
			$bookId 	= $request->has('bookId') ? $request->get('bookId') : null;
			$user = JWTAuth::toUser($token);
			//$user = \App\User::where('id', '=', 4)->first();
			
			/*$sql = 'SELECT *, v2.status as status FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE v1.id = '.$vehicleId.' AND 
				v1.vehicle_id = v2.vehicle_id AND v2.passenger_user_id = '.$user->id.' AND v2.booking_group_id = "'.$bookId.'" AND 
				DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by v2.created_at DESC';*/
				
			
			$sql = 'SELECT *, v2.status as status, v3.vehicle_type FROM `vehicle_trackers` v1,`driver_deals` v2,`vehicles` v3 WHERE v2.passenger_user_id = '.$user->id.' AND v2.status IN ("Accepted", "Pending", "Driver Canceled", "Going") AND 
				v2.id = "'.$bookId.'" AND v1.vehicle_id = v2.vehicle_id AND v2.vehicle_id = v3.id AND 
				DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by v2.created_at DESC';
				
			
			$deals = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0, 'deals'=>[], 'sql'=>$sql];
			$dilGoing = null;
			$dilAccept = null;
			$dilPending = null;
			$dilCancel = null;
			
			if(sizeof($deals)>0)
			{
				foreach($deals as $dil)
				{
					if($dil->status=='Going')
					{
						$dilGoing = $dil;
					}
					else if($dil->status=='Accepted')
					{
						$dilAccept = $dil;
					}
					else if($dil->status=='Pending')
					{
						$dilPending = $dil;
					}
					else if($dil->status=='Driver Canceled')
					{
						$dilCancel = $dil;
					}
				}
				
				if($dilGoing!=null)
				{
					$dlr = $dilGoing;
				}
				else if($dilAccept!=null)
				{
					$dlr = $dilAccept;
				}
				else if($dilPending!=null)
				{
					$dlr = $dilPending;
				}
				else if($dilCancel!=null)
				{
					$dlr = $dilCancel;
				}
				
				$dl['deal_status'] = $dlr->status;
				//if($deals[0]->status=='Accepted' || $deals[0]->status=='Pending' || $deals[0]->status=='Driver Canceled')
				//{
					$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
					$vehicleTypeKeys = array_keys($vehicleIcons);
					$vehicleTypeIcon = in_array($dlr->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$dlr->vehicle_type] : $vehicleIcons['Taxi'];
					$dl= ['tripId'=>$dlr->trip_id, 'deal_status'=>$dlr->status, 'icon'=>$vehicleTypeIcon, 'fee'=>$dlr->fee, 'currency'=>'ZMW'];
					$accts = ['status' =>1, 'deals'=>$dl];
				//}
				
			}
			
			return response()->json($accts);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422, 'e'=>$e]);
        }
	}
	
	
	
	/***makeDriverDeal***/
	public function makeDriverDeal(\App\Http\Requests\BaseTaxiRequest $request) 
	{
		
		//originVicinity:origin.vicinity, destinationVicinity
        try {
			
			$currency 	= $request->has('currency') ? $request->get('currency') : null;
			$originLat 	= $request->has('originLat') ? $request->get('originLat') : null;
			$originLng 	= $request->has('originLng') ? $request->get('originLng') : null;
			$origin_vicinity 	= $request->has('originVicinity') ? $request->get('originVicinity') : null;
			$dest_vicinity 	= $request->has('destinationVicinity') ? $request->get('destinationVicinity') : null;
			$destinationLat 	= $request->has('destinationLat') ? $request->get('destinationLat') : null;
			$destinationLng 	= $request->has('destinationLng') ? $request->get('destinationLng') : null;
			$distance 	= $request->has('distance') ? $request->get('distance') : null;
			$fee 	= $request->has('fee') ? $request->get('fee') : null;
			$note 	= $request->has('note') ? $request->get('note') : null;
			$paymentMethod 	= $request->has('paymentMethod') ? $request->get('paymentMethod') : null;
			$vehicleTrackerId 	= $request->has('vehicleTrackerId') ? $request->get('vehicleTrackerId') : null;
			$vehicleId 	= $request->has('vehicleId') ? $request->get('vehicleId') : null;
			$bookId = $request->has('bookId') ? $request->get('bookId') : null;
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			//$user = \App\User::where('id', '=', 4)->first();   
			$vehicle_tracker = \App\VehicleTracker::where('id', '=', $vehicleId)->first();
			
			if($vehicle_tracker!=null)
			{
				/*$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$originLat.",".$originLng."&key=".GOOGLE_MAP_KEY."&sensor=true";
				$resp1 = handleCurlGetRequest($url);
				$resp = json_decode($resp1);
				try{
					$len = sizeof($resp->results);
					//$origin_vicinity = ($resp->results[($len - 2)]->address_components[0]->long_name);
					$origin_vicinity = ($resp->results[($len - 2)]->formatted_address);
					
					$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$destinationLat.",".$destinationLng."&key=".GOOGLE_MAP_KEY."&result_type=route&sensor=true";
					$resp1 = handleCurlGetRequest($url);
					$resp = json_decode($resp1);
					try{
						$len = sizeof($resp->results);
						//$dest_vicinity = ($resp->results[($len - 2)]->address_components[0]->long_name);
						//$dest_vicinity = ($resp->results[($len - 2)]->formatted_address);
						if($resp->results>1)
							$dest_vicinity = ($resp->results[(1)]->formatted_address);
						if($resp->results>0)
							$dest_vicinity = ($resp->results[(0)]->formatted_address);
					}
					catch(Exception $e)
					{
						
					}
				}
				catch(Exception $e)
				{
				
				}*/
				date_default_timezone_set('Africa/Lusaka');


				$vehicleType = \App\VehicleType::where('id', '=', $vehicle_tracker->vehicle_type_id)->first();

				$tripRequest = new \App\TripRequest();
				$tripRequest->passenger_user_id = $user->id;
				$tripRequest->status = 'Pending';
				$tripRequest->save();


				

				$driverDeal = new \App\DriverDeal();
				$driverDeal->vehicle_id = $vehicle_tracker->vehicle_id;
				$driverDeal->vehicle_icon = $vehicleType->icon;
				$driverDeal->vehicle_type_name = $vehicleType->name;
				$driverDeal->passenger_user_id = $user->id;
				$driverDeal->passenger_user_full_name = $user->name;
				$driverDeal->driver_user_id = $vehicle_tracker->vehicle_driver_user_id;
				$driverDeal->origin_locality = $origin_vicinity;
				$driverDeal->destination_locality = $dest_vicinity;
				$driverDeal->origin_longitude = $originLng;
				$driverDeal->origin_latitude = $originLat;
				$driverDeal->destination_latitude = $destinationLat;
				$driverDeal->destination_longitude = $destinationLng;
				$driverDeal->distance = $distance;
				$driverDeal->fee = $fee;
				$driverDeal->booking_group_id = $bookId;
				$driverDeal->note = ((is_array($note) && isset($note['note']) && strlen($note['note'])>0) ? $note['note'] : ($note!=null && strlen($note)>0 ? $note : null));
				$driverDeal->payment_method = $paymentMethod;
				$driverDeal->status = 'Pending';
				$driverDeal->trip_request_id = $tripRequest->id;
				$driverDeal->phone_number = $user->mobileNumber;
				$driverDeal->save();
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			
			return response()->json(['status'=>1]);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	
	public function sendDriverDeals(\App\Http\Requests\BaseTaxiRequest $request) 
	{
		$jk = new \App\Junk();
		$jk->data="sendDriverDeals ... ".json_encode($request->all());
		$jk->save();
		//originVicinity:origin.vicinity, destinationVicinity
        try {
			
			$originLat 	= $request->has('originLat') ? $request->get('originLat') : null;
			$originLng 	= $request->has('originLng') ? $request->get('originLng') : null;
			$origin_vicinity 	= $request->has('originVicinity') ? $request->get('originVicinity') : null;
			$dest_vicinity 	= $request->has('destinationVicinity') ? $request->get('destinationVicinity') : null;
			$destinationLat 	= $request->has('destinationLat') ? $request->get('destinationLat') : null;
			$destinationLng 	= $request->has('destinationLng') ? $request->get('destinationLng') : null;
			//$distance 	= $request->has('distance') ? $request->get('distance') : null;
			$fee 	= $request->has('fee') ? $request->get('fee') : null;
			$note 	= $request->has('note') ? $request->get('note') : null;
			$paymentMethod 	= $request->has('paymentMethod') ? $request->get('paymentMethod') : 'Cash';
			
			//$vehicleTrackerId 	= $request->has('vehicleTrackerId') ? $request->get('vehicleTrackerId') : null;
			//$vehicleId 	= $request->has('vehicleId') ? $request->get('vehicleId') : null;
			$bookId = $request->has('randomStringCode') ? $request->get('randomStringCode') : null;
			$vehicleType = $request->has('vehicleType') ? $request->get('vehicleType') : null;
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			//$user = \App\User::where('id', '=', 4)->first();   
			
			
			$token = JWTAuth::getToken();
			
			
			date_default_timezone_set('Africa/Lusaka');
			$now = date_create(date('Y-m-d H:i'));
			$now = date_sub($now,date_interval_create_from_date_string("10 minutes"));
			$nowMinus10= date_format($now,"Y-m-d H:i");
			
			$vehiclesAvailable = \App\Vehicle::where('status', '=', 'Available');
			if($vehiclesAvailable->count()>0)
			{
				$allIds = [];
				$vehiclesAvailable = $vehiclesAvailable->get();
				foreach($vehiclesAvailable as $va)
				{
					array_push($allIds, $va->id);
				}
				$vehiclesAvailable = join(',', $allIds);
				$vehiclesAvailable = ' vehicle_id IN ('.$vehiclesAvailable.') AND ';
			}
			else
			{
				$vehiclesAvailable = '';
				return response()->json(['status'=>0,'message'=>'No vehiles']);
			}
			$sql = 'SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$originLat.' ) ) '.
				'+ COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$originLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$originLng.' )) ) * 6380 '.
				'AS `distanceFromUser` FROM `vehicle_trackers` WHERE '.$vehiclesAvailable.' updated_at > "'.$nowMinus10.'" AND vehicle_type = "'.$vehicleType.'" AND status = "Available" AND ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$originLat.' ) ) + '.
				'COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$originLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$originLng.' )) ) * 6380 < 100 '.
				'ORDER BY `distanceFromUser`';
			$vehicles = \DB::select($sql);

			$jk = new \App\Junk();
			$jk->data=$sql;
			$jk->save();

			
			$x1 = 0;

			$tripRequest  = null;
			if(sizeof($vehicles)>0)
			{
				$tripRequest = new \App\TripRequest();
				$tripRequest->passenger_user_id = $user->id;
				$tripRequest->status = 'Pending';
				$tripRequest->save();
			}

			$activePassengerDealId = null;
			$driversToNotify = [];


						$travelTime = 0;
						$travelDistance = 0;
						if($originLng!=null && $originLat!=null && $destinationLat!=null && $destinationLng!=null)
						{
							$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$originLat.",".$originLng."&destination=".$destinationLat.",".$destinationLng."&key=".GOOGLE_MAP_KEY."&traffic_model=best_guess&departure_time=".time();//1541202457
							$resp = handleCurlGetRequest($url);

						$jk = new \App\Junk();
						$jk->data='error'.$resp;
						$jk->save();
							$resp = json_decode($resp);
							if(isset($resp->status) && $resp->status=='OK')
							{


								$routes = isset($resp->routes) ? $resp->routes : null;
								$travelTime = $routes[0]->legs[0]->duration_in_traffic->text;
								$distance = $routes[0]->legs[0]->distance->text;
								$travelDistance = $routes[0]->legs[0]->distance->value;
							}
						}

			foreach($vehicles as $vehicle)
			{
				$driverUser = \App\User::where('id', '=',  $vehicle->vehicle_driver_user_id)->first();

				if($driverUser!=null && $driverUser->outstanding_balance > $fee)
				{ 
					$vehicle_tracker = \App\VehicleTracker::where('id', '=', $vehicle->id)->first();
					
					if($vehicle_tracker!=null)
					{
						/*$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$originLat.",".$originLng."&key=".GOOGLE_MAP_KEY."&sensor=true";
						$resp1 = handleCurlGetRequest($url);
						$resp = json_decode($resp1);
						try{
							$len = sizeof($resp->results);
							//$origin_vicinity = ($resp->results[($len - 2)]->address_components[0]->long_name);
							$origin_vicinity = ($resp->results[($len - 2)]->formatted_address);
							
							$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$destinationLat.",".$destinationLng."&key=".GOOGLE_MAP_KEY."&result_type=route&sensor=true";
							$resp1 = handleCurlGetRequest($url);
							$resp = json_decode($resp1);
							try{
								$len = sizeof($resp->results);
								//$dest_vicinity = ($resp->results[($len - 2)]->address_components[0]->long_name);
								//$dest_vicinity = ($resp->results[($len - 2)]->formatted_address);
								if($resp->results>1)
									$dest_vicinity = ($resp->results[(1)]->formatted_address);
								if($resp->results>0)
									$dest_vicinity = ($resp->results[(0)]->formatted_address);
							}
							catch(Exception $e)
							{
								
							}
						}
						catch(Exception $e)
						{
						
						}*/

						


						$vehicleType = \App\VehicleType::where('id', '=', $vehicle_tracker->vehicle_type_id)->first();


						$driverDeal = new \App\DriverDeal();
						$driverDeal->vehicle_id = $vehicle_tracker->vehicle_id;
						$driverDeal->vehicle_icon = $vehicleType->icon;
						$driverDeal->vehicle_type_name = $vehicleType->name;
						$driverDeal->passenger_user_id = $user->id;
						$driverDeal->passenger_user_full_name = $user->name;
						$driverDeal->driver_user_id = $vehicle_tracker->vehicle_driver_user_id;
						$driverDeal->origin_locality = $origin_vicinity;
						$driverDeal->destination_locality = $dest_vicinity;
						$driverDeal->origin_longitude = $originLng;
						$driverDeal->origin_latitude = $originLat;
						$driverDeal->destination_latitude = $destinationLat;
						$driverDeal->destination_longitude = $destinationLng;
						$driverDeal->distance = $vehicle->distanceFromUser;
						$driverDeal->fee = $fee;
						$driverDeal->booking_group_id = $bookId;
						$driverDeal->note = ((is_array($note) && isset($note['note']) && strlen($note['note'])>0) ? $note['note'] : ($note!=null && strlen($note)>0 ? $note : null));
						$driverDeal->payment_method = $paymentMethod;
						$driverDeal->status = 'Pending';
						$driverDeal->travel_time = $travelTime;
						$driverDeal->travel_distance = $travelDistance;
						$driverDeal->trip_request_id = $tripRequest!=null ? $tripRequest->id : null;
						$driverDeal->phone_number = $user->mobileNumber;
						$driverDeal->save();
						array_push($driversToNotify, $vehicle_tracker->vehicle_driver_user_id);

						
						$activePassengerDealId = $driverDeal->id;
						$x1++;
					}
				}
				else
				{
					$jk = new \App\Junk();
					$jk->data=json_encode([$driverUser->outstanding_balance, $fee]);
					$jk->save();
				}
			}
			


			if($x1>0)
			{
				$data1 = [];
				$data1['recL'] = $driversToNotify;
				$data1['status'] = 1;
				$data1['messageType'] = 'DRIVER DEAL';
				$data = json_encode($data1);
				$data = 'data='.urlencode($data);
				$url = "http://140.82.52.195:8080/post-new-trip-request";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				

				$jk = new \App\Junk();
				$jk->data=$data;
				$jk->save();

				//dd($server_output);


				$jk = new \App\Junk();
				$jk->data=json_encode(['status'=>1, 'sq' => $sql, 'activePassengerDealId'=>$activePassengerDealId]);
				$jk->save();
				return response()->json(['status'=>1, 'sq' => $sql, 'activePassengerDealId'=>$activePassengerDealId]);

			}
			else
			{
				//$driversToNotify[0] = 1;
				//$driversToNotify[1] = 2;
				$data1 = [];
				$data1['recL'] = $driversToNotify;
				$data1['status'] = 0;
				$data1['messageType'] = 'DRIVER DEAL';
				$data = json_encode($data1);

				$data = json_encode($driversToNotify);
				//dd($data);
				$data = 'data='.urlencode($data);
				$url = "http://140.82.52.195:8080/post-new-trip-request";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				

				$jk = new \App\Junk();
				$jk->data=$data;
				$jk->save();

				//dd($server_output);



				$jk = new \App\Junk();
				$jk->data=json_encode(['status'=>0, 'sq' => $sql]);
				$jk->save();
				return response()->json(['status'=>0, 'sq' => $sql]);
			}

        }catch(TokenExpiredException $e)
        {
					$jk = new \App\Junk();
					$jk->data='error';
					$jk->save();
            return response()->json(['status'=>422]);
        }
	 catch(\Exception $e)
        {
					$jk = new \App\Junk();
					$jk->data='error'.$e->getMessage();
					$jk->save();
            return response()->json(['status'=>0, 'sq' => $sql]);
        }
	}
	
	
	/***removeDriverDeal***/
	public function removeDriverDeal(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
			$token = JWTAuth::getToken();
			$dealId 	= $request->has('dealId') ? $request->get('dealId') : null;
			$isTimedout   = $request->has('isTimedout') ? $request->get('isTimedout') : null;
			$user = JWTAuth::toUser($token);
			
			$driverDeal = \App\DriverDeal::where('id', '=', $dealId)->where('status', '=', 'Pending')->first();
			$driversToNotify = [];	
			if($driverDeal!=null)
			{
				$driverDeals = \App\DriverDeal::where('id', '=', $driverDeal->id)->where('status', '=', 'Pending')->get();
				foreach($driverDeals as $dd)
				{
					$dd->status = $isTimedout==1 ? 'Timed Out' : 'Passenger Canceled';
					$dd->save();
					$driversToNotify[$dd->driver_user_id] = $dd->id;
				}


				$data1 = [];
				$data1['recL'] = $driversToNotify;
				$data1['status'] = 1;
				$data1['messageType'] = 'CANCEL DRIVER DEAL';
				$data = json_encode($data1);
				$data = 'data='.urlencode($data);
				$url = "http://140.82.52.195:8080/post-cancel-trip-request";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				

				$jk = new \App\Junk();
				$jk->data=$data;
				$jk->save();

				return response()->json(['status'=>1, 'message'=>'Your trip request has been canceled successfully']);

			}
			else
			{
				return response()->json(['status'=>0]);
			}
			
			return response()->json(['status'=>0]);


        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	

	
	public function driverCancelTrip(\App\Http\Requests\BaseTaxiRequest $request)
	{
        	try {
			$token = JWTAuth::getToken();
			$cancelId 	= $request->has('cancelId') ? $request->get('cancelId') : null;
			$tripId	= $request->has('tripId') ? $request->get('tripId') : null;
			$latitude	= $request->has('latitude') ? $request->get('latitude') : null;
			$longitude	= $request->has('longitude') ? $request->get('longitude') : null;

			$user = JWTAuth::toUser($token);
			
			$trip = \App\Trip::where('id', '=', $tripId)->whereIn('status', ['Pending','Going'])->where('vehicle_driver_user_id', '=', $user->id)->first();
			
			if($trip!=null)
			{
				$driverDeal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();


	
				$tripCancelation = new \App\TripCancelation();
				$tripCancelation->trip_id = $tripId;
				$tripCancelation->cancelation_reason_id = $cancelId==-1 ? null : $cancelId;
				$tripCancelation->user_id = $user->id;
				$tripCancelation->deal_id = $driverDeal->id;
				$tripCancelation->cancel_latitude = $latitude;
				$tripCancelation->cancel_longitude = $longitude;
				$tripCancelation->save();

				$trip->status = 'Driver Canceled';
				$trip->save();

				$driverDeal->status = 'Driver Canceled';
				$driverDeal->save();

				$vehicle = \App\Vehicle::where('id', '=', $trip->vehicle_id)->first();
				$vehicle->status = 'Available';
				$vehicle->save();
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			
			return response()->json(['status'=>1]);

        	}catch(TokenExpiredException $e)
        	{
           		return response()->json(['status'=>422]);
        	}
	}
	
	
	/***getTripById***/
	public function getTripById(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
            $token = JWTAuth::getToken();
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$user = JWTAuth::toUser($token);
			
			$trip = \App\Trip::where('id', '=', $tripId)->first();
			$deal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();
			
			
			if($trip!=null)
			{
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
				$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
				$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
				/*
				$origin = ['vicinity'=>$trip->origin_vicinity];
				$destination = ['vicinity'=>$trip->destination_vicinity];
				$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
					'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
					'destination'=> $destination, 'dealId'=>$trip->driver_deal_id];*/
					
					
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
				
				$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
					'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived, 'travel_time'=>$trip->travel_time,  'passengerName'=>$trip->passenger_user_name, 
					'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
					'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
					'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
					'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 
					'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'previousPassengerRating'=>$trip->previous_passenger_rating, 
					'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
					'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
					'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id];
				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'tripData'=>$trip]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	
	/***getTripGoingById***/
	public function getTripGoingById(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
		$vehicleTypeKeys = array_keys($vehicleIcons);
		
        try {
			$token = JWTAuth::getToken();
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$user = JWTAuth::toUser($token);
			
			$trip = \App\Trip::where('id', '=', $tripId)->first();
			
			
			if($trip!=null)
			{
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$locationDes=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];

				$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
				$destination = ['vicinity'=>$trip->destination_vicinity, 'location'=>$locationDes];
				$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
					'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin,
					'destination'=> $destination, 'status'=> $trip->status, 'id'=>$trip->id, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];

				$fare = $trip->amount_chargeable + $trip->extra_charges;
				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'tripData'=>$trip, 'fare'=>$fare]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	/***getTripGoingById***/
	public function setTripGoingById(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try {
            $token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$driverId 	= $request->has('driverId') ? $request->get('driverId') : null;
			
			//$trip = \App\Trip::where('id', '=', $tripId)->where('vehicle_driver_user_id', '=', $driverId)->first();
			$trip = \DB::table('trips')
				->join('driver_deals', 'trips.driver_deal_id', '=', 'driver_deals.id')->where('trips.vehicle_driver_user_id', '=', $driverId)
				->whereNotIn('trips.status', ['Completed', 'Driver Canceled', 'Passenger Canceled', 'Completed', 'Completed & Paid', 'Admin Canceled'])->orderBy('trips.created_at', 'DESC')
				->select('trips.*', 'driver_deals.vehicle_id', 'driver_deals.passenger_user_id', 'driver_deals.driver_user_id', 'driver_deals.trip_id'
				, 'driver_deals.origin_locality', 'driver_deals.origin_longitude', 'driver_deals.origin_latitude', 'driver_deals.destination_longitude'
				, 'driver_deals.destination_latitude', 'driver_deals.distance', 'driver_deals.fee', 'driver_deals.note'
				, 'driver_deals.payment_method', 'driver_deals.status as dealStatus', 'driver_deals.destination_locality', 'driver_deals.passenger_user_full_name'
				, 'driver_deals.booking_group_id')->first();
				
			
			
			if($trip!=null)
			{
				if($trip->status=='Pending')
				{
					date_default_timezone_set('Africa/Lusaka');
					$driver_deals = \App\DriverDeal::where('trip_id', '=', $trip->id)->first();
					$driver_deals->status = 'Going';
					$driver_deals->save();
					
					
					$trp = \App\Trip::where('id', '=', $trip->id)->first();
					$trp->pickedUpAt = date('Y-m-d H:i');
					$trp->status = 'Going';
					$trp->save();
				}
				
				$pkdUpAt = "";
				$pkdUpAt1 = "";
				if($trip->status=="Pending")
				{
					$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
					$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
				}
				else if($trip->status=="Going")
				{
					$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
					$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
				}
				else if($trip->status=="Passenger Canceled")
				{
					$pkdUpAt = "You canceled trip";
					$pkdUpAt1 = "You canceled trip";
				}
				else if($trip->status=="Driver Canceled")
				{
					$pkdUpAt = "Drivers canceled trip";
					$pkdUpAt1 = "Drivers canceled trip";
				}
				else if($trip->status=="Completed")
				{
					$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
					if($trip->paidYes==1)
					{
						$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						if($trip->payment_method=='Card')
						{
							$total_balance = $total_balance + $trip->amount_chargeable;
						}
					}
					
				}
				
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
				$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
				$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
					
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
				
				$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
					'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived, 'travel_time'=>$trip->travel_time, 
					'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
					'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
					'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
					'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$trip->dealStatus, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 'previousPassengerRating'=>$trip->previous_passenger_rating, 
					'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'is_arrived'=>$trip->is_arrived,
					'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes,  'passengerName'=>$trip->passenger_user_name, 
					'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
					'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id];
					
				
				
				
				$dealId = $trip->driver_deal_id;
				$sql = 'SELECT *, v2.status as status, v2.id as driverDealId, v1.id as vehicleTrackerId FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE 
					v1.vehicle_id = v2.vehicle_id AND v2.driver_user_id = '.$trip->vehicle_driver_user_id;
				if($dealId!=null)
				{
					$sql = $sql.' AND v2.id = '.$dealId;
				}
				$sql = $sql.' AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Completed", "Accepted", "Going"))) ORDER BY v2.id DESC LIMIT 0, 1';
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				
				$deals = \DB::select($sql);
				$dl = null;
				if(sizeof($deals)>0)
				{
					$dl = [];
					$vehicleTypeIcon = in_array($deals[0]->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$deals[0]->vehicle_type] : $vehicleIcons['Taxi'];
					$originLocation = ['lng'=>floatval($deals[0]->origin_longitude), 'lat'=>floatval($deals[0]->origin_latitude)];
					$destLocation = ['lng'=>floatval($deals[0]->destination_longitude), 'lat'=>floatval($deals[0]->destination_latitude)];
					$origin=['location'=>$originLocation, 'vicinity'=>$deals[0]->origin_locality];
					$destination=['location'=>$destLocation, 'vicinity'=>$deals[0]->destination_locality];
					$dl = ['tripId'=> $deals[0]->trip_id, 'status'=>$deals[0]->status, 'createdAt'=>strtotime($deals[0]->created_at), 'is_arrived'=>$deals[0]->is_arrived,
						'origin'=>$origin, 'destination'=>$destination, 'driverDealId'=>$deals[0]->driverDealId, 'id'=>$deals[0]->driverDealId, 
						'vehicleTrackerId'=>$deals[0]->vehicleTrackerId, 'deal_status'=>$deals[0]->status, 'icon'=>$vehicleTypeIcon, 'fee'=>$deals[0]->fee, 'currency'=>'ZMW'];
				}		



				$data1 = [];
				$recL = [];
				$checkTrip = \App\Trip::where('id', '=', $trip->id)->first();
				$location=['lat'=>$checkTrip->origin_latitude, 'lng'=>$checkTrip->origin_longitude];
				$locationDes=['lat'=>$checkTrip->destination_latitude, 'lng'=>$checkTrip->destination_longitude];
				$origin = ['vicinity'=>$checkTrip->origin_vicinity, 'location'=>$location];
				$destination = ['vicinity'=>$checkTrip->destination_vicinity, 'location'=>$locationDes];


				$trip1 = ['driverId' => $checkTrip->vehicle_driver_user_id, 'identifier'=>$checkTrip->trip_identifier,
					'currency'=> $checkTrip->currency, 'fee'=> $checkTrip->amount_chargeable, 'origin'=> $origin, 
					'destination'=> $destination, 'status'=> $checkTrip->status, 'id'=>$checkTrip->id, 
					'driver_starting_location_latitude'=>$checkTrip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$checkTrip->driver_starting_location_longitude];
					
				
				$vehicleTypeIcon = $checkTrip->vehicle_icon;
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$checkTrip->vehicle_driver_photo, 'name'=>$checkTrip->vehicle_driver_user_name,
					'rating' => $checkTrip->vehicle_driver_rating, 'plate'=>$checkTrip->vehicle_plate_number, 'brand'=>$checkTrip->vehicle_type, 
					'driverId' => $checkTrip->vehicle_driver_user_id, 'id'=>$checkTrip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$checkTrip->phone_number];
				
				$recL[($trip->passenger_user_id."-".$trip->id)] = ['status'=>1, 'trip'=>$trip1, 'driver'=>$driver, 'tripData'=>$checkTrip];
				$data1['recL'] = $recL;
				$data1['status'] = 1;
				$data1['messageType'] = 'DRIVER PICKED UP PASSENGER';
				$data = json_encode($data1);
				$data = 'data='.urlencode($data);
				$url = "http://140.82.52.195:8080/post-driver-picked-up-passenger";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				

				$jk = new \App\Junk();
				$jk->data=$data;
				$jk->save();

				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'deals'=>$dl]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}





	
	/***setArrivedForTripByTripId***/
	public function setArrivedForTripByTripId(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try {
            $token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$driverId 	= $request->has('driverId') ? $request->get('driverId') : null;
			
			//$trip = \App\Trip::where('id', '=', $tripId)->where('vehicle_driver_user_id', '=', $driverId)->first();
			$trip = \DB::table('trips')
				->join('driver_deals', 'trips.driver_deal_id', '=', 'driver_deals.id')->where('trips.vehicle_driver_user_id', '=', $driverId)
				->whereNotIn('trips.status', ['Completed', 'Driver Canceled', 'Passenger Canceled', 'Completed', 'Completed & Paid', 'Admin Canceled'])->orderBy('trips.created_at', 'DESC')
				->select('trips.*', 'driver_deals.vehicle_id', 'driver_deals.passenger_user_id', 'driver_deals.driver_user_id', 'driver_deals.trip_id'
				, 'driver_deals.origin_locality', 'driver_deals.origin_longitude', 'driver_deals.origin_latitude', 'driver_deals.destination_longitude'
				, 'driver_deals.destination_latitude', 'driver_deals.distance', 'driver_deals.fee', 'driver_deals.note'
				, 'driver_deals.payment_method', 'driver_deals.status as dealStatus', 'driver_deals.destination_locality', 'driver_deals.passenger_user_full_name'
				, 'driver_deals.booking_group_id')->first();
				
			
			
			if($trip!=null)
			{
				if($trip->status=='Pending')
				{
					date_default_timezone_set('Africa/Lusaka');
					$driver_deals = \App\DriverDeal::where('trip_id', '=', $trip->id)->first();
					$driver_deals->is_arrived = 1;
					$driver_deals->save();
					
					
					$trp = \App\Trip::where('id', '=', $trip->id)->first();
					$trp->pickedUpAt = date('Y-m-d H:i');
					$trp->is_arrived = 1;
					$trp->save();

					$trip->is_arrived = 1;

					


				}
				
				$pkdUpAt = "";
				$pkdUpAt1 = "";
				if($trip->status=="Pending")
				{
					$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
					$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
				}
				else if($trip->status=="Going")
				{
					$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
					$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
				}
				else if($trip->status=="Passenger Canceled")
				{
					$pkdUpAt = "You canceled trip";
					$pkdUpAt1 = "You canceled trip";
				}
				else if($trip->status=="Driver Canceled")
				{
					$pkdUpAt = "Drivers canceled trip";
					$pkdUpAt1 = "Drivers canceled trip";
				}
				else if($trip->status=="Completed")
				{
					$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
					if($trip->paidYes==1)
					{
						$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						if($trip->payment_method=='Card')
						{
							$total_balance = $total_balance + $trip->amount_chargeable;
						}
					}
					
				}
				
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
				$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
				$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
					
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
				
				$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
					'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived, 'travel_time'=>$trip->travel_time,  'passengerName'=>$trip->passenger_user_name,
					'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
					'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
					'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
					'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$trip->dealStatus, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 'previousPassengerRating'=>$trip->previous_passenger_rating, 
					'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'is_arrived'=>$trip->is_arrived,
					'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
					'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
					'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id];
					
				
				
				
				$dealId = $trip->driver_deal_id;
				$sql = 'SELECT *, v2.status as status, v2.id as driverDealId, v1.id as vehicleTrackerId FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE 
					v1.vehicle_id = v2.vehicle_id AND v2.driver_user_id = '.$trip->vehicle_driver_user_id;
				if($dealId!=null)
				{
					$sql = $sql.' AND v2.id = '.$dealId;
				}
				$sql = $sql.' AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Completed", "Accepted", "Going"))) ORDER BY v2.id DESC LIMIT 0, 1';
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				
				$deals = \DB::select($sql);
				$dl = null;
				if(sizeof($deals)>0)
				{
					$dl = [];
					$vehicleTypeIcon = in_array($deals[0]->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$deals[0]->vehicle_type] : $vehicleIcons['Taxi'];
					$originLocation = ['lng'=>floatval($deals[0]->origin_longitude), 'lat'=>floatval($deals[0]->origin_latitude)];
					$destLocation = ['lng'=>floatval($deals[0]->destination_longitude), 'lat'=>floatval($deals[0]->destination_latitude)];
					$origin=['location'=>$originLocation, 'vicinity'=>$deals[0]->origin_locality];
					$destination=['location'=>$destLocation, 'vicinity'=>$deals[0]->destination_locality];
					$dl = ['tripId'=> $deals[0]->trip_id, 'status'=>$deals[0]->status, 'createdAt'=>strtotime($deals[0]->created_at), 'is_arrived'=>$deals[0]->is_arrived,
						'origin'=>$origin, 'destination'=>$destination, 'driverDealId'=>$deals[0]->driverDealId, 'id'=>$deals[0]->driverDealId, 
						'vehicleTrackerId'=>$deals[0]->vehicleTrackerId, 'deal_status'=>$deals[0]->status, 'icon'=>$vehicleTypeIcon, 'fee'=>$deals[0]->fee, 'currency'=>'ZMW'];
				}

				$data1 = [];
				$recL = [];
				$checkTrip = \App\Trip::where('id', '=', $trip->id)->first();
				$location=['lat'=>$checkTrip->origin_latitude, 'lng'=>$checkTrip->origin_longitude];
				$locationDes=['lat'=>$checkTrip->destination_latitude, 'lng'=>$checkTrip->destination_longitude];
				$origin = ['vicinity'=>$checkTrip->origin_vicinity, 'location'=>$location];
				$destination = ['vicinity'=>$checkTrip->destination_vicinity, 'location'=>$locationDes];


				$trip1 = ['driverId' => $checkTrip->vehicle_driver_user_id, 'identifier'=>$checkTrip->trip_identifier,
					'currency'=> $checkTrip->currency, 'fee'=> $checkTrip->amount_chargeable, 'origin'=> $origin, 
					'destination'=> $destination, 'status'=> $checkTrip->status, 'id'=>$checkTrip->id, 
					'driver_starting_location_latitude'=>$checkTrip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$checkTrip->driver_starting_location_longitude];
					
				
				$vehicleTypeIcon = $trip->vehicle_icon;
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];
				$recl = [];
				$recl['DEALACCEPTED'.$checkTrip->passenger_user_id] = ['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'tripData'=>$trip];


				$recL[($trip->passenger_user_id."-".$trip->id)] = ['status'=>1, 'trip'=>$trip1, 'driver'=>$driver, 'tripData'=>$checkTrip];
				$data1['recL'] = $recL;
				$data1['status'] = 1;
				$data1['messageType'] = 'DRIVER PICKUP';
				$data = json_encode($data1);
				$data = 'data='.urlencode($data);
				$url = "http://140.82.52.195:8080/post-driver-arrived-pickup";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				

				$jk = new \App\Junk();
				$jk->data=$data;
				$jk->save();
		
				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'deals'=>$dl]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }catch(\Exception $e)
        {
            return response()->json(['status'=>500,  'message'=>$e->getMessage(), 'line'=>$e->getLine()]);
        }
	}
	
	
	public function receiveCashPayment(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$trans_ref = strtoupper(join('-', str_split(str_random(16), 4)));
		$input = ($request->all());
		$token = JWTAuth::getToken();
		$dealId = $request->has('dealId') ? $request->get('dealId') : null;
		$deal = \App\DriverDeal::where('id', '=', $dealId)->first();
		$trip = \App\Trip::where('driver_deal_id', '=', $deal->id)->first();
		$user = JWTAuth::toUser($token);
		$deviceRefNo = $request->has('deviceRefNo') ? $request->get('deviceRefNo') : null;
		$fee = $trip->amount_chargeable + $trip->extra_charges;
		
		$params = array();
		$params['orderId'] = $deviceRefNo;
		$params['payerName'] = $user->name;
		$params['payerEmail'] = $user->email;
		$params['payerPhone'] = $user->mobileNumber;
		$params['payerId'] = $user->nrcNumber;
		$params['nationalId'] = $user->nrcNumber;
		$params['scope'] = 'TAXI TRIP PAYMENT';
		$params['description'] = 'Payment for trip|'.$user->name.'|'.$user->mobileNumber;
		$params['paymentItem'] = ['Cash Payment For Trip'];
		$params['amount'] = [$fee];
		$params['currency'] = DEFAULT_CURRENCY;
		
		$txn = new \App\Transaction();
		$txn->orderId = $deviceRefNo;
		$txn->requestData = json_encode($params);
		$txn->status = 'Success';
		$txn->payeeUserId = $user->id;
		$txn->payeeUserFullName = $user->name;
		$txn->payeeUserMobile = $user->mobileNumber;
		$txn->tripId = $trip->id;
		$txn->tripOrigin = $trip->origin_vicinity;
		$txn->tripDestination = $trip->destination_vicinity;
		$txn->driverUserId = $trip->vehicle_driver_user_id;
		$txn->vehicleId = $trip->vehicle_id;
		$txn->transactionRef = $trans_ref;
		$txn->amount = $fee;
		$txn->payment_method = "CASH";
		$txn->status = 'Success';
		$txn->save();

		$fareBreakDownSettings = \App\FareBreakDownSetting::where('status', '=', 1)->get();
		$j = 0;
		$fareBreakDownSettingCount = $fareBreakDownSettings->count();
		$totalBreakDownAmount = 0;
		foreach($fareBreakDownSettings as $fareBreakDownSetting)
		{
			$breakdown_amount = $txn->amount * ($fareBreakDownSetting->value_percent/100);
			if(($fareBreakDownSettingCount-1)==$j)
			{
				$breakdown_amount = $txn->amount - $totalBreakDownAmount;
			}
			$transactionBreakdown = new \App\TransactionBreakdown();
			$transactionBreakdown->user_id = $fareBreakDownSetting->is_withdrawable==0 ? null : $txn->driverUserId;
			$transactionBreakdown->transaction_type = $fareBreakDownSetting->title;
			$transactionBreakdown->transaction_id = $txn->id;
			$transactionBreakdown->trip_id = $trip->id;
			$transactionBreakdown->breakdown_amount = $breakdown_amount;
			$transactionBreakdown->is_reversed = 0;
			$transactionBreakdown->details = $fareBreakDownSetting->details;
			$transactionBreakdown->is_credit = 0;
			$transactionBreakdown->is_withdrawable = $fareBreakDownSetting->is_withdrawable;
			$transactionBreakdown->save();
			$totalBreakDownAmount = $totalBreakDownAmount + $breakdown_amount;


		}
		
		$trip->status = 'Completed & Paid';
		$trip->paidYes = 1;
		$trip->save();
		
		$deal->status = 'Completed & Paid';
		$deal->save();
			
		$driver = \App\User::where('id', '=', $txn->driverUserId)->first();
		//$driver->virtualAccountValue = $driver->virtualAccountValue - $txn->amount;
		$driver->outstanding_balance = $driver->outstanding_balance - $txn->amount;
		$driver->save();	
		
		$vehicle = \App\Vehicle::where('id', '=', $trip->vehicle_id)->first();
		$vehicle->status = 'Available';
		$vehicle->save();
		
		try
		{
			$mobile = strpos($driver->mobileNumber, "260")==0 ? $driver->mobileNumber : ("26".$driver->mobileNumber);
			$msg = "Dear ".$driver->name."\nYour Tweende trip has been marked as completed and you received payment by cash of ZMW".$txn->amount;
			$sender = "Bevura";
			send_sms($mobile, $msg, $sender=NULL);
		}
		catch(\Exception $e)
		{
			
		}
		//dd(11);
		
		
		$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
		$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
		$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
		$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];


		$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
			'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived, 'travel_time'=>$trip->travel_time, 'previousPassengerRating'=>$trip->previous_passenger_rating,
			'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
			'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
			'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
			'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 
			'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo,  'passengerName'=>$trip->passenger_user_name, 
			'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
			'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude
		];



					$data1 = [];
					$recL = [];
					$checkTrip = \App\Trip::where('id', '=', $trip->id)->first();
					$location=['lat'=>$checkTrip->origin_latitude, 'lng'=>$checkTrip->origin_longitude];
					$locationDes=['lat'=>$checkTrip->destination_latitude, 'lng'=>$checkTrip->destination_longitude];
					$origin = ['vicinity'=>$checkTrip->origin_vicinity, 'location'=>$location];
					$destination = ['vicinity'=>$checkTrip->destination_vicinity, 'location'=>$locationDes];
	

					$trip1 = ['driverId' => $checkTrip->vehicle_driver_user_id, 'identifier'=>$checkTrip->trip_identifier,
						'currency'=> $checkTrip->currency, 'fee'=> $checkTrip->amount_chargeable, 'origin'=> $origin, 
						'destination'=> $destination, 'status'=> $checkTrip->status, 'id'=>$checkTrip->id, 
						'driver_starting_location_latitude'=>$checkTrip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$checkTrip->driver_starting_location_longitude];
					
				
					$vehicleTypeIcon = $trip->vehicle_icon;
					$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
						'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
						'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];
					$recl = [];
					

					$fare = $trip->amount_chargeable + $trip->extra_charges;
					$recL[($trip->passenger_user_id."-".$trip->id)] = ['status'=>1, 'trip'=>$trip1, 'driver'=>$driver, 'tripData'=>$checkTrip, 'fare'=>$fare];
					$data1['recL'] = $recL;
					$data1['status'] = 1;
					$data1['messageType'] = 'PAYMENT RECEIVED';
					$data = json_encode($data1);
					$data = 'data='.urlencode($data);
					$url = "http://140.82.52.195:8080/post-driver-received-cash-payment";
					//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
					$server_output = sendPostRequestForBevura($url, $data);
					$jk = new \App\Junk();
					$jk->data=$data;
					$jk->save();

			
		return response()->json(['status'=>1, 'trip'=>$trip_, 'message'=>'Payment was successful']);
	}
	
	/****getDriverPosition***/
	public function getDriverPosition(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
            
			$token = JWTAuth::getToken();
			$driverUserId 	= $request->has('driverUserId') ? $request->get('driverUserId') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			$user = JWTAuth::toUser($token);
			
			$vehicle_tracker = \DB::table('vehicle_trackers')->where('vehicle_driver_user_id', '=', $driverUserId)->first();
		
			if($vehicle_tracker!=null)
			{
				$location=[];
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				if($trip==null)
				{
					return response()->json(['status'=>0]);
				}
				return response()->json(['status'=>1, 'lat'=>$vehicle_tracker->current_latitude, 
					'lng'=>$vehicle_tracker->current_longitude, 'oldLat'=>$vehicle_tracker->old_latitude, 'oldLng'=>$vehicle_tracker->old_longitude, 'tripStatus'=>$trip->status, 'trip'=>$trip]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	public function getDriverPosition1(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
            
			$token = JWTAuth::getToken();
			$driverUserId 	= $request->has('driverUserId') ? $request->get('driverUserId') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			$user = JWTAuth::toUser($token);
			
			$vehicle_tracker = \DB::table('vehicle_trackers')->where('vehicle_driver_user_id', '=', $driverUserId)->first();
		
			if($vehicle_tracker!=null)
			{
				$location=[];
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				return response()->json(['status'=>1, 'lat'=>$vehicle_tracker->current_latitude, 
					'lng'=>$vehicle_tracker->current_longitude, 'oldLat'=>$vehicle_tracker->old_latitude, 'oldLng'=>$vehicle_tracker->old_longitude, 'tripStatus'=>$trip->status]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	/****rateTrip***/
	public function rateTrip(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try {
			$token = JWTAuth::getToken();
			$rating 	= $request->has('rating') ? $request->get('rating') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			$user = JWTAuth::toUser($token);    
			
			$trip = \App\Trip::where('id', '=', $tripId)->where('status', '=', 'Completed')->first();
			if($trip!=null)
			{
				$trip->drive_rating = $rating;
				$trip->save();
				$vehicleId = $trip->vehicle_id;
				$vehicle = \App\Vehicle::where('id', '=', $vehicleId)->first();
				$ratingTotal = ((($vehicle->avg_system_rating==null ? 0 :$vehicle->avg_system_rating) * $vehicle->rating_count) + $rating)/($vehicle->rating_count+1);
				$vehicle->avg_system_rating = $ratingTotal;
				$vehicle->rating_count = $vehicle->rating_count + 1;
				$vehicle->trips_completed_count = $vehicle->trips_completed_count + 1;
				$vehicle->outstanding_balance = $vehicle->outstanding_balance + $trip->amount_chargeable + $trip->extra_charges;
				$vehicle->save();
				return response()->json(['status'=>1]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	public function cancelPassengerOpenDeals(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
            $sql = 'SELECT * FROM `driver_deals` v2 WHERE 
				v2.passenger_user_id = '.$user->id.' AND v2.status = "Pending" ORDER by v2.created_at DESC';
			$deals = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0];
			$dealReceipients = [];
			$tripReceipients = [];
			
			if(sizeof($deals)>0)
			{
				foreach($deals as $deal)
				{
					$deal = \App\DriverDeal::where('id', '=', $deal->id)->first();
					$deal->status = 'Passenger Canceled';
					$deal->save();
					
					$trip = \App\Trip::where('driver_deal_id', '=', $deal->id)->first();
					$trip->status = 'Passenger Canceled';
					$trip->save();

					array_push($dealReceipients, $driverId."-".$dealId);
					array_push($tripReceipients, $driverId."-".$trip->id);
				}
				$accts = ['status' =>1];
			}

			if(sizeof($dealReceipients)>0)
			{
					$dealHolder = [];
					$dealHolder['dealsCanceled'] = $dealReceipients;
					$dealHolder['tripsCanceled'] = $tripReceipients;
					$data1 = [];
					$data1['recL'] = $dealHolder;
					$data1['status'] = 1;
					$data1['messageType'] = 'DRIVER DEAL TIMED OUT';
					$data = json_encode($data1);
					$data = 'data='.urlencode($data);
					$url = "http://140.82.52.195:8080/post-driver-deal-timed-out";
					//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
					$server_output = sendPostRequestForBevura($url, $data);

						$jk = new \App\Junk();
						$jk->data=$data;
						$jk->save();
			}

			return response()->json($accts);
			
        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	/***payTripFeeUsingCard***/
	public function payTripFeeUsingCard(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$fee = $request->has('fee') ? $request->get('fee') : null;
			$cardType = $request->has('cardType') ? $request->get('cardType') : null;
			$number = $request->has('number') ? $request->get('number') : null;
			$cvv = $request->has('cvv') ? $request->get('cvv') : null;
			$exp = $request->has('exp') ? $request->get('exp') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			
			$trans_ref = strtoupper(join('-', str_split(str_random(16), 4)));
			$paymentItem = array();
			$paymentItem[0] = urlencode('Payment for trip|'.$user->name.'|'.$user->mobileNumber);
			$amount[0] = number_format($fee, 2, '.', '');
			$totalAmount = number_format($fee, 2, '.', '');
			
			
			$params = array();
			$params['merchantId'] = PAYMENT_MERCHANT_ID;
			$params['deviceCode'] = PAYMENT_DEVICE_ID;
			$params['serviceTypeId'] = '1981511018900';
			$params['orderId'] = $trans_ref;
			$params['payerName'] = $user->name;
			$params['payerEmail'] = $user->email;
			$params['payerPhone'] = $user->mobileNumber;
			$params['payerId'] = $user->nrcNumber;
			$params['nationalId'] = $user->nrcNumber;
			$params['scope'] = 'Payment for trip|'.$user->name.'|'.$user->mobileNumber;
			$params['description'] = 'Payment for trip|'.$user->name.'|'.$user->mobileNumber;
			$params['responseurl'] = 'http://192.168.43.136/payments/handle-response-success';
			$params['paymentItem'] = $paymentItem;
			$params['amount'] = $amount;
			$params['currency'] = DEFAULT_CURRENCY;
			$params['cardnum'] = $number;
			$params['expdate'] = $exp;
			$params['cvv'] = $cvv;
			$params['paymentOption'] = "EAGLECARD";
			
			$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
					$params['orderId'].$totalAmount.$params['responseurl'].PAYMENT_API_KEY;
			
			$reqPrefs['http']['method'] = 'POST';
			$stream_context = stream_context_create($reqPrefs);
			$url = "http://payments.probasepay.com/payments/process-mobile-eagle-process-otp".
				"?merchantId=".PAYMENT_MERCHANT_ID."&deviceCode=".PAYMENT_DEVICE_ID.
				"&serviceTypeId=1981511018900&orderId=".$trans_ref.
				"&payerName=".urlencode($user->name)."&payerEmail=".$user->email.
				"&payerPhone=".urlencode($user->mobileNumber)."&payerId=".urlencode($user->nrcNumber).
				"&nationalId=".urlencode($user->nrcNumber)."&scope=".urlencode("Payment for trip|".$user->name."|".$user->mobileNumber).
				"&description=".urlencode("Payment for trip|".$user->name."|").$user->mobileNumber."&responseurl=http://192.168.43.136/payments/handle-response-success".
				"&paymentItem=".(json_encode($paymentItem))."&amount=".json_encode($amount).
				"&currency=".DEFAULT_CURRENCY."&hash=".hash('sha512', $toHash).
				"&cardnum=".$number."&expdate=".$exp."&cvv=".$cvv."&paymentOption=EAGLECARD";
			
			//dd($url);
			//$url = 'http://api.football-data.org/v1/competitions/';
			
			$json = file_get_contents($url, false, $stream_context);
			
			$json1 = json_decode($json);
			if($json1->status==1)
			{
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				
				$txn = new \App\Transaction();
				$txn->orderId = $trans_ref;
				$txn->requestData = json_encode($params);
				$txn->status = 'Pending';
				$txn->payeeUserId = $user->id;
				$txn->payeeUserFullName = $user->name;
				$txn->payeeUserMobile = $user->mobileNumber;
				$cardLength = ceil(strlen($number)/2) - 2;
				$txn->card_pan = substr($number, 0, $cardLength-1)."****".substr($number, (strlen($number)-4));
				$txn->payment_method = 'Card';
				$txn->tripId = $tripId;
				$txn->tripOrigin = $trip->origin_vicinity;
				$txn->tripDestination = $trip->destination_vicinity;
				$txn->driverUserId = $trip->vehicle_driver_user_id;
				$txn->vehicleId = $trip->vehicle_id;
				$txn->transactionRef = $json1->txnRef;
				$txn->save();
				
				
				
			}
			
			return $json;
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	



	public function getDriverPositionBatch(\App\Http\Requests\BaseTaxiRequest $request)
	{
		date_default_timezone_set('Africa/Lusaka');
		
		try {
            
			$token = JWTAuth::getToken();
			$driverUserId 	= $request->has('driverUserId') ? $request->get('driverUserId') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			$lastRouteId = $request->has('lastRouteId') ? $request->get('lastRouteId') : null;
			$user = JWTAuth::toUser($token);
			
			//$vehicle_tracker = \DB::table('vehicle_trackers')->where('vehicle_driver_user_id', '=', $driverUserId)->first();
			$tripDataPoints = \App\TripDataPoints::where('user_id', '=', $driverUserId);
			$x = 0;
			if($lastRouteId!=null)
			{
				$x = 1;
				$tripDataPoints = $tripDataPoints->where('id', '>', $lastRouteId);
			}
			$tripDataPoints = $tripDataPoints->first();

			$oldtripDataPoints = null;
			if($lastRouteId!=null)
			{
				$oldtripDataPoints = \App\TripDataPoints::where('user_id', '=', $driverUserId)->first();
			}

			if($oldtripDataPoints==null)
			{
				$oldtripDataPoints = $tripDataPoints;
			}

			$trip = \App\Trip::where('id', '=', $tripId)->first();

		
			if($trip!=null && $tripDataPoints!=null)
			{
				$location=[];
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				return response()->json(['status'=>1, 'lat'=>$tripDataPoints->latitude, 'lastRouteId'=>$tripDataPoints->id, 'x'=>$x, 
					'lng'=>$tripDataPoints->longitude, 'oldLat'=>$oldtripDataPoints->latitude, 'oldLng'=>$oldtripDataPoints->longitude, 'tripStatus'=>'Pending', 'trip'=>$trip]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

       	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>422]);
        	}

		try
		{
			$all = $request->all();
			$dataReq = new \App\DataRequest();
			$dataReq->data_request = "REQUEST DRIVER POSITION BATCH: ".json_encode($all);
			$dataReq->save();

			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$driverUserId = $all['driverUserId'];
			$lastRouteId = isset($all['lastRouteId']) ? $all['lastRouteId'] : null;


			$tripDataPoints = \App\TripDataPoints::where('user_id', '=', $driverUserId);
			if($lastRouteId!=null)
			{
				$tripDataPoints = $tripDataPoints->where('id', '>=', $lastRouteId);
			}
			$tripDataPoints = $tripDataPoints->limit(10)->get();


			$tdps = [];
			$i = 0;
			$t1 = null;
			$t2 = null;
			foreach($tripDataPoints as $tripDataPoint)
			{
				if($i==0)
				{
					$t1 = $tripDataPoint;
				}
				if($i==($tripDataPoints->count() - 1))
				{
					$t2 = $tripDataPoint;
				}
				$i++;
			}




			$url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$t1->latitude.",".$t1->longitude."&destination=".$t2->latitude.",".$t2->longitude."&key=".GOOGLE_MAP_KEY."&traffic_model=best_guess&departure_time=".time();//1541202457
			$resp = handleCurlGetRequest($url);
			$resp = json_decode($resp);
			$speedMs = null;
			if(isset($resp->status) && $resp->status=='OK')
			{
				$routes = isset($resp->routes) ? $resp->routes : null;
				$time = $routes[0]->legs[0]->duration_in_traffic->value;
				$distance = $routes[0]->legs[0]->distance->text;
				$distanceMeters = $routes[0]->legs[0]->distance->value;
				$speedMs = $distanceMeters/$time;
				$speedMs = $speedMs * 1000; 
			}
			return response()->json(['status'=>1, 'speedMs'=>$speedMs, 'lastRouteId'=>$t2->id, 'routeBatch'=>$tripDataPoints, 'resp'=>$resp]);
		
		}
		catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'message'=>'Token Expired']);
        	}
		catch(\Exception $e)
		{
            		return response()->json(['status'=>-1, 'message'=>$e->getMessage(), 'line'=>$e->getLine(), 'ft'=>$tripDataPoints ]);
		}

	}





	/*Get Driver Deal By Token*/
	public function getDriverDealByToken(\App\Http\Requests\BaseTaxiRequest $request)
	{
		date_default_timezone_set('Africa/Lusaka');
		try
		{
			$all = $request->all();
			$dataReq = new \App\DataRequest();
			$dataReq->data_request = "GET DRIVER DEAL BY TOKEN: ".json_encode($all);
			$dataReq->save();
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			
			
			$vehicleTracker = \App\VehicleTracker::where('vehicle_driver_user_id', '=', $user->id);
			if($vehicleTracker->count()>0)
			{
				$vehicleTracker = $vehicleTracker->first();
			}
			else
			{
				$vehicle = \App\Vehicle::where('driver_user_id', '=', $driverId);
				if($vehicle->count()==0)
				{
					return response()->json(['status'=>2, 'message'=>'Vehicle not found']);
				}
			}
			
			
			$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			
			$trip = \DB::table('trips')
				->join('driver_deals', 'trips.driver_deal_id', '=', 'driver_deals.id')->where('trips.vehicle_driver_user_id', '=', $user->id)
				->join('users', 'trips.passenger_user_id', '=', 'users.id')->where('trips.vehicle_driver_user_id', '=', $user->id)
				->whereNotIn('trips.status', ['Driver Canceled', 'Passenger Canceled', 'Completed & Paid', 'Admin Canceled'])
				->whereNotIn('driver_deals.status', ['Timed Out', 'Driver Canceled', 'Passenger Canceled', 'Completed & Paid', 'Admin Canceled'])->orderBy('trips.created_at', 'DESC')
				->select('trips.*', 'driver_deals.vehicle_id', 'driver_deals.passenger_user_id', 'driver_deals.driver_user_id', 'driver_deals.trip_id'
				, 'driver_deals.origin_locality', 'driver_deals.origin_longitude', 'driver_deals.origin_latitude', 'driver_deals.destination_longitude'
				, 'driver_deals.destination_latitude', 'driver_deals.distance', 'driver_deals.fee', 'driver_deals.note', 'users.passport_photo', 'users.name as passenger_user_name', 'users.userRating as passenger_rating'
				, 'driver_deals.payment_method', 'driver_deals.status as dealStatus', 'driver_deals.destination_locality', 'driver_deals.passenger_user_full_name'
				, 'driver_deals.booking_group_id')->first();
			$ij = 0;
			
			//dd($trip);
			if($trip!=null)
			{
				$origin['vicinity'] = $trip->origin_vicinity;
				$dest['vicinity'] = $trip->destination_vicinity;
				$trip_ = [];
				$pkdUpAt = "";
				$pkdUpAt1 = "";
				if($trip->status=="Pending")
				{
					$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
					$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
				}
				else if($trip->status=="Going")
				{
					$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
					$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
				}
				else if($trip->status=="Passenger Canceled")
				{
					$pkdUpAt = "You canceled trip";
					$pkdUpAt1 = "You canceled trip";
				}
				else if($trip->status=="Driver Canceled")
				{
					$pkdUpAt = "Drivers canceled trip";
					$pkdUpAt1 = "Drivers canceled trip";
				}
				else if($trip->status=="Completed")
				{
					$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
					if($trip->paidYes==1)
					{
						$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						if($trip->payment_method=='Card')
						{
							$total_balance = $total_balance + $trip->amount_chargeable;
						}
					}
					
				}
				
					
					
					
					
					
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
				$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
				$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
		
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
					
				$deal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();
				$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
					'payment_method'=>$trip->payment_method, 'travel_time'=>$trip->travel_time, 'previousPassengerRating'=>$trip->previous_passenger_rating,
					'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 'is_arrived'=>$trip->is_arrived, 
					'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 'passengerPix'=>$trip->passport_photo,
					'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id,  'passengerName'=>$trip->passenger_user_name,
					'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$trip->status, 'id'=>$trip->id,  'passengerRating'=>$trip->passenger_rating, 
					'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo,  'travel_time'=>$deal->travel_time,
					'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
					'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
					'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				return response()->json(['status'=>2, 'trip'=>$trip_]);
			}
				
				
			
			$driverDeals =null;	
			$sql = 'SELECT * FROM `driver_deals` v2 WHERE 
				v2.driver_user_id = '.$user->id;
			$sql = $sql.' AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Accepted", "Going", "Completed"))) ORDER BY v2.id DESC LIMIT 0, 1';

			$jk = new \App\Junk();
			$jk->data="search for driver deal - ".$sql;
			$jk->save();
			$deals = \DB::select($sql);
			if($deals!=null && sizeof($deals)>0)
			{
				$deal = $deals[0];
				$driverDeals = \App\DriverDeal::where('id', '=', $deal->id)->with('passenger')->first();
			}
			
			//updatePosition
			
			if($driverDeals!=null)
			{
				return response()->json(['status'=>1, 'message'=>'Updated successfully', 'driverDeal'=>$driverDeals]);
			}
			else
			{
				return response()->json(['status'=>0, 'vehicleTracker'=>$vehicleTracker]);
			}
		}
		catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'message'=>'Token Expired']);
        	}
		catch(\Exception $e)
		{
            		return response()->json(['status'=>-1, 'message'=>$e->getMessage()]);
		}
	}
	
	/***updateVehiclePosition***/
	public function updateVehiclePosition(\App\Http\Requests\BaseTaxiRequest $request)
	{
		date_default_timezone_set('Africa/Lusaka');
		try
		{
			$all = $request->all();
			$dataReq = new \App\DataRequest();
			$dataReq->data_request = "UPDATE DRIVER POSITION: ".json_encode($all);
			$dataReq->save();
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$driverId = $request->has('driverId') ? $request->get('driverId') : null;
			$lat = $request->has('lat') ? $request->get('lat') : null;
			$lng = $request->has('lng') ? $request->get('lng') : null;


			$tdp = new \App\TripDataPoints();
			$tdp->latitude = $lat;
			$tdp->longitude = $lng;
			$tdp->user_id = $driverId;
			$tdp->save();
			
			if($driverId==null || $lat==null || $lng==null)
			{
				return response()->json(['status'=>0, 'message'=>'Error updating position']);
			}
			
			$vehicleTracker = \App\VehicleTracker::where('vehicle_driver_user_id', '=', $driverId);
			if($vehicleTracker->count()>0)
			{
				$vehicleTracker = $vehicleTracker->first();


				if($vehicleTracker->current_latitude!=$lat || $vehicleTracker->current_longitude!=$lng)
				{
					$oldLat = $vehicleTracker->current_latitude;
					$oldLng = $vehicleTracker->current_longitude;
					$vehicleTracker->current_longitude = $lng;
					$vehicleTracker->current_latitude = $lat;
					$vehicleTracker->old_longitude = $oldLng;
					$vehicleTracker->old_latitude = $oldLat;
					$vehicleTracker->save();
				}
			}
			else
			{
				$vehicle = \App\Vehicle::where('driver_user_id', '=', $driverId);
				if($vehicle->count()>0)
				{
					$vehicle = $vehicle->first();
					$vehicleTracker = new \App\VehicleTracker();
					$vehicleTracker->vehicle_id = $vehicle->id;
					$vehicleTracker->vehicle_unique_id = str_random(10);//$vehicle->uniqid;
					$vehicleTracker->status = 'Available';
					$vehicleTracker->vehicle_type = $vehicle->vehicle_type;
					$vehicleTracker->vehicle_driver_name = $user->name;
					$vehicleTracker->vehicle_driver_user_id = $user->id;
					$vehicleTracker->vehicle_driver_photo = $user->passport_photo;
					$vehicleTracker->current_longitude = $lng;
					$vehicleTracker->current_latitude = $lat;
					$vehicleTracker->old_longitude = $lng;
					$vehicleTracker->old_latitude = $lat;
					$vehicleTracker->vehicle_type_id = $vehicle->vehicle_type_id;
					$vehicleTracker->save();
				}
				else{
					return response()->json(['status'=>2, 'message'=>'Vehicle not found']);
				}
			}
			
			
			$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			
			$trip = \DB::table('trips')
				->join('driver_deals', 'trips.driver_deal_id', '=', 'driver_deals.id')->where('trips.vehicle_driver_user_id', '=', $user->id)
				->join('users', 'trips.passenger_user_id', '=', 'users.id')->where('trips.vehicle_driver_user_id', '=', $user->id)
				->whereNotIn('trips.status', ['Driver Canceled', 'Passenger Canceled', 'Completed & Paid', 'Admin Canceled'])
				->whereNotIn('driver_deals.status', ['Timed Out', 'Driver Canceled', 'Passenger Canceled', 'Completed & Paid', 'Admin Canceled'])->orderBy('trips.created_at', 'DESC')
				->select('trips.*', 'driver_deals.vehicle_id', 'driver_deals.passenger_user_id', 'driver_deals.driver_user_id', 'driver_deals.trip_id'
				, 'driver_deals.origin_locality', 'driver_deals.origin_longitude', 'driver_deals.origin_latitude', 'driver_deals.destination_longitude'
				, 'driver_deals.destination_latitude', 'driver_deals.distance', 'driver_deals.fee', 'driver_deals.note', 'users.passport_photo', 'users.name as passenger_user_name', 'users.userRating as passenger_rating'
				, 'driver_deals.payment_method', 'driver_deals.status as dealStatus', 'driver_deals.destination_locality', 'driver_deals.passenger_user_full_name'
				, 'driver_deals.booking_group_id')->first();
			$ij = 0;
			
			//dd($trip);
			if($trip!=null)
			{
				$origin['vicinity'] = $trip->origin_vicinity;
				$dest['vicinity'] = $trip->destination_vicinity;
				$trip_ = [];
				$pkdUpAt = "";
				$pkdUpAt1 = "";
				if($trip->status=="Pending")
				{
					$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
					$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
				}
				else if($trip->status=="Going")
				{
					$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
					$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
				}
				else if($trip->status=="Passenger Canceled")
				{
					$pkdUpAt = "You canceled trip";
					$pkdUpAt1 = "You canceled trip";
				}
				else if($trip->status=="Driver Canceled")
				{
					$pkdUpAt = "Drivers canceled trip";
					$pkdUpAt1 = "Drivers canceled trip";
				}
				else if($trip->status=="Completed")
				{
					$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
					if($trip->paidYes==1)
					{
						$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						if($trip->payment_method=='Card')
						{
							$total_balance = $total_balance + $trip->amount_chargeable;
						}
					}
					
				}
				
					
					
					
					
					
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
				$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
				$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
		
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
					
				$deal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();
				$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
					'payment_method'=>$trip->payment_method, 'travel_time'=>$trip->travel_time, 'previousPassengerRating'=>$trip->previous_passenger_rating,
					'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 'is_arrived'=>$trip->is_arrived, 
					'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 'passengerPix'=>$trip->passport_photo,
					'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id,  'passengerName'=>$trip->passenger_user_name,
					'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$trip->status, 'id'=>$trip->id,  'passengerRating'=>$trip->passenger_rating, 
					'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo,  'travel_time'=>$deal->travel_time,
					'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
					'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
					'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				return response()->json(['status'=>2, 'trip'=>$trip_]);
			}
				
				
			
			$driverDeals =null;	
			$sql = 'SELECT * FROM `driver_deals` v2 WHERE 
				v2.driver_user_id = '.$user->id;
			$sql = $sql.' AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Accepted", "Going", "Completed"))) ORDER BY v2.id DESC LIMIT 0, 1';
			$deals = \DB::select($sql);
			if($deals!=null && sizeof($deals)>0)
			{
				$deal = $deals[0];
				$driverDeals = \App\DriverDeal::where('id', '=', $deal->id)->with('passenger')->first();
			}
			
			//updatePosition
			
			if($driverDeals!=null)
			{
				return response()->json(['status'=>1, 'message'=>'Updated successfully', 'driverDeal'=>$driverDeals]);
			}
			else
			{
				return response()->json(['status'=>0, 'vehicleTracker'=>$vehicleTracker]);
			}
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>-1, 'message'=>'Token Expired']);
        }
		catch(\Exception $e)
		{
            return response()->json(['status'=>-1, 'message'=>$e->getMessage()]);
		}
	}




	
	public function updateVehiclePositionBatch(\App\Http\Requests\BaseTaxiRequest $request)
	{
		date_default_timezone_set('Africa/Lusaka');
		//return response()->json(['status'=>422]);
		try
		{

			$all = $request->all();
			
			$dataReq = new \App\DataRequest();
			$dataReq->data_request = "UPDATE DRIVER POSITION: ".json_encode($all);
			$dataReq->save();
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$driverId = $request->has('driverId') ? $request->get('driverId') : null;
			$locations = $request->has('locations') ? $request->get('locations') : null;
			$ps =  $request->has('ps') ? $request->get('ps') : null;
			$dl =  $request->has('dl') ? $request->get('dl') : null;
			$tp =  $request->has('tp') ? $request->get('tp') : null;
			
			//$all = '{"driverId":154,"locations":"[{\"d\":154,\"la\":-15.3765978,\"lo\":28.312846},{\"d\":154,\"la\":-15.376583333333334,\"lo\":28.312793333333335},{\"d\":154,\"la\":-15.376546666666664,\"lo\":28.312824999999997},{\"d\":154,\"la\":-15.376491666666668,\"lo\":28.312855}]","Authorization":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjE1NCwiaXNzIjoiaHR0cHM6Ly90YXhpemFtYmlhLnByb2Jhc2VwYXkuY29tL2FwaS92MS9hdXRoL2xvZ2luIiwiaWF0IjoxNjI2MTYwMjM0LCJleHAiOjE2MjY3NjUwMzQsIm5iZiI6MTYyNjE2MDIzNCwianRpIjoiVUg0emVFdnpoWERka0RoeiJ9.-1Q9Ci5Xi5OyKZ5iPjp1yKYcX6PgexvOinvOtDzdcro","test":1,"tp":270,"ps":112}';
			//$all = json_decode($all, true);
			//$driverId = $all['driverId'];
			//$locations = $all['locations'];
			//$ps =  $all['ps'];
			//$dl =  isset($all['dl']) ? $all['dl'] : null;
			//$tp =  isset($all['tp']) ? $all['tp'] : null;

//dd($all);			
			if($driverId==null || $locations==null)
			{
				return response()->json(['status'=>0, 'message'=>'Error updating position']);
			}

			$locations = json_decode($locations);
			$x1 = 0;
			$dealLocations = [];
			$tripLocations = [];

			foreach($locations as $location)
			{
				$lat = $location->la;
				$lng = $location->lo;

				



				$tdp = new \App\TripDataPoints();
				$tdp->latitude = $lat;
				$tdp->longitude = $lng;
				$tdp->user_id = $driverId;
				$tdp->save();

				
				$vehicleTracker = \App\VehicleTracker::where('vehicle_driver_user_id', '=', $driverId);
				if($vehicleTracker->count()>0)
				{
					$vehicleTracker = $vehicleTracker->first();


					if($vehicleTracker->current_latitude!=$lat || $vehicleTracker->current_longitude!=$lng)
					{
						$oldLat = $vehicleTracker->current_latitude;
						$oldLng = $vehicleTracker->current_longitude;
						$vehicleTracker->current_longitude = $lng;
						$vehicleTracker->current_latitude = $lat;
						$vehicleTracker->old_longitude = $oldLng;
						$vehicleTracker->old_latitude = $oldLat;
						$vehicleTracker->save();
						$x1 = 1;


						if($dl!=null)
						{
							array_push($dealLocations, $location);
						}
						if($tp !=null)
						{
							array_push($tripLocations, $location);
						}
					}
				}
				else
				{
					$vehicle = \App\Vehicle::where('driver_user_id', '=', $driverId);
					if($vehicle->count()>0)
					{
						$vehicle = $vehicle->first();
						$vehicleTracker = new \App\VehicleTracker();
						$vehicleTracker->vehicle_id = $vehicle->id;
						$vehicleTracker->vehicle_unique_id = str_random(10);//$vehicle->uniqid;
						$vehicleTracker->status = 'Available';
						$vehicleTracker->vehicle_type = $vehicle->vehicle_type;
						$vehicleTracker->vehicle_driver_name = $user->name;
						$vehicleTracker->vehicle_driver_user_id = $user->id;
						$vehicleTracker->vehicle_driver_photo = $user->passport_photo;
						$vehicleTracker->current_longitude = $lng;
						$vehicleTracker->current_latitude = $lat;
						$vehicleTracker->old_longitude = $lng;
						$vehicleTracker->old_latitude = $lat;
						$vehicleTracker->vehicle_type_id = $vehicle->vehicle_type_id;
						$vehicleTracker->save();
						$x1 = 1;

						if($dl!=null)
						{
							array_push($dealLocations, $location);
						}
						if($tp !=null)
						{
							array_push($tripLocations, $location);
						}
					}
					else{
						return response()->json(['status'=>2, 'message'=>'Vehicle not found']);
					}
				}
			
			
			}
			
			if($x1==1)
			{
				
				if($tripLocations!=null && sizeof($tripLocations)>0)
				{
					$driversToNotify = [];
					$driversToNotify[($ps==null ? "" : $ps."-".$tp)] = $tripLocations;
					$data1 = [];
					$data1['recL'] = $driversToNotify;
					$data1['status'] = 1;
					$data1['messageType'] = 'SEND PASSENGER DRIVER TRIP LOCATION';
					$data = json_encode($data1);
					$data = 'data='.urlencode($data);
					$url = "http://140.82.52.195:8080/post-receive-driver-position";
					//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
					$server_output = sendPostRequestForBevura($url, $data);

						$jk = new \App\Junk();
						$jk->data=$data;
						$jk->save();
				}
				else
				{
					if($dealLocations!=null && sizeof($dealLocations)>0)
					{
						$driversToNotify = [];
						$driversToNotify[($ps==null ? "" : $ps."-".$dl)] = $dealLocations;
						$data1 = [];
						$data1['recL'] = $driversToNotify;
						$data1['status'] = 1;
						$data1['messageType'] = 'SEND PASSENGER DRIVER DEAL LOCATION';
						$data = json_encode($data1);
						$data = 'data='.urlencode($data);
						$url = "http://140.82.52.195:8080/post-receive-driver-position";
						//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
						$server_output = sendPostRequestForBevura($url, $data);

						$jk = new \App\Junk();
						$jk->data=$data;
						$jk->save();
					}
				}
				return response()->json(['status'=>1, 'message'=>'Updated successfully']);


			}
			else
			{
				return response()->json(['status'=>0]);
			}
		}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'message'=>'Token Expired']);
        	}
		catch(\Exception $e)
		{
			dd($e);
            		return response()->json(['status'=>-1, 'message'=>$e->getMessage()]);
		}
	}
	
	
	public function getDealBySpecificId()
	{
		
	}
	
	
	
	
	/***payTripFeeUsingCardStepTwo***/
	public function payTripFeeUsingCardStepTwo(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$otp = $request->has('otp') ? $request->get('otp') : null;
			$txnRef = $request->has('txnRef') ? $request->get('txnRef') : null;
			$cardType = $request->has('cardType') ? $request->get('cardType') : null;
			$number = $request->has('number') ? $request->get('number') : null;
			$cvv = $request->has('cvv') ? $request->get('cvv') : null;
			$exp = $request->has('exp') ? $request->get('exp') : null;
			$fee = $request->has('fee') ? $request->get('fee') : null;
			$token = $request->has('token') ? $request->get('token') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$transaction = \App\Transaction::where('transactionRef', '=', $txnRef)->where('transactionRef', '=', $txnRef)->first();
			if($transaction==null)
			{
				return response()->json(['status'=>-1, 'Invalid Transaction. Try again']);
			}
			
			$data = json_decode($transaction->requestData);
			
			
			$paymentItem = array();
			$paymentItem[0] = urlencode('Payment for trip|'.$user->name.'|'.$user->mobileNumber);
			$amount[0] = number_format($fee, 2, '.', '');
			$totalAmount = number_format($fee, 2, '.', '');
			
			
					
			$params = array();
			$params['cardnum'] = $number;
			$params['cvv'] = $cvv;
			$params['expdate'] = $exp;
			$params['payerName'] = $user->name;
			$params['payerEmail'] = $user->email;
			$params['payerPhone'] = $user->mobileNumber;
			$params['amount'] = $amount;
			$params['responseurl'] = 'http://192.168.43.136/payments/handle-response-success';
			$params['orderId'] = $txnRef;
			$params['merchantId'] = PAYMENT_MERCHANT_ID;
			$params['serviceTypeId'] = '1981511018900';
			$params['deviceCode'] = PAYMENT_DEVICE_ID;
			
			$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
					$params['orderId'].$totalAmount.$params['responseurl'].PAYMENT_API_KEY;
					
			$params['hash'] = hash('sha512', $toHash);
			$names = explode(' ', $user->name);
			$params['firstName'] = sizeof($names)>0 ? $names[0] : $user->name;
			$params['lastName'] = sizeof($names)>1 ? $names[1] : $user->name;
			$params['email'] = $user->email;
			$params['phoneNumber'] = $user->mobileNumber;
			$params['streetAddress'] = $user->streetAddress;
			$params['city'] = $user->city;
			$params['district'] = $user->district;
			$params['otp'] = $otp;
			$params['txnRef'] = $txnRef;
			$params['paymentOption'] = "EAGLECARD";
			
			
			$reqPrefs['http']['method'] = 'POST';
			$stream_context = stream_context_create($reqPrefs);
			$url = "http://payments.probasepay.com/payments/process-mobile-eagle-process-payment".
				"?paymentItem=".(json_encode($paymentItem))."&amount=".json_encode($amount).
				"&cardnum=".$number."&expdate=".$exp."&cvv=".$cvv.
				"&payerName=".urlencode($user->name)."&payerEmail=".$user->email."&payerPhone=".$user->mobileNumber.
				"&responseurl=http://192.168.43.136/payments/handle-response-success&orderId=".$transaction->orderId.
				"&hash=".hash('sha512', $toHash)."&merchantId=".PAYMENT_MERCHANT_ID."&serviceTypeId=1981511018900".
				"&firstName=".urlencode(sizeof($names)>0 ? $names[0] : $user->name)."&lastName=".(sizeof($names)>1 ? $names[1] : $user->name).
				"&phoneNumber=".urlencode($user->mobileNumber)."&email=".urlencode($user->email).
				"&streetAddress=".urlencode($user->streetAddress)."&city=".urlencode($user->city).
				"&district=".urlencode($user->district)."&otp=".$otp.
				"&deviceCode=".PAYMENT_DEVICE_ID."&txnRef=".$txnRef."&token=".$token."&paymentOption=EAGLECARD";
			//dd($url);
			//dd($url);
			//$url = 'http://api.football-data.org/v1/competitions/';
			$json = file_get_contents($url, false, $stream_context);
			
			//dd($json);
			
			if(isset($json['status']) && $json['status']==1)
			{
				$transaction->amount = $fee;
				$transaction->status = 'Success';
				$transaction->save();
				
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				$trip->status = 'Completed & Paid';
				$trip->save();
				
				$userDriver = \App\User::where('id', '=', $trip->vehicle_driver_user_id)->first();
				$userDriver->outstanding_balance = $userDriver->outstanding_balance + $fee;
				$userDriver->save();
			}
			else
			{
				$transaction->status = 'Fail';
				$transaction->save();
			}
			return $json;
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	
	}
	
	/***verifyProbaseWallet***/
	public function verifyProbaseWallet()
	{
		return ['status'=>1, 'wallet_token'=>'askdk322323kkfsdf2323kskdfksf2', 'user_id'=>1];
	}
	
	
	/***getTrips***/
	public function getTrips()
	{
		
        try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$trips = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereNotIn('status', ['Pending','Going'])->orderBy('created_at', 'DESC')->get();

			
			if($trips!=null)
			{
				$trip__ = [];
				$ij = 0;
				foreach($trips as $trip)
				{
					
						$bgColor = $ij%2==0 ? 0 : 1;
						$origin['vicinity'] = $trip->origin_vicinity;
						$dest['vicinity'] = $trip->destination_vicinity;
						$trip_ = [];
						$pkdUpAt = "";
						$time = "";
						if($trip->status=="Completed")
						{
							$pkdUpAt = "Completed. Dropped Off - ".date('M d, Y', strtotime($trip->droppedOffAt));
							$time = date('H:i', strtotime($trip->droppedOffAt))."HRS";

							if($trip->paidYes==1)
							{
								/*"Completed. Dropped Off - ".*/
								$pkdUpAt = date('M d, Y', strtotime($trip->droppedOffAt));
								$time = date('H:i', strtotime($trip->droppedOffAt))."HRS";

							}
							
						}
						else if($trip->status=="Completed & Paid")
						{
							/*$pkdUpAt = "Completed. Dropped Off - ".*/
							$pkdUpAt = date('M d, Y', strtotime($trip->droppedOffAt));
							$time = date('H:i', strtotime($trip->droppedOffAt))."HRS";

							/*if($trip->paidYes==1)
							{
								$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							}*/
							
						}
						
						$trip_=['status'=>$trip->status, 'time'=>$time, 'pickedUpAt'=>$pkdUpAt, 'payment_method'=>$trip->payment_method, 'vehicle_type'=>$trip->vehicle_type,
							'origin'=>$origin, 'destination'=>$dest, 'bookId'=>$trip->trip_identifier, 'id'=>$trip->id, 
							'photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 'updated_at'=>date('M d, Y', strtotime($trip->updated_at)),
							'name'=>$trip->vehicle_driver_user_name, 'currency'=>$trip->currency, 'fee'=>$trip->amount_chargeable, 'feeFormatted'=>number_format($trip->amount_chargeable, 2, '.', ','), 
							'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 'bgColor'=>$bgColor, 
							'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
						array_push($trip__, $trip_);
					
				}
				
				return response()->json(['status'=>1, 'trips'=>$trip__]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	/***getTrips***/
	public function getTripsOfDriver()
	{
		
        try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$tripsSql = "Select COUNT(id) as cid, YEAR(created_at) as yr, MONTH(created_at) as mnt from trips where vehicle_driver_user_id = ".$user->id." AND status Not In ('Pending') GROUP BY YEAR(created_at), MONTH(created_at) DESC";
			$trips = \DB::select($tripsSql);
			//
			$all_trips = [];
			$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			foreach($trips as $trip)
			{
				$yr = $trip->yr;
				$mnt = $trip->mnt;
				$all_trips[$yr][$mnt] = ['total' => $trip->cid];
				$all_trips[$yr]['total'] = (isset($all_trips[$yr]['total']) ? $all_trips[$yr]['total'] : 0) + $trip->cid;
			}
			
			$total_balance = 0;
		
            $trips = \DB::table('trips')->where('vehicle_driver_user_id', '=', $user->id)->whereNotIn('status', ['Pending'])->orderBy('created_at', 'DESC')->get();
			
			if($trips!=null)
			{
				$trip__ = [];
				$ij = 0;
				foreach($trips as $trip)
				{
					if($ij<15)
					{
						$bgColor = $ij%2==0 ? 0 : 1;
						$origin['vicinity'] = $trip->origin_vicinity;
						$dest['vicinity'] = $trip->destination_vicinity;
						$trip_ = [];
						$pkdUpAt = "";
						$pkdUpAt1 = "";
						if($trip->status=="Pending")
						{
							$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
							$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
						}
						else if($trip->status=="Going")
						{
							$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
							$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
						}
						else if($trip->status=="Passenger Canceled")
						{
							$pkdUpAt = "You canceled trip";
							$pkdUpAt1 = "You canceled trip";
						}
						else if($trip->status=="Driver Canceled")
						{
							$pkdUpAt = "Drivers canceled trip";
							$pkdUpAt1 = "Drivers canceled trip";
						}
						else if($trip->status=="Completed")
						{
							$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							if($trip->paidYes==1)
							{
								$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
								$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
								if($trip->payment_method=='Card')
								{
									$total_balance = $total_balance + $trip->amount_chargeable;
								}
							}
							
						}
						else if($trip->status=="Completed & Paid")
						{
							$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							if($trip->paidYes==1)
							{
								$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
								$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
								if($trip->payment_method=='Card')
								{
									$total_balance = $total_balance + $trip->amount_chargeable;
								}
							}
							
						}
						
						$trip_=['pickedUpAt'=>$pkdUpAt, 'pickedUpAt1' =>$pkdUpAt1, 'status'=>$trip->status,
							'origin'=>$origin, 'destination'=>$dest, 'bookId'=>$trip->trip_identifier, 
							'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'paymentMethod'=>$trip->payment_method,
							'name'=>$trip->vehicle_driver_user_name, 'currency'=>$trip->currency, 'fee'=>$trip->amount_chargeable, 
							'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 'bgColor'=>$bgColor, 
							'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
						array_push($trip__, $trip_);
					}
					$ij++;
				}
				
				return response()->json(['status'=>1, 'stats'=>$all_trips, 'trips'=>$trip__, 'balance'=>$user->outstanding_balance]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	/*getTripsOfDriverForWallet*/
	public function getTripsOfDriverForWallet()
	{
		
        try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$tripsSql = "Select COUNT(id) as cid, YEAR(created_at) as yr, MONTH(created_at) as mnt from trips where 
				vehicle_driver_user_id = ".$user->id." AND status In ('Completed & Paid') GROUP BY YEAR(created_at), MONTH(created_at) DESC";
			$trips = \DB::select($tripsSql);
			//
			$all_trips = [];
			$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			foreach($trips as $trip)
			{
				$yr = $trip->yr;
				$mnt = $trip->mnt;
				$all_trips[$yr][$mnt] = ['total' => $trip->cid];
				$all_trips[$yr]['total'] = (isset($all_trips[$yr]['total']) ? $all_trips[$yr]['total'] : 0) + $trip->cid;
			}
			
			$total_balance = 0;
		
            $trips = \DB::table('trips')->where('vehicle_driver_user_id', '=', $user->id)->whereIn('status', ['Completed & Paid'])->orderBy('created_at', 'DESC')->get();
			
			if($trips!=null)
			{
				$trip__ = [];
				$ij = 0;
				foreach($trips as $trip)
				{
					if($ij<15)
					{
						$bgColor = $ij%2==0 ? 0 : 1;
						$origin['vicinity'] = $trip->origin_vicinity;
						$dest['vicinity'] = $trip->destination_vicinity;
						$trip_ = [];
						$pkdUpAt = "";
						$pkdUpAt1 = "";
						if($trip->status=="Pending")
						{
							$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
							$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
						}
						else if($trip->status=="Going")
						{
							$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
							$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
						}
						else if($trip->status=="Passenger Canceled")
						{
							$pkdUpAt = "You canceled trip";
							$pkdUpAt1 = "You canceled trip";
						}
						else if($trip->status=="Driver Canceled")
						{
							$pkdUpAt = "Drivers canceled trip";
							$pkdUpAt1 = "Drivers canceled trip";
						}
						else if($trip->status=="Completed")
						{
							$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							if($trip->paidYes==1)
							{
								$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
								$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
								if($trip->payment_method=='Card')
								{
									$total_balance = $total_balance + $trip->amount_chargeable;
								}
							}
							
						}
						else if($trip->status=="Completed & Paid")
						{
							$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							if($trip->payment_method=='Card')
							{
								$total_balance = $total_balance + $trip->amount_chargeable;
							}
						}
						
						$trip_=['pickedUpAt'=>$pkdUpAt, 'pickedUpAt1' =>$pkdUpAt1,
							'origin'=>$origin, 'destination'=>$dest, 'bookId'=>$trip->trip_identifier, 
							'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'paymentMethod'=>$trip->payment_method,
							'name'=>$trip->vehicle_driver_user_name, 'currency'=>$trip->currency, 'fee'=>$trip->amount_chargeable, 
							'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 'bgColor'=>$bgColor, 
							'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
						array_push($trip__, $trip_);
					}
					$ij++;
				}
				
				return response()->json(['status'=>1, 'stats'=>$all_trips, 'trips'=>$trip__, 'balance'=>$user->outstanding_balance]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	



	public function getTransactionsOfDriver()
	{
		
        	try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$transactions = \App\Transaction::where('driverUserId', '=', $user->id)->orderBy('created_at', 'DESC')->get();

			if($transactions->count()>0)
				return response()->json(['status'=>1, 'transactions'=>$transactions]);
			else
				return response()->json(['status'=>0]);
        	}
		catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>422]);
        	}
	}
	
	
	/***getTrips***/
	public function widthDraw(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
        try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$amount = $request->get('amount');
			
			
			$total_balance = $user->outstanding_balance;
            //$trips = \DB::table('trips')->where('vehicle_driver_user_id', '=', $user->id)->where('paidYes', '=', 1)->where('status', '=', 'Completed')->get();
			if($total_balance > $amount)
			{
				$withdrawalRequest = new \App\WithdrawalRequest();
				$withdrawalRequest->amount = $amount;
				$withdrawalRequest->driver_user_id = $user->id;
				$withdrawalRequest->details = "Withdrawal Request of ZMW".$amount." by ".$user->name;
				$withdrawalRequest->status = 'Pending';
				$withdrawalRequest->user_name = $user->name;
				$withdrawalRequest->user_mobile = $user->mobileNumber;
				$withdrawalRequest->request_id = join('-', str_split(strtoupper(str_random(12)), 4));
				$withdrawalRequest->save();
				
				$user->outstanding_balance = $total_balance - $amount;
				$user->save();
				return response()->json(['status'=>1, 'balance'=>($total_balance - $amount), 'message'=>'Withdrawal Request Successful. Your requested amount will be paid into your specified account on our next payout date']);
			}
			return response()->json(['status'=>0, 'message'=>'Withdrawal request was not successful. You can not withdraw above your current balance']);
		
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>-1, 'balance'=>$total_balance, 'message'=>'Token Expired']);
        }
	}
	
	public function getUserSettings(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
        	try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);

			
			$us = \App\User::where('id', '=', $user->id)->first();
			return response()->json(['status'=>1, 'enablePin'=>$us->pin==null ? 0 : 1, 'enable_trip_code'=>$us->enable_trip_code, 'turn_off_advert'=>$us->turn_off_advert]);
		
        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'message'=>'Token Expired']);
        	}
	}


	public function enableTripCodeAction(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
        	try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$enableTripCode = $request->get('enableTripCode');
			
			$us = \App\User::where('id', '=', $user->id)->first();
			$us->enable_trip_code = $enableTripCode;
			$us->save();
			return response()->json(['status'=>1, 'message'=>'Trip code validation from Driver enabled']);
		
        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'balance'=>$total_balance, 'message'=>'Token Expired']);
        	}
	}
	
	public function enablePinAction(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
        	try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$enablePin= $request->get('enablePin');
			
			$us = \App\User::where('id', '=', $user->id)->first();
			$us->pin = $enablePin==1 ? mt_rand(1000, 9999) : null;
			$us->save();


			$msg = "Your new Tweende pin is ".$us->pin;
			try
			{
				send_sms($us->mobileNumber, $msg, "Bevura");
			}
			catch(\Exception $e)
			{

			}

			return response()->json(['status'=>1, 'message'=>'Pin enabled successfully']);
		
        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'balance'=>$total_balance, 'message'=>'Token Expired']);
        	}
	}

	public function turnOffAdvertsAction(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
        	try {
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$turnOffAdvert = $request->get('turnOffAdvert');
			
			$us = \App\User::where('id', '=', $user->id)->first();
			$us->turn_off_advert = $turnOffAdvert;
			$us->save();
			return response()->json(['status'=>1, 'message'=>'Adverts turned off']);
		
        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>-1, 'balance'=>$total_balance, 'message'=>'Token Expired']);
        	}
	}
	
	/***sendSupportMessage***/
	public function sendSupportMessage(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
		
        try {
			$token = JWTAuth::getToken();
			$feedBack = $request->get('feedBack');
			
			$user = JWTAuth::toUser($token);
            $supportMessage = new \App\SupportMessage();
			$supportMessage->message = $feedBack;
			$supportMessage->user_id = $user->id;
			$supportMessage->user_full_name = $user->name;
			$supportMessage->user_email = $user->email;
			$supportMessage->user_phone = $user->mobileNumber;
			$supportMessage->user_role = $user->role_code;
			
			if($supportMessage->save())
			{
				return response()->json(['status'=>1, 'message'=>'Thank you for sending us a message. Our support team will reach out to you if necessary']);
			}
			else
			{
				return response()->json(['status'=>0, 'message'=>'Your message could not be sent successfully']);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	
	/***DRIVER-BASED SERVICE CALLS START HERE***/
	/***getDealForDriver***/
	public function getDealForDriver(\App\Http\Requests\BaseTaxiRequest $request) 
	{
        try {
            $token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$driverId 	= $request->has('driverId') ? $request->get('driverId') : null;
			$dealId = $request->has('dealId') ? $request->get('dealId') : null;
			$sql = 'SELECT *, v2.status as status, v2.id as driverDealId, v1.id as vehicleTrackerId FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE 
				v1.vehicle_id = v2.vehicle_id AND v2.driver_user_id = '.$driverId;
			if($dealId!=null)
			{
				$sql = $sql.' AND v2.id = '.$dealId;
			}
			$sql = $sql.' AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Completed", "Accepted", "Going"))) ORDER BY v2.id DESC LIMIT 0, 1';
			$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
			$vehicleTypeKeys = array_keys($vehicleIcons);
			
				
				
				//updatePositionrefactor code. Interval being greater than or less than is dependent on status
				
			
			$deals = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0, 'deals'=>[], 'sql'=>$sql];
			if(sizeof($deals)>0)
			{
				$dl = [];
				//if($deals[0]->status=='Pending')
				//{
					$vehicleTypeIcon = in_array($deals[0]->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$deals[0]->vehicle_type] : $vehicleIcons['Taxi'];
					$originLocation = ['lng'=>floatval($deals[0]->origin_longitude), 'lat'=>floatval($deals[0]->origin_latitude)];
					$destLocation = ['lng'=>floatval($deals[0]->destination_longitude), 'lat'=>floatval($deals[0]->destination_latitude)];
					$origin=['location'=>$originLocation, 'vicinity'=>$deals[0]->origin_locality];
					$destination=['location'=>$destLocation, 'vicinity'=>$deals[0]->destination_locality];
					$dl = ['tripId'=> $deals[0]->trip_id, 'status'=>$deals[0]->status, 'createdAt'=>strtotime($deals[0]->created_at), 
						'origin'=>$origin, 'destination'=>$destination, 'driverDealId'=>$deals[0]->driverDealId, 'id'=>$deals[0]->driverDealId, 
						'vehicleTrackerId'=>$deals[0]->vehicleTrackerId, 'deal_status'=>$deals[0]->status, 'icon'=>$vehicleTypeIcon, 'fee'=>$deals[0]->fee, 'currency'=>'ZMW'];
				//}
					
				$trip_ = [];
				if($deals[0]->status=='Accepted')
				{
					$tripQuery = \App\Trip::where('id', '=', $deals[0]->trip_id)->first();
					$origin = ['vicinity'=>$deals[0]->origin_locality];
					$dest = ['vicinity'=>$deals[0]->destination_locality];
					$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
								'payment_method'=>$tripQuery->payment_method,
								'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
								'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
								'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deals[0]->driver_user_id, 'droppedOffAt'=>'', 'driverDealStatus'=>'Accepted', 'tripStatus'=>$tripQuery->status,
								'id'=>$tripQuery->id, 'driver_starting_location_latitude'=>$tripQuery->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$tripQuery->driver_starting_location_longitude];
				}
				else if($deals[0]->status=='Going')
				{
					$tripQuery = \App\Trip::where('id', '=', $deals[0]->trip_id)->first();
					
					$origin = ['vicinity'=>$deals[0]->origin_locality];
					$dest = ['vicinity'=>$deals[0]->destination_locality];
					$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
								'payment_method'=>$tripQuery->payment_method,
								'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
								'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
								'status'=>'Pending', 'pickedUpAt'=>date('Y-m-d H:i', strtotime($tripQuery->pickedUpAt)), 'currency'=>"ZMW", 'driverId'=>$deals[0]->driver_user_id, 'droppedOffAt'=>'', 'driverDealStatus'=>'Accepted', 'tripStatus'=>$tripQuery->status,
								'id'=>$tripQuery->id, 'driver_starting_location_latitude'=>$tripQuery->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$tripQuery->driver_starting_location_longitude];
				}
				else if($deals[0]->status=='Completed')
				{
					$tripQuery = \App\Trip::where('id', '=', $deals[0]->trip_id)->first();
					
					$origin = ['vicinity'=>$deals[0]->origin_locality];
					$dest = ['vicinity'=>$deals[0]->destination_locality];
					$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
								'payment_method'=>$tripQuery->payment_method,
								'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
								'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
								'status'=>'Pending', 'pickedUpAt'=>date('Y-m-d H:i', strtotime($tripQuery->pickedUpAt)), 'currency'=>"ZMW", 'driverId'=>$deals[0]->driver_user_id, 'droppedOffAt'=>'', 'driverDealStatus'=>'Accepted', 'tripStatus'=>$tripQuery->status,
								'id'=>$tripQuery->id, 'driver_starting_location_latitude'=>$tripQuery->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$tripQuery->driver_starting_location_longitude];
				}
				$accts = ['status' =>1, 'deals'=>$dl, 'trip'=>$trip_];
			}
			
			return response()->json($accts);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	/***acceptJob***/
	public function acceptJob(\App\Http\Requests\BaseTaxiRequest $request)
	{
		
        try {
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$dealId = $request->has('dealId') ? $request->get('dealId') : null;
			$driverId = $request->has('driverId') ? $request->get('driverId') : null;
			$accts = ['status' =>0];
			if($dealId!=null)
			{
				date_default_timezone_set('Africa/Lusaka');
				$now = date_create(date('Y-m-d H:i'));
				$now = date_sub($now,date_interval_create_from_date_string("10 minutes"));
				$nowMinus10= date_format($now,"Y-m-d H:i");
				
				$sql = 'SELECT *, v2.status as status, v2.id as driverDealId, v1.id as vehicleTrackerId, v1.current_latitude, v1.current_longitude FROM `vehicle_trackers` v1,`driver_deals` v2, 
				`vehicles` v3 WHERE 
				v2.id = '.$dealId.' AND v2.updated_at > "'.$nowMinus10.'" AND 
				v1.vehicle_id = v2.vehicle_id AND 
				v1.vehicle_id = v3.id AND v2.driver_user_id = '.$driverId." LIMIT 0, 1";
				$deal = \DB::select($sql);

				if($deal==null)
				{
					$dealReceipients = [];
					$driverDeal = \App\DriverDeal::where('id', '=', $dealId)->first();
					if($driverDeal!=null)
					{
						$driverDeal->status='Timed Out';
						$driverDeal->save();
						
					}
					$accts = ['status' =>101, 'message'=>'This trip request has timed out', 'sql'=>$sql];

					
					

					return response()->json($accts);
				}
			
				$deal = $deal[0];
				
				
				if($deal!=null)
				{
					$veh = \App\Vehicle::where('id', '=', $deal->vehicle_id)->first();
					$vehTy = \App\VehicleType::where('id', '=', $veh->vehicle_type_id)->first();
					$checkTrip = \App\Trip::where('deal_booking_group_id', '=', $deal->booking_group_id);
					//->whereNotIn('vehicle_id', [$deal->vehicle_id]);
					if($checkTrip->count()>0)
					{
						$checkTrip = $checkTrip->first();
						if($checkTrip->vehicle_id == $deal->vehicle_id)
						{
							
							$location=['lat'=>$deal->origin_latitude, 'lng'=>$deal->origin_longitude];
							$origin = ['vicinity'=>$deal->origin_locality, 'location'=>$location];
							$destlocation=['lat'=>$deal->destination_latitude, 'lng'=>$deal->destination_longitude];
							$dest = ['vicinity'=>$deal->destination_locality, 'location'=>$destlocation];
					
					
					
							$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
							$vehicleTypeKeys = array_keys($vehicleIcons);
							$vehicleTypeIcon = in_array($checkTrip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$checkTrip->vehicle_type] : $vehicleIcons['Taxi'];
							$trip_ = ['trip_identifier'=>$checkTrip->trip_identifier, 'origin_vicinity'=>$checkTrip->origin_vicinity, 
								'payment_method'=>$checkTrip->payment_method,
								'destination_vicinity'=>$checkTrip->destination_vicinity, 'fee'=>number_format($checkTrip->amount_chargeable, 2, '.', ','), 
								'driver_deal_id'=>$checkTrip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$checkTrip->passenger_user_id, 
								'status'=>$checkTrip->status, 'pickedUpAt'=>$checkTrip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$checkTrip->vehicle_driver_user_id, 
								'droppedOffAt'=>$checkTrip->droppedOffAt, 'id'=>$checkTrip->id, 'driverDealStatus'=>$deal->status, 
								'bookId'=>$checkTrip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$checkTrip->vehicle_driver_photo, 
								'name'=>$checkTrip->vehicle_driver_user_name, 'note'=>$checkTrip->notes, 'rating'=>$checkTrip->vehicle_driver_rating, 'paidYes'=>$checkTrip->paidYes, 
								'profile_pix'=>"http://192.168.43.136/users/".$checkTrip->vehicle_driver_photo, 'plate'=>$checkTrip->vehicle_plate_number, 'type'=>$checkTrip->vehicle_type, 
								'icon'=>$vehTy->icon, 'driver_starting_location_latitude'=>$checkTrip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$checkTrip->driver_starting_location_longitude];
							$accts = ['status' =>1, 'id'=>$checkTrip->id, 'trip'=>$trip_];


							



							$token = JWTAuth::getToken();
							$tripId 	= $checkTrip->id;
							
			
							$trip = $checkTrip;

							$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
							$locationDes=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];

							$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
							$destination = ['vicinity'=>$trip->destination_vicinity, 'location'=>$locationDes];
							$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
								'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
								'destination'=> $destination, 'status'=> $trip->status, 'id'=>$trip->id, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				
							$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
							$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
								'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
								'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];
							$recl = [];
							$recl['DEALACCEPTED'.$checkTrip->passenger_user_id] = ['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'tripData'=>$trip];
							$data1 = [];
							$data1['recL'] = $recl;
							$data1['status'] = 1;
							$data1['messageType'] = 'DEAL ACCEPTED';
							$data = json_encode($data1);
							$data = 'data='.urlencode($data);

							$jk = new \App\Junk();
							$jk->data=$data;
							$jk->save();


							$url = "http://140.82.52.195:8080/post-job-accepted";
							//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
							$server_output = sendPostRequestForBevura($url, $data);

							
							return response()->json($accts);
						}
						else
						{
							$accts = ['status' =>0, 'message'=>'This job has already been assigned to another driver. You responded late to this trip request'];
							return response()->json($accts);
						}
						
					}
					
					
					$sql = 'SELECT * FROM `trips` v1 WHERE 
						v1.vehicle_driver_user_id = '.$driverId.' AND 
						v1.origin_longitude = '.$deal->origin_longitude.' AND 
						v1.origin_latitude = '.$deal->origin_latitude.' AND 
						v1.destination_longitude = '.$deal->destination_longitude.' AND 
						v1.destination_latitude = '.$deal->destination_latitude.' AND 
						v1.vehicle_id = '.$deal->vehicle_id.' AND 
						v1.passenger_user_id = '.$deal->passenger_user_id.' AND 
						v1.driver_deal_id = '.$deal->id.' AND 
						v1.status IN ("Pending", "Going") LIMIT 0, 1';
					
					$tripQuery = \DB::select($sql);
					if(sizeof($tripQuery)>0)
					{
						
						$tripQuery = $tripQuery[0];
						
						$location=['lat'=>$tripQuery->origin_latitude, 'lng'=>$tripQuery->origin_longitude];
						$origin = ['vicinity'=>$tripQuery->origin_locality, 'location'=>$location];
						$destlocation=['lat'=>$tripQuery->destination_latitude, 'lng'=>$tripQuery->destination_longitude];
						$dest = ['vicinity'=>$tripQuery->destination_locality, 'location'=>$destlocation];
				
						
						$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
						$vehicleTypeKeys = array_keys($vehicleIcons);
						$vehicleTypeIcon = in_array($tripQuery->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$tripQuery->vehicle_type] : $vehicleIcons['Taxi'];
						
						
						
						
						$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
							'payment_method'=>$tripQuery->payment_method,
							'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
							'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
							'status'=>$tripQuery->status, 'pickedUpAt'=>$tripQuery->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$tripQuery->vehicle_driver_user_id, 
							'droppedOffAt'=>$tripQuery->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$tripQuery->status, 'id'=>$tripQuery->id, 
							'bookId'=>$tripQuery->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$tripQuery->vehicle_driver_photo, 
							'name'=>$tripQuery->vehicle_driver_user_name, 'note'=>$tripQuery->notes, 'rating'=>$tripQuery->vehicle_driver_rating, 'paidYes'=>$tripQuery->paidYes, 
							'profile_pix'=>"http://192.168.43.136/users/".$tripQuery->vehicle_driver_photo, 'plate'=>$tripQuery->vehicle_plate_number, 'type'=>$tripQuery->vehicle_type, 
							'icon'=>$tripQuery->vehicle_icon, 'driver_starting_location_latitude'=>$tripQuery->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$tripQuery->driver_starting_location_longitude];
						$accts = ['status' =>($tripQuery->status=='Pending' ? 1 : 2), 'id'=>$tripQuery->id, 'trip'=>$trip_];


							


							$token = JWTAuth::getToken();
							$tripId 	= $tripQuery->id;
							
			
							$trip = $tripQuery ;

							$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
							$locationDes=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];

							$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
							$destination = ['vicinity'=>$trip->destination_vicinity, 'location'=>$locationDes];
							$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
								'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
								'destination'=> $destination, 'status'=> $trip->status, 'id'=>$trip->id, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				
							$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
							$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
								'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
								'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];
							

							$recl = [];
							$recl['DEALACCEPTED'.$deal->passenger_user_id] = ['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'tripData'=>$trip];
							$data1 = [];
							$data1['recL'] = $recl;
							$data1['status'] = 1;
							$data1['messageType'] = 'DEAL ACCEPTED';
							$data = json_encode($data1);
							$data = 'data='.urlencode($data);


							$jk = new \App\Junk();
							$jk->data=$data;
							$jk->save();

							$url = "http://140.82.52.195:8080/post-job-accepted";
							//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
							$server_output = sendPostRequestForBevura($url, $data);



						return response()->json($accts);
					}
					
					$passengerUser = \App\User::where('id', '=', $deal->passenger_user_id)->first();
					
					$driverUserN = \App\User::where('id', '=', $driverId)->first();
					$trip = new \App\Trip();
					$trip->trip_identifier = strtoupper(str_random(9));
					$trip->origin_longitude = $deal->origin_longitude;
					$trip->origin_latitude = $deal->origin_latitude;
					$trip->destination_longitude = $deal->destination_longitude;
					$trip->destination_latitude = $deal->destination_latitude;
					$trip->vehicle_id = $deal->vehicle_id;
					$trip->vehicle_driver_user_id = $deal->driver_user_id;
					$trip->vehicle_driver_user_name = $deal->user_full_name;
					$trip->amount_chargeable = $deal->fee;
					$trip->extra_charges = 0.00;
					$trip->passenger_user_id = $deal->passenger_user_id;
					$trip->passenger_user_name = $deal->passenger_user_full_name;
					$trip->payment_method = $deal->payment_method;
					$trip->driver_deal_id = $deal->driverDealId;
					$trip->status = 'Pending';
					$trip->drive_rating = null;
					$trip->notes = $deal->note;
					$trip->vehicle_driver_photo = $deal->vehicle_driver_photo;
					$trip->vehicle_driver_rating = $deal->avg_system_rating;
					$trip->vehicle_plate_number = $deal->vehicle_plate_number;
					$trip->vehicle_type = $deal->vehicle_type;
					$trip->currency = "ZMW";
					$trip->origin_vicinity = $deal->origin_locality;
					$trip->destination_vicinity = $deal->destination_locality;
					$trip->pickedUpAt = null;
					$trip->deal_booking_group_id = $deal->booking_group_id;
					$trip->phone_number = $deal->phone_number;
					$trip->vehicle_icon = $vehTy->icon;
					$trip->driver_starting_location_latitude = $deal->current_latitude;
					$trip->driver_starting_location_longitude = $deal->current_longitude;
					$trip->vehicle_driver_photo = $driverUserN->passport_photo;
					$trip->travel_time = $deal->travel_time;
					$trip->is_arrived = 0;
					$trip->passenger_rating = 0;
					$trip->previous_passenger_rating = $passengerUser!=null ? $passengerUser->userRating : 0;
					if($trip->save())
					{
						$vehicle = \App\Vehicle::where('id', '=', $trip->vehicle_id)->first();
						$vehicle->status = 'In Use';
						$vehicle->save();
						
						$driverDeal = \App\DriverDeal::where('id', '=', $dealId)->whereIn('status', ['Pending', 'Accepted'])->first();
						if($driverDeal==null)
						{
							$accts = ['status' =>0];
							return response()->json($accts);
						}
						if($driverDeal->status=='Pending')
						{
							$driverDeal->status = 'Accepted';
							$driverDeal->trip_id = $trip->id;
							$driverDeal->save();
							
							/*$tripGrp = new \App\TripDealGroup();
							$tripGrp->booking_group_id = $deal->booking_group_id;
							$tripGrp->save();*/
							
							$driverDeals = \App\DriverDeal::whereNotIn('id', [$dealId])
								->where('booking_group_id', '=', $deal->booking_group_id)->whereIn('status', ['Pending'])->get();
							$dealReceipients = [];
							foreach($driverDeals as $dd)
							{
								$dd->status = 'Already Taken';
								$dd->save();

								array_push($dealReceipients, $dd->driver_user_id."-".$dealId);
							}


							


							$dealHolder = [];
							$dealHolder['dealsTaken'] = $dealReceipients;
							$data1 = [];
							$data1['recL'] = $dealHolder;
							$data1['status'] = 1;
							$data1['messageType'] = 'DRIVER DEAL TAKEN';
							$data = json_encode($data1);
							$data = 'data='.urlencode($data);

							$jk = new \App\Junk();
							$jk->data=$data;
							$jk->save();

							$url = "http://140.82.52.195:8080/post-driver-deal-taken";
							//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
							$server_output = sendPostRequestForBevura($url, $data);
						}
						
						$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
						$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
						$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
						$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
						
						$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
						$vehicleTypeKeys = array_keys($vehicleIcons);
						$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
				
				
						$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
							'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived, 'travel_time'=>$trip->travel_time,  'passengerName'=>$trip->passenger_user_name,
							'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
							'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
							'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
							'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 
							'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'previousPassengerRating'=>$trip->previous_passenger_rating, 
							'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
							'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
							'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
							
						$accts = ['status' =>($trip->status=='Pending' ? 1 : 2), 'id'=>$trip->id, 'trip'=>$trip_];




							$token = JWTAuth::getToken();
							$tripId 	= $trip->id;
							
			

							$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
							$locationDes=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];

							$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
							$destination = ['vicinity'=>$trip->destination_vicinity, 'location'=>$locationDes];
							$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
								'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
								'destination'=> $destination, 'status'=> $trip->status, 'id'=>$trip->id, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					
				
							$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
							$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
								'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
								'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];
							

							$recl = [];
							$recl['DEALACCEPTED'.$trip->passenger_user_id] = ['status'=>1, 'trip'=>$trip_, 'driver'=>$driver, 'tripData'=>$trip];
							$data1 = [];
							$data1['recL'] = $recl;
							$data1['status'] = 1;
							$data1['messageType'] = 'DEAL ACCEPTED';
							$data = json_encode($data1);
							$data = 'data='.urlencode($data);


							$jk = new \App\Junk();
							$jk->data=$data;
							$jk->save();


							$url = "http://140.82.52.195:8080/post-job-accepted";
							//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
							$server_output = sendPostRequestForBevura($url, $data);



						return response()->json($accts);
					}
					else
					{
						$accts = ['status' =>0, 'message'=>'We experienced issues assigning the trip to you. Our sincere apologies'];
					}
				
					
				}
				else
				{
					$accts = ['status' =>0, 'message'=>'The request from the passenger has either been taken by another driver or was canceled by the passenger'];
				}
			}
			else
			{
				$accts = ['status' =>0, 'message'=>'Invalid trip request received'];
			}
			return response()->json($accts);
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	public function getTripFromDeal(\App\Http\Requests\BaseTaxiRequest $request) 
	{
		$driverId 	= $request->has('driverId') ? $request->get('driverId') : null;
		$originLng 	= $request->has('originLng') ? $request->get('originLng') : null;
		$originLat 	= $request->has('originLat') ? $request->get('originLat') : null;
		$destinationLng 	= $request->has('destinationLng') ? $request->get('destinationLng') : null;
		$destinationLat 	= $request->has('destinationLat') ? $request->get('destinationLat') : null;
		$driverDealId 	= $request->has('driverDealId') ? $request->get('driverDealId') : null;
		$vehicleTrackerId 	= $request->has('vehicleTrackerId') ? $request->get('vehicleTrackerId') : null;
		
		
		
        try {
            
			$token = JWTAuth::getToken();
			$sql = 'SELECT *, v2.id as driverDealId, v1.id as vehicleTrackerId FROM `vehicle_trackers` v1,`driver_deals` v2, 
				`vehicles` v3 WHERE 
				v2.id = '.$driverDealId.' AND 
				v1.vehicle_id = v2.vehicle_id AND 
				v1.vehicle_id = v3.id AND v2.driver_user_id = '.$driverId." LIMIT 0, 1";
			
			
			$deal = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0];
			if(sizeof($deal)>0)
			{
				$sql = 'SELECT * FROM `trips` v1 WHERE 
					v1.vehicle_driver_user_id = '.$driverId.' AND 
					v1.origin_longitude = '.$deal[0]->origin_longitude.' AND 
					v1.origin_latitude = '.$deal[0]->origin_latitude.' AND 
					v1.destination_longitude = '.$deal[0]->destination_longitude.' AND 
					v1.destination_latitude = '.$deal[0]->destination_latitude.' AND 
					v1.vehicle_id = '.$deal[0]->vehicle_id.' AND 
					v1.passenger_user_id = '.$deal[0]->passenger_user_id.' AND 
					v1.driver_deal_id = '.$driverDealId.' AND 
					v1.status IN ("Pending", "Going") LIMIT 0, 1';
				$tripQuery = \DB::select($sql);
				if(sizeof($tripQuery)>0)
				{
					$tripQuery = $tripQuery[0];
					$location=['lat'=>$deal[0]->origin_latitude, 'lng'=>$deal[0]->origin_longitude];
					$origin = ['vicinity'=>$deal[0]->origin_locality, 'location'=>$location];
					$destlocation=['lat'=>$deal[0]->destination_latitude, 'lng'=>$deal[0]->destination_longitude];
					$dest = ['vicinity'=>$deal[0]->destination_locality, 'location'=>$destlocation];
				
				
				
					
					$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
					$vehicleTypeKeys = array_keys($vehicleIcons);
					$vehicleTypeIcon = in_array($tripQuery->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$tripQuery->vehicle_type] : $vehicleIcons['Taxi'];
					
					$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
						'payment_method'=>$tripQuery->payment_method,
						'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
						'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
						'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal[0]->driver_user_id, 'droppedOffAt'=>'', 
						'id'=>$tripQuery->id, 'driverDealStatus'=>$deal[0]->status, 
						'bookId'=>$tripQuery->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$tripQuery->vehicle_driver_photo, 
						'name'=>$tripQuery->vehicle_driver_user_name, 'note'=>$tripQuery->notes, 'rating'=>$tripQuery->vehicle_driver_rating, 'paidYes'=>$tripQuery->paidYes, 
						'profile_pix'=>"http://192.168.43.136/users/".$tripQuery->vehicle_driver_photo, 'plate'=>$tripQuery->vehicle_plate_number, 'type'=>$tripQuery->vehicle_type, 
						'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$tripQuery->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$tripQuery->driver_starting_location_longitude];
					$accts = ['status' =>1, 'id'=>$tripQuery->id, 'trip'=>$trip_];
					return response()->json($accts);
				}
				
				/*$trip = new \App\Trip();
				$trip->trip_identifier = strtoupper(str_random(9));
				$trip->origin_longitude = $deal[0]->origin_longitude;
				$trip->origin_latitude = $deal[0]->origin_latitude;
				$trip->destination_longitude = $deal[0]->destination_longitude;
				$trip->destination_latitude = $deal[0]->destination_latitude;
				$trip->vehicle_id = $deal[0]->vehicle_id;
				$trip->vehicle_driver_user_id = $deal[0]->driver_user_id;
				$trip->vehicle_driver_user_name = $deal[0]->user_full_name;
				$trip->amount_chargeable = $deal[0]->fee;
				$trip->extra_charges = 0.00;
				$trip->passenger_user_id = $deal[0]->passenger_user_id;
				$trip->passenger_user_name = $deal[0]->passenger_user_full_name;
				$trip->payment_method = $deal[0]->payment_method;
				$trip->driver_deal_id = $deal[0]->driverDealId;
				$trip->status = 'Pending';
				$trip->drive_rating = null;
				$trip->notes = $deal[0]->note;
				$trip->vehicle_driver_photo = $deal[0]->vehicle_driver_photo;
				$trip->vehicle_driver_rating = $deal[0]->avg_system_rating;
				$trip->vehicle_plate_number = $deal[0]->vehicle_plate_number;
				$trip->vehicle_type = $deal[0]->vehicle_type;
				$trip->currency = "ZMW";
				$trip->origin_vicinity = $deal[0]->origin_locality;
				$trip->destination_vicinity = $deal[0]->destination_locality;
				$trip->pickedUpAt = null;
				$trip->travel_time = $deal[0]->travel_time;
				$trip->is_arrived = 0;
				if($trip->save())
				{
					$origin = ['vicinity'=>$deal[0]->origin_locality];
					$dest = ['vicinity'=>$deal[0]->destination_locality];
					$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 'payment_method'=>$trip->payment_method,
						'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 'is_arrived'=>$trip->is_arrived, 'previousPassengerRating'=>$trip->previous_passenger_rating, 
						'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 'travel_time'=>$trip->travel_time, 
						'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal[0]->driver_user_id, 'droppedOffAt'=>'', 'id'=>$trip->id,  'passengerName'=>$trip->passenger_user_name];
					$accts = ['status' =>1, 'id'=>$trip->id, 'trip'=>$trip_];
				}*/
				
			}
			
			return response()->json($accts);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	
	/***getPassenger***/
	public function getPassenger(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try{
			$token = JWTAuth::getToken();
			$passengerId = $request->has("passengerId") ? $request->get('passengerId') : null;
			$user = \DB::table('users')->where('id', '=', $passengerId)->first();
			if($user!=null)
			{
				$passenger = ['id'=>$user->id, 'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber,
					'passport_photo'=>"http://192.168.43.136/users/".$user->passport_photo];
				$accts = ['status' =>1, 'passenger'=>$passenger];
				return response()->json($accts);
			}
			$accts = ['status' =>0];
			return response()->json($accts);
		}
		catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	/***dropOffPassenger***/
	public function dropOffPassenger(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$token = JWTAuth::getToken();
			$driverId = $request->has("driverId") ? $request->get('driverId') : null;
			$tripId = $request->has("tripId") ? $request->get('tripId') : null;
			
			
			$trip = \App\Trip::where('id', '=', $tripId)->where('vehicle_driver_user_id', '=', $driverId)->first();
			
			if($trip!=null)
			{
				//dd($trip);
				$deal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();
				
				if($deal!=null)
				{
					date_default_timezone_set('Africa/Lusaka');
					$trip->status = 'Completed';
					$trip->droppedOffAt = date('Y-m-d H:i');
					$trip->save();
					
					$deal->status = 'Completed';
					$deal->save();
					
					$vehicleDB = \DB::table('vehicles')->join('users', 'vehicles.driver_user_id', '=', 'users.id')
						->where('vehicles.driver_user_id', '=', $driverId)->first();
					//dd($vehicleDB);
					
					/*$accts = ['status' =>1, 
						'driver'=>['id'=>$driverId, 'plate'=>$vehicleDB->vehicle_plate_number, 'type'=>$vehicleDB->vehicle_type, 
							'name'=>$vehicleDB->user_full_name, 'refCode'=>$vehicleDB->refCode, 'rating'=>$vehicleDB->avg_system_rating, 
							'balance'=>number_format($vehicleDB->outstanding_balance, 2, '.', ','),  
							'profile_pix'=>"http://192.168.43.136/users/".$vehicleDB->passport_photo, 'tripData'=>$trip, 'fare'=>($trip->amount_chargeable + $trip->extra_charges)]
						];*/
						
						
					
					$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
					$origin = ['vicinity'=>$trip->origin_locality, 'location'=>$location];
					$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
					$dest = ['vicinity'=>$trip->destination_locality, 'location'=>$destlocation];
			
					
					$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
					$vehicleTypeKeys = array_keys($vehicleIcons);
					$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
					
					$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
						'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived,  'passengerName'=>$trip->passenger_user_name,
						'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
						'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
						'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
						'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$deal->status, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 'previousPassengerRating'=>$trip->previous_passenger_rating, 
						'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'travel_time'=>$trip->travel_time, 
						'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
						'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
						'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude];
					



					$data1 = [];
					$recL = [];
					$checkTrip = \App\Trip::where('id', '=', $trip->id)->first();
					$location=['lat'=>$checkTrip->origin_latitude, 'lng'=>$checkTrip->origin_longitude];
					$locationDes=['lat'=>$checkTrip->destination_latitude, 'lng'=>$checkTrip->destination_longitude];
					$origin = ['vicinity'=>$checkTrip->origin_vicinity, 'location'=>$location];
					$destination = ['vicinity'=>$checkTrip->destination_vicinity, 'location'=>$locationDes];
	

					$trip1 = ['driverId' => $checkTrip->vehicle_driver_user_id, 'identifier'=>$checkTrip->trip_identifier,
						'currency'=> $checkTrip->currency, 'fee'=> $checkTrip->amount_chargeable, 'origin'=> $origin, 
						'destination'=> $destination, 'status'=> $checkTrip->status, 'id'=>$checkTrip->id, 
						'driver_starting_location_latitude'=>$checkTrip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$checkTrip->driver_starting_location_longitude];
					
				
					$vehicleTypeIcon = $trip->vehicle_icon;
					$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
						'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
						'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon, 'phoneNumber'=>$trip->phone_number];
					$recl = [];
					

					$fare = $trip->amount_chargeable + $trip->extra_charges;
					$recL[($trip->passenger_user_id."-".$trip->id)] = ['status'=>1, 'trip'=>$trip1, 'driver'=>$driver, 'tripData'=>$checkTrip, 'fare'=>$fare];
					$data1['recL'] = $recL;
					$data1['status'] = 1;
					$data1['messageType'] = 'DESTINATION ARRIVED';
					$data = json_encode($data1);
					$data = 'data='.urlencode($data);
					$url = "http://140.82.52.195:8080/post-driver-arrived-destination";
					//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
					$server_output = sendPostRequestForBevura($url, $data);
					$jk = new \App\Junk();
					$jk->data=$data;
					$jk->save();

	
					return response()->json(['status' =>1, 'trip'=>$trip_]);
					
					
					//return response()->json($accts);
				}
			}
			$accts = ['status' =>0];
			return response()->json($accts);
		}
		catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	


	public function checkPaymentStatus(\App\Http\Requests\BaseTaxiRequest $request)
	{
		 //TODO: authenticate JWT
		$token = null;
       	$acctCount = 0;
		$tripId = $request->get('tripId');
        
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		$trip = \DB::table('trips')->where('id', '=', $tripId)->first();
		return response()->json(['status'=>1, 'trip'=>$trip]);

	}



	
	
	public function getPassengerActiveTrip(\App\Http\Requests\BaseTaxiRequest $request)
	{
		 //TODO: authenticate JWT
		$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
		$vehicleTypeKeys = array_keys($vehicleIcons);
		$token = null;
        $acctCount = 0;
        
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		$vehicleDB = \DB::table('vehicles')->where('driver_user_id', '=', $user->id)->first();
		$driver = [];
		$activePassengerTripId = 0;
		$passengerDeal = null;
		
		if($user->role_code=='PASSENGER')
		{
			$activePassengerTrip = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereIn('status', ['Pending', 'Going', 'Completed'])->orderBy('id', 'DESC')->first();

			if($activePassengerTrip!=null)
			{
				

				$activePassengerTripId = $activePassengerTrip->id;
				$tripStatus = $activePassengerTrip->status;
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$activePassengerTrip ->vehicle_driver_photo, 'name'=>$activePassengerTrip ->vehicle_driver_user_name,
					'rating' => $activePassengerTrip ->vehicle_driver_rating, 'plate'=>$activePassengerTrip ->vehicle_plate_number, 'brand'=>$activePassengerTrip ->vehicle_type, 
					'driverId' => $activePassengerTrip ->vehicle_driver_user_id, 'id'=>$activePassengerTrip ->vehicle_driver_user_id, 'icon'=>$activePassengerTrip->vehicle_icon, 'phoneNumber'=>$activePassengerTrip ->phone_number];	
					
					
				return response()->json(['status'=>1, 'tripStatus'=>$tripStatus, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId]);
			}
			else
			{
				//$activePassengerDeal = \DB::table('driver_deals')->where('passenger_user_id', '=', $user->id)
				//	->whereIn('status', ['Pending', 'Going', 'Accepted'])->orderBy('id', 'DESC')->first();
					
				$sql = 'SELECT * FROM `driver_deals` v2 WHERE 
					v2.passenger_user_id = '.$user->id;
				$sql = $sql.' AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Accepted", "Going"))) ORDER BY v2.id DESC LIMIT 0, 1';
					
				/*$sql = 'SELECT * FROM `driver_deals` WHERE 
					passenger_user_id = '.$user->id.' AND status IN ("Pending") AND 
					DATE_ADD(created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by created_at DESC';*/
					//Created AT = 10:00 + 15 = 10:15	> Now = 10:30 (False) go to home page for new deals
					//Created AT = 10:00 + 15 = 10:15	> Now = 10:14 (true) repeat old deals
				$activePassengerDeal = \DB::select($sql);
				//dd($activePassengerDeal);
				
				$completedCheck = false;
				
				if($activePassengerDeal!=null && sizeof($activePassengerDeal)>0)
				{
					$activePassengerDeal = $activePassengerDeal[0];
					$activePassengerDealVehicle = \DB::table('vehicles')->where('id', '=', $activePassengerDeal->vehicle_id)->first();
					$vehicleTypeIcon = in_array($activePassengerDealVehicle->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$activePassengerDealVehicle->vehicle_type] : $vehicleIcons['Taxi'];
					$currentVehicle = ['vehicleTypeId'=>$activePassengerDealVehicle->vehicle_type_id, 'vehicleType'=>$activePassengerDealVehicle->vehicle_type, 'icon'=>$vehicleTypeIcon];
					$origin = ['location'=>['lat'=>$activePassengerDeal->origin_latitude, 'lng'=>$activePassengerDeal->origin_longitude, 'vicinity'=>$activePassengerDeal->origin_locality]];
					$dest = ['location'=>['lat'=>$activePassengerDeal->destination_longitude, 'lng'=>$activePassengerDeal->destination_longitude, 'vicinity'=>$activePassengerDeal->destination_locality]];
					$passengerDeal = ['locality'=>$activePassengerDeal->origin_locality, 'currentVehicle'=>$currentVehicle, 
						'originLat'=>$activePassengerDeal->origin_latitude, 'originLng'=>$activePassengerDeal->origin_longitude, 
						'origin'=>$origin, 'destination'=>$dest, 'distance'=>$activePassengerDeal->distance, 'fee'=>$activePassengerDeal->fee, 
						'currency'=>"ZMW", 'note'=>$activePassengerDeal->note, 'paymentMethod'=>$activePassengerDeal->payment_method, 'activePassengerDealId'=>$activePassengerDeal->id
					];
					$driver = [];
					
					
					
					
					$jk = new \App\Junk();
					$jk->data=json_encode(['status'=>2, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
						'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'passengerDeal'=>$passengerDeal]);
					$jk->save();
					
					
					$dealStatus = $activePassengerDeal->status;
					return response()->json(['status'=>2, 'token'=>$token, 'dealStatus'=>$dealStatus, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
						'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'passengerDeal'=>$passengerDeal]);
				}
				
				
				$jk = new \App\Junk();
				$jk->data=json_encode(['status'=>1, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);
				$jk->save();
		
				return response()->json(['status'=>0, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver]);
			}
			
		}
		else
		{
			return response()->json(['status'=>422, 'message'=>'Invalid User Account']);
		}
		
			
        

	}
	
	
	
	public function getDriverDealStatus(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			//$user = \App\User::where('id', '=', 4)->first();
			$sql = 'SELECT *, v2.status as status FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE 
				v1.vehicle_id = v2.vehicle_id AND v2.passenger_user_id = '.$user->id.' AND 
				DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by v2.created_at DESC';
			
			
			$deals = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0, 'deals'=>[]];
			if(sizeof($deals)>0)
			{
				$accepted = false;
				$driverCanceled = 0;
				$passengerCanceled = 0;
				$pendingAcceptance = 0;
				$deal_ = null;
				
				foreach($deals as $deal)
				{
					if($deal->status=='Accepted' || $deal->status=='Going')
					{
						$accepted = true;
						$deal_ = $deal;
					}
					else if($deal->status=='Driver Canceled')
					{
						$driverCanceled++;
					}
					else if($deal->status=='Passenger Canceled')
					{
						$passengerCanceled++;
					}
					else if($deal->status=='Pending')
					{
						$pendingAcceptance++;
					}
				}
				
				if($accepted==true)
				{
					$accts = ['status' =>1, 
						'deals'=>['tripId'=>$deal_->trip_id, 'fee'=>$deal_->fee, 'currency'=>'ZMW']
						];
					return response()->json($accts);
				}
				else 
				{
					if($driverCanceled==sizeof($deals) || $passengerCanceled==sizeof($deals))
					{
						$accts = ['status' =>2];
						return response()->json($accts);
					}
					else if($pendingAcceptance==sizeof($deals))
					{
						$accts = ['status' =>3];
						return response()->json($accts);
					}
				}
			}
			$accts = ['status' =>0];
			return response()->json($accts);
		}
		catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
		
	}
	
	
	
	/****getTransactionByTripId****/
	public function getTransactionByTripId(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$token = JWTAuth::getToken();
			$tripId = $request->has("tripId") ? $request->get('tripId') : null;
			$transaction = \DB::table('transactions')->where('tripId', '=', $tripId)->first();
			$accts = ['status' =>0];
			
			if($transaction!=null)
			{
				if($transaction->status=='Success')
				{
					$accts = ['status' =>1, 'id'=>$transaction->id, 'amount'=>$transaction->amount, 'paymentStatus'=>$transaction->status,
						'txnRef'=>$transaction->transactionRef];
				}
				else if($transaction->status=='Fail')
				{
					$accts = ['status' =>2, 'message'=>'Transaction was not successful. Try again'];
				}
				return response()->json($accts);
			}
			
			return response()->json($accts);
		}
		catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
	
	
	public function updateAvailabilityForJob(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			//$user = \App\User::where('id', '=', 3)->first();
			$status = $request->has('status') ? $request->get('status') : null;
			//return $status;
			
			if($status!=null)
			{
				$vehicle = \App\Vehicle::where('driver_user_id', '=', $user->id)->first();
				if($vehicle!=null)
				{
					$vehicleTracker = \App\VehicleTracker::where('vehicle_id', '=', $vehicle->id)->first();
					$vehicleTracker->status = $status=="1" ? 'Available' : 'Unavailable';
					if($vehicleTracker->save())
					{
						$ar = [];
						if($vehicle!=null)
						{
							$vehicle->status = $status==1 ? 'Available' : 'Unavailable';
							$vehicle->save();
							return response()->json(['status'=>1]);
						}
			

						return response()->json(['status'=>1]);
					}
				}
			}
			$accts = ['status' =>0, 'message'=>'We could not update your availability. Try again'];
			return response()->json($accts);
		}
		catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>-1, 'message'=>'Token expired']);
        }
	}
	
	
    


    /***Change Password***/
    public function changepassword(\App\Http\Requests\FTRequest $request) 
	{
	
		try
		{
			$token = JWTAuth::getToken();
			$pswd = $request->get('pswd');
			$npswd = $request->get('npswd');
			$cpswd = $request->get('cpswd');

			if($npswd==$cpswd && strlen($npswd)>0)
			{
				try {

					$user = JWTAuth::toUser($token);
					if(Auth::validate(array('email' => $user->email, 'password' => $pswd)))
					{
						$user->password = Hash::make($npswd);
						$user->save();
					}
					else
					{
						return response()->json(['err' => 'Password change failed. Ensure you provide valid details before you can change your password'], 500);
					}
					$token = JWTAuth::fromUser($user);

					return response()->json(['token' => $token, 'successMessage' => 'Password change was successful.']);

				}catch(TokenExpiredException $e)
				{
					return response()->json(['status'=>422]);
				}
			}else
			{
				//Invalid new password provided
				return response()->json(['err' => 'Password change failed. Ensure you provide valid details before you can change your password'], 500);
			}
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }

    }

	
	
	/***payTripFeeUsingProbaseWallet***/
	public function payTripFeeUsingProbaseWallet(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$fee = $request->has('fee') ? $request->get('fee') : null;
			$cardType = $request->has('cardType') ? $request->get('cardType') : null;
			$number = $request->has('wallet') ? $request->get('wallet') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			$walletAccounts = $request->has('walletAccounts') ? $request->get('walletAccounts') : null;
			$txnToken = $request->has('txnToken') ? $request->get('txnToken') : null;
			$walletAccounts = str_replace('@@@', '---', str_replace('~', ':::', $walletAccounts));
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			
			$trans_ref = strtoupper(join('-', str_split(str_random(16), 4)));
			$paymentItem = array();
			$paymentItem[0] = urlencode('Payment for trip|'.$user->name.'|'.$user->mobileNumber);
			$amount[0] = number_format($fee, 2, '.', '');
			$totalAmount = number_format($fee, 2, '.', '');
			
			
			$params = array();
			$params['merchantId'] = PAYMENT_MERCHANT_ID;
			$params['deviceCode'] = PAYMENT_DEVICE_ID;
			$params['serviceTypeId'] = '1981511018900';
			$params['orderId'] = $trans_ref;
			$params['payerName'] = $user->name;
			$params['payerEmail'] = $user->email;
			$params['payerPhone'] = $user->mobileNumber;
			$params['payerId'] = $user->nrcNumber;
			$params['nationalId'] = $user->nrcNumber;
			$params['scope'] = 'Payment for trip|'.$user->name.'|'.$user->mobileNumber;
			$params['description'] = 'Payment for trip|'.$user->name.'|'.$user->mobileNumber;
			$params['responseurl'] = 'http://192.168.43.136/payments/handle-response-success';
			$params['paymentItem'] = $paymentItem;
			$params['amount'] = $amount;
			$params['currency'] = DEFAULT_CURRENCY;
			$params['wallettoken'] = $number;
			$params['paymentOption'] = "PROBASEPAY-WALLET";
			
			$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
					$params['orderId'].$totalAmount.$params['responseurl'].PAYMENT_API_KEY;
			
			$reqPrefs['http']['method'] = 'POST';
			$stream_context = stream_context_create($reqPrefs);
			$names = explode(' ', $user->name);
			$firstname = sizeof($names)>0 ? $names[0] : $user->name;
			$lastname = sizeof($names)>1 ? $names[1] : '';
			$url = "http://payments.probasepay.com/payments/process-mobile-probase-pay-wallet-process-otp".
				"?merchantId=".PAYMENT_MERCHANT_ID."&deviceCode=".PAYMENT_DEVICE_ID.
				"&serviceTypeId=1981511018900&orderId=".$trans_ref.
				"&payerFirstName=".urlencode($firstname)."&payerLastName=".urlencode($lastname).
				"&payerEmail=".$user->email."&payerStreetAddress=".urlencode($user->streetAddress).
				"&payerCity=".urlencode($user->city)."&payerDistrict=1".
				"&payerPhone=".urlencode($user->mobileNumber)."&payerId=".urlencode($user->nrcNumber).
				"&nationalId=".urlencode($user->nrcNumber)."&scope=".urlencode("Payment for trip|".$user->name."|".$user->mobileNumber).
				"&description=".urlencode("Payment for trip|".$user->name."|").$user->mobileNumber."&responseurl=http://192.168.43.136/payments/handle-response-success".
				"&paymentItem=".(json_encode($paymentItem))."&amount=".json_encode($amount).
				"&currency=".DEFAULT_CURRENCY."&hash=".hash('sha512', $toHash).
				"&wallettoken=".$walletAccounts."&paymentOption=PROBASEPAY-WALLET&txnToken=".$txnToken;
			
			//1---001011679900:::3---001010323444
			//$url = 'http://api.football-data.org/v1/competitions/';
			
			$json = file_get_contents($url, false, $stream_context);
			$json1 = json_decode($json, TRUE);
			
			if($json1['status']==1)
			{
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				
				$txnRef = $json1['txnRef'];
				$txnRefJson = json_decode($txnRef);
				$txnRefs = "";
				$arr = [];
				foreach($txnRefJson as $k => $vl)
				{
					$txn = new \App\Transaction();
					$txn->orderId = $trans_ref;
					$txn->requestData = json_encode($params);
					$txn->status = 'Pending';
					$txn->payeeUserId = $user->id;
					$txn->payeeUserFullName = $user->name;
					$txn->payeeUserMobile = $user->mobileNumber;
					$txn->card_pan = $number;
					$txn->payment_method = 'ProbasePay Wallet';
					$txn->tripId = $tripId;
					$txn->tripOrigin = $trip->origin_vicinity;
					$txn->tripDestination = $trip->destination_vicinity;
					$txn->driverUserId = $trip->vehicle_driver_user_id;
					$txn->vehicleId = $trip->vehicle_id;
					$txn->transactionRef = $vl;
					$txn->amount = $k;
					
					$txn->save();
				}
				
				
				
				
			}
			
			return $json;
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	}
    
	
	/***payTripFeeUsingCardStepTwo***/
	public function payTripFeeUsingProbaseWalletStepTwo(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$otp = $request->has('otp') ? $request->get('otp') : null;
			$txnRef = $request->has('txnRef') ? $request->get('txnRef') : null;
			$cardType = $request->has('cardType') ? $request->get('cardType') : null;
			$number = $request->has('wallet') ? $request->get('wallet') : null;
			$walletAccounts = $request->has('walletAccounts') ? $request->get('walletAccounts') : null;
			$fee = $request->has('fee') ? $request->get('fee') : null;
			$txnToken = $request->has('txnToken') ? $request->get('txnToken') : null;
			$tripId = $request->has('tripId') ? $request->get('tripId') : null;
			$number = str_replace('@@@', '---', str_replace('~', ':::', $number));
			
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			
			$txnRefJson = json_decode($txnRef);
			$txnRefs = "";
			$arr = [];
			foreach($txnRefJson as $k => $vl)
			{
				array_push($arr, $vl);
			}
			$txnRef = join(':::', $arr);
			
			$transaction = \App\Transaction::whereIn('transactionRef', $arr)->where('status', '=', 'Pending')->where('tripId', '=', $tripId);
			if($transaction->count()==0)
			{
				return response()->json(['status'=>-1, 'message'=>'Invalid Transaction. Try again']);
			}
			$transaction = $transaction->first();
			/*$data = json_decode($transaction->requestData);*/
			
			
			$paymentItem = array();
			$paymentItem[0] = urlencode('Payment for trip|'.$user->name.'|'.$user->mobileNumber);
			$amount[0] = number_format($fee, 2, '.', '');
			$totalAmount = number_format($fee, 2, '.', '');
			
			
					
			$params = array();
			$params['wallettoken'] = $number;
			$params['payerName'] = $user->name;
			$params['payerEmail'] = $user->email;
			$params['payerPhone'] = $user->mobileNumber;
			$params['amount'] = $amount;
			$params['responseurl'] = 'http://192.168.43.136/payments/handle-response-success';
			$params['orderId'] = $txnRef;
			$params['merchantId'] = PAYMENT_MERCHANT_ID;
			$params['serviceTypeId'] = '1981511018900';
			$params['deviceCode'] = PAYMENT_DEVICE_ID;
			
			$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
					$params['orderId'].$totalAmount.$params['responseurl'].PAYMENT_API_KEY;
					
			$params['hash'] = hash('sha512', $toHash);
			$names = explode(' ', $user->name);
			$params['firstName'] = sizeof($names)>0 ? $names[0] : $user->name;
			$params['lastName'] = sizeof($names)>1 ? $names[1] : $user->name;
			$params['email'] = $user->email;
			$params['phoneNumber'] = $user->mobileNumber;
			$params['streetAddress'] = $user->streetAddress;
			$params['city'] = $user->city;
			$params['district'] = $user->district;
			$params['otp'] = $otp;
			$params['txnRef'] = $txnRef;
			$params['paymentOption'] = "EAGLECARD";
			
			
			
			$reqPrefs['http']['method'] = 'POST';
			$stream_context = stream_context_create($reqPrefs);
			$url = "http://payments.probasepay.com/payments/process-mobile-probase-pay-wallet-payment".
				"?paymentItem=".(json_encode($paymentItem))."&amount=".json_encode($amount).
				"&txnToken=".$txnToken."&merchantId=".PAYMENT_MERCHANT_ID."&deviceCode=".PAYMENT_DEVICE_ID.
				"&orderId=".$transaction->orderId."&txnRef=".$txnRef."&otp=".$otp;
				/*"&cardnum=".$number."&expdate=".$exp."&cvv=".$cvv.
				"&payerName=".urlencode($user->name)."&payerEmail=".$user->email."&payerPhone=".$user->mobileNumber.
				"&responseurl=http://192.168.43.136/payments/handle-response-success&hash=".hash('sha512', $toHash)."&serviceTypeId=1981511018900".
				"&firstName=".urlencode(sizeof($names)>0 ? $names[0] : $user->name)."&lastName=".(sizeof($names)>1 ? $names[1] : $user->name).
				"&phoneNumber=".urlencode($user->mobileNumber)."&email=".urlencode($user->email).
				"&streetAddress=".urlencode($user->streetAddress)."&city=".urlencode($user->city).
				"&district=".urlencode($user->district).
				"&paymentOption=EAGLECARD";*/
			//dd($url);
			//dd($url);
			//$url = 'http://api.football-data.org/v1/competitions/';
			$json = file_get_contents($url, false, $stream_context);
			$json1 = json_decode($json, TRUE);
			
			
			if(isset($json1['status']) && $json1['status']==1)
			{
				$txnRefs = $json1['txnRef'];
				
				if(1==1)
				{
					$txnRefs = explode(':::', $txnRefs);
				}
				else
				{
					$txnRefs = json_decode($txnRefs);
				}
				foreach($txnRefs as $k => $vl)
				{
					$transaction = \App\Transaction::where('transactionRef', '=', $vl)->first();
					if($transaction!=null)
					{
						$transaction->status = 'Success';
						$transaction->save();
					}
				}
				
				$trip = \App\Trip::where('id', '=', $tripId)->first();
				$trip->status = 'Completed & Paid';
				$trip->save();
				
				$userDriver = \App\User::where('id', '=', $trip->vehicle_driver_user_id)->first();
				$userDriver->outstanding_balance = $userDriver->outstanding_balance + $fee;
				$userDriver->save();
			}
			else
			{
				$transaction->status = 'Fail';
				$transaction->save();
			}
			return $json;
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
	
	}
	
	
	public function authenticateProbasePayWallet(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$wallet_username = $request->has('wallet_username') ? $request->get('wallet_username') : null;
		$wallet_password = $request->has('wallet_password') ? $request->get('wallet_password') : null;
		
		if($wallet_password!=null && $wallet_username!=null)
		{
			$reqPrefs['http']['method'] = 'POST';
			$stream_context = stream_context_create($reqPrefs);
			$url = "http://wallet.probasepay.com/login-wallet-json".
				"?username=".($wallet_username)."&password=".$wallet_password;
			//dd($url);
			//dd($url);
			//$url = 'http://api.football-data.org/v1/competitions/';
			$json = file_get_contents($url, false, $stream_context);
			
			return $json;
		}
		return response()->json(['status'=>0, 'otp'=>str_random(4), 'message'=>'Error logging in. Please try again later']);
	}
	
	
	public function authenticateProbasePayWalletWithOtp(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$otp = $request->has('otp') ? $request->get('otp') : null;
		$walletToken = $request->has('walletToken') ? $request->get('walletToken') : null;
		
		if($otp!=null && $walletToken!=null)
		{
			$reqPrefs['http']['method'] = 'POST';
			$stream_context = stream_context_create($reqPrefs);
			$url = "http://wallet.probasepay.com/otp-login-json?otp=".($otp)."&token=".$walletToken;
			//dd($url);
			//dd($url);
			//$url = 'http://api.football-data.org/v1/competitions/';
			//dd($url);
			try{
				$json = file_get_contents($url, false, $stream_context);
				//dd($json);
				$json = json_decode($json);
				
				if(isset($json->status) && $json->status==1)
				{
					$wallets = $json->wallet_accounts;
					$wallAcc = (json_decode($wallets));
					$str = "";
					for($i=0; $i<sizeof($wallAcc); $i++)
					{
						$str = $str."".$wallAcc[$i]->id."@@@".$wallAcc[$i]->account->accountIdentifier."@@@".$wallAcc[$i]->wallet->walletCode."~";
					}
					return response()->json(['status'=>1, 'wallet_accounts'=>$str, 'token'=>$json->token]);
				}
			}catch(\Exception $e)
			{
				return response()->json(['status'=>0, 'message'=>'Error authenticating your wallet account. Please try again later']);
			}
		}
		return response()->json(['status'=>0, 'message'=>'Error authenticating your wallet account. Please try again later']);
	}
	
	
	
	public function getGoingTripById(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try {
			$jk = new \App\Junk();
			$jk->data="getGoingTripById - ".json_encode($request->all());
			$jk->save();

            		$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$driverId 	= $request->has('driverId') ? $request->get('driverId') : null;
			
			//$trip = \App\Trip::where('id', '=', $tripId)->where('vehicle_driver_user_id', '=', $driverId)->first();
			$trip = \DB::table('trips')
				->join('driver_deals', 'trips.driver_deal_id', '=', 'driver_deals.id')->where('trips.vehicle_driver_user_id', '=', $driverId)
				->whereNotIn('trips.status', ['Completed', 'Driver Canceled', 'Passenger Canceled', 'Completed', 'Completed & Paid', 'Admin Canceled'])->orderBy('trips.created_at', 'DESC')
				->select('trips.*', 'driver_deals.vehicle_id', 'driver_deals.passenger_user_id', 'driver_deals.driver_user_id', 'driver_deals.trip_id'
				, 'driver_deals.origin_locality', 'driver_deals.origin_longitude', 'driver_deals.origin_latitude', 'driver_deals.destination_longitude'
				, 'driver_deals.destination_latitude', 'driver_deals.distance', 'driver_deals.fee', 'driver_deals.note'
				, 'driver_deals.payment_method', 'driver_deals.status as dealStatus', 'driver_deals.destination_locality', 'driver_deals.passenger_user_full_name'
				, 'driver_deals.booking_group_id')->first();
				
			
			
			if($trip!=null)
			{
				if($trip->status=='Going')
				{
					date_default_timezone_set('Africa/Lusaka');
					
				}
				
				$pkdUpAt = "";
				$pkdUpAt1 = "";
				if($trip->status=="Pending")
				{
					$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
					$pkdUpAt1 = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
				}
				else if($trip->status=="Going")
				{
					$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
					$pkdUpAt1 = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
				}
				else if($trip->status=="Passenger Canceled")
				{
					$pkdUpAt = "You canceled trip";
					$pkdUpAt1 = "You canceled trip";
				}
				else if($trip->status=="Driver Canceled")
				{
					$pkdUpAt = "Drivers canceled trip";
					$pkdUpAt1 = "Drivers canceled trip";
				}
				else if($trip->status=="Completed")
				{
					$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
					if($trip->paidYes==1)
					{
						$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						$pkdUpAt1 = "Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
						if($trip->payment_method=='Card')
						{
							$total_balance = $total_balance + $trip->amount_chargeable;
						}
					}
					
				}
				
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
				$destlocation=['lat'=>$trip->destination_latitude, 'lng'=>$trip->destination_longitude];
				$destination = ['vicinity'=>$trip->destination_vicinity, 'location'=>$destlocation];
				
				
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
					
				
						
				$trip_ = ['passenger_number'=>$trip->phone_number, 'trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 
					'payment_method'=>$trip->payment_method, 'is_arrived'=>$trip->is_arrived, 'travel_time'=>$trip->travel_time,  'passengerName'=>$trip->passenger_user_name,
					'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
					'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$destination, 'passenger'=>$trip->passenger_user_id, 
					'status'=>$trip->status, 'pickedUpAt'=>$trip->pickedUpAt, 'currency'=>"ZMW", 'driverId'=>$trip->vehicle_driver_user_id, 
					'droppedOffAt'=>$trip->droppedOffAt, 'driverDealStatus'=>$trip->dealStatus, 'tripStatus'=>$trip->status, 'id'=>$trip->id, 
					'bookId'=>$trip->trip_identifier, 'photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'previousPassengerRating'=>$trip->previous_passenger_rating,
					'name'=>$trip->vehicle_driver_user_name, 'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 
					'profile_pix'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'plate'=>$trip->vehicle_plate_number, 'type'=>$trip->vehicle_type, 
					'icon'=>$vehicleTypeIcon, 'driver_starting_location_latitude'=>$trip->driver_starting_location_latitude, 'driver_starting_location_longitude'=>$trip->driver_starting_location_longitude
				];
					
				$driver = ['photoURL'=>"http://192.168.43.136/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id];
					
				
				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver]);
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }catch(Exception $e)
        {
            return response()->json(['status'=>500, 'e'=>$e->getMessage(), 'e1'=>$e]);
        }
	}



	public function confirmPassengerCancelTrip(\App\Http\Requests\BaseTaxiRequest $request)
	{
		date_default_timezone_set('Africa/Lusaka');
		try {
            		$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$cancelId 	= $request->has('cancelId') ? $request->get('cancelId') : null;
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$latitude	= $request->has('latitude') ? $request->get('latitude') : null;
			$longitude	= $request->has('longitude') ? $request->get('longitude') : null;
			$trip = \App\Trip::where('id', '=', $tripId)->whereIn('status', ['Pending','Going'])->first();

			if($trip!=null)
			{
				$driverDeal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();

				$tripCancelation = new \App\TripCancelation();
				$tripCancelation->trip_id = $tripId;
				$tripCancelation->cancelation_reason_id = $cancelId;
				$tripCancelation->user_id = $user->id;
				$tripCancelation->deal_id = $driverDeal->id;
				$tripCancelation->cancel_latitude = $latitude;
				$tripCancelation->cancel_longitude = $longitude;
				$tripCancelation->save();


				$amount_chargeable = 20.00;
				$trip->status = 'Passenger Canceled';
				$trip->trip_ended = date('Y-m-d H:i:s');
				$trip->amount_chargeable = $amount_chargeable;
				$trip->save();

				$driverDeal->status = 'Passenger Canceled';
				$driverDeal->save();

				$vehicle = \App\Vehicle::where('id', '=', $trip->vehicle_id)->first();
				$vehicle->status = 'Available';
				$vehicle->save();


				$driversToNotify = [];
				$driversToNotify[($trip->vehicle_driver_user_id."")] = $trip->id;
				$data1 = [];
				$data1['recL'] = $driversToNotify;
				$data1['status'] = 1;
				$data1['messageType'] = 'CANCEL DRIVER TRIP BY PASSENGER';
				$data1['charge'] = $amount_chargeable;
				$data = json_encode($data1);
				$data = 'data='.urlencode($data);
				$url = "http://140.82.52.195:8080/post-cancel-trip-by-passenger";
				//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				$jk = new \App\Junk();
				$jk->data=$data;
				$jk->save(); 
			}
			return response()->json(['status'=>1]);
		}
		catch(\Exception $e)
		{
			return response()->json(['status'=>0, 'eee'=>$e]);
		}

	}


	public function getAdvertsRandomly(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try {
            		$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$latitude 	= $request->has('latitude') ? $request->get('latitude') : null;
			$longitude 	= $request->has('longitude') ? $request->get('longitude') : null;
			$adverts = \App\Advert::where('status', '=', 'Active')->inRandomOrder()->limit(5)->get();
			return response()->json(['status'=>1, 'adverts'=>$adverts]);
		}
		catch(\Exception $e)
		{
			return response()->json(['status'=>0]);
		}
	}


	public function getUserData()
	{
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		
		
        	try {
            
			if($user!=null)
			{
				$user = \App\User::where('id', '=', $user->id)->first();
				if($user->role_code=="DRIVER")
				{
					$trips = \App\Trip::whereIn('status', ['Completed', 'Completed & Paid'])->where('vehicle_driver_user_id', '=', $user->id)->count();
					$createdDate = $user->created_at;
					$createdDate = strtotime($createdDate);
					$now = time();
					$diff = $now - $createdDate;
					$diff = round($diff / (60 * 60 * 24));
					$rnd = round($diff/365, 0);
					$since = "";
					$sinceType = "";
					if($rnd>0)
					{
						$since = $rnd;
						$sinceType = "Years";
					}
					else
					{
						$rnd = round($diff/30, 0);
						if($rnd>0)
						{
							$since = $rnd;
							$sinceType = "Months";
						}
						else
						{
							$since = $diff;
							$sinceType = "Days";
						}
					}
					$vehicle = \App\Vehicle::where('driver_user_id', '=', $user->id)->first();
					$vehicleType = \App\VehicleType::where('id', '=', $vehicle->vehicle_type_id)->first();
					$vehicleTrips = \App\Trip::whereIn('status', ['Completed', 'Completed & Paid'])->where('vehicle_id', '=', $vehicle->id)->count();
					$transactions = \App\Transaction::where('driverUserId', '=', $user->id)->orderBy('created_at', 'DESC')->limit(5)->get();
					$user_ = ['id'=>$user->id, 'name'=>$user->name, 'tripCount'=>$trips, 'userRating'=>$user->userRating, 'since'=>$since, 'sinceType'=>$sinceType,
						'email' => $user->email, 'role_code'=>$user->role_code, 'photoURL'=> $user->passport_photo!=null && strlen($user->passport_photo)>0 ? "http://taxizambia.probasepay.com/users/".$user->passport_photo : null,
						'phoneNumber'=>$user->mobileNumber, 'displayName'=>$user->name, 'virtualAccountValue' => number_format($user->virtualAccountValue, 2, '.', ','), 'totalPayments' => number_format($user->totalPayments, 2, '.', ','), 
						'cashPayments' => number_format($user->cashPayments, 2, '.', ',')
					];
					return response()->json(['status'=>1, 'user'=>$user_, 'vehicle'=>$vehicle, 'vehicleTrips'=>$vehicleTrips, 'vehicleType'=>$vehicleType, 'transactions'=>$transactions]);
				}
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['TokenExpired'], 422);
        	}
	}


	public function updateProfilePix(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		$all = $request->all();
		
        	try {
            
			if($user!=null)
			{
				$user = \App\User::where('id', '=', $user->id)->first();
				$image = isset($all['image']) ? $all['image'] : null;
				if($image==null || ($image!=null && strlen($image)==0))
				{
					return response()->json([
						'status' => 0,
						'message' => '1 Your profile picture could not be uploaded. Please try again'
					]);
				}
				$id = str_random(16);
				$upload_folder = "users";
				$path = $upload_folder."/".$user->id."".$id.".jpeg";
				$image = str_replace('data:image/jpeg;base64,', '', $image);
				$image = str_replace(' ', '+', $image);
				$data = $image;
				$imgCreated = file_put_contents($path, base64_decode($data));
				if($imgCreated===false)
				{
					return response()->json([
						'status' => 0,
						'message' => '2 Your profile picture could not be uploaded. Please try again'
					]);
				}
				else
				{
					$pix_path = $user->id."".$id.".jpeg";
					$user->passport_photo = $pix_path;
					$user->save();



					$user = \App\User::where('id', '=', $user->id)->first();
					if($user->role_code=="DRIVER")
					{
						$trips = \App\Trip::whereIn('status', ['Completed', 'Completed & Paid'])->where('vehicle_driver_user_id', '=', $user->id)->count();
						$createdDate = $user->created_at;
						$createdDate = strtotime($createdDate);
						$now = time();
						$diff = $now - $createdDate;
						$diff = round($diff / (60 * 60 * 24));
						$rnd = round($diff/365, 0);
						$since = "";
						$sinceType = "";
						if($rnd>0)
						{
							$since = $rnd;
							$sinceType = "Years";
						}
						else
						{
							$rnd = round($diff/30, 0);
							if($rnd>0)
							{
								$since = $rnd;
								$sinceType = "Months";
							}
							else
							{
								$since = $diff;
								$sinceType = "Days";
							}
						}
						$vehicle = \App\Vehicle::where('driver_user_id', '=', $user->id)->first();
						$vehicleType = \App\VehicleType::where('id', '=', $vehicle->vehicle_type_id)->first();
						$vehicleTrips = \App\Trip::whereIn('status', ['Completed', 'Completed & Paid'])->where('vehicle_id', '=', $vehicle->id)->count();
						$transactions = \App\Transaction::where('driverUserId', '=', $user->id)->orderBy('created_at', 'DESC')->limit(5)->get();
						$user_ = ['id'=>$user->id, 'name'=>$user->name, 'tripCount'=>$trips, 'userRating'=>$user->userRating, 'since'=>$since, 'sinceType'=>$sinceType,
							'email' => $user->email, 'role_code'=>$user->role_code, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo,
							'phoneNumber'=>$user->mobileNumber, 'displayName'=>$user->name, 'virtualAccountValue' => number_format($user->virtualAccountValue, 2, '.', ','), 'totalPayments' => number_format($user->totalPayments, 2, '.', ','), 
							'cashPayments' => number_format($user->cashPayments, 2, '.', ',')
						];
						return response()->json(['status'=>1, 'user'=>$user_, 'vehicle'=>$vehicle, 'vehicleTrips'=>$vehicleTrips, 'vehicleType'=>$vehicleType, 'transactions'=>$transactions]);
					}	
					return response()->json([
						'status' => 1,
						'message' => 'Your profile picture was uploaded successfully'
					]);
					
				}
			}
			else
			{
				return response()->json(['status'=>0]);
			}
			

        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['TokenExpired'], 422);
        	}
	}



	public function getSearchPreviousAddress()
	{
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);

        	try {
            		$trips = \App\Trip::where('passenger_user_id', '=', $user->id)->groupBy('destination_vicinity')->get();
			$ar = [];
			if($trips->count()>0)
			{
				foreach($trips as $trip)
				{
					$ar_ = [];
					$ar_['vicinity'] = $trip->destination_vicinity;
					$ar_['name'] = $trip->destination_vicinity;
					$ar_['geometry'] = ['location'=> ['lat'=> floatval($trip->destination_latitude), 'lng'=> floatval($trip->destination_longitude)]];
					array_push($ar, $ar_);
				}
				return response()->json(['status'=>1, 'results' => $ar, 'trips'=>$trips ]);
			}
			return response()->json(['status'=>0]);

        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>422]);
        	}

	}


	public function postSetDriverAvailable(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		$isAvailable = $request->get('isAvailable');

        	try {
            		$vehicle = \App\Vehicle::where('driver_user_id', '=', $user->id)->whereIn('status', ['Valid','Invalid', 'Available','Unavailable'])->first();
			$ar = [];
			if($vehicle!=null)
			{
				$vehicle->status = $isAvailable ==1 ? 'Available' : 'Unavailable';
				$vehicle->save();
				return response()->json(['status'=>1]);
			}
			return response()->json(['status'=>0]);

        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>422]);
        	}

	}


	public function postSendSOS(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		$tripId = $request->get('tripId');
		$vicinity = $request->get('vicinity');
		$latitude = $request->get('lat');
		$longitude = $request->get('lng');

        	try {
            		$trip = \App\Trip::where('id', '=', $tripId)->first();
			$ar = [];
			if($trip!=null)
			{
				/*$trip->lastSosSent = date('Y-m-d H:i');
				$trip->lastSosSentFromLatitude = $latitude;
				$trip->lastSosSentFromLongitude = $longitude;
				$trip->save();*/

				$sos = new \App\Sos();
				$sos->tripId = $tripId;
				$sos->latitude = $latitude;
				$sos->longitude= $longitude;
				$sos->sentByUserId = $user->id;
				$sos->vicinity = $vicinity;
				$sos->sentByUserMobileNumber = $user->mobileNumber;
				$sos->save();


				try
				{
					$mobile = strpos($user->mobileNumber, "260")==0 ? $user->mobileNumber: ("26".$user->mobileNumber);
					$msg = "SOS Sent.\nContact Number: +".$user->mobileNumber."\nVicinity: ".$vicinity;
					$sender = "Bevura";
					send_sms($mobile, $msg, $sender=NULL);
				}
				catch(\Exception $e)
				{
			
				}
				return response()->json(['status'=>1]);
			}
			return response()->json(['status'=>0]);

        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>422]);
        	}

	}


	public function submitTripRating(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		$tripId = $request->get('tripId');
		$slideColor = $request->get('slideColor');
		$rt = 0;
		if($slideColor!=null)
		{
			$slideColor = json_decode($slideColor, true);

			foreach($slideColor as $sc)
			{
				if($sc=='orange')
				{
					$rt++;
				}
			}
		}



        	try {

		if($user->role_code=="DRIVER")
		{
            		$trip = \App\Trip::where('id', '=', $tripId)->first();
			$ar = [];
			if($trip!=null)
			{
				$trip->passenger_rating = $rt;
				$trip->save();


				try
				{
					$trips = \DB::table('trips')->where('passenger_user_id', '=', $trip->passenger_user_id)->where('status', '=', 'Completed & Paid')->get();//->avg('drive_rating');
					$ttotal = 0;
					foreach($trips as $t)
					{
						$ttotal = $ttotal + $t->drive_rating;
					}

					$trips = $ttotal + $rt;
					$trips = $trips / (($trips==null ? 0 : sizeof($trips))+1);
					//dd($trips);
					$user_ = \App\User::where('id', '=', $user->id)->first();
					$user_->userRating = $trips;
					$user_->save();
				}
				catch(\Exception $e)
				{
			
				}
				return response()->json(['status'=>1]);
			}
			return response()->json(['status'=>0]);
		}
		else
		{
            		$trip = \App\Trip::where('id', '=', $tripId)->first();
			$ar = [];
			if($trip!=null)
			{
				$trip->drive_rating = $rt;
				$trip->save();


				try
				{
					$trips = \DB::table('trips')->where('vehicle_driver_user_id', '=', $trip->vehicle_driver_user_id)->where('status', '=', 'Completed & Paid')->get();//->avg('drive_rating');
					//dd($trips);
					$ttotal = 0;
					foreach($trips as $t)
					{
						$ttotal = $ttotal + $t->drive_rating;
					}

					$trips = $ttotal + $rt;
					$trips = $trips / (($trips==null ? 0 : sizeof($trips))+1);

					$user_ = \App\User::where('id', '=', $user->id)->first();
					$user_->userRating = $trips;
					$user_->save();
				}
				catch(\Exception $e)
				{
			
				}
				return response()->json(['status'=>1]);
			}
			return response()->json(['status'=>0]);
		}


        	}catch(TokenExpiredException $e)
        	{
            		return response()->json(['status'=>422]);
        	}

	}



	public function getWalletCardBalances(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
			$bevuraTokenCard = $request->get('bevuraTokenCard');
			$walletbevuratoken = $request->get('walletbevuratoken');
			$url = 'https://payments.probasepay.com/mobile-bridge/api/v1/api/get-balance-by-token';
			//return response()->json($password);
			$merchantId = PAYMENT_MERCHANT_ID;
			$deviceCode = PAYMENT_DEVICE_ID;
			$apkey = PAYMENT_API_KEY;


			$toHash = "$bevuraTokenCard$merchantId$deviceCode$apkey";
      		  	$hash = hash('sha512', $toHash);
			$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;

			//return response()->json($data);
			$server_output = sendPostRequestForBevura($url, $data);

			$bevuraNewCustomerData = json_decode($server_output);
			$user = \App\User::where('id', '=', $user->id)->first();


			$totalEarning = \App\TransactionBreakdown::where('user_id', '=', $user->id)->where('is_withdrawable', '=', 1)->sum('breakdown_amount');
			$totalDebits = \App\TransactionBreakdown::where('user_id', '=', $user->id)->where('transaction_type', '=', 'WALLET WITHDRAWAL')->sum('breakdown_amount');
			return response()->json(['status'=>1, 'bevuraNewCustomerData'=>$bevuraNewCustomerData, 'walletBalance'=>$user->outstanding_balance, 'totalEarning'=>$totalEarning, 'totalDebits'=>$totalDebits]);
		}
		catch(\Exception $e)
		{
			return response()->json(['status'=>0, 'bevuraNewCustomerData'=>$bevuraNewCustomerData]);
		}

	}


	public function getMenuAdvert(\App\Http\Requests\BaseTaxiRequest $request)
	{
		return response()->json(['status'=>1, 'backgroundAdvertImage'=>'http://taxizambia.probasepay.com/adverts/game_of_thrones.png', 'url'=>'http://google.com', 'advertText'=> 'Octoberfest - Oct 20th 2021', 'advertText2'=>'Get a ticket']);

	}


}
