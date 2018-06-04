<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" href="images/favicon.png" type="image/png">

  <title>Form Demo</title>

  {!! Html::style('assets/css/style.default.css') !!}
  @yield('css')

  <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="js/html5shiv.js"></script>
  <script src="js/respond.min.js"></script>
  <![endif]-->
</head>

<body>
<!-- Preloader -->
<div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>

<section>
    <div class="leftpanel">
        <div class="logopanel">
        	<img src="{{ URL::asset("assets/images/logo.png") }}" style="height: 30px;"/>
    	</div>
        <div class="leftpanelinner"></div>
    </div>
    <div class="mainpanel">
        <div class="headerbar">
            <a class="menutoggle"><i class="fa fa-bars"></i></a>
            <div class="header-right">
                <ul class="headermenu">
                    <li>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                {{ Auth::admin()->get()->firstName }} {{ Auth::admin()->get()->lastName }}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                                <li><a href="{{ URL::route("user.profile") }}"><i class="glyphicon glyphicon-user"></i> My Profile</a></li>
                                <li><a href="{{ URL::route("user.logout") }}"><i class="glyphicon glyphicon-log-out"></i> Log Out</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="pageheader">
            <h2><i class="glyphicon glyphicon-edit"></i> Lead Information</h2>
        </div>

        <div class="contentpanel">
            <div class="panel">
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-5">
                            <select class="form-control mb15" id="campaignName" name="campaignName">
                          <option>Select a Campaign</option>
                        </select>
                      </div>
                      <div class="col-sm-5">
                        <label class="col-sm-12 control-label">Lead Number: 5/100</label>
                      </div>
                    </div>
                    <hr>
                

                    @for($i=0; $i < count($leadFormDatas1); $i=$i+2)
                      <div class="form-group">
                        <div class="col-sm-6">
                          <label class="col-sm-3 control-label">{{ $leadFormDatas1[$i]['fieldName'] }}</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control preDefineformfields">
                        </div>
                        </div>
                        @if($i+1 < count($leadFormDatas1))
                        <div class="col-sm-6">
                          <label class="col-sm-3 control-label">{{ $leadFormDatas1[$i+1]['fieldName'] }}</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control preDefineformfields">
                        </div>
                        </div>
                        @endif
                      </div>
                      <hr>
                    @endfor
                    <div class="form-group">
                      <div class="col-sm-6">
                        <label class="col-sm-3 control-label">Interested</label>
                          <div class="col-sm-9">
                            <select class="form-control mb15" id="leadInterest">
                              <option value="NotSet">Select a Value</option>
                              <option value="Interested">Positive</option>
                              <option value="NotInterested">Negative</option>
                            </select>
                         </div>
                      </div>
                      @if($emailFieldExists)
                          <div class="col-sm-6">
                            <label class="col-sm-3 control-label">Send Email</label>
                              <div class="col-sm-9">
                                <select class="form-control mb15" id="emailTemplateID">
                                <option value="">Select a Template</option>
                                </select>
                              </div>
                          </div>
                      @endif
                    </div>
                    <hr>
                    <div class="form-group">
                    <label class="col-sm-3 control-label">Book Appointment</label>
                    <div class="col-sm-7">
                      <select class="form-control mb15" id="bookAppointment">
                              <option value="No">No</option>
                              <option value="Yes">Yes</option>
                            </select>
                    </div>
                </div>
                <hr>
                    @foreach($leadFormDatas2 as $leadFormData2)
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{ $leadFormData2['fieldName'] }}</label>
                    <div class="col-sm-7">
                    @if(strtolower($leadFormData2['fieldName']) == 'notes')
                      <textarea  id="importLeadTXT" name="importLeadTXT" class="form-control preDefineformfields" rows="5"></textarea>
                    @else
                      <input type="text" class="form-control preDefineformfields">
                    @endif 
                    </div>
                </div>
                <hr>
                    @endforeach

                    <div class="form-group">
                      <div class="col-sm-6">
                        <label class="col-sm-3 control-label"></label>
                          <div class="col-sm-9">
                            <input type="checkbox" id="followUpCall"> Follow up Call
                         </div>
                      </div>
                      <div class="col-sm-6">
                        <label class="col-sm-3 control-label">Reference</label>
                          <div class="col-sm-9">
                            <input type="text" id="leadReferenceNumber" class="form-control">
                          </div>
                      </div>
                    </div>
                <hr>
                
                <div class="form-group">
                    <label class="col-sm-3 control-label"></label>
                    <div class="col-sm-7">
                      <p>
                        <button onclick="goToAction('back')" class="btn btn-warning btn-sm">Back</button>&nbsp;
                        <button onclick="goToAction('saveandexit')" class="btn btn-primary btn-sm">Save and Exit</button>&nbsp;
                        <button onclick="goToAction('next')" class="btn btn-success btn-sm">Next</button>&nbsp;
                        <button onclick="skipLead()" class="btn btn-danger btn-sm">Skip</button>
                        
                      </p>
                    </div>
                </div>
                  

            </div><!-- panel-body -->
        </div><!-- panel -->

    </div><!-- contentpanel -->
   
    
  </div><!-- mainpanel -->
  
</section>
{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-ui-1.10.3.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/custom_date_field.js') !!}
</body>
</html>
