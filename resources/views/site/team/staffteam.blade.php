@extends('layouts.dashboard')

@section('css')
	  {!! Html::style('assets/css/jquery.datatables.css') !!}
@stop

@section('title')
	Staff Team Management
@stop

@section('content')
<div class="pageheader">
	<div class="pull-left">
		<h2><i class="fa fa-user"></i> Staff Management </h2>
	</div>
	<div class="pull-right mt5">
		<span class="mr10">You currently have {{ $remainingMembers }} team users available.</span>

		@if($memberCounts < $volumeLimit)
			<a href="javascript:;" onclick="addNewUser()" class="btn btn-xs btn-success pull-right">Add a new user</a></h2>
		@else
			<a href="{{ URL::route('user.profile') }}" class="btn btn-xs btn-success pull-right">Upgrade to add more team members</a></h2>
		@endif
	</div>
	<div class="clearfix"></div>
</div>

<div class="contentpanel">
	<ul class="letter-list">
		<li><a @if($letter == 'all') style="background: #428bca; color: white;" @endif href="{{ URL::route('user.teams.staffteam') }}" >all</a></li>
		@foreach(range('A', 'Z') as $char)
        	<li><a @if($char == $letter) style="background: #428bca; color: white;" @endif href="{{ URL::route('user.teams.staffteam') . '/' . $char }}" >{{ $char }}</a></li>
        @endforeach
	</ul>

	<div class="mb30"></div>
		@if($successMessage != '')
			<div class="row">
				<div class="col-sm-12">
					<div class="alert alert-{{ $successMessageClass or 'success'}}">
						<a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
						{!! $successMessage !!}
					</div>
				</div>
			</div>
	    @endif

      <div class="mb30"></div>
      
      <div class="people-list">
		<div class="row">
		@if(sizeof($teamMembers) > 0)
		@foreach($teamMembers as $teamMember)
			<div class="col-md-6">
				<div class="people-item">
					<div>
						<div class="media-body" style="overflow:visible;">
							<h4 class="person-name">{{ $teamMember->firstName . ' ' . $teamMember->lastName }}</h4>
							<div class="text-muted"><i class="fa fa-map-marker"></i> {{ $teamMember->country }}</div>
							<div class="text-muted"><i class="fa fa-briefcase"></i><a href="javascript:void(0);">{{ $teamMember->companyName }}</a></div>
							<p>Telephone number : {{ $teamMember->contactNumber }}</p>
							<div class="btn-group mr5">
								<button type="button" class="btn btn-primary">Action</button>
								<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
									<span class="caret"></span>
									<span class="sr-only">Toggle Dropdown</span>
								</button>
								<ul class="dropdown-menu" role="menu">
									<li><a href="javascript:void(0);" onclick="editStaffMember('{{ $teamMember->id }}')">Edit</a></li>
									<li><a href="javascript:void(0);" onclick="deleteStaffMember('{{ $teamMember->id }}')">Delete User</a></li>
								</ul>
							</div><!-- btn-group -->
						</div>
					</div>
				</div>
			</div><!-- col-md-6 -->
		@endforeach
		@endif
		</div><!-- row -->
	</div><!-- people-list -->
</div><!-- contentpanel -->
@stop


