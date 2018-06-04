@extends('layouts.dashboard')

@section('css')
    {!! Html::style('assets/css/jquery.datatables.css') !!}
	{!! Html::style('assets/css/emailediting.css') !!}
<style>
#massServerSettingForm .control-label {
	text-align:left;
	padding-left:15px;
}
</style>

@stop

@section('title')
    Mass Email Templates
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-user"></i>Mass Email Templates</h2>
</div>
<div class="contentpanel">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-8">
                    <p>Create Mass Mail Campaigns from here. View Click, Delivery, Bounce and Open Rates</p>
                    <p><strong>Email Credits:</strong> {{ $emailCredits }}</p>
                </div>
                <div class="col-md-4">
                    @can('mass-email', $user)
                    <p><button class="btn btn-primary" type="button" data-toggle="modal" data-target="#massMailServerModal">Mass Mail Server</button>
                    @else
                    @endcan

                    <a class="btn btn-primary " href="{{ URL::route('user.email.mass.create') }}"><i class="fa fa-plus"></i> Create Template</a></p>
                </div>
            </div>
        </div>
        <div class="panel-body">
            @if($warning != '')
                <div class="alert alert-warning">{!! $warning !!}</div>
            @endif
            <div class="table-responsive">
                <table id="massEmails" class="table table-striped">
                    <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Sent to</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('bootstrapModel')
<div id="emailShowModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Email Preview</h4>
                </div>
            </div>
            <div class="modal-body">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@can('mass-email', $user)
<div id="massMailServerModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Mass Mail Server Settings</h4>
                </div>
            </div>
            <form class="form form-horizontal" id="massServerSettingForm" action="#" method="post" role="form">
                <div class="modal-body">
					<div class="form-group">
						<label class="col-sm-3 control-label"></label>
						<div class="col-sm-7">
							<select id="selSendingProvider" name="sendingProvider" class="form-control">
								<option value="">Select Mass Email Service Provider</option>
								<option value="sparkpost" @if(isset($serverSetting) && $serverSetting->provider == 'sparkpost') selected @endif>SparkPost</option>
								<option value="mandrill" @if(isset($serverSetting) && $serverSetting->provider == 'mandrill') selected @endif>Mandrill</option>
								<option value="smtp" @if(isset($serverSetting) && $serverSetting->provider == 'smtp') selected @endif>SMTP</option>
							</select>
							<span class="help-block"></span>
						</div>
					</div>

                    <div class="form-group smtp_settings">
                        <label class="col-md-3 control-label">Host</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control serverSetting" name="host" value="{{ $serverSetting->host or '' }}" placeholder="Host" id="host">
                            <span class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group smtp_settings">
                        <label class="col-md-3 control-label">Port</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control serverSetting" name="port" value="{{ $serverSetting->port or '' }}" placeholder="Port" id="port">
                            <span class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group smtp_settings">
                        <label class="col-md-3 control-label">Username</label>
                        <div class="col-md-7">
                            <input type="text" class="form-control serverSetting" name="username" value="{{ $serverSetting->username or '' }}" placeholder="Username" id="username">
                            <span class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label passwordLabel">Password</label>
                        <div class="col-md-7">
                            <input type="password" class="form-control serverSetting" name="password" value="{{ $serverSetting->password or '' }}" placeholder="Password" id="password">
                            <span class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">From Email Address</label>
                        <div class="col-md-7">
                            <input type="email" class="form-control serverSetting" name="from_mail" value="{{ $serverSetting->from_mail or '' }}" placeholder="From Email Address" id="from_mail">
                            <span class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group smtp_settings">
                        <label class="col-md-3 control-label">Security</label>
                        <div class="col-md-7">
							<select id="security" name="security" class="form-control">
								<option value="No"@if(isset($serverSetting->security) && $serverSetting->security == 'No') selected @endif>None</option>
								<option value="tls" @if(isset($serverSetting->security) && $serverSetting->security == 'tls') selected @endif>TLS/STARTTLS</option>
								<option value="ssl" @if(isset($serverSetting->security) && $serverSetting->security == 'ssl') selected @endif>SSL</option>
							</select>
                            <span class="help-block"></span>
                        </div>
                    </div>

                    <div class="form-group sparkpost_info hidden">
                        <div class="col-md-10 col-md-offset-1">
						To obtain API key please use <a href="https://app.sparkpost.com/account/credentials">"API Keys" page at SparkPost</a>
						<br /><br />
						In order to track Bounce rates in Sanity OS please <br />
						use the following URL : https://www.sanityos.com/webhook/sparkpost <br /> as "Target URL"
						in the <a target="_blank" href="https://app.sparkpost.com/account/webhooks">SparkPost's webhooks config</a> <br />
						</div>
                    </div>

                    <div class="form-group mandrill_info hidden">
                        <div class="col-md-10 col-md-offset-1">
						To obtain API key please use <a href="https://mandrillapp.com/settings/">"API Keys" page at Mandrill</a>
						<br /><br />
						In order to track Bounce/Delivery/Spam rates in Sanity OS please <br />
						use the following URL : https://www.sanityos.com/webhook/mandrill <br /> as "Target URL"
						in the <a target="_blank" href="https://mandrillapp.com/settings/webhooks">Mandrill's webhooks config</a> <br />
						Next events are important for tracking: <br />
						<ul>
							<li>Message is sent</li>
							<li>Message is delayed</li>
							<li>Message is bounced</li>
							<li>Message is soft-bounced</li>
							<li>Message is marked as spam</li>
							<li>Message is rejected</li>
						</ul>

						</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="btnSaveMassEmailSettings" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
