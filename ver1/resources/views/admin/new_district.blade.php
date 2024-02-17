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
@stop


@section('content')
<div id="wrapper">

<div class="small-header">
    <div class="hpanel">
        <div class="panel-body">
            <div id="hbreadcrumb" class="pull-right">
                <ol class="hbreadcrumb breadcrumb">
                    <li><a href="index-2.html">Dashboard</a></li>
                    <li>
                        <span>Forms</span>
                    </li>
                    <li class="active">
                        <span>Forms elements </span>
                    </li>
                </ol>
            </div>
            <h2 class="font-light m-b-xs">
                Forms elements
            </h2>
            <small>Examples of various form controls.</small>
        </div>
    </div>
</div>


    @include('admin.errors.errors')

<div class="content">

<div>
<div class="row">
    <div class="col-lg-12">
        <div class="hpanel">
            <div class="panel-body">
                <h3>{{$bigTitle}}</h3>
                <p>{{$titleDescription}}</p>
            </div>
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-lg-8">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    {{$smallTitle}}
                </div>
                <div class="panel-body">
                <form method="post" action="/admin/settings/add-new-district" enctype="application/x-www-form-urlencoded" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-12 control-label pull-left" style="text-align: left !important">Enter District Details</label>
                    </div>
                    <div class="form-group">

                        <div class="col-sm-3"><input type="text" placeholder="District Name" name="districtname[]" class="form-control"></div>
                        <div class="col-sm-3"><input type="text" placeholder="District Code" name="districtcode[]" class="form-control"></div>
                        <div class="col-sm-6">
                            <select name="provincename[]" class="form-control">
                                @foreach($provinces as $province)
                                    <option value="{{$province->id}}|||{{$province->name}}|||{{$province->country->id}}|||{{$province->country->name}}">{{$province->name}} - {{$province->country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">

                        <div class="col-sm-3"><input type="text" placeholder="District Name" name="districtname[]" class="form-control"></div>
                        <div class="col-sm-3"><input type="text" placeholder="District Code" name="districtcode[]" class="form-control"></div>
                        <div class="col-sm-6">
                            <select name="provincename[]" class="form-control">
                                @foreach($provinces as $province)
                                    <option value="{{$province->id}}|||{{$province->name}}|||{{$province->country->id}}|||{{$province->country->name}}">{{$province->name}} - {{$province->country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">

                        <div class="col-sm-3"><input type="text" placeholder="District Name" name="districtname[]" class="form-control"></div>
                        <div class="col-sm-3"><input type="text" placeholder="District Code" name="districtcode[]" class="form-control"></div>
                        <div class="col-sm-6">
                            <select name="provincename[]" class="form-control">
                                @foreach($provinces as $province)
                                    <option value="{{$province->id}}|||{{$province->name}}|||{{$province->country->id}}|||{{$province->country->name}}">{{$province->name}} - {{$province->country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">

                        <div class="col-sm-3"><input type="text" placeholder="District Name" name="districtname[]" class="form-control"></div>
                        <div class="col-sm-3"><input type="text" placeholder="District Code" name="districtcode[]" class="form-control"></div>
                        <div class="col-sm-6">
                            <select name="provincename[]" class="form-control">
                                @foreach($provinces as $province)
                                    <option value="{{$province->id}}|||{{$province->name}}|||{{$province->country->id}}|||{{$province->country->name}}">{{$province->name}} - {{$province->country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">

                        <div class="col-sm-3"><input type="text" placeholder="District Name" name="districtname[]" class="form-control"></div>
                        <div class="col-sm-3"><input type="text" placeholder="District Code" name="districtcode[]" class="form-control"></div>
                        <div class="col-sm-6">
                            <select name="provincename[]" class="form-control">
                                @foreach($provinces as $province)
                                    <option value="{{$province->id}}|||{{$province->name}}|||{{$province->country->id}}|||{{$province->country->name}}">{{$province->name}} - {{$province->country->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-8">
                        <button class="btn btn-danger" name="submit" value="Cancel" type="submit">Cancel</button>
                        <button class="btn btn-primary" name="submit" value="Save" type="submit">Save changes</button>
                    </div>
                </div>
                </form>
                </div>
            </div>
        </div>
    </div>

</div>

</div>
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

    <!-- App scripts -->
    <script src="/admin/scripts/homer.js"></script>
@stop