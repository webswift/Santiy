@extends("base")

@section("title")
	Lead Management
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
      <section id="lead-management" class="text-center">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <h1>Lead Management Software</h1>
              <p>SanityOs improves the workflow and focus of converting leads into sales with total efficiency. Simple, informative and fully customizable
                <a href="signup" class="read-more">Sign up Today!</a></p>
            </div>
          </div>
        </div>

      </section>

      <section id="sanity-caraouser">
        <div class="container">
          <!-- BEGIN SIDEBAR & CONTENT -->
          <div class="row">
            <!-- BEGIN CONTENT -->
            <div class="col-md-12 col-sm-12">
              <div class="row margin-bottom-30">

                <!-- Bootstrap Caraosel Start -->

                <div id="myCarousel" class="carousel slide" data-ride="carousel">
                  <!-- Indicators -->
                  <ol class="carousel-indicators">
                    <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
                    <li data-target="#myCarousel" data-slide-to="1"></li>
                    <li data-target="#myCarousel" data-slide-to="2"></li>
                    <li data-target="#myCarousel" data-slide-to="3"></li>
                  </ol>

                  <!-- Wrapper for slides -->
                  <div class="carousel-inner" role="listbox">
                    <div class="item active">
                      <img src="img/slider1.jpg" alt="Dashboard">
                    </div>

                    <div class="item">
                      <img src="img/slider2a.jpg" alt="Reports">
                    </div>

                    <div class="item">
                      <img src="img/slider3.jpg" alt="Flower">
                    </div>

                    <div class="item">
                      <img src="img/slider4.jpg" alt="Flower">
                    </div>
                  </div>

                  <!-- Left and right controls -->
                  <!--  <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                     <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                     <span class="sr-only">Previous</span>
                   </a> -->
                  <!-- <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                  </a> -->
                </div>

                <!-- Bootstrap Caraosel End -->

              </div>
              <!-- END CONTENT -->
            </div>
            <!-- BEGIN SIDEBAR & CONTENT -->




          </div><!--End Carousel Row-->
        </div>
      </section>

      <section id="capture-improve">
        <div class="container">
          <div class="row padding-bottom">
            <div class="col-md-6 col-xm-12">
              <div class="capture-leads">
                <img src="img/capture-leads1.png">
                <h3>Capture leads from anywhere</h3>
                <p>Build your very own landing pages, surveys or embeddable contact forms to capture leads and seamlessly place them in to your data-sets.</div>
            </div><!--End col-md-6-->

            <div class="col-md-6 col-xm-12">
              <div class="improve-workflow">
                <img src="img/workflow.png">
                <h3>Improve your Workflow</h3>
                <p>Whether you're a small, growing business or even an enterprise
                  telemarketing company SanityOS is guaranteed to increase the
                  throughput of your team's success ratio and call volume.
                  <a href="http://www.sanityos.com/infographic.pdf" class="read-more">Learn More</a></p>
              </div>
            </div><!--End col-md-6-->
          </div><!--End Row-->
        </div><!--End .container-->
      </section>


      <section id="mass-email" class="section-padding">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <div class="mass-image">
                <img src="img/email_marketing.png">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mass-text">
                <h1>Mass Email Marketing</h1>
                <p>Design, schedule and send mass marketing emails with our integrated email marketing tools that work just like popular services such as <strong>Mailchimp</strong> and <strong>Constant Contact</strong>. SanityOS uses smart delivery and reporting technologies to ensure
                  your emails are successfully delivered to prospects also enabling you to efficiently track them.</p>
                <p>Use our enterprise Email delivery server or easily add your own. Our service includes up to 20,000 Free and fully tracked email credits per month.<br><br>


                  Thanks to SanityOS following up email responses with a call can now be done in a single environment.</p>
              </div>
            </div>
          </div><!--End .row-->

          <div class="row data-padding">
            <div class="col-md-6">
              <div class="data-driven">
                <img src="img/builtin.png">
                <h3>Integrated Email Marketing Server</h3>
                <p>Every license comes pre-configured with optional use of our
                  marketing mail server including up to 20,000 FREE emails credits per month.</div>
            </div><!--End col-md-6-->

            <div class="col-md-6">
              <div class="visitor-tracking">
                <img src="img/targetdata.png">
                <h3>Advanced Email Targeting</h3>
                <p>SanityOS allows you to target leads in a particular segment (such as age or other qualifying criteria) to maximize the relevance of your message to the recipient.</p>
              </div>
            </div><!--End col-md-6-->
          </div><!--End Row-->

        </div><!--End .container-->
      </section><!--End #mass-email-->

      <section id="fully-customizable" class="section-padding">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <h1 class="text-center">Fully Customizable</h1>
              <p class="text-center">Instead of working to a fixed set of rules and a fixed set of data import fields to manage leads and their contact information Sanity OS allows you to
                build your own Lead management Forms. Add the criteria that you want to see on your lead information page.
                Need drop down menus with multiple options to help identify lead criteria when you or your team are talking to prospects? No problem, want to make fields required input? Simple!<br><br>
                The form Builder function on Sanity OS allows you to build multiple forms for use with any data-set you like.</p>
            </div>
          </div><!--End .row-->
          <div class="row">

            <div class="row">
              <div class="col-md-12">

                <!-- Tab -->
                <div class="tab-content">
                  <div id="step-1" class="tab-pane row fade active in">
                    <div class="col-md-12 text-center">
                      <div class="form-builder-image">
                        <img src="img/step1.jpg">
                      </div>
                      <h1 class="step-heading text-center">Step 1: Create Lead Management Form</h1>
                      <p class="text-center">Design your own lead management fields so you can record any criteria you need to qualify your leads. The fields also become filter options to assist with ease of search when you are carrying out tasks such as exporting or lead look ups.</p>
                    </div>
                  </div>

                  <div id="step-2" class="tab-pane row fade">
                    <div class="col-md-12 text-center">
                      <div class="form-builder-image">
                        <img src="img/sortimport.jpg">
                      </div>
                      <h1 class="text-center">Step 2: Create Campaigns & import Data</h1>
                      <p class="text-center">SanityOS allows you to associate a campaign with a form and import data in to it accordingly. </p>
                    </div>
                  </div>

                  <div id="step-3" class="tab-pane row fade">
                    <div class="col-md-12 text-center">
                      <div class="form-builder-image">
                        <img src="img/leadinfo.jpg">
                      </div>
                      <h1 class="text-center">Step 3: Get to work!</h1>
                      <p class="text-center">Use SanityOS to power through the leads in your database working simultaneously with other co-workers, booking follow up calls and accessing a smart array of other lead management tools.</p>
                    </div>
                  </div>

                  <div id="tab-4" class="tab-pane row fade">
                    <div class="col-md-7">
                      <h2>Analytics... That make sense!</h2>
                      <p>The whole point of anaylytics is so you can digest key information about your business when you need it, at a glance. We've made understanding sales and team performance a piece of cake!</p>
                      <ul>
                        <li>As an Administrator you can see how your team and sales are performing</li>
                        <li>As a Team user you can see your performance and measure yourself up against your colleagues</li>
                        <li>You can share campaigns statistics with clients</li>
                        <li>Travelling? No problem, SanityOS will email you to let you know your teams performance</li>
                      </ul>
                    </div>
                    <div class="col-md-5">
                      <img alt="" src="assets/frontend/layout/img/analyticsos.png" class="img-responsive">
                    </div>
                  </div>
                  <ul class="pagination">
                    <li class="active"><a data-toggle="tab" href="#step-1" aria-expanded="true">1</a></li>
                    <li class=""><a data-toggle="tab" href="#step-2" aria-expanded="false">2</a></li>
                    <li class=""><a data-toggle="tab" href="#step-3" aria-expanded="false">3</a></li>
                  </ul>
                </div>
                <!-- Tab end -->
              </div>
            </div><!--End .row-->
          </div><!--End .container-->
        </div>
      </section><!--End #Fully Customizable-->


      <section id="follow" class="section-gray section-padding">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <img class="follow-left-image" src="img/calimg.jpg">
            </div>
            <div class="col-md-6">
              <div class="follow-right">
                <h1>Built in calendar...with SYNC!</h1>
                <p>Seamlessly book follow calls and even sales appointments for internal and external sales teams. SanityOS has a built in calander but also syncs to your preferred calendar and can (optionally) send your prospects a calendar request too.
                  <img src="img/emailsync.png"></p>
              </div>
            </div>
          </div><!--End .row-->
        </div><!--End .container-->
      </section><!--End #Follow Customizable-->


      <section id="view-update">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <div class="view-update-left">
                <h1>View, Update & Print Leads</h1>
                <p>View leads by positive or negative conversions, fileter them using custom drop menu's and glance at
                  timestamped information quickly and efficiently. Pretty neat huh?</p>
                <p><strong>Well thats not all!</strong><br>
                  Filter leads that you are looking for by selecting the criteria in the filters above and complie them into
                  a PDF document capturing all information about the lead and marketing involvements associated with it.<br> <br> You can call on the assistance of some pretty awesome functions here including drilling down qualifying leads, opening them up, looking at timestamped information in a simple glance and lots more.</p>
              </div>
            </div>
            <div class="col-md-6 view-update-right-col">
              <br>
              <img src="img/viewleads.jpg">

            </div>
          </div><!--End .row-->
        </div><!--End .container-->
      </section><!--End #view-update Customizable-->

      <section id="plugin-doc">
        <div class="container">
          <div class="row row-padding-small">
            <div class="col-md-4 col-xs-12">
              <div class="plug">
                <img src="img/searchleads.png">
                <h4>Search, Explore & Edit Leads</h4>
                <p>Search, explore & edit leads faster and easier than ever.</p>
              </div>
            </div>
            <div class="col-md-4 col-xs-12">
              <div class="mail-merge">
                <img src="img/print.png">
                <h4>Print Leads</h4>
                <p>Print single or entire data sets in a presentable format.</p>
              </div>
            </div>
            <div class="col-md-4 col-xs-12">
              <div class="doc">
                <img src="img/export.png">
                <h4>Advanced Exporting</h4>
                <p>Instantly filter & export data in CSV and PDF format. </p>
              </div>

            </div>
          </div>
        </div>
      </section>


      <section id="nutshell" class="section-gray section-padding">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <div class="sos-video">
                <iframe width="100%" height="430" src="https://www.youtube.com/embed/BBtlbKyPRkY" frameborder="0" allowfullscreen></iframe>
              </div>
            </div>
            <div class="col-md-6">
              <div class="nutshell-right">
                <h1>In a Nutshell....</h1>
                <p>Some may say conventional Sales and Lead management solutions work in a rather unconventional way. They often require a level of training and introduction before they can be adopted by a new team or member of staff. Additionally many of the popular CRM's produce inaccurate data and forecasts often confusing managers or team members and creating conflict.<br><br>
                  SanityOS provides an easy to adopt interface that requires little or no induction, it seamlessly supports <strong>Follow up calls</strong>, <strong>Reminders</strong>,<strong> Mass Mailing</strong> and<strong> Sales appointments</strong> amongst many other features.</p>
                <p> SanityOS is informative, it lets your team know who has made the most calls, how many prospects are pending in your campaign and includes tools such as creating custom forms or email template designs. <a href="features" class="read-more">View our Feature List</a></p><br><br>
              </div>

            </div>
          </div><!--End .row-->
        </div><!--End .container-->
      </section><!--End #nutshell -->
    </div>
 @endsection