@extends('layouts.dashboard')

@section('css')
	{!! Html::style('assets/css/bootstrap-select.min.css') !!}
	{!! Html::style('assets/css/jquery.datatables.css') !!}

<style>
.ui-datepicker {
		z-index: 9000 !important;
}
#viewNotesModal .modal-body
{
	max-height:calc(100% - 166px);
	min-height:calc(100% - 166px);
	overflow-y: scroll;
}

#btnDeleteLeadFromNotes {
	margin-left:5px;
}

#btnEditLeadFromNotes {
	margin-left:0px;
}

#viewNotesModal .btn
{
	margin-top: 5px;
}

@media (min-width: 335px) {
#btnDeleteLeadFromNotes {
	margin-left:0px;
}
#btnEditLeadFromNotes {
	margin-left:5px;
}
}

@media (min-width: 488px) {
#viewNotesModal .modal-body
{
	max-height:calc(100% - 120px);
	min-height:calc(100% - 120px);
	overflow-y: scroll;
}
#btnDeleteLeadFromNotes {
	margin-left:5px;
}
#viewNotesModal .btn
{
	margin-top: 0px;
}
}

#viewNotesModal .modal-content
{
	height:100%;
}

#viewNotesModal .modal-dialog
{
	height:80%;
}
</style>
@stop

@section('title')
	Leads &amp; Contacts
@stop

@section('content')
<div class="pageheader">
	<h2><i class="fa fa-list"></i> Leads &amp; Contacts </h2>
</div>

<div class="contentpanel">
	<div class="panel">
		<div class="panel-heading">
			<div class="panel-btns">
				<a href="javascript:void(0);" class="minimize">−</a>
			</div>
			<h3 class="panel-title">Select Fields</h3>
			<p>Here is where you can view the leads that have already been actioned. You can also use this tool to edit, save, print and delete the leads.</p>
		</div>

		<div class="panel-body">
			<div class="form-group search" id="normalSearch">
				<div class="row">
            		<div class="col-sm-3">
            			<select class="form-control mb15" id="campaign" name="campaign">
                        	<option value="" selected>Choose a campaign</option>
                        	<option value="All">All Campaigns</option>
                        	@if(sizeof($campaignList) > 0)
								@foreach($campaignList as $campaign)
									<option value="{{$campaign->id}}">{{$campaign->name}}</option>
								@endforeach
                        	@endif
                        </select>
					</div>

					<div class="col-sm-3">
						<select class="form-control mb15" id="leadType" name="leadType">
                        	<option value="all">Display All</option>
                        	<option value="Interested">Positive</option>
                        	<option value="NotInterested">Negative</option>
                        	<option value="Unreachable">Unreachable</option>
                        	<option value="booked">Appointment Booked</option>
                        	<option value="unactioned">Unactioned Leads</option>
							<option value="custom_option">More Options</option>
                        </select>
						<div class="_leadTypeCustomReset" style="display: none;">
							<a href="/user/campaigns/leads">Reset Filters</a>
						</div>
                    </div>

                    <div class="col-sm-3">
						<select class="form-control mb15" id="callMade" name="callMade">
							<option value="0">Call made from beginning</option>
							<option value="1">Actioned in the past 1 Day</option>
							<option value="7">Actioned in the past 7 days</option>
						</select>
					</div>

					<div class="col-sm-3">
						<select class="form-control mb15" id="actionType" name="actionType" @if ($user->userType == "Single" || $user->userType == 'Multi') style="visibility: hidden" @endif>
							<option value="All">All Leads</option>
							<option value="my">My leads only</option>
						</select>
					</div>
				</div>
				<div class="row"><div class="alert alert-warning hidden">Please select a campaign first.</div></div>
			</div>



			<div class="form-horizontal">
			<div class="form-group">
				<div class="col-sm-5" id="divQuickSearch">
					<label class="col-sm-4 control-label" for="txtQuickSearch">Quick Search</label>
					<div class="col-sm-8">
						<input name="txtQuickSearch" id="txtQuickSearch" class="form-control" title="Type a keyword and press enter" placeholder="Type a keyword and press enter" />
						<span class="help-block"></span>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="col-sm-12">
					<!-- <button type="button" id="toggleButton" class="btn btn-success" title="Advance Search" onclick="toggleSearch();"><span class="glyphicon glyphicon-plus"></span> Smart Filters</button> -->
					</div>
				</div>
			</div>
			</div>
		</div>
	</div><!-- panel -->

	<div class="panel">
		<div class="panel-body">
			<div class="form-horizontal">
			<div class="form-group">
				<div class="col-sm-4">
					<select class="form-control mb15" id="action" name="action" title="Bulk Actions">
						<option value="">Bulk Actions</option>
						<option value="print">Print Selected Leads</option>
						<option value="savePDF">Save Selected Leads as PDF</option>
						<option value="saveDOCX">Save Selected Leads as DOC</option>
						<option value="delete">Delete Selected Leads</option>
						<option value="savePDFWithAttachment">Save selected leads as PDF with attachments</option>
						<option value="moveLeadsToOtherCampaign">Transfer record to another campaign</option>
					</select>
				</div>
				<div class="col-sm-4">
					<select class="form-control mb15" title="Per Page Results" id="perPageResult" name="perPageResult">
						<option value="10">10 per page</option>
						<option value="50">50 per page</option>
						<option value="100">100 per page</option>
					</select>
				</div>
				<div class="col-sm-4">
					<label class="control-label mb15">Total results: <span id="leadsTotalCount"></span></label>
				</div>
			</div>
			</div>

			<div id="tableDiv" class="row"></div>
		</div>
	</div><!-- panel -->
