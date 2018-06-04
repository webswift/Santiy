<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>SanityOS Invoice</title>

    {!! Html::style('assets/invoice/css/reset.css', array('media' => 'all')) !!}
    {!! HTML::style('assets/invoice/css/style.css', array('media' => 'all')) !!}
    {!! HTML::style('assets/invoice/css/print.css', array('media' => 'print')) !!}

</head>
<body>
<!-- begin markup -->


<div id="invoice" class="paid">


	<div class="this-is">
		<strong>Appointment Details</strong>
	</div><!-- invoice headline -->

    <header id="header" style="margin-top: 10px;">
		{{--
        <div class="invoice-intro">
            <img src="{{ URL::asset("assets/invoice/img/logo.png") }}"/>

        </div>
		--}}

        <table class="invoice-meta" style="margin-top: 20px;">
            <tr><td class="invoice-number first"><strong>Salesman Name: </strong></td>
            <td class="second">{{ $salesManName }}</td></tr>
            <tr><td class="invoice-date first"><strong>Appointment Date: </strong></td>
            <td class="second">{{ $bookingDate }}</td></tr>
            <tr><td class="invoice-date first"><strong>Appointment Time: </strong></td>
            <td class="second">{{ $bookingTime }}</td></tr>
            <tr><td class="invoice-date first"><strong>UTC Time: </strong></td>
            <td class="second">{{ $bookingUTCTime }}</td></tr>
        </table>
    </header>

	<!-- e: invoice header -->

	<section class="invoice-financials" style="margin-top: 10px;">

		<div class="invoice-items">
			<table>
				<caption>Appointment Details</caption>
				<tbody>
				@if(sizeof($leadCustomData) > 0)
				@foreach($leadCustomData as $data)
					<tr>
						<th>{{ $data->fieldName }}</th>
						<td>@if($data->value == "") - @else {{ $data->value }} @endif</td>
					</tr>
			    @endforeach
			    @endif
				</tbody>

			</table>
		</div><!-- e: invoice items -->

	</section><!-- e: invoice financials -->


	<footer id="footer">
		<p>
			Please save this PDF file or Print it for future reference.</p>
	</footer>


</div><!-- e: invoice -->


</body>
</html>
