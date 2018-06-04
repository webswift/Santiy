@extends('layouts.dashboard')

@section('title')
	Profile Page
@stop

@section('css')
{!! Html::style('assets/js/card-master/lib/css/card.css') !!}
@stop

@section('content')
<div class="pageheader"><h2><i class="fa fa-user"></i> Edit Profile</h2></div>

<div class="contentpanel">
	<div class="row">
		<div class="col-sm-3">
			<div class="profile-header">
				<h2 class="profile-name">{{ $userDetails->firstName }} {{ $userDetails->lastName }}</h2>
				@if($userDetails->userType === 'Single' || $userDetails->userType === 'Multi')
					<div class="profile-location"><i class="fa fa-map-marker"></i>{{ $userDetails->country }}</div>
					<div class="profile-position"><i class="fa fa-briefcase"></i> Company: {{ $userDetails->companyName }}</div>
				@endif

				<div class="row text-center">
					<button onclick="changeEmailSetting()" class="btn btn-primary btn-block btn-sm mt10">Edit Email Settings</button>
				</div>
				<div class="row text-center">
					<button onclick="changeDialerSetting()" class="btn btn-primary btn-block btn-sm mt10">Dialer Options</button>
				</div>
				<div class="row text-center">
					<button onclick="changePasswordModal()" class="btn btn-primary btn-block btn-sm mt10">Change Password</button>
				</div>
				<div class="row text-center">
					<button onclick="bccEmailModal()" class="btn btn-primary btn-block btn-sm mt10">Add BCC</button>
				</div>

				<div class="mb10"></div>

                <div class="row">
                    <div class="ckbox ckbox-primary">
                        <input type="checkbox" value="Yes" id="enableCallTimer" @if($userDetails->enableCallTimer == 'Yes')checked="checked"@endif />
                        <label for="enableCallTimer">Enable Call Timer</label>
                    </div>
                </div>

				@if($userDetails->userType === 'Multi')
					<p>&nbsp;</p>
					<div><b>Receive Statistic Emails:</b></div>
					<div class="form-group">
						<select id="statisticUpdates" class="form-control input-sm mb15">
							<option value="Daily" {{ ($userDetails->statisticUpdates == "Daily") ? "selected" : ""  }}>Daily</option>
							<option value="Weekly" {{ ($userDetails->statisticUpdates == "Weekly") ? "selected" : ""  }}>Weekly</option>
							<option value="Monthly" {{ ($userDetails->statisticUpdates == "Monthly") ? "selected" : ""  }}>Monthly</option>
							<option value="Never" {{ ($userDetails->statisticUpdates == "Never") ? "selected" : ""  }}>Never</option>
						</select>
					</div>
				@endif
				<div class="mb20"></div>
			</div>
		</div>
		<div class="col-sm-9">
			<ul class="nav nav-tabs nav-justified nav-profile">
				<li class="active"><a href="#activities" data-toggle="tab"><strong>Account Information</strong></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="activities">
					@if($successMessage != '')
						<div class="alert alert-success">
							<a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
							{{ $successMessage }}
						</div>
					@endif
					@if($errorMessage != '')
						<div class="alert alert-danger">
                    		<a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
                    		{{ $errorMessage }}
                  		</div>
					@endif

					{!! Form::open(array('route' => array('user.editprofile', $userDetails->id), 'id' => 'editProfileForm', 'class' =>'form-horizontal form-bordered',  'method' => 'post')) !!}
						<div id="error" ></div>
						<div class="form-group">
							<label class="col-sm-3 control-label">First Name</label>
							<div class="col-sm-6">
								<input type="text" placeholder="Enter First Name" name="firstName" value="{{ $userDetails->firstName }}" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Last Name</label>
							<div class="col-sm-6">
								<input type="text" placeholder="Enter Last Name" name="lastName" value="{{ $userDetails->lastName }}" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Registered email address</label>
							<div class="col-sm-6">
								<input type="text" placeholder="Enter Email Address" name="email" value="{{ $userDetails->email }}" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="disabledinput">Account Created On</label>
							<div class="col-sm-6">
								<input type="text" value="{{ \App\Http\Controllers\CommonController::formatDateForDisplay($userDetails->accountCreationDate) }}" class="form-control" disabled="" />
							</div>
						</div>

						@if($userDetails->userType === 'Single' || $userDetails->userType === 'Multi')
						<div class="form-group">
							<label class="col-sm-3 control-label" for="disabledinput">@if($transaction) Next Payment Due on @else License expires on @endif</label>
							<div class="col-sm-6">
								<input value="{{ \App\Http\Controllers\CommonController::formatDateForDisplay($licenseDetails->expireDate) }}" class="form-control" disabled="" />
							</div>
							<div class="col-sm-3">
								@if($licenseWillExpire)
									@if($userDetails->isTrial)
										<a href="{{ URL::route('user.payment.showContainer') }}" class="btn btn-primary btn-sm">Buy a license now</a>
									@else
										<a href="{{ URL::route('user.payment.showContainer') }}" class="btn btn-primary btn-sm">Renew Now</a>
									@endif
								@endif
                                @if($userDetails->resubscription == 'Yes' && $userDetails->userType == 'Multi' && $userDetails->existing == 'No')
                                    <a href="{{ URL::route('user.payment.resubscribe') }}" class="btn btn-primary btn-sm">Resubscribe license</a>
                                @endif
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label" for="disabledinput">License volume</label>
							<div class="col-sm-6">
								@if($userDetails->userType === 'Single')
									<input placeholder="Disabled Input" value="Single license" class="form-control" disabled="" />
								@else
									<input value="{{ $licenseDetails->licenseVolume + 1 }}" class="form-control"
                                           data-toggle="tooltip" data-placement="auto" title="Maximum {{ $licenseDetails->licenseVolume }} users + 1 Admin User" readonly />
								@endif
							</div>
                            @if(!$licenseWillExpire)
							@if($userDetails->userType === 'Multi' && !$user->isTrial && $userDetails->resubscription == 'No')
								<div class="col-sm-3">
									<a href="{{ URL::route('user.team.upgrade') }}" class="btn btn-primary btn-sm">Update</a>
								</div>
							@endif
                            @endif
						</div>

						@if(!$user->isTrial)
						 @if(isset($userPaymentInfo['info']))
						<div class="form-group">
							@if($userPaymentInfo['info']->recurring_type == 'Monthly')
								<label class="col-sm-3 control-label" for="disabledinput">You Monthly Cost is</label>
							@else
								<label class="col-sm-3 control-label" for="disabledinput">You Yearly Cost is</label>
							@endif
							<div class="col-sm-6">
								<input placeholder="Disabled Input" value="{{ $cost }}" class="form-control" disabled="" />
							</div>
                            @if($userDetails->resubscription == 'No')
                            @if(!$licenseWillExpire)
							<div class="col-sm-3">
								<a href="{{ route('user.paymentdetail.edit') }}" title="Change Payment Details" class="btn btn-primary btn-sm">Change Payment Details</a>
							</div>
                            @endif
                            @endif
						</div>
						 @endif
						@endif

						@endif

						@if($userDetails->userType === 'Team')
						<div class="form-group">
							<label class="col-sm-3 control-label">Skype ID</label>
							<div class="col-sm-6">
								<input type="text" placeholder="Enter Skype Id" value="{{ $userDetails->skypeID }}" name="skypID" class="form-control" />
							</div>
						</div>
						@endif

						<div class="form-group">
							<label class="col-sm-3 control-label">Your Contact Number</label>
							<div class="col-sm-6">
								<input type="text" placeholder="Enter Contact Number" value="{{ $userDetails->contactNumber }}" name="contactNumber" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Timezone</label>
							<div class="col-sm-6">
								{!! Timezone::selectForm($userDetails->timeZoneName
									, 'Select a timezone'
									, ['class' => 'form-control mb15', 'name' => 'timeZoneName', 'id' => 'timeZoneName', 'title' => 'Offset without DST']); 
								!!}
							</div>
						</div>
						<div class="panel-footer">
							<div class="row">
								<div class="col-sm-6 col-sm-offset-3">
									<button class="btn btn-primary">Save Change</button>
									@if($userDetails->isTrial)
										<button type="button" onclick="blockAccount()" class="btn btn-primary">Close Account</button>
									@endif
								</div>
							</div>
						</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@section('bootstrapModel')
