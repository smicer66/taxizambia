<?php
/**
 * Created by PhpStorm.
 * User: xkalibaer
 * Date: 31/10/2016
 * Time: 20:53
 */
 
 define("DEAL_MAKING_INTERVAL", 20000);
 define("CANCEL_DEAL_INTERVAL", 20000);
 define("PAYMENT_MERCHANT_ID", '3MQU46R2RT');
 define("PAYMENT_DEVICE_ID", 'EVZ8XQDN');
 define("DEFAULT_CURRENCY", 'ZMW');
 define("PAYMENT_API_KEY", 'kC8V4sqO6HPdob0MBoIyxTPo6lqfDnxN');
 define("GOOGLE_MAP_KEY", "AIzaSyC-WnofEeecFNJamJnKNIEu_mWPB8c4gWk");

function handleSOAPCalls($serviceMethodName, $wsdl, $data)
{
    SoapWrapper::add(function ($service) use ($serviceMethodName, $wsdl) {
		$service
				->name($serviceMethodName)
				->wsdl($wsdl)
				->trace(true)
				->cache(WSDL_CACHE_NONE);
	});


	$soapReturn = null;
	SoapWrapper::service($serviceMethodName, function ($service) use ($data, $serviceMethodName, &$soapReturn) {
		//dd($service->getFunctions());
		$soapReturn = ($service->call($serviceMethodName, [$data]));
	});
	$soapData = json_decode($soapReturn->return);
	return $soapData;
    /*$result = new \stdclass;
    if($serviceMethodName=='queryCustomer')
    {
        $result->customerFirstName = "John";
        $result->customerLastName = "Peters";
    }
    else if($serviceMethodName=='authenticateCustomer')
    {
        $result->status = 1;
        $result->token = uniqid();
    }
    else if($serviceMethodName=='balanceInquiry')
    {
        $result->balance = 10129.78;
        $result->token = uniqid();
    }
    else if($serviceMethodName=='miniStatement')
    {
        $result->statements = ["FT:190.00", "TOP:10.00", "POS:66.00", "CR:400.00", "VTOP:54.28"];
        $result->token = uniqid();
    }
    else if($serviceMethodName=='queryCustomerAccounts')
    {
        $result->customerAccounts = ["1. 1891208191", "2. 1891208192"];
        $result->token = uniqid();
    }
    else if($serviceMethodName=='pullBanksForFT')
    {
        $result->banks = ["1.BOZ", "2.STC", "3.UBA", "4.ECO", "5.FNB", "6.CAV"];
        $result->token = uniqid();
    }
    else if($serviceMethodName=='fundsTransfer')
    {
        $result->response = "Funds Transfer Successful";
        $result->token = uniqid();
    }
    else if($serviceMethodName=='payMerchant')
    {
        $result->response = "Merchant Payment Successful";
        $result->token = uniqid();
    }*/


    return $result;
}



function handleTokenUpdate($result)
{
    if(isset($result->token) ) {

        $tk = $result;
        return $tk;
    }else{

        return false;
    }
}


function getServiceBaseURL()
{
    //return "10.71.39.18:8080";
	return "localhost:8080";
}


function handleCurlGetRequest($url)
{
	return file_get_contents($url);
}


function send_sms($mobile, $msg, $sender=NULL)
{
	$xml = null;
	$mobile="260967307151";//Kachi
	$substr = substr($mobile, 0, 2);
	if($substr=='09')
	{
		$mobile = "26".$mobile;
	}
	//$mobile="260968499817";//Andrea
	$msg = urlencode($msg);
	$url = "https://probasesms.com/text/multi/res/trns/sms?username=smspbs@123$$&password=pbs@sms123$$&mobiles=".$mobile."&message=".$msg."&sender=Bevura&type=TEXT";
			
			

	$_h = curl_init();
	curl_setopt($_h, CURLOPT_HEADER, 1);
	curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($_h, CURLOPT_HTTPGET, 1);
	curl_setopt($_h, CURLOPT_URL, $url );
	curl_setopt($_h, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
	curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
	$ty = curl_exec($_h);
	try{
		$header_size = curl_getinfo($_h, CURLINFO_HEADER_SIZE);
		$header = substr($ty, 0, $header_size);
		$body = substr($ty, $header_size);

		curl_close($_h);
		$body = (trim(preg_replace('/\s+/', ' ', $body)));


		
		$xml = new \SimpleXMLElement($body);
		//print_r($xml);
		$str = ($xml->response[0]->messagestatus);
		$str_=($str);
		
		$smsLog = new \App\SmsLog();
		$smsLog->receipient_no = $mobile;
		$smsLog->response = $body;
		$smsLog->message = $msg;
		if($str_=='SUCCESS')
		{
			$smsLog->success = 1;
		}else
		{
			$smsLog->success = 0;
		}
		if(\Auth::user())
		{
			$smsLog->user_id = \Auth::user()->id;
		}
		$smsLog->save();
	}
	catch(\Exception $e)
	{

		$smsLog = new \App\SmsLog();
		$smsLog->receipient_no = $mobile;
		$smsLog->response = $body;
		$smsLog->message = $msg;
		$smsLog->success = 0;
		$smsLog->save();
	}
//			echo ($xml->error[0]);
	if(($xml!=NULL && $xml->error[0]) != 'SUCCESS')
	{
		return false;
	}
	return true;
}


function u_logout()
{
	\Auth::logout();
	sleep(4);
	\Auth::logout();
}



function sendPostRequestForBevura($url, $jsonData)
{
	$ch = curl_init($url);
	//dd($jsonData);

	/*$jsonDataEncoded = json_encode($jsonData);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: plain/text'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	dd([$url, $result]);
	try{
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($result, 0, $header_size);
		$body = substr($result, $header_size);


		curl_close($ch);
		$body = (trim(preg_replace('/\s+/', ' ', $body)));

		return $body;


	}
	catch(\Exception $e)
	{
		return $e;
	}*/


	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $jsonData,
		//"username=potzr_staff@gmail.com&encPassword=eyJpdiI6InRLOXJlM0t3cFR6WmNpdVJPWUdxNkE9PSIsInZhbHVlIjoiQTMxNGRFaHhLT3E4UEkwL1dheVV4Zz09IiwibWFjIjoiZmZjMjhmYTdjZTg5NGM3ZDUxYjViY2E4NzVkN2Y1OWYwNDM4M2FiNjA0YTg4M2E0MjY3MzVkYTgzYzE0Mzg4MyJ9&bankCode=PROBASE",
		CURLOPT_HTTPHEADER => array(
			"Content-Type: application/x-www-form-urlencoded"
		),
	));

	$response = curl_exec($curl);

	//dd($response);
	try{
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);


		curl_close($curl);

		//dd($body);
		$body = (trim(preg_replace('/\s+/', ' ', $body)));

		//return $body;
		return $response;


	}
	catch(\Exception $e)
	{
		return $e;
	}

}

?>