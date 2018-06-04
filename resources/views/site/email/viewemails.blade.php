@extends('layouts.dashboard')

@section('css')
    {!! Html::style('assets/css/jquery.datatables.css') !!}
	{!! Html::style('assets/css/emailediting.css') !!}
@stop

@section('title')
	Email Templates
@stop

@section('content')

<div class="pageheader">
  <h2><i class="fa fa-user"></i>Email</h2>
</div>

<div class="contentpanel">
    <div class="panel">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-10">
                    <p>Create a generalised email template to send to your prospects during calls -so when more information is requested you just
                        have to click a send button.
                    </p>
                </div>
                <div class="col-md-2">
                    <p><a class="btn btn-primary " href="{{ URL::route('user.email.create') }}">Create New</a></p>
                </div>
            </div>
        </div>
        <div class="panel-body">
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
            <div class="table-responsive">
                <table id="emails" class="table table-striped" id="table2">
                    <thead>
                    <tr>
                        <th>Template Name</th>
                        <th>Created On</th>
                        <th>Created By</th>
                        <th>Edit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
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
    <div id="emailPreview" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <div id="headerInfo">
                        <h4 class="modal-title">Email Preview</h4>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <div id="emailPreviewContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-xs btn-default pull-right" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                    <h4 class="modal-title">Disable Email</h4>
                </div>
                <div class="modal-body">
                    <p>Disable Email : <span id="emailTitle"></span></p>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" value="Yes" id="recipient">
                                <label for="recipient">Disable recipient</label>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="ckbox ckbox-primary">
                                <input type="checkbox" value="Yes" id="salesMember">
                                <label for="salesMember">Disable <span id="member"></span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" id="disableBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('modelJavascript')
{!! HTML::script('assets/js/jquery.datatables.min.js') !!}
<script>

function previewTemplate(templateID) {
    blockUI('.contentpanel');
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.email.previewtemplate') }}",
        cache: false,
        data: {"templateID": templateID},
        success: function(response) {
            var obj = jQuery.parseJSON(response);
            unblockUI('.contentpanel');

            if(obj.success == "success") {
                $('#emailPreviewContent').html(obj.templateContent);
                $('#emailPreview').modal('show');

                $('#emailPreview').on('hide.bs.modal', function (e) {
                    $('#emailPreviewContent').empty();
                });
            }
        },
        error: function(xhr, textStatus, thrownError) {
            unblockUI('.contentpanel');
            jQuery.gritter.add({
                title: 'Something went wrong',
                text: '<b>Error:</b> " + thrownError.message + ". Please try again!',
                class_name: 'growl-danger',
                sticky: false,
                time: 5000
            });
        }
    });
}

var table = $('#emails').dataTable( {
    "bProcessing": true,
	"bServerSide": true,
    "ajax": "{{ URL::route('user.email.ajaxemails') }}",
    "bSort": false,
    "aoColumns": [
        {'sClass': 'center', 'orderable': false },
        { 'sClass': 'center', 'orderable': false },
        { 'sClass': 'center', 'orderable': false },
        {'sClass': 'center', 'orderable': false }
    ],
    "sPaginationType": "full_numbers",
    "language": {
        "emptyTable": "Looks like you haven't created any email template yet"
    }
});
</script>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
<script type="text/javascript">
function deleteTemplate(templateID) {
    var answer = confirm("Want to delete this Template?");
    if (answer){
        blockUI('.contentpanel');
        $.ajax({
            type: 'post',
            url: "{{ URL::route('user.email.deletetemplate') }}",
            cache: false,
            data: {"templateID": templateID},
            success: function(response) {
                var obj = jQuery.parseJSON(response);
                unblockUI('.contentpanel');
                if(obj.success == "success") {
                    location.reload();
                }
            },
            error: function(xhr, textStatus, thrownError) {
                unblockUI('.contentpanel');
                jQuery.gritter.add({
                    title: 'Something went wrong',
                    text: '<b>Error:</b> " + thrownError.message + ". Please try again!',
                    class_name: 'growl-danger',
                    sticky: false,
                    time: 5000
                });
            }
        });
    }
}

function showModal(id, status, teamMemberStatus, name){
    if(name == 'Appointment booked'){
        $('#statusModal').find('#emailTitle').html('Appointment booked');
        $('#statusModal').find('#member').html('Sales Member');
    }
    else if(name == 'Follow Up Call'){
        $('#statusModal').find('#emailTitle').html('Follow up email');
        $('#statusModal').find('#member').html('Team User');
    }

    if(status == 'Disable'){
        $('#statusModal').find('#recipient').prop('checked', true);
    }
    else{
        $('#statusModal').find('#recipient').prop('checked', false);
    }

    if(teamMemberStatus == 'Disable'){
        $('#statusModal').find('#salesMember').prop('checked', true);
    }
    else{
        $('#statusModal').find('#salesMember').prop('checked', false);
    }

    $('#statusModal').find('#disableBtn').attr('onclick', 'setStatus('+id+')');

    $('#statusModal').modal('show');
}

function setStatus(id) {
    blockUI('#statusModal');

    var status, teamStatus;
    var recipient = $('#statusModal').find('#recipient').prop('checked');
    var member = $('#statusModal').find('#salesMember').prop('checked');

    if(recipient == true) {
        status = 'Disable';
    }
    else {
        status = 'Enable';
    }

    if(member == true) {
        teamStatus = 'Disable';
    }
    else {
        teamStatus = 'Enable';
    }

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.email.setEmailTemplateStatus') }}",
        cache: false,
        data: {"email": id, "status": status, "teamMember" : teamStatus},
        success: function(response) {
            unblockUI('#statusModal');
            $('#statusModal').modal('hide');
            showSuccess('status update successfully');
            table._fnDraw();
        },
        error: function(xhr, textStatus, thrownError) {
            alert('Something went wrong. Please Try again later!');
        }
    });
}
</script>
@stop
