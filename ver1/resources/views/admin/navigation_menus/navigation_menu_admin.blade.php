<aside id="menu">
    <div id="navigation">
        <div class="profile-picture">
            <a href="index-2.html">
			@if(\Auth::user()->passport_photo!=null)
                <img src="/users/{{\Auth::user()->passport_photo}}"  class="img-circle m-b" style="width:60px !important" alt="logo">
			@else
				<i class="fa fa-user" style="font-size: 50px !important"></i>
			@endif
            </a>

            <div class="stats-label text-color">
                <span class="font-extra-bold font-uppercase" style="color: #000 !important">{{\Auth::user()->name}}</span>

                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                        <small class="text-muted" style="color: #000 !important">{{\Auth::user()->role_code}} <b class="caret"></b></small>
                    </a>
                    <ul class="dropdown-menu animated flipInX m-t-xs">
                        <!--<li><a href="profile.html">Profile</a></li>-->
                        <li class="divider"></li>
                        <li><a href="/auth/logout" style="color: #000 !important">Logout</a></li>
                    </ul>
                </div>


                <!--<div id="sparkline1" class="small-chart m-t-sm"></div>
                <div>
                    <!--<h4 class="font-extra-bold m-b-xs">
                        ZMWnumber_format($general_income, 2, '.', ',')
                    </h4>
                    <small class="text-muted">Total income over the last one year in taxi trips.</small>
                </div>-->
            </div>
        </div>

        <ul class="nav" id="side-menu">
            <li class="active">
                <a href="/admin/dashboard"> <span class="nav-label" style="color: #000 !important">Dashboard</span></a>
            </li>
            <li>
                <a href="#"><span class="nav-label" style="color: #000 !important">Drivers</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="/admin/drivers/all-drivers" style="color: #000 !important">All Drivers</a></li>
                    <li><a href="/admin/drivers/all-vehicles" style="color: #000 !important">All Vehicles</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label" style="color: #000 !important">Passengers</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="/admin/passengers/all-passengers" style="color: #000 !important">All Passengers</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label" style="color: #000 !important">Trip Requests</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="/admin/trip-request/all-trip-requests" style="color: #000 !important">All Trip Requests</a></li>
                    <li><a href="/admin/trip-request/accepted-trip-requests" style="color: #000 !important">Accepted Trip Requests</a></li>
                    <li><a href="/admin/trip-request/canceled-trip-requests" style="color: #000 !important">Canceled Trip Requests</a></li>
                    <li><a href="/admin/trip-request/pending-trip-requests" style="color: #000 !important">Pending Trip Requests</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label" style="color: #000 !important">Trips</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="/admin/trips/all-trips" style="color: #000 !important">All Trips</a></li>
                    <li><a href="/admin/trips/completed-trips" style="color: #000 !important">Completed Trips</a></li>
                    <li><a href="/admin/trips/ongoing-trips" style="color: #000 !important">Ongoing Trips</a></li>
                    <li><a href="/admin/trips/canceled-trips" style="color: #000 !important">Canceled Trips</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label" style="color: #000 !important">Financials</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="/admin/financials/all-payments" style="color: #000 !important">All Payments</a></li>
                    <li><a href="/admin/financials/pending-payments" style="color: #000 !important">Pending Payments</a></li>
                    <li><a href="/admin/financials/cash-payments" style="color: #000 !important">Cash Payments</a></li>
                    <li><a href="/admin/financials/card-payments" style="color: #000 !important">Card Payments</a></li>
                    <li><a href="/admin/financials/wallet-payments" style="color: #000 !important">Wallet Payments</a></li>
                    <li><a href="/admin/financials/withdrawal-requests" style="color: #000 !important">Withdrawal Requests</a></li>
                    <li><a href="/admin/financials/driver-deposits" style="color: #000 !important">Driver Deposits</a></li>
                    <li><a href="/admin/financials/driver-payouts" style="color: #000 !important">Driver Payouts</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label" style="color: #000 !important">Settings</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="/admin/settings/vehicle-types" style="color: #000 !important">Vehicle Types</a></li>
                    <li><a href="/admin/settings/vehicle-manufacturers" style="color: #000 !important">Manufacturers</a></li>
                    <li><a href="/admin/settings/vehicle-makers" style="color: #000 !important">Vehicle Makers</a></li>
                    <li><a href="/admin/settings/districts" style="color: #000 !important">Districts</a></li>
                    <li><a href="/admin/settings/cities" style="color: #000 !important">Cities</a></li>
                    <!--<li><a href="/admin/settings/traffic-cost" style="color: #000 !important">Traffic Costs</a></li>
                    <li><a href="/admin/settings/vehicle-traffic-cost" style="color: #000 !important">Vehicle Traffic Costs</a></li>-->
                </ul>
            </li>

        </ul>
    </div>
</aside>