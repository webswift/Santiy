@extends('layouts.dashboard')

@section('css')
{!! Html::style('assets/css/colorpicker.css') !!}
<style>
    #demos section {
        overflow: hidden;
    }
    .sortable {
        width: 310px;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    .sortable.grid {
        overflow: hidden;
    }
    .sortable li {
        list-style: none;
        border: 1px solid #CCC;
        background: #FFF;
        color: #333;
        margin: 5px;
        padding: 5px 10px;
        min-height:32px;
        border-radius: 5px;
        overflow: hidden;
        width: 300px;
    }
    .sortable.grid li {
        line-height: 80px;
        float: left;
        width: 80px;
        height: 80px;
        text-align: center;
    }
    .handle {
        cursor: move;
    }
    .sortable.connected {
        width: 200px;
        min-height: 100px;
        float: left;
    }
    li.disabled {
        opacity: 0.5;
    }
    li.highlight {
        background: #FEE25F;
    }
    li.sortable-placeholder {
        border: 1px dashed #CCC;
        background: none;
    }
</style>
@stop

@section('title')
    Create/Edit Web Forms & Surveys
@stop

@section('content')
<div class="pageheader">
    <h2><i class="glyphicon glyphicon-edit"></i> Create/Edit Web Forms & Surveys</h2>
</div>

<div class="contentpanel">
    @if($successMessage != '')
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-{{ $successMessageClass or 'success'}}">
                    <a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
                    {!! $successMessage !!}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-sm-12"><div id="error1" class="error"></div></div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="alert alert-warning"> <i class="fa fa-info-circle"></i> You can create maximum  {{ $landingFormLimit }} landing forms.</div>
            <div class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-2">
                        <label class="control-label pull-right">New Form Name:</label>
                    </div>
                    <div class="col-sm-4">
                        <input type="text" id="newFormName" class="form-control" @if($landingFormLimit <= $formLists->count()) disabled @endif/>
                    </div>
                    <div class="col-sm-1" style="text-align: center">
                        <label class="control-label"><b>OR</b></label>
                    </div>
                    <div class="col-sm-3">
                        <select class="form-control" id="editFormName">
                            <option value="">Edit Existing Form</option>
                            @if(sizeof($formLists) > 0)
                                @foreach($formLists as $formList)
                                    <option value="{{ $formList->id }}">{{ $formList->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button class="btn btn-danger" id="deleteButton" style="display:none">Delete Form</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title">Landing Page/Signup Form Builder</h4></div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" id="companyName" value="Company Name" data-type="text" />
                                <label for="companyName">Company Name</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="First Name" id="firstName" data-type="text" />
                                <label for="firstName">First Name</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Last Name" id="lastName" data-type="text" />
                                <label for="lastName">Last Name</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Position" id="position" data-type="text" />
                                <label for="position">Position</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Email" id="email" data-type="text" />
                                <label for="email">Email</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Address" id="address" data-type="text" />
                                <label for="address">Address/Post/Zip code</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Telephone No" id="telephone" data-type="text" />
                                <label for="telephone">Telephone No</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Mobile No" id="mobile" data-type="text" />
                                <label for="mobile">Mobile No</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Notes" id="notes" data-type="text" />
                                <label for="notes">Notes</label>
                            </div>
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" class="predefindedFields" value="Website" id="website" data-type="text" />
                                <label for="website">Website</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-4">
                            <button onclick="addNewField()" class="btn btn-sm btn-primary">Add New Field</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title">Form Fields</h4></div>
                <div class="panel-body">
                    <div class="row">
                        <ul class="sortable"></ul>
                    </div>
                </div>

                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-3"><button class="btn btn-success" type="button" onclick="openFormDetailModal('header');">Extra's</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <div class="form-group">
                <div class="col-sm-5">
                    <label class="control-label"> You can drag and drop the positions of the fields and menus on your form to suit preference </label>
                </div>
                <div class="col-sm-7">
                    <button onclick="showDemoForm()" class="btn btn-warning">Preview (Open in new window)</button>
                    <button id="addOrEditButton" onclick="addThisForm()" type="button" class="btn btn-primary">Save Form</button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop


@section('bootstrapModel')
<div id="addNewField" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo"><h4 class="modal-title">Add New Field</h4></div>
            </div>
            <div class="modal-body mb10">
                <p>Would you like to add a text field or a drop menu?</p>
                <div class="row-fluid mb10">
                    <div class="rdio rdio-default col-sm-4">
                        <input type="radio" id="forDropMenu" value="forDropMenu" name="fieldType" />
                        <label for="forDropMenu">Drop Menu</label>
                    </div>
                    <div class="rdio rdio-default col-sm-4">
                        <input type="radio" id="forField" value="forField" name="fieldType" />
                        <label for="forField">Field</label>
                    </div>
					<div class="rdio rdio-default col-sm-4">
						<input type="radio" id="forDate" value="forDate" name="fieldType" />
						<label for="forDate">Field for Date</label>
					</div>
                </div>
                <div id="error"></div>
                <div class="row hidden" id="fieldDiv">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input id="newFieldName" type="text" name="fieldName" class="form-control input-sm" placeholder="Enter field name"/>
                        </div>
                    </div>
                </div>
                <div class="row hidden" id="dropMenudDiv" style="clear: both;width: 100%;">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input id="newFieldName" type="text" name="fieldName" class="form-control input-sm" placeholder="Enter field name"/>
                        </div>
                        <div id="optionDiv">
                            <div class="form-group">
                                <input type="text" class="form-control input-sm  options" name="options[]" placeholder="Option 1"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <button class="btn btn-default pull-right" onclick="addNewOptions();"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-4">
                        <button id="addThisNewField" class="btn btn-xs btn-success">Add Field</button>
                    </div>
                    <div class="col-md-8">
                        <div class="ckbox ckbox-default">
                            <input type="checkbox" value="Yes" id="requiredInput">
                            <label for="requiredInput">Make this required</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-tabs" id="formDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <ul class="nav nav-tabs nav-justified">
                <li class="active"><a data-toggle="tab" href="#headerTab"><strong>Header Text</strong></a></li>
                <li class=""><a data-toggle="tab" href="#footerTab"><strong>Footer Text</strong></a></li>
                <li class=""><a data-toggle="tab" href="#logoTab"><strong>Logo</strong></a></li>
                <li class=""><a data-toggle="tab" href="#colorTab"><strong>Colors</strong></a></li>
                <li class=""><a data-toggle="tab" href="#messageTab"><strong>Thank You Message</strong></a></li>
            </ul>
            <div class="tab-content">
                <div id="headerTab" class="tab-pane active">
                    <div class="row">
                        <div class="form-group">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Header Text</label>
                                <div class="col-sm-10">
                                    <textarea id="headerText" name="headerText" class="form-control" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="footerTab" class="tab-pane">
                    <div class="row">
                        <div class="form-group">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Footer Text</label>
                                <div class="col-sm-10">
                                    <textarea id="footerText" name="footerText" class="form-control" rows="5"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="logoTab" class="tab-pane">
                    <div class="row">
                        <div class="form-group">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Upload Logo</label>
                                <div class="col-md-5">
                                    <input type="file" name="logo" id="logo">
                                </div>
                                <div class="col-md-5">
                                    <img id="preview" height="200" width="200">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="colorTab" class="tab-pane">
                    <div class="form-group">
                        <label class="control-label">Color</label><br />
                        <input type="text" name="color" class="form-control colorpicker-input" id="colorpicker" />
                        <div class="clearfix"></div><br />
                        <span id="colorpickerholder"></span>
                    </div>
                </div>

                <div id="messageTab" class="tab-pane">
                    <div class="form-group">
                        <label class="control-label">Thank You Message</label><br />
                        <textarea  name="message" class="form-control" id="message"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Save & Close</button>
                </div>

            </div>
        </div>
    </div>
</div>
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/html5sortable/jquery.sortable.js') !!}
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/jquery.slimscroll.js') !!}
{!! HTML::script('assets/js/ckeditor/ckeditor.js') !!}
{!! HTML::script('assets/js/ckeditor/adapters/jquery.js') !!}
{!! HTML::script('assets/js/colorpicker.js')  !!}
<script>
    var roxyFileman = "{{ URL::asset('assets/fileman') }}/";
    var demoFormUrl = '{{ URL::route('user.forms.landing.showdemoform') }}';
    var deleteUrl = "{{ URL::route('user.forms.landing.deleteform') }}";
    var editUrl = "{{ URL::route('user.forms.landing.addoreditform') }}";
    var imagePath = '{{ URL::asset('assets/uploads/forms/logo') }}';
    var detailUrl = '{{ URL::route('user.forms.landing.formfieldsdetails') }}';
    $('.sortable').sortable();
</script>
{!! HTML::script('assets/js/landingJs.js') !!}

{{-- tools for dialog to map landing form fields to lead form fields --}}
<script>
    var mappingInfoUrl = '{{URL::route('user.form.landing.getMapping')}}';
    var setMappingUrl = '{{ URL::route('user.form.landing.setMapping') }}';
</script>
{!! HTML::script('assets/js/dialogs/mapleadlandingfields.js') !!}

@stop
