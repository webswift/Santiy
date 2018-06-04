@extends('layouts.dashboard')

@section('css')
	{!! Html::style('assets/css/jquery.datatables.css') !!}
@stop

@section('title')
	Export Data
@stop

@section('content')

<div class="pageheader"><h2><i class="fa fa-list"></i> Export Data</h2></div>

<div class="contentpanel">
	<div class="panel">
		<div class="panel-heading">
			<div class="panel-btns hidden" id="advanceSearchClose">
				<a href="javascript:void(0);" onclick="toggleSearch();" class="">×</a>
			</div>
			<h3 class="panel-title">Export Data</h3>
			<p>Search export or delete the leads you have uploaded to your account. Bewared that deleting a campaign will result in a irreversible loss of data.</p>
		</div><!-- panel-heading-->
		<div class="panel-body">
			<div id="error" ></div>
				<div class="search" id="normalSearch">
					<div class="form-group">
						<div class="col-sm-3">
							<select class="form-control mb15" id="campaignType" name="campaignType">
								<option value="Started">Active Campaign</option>
								<option value="Completed">Finished Campaign</option>
								<option value="All" selected>All Campaigns</option>
							</select>
						</div>
						<div class="col-sm-5">
							<select class="form-control mb15" id="exportType" name="exportType">
								<option value="all">Export all leads from selected campaigns</option>
								<option value="interested">Export positive leads from selected campaigns</option>
								<option value="notinterested">Export negative individuals from selected campaigns</option>
								<option value="outstanding">Export outstanding calls from selected campaigns</option>
								<option value="callsmade">Export calls made from selected campaigns</option>
								<option value="booked">Export appointments booked from selected campaigns</option>
							</select>
						</div>
						<div class="col-sm-3">
							<button onclick="exportToCSV()" class="btn btn-primary">Export Data</button>
						</div>
					</div>
				</div>

				<div class="search hidden" id="advanceSearch">
					<form method="post" id="advanceSearchForm">
					@if(sizeof($campaignLists) > 0)
						<div class="form-group">
							<div class="col-sm-3">
								<select class="form-control mb15 toBeSerialize" id="campaign" name="campaign">
									<option value="">Choose a campaign</option>
									@foreach($campaignLists as $data)
										<option value="{{ $data->id }}">{!! $data->name !!}</option>
                                    @endforeach
								</select>
							</div>

							<div class="col-sm-2">
								<div class="input-group">
									<input id="from" name="from" type="text" class="form-control toBeSerialize" placeholder="Date From">
									<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
								</div>
							</div>

							<div class="col-sm-2">
								<div class="input-group">
									<input id="to" type="text" class="form-control toBeSerialize" placeholder="Date To" name="to">
									<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
								</div>
							</div>

							<div class="col-sm-2">
								<select class="form-control mb15 toBeSerialize" id="leadType" name="leadType">
									<option value="All">All</option>
									<option value="Interested">Positive</option>
									<option value="NotInterested">Negative</option>
									<option value="Unreachable">Unreachable</option>
								</select>
							</div>

							<div class="col-sm-3">
								<select class="form-control mb15 toBeSerialize" id="teamMember" name="teamMember">
									<option value="All">All Team members</option>
									@foreach($teamMembers as $data)
										@if($user->userType == 'Multi' && $user->id == $data->id)
										@else
											<option value="{{$data->id}}">@if($user->id == $data->id){{'Me'}}@else{{$data->firstName. ' '. $data->lastName}}@endif</option>
										@endif
									@endforeach
								</select>
							</div>
						</div>

						<div class="form-group dropdown hidden">
							@foreach($campaignLists as $data)
								@if(sizeof($data['formFields']) > 0)
									<div id="campaign_{{$data->id}}" class="hidden">
										@for($i = 0; $i < sizeof($data['formFields']); $i++)
											@if($data['formFields'][$i]->type == 'dropdown')
												<div class="col-sm-2">
													<div class="ckbox ckbox-default">
														<input type="checkbox" value="Yes" id="{{$data->id.'_'.$data['formFields'][$i]->id}}" name="{{'check_'.$data['formFields'][$i]->fieldName}}">
														<label for="{{$data->id.'_'.$data['formFields'][$i]->id}}">{{$data['formFields'][$i]->fieldName}}</label>
													</div>
												</div>
												<div class="col-sm-3">
													<?php $values = explode(',', $data['formFields'][$i]->values) ?>
													<select name="{{$data['formFields'][$i]->fieldName}}" class="form-control input-sm">
														<option value="All">All</option>
														@foreach($values as $value)
														<option value="{{$value}}">{{$value}}</option>
														@endforeach
													</select>
												</div>
												<div class="col-sm-1"></div>
											@endif
										@endfor
									</div>
								@endif
							@endforeach
                        </div>

						<div class="form-group text hidden">
							<div class="col-sm-12">
								<div class="ckbox ckbox-default">
									<input type="checkbox" value="Yes" id="specificField" name="specificField">
									<label for="specificField">Export data from specific fields ONLY (Check the fields you want to include in your export)</label>
								</div>
							</div>
							@foreach($campaignLists as $data)
							@if(sizeof($data['formFields']) > 0)
								<div id="campaign_{{$data->id}}" class="hidden">
									@for($i = 0; $i < sizeof($data['formFields']); $i++)
										@if($data['formFields'][$i]->type == 'text' || $data['formFields'][$i]->type == 'date')
											<div class="col-sm-3">
												<div class="ckbox ckbox-default">
													<input type="checkbox" disabled value="Yes" id="{{$data->id.'_'.$data['formFields'][$i]->id}}" name="check_{{$data['formFields'][$i]->fieldName}}">
													<label for="{{$data->id.'_'.$data['formFields'][$i]->id}}">{{$data['formFields'][$i]->fieldName}}</label>
												</div>
											</div>
										@endif
									@endfor
								</div>
							@endif
						@endforeach
						</div>

						<div class="form-group">
							<div class="col-md-12">
								<div class="ckbox ckbox-default">
									<input class="toBeSerialize" type="checkbox" value="Yes" id="historicalNotes" name="historicalNotes">
									<label for="historicalNotes">Export historical notes</label>
								</div>
								<div class="ckbox ckbox-default">
									<input class="toBeSerialize" type="checkbox" value="Yes" id="appointmentBooked" name="appointmentBooked">
									<label for="appointmentBooked">Export leads with appointments booked</label>
								</div>
								<div class="ckbox ckbox-default">
									<input class="toBeSerialize" type="checkbox" value="Yes" id="attachments" name="attachments">
									<label for="attachments">Export any attachments with filtered leads</label>
								</div>
								<div class="ckbox ckbox-default">
									<input class="toBeSerialize" type="checkbox" value="Yes" id="contacted" name="contacted">
									<label for="contacted">Export total number of times contacted</label>
								</div>
								<div class="ckbox ckbox-default">
									<input class="toBeSerialize" type="checkbox" value="Yes" id="followupcalls" name="followupcalls">
									<label for="followupcalls">Export follow up calls</label>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-sm-12">
								<button type="button" id="advanceExportButton" class="btn btn-primary pull-right">Run Advanced Export</button>
							</div>
						</div>
					@endif
					</form>
				</div>

				<div class="form-group">
					<div class="col-sm-4">
						<button type="button" id="toggleButton" class="btn btn-success" title="Smart Filters" onclick="toggleSearch();"> Smart Filters </button>
					</div>
				</div>

		        	
            </div><!-- panel-body -->
        </div><!-- panel -->

        <div class="panel" id="listing">
        	<div class="panel-body">
	        	<div class="table-responsive" id="newCampaginData">
	                <table id="table1" class="table">
	                    <thead>
	                        <tr>
	                        	<th></th>
	                            <th>Campaign Name</th>
	                            <th>Created On</th>
	                            <th>Total Leads</th>
	                            <th>Calls Remaining</th>
	                            <!-- <th>Number of Interested</th> -->
	                        </tr>
	                    </thead>
	                    <tbody>
	                    	@if(sizeof($campaignLists) > 0)
	                    	@foreach($campaignLists as $campaignList)
	                            <tr>
	                            	<td>
	                            	<div class="ckbox ckbox-primary">
                                        <input type="checkbox" id="campaigndata_{{ $campaignList->id }}" value="{{ $campaignList->id }}" class="campaignCheckBox" >
                                        <label for="campaigndata_{{ $campaignList->id }}"></label>
                                      </div>
                                      </td>
	                                <td>{{ $campaignList->name }}</td>
	                                <td>{{ ($campaignList->timeStarted)?(\App\Http\Controllers\CommonController::formatDateForDisplay($campaignList->timeStarted)):'-' }}</td>
	                                <td>{{ $campaignList->totalLeads}}</td>
	                                <td>{{ $campaignList->callsRemaining}}</td>
	                            </tr>
	                        @endforeach
	                        @endif
	                    </tbody>
	                </table>
	            </div><!-- table-responsive -->
	        </div>
        </div><!-- panel -->

    </div><!-- contentpanel -->

