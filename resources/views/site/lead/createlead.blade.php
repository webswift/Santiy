@extends('layouts.dashboard')

@section('title')
	Generate New Lead
@stop

@section('css')
    {!! Html::style('assets/css/fullcalendar.css') !!}
	{!! HTML::style('assets/css/bootstrap-timepicker.min.css') !!}
	{!! Html::style('assets/css/jquery.tagsinput.css') !!}
	<style>
    .fileUpload {
        position: relative;
        overflow: hidden;
        margin: 10px;
    }
    .fileUpload input.upload {
        position: absolute;
        top: 0;
        right: 0;
        margin: 0;
        padding: 0;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        filter: alpha(opacity=0);
          bottom: 0;
    }

    #fileDetails .wrapContent{
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    #fileDetails .content{
    	width: 60%;
    	text-align: center;
    	margin-left: 5px;
    }

    #fileDetails .remove-file{
    	color: rgb(192, 44, 15);
    	cursor: pointer;
    }
    .fc-event-title{
        cursor: pointer;
        padding: 0 1px;
        padding-left: 5px;
    }
    .pac-container {
        z-index: 1051 !important;
    }
#callHistoryModal .modal-body
{
	max-height:calc(100% - 166px);
	min-height:calc(100% - 166px);
	overflow-y: scroll;
}

#callHistoryModal .btn
{
	margin-top: 5px;
}

@media (min-width: 488px) {
#callHistoryModal .modal-body
{
	max-height:calc(100% - 120px);
	min-height:calc(100% - 120px);
	overflow-y: scroll;
}
#callHistoryModal .btn
{
	margin-top: 0px;
}
}

#callHistoryModal .modal-content
{
	height:100%;
}

#callHistoryModal .modal-dialog
{
	height:80%;
}

div.tagsinput input {
 width:200px !important;
}


</style>
@stop

@section('content')
<div class="pageheader">
	<h2><i class="fa fa-plus"></i> Lead Information</h2>
</div>

