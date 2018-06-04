<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script async="" src="{{ asset('payment/files/gtm.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('payment/files/jquery.selectBoxIt.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('payment/files/jquery-ui-1.9.2.custom.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('payment/files/bootstrap-select.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('payment/files/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('payment/files/tour-component.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('payment/files/zohostorecommonlist.min.css') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=9,IE=10,chrome=1">

    <title>Buy Licence</title>
    <link href="{{ asset('payment/files/css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('payment/files/css(1)') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .freezeDiv {
            background: #FFFFFF;
            display: none;
            height: 100%;
            left: 0;
            opacity: 0.91;
            position: absolute;
            top: 0;
            width: 100%;
            z-index: 99;
            box-shadow: 2px 3px 3px #ccc
        }
        .processingHeader {
            font-size: 20px;
            margin: 5px 0 10px
        }
        .processing-message {
            font-size: 16px;
            font-weight: 300
        }
        .processGif {
            background: url(files/processing.gif) no-repeat 6px 0px;
            width: 55px;
            height: 55px
        }

        .hide {
            display: none
        }

        .inlineBlock,
        .ib,
        .footerLink a {
            display: inline-block
        }

        .mR10 {
            margin-right: 10px
        }

        .pB10 {
            padding-bottom: 10px
        }

        .manageplanFreeze {
            background: #EAEAEA;
            opacity: 1;
            top: 48px
        }
        .hisSort li,
        .dtSort li {
            cursor: pointer;
            padding: 10px;
            text-shadow: 0 1px 0.5px #FFFFFF;
        }

        .newcard {
            background: none repeat scroll 0 0 #F3F3F3;
            font-weight: 400;
            padding-left: 41px;
            margin-top: 10px;
        }
        /*.tyComb {height:auto;}*/

        .cholderNumber {
            margin-left: 10px;
            font-weight: 400;
            float: left;
            color: #000
        }

        .cholderName {
            font-size: 13px;
            line-height: 10px;
            float: left;
        }
        .cComb {
            height: 31px
        }

        .tyComb .ddChild {
            max-height: 400px;
            overflow: scroll
        }

        .dd .divider {
            border-left: 1px solid #D0D0D0;
            right: 24px;
        }

        .dd .ddChild li {
            padding: 5px;
            background-color: #fff;
        }

        .ddlabel {
            white-space: nowrap
        }

        #expMonth,
        #expYear {
            width: 105px;
            margin-right: 8px
        }

        #usStates_msdd {
            display: none
        }

        #selectcountry,
        #usStates {
            width: 148px
        }

        .ddOutOfVision {
            position: relative!important;
            width: 0
        }
        #volume_users:focus {
            border: none;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('payment/files/floatsupportbtn.css') }}">
</head>

