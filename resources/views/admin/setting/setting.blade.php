@extends('layouts.admindashboard')

@section('title')
    Website Settings
@stop

@section('css')
    {!! Html::style('assets/css/jquery.tagsinput.css') !!}
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-home"></i> Dashboard</h2>
    <div class="breadcrumb-wrapper">
        <span class="label">You are here:</span>
        <ol class="breadcrumb">
            <li>Super Admin</li>
            <li class="active"><a href="{{ URL::route("admin.dashboard") }}">Dashboard</a></li>
        </ol>
    </div>
</div>

<div class="contentpanel">
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-btns">
                <a href="" class="minimize">−</a>
            </div>
            <h4 class="panel-title">Website Settings</h4>
            <p>Manage your Website Setting here</p>
        </div>

        {!! Form::open(array('route' => array('admin.setting.savesetting'), 'id' => 'settingForm', 'method' => 'post')) !!}
        <div class="panel-body panel-body-nopadding">
            <div id="error"></div>
            <div class="form-horizontal form-bordered">
                <div class="form-group">
                    <div class="col-sm-6">
                        <label class="col-sm-4 control-label" for="adminEmail">Admin Email Address</label>
                        <div class="col-sm-8">
                            <input type="text" name="adminEmail" id="adminEmail" class="form-control" value="{!! $settingFields['adminEmail'] !!}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-6">
                        <label class="col-sm-4 control-label" for="paypalID">Paypal ID</label>
                        <div class="col-sm-8">
                            <input type="text"name="paypalID" id="paypalID"  class="form-control" value="{!! $settingFields['paypalID'] !!}">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="col-sm-4 control-label" for="apiSignature">Api Signature</label>
                        <div class="col-sm-8">
                            <input type="text" name="apiSignature" id="apiSignature" class="form-control" value="{!! $settingFields['apiSignature'] !!}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-6">
                        <label class="col-sm-4 control-label" for="supportEmail">Support Email Address</label>
                        <div class="col-sm-8">
                            <input type="text" name="supportEmail" id="supportEmail" class="form-control" value="{!! $settingFields['supportEmail'] !!}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="col-sm-4 control-label" for="defaultCurrency">Default Currency</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="defaultCurrency" id="defaultCurrency">
                                    <option value="USD" @if($settingFields['defaultCurrency'] == 'USD') selected @endif >USD</option>
                                    <option value="GBP" @if($settingFields['defaultCurrency'] == 'GBP') selected @endif >GBP</option>
                                    <option value="EUR" @if($settingFields['defaultCurrency'] == 'EUR') selected @endif >EUR</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="col-sm-4 control-label" for="baseCurrency">Base Currency</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="baseCurrency" id="baseCurrency">
                                    <option value="USD" @if($settingFields['baseCurrency'] == 'USD') selected @endif >USD</option>
                                    <option value="GBP" @if($settingFields['baseCurrency'] == 'GBP') selected @endif >GBP</option>
                                    <option value="EUR" @if($settingFields['baseCurrency'] == 'EUR') selected @endif >EUR</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
					<div class="col-sm-6">
						<label class="col-sm-4 control-label" for="maxFileUploadSize">MAX File Size (in MB)</label>
						<div class="col-sm-8">
							<input type="text" name="maxFileUploadSize" id="maxFileUploadSize" class="form-control" value="{!! $settingFields['maxFileUploadSize'] !!}">
						</div>
					</div>
				</div>

                <div class="form-group">
                    <div class="col-sm-6">
                        <label class="col-sm-4 control-label" for="maxLandingForm">Maximum Landing Forms</label>
                        <div class="col-sm-8">
                            <input type="text" name="maxLandingForm" id="maxLandingForm" class="form-control" value="{!! $settingFields['maxLandingForm'] !!}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="trackingCode">Website Tracking Code</label>
                        <div class="col-sm-8">
                            <textarea name="trackingCode" id="trackingCode" class="form-control" rows="5">{!! $settingFields['trackingCode'] !!}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="renewalRemainder">Send Renewal Remainder </label>
                        <div class="col-sm-8">
                            <select class="form-control" name="renewalRemainder" id="renewalRemainder">
                                <option value="everyDay" @if($settingFields['renewalRemainder'] == 'everyDay') selected @endif >Every Day untill renewal/expiry</option>
                                <option value="everyWeek" @if($settingFields['renewalRemainder'] == 'everyWeek') selected @endif >Every Week until renewal/expiry</option>
                                <option value="everyMonth" @if($settingFields['renewalRemainder'] == 'everyMonth') selected @endif >Every Month untill renewal/expiry</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="renewalRemainder">Send Trial User Renewal Remainder </label>
                        <div class="col-sm-8">
                            <select class="form-control" name="trialRenewalRemainder" id="trialRenewalRemainder">
                                <option value="everyDay" @if($settingFields['trialRenewalRemainder'] == 'everyDay') selected @endif >Every Day untill renewal/expiry</option>
                                <option value="everyWeek" @if($settingFields['trialRenewalRemainder'] == 'everyWeek') selected @endif >Every Week until renewal/expiry</option>
                                <option value="everyMonth" @if($settingFields['trialRenewalRemainder'] == 'everyMonth') selected @endif >Every Month untill renewal/expiry</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="industry">Industry Dropdowns</label>
                        <div class="col-sm-8">
                            <input name="industry" id="industry" class="form-control" value="{!! $settingFields['industry'] !!}" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="metaKeywords">Meta Keywords</label>
                        <div class="col-sm-8">
                            <input type="text"  name="metaKeywords" id="metaKeywords" class="form-control" value="{!! $settingFields['metaKeywords'] !!}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="metaDescription">Meta Description</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="metaDescription" id="metaDescription" rows="5">{!! $settingFields['metaDescription'] !!}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="frontHtml">Front Html</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="frontHtml" id="frontHtml" rows="5">{!! $settingFields['frontHtml'] !!}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="loginHtml">Login Html</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="loginHtml" id="loginHtml" rows="5">{!! $settingFields['loginHtml'] !!}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-10">
                        <label class="col-sm-4 control-label" for="adminLoginHtml">AdminLogin Html</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="adminLoginHtml" id="adminLoginHtml" rows="5">{!! $settingFields['adminLoginHtml'] !!}</textarea>
                        </div>
                    </div>
                </div>
				
				<div class="form-group">
                    <div class="col-sm-6">
                        <label class="col-sm-4 control-label" for="userHelpUrl">Help topic URL</label>
                        <div class="col-sm-8">
                            <input type="text" name="userHelpUrl" id="userHelpUrl" class="form-control" value="{!! $settingFields['userHelpUrl'] !!}">
                        </div>
                    </div>
                </div>

            </div>

        </div><!-- panel-body -->

        <div class="panel-footer">
            <div class="row">
                <div class="col-sm-6 col-sm-offset-3">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </div>
        </div>

        {!! Form::close() !!}

    </div><!-- panel -->