<div id="changeEmailSettingModel" class="modal bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
		<div id="headerInfo">
			<h4 class="modal-title">Edit default email settings</h4>
			<p>Change email configuration</p>
		</div>
	</div>
	<div class="modal-body">
		<div class="row">
			<div class="col-sm-offset-3 col-sm-9">
				<div class="ckbox ckbox-default mb15">
					<input type="checkbox" class="managerUsers" value="Yes" id="useDefaultEmailSetting" @if($emailSettingExists == false) checked @endif>
					<label for="useDefaultEmailSetting">Tick to use SanityOS default settings</label>
				</div>
			</div>
		</div>
		<div class="row"><div class="col-sm-12"><div id="error4"></div></div></div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">From email*:</label>
					<div class="col-sm-9">
						<input id="fromEmail" type="text" class="form-control editEmailDiv" @if($emailSettingExists) value="{{ $emailSettingDetail->fromEmail }}" @else disabled @endif />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">Reply to email*:</label>
					<div class="col-sm-9">
						<input id="replyToEmail" type="text" class="form-control editEmailDiv" @if($emailSettingExists) value="{{ $emailSettingDetail->replyToEmail }}" @else disabled @endif />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">Host*:</label>
					<div class="col-sm-9">
						<input id="emailHost" type="text" class="form-control editEmailDiv" @if($emailSettingExists) value="{{ $emailSettingDetail->host }}" @else disabled @endif />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">Port*:</label>
					<div class="col-sm-9">
						<input id="emailPort" type="text" class="form-control editEmailDiv" @if($emailSettingExists) value="{{ $emailSettingDetail->port }}" @else disabled @endif />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">UserName*:</label>
					<div class="col-sm-9">
						<input id="emailUsername" type="text" class="form-control editEmailDiv" @if($emailSettingExists) value="{{ $emailSettingDetail->userName }}" @else disabled @endif/>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">Password*:</label>
					<div class="col-sm-9">
						<input id="emailPassword" type="password" class="form-control editEmailDiv" @if($emailSettingExists) value="{{ $emailSettingDetail->password }}" @else disabled @endif />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group form-horizontal">
					<label class="col-sm-3 control-label">Security</label>
					<div class="col-sm-9">
						<select id="emailSecurity" name="emailSecurity" class="form-control editEmailDiv" @if(!$emailSettingExists) disabled  @endif>
							<option value="No"@if($emailSettingExists && $emailSettingDetail->security == 'No') selected @endif>None</option>
							<option value="tls" @if($emailSettingExists && $emailSettingDetail->security == 'tls') selected @endif>TLS/STARTTLS</option>
							<option value="ssl" @if($emailSettingExists && $emailSettingDetail->security == 'ssl') selected @endif>SSL</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3">
				<button id="saveEmailSettingButton" @if($emailSettingExists == false) disabled @endif onclick="saveEmailSetting()" class="btn btn-xs btn-success editEmailDiv">Verify and Save Email Settings</button>
			</div>
		</div>
	</div>
