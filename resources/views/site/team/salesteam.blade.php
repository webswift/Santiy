@extends('layouts.dashboard')

@section('css')
	{!! Html::style('assets/css/jquery.datatables.css') !!}
@stop

@section('title')
	Sales Team
@stop

@section('content')
<div class="pageheader"><h2><i class="glyphicon glyphicon-edit"></i> Sales Team</h2></div>

<div class="contentpanel">
	<div class="panel">
		<div class="panel-heading">
			<h4 class="panel-title">Manage your sales members</h4>
			<p>Sales members are added to this system for the use of booking appointments only. They can not login to this system. When you book an appointment for a member of the sales team we will email them with the details of the lead and add them to your calendar.</p>
		</div><!-- panel-heading -->
		<div class="panel-footer">
			<div class="row">
				<div class="col-sm-12">
					<a onclick="addNewUser()" class="btn btn-xs btn-success pull-right">Add a new user</a>
				</div>
			</div>
		</div><!-- panel-footer -->
	</div><!-- panel -->

	<div class="panel">
		<div class="panel-body">
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

			<div class="table-responsive" id="newInBoundData">
				<table id="table1" class="table table-info mb30">
					<thead>
						<tr>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email Address</th>
							<th>Skype ID</th>
							<th>Contact Number</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						@if(sizeof($salesMembers) > 0)
						@foreach($salesMembers as $salesMember)
							<tr>
								<td>{{ $salesMember->firstName }}</td>
	                    		<td>{{ $salesMember->lastName }}</td>
	                    		<td>{{ $salesMember->email }}</td>
	                    		<td>{{ $salesMember->skypeID }}</td>
	                    		<td>{{ $salesMember->contactNumber }}</td>
	                    		<td>
	                    			<div class="btn-group mr5">
						                <button type="button" class="btn btn-xs btn-primary">Action</button>
						                <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">
						                  <span class="caret"></span>
						                  <span class="sr-only">Toggle Dropdown</span>
						                </button>
						                <ul class="dropdown-menu" role="menu">
						                  <li><a href="javascript:;" onclick="editSalesman('{{ $salesMember->id }}')">Edit</a></li>
						                  <li><a href="javascript:;" onclick="deleteSalesman('{{ $salesMember->id }}')">Delete Salesman</a></li>
						                </ul>
						            </div><!-- btn-group -->
	                    		</td>
							</tr>
						@endforeach
						@else
						<tr><td colspan="6" class="text-center"><i>No salesman found...</i></td></tr>
						@endif
					</tbody>
				</table>
			</div><!-- table-responsive -->

			<div class="col-sm-12">
				<div class="pull-right">{!! str_replace('/?', '?', $salesMembers->render()) !!}</div>
			</div>
		</div>
	</div><!-- panel -->
</div><!-- contentpanel -->
@stop

@section('bootstrapModel')
<!-- Salesman model -->
<div id="salesmanModel" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo">
					<h4 class="modal-title">Add new Sales Member</h4>
					<p>By adding a sales member our system will message them the time and date they are booked in for an appointment along with the prospect information.</p>
				</div>
			</div>
	        <div class="modal-body">
	        	<div class="row">
	        		<div class="col-sm-12">
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
		                  		<label class="col-sm-3 control-label">Telephone:</label>
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
	        			<div class="col-sm-6">
							<div class="form-group form-horizontal" style="padding-left:10px">
								<label class="col-sm-3 control-label"></label>
								<div class="col-sm-3 rdio rdio-primary">
									<input type="radio" id="male" value="Male" name="gender" checked />
									<label for="male">Male</label>
								</div>
								<div class="col-sm-3 rdio rdio-primary">
									<input type="radio" value="Female" id="female" name="gender">
									<label for="female">Female</label>
								</div>
							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group form-horizontal">
		                  		<label class="col-sm-1 control-label"></label>
								<div class="col-sm-9">
									<select multiple class="form-control" name="campaign" id="campaign" style="height:300px">
										@if(sizeof($campaigns) > 0)
											@foreach($campaigns as $campaign)
												<option value="{{$campaign->id}}">{{$campaign->name}}</option>
											@endforeach
										@endif
									</select>
								</div>
								<div class="col-sm-2">
									<div class="row form-group">
										<button style="margin-left:15px;margin-top:10px; " id="btnSelectAll" class="btn btn-primary btn-xs" title="Select All Campaigns">Select All</button>
									</div>
									<div class="row form-group">
										<button style="margin-left:15px;" id="btnUnselectAll" class="btn btn-primary btn-xs" title="Unselect All Campaigns">Unselect All</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br>
					<div class="panel-footer">
						<div class="row">
							<div class="col-sm-7 col-sm-offset-5">
								<button id="addOrEdit" class="btn btn-success">Add Entry</button>
							</div>
						</div>
					</div><!-- panel-footer -->
				</div>
				</div>
			</div>
		</div>
	</div>
</div><!-- Salesman model -->
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}

<script type="text/javascript">

$(function() {
	$('#btnSelectAll').click(function() {
		$('#campaign option').prop('selected','selected');
	});

	$('#btnUnselectAll').click(function() {
		$('#campaign option').prop('selected','');
	});
});

