<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<meta content='{{ csrf_token() }}' name='csrf-token'>

<title>@yield('title') - Sanity OS</title>

<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
{!! Html::style('assets/css/style.default.css') !!}
{!! HTML::style('assets/css/jquery.gritter.css')  !!}
{!! HTML::style('assets/css/custom.css')  !!}

@yield('css')

<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
{!! HTML::script("assets/js/html5shiv.js") !!}
{!! HTML::script("assets/js/respond.min.js") !!}
<![endif]-->
</head>

<body>
<div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>

<section>
    <div class="leftpanel">
        <div class="logopanel">
            <img src="{{ URL::asset("assets/images/logo.png") }}" style="height: 30px;"/>
        </div>
        <div class="leftpanelinner">
            @if($user->accountStatus != 'LicenseExpired')
                <h5 class="sidebartitle">Navigation</h5>
                <ul class="nav nav-pills nav-stacked nav-bracket">
                    <li class="{{ $dashboardMenuActive or '' }}"><a href="{{ URL::route("user.dashboard") }}"><i class="fa fa-home"></i> <span>Dashboard</span></a></li>
                    <li class="nav-parent {{ $leadsMenuActive or ''}}">
                        <a href=""><i class="fa fa-edit"></i> <span>Lead Management</span></a>
                        <ul class="children" style="{{ $leadsStyleActive or ''}}">
							<li class="{{ $leadsStartActive or '' }} {{ $leadsCampaignActive or '' }} {{ $leadsImportActive or '' }}">
								<a href="{{ URL::route('user.campaigns.start') }}"><i class="fa fa-caret-right"></i> Campaign Manager</a>
							</li>
                            <li class="{{ $leadsListingActive or '' }}"><a href="{{ URL::route('user.campaigns.leads') }}"><i class="fa fa-caret-right"></i> Leads &amp; Contacts </a></li>
                            <li class="{{ $leadsInboundStyleActive or '' }}"><a href="{{ URL::route('user.leads.inboundcall') }}"><i class="fa fa-caret-right"></i> Inbound Leads<span class="pull-right badge badge-success">{{ \App\Http\Controllers\CommonController::getTotalUnActionedLandingLeads($user) }}</span></a></li>
                            <li class="{{ $leadsExportActive or '' }}"><a href="{{ URL::route('user.campaigns.listcampaigns') }}"><i class="fa fa-caret-right"></i> Export Leads &amp; Contacts </a></li>
                        </ul>
                    </li>
                    <li class="nav-parent {{ $appointmentsMenuActive or ''}}">
                        <a href=""><i class="glyphicon glyphicon-calendar"></i><span>Appointments</span><span class="pull-right badge badge-success">{{ $totalPendingCallBacks }}</span></a>
                        <ul class="children" style="{{ $appointmentsStyleActive or ''}}">
                            <li class="{{ $appointmentsPendingStyleActive or '' }}"><a href="{{ URL::route('user.leads.pendingcallbacks') }}"><i class="fa fa-caret-right"></i> View Pending call backs</a></li>
                            <li class="{{ $appointmentsMenuStyleActive or '' }}"><a href="{{ URL::route('user.leads.appointments') }}"><i class="fa fa-caret-right"></i> View Appointment calendar</a></li>
                        </ul>
                    </li>
                    {{--@if($user->userType != 'Multi')--}}
                        <li class="nav-parent {{ $geoExportsMenuActive or ''}}"><a href=""><i class="fa fa-list"></i> <span>Marketing Tools</span></a>
                            <ul class="children" style="{{ $geoExportsStyleActive or ''}}">
                                <li class="hidden {{ $settingsFormStyleActive or '' }}"><a href="{{ URL::route('user.forms') }}"><i class="fa fa-caret-right"></i> Create/Edit Forms</a></li>
                                <li class="{{ $settingsEmailStyleActive or '' }}"><a href="{{ URL::route('user.email.home') }}"><i class="fa fa-caret-right"></i> Email tools & Templates</a></li>
                            </ul>
                        </li>
                    {{--@endif--}}
                    <li class="nav-parent {{ $settingsMenuActive or ''}}">
                        <a href=""><i class="glyphicon glyphicon-wrench"></i> <span>Settings</span></a>
                        <ul class="children" style="{{ $settingsStyleActive or ''}}">
                            <li class="{{ $settingsMenuStyleActive or '' }}"><a href="{{ URL::route('user.profile') }}"><i class="fa fa-caret-right"></i> Manage Your Account</a></li>
                        </ul>
                    </li>
                    @if($user->userType == 'Single' || $user->userType == 'Multi')
                        <li class="nav-parent {{ $teamsMenuActive or ''}}">
                            <a href=""><i class="fa fa-group"></i> <span>Team</span></a>
                            <ul class="children" style="{{ $teamsStyleActive or ''}}">
                                @if($user->userType == 'Multi')
                                    <li class="{{ $teamsStaffStyleActive or '' }}"><a href="{{ URL::route('user.teams.staffteam') }}"><i class="fa fa-caret-right"></i> Create/Manage Team Users</a></li>
                                @endif
                                <li class="{{ $teamsSalesStyleActive or '' }}"><a href="{{ URL::route('user.teams.salesteam') }}"><i class="fa fa-caret-right"></i> Create/Manage Sales Team</a></li>
                            </ul>
                        </li>
                    @endif

                    @if($user->userType == 'Team' || $user->userType == 'Multi')
                        <li class="nav-parent {{ $pushMessageMenuActive or ''}}"><a href=""><i class="fa fa-envelope-o"></i> <span>Push Message</span></a>
                            <ul class="children" style="{{ $pushMessageStyleActive or ''}}">
                                <li class="{{ $pushMessageMenuStyleActive or '' }}"><a href="{{ URL::route('user.pushmessage') }}"><i class="fa fa-caret-right"></i> Messages</a></li>
                            </ul>
                        </li>
                    @endif

                    <li class="{{ $statisticsMenuActive or '' }}"><a href="{{ URL::route("user.statistics") }}"><i class="fa fa-pie-chart"></i> <span>Reports</span></a></li>
                    <li class="{{ $helpTopicMenuActive or ''}}"><a href="{{ $userHelpUrl }}" target="_blank"><i class="fa fa-book"></i> <span>Help Topics</span></a></li>
                </ul>
                <div class="infosummary" style="visibility: hidden;">
                    <h5 class="sidebartitle">Information Summary</h5>
                    <ul>
                        <li id="sidebarTotalCallMade">
                            <div class="datainfo"><span >CALLS MADE TODAY</span><h4>0</h4></div>
                            <div id="sidebar-chart" class="chart"></div>
                        </li>
                        <li id="sidebarTotalInterest">
                            <div class="datainfo"><span>NUMBER OF INTERESTED LEADS</span><h4>0</h4></div>
                            <div id="sidebar-chart2" class="chart"></div>
                        </li>
                        <li id="sidebarTotalBookAppointment">
                            <div class="datainfo"><span>BOOKED APPOINTMENTS</span><h4>0</h4></div>
                            <div id="sidebar-chart3" class="chart"></div>
                        </li>
                        <li id="topPerformer">
                            <div class="datainfo">
                                @if($user->userType == 'Single')
                                    <span>THIS WEEK HIGHEST RATE</span>
                                @else
                                    <span>TOP PERFORMER</span>
                                @endif
                                <h4>0</h4>
                            </div>
                            <div id="sidebar-chart4" class="chart"></div>
                        </li>
                        <li id="successPercentage">
                            <div class="datainfo">
                                @if($user->userType == 'Single')
                                    <span>AVERAGE CALL TIME</span>
                                @else
                                    <span>SUCCESS RATE</span>
                                @endif
                                <h4>0%</h4>
                            </div>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
    <div class="mainpanel">
        <div class="headerbar">
            <a class="menutoggle"><i class="fa fa-bars"></i></a>
            @if($user->accountStatus != 'LicenseExpired')
			<a class="header-addnewlead"><i class="fa fa-plus"></i></a>
			@endif
            <div class="header-right">
                <ul class="headermenu">
                    <li>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                {{ $user->firstName }} {{ $user->lastName }}
                                @if(!Route::is('user.payment.showContainer'))
                                <span class="caret"></span>
                                @endif
                            </button>
                            @if(!Route::is('user.payment.showContainer'))
                            <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                                <li><a href="{{ URL::route("user.profile") }}"><i class="glyphicon glyphicon-user"></i> My Profile</a></li>
                                <li><a href="{{ URL::route("user.logout") }}"><i class="glyphicon glyphicon-log-out"></i> Log Out</a></li>
                            </ul>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        @yield('content')
    </div>
    @yield('bootstrapModel')

