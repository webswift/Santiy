<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="images/favicon.png" type="image/png">

    <title>@yield('title') - Sanity OS</title>

    {!! Html::style('assets/css/style.default.css') !!}
    {!! HTML::style('assets/css/jquery.gritter.css') !!}
    @yield('css')

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    {!! HTML::script("assets/js/html5shiv.js") !!}
    {!! HTML::script("assets/js/respond.min.js") !!}
    <![endif]-->
</head>

<body class="signin">

<section>

@yield('content')

@yield('bootstrapModel')

</section>

{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/modernizr.min.js') !!}
{!! HTML::script('assets/js/jquery.sparkline.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}

{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/retina.min.js') !!}

{!! HTML::script('assets/js/select2.min.js') !!}

@yield('modelJavascript')

{!! HTML::script('assets/js/custom.js') !!}

<script>
    jQuery(document).ready(function(){

        jQuery(".select2").select2({
            width: '100%',
            minimumResultsForSearch: -1
        });

        jQuery(".select2-2").select2({
            width: '100%'
        });


        // Please do not use the code below
        // This is for demo purposes only
        var c = jQuery.cookie('change-skin');
        if (c && c == 'greyjoy') {
            jQuery('.btn-success').addClass('btn-orange').removeClass('btn-success');
        } else if(c && c == 'dodgerblue') {
            jQuery('.btn-success').addClass('btn-primary').removeClass('btn-success');
        } else if (c && c == 'katniss') {
            jQuery('.btn-success').addClass('btn-primary').removeClass('btn-success');
        }

    });
</script>

@yield('javascript')

</body>
</html>
