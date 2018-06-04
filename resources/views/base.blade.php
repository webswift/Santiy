<!DOCTYPE html>

<!-- -->
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<!-- Head BEGIN -->
<head>
<meta charset="utf-8">
<title>@yield("title") - Sanity OS</title>

<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="google-site-verification" content="gTBgvAWAFi8SBwh2oLTcT25FkAbtTWF57cbAKdYEHPA" />

    <meta content="Smart and easy to use lead management software for startups and growing businesses" name="description">
    <meta content="Lead management software, Lead management system, Telemarketing software, call center CRM, easy to use CRM, telesales software, Sales tools for startups" name="keywords">
<link rel="shortcut icon" href="favicon.png">

<!-- Fonts START -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700|PT+Sans+Narrow|Source+Sans+Pro:200,300,400,600,700,900&amp;subset=all" rel="stylesheet" type="text/css">
<!-- Fonts END -->

<!-- Global styles START -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.2/css/font-awesome.css" rel="stylesheet">
<link href="{{ asset('assets/global/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<!-- Global styles END -->

<!-- Page level plugin styles START -->
<link href="{{ asset('assets/global/plugins/fancybox/source/jquery.fancybox.css') }}" rel="stylesheet">
<link href="{{ asset('assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css') }}" rel="stylesheet">
<link href="{{ asset('assets/global/plugins/slider-revolution-slider/rs-plugin/css/settings.css') }}" rel="stylesheet">
<!-- Page level plugin styles END -->

    @yield("css")
<!-- Theme styles START -->
<link href="{{ asset('assets/global/css/components.css') }}" rel="stylesheet">
<link href="{{ asset('assets/frontend/layout/css/style.css') }}" rel="stylesheet">
<link href="{{ asset('assets/frontend/pages/css/style-revolution-slider.css') }}" rel="stylesheet"><!-- metronic revo slider styles -->
<link href="{{ asset('assets/frontend/layout/css/style-responsive.css') }}" rel="stylesheet">
<link href="{{ asset('assets/frontend/layout/css/themes/red.css') }}" rel="stylesheet" id="style-color">
<link href="{{ asset('assets/frontend/layout/css/custom.css') }}" rel="stylesheet">
<!-- Theme styles END -->
</head>
<!-- Head END -->

