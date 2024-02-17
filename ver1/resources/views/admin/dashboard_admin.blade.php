@extends('admin.layouts.layout')

@section('css')
    <link rel="stylesheet" href="/admin/vendor/fontawesome/css/font-awesome.css" />
    <link rel="stylesheet" href="/admin/vendor/metisMenu/dist/metisMenu.css" />
    <link rel="stylesheet" href="/admin/vendor/animate.css/animate.css" />
    <link rel="stylesheet" href="/admin/vendor/bootstrap/dist/css/bootstrap.css" />

    <!-- App styles -->
    <link rel="stylesheet" href="/admin/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css" />
    <link rel="stylesheet" href="/admin/fonts/pe-icon-7-stroke/css/helper.css" />
    <link rel="stylesheet" href="/admin/styles/style.css">

    <style>
        .skin-option {
            position: fixed;
            text-align: center;
            right: -1px;
            padding: 10px;
            top: 80px;
            width: 150px;
            height: 133px;
            text-transform: uppercase;
            background-color: #ffffff;
            box-shadow: 0 1px 10px 0px rgba(0, 0, 0, 0.05), 10px 12px 7px 3px rgba(0, 0, 0, .1);
            border-radius: 4px 0 0 4px;
            z-index: 100;
        }
    </style>
@stop


