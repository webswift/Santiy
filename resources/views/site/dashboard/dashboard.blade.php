@extends('layouts.dashboard')

@section('title')
	Dashboard
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-home"></i> Dashboard <span>This Week Overview</span></h2>
</div>
<div class="contentpanel">
    @if($user->email_verification == 'No')
        <div class="alert alert-warning" id="resendLink"><i class="glyphicon glyphicon-remove"></i> Please verify your email address. <a href="javascript:;" onclick="sendVerificationLink()">Click here</a> to resend verification email.</div>
    @endif
    @if($user->resubscription == 'Yes' && $user->userType == 'Multi' && $user->existing == 'No')
        <div class="alert alert-warning"><i class="glyphicon glyphicon-remove"></i> SanityOS have recently changed their prices, please <a href="{{ URL::route('user.payment.resubscribe') }}">click here</a> to update your subscription.
            Don't worry you won't be charged twice, The new rate wil be applied on your normal billing date.</div>
    @endif
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-success panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-user.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">LEADS ACTIONED</small>
                                <h1>{{ $callMade or '0'}}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- col-sm-6 -->
        
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-danger panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-document.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">LEADS/CONTACTS</small>
                                <h1>{{ $totalLeads or '0'}}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-primary panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-document.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">
                                    @if($user->userType == 'Multi')
                                        ACTIVE CAMPAIGNS
                                    @else
                                        APPOINTMENTS BOOKED
                                    @endif
                                </small>
                                <h1>
                                    @if($user->userType == 'Multi')
                                        {{ $activeCampaign or '0'}}
                                    @else
                                        {{ $appointmentBooked or '0'}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="panel panel-dark panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-document.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">
                                    @if($user->userType == 'Multi')
                                        MEMBERS IN YOUR TEAM
                                    @else
                                        AVERAGE TIME CALL
                                    @endif
                                </small>
                                <h1>
                                    @if($user->userType == 'Multi')
                                        {{ $totalTeamMember or '0'}}
                                    @else
                                        {{ $averageCallTime or '0:0' }}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8 col-md-9">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-8">
                            <h5 class="subtitle mb5">POSITIVE VS NEGATIVE</h5>
                            <p class="mb15">Improve the output of your campaigns by monitoring your interest rate</p>
                            <div id="basicflot" style="width: 100%; height: 300px; margin-bottom: 20px"></div>
                        </div>

                        @if($user->userType == 'Single')
                            <div class="col-sm-4">
                                <h5 class="subtitle mb5">MOST SUCCESSFUL CAMPAIGNS</h5>
                                <p class="mb15">Compare the success rate of each campaign</p>

                                @if(count($campaigns) > 0)
                                    @foreach($campaigns as $campaign)
                                        <span class="sublabel">{{ $campaign['name'] }}</span>
                                        <span>{{ $campaign['totalInterested'] }} Positive, {{ $campaign['totalNotInterested'] }} Negative</span>
                                        <div class="progress progress-sm">
                                            <div style="width: {{ $campaign['percentage'] }}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="40" role="progressbar" class="progress-bar progress-bar-{{ $campaign['color'] }}"></div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="no-data-placeholder">No Data Available</div>
                                @endif
                            </div>
                        @else
                            <div class="col-sm-4">
                                <h5 class="subtitle mb5">TEAM PERFORMANCE</h5>
                                <p class="mb15">Staff performance for past 7 days.</p>
                                <div class="form-group">
                                    <select class="form-control input-sm mb15" id="teamPerformance">
                                        <option value="calls">Leads Actioned(this week)</option>
                                        <option value="appointments">Appointments booked(this week)</option>
                                        <option value="callTime">Avg call time(this week)</option>
                                    </select>
                                </div>

                                <div id="teamPerformanceDiv"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($user->userType == 'Single')
            <div class="col-sm-4 col-md-3">
                <div class="panel panel-default panel-alt">
                    <div class="panel-heading">
                        <h3 class="panel-title">To Do List</h3>
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-12">
                            <div id="firstLi">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input id="newTodoText" type="text" placeholder="Enter task" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(sizeof($todoLists) > 0)
                                @foreach($todoLists as $todoList)
                                    <div id="todoList_{{ $todoList->id }}">
                                        <small class="pull-right"><?php $date = new DateTime($todoList->time); ?> {{ $date->format('M d') }}</small>
                                        <div class="sender">
                                            <div class="ckbox ckbox-default">
                                                <input onclick="completedTodo({{ $todoList->id }})" type="checkbox" value="1" id="todo_{{ $todoList->id }}" />
                                                <label for="todo_{{ $todoList->id }}">{{ $todoList->todoText }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-sm-4 col-md-3">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h5 class="subtitle mb5">TODAY'S PERFORMANCE</h5>
                        <div class="form-group">
                            <select class="form-control input-sm mb15" id="todayPerformance">
                                <option value="calls">Leads Actioned</option>
                                <option value="appointments">Appointments Booked</option>
                            </select>
                        </div>
                        <div id="donut-chart2" class="ex-donut-chart"></div>
                    </div>
                </div>
            </div>
        @endif
    </div><!-- row -->

    @if($user->userType == 'Team')
        <div class="row">
            <div class="col-sm-6 col-md-4">
                <div class="panel panel-default panel-alt">
                    <div class="panel-heading">
                        <h3 class="panel-title">To Do List</h3>
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-12">
                            <div id="firstLi">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <input id="newTodoText" type="text" placeholder="Enter task" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(sizeof($todoLists) > 0)
                                @foreach($todoLists as $todoList)
                                    <div id="todoList_{{ $todoList->id }}">
                                        <small class="pull-right"><?php $date = new DateTime($todoList->time); ?> {{ $date->format('M d') }}</small>
                                        <div class="sender">
                                            <div class="ckbox ckbox-default">
                                                <input onclick="completedTodo({{ $todoList->id }})" type="checkbox" value="1" id="todo_{{ $todoList->id }}" />
                                                <label for="todo_{{ $todoList->id }}">{{ $todoList->todoText }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('bootstrapModel')
@if($passwordChangesRequired === 'Yes')

<div id="passwordResetModel" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Welcome to SanityOS.com. Please update your password.</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(array('route' => 'user.changepassword', 'class' => 'form', 'id' => 'changePasswordForm')) !!}
                <div id="error" ></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">New Password</label>
                    <div class="col-sm-6">
                        <input type="password" name="newPassword" placeholder="New Password" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Confirm Password</label>
                    <div class="col-sm-6">
                        <input type="password" name="confirmPassword" placeholder="Confirm New Password" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"></label>
                    <div class="col-sm-6">
                        <button class="btn btn-primary">Save Password</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endif

@stop

@section('modelJavascript')
{!! Html::script('assets/js/flot/jquery.flot.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.resize.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.spline.min.js') !!}
{!! HTML::script('assets/js/morris.min.js') !!}
{!! HTML::script('assets/js/raphael-2.1.0.min.js') !!}

<script>


@if($user->userType == 'Single' || $user->userType == 'Team')

$('#newTodoText').keyup(function(e) {
    if(e.keyCode == 13) {
        var todoText = $(this).val();
        if(todoText == '') {
            showError("Please enter some ToDo text");
            return false;
        }

        $.ajax({
            type: 'post',
            url: "{{ URL::route('user.dashboard.createtodo') }}",
            cache: false,
            data: {"todoText": todoText},
            success: function(response) {
                var obj = jQuery.parseJSON(response);
                if(obj.success == "success") {
                    $('<div id="todoList_'+obj.todoID+'"><small class="pull-right">'+obj.date+'</small><div class="sender"><div class="ckbox ckbox-default"><input onclick="completedTodo(' + obj.todoID + ')" type="checkbox" id="todo_'+obj.todoID+'" /><label for="todo_'+obj.todoID+'">'+todoText+'</label></div></div>').insertAfter("#firstLi");
                    $('#newTodoText').val('');
                }
            },
            error: function(xhr, textStatus, thrownError) { }
        });
    }
 });

function completedTodo(todoID) {
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.dashboard.donetodo') }}",
        cache: false,
        data: {"todoID": todoID},
        success: function(response) {
            var obj = jQuery.parseJSON(response);

            if(obj.success == "success") {
                $("#todoList_"+todoID).fadeOut("slow");
            }
        },
        error: function(xhr, textStatus, thrownError) { }
    });
}
@endif
</script>