</div>
</div>
</div>

<div id="changeDialerSettingModel" class="modal bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
		<div id="headerInfo">
			<h4 class="modal-title">Dialing Services</h4>
			<p>SanityOS has a power dial function which uses call to protocol. This is compatible with many Softphones including Skype and Zoiper. </p>
		</div>
	</div>
	<form method="post" id="dialerSettingForm">
		<div class="modal-body">
			<div class="row"><div class="col-sm-12"><div id="error5"></div></div></div>
			<div class="row">
				<div class="col-sm-12">
					<div class="rdio rdio-primary">
						<input type="radio" name="callOption" value="enableCall" id="enableCall" @if($user->enableCall == 'Yes'){{'checked'}}@endif>
						<label for="enableCall">Select to enable click to dial setting</label>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="rdio rdio-primary">
						<input type="radio" name="callOption" value="enablePowerDial" id="enablePowerDial" @if($user->enablePowerDial == 'Yes'){{'checked'}}@endif>
						<label for="enablePowerDial">Select to enable Power Dialing for your campaigns</label>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12">
					<div class="rdio rdio-primary mb15">
						<input type="radio" name="callOption" value="disableDialServices" id="disableDialServices" 
						@if($user->enablePowerDial != 'Yes' && $user->enableCall != 'Yes'){{'checked'}}@endif
						>
						<label for="disableDialServices">Select to return to default</label>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
					<button type="button" class="btn btn-xs btn-success" onclick="saveDialerSettings()">Save Settings</button>
				</div>
			</div>
		</div>
	</form>
</div>
</div>
</div>

