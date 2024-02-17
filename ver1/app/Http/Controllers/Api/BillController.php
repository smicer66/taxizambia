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
use Tymon\JWTAuth\JWTAuth;
use Artisaninweb\SoapWrapper\SoapWrapper;

class BillController extends Controller
{
    private $soapWrapper;
	private $jwtauth;


    public function __construct(JWTAuth $jwtauth, SoapWrapper $soapWrapper)
    {
        $this->soapWrapper = $soapWrapper;
        $this->jwtauth = $jwtauth;
    }

    public function pullProductsByBiller(\App\Http\Requests\FTRequest $request) {
        $token = $this->jwtauth->getToken();


        try {

            $billerId = $request->get('biller');
			if($billerId=="DSTV")
			{
				$this->soapWrapper->add('Test', function ($service) {
				  $service
					->wsdl('http://uat.mcadigitalmedia.com/VendorSelfCare/SelfCareService.svc?singleWsdl')
					->trace(true);
				});
				$response = $this->soapWrapper->call('Test.GetAvailableProducts', [
				  'dataSource' => 'Ghana_UAT', 
				  'customerNumber'   => 73252963, 
				  'BusinessUnit'     => 'GOTV', 
				  'VendorCode'       => 'eTranzactDStv',
				  'language'   => 'English', 
				  'ipAddress'     => '127.1.1',
				]);

				var_dump($response);
				
				dd(22);
			}
			
            $cabletvproducts = \DB::table('cabletvproducts')->join('cabletv', 'cabletvproducts.cableTvId', '=', 'cabletv.id')
                ->where('cabletv.cableTVName', '=', $billerId)->select('cabletvproducts.*', 'cabletv.*', 'cabletvproducts.id');

            $cabletvproductsArray = [];
            if ($cabletvproducts->count() > 0) {
                $cabletvproducts = $cabletvproducts->get();


                foreach ($cabletvproducts as $cabletvproduct) {
                    $accts[$cabletvproduct->id] = $cabletvproduct->productName . " (ZMW".number_format($cabletvproduct->amount, 2, '.', ',').")";
                }
            }
            return \Response::json([
                'accts' => $accts
            ]);


        }catch(TokenExpiredException $e)
        {
            return response()->json(['TokenExpired'], 422);
        }
    }
}