<div class="contentpanel">
	<div class="panel">
		<div class="panel-body panel-body-nopadding">
			<div class="navbar-fixed-top" style="margin-top:50px;" id="callTimer">
				<div class="col-sm-2 pull-right bg-danger">
					<h4><i class="glyphicon glyphicon-time"></i> <span id="runner"></span></h4>
				</div>
			</div>

			<div class="form-horizontal form-bordered">
				<input type="hidden" id="leadID" value="{{ $leadDetails->id }}" >
				<input type="hidden" id="campaignID" value="{{ $campaignID }}" >

				<div class="form-group">
					<div class="col-sm-6 ">
						<label class="col-sm-3 control-label" for="campaignName">Select Campaign</label>
						<div class="col-sm-9">
							<select class="form-control" id="campaignName">
								@if(sizeof($campaignLists) > 0)
                            	@foreach($campaignLists as $campaignList)
                                	<option value="{{ $campaignList->id }}" @if($campaignList->id == $campaignID) selected @endif >{{ $campaignList->name }}</option>
                            	@endforeach
                            	@endif
                            </select>
                        </div>
					</div>
					<div class="col-sm-2">
						<label class="control-label mb15">Lead Number: {{ $currentLeadNumber }}/{{ $totalLeads }}</label>
					</div>
					<div class="col-sm-4">
						<label class="control-label mb15">Last Actioned By : <b>{{ $lastActionedUser or '-' }}</b></label>
						<br>
						<button class="btn btn-primary btn-xs" type="button" id="callHistory" rel="{{ $leadDetails->id }}">View Lead History</button>
					</div>
				</div>
				@for($i = 0; $i < count($leadFormDatas1); $i = ($i + 2))
					<div class="form-group">
						@if($leadFormDatas1[$i]['type'] == 'text')
							<div class="col-sm-6 parentDiv">
								<label class="col-sm-3 control-label">{{ $leadFormDatas1[$i]['fieldName'] }}</label>
								@if((in_array($leadFormDatas1[$i]['fieldName'], \App\Http\Controllers\CommonController::getFieldVariations('Mobile No')) ||
									in_array($leadFormDatas1[$i]['fieldName'], \App\Http\Controllers\CommonController::getFieldVariations('Telephone No'))) &&
									$user->enableCall == 'Yes'
								)
								<div class="col-sm-7">
									<input autocomplete="false" type="text" rel="{{ $leadFormDatas1[$i]['fieldID'] }}" class="form-control @if($leadFormDatas1[$i]['isRequired'] == 'Yes') required @endif preDefineformfields" value="{{ $leadFormDatas1[$i]['value'] }}">
                                    <span class="help-block"></span>
								</div>
								<div class="col-sm-2"><a href="tel:{{str_replace(' ', '', $leadFormDatas1[$i]['value'])}}" class="btn btn-primary btn-sm toDial">Dial!</a></div>
								@else
									@if($leadFormDatas1[$i]['fieldName'] == 'Address')
										<div class="col-sm-7">
											<input autocomplete="false" type="text" id="leadAddress" rel="{{ $leadFormDatas1[$i]['fieldID'] }}" class="@if($leadFormDatas1[$i]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i]['value'] }}">
											<span class="help-block"></span>
										</div>
										<div class="col-sm-1">
											<button class="btn btn-default" id="addressBtn" title="Search Address"><i class="fa fa-search"></i></button>
										</div>
									@elseif($leadFormDatas1[$i]['fieldName'] == 'Website')
										<div class="col-sm-7">
											<input autocomplete="false" type="text" id="txtWebsite" rel="{{ $leadFormDatas1[$i]['fieldID'] }}" class="@if($leadFormDatas1[$i]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i]['value'] }}">
											<span class="help-block"></span>
										</div>
										<div class="col-sm-1">
											<button class="btn btn-default" id="btnOpenWebsite" title="Open URL in a new window"><i class="fa fa-external-link"></i></button>
										</div>
									@else
										<div class="col-sm-9">
											<input autocomplete="false" type="text" @if($leadFormDatas1[$i]['fieldName'] == "Post/Zip code") id="leadPostalCode" @endif rel="{{ $leadFormDatas1[$i]['fieldID'] }}" class="@if($leadFormDatas1[$i]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i]['value'] }}">
											<span class="help-block"></span>
										</div>
									@endif
								@endif
							</div>
						@elseif($leadFormDatas1[$i]['type'] == 'date')
							<div class="col-sm-6 parentDiv">
								<label class="col-sm-3 control-label">{{ $leadFormDatas1[$i]['fieldName'] }}</label>
								<div class="col-sm-9">
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
									<input autocomplete="false" type="text" 
										rel="{{ $leadFormDatas1[$i]['fieldID'] }}" 
										class="@if($leadFormDatas1[$i]['isRequired'] == 'Yes') required @endif form-control customDateField" 
										value="{{ $leadFormDatas1[$i]['value'] }}">
										<span class="help-block"></span>
								</div>
								</div>
							</div>
						@elseif($leadFormDatas1[$i]['type'] == 'dropdown')
							<div class="col-sm-6 parentDiv">
								<label class="col-sm-3 control-label">{{ $leadFormDatas1[$i]['fieldName'] }}</label>
								<div class="col-sm-9">
									<select rel="{{ $leadFormDatas1[$i]['fieldID'] }}" class="form-control preDefineformfields @if($leadFormDatas1[$i]['isRequired'] == 'Yes') required @endif" >
										<option value="">Select</option>
										@foreach($leadFormDatas1[$i]['defaultValues'] as $value)
											<option value="{{$value}}" @if(strtolower($value) == strtolower($leadFormDatas1[$i]['value'])){{'selected'}}@endif>{{$value}}</option>
										@endforeach
									</select>
                                    <span class="help-block"></span>
								</div>
							</div>
						@endif


						@if($i+1 < count($leadFormDatas1))
		                    @if($leadFormDatas1[$i+1]['type'] == 'text')
								<div class="col-sm-6 parentDiv">
									<label class="col-sm-3 control-label">{{ $leadFormDatas1[$i+1]['fieldName'] }}</label>
									@if((in_array($leadFormDatas1[$i+1]['fieldName'], \App\Http\Controllers\CommonController::getFieldVariations('Mobile No')) ||
										in_array($leadFormDatas1[$i+1]['fieldName'], \App\Http\Controllers\CommonController::getFieldVariations('Telephone No'))) &&
										$user->enableCall == 'Yes'
									)
									<div class="col-sm-7">
										<input autocomplete="false" type="text" rel="{{ $leadFormDatas1[$i+1]['fieldID'] }}" class="@if($leadFormDatas1[$i+1]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i+1]['value'] }}">
                                        <span class="help-block"></span>
									</div>
									<div class="col-sm-2"><a href="tel:{{str_replace(' ', '', $leadFormDatas1[$i+1]['value'])}}" class="btn btn-primary btn-sm toDial">Dial!</a></div>
									@else
										@if($leadFormDatas1[$i+1]['fieldName'] == 'Address')
											<div class="col-sm-7">
												<input autocomplete="false" type="text" id="leadAddress" rel="{{ $leadFormDatas1[$i+1]['fieldID'] }}" class="@if($leadFormDatas1[$i+1]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i+1]['value'] }}">
												<span class="help-block"></span>
											</div>
											<div class="col-sm-1">
												<button class="btn btn-default" id="addressBtn" title="Search Address"><i class="fa fa-search"></i></button>
											</div>
										@elseif($leadFormDatas1[$i+1]['fieldName'] == 'Website')
											<div class="col-sm-7">
												<input autocomplete="false" type="text" id="txtWebsite" rel="{{ $leadFormDatas1[$i+1]['fieldID'] }}" class="@if($leadFormDatas1[$i+1]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i+1]['value'] }}">
												<span class="help-block"></span>
											</div>
											<div class="col-sm-1">
												<button class="btn btn-default" id="btnOpenWebsite" title="Open URL in a new window"><i class="fa fa-external-link"></i></button>
											</div>
										@else
											<div class="col-sm-9">
												<input autocomplete="false" type="text" @if($leadFormDatas1[$i+1]['fieldName'] == "Post/Zip code") id="leadPostalCode" @endif rel="{{ $leadFormDatas1[$i+1]['fieldID'] }}" class="@if($leadFormDatas1[$i+1]['isRequired'] == 'Yes') required @endif form-control preDefineformfields" value="{{ $leadFormDatas1[$i+1]['value'] }}">
												<span class="help-block"></span>
											</div>
										@endif
									@endif


								</div>
							@elseif($leadFormDatas1[$i + 1]['type'] == 'date')
								<div class="col-sm-6 parentDiv">
									<label class="col-sm-3 control-label">{{ $leadFormDatas1[$i + 1]['fieldName'] }}</label>
									<div class="col-sm-9">
									<div class="input-group">
										<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
										<input autocomplete="false" type="text" 
											rel="{{ $leadFormDatas1[$i + 1]['fieldID'] }}" 
											class="@if($leadFormDatas1[$i + 1]['isRequired'] == 'Yes') required @endif form-control customDateField" 
											value="{{ $leadFormDatas1[$i + 1]['value'] }}">
											<span class="help-block"></span>
									</div>
									</div>
								</div>
							@elseif($leadFormDatas1[$i+1]['type'] == 'dropdown')
								<div class="col-sm-6 parentDiv">
									<label class="col-sm-3 control-label">{{ $leadFormDatas1[$i+1]['fieldName'] }}</label>
									<div class="col-sm-9">
										<select rel="{{ $leadFormDatas1[$i+1]['fieldID'] }}" class="form-control preDefineformfields @if($leadFormDatas1[$i+1]['isRequired'] == 'Yes') required @endif">
											<option value="">Select</option>
											@foreach($leadFormDatas1[$i+1]['defaultValues'] as $value)
												<option value="{{$value}}" @if(strtolower($value) == strtolower($leadFormDatas1[$i+1]['value'])){{'selected'}}@endif>{{$value}}</option>
											@endforeach
										</select>
                                        <span class="help-block"></span>
									</div>
								</div>
							@endif
		                @endif
					</div>
				@endfor

				<div class="form-group">
					<div class="col-sm-6 parentDiv">
						<label class="col-sm-3 control-label">Outcome</label>
						<div class="col-sm-9">
							<select class="form-control required" id="leadInterest" name="leadInterest">
								<option value="NotSet" @if($leadDetails->interested == 'NotSet') selected @endif >Select a Value</option>
								<option value="Interested" @if($leadDetails->interested == 'Interested') selected @endif >Positive</option>
								<option value="NotInterested" @if($leadDetails->interested == 'NotInterested') selected @endif >Negative</option>
								<option value="Unreachable" @if($leadDetails->interested == 'Unreachable') selected @endif >Unreachable</option>
							</select>
							<span class="help-block"></span>
						</div>
					</div>
					@if($emailFieldExists)
						<div class="col-sm-6">
							<label for="emailTemplateID" class="control-label col-sm-3">Send Email</label>
							<div class="col-sm-7">
								<select class="form-control" id="emailTemplateID">
									<option value="">Do not send Email</option>
                                    @foreach($emailTempletes as $emailTemplete)
                                        @if($emailTemplete->name != 'Appointment booked' && $emailTemplete->name != 'Follow Up Call')
                                        <option value="{{ $emailTemplete->id }}" @if($leadDetails->emailTemplate == $emailTemplete->id) selected @endif >{{ $emailTemplete->name }}</option>
                                        @endif
                                    @endforeach
									{{--
									@forelse($massMailTemplates as $massTemplate)
										<option value="mass_{{ $massTemplate->id }}" @if($leadDetails->mass_email_template_id == $massTemplate->id) selected @endif>{{ $massTemplate->name }} (From mass mail archive)</option>
									@empty
									@endforelse
									--}}
                                    <option value="writeNew">Write a new Email</option>
								</select>
							</div>
							<button class="btn btn-primary btn-sm hidden" id="editAndSendEmailTemplate">Quick Edit</button>
						</div>
					@endif
				</div>

				@foreach($leadFormDatas2 as $leadFormData2)
					<div class="form-group parentDiv">
						<label class="col-sm-3 control-label">{{ $leadFormData2['fieldName'] }}</label>
		                <div class="col-sm-6">
		                	@if(strtolower($leadFormData2['fieldName']) == 'notes')
		                		<textarea rel="{{ $leadFormData2['fieldID'] }}"  id="importLeadTXT" class="@if($leadFormData2['isRequired'] == 'Yes') required @endif form-control preDefineformfields" rows="5">{{ $leadFormData2['value'] }}</textarea>
                                <span class="help-block"></span>
		                	@else
		                		<input rel="{{ $leadFormData2['fieldID'] }}" type="text" value="{{ $leadFormData2['value'] }}"
									   @if($leadFormData2['fieldName'] == 'Address')
									       id="leadAddress"
									   @elseif(strtolower($leadFormData2['fieldName']) == 'post/zip code')
                                           id="leadPostalCode"
                                       @endif
                                        class="form-control preDefineformfields @if($leadFormData2['isRequired'] == 'Yes') required @endif">
                                <span class="help-block"></span>
		               	 	@endif
		              	</div>
                        @if($leadFormData2['fieldName'] == 'Address' && $isAddressLookUp == true)
                            <div class="col-md-1">
                                <button class="btn btn-default" id="addressBtn" title="Search Address"><i class="fa fa-search"></i></button>
                            </div>
                        @endif
		              	@if(strtolower($leadFormData2['fieldName']) == 'notes')
		              		<div class="col-sm-3">
		              			<button type="button" style="margin-top: 75px;" rel="{{$leadDetails->id}}" class="btn btn-primary btn-sm" id="copyNotes">Timestamp Notes</button>
		              		</div>
		              	@endif
		            </div>
                @endforeach

                <div class="form-group">
                	<div class="col-sm-6">
                		<label class="col-sm-2 control-label"></label>
                		<div class="col-sm-5 mt10">
                			<div class="ckbox ckbox-default">
                				<input type="checkbox" id="followUpCall" @if($isCallBackUserExists) checked @endif >
		                        <label for="followUpCall">Follow Up Call</label>
		                        <span id="callBackViewDiv">
									@if($isCallBackUserExists)
										<a href="javascript:void(0);" onclick="showfollowUpCallModel()">(View)</a>
									@else
		                        		<a href="javascript:void(0);" onclick="showfollowUpCallModel()" class="hide">(View)</a>
		                        	@endif
		                        </span>
							</div>
						</div>
						<div class="col-sm-5 mt10">
							<div class="ckbox ckbox-default">
								<input type="checkbox" value="Yes" id="appointment" @if($leadDetails->bookAppointment == 'Yes') checked @endif >
								<label for="appointment">Book Appointment</label>
								<span id="appointmentViewDiv" class="hidden">
									@if($leadDetails->bookAppointment == 'Yes')
										<a href="javascript:void(0);" onclick="showBookAppointmentModal()">(View)</a>
									@else
										<a href="javascript:void(0);" onclick="showBookAppointmentModal()">(View)</a>
									@endif
								</span>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<label class="col-sm-3 control-label">Reference</label>
						<div class="col-sm-9">
							<input type="text" id="leadReferenceNumber" class="form-control" value="{{ $leadDetails->referenceNumber or '' }}">
						</div>
					</div>
				</div>

				<div class="form-group actionButtons">
					<label class="col-sm-1 control-label"></label>
					<div class="col-sm-6">
						<p>
				                <button onclick="goToAction('exitwithoutsave')" class="btn btn-info btn-sm">Exit without changes</button>&nbsp;
							@if($isEditable)
				                <button onclick="goToAction('back')" class="btn btn-warning btn-sm">Back</button>&nbsp;
				            @endif
				                <button onclick="goToAction('saveandexit')" class="btn btn-primary btn-sm">Save and Exit</button>&nbsp;
				            @if($isEditable)
				                <button onclick="goToAction('next')" class="btn btn-success btn-sm">Next</button>&nbsp;
				                <button onclick="skipLead()" class="btn btn-danger btn-sm">Skip</button>
				            @endif
						</p>
					</div>
					<div class="col-sm-2 invisible savingMessage">
						<p class="mt5">Saving Data...</p>
					</div>
					<div class="col-sm-3">
						<button type="button" id="attachFiles" class="btn btn-success btn-sm">Attach files</button>
						<button type="button" rel="{{$leadDetails->id}}" id="viewAttached" class="btn btn-info btn-sm">View attached files</button>
					</div>
				</div>
			</div>
		</div><!-- panel-body -->
	</div><!-- panel -->
