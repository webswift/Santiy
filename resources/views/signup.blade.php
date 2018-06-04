@extends('layouts.homepage')

@section('title')
    Register
@stop

@section('css')
{!! Html::style('assets/js/card-master/lib/css/card.css') !!}
<style type="text/css">
.signup-div
{
    background: rgba(255,255,255,0.2);
    border: 1px solid #ccc;
    -moz-box-shadow: 0 3px 0 rgba(12,12,12,0.03);
    -webkit-box-shadow: 0 3px 0 rgba(12,12,12,0.03);
    box-shadow: 0 3px 0 rgba(12,12,12,0.03);
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    padding: 30px;
}
</style>
{!! HTML::style('assets/css/signup.css') !!}
@stop

@section('content')

<div class="signuppanel">
    <div class="row">
        <div class="col-md-6"> {!! \App\Models\Setting::get('frontHtml') !!}</div>

        <div class="col-md-6">
            @if($successMessage != '')
                <div class="alert alert-success">
                    <a class="close" data-dismiss="alert" href="#" aria-hidden="true">x</a>
                    {!! $successMessage !!}
                </div>
            @endif

            @if($errorMessage != '')
                <div class="alert alert-danger">
                    <a class="close" data-dismiss="alert" href="#" aria-hidden="true">x</a>
                    {!! $errorMessage !!}
                </div>
            @endif

            <div id="error"></div>

            <div class="signup-div creditCartStep_1" id="signUpInfoDiv">
                <h3 class="nomargin">Sign Up</h3>
                <p class="mt5 mb20">Already a member? <a href="{{ URL::route("user.login") }}"><strong>Sign In</strong></a></p>

                <label class="control-label">Name</label>
                <div class="row mb10">
                    <div class="col-sm-6">
                        <input type="text" id="firstName" class="form-control" placeholder="First Name" />
                    </div>
                    <div class="col-sm-6">
                        <input type="text" id="lastName" class="form-control" placeholder="Last Name" />
                    </div>
                </div>

                <div class="row mb10">
                    <div class="col-sm-6">
                        <label class="control-label">Company Name</label>
                        <input type="text" id="companyName" class="form-control" placeholder="Company Name" />
                    </div>
                    <div class="col-sm-6">
                        <label class="control-label">Contact number</label>
                        <input type="text" id="contactNumber" class="form-control" placeholder="Contact Number" />
                    </div>
                </div>

                <div class="mb10">
                    <label class="control-label">Email Address</label>
                    <div class="input-group mb15">
                        <input type="text" id="email" class="form-control" placeholder="Email"  />
                        <span class="input-group-addon" id="emailCheck"><span class="fa fa-times"></span></span>
                    </div>
                    <span class="help-block"><small id="emailCheckText"></small></span>
                </div>

                <div class="mb10">
                    <label class="control-label">Password</label>
                    <div class="input-group mb15">
                        <input id="password" type="password" class="form-control" />
                        <span class="input-group-addon" id="passwordCheck"><span class="fa fa-times"></span></span>
                    </div>
                </div>

                <div class="mb10">
                    <label class="control-label">Retype Password</label>
                    <div class="input-group mb15">
                        <input id="retypePassword" type="password" class="form-control" />
                        <span class="input-group-addon" id="retypePasswordCheck"><span class="fa fa-times"></span></span>
                    </div>
                </div>

                <div class="mb10">
                    <label class="control-label">Country</label>
                    <select id="country" class="select2-2" data-placeholder="Choose a Country...">
                        <option value="">Select a Country</option>
                        <option value="AF">Afghanistan</option>
                        <option value="AX">Aland Islands</option>
                        <option value="AL">Albania</option>
                        <option value="DZ">Algeria</option>
                        <option value="AS">American Samoa</option>
                        <option value="AD">Andorra</option>
                        <option value="AO">Angola</option>
                        <option value="AI">Anguilla</option>
                        <option value="AQ">Antarctica</option>
                        <option value="AG">Antigua and Barbuda</option>
                        <option value="AR">Argentina</option>
                        <option value="AM">Armenia</option>
                        <option value="AW">Aruba</option>
                        <option value="AU">Australia</option>
                        <option value="AT">Austria</option>
                        <option value="AZ">Azerbaijan</option>
                        <option value="BS">Bahamas</option>
                        <option value="BH">Bahrain</option>
                        <option value="BD">Bangladesh</option>
                        <option value="BB">Barbados</option>
                        <option value="BY">Belarus</option>
                        <option value="BE">Belgium</option>
                        <option value="BZ">Belize</option>
                        <option value="BJ">Benin</option>
                        <option value="BM">Bermuda</option>
                        <option value="BT">Bhutan</option>
                        <option value="BO">Bolivia, Plurinational  State of</option>
                        <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                        <option value="BA">Bosnia and Herzegovina</option>
                        <option value="BW">Botswana</option>
                        <option value="BV">Bouvet Island</option>
                        <option value="BR">Brazil</option>
                        <option value="IO">British Indian Ocean Territory</option>
                        <option value="BN">Brunei Darussalam</option>
                        <option value="BG">Bulgaria</option>
                        <option value="BF">Burkina Faso</option>
                        <option value="BI">Burundi</option>
                        <option value="KH">Cambodia</option>
                        <option value="CM">Cameroon</option>
                        <option value="CA">Canada</option>
                        <option value="CV">Cape Verde</option>
                        <option value="KY">Cayman Islands</option>
                        <option value="CF">Central African Republic</option>
                        <option value="TD">Chad</option>
                        <option value="CL">Chile</option>
                        <option value="CN">China</option>
                        <option value="CX">Christmas Island</option>
                        <option value="CC">Cocos (Keeling) Islands</option>
                        <option value="CO">Colombia</option>
                        <option value="KM">Comoros</option>
                        <option value="CG">Congo</option>
                        <option value="CD">Congo, the Democratic Republic of the</option>
                        <option value="CK">Cook Islands</option>
                        <option value="CR">Costa Rica</option>
                        <option value="CI">Cote d\'Ivoire</option>
                        <option value="HR">Croatia</option>
                        <option value="CU">Cuba</option>
                        <option value="CW">Cura&ccedil;ao</option>
                        <option value="CY">Cyprus</option>
                        <option value="CZ">Czech Republic</option>
                        <option value="DK">Denmark</option>
                        <option value="DJ">Djibouti</option>
                        <option value="DM">Dominica</option>
                        <option value="DO">Dominican Republic</option>
                        <option value="EC">Ecuador</option>
                        <option value="EG">Egypt</option>
                        <option value="SV">El Salvador</option>
                        <option value="GQ">Equatorial Guinea</option>
                        <option value="ER">Eritrea</option>
                        <option value="EE">Estonia</option>
                        <option value="ET">Ethiopia</option>
                        <option value="FK">Falkland Islands (Malvinas)</option>
                        <option value="FO">Faroe Islands</option>
                        <option value="FJ">Fiji</option>
                        <option value="FI">Finland</option>
                        <option value="FR">France</option>
                        <option value="GF">French Guiana</option>
                        <option value="PF">French Polynesia</option>
                        <option value="TF">French Southern Territories</option>
                        <option value="GA">Gabon</option>
                        <option value="GM">Gambia</option>
                        <option value="GE">Georgia</option>
                        <option value="DE">Germany</option>
                        <option value="GH">Ghana</option>
                        <option value="GI">Gibraltar</option>
                        <option value="GR">Greece</option>
                        <option value="GL">Greenland</option>
                        <option value="GD">Grenada</option>
                        <option value="GP">Guadeloupe</option>
                        <option value="GU">Guam</option>
                        <option value="GT">Guatemala</option>
                        <option value="GG">Guernsey</option>
                        <option value="GN">Guinea</option>
                        <option value="GW">Guinea-Bissau</option>
                        <option value="GY">Guyana</option>
                        <option value="HT">Haiti</option>
                        <option value="HM">Heard Island and McDonald Islands</option>
                        <option value="VA">Holy See (Vatican City State)</option>
                        <option value="HN">Honduras</option>
                        <option value="HK">Hong Kong</option>
                        <option value="HU">Hungary</option>
                        <option value="IS">Iceland</option>
                        <option value="IN">India</option>
                        <option value="ID">Indonesia</option>
                        <option value="IR">Iran, Islamic Republic of</option>
                        <option value="IQ">Iraq</option>
                        <option value="IE">Ireland</option>
                        <option value="IM">Isle of Man</option>
                        <option value="IL">Israel</option>
                        <option value="IT">Italy</option>
                        <option value="JM">Jamaica</option>
                        <option value="JP">Japan</option>
                        <option value="JE">Jersey</option>
                        <option value="JO">Jordan</option>
                        <option value="KZ">Kazakhstan</option>
                        <option value="KE">Kenya</option>
                        <option value="KI">Kiribati</option>
                        <option value="KP">Korea, Democratic People's Republic of</option>
                        <option value="KR">Korea, Republic of</option>
                        <option value="KW">Kuwait</option>
                        <option value="KG">Kyrgyzstan</option>
                        <option value="LA">Lao People's Democratic Republic</option>
                        <option value="LV">Latvia</option>
                        <option value="LB">Lebanon</option>
                        <option value="LS">Lesotho</option>
                        <option value="LR">Liberia</option>
                        <option value="LY">Libya</option>
                        <option value="LI">Liechtenstein</option>
                        <option value="LT">Lithuania</option>
                        <option value="LU">Luxembourg</option>
                        <option value="MO">Macao</option>
                        <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                        <option value="MG">Madagascar</option>
                        <option value="MW">Malawi</option>
                        <option value="MY">Malaysia</option>
                        <option value="MV">Maldives</option>
                        <option value="ML">Mali</option>
                        <option value="MT">Malta</option>
                        <option value="MH">Marshall Islands</option>
                        <option value="MQ">Martinique</option>
                        <option value="MR">Mauritania</option>
                        <option value="MU">Mauritius</option>
                        <option value="YT">Mayotte</option>
                        <option value="MX">Mexico</option>
                        <option value="FM">Micronesia, Federated States of</option>
                        <option value="MD">Moldova, Republic of</option>
                        <option value="MC">Monaco</option>
                        <option value="MN">Mongolia</option>
                        <option value="ME">Montenegro</option>
                        <option value="MS">Montserrat</option>
                        <option value="MA">Morocco</option>
                        <option value="MZ">Mozambique</option>
                        <option value="MM">Myanmar</option>
                        <option value="NA">Namibia</option>
                        <option value="NR">Nauru</option>
                        <option value="NP">Nepal</option>
                        <option value="NL">Netherlands</option>
                        <option value="NC">New Caledonia</option>
                        <option value="NZ">New Zealand</option>
                        <option value="NI">Nicaragua</option>
                        <option value="NE">Niger</option>
                        <option value="NG">Nigeria</option>
                        <option value="NU">Niue</option>
                        <option value="NF">Norfolk Island</option>
                        <option value="MP">Northern Mariana Islands</option>
                        <option value="NO">Norway</option>
                        <option value="OM">Oman</option>
                        <option value="PK">Pakistan</option>
                        <option value="PW">Palau</option>
                        <option value="PS">Palestinian Territory, Occupied</option>
                        <option value="PA">Panama</option>
                        <option value="PG">Papua New Guinea</option>
                        <option value="PY">Paraguay</option>
                        <option value="PE">Peru</option>
                        <option value="PH">Philippines</option>
                        <option value="PN">Pitcairn</option>
                        <option value="PL">Poland</option>
                        <option value="PT">Portugal</option>
                        <option value="PR">Puerto Rico</option>
                        <option value="QA">Qatar</option>
                        <option value="RE">Reunion</option>
                        <option value="RO">Romania</option>
                        <option value="RU">Russian Federation</option>
                        <option value="RW">Rwanda</option>
                        <option value="BL">Saint Barth&eacute;lemy</option>
                        <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                        <option value="KN">Saint Kitts and Nevis</option>
                        <option value="LC">Saint Lucia</option>
                        <option value="MF">Saint Martin (French part)</option>
                        <option value="PM">Saint Pierre and Miquelon</option>
                        <option value="VC">Saint Vincent and the Grenadines</option>
                        <option value="WS">Samoa</option>
                        <option value="SM">San Marino</option>
                        <option value="ST">Sao Tome and Principe</option>
                        <option value="SA">Saudi Arabia</option>
                        <option value="SN">Senegal</option>
                        <option value="RS">Serbia</option>
                        <option value="SC">Seychelles</option>
                        <option value="SL">Sierra Leone</option>
                        <option value="SG">Singapore</option>
                        <option value="SX">Sint Maarten (Dutch part)</option>
                        <option value="SK">Slovakia</option>
                        <option value="SI">Slovenia</option>
                        <option value="SB">Solomon Islands</option>
                        <option value="SO">Somalia</option>
                        <option value="ZA">South Africa</option>
                        <option value="GS">South Georgia and the South Sandwich Islands</option>
                        <option value="SS">South Sudan</option>
                        <option value="ES">Spain</option>
                        <option value="LK">Sri Lanka</option>
                        <option value="SD">Sudan</option>
                        <option value="SR">Suriname</option>
                        <option value="SJ">Svalbard and Jan Mayen</option>
                        <option value="SZ">Swaziland</option>
                        <option value="SE">Sweden</option>
                        <option value="CH">Switzerland</option>
                        <option value="SY">Syrian Arab Republic</option>
                        <option value="TW">Taiwan, Province of China</option>
                        <option value="TJ">Tajikistan</option>
                        <option value="TZ">Tanzania, United Republic of</option>
                        <option value="TH">Thailand</option>
                        <option value="TL">Timor-Leste</option>
                        <option value="TG">Togo</option>
                        <option value="TK">Tokelau</option>
                        <option value="TO">Tonga</option>
                        <option value="TT">Trinidad and Tobago</option>
                        <option value="TN">Tunisia</option>
                        <option value="TR">Turkey</option>
                        <option value="TM">Turkmenistan</option>
                        <option value="TC">Turks and Caicos Islands</option>
                        <option value="TV">Tuvalu</option>
                        <option value="UG">Uganda</option>
                        <option value="UA">Ukraine</option>
                        <option value="AE">United Arab Emirates</option>
                        <option value="GB">United Kingdom</option>
                        <option value="US">United States</option>
                        <option value="UM">United States Minor Outlying Islands</option>
                        <option value="UY">Uruguay</option>
                        <option value="UZ">Uzbekistan</option>
                        <option value="VU">Vanuatu</option>
                        <option value="VE">Venezuela, Bolivarian Republic of</option>
                        <option value="VN">Viet Nam</option>
                        <option value="VG">Virgin Islands, British</option>
                        <option value="VI">Virgin Islands, U.S.</option>
                        <option value="WF">Wallis and Futuna</option>
                        <option value="EH">Western Sahara</option>
                        <option value="YE">Yemen</option>
                        <option value="ZM">Zambia</option>
                        <option value="ZW">Zimbabwe</option>
                    </select>
                </div>
				
				<div class="mb10">
					<label class="control-label">Timezone</label>
						{!! Timezone::selectForm('UTC'
							, 'Select a timezone'
							, ['class' => 'select2-2', 'name' => 'timeZoneName', 'id' => 'timeZoneName', 'title' => 'Offset without DST']); 
						!!}
				</div>

                <div class="mb10 hidden">
                    <label class="control-label">License Type</label>
                    <select class="form-control mb15 currencyCalculator" id="licenseType">
                        @if($trial_cart == 'No')
                        @foreach($licenses as $license)
                            <option value="{{ $license->id }}"> {{ $license->name }} @if($license->licenseClass == 'Multi') Admin + {{ $license->free_users }} team users @endif </option>
                        @endforeach
                        @else
                            @foreach($trialLicences as $license)
                                <option value="{{ $license->id }}"> {{ $license->name }} @if($license->licenseClass == 'Multi') Admin + {{ $paidLicences->free_users }} team users @endif </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @if($trial_cart == 'No')
                    {{--<div class="mb10">--}}
                        {{--<label class="control-label">Coupon Code</label>--}}
                        {{--<div class="input-group mb15">--}}
                            {{--<input id="couponCode" type="text" class="form-control" placeholder="Optional" />--}}
                            {{--<span class="input-group-addon" id="couponCheckStatus"></span>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    <div class="row mb10">
                        <div class="col-sm-6">
                            <label class="control-label">Preferred currency</label>
                            <select class="form-control mb15 currencyCalculator" id="preferredCurrency">
                                <option value="priceUSD">USD</option>
                                <option value="priceGBP" >GBP</option>
                                <option value="priceEuro">EUR</option>
                            </select>
                        </div>
                        {{--<div class="col-sm-6">--}}
                            {{--<label class="control-label">&nbsp;</label>--}}
                            {{--<p id="currencyPriceValue" style="margin-top: 10px; font-weight: bold; color: #1CAF9A;"></p>--}}
                        {{--</div>--}}
                    </div>
                    <br>
                    <input type="hidden" id="accountType" name="accountType" value="Paid">

                    {{--<button type="button" onclick="nextStep2()" class="btn btn-success btn-block">Proceed to Payment</button>--}}
                    <button type="button" onclick="nextStep2()" class="btn btn-success btn-block btn-signup">Next</button>
                @else
                    <input type="hidden" id="accountType" name="accountType" value="Trial">
                    <button type="button" onclick="nextStep2()" class="btn btn-success btn-block btn-signup">Sign Up for Free Trial</button>
                @endif
            </div>

            <div class="signup-div creditCartStep_4" id="pleaseWaitDiv" style="display: none; min-height: 480px; vertical-align: middle;text-align: center;">
                <h4 style="vertical-align: middle;position: relative;top: 100px;><img src="{{ URL::asset("assets/images/loaders/loader8.gif") }}" alt=""> Processing your request please wait...</h4>
            </div>
        </div>
    </div>

    <div class="signup-footer">
    </div>
</div>
@stop

@section('javascript')
<script>
$(function(){
    @if($trial_cart == 'No')
        showTotalAmount();

        $('.currencyCalculator').change(function(){
            showTotalAmount();
        });
    @endif

    $('#couponCode').keyup(function(){
        showTotalAmount();
    });

    $('#password').keyup(function(){
        var password = $(this).val();

        if(password.length >= 6) {
            $('#passwordCheck').html('<span class="fa fa-check text-success"></span>');
        }
        else{
            $('#passwordCheck').html('<span class="fa fa-times"></span>');
        }
    });

    $('#retypePassword').keyup(function(){
        var password = $('#password').val();
        var retypePassword = $('#retypePassword').val();

        if(password == retypePassword) {
            $('#retypePasswordCheck').html('<span class="fa fa-check text-success"></span>');
        }
        else {
            $('#retypePasswordCheck').html('<span class="fa fa-times"></span>');
        }
    });

    $('#email').bind('blur keyup click onchange', function(){
        var email = $(this).val();

        var filter = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

        $('#emailCheckText').html("");

        if (!filter.test(email)) {
            $('#emailCheck').html('<span class="fa fa-times"></span>');
        }
        else {
            $.ajax({
                type: 'post',
                url: "{{ URL::route('home.checkemail') }}",
                cache: false,
                data: {"email": email, '_token' : '{!! csrf_token() !!}'},
                beforeSend: function() {

                },
                success: function(obj) {
                    //var obj = jQuery.parseJSON(response);

                    if(obj.success == 'success') {
                        if(obj.emailFound) {
                            $('#emailCheck').html('<span class="fa fa-times"></span>');
                            $('#emailCheckText').html('<span class="text-danger">'+email+'</span> It appears this email address is already registered on our system please use another or contact us for assistance.');
                        }
                        else {
                            $('#emailCheck').html('<span class="fa fa-check text-success"></span>');
                        }
                    }
                }
            });
        }
    });
});


function showTotalAmount() {
    var licenseType = $('#licenseType').val();
    var preferredCurrency = $('#preferredCurrency').val();
    var couponCode = $('#couponCode').val();

    $.ajax({
        type: 'post',
        url: "{{ URL::route('home.showpreferredcurrency') }}",
        cache: false,
        data: {"licenseType": licenseType, "preferredCurrency": preferredCurrency, "couponCode": couponCode, '_token' : '{!! csrf_token() !!}'},
        dataType : 'json',
        beforeSend: function() {

        },
        success: function(obj) {
            if(obj.success == 'success') {
                if (preferredCurrency == "priceUSD") {
                    $('#currencyPriceValue').html("$" + obj.price);
                }
                else if (preferredCurrency == "priceEuro") {
                    $('#currencyPriceValue').html("&euro;" + obj.price);
                }
                else {
                    $('#currencyPriceValue').html("&pound;" + obj.price);
                }

                if(obj.couponSuccess) {
                    $('#couponCheckStatus').html('<span class="fa fa-check text-success"></span>');
                }
                else {
                    $('#couponCheckStatus').html('<span class="fa fa-times"></span>');
                }
            }
        }
    });
}

@if($trial_cart == 'No')
function nextStep2() {
    var firstName = $('#firstName').val();
    var lastName = $('#lastName').val();
    var companyName = $('#companyName').val();
    var contactNumber = $('#contactNumber').val();
    var email = $('#email').val();
    var password = $('#password').val();
    var retypePassword = $('#retypePassword').val();
    var country = $('#country option:selected').text();

    var countryCode = $('#country').val();
    var timeZoneName = $('#timeZoneName').val();

    var licenseType = $('#licenseType').val();
    var licenseName = $('#licenseType option:selected').text();
    var preferredCurrency = $('#preferredCurrency').val();
    //var couponCode = $('#couponCode').val();

    //var total = $('#currencyPriceValue').text();

    var filter = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    var error = "#error";
    $(error).html('');

    if(firstName == '') {
        $(error).html('<div class="alert alert-danger">Please Enter first name.</div>');
    }
    else if(companyName == '') {
        $(error).html('<div class="alert alert-danger">Please Enter Company name.</div>');
    }
    else if(contactNumber == '') {
        $(error).html('<div class="alert alert-danger">Please Enter Contact Number.</div>');
    }
    else if(email == '') {
        $(error).html('<div class="alert alert-danger">Please Enter a Email.</div>');
    }
    else if (!filter.test(email)) {
        $(error).html('<div class="alert alert-danger">Enter Valid Email ID</div>');
    }
    else if(password.length < 6) {
        $(error).html('<div class="alert alert-danger">Please Enter Password more than 6 character.</div>');
    }
    else if(password != retypePassword) {
        $(error).html('<div class="alert alert-danger">Password and Retype Password does not match.</div>');
    }
    else if(countryCode == '') {
        $(error).html('<div class="alert alert-danger">Please select a country.</div>');
    }
    else if(timeZoneName == '') {
        $(error).html('<div class="alert alert-danger">Please select a Timezone.</div>');
    }
    else {
        $('#signUpInfoDiv').hide();
        $('#pleaseWaitDiv').show();

        var formData = {
            "firstName": firstName,
            "lastName": lastName,
            "companyName": companyName,
            "contactNumber": contactNumber,
            "email": email,
            "password": password,
            "country": country,
            "countryCode": countryCode,
            "timeZoneName":timeZoneName,
            "licenseType": licenseType,
            "licenseName": licenseName,
            "preferredCurrency": preferredCurrency,
//            "couponCode": couponCode,
//            "total": total,
            'accountType' : $('#accountType').val(),
            "_token" : '{!! csrf_token() !!}'
        };

        $.ajax({
            type: 'post',
            url: "{{ URL::route('home.makepayment') }}",
            cache: false,
            dataType: "json",
            data: formData,
            success: function(obj) {
                if(obj.status == 'error') {
                    $("#error").html('<div class="alert alert-danger">' + obj.message + '</div>');
                    $("#pleaseWaitDiv").hide();
                    $('#signUpInfoDiv').show();
                }
                else if(obj.status == 'success') {
                    if(obj.action == 'redirect') {
                        location.href = obj.url;
                    }
                }
                else if(obj.success == "fail") {
                    $('#pleaseWaitDiv').hide();
                    $('#signUpInfoDiv').show();
                }
            },
            error: function(xhr, textStatus, thrownError) {
                $('#pleaseWaitDiv').hide();
                $('#signUpInfoDiv').show();
                $("#error").html('<div class="alert alert-danger">An error occurred while processing payment. Please try again!</div>');
            }
        });
    }
}
@else
    function nextStep2() {
    var firstName = $('#firstName').val();
    var lastName = $('#lastName').val();
    var companyName = $('#companyName').val();
    var contactNumber = $('#contactNumber').val();
    var email = $('#email').val();
    var password = $('#password').val();
    var retypePassword = $('#retypePassword').val();
    var country = $('#country option:selected').text();

    var countryCode = $('#country').val();
    var timeZoneName = $('#timeZoneName').val();

    var licenseType = $('#licenseType').val();
    var licenseName = $('#licenseType option:selected').text();

    var filter = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

    var error = "#error";
    $(error).html('');

    if(firstName == '') {
        $(error).html('<div class="alert alert-danger">Please Enter first name.</div>');
    }
    else if(companyName == '') {
        $(error).html('<div class="alert alert-danger">Please Enter Company name.</div>');
    }
    else if(contactNumber == '') {
        $(error).html('<div class="alert alert-danger">Please Enter Contact Number.</div>');
    }
    else if(email == '') {
        $(error).html('<div class="alert alert-danger">Please Enter a Email.</div>');
    }
    else if (!filter.test(email)) {
        $(error).html('<div class="alert alert-danger">Enter Valid Email ID</div>');
    }
    else if(password.length < 6) {
        $(error).html('<div class="alert alert-danger">Please Enter Password more than 6 character.</div>');
    }
    else if(password != retypePassword) {
        $(error).html('<div class="alert alert-danger">Password and Retype Password does not match.</div>');
    }
    else if(countryCode == '') {
        $(error).html('<div class="alert alert-danger">Please select a country.</div>');
    }
    else if(timeZoneName == '') {
        $(error).html('<div class="alert alert-danger">Please select a Timezone.</div>');
    }
    else {
        $('#signUpInfoDiv').hide();
        $('#pleaseWaitDiv').show();

        var formData = {
            "firstName": firstName,
            "lastName": lastName,
            "companyName": companyName,
            "contactNumber": contactNumber,
            "email": email,
            "password": password,
            "country": country,
            "countryCode": countryCode,
            "timeZoneName":timeZoneName,
            "licenseType": licenseType,
            "licenseName": licenseName,
            'accountType' : $('#accountType').val(),
            "_token" : '{!! csrf_token() !!}'
        };

        $.ajax({
            type: 'post',
            url: "{{ URL::route('home.register.trial') }}",
            cache: false,
            dataType: "json",
            data: formData,
            success: function(obj) {
                if(obj.status == 'error') {
                    $("#error").html('<div class="alert alert-danger">' + obj.message + '</div>');
                    $("#pleaseWaitDiv").hide();
                    $('#signUpInfoDiv').show();
                }
                else if(obj.status == 'success'){
                    location.href = obj.redirectUrl;
                }
                else if(obj.status == "fail") {
                    $('#pleaseWaitDiv').hide();
                    $('#signUpInfoDiv').show();
                }
            },
            error: function (xhr, textStatus, thrownError) {
                $('#pleaseWaitDiv').hide();
                $('#signUpInfoDiv').show();
                var error = 'An error occurred while processing. Please try again!';
                var str = '';
                for(var key in xhr.responseJSON) {
                    if (xhr.responseJSON.hasOwnProperty(key)) {
                        var obj = xhr.responseJSON[key];
                        str += '<p>'+obj[0]+'</p>';
                    }
                }

                if(str == ''){
                    str = error;
                }

                $("#error").html('<div class="alert alert-danger">'+str+'</div>');
            }
        });
    }
}
@endif
</script>
@stop
