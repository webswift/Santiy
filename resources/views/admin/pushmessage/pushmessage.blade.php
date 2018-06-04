@extends('layouts.admindashboard')

@section('title')
    Push Message
@stop

@section('css')
    {!! Html::style('assets/css/jquery.tagsinput.css') !!}
@stop

@section('content')

<div class="pageheader">
    <h2><i class="glyphicon glyphicon-edit"></i> Push Message</h2>
</div>

<div class="contentpanel">
    <div class="panel-heading">
        <h3 class="panel-title">Push Message </h3>
    </div><!-- panel-heading-->

    <div class="panel">
        <div class="panel-body">

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
                                <select id="userIDs" class="select2" multiple data-placeholder="Select Users">
                                @foreach($allUsers as $allUser)
                                    <option value="{{ $allUser->id }}">{{ $allUser->firstName . ' ' . $allUser->lastName }}</option>
                                @endforeach
                                </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-footer sendMessageDiv">
                <div class="row">
                    <button onclick="sendMessage()" class="btn btn-success">Send</button>
                </div>
            </div><!-- panel-footer -->
        </div><!-- panel-body -->
    </div><!-- panel -->

</div>

@stop


@section('modelJavascript')
    {!! HTML::script('assets/js/jquery.maskedinput.min.js') !!}
    {!! HTML::script('assets/js/jquery.tagsinput.min.js') !!}
@stop

@section('javascript')
<script>
    jQuery(document).ready(function(){

        "use strict";

        // Tags Input
        jQuery('#tags').tagsInput({width:'auto'});

        // Select2
        jQuery(".select2").select2({
            width: '100%'
        });
    });

    function sendMessage()
    {
        var message = $('#message').val();
        var usersID = $('#userIDs').val();
        var error = "#error";

        if(message == "")
        {
            $(error).html('<div class="alert alert-danger">Please enter some text message.</div>');
        }else if(usersID == null)
        {
            $(error).html('<div class="alert alert-danger">Please Select a User.</div>');
        }else{
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.pushmessage.generatemessage') }}",
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