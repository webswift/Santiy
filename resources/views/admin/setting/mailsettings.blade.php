@extends('layouts.admindashboard')

@section('title')
    Mail Server Settings
@stop

@section('css')
    {!! Html::style('assets/css/jquery.tagsinput.css') !!}
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-home"></i> Settings</h2>
</div>

<div class="contentpanel">

    <div class="panel">

        <div class="panel-heading">
            <div class="panel-btns">
                <a href="" class="minimize">−</a>
            </div>
            <h4 class="panel-title">Mail Server Settings</h4>
        </div>
        <div class="panel-body">
        	<div class="padding10" id="intro">
			<h4>Mail Server settings for Emails to users from SanityOS</h4>
			<p>This includes:</p>
			<ul>
				<li>Appointments Booked</li>
				<li>Follow Up Calls Reminder</li>
				<li>Invoices for any purchases</li>
				<li>Team Statistic Update Email</li>
			</ul>
			<button class="btn btn-primary btn-sm" onclick="showSettings('inbound');">Edit Settings <i class="fa fa-chevron-circle-right"></i></button>
			<p>&nbsp;</p>

			<h4>Mail Server settings for users sending outbound email (when they do not use their own SMTP Server)</h4>
			<button class="btn btn-primary btn-sm" onclick="showSettings('outbound');">Edit Settings <i class="fa fa-chevron-circle-right"></i></button>
			</div>

        	<form class="form-horizontal form-bordered hidden" method="POST" action="{{ URL::route("admin.savemailsettings") }}" id="inboundMails">
				<h4 class="text-center">Mail Server settings for Emails to users from SanityOS</h4>
				<input type="hidden" name="type" value="inbound">
				{!! csrf_field() !!}
                <div class="row">
					<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">From email:</label>
								<div class="col-sm-6">
									<input name="from" type="text" class="form-control" value="{!! $inboundMail["from"]["address"] !!} "/>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
					<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">From Name:</label>
								<div class="col-sm-6">
									<input name="fromName" type="text" class="form-control" value="{!! $inboundMail["from"]["name"]  !!}"/>
								</div>
							</div>
						</div>
					</div>

                					<div class="row">
                						<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">Reply to email:</label>
                								<div class="col-sm-6">
                									<input name="replyTo" type="text" class="form-control" value="{!! $inboundMail["replyTo"] !!}"/>
                								</div>
                							</div>
                						</div>
                					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">Host:</label>
								<div class="col-sm-6">
									<input name="host" type="text" class="form-control" value="{{ $inboundMail["host"] }}"/>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">Port:</label>
								<div class="col-sm-6">
									<input name="port" type="text" class="form-control" value="{{ $inboundMail["port"] }}"/>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">UserName:</label>
								<div class="col-sm-6">
									<input name="username" type="text" class="form-control" value="{{ $inboundMail["username"] }}"/>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">Password:</label>
								<div class="col-sm-6">
									<input name="password" type="text" class="form-control" value="{{ $inboundMail["password"] }}"/>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<div class="form-group form-horizontal">
								<label class="col-sm-3 control-label">Require SSL:</label>
								<div class="col-sm-9 mt10">
									<div class="ckbox ckbox-default">
										<input type="checkbox" name="requireSSL" id="requireSSL" @if($inboundMail["requireSSL"] == "on") checked @endif>
										<label for="requireSSL"></label>
									</div>
								</div>
							</div>
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

			<form class="form-horizontal form-bordered hidden" method="POST" action="{{ URL::route("admin.savemailsettings") }}" id="outboundMails">
				<h4 class="text-center">Mail Server settings for users sending outbound email (when they do not use their own SMTP Server)</h4>
				<input type="hidden" name="type" value="outbound">
				{!! csrf_field() !!}

				<div class="row">
                					<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">From email:</label>
                								<div class="col-sm-6">
                									<input name="from" type="text" class="form-control" value="{!! $outboundMail["from"]["address"] !!} "/>
                								</div>
                							</div>
                						</div>
                					</div>
                					<div class="row">
                						<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">Host:</label>
                								<div class="col-sm-6">
                									<input name="host" type="text" class="form-control" value="{!! $outboundMail["host"] !!}"/>
                								</div>
                							</div>
                						</div>
                					</div>
                					<div class="row">
                						<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">Port:</label>
                								<div class="col-sm-6">
                									<input name="port" type="text" class="form-control" value="{{ $outboundMail["port"] }}"/>
                								</div>
                							</div>
                						</div>
                					</div>
                					<div class="row">
                						<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">UserName:</label>
                								<div class="col-sm-6">
                									<input name="username" type="text" class="form-control" value="{{ $outboundMail["username"] }}"/>
                								</div>
                							</div>
                						</div>
                					</div>
                					<div class="row">
                						<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">Password:</label>
                								<div class="col-sm-6">
                									<input name="password" type="text" class="form-control" value="{{ $outboundMail["password"] }}"/>
                								</div>
                							</div>
                						</div>
                					</div>
                					<div class="row">
                						<div class="col-sm-12">
                							<div class="form-group form-horizontal">
                								<label class="col-sm-3 control-label">Require SSL:</label>
                								<div class="col-sm-9 mt10">
                									<div class="ckbox ckbox-default">
                										<input type="checkbox" name="requireSSL" id="requireSSL1" @if($outboundMail["requireSSL"] == "on") checked @endif>
                										<label for="requireSSL1"></label>
                									</div>
                								</div>
                							</div>
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

    </div><!-- panel -->

</div><!-- contentpanel -->

@stop

@section('modelJavascript')
    {!! HTML::script('assets/js/jquery.tagsinput.min.js') !!}
    {!! HTML::script('assets/js/ckeditor/ckeditor.js') !!}
    {!! HTML::script('assets/js/ckeditor/adapters/jquery.js') !!}
@stop

@section('javascript')
<script>
	function showSettings(type) {
		$("#intro").addClass("hidden");
		if (type == "inbound") {
			$("#inboundMails").removeClass("hidden");
		}
		else {
			$("#outboundMails").removeClass("hidden");
		}
	}

	@if(Session::has("message"))
		showSuccess("{!! Session::get("message") !!}");
	@endif

</script>
@stop
