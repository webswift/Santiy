@extends('layouts.dashboard')

@section('title')
    Change Payment Details
@stop

@section('css')
@stop

@section('content')
<div class="pageheader"><h2><i class="fa fa-user"></i> Edit Profile</h2></div>
<div class="contentpanel">
    <div class="panel">
        <div class="panel-body">
            <div class="pull-right"><a href="{{ route('user.profile') }}">Back to manage account</a></div>
            <div class="clearfix"></div>
            <form role="form" class="form-body" action="{{ route('user.paymentdetail.update') }}" method="post">
                @if(Session::has('successMessage'))
                    <div class="alert alert-success">{{ Session::get('successMessage') }}</div>
                @endif

                @if($errorMessage != '')
                    <div class="alert alert-danger">
                        <a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
                        {{ $errorMessage }}
                    </div>
                @endif

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                {!! csrf_field() !!}
				<h3 class="mt20 text-primary">Billing Email Address</h3>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Preferred Email Address for Invoicing</label>
							<input type="text" class="form-control" name="billing_email" value="{{ $paymentInfo->billing_email or '' }}">
						</div>
					</div>
				</div>

                <h3 class="mb10 text-primary">Payment Method</h3>
                <p>Payment details that you change here will be used for all future contracts.</p>
                <div class="form-group margin-top-20">
                    <label>Select whether you would like to pay credit card or via paypal.</label>
                    <div class="rdio rdio-primary mt10">
                        <input type="radio" name="payment_method" id="card" value="card" @if(isset($paymentInfo->payment_method) && $paymentInfo->payment_method == 'Card') checked @endif>
                        <label for="card">Credit Card</label>
                    </div>
                    <div class="rdio rdio-primary">
                        <input type="radio" name="payment_method" id="paypal" value="paypal" @if(isset($paymentInfo->payment_method) && $paymentInfo->payment_method == 'Paypal') checked @endif>
                        <label for="paypal">Paypal</label>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Billing Agreement Number</label>
                                <input type="text" class="form-control" value="{{ $transaction->id or '' }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div id="paypalDetailDiv">
                        <h3 class="mt20 text-primary">Paypal Account Details</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Use this email-address for paypal account login</label>
                                    <input type="text" class="form-control" name="paypal_email" value="{{ $paymentInfo->paypal_email or '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                <div id="cardDetailDiv">
                    <h3 class="mt20 text-primary">Payment Details</h3>
                    <p>You are currently make payments using the credit card {{ $cardNumber }}. <br> to update your credit card information <a href="javascript:;" id="cardLink">Click here</a></p>
                    <div id="cardInfoDiv" class="hidden">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Card Number</label>
                                            <input class="form-control" name="card_number" type="text" value="{{ $cardNumber or '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Card Verification Code</label>
                                            <input class="form-control" type="text" name="cvv" maxlength="4" value="xxx">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Expires(month/year)</label>
                                            <select class="form-control" name="month">
                                                <option value="MM">MM</option>
                                                <option value="01">01</option>
                                                <option value="02">02</option>
                                                <option value="03">03</option>
                                                <option value="04">04</option>
                                                <option value="05">05</option>
                                                <option value="06">06</option>
                                                <option value="07">07</option>
                                                <option value="08">08</option>
                                                <option value="09">09</option>
                                                <option value="10">10</option>
                                                <option value="11">11</option>
                                                <option value="12">12</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>&nbsp;</label>
                                            <select class="form-control" name="year">
                                                <option value="YYYY">YYYY</option>
                                                <option value="2016"> 2016 </option>
                                                <option value="2017"> 2017 </option>
                                                <option value="2018"> 2018 </option>
                                                <option value="2019"> 2019 </option>
                                                <option value="2020"> 2020 </option>
                                                <option value="2021"> 2021 </option>
                                                <option value="2022"> 2022 </option>
                                                <option value="2023"> 2023 </option>
                                                <option value="2024"> 2024 </option>
                                                <option value="2025"> 2025 </option>
                                                <option value="2026"> 2026 </option>
                                                <option value="2027"> 2027 </option>
                                                <option value="2028"> 2028 </option>
                                                <option value="2029"> 2029 </option>
                                                <option value="2030"> 2030 </option>
                                                <option value="2031"> 2031 </option>
                                                <option value="2032"> 2032 </option>
                                                <option value="2033"> 2033 </option>
                                                <option value="2034"> 2034 </option>
                                                <option value="2035"> 2035 </option>
                                                <option value="2036"> 2036 </option>
                                                <option value="2037"> 2037 </option>
                                                <option value="2038"> 2038 </option>
                                                <option value="2039"> 2039 </option>
                                                <option value="2040"> 2040 </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6"></div>
                        </div>
                        <h3 class="mt20 text-primary">Data of credit card owner</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cardholder's First Name</label>
                                    <input type="text" name="fname" class="form-control" value="{{ $firstName }}">
                                </div>
                                <div class="form-group">
                                    <label>Cardholder's Last Name</label>
                                    <input type="text" name="lname" class="form-control" value="{{ $lastName }}">
                                </div>
                                <div class="form-group">
                                    <label>Country</label>
                                    <select name="country" class="form-control" id="country">

                                    </select>
                                    {{--<input type="text" name="country" class="form-control" value="@if(isset($addressDetails['country'])){{ $addressDetails['country'] }}@endif">--}}
                                </div>
                                <div class="form-group">
                                    <label>House Number, Street Number</label>
                                    <input type="text" name="address" class="form-control" value="@if(isset($addressDetails['address1'])){{ $addressDetails['address1'] }}@endif">
                                </div>
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="@if(isset($addressDetails['city'])){{ $addressDetails['city'] }}@endif">
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="state" class="form-control" value="@if(isset($addressDetails['state'])){{ $addressDetails['state'] }}@endif">
                                </div>
                                <div class="form-group">
                                    <label>Zipcode</label>
                                    <input type="text" name="zipcode" class="form-control" value="@if(isset($addressDetails['pincode'])){{ $addressDetails['pincode'] }}@endif">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions mt10">
                    <input class="btn btn-success" value="Save" type="submit">
                    <input class="btn btn-default" value="Cancel" type="reset">
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('javascript')
{!! Html::script('assets/js/custom.js') !!}