</div><!-- contentpanel -->


<div id="leadsAdvanceFilterModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<h4 class="modal-title">Smart Search</h4>
			</div>
			<div class="modal-body">

				<!-- -->
				<form action="#" class="_customCriteriaBoxForm">
				<div class="form-group search form-bordered _customCriteriaBox" id="">

					<div class="form-group _criteriaLine _customCriteria">
						<div class="col-sm-2 control-label checkboxLabel">
							<div class="ckbox ckbox-default">
								Fields & Menu's
							</div>
						</div>

						<div class="col-sm-8">
							<div class="col-sm-4 FormCriteriaDiv ">
								<select class="form-control _formCriteria"  name="formCriteria[]" rel="checkboxFormCriteria">
									<option value="reference" data-type="text">Reference Number</option>
									<option value="_outcome" data-type="outcome">Outcome</option>
									<option value="salespersonAppointment" data-type="appointment">Appointment booked with</option>
									@if ($user->userType !== "Single")
										<option value="lastCallMember" data-type="lastcallmember">Last Updated by</option>
									@endif
									@if(sizeof($formFields) > 0)
										@foreach($formFields as $formField)
											<option class="option" value="{{$formField->fieldName}}" data-value="{{$formField->values}}" id="{{$formField->campaignIDs}}" data-type="{{$formField->type}}">{!! $formField->fieldName !!}</option>
										@endforeach
									@endif
								</select>
							</div>
							<div class="col-sm-8 FormCriteriaDiv  formValueDiv">
								<input name="formValue[]" id="" class="form-control _formValue">
							</div>
						</div>

						<div class="col-sm-2 FormCriteriaDiv ">
							<button  type="button" class="btn btn-default btn-sm _btnAddFilter" title="Add field filter"><i class="fa fa-plus"></i></button>
							<button  type="button" class="btn btn-default btn-sm _btnDelFilter" title="Remove field filter"><i class="fa fa-minus"></i></button>
						</div>
					</div>

					<div class="form-group _criteriaLine">
						<div class="col-sm-2 control-label checkboxLabel">
							<div class="ckbox ckbox-default">
								Date Range
							</div>
						</div>
						<div class="col-sm-8 FormCriteriaDiv  formValueDiv">
							<div class="col-sm-5">
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
									<input id="dateRangeFrom" name="DateRangeFrom" type="text" class="form-control" rel="checkboxDateRange">
								</div>
							</div>

							<div class="col-sm-2 text-center"><label class="control-label">to</label></div>

							<div class="col-sm-5">
								<div class="input-group">
									<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
									<input id="dateRangeTo" name="DateRangeTo" type="text" class="form-control" rel="checkboxDateRange">
								</div>
							</div>
						</div>
						<div class="col-sm-2 FormCriteriaDiv "></div>

					</div>

				</div>
				</form>
				<!-- -->


			</div>
			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="_btnSearch">Search</button>
			</div>
		</div>
	</div>
</div>

@stop

@section('modelJavascript')
<script type="text/javascript">

var chkIndex = 0;

