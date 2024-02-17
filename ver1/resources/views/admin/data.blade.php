@extends('admin.layouts.layout')

@section('css')
    <link rel="stylesheet" href="/admin/vendor/fontawesome/css/font-awesome.css" />
    <link rel="stylesheet" href="/admin/vendor/metisMenu/dist/metisMenu.css" />
    <link rel="stylesheet" href="/admin/vendor/animate.css/animate.css" />
    <link rel="stylesheet" href="/admin/vendor/bootstrap/dist/css/bootstrap.css" />
    <link rel="stylesheet" href="/admin/vendor/datatables.net-bs/css/dataTables.bootstrap.min.css" />

    <!-- App styles -->
    <link rel="stylesheet" href="/admin/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
    <link rel="stylesheet" href="/admin/fonts/pe-icon-7-stroke/css/helper.css" />
    <link rel="stylesheet" href="/admin/styles/style.css">
	
@stop

@include('admin.errors.errors')
@section('content')
<!-- Main Wrapper -->
<div id="wrapper">

    <div class="normalheader ">
        <div class="hpanel">
            <div class="panel-body">
                <a class="small-header-action" href="#">
                    <div class="clip-header">
                        <i class="fa fa-arrow-up"></i>
                    </div>
                </a>

                <div id="hbreadcrumb" class="pull-right m-t-lg">
                    @if($type!=null && ($type=='Vehicle Types List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-vehicle-type" class="btn btn-primary">Add New Vehicle Type</a>
                        </div>
                    @elseif($type!=null && ($type=='Vehicle Manufacturers List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-vehicle-manufacturer" class="btn btn-primary">Add New Vehicle Manufacturer</a>
                        </div>
                    @elseif($type!=null && ($type=='Vehicle Makers List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-vehicle-maker" class="btn btn-primary">Add New Vehicle Maker</a>
                        </div>
                    @elseif($type!=null && ($type=='Districts List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-district" class="btn btn-primary">Add New District</a>
                        </div>
                    @elseif($type!=null && ($type=='Cities List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-city" class="btn btn-primary">Add New City</a>
                        </div>
                    @elseif($type!=null && ($type=='Traffic Costs List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-traffic-cost" class="btn btn-primary">Add New Traffic Costs</a>
                            <a href="/admin/settings/add-new-vehicle-traffic-costs" class="btn btn-primary">Map Traffic Costs To Vehicle Types</a>
                        </div>
                    @elseif($type!=null && ($type=='Vehicle Traffic Costs List'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-traffic-cost" class="btn btn-primary">Add New Traffic Costs</a>
                            <a href="/admin/settings/add-new-vehicle-traffic-costs" class="btn btn-primary">Map Traffic Costs To Vehicle Types</a>
                        </div>
                    @elseif($type!=null && ($type=='Add Vehicle Traffic Cost'))
                        <div style="float:right">
                            <a href="/admin/settings/add-new-traffic-cost" class="btn btn-primary">Add New Traffic Costs</a>
                            <a href="/admin/settings/add-new-vehicle-traffic-costs" class="btn btn-primary">Map Traffic Costs To Vehicle Types</a>
                        </div>
                    @endif

                </div>
                <h2 class="font-light m-b-xs">
                    {{$bigTitle}}
                </h2>
                <small>{{$titleDescription}}</small>
            </div>
        </div>
    </div>


<div class="content">



    <div class="row">
        <div class="col-lg-12">


            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    {{$smallTitle}}
                </div>
                <div class="panel-body">

                    <form method="post" action="/admin/manage-data-items" id="managedataform" enctype="application/x-www-form-urlencoded" class="form-horizontal">
                        @if($type!=null && $type=='Driver List')
                            <input type="hidden" name="data-key" value="driverlist">
                        @elseif($type!=null && $type=='Vehicle List')
                            <input type="hidden" name="data-key" value="vehiclelist">
                        @elseif($type!=null && $type=='Passenger List')
                            <input type="hidden" name="data-key" value="passengerlist">
                        @elseif($type!=null && ($type=='Trip Request List' || $type=='Accepted Trip Request List' || $type=='Canceled Trip Request List' || $type=='Pending Trip Request List'))
                            <input type="hidden" name="data-key" value="triprequestlist">
                        @elseif($type!=null && ($type=='Trips List' || $type=='Completed Trips List' || $type=='Ongoing Trips List' || $type=='Canceled Trips List'))
                            <input type="hidden" name="data-key" value="triplist">
                        @elseif($type!=null && ($type=='All Payment Transactions List' || $type=='Pending Payment Transactions List' || $type=='Cash Payment Transactions List' || $type=='Wallet Payment Transactions List' || $type=='Card Payment Transactions List'))
                            <input type="hidden" name="data-key" value="paymentlist">
                        @elseif($type!=null && ($type=='Withdrawal Requests List'))
                            <input type="hidden" name="data-key" value="withdrawallist">
                        @elseif($type!=null && ($type=='Vehicle Types List'))
                            <input type="hidden" name="data-key" value="vehicletypelist">
                        @elseif($type!=null && ($type=='Vehicle Manufacturers List'))
                            <input type="hidden" name="data-key" value="vehiclemanufacturelist">
                        @elseif($type!=null && ($type=='Vehicle Makers List'))
                            <input type="hidden" name="data-key" value="vehiclemakerlist">
                        @elseif($type!=null && ($type=='Districts List'))
                            <input type="hidden" name="data-key" value="districtlist">
                        @elseif($type!=null && ($type=='Cities List'))
                            <input type="hidden" name="data-key" value="citylist">
                        @elseif($type!=null && ($type=='Traffic Costs List'))
                            <input type="hidden" name="data-key" value="trafficcostslist">
                        @elseif($type!=null && ($type=='Vehicle Traffic Costs List'))
                            <input type="hidden" name="data-key" value="vehicletrafficcostslist">
                        @elseif($type!=null && ($type=='Add Vehicle Traffic Cost'))
                            <input type="hidden" name="data-key" value="addvehicletrafficcost">
                        @endif

                        <table id="example2" class="table table-striped table-bordered table-hover">
                        <thead>
                        @if($type!=null && $type=='Driver List')
                            <tr>
                                <th>Name</th>
                                <th>Signed Up On</th>
                                <th>Mobile Number</th>
                                <th>Details</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th class="text-right">Cash Wallet Balance<sup>ZMW</sup></th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && $type=='Vehicle List')
                            <tr>
                                <th>Reg No</th>
                                <th>Driver</th>
                                <th>Signed Up On</th>
                                <th>Vehicle Type</th>
                                <th>Details</th>
                                <th>Last Location</th>
                                <th>Status</th>
                                <th class="text-right">Outstanding Balance<sup>ZMW</sup></th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && $type=='Passenger List')
                            <tr>
                                <th>Name</th>
                                <th>Mobile Number</th>
                                <th>Signed Up On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Trip Request List' || $type=='Accepted Trip Request List' || $type=='Canceled Trip Request List' || $type=='Pending Trip Request List'))
                            <tr>
                                <th>Passenger</th>
                                <th>Driver</th>
                                <th>Vehicle Reg No</th>
                                <th>Trip Itinerary</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Trips List' || $type=='Completed Trips List' || $type=='Ongoing Trips List' || $type=='Canceled Trips List'))
                            <tr>
								<th style="display: none !important">Id</th>
                                <th>Trip Id & Passenger</th>
                                <th>Vehicle Reg No</th>
                                <th>Driver</th>
                                <th>Trip Details</th>
                                <th>More Details</th>
                                <th>Status</th>
                                <th class="text-right">Amount <sup>(ZMW)</sup></th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='All Payment Transactions List' || $type=='Pending Payment Transactions List' || $type=='Cash Payment Transactions List' || $type=='Wallet Payment Transactions List' || $type=='Card Payment Transactions List'))
                            <tr>
                                <th>Date Paid</th>
                                <th>Transaction Id</th>
                                <th>Driver & Paid By</th>
                                <th>Trip Details</th>
                                <th>Payment Status</th>
                                <th style="text-align:right !important">Amount<sup>(ZMW)</sup></th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Withdrawal Requests List'))
                            <tr>
                                <th>Date Requested</th>
                                <th>Request Id</th>
                                <th>Driver Details</th>
                                <th>Withdrawal Status</th>
                                <th style='text-align:right'>Current Balance (ZMW)</th>
                                <th style='text-align:right'>Withdrawal Amount (ZMW)</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Vehicle Types List'))
                            <tr>
                                <th>Vehicle Type Name</th>
                                <th class="text-center">Vehicle Icon</th>
                                <th>Status</th>
                                <th class="text-right">Base Fare<sup>ZMW</sup></th>
                                <th class="text-right">Charge Per Second<sup>ZMW</sup></th>
                                <th class="text-right">Charge Per Meter<sup>ZMW</sup></th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Vehicle Manufacturers List'))
                            <tr>
                                <th>Vehicle Manufacturer Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Vehicle Makers List'))
                            <tr>
                                <th>Vehicle Maker Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Districts List'))
                            <tr>
                                <th>Name of District</th>
                                <th>Name of Province</th>
                                <th>Country</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Cities List'))
                            <tr>
                                <th>Name of City</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Traffic Costs List'))
                            <tr>
                                <th>Name of District</th>
                                <th style="text-align:right">Base Fare (ZMW)</th>
                                <th style="text-align:right">Minimum Fare (ZMW)</th>
                                <th style="text-align:right">Cancellation Fare (ZMW)</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Vehicle Traffic Costs List'))
                            <tr>
                                <th>Vehicle Type</th>
                                <th>Name of District</th>
                                <th style="text-align:right">Base Fare (ZMW)</th>
                                <th style="text-align:right">Minimum Fare (ZMW)</th>
                                <th style="text-align:right">Cancellation Fare (ZMW)</th>
                                <th>Actions</th>
                            </tr>
                        @elseif($type!=null && ($type=='Add Vehicle Traffic Cost'))
                            <tr>
                                <th>Vehicle Type</th>
                                <th>Name of District</th>
                                <th style="text-align:right">Base Fare (ZMW)</th>
                                <th style="text-align:right">Minimum Fare (ZMW)</th>
                                <th style="text-align:right">Cancellation Fare (ZMW)</th>
                            </tr>
                        @endif
                        </thead>
                        <tbody>
                        @if($type!=null && $type=='Driver List')
                            @foreach($list as $li)
                            <tr>
                                <td><strong>{{$li->name}}</strong><br>
								<small><strong>Gender:</strong> {{$li->gender}}
								</small>
								</td>
                                <td>{{date('Y M d H:s', strtotime($li->created_at))}}Hrs<br>
									<small><strong>Last Login:</strong> {{date('Y M d H:s', strtotime($li->lastLogin))}}Hrs</small>
								</td>
                                <td>+{{$li->mobileNumber}}</td>
                                <td><strong>Identification:</strong> ({{$li->means_of_identification}}) {{$li->means_of_identification_number}}<br><strong>Address:</strong> {{$li->streetAddress}}</td>
                                <td><strong>Type:</strong> {{$li->vehicle_type}}<br>
									<strong>Plate Number:</strong> <i>{{$li->vehicle_plate_number}}</i><br>
									<strong>Make:</strong> <i>{{$li->vehicle_maker}}</i>
								</td>
                                <td>{{$li->status}}<br>
								<small><strong>Vehicle Status:</strong> {{$li->vehicle_status}}</small>
								</td>
                                <td style="text-align:right">{{number_format($li->outstanding_balance, 2, '.', ',')}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                        <ul class="dropdown-menu">
                                            @if($li->status=='Active')
                                                <li><a href="javascript: handleButtonAction('deactivatedriveraccount', {{$li->id}})">Deactivate Driver Account</a></li>
                                            @elseif($li->status=='Inactive')
                                                <li><a href="javascript: handleButtonAction('activatedriveraccount', {{$li->id}})">Activate Driver Account</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && $type=='Vehicle List')
                            @foreach($list as $li)
                            <tr>
                                <td><strong>{{$li->vehicle_plate_number}}</strong></td>
                                <td>{{$li->user_full_name}}</td>
                                <td>{{date('Y M d H:s', strtotime($li->created_at))}}Hrs</td>
                                <td>
                                    <strong>Type: </strong>{{$li->vehicle_type}}<br>
                                    <strong>Maker: </strong>{{$li->vehicle_maker}}<br>
                                    <strong>Brand: </strong>{{$li->vehicle_make}}
                                </td>
                                <td>
                                    <strong>Doors:</strong> {{$li->doors}}<br>
                                    <strong>Year:</strong> {{$li->year}}<br>
                                    <strong>Passenger:</strong> {{$li->passenger_count}}<br>
                                    <!--Ins. Exp: {{date('Y-m', strtotime($li->insurance_expiry))}}<br>-->
                                </td>
                                <td><strong>Latitude: </strong>{{($li->current_latitude!=null ) ? ($li->current_latitude) : "N/A"}}<br>
									<strong>Longitude: </strong>{{($li->current_longitude!=null) ? ($li->current_longitude) : "N/A"}}
								</td>
                                <td>{{$li->status}}</td>
                                <td class="text-right">{{number_format($li->outstanding_balance, 2, '.', ',')}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                        <ul class="dropdown-menu">
                                            @if($li->status=='Valid')
                                                <li><a href="javascript: handleButtonAction('deactivatevehicle', {{$li->id}})">Deactivate Vehicle</a></li>
                                            @elseif($li->status=='Deactivated')
                                                <li><a href="javascript: handleButtonAction('activatevehicle', {{$li->id}})">Activate Vehicle</a></li>
                                            @elseif($li->status=='Available')
                                                <li><a href="javascript: handleButtonAction('deactivatevehicle', {{$li->id}})">Deactivate Vehicle</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && $type=='Passenger List')
                            @foreach($list as $li)
                                <tr>
                                    <td><strong>{{$li->name}}</strong></td>
                                    <td>+{{$li->mobileNumber}}</td>
                                    <td>{{date('Y M d H:s', strtotime($li->created_at))}}Hrs</td>
                                    <td>{{$li->status}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                @if($li->status=='Active')
                                                    <li><a href="javascript: handleButtonAction('deactivatepassenger', {{$li->id}})">Deactivate Passenger Account</a></li>
                                                @elseif($li->status=='Inactive')
                                                    <li><a href="javascript: handleButtonAction('activatepassenger', {{$li->id}})">Activate Passenger Account</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Trip Request List' || $type=='Accepted Trip Request List' || $type=='Canceled Trip Request List' || $type=='Pending Trip Request List'))
                            @foreach($list as $li)
                                <tr>
                                    <td><strong>{{$li->passenger_user_full_name}}</strong></td>
                                    <td>{{$li->driver_user_name}}</td>
                                    <td>{{$li->vehicle_plate_number}}</td>
                                    <td><strong>Pick Up:</strong> {{$li->origin_locality}}<br>
									<strong>Drop Off:</strong> 
									{{$li->destination_locality}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Trip Date:</span> {{date('Y M d H:s', strtotime($li->created_at))}}Hrs</td>
                                    <td>
                                        <span style="text-decoration:underline; font-weight:bold">Distance:</span> {{$li->distance}}Km<br>
                                        <span style="text-decoration:underline; font-weight:bold">Fee(ZMW):</span> {{number_format($li->fee, 2, '.', ',')}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Booking Id:</span> {{strtoupper($li->booking_group_id)}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Payment Mode:</span> {{$li->payment_method}}
                                    </td>
                                    <td>{{$li->status}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                @if($type=='Trip Request List' || $type=='Pending Trip Request List')
                                                    <li><a href="javascript: handleButtonAction('canceltriprequest', {{$li->id}})">Cancel Trip Request</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Trips List' || $type=='Completed Trips List' || $type=='Ongoing Trips List' || $type=='Canceled Trips List'))
                            @foreach($list as $li)
                                <tr>
                                    <td style="display: none !important">{{$li->id}}</td>
                                    <td><strong>{{$li->trip_identifier}}</strong><br>
                                        {{$li->passenger_user_name}}</td>
                                    <td>{{$li->vehicle_plate_number}}</td>
                                    <td>{{$li->vehicle_driver_user_name}}</td>
                                    <td>
                                        <span style="text-decoration:underline; font-weight:bold">Date:</span> {{date('Y M d H:s', strtotime($li->created_at))}}Hrs<br>
                                        <span style="text-decoration:underline; font-weight:bold">Origin:</span> {{$li->origin_vicinity}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Destination:</span> {{$li->destination_vicinity}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Drive Rating:</span> {{$li->drive_rating==null ? "N/A" : $li->drive_rating}}
                                    </td>
                                    <td>
                                        <span style="text-decoration:underline; font-weight:bold">Booking Id:</span> {{$li->deal_booking_group_id}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Paid:</span> {{$li->paidYes!=null && $li->paidYes==1 ? 'Yes' : 'No'}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Pay Method:</span> {{$li->payment_method}}
                                    </td>
                                    <td>{{$li->status}}</td>
                                    <td class="text-right">{{number_format($li->amount_chargeable, 2, '.', ',')}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                @if($type=='Trips List' || $type=='Completed Trips List' || $type=='Ongoing Trips List')
                                                    <li><a href="javascript: handleButtonAction('canceltrip', {{$li->id}})">Cancel Trip</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='All Payment Transactions List' || $type=='Pending Payment Transactions List' || $type=='Cash Payment Transactions List' || $type=='Wallet Payment Transactions List' || $type=='Card Payment Transactions List'))
                            @foreach($list as $li)
                                <tr>
                                    <td>{{date('Y M d H:s', strtotime($li->created_at))}}Hrs</td>
                                    <td>{{$li->transactionRef}}</td>
                                    <td>
                                        <span style="text-decoration:underline; font-weight:bold">Driver:</span> <br>{{$li->vehicle_driver_user_name}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Payee:</span> <br>{{$li->payeeUserFullName}}
                                    </td>
                                    <td>
                                        <span style="text-decoration:underline; font-weight:bold">Trip Id:</span> {{$li->trip_identifier}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Booking Id:</span> {{strtoupper($li->deal_booking_group_id)}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Origin:</span> {{$li->origin_vicinity}}<br>
                                        <span style="text-decoration:underline; font-weight:bold">Destination:</span> {{$li->destination_vicinity}}<br>
                                    </td>
                                    <td>{{$li->status}}</td>
                                    <td style='text-align:right'>{{number_format($li->amount, 2, '.', ',')}}</td>
                                    <td>
                                        @if($li->status=='Success')
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Withdrawal Requests List'))
                            @foreach($list as $li)
                                <tr>
                                    <td>{{date('Y M d H:s', strtotime($li->created_at))}}Hrs</td>
                                    <td>{{$li->request_id}}</td>
                                    <td>
                                        {{$li->user_name}}<br>
                                        {{$li->user_mobile}}
                                    </td>
                                    <td>
                                        {!! $li->details !!}
                                    </td>
                                    <td>{{$li->status}}</td>
                                    <td style='text-align:right'>
                                        {{$li->outstanding_balance}}
                                    </td>
                                    <td>{{number_format($li->amount, 2, '.', ',')}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('approvewithdrawal', {{$li->id}})">Approve Withdrawal</a></li>
                                                <li><a href="javascript: handleButtonAction('disapprovewithdrawal', {{$li->id}})">Disapprove Withdrawal</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>


                            @endforeach
                        @elseif($type!=null && ($type=='Vehicle Types List'))
                            @foreach($list as $li)
                            <tr>
                                    <td>{{$li->name}}</td>
                                    <td class="text-center"><img src="/vehicles/{{$li->icon}}.png" style="height: 40px !important"></td>
                                    <td>{{$li->status==1 ? 'Valid' : 'Invalid'}}</td>
                                    <td class="text-right">{{number_format($li->base_fare_for_vehicle_type, 2, '.', ',')}}</td>
                                    <td class="text-right">{{number_format($li->chargePerSecond, 2, '.', ',')}}</td>
                                    <td class="text-right">{{number_format($li->chargePerMeter, 2, '.', ',')}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('deletevehicletype', {{$li->id}})">Delete Vehicle Type</a></li>
                                            </ul>
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Vehicle Manufacturers List'))
                            @foreach($list as $li)
                            <tr>
                                    <td>{{$li->name}}</td>
                                    <td>{{$li->status==1 ? 'Valid' : 'Invalid'}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('deletevehiclemanufacturer', {{$li->id}})">Delete Delivery Cost</a></li>
                                            </ul>
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Vehicle Makers List'))
                            @foreach($list as $li)
                            <tr>
                                    <td>{{$li->name}}</td>
                                    <td>{{$li->status==1 ? 'Valid' : 'Invalid'}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('deletevehiclemaker', {{$li->id}})">Delete Vehicle Maker Cost</a></li>
                                            </ul>
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Districts List'))
                            @foreach($list as $li)
                            <tr>
                                    <td>{{$li->name}}</td>
                                    <td>{{$li->provinceName}}</td>
                                    <td>{{$li->countryName}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('deletedistrict', {{$li->id}})">Delete District</a></li>
                                            </ul>
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Cities List'))
                            @foreach($list as $li)
                            <tr>
                                    <td>{{$li->name}}</td>
                                    <td>{{$li->status}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('deletecity', {{$li->id}})">Delete City</a></li>
                                            </ul>
                                        </div>
                                    </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Traffic Costs List'))
                            @foreach($list as $li)
                            <tr>
                                <td>{{$li->district_name}}</td>
                                <td style="text-align:right">{{number_format($li->base_fare, 2, '.', ',')}}</td>
                                <td style="text-align:right">{{number_format($li->minimumFare, 2, '.', ',')}}</td>
                                <td style="text-align:right">{{number_format($li->cancellationFee, 2, '.', ',')}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                        <ul class="dropdown-menu">
                                            <li><a href="javascript: handleButtonAction('deletetrafficcost', {{$li->id}})">Delete Traffic Cost</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Vehicle Traffic Costs List'))
                            @foreach($list as $li)
                                <tr>
                                    <td>{{$li->vehicle_type}}</td>
                                    <td>{{$li->district_name}}</td>
                                    <td style="text-align:right">{{number_format($li->base_fare, 2, '.', ',')}}</td>
                                    <td style="text-align:right">{{number_format($li->minimumFare, 2, '.', ',')}}</td>
                                    <td style="text-align:right">{{number_format($li->cancellationFee, 2, '.', ',')}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Action <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a href="javascript: handleButtonAction('deletevehicletrafficcost', {{$li->id}})">Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($type!=null && ($type=='Add Vehicle Traffic Cost'))
                            @foreach($list as $li)
                                <tr>
                                    <td>
                                        <div class="col-sm-12">
                                            <select name="vehicle_{{$li->id}}[]" class="form-control" multiple style="width:100% !important">
                                                @foreach($vehicleTypes as $vehicleType)
                                                    <option value="{{$li->id}}|||{{$vehicleType->name}}">{{$vehicleType->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>{{$li->district_name}}</td>
                                    <td style="text-align:right">{{number_format($li->base_fare, 2, '.', ',')}}</td>
                                    <td style="text-align:right">{{number_format($li->minimumFare, 2, '.', ',')}}</td>
                                    <td style="text-align:right">{{number_format($li->cancellationFee, 2, '.', ',')}}</td>
                                </tr>
                            @endforeach
                        @endif

                        </tbody>
                        </table>

                            @if($type!=null && $type=='Add Vehicle Traffic Cost')
                                <button class="btn btn-primary" name="submit" value="Save" type="submit">Update Vehicle Traffic Costs</button>
                            @endif

                            <input type="hidden" name="datatablesformId" value="" id="datatablesformId">
                            <input type="hidden" name="selectedUserAction" value="" id="selectedUserAction">
                    </form>
                </div>
            </div>
        </div>

    </div>
    </div>

    <!-- Right sidebar -->
    <!--<div id="right-sidebar" class="animated fadeInRight">

        <div class="p-m">
            <button id="sidebar-close" class="right-sidebar-toggle sidebar-button btn btn-default m-b-md"><i class="pe pe-7s-close"></i>
            </button>
            <div>
                <span class="font-bold no-margins"> Analytics </span>
                <br>
                <small> Lorem Ipsum is simply dummy text of the printing simply all dummy text.</small>
            </div>
            <div class="row m-t-sm m-b-sm">
                <div class="col-lg-6">
                    <h3 class="no-margins font-extra-bold text-success">300,102</h3>

                    <div class="font-bold">98% <i class="fa fa-level-up text-success"></i></div>
                </div>
                <div class="col-lg-6">
                    <h3 class="no-margins font-extra-bold text-success">280,200</h3>

                    <div class="font-bold">98% <i class="fa fa-level-up text-success"></i></div>
                </div>
            </div>
            <div class="progress m-t-xs full progress-small">
                <div style="width: 25%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="25" role="progressbar"
                     class=" progress-bar progress-bar-success">
                    <span class="sr-only">35% Complete (success)</span>
                </div>
            </div>
        </div>
        <div class="p-m bg-light border-bottom border-top">
            <span class="font-bold no-margins"> Social talks </span>
            <br>
            <small> Lorem Ipsum is simply dummy text of the printing simply all dummy text.</small>
            <div class="m-t-md">
                <div class="social-talk">
                    <div class="media social-profile clearfix">
                        <a class="pull-left">
                            <img src="/admin/images/a1.jpg" alt="profile-picture">
                        </a>

                        <div class="media-body">
                            <span class="font-bold">John Novak</span>
                            <small class="text-muted">21.03.2015</small>
                            <div class="social-content small">
                                Injected humour, or randomised words which don't look even slightly believable.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social-talk">
                    <div class="media social-profile clearfix">
                        <a class="pull-left">
                            <img src="/admin/images/a3.jpg" alt="profile-picture">
                        </a>

                        <div class="media-body">
                            <span class="font-bold">Mark Smith</span>
                            <small class="text-muted">14.04.2015</small>
                            <div class="social-content">
                                Many desktop publishing packages and web page editors.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social-talk">
                    <div class="media social-profile clearfix">
                        <a class="pull-left">
                            <img src="images/a4.jpg" alt="profile-picture">
                        </a>

                        <div class="media-body">
                            <span class="font-bold">Marica Morgan</span>
                            <small class="text-muted">21.03.2015</small>

                            <div class="social-content">
                                There are many variations of passages of Lorem Ipsum available, but the majority have
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-m">
            <span class="font-bold no-margins"> Sales in last week </span>
            <div class="m-t-xs">
                <div class="row">
                    <div class="col-xs-6">
                        <small>Today</small>
                        <h4 class="m-t-xs">$170,20 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                    <div class="col-xs-6">
                        <small>Last week</small>
                        <h4 class="m-t-xs">$580,90 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <small>Today</small>
                        <h4 class="m-t-xs">$620,20 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                    <div class="col-xs-6">
                        <small>Last week</small>
                        <h4 class="m-t-xs">$140,70 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                </div>
            </div>
            <small> Lorem Ipsum is simply dummy text of the printing simply all dummy text.
                Many desktop publishing packages and web page editors.
            </small>
        </div>

    </div>-->

    <!-- Footer-->
    <footer class="footer">
        <span class="pull-right">
            Example text
        </span>
        Company 2015-2020
    </footer>

</div>

@stop


@section('js')

<!-- Vendor scripts -->
<script src="/admin/vendor/jquery/dist/jquery.min.js"></script>
<script src="/admin/vendor/jquery-ui/jquery-ui.min.js"></script>
<script src="/admin/vendor/slimScroll/jquery.slimscroll.min.js"></script>
<script src="/admin/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/admin/vendor/metisMenu/dist/metisMenu.min.js"></script>
<script src="/admin/vendor/iCheck/icheck.min.js"></script>
<script src="/admin/vendor/sparkline/index.js"></script>
<!-- DataTables -->
<script src="/admin/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="/admin/vendor/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- DataTables buttons scripts -->
<script src="/admin/vendor/pdfmake/build/pdfmake.min.js"></script>
<script src="/admin/vendor/pdfmake/build/vfs_fonts.js"></script>
<script src="/admin/vendor/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="/admin/vendor/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="/admin/vendor/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="/admin/vendor/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
<!-- App scripts -->
<script src="/admin/scripts/homer.js"></script>

<style>
td{
	color: #000 !important;
}


th{
	color: #fff !important;
	background-color: #000 !important;
}

body{
	color: #000 !important;
}
</style>
<script>
    function handleButtonAction(action, usId){

        if(action=='reversepayment' || action=='approvewithdrawal' || action=='disapprovewithdrawal' || action=='deletevehicletype'
                || action=='deletevehiclemanufacturer' || action=='deletevehiclemaker'
                || action=='deletedistrict' || action=='deletecity'
                || action=='deletetrafficcost' || action=='deletevehicletrafficcost')
        {
            if(confirm("Are you sure you want to carry out this action?"))
            {
                document.getElementById('datatablesformId').value = usId;
                document.getElementById('selectedUserAction').value = action;
                document.getElementById('managedataform').submit();
            }
        }else
        {
            document.getElementById('datatablesformId').value = usId;
            document.getElementById('selectedUserAction').value = action;
            document.getElementById('managedataform').submit();
        }
    }


    $(function () {

        // Initialize Example 1
        /*$('#example1').dataTable( {
            "ajax": 'api/datatables.json',
            dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            buttons: [
                {extend: 'copy',className: 'btn-sm'},
                {extend: 'csv',title: 'ExampleFile', className: 'btn-sm'},
                {extend: 'pdf', title: 'ExampleFile', className: 'btn-sm'},
                {extend: 'print',className: 'btn-sm'}
            ]
        });*/

        // Initialize Example 2
        $('#example2').dataTable();

    });

</script>

@stop