@section('bootstrapModel')
	<!-- Salesman model -->
	<div id="staffMemberModel" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog modal-lg">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <div id="headerInfo">
		            <h4 class="modal-title">Add a Staff Member to your Team.</h4>
		            <p>Your member of staff will need to login using their email address. Don't worry we'll send them a link to activate their account once your've created it.</p>
	        	</div>
	        </div>
	        <div class="modal-body">
	        <div class="form-horizontal">
	        	<div id="error" ></div>
	        	<div class="row">
	        		<div class="col-sm-6">
	        			<div class="form-group form-horizontal">
		                  <label class="col-sm-3 control-label">First Name*:</label>
		                  <div class="col-sm-9">
		                    <input id="firstName" type="text" class="form-control" />
		                  </div>
		                </div>
	        		</div>
	        		<div class="col-sm-6">
	        			<div class="form-group">
		                  <label class="col-sm-3 control-label">Last Name*:</label>
		                  <div class="col-sm-9">
		                    <input id="lastName" type="text" class="form-control" />
		                  </div>
		                </div>
	        		</div>
	        	</div>
	        	<br>
	        	<div class="row">
	        		<div class="col-sm-6">
	        			<div class="form-group form-horizontal">
		                  <label class="col-sm-3 control-label">Email Address*:</label>
		                  <div class="col-sm-9">
		                    <input id="email" type="text" class="form-control" />
		                  </div>
		                </div>
	        		</div>
	        		<div class="col-sm-6">
	        			<div class="form-group">
		                  <label class="col-sm-3 control-label">Contact Number:</label>
		                  <div class="col-sm-9">
		                    <input id="telephone" type="text" class="form-control" />
		                  </div>
		                </div>
	        		</div>
	        	</div>
	        	<br>
	        	<div class="row">
	        		<div class="col-sm-6">
	        			<div class="form-group form-horizontal">
		                  <label class="col-sm-3 control-label">SkypeID:</label>
		                  <div class="col-sm-9">
		                    <input id="skypeID" type="text" name="name" class="form-control" />
		                  </div>
		                </div>
	        		</div>
	        		<div class="col-sm-6" id="passwordDiv">
	        			<div class="form-group">
		                  <label class="col-sm-3 control-label">Password:</label>
		                  <div class="col-sm-9">
		                  <input type="hidden" id="fakerPassword" value="{{ $fackerPassword }}" >
		                    <input id="password" type="text" class="form-control" value="" />
		                  </div>
		                </div>
	        		</div>
	        	</div>
	        	<br>
	        	<div class="row" id="messageDiv" style="display:none">
	        		<div class="col-sm-12">
	        			<div class="form-group form-horizontal">
		                  <label class="col-sm-2 control-label">Welcome message sent with password:</label>
		                  <div class="col-sm-10">
		                    <textarea class="form-control" name="message" id="message" rows="5" placeholder="Message"></textarea>
		                  </div>
		                </div>
	        		</div>
	        	</div>
	        	<br>
	        	<div class="panel-footer">
					 <div class="row">
						<div class="col-sm-7 col-sm-offset-5">
						  <button id="addOrEdit" class="btn btn-xs btn-success">Add Entry</button>
						</div>
					 </div>
			    </div><!-- panel-footer -->

	        </div>   

	        </div>
	    </div>
	  </div>
	</div><!-- Salesman model -->

	<!-- Volume Limit Exceed model -->
	<div id="volumeLimitModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog modal-sm">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <div id="headerInfo">
		            <h4 class="modal-title">Maximum Team Limit Reached!</h4>
	        	</div>
	        </div>
	        <div class="modal-body">
	        	<p>Your account currently supports a maximum <span id="maxVolumeLimit"></span> users. Upgrade it now with a one off payment. The upgrade fee won't be added to your license renewal after your upgrade.</p>
	        </div>
	        <div class="panel-footer">
				 <div class="row">
					<p>
					  <a href="{{ URL::route('user.profile') }}#update" class="btn btn-xs btn-success">Click here to upgrade.</a>
					  <button data-dismiss="modal" class="btn btn-xs btn-default">Cancel</button>
					</p>
				 </div>
		    </div><!-- panel-footer -->
	    </div>
	  </div>
	</div><!-- Salesman model -->
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#table1').dataTable({
		"sPaginationType": "full_numbers"
	});
});
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
<script type="text/javascript">

function addNewUser() {

	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.teams.checkvolumelimit') }}",
		cache: false,
		success: function(response) {
			var obj = jQuery.parseJSON(response);

			if(obj.success == "success") {
				var fackerPassword = $('#fakerPassword').val();
				$('#password').val(fackerPassword);
				$('#messageDiv').show();
				$('#firstName').val('');
				$('#lastName').val('');
				$('#email').val('');
				$('#telephone').val('');
				$('#skypeID').val('');
				$('#error').html('');
				$('#headerInfo').html('<h4 class="modal-title">Add a Staff Member to your Team.</h4><p>Your member of staff needs to login with their email address don\'t worry we\'ll send them a link to activate their account once your\'ve st it up.</p>');
				$('#staffMemberModel').modal('show');
				$('#addOrEdit').html('Add Entry');
				$('#addOrEdit').attr('onclick', "add()");
			}
			else if(obj.success == "fail"){
				$('#maxVolumeLimit').html(obj.volumeLimit);
				$('#volumeLimitModel').modal('show');
			}
		},
		error: function(xhr, textStatus, thrownError) {
			alert('Something went wrong. Please Try again later!');
		}
	});
}