jQuery(document).ready(function() {


	@if ($recentlyUpdatedCampaignId != 0) 
		var selectedCampaignId = '{{ $recentlyUpdatedCampaignId }}';
	@else
		var selectedCampaignId = 'All';
	@endif

	$('#txtQuickSearch').val('');
	$('#normalSearch').find('#campaign').val(selectedCampaignId);
	$('#advanceSearch').find('#campaign').val('');

	toggleAdvanceSearchFormFields();
	displayCampaignWisePerson();
	toggleSearchCriteriaSelect('');

	initButtons();
	initCriteriaInputs();

    getLeadData(getCampaignLeadData());

	moveLeads2Campaign.init(
		'{{URL::route('user.leads.getMoveLeadsDlg')}}'
		, '{{URL::route('user.leads.getMoveMappingDlg')}}'
		, '{{URL::route('user.leads.doMoveLeads')}}'
		, 
			function() {
				$('#action').val('');
			}
		, 
			function() {
				getLeadData(getCampaignLeadData());
			}
		);

	$('#txtQuickSearch').keydown(function(e) {
		if(e.keyCode === 13) {
			var campaignID = $('#normalSearch').find('#campaign').val();
			hideErrors();
			if(campaignID != '') {
				var query = $(this).val();
				if(query !== "") {
					getLeadData(getCampaignLeadData());
				} else {
					showError('Please enter a query first');
					getLeadData(getCampaignLeadData());
				}
			} else {
				showError('Please select a campaign');
			}
		}
	});

	$('#tableDiv').on('click', '.pagination a', function(event) {
		blockUI('#tableDiv');
		event.preventDefault();

		if ( $(this).attr('href') != '#' ) {
			$("html, #tableDiv").animate({ scrollTop: 0 }, "fast");

			var url = $(this).attr('href');
			$.get(url, function(response) {
				$('#tableDiv').html(response);
				$('#leadsTotalCount').html($('#hiddenLeadsTotalCount').val());
				unblockUI('#tableDiv');
			});
		}
	});


	$('#campaign').change(function(){
		$('._customCriteriaBoxForm')[0].reset();
		$('._btnAddFilter').click();
		$('._btnDelFilter').each(function(){
			$(this).click();
		});
		$('#leadType').val('all');


	});

	$('#normalSearch').find('select').change(function() {
		var formData = getCampaignLeadData();
		var campaignID = $('#normalSearch').find('#campaign').val();

		hideErrors();

		if(campaignID != '') {

			if ( $('#leadType').val() == 'custom_option' ){


				if ( campaignID != 'All'){

					toggleAdvanceSearchFormFields();
					displayCampaignWisePerson();
					toggleSearchCriteriaSelect( campaignID );

					$('#leadsAdvanceFilterModal').modal('show');

					$('._leadTypeCustomReset').show();

				} else {
					showError('Please select a campaign');
					return false;
				}

			} else {

				$('._leadTypeCustomReset').hide();

				getLeadData(formData);

			}


		}
		else {
			showError('Please select a campaign');
			return false;
		}
	});



	$('#tableDiv').on('change', '#parentCheckBox', function() {
		var isCheck =  this.checked;
		if(isCheck) {
			$('.checkBox').prop('checked', true);
		}
		else {
			$('.checkBox').prop('checked', false);
		}
	});

	$('#advanceSearch').find('#campaign').change(function() {

		toggleAdvanceSearchFormFields();
		displayCampaignWisePerson();
		toggleSearchCriteriaSelect($(this).val());
	});

	jQuery('#dateRangeFrom').datepicker({
		dateFormat: 'dd-mm-yy'
	});
	jQuery('#dateRangeTo').datepicker({
		dateFormat: 'dd-mm-yy'
	});

	$('#advanceSearch').find('input[type=checkbox]').change(function() {
		var id = $(this).attr('id');
		id = id.replace('checkbox', '');

		if(this.checked) {
			$('#advanceSearch').find('.'+id+'Div').removeClass('invisible');
		}
		else {
			$('#advanceSearch').find('.'+id+'Div').addClass('invisible');
		}
	});

	$('#search').click(function() {
		hideErrors();

		var campaign = $('#normalSearch').find('#campaign').val();
		if(campaign == '' || campaign == undefined) {
			showError('Please select a campaign first');
			return false;
		}

		var filter = '', campaignStr = '', dateRange = '', lastCallMember = '', salespersonAppointment = '', formCriteria = '';
		var advancedSearchDiv = $('#advanceSearch');
		var callMade = $('#normalSearch').find('#callMade').val();

			campaignStr = 'campaign=' + encodeURIComponent( $('#normalSearch').find('#campaign').val() ) + '&callMade=' + callMade+ '&';


			advancedSearchDiv.find('[id^=checkbox]').each(function() {
			if(this.checked) {
				var id = $(this).attr('id');
				var controlID = $('[rel='+id+']').attr('id');

				if(controlID == 'from') {
					var from = $('#advanceSearch').find('#from').val();
					var to = $('#advanceSearch').find('#to').val();

					if(from != '' && from != undefined && to != '' && to != undefined){
						dateRange = 'from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) + '&';
					}
				}
				else if(controlID == 'lastCallMember') {
					var val = $('#advanceSearch').find('#lastCallMember').val();
					if(val != '' && val != undefined){
						lastCallMember = 'lastCallMember=' + encodeURIComponent(val) + '&';
					}
				}
				else if(controlID == 'salespersonAppointment') {
					var val = $('#advanceSearch').find('#salespersonAppointment').val();
					if(val != '' && val != undefined){
						salespersonAppointment = 'salespersonAppointment=' + encodeURIComponent(val) + '&';
					}
				}
				else if(controlID == 'formCriteria') {
					var criteriaControl = advancedSearchDiv.find('#formCriteria');
					var type = $('option:selected', criteriaControl).attr('data-type');
					var columnName = criteriaControl.val();
					if(type == 'date') {
						var formValueFrom = advancedSearchDiv.find('#formValueFrom').val();
						var formValueTo = advancedSearchDiv.find('#formValueTo').val();
						if(columnName != '' && columnName != undefined && ((formValueFrom != '' && formValueFrom != undefined) || (formValueTo != '' && formValueTo != undefined))){
							formCriteria = 'formCriteriaDate=' + encodeURIComponent(columnName) + 
								'&formValueFrom=' + encodeURIComponent(formValueFrom) + 
								'&formValueTo=' + encodeURIComponent(formValueTo) + '&';
						}
					} else {
						var formValue = advancedSearchDiv.find('#formValue').val();
						if(columnName != '' && columnName != undefined && formValue != '' && formValue != undefined){
							formCriteria = 'formCriteria=' + encodeURIComponent(columnName) + '&formValue=' + encodeURIComponent(formValue) + '&';
						}
					}
				}
			}
		});

		var filter = campaignStr + dateRange + lastCallMember + salespersonAppointment + formCriteria;
		filter = filter.substring(0, filter.length - 1);

		if(campaignStr == ''){
			showError('Please select a specific campaign');
			return false;
		}

        if($('.sort').hasClass('highlight')) {
            var sortDiv = $('.sort.highlight');
            var column = sortDiv.attr('data-name');
            var type = sortDiv.attr('data-type');

            filter = filter + '&column=' + column + '&type=' + type;
        }

		getLeadData(filter);

		$('#leadsAdvanceFilterModal').modal('hide');

	});



	$('#_btnSearch').click(function(){
		hideErrors();

		var campaign = $('#normalSearch').find('#campaign').val();
		if(campaign == '' || campaign == undefined) {
			showError('Please select a campaign first');
			return false;
		}

		var filter = 'campaign=' + encodeURIComponent(campaign);

		$('._customCriteriaBox ._customCriteria').each(function(){

			var type = $('option:selected', $(this).find('._formCriteria')).attr('data-type');

			var fieldName = $(this).find('.FormCriteriaDiv ._formCriteria').val();
			var line = $(this).find('.formValueDiv');
			var val = '';

			if ( fieldName != ''){

				switch(type){

					case 'lastcallmember':
					case 'appointment':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ) {
								filter += '&' + encodeURIComponent(fieldName) + '[]=' + encodeURIComponent($(this).val());
							}
						});
						break;

					case 'outcome':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ) {
								filter += '&' + encodeURIComponent(fieldName) + '[]=' + encodeURIComponent($(this).val());
							}
						});
						break;

					case 'lastCallMember':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ) {
								filter += '&' + encodeURIComponent(fieldName) + '[]=' + encodeURIComponent($(this).val());
							}
						});
						break;

					case 'dropdown':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ){
								filter += '&formCriteria[]=' + encodeURIComponent(fieldName) + '&formValue[]=' + encodeURIComponent( $(this).val() ) + '&formExact[]=1';
							}
						});
						break;

					case 'text':
						if ( $(line).find('input').val() != ''){
							filter += '&formCriteria[]=' + encodeURIComponent(fieldName) + '&formValue[]=' + encodeURIComponent( $(line).find('input').val() ) + '&formExact[]=0';
						}
						break;

					case 'date':

						var formValueFrom = $(line).find('input[name="formValueFrom[]"]').val();
						var formValueTo = $(line).find('input[name="formValueTo[]"]').val();
						if(fieldName != '' && fieldName != undefined && ((formValueFrom != '' && formValueFrom != undefined) || (formValueTo != '' && formValueTo != undefined))){
							filter += '&formCriteriaDate[]=' + encodeURIComponent(fieldName) +
									'&formValueFrom[]=' + encodeURIComponent( formValueFrom ) +
									'&formValueTo[]=' + encodeURIComponent( formValueTo );
						}
						break;

					default:
						break;
				}

			}


		});

		var from = $('#dateRangeFrom').val();
		var to = $('#dateRangeTo').val();
		if(from != '' && from != undefined && to != '' && to != undefined){
			filter += '&from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
		}

		getLeadData(filter);

		$('#leadsAdvanceFilterModal').modal('hide');

	});


	$('#action').change(function() {
		var val = $(this).val();

		var campaignId = "";

		if ($("#normalSearch").hasClass("hidden")) {
			campaignId = $('#advanceSearch').find('#campaign').val();
		}
		else {
			campaignId = $('#normalSearch').find('#campaign').val();
		}


		if(campaignId == '' || campaignId == 'All') {
			showError('Please select a specific campaign to export data');
			$(this).val("");
			return false;
		}

		if(val != '' && $('.checkBox:checked').length <= 0) {
			showError("Please select at least one lead");
			$(this).val("");
			return false;
		}

		if(val != '') {
			if(val == 'delete') {
				showConfirmDialog('leadAction(\'delete\', \'multiple\')');
			} else if(val == "moveLeadsToOtherCampaign") {
				moveLeadsToOtherCampaign(campaignId);
			} else {
				leadAction(val, 'multiple');
			}
		}
	});

	$('#perPageResult').change(function() {
		$('.search').each(function() {
			var id = $(this).attr('id');

			if(!$(this).hasClass('hidden')) {
				if(id == 'normalSearch') {
					var formData = getCampaignLeadData();

					if($('.sort').hasClass('highlight')) {
						var sortDiv = $('.sort.highlight');
						var column = sortDiv.attr('data-name');
						var type = sortDiv.attr('data-type');

						formData = formData + '&column=' + column + '&type=' + type;
					}
					var campaignID = $('#normalSearch').find('#campaign').val();

					hideErrors();

					if(campaignID != '') {
						getLeadData(formData);
					}
					else {
						showError('Please select a campaign');
						return false;
					}
				}
				else if(id == 'advanceSearch') {
					$('#advanceSearch').find('#search').trigger('click');
				}
			}
		});
	});

	$('#tableDiv').on('click', '.leadRow',function() {
		var id = $(this).parent().attr('id');
		var leadtype = $(this).parent().data('leadtype');
		id = id.replace('lead_', '');

		if(leadtype === 'actioned') {
			viewCallHistoryNotes(id);
		} else {
			bootbox.confirm("Opening this lead will mark it as Actioned. Do you wish to continue?", function(result) {
				if(result) {
					var url = "{{URL::route('user.leads.createlead', array('leadID'))}}";
					url = url.replace('leadID', id);

					window.open(url);
				}
			}); 
		}
	});



});

