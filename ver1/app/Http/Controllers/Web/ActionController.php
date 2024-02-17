<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\PullAccountsByTokenRequest;
use App\Http\Controllers\Controller;
use JWTAuth;

class ActionController extends Controller
{
    //
	//private $jwtauth;
	public function getIndex() {
    		return view('guest.index');
	}

    /*public function __construct(JWTAuth $jwtauth)
    {
        $this->middleware('jwt.auth', ['except'=> ['sendRequestForAPaymentCard', 'pullRegisterData', 'authenticateProbasePayWallet',
			'authenticateProbasePayWalletWithOtp']]);
        $this->jwtauth = $jwtauth;
    }
	*/

	public function __construct(){}


	public function getDashboard()
	{
		if(\Auth::user()== null)
		{
			return redirect('/auth/login');
		}else
		{
			$allTaxiDrivers = \App\User::where('role_code', '=', \App\Roles::$ROLE_DRIVER_USER)->count();
			$allPassengers = \App\User::where('role_code', '=', \App\Roles::$ROLE_PASSENGER_USER)->count();
			$lastTrip = \App\Trip::orderBy('created_at', 'DESC')->first();
			$sql = "Select sum(amount) as sm from transactions where status = 'Success' AND payment_method IN ('Card', 'Wallet', 'Cash')";
			$total_transactions = \DB::select($sql);
			$total_transactions = $total_transactions[0]->sm==null ? 0.00 : $total_transactions[0]->sm;
			$trip_requests = \App\DriverDeal::all()->count();
			$tripsCompleted = \App\Trip::where('status', '=', 'Completed & Paid')->count();
			$allTripRequests = \App\Trip::count();
			$tripsRejected = \App\Trip::whereIn('status', ['Passenger Canceled','Driver Canceled','Admin Canceled'])->count();;
			$cashAccrued = \DB::select("Select sum(amount) as sm from transactions where status = 'Success' AND payment_method IN ('Cash')");
			$cashAccrued = $cashAccrued[0]->sm==null ? 0.0 : $cashAccrued[0]->sm;
			$todayTransactionSum = \DB::select("Select sum(amount) as sm  from transactions where status = 'Success' AND DATE(created_at) = CURDATE()");
			$todayTransactionSum = $todayTransactionSum[0]->sm==null ? 0.00 : $todayTransactionSum[0]->sm;
			$lastWeekTransactionSum = \DB::select("Select sum(amount) as sm from transactions where status = 'Success' AND created_at >= (CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+6 DAY) AND created_at <= (CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-1 DAY)");
			$lastWeekTransactionSum = $lastWeekTransactionSum[0]->sm==null ? 0.00 : $lastWeekTransactionSum[0]->sm;
			//$lastFiveTrips = \DB::table('trips')->orderBy('created_at', 'DESC')->limit(5)->get();
			$lastFiveTrips = \DB::select("Select *  from trips where DATE(created_at) = CURDATE() ORDER BY created_at");
			$passengersByMonth = \DB::table('users')->where('role_code', '=', 'PASSENGER')->groupBy(\DB::raw('MONTH(created_at)'))
					->select(\DB::raw('count(id) as cid, MONTH(created_at) as mnt'))->get();
			//\DB::select('select count(id) as cid from users where role_code = "PASSENGER" GROUP BY MONTH(created_at)');
			$driversByMonth = \DB::table('users')->where('role_code', '=', 'DRIVER')->groupBy(\DB::raw('MONTH(created_at)'))
					->select(\DB::raw('count(id) as cid, MONTH(created_at) as mnt'))->get()->toArray();
			//select count(id) as cid from users where role_code = "DRIVER" GROUP BY MONTH(created_at)');
			$dM = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
			$pM = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
			$x1 = 0;
			$y1 = 0;
			foreach($driversByMonth as $driverByMonth)
			{
				if($x1==0)
				{
					$x1 = $driverByMonth->mnt;
				}
				$dM[$driverByMonth->mnt] = $driverByMonth->cid;
			}
			foreach($passengersByMonth as $passengerByMonth)
			{
				if($y1==0)
				{
					$y1 = $passengerByMonth->mnt;
				}
				$pM[$passengerByMonth->mnt] = $passengerByMonth->cid;
			}

			if($x1>7)
				$x1=4;
			else
				$x1=0;
			if($y1>7)
				$y1=4;
			else
				$y1=0;


			$general_income = 0;
			$pageTitle = "My Dashboard - Tweende";
			return view('admin.dashboard_admin', compact('dM', 'pM', 'general_income', 'pageTitle', 'allTaxiDrivers', 'allPassengers',
					'x1', 'y1', 'lastTrip', 'total_transactions', 'trip_requests', 'tripsCompleted', 'allTripRequests', 'passengersByMonth',
					'driversByMonth', 'tripsRejected', 'cashAccrued', 'todayTransactionSum', 'lastWeekTransactionSum', 'lastFiveTrips'));
		}
	}

	/*Drivers & Passengers*/
	public function getAllDrivers(){
		$list = \DB::table('users')->join('vehicles', 'users.id', '=', 'vehicles.driver_user_id')
				->whereNull('users.deleted_at')->where('users.role_code', '=', \App\Roles::$ROLE_DRIVER_USER)
				->select('users.*', 'vehicles.vehicle_type', 'vehicles.vehicle_plate_number', 'vehicles.vehicle_maker', 'vehicles.status as vehicle_status')->get();
		$type = "Driver List";
		$pageTitle = "List of Drivers";
		$bigTitle = "List of Drivers";
		$titleDescription = "All drivers on Tweende";
		$smallTitle = "All Drivers";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}


	public function getAllVehicles(){
		$list = \DB::table('vehicles')->join('vehicle_trackers', 'vehicles.id', '=', 'vehicle_trackers.vehicle_id')
				->whereNull('vehicles.deleted_at')
				->select('vehicles.*', 'vehicle_trackers.current_latitude', 'vehicle_trackers.current_longitude')->get();
		$type = "Vehicle List";
		$pageTitle = "List of Vehicle";
		$bigTitle = "List of Vehicle";
		$titleDescription = "All vehicle on Tweende";
		$smallTitle = "All Vehicle";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}