</div><!-- contentpanel -->
@stop


@section('bootstrapModel')
	<!-- Booking model -->
	<div id="bookedModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Booking Calendar</h4>
	        </div>
	        <div class="modal-body">
	        	<div id="error1" ></div>
	        	<div class="form-group">
					<label class="col-sm-4 control-label padding5" for="salesmanID">Sales Person</label>
	                <div class="col-sm-6">
			            <select class="form-control" id="salesmanID">
			            <option value="">Select a Sales Person</option>
			            	@if(sizeof($salesMemberLists) > 0)
	                        @foreach($salesMemberLists as $salesMemberList)
	                        	<option value="{{ $salesMemberList->id }}" @if( $salesMemberList->id == $salesManDetail['salesManUserID']) selected  @endif >{{ $salesMemberList->firstName . ' ' . $salesMemberList->lastName }}</option>
	                        @endforeach
	                        @endif
                        </select>
	                </div>
	            </div>
	        	<div class="form-group">
					<label class="col-sm-4 control-label padding5" for="date1">Date</label>
	                <div class="col-sm-6">
			            <div class="input-group">
			                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			                <input type="text" placeholder="Date" id="date1" @if($isSalesManExists) value="{{$salesManDetail['date']}}" @endif style="width:100%; padding: 3px" />
			            </div>
	                </div>
	            </div>
	        	<div class="form-group">
					<label class="col-sm-4 control-label padding5" for="timepicker1">Time</label>
	                <div class="col-sm-6">
	                	<div class="input-group">
			                <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
			                <div class="bootstrap-timepicker"><input id="timepicker1" type="text" class="form-control" @if($isSalesManExists) value="{{$salesManDetail['time']}}" @endif /></div>
		                </div>
	              	</div>
	            </div>
                <div class="form-group">
                    <p><a href="javascript:;" onclick="viewSalesmanCalendar()">Click here</a> to view the calendar</p>
                </div>
				@if($emailFieldExists)
	        	<div class="form-group">
	                <div class="col-sm-12">
						<div class="ckbox ckbox-default">
							<input type="checkbox" id="chkBoxSendEmailToLead">
							<label for="chkBoxSendEmailToLead">Send this lead a confirmation email</label>
						</div>
	                </div>
	            </div>
				@endif
	        	<div class="form-group">
	                <div class="col-sm-12">
						<div class="ckbox ckbox-default">
							<input type="checkbox" id="chkBoxSendEmailToSalesman">
							<label for="chkBoxSendEmailToSalesman">Send a confirmation & reminder to Sales Person</label>
						</div>
	                </div>
	            </div>
	        </div>
	        <div class="modal-footer">
	            <button onclick="submitBooking()" class="btn btn-primary btn-xs" id="bookingButton">Book</button>
	        </div>
	    </div>
	  </div>
	</div><!-- Booking model -->	

	<!-- Follow Up call model -->
	<div id="followUpCallModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	  <div class="modal-dialog">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button aria-hidden="true" id="followUpModalCancle" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Follow up call</h4>
	        </div>
	        <div class="modal-body">
	        	<div id="error2" ></div>
	        	<div class="form-group">
					<label class="col-sm-4 control-label padding5" for="date2">Date</label>
	                <div class="col-sm-6">
			            <div class="input-group">
			                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
			                <input type="text" placeholder="Date" id="date2" @if($isCallBackUserExists) value="{{$callBackUserDetail['date']}}" @endif style="width:100%; padding: 3px" />
			            </div>
	                </div>
	            </div>
	        	<div class="form-group">
					<label class="col-sm-4 control-label padding5" for="timepicker2">Time</label>
	                <div class="col-sm-6">
	                	<div class="input-group">
			                <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
			                <div class="bootstrap-timepicker"><input id="timepicker2" type="text" class="form-control" @if($isCallBackUserExists) value="{{$callBackUserDetail['time']}}" @endif /></div>
		                </div>
	              	</div>
	            </div>
	            <div class="form-group">
					<label class="col-sm-4 control-label padding5" for="callBackUserID">Who would like to follow up this call?</label>
	                <div class="col-sm-6">
			            <select class="form-control" id="callBackUserID">
			            	<option value="">Select a User</option>
			            	<option value="{{ $user->id }}">Me</option>
			            	@if(sizeof($callBackUserLists) > 0)
	                        @foreach($callBackUserLists as $callBackUserList)
	                        	<option value="{{ $callBackUserList->id }}" @if( $callBackUserList->id == $callBackUserDetail['callBackUserID']) selected  @endif >{{ $callBackUserList->firstName . ' ' . $callBackUserList->lastName }}</option>
	                        @endforeach
	                        @endif
                        </select>
	                </div>
	            </div>
                <div class="form-group">
                    <p><a target="_blank" href="{{ URL::route('user.leads.pendingcallbacks') }}">Click here</a> to view your Diary</p>
                </div>
				@if($emailFieldExists)
	        	<div class="form-group">
	                <div class="col-sm-12">
						<div class="ckbox ckbox-default">
							<input type="checkbox" id="chkBoxFollowUpSendEmailToLead">
							<label for="chkBoxFollowUpSendEmailToLead">Send this lead a confirmation email</label>
						</div>
	                </div>
	            </div>
				@endif
	        	<div class="form-group">
	                <div class="col-sm-12">
						<div class="ckbox ckbox-default">
							<input type="checkbox" id="chkBoxFollowUpSendEmailToMember">
							<label for="chkBoxFollowUpSendEmailToMember">Send a confirmation & reminder to selected user</label>
						</div>
	                </div>
	            </div>
	        </div>
	        <div class="modal-footer">
	        <button onclick="submitFollowUpCall()" class="btn btn-primary btn-xs">Follow Up</button>
	        </div>
	    </div>
	  </div>
	</div><!-- Follow Up call model -->

	<!-- Skip model -->
	<div id="skipModel" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-sm">
	    <div class="modal-content">
	        <div class="modal-header">
	            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <h4 class="modal-title">Skip and Remove</h4>
	        </div>
	        <div class="modal-body">
				<p>Would you like to skip and delete this entry from your list or come back to it later?</p>
				<div class="ckbox ckbox-default form-control-mt7">
					<input type="checkbox" id="chkSkipAuto">
					<label for="chkSkipAuto"> Don’t ask again in future, Just Skip </label>
				</div>
	        </div>
	        <div class="modal-footer">
	            <button javascript="void(0)" id="skipAndDelete" class="btn btn-primary btn-xs">Skip and Delete</button>
                <button javascript="void(0)" id="comeBackLater" class="btn btn-default btn-xs">Come back later</button>
	        </div>
	    </div>
	  </div>
	</div><!-- skip model -->

    <!-- Send Email model -->
    <div id="sendEmailModel" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" id="emailCancleButton" data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">Send Email</h4>
            </div>
            <div class="modal-body">
                <div id="emailError" ></div>
                <div class="form-group">
                    <label class="control-label">Subject</label>
                    <input type="text" id="subject" class="form-control"/>
                </div>
                <div class="form-group">
                    <label class="control-label">CC</label>
                    <input type="email" id="email_cc" class="form-control" title="list of addresses"/>
					<a id="aShowBcc" href="javascript:void" onclick="showBCC();return false;" class="pull-right">Show bcc</a>
                </div>
                <div class="form-group hidden" id="divBccField">
                    <label class="control-label">BCC</label>
                    <input type="email" id="email_bcc" class="form-control" title="list of addresses"/>
                </div>
                <div class="form-group">
                    <textarea id="ckeditor" class="form-control" rows="10"></textarea>
                </div>
                <div class="row">
					<div class="col-md-3" style="width: 20%">
						<div class="fileUpload btn btn-primary"><span>Add Attachments</span>
						<input class="upload" type="file" id="emailAttachments"></div>
					</div>
					<div class="col-md-8">
						<div class="form-group" id="fileDetails">

						</div>
					</div>
					<div class="row hidden" id="fileError">
						<div class="col-md-8 col-md-offset-2"><p class="text-danger" style="margin-left: 10px;"></p></div>
					</div>
				</div>
                <div class="form-group">
                  <div class="col-sm-2">
                     <label class="control-label"> FormFields Names </label>
                  </div>
                  <div class="col-sm-10">
					  <div class="row">
                    @foreach($formFieldsNames as $formFieldsName)
                    <div class="col-sm-2">
                      <a href="javascript:void(0)" onclick="addTextToCKeditor('{{ $formFieldsName->fieldName }}')">{{ $formFieldsName->fieldName }}</a>
                    </div>
                    @endforeach
					  </div>
					  <div class="col-sm-2">
						  <a href="javascript:void(0)" onclick="addTextToCKeditor('Agent')">Agent</a>
					  </div>
                  </div>
                </div>
            </div>
            <div class="modal-footer">
            <button id="EmailSendButton" onclick="sendEmail()" class="btn btn-primary btn-xs">Send Email</button>
            </div>
        </div>
      </div>
    </div><!-- Follow Up call model -->


    <div id="callHistoryModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="callHistoryModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog" style="width: 1025px;">
			<div class="modal-content">
    	        <div class="modal-header">
    	            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    	            <h4 class="modal-title">Call History</h4>
    	        </div>
    	        <div class="modal-body">
    	        </div>
    	        <div class="modal-footer">
    	            <button class="btn btn-primary btn-xs" data-dismiss="modal">Close</button>
    	        </div>
    	    </div>
		</div>
	</div><!-- Call history model -->

	<div id="viewAttachmentModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewAttachmentModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
					<h4 class="modal-title">Lead Attachment(s)</h4>
				</div>
				<div class="modal-body">
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary btn-xs" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div><!-- Call history model -->

	<div id="addFilesModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addFilesModalLabel" aria-hidden="true" data-backdrop="static">
    		<div class="modal-dialog modal-lg">
    			<div class="modal-content">
        	        <div class="modal-header">
        	            <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        	            <h4 class="modal-title">Add attachment(s) to lead</h4>
        	        </div>
        	        <div class="modal-body">
        	        <div class="alert alert-danger hidden" id="fileError">
						<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
						<span></span>
					  </div>
        	        <div class="table-responsive hidden">
        	        	<table class="table table-danger mb30">
        	        		<thead>
        	        			<tr>
        	        				<th>File Name</th>
        	        				<th>Status</th>
        	        			</tr>
        	        		</thead>
        	        		<tbody></tbody>
        	        	</table>
        	        </div>
        	        <form id="attachForm" enctype="multipart/form-data">
        	        <div class="form-group">
						<label class="control-label">Browse Files</label>
						<input type="file" id="fileInput" class="form-control" name="file[]" multiple/>
						<input type="hidden" id="leadID" name="leadID" value="{{$leadDetails->id}}">
        	        </div>
        	        </form>
        	        </div>
        	        <div class="modal-footer">
        	            <button class="btn btn-primary btn-xs" data-dismiss="modal">Close</button>
        	        </div>
        	    </div>
    		</div>
    	</div><!-- Call history model -->

