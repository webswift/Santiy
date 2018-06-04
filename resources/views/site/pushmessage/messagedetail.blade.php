@extends('layouts.dashboard')

@section('title')
Read Message
@stop

@section('content')

<div class="pageheader">
    <h2><i class="fa fa-envelope"></i> Read Message </h2>

</div>

<div class="contentpanel panel-email">

    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <a onclick="showMessageModal()" class="btn btn-danger btn-block btn-compose-email">Push Message</a>


        </div><!-- col-sm-3 -->

        <div class="col-sm-9 col-lg-10">

            <div class="panel panel-default">
                <div class="panel-body">

                    <div class="btn-group mr10">
                        <a href="{{ URL::route('user.pushmessage') }}" class="btn btn-sm btn-white tooltips" type="button" data-toggle="tooltip" title="Show All Messages"><i class="glyphicon glyphicon-chevron-left"></i></a>
                    </div>

                    <div class="read-panel">

                        <div class="media">
                            <div class="media-body">
                                <span class="media-meta pull-right">{{ (new DateTime($messageDetails->time))->format('d-m-Y') }}</span>
                                <h4 class="text-primary">{{ $messageDetails->firstName . $messageDetails->lastName }}</h4>
                            </div>
                        </div><!-- media -->


                        <p>{!! $messageDetails->message !!}</p>

                        <br />



                    </div><!-- read-panel -->

                </div><!-- panel-body -->
            </div><!-- panel -->

        </div><!-- col-sm-9 -->

    </div><!-- row -->

</div>
@stop


@section('bootstrapModel')
<!-- Email Preview model -->
<div id="createMessageModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <div id="headerInfo">
                    <h4 class="modal-title">Push Message</h4>

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
                                	@if(sizeof($teamMembers) > 0)
                                    @foreach($teamMembers as $teamMember)
                                    <div class="ckbox ckbox-default col-sm-3">
                                        <input type="checkbox" class="managerUsers" value="{{ $teamMember->id }}" id="chkuser{{ $teamMember->id }}">
                                        <label for="chkuser{{ $teamMember->id }}">{!! $teamMember->firstName !!}</label>
                                    </div>
                                    @endforeach
                                    @endif
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
</div>
</div><!-- Email Preview model -->
<script>

    function showMessageModal()
    {
        $('#createMessageModal').modal('show');
    }
</script>
@stop

@section('modelJavascript')

@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
<script>
    $('#managerAllUsers').click(function(){
        if ($(this).is(":checked"))
        {
            $(".managerUsers").prop('checked', true);
        }else{
            $(".managerUsers").prop('checked', false);
        }
    });

    function sendMessage()
    {
        var message = $('#message').val();
        var usersID = new Array();
        var error = "#error";

        $('.managerUsers:checked').each(function() {
            usersID.push($(this).val());
        });

        if(message == "")
        {
            $(error).html('<div class="alert alert-danger">Please enter some text message.</div>');
        }else if(usersID.length == 0)
        {
            $(error).html('<div class="alert alert-danger">Please Select a User.</div>');
        }else{
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
                        $(error).html('<div class="alert alert-success">Message Send Successfully.</div>');
                    }
                }
            });
        }
    }
</script>
@stop