<!-- Body BEGIN -->
<body class="corporate page-header-fixed" style="overflow-x: hidden;"><!-- add class page-header-fixed, if you want to fix header -->
    <div class="header">
        <div class="container">
            <a class="site-logo" href="{{ URL::route('home') }}"><img src="{{ asset('assets/frontend/layout/img/logo.png') }}" alt="Wise old owl"></a>
            <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>

            <div class="header-navigation pull-right font-transform-inherit">
                <ul>
                    <li class="dropdown-toggle">
                    <li @if(Route::currentRouteName() == "home") class="dropdown active" @endif><a href="{{ URL::route("home") }}">Home</a></li>
                    <li @if(Route::currentRouteName() == "leadmanagement") class="dropdown active" @endif><a href="{{ route('leadmanagement') }}">Lead Management</a></li>
                    <li @if(Route::currentRouteName() == "features") class="dropdown active" @endif><a href="{{ route('features') }}">Features</a></li>
                    <li @if(Route::currentRouteName() == "faq") class="dropdown active" @endif><a href="{{ route('faq') }}">Frequently Asked Questions</a></li>
                    <li @if(Route::currentRouteName() == "pricing") class="dropdown active" @endif><a href="{{ route('pricing') }}">Pricing</a></li>
                    <li @if(Route::currentRouteName() == "contact") class="dropdown active" @endif><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li class="menu-search">
                        <span class="sep"></span>
                        <li><a href="{{ URL::route('user.login')}}">Login</a></li>
                        <div class="search-box"></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    @yield("content")

    <div class="pre-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-6 pre-footer-col">
                    <h2>Get on Board!</h2>
                    <p>In order for your business to grow and survive does your company need to make calls or receive them? Does it need to be prompt and send your prospects information when they request it and do you need to organise and book appointments for your sales team to close these deals? <br><br>Chances are you most likely answered yes to most/all of the above questions and if so SanityOS has the power to transform your company's productivity in just a few simple steps.</p>
                    <div class="photo-stream"></div>
                </div>

                <div class="col-md-4 col-sm-6 pre-footer-col">
                    <h2>Our Address</h2>
                    <address class="margin-bottom-40">
                        SanityOS<br>Slapton Hill Offices<br>
                        Silverstone, Northants<br>
                        NN12 8QD<br>
                        United Kingdom<br>
                        Tel: +44(0)1327 860860<br>
                        Email: <a href="mailto:contact@sanityos.com">contact@sanityos.com</a><br>
                    </address>


                </div>

                <div class="col-md-4 col-sm-6 pre-footer-col" data-twttr-id="twttr-sandbox-0">
                    <h2>Newsletter</h2>
                    <p>Subscribe to our newsletter and stay up to date with the latest news and updates!</p>
                    @if(Session::has('formName') && Session::get('formName') == 'newsletter')
                    @if (count($errors) > 1)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif(count($errors) == 1)
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </div>
                    @endif
                    @endif

                    @if (!Session::has("success"))
                        <form action="{{ URL::route("newsletter") }}" method="POST">
                            {!! Honeypot::generate('password', 'confirmPassword') !!}
                            {!! csrf_field() !!}
                            <div class="input-group">
                                <input type="text" placeholder="youremail@mail.com" class="form-control" name="email">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="submit">Subscribe</button>
                                </span>
                            </div>
                        </form>
                    @else
                        @if(Session::has('formName') && Session::get('formName') == 'newsletter')
                        <div class="alert alert-success">{{ Session::get("success") }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

<div class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 padding-top-10">
                2015 &copy; SanityOS. ALL Rights Reserved. | <a href="http://www.sanityos.com/terms.pdf">Terms of Service</a> | <a href="call-center-crm">Call Center CRM</a> | <a href="sales-tools-for-startups">Sales Tools for StartUps</a>
            </div>
        </div>
    </div>
</div>


<!-- Load javascripts at bottom, this will reduce page load time -->
<!-- BEGIN CORE PLUGINS (REQUIRED FOR ALL PAGES) -->
<!--[if lt IE 9]>
<script src="{{ asset('assets/global/plugins/respond.min.js') }}"></script>
<![endif]-->

<script src="{{ asset('assets/global/plugins/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/global/plugins/jquery-migrate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/global/plugins/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/frontend/layout/scripts/back-to-top.js') }}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->
<script src="{{ asset('assets/global/plugins/fancybox/source/jquery.fancybox.pack.js') }}" type="text/javascript"></script><!-- pop up -->
<script src="{{ asset('assets/global/plugins/uniform/jquery.uniform.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/global/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js') }}" type="text/javascript"></script><!-- slider for products -->

<script src="https://maps.google.com/maps/api/js?sensor=true" type="text/javascript"></script>
<script src="{{ asset('assets/global/plugins/gmaps/gmaps.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/frontend/pages/scripts/contact-us.js') }}" type="text/javascript"></script>

<script src="{{ asset('assets/global/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.plugins.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/global/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.revolution.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/global/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.tools.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/frontend/pages/scripts/revo-slider-init.js') }}" type="text/javascript"></script>

<script src="{{ asset('assets/frontend/layout/scripts/layout.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        Layout.init();
        Layout.initOWL();
        RevosliderInit.initRevoSlider();
        Layout.initTwitter();
        Layout.initUniform();
        ContactUs.init();
        Layout.initTwitter();

        var hash = window.location.hash;
        if (hash == "#success") {
          $("#contactform").css("display", "none");
          $("#success").css("display", "block");
        }
    });
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-61124245-1', 'auto');
  ga('send', 'pageview');

</script>

<script>
			$(document).ready(function(){
				$("#eur").click(function(){
					$("#peep").text("€12");
					$("#eur").addClass( "active" );
					$("#dollar").removeClass( "active" );
					$("#gbp").removeClass( "active" );
					
				});
				$("#dollar").click(function(){
					$("#peep").text("$14");
					$("#dollar").addClass( "active" );
					$("#eur").removeClass( "active" );
					$("#gbp").removeClass( "active" );
				});
				$("#gbp").click(function(){
					$("#peep").text("£10");
					$("#gbp").addClass( "active" );
					$("#eur").removeClass( "active" );
					$("#dollar").removeClass( "active" );
				});
			});
			</script>

    <!-- END PAGE LEVEL JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>