	public function getAllPassengers(){
		$list = \DB::table('users')
				->whereNull('users.deleted_at')->where('users.role_code', '=', \App\Roles::$ROLE_PASSENGER_USER)
				->select('users.*')->get();
		$type = "Passenger List";
		$pageTitle = "List of Passengers";
		$bigTitle = "List of Passengers";
		$titleDescription = "All passengers on Tweende";
		$smallTitle = "All Passengers";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	/*Trip Requests*/
	public function getAllTripRequests(){
		$list = \DB::table('driver_deals')->join('users', 'driver_deals.driver_user_id', '=', 'users.id')
				->join('vehicles', 'driver_deals.vehicle_id', '=', 'vehicles.id')
				->whereNull('driver_deals.deleted_at')
				->select('driver_deals.*', 'users.name as driver_user_name', 'vehicles.vehicle_plate_number')->get();
		$type = "Trip Request List";
		$pageTitle = "List of Trip Requests";
		$bigTitle = "List of Trip Requests";
		$titleDescription = "All Trip Requests on Tweende";
		$smallTitle = "All Trip Requests";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getAcceptedTripRequests(){
		$list = \DB::table('driver_deals')->join('users', 'driver_deals.driver_user_id', '=', 'users.id')
				->join('vehicles', 'driver_deals.vehicle_id', '=', 'vehicles.id')
				->whereNull('driver_deals.deleted_at')
				->where(function($x){
					$x->whereIn('driver_deals.status', ['Accepted', 'Completed & Paid', 'Going', 'Completed']);
				})
				->select('driver_deals.*', 'users.name as driver_user_name', 'vehicles.vehicle_plate_number')->get();
		
		$type = "Accepted Trip Request List";
		$pageTitle = "List of Accepted Trip Requests";
		$bigTitle = "List of Accepted Trip Requests";
		$titleDescription = "All Accepted Trip Requests on Tweende";
		$smallTitle = "All Accepted Trip Requests";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getCanceledTripRequests(){
		$list = \DB::table('driver_deals')->join('users', 'driver_deals.driver_user_id', '=', 'users.id')
				->join('vehicles', 'driver_deals.vehicle_id', '=', 'vehicles.id')
				->whereNull('driver_deals.deleted_at')
				->where(function($x){
					$x->where('driver_deals.status', '=', 'Passenger Canceled')
						->orWhere('driver_deals.status', '=', 'Driver Canceled');
				})->select('driver_deals.*', 'users.name as driver_user_name', 'vehicles.vehicle_plate_number')->get();
		$type = "Canceled Trip Request List";
		$pageTitle = "List of Canceled Trip Requests";
		$bigTitle = "List of Canceled Trip Requests";
		$titleDescription = "All Canceled Trip Requests on Tweende";
		$smallTitle = "All Canceled Trip Requests";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getPendingTripRequests(){
		$list = \DB::table('driver_deals')->join('users', 'driver_deals.driver_user_id', '=', 'users.id')
				->join('vehicles', 'driver_deals.vehicle_id', '=', 'vehicles.id')
				->whereNull('driver_deals.deleted_at')
				->where('driver_deals.status', '=', 'Pending')
				->select('driver_deals.*', 'users.name as driver_user_name', 'vehicles.vehicle_plate_number')->orderBy('driver_deals.id', 'DESC')->get();
		$type = "Pending Trip Request List";
		$pageTitle = "List of Pending Trip Requests";
		$bigTitle = "List of Pending Trip Requests";
		$titleDescription = "All Pending Trip Requests on Tweende";
		$smallTitle = "All Pending Trip Requests";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	/*Trips*/
	public function getAllTrips(){
		$list = \DB::table('trips')
				->whereNull('trips.deleted_at')->get();
		$type = "Trips List";
		$pageTitle = "List of Trips";
		$bigTitle = "List of Trips";
		$titleDescription = "All Trips on Tweende";
		$smallTitle = "All Trips";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getCompletedTrips(){
		$list = \DB::table('trips')->whereIn('status', ['Completed', 'Completed & Paid'])
				->whereNull('trips.deleted_at')->get();
		$type = "Completed Trips List";
		$pageTitle = "List of Completed Trips";
		$bigTitle = "List of Completed Trips";
		$titleDescription = "All Completed Trips on Tweende";
		$smallTitle = "All Completed Trips";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getOngoingTrips(){
		$list = \DB::table('trips')->whereIn('status', ['Going'])
				->whereNull('trips.deleted_at')->get();
		$type = "Ongoing Trips List";
		$pageTitle = "List of Ongoing Trips";
		$bigTitle = "List of Ongoing Trips";
		$titleDescription = "All Ongoing Trips on Tweende";
		$smallTitle = "All Ongoing Trips";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getCanceledTrips(){
		$list = \DB::table('trips')->whereIn('status', ['Passenger Canceled', 'Driver Canceled'])
				->whereNull('trips.deleted_at')->get();
		$type = "Canceled Trips List";
		$pageTitle = "List of Canceled Trips";
		$bigTitle = "List of Canceled Trips";
		$titleDescription = "All Canceled Trips on Tweende";
		$smallTitle = "All Canceled Trips";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	/*Payments*/
	public function getAllPayments(){
		$list = \DB::table('transactions')
				->join('users', 'transactions.driverUserId', '=', 'users.id')
				->join('trips', 'transactions.tripId', '=', 'trips.id')
				->whereNull('transactions.deleted_at')
				->select('transactions.*', 'users.name as vehicle_driver_user_name',
						'trips.trip_identifier', 'trips.deal_booking_group_id',
						'trips.origin_vicinity', 'trips.destination_vicinity')->get();
		$type = "All Payment Transactions List";
		$pageTitle = "List of Payment Transactions";
		$bigTitle = "List of Payment Transactions";
		$titleDescription = "All Payment Transactions on Tweende";
		$smallTitle = "All Payment Transactions";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getPendingPayments(){
		$list = \DB::table('transactions')
				->join('users', 'transactions.driverUserId', '=', 'users.id')
				->join('trips', 'transactions.tripId', '=', 'trips.id')
				->whereNull('transactions.deleted_at')
				->where('transactions.status', '=', 'Pending')
				->select('transactions.*', 'users.name as vehicle_driver_user_name',
						'trips.trip_identifier', 'trips.deal_booking_group_id',
						'trips.origin_vicinity', 'trips.destination_vicinity')->get();
		$type = "Pending Payment Transactions List";
		$pageTitle = "List of Pending Payment Transactions";
		$bigTitle = "List of Pending Payment Transactions";
		$titleDescription = "All Pending Payment Transactions on Tweende";
		$smallTitle = "All Pending Payment Transactions";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}


	public function getCashPayments(){
		$list = \DB::table('transactions')
				->join('users', 'transactions.driverUserId', '=', 'users.id')
				->join('trips', 'transactions.tripId', '=', 'trips.id')
				->whereNull('transactions.deleted_at')
				->where('transactions.payment_method', '=', 'Cash')
				->select('transactions.*', 'users.name as vehicle_driver_user_name',
						'trips.trip_identifier', 'trips.deal_booking_group_id',
						'trips.origin_vicinity', 'trips.destination_vicinity')->get();
		$type = "Cash Payment Transactions List";
		$pageTitle = "List of Cash Payment Transactions";
		$bigTitle = "List of Cash Payment Transactions";
		$titleDescription = "All Cash Payment Transactions on Tweende";
		$smallTitle = "All Cash Payment Transactions";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getCardPayments(){
		$list = \DB::table('transactions')
				->join('users', 'transactions.driverUserId', '=', 'users.id')
				->join('trips', 'transactions.tripId', '=', 'trips.id')
				->whereNull('transactions.deleted_at')
				->where('transactions.payment_method', '=', 'Card')
				->select('transactions.*', 'users.name as vehicle_driver_user_name',
						'trips.trip_identifier', 'trips.deal_booking_group_id',
						'trips.origin_vicinity', 'trips.destination_vicinity')->get();
		$type = "Card Payment Transactions List";
		$pageTitle = "List of Card Payment Transactions";
		$bigTitle = "List of Card Payment Transactions";
		$titleDescription = "All Card Payment Transactions on Tweende";
		$smallTitle = "All Card Payment Transactions";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getWalletPayments(){
		$list = \DB::table('transactions')
				->join('users', 'transactions.driverUserId', '=', 'users.id')
				->join('trips', 'transactions.tripId', '=', 'trips.id')
				->whereNull('transactions.deleted_at')
				->where('transactions.payment_method', '=', 'Wallet')
				->select('transactions.*', 'users.name as vehicle_driver_user_name',
						'trips.trip_identifier', 'trips.deal_booking_group_id',
						'trips.origin_vicinity', 'trips.destination_vicinity')->get();
		$type = "Wallet Payment Transactions List";
		$pageTitle = "List of Wallet Payment Transactions";
		$bigTitle = "List of Wallet Payment Transactions";
		$titleDescription = "All Wallet Payment Transactions on Tweende";
		$smallTitle = "All Wallet Payment Transactions";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getWithdrawalRequests(){
		$list = \DB::table('withdrawal_requests')->join('users', 'withdrawal_requests.driver_user_id', '=', 'users.id')
				->whereNull('withdrawal_requests.deleted_at')
				->select('withdrawal_requests.*', 'users.outstanding_balance')->get();
		$type = "Withdrawal Requests List";
		$pageTitle = "List of Withdrawal Requests";
		$bigTitle = "List of Withdrawal Requests";
		$titleDescription = "All Withdrawal Requests on Tweende";
		$smallTitle = "All Withdrawal Requests";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getDriverPayouts(){
		
	}

	/*Settings*/
	public function getVehicleTypes(){
		$list = \DB::table('vehicle_types')->join('vehicle_traffic_costs', 'vehicle_types.id', '=', 'vehicle_traffic_costs.vehicle_type_id')->whereNull('vehicle_types.deleted_at')
			->select('vehicle_types.*', 'vehicle_traffic_costs.*', 'vehicle_types.id', 'vehicle_types.status')->get();
		
		$type = "Vehicle Types List";
		$pageTitle = "List of Vehicle Types";
		$bigTitle = "List of Vehicle Types";
		$titleDescription = "All Vehicle Types on Tweende";
		$smallTitle = "All Vehicle Types";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getVehicleManufacturers(){
		$list = \DB::table('vehicle_manufacturers')->whereNull('vehicle_manufacturers.deleted_at')->get();
		$type = "Vehicle Manufacturers List";
		$pageTitle = "List of Vehicle Manufacturers";
		$bigTitle = "List of Vehicle Manufacturers";
		$titleDescription = "All Vehicle Manufacturers on Tweende";
		$smallTitle = "All Vehicle Manufacturers";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getVehicleMakers(){
		$list = \DB::table('vehicle_makers')->whereNull('vehicle_makers.deleted_at')->get();
		$type = "Vehicle Makers List";
		$pageTitle = "List of Vehicle Makers";
		$bigTitle = "List of Vehicle Makers";
		$titleDescription = "All Vehicle Makers on Tweende";
		$smallTitle = "All Vehicle Makers";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}


	public function getDistricts(){
		$list = \DB::table('districts')->whereNull('districts.deleted_at')->get();
		$type = "Districts List";
		$pageTitle = "List of Districts";
		$bigTitle = "List of Districts";
		$titleDescription = "All Districts on Tweende";
		$smallTitle = "All Districts";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getCities(){
		$list = \DB::table('cities')->whereNull('cities.deleted_at')->get();
		$type = "Cities List";
		$pageTitle = "List of Cities";
		$bigTitle = "List of Cities";
		$titleDescription = "All Cities on Tweende";
		$smallTitle = "All Cities";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getTrafficCosts(){
		$list = \DB::table('traffic_costs')->whereNull('traffic_costs.deleted_at')->get();
		$type = "Traffic Costs List";
		$pageTitle = "List of Traffic Costs";
		$bigTitle = "List of Traffic Costs";
		$titleDescription = "All Traffic Costs on Tweende";
		$smallTitle = "All Traffic Costs";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}

	public function getVehicleTrafficCosts(){
		$list = \DB::table('vehicle_traffic_costs')
				->join('traffic_costs', 'vehicle_traffic_costs.traffic_cost_id', '=', 'traffic_costs.id')
				->whereNull('traffic_costs.deleted_at')
				->select('traffic_costs.*', 'vehicle_traffic_costs.id', 'vehicle_traffic_costs.vehicle_type')->get();
		$type = "Vehicle Traffic Costs List";
		$pageTitle = "List of Vehicle Traffic Costs";
		$bigTitle = "List of Vehicle Traffic Costs";
		$titleDescription = "All Vehicle Traffic Costs on Tweende";
		$smallTitle = "All Vehicle Traffic Costs";
		return view('admin.data', compact('list','type', 'smallTitle', 'bigTitle', 'titleDescription', 'pageTitle'));
	}


	public function getAddNewVehicleType(){
		$pageTitle = "Add New Vehicle Type";
		$bigTitle = "Add New Vehicle Type";
		$titleDescription = "Add Vehicle Type on Tweende";
		$smallTitle = "New Vehicle Type";
		return view('admin.new_vehicle_type', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription'));
	}
	
	
	public function getDriverDeposits(){
		$pageTitle = "Driver Cash Deposit";
		$bigTitle = "Driver Cash Deposit";
		$titleDescription = "Driver Cash Deposit on Tweende";
		$smallTitle = "Driver Cash Deposit";
		$list = \DB::table('users')
				->join('vehicles', 'vehicles.driver_user_id', '=', 'users.id')
				->whereNull('users.deleted_at')->whereNull('vehicles.deleted_at')->where('role_code', '=', 'DRIVER')
				->select('users.*', 'vehicles.*', 'users.id', 'vehicles.id as vehicleId')->get();
		return view('admin.new_driver_cash_deposit', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription', 'list'));
	}
	
	
	public function postFundDriverAccount(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$input = $request->all();
		dd($input);
		$amount = $input['amount'];
		$input = explode('|||',$input['driver']);
		$driver = \App\User::where('id', '=', $input[0])->first();
		$orderId = strtoupper(str_random(8));
		
		$params = [];
		$params["merchantId"]= PAYMENT_MERCHANT_ID;
		$params["deviceCode"]= PAYMENT_DEVICE_ID;
		$params["serviceTypeId"]= '1981511018900';
		$params["orderId"]= $orderId;
		$params["payerName"]= $driver->name;
		$params["payerEmail"]=$driver->email;
		$params["payerPhone"]=$driver->mobileNumber;
		$params["currency"]= "ZMW";
		$params["amount"][0]= $amount;
		$params["paymentItem"][0]= 'Fund Driver Account | K'.$amount;
		$params["responseurl"]= 'http://taxizambia.com/fund-account';
		$params["payerId"]= $driver->mobileNumber;
		$params["nationalId"]= $driver->mobileNumber;
		$params["scope"]= 'Fund Driver Account | K'.$amount;
		$params["description"]= 'Fund Driver Account | K'.$amount;
		$totalAmount = 0;
		for ($i1 = 0; $i1 < sizeof($params['amount']); $i1++) {
			$totalAmount = $totalAmount + floatval($params['amount'][$i1]);
		}
		$totalAmount = (number_format($totalAmount, 2, '.', ''));
		$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
			$params['orderId'].$totalAmount.$params['responseurl'].PAYMENT_API_KEY;
		$hash = hash('sha512', $toHash);
		$params["hash"]= $hash;
		
		
		$txn = new \App\Transaction();
		$txn->orderId = $orderId;
		$txn->requestData = json_encode($params);
		$txn->status = 'Pending';
		$txn->payeeUserId = $driver->id;
		$txn->payeeUserFullName = $driver->name;
		$txn->payeeUserMobile = $driver->mobileNumber;
		$txn->payment_method = 'Card';
		$txn->driverUserId = $driver->id;
		$txn->vehicleId = $input[2];
		$txn->adminUserId = \Auth::user()->id;
		$txn->amount = $amount;
		$txn->paymentType = 'Deposit';
		if($txn->save())
		{
			return view('guest.probasepay', compact('params'));
		}
		return \Redirect::back()->with('error', 'We could not process this payment at this time');
	}
	
	
	public function handleSuccessPaymentResponse(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$input = ($request->all());
		$resp = $input['resp'];
		$resp = json_decode($resp);


		if(isset($resp->scope) && $resp->scope=="TOKENIZE")
		{
			if($resp->success==1 && $resp->success==true)
			{
				$recL = [];
				$recL['listing'] = $resp->listing;
				$recL['key'] = explode('-', $resp->orderId)[0];
				$data1 = [];
				$data1['recL'] = json_encode($recL);
				$data1['status'] = 1;
				$data1['messageType'] = 'PAYCONF';
				$data = json_encode($data1);
				$data = 'data='.$data;
				$url = "http://140.82.52.195:8080/post-pay-configuration-tokenize";
					//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
				$server_output = sendPostRequestForBevura($url, $data);
				$jk = new \App\Junk();
				$jk->data=$server_output;
				$jk->save();

				$listing = $resp->listing;
				return view('guest.bevura_tokenize_page_success', compact('listing'));			}
		}
		else
		{
			if($resp->status=='0' && $resp->success=='1')
			{
				$orderId = $resp->order_ref;
				$transaction = \App\Transaction::where('orderId', '=', $orderId)->first();
				$requestData = $transaction->requestData;
				$requestData = json_decode($requestData, true);

				$transaction->responseData = $resp->paying_info;
				$transaction->payment_method = $resp->channel;
				$transaction->status = 'Success';


				if(isset($requestData['scope']) && $requestData['scope']=='TAXI WALLET DEPOSIT')
				{
					if($transaction->save())
					{
											
						
						$driver = \App\User::where('id', '=', $transaction->driverUserId)->first();
						$driver->outstanding_balance = $driver->outstanding_balance + $transaction->amount;
						$driver->save();/**/

						$transactionBreakdown = new \App\TransactionBreakdown();
						$transactionBreakdown->user_id = $transaction->driverUserId;
						$transactionBreakdown->transaction_type = "FUND WALLET";
						$transactionBreakdown->transaction_id = $transaction->id;
						$transactionBreakdown->trip_id = null;
						$transactionBreakdown->breakdown_amount = $transaction->amount;
						$transactionBreakdown->is_reversed = 0;
						$transactionBreakdown->details = "Fund Drivers Wallet";
						$transactionBreakdown->is_credit = 1;
						$transactionBreakdown->is_withdrawable = 0;
						$transactionBreakdown->save();

						$mobile = strpos($driver->mobileNumber, "260")==0 ? $driver->mobileNumber : ("26".$driver->mobileNumber);
						$msg = "Dear ".$driver->name."\nYour Tweende Driver wallet has been credited with ZMW".$transaction->amount.'. The money in your wallet is used for cash transactions. Each time you receive a cash payment from a rider, we debit the equivalent from your wallet. Without funding your wallet you can not receive requests for rides from passengers.';
						$sender = "Bevura";
						send_sms($mobile, $msg, $sender=NULL);
						//dd(11);
						return \Redirect::to('/payments/redirect-to-success-page/'.\Crypt::encrypt($transaction->id))->with('message', 'Wallet funded successfully.');
					}
					else
					{
						return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Wallet funding was not successful. Try again');

					}

				}
				
				$trip = \App\Trip::where('id', '=', $transaction->tripId)->first();
				$trip->status = 'Completed & Paid';
				$trip->paidYes = 1;
				$trip->save();
				
				$deal = \App\DriverDeal::where('id', '=', $trip->driver_deal_id)->first();
				$deal->status = 'Completed & Paid';
				$deal->save();
				
				
				if($transaction->save())
				{
					$vehicle = \App\Vehicle::where('id', '=', $trip->vehicle_id)->first();
					$vehicle->status = 'Available';
					$vehicle->save();
					
					
					$driver = \App\User::where('id', '=', $transaction->driverUserId)->first();
					$driver->totalPayments = $driver->totalPayments + $transaction->amount;
					$driver->save();/**/

					$fareBreakDownSettings = \App\FareBreakDownSetting::where('status', '=', 1)->get();
					$j = 0;
					$fareBreakDownSettingCount = $fareBreakDownSettings->count();
					$totalBreakDownAmount = 0;
					foreach($fareBreakDownSettings as $fareBreakDownSetting)
					{
						$breakdown_amount = $transaction->amount * ($fareBreakDownSetting->value_percent/100);
						if(($fareBreakDownSettingCount-1)==$j)
						{
							$breakdown_amount = $transaction->amount - $totalBreakDownAmount;
						}
						$transactionBreakdown = new \App\TransactionBreakdown();
						$transactionBreakdown->user_id = $fareBreakDownSetting->is_withdrawable==0 ? null : $transaction->driverUserId;
						$transactionBreakdown->transaction_type = $fareBreakDownSetting->title;
						$transactionBreakdown->transaction_id = $transaction->id;
						$transactionBreakdown->trip_id = $trip->id;
						$transactionBreakdown->breakdown_amount = $breakdown_amount;
						$transactionBreakdown->is_reversed = 0;
						$transactionBreakdown->details = $fareBreakDownSetting->details;
						$transactionBreakdown->is_credit = 0;
						$transactionBreakdown->is_withdrawable = $fareBreakDownSetting->is_withdrawable;
						$transactionBreakdown->save();
						$totalBreakDownAmount = $totalBreakDownAmount + $breakdown_amount;


					}


					$recL = $transaction->driverUserId."~~~".$trip->id;
					$data1 = [];
					$data1['recL'] = ($recL);
					$data1['status'] = 1;
					$data1['messageType'] = 'CONFIRM TRIP PAYMENT';
					$data = json_encode($data1);
					$data = 'data='.urlencode($data);
					$url = "http://140.82.52.195:8080/notify-driver-of-trip-payment";
					//$data = 'bevuraTokenCard='.$bevuraTokenCard.'&hash='.$hash.'&merchantCode='.$merchantId.'&deviceCode='.$deviceCode;
					$server_output = sendPostRequestForBevura($url, $data);
					$jk = new \App\Junk();
					$jk->data=$data;
					$jk->save();

					$mobile = strpos($driver->mobileNumber, "260")==0 ? $driver->mobileNumber : ("26".$driver->mobileNumber);
					$msg = "Dear ".$driver->name."\nYour Tweende Driver account has been credited with ZMW".$transaction->amount.'. Your customer paid using Bevura';
					$sender = "Bevura";
					send_sms($mobile, $msg, $sender=NULL);
					//dd(11);
					return \Redirect::to('/payments/redirect-to-success-page/'.\Crypt::encrypt($transaction->id))->with('message', 'Payment was successful');
				}
				else
				{
					return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Payment was not successful. Try again');
				}
			}
		}


		//dd(22);
		if(isset($input['message']) && $input['message']!=null)
			return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', $input['message']);
		else
			return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Payment was not successful. Try again');
	}


	public function handleFailPaymentResponse(\App\Http\Requests\BaseTaxiRequest $request)
	{
		$input = ($request->all());
		$resp = $input['resp'];
		$resp = json_decode($resp);
		//dd($resp);
		$orderId = $resp->order_ref;
		$transaction = \App\Transaction::where('orderId', '=', $orderId)->first();
		$requestData = $transaction->requestData;
		$requestData = json_decode($requestData, true);

		$transaction->responseData = isset($resp->paying_info) ? $resp->paying_info: json_encode($resp);
		$transaction->payment_method = $resp->channel;
		$transaction->status = 'Fail';


		if(isset($requestData['scope']) && $requestData['scope']=='TAXI WALLET DEPOSIT')
		{
			if($transaction->save())
			{
				return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Wallet funding was not successful. Try again');

			}
			else
			{
				return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Wallet funding was not successful. Try again');

			}

		}
		
		
		
		if($transaction->save())
		{
			return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Wallet funding was not successful. Try again');

		}
		else
		{
			return  \Redirect::to('/payments/redirect-to-fail-page/'.\Crypt::encrypt($transaction->id))->with('error', 'Payment was not successful. Try again');
		}
	}
	
	
	public function handleDisplaySucessPage(\App\Http\Requests\BaseTaxiRequest $request, $txnId)
	{
		$transaction = null;
		$txnId = \Crypt::decrypt($txnId);
		$transaction = \App\Transaction::where('id', '=', $txnId)->first();
		return view('guest.bevura_page_success', compact('transaction'));
	}
	
	
	public function handleDisplayFailPage(\App\Http\Requests\BaseTaxiRequest $request, $txnId)
	{
		$transaction = null;
		$txnId = \Crypt::decrypt($txnId);
		$transaction = \App\Transaction::where('id', '=', $txnId)->first();
		$amount = $transaction->amount;
		return view('guest.bevura_page_error', compact('transaction', 'amount'));
	}
	
	public function handleGetInitiatePayment(\App\Http\Requests\BaseTaxiRequest $request, $tripId, $deviceRefNo, $token)
	{
		$fee = 0.00;
		try
		{
			$user = JWTAuth::toUser($token);
			
			if($tripId!=null)
			{
				$trip = \App\Trip::where('id', '=', $tripId)->first();//->where('status', '=', 'Completed')
				if($trip!=null)
				{
					$fee = $trip->amount_chargeable + ($trip->extra_charges==null ? 0 : $trip->extra_charges);
					$trans_ref = strtoupper(join('-', str_split(str_random(16), 4)));
					
					$paymentItem = array();
					$amount = array();
					$paymentItem[0] = urlencode('Payment for trip|'.$user->name.'|'.$user->mobileNumber);
					$amount[0] = $fee;
					$totalAmount = $fee;
			
					$params = array();
					$params['merchantId'] = PAYMENT_MERCHANT_ID;
					$params['deviceCode'] = PAYMENT_DEVICE_ID;
					$params['serviceTypeId'] = '1981511018900';
					$params['orderId'] = $deviceRefNo;
					$params['payerName'] = $user->name;
					$params['payerEmail'] = $user->email;
					$params['payerPhone'] = $user->mobileNumber;
					$params['payerId'] = $user->nrcNumber;
					$params['nationalId'] = $user->nrcNumber;
					$params['scope'] = 'TAXI TRIP PAYMENT';
					$params['description'] = 'Payment for trip|'.$user->name.'|'.$user->mobileNumber;
					$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
					$params['paymentItem'] = $paymentItem;
					$params['amount'] = $amount;
					$params['currency'] = DEFAULT_CURRENCY;
					$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
						$params['orderId'].number_format($totalAmount, 2, '.', '').$params['responseurl'].PAYMENT_API_KEY;
					$hash = hash('sha512', $toHash);
					$params['toHash'] = $toHash;
					$params['hash'] = $hash;
					
					$txn = new \App\Transaction();
					$txn->orderId = $deviceRefNo;
					$txn->requestData = json_encode($params);
					$txn->status = 'Pending';
					$txn->payeeUserId = $user->id;
					$txn->payeeUserFullName = $user->name;
					$txn->payeeUserMobile = $user->mobileNumber;
					$txn->tripId = $tripId;
					$txn->tripOrigin = $trip->origin_vicinity;
					$txn->tripDestination = $trip->destination_vicinity;
					$txn->driverUserId = $trip->vehicle_driver_user_id;
					$txn->vehicleId = $trip->vehicle_id;
					$txn->transactionRef = $trans_ref;
					$txn->amount = $totalAmount;
					$txn->save();
					
					$probasePayMerchant = PAYMENT_MERCHANT_ID;
					$probasePayDeviceCode = PAYMENT_DEVICE_ID;
					$mobileNumber = $user->mobileNumber;
					$payeeName = $user->name;
					$type = 'TAXI FARE';
					$amountToPay = $totalAmount;

					
					return view('guest.bevura', compact('probasePayMerchant', 'deviceRefNo', 'probasePayDeviceCode', 'mobileNumber', 'payeeName', 'type', 
						'amountToPay', 'params'));
				}
				
				return view('guest.bevura_page_error', compact());
			}
			return view('guest.bevura_page_error', compact('message', 'tripId', 'deviceRefNo'));
		}
		catch(TokenExpiredException $e)
        {
		$amount = $fee;
            return view('guest.bevura_page_error', compact($amount));
        }
		catch(\Exception $e)
        {
		$amount = $fee;

            return view('guest.bevura_page_error', compact($amount));
        }
	}



	
	public function handleGetInitiateDepositPayment(\App\Http\Requests\BaseTaxiRequest $request, $deviceRefNo, $token, $fee, $type)
	{
		$amountToPay = 0.00;
		try
		{
			$user = JWTAuth::toUser($token);
			
					$trans_ref = strtoupper(join('-', str_split(str_random(16), 4)));
					
					$paymentItem = array();
					$amount = array();
					$paymentItem[0] = urlencode('Deposit Funds In Wallet|'.$user->name.'|'.$user->mobileNumber);
					$amount[0] = doubleval($fee);
					$totalAmount = doubleval($fee);
			
					$params = array();
					$params['merchantId'] = PAYMENT_MERCHANT_ID;
					$params['deviceCode'] = PAYMENT_DEVICE_ID;
					$params['serviceTypeId'] = '1981511018900';
					$params['orderId'] = $deviceRefNo;
					$params['payerName'] = $user->name;
					$params['payerEmail'] = $user->email;
					$params['payerPhone'] = $user->mobileNumber;
					$params['payerId'] = $user->nrcNumber;
					$params['nationalId'] = $user->nrcNumber;
					$params['scope'] = 'TAXI WALLET DEPOSIT';
					$params['description'] = 'Deposit Funds In Wallet|'.$user->name.'|'.$user->mobileNumber;
					$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
					$params['paymentItem'] = $paymentItem;
					$params['amount'] = $amount;
					$params['currency'] = DEFAULT_CURRENCY;
					$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
						$params['orderId'].number_format($totalAmount, 2, '.', '').$params['responseurl'].PAYMENT_API_KEY;
					$hash = hash('sha512', $toHash);
					$params['toHash'] = $toHash;
					$params['hash'] = $hash;
					
					$txn = new \App\Transaction();
					$txn->orderId = $deviceRefNo;
					$txn->requestData = json_encode($params);
					$txn->status = 'Pending';
					$txn->payeeUserId = $user->id;
					$txn->payeeUserFullName = $user->name;
					$txn->payeeUserMobile = $user->mobileNumber;
					$txn->driverUserId = $user->id;
					$txn->transactionRef = $trans_ref;
					$txn->amount = $totalAmount;
					$txn->save();
					
					$probasePayMerchant = PAYMENT_MERCHANT_ID;
					$probasePayDeviceCode = PAYMENT_DEVICE_ID;
					$mobileNumber = $user->mobileNumber;
					$payeeName = $user->name;
					$type = 'TAXI FARE';
					$amountToPay = $totalAmount;

					
					return view('guest.bevura', compact('probasePayMerchant', 'deviceRefNo', 'probasePayDeviceCode', 'mobileNumber', 'payeeName', 'type', 
						'amountToPay', 'params'));

		}
		catch(TokenExpiredException $e)
        {
		$amount = $amountToPay;
            return view('guest.bevura_page_error', compact('amount'));
        }
		catch(\Exception $e)
        {
		dd($e);
		$amount = $amountToPay;
            return view('guest.bevura_page_error', compact('amount'));
        }
	}





	public function handleGetInitiateTokenize(\App\Http\Requests\BaseTaxiRequest $request, $token, $type)
	{
		try
		{
			$user = JWTAuth::toUser($token);
			
					
					$deviceRefNo = $user->id."-".strtoupper(str_random(16));
					$totalAmount = 0.00;
					$params = array();
					$params['merchantId'] = PAYMENT_MERCHANT_ID;
					$params['deviceCode'] = PAYMENT_DEVICE_ID;
					$params['serviceTypeId'] = '1981598182746';
					$params['scope'] = 'TOKENIZE '.$type;
					$params['responseurl'] = 'http://taxizambia.probasepay.com/payments/handle-response-success';
					$params['orderId'] = $deviceRefNo;
					$params['amount'] = 0.00;
					$params['currency'] = DEFAULT_CURRENCY;



					$toHash = $params['merchantId'].$params['deviceCode'].$params['serviceTypeId'].
						$params['orderId'].number_format($totalAmount, 2, '.', '').$params['responseurl'].PAYMENT_API_KEY;
					
					$hash = hash('sha512', $toHash);
					$params['toHash'] = $toHash;
					$params['hash'] = $hash;
					
					
										
					$probasePayMerchant = PAYMENT_MERCHANT_ID;
					$probasePayDeviceCode = PAYMENT_DEVICE_ID;
					$mobileNumber = $user->mobileNumber;
					$payeeName = $user->name;
					$type = 'TOKENIZE';
					
					return view('guest.bevura_tokenize', compact('probasePayMerchant', 'deviceRefNo', 'probasePayDeviceCode', 'mobileNumber', 'payeeName', 'type', 
						'params'));

		}
		catch(TokenExpiredException $e)
        {
		$amount = $amountToPay;
            return view('guest.bevura_page_error', compact('amount'));
        }
		catch(\Exception $e)
        {
		dd($e);
		$amount = $amountToPay;
            return view('guest.bevura_page_error', compact('amount'));
        }
	}

	public function getAddNewVehicleManufacturer(){
		$pageTitle = "Add New Vehicle Manufacturer";
		$bigTitle = "Add New Vehicle Manufacturer";
		$titleDescription = "Add Vehicle Manufacturers on Tweende";
		$smallTitle = "New Vehicle Manufacturers";
		return view('admin.new_vehicle_manufacturer', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription'));
	}

	public function getAddNewVehicleMaker(){
		$pageTitle = "Add New Vehicle Maker";
		$bigTitle = "Add New Vehicle Maker";
		$titleDescription = "Add Vehicle Makers on Tweende";
		$smallTitle = "New Vehicle Makers";
		return view('admin.new_vehicle_maker', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription'));
	}

	public function getAddNewDistrict(){
		$pageTitle = "Add New District";
		$bigTitle = "Add New District";
		$titleDescription = "Add Districts on Tweende";
		$smallTitle = "New Districts";
		$provinces = \App\Province::with('country')->get();
		return view('admin.new_district', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription', 'provinces'));
	}

	public function getAddNewCity(){
		$pageTitle = "Add New City";
		$bigTitle = "Add New City";
		$titleDescription = "Add Cities on Tweende";
		$smallTitle = "New Cities";
		return view('admin.new_city', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription'));
	}


	public function getAddNewTrafficCost()
	{
		$pageTitle = "Add New Traffic Cost";
		$bigTitle = "Add New Traffic Cost";
		$titleDescription = "Add Traffic Costs on Tweende";
		$smallTitle = "New Traffic Costs";
		$vtc = \DB::table('traffic_costs')->select('district_id')->get();
		$vtc_ = [];
		foreach($vtc as $vt)
		{
			array_push($vtc_, $vt->district_id);
		}
		$districts = \App\District::whereNotIn('id', $vtc_)->with('province')->get();
		return view('admin.new_traffic_cost', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription', 'districts'));
	}



	public function getNewVehicleTrafficCosts()
	{
		$pageTitle = "Add New Vehicle Traffic Cost";
		$bigTitle = "Add New Vehicle Traffic Cost";
		$titleDescription = "Add Vehicle Traffic Cost on Tweende";
		$smallTitle = "New Vehicle Traffic Cost";
		$vehicleTypes = \App\VehicleType::get();
		$type = "Add Vehicle Traffic Cost";
		$list = \DB::table('traffic_costs')->whereNull('traffic_costs.deleted_at')->get();
		return view('admin.data', compact('pageTitle', 'bigTitle', 'smallTitle', 'titleDescription', 'vehicleTypes', 'type', 'list'));
	}


	public function postAddNewVehicleType(\App\Http\Requests\BaseTaxiRequest $request){
		$rules = ['vtype' => 'required'];

		$messages = [
				'vtype.required' => 'You must provide at least one vehicle type',
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
			return \Redirect::back()->withInput($request->all())->with('error', $str_error);
		}
		//dd($request->all());
		$vehicleTypes = $request->get('vtype');
		$icons = $request->file('icon');
		$basefares = $request->get('basefare');
		$chargeperseconds = $request->get('chargepersecond');
		$chargepermeters = $request->get('chargepermeter');
		$isSuccess = false;
		
			try {
				$vehicleType = trim($vehicleTypes);
				$uploadedFile = $icons; 
				$basefare = $basefares; 
				$chargepersecond = $chargeperseconds; 
				$chargepermeter = $chargepermeters; 
				//dd([$uploadedFile->isValid(), strlen(trim($vehicleType))>0, is_numeric(floatval(trim($basefare))), is_numeric(floatval(trim($chargepersecond))), is_numeric(floatval(trim($chargepermeter)))]);
				if ($uploadedFile->isValid() && strlen(trim($vehicleType))>0 && is_numeric(floatval(trim($basefare)))==true  && is_numeric(floatval(trim($chargepersecond)))==true  && is_numeric(floatval(trim($chargepermeter)))==true) 
				{
					$destinationPath = 'vehicles/';
					$file_name_logo = strtolower(trim(str_replace(' ', '_', $vehicleType))) . '.' . $uploadedFile->getClientOriginalExtension();
					$file_name_logo1 = strtolower(trim(str_replace(' ', '_', $vehicleType)));
					
					if($uploadedFile->move($destinationPath, $file_name_logo))
					{
						$vt = \App\VehicleType::where('name', '=', $vehicleType)->count();
						if($vt==0)
						{
							$vt = new \App\VehicleType();
							$vt->name = $vehicleType;
							$vt->status = 1;
							$vt->icon = $file_name_logo1;
							$vt->base_fare_for_vehicle_type = $basefare;
							$vt->save();
							
							$tc = new \App\TrafficCost();
							$tc->base_fare = $basefare;
							$tc->district_id = 1;
							$tc->district_name = "Lusaka District";
							$tc->cancellationFee = 0.00;
							$tc->minimumFare = $basefare;
							$tc->save();
							
							
							$vtc = new \App\VehicleTrafficCost();
							$vtc->vehicle_type = $vehicleType;
							$vtc->chargePerSecond = $chargepersecond;
							$vtc->status = 'Active';
							$vtc->traffic_cost_id = $tc->id;
							$vtc->vehicle_type_id = $vt->id;
							$vtc->chargePerMeter = $chargepermeter;
							$vtc->save();
							
							$isSuccess = true;
						}
					}
				}
			}
			catch(\Exception $e)
			{
				//dd($e);
			}
		
		
		//dd($isSuccess);
		
		if($isSuccess===true)
			return \Redirect::back()->with('message', 'New Vehicle Types Added successfully');
		else
			return \Redirect::back()->with('error', 'New Vehicle Types Could not be successfully');
	}

	public function postAddNewVehicleManufacturer(\App\Http\Requests\BaseTaxiRequest $request){
		$rules = ['vtype' => 'required|array|min:1'];

		$messages = [
				'vtype.required' => 'You must provide at least one vehicle manufacturer',
				'vtype.array' => 'Invalid vehicle manufacturers data submitted',
				'vtype.min' => 'You must provide at least one vehicle manufacturer'
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
			return \Redirect::back()->withInput($request->all())->with('error', $str_error);
		}

		$vehicleManufacturers = $request->get('vtype');
		foreach($vehicleManufacturers as $vehicleManufacturer)
		{
			try {
				$vt = new \App\VehicleManufacturer();
				$vt->name = $vehicleManufacturer;
				$vt->status = 1;
				$vt->save();
			}
			catch(\Illuminate\Database\QueryException $e)
			{
				//dd($e);
			}
		}
		return \Redirect::back()->with('message', 'New Vehicle Manufacturers Added successfully');
	}

	public function postAddNewVehicleMaker(\App\Http\Requests\BaseTaxiRequest $request){
		$rules = ['vtype' => 'required|array|min:1'];

		$messages = [
				'vtype.required' => 'You must provide at least one vehicle maker',
				'vtype.array' => 'Invalid vehicle makers data submitted',
				'vtype.min' => 'You must provide at least one vehicle makers'
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
			return \Redirect::back()->withInput($request->all())->with('error', $str_error);
		}

		$vehicleMakers = $request->get('vtype');
		foreach($vehicleMakers as $vehicleMaker)
		{
			try {
				$vt = new \App\VehicleMake();
				$vt->name = $vehicleMaker;
				$vt->status = 1;
				$vt->save();
			}
			catch(\Illuminate\Database\QueryException $e)
			{
				//dd($e);
			}
		}
		return \Redirect::back()->with('message', 'New Vehicle Makers Added successfully');
	}

	public function postAddNewDistrict(\App\Http\Requests\BaseTaxiRequest $request){
		$rules = ['districtname' => 'required|array|min:1',
				'districtcode' => 'required|array|min:1',
				'provincename' => 'required|array|min:1'];

		$messages = [
				'districtname.required' => 'You must provide at least one district name',
				'districtname.array' => 'Invalid district names data submitted',
				'districtname.min' => 'You must provide at least one district name',
				'districtcode.required' => 'You must provide at least one district code',
				'districtcode.array' => 'Invalid district codes data submitted',
				'districtcode.min' => 'You must provide at least one district code',
				'provincename.required' => 'You must provide at least one province',
				'provincename.array' => 'Invalid provinces data submitted',
				'provincename.min' => 'You must provide at least one province'
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
			return \Redirect::back()->withInput($request->all())->with('error', $str_error);
		}


		$districtname = $request->get('districtname');
		$districtcode = $request->get('districtcode');
		$provincename = $request->get('provincename');
		for($i=0; $i<5; $i++)
		{
			try {
				if(isset($districtname[$i]) && isset($districtcode[$i]) && isset($provincename[$i])) {
					$vt = new \App\District();
					$vt->name = $districtname[$i];
					$vt->districtCode = $districtcode[$i];
					$vt->provinceId = explode('|||', $provincename[$i])[0];
					$vt->provinceName = explode('|||', $provincename[$i])[1];
					$vt->countryId = explode('|||', $provincename[$i])[2];
					$vt->countryName = explode('|||', $provincename[$i])[3];
					$vt->save();
				}
			}
			catch(\Illuminate\Database\QueryException $e)
			{
			}
		}
		return \Redirect::back()->with('message', 'New Vehicle Makers Added successfully');
	}

	public function postAddNewCity(\App\Http\Requests\BaseTaxiRequest $request){
		$rules = ['vtype' => 'required|array|min:1'];

		$messages = [
				'vtype.required' => 'You must provide at least one city',
				'vtype.array' => 'Invalid cities data submitted',
				'vtype.min' => 'You must provide at least one city'
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
			return \Redirect::back()->withInput($request->all())->with('error', $str_error);
		}

		$cities = $request->get('vtype');
		foreach($cities as $city)
		{
			try {
				$vt = new \App\City();
				$vt->name = $city;
				$vt->status = 1;
				$vt->save();
			}
			catch(\Illuminate\Database\QueryException $e)
			{
				//dd($e);
			}
		}
		return \Redirect::back()->with('message', 'New Cities Added successfully');
	}


	public function postAddNewTrafficCost(\App\Http\Requests\BaseTaxiRequest $request){
		$rules = ['district' => 'required|array|min:1',
				'basefare'=>'required|numeric',
				'cancelationfare'=>'required|numeric',
				'minfare'=>'required|numeric',
			];

		$messages = [
				'district.required' => 'You must provide at least one district',
				'district.array' => 'Invalid district data submitted',
				'district.min' => 'You must provide at least one district',
				'basefare.required' => 'You must provide your base fare',
				'basefare.numeric' => 'Base fare to be provided must be valid',
				'cancelationfare.numeric' => 'You must provide at your cancelation fee',
				'cancelationfare.required' => 'You must provide at your cancelation fee',
				'minfare.required' => 'You must provide at your minimum fee',
				'minfare.numeric' => 'You must provide at your minimum fee'
		];

		$validator = \Validator::make($request->all(), $rules, $messages);
		if($validator->fails())
		{
			$errMsg = json_decode($validator->messages(), true);
			$str_error = "";
			foreach($errMsg as $key => $value)
			{
				foreach($value as $val) {
					$str_error = ($val);
				}
			}
			return \Redirect::back()->withInput($request->all())->with('error', $str_error);
		}

		$districts = $request->get('district');
		$basefare = $request->get('basefare');
		$cancelationfare = $request->get('cancelationfare');
		$minfare = $request->get('minfare');




		foreach($districts as $district)
		{
			try {
				$vt = new \App\TrafficCost();
				$vt->base_fare = $basefare;
				$vt->district_id = explode('|||',$district)[0];
				$vt->district_name = explode('|||',$district)[1];
				$vt->cancellationFee = $cancelationfare;
				$vt->minimumFare = $minfare;
				$vt->save();
			}
			catch(\Illuminate\Database\QueryException $e)
			{
			}
		}
		return \Redirect::back()->with('message', 'New Traffic Cost Added successfully');
	}



	public function manageDataItems(\App\Http\Requests\BaseTaxiRequest $request){
		$data_key = $request->get('data-key');
		print_r($request->all());
		if($data_key=='addvehicletrafficcost')
		{
			$list = \DB::table('traffic_costs')->whereNull('traffic_costs.deleted_at')->get();
			//dd($list);
			foreach($list as $li)
			{
				$key = 'vehicle_'.$li->id;
				$vehicles = $request->get($key);
				foreach($vehicles as $vehicle)
				{
					$id = explode('|||', $vehicle)[0];
					$vtype = explode('|||', $vehicle)[1];
					$vtc = \App\VehicleTrafficCost::where('vehicle_type', '=', $vtype)
							->where('traffic_cost_id', '=', $id);
					//dd($vtc->get());
					if($vtc->count()==0) {
						$vtc = new \App\VehicleTrafficCost();
						$vtc->vehicle_type = $vtype;
						$vtc->chargePerSecond = 0.47;
						$vtc->status = 'Active';
						$vtc->traffic_cost_id = $id;
						//dd($vtc);
						$vtc->save();
					}
					//dd(2);
				}
			}
			return \Redirect::back()->with('message', 'Vehicle Traffic Cost Added Successfully');
		}
		else
		{
			$data_key = $request->get('selectedUserAction');
			$data_id = $request->get('datatablesformId');
			if($data_key!=null && $data_id!=null)
			{
				if ($data_key == 'deactivatedriveraccount') {
					$user = \App\User::where('id', '=', $data_id)->where('role_code', '=', \App\Roles::$ROLE_DRIVER_USER)->first();
					if($user!=null)
					{
						$user->status = 'Inactive';
						$user->save();
						return \Redirect::back()->with('message', 'Driver account deactivated successfully');
					}
				} else if ($data_key == 'deactivatevehicle') {
					$obj = \App\Vehicle::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Deactivated';
						$obj->save();
						return \Redirect::back()->with('message', 'Vehicle deactivated successfully');
					}
				} else if ($data_key == 'deactivatepassenger') {
					$obj = \App\User::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Inactive';
						$obj->save();
						return \Redirect::back()->with('message', 'Passenger account deactivated successfully');
					}
				} else if ($data_key == 'activatedriveraccount') {
					$user = \App\User::where('id', '=', $data_id)->where('role_code', '=', \App\Roles::$ROLE_DRIVER_USER)->first();
					if($user!=null)
					{
						$user->status = 'Active';
						$user->save();
						return \Redirect::back()->with('message', 'Driver account activated successfully');
					}
				} else if ($data_key == 'activatevehicle') {
					$obj = \App\Vehicle::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Valid';
						$obj->save();
						return \Redirect::back()->with('message', 'Vehicle activated successfully');
					}
				} else if ($data_key == 'activatepassenger') {
					$obj = \App\User::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Active';
						$obj->save();
						return \Redirect::back()->with('message', 'Passenger account activated successfully');
					}
				}
				else if ($data_key == 'canceltriprequest') {
					$obj = \App\DriverDeal::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Admin Canceled';
						$obj->save();
						return \Redirect::back()->with('message', 'Trip Request Canceled successfully');
					}
				} else if ($data_key == 'canceltrip') {
					$obj = \App\Trip::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Admin Canceled';
						$obj->save();
						return \Redirect::back()->with('message', 'Trip Canceled successfully');
					}
				} else if ($data_key == 'approvewithdrawal') {
					$obj = \App\WithdrawalRequest::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Paid';
						$obj->save();
						return \Redirect::back()->with('message', 'Withdrawal Request Approved successfully');
					}
				} else if ($data_key == 'disapprovewithdrawal') {
					$obj = \App\WithdrawalRequest::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->status = 'Canceled';
						$obj->save();
						return \Redirect::back()->with('message', 'Withdrawal Request Canceled successfully');
					}
				} else if ($data_key == 'deletevehicletype') {
					$obj = \App\VehicleType::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'Vehicle Type Deleted successfully');
					}
				} else if ($data_key == 'deletevehiclemanufacturer') {
					$obj = \App\VehicleType::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'Vehicle Type Deleted successfully');
					}
				} else if ($data_key == 'deletevehiclemaker') {
					$obj = \App\VehicleMake::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'Vehicle Maker Deleted successfully');
					}
				} else if ($data_key == 'deletedistrict') {
					$obj = \App\District::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'District Deleted successfully');
					}
				} else if ($data_key == 'deletecity') {
					$obj = \App\City::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'City Deleted successfully');
					}
				} else if ($data_key == 'deletetrafficcost') {
					$obj = \App\TrafficCost::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'Traffic Cost Deleted successfully');
					}
				} else if ($data_key == 'deletevehicletrafficcost') {
					$obj = \App\VehicleTrafficCost::where('id', '=', $data_id)->first();
					if($obj!=null)
					{
						$obj->delete();
						return \Redirect::back()->with('message', 'Vehicle Traffic Cost Deleted successfully');
					}
				}
			}

			return \Redirect::back()->with('error', 'Operation was not successfully');
		}

	}



}
