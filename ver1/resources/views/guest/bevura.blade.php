<html>
	<head>
		<title>Bevura</title>
		<script src="/js/jQuery/jQuery-2.1.4.min.js"></script>
		<script src="https://payments.probasepay.com/js/probase_inline.js?x={{date('ymdhi')}}"></script>
	</head>
	<body style="padding-top: 50px !important;">

	</body>

	<script>
		window.onload = handlePaySchoolFees();


		function handlePaySchoolFees()
		{
			var amounts = [];
			var paymentItems = [];
			@foreach($params['amount'] as $amt)
				amounts.push({{$amt}});
			@endforeach
			@foreach($params['paymentItem'] as $paymentItem)
				paymentItems.push('{{$paymentItem}}');
			@endforeach
			var items = [];
			items.push({{$amountToPay}});
			var dt = {
				merchantId: "{{$probasePayMerchant}}",
				deviceCode: "{{$probasePayDeviceCode}}",
				orderId: "{{$deviceRefNo}}",
				hash: "{{$params['hash']}}",
				serviceTypeId: "{{$params['serviceTypeId']}}",
				amount: amounts,
				paymentItem: paymentItems,
				currency: "{{$params['currency']}}",
				country_code: "ZM",
				total_amount: "{{$amountToPay}}",
				toHash: "{{$params['toHash']}}",
				responseurl: "{{$params['responseurl']}}",
				callback: function(dt1){
					console.log(dt1);
				}
			};
			console.log(dt);
			ppk = new ProbasePayKiosk(dt);
		}
	</script>
</html>