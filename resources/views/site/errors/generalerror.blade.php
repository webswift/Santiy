<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" href="images/favicon.png" type="image/png">

  <title>Sanity OS</title>

  {!! HTML::style('assets/css/style.default.css') !!}

<body class="notfound">


<section>

  <div class="notfoundpanel">
      <h1>Error!</h1>
      <h3>An error occured processing your request!</h3>
      <h4><b>Message:</b> {!! $message !!}</h4>
      <button class="btn btn-success" onclick="history.back(1);">Go Back to Previous Page</button>

    </div><!-- notfoundpanel -->

</section>

</body>
</html>