<body style="background-color: rgb(234, 234, 234);" cz-shortcut-listen="true">
    <div id="static_content">
        <div class="cp subscriptionDiv contentPanel">
            <div class="subscription-title">
                @if($purchaseType == 'new')
                    <span class="subscribeContChange">Subscribe to</span>
                @elseif($purchaseType == 'renew')
                    <span class="subscribeContChange">Renew</span>
                @elseif($purchaseType == 'teamUpdate')
                    <span class="subscribeContChange">Add/remove Team Member to</span>
                @elseif($purchaseType == 'resubscribe')
                    <span class="subscribeContChange">Resubscribe to</span>
                @endif
                <span class="domainName">SanityOS</span>
                <span class="serviceName fUppercase">CRM</span> in
                <span class="stepCount">3</span> simple steps
            </div>
            @if($purchaseType == 'resubscribe')
            <div class="newUrl" style="font-size: 17px;">SanityOS have recently chnaged their prices, please update you subscription. Don't worry you won't be charged twice,
                the new rate will be applied on your normal billing rate.</div>
            @endif
            <div id="messageinfo" class="announce_hdrmn announcemnt">
                <div class="infoIcon"></div>
                <div class="announce_shdr infoCont tal">
                    <span id="edition" class="mT5 lh24">Multi User Licence</span>
                </div>
            </div>
            <div id="message_notification" class="mB20 mT5 hide" style="width: 980px;">
                <div class="errorMsgCont" style="padding: 18px 18px 0px 24px">
                    <p class="fW300 f13 c99" id="errormsg">@if(isset($error)) {{ $error }} @endif</p>
                </div>
            </div>
            <div class="mT10 subCDiv planDetailHd">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" height="50">
                    <tbody>
                    <tr>
                        <td width="24"><span class="planNos" stepno="2">1</span></td>
                        <td>
                            <span class="mL20 hwinAc">Plan Details</span>
                            <table width="100%" border="0" cellspacing="0" cellpadding="5" class="hide hdDet">
                                <tbody>
                                <tr>
                                    <td width="270" bgcolor="#f5f5f5" class="pL20 c99">Plan Details</td>
                                    <td class="pL20">
                                        <span class="selectedPayPeriod fCaps f13 lh22 rel" style="top:2px"></span>
                                        <div class="cStroageT wizardSeltdFeatr">
                                            <span class="currencyClass"></span>
                                            <span class="totalToPayBand"></span>
                                            <span class="pricingPeriod"></span>
                                        </div>
                                    </td>
                                    <td width="25%">
                                        <span class="planEditBtn fr mR10 f15"></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="whiteBG subDetDiv planDetails rel">
                <div class="addonContainer tac">
                    <div class="plan-details-container clearfix">
                        <div class="sites-addon-err"></div>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="planDetailsCont pT15 tal">
                                <tbody>
                                <tr class="paymentDuration">
                                    <td>Payment Duration</td>
                                    @if($purchaseType == 'teamUpdate' || $purchaseType == 'resubscribe')
                                        <td colspan="2"> {{ $user->payment_info->recurring_type or 'Monthly' }} </td>
                                    @else
                                    <td colspan="2">
                                        <div class="payperiodDiv rel" style="top:3px">
                                            <input type="radio" name="duration" id="monthly" value="Monthly" class="mLZero" @if(isset($user->payment_info->recurring_type) && $user->payment_info->recurring_type == 'Monthly') checked @endif>
                                            <label for="monthly" class="fCaps" type="radio">Monthly</label>&nbsp;

                                            <input type="radio" name="duration" id="yearly" value="Annually" class="mLZero" @if(isset($user->payment_info->recurring_type) && $user->payment_info->recurring_type == 'Annually') checked @endif>
                                            <label for="yearly" class="fCaps" type="radio">Yearly</label>&nbsp;
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                                <tr class="list-Addon">
                                    <td addonlabel="Storage"> Licence Type </td>
                                    <td width="315" style="">
                                        {{ $licenceTypeDetail->licenseClass }} User Licence
                                        {{--(@if($freeUsers == 0) Admin Only @else Admin + {{ $freeUsers }} Users @endif)--}}
                                    </td>
                                </tr>
                                <tr class="list-Addon">
                                    <td addonlabel="Storage"> Currency </td>
                                    <td width="315" style="" colspan="2">
                                        <select class="selectpicker bootstrap-select cardInpt h28 p2 clEnumPrice w90 addons enumDD dd tyComb" sameprice="true"
                                                @if($purchaseType == 'teamUpdate' || $purchaseType == "resubscribe") disabled @endif
                                                value="1" data-size="6" classification="per-product" addonid="204" id="currency" onchange="setCurrency(this.value);">
                                            <option value="USD" @if($currency == 'USD') selected @endif>USD</option>
                                            <option value="GBP" @if($currency == 'GBP') selected @endif>GBP</option>
                                            <option value="EUR" @if($currency == 'EUR') selected @endif>EUR</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="list-Addon">
                                    @if($purchaseType == 'teamUpdate')
                                        <td addonlabel="Storage">
                                            No. of <strong>Users</strong> you want to
                                            <select id="teamUpdateType">
                                                <option value="">Select</option>
                                                <option value="add">add</option>
                                                <option value="remove">remove</option>
                                            </select>
                                        </td>
                                        <td width="315">
                                            <input type="text" id="volume_users" value="" class="fl mR5" style="width: 71px">
                                        </td>
                                    @elseif($purchaseType == 'resubscribe')
                                        <td addonlabel="Storage"> No. of <strong>Users</strong> <span class="currencyClass"></span> <span class="perUserPrice"></span>/user/month</td>
                                        <td width="315">
											{{-- !!! other code expects volume_uses without admin --}}
                                            <input type="text" id="volume_users" value="{{ $totalUsers + 1 }}" disabled class="cardInpt w270 fl mR5">
                                        </td>
                                    @else
                                    <td addonlabel="Storage" style="vertical-align: top"> No. of <strong>Users</strong> <span class="currencyClass"></span> <span class="perUserPrice"></span>/user/month</td>
                                    <td width="315">
										{{-- !!! other code expects volume_uses without admin --}}
                                        <input type="text" id="volume_users" value="{{ $totalUsers + 1 }}" class="fl mR5" style="width: 71px">
                                        <span class="help-block w270">When a team user is added/removed you must proceed to the merchant to confirm the change in value.</span>
                                    </td>
                                    @endif
                                </tr>
                                <tr class="totalRow hoverInactive">
                                    <td colspan="2" align="right" class="pR12">
                                        <div>Total</div>
                                    </td>
                                    <td class="tac">
                                        <div>
                                            <span class="currencyClass"></span>
                                            <span class="ib tar"><span class="totalPrice"></span></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="discountRow hoverInactive">
                                    <td colspan="2" align="right">
                                        <div><span class="totalDiscountPercent"></span> Discount</div>
                                    </td>
                                    <td class="tac">
                                        <div>
                                            <span class="discount-minus abs">-</span>
                                            <span class=" currencyClass"></span>
                                            <span class="ib tar"><span class="discountPrice"></span></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="fW400 cfff lh22">
                                    <td colspan="2" align="right" class="totalAmountBG bg444 ">
                                        @if($purchaseType == 'teamUpdate')
                                            Amount to be <span class="amountDesc">added/removed to/from</span> your billing cycle
                                        @else
                                            Amount to be paid per <span class="payPeriod">year</span>
                                        @endif
                                    </td>
                                    <td class="f16 bg333 fPrice" nowrap="nowrap">
                                        <span class="currencyClass pR2">$</span>
                                        <span class="ib tar"><span class="ib tar addonAmt totalToPay">0.00</span></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div id="addonPurchaseInfo" class="purchasedAlert hide"></div>
                            <input class="placeOrder1 plOrder" value="Continue" type="button">
                        </div>
                    </div>
            </div>
            <div class="mT10 subCDiv confirmOrderTitle" bgcolor="#F5F5F5">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" height="50">
                    <tbody>
                    <tr>
                        <td width="24"><span class="planNos" stepno="3">2</span></td>
                        <td>
                            <span class="mL20 hwinAc">Review Order</span>
                            <table width="100%" border="0" cellspacing="0" cellpadding="5" class="hide hdDet">
                                <tbody>
                                <tr>
                                    <td width="270" bgcolor="#f5f5f5" class="pL20 c99">Review Order</td>
                                    <td class="pL20"><span class="feature-available"></span></td>
                                    <td width="25%"></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="confirmOrderTitle whiteBG subDetDiv hide tac">
                <div class="confirmContainer">
                    <div class="plan-details-container clearfix mT35">
                        <div class="orderSumryHdr fW400 tal">Order Summary</div>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="confirmAddon tal summaryTbl f16">
                            <tbody>
                            <tr height="40" class="planAddonBdr c666">
                                <td width="150">Item</td>
                                <td width="1"></td>
                                <td>Per User Price</td>
                                <td><span class="f12">No. of</span> Users</td>
                                <td class="addOn-priceCont">Total <span class="f12">/<span class="payPeriod"></span></span></td>
                            </tr>
                            <tr id="planUnderList" class="fW400">
                                <td><span class="lh22 cPlanT">SanityOS License</span></td>
                                <td></td>
                                <td>
                                    <span class="currencyClass"></span>
                                    <span class="cPlanPrice" price="8640" planid="14804" addonid="644"></span>
                                    <span class="addOn-unit-text"> /<span class="payPeriod"></span></span>
                                </td>
                                <td><span class="confUnit">1</span> <span class="addOn-unit-text fCaps">users</span></td>
                                <td class="fPrice addOn-priceCont" nowrap="nowrap">
                                    <span class=" fW400 currencyClass pR2"></span>
                                    <span class="ib f16 tar" style="width: 63px;text-align: left;">
                                        <span class="addOnPrice planPrice" planid="14804" price="0" addonid="644"></span>
                                    </span>
                                </td>
                            </tr>
                            <tr addonconfirm="Storage"><td colspan="5" height="1" bgcolor="#e3e3e3" class="rowSplit"></td></tr>
                            <tr addonconfirm="Storage" class="hide  fW400">
                                <td>Storage Add-On</td>
                                <td></td>
                                <td></td>
                                <td><span class="confUnit"></span> <span class="addOn-unit-text fCaps">GB</span></td>
                                <td class="fPrice addOn-priceCont" nowrap="nowrap" addonname="Storage">
                                    <span class=" fW400 pR2 currencyClass">$</span>
                                    <span class="f16 ib tar"><span class="addOnPrice" addonid="">0.00</span></span>
                                </td>
                            </tr>
                            <tr class="discountSeperator hide hoverInactive">
                                <td colspan="5"></td>
                            </tr>
                            <tr class="totalRow hide hoverInactive">
                                <td colspan="4" align="right" class="pR12"> <div>Total</div> </td>
                                <td class="tac">
                                    <div>
                                        <span class=" currencyClass"></span>
                                        <span class="ib tar"><span class="totalPrice"></span></span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="discountRow hide hoverInactive">
                                <td colspan="4" align="right">
                                    <div><span class="totalDiscountPercent"></span> Discount</div>
                                </td>
                                <td class="tac">
                                    <div>
                                        <span class="discount-minus abs">-</span>
                                        <span class="currencyClass"></span>
                                        <span class="ib tar"><span class="discountPrice"></span></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" align="right" class="totalAmountBG bg444 lh22 cfff">
                                    @if($purchaseType == 'teamUpdate')
                                        Amount to be <span class="amountDesc">added/removed to/from</span> your billing cycle
                                    @else
                                        Amount to be paid per <span class="payPeriod">year</span>
                                    @endif
                                </td>

                                <td width="130" class="f16 cfff bg333 addOn-priceCont" nowrap="nowrap">
                                    <span class=" currencyClass pR2">$</span><span class="ib tar"><span class="ib tar addonAmt totalToPay"></span></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <input class="placeOrder1 confirmOrder" value="Confirm" type="button">
                        <div class="yearly-discount-gained hide">
                            Hooray! You have saved <span class="currency-symbol currencyClass ">$</span>
                            <span class="savedPrice" style="width: 14px;"></span> /year
                        </div>

                        <div class="proDueAmount hide" style="display:none">
                            <span id="content">Amount to be paid now</span> :
                            <span class="currencyClass"></span>
                            <span class="proDueAmt">0.00</span>
                        </div>

                        <div id="grannySwitch" class="pointer hide">
                            <span class="cnewplanIc fl"></span><span class="chCont nowrap fl"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mT10 subCDiv paymentDetails">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" height="50">
                    <tbody>
                    <tr>
                        <td width="24"><span class="planNos" stepno="4">3</span></td>
                        <td>
                            <span class="mL20 hwinAc dispcdetails">Payment Details</span>
                            <table width="100%" border="0" cellspacing="0" cellpadding="5" class="hide hdDet">
                                <tbody>
                                <tr>
                                    <td width="25%">
                                        <span class="f13 c99 lh12">Name on card</span>
                                        <br><span class="f18 crdName lh16"> - </span>
                                    </td>
                                    <td width="25%"><span class="f13 hdrBillAdress w165 ib lh16"><br></span></td>
                                    <td width="25%">
                                        <span class="f13 c99 lh12">Card Number</span>
                                        <br>
                                        <span class="fl" style="padding-top:2px;margin-right:3px">*** </span>
                                        <span class="f18 cnumber lh16">1111</span>
                                    </td>
                                    <td width="25%"><span class="planEditBtn fr mR10 f15"></span></td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class=" whiteBG subDetDiv hide ccardDiv">
                <div class="clearfix" id="cardContainer">
                    <div class="">
                        <form name="card_validation" autocomplete="off" class="creditCardForm" id="myCCForm" method="post" action="{{ URL::route('user.payment.process') }}" target="_top">
                            <input id="token" name="token" type="hidden" value="">
                            <input id="selectedUsers" name="selectedUsers" type="hidden" value="">
                            <input id="paymentMethod" name="paymentMethod" type="hidden" value="">

                            @if($purchaseType == 'teamUpdate')
                            <input id="action" name="action" type="hidden" value="">
                            @elseif($purchaseType == 'resubscribe')
                                <input id="action" name="action" type="hidden" value="resubscribe">
                            @endif

                            {!! csrf_field() !!}
                            <input type="text" style="display:none" />
                            <input type="password" style="display:none" />
                            <ul class="payment-details-menu clearfix">
                                <li class="choose-payment">Choose Payment Option</li>
                                <li class="payment-menu payment-active" tab="ccardContainer">Pay with Credit Card</li>
                                <li class="payment-menu" tab="paypalContainer">Pay with <span class="payPalTxt"></span></li>
                                <li class="payment-menu hide" tab="poContainer">Purchase Order</li>
                            </ul>
                            <div id="ccardContainer" class="payment-method fW400">
                                <div class="mT10 rel cardSelection" style="display: none;">
                                    <table width="840" border="0" cellspacing="0" cellpadding="0" class="ccardTbl pT15 m0auto">
                                        <tbody>
                                        <tr>
                                            <td align="right" width="180" valign="middle">
                                                <p class="existingCardTitle">Choose your credit card to make payment</p>
                                            </td>
                                            <td class="chooseccard">
                                                <select id="chooseccard" name="chooseccard" maxlength="50" class="w270 selectBoxIt bootstrap-select multiLineSelectOption choose-existing-ccard" ></select>
                                                <div class="existingcard-tip-container ccDetailsDiv hide" style="display: none;">
                                                    <div class="existingcard-tip-header"> Other sanityos subscriptions by this card: </div>
                                                    <div>
                                                        <table width="100%" border="0" cellspacing="0" cellpadding="20" class="Usage detailedView subscriptionView">
                                                            <tbody>
                                                            <tr>
                                                                <td width="250" style="border-right:1px solid #eaeaea">
                                                                    <span>Products</span>: <span id="mul_services" class="fW300"></span>
                                                                </td>
                                                                <td valign="top"><span class="fW400">Next Renewal Amount</span>:
                                                                    <div class="ib"><span class="">$</span><span id="mul_totalrenewal" class="fW300"></span></div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td valign="top" colspan="2"><span>Next Renewal Date</span>: <span id="mul_nextpaydate" class="fW300"></span></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <table border="0" cellspacing="0" cellpadding="0" width="840" class="ccardTbl pT25 hide m0auto" id="cardTable" style="display: table;">
                                    <tbody>
                                    <tr><td colspan="3"><div class="cardError"></div></td></tr>
                                    <tr>
                                        <td align="right" width="180">Card number</td>
                                        <td colspan="2">
                                            <input type="text" class="cardInpt w270 ccardNo fl mR5" id="ccNo" autocomplete="off" label="Card number" maxlength="16" mandatory="mandatory" value="@if(isset($cardNumber['cardNumber'])){{ $cardNumber['cardNumber'] }}@endif">
                                            <input type="hidden" id="cctype" name="type" maxlength="50">
                                            <div class="cTypeTd">
                                                <span class="visaIc inlineBlock" title="Visa"></span>
                                                <span class="masterIc inlineBlock" title="Master"></span>
                                                <span class="jcbIc inlineBlock" title="JCB"></span>
                                                <span class="discoverIc inlineBlock" title="Discover"></span>
                                                <span class="dinersclubIc inlineBlock" title="DinersClub"></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right">Card expires on</td>
                                        <td colspan="2">
                                            <select class="cardInpt h28 p1 eMon selectBoxIt bootstrap-select w110" maxlength="2" label="Month" mandatory="mandatory" id="expMonth" data-size="10">
                                                <option value="MM">MM</option>
                                                <option value="01" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '01') selected @endif>01</option>
                                                <option value="02" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '02') selected @endif>02</option>
                                                <option value="03" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '03') selected @endif>03</option>
                                                <option value="04" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '04') selected @endif>04</option>
                                                <option value="05" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '05') selected @endif>05</option>
                                                <option value="06" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '06') selected @endif>06</option>
                                                <option value="07" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '07') selected @endif>07</option>
                                                <option value="08" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '08') selected @endif>08</option>
                                                <option value="09" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '09') selected @endif>09</option>
                                                <option value="10" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '10') selected @endif>10</option>
                                                <option value="11" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '11') selected @endif>11</option>
                                                <option value="12" @if(isset($cardNumber['expMonth']) && $cardNumber['expMonth'] == '12') selected @endif>12</option>
                                            </select>
                                            <span class="w44 ib tac pT5"> / </span>
                                            <select class="cardInpt h28 p1 eYear mL7 selectBoxIt  bootstrap-select w110" maxlength="2" label="Year" mandatory="mandatory" id="expYear" data-size="10">
                                                <option value="YYYY">YYYY</option>
                                                <option value="2016" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2016') selected @endif> 2016 </option>
                                                <option value="2017" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2017') selected @endif> 2017 </option>
                                                <option value="2018" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2018') selected @endif> 2018 </option>
                                                <option value="2019" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2019') selected @endif>2019</option>
                                                <option value="2020" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2020') selected @endif>2020</option>
                                                <option value="2021" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2021') selected @endif>2021</option>
                                                <option value="2022" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2022') selected @endif>2022</option>
                                                <option value="2023" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2023') selected @endif>2023</option>
                                                <option value="2024" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2024') selected @endif>2024</option>
                                                <option value="2025" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2025') selected @endif>2025</option>
                                                <option value="2026" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2026') selected @endif>2026</option>
                                                <option value="2027" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2027') selected @endif>2027</option>
                                                <option value="2028" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2028') selected @endif>2028</option>
                                                <option value="2029" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2029') selected @endif>2029</option>
                                                <option value="2030" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2030') selected @endif>2030</option>
                                                <option value="2031" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2031') selected @endif>2031</option>
                                                <option value="2032" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2032') selected @endif>2032</option>
                                                <option value="2033" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2033') selected @endif>2033</option>
                                                <option value="2034" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2034') selected @endif>2034</option>
                                                <option value="2035" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2035') selected @endif>2035</option>
                                                <option value="2036" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2036') selected @endif>2036</option>
                                                <option value="2037" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2037') selected @endif>2037</option>
                                                <option value="2038" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2038') selected @endif>2038</option>
                                                <option value="2039" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2039') selected @endif>2039</option>
                                                <option value="2040" @if(isset($cardNumber['expYear']) && $cardNumber['expYear'] == '2040') selected @endif>2040</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right">Security code</td>
                                        <td colspan="2">
                                            <input type="password" autocomplete="off" class="cardInpt w50 cvvIn" id="cvvCard" label="Security code" maxlength="3" mandatory="mandatory">
                                            <div id="cvv-fullview"><img src="{{ asset('payment/files/cvv-large.png') }}"></div>
                                            <span class="f13 italic" style="color:#8e8e8e">The 3 digit number printed on back of the card</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right" rowspan="3" valign="top">
                                            Address <br>
                                            <span class="addressSubText ib">
                                                (Enter as it appears on your credit card statement. This is required to verify your credit card.)
                                            </span>
                                        </td>
                                        <td class="countryTd" valign="top" colspan="2"> </td>
                                    </tr>
                                    <tr id="creditcard-address">
                                        <td class="usCardstInf hide">
                                            <input class="w270 cardInpt usStreetAddress" type="text" label="Street Address" placeholder="Street Address" name="cardAddress" mandatory="mandatory" value="@if(isset($addressDetails['address1'])){{ $addressDetails['address1'] }}@endif">
                                        </td>
                                        <td class="notUsCardstInf">
                                            <textarea maxlength="500" class="w270 h69 cardInpt billAddress p4" label="Street address" placeholder="Street Address" name="cardAddress" id="address1" mandatory="mandatory">@if(isset($addressDetails['address1'])){{ $addressDetails['address1'] }}@endif</textarea>
                                        </td>
                                        <td class="usCardstInf hide">
                                            <input class="w300 cardInpt ccSuite fr" type="text" label="Apt./Suite" placeholder="Apt./Suite" name="suite">
                                        </td>
                                        <td class="notUsCard" valign="top">
                                            <input maxlength="50" type="text" id="city" name="cardCity" class="cardInpt cardCity w300 mB20 fr" mandatory="mandatory" label="City" placeholder="City" value="@if(isset($addressDetails['city'])){{ $addressDetails['city'] }}@endif">
                                            <div class="fr mT13">
                                                <input maxlength="50" type="text" name="cardState" class="cardInpt cardStateInp w175 fl mR5" mandatory="mandatory" label="State" placeholder="State" value="@if(isset($addressDetails['state'])){{ $addressDetails['state'] }}@endif" id="state">
                                                <select id="indStates" placeholder="State" class="selectBoxIt cardInpt w175 fl indStates cardIndStates hide" name="cardState" mandatory="mandatory" label="State" titleattr="State" data-size="10">
                                                    <option value="SelectState">Select State</option>
                                                    <option value="andhra pradesh">Andhra Pradesh</option>
                                                </select>

                                                <input type="text" name="cardZipCode" maxlength="50" id="pincode" class="cardInpt cardZCode w100 mL20" mandatory="mandatory" placeholder="ZIP/Postal" label="Zip/Postal code" value="@if(isset($addressDetails['pincode'])){{ $addressDetails['pincode'] }}@endif">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="usCardTd hide" id="creditcard-address">
                                        <td>
                                            <input maxlength="50" type="text" id="city" name="cardCity" class="cardInpt usCardCity fCaps w270" mandatory="mandatory" label="City" placeholder="City">
                                        </td>
                                        <td valign="top">
                                            <div class="fr">
                                                <select maxlength="50" class="cardInpt h28 w175 cardStateSel selectBoxIt hide p0 h34 usStates" name="cc_card_state" mandatory="mandatory" label="State" id="usStates" data-size="10">
                                                    <option value="none">Select State</option>
                                                    <option value="Alabama">Alabama</option>
                                                </select>

                                                <input type="text" name="cardZipCode" maxlength="50" class="cardInpt usCardZCode w100 mL20" mandatory="mandatory" placeholder="ZIP/Postal" label="Zip/Postal code">
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <div id="billAddrTable" class="hide" style="display: block;">
                                    <div class="billing-address-split mT15"></div>
                                    <table border="0" cellspacing="0" cellpadding="0" width="840" class="ccardTbl m0auto pT10" id="invoice-address-option">
                                        <tbody>
                                        <tr>
                                            <td align="right" width="180">
                                                <div class="billing-address-sub rel">
                                                    <span class="addressHeadLabel fCaps">Billing address</span>
                                                    <div class="aboutIcn ib vam mL5 about-billing-address">
                                                        <div class="billing-details-pop tal">
                                                            <span class="invoice-image"></span>
                                                            <p class="invoicemsg">By default, we’ll mention your <span class="used-card">credit card’s billing</span> address as your <span class="fW600">Bill To</span> address in your invoices. To change this, choose <span class="fW600">Enter a new address</span> option.</p>
                                                            <p class="mT10"><span class="fW600">Note:</span> Henceforth, this address will be used for all your invoices. Of course, you can change this later in <span class="fW600">Billing Details</span> section.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td valign="top" colspan="2">
                                                <input type="radio" name="billingAddress" id="card-address" class="mLZero" checked="checked" onchange="changeInvoiceAddressOption()">
                                                <label type="radio" for="card-address" class="mR10 card-address-label">Use the above address</label>
                                                <input type="radio" name="billingAddress" id="new-address" class="mLZero nsYear " onchange="changeInvoiceAddressOption()">
                                                <label type="radio" for="new-address" class="mR10 card-address-label1">Enter a new address</label>
                                            </td>
                                        </tr>
                                        <tr class="hide invoice-billing-address">
                                            <td align="right" rowspan="2" valign="top" width="180">Address</td>
                                            <td class="countryTd" valign="top" colspan="2"> </td>
                                        </tr>
                                        <tr class="hide invoice-billing-address">
                                            <td class="usCardstInf hide" valign="top">
                                                <input class="w270 cardInpt invoice-billAddress usStAddress fCaps" type="text" label="Street Address" placeholder="Street Address" name="streetAddress" mandatory="mandatory">
                                            </td>
                                            <td class="notUsCardstInf">
                                                <textarea maxlength="500" class="w270 h69 invoice-billAddress cardInpt stAddress p4" label="Street address" placeholder="Street Address" name="streetAddress" mandatory="mandatory" id="billing_address1">@if(isset($billingAddress['address1'])){{ $billingAddress['address1'] }}@endif</textarea>
                                            </td>
                                            <td class="usCardstInf hide" style="padding-left:36px">
                                                <input class="w300 cardInpt ccSuite fr" type="text" label="Apt./Suite" placeholder="Apt./Suite" name="suite">
                                            </td>
                                            <td class="notUsCard" valign="top">
                                                <input maxlength="50" type="text" id="city" name="city" class="cardInpt city invoice-city w300 fr" mandatory="mandatory" label="City" placeholder="City" value="@if(isset($billingAddress['city'])){{ $billingAddress['city'] }}@endif">
                                                <div class="fr mT13">
                                                    <input maxlength="50" type="text" name="state" class="cardInpt stateInp invoice-stateInp w175 fl mR5" mandatory="mandatory" label="State" placeholder="State" value="@if(isset($billingAddress['state'])){{ $billingAddress['state'] }}@endif" id="billing_state">
                                                    <select id="indStates" placeholder="State" class="selectBoxIt invoice-stateSel cardInpt w175 stateSel fl indStates cardIndStates hide" name="state" mandatory="mandatory" label="State" titleattr="State" data-size="10">
                                                        <option value="SelectState">Select State</option>
                                                        <option value="andhra pradesh">Andhra Pradesh</option>
                                                    </select>

                                                    <input type="text" name="zipCode" maxlength="50" class="cardInpt invoice-zCode zCode w100 mL20" mandatory="mandatory" placeholder="ZIP/Postal" label="Zip/Postal code" value="@if(isset($billingAddress['pincode'])){{ $billingAddress['pincode'] }}@endif" id="billing_pincode">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="usCardTd hide invoice-billing-address">
                                            <td></td>
                                            <td>
                                                <input maxlength="50" type="text" id="city" name="city" class="cardInpt invoice-city usCity mB20 w270" mandatory="mandatory" label="City" placeholder="City">
                                            </td>
                                            <td valign="top">
                                                <div class="fr">
                                                    <select maxlength="50" class="cardInpt h28 w175 stateSel invoice-stateSel selectBoxIt hide p0 h34 usStates" name="card_state" mandatory="mandatory" label="State" id="usStates" data-size="10">
                                                        <option value="none">Select State</option>
                                                        <option value="Alabama">Alabama</option>
                                                    </select>
                                                    <input type="text" name="zipCode" maxlength="50" class="cardInpt invoice-zCode usZCode w100 mL20" mandatory="mandatory" placeholder="ZIP/Postal" label="Zip/Postal code">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr id="billingAddress">
                                            <td align="right" width="180">Cardholder's First Name</td>
                                            <td class="tal">
                                                <input type="text" maxlength="100" name="invoice_billingName" class="cardInpt companyname" mandatory="mandatory" label="Company/Individual name" id="companyname" value="{{ $firstName }}">
                                            </td>
                                            <td align="right" width="180">Last Name</td>
                                            <td class="tal">
                                                <input type="text" maxlength="100" name="invoice_billingLastName" class="cardInpt companyname" mandatory="mandatory" label="Company/Individual name" id="lastname" value="{{ $lastName }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="right" width="180">Phone</td>
                                            <td class="tal">
                                                <input type="text" name="phone" maxlength="50" class="cardInpt phoneNo" mandatory="mandatory" label="Phone no." id="phoneNo" value={{ $user->payment_info->phone_no or '' }}>
                                            </td>
                                            {{--<td align="right" width="100" class="pR3"><span class="pR7">Phone</span>--}}
                                                {{--<input type="text" name="phone" maxlength="50" class="cardInpt phoneNo w100" mandatory="mandatory" label="Phone no." id="phoneNo" value={{ $user->payment_info->phone_no or '' }}>--}}
                                            {{--</td>--}}
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="payment-method hide tac c444 pB20" id="paypalContainer">
								<div class="cardError"></div>
                                <div class="paypalDetails hide">
                                    <div class="alreadyInvicedPaypalMessage">
                                        <div class="f16 mT15">Click "Continue" to proceed.</div>
                                        <div class="f16">You will be redirected to the PayPal website where you can login using your PayPal account and complete your payment. </div>
                                    </div>
                                    <div class="newcardPaypalMessage">
                                        <div class="f16 mT15">Click "Continue" to proceed. You will be redirected to the PayPal website where you can log in</div>
                                        <div class="f16">or create a PayPal account and update your payment method.</div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: none;" class="payment-method hide tac" id="poContainer">
                                <div id="billAddrTable" class="hide" style="display: block;">
                                    <div class="billing-address-split mT15"></div>
                                    <table border="0" cellspacing="0" cellpadding="0" width="840" class="ccardTbl m0auto pT10" id="invoice-address-option1">
                                        <tbody>
                                        <tr class="invoice-billing-address">
                                            <td align="right" rowspan="2" valign="top" width="180">Address</td>
                                            <td class="countryTd tal" valign="top" colspan="2">
                                                <select class="selectBoxIt cardInpt p2 selCountry w270 selectCountry invoice-billing-country" label="Country" name="country" mandatory="mandatory" maxlength="50" data-size="10">
                                                    <option value="United States" country-code="US" country-name="United States" dial-code="+1" data-icon="glyphicon flag-us">United States</option>
                                                </select>

                                            </td>
                                        </tr>
                                        <tr class="invoice-billing-address">
                                            <td class="usCardstInf hide" valign="top">
                                                <input class="w270 cardInpt invoice-billAddress usStAddress fCaps" type="text" label="Street Address" placeholder="Street Address" name="streetAddress" mandatory="mandatory">
                                            </td>
                                            <td class="notUsCardstInf">
                                                <textarea maxlength="500" class="w270 h69 invoice-billAddress cardInpt stAddress p4" label="Street address" placeholder="Street Address" name="streetAddress" mandatory="mandatory"></textarea>
                                            </td>
                                            <td class="usCardstInf hide" style="padding-left:36px">
                                                <input class="w300 cardInpt ccSuite fr" type="text" label="Apt./Suite" placeholder="Apt./Suite" name="suite">
                                            </td>
                                            <td class="notUsCard" valign="top">
                                                <input maxlength="50" type="text" id="city" name="city" class="cardInpt city invoice-city w300 fr" mandatory="mandatory" label="City" placeholder="City">
                                                <div class="fr mT13">
                                                    <input maxlength="50" type="text" name="state" class="cardInpt stateInp invoice-stateInp w175 fl mR5" mandatory="mandatory" label="State" placeholder="State">
                                                    <select id="indStates" placeholder="State" class="selectBoxIt invoice-stateSel cardInpt w175 stateSel fl indStates cardIndStates hide" name="cardState" mandatory="mandatory" label="State" titleattr="State" data-size="10" >
                                                        <option value="SelectState">Select State</option>
                                                        <option value="andhra pradesh">Andhra Pradesh</option>
                                                    </select>

                                                    <input type="text" name="zipCode" maxlength="50" class="cardInpt invoice-zCode zCode w100 mL20" mandatory="mandatory" placeholder="ZIP/Postal label=" zip="" postal="" code="" >
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="usCardTd hide invoice-billing-address ">
                                            <td></td>
                                            <td>
                                                <input maxlength="50 " type="text " id="city " name="city " class="cardInpt invoice-city usCity mB20 w270 " mandatory="mandatory " label="City " placeholder="City ">
                                            </td>
                                            <td valign="top ">
                                                <div class="fr">
                                                    <select maxlength="50 " class="cardInpt h28 w175 stateSel invoice-stateSel selectBoxIt hide p0 h34 usStates " name="card_state " mandatory="mandatory " label="State " id="usStates " data-size="10 ">
                                                        <option value="none ">Select State</option>
                                                        <option value="Alabama ">Alabama</option>
                                                    </select>
                                                    <input type="text " name="zipCode " maxlength="50 " class="cardInpt invoice-zCode usZCode w100 mL20 " mandatory="mandatory " placeholder="ZIP/Postal " label="Zip/Postal code ">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr id="billingAddress ">
                                            <td align="right " width="180 ">Company/Individual name</td>
                                            <td class="tal "><input type="text " maxlength="100 " name="invoice_billingName " class="cardInpt companyname w270 " mandatory="mandatory " label="Company/Individual name " id="companyname "></td>
                                            <td align="right " width="320 "><span class="mR10 ">Phone</span>
                                                <input type="text " name="phone " maxlength="50 " class="cardInpt phoneNo w200 " mandatory="mandatory " label="Phone no. " id="phoneNo ">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="billing-address-split mB10 "></div>
                            <div id="paypal-billAddrTable " class="hide paypal_billAddrTable ">
                                <table border="0 " cellspacing="0 " cellpadding="0 " width="840 " class="ccardTbl m0auto pT10 " id="invoice-address-option ">
                                    <tbody>
                                    <tr>
                                        <td align="right " width="180 ">
                                            <div class="billing-address-sub rel ">
                                                <span class="addressHeadLabel fCaps ">Billing address</span>
                                                <div class="aboutIcn ib vam mL5 about-billing-address ">
                                                    <div class="billing-details-pop tal ">
                                                        <span class="invoice-image "></span>
                                                        <p class="invoicemsg ">By default, we’ll mention your <span class="used-card ">credit card’s billing</span> address as your <span class="fW600 ">Bill To</span> address in your invoices. To change this, choose <span class="fW600 ">Enter a new address</span> option.</p><p class="mT10 "><span class="fW600 ">Note:</span> Henceforth, this address will be used for all your invoices. Of course, you can change this later in <span class="fW600 ">Billing Details</span> section.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td valign="top " colspan="2 ">
                                            <input type="radio " name="billingAddress " id="paypal-address " class="mLZero " onchange="changeInvoiceAddressOption() ">
                                            <label type="radio " for="paypal-address " class="mR10 card-address-label ">Use my PayPal account shipping address</label>
                                            <input type="radio " name="billingAddress " id="new-paypal-address " class="mLZero nsYear " onchange="changeInvoiceAddressOption() ">
                                            <label type="radio " for="new-paypal-address " class="mR10 card-address-label1 ">Enter a new address</label>
                                        </td>
                                    </tr>
                                    <tr class="hide invoice-billing-address ">
                                        <td align="right " rowspan="2 " valign="top " width="180 ">Address</td>
                                        <td class="countryTd " valign="top " colspan="2 ">
                                            <select class="selectBoxIt cardInpt p2 selCountry w270 selectCountry invoice-billing-country " label="Country " name="country " mandatory="mandatory " maxlength="50 " data-size="10 " style="display: none; ">
                                                <option value="United States " country-code="US " country-name="United States " dial-code="+1 " data-icon="glyphicon flag-us ">United States</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr class="hide invoice-billing-address ">
                                        <td class="usCardstInf hide " valign="top ">
                                            <input class="w270 cardInpt invoice-billAddress usStAddress " type="text " label="Street Address " placeholder="Street Address " name="streetAddress " mandatory="mandatory ">
                                        </td>
                                        <td class="notUsCardstInf ">
                                            <textarea maxlength="500 " class="w270 h69 invoice-billAddress cardInpt stAddress p4 " label="Street address " placeholder="Street Address " name="streetAddress " mandatory="mandatory "></textarea>
                                        </td>
                                        <td class="usCardstInf hide " style="padding-left:36px ">
                                            <input class="w300 cardInpt ccSuite fr " type="text " label="Apt./Suite " placeholder="Apt./Suite " name="suite ">
                                        </td>
                                        <td class="notUsCard " valign="top ">
                                            <input maxlength="50 " type="text" id="city " name="city " class="cardInpt invoice-city city w300 mB20 fr " mandatory="mandatory " label="City " placeholder="City ">
                                            <div class="fr mT13 ">
                                                <select id="indStates " placeholder="State " class="selectBoxIt invoice-stateSel paypalSelect cardInpt w175 stateSel fl indStates hide cardIndStates " name="cardState " mandatory="mandatory " label="State " titleattr="State " data-size="10 " style="display: none; ">
                                                    <option value="SelectState ">Select State</option>
                                                    <option value="andhra pradesh ">Andhra Pradesh</option>
                                                </select>
                                                <input maxlength="50 " type="text" name="state " class="cardInpt invoice-stateInp stateInp w175 fl mR5 " mandatory="mandatory " label="State " placeholder="State ">
                                                <input type="text" name="zipCode " maxlength="50 " class="cardInpt invoice-zCode zCode w100 mL20 " mandatory="mandatory " placeholder="ZIP/Postal " label="Zip/Postal code ">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="usCardTd hide invoice-billing-address ">
                                        <td></td>
                                        <td>
                                            <input maxlength="50 " type="text " id="city " name="city " class="cardInpt invoice-city usCity mB20 w270 " mandatory="mandatory " label="City " placeholder="City ">
                                        </td>
                                        <td valign="top ">
                                            <div class="fr ">
                                                <select maxlength="50 " class="cardInpt h28 w175 stateSel invoice-stateSel paypalSelect selectBoxIt hide p0 h34 usStates " name="card_state " mandatory="mandatory " label="State " id="usStates " data-size="10 " style="display: none; ">
                                                    <option value="none ">Select State</option>
                                                    <option value="Alabama ">Alabama</option>
                                                </select>
                                                <input type="text " name="zipCode " maxlength="50 " class="cardInpt invoice-zCode usZCode w100 mL20 " mandatory="mandatory " placeholder="ZIP/Postal " label="Zip/Postal code ">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="billingAddress ">
                                        <td align="right " width="180 ">Company/Individual name</td>
                                        <td class="tal ">
                                            <input type="text " maxlength="100 " name="invoice_billingName " class="cardInpt companyname w270 " mandatory="mandatory " label="Company/Individual name " id="companyname ">
                                        </td>
                                        <td align="right " width="320 " class="pR3 ">
                                            <span class="pR7 ">Phone</span>
                                            <input type="text " name="phone " maxlength="50 " class="cardInpt phoneNo w244 " mandatory="mandatory " label="Phone no. " id="phoneNo">
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="billing-address-split mT15 "></div>
                            </div>
                            <div id="payer-authentication-container " class="hide ">
                                <input type="checkbox " class="cardInpt " mandatory="mandatory " name="3dsecure " id="3dsecure ">
                                <label class="mB20 c66 " for="3dsecure " type="checkbox ">
                                    I agree to use the above card for automatic renewals and future transactions without
                                    <div class="payer-authentication ">
                                        3-D Secure authentication
                                        <div class="payer-authentication-description hide ">
                                            <div class="fW600 ">What is 3-D Secure authentication?</div>
                                            <div>3-D Secure authentication is an additional security layer for online transactions. It operates in a similar way to 'chip &amp; PIN' by asking the cardholder to enter the card's PIN / password to authenticate the identity of the cardholder at the time of purchase.</div>
                                        </div>
                                    </div>
                                    <div class="aboutIcn ib about-payer-authentication hide ">
                                        <div class="about-payer-authentication-description ">
                                            <p>When you use a credit card for the very first payment with Zoho, you have to go through the 3-D Secure authentication step to verify your card and complete the payment.</p>
                                            <p class="m0 ">Once your card has been verified, you won't be asked to go through the 3-D Secure authentication step for future transactions and renewals.</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <table class="m0auto ccardTbl pB10 " border="0 " cellpadding="0 " cellspacing="0 " width="840 " id="contBtnTable ">
                                <tbody>
                                <tr>
                                    <td width="300 " class="invoiceNote hide ">
                                        <p class="note-l-brd c7E ">
                                            <span class="f-bold c444 ">Note: </span>Invoices will be sent to the already saved billing address.
                                        </p>
                                    </td>
                                    <td class="tal ">
                                        <input value="Make Payment " id="makepayment" class="subscriptionGBtn makePayment " style="line-height:40px " mandatory="mandatory " type="button" selectedcard="newcard">
                                        <div class="fr mR30 safeSecure ">
                                            <i class="pciLogo ib "></i><span class="safeSecureMsg ">Safe and Secured<br>Payment Gateway </span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <table cellspacing="5 " cellpadding="0 " border="0 " width="100% " class="storeSplit ccardTbl f12 " id="aboutSubMsg ">
                                <tbody>
                                <tr>
                                    <td valign="top mt15 "><img src="{{ asset("assets/images/logo_icon.png") }}"/></td>
                                    <td class="c7E ">
                                        <div class="c444 mT12 ">About your subscription</div>
                                        <div class="abtSubscriptionMsg ">All subscriptions will be automatically renewed using your preferred payment method and we'll send you an invoice each time. You can upgrade or downgrade the volume of users in your account anytime.
                                            If your subscription is canceled, refunds will be subjected to our <a target="_blank " href="https://www.sanityos.com/terms.pdf ">Terms and conditions.</a>. All prices include taxes.</div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
            <br/>
            <table cellspacing="5" cellpadding="0" border="0" width="100%" class="" id="aboutSubMsg ">
                <tbody>
                    <tr>
                        <td style="text-align: right"><a href="{{ route("user.profile") }}" target="_parent">Cancel and Return to Account</a></td>
                    </tr>
                </tbody>
            </table>
            <br/>
        </div>
    </div>
