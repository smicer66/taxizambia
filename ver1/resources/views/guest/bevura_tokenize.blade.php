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
			amounts[0] = 0.00;
			paymentItems[0] = "Tokenize Pay Id";
			var items = [];
			items.push(0.00);


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
				toHash: "{{$params['toHash']}}",
				total_amount: "0.00",
				responseurl: "{{$params['responseurl']}}",
				scope: "TOKENIZE",
				callback: function(dt1){
					console.log(dt1);
				}
			};
			console.log(dt);
			ppk = new ProbasePayKiosk(dt);
		}
	</script>
</html>