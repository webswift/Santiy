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

      <section id="price-table-sec">
        <div class="container">

          <div class="row">
            <div class="col-md-12">
              <div class="simple-price text-center padding-bottom">
                <h1>Simple pricing. No contracts.</h1>
                <h4>Get organized, grow sales and save time <a href="signup" class="read-more">Sign up Today!</a></h4>
                <a href="signup"><img src="img/banner.gif"></a>
              </div>
            </div>

          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="pricing-wrapper comparison-table clearfix style-3">
                <div class="col-md-3 pricing-col list-feature">
                  <div class="pricing-card">
                    <div class="pricing-header">
                      <strong><h2>Choose Your Plan</h2></strong>
                      <p><br><br>
                        (Plans are billed annually)</p>
                    </div>
                    <div class="pricing-feature">
                      <li>
                        <p>Lead & Opportunity Management</p>
                      </li>
                      <li>
                        <p>Book Follow up calls</p>
                      </li>
                      <li>
                        <p>Book Sales Appointments</p>
                      </li>
                      <li>
                        <p>Store unlimited leads</p>
                      </li>
                      <li>
                        <p>Build customised lead information forms</p>
                      </li>
                      <li>
                        <p>Build customised contact/signup forms</p>
                      </li>
                      <li>
                        <p>Send individual emails</p>
                      </li>
                      <li>
                        <p>Design &amp; send  Mass marketing emails</p>
                      </li>
                      <li>
                        <p>Inbound call management & Easy lead look-up</p>
                      </li>
                      <li>
                        <p>Sales performance reporting</p>
                      </li>
                      <li>
                        <p>Custom reporting</p>
                      </li>
                      <li>
                        <p>Smart Export in PDF or CSV</p>
                      </li>
                      <li>
                        <p>Calendar Sync</p>
                      </li>
                      <li>
                        <p>Power Dialling</p>
                      </li>
                      <li>
                        <p>Users included</p>
                      </li>
                      <li>
                        <p>Customer Support</p>
                      </li>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 pricing-col person">
                  <div class="pricing-card">
                    <div class="pricing-header">
                      <h5>Single license<br><br>
                      </h5>
                      <div class="price-box">
                        <div class="price">5
                          <div class="currency">$</div>
                          <div class="plan">Per Month<br>
                          </div>


                        </div>
                      </div>
                    </div>
                    <div class="pricing-feature">
                      <li>
                        <p>
                          <i class="fa fa-check available"></i>
                        </p>
                      </li>
                      <li>
                        <p>
                          <i class="fa fa-check available"></i>
                        </p>
                      </li>
                      <li>
                        <p>
                          <i class="fa fa-check available"></i>
                        </p>
                      </li>
                      <li>
                        <p>
                          <i class="fa fa-check available"></i>
                        </p>
                      </li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br></i>
                        </p>
                      </li>
                      <li><p>7000 per month included</p></li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p>Single user only, non upgradable</p></li>
                      <li>
                        <p>by Email</p></li>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 pricing-col current unlim">
                  <div class="pricing-card">
                    <div class="pricing-header">
                      <h5>Multi-License<br>
                        (3 Users included)</h5>

                      <div class="price-box">
                        <div class="price">40
                          <div class="currency">$</div>
                          <div class="plan">Per Month</div>
                        </div>
                      </div>
                    </div>
                    <div class="pricing-feature">
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"><br><br></i></p></li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p>15,000 included per month</p></li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p>3 Users included- Add more any time!</p></li>
                      <li>
                        <p>by Email</p></li>


                    </div>

                  </div>
                </div>
                <div class="col-md-3 pricing-col business">
                  <div class="pricing-card">
                    <div class="pricing-header">
                      <h5>Enterprise<br>
                        <br>
                      </h5>
                      <div class="price-box">
                        <div class="price">POA

                        </div>
                      </div>
                    </div>
                    <div class="pricing-feature">
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"><br><br></i></p></li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li>
                        <p>&gt;500,000 included per month</p></li>
                      <li>
                        <p>
                          <i class="fa fa-check available"><br><br></i>
                        </p>
                      </li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p><i class="fa fa-check available"></i></p></li>
                      <li><p>Unlimited</p></li>
                      <li>
                        <p>by Email &amp; Phone</p></li>
                    </div>

                  </div>
                </div>
              </div><!-- Pricing Wrapper End -->
              <div class="col-md-12">
                <div class="singn-up-wrap text-center"> <a href="signup" class="signup-btn">Sign Up for a Free 14 day Trial</a> </div>
              </div>
            </div>

          </div><!-- End .row -->
          <div class="row row-padding-small">
          </div><img src="img/client-logo.png"></div>
      </section><!-- End .row -->

  <section id="follow" class="section-gray section-padding">
    <div class="container">
      <div class="row">
        <div class="col-md-6">
          <img class="follow-left-image" src="img/team_upgrade.jpg">
        </div>
        <div class="col-md-6">
          <div class="follow-right">
            <h1>Pay once for new users!</h1>
            <p><strong>That's Right!</strong><br>
              When you add new team members to your account on SanityOS you will only ever have to pay for them once! When you renew your annual subscription you will only have to pay the base license cost (which includes 1 account with Administrator privileges and 2 Team users!).</p>
            <p> It's as simple as that, you'll never have to re-purchase your additional team members again!</p>
            <p>SanityOS is committed to delivering our users the best possible service in its class of cloud business applications for the best possible value. We Guarantee our service to be the best solution for startups to medium businesses available.</p>
          </div>
        </div>
      </div><!--End .row-->
    </div><!--End .container-->
  </section><!--End #Follow Customizable-->
  </div>
@endsection