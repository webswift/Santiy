@extends('layouts.dashboard')

@section('title')
	Edit Email Template
@stop

@section('css')
{!! Html::style('assets/css/emailediting.css') !!}
@endsection

@section('content')

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
</style>

	<div class="pageheader"><h2><i class="fa fa-envelope-o"></i> Edit Template</h2></div>

    <div class="contentpanel">
    	<div class="panel panel-default">
    		<div class="panel-heading">
                <h3 class="panel-title">Edit Email Template</h3>
                <p>Use the editor below to create email templates. Once the templates have been created you can use them to send out at any time during calls. Click here for instructions on how to personalise your message.</p>
                <div id="error" ></div>
            </div><!-- panel-heading-->

            <div class="panel-body">
            	<form class="form-horizontal form-bordered">
            		@if(Session::has('errorMessage'))
            		<div class="alert alert-danger">
            			 <a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
            			{!! Session::get('errorMessage') !!}
            		</div>
            		@endif
            		<div id="error" ></div>
            		<div class="row">
            			<div class="col-md-12">
            				<div class="form-group">
            					<label class="control-label col-sm-2">Template Name</label>
            					<div class="col-sm-6">
            						<input type="text" id="templateName" class="form-control" value="{{ $templateDetails->name }}" @if($templateDetails->name == 'Follow Up Call' || $templateDetails->name == 'Appointment booked') disabled @endif/>
            					</div>
                                @if($templateDetails->name != 'Follow Up Call' && $templateDetails->name != 'Appointment booked')
                                    @if($user->userType == "Team" && $user->id == $templateDetails->creator)
                                        <div class="ckbox ckbox-primary mt10 col-sm-4">
                                            <input type="checkbox" id="shareWithTeam" @if ($user->manager == $templateDetails->owner)checked @endif/>
                                            <label for="shareWithTeam">Share template with team</label>
                                        </div><!-- rdio -->
                                    @endif
                                @endif
            				</div>
            			</div>
            		</div>
            		<div class="row">
            			<div class="col-md-12">
            				<div class="form-group">
            					<label class="control-label col-sm-2">Subject</label>
            					<div class="col-sm-6">
            						<input type="text" id="subject" class="form-control" value="{!! $templateDetails->subject !!}"/>
            					</div>
            				</div>
            			</div>
            		</div>
            		<div class="row">
            			<div class="col-md-12">
            				<div class="form-group">
            					<label class="control-label col-sm-2">Template</label>
            					<div class="col-sm-10">
            						<textarea id="ckeditor" class="form-control" rows="10">{!! $templateDetails->templateText !!}</textarea>
            					</div>
            				</div>
            			</div>
            		</div>
            		<div class="row">
						<div class="col-md-2 col-md-offset-2">
							<div class="fileUpload btn btn-primary"><span>Add Attachments</span>
							<input class="upload" type="file"></div>
						</div>
						<div class="col-md-8">
							<div class="form-group" id="fileDetails">
								@if(sizeof($attachments) > 0)
									@foreach($attachments as $data)
										<p class="col-md-4" rel="{{$data}}">
											<label class="pull-left" title="Remove File"><span class="glyphicon glyphicon-remove remove-file" onclick="removeFile('{{$data}}')"></span></label>
											<label class="wrapContent content"><span><i class="{{\App\Http\Controllers\CommonController::getIconForFileType(storage_path().'/attachments/email/'.$templateDetails->id.'/'.$data, 'path')}}"> </i> {{$data}}</span></label>
											<label class="pull-right"><span>{{\App\Http\Controllers\CommonController::formatBytes(File::size(storage_path().'/attachments/email/'.$templateDetails->id.'/'.$data))}}</span></label>
										</p>
									@endforeach
								@endif
							</div>
							<div class="row hidden" id="fileError">
								<div class="col-md-8 col-md-offset-2"><p class="text-danger" style="margin-left: 10px;"></p></div>
							</div>
						</div>
					</div>
            		<div class="row">
            			<div class="col-md-12">
            				<div class="form-group">
            					<div class="form-group">
            						<div class="col-sm-2">
            							<label class="control-label"> FormFields Names </label>
            						</div>
            						<div class="col-sm-10 mt10">
            							<div class= "col-sm-2">
            								<a href="javascript:void(0)" onclick="addTextToCKeditor('First Name')">First Name</a>
            							</div>
            							<div class= "col-sm-2">
            								<a href="javascript:void(0)" onclick="addTextToCKeditor('Last Name')">Last Name</a>
            							</div>
            							<div class= "col-sm-2">
            								<a href="javascript:void(0)" onclick="addTextToCKeditor('Company Name')">Company Name</a>
            							</div>
            							<div class= "col-sm-1">
            								<a href="javascript:void(0)" onclick="addTextToCKeditor('Address')">Address</a>
            							</div>
            							<div class= "col-sm-1">
            								<a href="javascript:void(0)" onclick="addTextToCKeditor('Email')">Email</a>
            							</div>
            							<div class= "col-sm-2">
            								<a href="javascript:void(0)" onclick="addTextToCKeditor('Post/Zip code')">Post/Zip code</a>
            							</div>
										<div class= "col-sm-1">
											<a href="javascript:void(0)" onclick="addTextToCKeditor('Agent')">Agent</a>
										</div>
            						</div>
            					</div>
            				</div>
            			</div>
            		</div>
            	</form>
            </div>
            <div class="panel-footer">
                <div class="col-sm-12">
                	<p class="pull-right">
		                <a href="{{ URL::route('user.email') }}" class="btn btn-default">Cancel</a>&nbsp;
		                <button onclick="previewTemplate()" class="btn btn-primary">Preview</button>&nbsp;
		                <button onclick="updateTemplate({{ $templateDetails->id }})" class="btn btn-success">Save</button>
		            </p>
              	</div>
            </div>
		</div><!-- panel-body -->
	</div><!-- panel -->