function editStaffMember(staffMemberID) {
	blockUI('.people-list');
	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.teams.editstaffmemberinfo') }}",
		cache: false,
		data: {"staffMemberID": staffMemberID},
		success: function(response) {
			var obj = jQuery.parseJSON(response);

			unblockUI('.people-list');

			if(obj.success == "success") {
				$('#firstName').val(obj.staffMemberDetails.firstName);
				$('#lastName').val(obj.staffMemberDetails.lastName);
				$('#email').val(obj.staffMemberDetails.email);
				$('#telephone').val(obj.staffMemberDetails.contactNumber);
				$('#skypeID').val(obj.staffMemberDetails.skypeID);
				$('#password').val('');
				$('#error').html('');
				$('#headerInfo').html('<h4 class="modal-title">EDIT STAFF MEMBER</h4>');
				$('#messageDiv').hide();
				$('#staffMemberModel').modal('show');
				$('#addOrEdit').html('Edit Staff Member');
				$('#addOrEdit').attr('onclick', "edit("+staffMemberID+")");
			}
		},
		error: function(xhr, textStatus, thrownError) {
			blockUI('.people-list');
			alert('Something went wrong. Please Try again later!');
		}
	});
}

function add() {
	var firstName = $('#firstName').val();
	var lastName = $('#lastName').val();
	var email = $('#email').val();
	var telephone = $('#telephone').val();
	var skypeID = $('#skypeID').val();
	var message = $('#message').val();
	var password = $('#password').val();

	if(firstName == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter First Name.</div>');
	}
	else if(lastName == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter Last Name.</div>');
	}
	else if(email == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter Email Address.</div>');
	}
	else if(password == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter Password.</div>');
	}
	else{
		blockUI('#staffMemberModel .modal-body');

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.teams.addnewstaffmember') }}",
			cache: false,
			data: {"firstName": firstName, "lastName": lastName, "email": email, "telephone": telephone, "skypeID": skypeID, "password": password, "message": message},
			success: function(response) {
				var obj = jQuery.parseJSON(response);

				unblockUI('#staffMemberModel .modal-body');

				if(obj.success == "success") {
					location.reload();
				}
				else if(obj.emailFound) {
					$('#error').html('<div class="alert alert-danger">User with this email is already added</div>');
				}
			},
			error: function(xhr, textStatus, thrownError) {
				blockUI('#staffMemberModel .modal-body');
				alert('Something went wrong. Please Try again later!');
			}
		});
	}
}

function edit(staffMemberID) {
	var firstName = $('#firstName').val();
	var lastName = $('#lastName').val();
	var email = $('#email').val();
	var telephone = $('#telephone').val();
	var skypeID = $('#skypeID').val();
	var gender = $("input[name=gender]:checked").val();
	var password = $('#password').val();

	if(firstName == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter First Name.</div>');
	}
	else if(lastName == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter Last Name.</div>');
	}
	else if(email == '') {
		$('#error').html('<div class="alert alert-danger">Please Enter Email Address.</div>');
	}
	else if(password.length > 0 && password.length < 6){
		$('#error').html('<div class="alert alert-danger">Please Enter Password more than 6 characters.</div>');
	}
	else{
		blockUI('#staffMemberModel .modal-body');
		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.teams.updatestaffmemberinfo') }}",
			cache: false,
			data: {"staffMemberID": staffMemberID, "firstName": firstName, "lastName": lastName, "email": email, "telephone": telephone, "skypeID": skypeID, "password": password},
			success: function(response) {
				var obj = jQuery.parseJSON(response);

				unblockUI('#staffMemberModel .modal-body');

				if(obj.success == "success") {
					location.reload();
				}
				else {
					showError(obj.message);
				}
			},
			error: function(xhr, textStatus, thrownError) {
				unblockUI('#staffMemberModel .modal-body');
				showError('Something went wrong. Please Try again later!');
			}
		});
	}
}

function deleteStaffMember(staffMemberID) {
	blockUI('.people-list');
	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.teams.deletestaffmember') }}",
		cache: false,
		data: {"staffMemberID": staffMemberID},
		success: function(response) {
			var obj = jQuery.parseJSON(response);
			unblockUI('.people-list');

			if(obj.success == "success") {
				location.reload();
			}
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI('.people-list');
			alert('Something went wrong. Please Try again later!');
		}
	});
}
</script>
@stop