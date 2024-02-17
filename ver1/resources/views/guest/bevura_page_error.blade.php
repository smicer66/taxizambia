<html>
	<head>
		<title>Bevura</title>
		<script src="/js/jQuery/jQuery-2.1.4.min.js"></script>
		<script src="https://payments.probasepay.com/js/probase_inline.js?x={{date('ymdhi')}}"></script>
		<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
		<style type="text/css">

			body
			{
				background:#f2f2f2;
			}

			.payment
			{
				border:1px solid #f2f2f2;
				height:280px;
				border-radius:20px;
				background:#fff;
			}
		   .payment_header
		   {
			   
			   padding:20px;
			   border-radius:20px 20px 0px 0px;
			   
		   }
		   
		   .check
		   {
			   margin:0px auto;
			   width:50px;
			   height:50px;
			   border-radius:100%;
			   background:#fff;
			   text-align:center;
		   }
		   
		   .check i
		   {
			   vertical-align:middle;
			   line-height:50px;
			   font-size:30px;
		   }

			.content 
			{
				text-align:center;
			}

			.content  h1
			{
				font-size:25px;
				padding-top:25px;
			}

			.content a
			{
				width:200px;
				height:35px;
				color:#fff;
				border-radius:30px;
				padding:5px 10px;
				background:rgba(255,102,0,1);
				transition:all ease-in-out 0.3s;
			}

			.content a:hover
			{
				text-decoration:none;
				background:#000;
			}
		   
		</style>
	</head>
	<body style="padding-top: 50px !important;">
		<div class="container">
		   <div class="row">
			  <div class="col-md-6 mx-auto mt-5">
				 <div class="payment">
					<div class="payment_header bg-danger">
					   <div class="check"><i class="fa fa-check" aria-hidden="true"></i></div>
					</div>
					<div class="content">
					   <h1>Payment Failed!</h1>
					   <p>Your payment of <small>ZMW</small>{{number_format($amount, 2, '.', ',')}} was not successful. <br>Please proceed to retry this payment again</p>
					</div>
					
				 </div>
			  </div>
		   </div>
		</div>
	</body>