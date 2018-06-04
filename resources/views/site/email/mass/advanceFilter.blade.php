<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    <h4 class="modal-title">Advance Targeting</h4>
    <p>Improve targeting information by selecting from the options below.</p>
</div>
<form id="advanceFilterForm" class="form-horizontal form-bordered">
    <div class="modal-body">
        <div class="form-group">
            <label class="col-md-3 control-label">Outcome</label>
            <div class="col-md-8">
                <select name="outcome" id="outcome" class="form-control">
                    <option value="">Include all</option>
                    <option value="Interested">Positive</option>
                    <option value="NotInterested">Negative</option>
                    <option value="Unreachable">Unreachable</option>
                    <option value="NotSet">Not Set</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-3 control-label">Lead Type</label>
            <div class="col-md-8">
                <select name="leadType" id="leadType" class="form-control">
                    <option value="">Select</option>
                    <option value="all">All Team</option>
                    <option value="me">My Leads</option>
                </select>
            </div>
        </div>

        <div class="form-group">
			<label class="col-md-3 control-label">
				<input name="chkLastUpdatedLeads" id="chkLastUpdatedLeads" type="checkbox" value="">  Last Updated
			</label>
            <div class="col-md-4">
                <div class="input-group mb15">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    <input title="Form" type="text" placeholder="From" id="updateFrom" class="form-control" name="updateFrom" />
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group mb15">
                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                    <input title="To" type="text" placeholder="To" id="updateTo" class="form-control" name="updateTo" />
                </div>
            </div>
        </div>

		<div class="form-group fieldFilterTemplate hidden">
			<div class="col-sm-4">
				<select class="form-control selFieldFilterField">
					<option value="" data-type="">Custom Fields/Options</option>
					@if(sizeof($campaignFormFields) > 0)
						@foreach($campaignFormFields as $formField)
							<option class="option" value="{{$formField->fieldName}}" data-value="{{$formField->values}}" data-type="{{$formField->type}}">{!! $formField->fieldName !!}</option>
						@endforeach
					@endif
				</select>
			</div>
			<input type="hidden" class="inputFieldFilterType">
			<div class="col-sm-6 divFieldFilterValue">
				<input class="form-control " disabled="disabled">
			</div>
			<div class="col-sm-2 mt3">
				<button onclick="MassEmail.addNewFieldFilterSet();return false;" type="button" class="btn btn-default btn-sm" title="Add field filter"><i class="fa fa-plus"></i></button>
				<button onclick="MassEmail.removeFieldFilterSet(this);return false;" type="button" class="btn btn-default btn-sm" title="Remove field filter"><i class="fa fa-minus"></i></button>
			</div>
		</div>

		<input type="hidden" id="inputFieldFiltersCount" name="inputFieldFiltersCount">

		<div id="divFieldFiltersDynamic" > 
		</div>

		@if(count($templatesForSameCampaign) > 0)
        <div class="form-group">
			<div class="row">
				<label class="col-md-3 "><b>Advanced</b></label>
			</div>
			<div class="row" title="Send new emails to those who already received mass email">
				<label class="col-md-3 control-label">Followup email to : </label>
				<div class="col-md-8">
					<select name="selFollowMassEmailTemplate" id="selFollowMassEmailTemplate" class="form-control">
						<option value="">All leads</option>
						@foreach($templatesForSameCampaign as $templateForSameCampaign)
							<option value="{{ $templateForSameCampaign->id }}">{{ $templateForSameCampaign->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<fieldset id="fldsFollowMassEmailTemplate">
				<div class="row mt10" title="Send new emails to those who opened mass email">
					<label class="col-md-3 control-label text-left-forced">
						<input name="chkFilterOpenedEmails" id="chkFilterOpenedEmails" type="checkbox" value="">  Opened
					</label>

					<div class="col-md-5 col-md-offset-3">
						<select name="selFilterOpenedEmails" id="selFilterOpenedEmails" class="form-control">
							<option value="">Include all</option>
							<option value="0">Never opened</option>
							<option value="1">Opened 1 or more times</option>
							<option value="2">Opened 2 or more times</option>
							<option value="3">Opened 3 or more times</option>
							<option value="5">Opened 5 or more times</option>
						</select>
					</div>
				</div>

				<div class="row mt10" title="Send new emails to those who clicked mass email">
					<label class="col-md-3 control-label text-left-forced">
						<input name="chkFilterClickedEmails" id="chkFilterClickedEmails" type="checkbox" value="">  Clicked
					</label>

					<div class="col-md-5 col-md-offset-3">
						<select name="selFilterClickedEmails" id="selFilterClickedEmails" class="form-control">
							<option value="">Include all</option>
							<option value="0">Never clicked</option>
							<option value="1">Clicked 1 or more times</option>
							<option value="2">Clicked 2 or more times</option>
							<option value="3">Clicked 3 or more times</option>
							<option value="5">Clicked 5 or more times</option>
						</select>
					</div>
				</div>
			</fieldset>
			<div class="row mt10">
				<label class="col-md-12 text-danger">Note: Recepients that have unsubscribed or Bounced will not be included</label>
			</div>
		</div>
		@endif 

    </div>
    <div class="modal-footer">
        <button onclick="MassEmail.resetMassEmailsAdvanceFilter();" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<script>
'use strict';
MassEmail.resetMassEmailsAdvanceFilter = function () {
	@forelse($filter as $key => $value)
		@if(!is_array($value))
		$('input[id="{{$key}}"]').val("{{ $value }}");
		$('select[id="{{$key}}"]').val("{{ $value }}");
		$('input[id="{{$key}}"][type="checkbox"]').prop('checked', true);
		@endif
	@empty
	@endforelse

	MassEmail.chkLastUpdatedLeads_onClick.call($('#chkLastUpdatedLeads')[0]);
	@if(count($templatesForSameCampaign) > 0)
	$('#selFollowMassEmailTemplate').trigger('change');
	MassEmail.chkFilterOpenedEmails_onClick.call($('#chkFilterOpenedEmails')[0]);
	MassEmail.chkFilterClickedEmails_onClick.call($('#chkFilterClickedEmails')[0]);
	@endif 

	$('#divFieldFiltersDynamic').empty();
	MassEmail.advancedFiltersCount = 0;
	$('#inputFieldFiltersCount').val(0);
	@if(isset($filter['inputFieldFiltersCount']) && is_numeric($filter['inputFieldFiltersCount']) && $filter['inputFieldFiltersCount'] > 0)
		@for ($i = 0; $i < $filter['inputFieldFiltersCount']; $i++)
			@if(!isset($filter['inputFieldFilterValue_' . $i]) || !is_array($filter['inputFieldFilterValue_' . $i]))
			MassEmail.addExistsFieldFilterSet(
				"{{$filter['selFieldFilterField_' . $i] or ''}}",
				"{{$filter['inputFieldFilterValue_' . $i] or ''}}",
				"{{$filter['inputFieldFilterValueFrom_' . $i] or ''}}",
				"{{$filter['inputFieldFilterValueTo_' . $i] or ''}}"
			);
			@elseif(count($filter['inputFieldFilterValue_' . $i]) > 0)
			MassEmail.addExistsFieldFilterSet(
				"{{$filter['selFieldFilterField_' . $i] or ''}}",
				["{!!implode('","', $filter['inputFieldFilterValue_' . $i])!!}"],
				"{{$filter['inputFieldFilterValueFrom_' . $i] or ''}}",
				"{{$filter['inputFieldFilterValueTo_' . $i] or ''}}"
			);
			@endif
		@endfor
	@else

	@endif

	MassEmail.addNewFieldFilterSet();

	return false;
}
MassEmail.resetMassEmailsAdvanceFilter();

</script>
