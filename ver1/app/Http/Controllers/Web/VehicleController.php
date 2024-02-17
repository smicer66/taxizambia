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
	

    public function __construct(JWTAuth $jwtauth)
    {
        //$this->middleware('jwt.auth', ['except' => ['pulllist']]);  // or use 'only' in place of except
		/*$this->middleware('jwt.auth', ['except'=> ['getAvailableVehiclesBetweenPoints', 'getActiveDrivers', 'getSearchByAddress', 'getDriverDeal', 
			'makeDriverDeal', 'getTripById', 'getTripGoingById', 'getDriverPosition', 'verifyProbaseWallet', 'getTrips', 'getTripsOfDriver', 'sendSupportMessage', 
			'getDealForDriver', 'getTripFromDeal', 'getPassenger', 'dropOffPassenger', 'acceptJob', 'setTripGoingById' , 'getDriverDealStatus', 
			'rateTrip', 'cancelPassengerOpenDeals', 'payTripFeeUsingCard', 'payTripFeeUsingCardStepTwo', 'getTransactionByTripId', 'getTripsOfDriverForWallet'
			'updateAvailabilityForJob', 'widthDraw', 'updateVehiclePosition', 'sendRequestForAPaymentCard']]);*/  // or use 'only' in place of except
		$this->middleware('jwt.auth', ['except'=> ['sendRequestForAPaymentCard', 'pullRegisterData', 'authenticateProbasePayWallet', 
			'authenticateProbasePayWalletWithOtp']]);
        $this->jwtauth = $jwtauth;
    }
	
	
	
	public function pullRegisterData()
	{
		$vehicleTypes = \App\VehicleType::where('status', '=', 1)->get()->pluck('name', 'id');
		$vehicleManufacturers = \App\VehicleManufacturer::where('status', '=', 1)->pluck('name', 'id');
		$vehicleMakes = \App\VehicleMake::where('status', '=', 1)->pluck('name', 'id');
		$cities = \App\City::where('status', '=', 1)->pluck('name', 'id');
		$districts = \App\District::pluck('name', 'id');
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


    public function getAvailableVehiclesBetweenPoints(\App\Http\Requests\BaseTaxiRequest $request)
    {
		$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
        
		
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
			
			$city = 'Lusaka';
			try{
				$len = sizeof($resp->results);
				$city = ($resp->results[($len - 2)]->address_components[0]->long_name);
				
			}
			catch(Exception $e)
			{
			
			}
			
			//Distance between taxi drivers and the passenger at the moment
			$sql = 'SELECT v1.id, v1.vehicle_type, distanceFromUser, v1.vehicle_driver_name, v1.vehicle_driver_photo FROM `vehicle_trackers` v1 INNER JOIN ((SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) '.
			'+ COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 '.
			'AS `distanceFromUser` FROM `vehicle_trackers` WHERE ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) + '.
			'COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 < 100 '.
			'ORDER BY `distanceFromUser`)) v2 ON v1.id = v2.id GROUP BY v1.vehicle_type';
			$vehicles = \DB::select($sql);
			$accts = [];
			$time = 0;
			$distance = '0km';
			
			
			
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
				}
			}
			
			
			
			
            
            if(sizeof($vehicles)>0)
            {
				
				$i=0;
				$vehicleTypeKeys = array_keys($vehicleIcons);
				
                foreach ($vehicles as $vehicle) {
					$sql = "SELECT v4.*, v3.* from `vehicle_traffic_costs` v3, `traffic_costs` v4 WHERE ".
						"v3.traffic_cost_id = v4.id AND v3.vehicle_type = '".$vehicle->vehicle_type."' AND ".
						"v4.district_name = '".$city."' AND v3.status = 'Active'";
					//dd($sql);
					$tpCosts = \DB::select($sql);
					
					//dd($tpCosts);
					
					$total_cost = 0;
					
					
					
					if($tpCosts!=null && sizeof($tpCosts)>0)
					{
						$baseRate = $tpCosts[0]->base_fare;	//based on city
						$ratePerSecond = $tpCosts[0]->chargePerSecond;	//based on time
						$currentDemandIndex = 0;
						$cancellationFee = $tpCosts[0]->cancellationFee;
						$minimumFare = $tpCosts[0]->minimumFare;
						$amtToPay = ($baseRate + $currentDemandIndex) + ($time*$ratePerSecond);
						$cost = ($amtToPay  < $minimumFare) ? $minimumFare : $amtToPay;
						$vehicleTypeIcon = in_array($vehicle->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$vehicle->vehicle_type] : $vehicleIcons['Taxi'];
						$photoURL = "http://taxizambia.probasepay.com/users/".$vehicle->vehicle_driver_photo;
						$accts[$i++] = ['id'=>$vehicle->id, 'photoURL'=>$photoURL, 'name'=>$vehicle->vehicle_driver_name, 'distanceFromUser'=>$vehicle->distanceFromUser, 'vehicleType'=>$vehicle->vehicle_type, 'fee'=>$cost, 'icon'=>$vehicleTypeIcon];
						
						
					}
                }
				$arr_ = ['vehicles' => $accts, 'distance'=>$distance, 'currency' => 'ZMW'];
				$fresp[$locality] = $arr_;
                return response()->json($fresp);
            }else{
                return response()->json(['err' => 'No airlines were found'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
        }
    }

    /***getActiveDrivers***/
    public function getActiveDrivers(\App\Http\Requests\BaseTaxiRequest $request) {
        
		
		$locality 	= $request->has('locality') ? $request->get('locality') : null;
		$oLat 	= $request->has('originLat') ? $request->get('originLat') : null;
		$oLon 	= $request->has('originLng') ? $request->get('originLng') : null;
		$vehicleType 	= $request->has('vehicleType') ? $request->get('vehicleType') : null;
		
        try {
			$token = JWTAuth::getToken();
			$sql = 'SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) '.
				'+ COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( '.$oLat.' )) * COS( RADIANS( `current_longitude` ) - RADIANS( '.$oLon.' )) ) * 6380 '.
				'AS `distanceFromUser` FROM `vehicle_trackers` WHERE vehicle_type = "'.$vehicleType.'" AND ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( '.$oLat.' ) ) + '.
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
					$photoURL = "http://taxizambia.probasepay.com/users/".$vehicle->vehicle_driver_photo;
					$accts[$i++] = ['id'=>$vehicle->id, 'distanceFromUser'=>$vehicle->distanceFromUser, 
						'vehicleType'=>$vehicle->vehicle_type, 'lat'=>$vehicle->current_latitude, 'lng'=>$vehicle->current_longitude, 
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
				
			
			$sql = 'SELECT *, v2.status as status FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE v2.passenger_user_id = '.$user->id.' AND v2.status IN ("Accepted", "Pending", "Driver Canceled", "Going") AND 
				v2.booking_group_id = "'.$bookId.'" AND v1.vehicle_id = v2.vehicle_id AND 
				DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by v2.created_at DESC';
				
			
			$deals = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0, 'deals'=>[]];
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
					$dl= ['tripId'=>$dlr->trip_id, 'deal_status'=>$dlr->status];
					$accts = ['status' =>1, 'deals'=>$dl];
				//}
				
			}
			
			return response()->json($accts);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>422]);
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
				date_default_timezone_set('Africa/Lagos');
				$driverDeal = new \App\DriverDeal();
				$driverDeal->vehicle_id = $vehicle_tracker->vehicle_id;
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
	
	
	/***removeDriverDeal***/
	public function removeDriverDeal(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
			$token = JWTAuth::getToken();
			$vehicleId 	= $request->has('vehicleId') ? $request->get('vehicleId') : null;
			$user = JWTAuth::toUser($token);
			
			$vehicle_tracker = \App\VehicleTracker::where('id', '=', $vehicleId)->first();
			
			if($vehicle_tracker!=null)
			{
				$driverDeal = \App\DriverDeal::where('vehicle_id', '=', $vehicle_tracker->vehicleId)->where('passenger_user_id', '=', $user->id)->first();
				$driverDeal->status = 'Timed Out';
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
	
	
	
	
	/***getTripById***/
	public function getTripById(\App\Http\Requests\BaseTaxiRequest $request)
	{
        try {
            $token = JWTAuth::getToken();
			$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
			$user = JWTAuth::toUser($token);
			
			$trip = \App\Trip::where('id', '=', $tripId)->first();
			
			
			if($trip!=null)
			{
				$origin = ['vicinity'=>$trip->origin_vicinity];
				$destination = ['vicinity'=>$trip->destination_vicinity];
				$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
					'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
					'destination'=> $destination];
					
				$driver = ['photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id];
				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver]);
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
				$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
				$destination = ['vicinity'=>$trip->destination_vicinity];
				$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
					'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
					'destination'=> $destination, 'status'=> $trip->status, 'id'=>$trip->id];
					
				
				$vehicleTypeIcon = in_array($trip->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$trip->vehicle_type] : $vehicleIcons['Taxi'];
				$driver = ['photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
					'rating' => $trip->vehicle_driver_rating, 'plate'=>$trip->vehicle_plate_number, 'brand'=>$trip->vehicle_type, 
					'driverId' => $trip->vehicle_driver_user_id, 'id'=>$trip->vehicle_driver_user_id, 'icon'=>$vehicleTypeIcon];
				return response()->json(['status'=>1, 'trip'=>$trip_, 'driver'=>$driver]);
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
			
			$trip = \App\Trip::where('id', '=', $tripId)->where('vehicle_driver_user_id', '=', $driverId)->first();
			if($trip->status=='Pending')
			{
				date_default_timezone_set('Africa/Lagos');
				$driver_deals = \App\DriverDeal::where('trip_id', '=', $trip->id)->first();
				$driver_deals->status = 'Going';
				$driver_deals->save();
				
				$trip->pickedUpAt = date('Y-m-d H:i');
				$trip->status = 'Going';
				$trip->save();
				
				
			}
			
			if($trip!=null)
			{
				$location=['lat'=>$trip->origin_latitude, 'lng'=>$trip->origin_longitude];
				$origin = ['vicinity'=>$trip->origin_vicinity, 'location'=>$location];
				$destination = ['vicinity'=>$trip->destination_vicinity];
				$trip_ = ['driverId' => $trip->vehicle_driver_user_id, 'identifier'=>$trip->trip_identifier,
					'currency'=> $trip->currency, 'fee'=> $trip->amount_chargeable, 'origin'=> $origin, 
					'destination'=> $destination, 'status'=> $trip->status];
					
				$driver = ['photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 'name'=>$trip->vehicle_driver_user_name,
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
        }
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
				}
				$accts = ['status' =>1];
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
			$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
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
				"&description=".urlencode("Payment for trip|".$user->name."|").$user->mobileNumber."&responseurl=http://taxizambia.probasepay.com/payments/handle-response-success".
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
	
	
	
	/***updateVehiclePosition***/
	public function updateVehiclePosition(\App\Http\Requests\BaseTaxiRequest $request)
	{
		try
		{
			
			$all = $request->all();
			$dataReq = new \App\DataRequest();
			$dataReq->data_request = json_encode($all);
			$dataReq->save();
			
			$token = JWTAuth::getToken();
			$user = JWTAuth::toUser($token);
		
			$driverId = $request->has('driverId') ? $request->get('driverId') : null;
			$locality = $request->has('locality') ? $request->get('locality') : null;
			$lat = $request->has('lat') ? $request->get('lat') : null;
			$lng = $request->has('lng') ? $request->get('lng') : null;
			
			if($driverId==null || $locality==null || $lat==null || $lng==null)
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
					$vehicleTracker->vehicle_id = $vehicle->id;
					$vehicleTracker->vehicle_unique_id = $vehicle->uniqid;
					$vehicleTracker->status = 'Available';
					$vehicleTracker->vehicle_type = $vehicleType;
					$vehicleTracker->vehicle_driver_name = $user->name;
					$vehicleTracker->vehicle_driver_user_id = $user->id;
					$vehicleTracker->vehicle_driver_photo = $user->passport_photo;
					$vehicleTracker->current_longitude = $lng;
					$vehicleTracker->current_latitude = $lat;
					$vehicleTracker->old_longitude = $oldLng;
					$vehicleTracker->old_latitude = $oldLat;
					$vehicleTracker->save();
				}
				else
				{
					return response()->json(['status'=>2, 'message'=>'Vehicle not found']);
				}
			}
			
			
			$driverRequests = \DB::table('trip_requests')->where('trip_requests.driver_user_id', '=', $user->id)->where('trip_requests.status', '=', 'AWAITING')
				->join('trips', 'trip_requests.trip_id', '=', 'trips.id')->where('trips.status', '=', 'Pending')
				->select('trips.*')
				->get();
				
				
			return response()->json(['status'=>1, 'message'=>'Updated successfully', 'driverRequests'=>$driverRequests]);
		}catch(TokenExpiredException $e)
        {
            return response()->json(['status'=>-1, 'message'=>'Token Expired']);
        }
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
			$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
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
				"&responseurl=http://taxizambia.probasepay.com/payments/handle-response-success&orderId=".$transaction->orderId.
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
			$trips = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereNotIn('status', ['Pending'])->orderBy('created_at', 'DESC')->get();
		    
			
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
						if($trip->status=="Pending")
						{
							$pkdUpAt = date('Y, M dS H:i', strtotime($trip->created_at))."Hrs - Pending";
						}
						else if($trip->status=="Going")
						{
							$pkdUpAt = "On-Going. ".($trip->pickedUpAt==null ? " Created at ".date('Y, M dS H:i', strtotime($trip->created_at))."Hrs" : " Picked Up at ".date('Y, M dS H:i', strtotime($trip->pickedUpAt))."Hrs");
						}
						else if($trip->status=="Passenger Canceled")
						{
							$pkdUpAt = "You canceled trip";
						}
						else if($trip->status=="Driver Canceled")
						{
							$pkdUpAt = "Drivers canceled trip";
						}
						else if($trip->status=="Completed")
						{
							$pkdUpAt = "Completed. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							if($trip->paidYes==1)
							{
								$pkdUpAt = "Completed & Paid. Dropped Off - ".date('Y, M dS H:i', strtotime($trip->droppedOffAt))."Hrs";
							}
							
						}
						
						$trip_=['pickedUpAt'=>$pkdUpAt, 
							'origin'=>$origin, 'destination'=>$dest, 'bookId'=>$trip->trip_identifier, 'id'=>$trip->id, 
							'photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 
							'name'=>$trip->vehicle_driver_user_name, 'currency'=>$trip->currency, 'fee'=>$trip->amount_chargeable, 
							'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 'bgColor'=>$bgColor];
						array_push($trip__, $trip_);
					}
					$ij++;
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
						
						$trip_=['pickedUpAt'=>$pkdUpAt, 'pickedUpAt1' =>$pkdUpAt1,
							'origin'=>$origin, 'destination'=>$dest, 'bookId'=>$trip->trip_identifier, 
							'photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 'paymentMethod'=>$trip->payment_method,
							'name'=>$trip->vehicle_driver_user_name, 'currency'=>$trip->currency, 'fee'=>$trip->amount_chargeable, 
							'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 'bgColor'=>$bgColor];
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
							'photoURL'=>"http://taxizambia.probasepay.com/users/".$trip->vehicle_driver_photo, 'paymentMethod'=>$trip->payment_method,
							'name'=>$trip->vehicle_driver_user_name, 'currency'=>$trip->currency, 'fee'=>$trip->amount_chargeable, 
							'note'=>$trip->notes, 'rating'=>$trip->vehicle_driver_rating, 'paidYes'=>$trip->paidYes, 'bgColor'=>$bgColor];
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
			$sql = 'SELECT *, v2.status as status, v2.id as driverDealId, v1.id as vehicleTrackerId FROM `vehicle_trackers` v1,`driver_deals` v2 WHERE 
				v1.vehicle_id = v2.vehicle_id AND v2.driver_user_id = '.$driverId.' 
				AND ((v2.status IN ("Pending") AND DATE_ADD(v2.created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW()) OR (v2.status IN ("Accepted", "Going"))) ORDER BY v2.id DESC LIMIT 0, 1';
				
				
				//refactor code. Interval being greater than or less than is dependent on status
				
			
			$deals = \DB::select($sql);
			$accts = [];
			
			$accts = ['status' =>0, 'deals'=>[]];
			if(sizeof($deals)>0)
			{
				$dl = [];
				//if($deals[0]->status=='Pending')
				//{
					$originLocation = ['lng'=>$deals[0]->origin_longitude, 'lat'=>$deals[0]->origin_latitude];
					$destLocation = ['lng'=>$deals[0]->destination_longitude, 'lat'=>$deals[0]->destination_latitude];
					$origin=['location'=>$originLocation, 'vicinity'=>$deals[0]->origin_locality];
					$destination=['location'=>$destLocation, 'vicinity'=>$deals[0]->destination_locality];
					$dl = ['tripId'=> $deals[0]->trip_id, 'status'=>$deals[0]->status, 'createdAt'=>strtotime($deals[0]->created_at), 
						'origin'=>$origin, 'destination'=>$destination, 'driverDealId'=>$deals[0]->driverDealId, 'id'=>$deals[0]->driverDealId, 
						'vehicleTrackerId'=>$deals[0]->vehicleTrackerId, 'deal_status'=>$deals[0]->status];
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
								'id'=>$tripQuery->id];
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
								'id'=>$tripQuery->id];
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
			$dealId = $request->has('dealId') ? $request->get('dealId') : null;
			$driverId = $request->has('driverId') ? $request->get('driverId') : null;
			$accts = ['status' =>0];
			if($dealId!=null)
			{
				
				$sql = 'SELECT *, v2.status as status, v2.id as driverDealId, v1.id as vehicleTrackerId FROM `vehicle_trackers` v1,`driver_deals` v2, 
				`vehicles` v3 WHERE 
				v2.id = '.$dealId.' AND 
				v1.vehicle_id = v2.vehicle_id AND 
				v1.vehicle_id = v3.id AND v2.driver_user_id = '.$driverId." LIMIT 0, 1";
				$deal = \DB::select($sql);
				$deal = $deal[0];
				
				
				if($deal!=null)
				{
					$checkTrip = \App\Trip::where('deal_booking_group_id', '=', $deal->booking_group_id);
					//->whereNotIn('vehicle_id', [$deal->vehicle_id]);
					if($checkTrip->count()>0)
					{
						$checkTrip = $checkTrip->first();
						if($checkTrip->vehicle_id == $deal->vehicle_id)
						{
							$origin = ['vicinity'=>$deal->origin_locality];
							$dest = ['vicinity'=>$deal->destination_locality];
							$trip_ = ['trip_identifier'=>$checkTrip->trip_identifier, 'origin_vicinity'=>$checkTrip->origin_vicinity, 'payment_method'=>$checkTrip->payment_method, 'origin'=>$origin, 'destination'=>$dest, 
								'destination_vicinity'=>$checkTrip->destination_vicinity, 'fee'=>number_format($checkTrip->amount_chargeable, 2, '.', ','), 
								'driver_deal_id'=>$checkTrip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$checkTrip->passenger_user_id, 
								'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal->driver_user_id, 'droppedOffAt'=>'', 'id'=>$checkTrip->id, 'driverDealStatus'=>'Accepted'];
							$accts = ['status' =>1, 'id'=>$trip->id, 'trip'=>$trip_];
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
						$origin = ['vicinity'=>$deal->origin_locality];
						$dest = ['vicinity'=>$deal->destination_locality];
						$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
							'payment_method'=>$tripQuery->payment_method,
							'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
							'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
							'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal->driver_user_id, 'droppedOffAt'=>'', 'driverDealStatus'=>'Accepted', 'tripStatus'=>$tripQuery->status,
							'id'=>$tripQuery->id];
						$accts = ['status' =>($tripQuery->status=='Pending' ? 1 : 2), 'id'=>$tripQuery->id, 'trip'=>$trip_];
						return response()->json($accts);
					}
					
					
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
					if($trip->save())
					{
						
						
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
							foreach($driverDeals as $dd)
							{
								$dd->status = 'Already Taken';
								$dd->save();
							}
						}
						$origin = ['vicinity'=>$deal->origin_locality];
						$dest = ['vicinity'=>$deal->destination_locality];
						$trip_ = ['trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 'payment_method'=>$trip->payment_method, 'origin'=>$origin, 'destination'=>$dest, 
							'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
							'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
							'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal->driver_user_id, 'droppedOffAt'=>'', 'id'=>$trip->id, 'driverDealStatus'=>'Accepted'];
						$accts = ['status' =>1, 'id'=>$trip->id, 'trip'=>$trip_];
					}
					else
					{
						$accts = ['status' =>0, 'message'=>'We experienced issues assigning the trip to you. Our sincere apologies'];
					}
				
					
				}
				else
				{
					$accts = ['status' =>0, 'message'=>'Invalid deal code provided'];
				}
			}
			else
			{
				$accts = ['status' =>0, 'message'=>'Invalid deal code provided'];
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
					$origin = ['vicinity'=>$deal[0]->origin_locality];
					$dest = ['vicinity'=>$deal[0]->destination_locality];
					$trip_ = ['trip_identifier'=>$tripQuery->trip_identifier, 'origin_vicinity'=>$tripQuery->origin_vicinity, 
						'payment_method'=>$tripQuery->payment_method,
						'destination_vicinity'=>$tripQuery->destination_vicinity, 'fee'=>number_format($tripQuery->amount_chargeable, 2, '.', ','), 
						'driver_deal_id'=>$tripQuery->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$tripQuery->passenger_user_id, 
						'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal[0]->driver_user_id, 'droppedOffAt'=>'', 
						'id'=>$tripQuery->id];
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
				if($trip->save())
				{
					$origin = ['vicinity'=>$deal[0]->origin_locality];
					$dest = ['vicinity'=>$deal[0]->destination_locality];
					$trip_ = ['trip_identifier'=>$trip->trip_identifier, 'origin_vicinity'=>$trip->origin_vicinity, 'payment_method'=>$trip->payment_method,
						'destination_vicinity'=>$trip->destination_vicinity, 'fee'=>number_format($trip->amount_chargeable, 2, '.', ','), 
						'driver_deal_id'=>$trip->driver_deal_id, 'origin'=>$origin, 'destination'=>$dest, 'passenger'=>$trip->passenger_user_id, 
						'status'=>'Pending', 'pickedUpAt'=>'', 'currency'=>"ZMW", 'driverId'=>$deal[0]->driver_user_id, 'droppedOffAt'=>'', 'id'=>$trip->id];
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
					'passport_photo'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo];
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
					date_default_timezone_set('Africa/Lagos');
					$trip->status = 'Completed';
					$trip->droppedOffAt = date('Y-m-d H:i');
					$trip->save();
					
					$deal->status = 'Completed';
					$deal->save();
					
					$vehicleDB = \DB::table('vehicles')->join('users', 'vehicles.driver_user_id', '=', 'users.id')
						->where('vehicles.driver_user_id', '=', $driverId)->first();
					//dd($vehicleDB);
					$accts = ['status' =>1, 
						'driver'=>['id'=>$driverId, 'plate'=>$vehicleDB->vehicle_plate_number, 'type'=>$vehicleDB->vehicle_type, 
							'name'=>$vehicleDB->user_full_name, 'refCode'=>$vehicleDB->refCode, 'rating'=>$vehicleDB->avg_system_rating, 
							'balance'=>number_format($vehicleDB->outstanding_balance, 2, '.', ','),  
							'profile_pix'=>"http://taxizambia.probasepay.com/users/".$vehicleDB->passport_photo]
						];
					return response()->json($accts);
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
						'deals'=>['tripId'=>$deal_->trip_id]
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
			$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
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
				"&description=".urlencode("Payment for trip|".$user->name."|").$user->mobileNumber."&responseurl=http://taxizambia.probasepay.com/payments/handle-response-success".
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
			$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
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
				"&responseurl=http://taxizambia.probasepay.com/payments/handle-response-success&hash=".hash('sha512', $toHash)."&serviceTypeId=1981511018900".
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
	
}
