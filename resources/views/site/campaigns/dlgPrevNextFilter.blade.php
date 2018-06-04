@if(isset($renderDlgContent))
<div class="modal-header">
    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    <h4 class="modal-title">Lead distribution</h4>
    <p>Change lead distribution options.</p>
    <p>"Next" Lead will be selected based on a filter below.</p>
</div>
<input type="hidden" id="campaignID4PrevNextLeadFilter" name="campaignID4PrevNextLeadFilter" value="{{ $campaign->id }}">
<form id="frmPrevNextLeadFilter" class="form-horizontal form-bordered">
    <div class="modal-body">

		<div class="form-group">
			<label class="col-md-3 control-label">Set for </label>
			<div class="col-md-8">
				<select name="selFilterPerUser" id="selFilterPerUser" class="form-control">
					<option value="all" data-user="For all users">For all users</option>
					@forelse($perUserFilters as $perUserFilter)
						<option value="{{ $perUserFilter->user_id }}" data-user=" {{ $perUserFilter->firstName }} {{ $perUserFilter->lastName }}"
							> {{ $perUserFilter->firstName }} {{ $perUserFilter->lastName }}</option>
					@empty
					@endforelse
				</select>
			</div>
		</div>

        @forelse($formFields as $formField)
            <div class="form-group">
                <label class="col-md-3 control-label">{{ $formField->fieldName }}</label>
                <div class="col-md-8">
                    <select name="{{ $formField->fieldName }}" id="{{ $formField->fieldName }}" class="form-control">
                        <option value="">Include all leads</option>
                        <?php $values = explode(',', $formField->values); ?>
                        @forelse($values as $value)
                            <option value="{{ $value }}"> {{ $value }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
        @empty
        @endforelse

        <div class="form-group">
            <label class="col-md-3 control-label">Outcome</label>
            <div class="col-md-8">
                <select name="outcome" id="outcome" class="form-control">
                    <option value="">Include all leads</option>
                    <option value="Interested">Positive</option>
                    <option value="NotInterested">Negative</option>
                    <option value="Unreachable">Unreachable</option>
                    <option value="NotSet">Not Set</option>
                </select>
            </div>
        </div>

		<div class="form-group">
			<label class="col-sm-3 control-label" for="txtTextFilter">Text filter</label>
			<div class="col-sm-8">
				<input name="txtTextFilter" id="txtTextFilter" class="form-control" title="Show leads with next text" placeholder="Text filter" />
			</div>
		</div>

		@if(count($massEmailTemplates) > 0)
        <div class="form-group">
			<div class="row" title="Show leads where emails were sent">
				<label class="col-md-3 control-label">Select Mass Email</label>
				<div class="col-md-8">
					<select name="selMassEmailTemplate" id="selMassEmailTemplate" class="form-control">
						<option value="">Include all leads</option>
						@foreach($massEmailTemplates as $templateForSameCampaign)
							<option value="{{ $templateForSameCampaign->id }}">{{ $templateForSameCampaign->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<fieldset id="fldsFollowMassEmailTemplate">
				<div class="row mt10" title="Show leads who opened mass email">
					<label class="col-md-2 col-md-offset-1 control-label text-left-forced">
						<input name="chkFilterOpenedEmails" id="chkFilterOpenedEmails" type="checkbox" value="checked">  Opened
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

				<div class="row mt10" title="Show leads who clicked mass email">
					<label class="col-md-2 col-md-offset-1 control-label text-left-forced">
						<input name="chkFilterClickedEmails" id="chkFilterClickedEmails" type="checkbox" value="checked">   Clicked
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
        <button type="button" class="btn btn-info pull-left" id="btnResetAllFilters">Reset All</button>
        <button type="button" class="btn btn-info pull-left" id="btnResetPrevNextFilter">Reset</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Save</button>
    </div>
</form>

<script>
{{-- embed inside dialog returned by ajax --}}
'use strict';

filterPerUser = {};
@foreach($perUserFilters as $perUserFilter)
		@if(count(json_decode($perUserFilter->prevNextFilter)) > 0)
		filterPerUser[{{ $perUserFilter->user_id }}] = JSON.parse('{!! $perUserFilter->prevNextFilter !!}');
		@else
		filterPerUser[{{ $perUserFilter->user_id }}] = {};
		@endif
@endforeach
filterPerUser['all'] = @if(count($filter) )JSON.parse('{!! json_encode($filter) !!}') @else {} @endif;

//console.log(filterPerUser);

//init form
setFormWithUserFilter('all');
updateSelectWithUsers();

</script>

@else 
{{-- render top level functionality if(!isset($renderDlgContent)) --}}

<div id="prevNextLeadFilterModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
        </div>
    </div>
</div>

<script>
'use strict';
function editPrevNextLeadFilter(campaignID){
    var url = '{{URL::route('user.campaigns.getPrevNextLeadFilterDialog')}}'+'?campaignID='+campaignID;

	$("#prevNextLeadFilterModal .modal-content").html('');
    $("#prevNextLeadFilterModal").removeData('bs.modal').modal({
        remote: url,
        show: true
    });

    return false;
}

$('#prevNextLeadFilterModal').on('change', '#selMassEmailTemplate', function() {
	var val = $(this).val();
	$('#prevNextLeadFilterModal #fldsFollowMassEmailTemplate').prop('disabled', val === '');
	if(val === '') {
		$('#prevNextLeadFilterModal #chkFilterOpenedEmails').prop('checked', false);
		$('#prevNextLeadFilterModal #chkFilterClickedEmails').prop('checked', false);
		prevNextFilter_chkFilterOpenedEmails_clicked();
		prevNextFilter_chkFilterClickedEmails_clicked();
	}
});

function prevNextFilter_chkFilterOpenedEmails_clicked() {
	var chkBox = $('#prevNextLeadFilterModal #chkFilterOpenedEmails');
	if(chkBox.length > 0) {
		chkBox = chkBox[0];
	} else {
		alert('checkbox not found');
	}
	var selFilterOpenedEmails = $('#prevNextLeadFilterModal #selFilterOpenedEmails');
	selFilterOpenedEmails.prop('disabled', !chkBox.checked);
	if(chkBox.checked) {
		if(selFilterOpenedEmails.val() === '') {
			selFilterOpenedEmails.val('0');
		}
	} else {
		selFilterOpenedEmails
			.val('')
			;
	}
}
$('#prevNextLeadFilterModal').on('click', '#chkFilterOpenedEmails', prevNextFilter_chkFilterOpenedEmails_clicked);

function prevNextFilter_chkFilterClickedEmails_clicked() {
	var chkBox = $('#prevNextLeadFilterModal #chkFilterClickedEmails');
	if(chkBox.length > 0) {
		chkBox = chkBox[0];
	} else {
		alert('checkbox not found');
	}
	var selFilterClickedEmails = $('#prevNextLeadFilterModal #selFilterClickedEmails');
	selFilterClickedEmails.prop('disabled', !chkBox.checked);
	if(chkBox.checked) {
		if(selFilterClickedEmails.val() === '') {
			selFilterClickedEmails.val('0');
		}
	} else {
		selFilterClickedEmails
			.val('')
			;
	}
}
$('#prevNextLeadFilterModal').on('click', '#chkFilterClickedEmails', prevNextFilter_chkFilterClickedEmails_clicked);

$('#prevNextLeadFilterModal').on('click', '#btnResetPrevNextFilter', function(e) {
	resetFilterFormFresh();
	filterPerUser[currentFilterForUser] = {};
	updateSelectWithUsers();
});

$('#prevNextLeadFilterModal').on('submit', '#frmPrevNextLeadFilter', function(e) {
    e.preventDefault();
	var dlg = $('#prevNextLeadFilterModal');
	var form = $(this);

	var campaignID = dlg.find("#campaignID4PrevNextLeadFilter").val();

	if(campaignID == undefined || campaignID == ''){
		showError('Please select campaign');
		return false;
	}
	
	hideErrors();
	
	saveCurrentFilterForCurrentUser();

	var formData = {
		'campaignID' : campaignID
		, 'filters' : filterPerUser
	};

	blockUI(dlg);

	$.ajax({
		type: 'post',
		url: '{{URL::route('user.campaigns.setPrevNextLeadFilterDialog')}}',
		cache: false,
		data: formData,
		dataType: 'json',
		success: function(response) {
			unblockUI(dlg);
			if(response.status !== 'success'){
				showError(response.message);
			} else {
				dlg.modal('hide');
				showSuccess('Lead distribution updated successfully');
				if(typeof response.totalLeadsString !== 'undefined') {
					$('#trTotalLeads-' + campaignID).html(response.totalLeadsString);
				}
			}
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI(dlg);
			showError("Request failed: " + textStatus);
		}
	});
});

function resetFilterFormFresh() 
{
	var saveCurrUser = $('#prevNextLeadFilterModal #frmPrevNextLeadFilter #selFilterPerUser').val();
	$('#prevNextLeadFilterModal #frmPrevNextLeadFilter input[type!="checkbox"]').val('');
	$('#prevNextLeadFilterModal #frmPrevNextLeadFilter input[type="checkbox"]').prop('checked', false);
	$('#prevNextLeadFilterModal #frmPrevNextLeadFilter select').val('');
	$('#prevNextLeadFilterModal #frmPrevNextLeadFilter #selFilterPerUser').val(saveCurrUser);
	$('#prevNextLeadFilterModal #selFilterOpenedEmails').val('');
	$('#prevNextLeadFilterModal #selMassEmailTemplate').trigger('change');
	if($('#prevNextLeadFilterModal #chkFilterClickedEmails').length) {
		prevNextFilter_chkFilterOpenedEmails_clicked();
		prevNextFilter_chkFilterClickedEmails_clicked();
	}
}

var filterPerUser;
var currentFilterForUser = '';

function setFormWithUserFilter(userID) 
{
	if(typeof filterPerUser[userID] == 'undefined') {
		showError("Incorrect userID");
	}

	resetFilterFormFresh();

	var userFilter = filterPerUser[userID];
	for(var prop in userFilter) {
		$('#prevNextLeadFilterModal input[id="' + prop + '"]').val(userFilter[prop]);
		$('#prevNextLeadFilterModal select[id="' + prop + '"]').val(userFilter[prop]);
		$('#prevNextLeadFilterModal input[id="' + prop + '"][type="checkbox"]').prop('checked', true);
	}
	$('#prevNextLeadFilterModal #selMassEmailTemplate').trigger('change');
	if($('#prevNextLeadFilterModal #chkFilterClickedEmails').length) {
		prevNextFilter_chkFilterOpenedEmails_clicked();
		prevNextFilter_chkFilterClickedEmails_clicked();
	}
	currentFilterForUser = userID;
}

function saveCurrentFilterForCurrentUser() 
{
	//move data from form to filterPerUser
	var form = $('#prevNextLeadFilterModal #frmPrevNextLeadFilter');
	var filter = form.serializeArray();
	//console.log(filter);
	var filterOject = {};
	for(var prop in filter) {
		if(filter[prop].name != "selFilterPerUser") {
			filterOject[filter[prop].name] = filter[prop].value;
		}
	}
	//console.log(filterOject);
	filterPerUser[currentFilterForUser] = filterOject;
	updateSelectWithUsers();
}

$('#prevNextLeadFilterModal').on('change', '#selFilterPerUser', function() {
	//save current settings 
	saveCurrentFilterForCurrentUser();

	var val = $(this).val();
	setFormWithUserFilter(val);

});

$('#prevNextLeadFilterModal').on('click', '#btnResetAllFilters', function(e) {
	resetFilterFormFresh();
	for(var prop in filterPerUser) {
		filterPerUser[prop] = {};
	}
	updateSelectWithUsers();
});

function updateSelectWithUsers()
{
	for(var prop in filterPerUser) {
		var option = $('#prevNextLeadFilterModal #frmPrevNextLeadFilter #selFilterPerUser option[value="' + prop + '"]');
		if($.isEmptyObject(filterPerUser[prop])) {
			option.text(option.attr('data-user'));
		} else {
			//check deep
			var filtered = false;
			var filter = filterPerUser[prop];
			for(var filterprop in filter) {
				if(filter[filterprop] != "") {
					filtered = true;
					break;
				}
			}
			if(filtered) {
				option.text(option.attr('data-user') + ' - filtered');
			} else {
				option.text(option.attr('data-user'));
			}
		}
	}
	//refresh select
	//$('#prevNextLeadFilterModal #frmPrevNextLeadFilter #selFilterPerUser').val($('#prevNextLeadFilterModal #frmPrevNextLeadFilter #selFilterPerUser').val());
}

</script>


@endif