<div id="calenderModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">Calendar</h4>
            </div>
            <div class="modal-body">
                Loading...
            </div>
        </div>
    </div>
</div>

<div id="bookedInfo" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button id="bookedInfoCancle"  data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">Booking Calendar</h4>
            </div>
            <div class="modal-body">
                <div id="bookingError" ></div>
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
                            <input type="text" placeholder="Date" id="bookDate" class="form-control"  />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <label>Time</label>
                        <div class="input-group mb15">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                            <div class="bootstrap-timepicker"><input id="bookTimepicker" type="text" class="form-control" /></div>
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
</div>

<div id="addressLookUpModal" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">Address Search</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                   <form id="addressLookUpForm">
                       <input class="form-control" placeholder="Enter Address" name="searchAddress" id="searchAddress" type="text">
                   </form>
               </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/jquery.runner.js') !!}

<script type="text/javascript">

$('#runner').runner({
	autostart: true,
	startAt: {{ $leadDetails->timeTaken }},
	milliseconds: false,
	format: function(sec, msec) {
		sec = Math.floor(sec/1000);
		var seconds = sec % 60;
		var min = Math.floor(sec / 60);
		return min +"m " + ((seconds<10)?"0":"")+seconds + "s";
	}
});

$('#calenderModal').on('shown.bs.modal', function () {
    $("#calendar").fullCalendar('render');
});

setInterval(function() {
    var leadID = $('#leadID').val();
    var timerInfo = $('#runner').runner('info');
    var timeTaken = timerInfo.time;

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.leads.saveleadtimetaken') }}",
        cache: false,
        data: {"leadID": leadID, "timeTaken": timeTaken}
    });
}, 15000);

@if($user->enableCallTimer == 'No')
    $('#callTimer').hide();
@endif
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/bootstrap-timepicker.min.js') !!}
{!! HTML::script('assets/js/jquery.maskedinput.min.js') !!}
{!! HTML::script('assets/js/ckeditor/ckeditor.js') !!}
{!! HTML::script('assets/js/ckeditor/adapters/jquery.js') !!}
{!! HTML::script('assets/js/fullcalendar.min.js') !!}
{!! Html::script('assets/js/jquery.tagsinput.min.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}
{!! HTML::script('assets/js/custom_date_field.js') !!}
@if($isAddressLookUp == true)
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?libraries=places"></script>
@endif
	
<script type="text/javascript">

var previousBookingValue = "";
var previousFollowUpValue = "";
var previousClickBookingValue = "";

var powerDialer = '{{$user->enablePowerDial}}';

var maxFileSize = {{$maxFileSize}};
var emailFormData = new FormData();
var filesToBeDeleted = [];
var sendEmailWithTemplateId = 0;
var resetEmailDropdown = false;

var roxyFileman = "{{ URL::asset('assets/fileman') }}/";

var pendingRequests = 0;

@if($isAddressLookUp == true)
var placeSearch, autocomplete;
var componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    postal_code: 'short_name'
};

function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
            (document.getElementById('searchAddress')));

    autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() {
    // Get the place details from the autocomplete object.
    var place = autocomplete.getPlace();
    var address  = document.getElementById('searchAddress').value;
    document.getElementById('leadAddress').value = address;
    $('#leadAddress').trigger('blur');

    var postcode = false;

    for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        if (addressType == 'postal_code') {
            var val = place.address_components[i][componentForm[addressType]];
            document.getElementById('leadPostalCode').value = val;
            $('#leadPostalCode').trigger('blur');
            postcode = true;
        }
    }

    if(postcode == false) {
        document.getElementById('leadPostalCode').value = '';
        $('#leadPostalCode').trigger('blur');
    }

    $('#addressLookUpModal').modal('hide');
}
@endif

function processButtons() {
	if(pendingRequests == 0) {
		$(".actionButtons button").each(function() {
			$(this).prop('disabled', false);
		});
		$('.savingMessage').addClass('invisible');
	}
	else {
		$(".actionButtons button").each(function() {
			$(this).prop('disabled', true);
		});
		$('.savingMessage').removeClass('invisible');
	}
}

