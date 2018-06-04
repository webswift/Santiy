@extends('layouts.dashboard')

@section('title')
    Duplicate Mass Mail
@stop

@section('css')
{!! Html::style('assets/css/bootstrap-timepicker.min.css') !!}
{!! Html::style('assets/css/jquery.tagsinput.css') !!}
{!! Html::style('assets/css/bootstrap-datetimepicker.min.css') !!}
{!! Html::style('assets/css/bootstrap-select.min.css') !!}
{!! Html::style('assets/css/emailediting.css') !!}
{!! Html::style('assets/css/massmail.css') !!}
@endsection

@section('content')
<div class="pageheader"><h2><i class="fa fa-envelope-o"></i> Mass Mail Manager</h2></div>

<div class="contentpanel">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Create Email Template</h3>
            <p>Create your mass mail message here</p>
        </div>

        <form class="form-horizontal form-bordered" action="{{ URL::route('user.email.mass.store') }}" id="templateCreateForm">
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">From Name</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="From Name" id="from_name" value="{{ $template->from_name }}" name="from_name" class="form-control">
                        <span class="help-block"></span>
                    </div>
                </div>
                <input type="hidden" id="templateID" name="templateID" value="{{ $template->id }}">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Send From Address</label>
                    <div class="col-sm-8">
						<input type="email" placeholder="Send From Address" value="{{ $template->from_email }}" id="from_email" name="from_email" 
							data-toggle="tooltip" data-placement="auto" title="" 
							data-original-title="Please ensure the from email address is verified with your mail provider or your campaign won’t send"
							class="form-control">
                        <span class="help-block"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Reply-To</label>
                    <div class="col-sm-8">
                        <input type="email" placeholder="Reply To" value="{{ $template->reply_to }}" id="reply_to" name="reply_to" class="form-control">
                        <span class="help-block"></span>
                    </div>
                </div>
                <div class="form-group hidden">
                    <label class="col-sm-2 control-label">Select Dataset to send to:</label>
                    <div class="col-sm-8">
                        <select name="datasetType" id="datasetType" class="form-control">
                            <option value="">Select</option>
                            <option value="campaign"  selected="selected">Campaign</option>
                            <option value="custom">Import mailing list</option>
                        </select>
                        <span class="help-block"></span>
                    </div>
                </div>

                <div class="form-group campaignDiv hidden">
                    <label class="col-sm-2 control-label">Select Campaign</label>
                    <div class="col-sm-3">
                        <select name="campaign" id="campaign" class="form-control">
                            <option value="">Select</option>
                            @forelse($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" rel="{{ $campaign->status }}" >{{ $campaign->name }}</option>
                            @empty
                            @endforelse
                        </select>
                        <span class="help-block"></span>
                    </div>
                    <div class="col-md-2 text-danger hidden control-label" style="text-align: left" id="emailCountDiv">loading...</div>
                    <div class="col-md-2 hidden" id="advanceButton">
                        <button type="button" id="adButton" class="btn btn-default" title="Advance Options"><i class="fa fa-plus"></i></button>
                    </div>
                </div>

                <div class="form-group importDiv hidden">
                    <label class="col-sm-2 control-label">Select CSV File</label>
                    <div class="col-sm-3">
                        <input type="file" class="form-control" name="csvFile" id="csvFile">
                        <span class="help-block"></span>
                    </div>
                    <div class="col-md-2 text-danger hidden control-label" style="text-align: left" id="leadEmailCountDiv">loading...</div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Template Name</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="Template Name" value="{{ $template->name }}" id="name" name="name" class="form-control">
                        <span class="help-block"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Subject</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="Subject" value="{{ $template->subject }}" id="subject" name="subject" class="form-control">
                        <span class="help-block"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2">Template</label>
                    <div class="col-sm-8">
                        <textarea id="content" name="content"  value="{!! $template->content !!}" class="form-control" rows="10"></textarea>
                        <span class="help-block"></span>
                    </div>
                </div>

				@include('site.email.mass.addFormFieldsToTemplate')

                <div class="form-group">
                    <div class="col-md-2 col-md-offset-2">
                        <div class="fileUpload btn btn-primary">
                            <span>Add Attachments</span>
                            <input class="upload" type="file" name="attachmentFiles">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group" id="fileDetails">
                            @if(sizeof($attachments) > 0)
                                @foreach($attachments as $data)
                                    <p class="col-md-4" rel="{{$data}}">
                                        <label class="pull-left" title="Remove File"><span class="glyphicon glyphicon-remove remove-file" onclick="MassEmail.removeFile('{{$data}}')"></span></label>
                                        <label class="wrapContent content"><span><i class="{{\App\Http\Controllers\CommonController::getIconForFileType(storage_path().'/attachments/massmail/'.$template->id.'/'.$data, 'path')}}"> </i> {{$data}}</span></label>
                                        <label class="pull-right"><span>{{\App\Http\Controllers\CommonController::formatBytes(File::size(storage_path().'/attachments/massmail/'.$template->id.'/'.$data))}}</span></label>
                                    </p>
                                @endforeach
                            @endif
                        </div>
                        <div class="row hidden" id="fileError">
                            <div class="col-md-8 col-md-offset-2"><p class="text-danger" style="margin-left: 10px;"></p></div>
                        </div>
                    </div>
                </div>
                <div class="row hidden" id="fileError">
                    <div class="col-md-8 col-md-offset-2"><p class="text-danger" style="margin-left: 10px;"></p></div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="pull-right btn-list">
                    <button type="button" value="schedule" name="btnType" id="scheduleBtn" class="btn btn-primary">Schedule</button>
                    <button type="button" onclick="javascript:location.href= '{{ URL::route('user.email.mass') }}'" class="btn btn-default">Cancel</button>
                    <button type="button" id="previewTemplate" class="btn btn-primary">Preview</button>
                    <button type="button" id="sendTestMessageBtn" class="btn btn-primary">Send a Test Message</button>
                    <button type="button" value="draft" name="btnType" class="btn btn-primary saveTemplate">Save as draft</button>
                    <button type="button" name="btnType" value="send" class="btn btn-primary saveTemplate">Send</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('bootstrapModel')
    <div id="emailPreview" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <div id="headerInfo"><h4 class="modal-title">Email Preview</h4></div>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div id="emailPreviewContent"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-xs btn-default pull-right" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="advanceFilterModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <h4 class="modal-title">Advance Filter</h4>
                </div>
                <div class="modal-body"> Loading... </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div id="leadMappingModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <h4 class="modal-title">Field Mapping</h4>
                </div>
                <div class="modal-body"> Loading... </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div id="testEmailModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <h4 class="modal-title">Test Message</h4>
                </div>
                <form class="form" id="textMessageForm" action="{{ URL::route('user.email.mass.testmail') }}">
                    <div id="error"></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input name="testEmails" id="testEmails" title="Add new email" class="form-control" value="{{ $user->email }}" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="scheduleModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <h4 class="modal-title">Schedule Emails</h4>
                </div>
                <form class="form" id="scheduleForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label col-md-4"> Select Date & Time</label>
                            <div class="col-md-8">
                                <div class="input-group date form_datetime input-large">
                                    <input type="text" size="16" readonly class="form-control" name="scheduleTime" id="scheduleTime">
                                <span class="input-group-btn">
                                    <button class="btn default date-set" type="button">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                                </div>
                                <span class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('modelJavascript')
{!! Html::script('assets/js/ckeditor/ckeditor.js') !!}
{!! HTML::script('assets/js/ckeditor/adapters/jquery.js') !!}
{!! Html::script('assets/js/bootstrap-timepicker.min.js') !!}
{!! Html::script('assets/js/jquery.tagsinput.min.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}
{!! HTML::script('assets/js/moment.js') !!}
{!! HTML::script('assets/js/bootstrap-datetimepicker.min.js') !!}
{!! HTML::script('assets/js/bootstrap-select.min.js') !!}
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/page/massEmail.js') !!}

<script type="text/javascript">
    $(function() {
        MassEmail.setCkeditorPath("{{ URL::asset('assets/fileman') }}/");
        MassEmail.init();
        MassEmail.setMaxFileSize({!! $maxFileSize !!});
        MassEmail.setDetectEmailUrl(" {{ URL::route('user.email.mass.detectEmail') }}");
        MassEmail.setCsvFileUrl(' {{ URL::route('user.email.mass.csvFile') }}');
        MassEmail.setTemplateType('duplicate');
        MassEmail.setCkeditorValue({!! json_encode($template->content) !!});
        MassEmail.setDuplicateTemplate('{{ $template->id }}');
        MassEmail.setApplyFilterUrl(' {{ URL::route('user.email.mass.campaignEmailWithFilter') }}');
		$('[data-toggle="tooltip"]').tooltip()
    });
</script>
@stop