function initButtons(){

	var campaignID = $('#normalSearch').find('#campaign').val();
	$('._btnAddFilter').unbind();
	$('._btnDelFilter').unbind();

	$('._btnAddFilter').click(function(){

		chkIndex++;

		var insertHtml =
		'<div class="form-group _criteriaLine _customCriteria">'+
			'<div class="col-sm-2 control-label checkboxLabel">'+
				'<div class="ckbox ckbox-default">'+
					'Fields & Menu\'s'+
				'</div>'+
			'</div>'+
			'<div class="col-sm-8">'+
				'<div class="col-sm-4 FormCriteriaDiv">'+
					'<select class="form-control _formCriteria"  name="formCriteria[]" rel="checkboxFormCriteria">'+
						'<option value="reference" data-type="text">Reference Number</option>'+
						'<option value="_outcome" data-type="outcome">Outcome</option>'+
						'<option value="salespersonAppointment" data-type="appointment">Appointment booked with</option>'+
						@if ($user->userType == "Single")
						'<option value="lastCallMember" data-type="lastcallmember">Last Updated by</option>'+
						@endif
						@if(sizeof($formFields) > 0)
							@foreach($formFields as $formField)
								'<option class="option" value="{{$formField->fieldName}}" data-value="{{$formField->values}}" id="{{$formField->campaignIDs}}" data-type="{{$formField->type}}">{!! $formField->fieldName !!}</option>'+
							@endforeach
						@endif
					'</select>'+
				'</div>'+
				'<div class="col-sm-8 FormCriteriaDiv formValueDiv">'+
					'<input name="formValue[]" id="" class="form-control _formValue">'+
				'</div>'+
			'</div>'+
			'<div class="col-sm-2 FormCriteriaDiv">'+
				'<button  type="button" class="btn btn-default btn-sm _btnAddFilter" title="Add field filter"><i class="fa fa-plus"></i></button>'+
				'<button  type="button" class="btn btn-default btn-sm _btnDelFilter" title="Remove field filter"><i class="fa fa-minus"></i></button>'+
			'</div>'+
		'</div>';


		$( $('._customCriteria').last() ).after( insertHtml );

		toggleAdvanceSearchFormFields();
		displayCampaignWisePerson();
		toggleSearchCriteriaSelect( $('#normalSearch').find('#campaign').val() );


		initButtons();
		initCriteriaInputs();

	});

	$('._btnDelFilter').click(function(){
		if ( $('._customCriteria').length > 1 ){
			$(this).parent().parent().remove();
		}
	});

}

