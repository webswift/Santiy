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
	<div id="invoice" class="paid">
		<div class="this-is"><strong>Invoice</strong></div>
		<header id="header">
			<div class="invoice-intro"><img src="{{ URL::asset("assets/invoice/img/logo.png") }}"/></div>

			<table class="invoice-meta">
				<tr>
					<td class="invoice-number first">Invoice #</td>
        			<td class="second">{{ $invoiceNumber }}</td>
				</tr>
				<tr>
					<td class="invoice-date first">Date of Invoice</td>
        			<td class="second">{{ $invoiceDate }}</td>
				</tr>
			</table>
		</header>

		<section id="parties">
			<div class="invoice-to">
				<h2>Invoice To:</h2>
				<div id="hcard-Hiram-Roth" class="vcard">
					<a class="url fn" href="#">{{ $customerName }}</a>
					<div class="org">{{ $companyName }}</div>
					<a class="email" href="mailto:email@email.com">{{ $email }}</a>

					<div class="adr">
						<div class="street-address">{{ $address }}</div>
						<span class="country-name">{{ $country }}</span>
					</div>
					<div class="tel"></div>
				</div>
			</div>

			<div class="invoice-status"><h3>Invoice Status</h3><strong>Invoice is <em>Paid</em></strong></div>
		</section>

		<section class="invoice-financials">
			<div class="invoice-items">
				<table>
					<caption>Your Invoice</caption>
					<thead>
					<tr>
						<th>Item &amp; Description</th>
						<th>Quantity</th>
						<th>Price </th>
					</tr>
					</thead>
					<tbody>
						<tr>
							<th>{{ $licenseName }}</th>
							<td>{{ $quantity or '1' }}</td>
							<td>&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="invoice-totals">
				<table>
					<caption>Totals:</caption>
					<tbody>
					<tr>
						<th>Subtotal:</th>
						<td></td>
						<td>{{ $price }}</td>
					</tr>
					<tr>
						<th>Discount/Voucher</th>
						<td></td>
						<td>{{ $discount }}</td>
					</tr>
					<tr>
						<th>Total:</th>
						<td></td>
						<td>{{ $total }}</td>
					</tr>
					</tbody>
				</table>

				<div class="invoice-pay">
					<h5>Invoice Status:</h5>
					<ul></ul>
				</div>
			</div><!-- e: invoice totals -->

			<div class="invoice-notes">
				<h6>Notes &amp; Information:</h6>
				<p>This invoice contains details of your purchase for using SanityOS.com. Your license expiration date can be found under &quot;your profile&quot; in your account. </p>
				<p>For support and guidance on using Sanityos.com please refer to the Help Topics section of your account or email support@sanityos.com</p>
			</div>
		</section>

		<footer id="footer">
			<p> Please save this PDF file or Print it for future reference.</p>
		</footer>
	</div>
</body>
</html>