@extends('layouts.login')

@section('title')
	Sign In
@stop

@section('loginSideBar')
{!! \App\Models\Setting::get('loginHtml') !!}
@stop

@section('loginForm')
	
@if(Input::get("payment") == "true")
	<div class="alert alert-success"><i class="fa fa-check"></i> {{ Session::get('message') }}</div>
@endif

{!! Form::open(array('route' => 'user.login.check', 'class' => 'form', 'id' => 'login-form')) !!}
<h4 class="nomargin">Sign In</h4>
<p class="mt5 mb20">Login to access your account.</p>
<div id="error" ></div>
<div class="clearfix">
	@if($successMessage != '')

		<div class="alert alert-success" id="successMessage">{!! $successMessage !!}</div>


	@endif
	</div>
	{!! Form::text('email', Input::old('email'), array('class' => 'form-control uname', 'id' => 'login-username', 'placeholder' => 'Username')) !!}
	{!! Form::password('password', array('class' => 'form-control pword', 'id' => 'login-password', 'placeholder' => 'Password')) !!}
	<a href="javascript:void(0)" onclick="recoverEmail()"><small>Forgot Your Password?</small></a>
	<button class="btn btn-success btn-block btn-signup">Sign In</button>
{!! Form::close() !!}
@stop

@section('bootstrapModel')
	<!-- Password Reset model -->
<div id="passwordRecoverModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
	        <div class="modal-header">
	            <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <div id="headerInfo">
		            <h4 class="modal-title">Password Recover.</h4>
		            <p>Enter valid email id for which you want to recover password.</p>
	        	</div>
	        </div>
	        <div class="modal-body">
	        	<div class="form-horizontal">
	        	<div id="error1" ></div>
	        	<div class="row hidediv">
	        		<div class="col-sm-10">
	        			<div class="form-group form-horizontal">
		                  <label class="col-sm-3 control-label">Email</label>
		                  <div class="col-sm-9">
							  <input type="hidden" value="{{ csrf_token() }}" name="_token" id="_token"/>
		                    <input id="recoverEmail" type="text" class="form-control" />
		                  </div>
		                </div>
	        		</div>
	        	</div>
	        </div>
			</div>
	        <div class="panel-footer hidediv">
                 <div class="row">
                    <div class="col-sm-8 col-sm-offset-4">
                      <button id="resetPasswordButton" class="btn btn-success">Recover Password</button>
                    </div>
                 </div>
            </div><!-- panel-footer -->
	    </div>
	</div>
</div><!-- Salesman model -->
@stop

@section('javascript')
	@if(Session::has('trial'))
		@if(Session::get('trial') === true)
			{!! \App\Models\Setting::get('conversion_tracking_code') !!}
		@endif
	@endif
<script type="text/javascript">
$(function(){
	@if(Session::has('action') && (Session::get('action') == 'accountBlocked'))
		<?php Session::forget('action'); ?>
		showSuccess('Your account has been closed');
	@endif
});

$('#login-form').submit(function(e) {
	$('#successMessage').remove();
	e.preventDefault();
	$.ajax({
		type: 'post',
		url: $('#login-form').attr('action'),
		cache: false,
		data: $('#login-form').serialize(),
		beforeSend: function() {
			$('#error').html('<div class="alert alert-info">Submitting..</div>');
		},
		success: function(data) {
			var obj = jQuery.parseJSON(data);
			if(obj.success === false) {
				$('#error').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');
			} else {
				if(typeof obj.redirect_url !== "undefined") {
					window.location.href = obj.redirect_url;
				} else {
					window.location.href = "{{ URL::route('user') }}/dashboard";
				}
			}
		},
		error: function(xhr, textStatus, thrownError) {
			$('#error').html('<div class="alert alert-danger"><p>Something went wrong. Please try again!</p></div>');
		}
	});
	return false;
});

function recoverEmail() {
	$('#passwordRecoverModal').modal('show');
}

$('#resetPasswordButton').click(function() {
	var email = $('#recoverEmail').val();
	var token = $('#_token').val();
	$('#resetPasswordButton').attr('disabled','disabled');
	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.resetpasswordemail') }}",
		cache: false,
		data: {"email": email, "_token": token},
		beforeSend: function() {
			$('#error1').html('<div class="alert alert-info">Submitting request..</div>');
		},
		success: function(data) {
			$('#resetPasswordButton').removeAttr('disabled');
			var obj = jQuery.parseJSON(data);

			if(obj.success === "success") {
				$('.hidediv').hide();
				$('#error1').html('<div class="alert alert-success"><p>'+obj.error+'</p></div>');
			}
			else {
				$('#error1').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');
			}
		},
		error: function(xhr, textStatus, thrownError) {
			$('#resetPasswordButton').removeAttr('disabled');
			$('#error1').html('<div class="alert alert-danger"><p>Something went wrong. Please try again!</p></div>');
		}
	});
});
</script>
@stop
