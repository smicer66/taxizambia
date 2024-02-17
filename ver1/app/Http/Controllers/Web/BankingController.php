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
		$this->middleware('jwt.auth', ['except'=> ['sendRequestForAPaymentCard']]);
        $this->jwtauth = $jwtauth;
    }
	
	
	
	/***pullFlights By Departure, Airport, and flightType***/
    public function pullArrivalsByAirlineDepartureFlightType(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {
            $airline = $request->get('airline');
            $flighttype = $request->get('flighttype');
            $departure = $request->get('departure');
            $airlineflights = \DB::table('airlineflights')->join('airlinecity', 'airlineflights.arrivalCityId', '=', 'airlinecity.id')
                ->where('airlineflights.airlineId', '=', $airline)
                ->where('airlineflights.domesticYes', '=', $flighttype)
                ->where('airlineflights.departureCityId', '=', $departure)
                ->groupBy('airlineflights.arrivalCityId')
                ->select('airlinecity.id', 'airlinecity.airportName');
            $accts = [];
            if($airlineflights->count()>0)
            {
                $airlineflights = $airlineflights->get();
                foreach ($airlineflights as $airlineflight) {
                    $accts[$airlineflight->id] = $airlineflight->airportName;
                }
                $user = JWTAuth::toUser($token);
                $token = JWTAuth::fromUser($user);
                return response()->json(['token' => $token,'arrivals' => $accts]);
            }else{
                return response()->json(['err' => 'No flights available'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }


    public function verifyFlight(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {
            $token = $request->get('token');
            $adult = $request->get('adult');
            $child = $request->get('child');
            $infant = $request->get('infant');
            $travelClass = $request->get('travelClass');
            $returnDate = $request->get('returnDate');
            $departDate = $request->get('departDate');
            $destination = $request->get('destination');
            $departure = $request->get('departure');
            $tripType = $request->get('tripType');
            $flightType = $request->get('flightType');
            $airline = $request->get('airline');



            $airlineflights = \DB::table('airlineflights')
                ->join('airlines', 'airlineflights.airlineId', '=', 'airlines.id')
                ->where('airlineflights.airlineId', '=', $airline)
                ->where('airlineflights.domesticYes', '=', $flightType)
                ->where('airlineflights.departureCityId', '=', $departure)
                ->where('airlineflights.arrivalCityId', '=', $destination)
                ->select('airlines.*', 'airlineflights.*', 'airlineflights.id');
            $accts = [];
            $flightsAvailable = [];
            if($airlineflights->count()>0)
            {
                $airlineflights = $airlineflights->get();
                foreach ($airlineflights as $airlineflight) {
                    $depart = \DB::table('airlinecity')->where('id', '=', $airlineflight->departureCityId)->first();
                    $arrive = \DB::table('airlinecity')->where('id', '=', $airlineflight->arrivalCityId)->first();
                    $flightsAvailable[$airlineflight->id] = $airlineflight->airlineName."|||".$airlineflight->flightPrice
                        ."|||".$airlineflight->flightTime."Hrs|||".$depart->airportName."|||".$arrive->airportName."|||ZMW".
                        number_format($airlineflight->flightPrice, 2, '.', ',');
                }
                $user = JWTAuth::toUser($token);
                $token = JWTAuth::fromUser($user);
                $userAccts = \App\UserAccount::where('userId', '=', $user->id)->get();
                foreach($userAccts as $userAcct)
                {
                    $accts[$userAcct->id] = $userAcct->accountNumber." - ZMW".number_format($userAcct->currentBalance, 2, '.', ',');
                }
                return response()->json(['token' => $token,'flights' => $flightsAvailable, 'accts' => $accts]);
            }else{
                return response()->json(['err' => 'No flights available'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }





    /***Pull Branches By State***/
    public function pullBranchesByState(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();
        $state = $request->get('state');

        try{
            $branches = \DB::table('bankbranches')->join('states', 'bankbranches.stateId', '=', 'states.id')
                ->where('bankbranches.stateId', '=', $state)->select('states.*', 'bankbranches.*', 'bankbranches.id');
            if($branches->count() > 0)
            {
                $br = [];
                foreach($branches->get() as $branch)
                {
                    $br[$branch->id] = $branch->branchName."|||".$branch->addressLine1."|||".$branch->addressLine2."|||".$branch->mobileNumber.
                        "|||".$branch->email."|||".$branch->stateName;
                }
                $user = JWTAuth::toUser($token);
                $token = JWTAuth::fromUser($user);
                return response()->json(['token' => $token, 'branches' => $br]);
            }
            else{
                return response()->json(['err' => 'No branches in the selected state'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['err' => 'TokenExpired'], 422);
        }
    }


    /***Request For A Cheque Book***/
    public function requestForACheque(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();
        $leaves = $request->get('leaves');
        $branch = $request->get('branch');
        $chequeAcct = $request->get('chequeAcct');

        try{
            $lv = explode('|||', $leaves);

            $userAcctDebit = \App\UserAccount::where('id', '=', $chequeAcct)
                ->where('currentBalance', '>', $lv[0]);
            if($userAcctDebit->count()>0)
            {

                try {
                    \DB::beginTransaction();

                    $userAcctDebit = $userAcctDebit->first();
                    $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $lv[0];
                    $userAcctDebit->save();

                    //Then Credit Next
                    $userAcctCredit = \DB::table('useraccounts')->join('users', 'useraccounts.userId', '=', 'users.id')
                        ->where('users.name', '=', 'BANCABC')->select('useraccounts.id');



                    if ($userAcctCredit->count() > 0) {
                        $userAcctCredit = \App\UserAccount::where('id', '=', $userAcctCredit->first()->id);
                        $userAcctCredit = $userAcctCredit->first();
                        $userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $lv[0];
                        $userAcctCredit->save();



                        $chequeBookReq = new \App\ChequeBookRequest();
                        $chequeBookReq->chequeAcctId = $chequeAcct;
                        $chequeBookReq->leaves = $lv[1];
                        $chequeBookReq->branchId = $branch;
                        $chequeBookReq->referenceNumber = str_random(10);
                        $chequeBookReq->save();
                        $user = JWTAuth::toUser($token);
                        $token = JWTAuth::fromUser($user);
                        \DB::commit();
                        return response()->json(['token' => $token,
                            'successMessage' => 'Your Request For A Cheque book has been placed successfully . Your Cheque Request Ref Number is #'.$chequeBookReq->referenceNumber]);
                    }else
                    {
                        \DB::rollback();
                        return response()->json(['err' => 'Errors encountered debiting your account'], 500);
                    }

                }catch(Exception $e)
                {
                    \DB::rollback();
                    return response()->json(['err' => $e->getMessage()], 500);
                }


            }else
            {
                return response()->json(['err' => 'Cheque Book Cost exceeds your current account balance'], 500);
            }




        }catch(TokenExpiredException $e)
        {
            return response()->json(['err' => 'TokenExpired'], 422);
        }
    }


    /****Initialize for cheque confirm***/
    public function initializeChequeConfirm(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {

            $user = JWTAuth::toUser($token);
            $token = JWTAuth::fromUser($user);

            $userAccts = \App\UserAccount::where('userId', '=', $user->id);


            if($userAccts->count()>0)
            {
                foreach($userAccts->get() as $userAcct)
                {
                    $accts[$userAcct->id] = $userAcct->accountNumber." (ZMW".number_format($userAcct->currentBalance, 2, '.', ',').")";
                }
                return response()->json(['token' => $token, 'accts' => $accts]);
            }else
            {
                return response()->json(['err' => 'Flight cost exceeds your current account balance'], 500);
            }


        }catch(TokenExpiredException $e)
        {
            return response()->json(['err' => 'TokenExpired'], 422);
        }
    }


    /***Initialize Cheque Request****/
    public function initializeChequeRequest(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {



            $states = \DB::table('states');
            $state_ = [];
            $accts = [];
            if($states->count()>0)
            {
                $states = $states->get();
                foreach($states as $state)
                {
                    $state_[$state->id] = $state->stateName;
                }

                $user = JWTAuth::toUser($token);
                $token = JWTAuth::fromUser($user);

                $userAccts = \App\UserAccount::where('userId', '=', $user->id);


                if($userAccts->count()>0)
                {
                    foreach($userAccts->get() as $userAcct)
                    {
                        $accts[$userAcct->id] = $userAcct->accountNumber." (ZMW".number_format($userAcct->currentBalance, 2, '.', ',').")";
                    }
                    return response()->json(['token' => $token, 'states' => $state_, 'accts' => $accts]);
                }else
                {
                    return response()->json(['err' => 'Flight cost exceeds your current account balance'], 500);
                }
            }else{
                return response()->json(['err' => 'No flights available'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['err' => 'TokenExpired'], 422);
        }
    }



    /***Confirm Cheque****/
    public function confirmCheque(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();
        $chequeAcct = $request->get('chequeAcct');
        $chequeNumber = $request->get('chequeNumber');
        $beneficiaryAcctNumber = $request->get('beneficiaryAcctNumber');
        $amount = $request->get('amount');

        $confirmCheque = new \App\ConfirmCheque();
        $confirmCheque->amount = $amount;
        $confirmCheque->beneficiaryAcctNumber = $beneficiaryAcctNumber;
        $confirmCheque->chequeNumber = $chequeNumber;
        $confirmCheque->chequeAcctId = $chequeAcct;
        $confirmCheque->save();

        try {

            $user = JWTAuth::toUser($token);
            $token = JWTAuth::fromUser($user);

            return response()->json(['token' => $token, 'successMessage' => 'Your Cheque has been confirmed']);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['err' => 'TokenExpired'], 422);
        }
    }
	
	
	/***Change Password***/
    public function changepin(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();
        $pin = $request->get('pin');
        $npin = $request->get('npin');
        $cpin = $request->get('cpin');

        if($npin==$cpin && strlen($npin)>0)
        {
            try {

                $user = JWTAuth::toUser($token);
                if($user->pin==NULL)
                {
                    $user->pin = bcrypt($pin);
                    $user->save();
                }else {

                    if (Auth::validate(array('email' => $user->email, 'pin' => $pin))) {
                        dd(33);
                        $user->pin = Hash::make($pin);
                        $user->save();
                    } else {
                        return response()->json(['err' => 'Pin change failed. Ensure you provide valid details before you can change your pin'], 500);
                    }
                }
                $token = JWTAuth::fromUser($user);

                return response()->json(['token' => $token, 'successMessage' => 'Pin change was successful.']);

            }catch(TokenExpiredException $e)
            {
                return response()->json(['err' => 'TokenExpired'], 422);
            }
        }else
        {
            //Invalid new password provided
            return response()->json(['err' => 'Pin change failed. Ensure you provide valid details before you can change your pin'], 500);
        }


    }


    /***Confirm Cheque****/
    public function stopCheque(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();
        $sendingAcct = $request->get('sendingAcct');
        $chequeNumber = $request->get('chequeNumber');
        $narration = $request->get('narration');

        $stopCheque = new \App\StopCheque();
        $stopCheque->sendingAcctId = $sendingAcct;
        $stopCheque->chequeNumber = $chequeNumber;
        $stopCheque->narration = $narration;
        $stopCheque->save();

        try {

            $user = JWTAuth::toUser($token);
            $token = JWTAuth::fromUser($user);

            return response()->json(['token' => $token, 'successMessage' => 'Your Cheque has been stopped']);

        }catch(TokenExpiredException $e)
        {
            return response()->json(['err' => 'TokenExpired'], 422);
        }
    }

    /***Purchase Flight****/
    public function purchaseFlight(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {
            $flightchoice = $request->get('flightchoice');
            $srcAcct = $request->get('srcAcct');



            $airlineflights = \DB::table('airlineflights')
                ->join('airlines', 'airlineflights.airlineId', '=', 'airlines.id')
                ->where('airlineflights.id', '=', $flightchoice)
                ->select('airlines.*', 'airlineflights.*', 'airlineflights.id');
            $accts = [];
            $flightsAvailable = [];
            if($airlineflights->count()>0)
            {
                $airlineflights = $airlineflights->first();

                $user = JWTAuth::toUser($token);
                $token = JWTAuth::fromUser($user);

                $userAccts = \App\UserAccount::where('id', '=', $srcAcct)->first();

                $userAcctDebit = \App\UserAccount::where('id', '=', $srcAcct)
                    ->where('currentBalance', '>', $airlineflights->flightPrice);
                if($userAcctDebit->count()>0)
                {

                    try {
                        \DB::beginTransaction();

                        if ($userAcctDebit->count() > 0) {
                            $userAcctDebit = $userAcctDebit->first();
                            $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $airlineflights->flightPrice;
                            $userAcctDebit->save();

                            //Then Credit Next
                            $air = \DB::table('airlines')->where('id', '=', $airlineflights->airlineId)->first();

                            $userAcctCredit = \App\UserAccount::where('id', '=', $air->userAccountId);
                            if ($userAcctCredit->count() > 0) {
                                $userAcctCredit = $userAcctCredit->first();
                                $userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $airlineflights->flightPrice;
                                $userAcctCredit->save();

                                $flightTransaction = new \App\FlightTransaction();
                                $flightTransaction->txnReference = str_random(10);
                                $flightTransaction->airlineFlightId = $airlineflights->id;
                                $flightTransaction->destinationAccountId = $userAcctCredit->id;
                                $flightTransaction->amount = $airlineflights->flightPrice;
                                $flightTransaction->sourceAccountId = $srcAcct;
                                $flightTransaction->save();
                                \DB::commit();
                                $token = JWTAuth::fromUser($user);
                                return response()->json(['token' => $token,
                                    'successMessage' => 'Flight Successfully Purchased. Flight Ref #'.$flightTransaction->txnReference
                                        .'| Airline: '.$airlineflights->airlineName.' | Amount Paid: ZMW'. number_format($airlineflights->flightPrice, 2, '.', ',')]);
                            }else
                            {
                                \DB::rollback();
                                return response()->json(['err' => 'Errors encountered debiting your account'.$airlineflights->airlineId], 500);
                            }
                        }
                        else
                        {
                            \DB::rollback();
                            return response()->json(['err' => 'Reference Number provided has already been paid for. Confirm your reference number is correct'], 500);
                        }
                    }catch(Exception $e)
                    {
                        \DB::rollback();
                        return response()->json(['err' => $e->getMessage()], 500);
                    }


                }else
                {
                    return response()->json(['err' => $srcAcct.'Flight cost exceeds your current account balance'.$srcAcct], 500);
                }
            }else{
                return response()->json(['err' => 'No flights available'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }


    /***pay merchant initial confirm**/
    public function initiatemerchantpayment(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {

            $sendingAcct = $request->get('sendingAcct');
            $merchantcode = $request->get('merchantcode');
            $referencenumber = $request->get('referencenumber');
            $amounttopay = $request->get('amounttopay');
            $narration = $request->get('narration');

            $merchant = \DB::table('merchants')->where('merchantCode', '=', $merchantcode);
            if($merchant->count()>0)
            {
                $merchant = $merchant->first();
                $merchantTransactions = \DB::table('merchanttransactions')->where('merchantId', '=', $merchant->id)
                    ->where('txnReference', '=', $referencenumber);
                if($merchantTransactions->count()>0)
                {
                    return response()->json(['err' => 'Reference Number provided has already been paid for. Confirm your reference number is correct'], 500);
                }else{
                    $user = JWTAuth::toUser($token);
                    $userAcctDebit = \App\UserAccount::where('userId', '=', $user->id)->where('id', '=', $sendingAcct)
                        ->where('currentBalance', '>', $amounttopay);
                    if($userAcctDebit->count()>0)
                    {

                        try {
                            \DB::beginTransaction();

                            if ($userAcctDebit->count() > 0) {
                                $userAcctDebit = $userAcctDebit->first();
                                //$userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $amounttopay;
                                //$userAcctDebit->save();

                                //Then Credit Next
                                $userAcctCredit = \App\UserAccount::where('id', '=', $merchant->userAccountId);
                                if ($userAcctCredit->count() > 0) {
                                    //$userAcctCredit = $userAcctCredit->first();
                                    //$userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $amounttopay;
                                    //$userAcctCredit->save();

                                    /*$merchantTransaction = new \App\MerchantTransaction();
                                    $merchantTransaction->txnReference = $referencenumber;
                                    $merchantTransaction->merchantId = $merchant->id;
                                    $merchantTransaction->amount = $amounttopay;
                                    $merchantTransaction->sourceAccountId = $sendingAcct;
                                    $merchantTransaction->narration = $narration;
                                    $merchantTransaction->save();*/
                                    return response()->json(['successMessage' => 'Confirm Your transaction. Merchant Name: '.$merchant->merchantName
                                       .'| Merchant Code: '.$merchant->merchantCode.' | Amount: ZMW'. $amounttopay]);
                                }else
                                {
                                    \DB::rollback();
                                    return response()->json(['err' => 'Errors encountered debiting your account'], 500);
                                }
                            }
                            else
                            {
                                \DB::rollback();
                                return response()->json(['err' => 'Reference Number provided has already been paid for. Confirm your reference number is correct'], 500);
                            }
                        }catch(Exception $e)
                        {
                            \DB::rollback();
                            return response()->json([$e->getMessage()], 500);
                        }


                    }else
                    {
                        dd(2323);
                    }
                }
            }else{
                dd(233413);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }


    /***billing - airtime topup***/

    public function topupairtime(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {

            $biller = $request->get('biller');
            $receipientAccountType = $request->get('receipientAccountType');
            $recMobileNumber = $request->get('recMobileNumber');
            $topupAmt = $request->get('topupAmt');
            $referencenumber = str_random(10);
            $sendingAcct = $request->get('sendingAcct');

            $merchant = \DB::table('mobiletelcos')->where('telcoName', '=', $biller);
            if($merchant->count()>0)
            {
                $merchant = $merchant->first();

                $user = JWTAuth::toUser($token);
                $userAcctDebit = \App\UserAccount::where('userId', '=', $user->id)->where('id', '=', $sendingAcct)
                    ->where('currentBalance', '>', $topupAmt);
                if($userAcctDebit->count()>0)
                {

                    try {
                        \DB::beginTransaction();

                            $userAcctDebit = $userAcctDebit->first();
                            $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $topupAmt;
                            $userAcctDebit->save();

                            //Then Credit Next
                            $userAcctCredit = \App\UserAccount::where('id', '=', $merchant->userAccountId);
                            if ($userAcctCredit->count() > 0) {
                                $userAcctCredit = $userAcctCredit->first();
                                $userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $topupAmt;
                                $userAcctCredit->save();

                                $mobiletelcoTransaction = new \App\MobileTelcoTransaction();
                                $mobiletelcoTransaction->txnReference = $referencenumber;
                                $mobiletelcoTransaction->mobiletelcoId = $merchant->id;
                                $mobiletelcoTransaction->amount = $topupAmt;
                                $mobiletelcoTransaction->sourceAccountId = $sendingAcct;
                                if($receipientAccountType == 'Yes') {
                                    $mobiletelcoTransaction->receipientMobile = $recMobileNumber;
                                }
                                $mobiletelcoTransaction->save();
                                $token = JWTAuth::fromUser($user);
                                \DB::commit();
                                return response()->json(['token' => $token,
                                    'successMessage' => 'Your mobile topup '.($receipientAccountType == 'Yes' ? 'to '.$merchant->merchantName.' ' : '')
                                        .'from your account #'.$userAcctDebit->accountNumber.' for the sum ZMW'. $topupAmt.' was successful']);
                            }else
                            {
                                \DB::rollback();
                                return response()->json(['err' => 'Errors encountered debiting your account'], 500);
                            }

                    }catch(Exception $e)
                    {
                        \DB::rollback();
                        return response()->json([$e->getMessage()], 500);
                    }


                }else
                {
                    return response()->json(['err' => 'Invalid transaction. Try again'], 500);
                }

            }else{
                return response()->json(['err' => 'Invalid transaction. Try again'], 500);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }



    /***pay merchant initial confirm**/
    public function payMerchantConfirm(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {

            $sendingAcct = $request->get('sendingAcct');
            $merchantcode = $request->get('merchantcode');
            $referencenumber = $request->get('referencenumber');
            $amounttopay = $request->get('amounttopay');
            $narration = $request->get('narration');

            $merchant = \DB::table('merchants')->where('merchantCode', '=', $merchantcode);
            if($merchant->count()>0)
            {
                $merchant = $merchant->first();
                $merchantTransactions = \DB::table('merchanttransactions')->where('merchantId', '=', $merchant->id)
                    ->where('txnReference', '=', $referencenumber);
                if($merchantTransactions->count()>0)
                {
                    return response()->json(['err' => 'Reference Number provided has already been paid for. Confirm your reference number is correct'], 500);
                }else{
                    $user = JWTAuth::toUser($token);
                    $userAcctDebit = \App\UserAccount::where('userId', '=', $user->id)->where('id', '=', $sendingAcct)
                        ->where('currentBalance', '>', $amounttopay);
                    if($userAcctDebit->count()>0)
                    {

                        try {
                            \DB::beginTransaction();

                            if ($userAcctDebit->count() > 0) {
                                $userAcctDebit = $userAcctDebit->first();
                                $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $amounttopay;
                                $userAcctDebit->save();

                                //Then Credit Next
                                $userAcctCredit = \App\UserAccount::where('id', '=', $merchant->userAccountId);
                                if ($userAcctCredit->count() > 0) {
                                    $userAcctCredit = $userAcctCredit->first();
                                    $userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $amounttopay;
                                    $userAcctCredit->save();

                                    $merchantTransaction = new \App\MerchantTransaction();
                                    $merchantTransaction->txnReference = $referencenumber;
                                    $merchantTransaction->merchantId = $merchant->id;
                                    $merchantTransaction->amount = $amounttopay;
                                    $merchantTransaction->sourceAccountId = $sendingAcct;
                                    $merchantTransaction->narration = $narration;
                                    $merchantTransaction->save();
                                    $token = JWTAuth::fromUser($user);
                                    return response()->json(['token' => $token,
                                        'successMessage' => 'Payment to '.$merchant->merchantName
                                        .'('.$merchant->merchantCode.') of the sum ZMW'. $amounttopay.' was successful']);
                                }else
                                {
                                    \DB::rollback();
                                    return response()->json(['err' => 'Errors encountered debiting your account'], 500);
                                }
                            }
                            else
                            {
                                \DB::rollback();
                                return response()->json(['err' => 'Reference Number provided has already been paid for. Confirm your reference number is correct'], 500);
                            }
                        }catch(Exception $e)
                        {
                            \DB::rollback();
                            return response()->json([$e->getMessage()], 500);
                        }


                    }else
                    {
                        dd(2323);
                    }
                }
            }else{
                dd(233413);
            }

        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }


    public function pullproductsbybiller(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {

            $billerId = $request->get('biller');
            $cabletvproducts = \DB::table('cabletvproducts')->join('cabletv', 'cabletvproducts.cableTvId', '=', 'cabletv.id')
                ->where('cabletv.cableTVName', '=', $billerId)->select('cabletvproducts.*', 'cabletv.*', 'cabletvproducts.id');

            $cabletvproductsArray = [];
            if ($cabletvproducts->count() > 0) {
                $cabletvproducts = $cabletvproducts->get();


                foreach ($cabletvproducts as $cabletvproduct) {
                    $accts[$cabletvproduct->id] = $cabletvproduct->productName . " (ZMW".number_format($cabletvproduct->amount, 2, '.', ',').")";
                }
            }
            return Response::json([
                'accts' => $accts
            ]);


        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }






    public function confirmFT(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();


        try {
            $user = JWTAuth::toUser($token);

            $srcAcctId = $request->get('srcAcctId');

            $selectTransferType = $request->get('selectTransferType');
            $recBank = $request->get('recBank');
            $receipientAccount = $request->get('receipientAccount');
            $acctNumber = $request->get('acctNumber');
            $amtTransfer = $request->get('amtTransfer');
            $narration = $request->get('narration');



            if($selectTransferType=='BTW')
            {
                //To Be implemented

            }
            else if($selectTransferType=='OTH')
            {
                //Send Back Account Details for confirmation
                try {
                    \DB::beginTransaction();

                    //Debit First
                    $userAcctDebit = \App\UserAccount::where('userId', '=', $user->id)->where('id', '=', $srcAcctId)
                        ->where('currentBalance', '>', $amtTransfer);
                    if ($userAcctDebit->count() > 0) {
                        $userAcctDebit = $userAcctDebit->first();
                        $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $amtTransfer;
                        $userAcctDebit->save();

                        //Then Credit Next
                        $userAcctCredit = \App\UserAccount::where('accountNumber', '=', $acctNumber);
                        if ($userAcctCredit->count() > 0) {
                            $userAcctCredit = $userAcctCredit->first();
                            $userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $amtTransfer;
                            $userAcctCredit->save();

                            $fundsTransfer = new \App\FundsTransfer();
                            $fundsTransfer->sourceAccount = $userAcctDebit->accountNumber;
                            $fundsTransfer->receipientAccount = $acctNumber;
                            $fundsTransfer->sourceUserAccountId = $userAcctDebit->id;
                            $fundsTransfer->receipientUserAccountId = $userAcctCredit->id;
                            $fundsTransfer->amount = $amtTransfer;
                            $fundsTransfer->charges = NULL;
                            $fundsTransfer->narration = $narration;
                            $fundsTransfer->bank = 'BANCABC';
                            $fundsTransfer->save();

                            \DB::commit();
                            $token = JWTAuth::fromUser($user);

                            return Response::json([
                                'successMessage' => 'The sum of ZMW' . $amtTransfer . ' has been successfully transfered to ' . $userAcctCredit->accountNumber,
                                'token' => $token
                            ]);
                        } else {
                            \DB::rollback();
                            return response()->json(['failed_to_transfer_money1'], 500);
                        }
                    } else {
                        return response()->json(['failed_to_transfer_mone2y'], 500);
                    }

                }catch(\Exception $e) {
                    \DB::rollback();
                    return response()->json([$e->getMessage()], 500);
                }

            }
            else if($selectTransferType=='OTHBA')
            {
                try {
                    \DB::beginTransaction();

                    //Debit First
                    $userAcctDebit = \App\UserAccount::where('userId', '=', $user->id)->where('accountNumber', '=', $srcAcctId)
                        ->where('currentBalance', '>', $amtTransfer);
                    if ($userAcctDebit->count() > 0) {
                        $userAcctDebit = $userAcctDebit->first();
                        $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $amtTransfer;
                        $userAcctDebit->save();

                        //Then Credit Next


                        $fundsTransfer = new \App\FundsTransfer();
                        $fundsTransfer->sourceAccount = $srcAcctId;
                        $fundsTransfer->receipientAccount = $acctNumber;
                        $fundsTransfer->sourceUserAccountId = $userAcctDebit->id;
                        $fundsTransfer->receipientUserAccountId = NULL;
                        $fundsTransfer->amount = $amtTransfer;
                        $fundsTransfer->charges = NULL;
                        $fundsTransfer->narration = $narration;
                        $fundsTransfer->bank = $recBank;
                        $fundsTransfer->save();

                        \DB::commit();
                        $token = JWTAuth::fromUser($user);
                        return Response::json([
                            'successMessage' => 'The sum of ZMW' . $amtTransfer . ' has been successfully transfered to ' . $acctNumber,
                            'token' => $token
                        ]);

                    } else {
                        return response()->json(['failed_to_transfer_mone2y'], 500);
                        \DB::rollback();
                    }

                }catch(\Exception $e) {
                    \DB::rollback();
                    return response()->json([$e->getMessage()], 500);
                }
            }


        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }


    public function manageFT(\App\Http\Requests\FTRequest $request) {
        //$user = JWTAuth::parseToken()->authenticate();
        $token = JWTAuth::getToken();


        try {
            $user = JWTAuth::toUser($token);

            $srcAcctId = $request->get('srcAcctId');

            $selectTransferType = $request->get('selectTransferType');
            $recBank = $request->get('recBank');
            $receipientAccount = $request->get('receipientAccount');
            $acctNumber = $request->get('acctNumber');
            $amtTransfer = $request->get('amtTransfer');
            $narration = $request->get('narration');

            /*return Response::json([
                'srcAcctId' => $srcAcctId,
                '$selectTransferType' => $selectTransferType,
                '$recBank' => $recBank,
                '$receipientAccount' => $receipientAccount,
                '$acctNumber' => $acctNumber,
                '$amtTransfer' => $amtTransfer,
            ]);*/

            if($selectTransferType=='BTW')
            {
                try {
                    \DB::beginTransaction();

                    //Debit First
                    $userAcctDebit = \App\UserAccount::where('userId', '=', $user->id)->where('id', '=', $srcAcctId)
                        ->where('currentBalance', '>', $amtTransfer);
                    if ($userAcctDebit->count() > 0) {
                        $userAcctDebit = $userAcctDebit->first();
                        $userAcctDebit->currentBalance = $userAcctDebit->currentBalance - $amtTransfer;
                        $userAcctDebit->save();

                        //Then Credit Next
                        $userAcctCredit = \App\UserAccount::where('id', '=', $receipientAccount);
                        if ($userAcctCredit->count() > 0) {
                            $userAcctCredit = $userAcctCredit->first();
                            $userAcctCredit->currentBalance = $userAcctCredit->currentBalance + $amtTransfer;
                            $userAcctCredit->save();
                            $token = JWTAuth::fromUser($user);

                            $fundsTransfer = new \App\FundsTransfer();
                            $fundsTransfer->sourceAccount = $userAcctDebit->accountNumber;
                            $fundsTransfer->receipientAccount = $receipientAccount;
                            $fundsTransfer->sourceUserAccountId = $userAcctDebit->id;
                            $fundsTransfer->receipientUserAccountId = $userAcctCredit->id;
                            $fundsTransfer->amount = $amtTransfer;
                            $fundsTransfer->charges = NULL;
                            $fundsTransfer->narration = $narration;
                            $fundsTransfer->bank = 'BANCABC';
                            $fundsTransfer->save();

                            \DB::commit();
                            $token = JWTAuth::fromUser($user);
                            return Response::json([
                                'successMessage' => 'The sum of ZMW' . $amtTransfer . ' has been successfully transfered to ' . $userAcctCredit->accountNumber,
                                'token' => $token
                            ]);
                        } else {
                            \DB::rollback();
                            return response()->json(['failed_to_transfer_money4'], 500);
                        }
                    } else {
                        return response()->json(['failed_to_transfer_money5'], 500);
                    }

                }catch(\Exception $e) {
                    \DB::rollback();
                    return response()->json([$e->getMessage()], 500);
                }

            }
            else if($selectTransferType=='OTH')
            {
                //Send Back Account Details for confirmation
                $confirmUserAccount = \DB::table('useraccounts')->join('users', 'useraccounts.userId', '=', 'users.id')
                    ->where('accountNumber', '=', $acctNumber)->select('users.*', 'useraccounts.*', 'useraccounts.id as id');
                if ($confirmUserAccount->count() > 0) {
                    $confirmUserAccount = $confirmUserAccount->first();
                    return Response::json([
                        'confirmReceipient' => $confirmUserAccount->name." (".$confirmUserAccount->accountNumber.")"
                    ]);
                }
                return response()->json(['Invalid Beneficiary Account Provided. This account does not exist'], 500);

            }
            else if($selectTransferType=='OTHBA')
            {
                return Response::json([
                    'confirmReceipient' => "Bank: ".$recBank." | Receipient Account: ".$acctNumber
                ]);
            }


        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }



    public function getLastTransactions(\App\Http\Requests\FTRequest $request) {
        $token = JWTAuth::getToken();
        try {
            $user = JWTAuth::toUser($token);
            $srcAcctId = $request->get('srcAcctId');

            $confirmUserAccountType = '';
            $confirmUserAccount = \DB::table('useraccounts')
                ->join('users', 'useraccounts.userId', '=', 'users.id')
                ->where('accountNumber', '=', $srcAcctId)
                ->select('users.*', 'useraccounts.*', 'useraccounts.id as id');
            if ($confirmUserAccount->count() ==0){
                return response()->json(['message' => 'Invalid Account Provided'], 500);
            }else
            {
                $confirmUserAccountType = $confirmUserAccount->first()->accountType;
            }


            $accounts = \DB::table('fundstransfers')
                ->join('useraccounts', 'fundstransfers.sourceUserAccountId', '=', 'useraccounts.id')
                ->where('useraccounts.userId', '=', $user->id)
                ->where('fundstransfers.sourceAccount', '=', $srcAcctId)
                ->select('fundstransfers.*', 'useraccounts.*', 'fundstransfers.id as id',
                    'fundstransfers.created_at as ftcreatedat');
            //dd($accounts->get());
            $currBal = 0.00;
            $accts = [];
            $x1 = 0;
            if ($accounts->count() > 0) {
                $accounts = $accounts->get();


                foreach ($accounts as $acct) {
                    $currBal = $acct->currentBalance;
                    $x1++;
                    $accts[$acct->id] = date('l, F jS, Y', strtotime($acct->ftcreatedat))."|||Funds Transfer to Acct #".$acct->receipientAccount."|||".number_format($acct->amount, 2, '.', ',');
                }
            }

            $accounts = \DB::table('fundstransfers')
                ->join('useraccounts', 'fundstransfers.receipientUserAccountId', '=', 'useraccounts.id')
                ->where('useraccounts.userId', '=', $user->id)
                ->where('fundstransfers.sourceAccount', '=', $srcAcctId)
                ->select('fundstransfers.*', 'useraccounts.*', 'fundstransfers.id as id',
                    'fundstransfers.created_at as ftcreatedat');

            if ($accounts->count() > 0) {
                $accounts = $accounts->get();


                foreach ($accounts as $acct) {
                    $currBal = $acct->currentBalance;
                    $x1++;
                    $accts[$acct->id] = date('l, F jS, Y', strtotime($acct->ftcreatedat))."|||Funds Transfer from Acct #".$acct->sourceAccount."|||".number_format($acct->amount, 2, '.', ',');
                }
            }




            $accounts = \DB::table('mobiletelcotransactions')
                ->join('useraccounts', 'mobiletelcotransactions.sourceAccountId', '=', 'useraccounts.id')
                ->join('mobiletelcos', 'mobiletelcotransactions.mobiletelcoId', '=', 'mobiletelcos.id')
                ->where('useraccounts.userId', '=', $user->id)
                ->where('useraccounts.accountNumber', '=', $srcAcctId)
                ->select('mobiletelcos.*', 'useraccounts.*', 'mobiletelcotransactions.*',
                    'mobiletelcotransactions.id as id',
                    'mobiletelcotransactions.created_at as ftcreatedat');

            if ($accounts->count() > 0) {
                $accounts = $accounts->get();


                foreach ($accounts as $acct) {
                    $currBal = $acct->currentBalance;
                    $x1++;
                    $accts[$acct->id] = date('l, F jS, Y', strtotime($acct->ftcreatedat))."|||".
                        "Mobile TopUp - ".$acct->telcoName."|||".number_format($acct->amount, 2, '.', ',');
                }
            }


            $accounts = \DB::table('flighttransactions')
                ->join('useraccounts', 'flighttransactions.sourceAccountId', '=', 'useraccounts.id')
                ->join('airlineflights', 'flighttransactions.airlineFlightId', '=', 'airlineflights.id')
                ->where('useraccounts.userId', '=', $user->id)
                ->where('useraccounts.accountNumber', '=', $srcAcctId)
                ->select('airlineflights.*', 'useraccounts.*', 'flighttransactions.*',
                    'flighttransactions.id as id',
                    'flighttransactions.created_at as ftcreatedat');

            if ($accounts->count() > 0) {
                $accounts = $accounts->get();


                foreach ($accounts as $acct) {
                    $currBal = $acct->currentBalance;
                    $x1++;
                    $accts[$acct->id] = date('l, F jS, Y', strtotime($acct->ftcreatedat))."|||".
                        "Flight #".$acct->txnReference."|||".number_format($acct->amount, 2, '.', ',');
                }
            }

            if($x1==0)
            {
                return response()->json(['message' => 'No Transactions Carried Out',
                    'currBal' => '0.00',
                    'acctName' => $confirmUserAccountType], 500);
            }

            $token = JWTAuth::fromUser($user);
            return Response::json([
                'txns' => ($accts),
                'token' => $token,
                'currBal' => number_format($currBal, 2, '.', ','),
                'acctName' => $confirmUserAccountType
            ]);



        }catch(Exception $e)
        {
            //dd($e->getMessage());
            return response()->json(['message' => 'TokenExpired',
                'acctName' => '',
                'currBal' => '0.00'], 422);
        }

    }



    public function pulllist(\App\Http\Requests\FTRequest $request) {
		$input = $request->all();
		$accts = array();
		$data = array();

        $data['token'] = $input['token'];

        $result = handleSOAPCalls('listCustomerMobileMoneyAccounts', 'http://'.getServiceBaseURL().'/ProbasePayEngine/services/MobileMoneyServices?wsdl', $data);


        

        if(handleTokenUpdate($result)==false)
        {
			return \Response::json([
                'status' => -1,
                'msg' => 'Login session expired. Please relogin again'
            ]);
        }
		
		


        if($result->status == 5000)
        {
			$customeraccountlist = (json_decode($result->customeraccountlist));
			//dd($customeraccountlist);
			foreach($customeraccountlist as $acct)
			{
				foreach($acct as $k => $y)
				{
					
					$accts[$k] = number_format($y, 2, '.', ',');
				}
			}
			
			
            return \Response::json([
                'status' => 0,
                'accts' => $accts
            ]);
        }else
        {
            return \Response::json([
                'status' => -1,
                'msg' => 'Login session expired. Please relogin again'
            ]);
        }
		

    }
	
}