@extends('layouts.dashboard')

@section('title')
	Lead Information
@stop

@section('css')
	{!! Html::style('assets/css/bootstrap-timepicker.min.css') !!}
@stop

@section('content')
	<div class="pageheader">
		<h2><i class="fa fa-plus"></i> View Lead</h2>
    </div>

    <div class="contentpanel">
		<div class="panel">
			<div class="panel-body panel-body-nopadding">
				<div class="form-horizontal form-bordered">
					<div class="form-group">
						<div class="col-sm-6">
							<label class="col-sm-3 control-label" for="campaignName"><b>Campaign</b></label>
							<div class="col-sm-9" style="padding-top: 10px;">
								{!! $campaignName !!}
							</div>
						</div>
						<div class="col-sm-6">
							<label class="control-label mb15"><b>Lead Number</b>: {{ $currentLeadNumber }}/{{ $totalLeads }}</label>
						</div>
					</div>
                    @for($i=0; $i < count($leadFormDatas1); $i=$i+2)
						<div class="form-group">
		                    <div class="col-sm-6">
		                    	<label class="col-sm-3 control-label"><b>{{ $leadFormDatas1[$i]['fieldName'] }}</b></label>
				                <div class="col-sm-9" style="padding-top: 10px;">
				                	{{ $leadFormDatas1[$i]['value'] }}
				              	</div>
		                    </div>
		                    @if($i+1 < count($leadFormDatas1))
		                    <div class="col-sm-6">
		                    	<label class="col-sm-3 control-label"><b>{{ $leadFormDatas1[$i+1]['fieldName'] }}</b></label>
				                <div class="col-sm-9" style="padding-top: 10px;">
				                	{{ $leadFormDatas1[$i+1]['value'] }}
				              	</div>
		                    </div>
		                    @endif
	                    </div>
                    @endfor
                    <div class="form-group">
	                    <div class="col-sm-6">
	                    	<label class="col-sm-3 control-label"><b>Interested</b></label>
	                      	<div class="col-sm-9" style="padding-top: 10px;">
								@if($leadDetails->interested == 'NotSet')
									Not Set
								@elseif($leadDetails->interested == 'Interested')
									Positive
								@elseif($leadDetails->interested == 'NotInterested')
									Negative
								@elseif($leadDetails->interested == 'Unrechable')
									Unreachable
								@endif
	                       </div>
	                    </div>
                        @if($emailFieldExists)
                            <div class="col-sm-6">
                                <label class="col-sm-3 control-label"><b>Send Email</b></label>
                                <div class="col-sm-9" style="padding-top: 10px;">
                                    @foreach($emailTempletes as $emailTemplete)
                                        {{ $emailTemplete->name }}
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
		                <label class="col-sm-3 control-label"><b>Book Appointment</b></label>
		                <div class="col-sm-7" style="padding-top: 10px;" @if($leadDetails->bookAppointment == 'Yes') id="bookAppointment" @endif><a href="javascript:;" onclick="return false;">{{ $leadDetails->bookAppointment }}</a></div>
		            </div>
		            @if(sizeof($leadFormDatas2) > 0)
                    	@foreach($leadFormDatas2 as $leadFormData2)
		            		<div class="form-group">
		                		<label class="col-sm-3 control-label"><b>{{ $leadFormData2['fieldName'] }}</b></label>
								<div class="col-sm-7" style="padding-top: 10px;">
									@if(strtolower($leadFormData2['fieldName']) == 'notes')
										<textarea rel="{{ $leadFormData2['fieldID'] }}"  id="importLeadTXT" class="form-control preDefineformfields" rows="5" disabled>{{ $leadFormData2['value'] }}</textarea>
									@else
										{{ $leadFormData2['value'] }}
									@endif
		              			</div>
		            		</div>
                    	@endforeach
                    @endif

                    <div class="form-group">
	                    <div class="col-sm-6">
	                    	<label class="col-sm-3 control-label"></label>
	                      	<div class="col-sm-9" style="padding-top: 10px;">
	                      		<div class="ckbox ckbox-default">
									<input type="checkbox" id="followUpCall" @if($isCallBackUserExists) checked @endif disabled>
									<label for="followUpCall">Follow Up Call</label>
		                        	<span id="callBackViewDiv">
										@if($isCallBackUserExists)
											<a onclick="showfollowUpCallModel()">(View)</a>
										@else
											<a onclick="showfollowUpCallModel()" class="hide">(View)</a>
										@endif
		                       	 	</span>
		                     	</div>
	                       	</div>
	                    </div>
	                    <div class="col-sm-6">
	                    	<label class="col-sm-3 control-label"><b>Reference</b></label>
	                        <div class="col-sm-9" style="padding-top: 10px;">
		                        {{ $leadDetails->referenceNumber or '-' }}
	                        </div>
	                    </div>
                    </div>

                    {{--@if ($user->userType != "Multi")--}}
                    <div class="form-group">
                        <label class="col-sm-3 control-label"></label>
                        <div class="col-sm-7">
                            <p>
                                <button type="button" onclick="editlead()" class="btn btn-primary btn-sm">Edit Lead</button>
                            </p>
                        </div>
                    </div>
                    {{--@endif--}}
				</div>
			</div><!-- panel-body -->
        </div><!-- panel -->
	</div><!-- contentpanel -->