function initCriteriaInputs(){

	$('._formCriteria').unbind();

	$('._formCriteria').change(function(){

		var valueDiv = $(this).parent().parent().find('.formValueDiv');
		var type = $('option:selected', this).attr('data-type');

		if(type == 'text') {
			$( valueDiv ).html('<input name="formValue[]" id="" class="form-control _formValue">');
		} else if(type == 'date') {
			var newHtml = '<div class="col-sm-6">' +
					'<label class="col-sm-3 control-label" style="padding-top: 7px;padding-left: 0px;" for="formValueFrom">From&nbsp;</label>' +
					'<div class="col-sm-9 input-group">' +
					'<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' +
					'<input name="formValueFrom[]" id="" class="form-control customDateField _formValue"></div></div>' +
					'<div class="col-sm-6">' +
					'<label class="col-sm-2 control-label" style="padding-top: 7px;padding-left: 0px;" for="formValueTo">To</label>' +
					'<div class="col-sm-9 input-group">' +
					'<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' +
					'<input name="formValueTo[]" id="" class="form-control customDateField _formValue"></div></div>';
			$( valueDiv ).html(newHtml);

			activateCustomDateFields();

		} else if(type == 'dropdown') {
			var values = $('option:selected', this).attr('data-value').split(',');

			var newHtml = '<select name="formValue[]" id="" class="form-control selectpicker _formValue" multiple>';

			for(var i=0; i<values.length; i++) {
				newHtml += '<option value="'+values[i]+'">'+values[i]+'</option>';
			}
			newHtml += '</select>';

			$( valueDiv ).html(newHtml);

		} else if (type=='appointment'){

			var newHtml =
					'<select class="form-control selectpicker _formValue" multiple id="salespersonAppointment" name="salespersonAppointment[]" rel="checkboxAppointment">'+
//					'<option value="All" selected>All</option>'+
						@if(sizeof($salesPerson) > 0)
							@foreach($salesPerson as $person)
								'<option value="{{$person->id}}" id="{{$person->campaignID or 'All'}}">{{$person->firstName.' '.$person->lastName}}</option>'+
							@endforeach
						@endif
					'</select>';
			$( valueDiv ).html(newHtml);

		} else if (type=='lastcallmember') {

			var newHtml =
					'<select class="form-control selectpicker _formValue" multiple id="lastCallMember" name="lastCallMember[]" rel="checkboxLastCall">'+
						@if(sizeof($teamMembers) > 0)
						@foreach($teamMembers as $team)
							'<option value="{{$team->id}}">@if($team->id == $user->id){{'Me'}}@else{{$team->firstName.' '.$team->lastName}}@endif</option>'+
						@endforeach
						@endif
					'</select>';
			$( valueDiv ).html(newHtml);

		} else if (type=='outcome') {

			var newHtml =
					'<select class="form-control selectpicker _formValue" multiple id="outcome" name="outcome[]">'+
						'<option value="Interested" >Positive</option>'+
						'<option value="NotInterested" >Negative</option>'+
						'<option value="Unreachable" >Unreachable</option>'+
					'</select>';
			$( valueDiv ).html(newHtml);

		}


		$('._criteriaLine').find('.selectpicker').selectpicker();

	});
}