@section('content')
    <div id="wrapper">

        <div class="content">
            <div class="row">
                <div class="col-lg-12 text-center welcome-message">
                    <h2 style="color: #000 !important">
                        Welcome to Tweende
                    </h2>

                    <p style="color: #000 !important">
                        <strong>Kindly Note:</strong> Data displayed on this dashboard are refreshed on page refresh
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-heading" style="color: #000 !important">
                            <div class="panel-tools">
                                <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                                <a class="closebox"><i class="fa fa-times"></i></a>
                            </div>
                            <h4 style="color: #000 !important">Dashboard information and statistics</h4>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="small" style="color: #000 !important">
                                        <i class="fa fa-user" style="color:#ff6600"></i> Taxi Drivers
                                    </div>
                                    <div>
                                        <h1 class="font-extra-bold m-t-xl m-b-xs" style="color: #000 !important">
                                            {{$allTaxiDrivers}}
                                        </h1>
                                        <small style="color: #000 !important">Active Drivers on Platform</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center small" style="color: #000 !important">
                                        <i class="fa fa-laptop"></i> Active users in current month (December)
                                    </div>
                                    <div class="flot-chart" style="height: 160px">
                                        <div class="flot-chart-content" id="flot-line-chart"></div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="small" style="color: #000 !important">
                                        <i class="fa fa-user-o" style="color:#62cb31"></i> Passengers
                                    </div>
                                    <div>
                                        <h1 class="font-extra-bold m-t-xl m-b-xs" style="color: #000 !important">
                                            {{$allPassengers}}
                                        </h1>
                                    </div>
                                    <div class="small m-t-xl" style="color: #000 !important">
                                        <i class="fa fa-clock-o" style="color: #000 !important"></i> Active Passengers on Platform
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer" style="color: #000 !important">
                        <span class="pull-right" style="color: #000 !important">
                              <strong style="color: #000 !important">Last trip from</strong> {{$lastTrip->origin_vicinity}} <strong>to</strong> {{$lastTrip->destination_vicinity}}: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><sup>ZMW</sup>{{number_format($lastTrip->amount_chargeable, 2, '.', ',')}}</strong>
                        </span style="color: #000 !important">
                            As at: {{date('Y M d H:m')}}Hrs
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="hpanel">
                        <div class="panel-body text-center h-200">
                            <i class="pe-7s-graph1 fa-4x"></i>

                            <h1 class="m-xs" style="color: #000 !important">ZMW{{number_format($total_transactions, 2, '.', ',')}}</h1>

                            <h3 class="font-extra-bold no-margins text-success" style="color: #000 !important">
                                All Payments
                            </h3>
                            <small style="color: #000 !important">All Payments (Cash, Card, Wallet payments)</small>
                        </div>
                        <div class="panel-footer" style="color: #000 !important">
                            Click to view <a href="/admin/financials/cash-payments">Cash Transactions</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="hpanel stats">
                        <div class="panel-body h-200">
                            <div class="stats-title pull-left">
                                <h4 style="color: #000 !important">Trip Requests</h4>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="pe-7s-share fa-4x"></i>
                            </div>
                            <div class="m-t-xl">
                                <h3 class="m-b-xs" style="color: #000 !important">{{number_format($trip_requests, 0, '.', ',')}}</h3>
                        <span class="font-bold no-margins" style="color: #000 !important">
                            Trip requests by passengers
                        </span>

                                <div class="progress m-t-xs full progress-small">
                                    <div style="width: {{round(($tripsCompleted/($allTripRequests==0 ? 1 : $allTripRequests))*100)}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{round(($tripsCompleted/($allTripRequests==0 ? 1 : $allTripRequests))*100)}}"
                                         role="progressbar" class=" progress-bar progress-bar-success">
                                        <span class="sr-only" style="color: #000 !important">{{round(($tripsCompleted/($allTripRequests==0 ? 1 : $allTripRequests))*100)}}% Complete (success)</span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-xs-6">
                                        <small class="stats-label" style="color: #000 !important">Completed</small>
                                        <h4 style="color: #000 !important">{{round(($tripsCompleted/($allTripRequests==0 ? 1 : $allTripRequests))*100)}}%</h4>
                                    </div>

                                    <div class="col-xs-6">
                                        <small class="stats-label" style="color: #000 !important">Trips / Rejected</small>
                                        <h4 style="color: #000 !important">{{round(($tripsRejected/($allTripRequests==0 ? 1 : $allTripRequests))*100)}}%</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer" style="color: #000 !important">
                            Actual Trips by drivers
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="hpanel stats">
                        <div class="panel-body h-200">
                            <div class="stats-title pull-left">
                                <h4 style="color: #000 !important">Cash Payments</h4>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="pe-7s-cash fa-4x"></i>
                            </div>
                            <div class="m-t-xl">
                                <h1 class="text-success" style="color: #000 !important">ZMW{{number_format($cashAccrued, 2, '.', ',')}}</h1>
                        <span class="font-bold no-margins" style="color: #000 !important">
                            Transaction details
                        </span>
                                <br/>
                                <small style="color: #000 !important">
                                    Total amount accrued from cash collections by drivers rather than electronic payments
                                </small>
                            </div>
                        </div>
                        <div class="panel-footer" style="color: #000 !important">
                            Cash based transactions
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="hpanel stats">
                        <div class="panel-body h-200">
                            <div class="stats-title pull-left">
                                <h4 style="color: #000 !important">Todays Payments</h4>
                            </div>
                            <div class="stats-icon pull-right">
                                <i class="pe-7s-cash fa-4x"></i>
                            </div>
                            <div class="clearfix"></div>
                            <div class="flot-chart">
                                <div class="flot-chart-content" id="flot-income-chart"></div>
                            </div>
                            <div class="m-t-xs">

                                <div class="row">
                                    <div class="col-xs-7">
                                        <small class="stat-label" style="color: #000 !important">Last week</small>
                                        <h4 style="color: #000 !important">(ZMW){{number_format($lastWeekTransactionSum, 2, '.', ',')}} <i class="fa fa-level-up text-success"></i></h4>
                                    </div>
                                    <div class="col-xs-5">
                                        <small class="stat-label" style="color: #000 !important">Today</small>
                                        <h4 style="color: #000 !important">(ZMW){{number_format($todayTransactionSum, 2, '.', ',')}} </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer" style="color: #000 !important">
                            All successful transactions
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-heading">
                            <div class="panel-tools">
                                <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                                <a class="closebox"><i class="fa fa-times"></i></a>
                            </div>
                            <h4  style="color: #000 !important">Todays Trips</h4>
                        </div>
                        <div class="panel-body list">
                            <div class="table-responsive project-list">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>

                                        <th style="color: #000 !important">Trip Detail</th>
                                        <th style="color: #000 !important">Trip Number</th>
                                        <th style="color: #000 !important">Driver</th>
                                        <th style="color: #000 !important">Passenger</th>
                                        <th style="color: #000 !important">Plate No</th>
                                        <th style="color: #000 !important">Vehicle</th>
                                        <th style="color: #000 !important">Status</th>
                                        <th class="text-right" style="color: #000 !important">Amount (ZMW)</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($lastFiveTrips as $trip)
                                    <tr>
                                        <td style="color: #000 !important">{{$trip->origin_vicinity}} <strong>to</strong> {{$trip->destination_vicinity}}
                                            <br/>
                                            <small><i class="fa fa-clock-o"></i> Created {{date('Y M d H:m', strtotime($trip->created_at))}}</small>
                                        </td>
                                        <td style="color: #000 !important">{{$trip->trip_identifier}}</td>
                                        <td style="color: #000 !important">{{$trip->vehicle_driver_user_name}}</td>
                                        <td style="color: #000 !important">{{$trip->passenger_user_name}}</td>
                                        <td style="color: #000 !important">{{$trip->vehicle_plate_number}}</td>
                                        <td style="color: #000 !important">{{$trip->vehicle_type}}</td>
                                        <td style="color: #000 !important">
											{{$trip->status}}
                                        </td>
                                        <td class="text-right" style="color: #000 !important"><strong>{{number_format($trip->amount_chargeable, 2, '.', ',')}}</strong></td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer-->
        <footer class="footer" style="color: #000 !important">
            <span class="pull-right" style="color: #000 !important">
                Tweende
            </span>
            Probase | Copyright {{date('Y')}}
        </footer>

    </div>