<div id="dlgCreateNewLead" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
				<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
                <h4 class="modal-title">Add a new record</h4>
            </div>
            <div class="modal-body form-horizontal">
            </div>
	        <div class="panel-footer">
	        	<div class="row">
                    <div class="col-sm-8">
                        <button id="btnCreateNewLead" class="btn btn-sm btn-primary">Create new lead</button>
                    </div>
	        	</div>
	        </div>
        </div>
    </div>
</div>
</section>

{!! Html::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('assets/js/jquery-ui-1.10.3.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/modernizr.min.js') !!}
{!! HTML::script('assets/js/jquery.sparkline.min.js') !!}
{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/retina.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}
{!! HTML::script('/assets/js/jquery.blockui.min.js') !!}

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>

@yield('modelJavascript')

{!! HTML::script('assets/js/select2.min.js') !!}
{!! HTML::script('assets/js/jquery.gritter.min.js') !!}

<script type="text/javascript">
    var assetsPath = "{{ URL::asset("assets") }}/images/";

    $(document).ready(function() {
        $('.dataTables_wrapper select').select2({
            minimumResultsForSearch: -1
        });
    });
</script>
@yield('javascript')

<script type="text/javascript">
@if($user->accountStatus != 'LicenseExpired')
$('.header-addnewlead').click(function() {
	$.get('{{URL::route('user.campaigns.getAddNewLeadDialog')}}', function(response) {
		$('#dlgCreateNewLead').find('.modal-body').html(response);
		$('#dlgCreateNewLead').modal('show');
	});
});