function initCheckBox(){
	return true;
//	$('._chk').unbind();
//	$('._chk').click(function(){
//		var dl = $(this).parent().parent().parent();
//		if ( this.checked ){
//			$(dl).find('.FormCriteriaDiv').removeClass('invisible');
//		} else {
//			$(dl).find('.FormCriteriaDiv').addClass('invisible');
//		}
//	});
}


function getCampaignLeadData(){

	var formData = '';
	var campaign = $('#normalSearch').find('#campaign').val();
	var leadType = $('#normalSearch').find('#leadType').val();
	var callMade = $('#normalSearch').find('#callMade').val();
	var actionType = $('#normalSearch').find('#actionType').val();

	if ( $('#leadType').val() == 'custom_option' ){

		var filter = 'campaign=' + encodeURIComponent(campaign);

		$('._customCriteriaBox ._customCriteria').each(function(){

			var type = $('option:selected', $(this).find('._formCriteria')).attr('data-type');

			var fieldName = $(this).find('.FormCriteriaDiv ._formCriteria').val();
			var line = $(this).find('.formValueDiv');
			var val = '';

			if ( fieldName != ''){

				switch(type){

					case 'lastcallmember':
					case 'appointment':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ) {
								filter += '&' + encodeURIComponent(fieldName) + '[]=' + encodeURIComponent($(this).val());
							}
						});
						break;

					case 'outcome':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ) {
								filter += '&' + encodeURIComponent(fieldName) + '[]=' + encodeURIComponent($(this).val());
							}
						});
						break;

					case 'lastCallMember':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ) {
								filter += '&' + encodeURIComponent(fieldName) + '[]=' + encodeURIComponent($(this).val());
							}
						});
						break;

					case 'dropdown':
						$(line).find('select > option:selected').each(function(){
							if ( $(this).val() != '' ){
								filter += '&formCriteria[]=' + encodeURIComponent(fieldName) + '&formValue[]=' + encodeURIComponent( $(this).val() ) + '&formExact[]=1';
							}
						});
						break;

					case 'text':
						if ( $(line).find('input').val() != ''){
							filter += '&formCriteria[]=' + encodeURIComponent(fieldName) + '&formValue[]=' + encodeURIComponent( $(line).find('input').val() ) + '&formExact[]=0';
						}
						break;

					case 'date':

						var formValueFrom = $(line).find('input[name="formValueFrom[]"]').val();
						var formValueTo = $(line).find('input[name="formValueTo[]"]').val();
						if(fieldName != '' && fieldName != undefined && ((formValueFrom != '' && formValueFrom != undefined) || (formValueTo != '' && formValueTo != undefined))){
							filter += '&formCriteriaDate[]=' + encodeURIComponent(fieldName) +
									'&formValueFrom[]=' + encodeURIComponent( formValueFrom ) +
									'&formValueTo[]=' + encodeURIComponent( formValueTo );
						}
						break;

					default:
						break;
				}

			}


		});

		var from = $('#dateRangeFrom').val();
		var to = $('#dateRangeTo').val();
		if(from != '' && from != undefined && to != '' && to != undefined){
			filter += '&from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
		}

		formData = filter + "&callMade="+callMade+'&actionType='+actionType;

	} else {

		formData = "campaign="+campaign+"&leadType="+leadType+"&callMade="+callMade+'&actionType='+actionType;
	}


	
	var quickSearch = $('#txtQuickSearch').val();
	if(quickSearch !== "") {
		formData += "&quickSearch=" + encodeURIComponent(quickSearch);
	}

	return formData;
}

function toggleSearch() {
	$('.search').each(function() {
		if($(this).hasClass('hidden')) {
			$(this).removeClass('hidden');
		}
		else {
			$(this).addClass('hidden');
		}
	});

	$("#toggleButton").find('span').toggleClass(function() {
		if ( $( this ).is( ".glyphicon-plus" ) ) {
			return "glyphicon-arrow-left";
		}
		else {
			return "glyphicon-plus";
		}
	});

	//copy selected campaign
	if(!$('#advanceSearch').hasClass('hidden')) {
		var campaign = $('#normalSearch').find('#campaign').val();
		if(campaign == "All") {
			campaign = "";
		}
		$('#advanceSearch').find('#campaign').val(campaign);
		toggleSearchCriteriaSelect(campaign);
		toggleAdvanceSearchFormFields();
		displayCampaignWisePerson();
		$('#divQuickSearch').hide();
	} else {
		$('#divQuickSearch').show();
	}
}

