@extends('layouts.admindashboard')

@section('title')
    Mass Mail Server Settings
@stop

@section('css')

@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-home"></i> Settings</h2>
</div>

<div class="contentpanel">
    <div class="panel">
        <div class="panel-heading">
            <h4 class="panel-title">Mass Mail Server Settings</h4>
        </div>
        <div class="panel-body">
            <form class="form-horizontal" method="POST" action="{{ URL::route("admin.mass.savemailsettings") }}" id="massMailForm">
                <h4 class="text-center">Mail Server settings for mass mail sent from SanityOS Users</h4>
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                    </div>
                @endif
                {!! csrf_field() !!}

                <div class="form-group">
                    <label class="col-sm-3 control-label">Amazon AWS Access Key</label>
                    <div class="col-sm-6">
                        <input name="accessKey" type="text" class="form-control" value="{!! $massMailServer['accessKey'] !!}" placeholder="Access Key"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Amazon AWS Secret Key</label>
                    <div class="col-sm-6">
                        <input name="secretKey" type="text" class="form-control" value="{!! $massMailServer['secretKey'] !!}" placeholder="Secret Key"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Amazon AWS Region</label>
                    <div class="col-sm-6">
                        <input name="region" type="text" class="form-control" value="{!! $massMailServer['region'] !!}" placeholder="Region"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">From email</label>
                    <div class="col-sm-6">
                        <input name="fromEmail" type="text" class="form-control" value="{!! $massMailServer['fromEmail'] !!}" placeholder="From Email" data-toggle="tooltip" data-placement="auto" title="From Email address is the one you validated earlier on AWS"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group form-horizontal">
                            <div class="col-sm-9 col-sm-offset-3">
                                <input type="submit" class="btn btn-primary" value="Submit"/>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <form class="form-horizontal" method="POST" action="{{ URL::route("admin.mass.saveMailLimitation") }}" id="limitationForm">
                <h4 class="text-center">Set Limitations</h4>
                {!! csrf_field() !!}

                <div class="form-group">
                    <label class="col-sm-2 control-label">Trial Users:</label>
                    <div class="col-sm-2">
                        <select class="form-control" name="trialLimitBy" id="trialLimitBy">
                            <option @if($trialUserLimit['limitBy'] == 'Day') selected @endif value="Day">Limit Users By Day </option>
                            <option @if($trialUserLimit['limitBy'] == 'Month') selected @endif value="Month">Limit Users By Month </option>
                        </select>
                    </div>
                    <label class="col-sm-2 control-label">Number of emails</label>
                    <div class="col-sm-2">
                       <input type="text" name="trialEmails" value="{!! $trialUserLimit['emails'] !!}" id="trialEmails" class="form-control">
                        <div class="mb10"></div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="trialErrorMessage" value="{!! $trialUserLimit['errorMessage']  !!}" id="trialErrorMessage" class="form-control" data-toggle="tooltip" data-placement="auto" title="Error message when limit is reached">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Single License Users:</label>
                    <div class="col-sm-2">
                        <select class="form-control" name="singleLimitBy" id="singleLimitBy">
                            <option @if($singleUserLimit['limitBy'] == 'Day') selected @endif value="Day">Limit Users By Day </option>
                            <option @if($singleUserLimit['limitBy'] == 'Month') selected @endif value="Month">Limit Users By Month </option>
                        </select>
                    </div>
                    <label class="col-sm-2 control-label">Number of emails</label>
                    <div class="col-sm-2">
                        <input type="text" name="singleEmails" value="{!! $singleUserLimit['emails'] !!}" id="singleEmails" class="form-control">
                        <div class="mb10"></div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="singleErrorMessage" value="{!! $singleUserLimit['errorMessage'] !!}" id="singleErrorMessage" class="form-control" data-toggle="tooltip" data-placement="auto" title="Error message when limit is reached">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">Multi License Users:</label>
                    <div class="col-sm-2">
                        <select class="form-control" name="multiLimitBy" id="multiLimitBy">
                            <option @if($multiUserLimit['limitBy'] == 'Day') selected @endif value="Day">Limit Users By Day </option>
                            <option @if($multiUserLimit['limitBy'] == 'Month') selected @endif value="Month">Limit Users By Month </option>
                        </select>
                    </div>
                    <label class="col-sm-2 control-label">Number of emails</label>
                    <div class="col-sm-2">
                        <input type="text" name="multiEmails" value="{!! $multiUserLimit['emails'] !!}" id="multiEmails" class="form-control">
                        <div class="mb10"></div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="multiErrorMessage" value="{!! $multiUserLimit['errorMessage'] !!}" id="multiErrorMessage" class="form-control" data-toggle="tooltip" data-placement="auto" title="Error message when limit is reached">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group form-horizontal">
                            <div class="col-sm-9 col-sm-offset-3">
                                <input type="submit" class="btn btn-primary" value="Submit"/>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('modelJavascript')
@stop

@section('javascript')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
    @if(Session::has("message"))
		showSuccess("{!! Session::get("message") !!}");
    @endif
</script>
@stop