$(function() {
	if(powerDialer == 'Yes') {
		$('.toDial').each(function() {
			window.open($(this).attr('href'));
		});
	}

	CKEDITOR.replace( 'ckeditor',
			{
				filebrowserBrowseUrl:roxyFileman, 
				filebrowserImageBrowseUrl:roxyFileman+'?type=image' , 
				removeDialogTabs: 'link:upload;image:upload'
				, toolbarGroups : [
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
					{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
					{ name: 'forms', groups: [ 'forms' ] },
					{ name: 'insert', groups: [ 'insert' ] },
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
					{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
					'/',
					{ name: 'styles', groups: [ 'styles' ] },
					{ name: 'colors', groups: [ 'colors' ] },
					{ name: 'tools', groups: [ 'tools' ] },
					{ name: 'others', groups: [ 'others' ] },
					{ name: 'about', groups: [ 'about' ] },
					{ name: 'links', groups: [ 'links' ] }
				],
				removeButtons : 'Save,Templates,,Source,NewPage,Preview,Print,Cut,Copy,Paste,PasteText,PasteFromWord,Undo,Redo,Flash,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,NumberedList,BulletedList,Outdent,Indent,Blockquote,CreateDiv,Find,Replace,SelectAll,Scayt' 
			});


	CKEDITOR.config.disableNativeSpellChecker = true;
	CKEDITOR.config.scayt_autoStartup = true;
	CKEDITOR.config.allowedContent = true;

	$('#email_cc,#email_bcc').tagsInput({width:'100%', height:'auto', defaultText : 'add email'});
	

	@if($leadDetails->bookAppointment == 'Yes')
		previousBookingValue = 'Yes';
		$('#appointmentViewDiv').removeClass('hidden');
	@else
		previousBookingValue = 'No';
	@endif

	$('#appointment').change(function() {
		if(this.checked) {
			//show modal
			$('#bookedModel').find('#date1').datepicker("setDate", "0");
			$('#bookedModel').find('#error1').html('');
			$('#bookingButton').removeClass('hidden');
			$('#bookingButton').removeAttr('disabled');
			$('#bookedModel').modal('show');
		}
		else {
			//delete appointment
			if(previousBookingValue == 'Yes') {
				$('#bookedModel').find('#date1').datepicker("setDate", "0");
				$('#bookedModel').find('#salesmanID').val('');
				pendingRequests++;
				processButtons();
				var leadID = $('#leadID').val();

				$.ajax({
					type: 'post',
					url: "{{ URL::route('user.leads.bookappointmentstatusno') }}",
					cache: false,
					data: {"leadID": leadID}
				}).done(function(response) {
					$('#appointmentViewDiv').addClass('hidden');
					pendingRequests--;
					processButtons();
					previousBookingValue = 'No';
				});
			}
		}
    });

    $('#bookedModelCancle').click(function () {
        if(previousBookingValue == 'No') {
            $("#appointment").prop('checked', false);
            $('#appointmentViewDiv').addClass('hidden');
        }
    });

    @if($isAddressLookUp == true)
        $('#addressBtn').click(function () {
            document.getElementById("addressLookUpForm").reset();
            $('#addressLookUpModal').modal('show');
        });

        $('#addressLookUpModal').on('shown.bs.modal', function () {
            initAutocomplete();
        });

        $('#addressLookUpModal').on('submit', '#addressLookUpForm', function (e) {
            e.preventDefault();
        });
    @endif
		
	$('#btnOpenWebsite').click(function () {
		var url = $('#txtWebsite').val();
		if(url != '') {
			if(url.indexOf('http://') != 0 && url.indexOf('https://') != 0) {
				url = 'http://' + url;
			}
			window.open(url, '_blank');
		} else {
			showError('Please enter a url first');
		}
	});
});

$('#copyNotes').click(function(event) {
	var notes = $.trim($('#importLeadTXT').val());
	var leadID = $(this).attr('rel');

	if(notes == '' || notes == undefined) {
		return false;
	}

	$.post("{{URL::route('user.leads.updateNotes')}}", {notes : notes, leadID : leadID},function(response) {
		if(response.status == 'success') {
			$('#copyNotes').text('Copied');
			$('#copyNotes').prop('disabled', true);
		}
	}, 'json');
});

$('#callHistory').click(function() {
	var leadID = $(this).attr('rel');
	blockUI('.contentpanel');

	$.post('{{URL::route('user.leads.callHistory')}}', {'leadID' : leadID}, function(response) {
		$('#callHistoryModal').find('.modal-body').html(response);
		$('#callHistoryModal').modal('show');

		unblockUI('.contentpanel');
	});
});

$('#attachFiles').click(function() {
	$('#addFilesModal').find('.table-responsive').addClass('hidden');
	$('#addFilesModal').find('#attachForm').removeClass('hidden');
	$('#addFilesModal').find('#fileInput').val('');
	$('#addFilesModal').find('#fileError').addClass('hidden');
	$('#addFilesModal').modal('show');
});

document.querySelector('input[id="fileInput"]').addEventListener('change', function(e) {
  uploadFiles(this.files);
}, false);

document.querySelector('input[id="emailAttachments"]').addEventListener('change', function(e) {
	validateEmailAttachments(this.files);
}, false);

function validateEmailAttachments(files) {
	for (var i = 0, file; file = files[i]; ++i) {
		var fileType = file.type;
		var fileSize = file.size;
		var fileName = file.name;

		$('#sendEmailModel').find('#fileError').addClass('hidden');
		var error = false;

		if(fileSize > (maxFileSize*1024*1024)){
			$('#sendEmailModel').find('#fileError').find('p').html(file.name + ' cannot be attached. It exceeds the max file size limit '+maxFileSize+' MB. Try another file.');
			$('#sendEmailModel').find('#fileError').removeClass('hidden');

			continue;
		}

		var extension = fileName.split('.').pop();

		if(extension == 'php' || extension == 'js' || extension == 'exe'){
			$('#sendEmailModel').find('#fileError').find('p').html(file.name + ' cannot be attached. File extension js, php and exe are not allowed.');
			$('#sendEmailModel').find('#fileError').removeClass('hidden');

			return false;
		}

		var fileIcon  = 'fa fa-file-text-o';

		switch (fileType) {
			case 'application/pdf':
				fileIcon = 'fa fa-file-pdf-o';
				break;
			case 'application/zip':
			case 'application/x-compressed-zip':
			case 'application/x-zip-compressed':
				fileIcon = 'fa fa-file-archive-o';
				break;
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.template':
				fileIcon = 'fa fa-file-word-o';
				break;
			case 'image/gif':
			case 'image/jpeg':
			case 'image/png':
				fileIcon = 'fa fa-file-image-o';
				break;
			case 'application/vnd.openxmlformats-officedocument.presentationml.slideshow':
			case 'application/vnd.ms-powerpoint':
			case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
				fileIcon = 'fa fa-file-powerpoint-o';
				break;
			case 'application/vnd.ms-excel':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				fileIcon = 'fa fa-file-excel-o';
				break;
			case 'text/plain':
				fileIcon = 'fa fa-file-text-o';
				break;
			default :
				fileIcon = 'fa fa-file-text-o';
		}

		if(error == false){
			addAttachmentFile(file.name, fileIcon, file.size);
			emailFormData.append('file[]', file, file.name);
		}
	}
}

function addAttachmentFile(fileName, fileIcon, fileSize) {
	if(fileIcon == null) {
		fileIcon = 'fa fa-file-text-o';
	}
	var str = '<p class="col-md-4" rel="'+fileName+'">' +
		'<label class="pull-left" title="Remove File"><span class="glyphicon glyphicon-remove remove-file" onclick="removeFile(\''+fileName+'\')"></span></label>' +
		 '<label class="wrapContent content"><span><i class="'+fileIcon+'"> </i> '+fileName+'</span></label>' +
		  '<label class="pull-right"><span>'+bytesToSize(fileSize)+'</span></label>' +
		   '</p>';

	 $('#fileDetails').append(str);
}

function uploadFiles(files){
	var fileError = false;

	$('#addFilesModal').find('.table-responsive').addClass('hidden');
    $('#addFilesModal').find('table').find('tbody').html('');

    $('#addFilesModal').find('#fileError').addClass('hidden');

	for (var i = 0, file; file = files[i]; ++i) {
		var fileType = file.type;
		var fileSize = file.size;

		hideErrors();

		if(fileSize > (maxFileSize*1024*1024)){
			fileError = true;
            //showFieldError(file.name + ' cannot be attached. It exceeds the max file size limit '+maxFileSize+' MB. Try another file.', 'uploadedcsvFile');

            $('#addFilesModal').find('.table-responsive').removeClass('hidden');
			$('#addFilesModal').find('table').find('tbody').append('<tr><td>'+file.name+'</td><td>cannot be upload. It exceeds the max file size limit '+maxFileSize+' MB</td></tr>');
            continue;
		}
	}

	if(!fileError){
		var form = document.getElementById('attachForm');
		var formData = new FormData(form);

		blockUI('#addFilesModal .modal-body');

		$.ajax({
			method: 'POST',
			url: "{{URL::route('user.leads.attachFiles')}}",
			dataType: "JSON",
			data: formData,
			contentType : false,
			processData : false,
			success: function (response) {
				if(response.success == false){
					$('#addFilesModal').find('#fileError').find('span').html(response.message);
					$('#addFilesModal').find('#fileError').removeClass('hidden');
				}
				else{
					$('#addFilesModal').find('#attachForm').addClass('hidden');

					var str = '';

					for(var i = 0; i< response.files.length; i++){
						str += '<tr><td>'+response.files[i]+'</td><td>'+response.status[i]+'</td></tr>';
					}

					$('#addFilesModal').find('.table-responsive').removeClass('hidden');
					$('#addFilesModal').find('table').find('tbody').html(str);
				}

				unblockUI('#addFilesModal .modal-body');
			},
			error: function (xhr, textStatus, thrownError) {
				unblockUI('#addFilesModal .modal-body');
				alert("There is some error. Try again!!");
			}
		});

		return false; // To avoid actual submission of the form
	}
}

$('#viewAttached').click(function(){
	var leadID = $(this).attr('rel');

	blockUI('.contentpanel');

	$.post('{{URL::route('user.leads.attachmentHistory')}}', {'leadID' : leadID}, function(response){
		$('#viewAttachmentModal').find('.modal-body').html(response);
		$('#viewAttachmentModal').modal('show');

		unblockUI('.contentpanel');
	});
});

function deleteAttachedFile(attachedID){
	blockUI('#viewAttachmentModal .modal-body');
	$.post('{{URL::route('user.leads.deleteAttachment')}}', {'id' : attachedID}, function(){
		$('#attached_'+attachedID).remove();
		unblockUI('#viewAttachmentModal .modal-body');
	});
}

function savefielddata()
{
	var leadID = $('#leadID').val();
	var fieldID = $(this).attr('rel');
	var fieldValue = $(this).val();

	//pendingRequests++;
	//processButtons();
	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.leads.saveleadformdata') }}",
		cache: false,
		data: {"leadID": leadID, "fieldID": fieldID, "fieldValue": fieldValue},
		dataType : 'json'
	}).done(function(response){
		//pendingRequests--;
		//processButtons();
		if(response.status == 'error' && response.code == 23000){
			showError('Lead with this information already exists. Try again with new information.');
		}
	}).fail(function () {
		//pendingRequests--;
		//processButtons();
		showError('Something went wrong. Please try again later!');
	});
};

