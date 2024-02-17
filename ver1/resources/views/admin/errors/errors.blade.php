@if(Session::has('message'))
    <div class="alert alert-dismissible alert-success">
        <button type="button" class="close" data-dismiss="alert">X</button>
        <?php echo Session::get('message'); ?>
    </div>
@endif
@if(Session::has('error'))
    <div class="alert alert-dismissible alert-danger">
        <button type="button" class="close" data-dismiss="alert">X</button>
        <?php echo Session::get('error'); ?>
    </div>
@endif
@if(Session::has('warning'))
    <div class="alert alert-dismissible alert-warning">
        <button type="button" class="close" data-dismiss="alert">X</button>
        <?php echo Session::get('warning'); ?>
    </div>
@endif
@if($errors->any())
    <ul class="alert alert-danger" style="list-style-type: none; margin-left:0px !important">
        @foreach($errors->all() as $error)
            <li> <small>-</small> <?php echo $error; ?></li>
        @endforeach
    </ul>
@endif



