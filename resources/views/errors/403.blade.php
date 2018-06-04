<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" href="images/favicon.png" type="image/png">

  <title>Not Authorized - Sanity OS</title>

  {!! Html::style('assets/css/style.default.css') !!}

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  {!! HTML::script("assets/js/html5shiv.js") !!}
  {!! HTML::script("assets/js/respond.min.js") !!}
  <![endif]-->
</head>

<body class="notfound">


<section>

    <div class="notfoundpanel">
        <h1>403!</h1>
        <h3>You are Not Authorized to assess this page.</h3>
        <p>{{ $exception->getMessage() }}</p>

        <button class="btn btn-primary" onclick="javascript:history.back();">Back</button>
    </div><!-- notfoundpanel -->
  
</section>


{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/modernizr.min.js') !!}
{!! HTML::script('assets/js/jquery.sparkline.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}

{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/retina.min.js') !!}

{!! HTML::script('assets/js/custom.js') !!}
<script>
    jQuery(document).ready(function(){
        "use strict";
        
        // Do not use the code below
        // For demo purposes only
        var c = jQuery.cookie('change-skin');
        if (c && c == 'katniss') {
            jQuery('.btn-success').addClass('btn-primary').removeClass('btn-success');
        }
        
    });
</script>

</body>
</html>
