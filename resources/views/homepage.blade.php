@extends("base")

@section("title")
  Lead Management Software
@endsection

@section("content")
    <!-- BEGIN SLIDER -->
    <div class="page-slider margin-bottom-40">
      <div class="fullwidthbanner-container revolution-slider">
        <div class="fullwidthabnner">
          <ul id="revolutionul">
            <!-- THE NEW SLIDE -->
           
            <!-- THE SECOND SLIDE -->
            <li data-transition="fade" data-slotamount="7" data-masterspeed="300" data-delay="9400" data-thumb="assets/frontend/pages/img/revolutionslider/thumbs/thumb2.jpg"> <img src="assets/frontend/pages/img/revolutionslider/bg2.jpg" alt="">
              <div class="caption lfl slide_title slide_item_left"
                data-x="30"
                data-y="125"
                data-speed="400"
                data-start="3500"
                data-easing="easeOutExpo"> #1 for Sales Results</div>
              <div class="caption lfl slide_subtitle slide_item_left"
                data-x="30"
                data-y="200"
                data-speed="400"
                data-start="4000"
                data-easing="easeOutExpo"> start increasing productivity now! </div>
              <div class="caption lfl slide_desc slide_item_left"
                data-x="30"
                data-y="245"
                data-speed="400"
                data-start="4500"
                data-easing="easeOutExpo"> For most users SanityOS requires no training,<br>it is feature-rich and has been designed to increase sales-related<br>productivity for businesses and organisations of all sizes
                </div>
              <div class="caption lfr slide_item_right" 
                data-x="635" 
                data-y="105" 
                data-speed="1200" 
                data-start="1500" 
                data-easing="easeOutBack"> <img src="assets/frontend/pages/img/revolutionslider/mac.png" alt="Image 1"> </div>
              <div class="caption lfr slide_item_right" 
                data-x="580" 
                data-y="245" 
                data-speed="1200" 
                data-start="2000" 
                data-easing="easeOutBack"> <img src="assets/frontend/pages/img/revolutionslider/ipad.png" alt="Image 1"> </div>
              <div class="caption lfr slide_item_right" 
                data-x="735" 
                data-y="290" 
                data-speed="1200" 
                data-start="2500" 
                data-easing="easeOutBack"> <img src="assets/frontend/pages/img/revolutionslider/iphone.png" alt="Image 1"> </div>
              <div class="caption lfr slide_item_right" 
                data-x="835" 
                data-y="230" 
                data-speed="1200" 
                data-start="3000" 
                data-easing="easeOutBack"> <img src="assets/frontend/pages/img/revolutionslider/macbook.png" alt="Image 1"> </div>
              <div class="caption lft slide_item_right" 
                data-x="865" 
                data-y="45" 
                data-speed="500" 
                data-start="5000" 
                data-easing="easeOutBack"> <img src="assets/frontend/pages/img/revolutionslider/hint1-red.png" id="rev-hint1" alt="Image 1"> </div>
              <div class="caption lfb slide_item_right" 
                data-x="355" 
                data-y="355" 
                data-speed="500" 
                data-start="5500" 
                data-easing="easeOutBack"> <img src="assets/frontend/pages/img/revolutionslider/hint2-red.png" id="rev-hint2" alt="Image 1"> </div>
            </li>
            <!-- THE THIRD SLIDE -->
            <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400" data-thumb="assets/frontend/pages/img/revolutionslider/thumbs/thumb2.jpg"> <img src="assets/frontend/pages/img/revolutionslider/bg3.jpg" alt="">
              <div class="caption lfl slide_item_left" 
                data-x="30" 
                data-y="95" 
                data-speed="400" 
                data-start="1500" 
                data-easing="easeOutBack">
                <iframe width="420" height="240" src="https://www.youtube.com/embed/gmF20QqqxB8" frameborder="0" allowfullscreen></iframe>
              </div>
              <div class="caption lfr slide_title"
                data-x="470"
                data-y="100"
                data-speed="100"
                data-start="2000"
                data-easing="easeOutExpo"><h3> "See what others are saying"</h3> 
             <img src="assets/frontend/pages/img/works/reviews.png" ></div>
              <div class="caption lfr slide_desc"
                data-x="470"
                data-y="220"
                data-speed="400"
                data-start="3000"
                data-easing="easeOutExpo">5 STAR RATINGS<br>
                From leading technology reviewers</div>
              <a class="caption lfr btn yellow slide_btn" href="/lead-management"
                data-x="470"
                data-y="280"
                                 data-speed="400"
                                 data-start="3500"
                                 data-easing="easeOutExpo"> Learn More </a> </li>
            <!-- THE FORTH SLIDE -->
            <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400" data-thumb="assets/frontend/pages/img/revolutionslider/thumbs/thumb2.jpg">
              <!-- THE MAIN IMAGE IN THE FIRST SLIDE -->
              <img src="assets/frontend/pages/img/revolutionslider/bg4.jpg" alt="">
              <div class="caption lft slide_title"
                                 data-x="30"
                                 data-y="105"
                                 data-speed="400"
                                 data-start="1500"
                                 data-easing="easeOutExpo"> <h2>What type of user are you? </h2></div>
              <div class="caption lft slide_subtitle"
                                 data-x="30"
                                 data-y="180"
                                 data-speed="400"
                                 data-start="2000"
                                 data-easing="easeOutExpo"> Designed to serve everyone </div>
              <div class="caption lft slide_desc"
                                 data-x="30"
                                 data-y="225"
                                 data-speed="400"
                                 data-start="2500"
                                 data-easing="easeOutExpo"> We've built account packages to suit everyone<br>
                from single users, SME's to large corporations</div>
              <a class="caption lft slide_btn btn red slide_item_left" href="pricing" 
                                 data-x="30"
                                 data-y="300"
                                 data-speed="400"
                                 data-start="3000"
                                 data-easing="easeOutExpo"> Learn More! </a>
              <div class="caption lft start"  
                                 data-x="670" 
                                 data-y="55" 
                                 data-speed="400" 
                                 data-start="2000" 
                                 data-easing="easeOutBack"  > <img src="assets/frontend/pages/img/revolutionslider/iphone_left.png" alt="Image 2"> </div>
              <div class="caption lft start"  
                                 data-x="850" 
                                 data-y="55" 
                                 data-speed="400" 
                                 data-start="2400" 
                                 data-easing="easeOutBack"  > <img src="assets/frontend/pages/img/revolutionslider/iphone_right.png" alt="Image 3"> </div>
            </li>
          </ul>
          <div class="tp-bannertimer tp-bottom"></div>
        </div>
      </div>
    </div>
    <!-- END SLIDER -->

    <div class="main">
      <div class="container">
        <!-- BEGIN SERVICE BOX -->   
        <div class="row service-box margin-bottom-40">
          <div class="col-md-4 col-sm-4">
            <div class="service-box-heading">
              <em><i class="fa fa-check red"></i></em>
              <span>Practical</span>
            </div>
            <p>Record data from in/outbound calls, email customers, book appointments or follow up calls all in the most efficient sales & marketing application you will ever use.</p>
          </div>
          <div class="col-md-4 col-sm-4">
            <div class="service-box-heading">
              <em><i class="fa fa-check red"></i></em>
              <span>Scalable</span>
            </div>
            <p>Add & remove users as you need them with flexible pay as you go pricing. Compare your teams sales performance & let SanityOS send daily statistics via Email.</p>
          </div>
          <div class="col-md-4 col-sm-4">
            <div class="service-box-heading">
              <em><i class="fa fa-check red"></i></em>
              <span>User Friendly</span>
            </div>
            <p>SanityOS is easy for any organisation to adopt & customize. Benefits from logging calls to making sales appointments have endless advantages over conventional CRM's.</p>
          </div>
        </div>
        <!-- END SERVICE BOX -->

        <!-- BEGIN BLOCKQUOTE BLOCK -->   
        <div class="row quote-v1 margin-bottom-30">
          <div class="col-md-9">
            <span>Possibly the most productive Lead management software you'll ever use!</span>
          </div>
          <div class="col-md-3 text-right">
            <a class="btn-transparent" href="http://www.sanityos.com/pricing"><i class="fa fa-rocket margin-right-10"></i>Click Here to Get Started</a>
          </div>
        </div>
        <!-- END BLOCKQUOTE BLOCK -->

        <!-- BEGIN RECENT WORKS -->
        <div class="row recent-work margin-bottom-40">
          <div class="col-md-3">
            <h2>Cross Platform</h2>
            <p>Stay in constant contact with your customers and team. Whether you're on the Go or in the office SanityOS will work across all of your devices. No app downloads required! <br><br> <strong>Do you Hire Work from home staff?</strong><br>SanityOS the best telesales software for measuring staff performance whilst they work from home.</p>
          </div>
          <div class="col-md-9">
            <div class="owl-carousel owl-carousel3">
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img1.jpg" alt="Cross Platform 1" class="img-responsive">
                 
                  <a href="assets/frontend/pages/img/works/img1.jpg" class="fancybox-button" title="Project Name #1" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>
                
              </div>
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img2.jpg" alt="Cross Platform 2" class="img-responsive">
               
                  <a href="assets/frontend/pages/img/works/img2.jpg" class="fancybox-button" title="Project Name #2" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>
               
              </div>
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img3.jpg" alt="Cross Platform 3" class="img-responsive">
 
                  <a href="assets/frontend/pages/img/works/img3.jpg" class="fancybox-button" title="Project Name #3" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>

              </div>
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img4.jpg" alt="Cross Platform 4" class="img-responsive">
                 
                  <a href="assets/frontend/pages/img/works/img4.jpg" class="fancybox-button" title="Project Name #4" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>
               
              </div>
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img5.jpg" alt="Cross Platform 5" class="img-responsive">
                
                  <a href="assets/frontend/pages/img/works/img5.jpg" class="fancybox-button" title="Project Name #5" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>
                
               
               
              </div>
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img3.jpg" alt="Cross platform 3" class="img-responsive">
                  
                  <a href="assets/frontend/pages/img/works/img3.jpg" class="fancybox-button" title="Project Name #3" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>
                
              </div>
              <div class="recent-work-item">
                <em>
                  <img src="assets/frontend/pages/img/works/img4.jpg" alt="Cross Platform 4" class="img-responsive">
                 
                  <a href="assets/frontend/pages/img/works/img4.jpg" class="fancybox-button" title="Project Name #4" data-rel="fancybox-button"><i class="fa fa-search"></i></a>
                </em>
             
              </div>
            </div>       
          </div>
        </div>   
        <!-- END RECENT WORKS -->

        <!-- BEGIN TABS AND TESTIMONIALS -->
        <div class="row mix-block margin-bottom-40">
          <!-- TABS -->
          <div class="col-md-7 tab-style-1">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab-1" data-toggle="tab">High Performance</a></li>
              <li><a href="#tab-2" data-toggle="tab">Easy to Use</a></li>
              <li><a href="#tab-3" data-toggle="tab">Business Smart Features</a></li>
              <li><a href="#tab-4" data-toggle="tab">Why choose SanityOS</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane row fade in active" id="tab-1">
                <div class="col-md-3 col-sm-3">
                  <a href="assets/temp/photos/img7.png" class="fancybox-button" title="AWS" data-rel="fancybox-button">
                    <img class="img-responsive" src="assets/frontend/pages/img/photos/img7.png" alt="">
                  </a>
                </div>
                <div class="col-md-9 col-sm-9">
                  <p class="margin-bottom-10">SanityOS is optimised to provide a high speed and reactive service on demand. Developed by intelligent programmers and hosted using cutting edge cloud technology provided by Amazon web services. You’ll be able to count on us for a fast and reliable service at all times.</p>
                  <p><a class="more" href="{{ route("leadmanagement") }}">Read more <i class="icon-angle-right"></i></a></p>
                </div>
              </div>
              <div class="tab-pane row fade" id="tab-2">
                <div class="col-md-9 col-sm-9">
                  <p>Whether you or your team are computer-savvy or more used to using conventional methods of sales and communications SanityOS will offer an easy to use environment to work from whilst completing even the most complicated tasks. This is more than just a claim, we are confident that within 1 week of using our enterprise software you’ll no longer rely on help topics or support.</p>
                </div>
                <div class="col-md-3 col-sm-3">
                  <a href="assets/temp/photos/img10.png" class="fancybox-button" title="user friendly CRM" data-rel="fancybox-button">
                    <img class="img-responsive" src="assets/frontend/pages/img/photos/img10.png" alt="">
                  </a>
                </div>
              </div>
              <div class="tab-pane fade" id="tab-3">
                <p>SanityOS includes business smart features such as GEO planning which allows you to search and export prospects that are nearby one another enabling smarter journey planning. It also lets you use your own SMTP server for sending outgoing emails from your own domain. These are just a few advantages of choosing SanityOS.com for your Sales and business needs.</p>
              </div>
              <div class="tab-pane fade" id="tab-4">
                <p>SanityOS is an all purpose lead management software and appointment booking solution, it also lets you handle inbound calls and view statistical information about strike rates and performance all within an easy to use interface locked together with many other features.</p>
              </div>
            </div>
          </div>
          <!-- END TABS -->
        
          <!-- TESTIMONIALS -->
          <div class="col-md-5 testimonials-v1">
            <div id="myCarousel" class="carousel slide">
              <!-- Carousel items -->
              <div class="carousel-inner">
                <div class="active item">
                  <blockquote><p>"Fantastic piece of software! We've gained a noticeable increase in team productivity, our sales are up and our IT Department are finally happy."</p></blockquote>
                  <div class="carousel-info">
                    <img class="pull-left" src="assets/frontend/pages/img/people/img1-small.jpg" alt="">
                    <div class="pull-left">
                      <span class="testimonials-name">krystien Engel</span>
                      <span class="testimonials-post">Operations Director <br>Size of team: 80+</span>
                    </div>
                  </div>
                </div>
                <div class="item">
                  <blockquote><p>"As a leading supplier of fashion and accessories switching to SanityOS helped get the most from my staff. Its impacted our appointment making and sales process very positively."</p></blockquote>
                  <div class="carousel-info">
                    <img class="pull-left" src="assets/frontend/pages/img/people/img5-small.jpg" alt="">
                    <div class="pull-left">
                      <span class="testimonials-name">jennifer Llewellyn</span>
                      <span class="testimonials-post">Managing Director <br>Size of team: 200+</span>
                    </div>
                  </div>
                </div>
                <div class="item">
                  <blockquote><p>"Before discovering SanityOS my non-savvy agents were calling leads and making notes in excel. The benefits of using SanityOS have been embraced instantly, I highly recommend it."</p></blockquote>
                  <div class="carousel-info">
                    <img class="pull-left" src="assets/frontend/pages/img/people/img2-small.jpg" alt="">
                    <div class="pull-left">
                      <span class="testimonials-name">Jake Simms</span>
                      <span class="testimonials-post">Sales Manager <br>Size of team: 35+</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Carousel nav -->
              <a class="left-btn" href="#myCarousel" data-slide="prev"></a>
              <a class="right-btn" href="#myCarousel" data-slide="next"></a>
            </div>
          </div>
          <!-- END TESTIMONIALS -->
        </div>                
        <!-- END TABS AND TESTIMONIALS -->

        <!-- BEGIN STEPS -->
        <div class="row margin-bottom-40 front-steps-wrapper front-steps-count-3">
          <div class="col-md-4 col-sm-4 front-step-col">
            <div class="front-step front-step1">
              <h2>Import DATA</h2>
              <p>Import prospect DATA in to the world's most efficient SALES interface in a few simple steps, You can also create custom forms to match the qualifying criteria of your calls.</p>
            </div>
          </div>
          <div class="col-md-4 col-sm-4 front-step-col">
            <div class="front-step front-step2">
              <h2>Start Calling</h2>
              <p>SanityOS lets your team work from the same list booking call backs or sales appointments at the touch of a button. It also measures call and team performance for each campaign.</p>
            </div>
          </div>
          <div class="col-md-4 col-sm-4 front-step-col">
            <div class="front-step front-step3">
              <h2>Analyze</h2>
              <p>Analyze the performance of you or your team's campaigns. Get updated via email or export positive, negative or unreachable leads.</p>
            </div>
          </div>
        </div>
        <!-- END STEPS -->

        <!-- BEGIN CLIENTS -->
        
              </div>                  
            </div>

@endsection