</div><!-- contentpanel -->

@stop

@section('modelJavascript')
    {!! HTML::script('assets/js/jquery.tagsinput.min.js') !!}
    {!! HTML::script('assets/js/ckeditor/ckeditor.js') !!}
    {!! HTML::script('assets/js/ckeditor/adapters/jquery.js') !!}
<script>

    var roxyFileman = "{{ URL::asset('assets/fileman') }}/";
     $(function(){
        CKEDITOR.replace( 'frontHtml',{filebrowserBrowseUrl:roxyFileman,
                                     filebrowserImageBrowseUrl:roxyFileman+'index.html?type=image',
                                     removeDialogTabs: 'link:upload;image:upload'});
     });

      $(function(){
         CKEDITOR.replace( 'loginHtml',{filebrowserBrowseUrl:roxyFileman,
                                      filebrowserImageBrowseUrl:roxyFileman+'index.html?type=image',
                                      removeDialogTabs: 'link:upload;image:upload'});
      });

      $(function(){
           CKEDITOR.replace( 'adminLoginHtml',{filebrowserBrowseUrl:roxyFileman,
                                        filebrowserImageBrowseUrl:roxyFileman+'index.html?type=image',
                                        removeDialogTabs: 'link:upload;image:upload'});
        });
</script>
@stop

@section('javascript')
<script>
    jQuery(document).ready(function(){

        // Tags Input
        jQuery('#industry').tagsInput({width:'auto'});

    });

    $('#settingForm').submit(function(e) {
        e.preventDefault();

        for ( instance in CKEDITOR.instances )
                CKEDITOR.instances[instance].updateElement();

        $.ajax({
            type: 'post',
            url: $(this).attr('action'),
            cache: false,
            data: $(this).serialize(),
            beforeSend: function() {
                $('#error').html('<div class="alert alert-info">Loading...</div>');
            },
            success: function(data) {
                var obj = jQuery.parseJSON(data);

                if(obj.success == 'success')
                {
                    $('#error').html('<div class="alert alert-info">Website Setting Saved Successfully.</div>');
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }


            },
            error: function(xhr, textStatus, thrownError) {
                showError('Something went wrong. Please Try again later!');
            }
        });
        return false;
    });
</script>
@stop
