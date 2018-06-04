@extends("base")

@section("title")
  Pricing - Lead Management Software
@endsection

@section("css")
  <link href="{{ asset("assets/frontend/layout/css/fonts.css") }}" type="text/css" rel="stylesheet" media="all">
  <link href="{{ asset("assets/frontend/layout/css/vendor.css") }}" type="text/css" rel="stylesheet" media="all">

  <!-- Pricing Setting -->
  <link href="{{ asset("assets/frontend/layout/css/settings.css") }}" type="text/css" rel="stylesheet" media="all">
  <link href="{{ asset("assets/frontend/layout/css/pricing-style.css") }}" type="text/css" rel="stylesheet" media="all">
  <!-- Pricing Setting -->

  <!-- Pricing Color -->
  <link href="{{ asset("assets/frontend/layout/css/blue.css") }}" type="text/css" rel="stylesheet" media="all">
  <!-- Pricing Table CSS End -->
@endsection

@section("content")


<div class="main">
<div class="main pricing_main">

          <section id="price-table-sec" class="prices_sec">
            <div class="container">
                <div class="col-md-12">
                  <div class="simple-price text-center padding-bottom">
                    <h1 class="p_heading">Simple pricing. No contracts.
</h1>
                  </div>
                </div>
              </div>
              
              <!-- price col -->
			
              	<div class="price_col_main">
                	<span class="p_pot"></span>
                	<span class="p_books"></span>
                	<span class="p_pen"></span>
                    <div class="col-sm-6 price_list_col">
                    	<ul>
                        	<li>Demand Generation</li>
                            <li>Lead Management</li>
                            <li>Book Follow up calls & Appointments</li>
							<li>Easily build Web/Contact forms</li>
							<li>Fully Customizable</li>
							<li>Smart Reporting</li>
                            <li>Basic & Advanced Exporting</li>
                            <li>Time Stamp notes & Lead History</li>
                            <li>Send UNLIMITED Marketing Emails</li>
                            <li>Much More Included!</li>

                        </ul>
                    </div>
					<div class="price_right_col">
						<h1><span id="peep">£10</span></h1>
						<p class="p_light">Per user per month</p>
						<div class="currencySelector">
							<strong>Show prices in </strong>
							<wbr>
							<a href="javascript:void(0);" id="eur" class="">EUR</a>	
							<a href="javascript:void(0);" id="dollar" class="">USD</a>
							<a href="javascript:void(0);" id="gbp" class="active">GBP</a>
						</div>
					</div>
					
					<div class="col-md-12">
                <div class="singn-up-wrap text-center signup_btn"> <a href="https://www.sanityos.com/signup" class="signup-btn">Sign Up for a Free 14 day Trial</a> </div>
              </div>
				  <div class="col-md-12 text-center">
						<h2 class="webinar_txt">Free setup and one-to-one webinar with every purchase</h2>
				  </div>
                </div>
               <!-- /price col end-->

              <!-- End .row -->
              <div class="row row-padding-small">
            <div class="col-md-12 text-center">
                      
                        <img src="img/client-logo.png">
                      </div></div>
              </div><!-- End .row -->
          
            
            <section id="follow" class="section-gray section-padding">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <br><br><iframe width="480" height="300" src="https://www.youtube.com/embed/Ex-T3Jbtwbs?start=31" frameborder="0" allowfullscreen></iframe><br><br></div>
          <div class="col-md-6">
            <div class="follow-right">
              <h1>Want a Demo?</h1>
            <p><strong>Check out our Getting Started video Tutorial!</strong></p>
                        <p>We're confident that there isn't a solution out there that can help you boost the demand of your businesses product or service as well as SanityOS.</p>
            <p>If you're still not sure why not<a href="/contact"> contact us</a> for a FREE consulation and demonstration of our Enterprise software application.</p>
            </div>
          </div>
        </div><!--End .row-->
      </div><!--End .container-->
  </div>
@endsection
 