function showConfirmDialog(functionName) {
	$('#confirmButton').attr('onclick', functionName);
	$('#confirmationModal').modal('show');

	$('#viewNotesModal').modal('hide');
}

function viewLeadAttachments(leadID) {
	blockUI('.contentpanel');

	$.post('{{URL::route('user.leads.attachmentHistory')}}', {'leadID' : leadID}, function(response) {
		$('#viewAttachmentModal').find('.modal-body').html(response);
		$('#viewAttachmentModal').modal('show');

		unblockUI('.contentpanel');
	});
}

function deleteAttachedFile(attachedID){
	blockUI('#viewAttachmentModal .modal-body');
	$.post('{{URL::route('user.leads.deleteAttachment')}}', {'id' : attachedID}, function(){
		$('#attached_'+attachedID).remove();
		unblockUI('#viewAttachmentModal .modal-body');
	});
}

function viewCallHistoryNotes(leadID) {
	blockUI('.contentpanel');

	$.post('{{URL::route('user.leads.notesHistory')}}', {'leadID' : leadID}, function(response) {
		$('#viewNotesModal').find('.modal-body').html(response);
		$('#viewNotesModal').modal('show');
		$('#viewNotesModal').find('.modal-header').find('button').attr('rel', leadID);

		unblockUI('.contentpanel');
	});
}

function moveLeadsToOtherCampaign(campaignID)
{
	moveLeads2Campaign.start(campaignID);

    return false;
}

function leadAction(task, type) {
	var lead = [];

	if(type == 'single') {
		var leadID = $('#viewNotesModal').find('.modal-header').find('button').attr('rel');
		lead.push(leadID);
		$('#viewNotesModal').modal('hide');

		if(task == 'edit') {
			$.post('{{URL::route('user.leads.isLeadLocked')}}', {'leadID' : leadID}, function(response) {
				if(typeof response.status != "undefined" && response.status == "success") {
					var url = "{{URL::route('user.leads.createlead', array('leadID'))}}";
					url = url.replace('leadID', leadID);

					window.open(url);
				} else {
					var message = "Unknown error";
					if(typeof response.message != "undefined") {
						message = response.message ;
					}
					showError(message);
				}
			});
			return false;
		}
		else if(task == 'view') {
			var url = "{{URL::route('user.leads.viewlead', array('leadID'))}}";
			url = url.replace('leadID', leadID);

			window.open(url);
			return false;
		}
	}
	else if(type == 'multiple') {
		$('#tableDiv').find('.checkBox:checked').each(function(){
			var id = $(this).attr('id');
			lead.push(id.replace('check_', ''));
		});
	}

	if(lead.length <= 0) {
		return false;
	}

	if(task == 'delete') {
		$('#confirmationModal').modal('hide');
		blockUI('#tableDiv');
		$.get('{{URL::route('user.campaigns.leadActions')}}?task='+task+'&leads='+lead.join(), function(response){
			if(response.status == 'success'){
				for(var i=0; i<lead.length; i++){
					$('#lead_'+lead[i]).fadeOut(500);
				}
			}
			unblockUI('#tableDiv');
		}, 'json');
	}
	else if(task == 'print') {
		window.open('{{URL::route('user.campaigns.leadActions')}}?task='+task+'&leads='+lead.join());
		return false;
	}
	else {
		window.location.href = '{{URL::route('user.campaigns.leadActions')}}?task='+task+'&leads='+lead.join();
		return false;
	}
}

function getLeadData(formData) {
	blockUI('#tableDiv');
//alert(formData);
	formData = formData + '&perPage='+$('#perPageResult').val();

	$.get("{{URL::route('user.campaigns.getLeadData')}}?"+formData, function(response) {
		$('#tableDiv').html(response);
		$('#leadsTotalCount').html($('#hiddenLeadsTotalCount').val());
		unblockUI('#tableDiv');
	});
	return false;
}

function getPreviousColumn() {
	if(currentColumn > 0) {
		currentColumn = currentColumn - 1;

		$('th[class^=col_]').addClass('hidden');
		for(var i=0; i <= extraColumn; i++) {
			$('#tableDiv').find('.col_'+i).addClass('hidden');
		}

		$('.col_'+currentColumn).removeClass('hidden');
	}
}

function getNextColumn() {
	if(currentColumn < extraColumn) {
		currentColumn = currentColumn + 1;

		$('th[class^=col_]').addClass('hidden');

		for(var i=0; i < extraColumn; i++) {
			$('#tableDiv').find('.col_'+i).addClass('hidden');
		}

		$('.col_'+currentColumn).removeClass('hidden');

		if(currentColumn == extraColumn) {
			$('.col_'+currentColumn).find('.next').addClass('hidden');
		}
	}
}