@stop


@section('bootstrapModel')
	<!-- Booking model -->
	<div id="bookedModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog modal-sm">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Booking Calendar</h4>
	        </div>
	        <div class="modal-body">
	        	<div id="error1" ></div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label><b>Salesman</b></label>
	                	<div class="input-group mb15">
	                		@if(sizeof($salesMemberLists) > 0)
	                        @foreach($salesMemberLists as $salesMemberList)
	                        	@if( $salesMemberList->id == $salesManDetail['salesManUserID'])
	                        	    {{ $salesMemberList->firstName . ' ' . $salesMemberList->lastName  }}
	                        	@endif
	                        @endforeach
	                        @endif
	                    </div>
	                </div>
	            </div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label><b>Date</b></label>
			            <div class="input-group mb15">
			                @if($isSalesManExists)
			                    {{$salesManDetail['date']}}
			                @endif
			            </div>
	                </div>
	            </div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label><b>Time</b></label>
	                	<div class="input-group mb15">
	                	@if($isSalesManExists)
	                	    {{$salesManDetail['time']}}
	                	@endif
	                	</div>
                    </div>
                </div>
            </div>
	        <div class="modal-footer">
	            <button onclick="$('#bookedModel').modal('hide');" class="btn btn-primary btn-xs">Ok</button>
	        </div>
	    </div>
	  </div>
	</div><!-- Booking model -->	

	<!-- Follow Up call model -->
	<div id="followUpCallModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog modal-sm">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button aria-hidden="true" id="followUpModalCancle" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Follow up call</h4>
	        </div>
	        <div class="modal-body">
	        	<div id="error2" ></div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label><b>Date</b></label>
			            <div class="input-group mb15">
			                @if($isCallBackUserExists)
			                    {{$callBackUserDetail['date']}}
			                @endif
			            </div>
	                </div>
	            </div>
	        	<div class="form-group">
	                <div class="col-sm-12">
	                	<label><b>Time</b></label>
	                	<div class="input-group mb15">
			                @if($isCallBackUserExists)
			                    {{$callBackUserDetail['time']}}
			                @endif
		                </div>
	              	</div>
	            </div>
	            <div class="form-group">
	                <div class="col-sm-12">
	                	<label class="text-center"><b>Team Member</b></label>
	                	<div class="input-group mb15">
	                		@if(sizeof($callBackUserLists) > 0)
	                        @foreach($callBackUserLists as $callBackUserList)
	                        	@if( $callBackUserList->id == $callBackUserDetail['callBackUserID'])
	                        	    {{ $callBackUserList->firstName . ' ' . $callBackUserList->lastName }}
	                        	@endif
	                        @endforeach
	                        @endif
	                    </div>
	                </div>
	            </div>
	        </div>
	        <div class="modal-footer">
	        <button onclick="$('#followUpCallModel').modal('hide');" class="btn btn-primary btn-xs">Ok</button>
	        </div>
	    </div>
	  </div>
	</div><!-- Follow Up call model -->
@endsection

@section('javascript')
    {!! HTML::script('assets/js/custom.js') !!}
	{!! HTML::script('assets/js/bootstrap-timepicker.min.js') !!}
	{!!  HTML::script('assets/js/jquery.maskedinput.min.js') !!}
	
	<script type="text/javascript">

	var previousBookingValue = "";
	var previousFollowUpValue = "";

		$('#bookAppointment').bind('focus',function(){
		
		    previousBookingValue =$(this).val();

		});

		$('#bookAppointment').click(function(){

			var bookedAppointment = $(this).text();

			if(bookedAppointment == 'Yes')
			{
				$('#bookedModel').modal('show');
			}

			
		});

		jQuery('#timepicker1').timepicker();
		jQuery("#date1").mask("99-99-9999");
		jQuery('#timepicker2').timepicker();
		jQuery("#date2").mask("99-99-9999");

		function submitBooking()
		{
			$('#bookedModel').modal('hide');
		}

		$('#followUpCall').click(function(){
			if ($(this).is(":checked"))
			{
			  $("#followUpCallModel").modal('show');

			}
		});

		function showfollowUpCallModel()
		{
			$("#followUpCallModel").modal('show');
		}

		function removeFollowUpCall()
		{
			var leadID = $('#leadID').val();
			$.ajax({
	          type: 'post',
	          url: "{{ URL::route('user.leads.removefollowupcall') }}",
	          cache: false,
	          data: {"leadID": leadID}
	        });
		}

		function editlead() {
		    window.location.href = "{{URL::route("user.leads.createlead") . '/'. $leadID }}";
		}

	</script>
@stop
