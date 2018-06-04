@extends('layouts.dashboard')

@section('title')
 Push Messages
@stop

@section('content')

<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> Messages </h2>
</div>

<div class="contentpanel panel-email">
    <div class="row">
    <div class="col-sm-3 col-lg-2">
    <a onclick="showMessageModal()" class="btn btn-danger btn-block btn-compose-email">Push Message</a>
</div>

<div class="col-sm-9 col-lg-10">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="pull-right">
                <div class="btn-group">
                    @if($pageNumber-1 > 0)
                        <a href="{{ URL::route('user.pushmessage', array($pageNumber-1)) }}" class="btn btn-sm btn-white" type="button"><i class="glyphicon glyphicon-chevron-left"></i></a>
                    @endif
                    @if(($pageNumber)*10 < $totalMessages)
                        <a href="{{ URL::route('user.pushmessage', array(($pageNumber+1))) }}" class="btn btn-sm btn-white" type="button"><i class="glyphicon glyphicon-chevron-right"></i></a>
                    @endif
                </div>
            </div>

            <h5 class="subtitle mb5">Push Messages</h5>
            <p class="text-muted">Showing {{ (($pageNumber - 1)*10)+1 }} - @if(($pageNumber)*10 > $totalMessages) {{ $totalMessages }}  @else {{ (($pageNumber)*10) }} @endif of {{ $totalMessages }} messages</p>
            <div id="error3"></div>

            <div class="table-responsive">
                <table class="table table-email">
                    <tbody>
                    @foreach($allMessages as $allMessage)
                        <tr>
                            <td id="message_{{ $allMessage->messageID }}">
                                <div class="media">
                                    <div class="media-body">
                                        <span class="media-meta pull-right">{{ (new DateTime($allMessage->time))->format('d-m-Y') }} &nbsp;&nbsp;  <i onclick="deleteThisMessage({{ $allMessage->messageID }})" class="fa fa fa-trash-o text-danger"></i></span>
                                        <h4 onclick="readMessage({{ $allMessage->messageID }})" class="text-primary">{{ $allMessage->firstName . $allMessage->lastName }}</h4>
                                        <small class="text-muted"></small>
                                        <p onclick="readMessage({{ $allMessage->messageID }})" class="email-summary">{!! substr($allMessage->message, 0, 80) !!}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div><!-- row -->
</div>

@stop


@section('bootstrapModel')
<div id="createMessageModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Create Message</h4>

                </div>
            </div>
            <div class="modal-body">
                <div id="error"></div>
                <div class="sendMessageDiv">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label">Message</label>
                                <textarea id="message" class="form-control" rows="5" placeholder="Message"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="control-label">Send To</label>
                                <div class="row">
                                    <div class="col-sm-12">
                                        @foreach($teamMembers as $teamMember)
                                        <div class="ckbox ckbox-default col-sm-3">
                                            <input type="checkbox" class="managerUsers" value="{{ $teamMember->id }}" id="chkuser{{ $teamMember->id }}">
                                            <label for="chkuser{{ $teamMember->id }}">{{ $teamMember->firstName }}</label>
                                        </div>
                                        @endforeach
                                        <div class="ckbox ckbox-default col-sm-3">
                                            <input type="checkbox" id="managerAllUsers">
                                            <label for="managerAllUsers">All</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer sendMessageDiv">
                <div class="row">
                    <div class="col-sm-12">
                        <button onclick="sendMessage()" class="btn btn-xs btn-success">Send</button>
                    </div>
                </div>
            </div><!-- panel-footer -->

        </div>

    </div>
</div>

@stop

@section('modelJavascript')
<script>
    function showMessageModal() {
        $("#error").html('');
        $('.sendMessageDiv').show();
        $(".managerUsers").prop('checked', false);
        $('#createMessageModal').modal('show');
    }
</script>
@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
<script>
    $('#managerAllUsers').click(function(){
        if ($(this).is(":checked")) {
            $(".managerUsers").prop('checked', true);
        }
        else {
            $(".managerUsers").prop('checked', false);
        }
    });

    function readMessage(messageID) {
        location.href = "{{ URL::route('user.pushmessage.messagedetail') }}/"+messageID;
    }

    function sendMessage() {
        var message = $('#message').val();
        var usersID = new Array();
        var error = "#error";

        $('.managerUsers:checked').each(function() {
            usersID.push($(this).val());
        });

        if(message == "") {
            $(error).html('<div class="alert alert-danger">Please enter some text message.</div>');
        }
        else if(usersID.length == 0) {
            $(error).html('<div class="alert alert-danger">Please Select a User.</div>');
        }
        else {
            $.ajax({
                type: 'post',
                url: "{{ URL::route('user.pushmessage.generatemessage') }}",
                cache: false,
                data: {"message": message, "usersIDs": usersID},
                beforeSend: function() {
                    $(this).attr('disabled', 'disabled');
                },
                success: function( response ) {
                    $(this).removeAttr('disabled');
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == "success")
                    {
                        $('.sendMessageDiv').hide();
                        $('#message').val('');
                        $(error).html('<div class="alert alert-success">Message Sent Successfully.</div>');
                    }
                }
            });
        }
    }

    function deleteThisMessage(messageID)
    {
        $.ajax({
            type: 'post',
            url: "{{ URL::route('user.pushmessage.deletepushmessage') }}",
            cache: false,
            data: {"messageID": messageID},
            beforeSend: function() {
                //$(this).attr('disabled', 'disabled');
            },
            success: function( response ) {
                var obj = jQuery.parseJSON(response);

                if(obj.success == "success")
                {
                    $("#message_"+messageID).fadeOut("slow");
                    $("#error3").html('<div class="alert alert-danger">Message Successfully Deleted.</div>');
                }else{
                    $("#error3").html('<div class="alert alert-danger">Something Wrong.</div>');
                }
            }
        });
    }

</script>
@stop