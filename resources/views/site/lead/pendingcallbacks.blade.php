@extends('layouts.dashboard')

@section('css')
	{!! Html::style('assets/css/jquery.datatables.css') !!}
@stop

@section('title')
	View Pending CallBacks
@stop

@section('content')
<div class="pageheader">
	<h2><i class="glyphicon glyphicon-edit"></i> View Pending Call Backs</h2>
</div>
<div class="contentpanel">
	<div class="panel">
		<div class="panel-heading">
			<h3 class="panel-title">Select Fields</h3>
			<p>Here you can view all the follow up calls your staff have booked.</p>
		</div>
		<div class="panel-body">
			<form class="form-horizontal form-bordered">
				<div class="form-group">
					<div class="col-sm-3">
						<label for="campaignName" class="control-label">View Pending Follow up calls for:</label>
					</div>
					@if($campaignLists->count() > 0)
						<div class="col-sm-3">
							<select class="form-control mb15" id="campaignName">
								<option value="All">All Campaigns</option>
                                @foreach($campaignLists as $campaignList)
                                    <option value="{{ $campaignList->id }}">{{ $campaignList->name }}</option>
                                @endforeach
                            </select>
						</div>
						<div class="col-sm-3">
							<select class="form-control mb15" id="pendingdays">
                                <option value="-0" selected>All</option>
                                <option value="+0">Today</option>
                                <option value="+1">Tomorrow</option>
                                <option value="+7">Within 7 Days</option>
                                <option value="+30">Within One Month</option>
                                <option value="+10000">Overdue</option>
                            </select>
						</div>
						<div class="col-sm-3">
							<select class="form-control mb15" id="leadType">
								<option value="All" selected>All</option>
								<option value="Me">My Calls Only</option>
							</select>
						</div>
					@else
						<div class="col-sm-4">
							<p style="margin-top: 10px"><i>No campaigns found of which you are a member</i></p>
						</div>
					@endif
				</div>
			</form>
		</div>
	</div>

	<div class="panel">
		<div class="panel-body">
			<div class="form-horizontal">
			<div class="form-group">
				<div class="col-sm-4">
					<select class="form-control mb15" id="action" name="action" title="Bulk Actions">
						<option value="">Bulk Actions</option>
						<option value="cancel">Cancel</option>
						<option value="already_contacted">Already contacted</option>
					</select>
				</div>
			</div>
			</div>
		</div>
	</div><!-- panel -->

	<div class="panel">
		<div class="panel-body">
			<div id="error" ></div>
			<div class="table-responsive" id="newPendingData">
				<table id="tableDiv" class="table table-warning mb30">
					<thead>
					<tr>
						<th>Company Name</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Campaign Name</th>
						<th>Call Back On</th>
						<th>Agent</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@stop

@section('bootstrapModel')
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}

