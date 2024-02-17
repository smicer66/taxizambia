<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\UserAccount;
use App\Http\Requests;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterOTPRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use JWTAuth;

class AuthController extends Controller
{
    private $user;
    private $userAccount;
    private $jwtauth;
	var $getServiceBaseURL = "payments.probasepay.com";


    private $fnames = ['Emma', 'Olivia', 'Sophia', 'Isabella', 'Ava', 'Mia', 'Emily', 'Abigail', 'Madison', 'Charlotte',
        'Noah', 'Liam', 'Mason', 'Jacob', 'William', 'Ethan', 'Michael', 'Alexander', 'James', 'Daniel'];
    private $snames = ['Cesar', 'Aaron', 'Muyelu', 'Muleyu', 'Somili', 'Milaho', 'Ganiu', 'Benjamin', 'Koggu', 'Shaguy'];
    private $mailsuffix = ['@yahoo.com', '@yahoo.co.uk', '@gmail.com', '@hotmail.com', '@aol.com', '@stanleaf.com',
        '@silverslide.com', '@zescomail.com', '@phillips.com', '@swizz.co.zm'];


    public function __construct(User $user, UserAccount $userAccount, JWTAuth $jwtauth)
    {
        $this->user = $user;
        $this->userAccount = $userAccount;
		$this->middleware('jwt.auth', ['except'=> ['register', 'validatePassword', 'signUpUserProfile', 'login', 'verifyEmailAddress', 'recoverPassword', 'test', 'validateBioData', 'validateMobileNumber']]);
        $this->jwtauth = $jwtauth;
    }
	

    public function getName()
    {
        return $this->fnames[rand(0, 19)]." ".$this->snames[rand(0, 9)];
    }

    public function getEmail($name)
    {
        $name = strtolower(str_replace(' ', '_', $name));
        return $name."".$this->mailsuffix[rand(0, 9)];
    }

    public function getMobileNumber()
    {
        return substr(("080".date('mdHs')), 0, 11);
    }



    public function test()
    {
		$sql = 'SELECT *, ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( -15.3756088 ) ) + COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( -15.3756088 )) * COS( RADIANS( `current_longitude` ) - RADIANS( 28.3107203 )) ) * 6380 AS `distanceFromUser` FROM `vehicle_trackers` WHERE  vehicle_id IN (2,3) AND  updated_at > "2021-05-27 10:57" AND 
			vehicle_type = "Sedan" AND status = "Available" AND ACOS( SIN( RADIANS( `current_latitude` ) ) * SIN( RADIANS( -15.3756088 ) ) + COS( RADIANS( `current_latitude` ) ) * COS( RADIANS( -15.3756088 )) * COS( RADIANS( `current_longitude` ) - RADIANS( 28.3107203 )) ) * 6380 < 100 ORDER BY `distanceFromUser`';
		$vehicles = \DB::select($sql);
		
		$x1 = 0;
		foreach($vehicles as $vehicle)
		{
			$vehicle_tracker = \App\VehicleTracker::where('id', '=', $vehicle->id)->first();
			
			if($vehicle_tracker!=null)
			{
				dd(33);
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
				$driverDeal->distance = $vehicle->distanceFromUser;
				$driverDeal->fee = $fee;
				$driverDeal->booking_group_id = $bookId;
				$driverDeal->note = ((is_array($note) && isset($note['note']) && strlen($note['note'])>0) ? $note['note'] : ($note!=null && strlen($note)>0 ? $note : null));
				$driverDeal->payment_method = $paymentMethod;
				$driverDeal->status = 'Pending';
				$driverDeal->save();
				
				$x1++;
			}
		}
		
		if($x1>0)
			return response()->json(['status'=>1, 'sq' => $sql]);
		else
			return response()->json(['status'=>0, 'sq' => $sql]);
		dd(33);
        $str["acctNumber"] = ["Account Numbers must be a 10-digit number"];

        $str_error = "";
        foreach($str as $key => $value)
        {
            foreach($value as $val) {
                $str_error = $str_error . "" . ($val);
            }
        }
        dd(("080".date('mdHs')));
    }
	
	
	public function logoutUser()
	{
		return ['status'=>1];
	}


