<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta content='{{ csrf_token() }}' name='csrf-token'>

	<title>@yield('title')</title>

	<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
	{!! Html::style('assets/css/style.default.css') !!}
	{!! HTML::style('assets/css/jquery.gritter.css') !!}
	@yield('css')

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	{!! HTML::script("assets/js/html5shiv.js") !!}
    {!! HTML::script("assets/js/respond.min.js") !!}

	<![endif]-->
</head>

<body>
<!-- Preloader -->
<div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>

<section>
	<div class="leftpanel">
		<div class="logopanel">
			<img src="{{ URL::asset("assets/images/logo.png") }}" style="height: 30px;"/>
		</div><!-- logopanel -->

		<div class="leftpanelinner">
			<h5 class="sidebartitle">Navigation</h5>

			<ul class="nav nav-pills nav-stacked nav-bracket">
				<li class="{{ $dashboardMenuActive or ''}}"><a href="{{ URL::route('admin.dashboard') }}"><i class="fa fa-home"></i> <span>Dashboard</span></a></li>
				<li class="{{ $dataActivityMenuActive or ''}}"><a href="{{ URL::route('admin.dashboard.data') }}"><i class="fa fa-list"></i> <span>Data Activity</span></a></li>
				<li class="{{ $usersMenuActive or ''}}"><a href="{{ URL::route('admin.users') }}"><i class="fa fa-user"></i> <span>Users</span></a></li>
				<li class="nav-parent {{ $licensesMenuActive or ''}}">
					<a href=""><i class="fa fa-edit"></i> <span>License</span></a>
					<ul class="children" style="{{ $licensesStyleActive or ''}}">
						<li><a href="{{ URL::route('admin.licenses.searchtransactions') }}"><i class="fa fa-caret-right"></i> Search Transactions</a></li>
						<li class="{{ $shoppingCartMenuActive or '' }}"><a href="{{ URL::route('admin.licenses.shoppingcart') }}"><i class="fa fa-caret-right"></i> Shopping Cart Management</a></li>
					</ul>
				</li>
				<li class="{{ $settingMenuActive or ''}}"><a href="{{ URL::route('admin.setting') }}"><i class="fa fa-cog"></i> <span>Settings</span></a></li>
				<li class="{{ $FormMenuActive or ''}}"><a href="{{ URL::route('admin.forms.createoredit') }}"><i class="fa fa-file-text-o"></i> <span>Form Settings</span></a></li>
				<li class="{{ $exportUserDataActive or ''}}"><a href="{{ URL::route('admin.users.exportuserdata') }}"><i class="fa fa-users"></i> <span>Export User Data</span></a></li>
				<li class="{{ $emailTemplateMenuActive or ''}}"><a href="{{ URL::route('admin.emailtemplates') }}"><i class="fa fa-envelope-o"></i> <span>Email Templates</span></a></li>
				<li class="{{ $helpTopicMenuActive or ''}}"><a href="{{ URL::route('admin.helptopics') }}"><i class="fa fa-book"></i> <span>User Help Topics</span></a></li>
				<li class="{{ $pushMessageMenuActive or ''}}"><a href="{{ URL::route('admin.pushmessage') }}"><i class="fa fa-book"></i> <span>Push Message</span></a></li>
				<li class="{{ $mailSettingsMenuActive or ''}}"><a href="{{ URL::route('admin.mailsettings') }}"><i class="fa fa-inbox"></i> <span>Mail Server Settings</span></a></li>
				<li class="{{ $massMailSettingsMenuActive or ''}}"><a href="{{ URL::route('admin.mass.mailsettings') }}"><i class="fa fa-inbox"></i> <span>Mass Mail Server Settings</span></a></li>

			</ul>
		</div><!-- leftpanelinner -->
	</div><!-- leftpanel -->

	<div class="mainpanel">
		<div class="headerbar">
			<a class="menutoggle"><i class="fa fa-bars"></i></a>
			<div class="header-right">
				<ul class="headermenu">
					<li>
						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								{{ Auth::admin()->get()->firstName }} {{ Auth::admin()->get()->lastName }}
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-usermenu pull-right">
								<li><a href="{{ URL::route('admin.logout') }}"><i class="glyphicon glyphicon-log-out"></i> Log Out</a></li>
							</ul>
						</div>
					</li>
				</ul>
			</div>
		</div>

		@yield('content')
	</div><!-- mainpanel -->
	@yield('passwordResetModel')
  @yield('bootstrapModel')
</section>


{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('assets/js/jquery-ui-1.10.3.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/modernizr.min.js') !!}
{!! HTML::script('assets/js/jquery.sparkline.min.js') !!}
{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/retina.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}
{!! HTML::script('assets/js/jquery.blockui.min.js') !!}
<script>
$(function(){
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
});
var assetsPath = '{{ asset('assets/global/img') }}/';
</script>
@yield('modelJavascript')
{!! HTML::script('assets/js/jquery.gritter.min.js') !!}
{!! HTML::script('assets/js/select2.min.js') !!}
{!! HTML::script('assets/js/custom.js') !!}
@yield('javascript')

</body>
</html>
