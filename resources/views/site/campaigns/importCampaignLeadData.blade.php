@extends('layouts.dashboard')

@section('title')
	Import Data
@stop

@section('content')
	<div class="pageheader">
        <h2><i class="glyphicon glyphicon-upload"></i> Import Data</h2>
    </div>

    <div class="contentpanel">
    	<div class="panel panel-default">
    		<div class="panel-heading">
    			<h3 class="panel-title">Import Data into Existing Campaigns</h3>
                <p>Search export or delete the leads you have uploaded to your account. Beware that deleting a campaign will result in a irreversible loss of data.</p>
                <div id="error" ></div>
                @if(Session::has('message'))
				<div class="alert alert-danger">
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
					{!! Session::get('message') !!}
				  </div>
				@endif
            </div><!-- panel-heading-->
             <div class="panel-body">
             	{!! Form::open(array('route' => 'user.campaigns.importcampaigndatatocsv', 'id' => 'campaignImportForm', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => "form-horizontal")) !!}
				<div class="form-group">
					<label class="col-sm-3 control-label">Choose a campaign</label>
					<div class="col-sm-7">
						<select class="form-control input-sm mb15" id="campaignName" name="campaignName">
							<option value="">Select a Campaign</option>
							@if(sizeof($userCampaignLists) > 0)
							@foreach($userCampaignLists as $userCampaignList)
								<option value="{{ $userCampaignList->id }}" @if($userCampaignList->id == $selectedCampaignID) selected="selected" @endif>{{ $userCampaignList->name }}</option>
							@endforeach
							@endif
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="uploadedcsvFile">Upload CSV file</label>
					<div class="col-sm-7">
						<input type="hidden" id="hiddenCampaignData" name="hiddenCampaignData" value="">
		                <input id="uploadedcsvFile" name="uploadedcsvFile" class="input-sm mb15" type="file" accept=".csv,.tsv">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Copy/Paste from excel or TXT file</label>
					<div class="col-sm-7">
						<textarea  id="importLeadTXT" name="importLeadTXT" class="form-control" rows="5"></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label"></label>
					<div class="col-sm-7">
						<b>Warning: </b> Please Choose one of the above options, using both at the same time will not work.
					</div>
				</div>
				{!! Form::close() !!}
			</div>
			<div class="panel-footer">
				<div class="col-sm-7 col-lg-offset-3">
					<button onclick="createAndStoreCsv()" class="btn btn-primary">Next</button>&nbsp;
					<a href="{{ URL::route('user.campaigns.start') }}" class="btn btn-default ">Cancel</a>
				</div>
			</div>
		</div><!-- panel-body -->
	</div><!-- panel -->
</div><!-- contentpanel -->
@stop


@section('bootstrapModel')
	
@stop

@section('modelJavascript')

@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
<script type="text/javascript">
var maxFileSize = {!! $maxFileSize !!};
var fileError = false;

document.querySelector('input[type="file"]').addEventListener('change', function(e) {
  uploadFiles(this.files);
}, false);

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

function createAndStoreCsv() {
	var campaignName 	= $('#campaignName').val();
	var txtFileText 	= $('#importLeadTXT').val();
	var uploadCsvFile	= $('#uploadedcsvFile').val();

	$('#error').html('');

	if(fileError){
		showFieldError('Please upload another CSV file', "uploadedcsvFile");
		return false;
	}

	if(campaignName == "") {
		$('#error').html('<div class="alert alert-danger">Please Select a Campaign Name.</div>');
	}
	else if(uploadCsvFile == "" && txtFileText == "") {
		$('#error').html('<div class="alert alert-danger">Please Enter Text Or upload a file.</div>');
	}
	else if(uploadCsvFile != "" && txtFileText != "") {
		$('#error').html('<div class="alert alert-danger">Either enter text Or upload a file.</div>');
	}
	else{
		$('#campaignImportForm').submit();
	}
}
</script>
@stop