$('.preDefineformfields').blur(savefielddata);
$('.customDateField').change(savefielddata);


$('#campaignName').change(function(){
	var campaignID = $(this).val();

	blockUI('.contentpanel');

	$.ajax({
		type: 'post',
		url: "{{ URL::route('user.leads.selectedcampaignlead') }}",
		cache: false,
		data: {"campaignID": campaignID}
	}).done(function( response ) {
		var obj = response;

		if(obj.status == 'success'){
			location.href = obj.leadID;
		}
		else {
			showError("Unable to get a lead for this campaign");
		}

		unblockUI('.contentpanel');
	});
});

jQuery('#timepicker1').timepicker();
//jQuery("#date1").mask("99-99-9999");
jQuery('#timepicker2').timepicker();
//jQuery("#date2").mask("99-99-9999");
jQuery('#date1').datepicker({
	dateFormat: 'dd-mm-yy',
	minDate: new Date()
});
jQuery('#date2').datepicker({
	dateFormat: 'dd-mm-yy',
	minDate: new Date()
});

function showBookAppointmentModal(){
	$('#bookingButton').addClass('hidden');
	$('#bookedModel').modal('show');
}

$(function() {
	$('#bookedModel').find('#chkBoxSendEmailToLead').prop('checked', false);
	$('#bookedModel').find('#chkBoxSendEmailToSalesman').prop('checked', false);
});

function submitBooking() {
	var leadID = $('#leadID').val();
	var salesmanID = $('#bookedModel').find('#salesmanID').val();
	var date1 = $('#bookedModel').find('#date1').val();
	var timepicker1 = $('#bookedModel').find('#timepicker1').val();

    @if($emailFieldExists)
    var emailExists = true;
    @else
    var emailExists = false;
    @endif

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
		$('#error1').html('<div class="alert alert-info">Please wait...</div>');
		$('#bookedModel').find('#bookingButton').attr('disabled', 'disabled');

		pendingRequests++;
		processButtons();

		$.ajax({
		  type: 'post',
		  url: "{{ URL::route('user.leads.bookthissalesman') }}",
		  cache: false,
		  dataType: "json",
		  data: {
			  "leadID": leadID
			  , "salesmanID": salesmanID
			  , "date1": date1
			  , "timepicker1": timepicker1
			  , 'emailExists' : emailExists
			  , 'sendEmailToLead' : $('#bookedModel').find('#chkBoxSendEmailToLead').is(":checked")
			  , 'sendEmailToSalesman' : $('#bookedModel').find('#chkBoxSendEmailToSalesman').is(":checked")
		  }
		}).done(function (response) {
			pendingRequests--;
			processButtons();

			if(response.status == "success") {
				previousBookingValue = 'Yes';
				$('#bookedModel').modal('hide');
				$('#appointmentViewDiv').removeClass('hidden');
				$('#bookedModel').find('#error1').html('');
			}
			else {
				$('#error1').html('<div class="alert alert-danger">' + response.message + '</div>');
				$('#bookedModel').find('#bookingButton').removeAttr('disabled');
			}
		});
	}
}

$('#leadInterest').change(function() {
	var leadID = $('#leadID').val();
	var intertestType = $(this).val();

	//pendingRequests++;
	//processButtons();
	$.ajax({
	  type: 'post',
	  url: "{{ URL::route('user.leads.changeleadinterest') }}",
	  cache: false,
	  data: {"leadID": leadID, "intertestType": intertestType}
	}).done(function() {
		//pendingRequests--;
		//processButtons();
	}).fail(function () {
		//pendingRequests--;
		//processButtons();
		showError('Something went wrong. Please try again later!');
	});
});

$('#emailTemplateID').change(function() {
	var leadID = $('#leadID').val();
	var emailTemplateID = $(this).val();
	resetEmailDropdown = false;

	if(emailTemplateID == "writeNew") {
		$('#sendEmailModel').find('#emailError').html('');
		$('#sendEmailModel').find('#subject').val('');
		$('#sendEmailModel').find('#email_cc').val('');
		$('#sendEmailModel').find('#email_bcc').val('');
		$('#sendEmailModel').find('#divBccField').addClass('hidden');
		$('#sendEmailModel').find('#aShowBcc').removeClass('hidden');
		$('#email_cc').importTags('');
		$('#email_bcc').importTags('');
		CKEDITOR.instances.ckeditor.setData('');
		$('#sendEmailModel').find('#ckeditor').val('');
		$('#sendEmailModel').find('#fileDetails').html('');
		$('#sendEmailModel').find('#fileError').addClass('hidden');
		$('#sendEmailModel').find('#emailAttachments').val('');
		$('#sendEmailModel').modal('show');
		resetEmailDropdown = true;
	}
	else {
		pendingRequests++;
		processButtons();
		$.ajax({
		  type: 'post',
		  url: "{{ URL::route('user.leads.changeemailtemplate') }}",
		  cache: false,
		  data: {"leadID": leadID, "emailTemplateID": emailTemplateID}
		}).done(function () {
			pendingRequests--;
			processButtons();
			if(emailTemplateID != '') {
				$('#editAndSendEmailTemplate').removeClass('hidden');
			} else {
				$('#editAndSendEmailTemplate').addClass('hidden');
			}
		});
	}
});

$(function() {
	var emailTemplateID = $('#emailTemplateID').val();
	if(emailTemplateID != '') {
		$('#editAndSendEmailTemplate').removeClass('hidden');
	} else {
		$('#editAndSendEmailTemplate').addClass('hidden');
	}
});

function getCCEmailsFromFields() 
{
	var fieldsToSearchForEmails = [{{ $fieldsToSearchForEmails }}];

	//search for cc emails
	var allValues = '';

	for(var i = 0; i< fieldsToSearchForEmails.length; i++){
		allValues = allValues + $('input.preDefineformfields[rel="' + fieldsToSearchForEmails[i] + '"]').val()  + ' ';
	}
	allValues = allValues + $('#importLeadTXT').val();
	//console.log(allValues);

	//var additionalEmailsCC = allValues.match(/\S+@\S+/g);
	var additionalEmailsCC = allValues.match(/(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})/g);
	//console.log(additionalEmailsCC);

	return additionalEmailsCC;
}