@stop


@section('bootstrapModel')
	
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#table1').dataTable({
			"sPaginationType": "full_numbers",
			"bSort": false
		});

		jQuery(".dataTables_wrapper select").select2({
			minimumResultsForSearch: -1
		});

		jQuery('#from').datepicker({
			dateFormat: 'dd-mm-yy'
		});
		jQuery('#to').datepicker({
			dateFormat: 'dd-mm-yy'
		});


		@if($user->userType == 'Single')
			$('#teamMember').parent().addClass('hidden');
		@endif

		$('#advanceSearch').find('#campaign').change(function(){
			var val = $(this).val();
			if(val == ''){
				$('.dropdown, .text').addClass('hidden');
			}
			else{
				$('.dropdown, .text').removeClass('hidden');

				$('.dropdown').find('div[id^=campaign_]').addClass('hidden');
				$('.text').find('div[id^=campaign_]').addClass('hidden');

				//remove serialize class
				$('.dropdown').find('div[id^=campaign_]').find('input, select').removeClass('toBeSerialize');
				$('.text').find('div[id^=campaign_]').find('input, select').removeClass('toBeSerialize');


				$('.dropdown').find('div[id=campaign_'+val+']').removeClass('hidden');
                $('.text').find('div[id=campaign_'+val+']').removeClass('hidden');

				//add serialize class
				$('.dropdown').find('div[id=campaign_'+val+']').find('input, select').addClass('toBeSerialize');
				$('.text').find('div[id=campaign_'+val+']').find('input, select').addClass('toBeSerialize');
			}
		});

		$('#advanceSearch').find('#advanceExportButton').click(function(){
			var formData = $('#advanceSearch').find('.toBeSerialize').serialize();
			var val = $('#advanceSearch').find('#campaign').val();

			if(val == ''){
				return false;
			}

			var url = '{{URL::route('user.campaigns.exportcampaignsdata')}}';
			location.href  = url+"?type=advance&"+formData;
		});

		$('#specificField').change(function(){
			if(this.checked){
				$('.text').find('div[id^=campaign_]').find('input[type=checkbox]').prop('disabled', false);
				$('.text').find('div[id^=campaign_]').find('input[type=checkbox]').prop('checked', true);
			}
			else{
				$('.text').find('div[id^=campaign_]').find('input[type=checkbox]').prop('disabled', true);
				$('.text').find('div[id^=campaign_]').find('input[type=checkbox]').prop('checked', false);
			}
		});
	});
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}