</div>
{!! Html::script('assets/js/jquery-1.11.1.min.js') !!}
<script src="{{ asset('payment/files/zohostorecommon.min.js') }}"></script>
<script src="{{ asset('payment/files/properties.js') }}"></script>
<script src="{{ asset('assets/js/jquery.typewatch.js') }}"></script>
<script src="{{ asset('assets/js/lodash.js') }}"></script>

<script>
    StoreProperties.pricing = {!! $pricing !!};
    StoreProperties.discount = {!! $licenceTypeDetail->discount !!};
    StoreProperties.teamUser = {!! $teamUser !!};

    @if(!isset($user->existing))
        StoreProperties.pricingPeriod = '{!! $user->payment_info->recurring_type or 'Monthly' !!}';
    @else
        @if($user->existing == 'No')
        StoreProperties.pricingPeriod = '{!! $user->payment_info->recurring_type or 'Monthly' !!}';
        @else
            StoreProperties.pricingPeriod = '{!! $user->payment_info->recurring_type or 'Monthly' !!}';
        @endif
    @endif
    StoreProperties.purchaseType = '{!! $purchaseType !!}';

    StoreProperties.addressDetails = {!! json_encode($addressDetails) !!};
    StoreProperties.billingAddress = {!! json_encode($billingAddress) !!};
    StoreProperties.paymentMethod = 'card';
    var paymentUrl = '{{ URL::route('user.payment.info') }}';
    var cvvImagePath = '{{ asset('payment/files/cvv-large.png') }}';
    var assetsPath = "{{ URL::asset("assets") }}/images/";

    $('input[value="'+StoreProperties.pricingPeriod+'"]').prop('checked', true);