$('#editAndSendEmailTemplate').click(function(){
	var emailTemplateID = $('#emailTemplateID').val();
	var leadID = $('#leadID').val();
	sendEmailWithTemplateId = 0;

	if(emailTemplateID == "writeNew") {
		showError('Please select template');
	} else {
		pendingRequests++;
		processButtons();
		$.ajax({
		  type: 'post',
		  url: "{{ URL::route('user.leads.getemailtemplatedata') }}",
		  cache: false,
		  data: {"leadID": leadID, "emailTemplateID": emailTemplateID}
		}).done(function (data) {
			pendingRequests--;
			processButtons();
			if(data.status != 'success') {
				showError('Error: ' + data.message);
			} else {
				$('#sendEmailModel').find('#emailError').html('');
				$('#sendEmailModel').find('#subject').val(data.subject);
				$('#sendEmailModel').find('#email_cc').val('');
				$('#sendEmailModel').find('#email_bcc').val('');
				$('#sendEmailModel').find('#divBccField').addClass('hidden');
				$('#sendEmailModel').find('#aShowBcc').removeClass('hidden');
				$('#email_cc').importTags('');

				var additionalEmailsCC = getCCEmailsFromFields();
				if(additionalEmailsCC) {
					$('#email_cc').importTags(additionalEmailsCC.join());
				}
				$('#email_bcc').importTags('');
				CKEDITOR.instances.ckeditor.setData(data.text);
				$('#sendEmailModel').find('#ckeditor').val('');
				$('#sendEmailModel').find('#fileDetails').html('');
				$('#sendEmailModel').find('#fileError').addClass('hidden');
				$('#sendEmailModel').find('#emailAttachments').val('');
				if(typeof data.attachments != 'undefined') {
					for (var i = 0, file; file = data.attachments[i]; ++i) {
						addAttachmentFile(file.file, file.icon, file.size);
					}
				}
				sendEmailWithTemplateId = emailTemplateID;

				$('#sendEmailModel').modal('show');
			}
		}).fail(function () {
			pendingRequests--;
			processButtons();
			showError('Something went wrong. Please try again later!');
		});
	}
});

function addTextToCKeditor(fieldName) {
	CKEDITOR.instances.ckeditor.insertText('##'+fieldName+'##');
}

$('#emailCancleButton').click(function(){
	if(resetEmailDropdown) {
		$('#emailTemplateID').val("").trigger('change');
	}
	emailFormData = new FormData();
	filesToBeDeleted = [];
	sendEmailWithTemplateId = 0;
});

function showBCC()
{
	$('#sendEmailModel').find('#divBccField').removeClass('hidden');
	$('#sendEmailModel').find('#aShowBcc').addClass('hidden');
}

function sendEmail() {
	var emailText = CKEDITOR.instances.ckeditor.getData();
	var subject = $("#subject").val();
	var leadID = $('#leadID').val();
	var email_cc = $("#email_cc").val();
	var email_bcc = $("#email_bcc").val();

	if(emailText == "") {
		$('#emailError').html('<div class="alert alert-danger">Please enter body of email</div>');
	}
	else if(subject == "") {
	   $('#emailError').html('<div class="alert alert-danger">Please enter subject</div>');
	}
	else {
		$('#EmailSendButton').attr('disabled', 'disabled');
		blockUI('#sendEmailModel');

		emailFormData.append('leadID', leadID);
		emailFormData.append('emailText', emailText);
		emailFormData.append('subject', subject);
		emailFormData.append('email_cc', email_cc);
		emailFormData.append('email_bcc', email_bcc);

		emailFormData.append('fileDeleted', JSON.stringify(filesToBeDeleted));

		if(sendEmailWithTemplateId != 0) {
			emailFormData.append('emailTemplateID', sendEmailWithTemplateId);
		}

		$.ajax({
			method: 'POST',
			url: "{{URL::route('user.leads.sendnewemail')}}",
			dataType: "JSON",
			data: emailFormData,
			processData : false,
			contentType : false,
			success: function (obj) {
				unblockUI('#sendEmailModel');
				if(obj.status == "success") {
					resetEmailDropdown = true;
					$('#emailError').html('<div class="alert alert-success">'+obj.message+'</div>');
				}
				else if(obj.status == 'error'){
					$('#emailError').html('<div class="alert alert-danger">'+obj.message+'</div>');

					formData = new FormData();
					$('sendEmailModel').find('#fileDetails').html('');
					$('sendEmailModel').find('#fileError').html('');
				}

				$('#EmailSendButton').removeAttr('disabled');
			},
			error: function (xhr, textStatus, thrownError) {
				unblockUI('#sendEmailModel');
				$('#EmailSendButton').removeAttr('disabled');
				alert("There is some error. Try again!!");
			}
		});
	}
}

function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    if (i == 0) return bytes + ' ' + sizes[i];
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
};

function removeFile(fileName){
	$('#sendEmailModel').find('#fileDetails').find('p[rel="'+fileName+'"]').remove();
	filesToBeDeleted.push(fileName);
}

$('#leadReferenceNumber').blur(function() {
	var leadID = $('#leadID').val();
	var referenceNumber = $(this).val();

	//pendingRequests++;
	//processButtons();
	$.ajax({
	  type: 'post',
	  url: "{{ URL::route('user.leads.savereferencenumber') }}",
	  cache: false,
	  data: {"leadID": leadID, "referenceNumber": referenceNumber}
	}).done(function() {
		//pendingRequests--;
		//processButtons();
	}).fail(function () {
		//pendingRequests--;
		//processButtons();
		showError('Something went wrong. Please try again later!');
	});
});

		
$('#followUpCall').bind('focus',function(){
	if ($(this).is(":checked")) {
		previousFollowUpValue = 'Yes';
	}
	else {
		previousFollowUpValue = 'No';
	}
});

$('#followUpModalCancle').click(function () {
	if(previousFollowUpValue == 'No') {
		$("#followUpCall").prop('checked', false);
		$('#callBackViewDiv').hide();
	}
});

$('#followUpCall').click(function() {

	@if($user->userType == 'Single')
		$('#callBackUserID').val('{{$user->id}}');
		$('#callBackUserID').parent().parent().addClass('hidden');
	@endif

	if ($(this).is(":checked")) {
		$('#error2').html('');
	  	$("#followUpCallModel").modal('show');
	  	$('#callBackViewDiv a').removeClass('hide');
	  	$('#callBackViewDiv').show();
	}
	else {
		$("#followUpCall").prop('checked', false);
		$('#callBackViewDiv').hide();
		$('#date2').val('');
		$('#callBackUserID').val('');
		removeFollowUpCall();
	}
});

function showfollowUpCallModel() {
	$("#followUpCallModel").modal('show');
}

function removeFollowUpCall() {
	var leadID = $('#leadID').val();
	pendingRequests++;
	processButtons();
	$.ajax({
	  type: 'post',
	  url: "{{ URL::route('user.leads.removefollowupcall') }}",
	  cache: false,
	  data: {"leadID": leadID}
	}).done(function () {
		pendingRequests--;
		processButtons();
	});
}

$(function() {
	$('#followUpCallModel').find('#chkBoxFollowUpSendEmailToLead').prop('checked', false);
	$('#followUpCallModel').find('#chkBoxFollowUpSendEmailToMember').prop('checked', false);
});

function submitFollowUpCall() {
	var leadID = $('#leadID').val();
	var callBackUserID = $('#callBackUserID').val();
	var date2 = $('#date2').val();
	var timepicker2 = $('#timepicker2').val();

	@if($emailFieldExists)
		var emailExists = true;
	@else
		var emailExists = false;
	@endif

	if(callBackUserID == '') {
		$('#error2').html('<div class="alert alert-danger">Please Select a User</div>');
	} else if(date2 == '') {
		$('#error2').html('<div class="alert alert-danger">Please Enter Date</div>');
	} else if(timepicker2 == '') {
		$('#error2').html('<div class="alert alert-danger">Please Select Time</div>');
	} else {
		$('#error2').html('<div class="alert alert-info">Please wait...</div>');
		pendingRequests++;
		processButtons();

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.leads.followupcall') }}",
			cache: false,
			data: {
				"leadID": leadID
				, "callBackUserID": callBackUserID
				, "date2": date2
				, "timepicker2": timepicker2
				, 'emailExists' : emailExists
				, 'sendEmailToLead' : $('#followUpCallModel').find('#chkBoxFollowUpSendEmailToLead').is(":checked")
				, 'sendEmailToMember' : $('#followUpCallModel').find('#chkBoxFollowUpSendEmailToMember').is(":checked")
			}
		}).done(function (response) {
			pendingRequests--;
			processButtons();
			if(response.status == "success") {
				$('#followUpCallModel').modal('hide');
				$('#followUpCallModel').find('#error2').html('');
			} else if(response.status == "warn_already_scheduled") {
				$('#followUpCallModel').find('#error2').html('');
				bootbox.confirm("This user already has a follow up call scheduled on or near this time. Click OK to book it anyway", function(result) {
					if(result) {
						$('#error2').html('<div class="alert alert-info">Please wait...</div>');
						pendingRequests++;
						processButtons();

						$.ajax({
							type: 'post',
							url: "{{ URL::route('user.leads.followupcall') }}",
							cache: false,
							data: {
								"leadID": leadID
								, "callBackUserID": callBackUserID
								, "date2": date2
								, "timepicker2": timepicker2
								, 'emailExists' : emailExists
								, 'force' : true
								, 'sendEmailToLead' : $('#followUpCallModel').find('#chkBoxFollowUpSendEmailToLead').is(":checked")
								, 'sendEmailToMember' : $('#followUpCallModel').find('#chkBoxFollowUpSendEmailToMember').is(":checked")
							}
						}).done(function (response) {
							pendingRequests--;
							processButtons();
							if(response.status == "success") {
								$('#followUpCallModel').modal('hide');
								$('#followUpCallModel').find('#error2').html('');
							} else {
								$('#followUpCallModel').find('#error2').html('<div class="alert alert-danger">' + response.message + '</div>');
							}
						});
					}
				});

			} else {
				$('#followUpCallModel').find('#error2').html('<div class="alert alert-danger">' + response.message + '</div>');
			}
		});
	}
}