<div id="changePasswordModal" class="modal bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo">
					<h4 class="modal-title">Change Password</h4>
				</div>
			</div>
			<form method="post" id="changePasswordForm">
				<div class="modal-body">
					<div class="row"><div class="col-sm-12"><div id="error6"></div></div></div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-3 control-label">New Password</label>
							<div class="col-sm-6">
								<input type="password" placeholder="New Password" value="" name="newPassword" class="form-control" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Confirm Password</label>
							<div class="col-sm-6">
								<input type="password" placeholder="Confirm Password" value="" name="confirmPassword" class="form-control" />
							</div>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-sm-6 col-sm-offset-3">
							<button type="button" class="btn btn-success" onclick="changePassword()">Change Password</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="addBccModal" class="modal bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo">
					<h4 class="modal-title">Add BCC</h4>
				</div>
			</div>
			<form method="post" id="addBccForm">
				<div class="modal-body">
					<div class="row"><div class="col-sm-12"><div id="error7"></div></div></div>
					<div class="row">
						<div class="form-group">
							<label class="col-sm-3 control-label">Bcc Email</label>
							<div class="col-sm-6">
								<input type="text" placeholder="BCC Email" value="{{ $user->bccEmail }}" name="bccEmail" id="bccEmail" class="form-control" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row">
						<div class="col-sm-12 pull-right">
							<button type="submit" class="btn btn-success">Save</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/card-master/lib/js/card.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}
{!! HTML::script('assets/js/jquery.typewatch.js') !!}

<script type="text/javascript">
$(function() {
    jQuery(".select2").select2({width: '100%'});
	$('[data-toggle="tooltip"]').tooltip();

	if (window.location.hash == "#update") { addNewTeamMember();}

	$('#enableCallTimer').change(function(e) {
		var enableTimer = 'No';

		if(this.checked) { enableTimer = 'Yes';}
		else { enableTimer = 'No'; }

		$.ajax({
			type: 'post',
			url: '{{ URL::route('user.enableTimer') }}',
			cache: false,
			data: {'enableTimer' : enableTimer},
			dataType: 'json',
			beforeSend: function() {
				blockUI('#enableCallTimer');
			},
			success: function(data) {
				unblockUI('#enableCallTimer');
				showSuccess('Timer settings has been updated');
			},
			error: function(xhr, textStatus, thrownError) {
				showError("Error saving profile info. Please try again!")
			}
		});
	});

	$('#addBccForm').submit(function(e) {
		hideErrors();
		e.preventDefault();
		var bccEmail = $(this).find('#bccEmail').val();

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.profile.bcc') }}",
			dataType: "JSON",
			data: {"bccEmail" : bccEmail},
			beforeSend: function() {
				blockUI('#addBccModal');
			},
			success: function (response) {
				unblockUI('#addBccModal');
				if(response.status == 'success') {
					showSuccess('BCC email updated');
					$('#addBccModal').modal('hide');
				}
				else if(response.status == 'fail') {
					showFieldError(response.error.bccEmail, 'bccEmail');
				}
			},
			error: function (xhr, textStatus, thrownError) {
				unblockUI('#addBccModal');
				showError('There is some error. Reload the page and try again!');
				$('#addBccModal').modal('hide');
			}
		});
	});

    $('#editProfileForm').submit(function(e) {
        e.preventDefault();
        var statisticUpdates = $('#statisticUpdates').val();
        var serialized = $('#editProfileForm').serialize();

        if(statisticUpdates != undefined) {
            serialized += "&statisticUpdates="+statisticUpdates;
        }

        $.ajax({
            type: 'post',
            url: $('#editProfileForm').attr('action'),
            cache: false,
            data: serialized,
            beforeSend: function() {
                $('#error').html('<div class="alert alert-info">Submitting..</div>');
            },
            success: function(data) {
                var obj = jQuery.parseJSON(data);

                if(obj.success === false) {  $('#error').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');}
                else { location.reload(); }
            },
            error: function(xhr, textStatus, thrownError) {
                showError("Error saving profile info. Please try again!")
            }
        });
        return false;
    });
});

function changePasswordModal() {
    var $changePasswordModal = $('#changePasswordModal');
    $changePasswordModal.find('#error6').html('');
    $changePasswordModal.find('input').html('');
    $changePasswordModal.modal('show');
}

function changePassword() {
	var formData = $('#changePasswordForm').serialize();

	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.changepassword') }}",
		cache: false,
		dataType: "json",
		data: formData,
		beforeSend: function() {
			blockUI('#changePasswordModal');
		},
		success: function(response) {
			var obj = response;
			unblockUI('#changePasswordModal');
			if(obj.success == true) {
				$("#error6").html('<div class="alert alert-success">'+response.error+'</div>');
				location.reload();
			}
			else {
				$("#error6").html('<div class="alert alert-danger">'+response.error+'</div>');
			}
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI('#changePasswordModal');
			$("#error6").html('<div class="alert alert-danger">Problem in change password. Please try again!</div>');
		}
	});
}

function changeEmailSetting() {
	$('#changeEmailSettingModel').modal('show');
}

var isEmailSettingValid = false;

