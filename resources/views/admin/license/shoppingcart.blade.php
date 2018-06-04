@extends('layouts.admindashboard')

@section('title')
	Shopping Cart
@stop

@section('css')
  {!! Html::style('assets/css/jquery.datatables.css') !!}
  {!! HTML::style('assets/plugin/bootstraptags/bootstrap-tagsinput.css') !!}
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-shopping-cart"></i>Shopping Cart Management</h2>
    <div class="breadcrumb-wrapper">
        <span class="label">You are here:</span>
        <ol class="breadcrumb">
            <li>Super Admin</li>
            <li class="active"><a href="{{ URL::route("admin.licenses.shoppingcart") }}">Shopping Cart Management</a></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-body">
                @if($successMessage != '')
                  <div class="row">
                    <div class="col-sm-12">
                      <div class="alert alert-success">
                            <a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
                            {{ $successMessage }}
                      </div>
                    </div>
                  </div>
                @endif

                {!! Form::open(array('route' => array('admin.licenses.updateshoppingcart', $licenseTypeDetail->id), 'id' => 'updateShoppingCart', 'method' => 'post')) !!}
                    <div id="error"></div>

                    <div class="form-group">
                        <label class="control-label">Product Name</label>
                        <input type="text" name="productName" id="productName" class="form-control" value="{{ $licenseTypeDetail->name }}">
                    </div>

                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="5" placeholder="Description">{{ $licenseTypeDetail->description }}</textarea>
                    </div>

                    <hr>
                    <input type="hidden" name="licenseClass" value="{{ $licenseTypeDetail->licenseClass }}">

                    <div class="form-group" title="Amount of team members that can be added to team for free">
                        <label class="control-label">Free (unpaid) Team Members (Not including Team Admin)</label>
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="freeUsers" id="freeUsers" class="form-control" value="{{ $licenseTypeDetail->free_users }}" />
                            </div>
                        </div>
                    </div>
                    <hr>

					@include('admin.license.licensepricesblock')

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group pull-right">
                                <br>
                                <input type="submit" value="Save Changes" class="btn btn-primary btn-sm" />
                                <input type="reset" value="Cancel" class="btn btn-primary btn-sm" />
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}

                {{--<div class="row">--}}
                    {{--<div class="col-md-12">--}}
                        {{--<div class="table-responsive">--}}
                            {{--<table class="table table-dark mb30">--}}
                                {{--<thead>--}}
                                {{--<tr>--}}
                                    {{--<th>Product</th>--}}
                                    {{--<th>Price</th>--}}
                                    {{--<th>License Type</th>--}}
                                    {{--<th>Edit</th>--}}
                                {{--</tr>--}}
                                {{--</thead>--}}
                                {{--<tbody>--}}
                                    {{--@foreach($licenseTypes as $licenseType)--}}
                                    {{--<tr>--}}
                                        {{--<td>{{ $licenseType->name }}</td>--}}
                                        {{--<td>${{ $licenseType->priceUSD }}</td>--}}
                                        {{--<td>{{ $licenseType->licenseClass }}</td>--}}
                                        {{--<td>{!! Form::open(array('route' => array('admin.licenses.shoppingcart.edit', $licenseType->id), 'class' => 'shoopingCartForm', 'method' => 'post')) !!} <button class="btn btn-info btn-xs"> <i class="fa fa-edit"></i> Edit</button> {!! Form::close() !!}</td>--}}
                                    {{--</tr>--}}
                                    {{--@endforeach--}}
                                {{--</tbody>--}}
                            {{--</table>--}}
                        {{--</div><!-- table-responsive -->--}}
                    {{--</div>--}}
                {{--</div><!-- panel-body -->--}}

                {{--<div class="row pull-right">--}}
                    {{--<div class="col-sm-12">--}}
                        {{--<a onclick="editPerMemberPrice()" class="btn btn-xs btn-primary">Edit per Member Price</a>--}}
                    {{--</div>--}}
                {{--</div>--}}
            </div><!-- panel -->

            {{--<div class="panel">--}}
                {{--<div class="panel-body">--}}
                    {{--<div id="shoppingCartForm"></div>--}}
                {{--</div>--}}
            {{--</div>--}}

            <div class="panel panel-default">
                <form method="post" id="trialSettingForm">
                    <div class="panel-body">
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="ckbox ckbox-primary">
                                    <input type="checkbox" value="1" id="trial_cart" name="trial_cart" @if($trial_cart == 'Yes') checked @endif />
                                    <label for="trial_cart">Enable trial cart</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label">Free Trial Period (Days)</label>
                            <div class="col-md-4">
                                <input class="form-control" name="trial_period" id="trial_period" value="{{ $trial_period }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label>Conversion Tracking Code</label>
                            </div>
                            <div class="col-md-5">
                                <textarea class="form-control" placeholder="Conversion Tracking Code" rows="4" name="conversion_tracking_code">{!! $conversion_tracking_code !!}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="ckbox ckbox-primary">
                                    <input type="checkbox" value="1" id="email_verification" name="email_verification" @if($email_verification == 'Yes') checked @endif />
                                    <label for="email_verification">Require email verification after signup</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="pull-right">
                            <button class="btn btn-primary" type="submit">Submit</button>
                            <button class="btn btn-default" type="reset">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('bootstrapModel')
	{{--<div id="perMemberPriceModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">--}}
	  {{--<div class="modal-dialog modal-sm">--}}
	    {{--<div class="modal-content">--}}
	        {{--<div class="modal-header">--}}
	            {{--<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>--}}
	            {{--<h4 class="modal-title">One Member Price</h4>--}}
	        {{--</div>--}}
	        {{--<div class="modal-body">--}}
	        	{{--<div id="errorprice" ></div>--}}
                {{--<form id="editPriceForm">--}}
	        	{{--<div class="form-group">--}}
                    {{--<label class="control-label">Price in Dollar</label>--}}
                    {{--<input type="text" id="priceInDollar" class="form-control" value="{{ $dollarPrice }}">--}}
                {{--</div>--}}
                {{--<div class="form-group">--}}
                    {{--<label class="control-label">Price in EURO</label>--}}
                    {{--<input type="text" id="priceInEuro" class="form-control" value="{{ $euroPrice }}">--}}
                {{--</div>--}}
                {{--<div class="form-group">--}}
                    {{--<label class="control-label">Price in GBP</label>--}}
                    {{--<input type="text" id="priceInGbp" class="form-control" value="{{ $gbpPrice }}">--}}
                {{--</div>--}}
                {{--{!! csrf_field() !!}--}}
                {{--</form>--}}
	        {{--</div>--}}
	        {{--<div class="modal-footer">--}}
	            {{--<button type="button" id="oneMemberPriceButton" onclick="submitOneMemberPrice()" class="btn btn-primary btn-xs">Save</button>--}}
	        {{--</div>--}}
	    {{--</div>--}}
	  {{--</div>--}}
	{{--</div><!-- Booking model -->--}}