@if($user->userType == 'Multi' || $user->userType == 'Team')
<script>
$(function(){
    todayPerformance("calls");
    teamPerformance("calls");
});

$('#todayPerformance').change(function() {
    var performanceType = $(this).val();
    todayPerformance(performanceType);
});

$('#teamPerformance').change(function() {
    var actionType = $(this).val();
    teamPerformance(actionType);
});

function todayPerformance(performanceType) {
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.dashboard.ajaxtodayperformance') }}",
        cache: false,
        data: {"actionType": performanceType},
        success: function(response) {
            var obj = jQuery.parseJSON(response);

            if(obj.success == "success") {
                var memberDetails = obj.memberDetails;

                if(memberDetails.length == 0) {
                    $('#donut-chart2').html("<div class=\"no-data-placeholder\">Looks like your team hasn't started yet</div>");
                }
                else{
                    var m1 = new Morris.Donut({
                        element: 'donut-chart2',
                        data: obj.memberDetails,
                        colors: obj.colors
                    });
                }
            }
        }
    });
}

function teamPerformance(actionType) {
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.dashboard.ajaxteamperformance') }}",
        cache: false,
        data: {"actionType": actionType},
        success: function(response) {
            var obj = jQuery.parseJSON(response);

            if(obj.success == "success") {
                var string = "";
                var memberDetails = obj.memberDetails;

                if(memberDetails.length == 0) {
                    $('#teamPerformanceDiv').html("<div class=\"no-data-placeholder\">No Data Available</div>");
                }
                else{
                    memberDetails.forEach(function(member) {
                        string += '<span class="sublabel">'+ member.name +' ('+member.done+')</span><div class="progress progress-sm"><div style="width: '+member.percentage+'%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="80" role="progressbar" class="progress-bar progress-bar-'+member.color+'"></div></div>';
                    });
                    $('#teamPerformanceDiv').html(string);
                }
            }
        }
    });
}
</script>
@endif