<script type="text/javascript">
jQuery(document).ready(function() {
	//preselect
	$('#campaignName').val('All');
	$('#pendingdays').val('+7');
	$('#leadType').val('Me');

	@if(isset($pendingCallbacksFilter))
		$('#campaignName').val('{{$pendingCallbacksFilter['campaignID'] }}');
		$('#pendingdays').val('{{$pendingCallbacksFilter['pendingDays'] }}');
		$('#leadType').val('{{$pendingCallbacksFilter['leadType'] }}');
	@endif

	var campaignID = $('#campaignName').val();
	var pendingDays = $('#pendingdays').val();
	var leadType = $('#leadType').val();

	if (campaignID != undefined && pendingDays != undefined && leadType != undefined) {
		var url = "{{ URL::route('user.leads.ajaxpendingdaysdata') }}";
		showPendingData(campaignID, pendingDays, leadType, url);
	}
	
	$('#action').change(function() {
		var val = $(this).val();
		$(this).val("");
		
		var callbacks = [];
		$('#tableDiv').find('.checkBox:checked').each(function(){
			var id = $(this).attr('id');
			callbacks.push(id.replace('check_', ''));
		});


		if(val != '') {
			if(callbacks.length <= 0) {
				showError("Please select at least one follow up call");
				return false;
			}
			bootbox.confirm("Selected follow up call will be removed . Click OK to proceed", function(result) {
				if(result) {
					$.ajax({
						type: 'post',
						url: "{{ URL::route('user.leads.followupcallsbulkaction') }}",
						cache: false,
						data: {"callbacks": callbacks, 'action' : val}
						, beforeSend: function() {
							blockUI('#newPendingData');
						}
					}).done(function (response) {
						unblockUI('#newPendingData');
						if(response.status == 'success'){
							for(var i=0; i<callbacks.length; i++){
								$('.cancelCallBack_'+callbacks[i]).fadeOut(500);
							}
							showSuccess('Follow up calls successfully proceed');
						} else {
							showError(response.message);
						}
					}).fail(function(xhr, textStatus, thrownError) {
						unblockUI('#newPendingData');
						showError("Error on bulk action over call backs. Please try again!");
				  	});
				}
			});
		}
	});

	$('#newPendingData').on('change', '#parentCheckBox', function() {
		var isCheck =  this.checked;
		if(isCheck) {
			$('#tableDiv .checkBox').prop('checked', true);
		}
		else {
			$('#tableDiv .checkBox').prop('checked', false);
		}
	});
});
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}

<script type="text/javascript">
$('#campaignName').change(function(){
	var campaignID = $(this).val();
	var pendingDays = $('#pendingdays').val();
	var leadType = $('#leadType').val();

	if (campaignID != undefined && pendingDays != undefined && leadType != undefined) {
		var url = "{{ URL::route('user.leads.ajaxpendingdaysdata') }}";
		showPendingData(campaignID, pendingDays, leadType, url);
	}
});

$('#pendingdays').change(function(){
	var campaignID = $('#campaignName').val();
	var pendingDays = $('#pendingdays').val();
	var leadType = $('#leadType').val();

	if (campaignID != undefined && pendingDays != undefined && leadType != undefined) {
		var url = "{{ URL::route('user.leads.ajaxpendingdaysdata') }}";
		showPendingData(campaignID, pendingDays, leadType, url);
	}
});

$('#leadType').change(function(){
	var campaignID = $('#campaignName').val();
	var pendingDays = $('#pendingdays').val();
	var leadType = $('#leadType').val();

	if (campaignID != undefined && pendingDays != undefined && leadType != undefined) {
		var url = "{{ URL::route('user.leads.ajaxpendingdaysdata') }}";
		showPendingData(campaignID, pendingDays, leadType, url);
	}
});

function showPendingData(campaignID, pendingDays, leadType, url) {
	$.ajax({
		type: 'post',
		url: url,
		cache: false,
		data: {"campaignID": campaignID, "pendingDays": pendingDays, "leadType" : leadType},
		beforeSend: function() {
			blockUI('#newPendingData');
		},
		success: function(data) {
			unblockUI('#newPendingData');
			$('#newPendingData').html(data);
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI('#newPendingData');
			showError("Error getting pending call backs. Please try again!");
		}
	});
}

function cancelCallBak(callBackID) {
	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.leads.cancelcallback') }}",
		cache: false,
		data: {"callBackID": callBackID},
		beforeSend: function() {
			blockUI('#newPendingData');
		}
		}).done(function( response ) {
		var obj = jQuery.parseJSON(response);
		unblockUI('#newPendingData');

		if(obj.success == 'success') {
			$('.cancelCallBack_'+callBackID).hide();
			showSuccess('Call Back successfully canceled');
		}
	});
}

function deleteLead(leadID){
	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.leads.deletelead') }}",
		cache: false,
		data: {"leadID": leadID},
		beforeSend: function() {
			blockUI('#newPendingData');
		}
		}).done( function( response ) {
		var obj = jQuery.parseJSON(response);
		unblockUI('#newPendingData');

		if(obj.success == 'success') {
			$('.deleteLead_'+leadID).hide();
			showSuccess('Lead successfully deleted');
		}
	});
}
</script>
@stop