@stop

@section('javascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}
{!! HTML::script('assets/plugin/bootstraptags/bootstrap-tagsinput.min.js') !!}
{!! HTML::script('assets/js/jquery.typewatch.js') !!}

@yield('licensesblock_javascript')

<script type="text/javascript">
$(function() {
    jQuery('#freeUsers').spinner({min: 0});
    jQuery('#trial_period').spinner({min: 0});

    $('#trialSettingForm').submit(function(e) {
        e.preventDefault();
        var thisForm = $(this);
        $.ajax({
            method: 'post',
            url: '{{ URL::route('admin.trial.settings') }}',
            dataType: 'json',
            data: $(this).serialize(),
            beforeSend: function (xhr) {
                thisForm.find('button[type="submit"]').prop('disabled', true);
                blockUI('#trialSettingForm');
            },
            success: function (response) {
                unblockUI('#trialSettingForm');
                slideToElement(thisForm);
                thisForm.find('button[type="submit"]').prop('disabled', false);
                showSuccess(response.message);
            },
            error: function (xhr, textStatus, thrownError) {
                unblockUI('#trialSettingForm');
                slideToElement(thisForm);
                thisForm.find('button[type="submit"]').prop('disabled', false);
                showError('There is some error. Try again!');
            }
        });
    });

    $('#updateShoppingCart').submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: 'post',
            url: $('#updateShoppingCart').attr('action'),
            cache: false,
            data: $('#updateShoppingCart').serialize(),
            beforeSend: function() {
                $('#error').html('<div class="alert alert-info">Submitting..</div>');
            },
            success: function(data) {
                slideToElement('#updateShoppingCart');
                if(data.status == 'fail') {
                    $('#error').html('<div class="alert alert-danger">'+ data.message +'</div>');
                }
                else if(data.status == 'success') {
                    $('#error').html('<div class="alert alert-success">'+ data.message +'</div>');
                }
            },
            error: function(xhr, textStatus, thrownError) {
                slideToElement('#updateShoppingCart');
                alert('Something went wrong. Please try again later!');
            }
        });
        return false;
    });
});

