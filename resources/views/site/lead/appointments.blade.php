@extends('layouts.dashboard')

@section('css')
{!! Html::style('assets/css/fullcalendar.css') !!}
{!! HTML::style('assets/css/bootstrap-timepicker.min.css') !!}
<style>
.fc-event-title{
	cursor: pointer;
	padding: 0 1px;
	padding-left: 5px;
}
</style>
@stop

@section('title')
	Appointment
@stop

@section('content')
<div class="pageheader">
	<h2><i class="fa fa-calendar"></i> Appointments</h2>
</div>
<div class="contentpanel">
	<p class="mb20">Select a member of your sales team to reveal what dates and times they are booked in for appointments. Click on their names on the calendar to edit their appointment.</p>
		<div class="row">

		@if (isset($error))
		<div class="no-data-placeholder">{!! $error !!}</div>
		@else
        <div class="col-md-3">
          <div class="panel panel-default panel-dark panel-alt">
            <div class="panel-heading">
              <h4 class="panel-title">SALES TEAM</h4>
            </div>
            <div class="panel-body">
              <div id='external-events'>
              	@if(sizeof($salesMemberLists) > 0)
              	@foreach($salesMemberLists as $salesMemberList)
                	<div class='external-event'>
                	<div class="ckbox ckbox-default" style="margin-top: 7px;">
                        <input class="salesmanBox" type="checkbox" value="{{ $salesMemberList->id }}" id="{{ $salesMemberList->id }}" checked="checked">
                        <label for="{{ $salesMemberList->id }}" style="color: white;">{{ trim($salesMemberList->firstName . ' ' . $salesMemberList->lastName) }}</label>
                      </div>
                    </div>
                @endforeach
                @endif
              </div>
            </div>
          </div>
        </div><!-- col-md-3 -->
        <div class="col-md-9">
          <div id="calendar"></div>
        </div><!-- col-md-9 -->
      </div>
@endif
    </div>

@stop

@if(!isset($error))
@section('bootstrapModel')
	<!-- Booking model -->
	<div id="bookedModel" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Booking Calendar</h4>
	        </div>
	        <div class="modal-body">
	        	<div id="error1" ></div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label>Salesman</label>
	                	<input type="hidden" id="leadID" value="" />
			            <select class="form-control mb15" id="salesmanID">
			            	@if(sizeof($salesMemberLists) > 0)
	                        @foreach($salesMemberLists as $salesMemberList)
	                        	<option value="{{ $salesMemberList->id }}" >{{ $salesMemberList->firstName . ' ' . $salesMemberList->lastName }}</option>
	                        @endforeach
	                        @endif
                        </select>
	                </div>
	            </div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label>Date</label>
			            <div class="input-group mb15">
			                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			                <input type="text" placeholder="Date" id="date1" class="form-control"  />
			            </div>
	                </div>
	            </div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label>Time</label>
	                	<div class="input-group mb15">
			                <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
			                <div class="bootstrap-timepicker"><input id="timepicker1" type="text" class="form-control" /></div>
		                </div>
	              	</div>
	            </div>
	            <div class="form-group">
	                <p>
                		<button id="cancelAppointment" onclick="" class="btn btn-danger btn-xs">Cancel appointment</button>
                		<a target="_blank" id="viewAppointmentInfo" class="btn btn-warning btn-xs">View Lead Information</a>
                		<button id="saveApppintment" onclick="" class="btn btn-success btn-xs">Save Changes</button>
		            </p>
	            </div>
	            

	        </div>
	    </div>
	  </div>
	</div><!-- Booking model -->	
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/fullcalendar.min.js') !!}

<script type="text/javascript">
jQuery(document).ready(function() {
	"use strict";

	jQuery('#external-events div.external-event').each(function() {
		var eventObject = {title: $.trim($(this).text())};

		jQuery(this).data('eventObject', eventObject);

		jQuery(this).draggable({
			zIndex: 999,
			revert: true,
			revertDuration: 0
		});
	});

	jQuery('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		events: {
			url: "{{ URL::route('user.leads.calanderappointmentdata') }}",
			type: 'POST',
			data: function() {
				var salesmanIDs = new Array();
				$('.salesmanBox:checked').each(function() {
					salesmanIDs.push($(this).val());
				});
				return {salesmanIDs: salesmanIDs};
			},
			error: function() {
				unblockUI('.contentpanel');
				showError('Please select a salesman');
			}
		},
		loading : function(bool){
			if(bool){
				blockUI('#calendar');
			}
			else{
				unblockUI('#calendar');
			}
		},
		eventClick: function(calEvent, jsEvent, view) {
			var appointmentID = calEvent.id;
			var newdate = calEvent.newdate;
			var time = calEvent.time;
			var salesmanID = calEvent.salesmanID;
			var leadID = calEvent.leadID;

			$('#salesmanID').val(salesmanID);
			$('#date1').val(newdate);
			$('#timepicker1').val(time);
			$('#leadID').val(leadID);

			var viewLeadUrl = '{{ URL::route('user.leads.viewlead', ['leadID' => '#id']) }}';
			viewLeadUrl = viewLeadUrl.replace('#id', leadID);

			$('#cancelAppointment').attr('onclick', "cancelAppointment("+appointmentID+")");
			$('#viewAppointmentInfo').attr('href', viewLeadUrl);
			$('#saveApppintment').attr('onclick', "saveAppointment("+appointmentID+")");

			$('#bookedModel').modal('show');
		}
	});
});
</script>
@stop
@endif


@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/bootstrap-timepicker.min.js') !!}
{!! HTML::script('assets/js/jquery.maskedinput.min.js') !!}

@if(!isset($error))
<script type="text/javascript">
	jQuery('#timepicker1').timepicker();
	jQuery("#date1").mask("99-99-9999");
	
	function cancelAppointment(appointmentID) {
		blockUI('#bookedModel');
		var leadID = $('#leadID').val();

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.leads.cancelappointment') }}",
			cache: false,
			data: {"leadID": leadID, "appointmentID": appointmentID}
		}).done(function( response ) {
			var obj = jQuery.parseJSON(response);
			if(obj.success == 'success') {
				location.reload();
			}

			unblockUI('#bookedModel');
		});
	}

	function saveAppointment(appointmentID) {
		var leadID = $('#leadID').val();
		var salesmanID = $('#salesmanID').val();
		var date1 = $('#date1').val();
		var timepicker1 = $('#timepicker1').val();

		if(salesmanID == '') {
			$('#error1').html('<div class="alert alert-danger">Please Select a Salesman</div>');
		}
		else if(date1 == '') {
			$('#error1').html('<div class="alert alert-danger">Please Enter Date</div>');
		}
		else if(timepicker1 == '') {
			$('#error1').html('<div class="alert alert-danger">Please Select Time</div>');
		}
		else {
			blockUI('#bookedModel');
			$.ajax({
				type: 'post',
				url: "{{ URL::route('user.leads.bookthissalesman') }}",
				cache: false,
				data: {"leadID": leadID, "salesmanID": salesmanID, "date1": date1, "timepicker1": timepicker1}
			}).done(function( response ) {
				unblockUI('#bookedModel');
				location.reload();
			});
		}
	}

	$('.salesmanBox:checked').click(function(){
		jQuery('#calendar').fullCalendar( 'refetchEvents' );
	});
</script>
	@endif
@stop