</script>
{!! Html::script('assets/js/page/licence.js') !!}
{!! HTML::script('/assets/js/jquery.blockui.min.js') !!}

<script>
    setCurrency('{{ $currency }}');
</script>

<script src="{{ asset('payment/files/payment.js') }}"></script>
<script>
    //Section One Variables
    var planTitleOne = $(".chPlanHead"); // 1 Title
    var currencySelector = $(".chPlanHead + .currencycontainer"); // 1 Currency Selector
    var planTitleSelectOne = $(".planTitleDiv .hdDet"); // 1 Title after Selection
    var plansBox = $(".planTitleDiv + .subDetDiv"); // Pricing Plans
    var upgradeBtn = $(".upgradePlan"); // Pricing Plans Btn
    var editBtnOne = $(".planTitleDiv .planEditBtn"); // Edit Plan Btn

    //Section Two Variables
    var continueBtn = $(".plOrder"); // Continue Btn
    var planDetail = $(".planDetailHd + .subDetDiv"); // Plans Details
    var planTitleTwo = $(".planDetailHd .hwinAc"); // Title Before selected
    var planTitleSelectTwo = $(".planDetailHd .hdDet"); // Title Before selected
    var editBtnTwo = $(".planDetailHd .planEditBtn"); // Edit Plan Btn

    //Section Three Variables
    var confirmBtn = $(".confirmOrder"); // Confirm Btn
    var confirmDetail = $(".confirmOrderTitle + .subDetDiv"); // Confirm Details
    var planTitleThree = $(".confirmOrderTitle .hwinAc"); // Title Before selected
    var planTitleSelectThree = $(".confirmOrderTitle .hdDet"); // Title Before selected

    //Section Three Variables
    var paymentDetails = $(".paymentDetails + .subDetDiv"); // Confirm Details

    $(document).ready(function(){
        setPayMethod();
		@if($purchaseType != 'resubscribe')
        $("#volume_users").spinner({
            min : 1,
            stop : function() {
                calculateTotalAmount();
            }
        });
        @endif
        //Section One
        upgradeBtn.click(function(){
            planTitleOne.hide();
            currencySelector.hide();
            planTitleSelectOne.show();
            planDetail.fadeIn('slow');
            editBtnOne.show();
            plansBox.hide();
        });

        editBtnOne.click(function(){
            planTitleOne.show();
            currencySelector.show();
            planTitleSelectOne.hide();
            planDetail.hide();
            editBtnOne.hide();
            $(".subDetDiv").hide();
            plansBox.fadeIn('slow');
            planTitleThree.show();
            planTitleSelectThree.hide();
            planTitleSelectTwo.hide();
            planTitleTwo.show();
        });

        //Section Two
        continueBtn.click(function() {
            //TODO:Add validation
            if(StoreProperties.purchaseType == 'teamUpdate') {
                if(!validateTeamMemberUpdateStep1()) {
                    return;
                }
            }
            planTitleTwo.hide();
            planTitleSelectTwo.show();
            confirmDetail.fadeIn('slow');
            editBtnOne.show();
            editBtnTwo.show();
            planDetail.hide();
            $(".confirmOrderTitle").removeClass("peNone");
        });

        editBtnTwo.click(function(){
            planTitleTwo.show();
            planTitleSelectTwo.hide();
            confirmDetail.hide();
            editBtnTwo.hide();
            $(".subDetDiv").hide();
            planDetail.fadeIn('slow');
            planTitleSelectThree.hide();
            planTitleThree.show();
            planTitleSelectOne.show();
            planTitleOne.hide();
        });

        //Section Three
        confirmBtn.click(function() {
            planTitleThree.hide();
            planTitleSelectThree.show();
            confirmDetail.hide();
            paymentDetails.fadeIn('slow', function() {
//                if($('#ccNo').val() != '') { setCardType(); }
                setAddress();
            });
        });
    });
