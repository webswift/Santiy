@extends('layouts.dashboard')

@section('title')
	Create New Campaign
@stop

@section('content')
<div class="pageheader"><h2><i class="glyphicon glyphicon-edit"></i> Create a new campaign</h2></div>
<div class="contentpanel">
    <div class="panel" id="step1Panel">
        <div class="panel-heading">
            <h4 class="panel-title">Step 1 - Campaign Wizard</h4>
            <p>The wizard will guide you through a few simple steps to getting your campaign started</p>
        </div><!-- panel-heading-->

        <div class="panel-body form-horizontal">
            <div id="error"></div>
            @if(Session::has('message'))
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {!! Session::get('message') !!}
                </div>
            @endif
            <div class="form-group">
                <label class="col-sm-3 control-label">Campaign Name</label>
                <div class="col-sm-7">
                    <input type="text" id="campaignName" name="campaignName" class="form-control" />
                </div>
            </div>
            @if($user->userType === 'Team' || $user->userType === 'Multi')
                <div class="form-group">
                    <label class="col-sm-3 control-label">Add colleagues to campaign</label>
                    <div class="col-sm-7">
                        @if(sizeof($allManagerUsers) > 0)
                            @foreach($allManagerUsers as $allManagerUser)
                                @if ($user->id == $allManagerUser->id)
                                    {{--@if($user->userType != "Multi")--}}
                                        <div class="ckbox ckbox-default col-sm-3">
                                            <input type="checkbox" class="managerUsers" value="{{ $allManagerUser->id }}" id="chkuser{{ $allManagerUser->id }}">
                                            <label for="chkuser{{ $allManagerUser->id }}">Me</label>
                                        </div>
                                    {{--@endif--}}
                                @else
                                    <div class="ckbox ckbox-default col-sm-3">
                                        <input type="checkbox" class="managerUsers" value="{{ $allManagerUser->id }}" id="chkuser{{ $allManagerUser->id }}">
                                        <label for="chkuser{{ $allManagerUser->id }}">{{ $allManagerUser->firstName }}</label>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                        <div class="ckbox ckbox-default col-sm-3">
                            <input type="checkbox" id="managerAllUsers">
                            <label for="managerAllUsers">All</label>
                        </div>
                        <div class="clearfix" id="managerUser"></div>
                    </div>
                </div>
            @elseif($user->userType === 'Single')
                <input type="checkbox" class="managerUsers" value="{{ $user->id }}" checked="checked" style="display: none;" />
            @endif
            <div class="form-group">
                <label class="col-sm-3 control-label" for="formLayout">Select a Lead Form</label>
                <div class="col-sm-7">
                    <select id="formLayout" name="formLayout" class="form-control input-sm mb15">
						<option value="">Please select</option>
                        @if(sizeof($formLayouts) > 0)
                            @foreach($formLayouts as $formLayout)
                                <option value="{{ $formLayout->id }}">{{ $formLayout->formName }}</option>
                            @endforeach
                        @endif
						<option value="create_new">Create a new Lead/Contact form</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="landingForm">Associate a Web Form with this campaign?</label>
                <div class="col-md-1">
                    <div class="ckbox ckbox-default col" style="margin-top: 7px;">
                        <input type="checkbox" id="landingForm" name="landingForm">
                        <label for="landingForm"></label>
                    </div>
                </div>
                <div class="col-md-7 hidden" id="landingFormDiv">
					<div class="col-md-7">
						<select id="landingFormID" name="landingFormID" class="form-control input-sm mb15 pull-left">
							<option value="">Select Landing Form</option>
							@if(sizeof($landingForms) > 0)
								@foreach($landingForms as $landingForm)
									<option value="{{ $landingForm->id }}">{{ $landingForm->name }}</option>
								@endforeach
							@endif
						</select>
					</div>
					<button id="createMapping" disabled type="button" class="pull-left btn btn-info hidden" data-toggle="tooltip" data-placement="left" 
						title="Remap fields of landing and lead forms">MAP Form</button>
                   <div class="row hidden" id="mappingSuccessful">
                       <div class="col-md-7">
							<p class="text-center text-success">Mapping Successful</p>
                       </div>
                   </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-3"> </div>
                <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="hidden" id="loading">getting url...</p>
                            <span id="landingUrl"></span>  <i class="fa fa-chevron-circle-right hidden" id="embedBtn" style="cursor:pointer;"></i>
                        </div>
                    </div>
                   <div class="row">
                       <div class="col-md-12">
                           <textarea id="iFrameDiv" readonly class="form-control hidden"></textarea>
                       </div>
                   </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="autoReferenceGenerator">Auto Generate Reference Numbers</label>
                <div class="col-sm-7">
                    <div class="ckbox ckbox-default col" style="margin-top: 7px;">
                        <input type="checkbox" id="autoReferenceGenerator" name="autoReferenceGenerator">
                        <label for="autoReferenceGenerator"></label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="autoReferencePrefix">Reference Number Prefix <b>(Optional)</b></label>
                <div class="col-sm-7">
                    <input type="text" id="autoReferencePrefix" name="autoReferencePrefix" class="form-control" disabled />
                </div>
            </div>

            <h4>Advanced Options</h4>

			@include('site.campaigns.campaignEmailSettings')

            <div class="panel-footer">
                <div class="col-sm-6 col-sm-offset-3">
                    <button onclick="createCampaign()" class="btn btn-primary">Next</button>
                    <button class="btn btn-default" onclick="gotoStartCampaignPage();">Cancel</button>
                </div>
            </div>
        </div><!-- panel -->
    </div>

    <div class="panel" id="step2Panel" style="display: none">
        <div class="panel-heading">
            <h4 class="panel-title">Step 2 - Importing Leads</h4>
            <p>Choose whether to upload your leads OR copy and paste them from Microsoft Excel. We'll automatically remove duplicate entries for you and help you sort the data in the next step.</p>
        </div>
        <div class="panel-body form-horizontal">
            <div id="error1" ></div>
            {!! Form::open(array('route' => 'user.campaigns.save', 'id' => 'campaignCsvForm', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')) !!}
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="importLeadTXT">Copy/Paste from Excel or TXT file</label>
                    <div class="col-sm-7">
                        <textarea  id="importLeadTXT" name="importLeadTXT" class="form-control" rows="5"></textarea>
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Upload CSV File</label>
                    <div class="col-sm-7">
                        <input type="hidden" id="hiddenCampaignData" name="hiddenCampaignData" value="">
                        <input id="uploadedcsvFile" name="uploadedcsvFile" type="file" class="input-sm mb15" accept=".csv,.tsv">
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="industry">Industry</label>
                    <div class="col-sm-7">
                        <select id="industry" name="industry" class="form-control">
                        	@if(sizeof($industries) > 0)
                            @foreach($industries as $industry)
                            <option value="{!! $industry !!}">{!! $industry !!}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
        <div class="panel-footer">
            <div class="col-sm-6 col-sm-offset-3">
                <button onclick="showNotificationModel()" class="btn btn-warning btn-xs">I don't have either of above</button>
                <button id="step2CancleButton" class="btn btn-default btn-xs">Cancel</button>
                <button onclick="createAndStoreCsv()" class="btn btn-primary btn-xs">Next</button>
            </div>
        </div>
    </div>
</div>
@stop
@section('bootstrapModel')
<div id="secondModelNotification" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-m">
        <div class="alert alert-info fade in nomargin">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4>Notification</h4>
            <p>If you don't have any leads either on an excel spreadsheet or to paste into your campaign choosing this option will allow you to use the call manager on the "fly" saving your collected data into a spreadsheet for you.</p>
            <div class="form-group">
                <div class="col-sm-12">
                    {!! Form::open(array('route' => 'user.campaigns.savewithoutcsv', 'id' => 'campaignCsvForm1', 'method' => 'post', 'enctype' => 'multipart/form-data')) !!}
                        <input type="hidden" id="hiddenCampaignData1" name="hiddenCampaignData1" value="">
                    {!! Form::close() !!}
                </div>
            </div>
            <p>
                <button onclick="submitWithoutCsv()" class="btn btn-primary btn-xs">Choose this Option</button>
                <button data-dismiss="modal" class="btn btn-default btn-xs">Return to upload Page</button>
            </p>
        </div>
    </div>
</div>
@stop

@section('modelJavascript')

@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
{!! Html::script('assets/js/dialogs/campaignmailsettings.js') !!}

<script type="text/javascript">

var maxFileSize = {!! $maxFileSize !!};

var fileError = false;

$(function(){
    $('input[name=landingForm]').change(function(){
        if(this.checked){
            $('#landingFormDiv').removeClass('hidden');
        }
        else{
            $('#landingFormDiv').addClass('hidden');
			$('#landingFormID').val('');
			resetLandingPageUrl();
        }
    });

    $('#campaignName').blur(function(){
        if(!$('input[name=landingForm]').is(':checked')){
            return;
        }
        var campaignName = $('#campaignName').val();
        var landingFormID = $('#landingFormID').val();

        if(campaignName != undefined && campaignName != '' && campaignName != null && landingFormID != undefined && landingFormID != '' && landingFormID != null){
            getLandingPageUrl(campaignName, landingFormID);
        }
    });

	var checkFieldsMapping  = function() {
        var landingFormID = $('#landingFormID').val();
        var leadFormID = $('#formLayout').val();
		
		if(landingFormID != '' && leadFormID != '' && leadFormID != 'create_new'){
			$('#createMapping')
				.prop('disabled', true)
				.addClass('hidden');
			$('#mappingSuccessful').addClass('hidden');
			Landing2LeadFieldsMapping.edit(landingFormID, leadFormID, function() {
				$('#createMapping')
					.prop('disabled', false)
					.removeClass('hidden');
				$('#mappingSuccessful').removeClass('hidden');
			});
		} else {
			$('#createMapping')
				.prop('disabled', true)
				.addClass('hidden');
			$('#mappingSuccessful').addClass('hidden');
		}
	}
	
	$('#createMapping').click(checkFieldsMapping);
	$('#formLayout').change(function() {
        var leadFormID = $(this).val();
		if(leadFormID == 'create_new') {
			window.location.href = "{{ URL::route('user.forms.createoredit') }}";
		} else {
			checkFieldsMapping();
		}
	});

    $('#landingFormID').change(function(){
        if(!$('input[name=landingForm]').is(':checked')){
            return;
        }

        var campaignName = $('#campaignName').val();
        var landingFormID = $(this).val();
		
		resetLandingPageUrl();
		
        if(campaignName != undefined && campaignName != '' && campaignName != null && landingFormID != undefined && landingFormID != '' && landingFormID != null){
            getLandingPageUrl(campaignName, landingFormID);
        }
		checkFieldsMapping();
    });
	

    $('#embedBtn').on('click', function(){
        if($(this).hasClass('fa-chevron-circle-right')){
            $('#iFrameDiv').removeClass('hidden');
            $(this).removeClass('fa-chevron-circle-right');
            $(this).addClass('fa-chevron-circle-down');
        }
        else if($(this).hasClass('fa-chevron-circle-down')){
            $('#iFrameDiv').addClass('hidden');
            $(this).addClass('fa-chevron-circle-right');
            $(this).removeClass('fa-chevron-circle-down');
        }

    });
});

document.querySelector('input[type="file"]').addEventListener('change', function(e) {
  uploadFiles(this.files);
}, false);


function resetLandingPageUrl() {
    $('#landingUrl').html('');
    $('#embedBtn').addClass('hidden');
	$('#iFrameDiv').val('')
	$('#iFrameDiv').addClass('hidden');
	$('#embedBtn').addClass('fa-chevron-circle-right');
	$('#embedBtn').removeClass('fa-chevron-circle-down');
	$('#createMapping')
		.prop('disabled', true)
		.addClass('hidden');
}

function getLandingPageUrl(campaignName, landingFormID){
    $('#loading').removeClass('hidden');
	resetLandingPageUrl();
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.campaigns.getLandingPageUrl') }}",
        cache: false,
        data: {campaignName : campaignName, landingForm : landingFormID},
        success: function(response) {
            $('#loading').addClass('hidden');
            $('#landingUrl').html(response.url);
            $('#embedBtn').removeClass('hidden');
            $('#iFrameDiv').val('<iframe src="'+response.url+'" height="50" width="100"></iframe>');
        },
        error: function(xhr, textStatus, thrownError) {
            showError('Something went wrong. Please try again later!');
        }
    });
}

function uploadFiles(files) {
	for (var i = 0, file; file = files[i]; ++i) {
		var fileType = file.type;
		var fileSize = file.size;

		hideErrors();

		if(fileSize > (maxFileSize*1024*1024)){
			fileError = true;
            showFieldError(file.name + ' cannot be attached. It exceeds the max file size limit '+maxFileSize+' MB. Try another file.', 'uploadedcsvFile');

            continue;
		}
		else{
			fileError = false;
		}
	}
}

function createCampaign() {
	var campaignName 		= $('#campaignName').val();
	var formLayout   		= $('#formLayout').val();
	var autoReferencePrefix = $('#autoReferencePrefix').val();

    var isLandingLayout = $('input[name=landingForm]').prop('checked');

	hideErrors();

	if(campaignName == "") {
		showFieldError("Please enter campaign name", "campaignName");
		return false;
	}
	if(formLayout == "" || formLayout == 'create_new') {
		showFieldError("Please select Lead Form", "formLayout");
		return false;
	}
 
	if ($('.managerUsers').is(":checked") == false) {
		showFieldError('Please Select at least one user for Campaign', 'managerUser');
		return false;
	}

    if(isLandingLayout){
        if($('#landingFormID').val() == ''){
            showFieldError('Please Select at least one landing form Campaign', 'landingFormID');
            return false;
        }
    }

	if(!campaignEmailSettings.validate()) {
		return false;
	}

	$('#error').html('');
    importLeads();
}

function verifyEmailSettings(){
	hideErrors();

	blockUI('#step1Panel');
	campaignEmailSettings.veryfyEmailSettings(
		'{{URL::route('user.campaigns.verifyEmailSetting')}}', 
		function() {
			unblockUI('#step1Panel');
		}
	);
}

$('#autoReferenceGenerator').click(function(){
	var isAutoGeneratorChecked = $(this).val();

	if ($(this).is(":checked")) {
		$("#autoReferencePrefix").prop('disabled', false);
	}
	else {
		$("#autoReferencePrefix").prop('disabled', true);
	}
});

$('#managerAllUsers').click(function(){
	if ($(this).is(":checked")) {
		$(".managerUsers").prop('checked', true);
	}
	else {
		$(".managerUsers").prop('checked', false);
	}
});

function importLeads() {
	$('#step1Panel').hide();
	$('#step2Panel').show();
}

$('#step2CancleButton').click(function() {
	$('#step1Panel').show();
	$('#step2Panel').hide();
});

function gotoStartCampaignPage() {
	window.location.href = "{{ URL::route('user.campaigns.start') }}";
}

function createAndStoreCsv() {
	//Step2 Data
	var txtFileText 	= $('#importLeadTXT').val();
	var uploadCsvFile	= $('#uploadedcsvFile').val();
	var industry = $("#industry").val();

	var uploadedFileExtension = uploadCsvFile.split('.').pop();
	uploadedFileExtension = uploadedFileExtension.toLowerCase();

	$('#error1').html('');
	hideErrors();

	if(fileError){
		showFieldError('Please upload another CSV file', "uploadedcsvFile");
		return false;
	}

	if(uploadCsvFile == "" && txtFileText == "") {
		showFieldError("Please upload a file (OR enter text above)", "uploadedcsvFile");
	}
	else if(uploadCsvFile != "" && uploadedFileExtension != 'csv') {
		showFieldError('Please upload only CSV file', "uploadedcsvFile");
	}
	else if(industry == "" ) {
		showFieldError('Please select an Industry', "industry");
	}
	else{
		var campaignData = getStep1Data();
		campaignData['industry'] = industry;

		var usersID = new Array();

		$('.managerUsers:checked').each(function() {
			usersID.push({
				id: $(this).val()
			});
		});

		campaignData.allUsersIDs = usersID;
		campaignData = JSON.stringify(campaignData);
		$('#hiddenCampaignData').val(campaignData);
		$('#campaignCsvForm').submit();
	}
}

function showNotificationModel() {
	$('#secondModelNotification').modal('show');
}

function submitWithoutCsv() {
	var industry = $("#industry").val();

	var campaignData = getStep1Data();
	campaignData['industry'] = industry;
			
	var usersID = [];

	$('.managerUsers:checked').each(function() {
		usersID.push({
			id: $(this).val()
		});
	});

	campaignData.allUsersIDs = usersID;
	campaignData = JSON.stringify(campaignData);
	$('#hiddenCampaignData1').val(campaignData);
	$('#campaignCsvForm1').submit();
}

function getStep1Data(){
	var campaignName = $('#campaignName').val();
	var formLayout  = $('#formLayout').val();
	var autoReferencePrefix = $('#autoReferencePrefix').val();
	var autoReferenceGenerator = "No";
    var isLandingLayout = $('input[name=landingForm]').prop('checked');

	if($('#autoReferenceGenerator').is(":checked")) {
		autoReferenceGenerator = "Yes";
	}

	var formData = {
		'campaignName' : campaignName,
		'formLayout' : formLayout,
		'autoReferenceGenerator' : autoReferenceGenerator,
		'autoReferencePrefix' : autoReferencePrefix,
        'isLandingLayout' : isLandingLayout
	};

    if(isLandingLayout){
        formData['landingLayout'] = $('#landingFormID').val();
    }

	formData = campaignEmailSettings.fillFormData(formData);	

	return formData;
}
</script>

{{-- tools for dialog to map landing form fields to lead form fields --}}
<script>
    var mappingInfoUrl = '{{URL::route('user.form.landing.getMapping')}}';
    var setMappingUrl = '{{ URL::route('user.form.landing.setMapping') }}';
</script>
{!! HTML::script('assets/js/dialogs/mapleadlandingfields.js') !!}

@stop
