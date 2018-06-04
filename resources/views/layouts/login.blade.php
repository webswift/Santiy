<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" href="images/favicon.png" type="image/png">

  <title>@yield('title')</title>

  <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
  {!! Html::style('assets/css/style.default.css') !!}
  {!! HTML::style('assets/css/jquery.gritter.css')  !!}
  {!! HTML::style('assets/css/signup.css') !!}
  @yield('css')

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  {!! HTML::script('assets/js/html5shiv.js') !!}
  {!! HTML::script('assets/js/respond.min.js') !!}
  <![endif]-->
</head>

<body class="signin">
<section>
    <div class="signinpanel">
        <div class="row">
            <div class="col-md-7">
                @yield('loginSideBar')
            </div>
            <div class="col-md-5">
                @yield('loginForm')
            </div>
        </div>
        <div class="signup-footer">
        </div>
    </div>
    @yield('bootstrapModel')
</section>

{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/modernizr.min.js') !!}
{!! HTML::script('assets/js/jquery.sparkline.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}
{!! HTML::script('assets/js/jquery.gritter.min.js') !!}

@yield('modelJavascript')

{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/retina.min.js') !!}

{!! HTML::script('assets/js/custom.js') !!}
@yield('javascript')
</body>
</html>