    public function register(RegisterRequest $request)
    {
		$type = $request->get('type');
		if($type=='Driver')
		{
			$rules = ['mobilenumber' => 'required|numeric', 
			'password' => 'required', 'fname' => 'required',
			'vehicleType' => 'required', 'vehicleManufacturer' => 'required', 
			'manufactureYear' => 'required', 'passengerCount' => 'required', 
			'doors' => 'required', 'vehicleMake' => 'required', 
			'vehicleRegNo' => 'required', 'insuranceExpiryDate' => 'required', 
			'shareARide' => 'required', 'streetAddress'=>'required', 'city'=>'required', 'district'=>'required'];
			
			$messages = [
					'mobilenumber.required' => 'Your Mobile Number must be provided',
					'mobilenumber.numeric' => 'Provide a valid mobile number',
					'password.required' => 'You must provide a valid password',
					'fname.required' => 'Provide your full names',
					'vehicleType.required' => 'Select your vehicle type', 'vehicleManufacturer.required' => 'Select your vehicle manufacturer', 
					'manufactureYear.required' => 'Select your vehicle manufacture year', 'passengerCount.required' => 'Select your vehicles passenger count', 
					'doors.required' => 'Select your vehicles door count', 'vehicleMake.required' => 'Select your vehicles make', 
					'vehicleRegNo.required' => 'Provide the registration number of your vehicle', 'insuranceExpiryDate.required' => 'Provide your expiry date', 'shareARide.required' => 'Specify if you would like to be part of our ShareARide program'
					, 'streetAddress.required' => 'Specify your house address'
					, 'city.required' => 'Specify the city where you live/operate'
					, 'district.required' => 'Specify your district'
				];
				
			$validator = \Validator::make($request->all(), $rules, $messages);
			if($validator->fails())
			{
				$errMsg = json_decode($validator->messages(), true);
				$str_error = "";
				$i = 1;
				foreach($errMsg as $key => $value)
				{
					foreach($value as $val) {
						$str_error = ($val);
					}
				}
				return response()->json(['status'=>500, 'message'=>($str_error)]);
			}
			
			
			$usrCheck = User::where('mobileNumber', '=', $request->get('mobilenumber'))->orWhere('email', '=', $request->get('email'));
			if($usrCheck->count()>0)
			{
				return response()->json(['status'=>0, 'message'=>'You can not register with the mobile number and email you provided. Accounts matching these belong to another account']);
			}
			
			$vehCheck = \App\Vehicle::where('vehicle_plate_number', '=', $request->get('vehicleRegNo'));
			if($vehCheck->count()>0)
			{
				return response()->json(['status'=>0, 'message'=>'You can not register the vehicle. An account matching the vehicle number already exists']);
			}
			
			$user = new User();
			$user->mobileNumber = format_mobile_number($request->get('mobilenumber'));
			$user->password = \Hash::make($request->get('password'));
			$user->email = $request->has('email') ? $request->get('email') : null;
			$user->name = $request->get('fname');
			$user->pin = str_random(4);
			$user->instance_id = "1.0";
			$user->status = 'Active';
			$user->role_code = $type;
			$user->passport_photo = null;
			$user->refCode = strtoupper(str_random(8));
			$user->streetAddress = $request->get('streetAddress');
			$user->city = $request->get('city');
			$user->district = $request->get('district');
			$user->outstanding_balance = 0.00;
			
			if($user->save())
			{
				$vehicle = new \App\Vehicle();
				$vehicle->vehicle_plate_number = $request->get('vehicleRegNo');
				$vehicle->vehicle_type = $request->get('vehicleRegNo');
				$vehicle->vehicle_maker = $request->get('vehicleRegNo');
				$vehicle->doors = $request->get('vehicleRegNo');
				$vehicle->year = $request->get('vehicleRegNo');
				$vehicle->insurance_expiry = $request->get('vehicleRegNo');
				$vehicle->vehicle_make = $request->get('vehicleRegNo');
				$vehicle->passenger_count = $request->get('vehicleRegNo');
				$vehicle->share_ride = $request->get('vehicleRegNo');
				$vehicle->driver_user_id = $user->id;
				$vehicle->user_full_name = $request->get('fname');
				$vehicle->avg_system_rating = 0.0;
				$vehicle->status = 'Valid';
				$vehicle->outstanding_balance = 0.0;
				$vehicle->rating_count = 0;
				$vehicle->trips_completed_count = 0;
				$vehicle->admin_validated = 0;
				/*$credentials = $request->only('mobilenumber', 'password');
				$token = null;
				try {
					if (!$token = JWTAuth::attempt($credentials)) {
						return response()->json(['invalid_email_or_password'], 422);
					}
				} catch (JWTAuthException $e) {
					return response()->json(['failed_to_create_token'], 500);
				}
				return response()->json(['token'=>$token, 'id'=>$user->id]);*/
				return response()->json(['status'=>1, 'New Driver Account Created Successfully']);
			}
			else
			{
				return response()->json(['status'=>0, 'New Driver Account Creation was not Successful. Please try again']);
			}
		}
		else if($type=='Passenger')
		{
			$rules = ['mobilenumber' => 'required|numeric', 
			'password' => 'required', 
			'fname' => 'required', 'streetAddress'=>'required', 'city'=>'required', 'district'=>'required'];
			
			$messages = [
					'mobilenumber.required' => 'Your Mobile Number must be provided',
					'mobilenumber.numeric' => 'Provide a valid mobile number',
					'password.required' => 'You must provide a valid password',
					'fname.required' => 'Provide your full names', 'streetAddress.required' => 'Specify your house address'
					, 'city.required' => 'Specify the city where you live/operate'
					, 'district.required' => 'Specify your district'
				];
			$validator = \Validator::make($request->all(), $rules, $messages);
			if($validator->fails())
			{
				$errMsg = json_decode($validator->messages(), true);
				$str_error = "";
				$i = 1;
				foreach($errMsg as $key => $value)
				{
					foreach($value as $val) {
						$str_error = ($val);
					}
				}
				return response()->json(['status'=>500, 'message'=>($str_error)]);
			}
			
			$usrCheck = User::where('mobileNumber', '=', $request->get('mobilenumber'))->orWhere('email', '=', $request->get('email'));
			if($usrCheck->count()>0)
			{
				return response()->json(['status'=>0, 'message'=>'You can not register with the mobile number and email you provided. Accounts matching these belong to another account']);
			}
			
			$user = new User();
			$user->mobileNumber = format_mobile_number($request->get('mobilenumber'));
			$user->password = \Hash::make($request->get('password'));
			$user->email = $request->get('email');
			$user->name = $request->get('fname');
			$user->pin = str_random(4);
			$user->instance_id = "1.0";
			$user->status = 'Active';
			$user->role_code = $type;
			$user->passport_photo = null;
			$user->refCode = strtoupper(str_random(8));
			$user->streetAddress = $request->get('streetAddress');
			$user->city = $request->get('city');
			$user->district = $request->get('district');
			$user->outstanding_balance = 0.00;
			
			if($user->save())
			{
				/*$credentials = $request->only('mobilenumber', 'password');
				$token = null;
				try {
					if (!$token = JWTAuth::attempt($credentials)) {
						return response()->json(['invalid_email_or_password'], 422);
					}
				} catch (JWTAuthException $e) {
					return response()->json(['failed_to_create_token'], 500);
				}
				return response()->json(['token'=>$token, 'id'=>$user->id]);
			}
			else
			{
				return response()->json(['invalid_email_or_password'], 422);*/
				return response()->json(['status'=>1, 'New Passenger Account Created Successfully']);
			}
			else
			{
				return response()->json(['status'=>0, 'New Passenger Account Creation was not Successful. Please try again']);
			}
			
			//TODO: implement JWT
		}
    }


	public function validateMobileNumber(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$all = $request->all();

		$mobileNumber = $all['mobileNumber'];
		$otp = $all['otp'];
		$userSignUpRequest = \App\UserSignUpRequest::where('mobile_number', '=', $mobileNumber)->where('otp', '=', $otp)->first();
		if($userSignUpRequest!=null)
		{
			$userSignUpRequest->otp = null;
			$userSignUpRequest->save();
			return response()->json(['status'=>1]);
		}

		$otp = mt_rand(1000, 9999);
		$msg = "Your new one-time code is ".$otp;
		try
		{
			//send_sms($mobileNumber, $msg, "Bevura");
		}
		catch(\Exception $e)
		{

		}
		return response()->json(['status'=>0, 'message'=>'Invalid one-time code provided. We have resent you a new One-time password']);
	}

