@extends('layouts.dashboard')

@section('css')
@stop

@section('title')
    Email Settings
@stop

@section('content')
    <div class="pageheader">
        <h2><i class="glyphicon glyphicon-user"></i> Email</h2>
    </div>

    <div class="contentpanel">
        <div class="row">
            <div class="col-md-12 mt10 mt10">
                <p class="text-center">Select the type of email functionality you require</p>
                <p class="text-center">I would like to Create or Edit:</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-xs-6  col-sm-6">
                <a href="{{ URL::route('user.email') }}" class="btn btn-success pull-right" data-toggle="tooltip" data-placement="auto"
                   title="More info mails are simply a method for you/your agents to send emails containing more information during calls. They are easily accessible and only go to the recipient you're may be talking to.">
                    More Info Emails
                </a>
            </div>
            <div class="col-md-6 col-xs-6  col-sm-6">
                <a href="{{ URL::route('user.email.mass') }}" class="btn btn-success" data-toggle="tooltip" data-placement="auto"
                   title="Warm up your leads by sending a mass mailer out to them all about your product or service.">
                    Mass Email Campaign
                </a>
            </div>
        </div>
    </div>
@stop
@section('javascript')
    {!! Html::script('assets/js/custom.js') !!}
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@stop