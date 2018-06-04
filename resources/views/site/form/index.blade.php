@extends('layouts.dashboard')

@section('css')
@stop

@section('title')
    Forms
@stop

@section('content')
<div class="pageheader">
    <h2><i class="glyphicon glyphicon-edit"></i> Welcome to Form Builder!</h2>
</div>

<div class="contentpanel">
    <div class="row">
        <div class="col-md-12 mt10 mt10">
            <p class="text-center">Select the type of Form you would like to Build or Edit</p>
            <p class="text-center">I would like to Create or Edit:</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-6  col-sm-6"><a href="{{ URL::route('user.forms.createoredit') }}" class="btn btn-success pull-right">Lead Form for Campaigns</a></div>
        <div class="col-md-6 col-xs-6  col-sm-6"><a href="{{ URL::route('user.forms.landing.createoredit') }}" class="btn btn-success">Web forms/Surveys</a></div>
    </div>
</div>
@stop
@section('javascript')
{!! Html::script('assets/js/custom.js') !!}
@stop
