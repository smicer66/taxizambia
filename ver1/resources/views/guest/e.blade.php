<?php
	$apiKey	= "D3D1D05AFE42AD50818167EAC73C109168A0F108F32645C8B59E897FA930DA44F9230910DAC9E20641823799A107A02068F7BC0F4CC41D2952E249552255710F";
	$merchantId = '7OESIFCUXQ';
	$deviceCode = 'QYSH2MM5';
	$serviceTypeId = '1981511018900';
	$orderId = date('His').rand(1, 9);
	$payerName = 'Charles Mtonga';
	$payerEmail = 'charles@probasegroup.com';
	$payerPhone = '260963686873';
	$payerId = '268398/24/1';
	$nationalId = '268398/24/1';
	$scope = '2017 Term 1';
	$description = 'Charles Mtonga, 237110/65/1, 2017 Term 1';
	$currency = 'ZMW';
	
	$amounts = [
		1, 1, 1,
	];
	$paymentItems = [
		'Payment Item A', 'Payment Item D', 'Payment Item C'
	];
	
	$totalAmount = 0;
	foreach($amounts as $amount){
		$totalAmount += $amount;
	}
	$totalAmount = number_format($totalAmount, 2, '.', '');
	
// 	$responseurl = 'http://localhost:8090/ProBasePay-Merchant/payment_status.php';
	$responseurl = 'http://localhost:8080/work/pbspay/status.php';
	
	
	$hash = hash('sha512', $merchantId.$deviceCode.$serviceTypeId.$orderId.$totalAmount.$responseurl.$apiKey);
?>



<html>
<body>
<form action="http://payments.probasepay.com/payments/init" name="SubmitProbasePayForm" method="POST">
<input name="merchantId" value="<?php echo $merchantId; ?>" type="hidden">
<input name="deviceCode" value="<?php echo $deviceCode; ?>" type="hidden">
<input name="serviceTypeId" value="<?php echo $serviceTypeId; ?>" type="hidden">
<input name="orderId" value="<?php echo $orderId; ?>" type="hidden">
<input name="hash" value="<?php echo $hash; ?>" type="hidden">
<input name="payerName" value="<?php echo $payerName; ?>" type="hidden">
<input name="payerEmail" value="<?php echo $payerEmail; ?>m" type="hidden">
<input name="payerPhone" value="<?php echo $payerPhone; ?>" type="hidden">
<input name="payerId" value="<?php echo $payerId; ?>" type="hidden">
<input name="nationalId" value="<?php echo $nationalId; ?>" type="hidden">
<input name="scope" value="<?php echo $scope; ?>" type="hidden">
<input name="description" value="<?php echo $description; ?>" type="hidden">
<input name="currency" value="<?php echo $currency; ?>" type="hidden">

<?php foreach($amounts as $amount){ ?>
<input name="amount[]" value="<?php echo $amount; ?>" type="hidden">
<?php } ?>

<?php foreach($paymentItems as $paymentItem){ ?>
<input name="paymentItem[]" value="<?php echo $paymentItem; ?>" type="hidden">
<?php } ?>

<input name="responseurl" value="<?php echo $responseurl; ?>" type="hidden">
<input type ="submit" name="submit_btn" value="Pay Via ProbasePAY">
</form>
</body>
</html>