function addNewUser() {
	$('#firstName').val('');
	$('#lastName').val('');
	$('#email').val('');
	$('#telephone').val('');
	$('#skypeID').val('');
	$('#campaign').val('All');
	$('#error').html('');

	$('#headerInfo').html('<h4 class="modal-title">Add new Sales Member</h4><p>By adding a sales member our system will message them the time and date they are booked in for an appointment along with the prospect information.</p>');
	$('#salesmanModel').modal('show');
	$('#addOrEdit').html('Add Entry');
	$('#addOrEdit').attr('onclick', "add()");
}

function editSalesman(salesmanID) {
	blockUI('#table1');

	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.teams.editsalesmaninfo') }}",
		cache: false,
		data: {"salesmanID": salesmanID},
		success: function(response) {

			unblockUI('#table1');

			var obj = jQuery.parseJSON(response);

			if(obj.success == "success") {
				$('#firstName').val(obj.salesmanDetails.firstName);
				$('#lastName').val(obj.salesmanDetails.lastName);
				$('#email').val(obj.salesmanDetails.email);
				$('#telephone').val(obj.salesmanDetails.contactNumber);
				$('#skypeID').val(obj.salesmanDetails.skypeID);
				
				$('#campaign').val('');

				//if(obj.campaigns != null){
					$('#campaign').val(obj.campaigns);
				//}

				if(obj.salesmanDetails.gender == 'Male') {
					$("#male").prop("checked", true);
				}
				else if(obj.salesmanDetails.gender == 'Female') {
					$("#female").prop("checked", true);
				}

				$('#error').html('');
				$('#headerInfo').html('<h4 class="modal-title">Edit Sales Member</h4>');
				$('#salesmanModel').modal('show');
				$('#addOrEdit').html('Edit Salesman');
				$('#addOrEdit').attr('onclick', "edit("+salesmanID+")");
			}
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI('#table1');
			showError("Error adding sales member. Please try again.");
		}
	});
}

function add() {
	var firstName = $('#firstName').val();
	var lastName = $('#lastName').val();
	var email = $('#email').val();
	var telephone = $('#telephone').val();
	var skypeID = $('#skypeID').val();
	var gender = $("input[name=gender]:checked").val();
	var campaign = $('#campaign').val();

	if(firstName == '') {
		$('#error').html('<div class="alert alert-danger">Please enter First Name</div>');
	}
	else if(lastName == '') {
		$('#error').html('<div class="alert alert-danger">Please enter Last Name</div>');
	}
	else if(email == '') {
		$('#error').html('<div class="alert alert-danger">Please enter Email Address</div>');
	}
	else if(gender == '') {
		$('#error').html('<div class="alert alert-danger">Please select Gender</div>');
	}
	else if(campaign == ''){
		$('#error').html('<div class="alert alert-danger">Please select campaign</div>');
	}
	else{
		blockUI('#salesmanModel .modal-body');
		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.teams.addnewsalesman') }}",
			cache: false,
			data: {"firstName": firstName, "lastName": lastName, "email": email, "telephone": telephone, "skypeID": skypeID, "gender": gender, "campaign" : campaign},
			success: function(response) {
				unblockUI('#salesmanModel .modal-body');
				var obj = jQuery.parseJSON(response);

				if(obj.success == "success") {
					location.reload();
				}
				else if(obj.emailFound){
					$('#error').html('<div class="alert alert-danger">Sales Member with this Email ID is already registered</div>');
				}
			},
			error: function(xhr, textStatus, thrownError) {
				unblockUI('#salesmanModel .modal-body');
				showError("Error adding sales member. Please try again.");
			}
		});
	}
}

function edit(salesmanID) {
	var firstName = $('#firstName').val();
	var lastName = $('#lastName').val();
	var email = $('#email').val();
	var telephone = $('#telephone').val();
	var skypeID = $('#skypeID').val();
	var gender = $("input[name=gender]:checked").val();
	var campaign = $('#campaign').val();

	if(firstName == '') {
		$('#error').html('<div class="alert alert-danger">Please enter First Name</div>');
	}
	else if(lastName == '') {
		$('#error').html('<div class="alert alert-danger">Please enter Last Name</div>');
	}
	else if(email == '') {
		$('#error').html('<div class="alert alert-danger">Please enter Email Address</div>');
	}
	else if(gender == '') {
		$('#error').html('<div class="alert alert-danger">Please select Gender</div>');
	}
	else if(campaign == '') {
		$('#error').html('<div class="alert alert-danger">Please select Campaign</div>');
	}
	else {
		blockUI('#salesmanModel .modal-body');

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.teams.updatesalesmaninfo') }}",
			cache: false,
			data: {"salesmanID": salesmanID, "firstName": firstName, "lastName": lastName, "email": email, "telephone": telephone, "skypeID": skypeID, "gender": gender, campaign : campaign},
			success: function(response) {
				unblockUI('#salesmanModel .modal-body');
				var obj = jQuery.parseJSON(response);
				if(obj.success == "success") {
					location.reload();
				}
			},
			error: function(xhr, textStatus, thrownError) {
				unblockUI('#salesmanModel .modal-body');
				showError("Error updating sales member details. Please try again!");
			}
		});
	}
}

function deleteSalesman(salesmanID) {
	blockUI('.contentpanel');

	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.teams.deletesalesman') }}",
		cache: false,
		data: {"salesmanID": salesmanID},
		success: function(response) {

			unblockUI('.contentpanel');
			var obj = jQuery.parseJSON(response);

			if(obj.success == "success") {
				location.reload();
			}
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI('#salesmanModel .modal-body');
			showError("Error deleting sales member. Please try again!")
		}
	});
}
</script>
@stop