<script>
$(function () {
    changeMethod();
    $('input[name="payment_method"]').change(function () {
        changeMethod();
    });

    $('#cardLink').click(function () {
        $('#cardInfoDiv').removeClass('hidden');
    });

    var countryJSON = [{"countryName":"United States","countryCode":"US","dialCode":"+1"},{"countryName":"United Kingdom","countryCode":"GB","dialCode":"+44"},{"countryName":"Japan","countryCode":"JP","dialCode":"+81"},{"countryName":"China","countryCode":"CN","dialCode":"+86"},{"countryName":"Australia","countryCode":"AU","dialCode":"+61"},{"countryName":"India","countryCode":"IN","dialCode":"+91"},{"countryName":"Afghanistan","countryCode":"AF","dialCode":"+93"},{"countryName":"Aland Islands","countryCode":"AX","dialCode":"+358"},{"countryName":"Albania","countryCode":"AL","dialCode":"+355"},{"countryName":"Algeria","countryCode":"DZ","dialCode":"+213"},{"countryName":"American Samoa","countryCode":"AS","dialCode":"+1 684"},{"countryName":"Andorra","countryCode":"AD","dialCode":"+376"},{"countryName":"Angola","countryCode":"AO","dialCode":"+244"},{"countryName":"Anguilla","countryCode":"AI","dialCode":"+1 264"},{"countryName":"Antarctica","countryCode":"AQ","dialCode":"+672"},{"countryName":"Antigua and Barbuda","countryCode":"AG","dialCode":"+1268"},{"countryName":"Argentina","countryCode":"AR","dialCode":"+54"},{"countryName":"Armenia","countryCode":"AM","dialCode":"+374"},{"countryName":"Aruba","countryCode":"AW","dialCode":"+297"},{"countryName":"Austria","countryCode":"AT","dialCode":"+43"},{"countryName":"Azerbaijan","countryCode":"AZ","dialCode":"+994"},{"countryName":"Bahamas","countryCode":"BS","dialCode":"+1 242"},{"countryName":"Bahrain","countryCode":"BH","dialCode":"+973"},{"countryName":"Bangladesh","countryCode":"BD","dialCode":"+880"},{"countryName":"Barbados","countryCode":"BB","dialCode":"+1 246"},{"countryName":"Belarus","countryCode":"BY","dialCode":"+375"},{"countryName":"Belgium","countryCode":"BE","dialCode":"+32"},{"countryName":"Belize","countryCode":"BZ","dialCode":"+501"},{"countryName":"Benin","countryCode":"BJ","dialCode":"+229"},{"countryName":"Bermuda","countryCode":"BM","dialCode":"+1 441"},{"countryName":"Bhutan","countryCode":"BT","dialCode":"+975"},{"countryName":"Bolivia","countryCode":"BO","dialCode":"+591"},{"countryName":"Bonaire","countryCode":"BQ","dialCode":"+599 7"},{"countryName":"Bosnia and Herzegovina","countryCode":"BA","dialCode":"+387"},{"countryName":"Botswana","countryCode":"BW","dialCode":"+267"},{"countryName":"Bouvet Island","countryCode":"BV"},{"countryName":"Brazil","countryCode":"BR","dialCode":"+55"},{"countryName":"British Indian Ocean Territory","countryCode":"IO","dialCode":"+246"},{"countryName":"Brunei Darussalam","countryCode":"BN","dialCode":"+673"},{"countryName":"Bulgaria","countryCode":"BG","dialCode":"+359"},{"countryName":"Burkina Faso","countryCode":"BF","dialCode":"+226"},{"countryName":"Burundi","countryCode":"BI","dialCode":"+257"},{"countryName":"Cambodia","countryCode":"KH","dialCode":"+855"},{"countryName":"Cameroon","countryCode":"CM","dialCode":"+237"},{"countryName":"Canada","countryCode":"CA","dialCode":"+1"},{"countryName":"Cape Verde","countryCode":"CV","dialCode":"+238"},{"countryName":"Cayman Islands","countryCode":"KY","dialCode":"+ 345"},{"countryName":"Central African Republic","countryCode":"CF","dialCode":"+236"},{"countryName":"Chad","countryCode":"TD","dialCode":"+235"},{"countryName":"Chile","countryCode":"CL","dialCode":"+56"},{"countryName":"Christmas Island","countryCode":"CX","dialCode":"+61"},{"countryName":"Cocos (Keeling) Islands","countryCode":"CC","dialCode":"+61"},{"countryName":"Colombia","countryCode":"CO","dialCode":"+57"},{"countryName":"Comoros","countryCode":"KM","dialCode":"+269"},{"countryName":"Congo","countryCode":"CG","dialCode":"+242"},{"countryName":"Congo, Democratic Republic","countryCode":"CD","dialCode":"+243"},{"countryName":"Cook Islands","countryCode":"CK","dialCode":"+682"},{"countryName":"Costa Rica","countryCode":"CR","dialCode":"+506"},{"countryName":"Cote d\'Ivoire","countryCode":"CI","dialCode":"+225"},{"countryName":"Croatia","countryCode":"HR","dialCode":"+385"},{"countryName":"Curacao","countryCode":"CW","dialCode":"+599"},{"countryName":"Cyprus","countryCode":"CY","dialCode":"+537"},{"countryName":"Czech Republic","countryCode":"CZ","dialCode":"+420"},{"countryName":"Denmark","countryCode":"DK","dialCode":"+45"},{"countryName":"Djibouti","countryCode":"DJ","dialCode":"+253"},{"countryName":"Dominica","countryCode":"DM","dialCode":"+1 767"},{"countryName":"Dominican Republic","countryCode":"DO","dialCode":"+1 849"},{"countryName":"East Timor","countryCode":"TL","dialCode":"+670"},{"countryName":"Ecuador","countryCode":"EC","dialCode":"+593"},{"countryName":"Egypt","countryCode":"EG","dialCode":"+20"},{"countryName":"El Salvador","countryCode":"SV","dialCode":"+503"},{"countryName":"Equatorial Guinea","countryCode":"GQ","dialCode":"+240"},{"countryName":"Eritrea","countryCode":"ER","dialCode":"+291"},{"countryName":"Estonia","countryCode":"EE","dialCode":"+372"},{"countryName":"Ethiopia","countryCode":"ET","dialCode":"+251"},{"countryName":"Falkland Islands (Malvinas)","countryCode":"FK","dialCode":"+500"},{"countryName":"Faroe Islands","countryCode":"FO","dialCode":"+298"},{"countryName":"Fiji","countryCode":"FJ","dialCode":"+679"},{"countryName":"Finland","countryCode":"FI","dialCode":"+358"},{"countryName":"France","countryCode":"FR","dialCode":"+33"},{"countryName":"French Guiana","countryCode":"GF","dialCode":"+594"},{"countryName":"French Polynesia","countryCode":"PF","dialCode":"+689"},{"countryName":"French Southern Territories","countryCode":"TF","dialCode":"+672"},{"countryName":"Gabon","countryCode":"GA","dialCode":"+241"},{"countryName":"Gambia","countryCode":"GM","dialCode":"+220"},{"countryName":"Georgia","countryCode":"GE","dialCode":"+995"},{"countryName":"Germany","countryCode":"DE","dialCode":"+49"},{"countryName":"Ghana","countryCode":"GH","dialCode":"+233"},{"countryName":"Gibraltar","countryCode":"GI","dialCode":"+350"},{"countryName":"Greece","countryCode":"GR","dialCode":"+30"},{"countryName":"Greenland","countryCode":"GL","dialCode":"+299"},{"countryName":"Grenada","countryCode":"GD","dialCode":"+1 473"},{"countryName":"Guadeloupe","countryCode":"GP","dialCode":"+590"},{"countryName":"Guam","countryCode":"GU","dialCode":"+1 671"},{"countryName":"Guatemala","countryCode":"GT","dialCode":"+502"},{"countryName":"Guernsey","countryCode":"GG","dialCode":"+44"},{"countryName":"Guinea","countryCode":"GN","dialCode":"+224"},{"countryName":"Guinea-bissau","countryCode":"GW","dialCode":"+245"},{"countryName":"Guyana","countryCode":"GY","dialCode":"+595"},{"countryName":"Haiti","countryCode":"HT","dialCode":"+509"},{"countryName":"Heard Island and Mcdonald Islands","countryCode":"HM"},{"countryName":"Holy See (Vatican City State)","countryCode":"VA","dialCode":"+379"},{"countryName":"Honduras","countryCode":"HN","dialCode":"+504"},{"countryName":"Hong Kong","countryCode":"HK","dialCode":"+852"},{"countryName":"Hungary","countryCode":"HU","dialCode":"+36"},{"countryName":"Iceland","countryCode":"IS","dialCode":"+354"},{"countryName":"Indonesia","countryCode":"ID","dialCode":"+62"},{"countryName":"Iraq","countryCode":"IQ","dialCode":"+964"},{"countryName":"Ireland","countryCode":"IE","dialCode":"+353"},{"countryName":"Isle Of Man","countryCode":"IM","dialCode":"+44"},{"countryName":"Israel","countryCode":"IL","dialCode":"+972"},{"countryName":"Italy","countryCode":"IT","dialCode":"+39"},{"countryName":"Jamaica","countryCode":"JM","dialCode":"+1 876"},{"countryName":"Jersey","countryCode":"JE","dialCode":"+44"},{"countryName":"Jordan","countryCode":"JO","dialCode":"+962"},{"countryName":"Kazakhstan","countryCode":"KZ","dialCode":"+7 7"},{"countryName":"Kenya","countryCode":"KE","dialCode":"+254"},{"countryName":"Kiribati","countryCode":"KI","dialCode":"+686"},{"countryName":"Kosovo","countryCode":"KV"},{"countryName":"Kuwait","countryCode":"KW","dialCode":"+965"},{"countryName":"Kyrgyzstan","countryCode":"KG","dialCode":"+996"},{"countryName":"Laos","countryCode":"LA","dialCode":"+856"},{"countryName":"Latvia","countryCode":"LV","dialCode":"+371"},{"countryName":"Lebanon","countryCode":"LB","dialCode":"+961"},{"countryName":"Lesotho","countryCode":"LS","dialCode":"+266"},{"countryName":"Liberia","countryCode":"LR","dialCode":"+231"},{"countryName":"Libya","countryCode":"LY","dialCode":"+218"},{"countryName":"Liechtenstein","countryCode":"LI","dialCode":"+423"},{"countryName":"Lithuania","countryCode":"LT","dialCode":"+370"},{"countryName":"Luxembourg","countryCode":"LU","dialCode":"+352"},{"countryName":"Macau","countryCode":"MO","dialCode":"+853"},{"countryName":"Macedonia, The Former Yug","countryCode":"MK","dialCode":"+389"},{"countryName":"Madagascar","countryCode":"MG","dialCode":"+261"},{"countryName":"Malawi","countryCode":"MW","dialCode":"+265"},{"countryName":"Malaysia","countryCode":"MY","dialCode":"+60"},{"countryName":"Maldives","countryCode":"MV","dialCode":"+960"},{"countryName":"Mali","countryCode":"ML","dialCode":"+223"},{"countryName":"Malta","countryCode":"MT","dialCode":"+356"},{"countryName":"Marshall Islands","countryCode":"MH","dialCode":"+692"},{"countryName":"Martinique","countryCode":"MQ","dialCode":"+596"},{"countryName":"Mauritania","countryCode":"MR","dialCode":"+222"},{"countryName":"Mauritius","countryCode":"MU","dialCode":"+230"},{"countryName":"Mayotte","countryCode":"YT","dialCode":"+262"},{"countryName":"Mexico","countryCode":"MX","dialCode":"+52"},{"countryName":"Micronesia, Federated States","countryCode":"FM","dialCode":"+691"},{"countryName":"Moldova","countryCode":"MD","dialCode":"+373"},{"countryName":"Monaco","countryCode":"MC","dialCode":"+377"},{"countryName":"Mongolia","countryCode":"MN","dialCode":"+976"},{"countryName":"Montenegro","countryCode":"ME","dialCode":"+382"},{"countryName":"Montserrat","countryCode":"MS","dialCode":"+1664"},{"countryName":"Morocco","countryCode":"MA","dialCode":"+212"},{"countryName":"Mozambique","countryCode":"MZ","dialCode":"+258"},{"countryName":"Myanmar","countryCode":"MM","dialCode":"+95"},{"countryName":"Namibia","countryCode":"NA","dialCode":"+264"},{"countryName":"Nauru","countryCode":"NR","dialCode":"+674"},{"countryName":"Nepal","countryCode":"NP","dialCode":"+977"},{"countryName":"Netherlands","countryCode":"NL","dialCode":"+31"},{"countryName":"Netherlands Antilles","countryCode":"AN","dialCode":"+599"},{"countryName":"New Caledonia","countryCode":"NC","dialCode":"+687"},{"countryName":"New Zealand","countryCode":"NZ","dialCode":"+64"},{"countryName":"Nicaragua","countryCode":"NI","dialCode":"+505"},{"countryName":"Niger","countryCode":"NE","dialCode":"+227"},{"countryName":"Nigeria","countryCode":"NG","dialCode":"+234"},{"countryName":"Niue","countryCode":"NU","dialCode":"+683"},{"countryName":"Norfolk Island","countryCode":"NF","dialCode":"+672"},{"countryName":"Northern Mariana Islands","countryCode":"MP","dialCode":"+1 670"},{"countryName":"Norway","countryCode":"NO","dialCode":"+47"},{"countryName":"Oman","countryCode":"OM","dialCode":"+968"},{"countryName":"Pakistan","countryCode":"PK","dialCode":"+92"},{"countryName":"Palau","countryCode":"PW","dialCode":"+680"},{"countryName":"Palestine","countryCode":"PS","dialCode":"+970"},{"countryName":"Panama","countryCode":"PA","dialCode":"+507"},{"countryName":"Papua New Guinea","countryCode":"PG","dialCode":"+675"},{"countryName":"Paraguay","countryCode":"PY","dialCode":"+595"},{"countryName":"Peru","countryCode":"PE","dialCode":"+51"},{"countryName":"Philippines","countryCode":"PH","dialCode":"+63"},{"countryName":"Pitcairn","countryCode":"PN","dialCode":"+872"},{"countryName":"Poland","countryCode":"PL","dialCode":"+48"},{"countryName":"Portugal","countryCode":"PT","dialCode":"+351"},{"countryName":"Puerto Rico","countryCode":"PR","dialCode":"+1 939"},{"countryName":"Qatar","countryCode":"QA","dialCode":"+974"},{"countryName":"Reunion","countryCode":"RE","dialCode":"+262"},{"countryName":"Romania","countryCode":"RO","dialCode":"+40"},{"countryName":"Russian Federation","countryCode":"RU","dialCode":"+7"},{"countryName":"Rwanda","countryCode":"RW","dialCode":"+250"},{"countryName":"Saint Barthelemy","countryCode":"BL","dialCode":"+590"},{"countryName":"Saint Helena","countryCode":"SH","dialCode":"+290"},{"countryName":"Saint Kitts and Nevis","countryCode":"KN","dialCode":"+1 869"},{"countryName":"Saint Lucia","countryCode":"LC","dialCode":"+1 758"},{"countryName":"Saint Martin (French Part)","countryCode":"MF","dialCode":"+590"},{"countryName":"Saint Pierre And Miquelon","countryCode":"PM","dialCode":"+508"},{"countryName":"Saint Vincent and The Grenadines","countryCode":"VC","dialCode":"+1 784"},{"countryName":"Samoa","countryCode":"WS","dialCode":"+685"},{"countryName":"San Marino","countryCode":"SM","dialCode":"+378"},{"countryName":"Sao Tome and Principe","countryCode":"ST","dialCode":"+239"},{"countryName":"Saudi Arabia","countryCode":"SA","dialCode":"+966"},{"countryName":"Senegal","countryCode":"SN","dialCode":"+221"},{"countryName":"Serbia","countryCode":"RS","dialCode":"+381"},{"countryName":"Seychelles","countryCode":"SC","dialCode":"+248"},{"countryName":"Sierra Leone","countryCode":"SL","dialCode":"+232"},{"countryName":"Singapore","countryCode":"SG","dialCode":"+65"},{"countryName":"Sint Maarten (Dutch Part)","countryCode":"SX","dialCode":"+1 721"},{"countryName":"Slovakia","countryCode":"SK","dialCode":"+421"},{"countryName":"Slovenia","countryCode":"SI","dialCode":"+386"},{"countryName":"Solomon Islands","countryCode":"SB","dialCode":"+677"},{"countryName":"Somalia","countryCode":"SO","dialCode":"+252"},{"countryName":"South Africa","countryCode":"ZA","dialCode":"+27"},{"countryName":"South Georgia and The South Sandwich Islands","countryCode":"GS","dialCode":"+500"},{"countryName":"South Korea","countryCode":"KR","dialCode":"+82"},{"countryName":"South Sudan","countryCode":"SS","dialCode":"+211"},{"countryName":"Spain","countryCode":"ES","dialCode":"+34"},{"countryName":"Sri Lanka","countryCode":"LK","dialCode":"+94"},{"countryName":"Suriname","countryCode":"SR","dialCode":"+597"},{"countryName":"Svalbard and Jan Mayen","countryCode":"SJ","dialCode":"+47"},{"countryName":"Swaziland","countryCode":"SZ","dialCode":"+268"},{"countryName":"Sweden","countryCode":"SE","dialCode":"+46"},{"countryName":"Switzerland","countryCode":"CH","dialCode":"+41"},{"countryName":"Taiwan","countryCode":"TW","dialCode":"+886"},{"countryName":"Tajikistan","countryCode":"TJ","dialCode":"+992"},{"countryName":"Tanzania","countryCode":"TZ","dialCode":"+255"},{"countryName":"Thailand","countryCode":"TH","dialCode":"+66"},{"countryName":"Togo","countryCode":"TG","dialCode":"+228"},{"countryName":"Tokelau","countryCode":"TK","dialCode":"+690"},{"countryName":"Tonga","countryCode":"TO","dialCode":"+676"},{"countryName":"Trinidad and Tobago","countryCode":"TT","dialCode":"+1 868"},{"countryName":"Tunisia","countryCode":"TN","dialCode":"+216"},{"countryName":"Turkey","countryCode":"TR","dialCode":"+90"},{"countryName":"Turkmenistan","countryCode":"TM","dialCode":"+993"},{"countryName":"Turks and Caicos Islands","countryCode":"TC","dialCode":"+1 649"},{"countryName":"Tuvalu","countryCode":"TV","dialCode":"+688"},{"countryName":"Uganda","countryCode":"UG","dialCode":"+256"},{"countryName":"Ukraine","countryCode":"UA","dialCode":"+380"},{"countryName":"United Arab Emirates","countryCode":"AE","dialCode":"+971"},{"countryName":"United States Minor Outlying Islands","countryCode":"UM","dialCode":"+1"},{"countryName":"Uruguay","countryCode":"UY","dialCode":"+598"},{"countryName":"Uzbekistan","countryCode":"UZ","dialCode":"+998"},{"countryName":"Vanuatu","countryCode":"VU","dialCode":"+678"},{"countryName":"Venezuela","countryCode":"VE","dialCode":"+58"},{"countryName":"Vietnam","countryCode":"VN","dialCode":"+84"},{"countryName":"Virgin Islands (British)","countryCode":"VG","dialCode":"+1 284"},{"countryName":"Virgin Islands (U.S.)","countryCode":"VI","dialCode":"+1 340"},{"countryName":"Wallis and Futuna","countryCode":"WF","dialCode":"+681"},{"countryName":"Western Sahara","countryCode":"EH","dialCode":"+212"},{"countryName":"Yemen","countryCode":"YE","dialCode":"+967"},{"countryName":"Yugoslavia","countryCode":"YU"},{"countryName":"Zambia","countryCode":"ZM","dialCode":"+260"},{"countryName":"Zimbabwe","countryCode":"ZW","dialCode":"+263"}];

    $.each(countryJSON, function(i, val) {
        var item = "<option value='" + val.countryName + '|' + val.countryCode + "' country-code='" + val.countryCode + "' country-name='" + val.countryName + "' dial-code='" + val.dialCode + "' data-icon='glyphicon flag-" + val.countryCode.toLowerCase() + "'>" + val.countryName + "</option>";
        $('#country').append(item);
    });

    @if(isset($addressDetails['country']))
    var val = '{{ $addressDetails['country'] }}' + '|' + '{{ $addressDetails['countryCode'] }}';
    $('#country').val(val);
    @endif
});

function changeMethod() {
    var payment_method = $('input[name="payment_method"]:checked').attr('id');
    if(payment_method == 'card') {
        $('#cardDetailDiv').removeClass('hidden');
        $('#paypalDetailDiv').addClass('hidden');
    }
    else if(payment_method == 'paypal') {
        $('#cardDetailDiv').addClass('hidden');
        $('#paypalDetailDiv').removeClass('hidden');
    }
}
</script>
@stop