@stop


@section('bootstrapModel')
<!-- Email Preview model -->
<div id="emailPreview" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <div class="modal-header">
    <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    <div id="headerInfo"><h4 class="modal-title">Email Preview</h4></div>
   </div>
   <div class="modal-body">
    <div class="row">
     <div class="col-sm-12">
      <div class="form-group">
       <div id="emailPreviewContent"></div>
      </div>
     </div><!-- col-sm-12 -->
    </div><!-- row -->
   </div>
   <div class="panel-footer">
    <div class="row">
     <div class="col-sm-12">
      <button class="btn btn-xs btn-default pull-right" data-dismiss="modal">Close</button>
     </div>
    </div>
   </div><!-- panel-footer -->
  </div>
 </div>
</div>
@stop

@section('modelJavascript')
{!! Html::script('assets/js/ckeditor/ckeditor.js') !!}
{!! HTML::script('assets/js/ckeditor/adapters/jquery.js') !!}

<script type="text/javascript">

function previewTemplate() {
	var templateContent = CKEDITOR.instances.ckeditor.getData();

	$('#emailPreviewContent').html(templateContent);
	$('#emailPreview').modal('show');
}

var roxyFileman = "{{ URL::asset('assets/fileman') }}/";
$(function(){
	CKEDITOR.replace( 'ckeditor', {filebrowserBrowseUrl:roxyFileman +"/index.html",
                                             filebrowserImageBrowseUrl:roxyFileman+'/index.html?type=image',
                                     removeDialogTabs: 'link:upload;image:upload'});
});
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
<script type="text/javascript">
var formData = new FormData();
var maxFileSize = {!! $maxFileSize !!};
var filesToBeDeleted = [];

function updateTemplate(templateID) {
	var templateName    = $('#templateName').val();
	var subject         = $('#subject').val();
	var templateContent = CKEDITOR.instances.ckeditor.getData();
	var shareWithTeam = $("#shareWithTeam").is(":checked");

	if(templateName == '') {
		$('#error').html('<div class="alert alert-danger">Please enter email template name.</div>');
	}
	else if(templateContent == '') {
		$('#error').html('<div class="alert alert-danger">Please enter some email format.</div>');
	}
	else{
		blockUI('.contentpanel');
		formData.append('templateName', templateName);
		formData.append('subject', subject);
		formData.append('templateContent', templateContent);
		formData.append('shareWithTeam', shareWithTeam);
		formData.append('templateID', templateID);

		formData.append('fileDeleted', JSON.stringify(filesToBeDeleted));

		$.ajax({
			method: 'POST',
			url: "{{URL::route('user.email.updatetemplate')}}",
			dataType: "JSON",
			data: formData,
			contentType : false,
			processData : false,
			success: function (obj) {
				unblockUI('.contentpanel');
				if(obj.success == "success") {
					location.href = "{{ URL::route('user.email') }}";
				}
				else if(obj.success == 'error'){
					location.href = "{{ URL::route('user.email.edit', array($templateDetails->id)) }}";
				}
			},
			error: function (xhr, textStatus, thrownError) {
				unblockUI('.contentpanel');
				alert("There is some error. Try again!!");
			}
		});
	}
}

function addTextToCKeditor(fieldName) {
	CKEDITOR.instances.ckeditor.insertText('##'+fieldName+'##');
}

document.querySelector('input[type="file"]').addEventListener('change', function(e) {
  uploadFiles(this.files);
}, false);

function uploadFiles(files) {
	for (var i = 0, file; file = files[i]; ++i) {
		var fileType = file.type;
		var fileSize = file.size;
		var fileName = file.name;

		$('#fileError').addClass('hidden');

		var error = false;

		if(fileSize > (maxFileSize*1024*1024)){
			$('#fileError').find('p').html(file.name + ' cannot be attached. It exceeds the max file size limit '+maxFileSize+' MB. Try another file.');
            $('#fileError').removeClass('hidden');

            continue;
		}

		var extension = fileName.split('.').pop();

		if(extension == 'php' || extension == 'js' || extension == 'exe'){
			$('#fileError').find('p').html(file.name + ' cannot be attached. File extension js, php and exe are not allowed.');
			$('#fileError').removeClass('hidden');

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
			var str = '<p class="col-md-4" rel="'+file.name+'">' +
			 	'<label class="pull-left" title="Remove File"><span class="glyphicon glyphicon-remove remove-file" onclick="removeFile(\''+file.name+'\')"></span></label>' +
			 	 '<label class="wrapContent content"><span><i class="'+fileIcon+'"> </i> '+file.name+'</span></label>' +
			 	  '<label class="pull-right"><span>'+bytesToSize(file.size)+'</span></label>' +
			 	   '</p>';

			$('#fileDetails').append(str);
			formData.append('file[]', file, file.name);
		}
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
	$('#fileDetails').find('p[rel="'+fileName+'"]').remove();
	filesToBeDeleted.push(fileName);
}
</script>
@stop