</script>
<script type="text/javascript">
    function getActionMode() {
        mode = null; method = null; invalid_params = false; invalid_hash = false; queryStr = null; serviceId = null; customId = null; planId = null; bugtracker = null;projectslite=null, view=null, ccu=null, successUrl=null, failureUrl=null, id=-1, token=-1, subscriptionId=-1;
        var href = new String(location.href);
        if(href.indexOf('?') != -1 && href.indexOf('#') != -1) {
            var invalid_params = false,invalid_hash = false;
            queryStr = href.split("?")[1];
            var hash = (href.substring(href.indexOf('#'),href.indexOf('?'))).replace('#','');

            if(hash == "history") {
                mode = "history"; method = "history";
            } else if(hash == "mystore") {
                mode = "mysubscription";
            } else if(hash == "myaccount") {
                mode = "myaccount";
            } else if(hash == "subscription") {
                mode = "subscription";
            } else if(hash == "crmpluscreate") {
                mode = "crmpluscreate";
            } else if(hash == "payment" || hash.indexOf("payment_") !== -1) {
                mode = "payment"; method = "payment";
            } else {
                //Error
                mode = hash;
            }

            invalid_hash = actionPage[mode]  ? false : mode == "subscription" ? false : true;

            serviceId = queryStr.indexOf('serviceId=') != -1 ? queryStr.split('serviceId=')[1].split('&')[0] : invalid_params=true;
            if(mode === "payment" && hash.indexOf("payment_") !== -1) { //Getting serviceId for UI version1 support
                serviceId = hash.split('_')[1];
            }
            customId = queryStr.indexOf('customId=') != -1 ? queryStr.split('customId=')[1].split('&')[0] : invalid_params=true;
            planId = queryStr.indexOf('planId=') != -1 ? queryStr.split('planId=')[1].split('&')[0] : "";
            successUrl = queryStr.indexOf('successUrl=') != -1 ? queryStr.split('successUrl=')[1].split('&')[0] : "";
            failureUrl = queryStr.indexOf('failureUrl=') != -1 ? queryStr.split('failureUrl=')[1].split('&')[0] : "";
            view = queryStr.indexOf('view=') != -1 ? queryStr.split('view=')[1].split('&')[0] : null;
            bugtracker = queryStr.indexOf('bugtracker=') != -1 ? queryStr.split('bugtracker=')[1].split('&')[0] : "false";
            subscriptionId = queryStr.indexOf('subscriptionId=') != -1 ? queryStr.split('subscriptionId=')[1].split('&')[0] : -1;
            projectslite=queryStr.indexOf('projectslite=') != -1 ? queryStr.split('projectslite=')[1].split('&')[0] : "false";
            id = queryStr.indexOf('id=') != -1 ? queryStr.split('id=')[1].split('&')[0] : -1;
            token = queryStr.indexOf('token=') != -1 ? queryStr.split('token=')[1].split('&')[0] : -1;
            ccu = queryStr.indexOf('ccu=') != -1 ? queryStr.split('ccu=')[1].split('&')[0] : "false";
            if((invalid_params && mode !== "payment") || invalid_hash) {
                mode = null;
                location.href = actionPage.error;
            }
        } else {
            location.href = actionPage.error;
        }
    }

    function addQueryParams(obj) {
        var queryStr = null; serviceId = null; customId = null; bugtracker = null;projectslite=null, view=null, id=-1, token=-1, subscriptionId=-1;
        var href = new String(location.href);
        if(href.indexOf('?') != -1 && href.indexOf('#') != -1) {
            queryStr = href.split("?")[1];
            obj.serviceId = queryStr.indexOf('serviceId=') != -1 ? queryStr.split('serviceId=')[1].split('&')[0] : invalid_params=true;
            obj.view = queryStr.indexOf('view=') != -1 ? queryStr.split('view=')[1].split('&')[0] : null;
            obj.bugtracker = queryStr.indexOf('bugtracker=') != -1 ? queryStr.split('bugtracker=')[1].split('&')[0] : "false";
            obj.projectslite=queryStr.indexOf('projectslite=') != -1 ? queryStr.split('projectslite=')[1].split('&')[0] : "false";
            obj.id = queryStr.indexOf('id=') != -1 ? queryStr.split('id=')[1].split('&')[0] : -1;
            obj.token = queryStr.indexOf('token=') != -1 ? queryStr.split('token=')[1].split('&')[0] : -1;
            obj.subscriptionId = queryStr.indexOf('subscriptionId=') != -1 ? queryStr.split('subscriptionId=')[1].split('&')[0] : -1;
        }
    }

    function getURLHash() {
        var href = new String(location.href), hash;
        if(href.indexOf('?') != -1 && href.indexOf('#') != -1) {
            hash = (href.substring(href.indexOf('#'),href.indexOf('?'))).replace('#','');
        }
        return hash;
    }

    function getRequestData() {
        var dataObj = new Object();
        if(method === "payment") {
            dataObj.method = method;
            dataObj.id = id;
            dataObj.token = token;
        } else {
            dataObj.serviceId  = serviceId;
            dataObj.customId  = customId;
            if(method == null) {
                dataObj.view = view;
                dataObj.bugtracker = bugtracker;
                dataObj.projectslite = projectslite;
                dataObj.planId  = planId;
                dataObj.successUrl  = successUrl;
                dataObj.failureUrl  = failureUrl;
                dataObj.mode = mode;
                dataObj.subscriptionId = subscriptionId;
            } else if(method == "history") {
                dataObj.method = method;
            }
        }
        return dataObj;
    }

    alignPopcontainer = function(e){
        var docHeight = $(document).height(),
                docWidth = $(document).width(),
                elementHeight = e.height(),
                elementWidth = e.width(),
                top = (docHeight - elementHeight) / 2,
                left = (docWidth - elementWidth) / 2;
        e.css({'top':top,'left':left});//NO I18N
    }

    function action() {
        getActionMode();
        if(serviceId === "5001" || serviceId === "3") { //Sites and Site24x7 - UI version1 support
            ui_version = "v1";
        }
        //CSS loading
        loadStyleSheet(getConfig(index_stylesheets,serviceId,ui_version));
        //JS loading
        loadScriptAsynchronous(getConfig(index_scripts,serviceId,ui_version));

        var alignLoading = $('.loadingMsg');
        alignPopcontainer(alignLoading);
        $(".freezeDiv,.loadingMsg,.loadingBG").show();
        $.ajax({
            url  : "/store/service.do",
            type : "POST",
            data : getRequestData(),
            success : function(result) {
                if(result.messageObj) {
                    $(".freezeDiv,.loadingMsg,.loadingBG").hide();
                    $('.loadingMsg').removeAttr('style');
                    sessionStorage.setItem('ERROR_PRIMARYMSG', result.messageObj.primaryMessage);
                    sessionStorage.setItem('ERROR_SECONDARYMSG', result.messageObj.secondaryMessage);
                    sessionStorage.setItem('ERROR_TYPE', result.messageObj.type);
                    location.href = actionPage.error;
                } else {

                    StoreProperties = result;
                    StoreProperties.customId = customId;
                    StoreProperties.pageaction = mode;
                    if(serviceId === "5001" || serviceId === "3") { //Sites and Site24x7 - UI version1 support
                        StoreProperties.ui_version = "v1";
                        if(StoreProperties.countryJSON) {
                            var countrySel = {
                                "countryName": "Select Country",
                                "countryCode": ""
                            }
                            StoreProperties.countryJSON.unshift(countrySel);
                        }
                    }
                    //i18n values assigned to handlebar-helper.js i18n variable
                    i18n = JSON.parse(StoreProperties.i18n);

                    confJSON.iam_url = StoreProperties.iam_url;
                    confJSON.iam_username = StoreProperties.iam_username;

                    confJSON.iam_email = StoreProperties.iam_email;
                    confJSON.iam_photo = StoreProperties.iam_photourl;
                    confJSON.iam_zuid = StoreProperties.iam_zuid;
                    confJSON.search = queryStr;
                    confJSON.locale = StoreProperties.locale;
                    confJSON.generatetokenurl = StoreProperties.generatetoken_url;
                    //For direct card link in retry mail hide settings icon
                    if(ccu !== null && ccu === "true" && mode === "myaccount") {
                        confJSON.settings = "silent";
                    }
                    loadTemplate();
                    loadPage();
                    $("#storeprop").val(JSON.stringify(StoreProperties));
                    var url = new String(location.href);
                    var queryParams = url.split("?")[1];
                    if(StoreProperties.pageaction === "newpurchase" && queryParams.indexOf('subscriptionId=') !== -1) {
                        //Do nothing
                    } else {
                        $(".freezeDiv,.loadingMsg,.loadingBG").hide();
                        $('.loadingMsg').removeAttr('style');
                    }
                    initializeTopBar(confJSON);
                    initializeWMS(StoreProperties.iam_zuid);
                }

            }, error : function(result) {

                $(".freezeDiv,.loadingMsg,.loadingBG").hide();
                $('.loadingMsg').removeAttr('style');
                if(result.messageObj) {
                    sessionStorage.setItem('ERROR_PRIMARYMSG', result.messageObj.primaryMessage);
                    sessionStorage.setItem('ERROR_SECONDARYMSG', result.messageObj.secondaryMessage);
                }
                sessionStorage.setItem('ERROR_TYPE', (result.messageObj) ? result.messageObj.type : "ServerError");
                location.href = actionPage.error;
            },
            dataType : "JSON"
        });
    }
    function initializeWMS(zuid) {
        $.getScript( 'https://js.zohostatic.com/ichat/v108_https/js/wmsliteapi.js' ).done(function( script, textStatus ) {
            WmsLite.setNoDomainChange();
            WmsLite.registerZuid("ZP", zuid,"", true);
        });
    }

    function loadTemplate() {
        StoreProperties.renderingJSON = (StoreProperties.renderingJSON) ? StoreProperties.renderingJSON : {};
        StoreProperties.renderingJSON.domain = StoreProperties.product_domain;
        var footerContent = Handlebars.templates['index_footer_content-template'](StoreProperties.renderingJSON);
        $('#footer_content').html(footerContent);
    }

    function inPaidButFree(StoreProperties) {
        var paidButFree = false;
        var selPlan = StoreProperties.renderingJSON.planTypes[0].selectedPlan;
        var renderingKey = StoreProperties.renderingJSON.renderingKey;
        if(StoreProperties.serviceId == 1 && (selPlan == 1 || (selPlan == 7 && projectslite == 'false') || selPlan == 9501 || selPlan == 9502 || selPlan == 9701 || selPlan === 2 || selPlan === 3 || selPlan === 4)) { //Projects
            paidButFree = true;
        } else if (StoreProperties.serviceId == 4801 && selPlan == 13104) {
            paidButFree = true;
        } else if (StoreProperties.serviceId == 7000 && selPlan == 7002) {
            paidButFree = true;
        } else if (StoreProperties.serviceId == 2201 && selPlan == 2202) {
            paidButFree = true;
        } else if (StoreProperties.serviceId == 2201 && renderingKey === "new" && (selPlan == 22104 || selPlan == 2203 || selPlan == 2204)) {
            paidButFree = true;
        } else if (StoreProperties.serviceId === 4501 && renderingKey !== "express" && (selPlan === 10204 || selPlan === 10205 || selPlan === 10206)) {
            paidButFree = true;
        } else if (StoreProperties.serviceId === 3601 && (selPlan === 7801 || (renderingKey !== "standard1" && renderingKey !== "standard2" && (selPlan === 7501 || selPlan === 11301)))) {
            paidButFree = true;
        } else if(StoreProperties.serviceId == 2 && renderingKey === "new" && (selPlan !== 14804 && selPlan !== 14805 && selPlan !== 14806)) {
            paidButFree = true;
        } else if (StoreProperties.serviceId == 1201 && selPlan == 3605 && renderingKey === "new") {
            paidButFree = true;
        } else if (StoreProperties.serviceId == 601 && (selPlan == 603 || selPlan == 604 || selPlan == 605)) {
            paidButFree = true;
        }
        return paidButFree;
    }

    function loadPage() {
        if(mode == "subscription") {
            if(StoreProperties.renderingJSON.planTypes[0].selectedPlan != null && StoreProperties.renderingJSON.planTypes[0].selectedPlan != 0) {
                if(StoreProperties.fromProductPage && StoreProperties.crmPlusCustomer) {
                    location.href = "#subscription?serviceId=180000&customId="+StoreProperties.crmPlusCustomId;
                } else {
                    //Specific handling for Projects & Bug-tracker
                    if(inPaidButFree(StoreProperties)) {
                        StoreProperties.subscription = false;
                        StoreProperties.landingpagekey = "newpurchase"; //NO I18N
                        $("#bp").load(actionPage.newpurchase);
                    } else {
                        StoreProperties.subscription = true;
                        StoreProperties.landingpagekey = "manageplan"; //NO I18N
                        $("#bp").load(actionPage.manageplan);
                    }
                }
            } else {
                if(StoreProperties.fromProductPage && StoreProperties.crmPlusCustomer) {
                    location.href = "#subscription?serviceId=180000&customId="+StoreProperties.crmPlusCustomId;
                } else if(parseInt(StoreProperties.serviceId) === 180000 && Object.keys(StoreProperties.serviceOrgList).length === 0) {
                    StoreProperties.subscription = false;
                    StoreProperties.landingpagekey = "crmpluscreate"; //NO I18N
                    $("#bp").load(actionPage.crmpluscreate);
                } else {
                    StoreProperties.subscription = false;
                    StoreProperties.landingpagekey = "newpurchase";//NO I18N
                    $("#bp").load(actionPage.newpurchase);
                }
            }
        } else if(mode == "crmpluscreate") {
            StoreProperties.landingpagekey = "crmpluscreate";//NO I18N
            $("#bp").load(actionPage.crmpluscreate);
        } else {
            StoreProperties.landingpagekey = mode;//NO I18N
            $("#bp").load(actionPage[mode]);
        }
    }

    function getConfig(json, id, version) {
        var returnList = [];
        var configJSON = json.config["default"];
        if(json.config["service_"+id]) {
            configJSON = json.config["service_"+id];
        } else if(version && json.config[version]) {
            configJSON = json.config[version];
        } else if(StoreProperties && StoreProperties.ui_version && json.config[StoreProperties.ui_version]) {
            configJSON = json.config[StoreProperties.ui_version];
        }
        configJSON = (StoreProperties && StoreProperties.development) ? configJSON.unminified : configJSON.minified;
        $.each(configJSON, function(index, val) {
            returnList.push(json.list[val]);
        });
        return returnList;
    }

    function getURL(json) {
        var url = "";
        if(json.path) {
            var pathEndsWith = json.path.substring(json.path.length-1,json.path.length);
            url += (pathEndsWith === "/") ? json.path : json.path + '/';
        } if(json.source) {
            var sourceEndsWith = json.source.substring(json.source.length-1,json.source.length);
            url += (sourceEndsWith === "/") ? json.source : json.source + '/';
        }
        url += json.name;
        return url;
    }

    function loadScriptAsynchronous (scripts) {
        $.each(scripts, function( index, value ) {
            var jsURL = getURL(value);
            var s = document.createElement("script");
            s.type = "text/javascript";
            s.src = jsURL;
            $("head").append(s);
        });
    }

    function loadScript (scriptURL, callback) {
        $.getScript( scriptURL ).done(function( script, textStatus ) {
            if(typeof(callback) == "function") { callback(); }
        }).fail(function( jqxhr, settings, exception ) {
            //console.log( scriptURL +" : "+exception );
            if(typeof(callback) == "function") { callback(); }
        });
    }

    function loadMultipleScript (scripts, callback) {
        var process = 0;
        internalCallback();
        function internalCallback() {
            process += 1;
            if(scripts.length >= process) {
                var scriptURL = getURL(scripts[process - 1]);
                loadScript (scriptURL, internalCallback);
            } else {
                if(typeof(callback) == "function") { callback(); }
            }
        }
    }

    function loadStyleSheet (stylesheets) {
        $.each(stylesheets, function( index, value ) {
            var cssURL = getURL(value);
            $('<link rel="stylesheet" type="text/css" href="'+cssURL+'" >').prependTo("head");
        });
    }

    function changeViewParamValue(href, newVal) {
        var newUrl;
        if(newVal) {
            if(href.indexOf("view=") !== -1) {
                var reExp = /view=[A-Za-z0-9_]*/;
                newUrl = href.replace(reExp, "view=" + newVal);
            } else {
                newUrl = href+"&view="+newVal;
            }
        } else {
            var reExp = /&view=[A-Za-z0-9_]*/;
            newUrl = href.replace(reExp, "");
        }
        return newUrl;
    }

    function changeUrlParamValue(href, paramName, newVal) {
        var newUrl;
        if(newVal) {
            if(href.indexOf(paramName+"=") !== -1) {
                var reExp = new RegExp("("+ paramName + "=)[^&]+");
                newUrl = href.replace(reExp, paramName+"=" + newVal);
            } else {
                newUrl = href+"&"+paramName+"="+newVal;
            }
        } else {
            var reExp = new RegExp("(&"+ paramName + "=)[^&]+");
            newUrl = href.replace(reExp, "");
        }
        return newUrl;
    }

    function getUrlParamValue(href, paramName) {
        var paramValue = "";
        if(href.indexOf(paramName+"=") !== -1) {
            var reg = new RegExp( '[?&]' + paramName + '=([^&#]*)', 'i' );
            var string = reg.exec(href);
            paramValue = string ? string[1] : "";
        }
        return paramValue;
    }

    function updateToolFreeNumber(){
        if(StoreProperties.renderingJSON.multipleAddonsPurchase !== undefined && StoreProperties.renderingJSON.multipleAddonsPurchase){
            var tooFreeNumber = (StoreProperties.contactJSON[StoreProperties.country] !== undefined && StoreProperties.contactJSON[StoreProperties.country]) ? StoreProperties.contactJSON[StoreProperties.country] : StoreProperties.contactJSON["US"];
            $(".salesToolFree").html(tooFreeNumber);
        }
    }
    $(window).on( 'hashchange', function(event) { location.reload(); });

    /*-------------------- zoho store topbar custom menu starts ---------------------*/
    $(document).on("click", ".custom-menu-label", function(event) { //#ztb-container
        $(this).toggleClass("custom-active-menu");
        _customenuClass = $("#ztb-container").find(".custom-menu").attr("class").split(" ")[1];
        if ( _customenuClass == "collapse-custom-menu" ) {
            $("#ztb-container").css("z-index", "99999999").find(".custom-menu[class *= collapse]").removeClass("collapse-custom-menu").delay(10).addClass("expand-custom-menu");
        } else {
            $("#ztb-container").removeAttr("style").find(".custom-menu[class *= expand]").removeClass("expand-custom-menu").delay(10).addClass("collapse-custom-menu");
        }
        event.stopImmediatePropagation();
    });
    $(document).add("#ztb-container").on({
        "keydown": function (event) {//NO I18N
            if (event.which == 27 && event.keyCode == 27) {
                $("#ztb-container").removeAttr("style").find(".custom-menu[class *= expand]").removeClass("expand-custom-menu").delay(10).addClass("collapse-custom-menu").end()
                        .find(".custom-menu-label").removeClass("custom-active-menu");
            }
        },
        "click": function (event) {//NO I18N
            $("#ztb-container").removeAttr("style").find(".custom-menu[class *= expand]").removeClass("expand-custom-menu").delay(10).addClass("collapse-custom-menu").end()
                    .find(".custom-menu-label").removeClass("custom-active-menu");
        }
    });
    /*---------------------- zoho store topbar custom menu ends ---------------------*/
    function changeInvoiceAddressOption() {
        var $billingAddress = $(".invoice-billing-address");
        if ($("#card-address,#paypal-address").is(":checked")) $billingAddress.slideUp();
        else {
            $billingAddress.slideDown();
            var billingCountry = $billingAddress.find("selectCountry").val();
            "United States" == billingCountry ? ($(".notUsCard,.notUsCardstInf").hide(), $(".usCardTd,.usCardstInf").show().find("div.stateSel").css("display", "inline-block")) : ($(".notUsCard,.notUsCardstInf").show(), $(".usCardTd,.usCardstInf").hide().find("div.stateSel").css("display", "inline-block"))
        }
    }
</script>
</body>
</html>
