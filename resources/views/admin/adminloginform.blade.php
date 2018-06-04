@extends('layouts.login')

@section('title')
	Admin Login Page
@stop

@section('loginSideBar')
<div class="signin-info">
	<div class="logopanel">
		<img src="{{ URL::asset("assets/images/logo.png") }}" style="height: 30px;"/>
	</div>

	<div class="mb20"></div>

	{!!  \App\Models\Setting::get('adminLoginHtml') !!}
</div>
@stop

@section('loginForm')
{!! Form::open(array('route' => 'admin.login.check', 'class' => 'form', 'id' => 'login-form')) !!}
	<h4 class="nomargin">Sign In</h4>
	<p class="mt5 mb20">Login to access your account.</p>

	<div id="error" ></div>

	{!! Form::text('email', Input::old('email'), array('class' => 'form-control uname', 'placeholder' => 'Email')) !!}
	{!! Form::password('password', array('class' => 'form-control pword', 'id' => 'login-password', 'placeholder' => 'Password')) !!}
	<button class="btn btn-success btn-block">Sign In</button>

{!! Form::close() !!}
@stop

@section('javascript')
<script type="text/javascript">
	$('#login-form').submit(function(e) {
		e.preventDefault();
		$.ajax({
			type: 'post',
			url: $('#login-form').attr('action'),
			cache: false,
			data: $('#login-form').serialize(),
			beforeSend: function() {
				$('#error').html('<div class="alert alert-info">Submitting..</div>');
			},
			success: function(obj) {
				if(obj.success === false) {
					$('#error').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');
				}
				else {
					window.location.href = "{{ URL::route('admin') }}/dashboard";
				}
			},
			error: function(xhr, textStatus, thrownError) {
				alert('Something went wrong. Please try again!');
			}
		});
		return false;
	});
</script>
@stop