	public function validateBioData(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$all = $request->all();

			$rules = ['firstName' => 'required', 
				'lastName' => 'required', 'streetAddress' => 'required',
				'city' => 'required', 'district'=>'required'];
			
			$messages = [
					'firstName.required' => 'Your First Name must be provided',
					'lastName.required' => 'Your Last Name must be provided',
					'streetAddress.required' => 'Provide Your House Address',
					'city.required' => 'Specify the city you live in', 
					'district.required' => 'Specify your district'
				];
				
			$validator = \Validator::make($request->all(), $rules, $messages);
			if($validator->fails())
			{
				$errMsg = json_decode($validator->messages(), true);
				$str_error = "";
				$i = 1;
				foreach($errMsg as $key => $value)
				{
					foreach($value as $val) {
						$str_error = ($val);
					}
				}
				return response()->json(['status'=>500, 'message'=>($str_error)]);
			}
			
			
			return response()->json(['status'=>1]);
	}


	public function verifyEmailAddress(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$all = $request->all();

		$emailAddress = $all['emailAddress'];
		$rules = ['emailAddress'=>'required|email'];
			
		$messages = [
			'emailAddress.required' => 'You must provide an email address',
			'emailAddress.email' => 'Specify a valid email address.'
		];
				
		$validator = \Validator::make($request->all(), $rules, $messages);
		if($validator->fails())
		{
			$errMsg = json_decode($validator->messages(), true);
            		$str_error = "";
            		$i = 1;
           		foreach($errMsg as $key => $value)
            		{
                		foreach($value as $val) {
                    			$str_error = ($val);
                		}
            		}

			return response()->json(['status'=>0, 'message'=>$str_error]);
		}

		return response()->json(['status'=>1]);
	}


	public function validatePassword(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$all = $request->all();

		$password = $all['password'];
		$rules = ['password'=>'required|min:6'];
			
		$messages = [
			'password.required' => 'You must provide a password',
			'password.min' => 'Your password must be at least six characters'
		];
				
		$validator = \Validator::make($request->all(), $rules, $messages);
		if($validator->fails())
		{
			$errMsg = json_decode($validator->messages(), true);
            		$str_error = "";
            		$i = 1;
           		foreach($errMsg as $key => $value)
            		{
                		foreach($value as $val) {
                    			$str_error = ($val);
                		}
            		}

			return response()->json(['status'=>0, 'message'=>$str_error]);
		}

		return response()->json(['status'=>1]);
	}