@stop


@section('js')
    <script src="/admin/vendor/jquery/dist/jquery.min.js"></script>
    <script src="/admin/vendor/jquery-ui/jquery-ui.min.js"></script>
    <script src="/admin/vendor/slimScroll/jquery.slimscroll.min.js"></script>
    <script src="/admin/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="/admin/vendor/jquery-flot/jquery.flot.js"></script>
    <script src="/admin/vendor/jquery-flot/jquery.flot.resize.js"></script>
    <script src="/admin/vendor/jquery-flot/jquery.flot.pie.js"></script>
    <script src="/admin/vendor/flot.curvedlines/curvedLines.js"></script>
    <script src="/admin/vendor/jquery.flot.spline/index.js"></script>
    <script src="/admin/vendor/metisMenu/dist/metisMenu.min.js"></script>
    <script src="/admin/vendor/iCheck/icheck.min.js"></script>
    <script src="/admin/vendor/peity/jquery.peity.min.js"></script>
    <script src="/admin/vendor/sparkline/index.js"></script>

    <!-- App scripts -->
    <script src="/admin/scripts/homer.js"></script>
    <script src="/admin/scripts/charts.js"></script>

    <script>

        $(function () {

            /**
             * Flot charts data and options
             */

            var data1 = [ [{{$x1}}, {{$pM[$x1++]}}], [{{$x1}}, {{$pM[$x1++]}}], [{{$x1}}, {{$pM[$x1++]}}], [{{$x1}}, {{$pM[$x1++]}}], 
        [{{$x1}}, {{$pM[$x1++]}}], [{{$x1}}, {{$pM[$x1++]}}], [{{$x1}}, {{$pM[10]}}], [{{$x1}}, {{$pM[11]}}] ];
            var data2 = [ [{{$y1}}, {{$dM[$y1++]}}], [{{$y1}}, {{$dM[$y1++]}}], [{{$y1}}, {{$dM[$y1++]}}], [{{$y1}}, {{$dM[$y1++]}}], 
        [{{$y1}}, {{$dM[$y1++]}}], [{{$y1}}, {{$dM[$y1++]}}], [{{$y1}}, {{$dM[$y1++]}}], [{{$y1}}, {{$dM[$y1++]}}] ];

            var chartUsersOptions = {
                series: {
                    splines: {
                        show: true,
                        tension: 0.4,
                        lineWidth: 1,
                        fill: 0.4
                    },
                },
                grid: {
                    tickColor: "#f0f0f0",
                    borderWidth: 1,
                    borderColor: 'f0f0f0',
                    color: '#6a6c6f'
                },
                colors: [ "#62cb31", "#ff6600"],
            };

            $.plot($("#flot-line-chart"), [data1, data2], chartUsersOptions);

            /**
             * Flot charts 2 data and options
             */
            var chartIncomeData = [
                {
                    label: "line",
                    data: [ [1, 10], [2, 26], [3, 16], [4, 36], [5, 32], [6, 51] ]
                }
            ];

            var chartIncomeOptions = {
                series: {
                    lines: {
                        show: true,
                        lineWidth: 0,
                        fill: true,
                        fillColor: "#64cc34"

                    }
                },
                colors: ["#62cb31"],
                grid: {
                    show: false
                },
                legend: {
                    show: false
                }
            };

            $.plot($("#flot-income-chart"), chartIncomeData, chartIncomeOptions);



        });

    </script>
    <script>
        /*(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','http://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-4625583-2', 'webapplayers.com');
        ga('send', 'pageview');*/

    </script>
@stop