<script type="text/javascript">
	$('#campaignType').change(function(){
		var selectedValue = $(this).val();

		$.ajax({
			type: 'post',
			url: "{{ URL::route('user.campaigns.ajaxlistcampagins') }}",
			cache: false,
			data: {"selectedValue": selectedValue},
			beforeSend: function() {
				$('#newCampaginData').html('<div class="alert alert-info">Loading...</div>');
			},
			success: function(data) {
				$('#newCampaginData').html(data);
			},
			error: function(xhr, textStatus, thrownError) {
				alert('Something went wrong. Please Try again later!');
			}
		});
	});

	function exportToCSV() {
		var exportType = $('#exportType').val();
		var campaigns = new Array();

		$('.campaignCheckBox:checked').each(function(){
			campaigns.push($(this).val());
		});

		var campaignsIDs = JSON.stringify(campaigns);

		if(campaigns.length == 0) {
			alert("Please Select Campaign before Export");
		}
		else {
			var url = '{{URL::route('user.campaigns.exportcampaignsdata')}}';
			location.href  = url+"?exportType="+exportType+"&campaigns="+campaignsIDs+'&type=simple';
		}
	}

	function toggleSearch(){
		$('.search').each(function(){
			if($(this).hasClass('hidden')){
				$(this).removeClass('hidden');

				var id= $(this).attr('id');
				toggleDiv(id);
			}
			else {
				$(this).addClass('hidden');
				toggleDiv(id);
			}
		});
	}

	function toggleDiv(id){
		if(id == 'advanceSearch'){
			$('#listing').addClass('hidden');
			$("#toggleButton").addClass('hidden');
			$('#advanceSearchClose').removeClass('hidden');
			return false;
		}
		else if(id == 'normalSearch'){
			$('#listing').removeClass('hidden');
			$("#toggleButton").removeClass('hidden');
			$('#advanceSearchClose').addClass('hidden');
			return false;
		}
	}
	</script>
@stop