function showExtraColumn(columnName) {
	for(var i=0; i <= extraColumn; i++) {
		if($('#tableDiv').find('.col_'+i).find('.sort').attr('data-name') == columnName) {
			currentColumn = i;

			$('th[class^=col_]').addClass('hidden');

			for(i=0; i < extraColumn; i++) {
				$('#tableDiv').find('.col_'+i).addClass('hidden');
			}

			$('.col_'+currentColumn).removeClass('hidden');

			if(currentColumn == extraColumn) {
				$('.col_'+currentColumn).find('.next').addClass('hidden');
			}
			break;
		}
	}
}

function toggleSearchCriteriaSelect(campaign) {

	if(campaign == '' || campaign == 'All' || campaign == undefined) {
		$('._formCriteria').find('.option').addClass('hidden');
		$('._formCriteria').find('.option').attr('disabled',true);
		$('._formCriteria').find('.option').hide();
	}
	else {
		$('._formCriteria').find('.option').addClass('hidden');
		$('._formCriteria').find('option[id~='+campaign+']').removeClass('hidden');
		$('._formCriteria').find('.option').attr('disabled',true);
		$('._formCriteria').find('option[id~='+campaign+']').attr('disabled',false);
		$('._formCriteria').find('.option').hide();
		$('._formCriteria').find('option[id~='+campaign+']').show();
//		$('._formCriteria').find('option').first().attr("selected", "selected");
//		$('#advanceSearch').find('.formValueDiv').html('<input name="formValue[]" id="" class="form-control _formValue">');
	}
}

function toggleAdvanceSearchFormFields() {
	var campaignID = $('#normalSearch').find('#campaign').val()

	if(campaignID == '' || campaignID == undefined || campaignID == 'All') {
		//disable fields
		$('#advanceSearch').find('input[type=checkbox][id!="checkboxCampaign"]').prop('checked', false);
		$('#advanceSearch').find('input[type=checkbox][id!="checkboxCampaign"]').trigger('change');
		$('#advanceSearch').find('input[type=checkbox][id!="checkboxCampaign"]').prop('disabled', true);
	}
	else {
		//enable fields
		$('#advanceSearch').find('input[type=checkbox]').prop('disabled', false);
	}
}

function displayCampaignWisePerson() {
	var campaignID = $('#advanceSearch').find('#campaign').val();
	$('#salespersonAppointment').find('option[value!="All"]').addClass('hidden');
	$('#salespersonAppointment').find('option[value!="All"]').attr('disabled',true);

	$('#salespersonAppointment').find('option[id="All"]').removeClass('hidden');
	$('#salespersonAppointment').find('option[id="All"]').attr('disabled',false);

	if(campaignID != '') {
		$('#salespersonAppointment').find('option[id='+campaignID+']').removeClass('hidden');
		$('#salespersonAppointment').find('option[id='+campaignID+']').attr('disabled',false);
	}
}

</script>
@stop

@section('javascript')
	{!! HTML::script('assets/js/custom.js') !!}
	{!! HTML::script('assets/js/bootbox.min.js') !!}
	{!! HTML::script('assets/js/custom_date_field.js') !!}
	{!! HTML::script('assets/js/dialogs/moveleads2campaign.js') !!}
	{!! HTML::script('assets/js/bootstrap-select.min.js') !!}
@stop

@section('bootstrapModel')
<div class="modal fade" id="leadOptions" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Actions</h4>
			</div>
			<div class="modal-body">
				<button type="button" onclick="leadAction('print', 'single')" class="btn btn-success">Print lead</button>
				<button type="button" onclick="leadAction('savePDF', 'single')" class="btn btn-success">Save lead as PDF</button>
				@if($user->userType == 'Multi')
					<button type="button" onclick="leadAction('view', 'single')" class="btn btn-success">View lead</button>
				@else
					<button type="button" onclick="leadAction('edit', 'single')" class="btn btn-success">Edit lead</button>
				@endif
				<button type="button" onclick="showConfirmDialog('leadAction(\'delete\', \'single\')')" class="btn btn-success">Delete lead</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Confirm?</h4>
			</div>
			<div class="modal-body">
				<p>Are you sure to delete selected lead(s)?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
			</div>
		</div>
	</div>
</div>

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
</div>

<div id="viewNotesModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="viewNotesModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<h4 class="modal-title">Call history notes</h4>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
	        	<div class="row">
                    <div class="col-sm-12" style="text-align:left">
						<button type="button" onclick="leadAction('print', 'single')" class="btn btn-sm btn-success">Print lead</button>
						<button type="button" onclick="leadAction('savePDF', 'single')" class="btn btn-sm btn-success">Save lead as PDF</button>
						@if($user->userType == 'Multi')
							<button type="button" id="btnEditLeadFromNotes" onclick="leadAction('view', 'single')" class="btn btn-sm btn-success">View lead</button>
						@endif
						<button type="button" id="btnEditLeadFromNotes" onclick="leadAction('edit', 'single')" class="btn btn-sm btn-success">Edit lead</button>
						<button type="button" id="btnDeleteLeadFromNotes"  onclick="showConfirmDialog('leadAction(\'delete\', \'single\')')" class="btn btn-sm btn-success">Delete lead</button>
						<button class="btn btn-primary btn-sm" style="float:right" data-dismiss="modal">Close</button>
                    </div>
	        	</div>
			</div>
		</div>
	</div>
</div>
@stop