{{--$('.shoopingCartForm').submit(function(e) {--}}
    {{--e.preventDefault();--}}

    {{--$.ajax({--}}
        {{--type: 'post',--}}
        {{--url: $(this).attr('action'),--}}
        {{--cache: false,--}}
        {{--data: $(this).serialize(),--}}
        {{--beforeSend: function() {--}}
            {{--$('#shoppingCartForm').html('<div class="alert alert-info">Loading...</div>');--}}
        {{--},--}}
        {{--success: function(data) {--}}
            {{--$('#shoppingCartForm').html(data);--}}
        {{--},--}}
        {{--error: function(xhr, textStatus, thrownError) {--}}
            {{--alert('Something went wrong. Please Try again later!');--}}
        {{--}--}}
    {{--});--}}
    {{--return false;--}}
{{--});--}}

{{--function editPerMemberPrice() {--}}
    {{--$('#perMemberPriceModel').modal('show');--}}
{{--}--}}

{{--function submitOneMemberPrice() {--}}
    {{--var dollarPrice = $('#priceInDollar').val();--}}
    {{--var euroPrice = $('#priceInEuro').val();--}}
    {{--var gbpPrice = $('#priceInGbp').val();--}}
    {{--var token = $('input[name=_token]').val();--}}

    {{--if(dollarPrice == '') {--}}
        {{--$('#errorprice').html('<div class="alert alert-danger">Price in Dollar is required.</div>');--}}
    {{--}--}}
    {{--else if(euroPrice == '') {--}}
        {{--$('#errorprice').html('<div class="alert alert-danger">Price in Euro is required.</div>');--}}
    {{--}--}}
    {{--else if(gbpPrice == '') {--}}
        {{--$('#errorprice').html('<div class="alert alert-danger">Price in Gbp is required.</div>');--}}
    {{--}--}}
    {{--else{--}}
        {{--$.ajax({--}}
            {{--type: 'post',--}}
            {{--url: "{{ URL::route('admin.licenses.addonememeberprice') }}",--}}
            {{--cache: false,--}}
            {{--data: {"dollarPrice":dollarPrice, "euroPrice":euroPrice, "gbpPrice":gbpPrice, "_token" : token},--}}
            {{--beforeSend: function() {--}}
                {{--blockUI('#perMemberPriceModel');--}}
                {{--$('#oneMemberPriceButton').attr('disabled', 'disabled');--}}
            {{--},--}}
            {{--success: function(data) {--}}
                {{--unblockUI('#perMemberPriceModel');--}}
                {{--location.reload();--}}
            {{--},--}}
            {{--error: function(xhr, textStatus, thrownError) {--}}
                {{--unblockUI('#perMemberPriceModel');--}}
                {{--$('#oneMemberPriceButton').removeAttr('disabled');--}}
                {{--alert('Something went wrong. Please Try again later!');--}}
            {{--}--}}
        {{--});--}}
    {{--}--}}
{{--}--}}
</script>
@stop