<script type="text/javascript">
$(function(){
    $('#passwordResetModel').modal('show');
});

$(function() {
    $("<div id='tooltip'></div>").css({
        position: "absolute",
        display: "none",
        border: "1px solid #fdd",
        padding: "2px",
        "background-color": "#000",
        opacity: 0.80,
        color: "#fff"
    }).appendTo("body");

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.dashboard.ajaxinterestedandnotinterested') }}",
        cache: false,
        data: {},
        success: function(response) {
            var obj = jQuery.parseJSON(response);

            if(obj.success == "success") {
                var interested = obj.interested;
                var notinterested = obj.notInterested;

                if(obj.elementFound == false) {
                    $('#basicflot').html("<div class=\"no-data-placeholder\">No Data Available</div>");
                }
                else {
                    var plot = jQuery.plot(jQuery("#basicflot"),
                            [{ data: interested, label: "&nbsp;Positive", color: "#1CAF9A"}, { data: notinterested, label: "&nbsp;Negative", color: "#428BCA"}],
                            {
                                series: { lines: { show: false }, splines: { show: true, tension: 0.5, lineWidth: 1, fill: 0.45 }, shadowSize: 0},
                                points: { show: true },
                                legend: { position: 'ne' },
                                grid: { hoverable: true, clickable: true, borderColor: '#ddd', borderWidth: 1, labelMargin: 10, backgroundColor: '#fff'},
                                yaxis: { min: 0,  color: '#eee', tickDecimals: 0},
								xaxis: { color: '#eee', min: 1, max: 7, tickDecimals: 0
									, ticks : [
										[1, 'Mon'],
										[2, 'Tue'],
										[3, 'Wed'],
										[4, 'Thu'],
										[5, 'Fri'],
										[6, 'Sat'],
										[7, 'Sun']
									]
								}
                            });

                    $("#basicflot").bind("plothover", function (event, pos, item) {
                        if (item) {
                            var x = item.datapoint[0].toFixed(2), y = item.datapoint[1].toFixed(2);

                            $("#tooltip").html(item.series.label + " = " + Math.round(y)) .css({top: item.pageY+5, left: item.pageX+5}) .fadeIn(200);
                        }
                        else {
                            $("#tooltip").hide();
                        }
                    });
                }
            }
        }
    });
});
</script>
@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}

<script type="text/javascript">
$('#changePasswordForm').submit(function(e) {
    e.preventDefault();
	$.ajax({
        type: 'post',
        url: $('#changePasswordForm').attr('action'),
        cache: false,
        data: $('#changePasswordForm').serialize(),
        beforeSend: function() {
            $('#error').html('<div class="alert alert-info">Submitting..</div>');
        },
        success: function(data) {
            var obj = jQuery.parseJSON(data);
            if(obj.success === false) {
                $('#error').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');
            } else {
                location.reload();
            }
        },
        error: function(xhr, textStatus, thrownError) {
            showError('Something went wrong. Please try again!');
        }
    });
    return false;
});

function sendVerificationLink() {
    $.ajax({
        method: 'post',
        url: '{{ URL::route('user.resendverificatiolink') }}',
        dataType: 'json',
        beforeSend: function (xhr) {
            $('#resendLink').html('Sending....');
        },
        success: function (response) {
            $('#resendLink').html('<i class="glyphicon glyphicon-ok"></i> '+response.message);
        },
        error: function (xhr, textStatus, thrownError) {
            $('#resendLink').html('<i class="glyphicon glyphicon-remove"></i> Please verify your email address. <a href="javascript:;" onclick="sendVerificationLink()">Click here</a> to resend verification email.');
        }
    });
}

</script>
@stop