$('#btnCreateNewLead').click(function() {
	$campaignId = $('#selActiveCampaigns').val();
	if($campaignId == '') {
		showError("Please select a campaign");
		return false;
	}
	location.href = "{{ URL::route('user.leads.createleadforcampaign') }}/" + $campaignId;
});

$.ajax({
    type: 'post',
    url: "{{ URL::route('user.panel') }}",
    cache: false,
    data: {},
    beforeSend: function() {
        //$('#error').html('<div class="alert alert-info">Submitting..</div>');
    },
    success: function(data) {
        var obj = jQuery.parseJSON(data);
        var hidden = 0;

        if(obj.success == 'success') {
            if(obj.totalCallMade == 0) {
                jQuery('#sidebarTotalCallMade').hide();
                hidden ++;
            }
            else {
                jQuery("#sidebarTotalCallMade").show();
                jQuery('#sidebar-chart').sparkline(obj.todayCallMade[0], {
                type: 'bar',
                    height:'30px',
                    barColor: '#428BCA'
                });
            }

            $('#sidebarTotalCallMade h4').html(obj.totalCallMade);

            if(obj.totalInterest == 0) {
                $("#sidebarTotalInterest").hide();
                hidden ++;
            }
            else {
                $("#sidebarTotalInterest").show();
                jQuery('#sidebar-chart2').sparkline(obj.todayInterest[0], {
                    type: 'bar',
                    height:'30px',
                    barColor: '#D9534F'
                });
            }

            $('#sidebarTotalInterest h4').html(obj.totalInterest);

            if(obj.totalBookAppointment == 0) {
                $("#sidebarTotalBookAppointment").hide();
                hidden ++;
            }
            else {
                $("#sidebarTotalBookAppointment").show();
                jQuery('#sidebar-chart3').sparkline(obj.todayBookAppointment[0], {
                    type: 'bar',
                    height:'30px',
                    barColor: '#1CAF9A'
                });
            }

            $('#sidebarTotalBookAppointment h4').html(obj.totalBookAppointment);

            if(obj.topPerformerName == '' ) {
                $("#topPerformer").hide();
                hidden ++;
            }
            else {
                $("#topPerformer").show();
                jQuery('#sidebar-chart4').sparkline(obj.topPerformerArray[0], {
                    type: 'bar',
                    height:'30px',
                    barColor: '#428BCA'
                });
            }

            $('#topPerformer h4').html(obj.topPerformerName);

            if ( obj.successPercentage == 0) {
                hidden++;
                $("#successPercentage").hide();
            }
            else {
                $("#successPercentage").show();
                $('#successPercentage h4').html(obj.successPercentage);
            }

            if(hidden < $(".datainfo").length) {
                $(".infosummary").css("visibility", "visible");
            }
            else {
                $(".infosummary").css("visibility", "hidden");
            }
        }
    }
});

setInterval(function() {
    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.pushmessage.pushmessagenotification') }}",
        cache: false,
        data: {},
        beforeSend: function() {
            //$('#error').html('<div class="alert alert-info">Submitting..</div>');
        },
        success: function(data) {
            var obj = jQuery.parseJSON(data);

            if(obj.success == 'success') {
                var notification = obj.notifications;
                notification.forEach(function(msg){
                    jQuery.gritter.add(msg);
                });
            }
        }
    });
}, 30000);
@endif
</script>
</body>
</html>