	public function signUpUserProfile(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$all = $request->all();
		$bevuraNewCustomerData = [];

		$password = $all['password'];
		$emailAddress = $all['emailAddress'];
		$type = $all['roleCode'];
		$mobileNumber = $all['mobileNumber'];
		$firstName = $all['firstName'];
		$lastName = $all['lastName'];
			

		if($type=='PASSENGER')
		{
			$rules = ['password'=>'required|min:6','emailAddress'=>'required|email', 'mobileNumber'=>'required|numeric', 'roleCode'=>'required'];
			
			$messages = [
				'password.required' => 'You must provide a password',
				'password.min' => 'Your password must be at least six characters',
				'emailAddress.required' => 'You must provide an email address',
				'emailAddress.email' => 'Specify a valid email address.',
				'mobileNumber.required' => 'You must provide an email address',
				'mobileNumber.numeric' => 'Mobile number provided must only contain numbers',
				'roleCode.required' => 'Invalid request. Please start from the beginning'

			];
				
			$validator = \Validator::make($request->all(), $rules, $messages);
			if($validator->fails())
			{
				$errMsg = json_decode($validator->messages(), true);
            			$str_error = "";
            			$i = 1;
           			foreach($errMsg as $key => $value)
            			{
               	 		foreach($value as $val) {
                	    			$str_error = ($val);
                			}
            			}

				return response()->json(['status'=>0, 'message'=>$str_error]);
			}
			
			$usrCheck = User::where('mobileNumber', '=', $mobileNumber);
			if($usrCheck->count()>0)
			{
				return response()->json(['status'=>0, 'message'=>'You can not register with the mobile number you provided.']);
			}
			
			$user = new User();
			$user->mobileNumber = $mobileNumber;
			$user->password = \Hash::make($password);//\Hash::make($password);
			$user->email = $emailAddress;
			$user->name = $firstName." ".$lastName;
			$user->pin = null;
			$user->instance_id = "1.0";
			$user->status = 'Active';
			$user->role_code = $type;
			$user->passport_photo = null;
			$user->refCode = strtoupper(str_random(8));
			$user->outstanding_balance = 0.00;
			
			if($user->save())
			{
				$msg = "Welcome to Tweende ".$firstName."\nIn order to get started, we have signed you in so you can book your first ride.\nEnjoy!";
				send_sms($mobileNumber, $msg, "Bevura");
				
				$loginRoleType = $request->get('roleCode');
				$credentials = $request->only('mobileNumber', 'password');
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$token = null;
				$acctCount = 0;
				$rules = ['mobileNumber' => 'required|numeric', 
				'password' => 'required'];
				
				$messages = [
						'mobileNumber.required' => 'Your ProbasePay Mobile Account mobile number is required to login',
						'mobileNumber.numeric' => 'Your ProbasePay Mobile Account mobile number must consist of numbers',
						'password.required' => 'Your ProbasePay Mobile Account Password is required to login'
					];
				$validator = \Validator::make($request->all(), $rules, $messages);
				if($validator->fails())
				{
					$errMsg = json_decode($validator->messages(), true);
					$str_error = "";
					$i = 1;
					foreach($errMsg as $key => $value)
					{
						foreach($value as $val) {
							$str_error = ($val);
						}
					}
					return response()->json(['status'=>500, 'message'=>($str_error)]);
				}


				$credentials = $request->only('mobileNumber', 'password');
				$token = null;
				try {
					if (!$token = JWTAuth::attempt($credentials)) {
						return response()->json(['status'=>500, 'message'=>'Invalid username/password combination provided. Provide your valid username and password']);
					}
				} catch (JWTAuthException $e) {
					return response()->json(['status'=>500, 'message'=>'Invalid username/password combination provided. Provide your valid username and password']);
				}
				$user = JWTAuth::toUser($token);
				$vehicleDB = \DB::table('vehicles')->where('driver_user_id', '=', $user->id)->first();
				$driver = [];
				$activePassengerTripId = null;
				$passengerDeal = null;
				$cancelationReasons = null;
				if($user->role_code=='DRIVER' && $loginRoleType=='DRIVER')
				{
					$cancelationReasons = \App\CancelationReason::where('type', '=', 'DRIVER')->get();
					if($vehicleDB!=null)
					{
						$earnings = \DB::table('transactions')->where('driverUserId', '=', $user->id)->where('status', '=', 'Success')->get();
						$totalEarning = 0;
						foreach($earnings as $earning)
						{
							$totalEarning = $totalEarning + $earning->amount;
						}
						
						$vehicleTypeIcon = in_array($vehicleDB->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$vehicleDB->vehicle_type] : $vehicleIcons['Taxi'];
						$driver = ['id'=>$user->id, 'plate'=>$vehicleDB->vehicle_plate_number, 'type'=>$vehicleDB->vehicle_type, 'name'=>$user->name, 'vehicleIcon'=>$vehicleTypeIcon, 
							'refCode'=>$user->refCode, 'rating'=>$vehicleDB->avg_system_rating, 'balance'=>number_format($totalEarning, 2, '.', ','),  
							'profile_pix'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'earning'=>$totalEarning, 'outstanding_balance'=>$user->outstanding_balance];
					}
				}
				else if($user->role_code=='PASSENGER' && $loginRoleType=='PASSENGER')
				{
					$cancelationReasons = \App\CancelationReason::where('type', '=', 'PASSENGER')->get();
					$activePassengerTrip = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereIn('status', ['Pending', 'Going'])->orderBy('id', 'DESC')->first();
					if($activePassengerTrip!=null)
					{
						$activePassengerTripId = $activePassengerTrip->id;
					}
					else
					{
						$activePassengerDeal = \DB::table('driver_deals')->where('passenger_user_id', '=', $user->id)
							->whereIn('status', ['Pending'])->orderBy('id', 'DESC')->first();
							
						$sql = 'SELECT * FROM `driver_deals` WHERE 
							passenger_user_id = '.$user->id.' AND status IN ("Pending") AND 
							DATE_ADD(created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by created_at DESC';
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
							$currentVehicle = ['vehicleType'=>$activePassengerDealVehicle->vehicle_type, 'icon'=>$vehicleTypeIcon];
							$origin = ['location'=>['lat'=>$activePassengerDeal->origin_latitude, 'lng'=>$activePassengerDeal->origin_longitude, 'vicinity'=>$activePassengerDeal->origin_locality]];
							$dest = ['location'=>['lat'=>$activePassengerDeal->destination_longitude, 'lng'=>$activePassengerDeal->destination_longitude, 'vicinity'=>$activePassengerDeal->destination_locality]];
							$passengerDeal = ['locality'=>$activePassengerDeal->origin_locality, 'currentVehicle'=>$currentVehicle, 
							'originLat'=>$activePassengerDeal->origin_latitude, 'originLng'=>$activePassengerDeal->origin_longitude, 
							'origin'=>$origin, 'destination'=>$dest, 'distance'=>$activePassengerDeal->distance, 'fee'=>$activePassengerDeal->fee, 
							'currency'=>"ZMW", 'note'=>$activePassengerDeal->note, 'paymentMethod'=>$activePassengerDeal->payment_method];
						}
					}
					
				}
				else
				{
					return response()->json(['status'=>422, 'message'=>'Invalid User Account']);
				}
				
				$jk = new \App\Junk();
				$jk->data=json_encode(['status'=>1, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);
				$jk->save();
					


				
				return response()->json(['status'=>1, 'token'=>$token, 'cancelationReasons'=>$cancelationReasons, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);

			}
			else
			{
				return response()->json(['status'=>0, 'message'=>'New Passenger Account Creation was not Successful. Please try again']);
			}
			
			//TODO: implement JWT
		}
		else if($type=='DRIVER')
		{
			$meansOfIdentification = $all['meansOfIdentification'];
			$meansOfIdentificationNumber = $all['meansOfIdentificationNumber'];
			$gender = $all['gender'];
			$dateOfBirth = $all['dateOfBirth'];
			$mobileNumberLastDigits = $all['mobileNumberLastDigits'];
			$mobileNumberCountryCode = $all['mobileNumberCountryCode'];
			$deviceKey = $all['deviceKey'];
			$deviceId = $all['deviceId'];
			$identifier = $all['device_app_id'];
			$deviceVersion = $all['deviceVersion'];
			$deviceName = $all['deviceName'];
			$streetAddress = $all['streetAddress'];



			$rules = ['password'=>'required|min:6','emailAddress'=>'required|email', 'mobileNumber'=>'required|numeric', 'roleCode'=>'required', 
				'district'=>'required',
				'city'=>'required',
				'streetAddress'=>'required',
				'vehicleType'=>'required',
				'vehicleManufacturer'=>'required',
				'vehicleMake'=>'required',
				'passengerCount'=>'required',
				'manufactureYear'=>'required',
				'meansOfIdentification'=>'required',
				'meansOfIdentificationNumber'=>'required',
				'gender'=>'required',
				'dateOfBirth'=>'required'

			];
			
			$messages = [
				'password.required' => 'You must provide a password',
				'password.min' => 'Your password must be at least six characters',
				'emailAddress.required' => 'You must provide an email address',
				'emailAddress.email' => 'Specify a valid email address.',
				'mobileNumber.required' => 'You must provide an email address',
				'mobileNumber.numeric' => 'Mobile number provided must only contain numbers',
				'roleCode.required' => 'Invalid request. Please start from the beginning',
				'district.required' => 'Specify your district',
				'city.required' => 'Specify your city',
				'streetAddress.required' => 'Specify your house address',
				'vehicleType.required' => 'Specify the type of vehicle',
				'vehicleManufacturer.required' => 'Specify the manufacturer',
				'vehicleMake.required' => 'Specify the vehicle make',
				'passengerCount.required' => 'Specify the maximum number of passengers allowed',
				'manufactureYear.required' => 'Specify the year of manufacture of the vehicle',
				'meansOfIdentification.required' => 'Specify your means of identification',
				'meansOfIdentificationNumber.required' => 'Specify your means of identification',
				'gender.required' => 'Specify if you are Male or Female',
				'dateOfBirth.required' => 'Specify your date of birth'

			];
				
			$validator = \Validator::make($request->all(), $rules, $messages);
			if($validator->fails())
			{
				$errMsg = json_decode($validator->messages(), true);
            			$str_error = "";
            			$i = 1;
           			foreach($errMsg as $key => $value)
            			{
               	 		foreach($value as $val) {
                	    			$str_error = ($val);
                			}
            			}

				return response()->json(['status'=>0, 'message'=>$str_error]);
			}
			
			$usrCheck = User::where('mobileNumber', '=', $mobileNumber);
			if($usrCheck->count()>0)
			{
				return response()->json(['status'=>0, 'message'=>'You can not register with the mobile number you provided.']);
			}
			
			$user = new User();
			$user->mobileNumber = $mobileNumber;
			$user->password = \Hash::make($password);//\Hash::make($password);
			$user->email = $emailAddress;
			$user->name = $firstName." ".$lastName;
			$user->pin = null;
			$user->instance_id = "1.0";
			$user->status = 'Active';
			$user->role_code = $type;
			$user->passport_photo = null;
			$user->refCode = strtoupper(str_random(8));
			$user->outstanding_balance = 0.00;
			$user->means_of_identification = $meansOfIdentification;
			$user->means_of_identification_number = $meansOfIdentificationNumber;
			$user->gender = $gender;
			$user->date_of_birth = $dateOfBirth;
			
			if($user->save())
			{
				$vehicle = new \App\Vehicle();
				$vehicle->vehicle_plate_number = $request->get('vehiclePlateNo');
				$vehicle->vehicle_type = explode('|||', $request->get('vehicleType'))[1];
				$vehicle->vehicle_maker = $request->get('vehicleManufacturer');
				$vehicle->doors = $request->get('doors');
				$vehicle->year = $request->get('manufactureYear');
				$vehicle->insurance_expiry = date('Y-m-d H:i:s');
				$vehicle->vehicle_make = $request->get('vehicleMake');
				$vehicle->passenger_count = $request->get('passengerCount');
				$vehicle->share_ride = $request->get('shareRide');
				$vehicle->driver_user_id = $user->id;
				$vehicle->user_full_name = $firstName." ".$lastName;
				$vehicle->avg_system_rating = 0.0;
				$vehicle->status = 'Valid';
				$vehicle->outstanding_balance = 0.0;
				$vehicle->rating_count = 0;
				$vehicle->share_ride = 0;
				$vehicle->trips_completed_count = 0;
				$vehicle->vehicle_type_id = explode('|||', $request->get('vehicleType'))[0];
				$vehicle->save();



				$msg = "Welcome to Tweende ".$firstName."\nIn order to get started, we have signed you in so you can provide a ride to customers.\nEnjoy!";
				send_sms($mobileNumber, $msg, "Bevura");
				
				$loginRoleType = $request->get('roleCode');
				$credentials = $request->only('mobileNumber', 'password');
				$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
				$vehicleTypeKeys = array_keys($vehicleIcons);
				$token = null;
				$acctCount = 0;
				$rules = ['mobileNumber' => 'required|numeric', 
				'password' => 'required'];
				
				$messages = [
						'mobileNumber.required' => 'Your ProbasePay Mobile Account mobile number is required to login',
						'mobileNumber.numeric' => 'Your ProbasePay Mobile Account mobile number must consist of numbers',
						'password.required' => 'Your ProbasePay Mobile Account Password is required to login'
					];
				$validator = \Validator::make($request->all(), $rules, $messages);
				if($validator->fails())
				{
					$errMsg = json_decode($validator->messages(), true);
					$str_error = "";
					$i = 1;
					foreach($errMsg as $key => $value)
					{
						foreach($value as $val) {
							$str_error = ($val);
						}
					}
					return response()->json(['status'=>500, 'message'=>($str_error)]);
				}


				$credentials = $request->only('mobileNumber', 'password');
				$token = null;
				try {
					if (!$token = JWTAuth::attempt($credentials)) {
						return response()->json(['status'=>500, 'message'=>'Invalid username/password combination provided. Provide your valid username and password']);
					}
				} catch (JWTAuthException $e) {
					return response()->json(['status'=>500, 'message'=>'Invalid username/password combination provided. Provide your valid username and password']);
				}
				$user = JWTAuth::toUser($token);
				$vehicleDB = \DB::table('vehicles')->where('driver_user_id', '=', $user->id)->first();
				$driver = [];
				$activePassengerTripId = null;
				$passengerDeal = null;
				$cancelationReasons = null;
				if($user->role_code=='DRIVER' && $loginRoleType=='DRIVER')
				{
					$cancelationReasons = \App\CancelationReason::where('type', '=', 'DRIVER')->get();
					if($vehicleDB!=null)
					{
						$earnings = \DB::table('transactions')->where('driverUserId', '=', $user->id)->where('status', '=', 'Success')->get();
						$totalEarning = 0;
						foreach($earnings as $earning)
						{
							$totalEarning = $totalEarning + $earning->amount;
						}
						
						$vehicleTypeIcon = in_array($vehicleDB->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$vehicleDB->vehicle_type] : $vehicleIcons['Taxi'];
						$driver = ['id'=>$user->id, 'plate'=>$vehicleDB->vehicle_plate_number, 'type'=>$vehicleDB->vehicle_type, 'name'=>$user->name, 'vehicleIcon'=>$vehicleTypeIcon, 
							'refCode'=>$user->refCode, 'rating'=>$vehicleDB->avg_system_rating, 'balance'=>number_format($totalEarning, 2, '.', ','),  
							'profile_pix'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'earning'=>$totalEarning, 'outstanding_balance'=>$user->outstanding_balance];
					}

					try
					{
						//CREATE BEVURA WALLET
						$otherName = "";
						$password = $password;
						$confirmPassword = $password;
						$mobileNumber = $mobileNumberLastDigits;
						$countryCode = $mobileNumberCountryCode;
						//$username = $request->get('username');
						//$password = $request->get('password');
					
						$url = 'https://'.$this->getServiceBaseURL.'/mobile-bridge/api/v1/register-customer';
						//return response()->json($password);
						$data = 'firstName='.$firstName."&lastName=".$lastName."&otherName=".$otherName."&password=".$password."&confirmPassword=".$confirmPassword."&countrycode=".$countryCode."&mobileNumber=".($mobileNumber);

						if($deviceKey!=null && strlen($deviceKey)>0 && $deviceId!=null && strlen($deviceId)>0)
						{
							$data = $data.'&deviceKey='.$deviceKey;
							$data = $data.'&deviceId='.$deviceId;
							$data = $data.'&identifier='.$identifier;
							$data = $data.'&deviceVersion='.$deviceVersion;
							$data = $data.'&deviceName='.$deviceName;
						}

						//return response()->json($data);
						$server_output = sendPostRequestForBevura($url, $data);
						$bevuraNewCustomerData = json_decode($server_output);
						if($bevuraNewCustomerData!=null && $bevuraNewCustomerData->success==true)
						{
							$customerVerificationNumber = $bevuraNewCustomerData->verificationNumber;

							$username = $mobileNumberCountryCode."".$mobileNumberLastDigits;
							//$password = $request->json()->get('password');
							$autoAuthenticate = 1;
							//$username = $request->get('username');
							//$password = $request->get('password');
							$url = 'https://'.$this->getServiceBaseURL.'/mobile-bridge/api/v1/authenticate-username';
							//return response()->json($password);
							$data = 'username='.$username."&password=".$password."&isMobile=1";
							if($autoAuthenticate!=null)
							{
								$data = $data."&autoAuthenticate=".$autoAuthenticate;
							}

							$server_output = sendPostRequestForBevura($url, $data);
							//$bevuraNewCustomerData->authData = $server_output;
							$authData = json_decode($server_output);
							$customerToken = $authData->token;




							$data = [];
							$data['homeAddress'] = $all['streetAddress'];
							$data['city'] = $all['city'];
							$data['district'] = $all['district'];
							$data['meansOfIdentification'] = $meansOfIdentification;
							$data['identityNumber'] = $meansOfIdentificationNumber;
							$data['email'] = $emailAddress;
							$data['gender'] = $gender;
							$data['dateOfBirth'] = $dateOfBirth;
							$data['addNewAccountCustomerVerificationNumber'] = $customerVerificationNumber;
							$data['accountCurrency'] = "ZMW";
							$data['accountType'] = "SAVINGS";
							$data['token'] = $customerToken;
							//$data['openingAccountAmount'] = $all['openingAccountAmount'];
							$data['merchantCode'] = PAYMENT_MERCHANT_ID;
							$data['deviceCode'] = PAYMENT_DEVICE_ID;
							$data['isTokenize'] = isset($all['isTokenize']) ? $all['isTokenize'] : null;
							//return response()->json($data);
			
							$dataStr = "";
							foreach($data as $d => $v)
							{
								$dataStr = $dataStr."".$d."=".$v."&";
							}
							$token = $request->json()->get('token');
		

							$url = '';
							$url = 'https://'.$this->getServiceBaseURL.'/mobile-bridge/api/v1/create-wallet/1';
							//return response()->json($url);
							//$data = 'token='.$token;
							$server_output = sendPostRequestForBevura($url, $dataStr);
							//return response()->json($server_output);
							//echo ($server_output);
							//dd(1);
							$authData = json_decode($server_output);
							$bevuraNewCustomerData->walletDetails = $authData;
							/**/

						}
					}
					catch(\Exception $e)
					{
						$bevuraNewCustomerData = ['err'=>$e->getMessage(), 'errLine'=>$e->getLine()];
					}
					
				}
				else if($user->role_code=='PASSENGER' && $loginRoleType=='PASSENGER')
				{
					$cancelationReasons = \App\CancelationReason::where('type', '=', 'PASSENGER')->get();
					$activePassengerTrip = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereIn('status', ['Pending', 'Going'])->orderBy('id', 'DESC')->first();
					if($activePassengerTrip!=null)
					{
						$activePassengerTripId = $activePassengerTrip->id;
					}
					else
					{
						$activePassengerDeal = \DB::table('driver_deals')->where('passenger_user_id', '=', $user->id)
							->whereIn('status', ['Pending'])->orderBy('id', 'DESC')->first();
							
						$sql = 'SELECT * FROM `driver_deals` WHERE 
							passenger_user_id = '.$user->id.' AND status IN ("Pending") AND 
							DATE_ADD(created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by created_at DESC';
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
							$currentVehicle = ['vehicleType'=>$activePassengerDealVehicle->vehicle_type, 'icon'=>$vehicleTypeIcon];
							$origin = ['location'=>['lat'=>$activePassengerDeal->origin_latitude, 'lng'=>$activePassengerDeal->origin_longitude, 'vicinity'=>$activePassengerDeal->origin_locality]];
							$dest = ['location'=>['lat'=>$activePassengerDeal->destination_longitude, 'lng'=>$activePassengerDeal->destination_longitude, 'vicinity'=>$activePassengerDeal->destination_locality]];
							$passengerDeal = ['locality'=>$activePassengerDeal->origin_locality, 'currentVehicle'=>$currentVehicle, 
							'originLat'=>$activePassengerDeal->origin_latitude, 'originLng'=>$activePassengerDeal->origin_longitude, 
							'origin'=>$origin, 'destination'=>$dest, 'distance'=>$activePassengerDeal->distance, 'fee'=>$activePassengerDeal->fee, 
							'currency'=>"ZMW", 'note'=>$activePassengerDeal->note, 'paymentMethod'=>$activePassengerDeal->payment_method];
						}
					}
					
				}
				else
				{
					return response()->json(['status'=>422, 'message'=>'Invalid User Account']);
				}
				
				$jk = new \App\Junk();
				$jk->data=json_encode(['status'=>1, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);
				$jk->save();
					


				
				return response()->json(['status'=>1, 'bevuraNewCustomerData'=>$bevuraNewCustomerData, 'token'=>$token, 'cancelationReasons'=>$cancelationReasons, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
					'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);

			}
			else
			{
				return response()->json(['status'=>0, 'message'=>'New Passenger Account Creation was not Successful. Please try again']);
			}
			
			//TODO: implement JWT
		}

	}



    public function registerOTP(RegisterOTPRequest $request)
    {

        $rules=[
            //
            'otpAcctNumber'=>'required|size:10',
            'otpMobileNumber' => 'required|size:11',
            'otpNumber'=>'required',
        ];

        $messages=[
            //
            'otpAcctNumber.required'=>'Account Number must be provided',
            'otpAcctNumber.size'=>'Account Number must be a 10-digit number',
            'otpMobileNumber.required' => 'Your registered mobile number must be provided',
            'otpMobileNumber.size'=>'Mobile number must be a 11-digits',
            'otpNumber.required' => 'Authentication Code/OTP must be provided',
        ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if($validator->fails())
        {
            $errMsg = json_decode($validator->messages(), true);
            $str_error = "";
            $i = 1;
            foreach($errMsg as $key => $value)
            {
                foreach($value as $val) {
                    $str_error = ($val);
                }
            }
            return response()->json(($str_error), 500);
        }
        $otpAcctNumber = $request->get('otpAcctNumber');
        $otpMobileNumber = $request->get('otpMobileNumber');
        $otpNumber = $request->get('otpNumber');


        $credentials = $request->only('username', 'password');
        if (\Auth::attempt(['mobileNumber' => $request->otpMobileNumber, 'password' => $request->otpNumber])) {

            $dbUser = NULL;

            if($otpNumber!=NULL) {
                $dbUser = \DB::table('useraccounts')
                    ->join('users', 'useraccounts.userId', '=', 'users.id')
                    ->where('useraccounts.accountNumber', '=', $otpAcctNumber)
                    ->where('users.mobileNumber', '=', $request->otpMobileNumber);
            }
            if ($dbUser!=NULL && $dbUser->count()>0) {
                User::whereId($dbUser->first()->userId)->update(
                    [
                        'status' => 'Active'
                    ]
                );
                return response()->json([
                    'status' => true
                ]);
            }else{
                return response()->json(['failed_to_validate_otp'], 500);
            }
        }


        //TODO: implement JWT

    }


    public function login(LoginRequest $request)
    {
        //TODO: authenticate JWT
		$loginRoleType = $request->get('roleCode');
		$credentials = $request->only('mobileNumber', 'password');
		$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
		$vehicleTypeKeys = array_keys($vehicleIcons);
        $token = null;
        $acctCount = 0;
        $rules = ['mobileNumber' => 'required|numeric', 
		'password' => 'required'];
		
        $messages = [
                'mobileNumber.required' => 'Your ProbasePay Mobile Account mobile number is required to login',
				'mobileNumber.numeric' => 'Your ProbasePay Mobile Account mobile number must consist of numbers',
				'password.required' => 'Your ProbasePay Mobile Account Password is required to login'
            ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if($validator->fails())
        {
            $errMsg = json_decode($validator->messages(), true);
            $str_error = "";
            $i = 1;
            foreach($errMsg as $key => $value)
            {
                foreach($value as $val) {
                    $str_error = ($val);
                }
            }
            return response()->json(['status'=>500, 'message'=>($str_error)]);
        }


        $credentials = $request->only('mobileNumber', 'password');
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status'=>500, 'dt'=>$credentials, 'message'=>'Invalid username/password combination provided. Provide your valid username and password']);
            }
        } catch (JWTAuthException $e) {
            return response()->json(['status'=>500, 'message'=>'Invalid username/password combination provided. Provide your valid username and password']);
        }
		$user = JWTAuth::toUser($token);
		$vehicleDB = \DB::table('vehicles')->where('driver_user_id', '=', $user->id)->first();
		$driver = [];
		$activePassengerTripId = null;
		$passengerDeal = null;
		$cancelationReasons = null;
		if($user->role_code=='DRIVER' && $loginRoleType=='DRIVER')
		{
			$cancelationReasons = \App\CancelationReason::where('type', '=', 'DRIVER')->get();
			if($vehicleDB!=null)
			{
				$earnings = \DB::table('transactions')->where('driverUserId', '=', $user->id)->where('status', '=', 'Success')->get();
				$totalEarning = 0;
				foreach($earnings as $earning)
				{
					$totalEarning = $totalEarning + $earning->amount;
				}
				
				$vehicleTypeIcon = in_array($vehicleDB->vehicle_type, $vehicleTypeKeys) ? $vehicleIcons[$vehicleDB->vehicle_type] : $vehicleIcons['Taxi'];
				$driver = ['id'=>$user->id, 'plate'=>$vehicleDB->vehicle_plate_number, 'type'=>$vehicleDB->vehicle_type, 'name'=>$user->name, 'vehicleIcon'=>$vehicleTypeIcon, 
					'refCode'=>$user->refCode, 'rating'=>$vehicleDB->avg_system_rating, 'balance'=>number_format($totalEarning, 2, '.', ','),  
					'profile_pix'=>$user->passport_photo!=null && strlen($user->passport_photo)>0 ? "http://taxizambia.probasepay.com/users/".$user->passport_photo : null, 'earning'=>$totalEarning, 'outstanding_balance'=>$user->outstanding_balance];
			}
		}
		else if($user->role_code=='PASSENGER' && $loginRoleType=='PASSENGER')
		{
			$cancelationReasons = \App\CancelationReason::where('type', '=', 'PASSENGER')->get();
			$activePassengerTrip = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereIn('status', ['Pending', 'Going'])->orderBy('id', 'DESC')->first();
			if($activePassengerTrip!=null)
			{
				$activePassengerTripId = $activePassengerTrip->id;
			}
			else
			{
				$activePassengerDeal = \DB::table('driver_deals')->where('passenger_user_id', '=', $user->id)
					->whereIn('status', ['Pending'])->orderBy('id', 'DESC')->first();
					
				$sql = 'SELECT * FROM `driver_deals` WHERE 
					passenger_user_id = '.$user->id.' AND status IN ("Pending") AND 
					DATE_ADD(created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by created_at DESC';
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
					$currentVehicle = ['vehicleType'=>$activePassengerDealVehicle->vehicle_type, 'icon'=>$vehicleTypeIcon];
					$origin = ['location'=>['lat'=>$activePassengerDeal->origin_latitude, 'lng'=>$activePassengerDeal->origin_longitude, 'vicinity'=>$activePassengerDeal->origin_locality]];
					$dest = ['location'=>['lat'=>$activePassengerDeal->destination_longitude, 'lng'=>$activePassengerDeal->destination_longitude, 'vicinity'=>$activePassengerDeal->destination_locality]];
					$passengerDeal = ['locality'=>$activePassengerDeal->origin_locality, 'currentVehicle'=>$currentVehicle, 
					'originLat'=>$activePassengerDeal->origin_latitude, 'originLng'=>$activePassengerDeal->origin_longitude, 
					'origin'=>$origin, 'destination'=>$dest, 'distance'=>$activePassengerDeal->distance, 'fee'=>$activePassengerDeal->fee, 
					'currency'=>"ZMW", 'note'=>$activePassengerDeal->note, 'paymentMethod'=>$activePassengerDeal->payment_method];
				}
			}
			
		}
		else
		{
			return response()->json(['status'=>422, 'message'=>'Invalid User Account']);
		}
		
		$jk = new \App\Junk();
		$jk->data=json_encode(['status'=>1, 'token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>$user->passport_photo==null ? null : "http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
			'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);
		$jk->save();
			


		
        return response()->json(['status'=>1, 'token'=>$token, 'cancelationReasons'=>$cancelationReasons, 'user'=>['id'=>$user->id, 'photoURL'=>$user->passport_photo==null ? null : "http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
			'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);

    }
	
	
	
	
	/*public function login(LoginRequest $request)
    {
        //TODO: authenticate JWT
		$loginRoleType = $request->get('roleCode');
		$credentials = $request->only('mobileNumber', 'password');
		$vehicleIcons = ['Sedan'=>'sedan', 'SUV'=>'suv', 'Sports'=>'sports', 'Luxury'=>'luxury', 'Taxi'=>'taxi'];
		$vehicleTypeKeys = array_keys($vehicleIcons);
        $token = null;
        $acctCount = 0;
        $rules = ['mobileNumber' => 'required|numeric', 
		'password' => 'required'];
		
        $messages = [
                'mobileNumber.required' => 'Your ProbasePay Mobile Account mobile number is required to login',
				'mobileNumber.numeric' => 'Your ProbasePay Mobile Account mobile number must consist of numbers',
				'password.required' => 'Your ProbasePay Mobile Account Password is required to login'
            ];
        $validator = \Validator::make($request->all(), $rules, $messages);
        if($validator->fails())
        {
            $errMsg = json_decode($validator->messages(), true);
            $str_error = "";
            $i = 1;
            foreach($errMsg as $key => $value)
            {
                foreach($value as $val) {
                    $str_error = ($val);
                }
            }
            return response()->json(($str_error), 500);
        }


        $credentials = $request->only('mobileNumber', 'password');
        $token = null;
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['invalid_email_or_password'], 422);
            }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
		$user = JWTAuth::toUser($token);
		$vehicleDB = \DB::table('vehicles')->where('driver_user_id', '=', $user->id)->first();
		$driver = [];
		$activePassengerTripId = 0;
		$passengerDeal = null;
		
		if($user->role_code=='DRIVER' && $loginRoleType=='DRIVER')
		{
			if($vehicleDB!=null)
			{
				$earnings = \DB::table('transactions')->where('driverUserId', '=', $user->id)->get();
				$totalEarning = 0;
				foreach($earnings as $earning)
				{
					$totalEarning = $totalEarning + $earning->amount;
				}
				$driver = ['id'=>$user->id, 'plate'=>$vehicleDB->vehicle_plate_number, 'type'=>$vehicleDB->vehicle_type, 'name'=>$user->name, 
					'refCode'=>$user->refCode, 'rating'=>$vehicleDB->avg_system_rating, 'balance'=>number_format($vehicleDB->outstanding_balance, 2, '.', ','),  
					'profile_pix'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'earning'=>$totalEarning];
			}
		}
		else if($user->role_code=='PASSENGER' && $loginRoleType=='PASSENGER')
		{
			$activePassengerTrip = \DB::table('trips')->where('passenger_user_id', '=', $user->id)->whereIn('status', ['Pending', 'Going'])->orderBy('id', 'DESC')->first();
			if($activePassengerTrip!=null)
			{
				$activePassengerTripId = $activePassengerTrip->id;
			}
			else
			{
				$activePassengerDeal = \DB::table('driver_deals')->where('passenger_user_id', '=', $user->id)
					->whereIn('status', ['Pending'])->orderBy('id', 'DESC')->first();
					
				$sql = 'SELECT * FROM `driver_deals` WHERE 
					passenger_user_id = '.$user->id.' AND status IN ("Pending") AND 
					DATE_ADD(created_at, INTERVAL '.DEAL_MAKING_INTERVAL.' MINUTE) > NOW() ORDER by created_at DESC';
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
					$currentVehicle = ['vehicleType'=>$activePassengerDealVehicle->vehicle_type, 'icon'=>$vehicleTypeIcon];
					$origin = ['location'=>['lat'=>$activePassengerDeal->origin_latitude, 'lng'=>$activePassengerDeal->origin_longitude, 'vicinity'=>$activePassengerDeal->origin_locality]];
					$dest = ['location'=>['lat'=>$activePassengerDeal->destination_longitude, 'lng'=>$activePassengerDeal->destination_longitude, 'vicinity'=>$activePassengerDeal->destination_locality]];
					$passengerDeal = ['locality'=>$activePassengerDeal->origin_locality, 'currentVehicle'=>$currentVehicle, 
					'originLat'=>$activePassengerDeal->origin_latitude, 'originLng'=>$activePassengerDeal->origin_longitude, 
					'origin'=>$origin, 'destination'=>$dest, 'distance'=>$activePassengerDeal->distance, 'fee'=>$activePassengerDeal->fee, 
					'currency'=>"ZMW", 'note'=>$activePassengerDeal->note, 'paymentMethod'=>$activePassengerDeal->payment_method];
				}
			}
			
		}
		else
		{
			return response()->json(['invalid_email_or_password'], 422);
		}
        return response()->json(['token'=>$token, 'user'=>['id'=>$user->id, 'photoURL'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo, 'displayName'=>$user->name, 
			'name'=>$user->name, 'phoneNumber'=>$user->mobileNumber, 'email'=>$user->email], 'driver'=>$driver, 'activePassengerTripId'=>$activePassengerTripId, 'passengerDeal'=>$passengerDeal]);

    }*/
	
	
	
	public function recoverPassword()
	{
		$input = \Input::all();
		$mobileNumber = $input['mobileNumber'];
		$type = $input['type'];
		$user = User::where('mobileNumber', '=', $input['mobileNumber'])->where('role_code', '=', $type)->first();
		if($user!=null)
		{
			$password = str_random(8);
			$user->password = \Hash::make($password);
			if($user->save())
			{
				$mobile = strpos($mobile, "260")==0 ? $mobile : ("26".$mobile);
				send_sms($mobile, $msg, $sender=NULL);
				return response()->json(['status'=>1, 'password'=>$password]);
			}
		}
		return response()->json(['status'=>0]);
	}
	
	
	public function getUserByToken()
	{
		//$token = JWTAuth::getToken();
		//$tripId 	= $request->has('tripId') ? $request->get('tripId') : null;
		//$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjQsImlzcyI6Imh0dHA6XC9cL3RheGl6YW1iaWEuY29tXC9hcGlcL3YxXC9hdXRoXC9sb2dpbiIsImlhdCI6MTUwNzIzMzAwOCwiZXhwIjoxNTA3MjM2NjA4LCJuYmYiOjE1MDcyMzMwMDgsImp0aSI6IjRmMWU0MDVlNDc0YmY2YTI2NTg3ZjQ2MDRjZTQ4ZDE0In0.8B66_SgOqHG9zhYvPjUKkDy1PqpDbCst6XjVs88IWPI';
		//$user = JWTAuth::toUser($token);
		$token = JWTAuth::getToken();
		$user = JWTAuth::toUser($token);
		
		
        try {
            
			if($user!=null)
			{
				$user_ = ['id'=>$user->id, 'name'=>$user->name,
					'email' => $user->email, 'role_code'=>$user->role_code, 'passport_photo'=>"http://taxizambia.probasepay.com/users/".$user->passport_photo];
				return response()->json(['status'=>1, 'user'=>$user_]);
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



	
}