@endcan
@stop

@section('javascript')
 {!! HTML::script('assets/js/jquery.datatables.min.js') !!}
 {!! HTML::script('assets/js/custom.js') !!}
 {!! HTML::script('assets/js/bootbox.min.js') !!}

<script>
var table = undefined;

$( function() {
    @if(Session::has('successMessage'))
        showSuccess('{!! Session::get('successMessage') !!}');
    @endif

    table = $('#massEmails').dataTable( {
        processing: true,
        serverSide: true,
        ajax: '{{ URL::route('user.email.mass') }}',
        columns: [
            {data: 'name', name: 'name'},
            {data: 'firstName', name: 'users.firstName'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'email_sent', name: 'email_sent', orderable: false, searchable: false},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        "sPaginationType": "full_numbers",
        "language": {
            "emptyTable": "Looks like you haven't created any email template yet"
        }
    });

    $('#massEmails').on('click', '.previewBtn', function() {
        var id = $(this).attr('data-id');

        var url = "{{ route("user.email.mass.show", ["id" => "#id"]) }}";
        url = url.replace("#id", id);

        $("#emailShowModal").removeData('bs.modal').modal({
            remote: url,
            show: true
        });

        $('#emailShowModal').on('hidden.bs.modal', function () {
            $(this).find('.modal-body').html('Loading...');
            $(this).data('bs.modal', null);
        });
    });

    $('#massEmails').on('click', '.deleteBtn', function() {
        var id = $(this).attr('data-id');

        var url = "{{ route("user.email.mass.delete", ["id" => "#id"]) }}";
        url = url.replace("#id", id);

        bootbox.confirm({
            message: "Are you sure to delete this template ?",
            title : 'Confirm',
            callback: function(result){
                if(result) {
                    $.ajax({
                        method : 'post',
                        url : url,
                        dataType : 'json',
                        beforeSend: function( xhr ) {
                            blockUI('#massEmails');
                        },
                        success : function (response) {
                            unblockUI('#massEmails');
                            table._fnDraw();
                        },
                        error : function(xhr){
                            unblockUI('#massEmails');
                            showError('There is some error. Try again!');
                        }
                    });
                }
            }
        });
    });

    @can('mass-email', $user)
    $('#massMailServerModal').on('shown.bs.modal', function () {
        $('[data-toggle="tooltip"]').tooltip();
		$('#selSendingProvider').trigger('change');
    });

    $('#massMailServerModal').on('show.bs.modal', function () {
        hideErrors();
        document.getElementById("massServerSettingForm").reset();

    });

	$('#massServerSettingForm').submit(function (event) {
		event.preventDefault();
		hideErrors();

		var thisForm = $(this);
		$.ajax({
			method: 'post',
			url: '{{ URL::route('user.email.mass.server') }}',
			dataType: 'json',
			data: thisForm.serialize(),
			beforeSend: function (xhr) {
				blockUI('#massMailServerModal');
				thisForm.find('button[type="submit"]').prop('disabled', true);
			},
			success: function (response) {
				unblockUI('#massMailServerModal');
				slideToElement(thisForm);
				thisForm.find('button[type="submit"]').prop('disabled', false);

				if(response.status == 'fail') { 
					showInputError('host', response.message);
				} else {
					if(response.action == 'redirect') {
						window.location.href = response.url;
					}
					//showSuccess(response.message);
					$('#massMailServerModal').modal('hide');
				}
			},
			error: function (xhr, textStatus, thrownError) {
				unblockUI('#massMailServerModal');
				slideToElement(thisForm);
				thisForm.find('button[type="submit"]').prop('disabled', false);
				var fieldError = false;
				for (var key in xhr.responseJSON) {
					if (xhr.responseJSON.hasOwnProperty(key)) {
						var obj = xhr.responseJSON[key];
						showInputError(key, obj[0]);
						fieldError = true;
					}
				}
				if(!fieldError) {
					showInputError('host', thrownError);
				}
			}
		});
	});

	$('#selSendingProvider').change(function () {
        var provider = $(this).val();
		$('#btnSaveMassEmailSettings').prop('disabled', false);
		$('#massServerSettingForm .serverSetting').prop('disabled', false);
		$('#massServerSettingForm .sparkpost_info').addClass('hidden');
		$('#massServerSettingForm .mandrill_info').addClass('hidden');
		$('#massServerSettingForm .passwordLabel').html('Password');
		$('#massServerSettingForm .smtp_settings').removeClass('hidden');
		
		if(provider == '') {
            $('#massServerSettingForm .serverSetting').prop('disabled', true);
			$('#btnSaveMassEmailSettings').prop('disabled', true);
		} else if(provider == 'smtp') {
		} else if(provider == 'sparkpost') {
			$('#massServerSettingForm input[name="host"]').val('smtp.sparkpostmail.com').prop('disabled', true);
			$('#massServerSettingForm input[name="port"]').val('587').prop('disabled', true);
			$('#massServerSettingForm input[name="username"]').val('SMTP_Injection').prop('disabled', true);
			$('#massServerSettingForm input[name="security"]').val('tls').prop('disabled', true);
			$('#massServerSettingForm .sparkpost_info').removeClass('hidden');
			$('#massServerSettingForm .passwordLabel').html('API Key');
		} else if(provider == 'mandrill') {
			$('#massServerSettingForm input[name="host"]').val('smtp.mandrillapp.com').prop('disabled', true);
			$('#massServerSettingForm input[name="port"]').val('587').prop('disabled', true);
			$('#massServerSettingForm input[name="username"]').val('SMTP_Injection').prop('disabled', true);
			$('#massServerSettingForm input[name="security"]').val('No').prop('disabled', true);
			$('#massServerSettingForm .mandrill_info').removeClass('hidden');
			$('#massServerSettingForm .passwordLabel').html('API Key');
			$('#massServerSettingForm .smtp_settings').addClass('hidden');
		}
	});
    @else
    @endcan

});
</script>
@stop