function saveEmailSetting() {
	var fromEmail = $('#fromEmail').val();
	var replyToEmail = $('#replyToEmail').val();
	var host = $('#emailHost').val();
	var port = $('#emailPort').val();
	var username = $('#emailUsername').val();
	var password = $('#emailPassword').val();
	var security = $('#emailSecurity').val();
	
	var errorDivID = "#error4";
	$(errorDivID).html('');
	$('#saveEmailSettingButton').attr('disabled', 'disabled');

	if(fromEmail == "") {
		$(errorDivID).html('<div class="alert alert-danger">Please enter from email id</div>');
	}
	else if(replyToEmail == "") {
		$(errorDivID).html('<div class="alert alert-danger">Please enter reply to email id</div>');
	}
	else if(host == "") {
		$(errorDivID).html('<div class="alert alert-danger">Please enter host detail</div>');
	}
	else if(port == "") {
		$(errorDivID).html('<div class="alert alert-danger">Please enter port id</div>');
	}
	else if(username == "") {
		$(errorDivID).html('<div class="alert alert-danger">Please enter username</div>');
	}
	else if(password == "") {
		$(errorDivID).html('<div class="alert alert-danger">Please enter your password</div>');
	}
	else{
		$(errorDivID).html('<div class="alert alert-info">Verifying your email. Please wait ... </div>');

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.saveandcheckemailsetting') }}",
			cache: false,
			dataType: "json",
			data: {"fromEmail" : fromEmail, 'replyToEmail':replyToEmail, 'host': host, 'port': port, 'username': username, 'password': password, 'security': security},
			success: function(response) {
				var obj = response;

				if(obj.success == "fail") {
					$(errorDivID).html('<div class="alert alert-danger">'+obj.error+'</div>');
				}
				else if(obj.success == "success") {
					$(errorDivID).html('<div class="alert alert-success">Email setting successfully saved.</div>');
					location.reload();
				}

				$('#saveEmailSettingButton').removeAttr('disabled');
			},
			error: function(xhr, textStatus, thrownError) {
				$(errorDivID).html('<div class="alert alert-danger">Problem in updating Settings. Please try again!</div>');
				$('#saveEmailSettingButton').removeAttr('disabled');
			}
		});
	}
}

$('#useDefaultEmailSetting').click(function() {
	$("#error4").html('');

	if ($(this).is(":checked")) {
		var defaultEmailSetting = 'Yes';
		$('.editEmailDiv').attr('disabled', true);
		$(this).attr('disabled', true);

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.defaultemailsettingtoyes') }}",
			cache: false,
			dataType: "json",
			data: {"defaultSetting" : defaultEmailSetting},
			success: function(response) {
				var obj = response;
				if(obj.success == "success") {
					location.reload();
				}
			},
			error: function(xhr, textStatus, thrownError) {
				$("#error4").html('<div class="alert alert-danger">Problem in updating Setting. Please try again!</div>');
			}
		});
	}
	else {
		var defaultEmailSetting = 'No';
		$('.editEmailDiv').attr('disabled', false);
	}
});

function changeDialerSetting() {
	document.getElementById("dialerSettingForm").reset();
	$('#changeDialerSettingModel').modal('show');
}

function saveDialerSettings() {
	var formData = $('#changeDialerSettingModel').find('form').serialize();

	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.dialerSetting') }}",
		cache: false,
		dataType: "json",
		data: formData,
		beforeSend: function() {
			blockUI('#changeDialerSettingModel');
		},
		success: function(response) {
			var obj = response;
			if(obj.success == "success") {
				location.reload();
			}
		},
		error: function(xhr, textStatus, thrownError) {
			$("#error5").html('<div class="alert alert-danger">Problem in updating Setting. Please try again!</div>');
		}
	});
}

function blockAccount() {
	bootbox.confirm("Are you sure to close account?", function(result) {
		if(result) {
			$.ajax({
				type: 'post',
				url: "{{ URL::route('user.account.close') }}",
				dataType: "JSON",
				beforeSend : function() {
					blockUI('.contentpanel');
				},
				success: function (response) {
					unblockUI('.contentpanel');
					if(response.status == 'success') {
						if(response.action == 'redirect') {
							window.location.href = response.url;
						}
					}
					else {
						showError("There is some error. Try Again!");
					}
				},
				error: function (xhr, textStatus, thrownError) {
					unblockUI('.contentpanel');
					showError("There is some error. Try Again!");
				}
			});
		}
	});
}

function bccEmailModal() {
	hideErrors();
	$('#addBccModal').modal('show');
}
</script>
@stop