function goToAction(actiontype, referee) {
	var leadID = $('#leadID').val();
	var sendEmail = $("#emailTemplateID").val();

	if(actiontype == 'saveandexit') {
        var noError = checkRequiredField();
        if(noError == false){
            return false;
        }

        blockUI('.contentpanel');

		$.ajax({
		  type: 'post',
		  url: "{{ URL::route('user.leads.saveandexit') }}",
		  cache: false,
		  data: {"leadID": leadID, "sendEmailTemplate": sendEmail}
		}).done(function( response ) {
			unblockUI('.contentpanel');
			var obj = jQuery.parseJSON(response);

			if(obj.success == 'success') {
				location.href = obj.redirect_link;
			}
		});

	} else if(actiontype == 'exitwithoutsave') {
		//just redirect
		location.href = "{{ URL::route('user.campaigns.start') }}";
	} else if(actiontype == 'back') {
		if(referee != 'comebacklater') {
			var noError = checkRequiredField();
			if(noError == false) {
				return false;
			}
		}
        blockUI('.contentpanel');
		$.ajax({
		  type: 'post',
		  url: "{{ URL::route('user.leads.gotoaction') }}",
		  cache: false,
		  data: {"leadID": leadID, "actiontype": actiontype, "sendEmailTemplate": sendEmail}
		}).done(function( response ) {
			unblockUI('.contentpanel');
			var obj = jQuery.parseJSON(response);

			if(obj.success == 'success') {
				location.href = obj.leadID;
			}
			else {
				showError('No more leads in this campaign');
			}
		});
	}
	else if(actiontype == 'next') {
		if(referee != 'comebacklater') {
			var noError = checkRequiredField();
			if(noError == false) {
				return false;
			}
		}

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.leads.gotoaction') }}",
			cache: false,
			data: {"leadID": leadID, "actiontype": actiontype, "sendEmailTemplate": sendEmail},
			beforeSend: function () {
				blockUI('.contentpanel');
			}
		}).done(function( response ) {
			unblockUI('.contentpanel');
			var obj = jQuery.parseJSON(response);

			if(obj.success == 'success') {
				if(obj.redirect == "redirect") {
					location.href = "{{ URL::route('user.campaigns.start') }}";
				}
				else {
					location.href = obj.leadID;
				}
			}
			else if(obj.success == 'createnew') {
				location.href = "{{ URL::route('user.leads.createlead') }}";
			}
		});
	}
	else if(actiontype == 'skip') {
		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.leads.gotoaction') }}",
			cache: false,
			data: {"leadID": leadID, "actiontype": actiontype, "sendEmailTemplate": sendEmail},
			beforeSend: function () {
				blockUI('.contentpanel');
			}
		}).done(function( response ) {
			unblockUI('.contentpanel');
			var obj = jQuery.parseJSON(response);

			if(obj.success == 'success') {
				if(obj.redirect == "redirect") {
					location.href = "{{ URL::route('user.campaigns.start') }}";
				}
				else {
					location.href = obj.leadID;
				}
			}
			else if(obj.success == 'createnew') {
				location.href = "{{ URL::route('user.leads.createlead') }}";
			}
		});
	}
}

function skipLead() {
	@if($user->leads_page_skip_auto == 'Yes')
	goToAction('skip');
	@else
	$("#skipModel").modal('show');
	@endif
}

function updateLeadsPageSkipAuto() 
{
	var dlg = $('#skipModel');
	var skipAuto = dlg.find('#chkSkipAuto:checked').length !== 0;
	if(skipAuto) {
		$.post('{{ URL::route('user.leads.leads_page_skip_auto') }}')
			.fail(function() {
				showError('Something went wrong. Please try again later!');
			})
		;
	}
}

$('#skipAndDelete').click(function() {
	updateLeadsPageSkipAuto();

	var leadID = $('#leadID').val();

	blockUI('.contentpanel');

	$.ajax({
	  type: 'post',
	  url: "{{ URL::route('user.leads.skipandelete') }}",
	  cache: false,
	  data: {"leadID": leadID}
	}).done(function( response ) {
		unblockUI('.contentpanel');
		var obj = jQuery.parseJSON(response);

		if(obj.success == 'success') {
			location.href = obj.leadID;
		}
		else if(obj.success == 'finish') {
			location.href = "{{ URL::route('user.campaigns.start') }}";
		}
	});
});

$('#comeBackLater').click(function() {
	updateLeadsPageSkipAuto();

	goToAction('skip');
});

function viewSalesmanCalendar() {
    var url = '{{URL::route('user.leads.appointments')}}';

	var campaignID = $('#campaignName').val();

	url = url + '?campaign='+campaignID;

    $("#calenderModal").removeData('bs.modal').modal({
        remote: url,
        show: true
    });

    $('#calenderModal').on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html('Loading...');
        $(this).data('bs.modal', null);
    });

    return false;
}

function checkRequiredField() {
    var noError = true;
    $('.required').each(function() {
        var val = $(this).val();

		var id = $(this).attr('id');
		if(id == 'leadInterest') {
			if(val == '' || val == undefined || val == 'NotSet') {
				showInputError(this);
				noError = false;
			}
			else {
				hideErrors(this);
			}
		}
		else {
			if(val == '' || val == undefined) {
				showInputError(this);
				noError = false;
			}
			else {
				hideErrors(this);
			}
		}
    });

    return noError;
}

function hideErrors(thisInput) {
    var formGroup = $(thisInput).closest(".parentDiv");
    formGroup.removeClass("has-error");
    formGroup.find(".help-block").text('');
}

function showInputError(thisInput) {
    var formGroup = $(thisInput).closest(".parentDiv");
    formGroup.addClass("has-error");
    formGroup.find(".help-block").text('This field is required.');
}

function cancelAppointment(appointmentID) {
    blockUI('#bookedInfo');
    var leadID = $('#bookedInfo').find('#leadID').val();

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.leads.cancelappointment') }}",
        cache: false,
        data: {"leadID": leadID, "appointmentID": appointmentID}
    }).done(function( response ) {
        unblockUI('#bookedInfo');
        $('#bookedInfo').modal('hide');

        var obj = jQuery.parseJSON(response);
        //if(obj.success == 'success') {
            //location.reload();
            jQuery('#calendar').fullCalendar( 'refetchEvents' );
        //}
    });
}

function saveAppointment(appointmentID) {
    var leadID = $('#bookedInfo').find('#leadID').val();
    var salesmanID = $('#bookedInfo').find('#salesmanID').val();
    var date1 = $('#bookedInfo').find('#bookDate').val();
    var timepicker1 = $('#bookedInfo').find('#bookTimepicker').val();

    if(salesmanID == '') {
        $('#bookingError').html('<div class="alert alert-danger">Please Select a Salesman</div>');
    }
    else if(date1 == '') {
        $('#bookingError').html('<div class="alert alert-danger">Please Enter Date</div>');
    }
    else if(timepicker1 == '') {
        $('#bookingError').html('<div class="alert alert-danger">Please Select Time</div>');
    }
    else {
        blockUI('#bookedInfo');
        $.ajax({
            type: 'post',
            url: "{{ URL::route('user.leads.bookthissalesman') }}",
            cache: false,
            data: {"leadID": leadID, "salesmanID": salesmanID, "date1": date1, "timepicker1": timepicker1}
        }).done(function( response ) {
            unblockUI('#bookedInfo');
            $('#bookedInfo').modal('hide');
            //location.reload();
            jQuery('#calendar').fullCalendar( 'refetchEvents' );
        });
    }
}
</script>
@stop
