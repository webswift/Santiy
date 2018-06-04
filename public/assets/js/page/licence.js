var formData = new FormData();

function setCurrency(currency, price) {
    if(currency == 'USD') {
        StoreProperties.currency = StoreProperties.US;
        StoreProperties.currencyId = StoreProperties.currencyJSON.currencycode_id.USD;
        StoreProperties.currencyCode = "USD";

        $('.currencyClass').html('$');
    }
    else if(currency == 'GBP') {
        StoreProperties.currency = StoreProperties.UK;
        StoreProperties.currencyId = StoreProperties.currencyJSON.currencycode_id.GBP;
        StoreProperties.currencyCode = "GBP";

        $('.currencyClass').html(StoreProperties.currencyJSON.currencySymbols.GBP);
    }
    else if(currency == 'EUR') {
        StoreProperties.currency = StoreProperties.EU;
        StoreProperties.currencyId = StoreProperties.currencyJSON.currencycode_id.EUR;
        StoreProperties.currencyCode = "EUR";

        $('.currencyClass').html(StoreProperties.currencyJSON.currencySymbols.EUR);
    }

    $('.perUserPrice').html(StoreProperties.pricing[StoreProperties.currencyCode].Monthly);
    activeCurrency = StoreProperties.currencyCode;

    calculateTotalAmount();
}

function calculateTotalAmount() {
    hideNotification();
    if(StoreProperties.purchaseType == 'teamUpdate') {
        $('.totalPrice,.totalToPay,.planPrice').html('');
        $('.totalToPayBand').html('');

        $('.totalDiscountPercent').html('');
        $('.discountPrice').html('');
        $('.amountDesc').html('removed/added to');
    }

    var selectedUsers = $("#volume_users").val();
    if(selectedUsers !== '') {
        selectedUsers = parseInt(selectedUsers);
        if(selectedUsers == "NaN" || isNaN(selectedUsers)) {
            showNotification("Select valid number of users");
            return false;
        }

		if(StoreProperties.purchaseType != 'teamUpdate') {
			//other code expects volume_uses without admin
			selectedUsers = selectedUsers - 1;
		}
    }
    else {
        return false;
    }

    if(StoreProperties.action == 'add') {
        var price = selectedUsers * StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod];
        price = _.round(price, 2);
        $('.totalPrice,.totalToPay,.planPrice').html(price);
        $('.totalToPayBand').html(price);

        $('.discountRow').hide();
        $('.totalDiscountPercent').html('');
        $('.discountPrice').html('');
        $('.amountDesc').html('added to');
    }
    else if(StoreProperties.action == 'remove') {
        var remainingUser = StoreProperties.teamUser.total - selectedUsers;

        if(remainingUser < StoreProperties.teamUser.free) {
            showNotification("Remaining User can not be less than free users");
            $('.totalPrice,.totalToPay,.planPrice').html('');
            $('.totalToPayBand').html('');

            $('.totalDiscountPercent').html('');
            $('.discountPrice').html('');
            $('.amountDesc').html('removed/added to');
            return false;
        }
        else if(remainingUser < StoreProperties.teamUser.teamSize) {
            showNotification("First delete team user from team management then remove users from here.");
            $('.totalPrice,.totalToPay,.planPrice').html('');
            $('.totalToPayBand').html('');

            $('.totalDiscountPercent').html('');
            $('.discountPrice').html('');
            $('.amountDesc').html('removed/added to');
            return false;
        }
        else {
            var price = selectedUsers * StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod];
            price = _.round(price, 2);
            $('.totalPrice,.totalToPay,.planPrice').html(price);
            $('.totalToPayBand').html(price);

            $('.discountRow').hide();
            $('.totalDiscountPercent').html('');
            $('.discountPrice').html('');
            $('.amountDesc').html('removed from');
        }
    }
    else {
        if(StoreProperties.purchaseType == 'teamUpdate') { showNotification("Select an action"); return;}

        if(selectedUsers < StoreProperties.teamUser.teamSize) {
            //TODO: show warning for remove team user first and return
            showNotification("Remove team user first");
            return false;
        }

        if(selectedUsers <= StoreProperties.teamUser.free) {
            selectedUsers = StoreProperties.teamUser.free;
			//other code expects volume_uses without admin
            $("#volume_users").val(selectedUsers + 1);
            $("#volume_users").typeWatch({ highlight:true, wait:500, captureLength: -1, callback: calculateTotalAmount });
            //TODO: annual pricing
            //set monthly and annually price as base fare
            if(StoreProperties.pricingPeriod == 'Annually') {
                var totalPrice = _.round(((StoreProperties.pricing[StoreProperties.currencyCode].Monthly) * 12), 2);
                var priceToPay = _.round(StoreProperties.pricing[StoreProperties.currencyCode].Annually, 2);

                $('.totalPrice,.planPrice').html( totalPrice );
                $('.totalToPay').html(priceToPay);
                $('.totalToPayBand').html(priceToPay);

                $('.discountRow').show();
                $('.totalDiscountPercent').html('Yearly');

                var discountPrice = (totalPrice) - priceToPay;
                $('.discountPrice').html(_.round(discountPrice, 2));
            }
            else {
                $('.totalPrice,.totalToPay,.planPrice').html(StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod]);
                $('.totalToPayBand').html(StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod]);
                $('.discountRow').hide();
                $('.totalDiscountPercent').html('');
                $('.discountPrice').html(0);
            }
        }
        else if(selectedUsers > StoreProperties.teamUser.free) {
            // case 1 if selected users are less than paid users
            if(selectedUsers < StoreProperties.teamUser.paid) {

            }
            // case 2 if selected users are grater than paid users
            else if(selectedUsers < StoreProperties.teamUser.paid) {

            }

            var extraUsers = selectedUsers - StoreProperties.teamUser.free;
            if(StoreProperties.pricingPeriod == 'Annually') {
                var MonthlyBaseFare = _.round(StoreProperties.pricing[StoreProperties.currencyCode].Monthly, 2);
                var AnnuallyBaseFare = _.round(StoreProperties.pricing[StoreProperties.currencyCode].Annually, 2);

                var extraUsersPrice = (StoreProperties.pricing[StoreProperties.currencyCode].Monthly) * 12 * extraUsers;
                extraUsersPrice = _.round(extraUsersPrice, 2);
                totalPrice = _.round(extraUsersPrice + (MonthlyBaseFare * 12), 2);

                var extraUsersPriceToPay = StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod] * extraUsers;
                extraUsersPriceToPay = _.round(extraUsersPriceToPay, 2);
                priceToPay = _.round((extraUsersPriceToPay + AnnuallyBaseFare), 2);

                $('.totalPrice,.planPrice').html( totalPrice );
                $('.totalToPay').html(priceToPay);
                $('.totalToPayBand').html(priceToPay);

                $('.discountRow').show();
                $('.totalDiscountPercent').html('Yearly');

                var discountPrice = (totalPrice) - priceToPay;
                $('.discountPrice').html(_.round(discountPrice, 2));
            }
            else {
                var baseFare = _.round(StoreProperties.pricing[StoreProperties.currencyCode].Monthly, 2);
                var extraUsersPrice = _.round((baseFare * extraUsers), 2);
                var totalPrice = _.round(baseFare + extraUsersPrice);
                $('.totalPrice,.totalToPay,.planPrice').html(totalPrice);
                $('.totalToPayBand').html(totalPrice);

                $('.discountRow').hide();
                $('.totalDiscountPercent').html('');
                $('.discountPrice').html('');
            }
        }
    }

    $('.cPlanPrice').html(_.round(StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod] ,2));
    var confUnit = parseInt(selectedUsers) + 1;
    if(StoreProperties.action == 'add') {
		confUnit = parseInt(StoreProperties.teamUser.total) + confUnit;
    } else if(StoreProperties.action == 'remove') {
		confUnit = parseInt(StoreProperties.teamUser.total) + 2 - confUnit;
	}

    $('.confUnit').html(confUnit);
    $('input#selectedUsers').val(selectedUsers);

    setPlanPeriod();
}

function setPlanPeriod() {
    var planPeriod = StoreProperties.pricingPeriod;
    if(planPeriod == 'Annually') {
        planPeriod = 'Yearly';
    }
    $('.pricingPeriod').html(planPeriod);

    if(planPeriod == 'Monthly') {
        $('.payPeriod').html('month');
    }
    else {
        $('.payPeriod').html('year');
    }
}

function setCardType() {
    var nm = $('.ccardNo').val(),
        ctype = "Visa";
    if ($("#cvvCard").attr("maxlength", "3"), $("#cvv-fullview").html('<img src="'+cvvImagePath+'">'), $("#cvv-fullview + span").text("The 3 digit number printed on back of the card"), loadSupportedCards(), nm.length > 1) {
        $(".ccardNo").attr("maxlength", 16), nm.match("^4") ? (cardTypeClass = "visa", ctype = "Visa") : nm.match("^51") || nm.match("^52") || nm.match("^53") || nm.match("^54") || nm.match("^55") ? (cardTypeClass = "master", ctype = "MasterCard") : nm.match("^34") || nm.match("^37") ? (cardTypeClass = "amex", ctype = "AMEX") : nm.match("^36") || nm.match("^30") || nm.match("^38") ? (cardTypeClass = "dinners", ctype = "DinersClub") : nm.match("^6011") || nm.match("^6221") || nm.match("^ 644") || nm.match("^65") ? (cardTypeClass = "discover", ctype = "Discover") : nm.match("^35") && (cardTypeClass = "jcb", ctype = "JCB"), $("#cctype").val(ctype);
        var supportedCards = getSupportedCards();
        if (-1 === supportedCards.indexOf(ctype)) {
            var position = $(".ccardNo").position();
            return cardMsg(position.left, position.top + 1, getCardNotSupportedMsg(ctype), !0), !1
        }
        var cardImg = getCardImg(ctype);
        $(".cTypeTd ." + cardImg).removeClass("hide").addClass("inlineBlock"), $(".cTypeTd span:not(." + cardImg + ")").removeClass("inlineBlock").addClass("hide"), "AMEX" === ctype && ($("#cvvCard").attr("maxlength", "4"), $("#cvv-fullview").html('<img src="../../images/store/store-amex-cvv-large.png">'), $("#cvv-fullview + span").text("The 4 digit number printed on front of the card"))
    }
    $("#cctype").val(ctype);
}

function setAddress() {
    if(StoreProperties.addressDetails != null && StoreProperties.addressDetails != undefined) {
        $('select.cardSelectCountry').val(StoreProperties.addressDetails.country);
        $('select.invoice-billing-country').val(StoreProperties.billingAddress.country);

        if(JSON.stringify(StoreProperties.addressDetails) == JSON.stringify(StoreProperties.billingAddress)) {
            $('#card-address').click();
        }
        else {
            $('#new-address').click();
        }
    }
}

function showError(message) {
    $("html, body").animate({scrollTop: $('#myCCForm').offset().top-50 }, 1000);
    $('.cardError').addClass('alert alert-danger').html(message);
}

function hideError() {
    $('.cardError').removeClass('alert alert-danger').html('');
}

function showNotification(message) {
    $("html, body").animate({scrollTop: $('#message_notification').offset().top-50 }, 1000);
    $('#message_notification').removeClass('hide');
    $('#errormsg').html('<div class="alert alert-danger">'+message+'</div>');
}

function hideNotification() {
    $('#message_notification').addClass('hide');
}

function showActionAmount(price) {
    $('.totalPrice,.totalToPay,.planPrice').html(price);
    $('.totalToPayBand').html(price);
    $('.discountRow').hide();
    $('.totalDiscountPercent').html('');
    $('.discountPrice').html(0);

    $('.cPlanPrice').html(_.round(StoreProperties.pricing[StoreProperties.currencyCode][StoreProperties.pricingPeriod] ,2));
    var confunit = parseInt(StoreProperties.members) + 1;
    $('.confUnit').html(confunit);
    $('input#selectedUsers').val(StoreProperties.members);

    setPlanPeriod();
}

function setPayMethod() {
    var activeTab = $('.payment-menu.payment-active').attr('tab');

    if(activeTab == 'ccardContainer') var method = 'card';
    if(activeTab == 'paypalContainer') var method = 'paypal';

    StoreProperties.paymentMethod = method;

    $('input#paymentMethod').val(method);
    formData.append('paymentMethod', method);
}

$(function() {
    $("#volume_users").typeWatch({ highlight:true, wait:500, captureLength: -1, callback: calculateTotalAmount });

    $('input[name="duration"]').change(function () {
        StoreProperties.pricingPeriod = this.value;
        calculateTotalAmount();
    });

    $('select#teamUpdateType').change(function () {
        hideNotification();
        var type = $(this).val();

		//init number of users
		if(type !== '') {
			var selectedUsers = $("#volume_users").val();
			if(selectedUsers === '') {
				$("#volume_users").val('1');
			}
		}
        if(type === '')  {
            StoreProperties.action = '';
            showNotification("Select an action");
            $('.amountDesc').html('added/remove from');
            return;
        }
        else if(type == 'add') {
            StoreProperties.action = 'add';
            calculateTotalAmount();
        }
        else if(type == 'remove') {
            StoreProperties.action = 'remove';
            calculateTotalAmount();
        }
    });

    $("#makepayment").click(function(e) {
        hideError();
        setPayMethod();
        var currency = $('#currency').val();

        if(StoreProperties.paymentMethod == 'card') {
            var name = $('#companyname').val();
            var lastname = $('#lastname').val();
            var phone = $('#phoneNo').val();
            var cardNumber = $("#ccNo").val();
            var cvv = $("input#cvvCard").val();
            var expMonth = $("#expMonth").val();
            var expYear = $("#expYear").val();
            var country = $("select.cardSelectCountry").val();
            var countryCode = $('select.cardSelectCountry').children(":selected").attr("country-code");
            var address1 = $("#address1").val();
            var state = $("input#state").val();
            var city = $("input#city").val();
            var pincode = $("input#pincode").val();

            if(name == undefined || name == '') {
                showError('First Name is required');
                return false;
            }

            if(lastname == undefined || lastname == '') {
                showError('Last Name is required');
                return false;
            }

            if(phone == undefined || phone == '') {
                showError('Phone is required');
                return false;
            }

            if(cardNumber == undefined || cardNumber == '') {
                showError('Card Number is required');
                return false;
            }

            if(cvv == undefined || cvv == '') {
                showError('CVV is required');
                return false;
            }

            if(expMonth == undefined || expMonth == '') {
                showError('Exp Month is required');
                return false;
            }

            if(country == undefined || country == '') {
                showError('Country is required');
                return false;
            }

            if(address1 == undefined || address1 == '') {
                showError('Street Address is required');
                return false;
            }

            if(state == undefined || state == '') {
                showError('State is required');
                return false;
            }

            if(city == undefined || city == '') {
                showError('City is required');
                return false;
            }

            if(pincode == undefined || pincode == '') {
                showError('Pincode is required');
                return false;
            }

            if($("#new-address").is(":checked")) {
                var billing_country = $("select.invoice-billing-country").val();
                var billing_countryCode = $("select.invoice-billing-country").children(":selected").attr("country-code");
                var billing_address1 = $("#billing_address1").val();
                var billing_state = $("input#billing_state").val();
                var billing_city = $('.invoice-billing-address').find("#city").val();
                var billing_pincode = $("input#billing_pincode").val();

                if(billing_country == undefined || billing_country == '') {
                    showError('Billing country is required');
                    return false;
                }

                if(billing_address1 == undefined || billing_address1 == '') {
                    showError('Billing Street Address is required');
                    return false;
                }

                if(billing_state == undefined || billing_state == '') {
                    showError('Billing State is required');
                    return false;
                }

                if(billing_city == undefined || billing_city == '') {
                    showError('Billing City is required');
                    return false;
                }

                if(billing_pincode == undefined || billing_pincode == '') {
                    showError('Billing Pincode is required');
                    return false;
                }
            }

            formData.append('name', name);
            formData.append('lastname', lastname);
            formData.append('phone', phone);

            formData.append('cardNumber', cardNumber);
            formData.append('cvv', cvv);
            formData.append('expMonth', expMonth);
            formData.append('expYear', expYear);

            formData.append('country', country);
            formData.append('countryCode', countryCode);
            formData.append('address1', address1);
            formData.append('state', state);
            formData.append('city', city);
            formData.append('pincode', pincode);

            if ($("#card-address").is(":checked")) {
                formData.append('billing_country', country);
                formData.append('billing_address1', address1);
                formData.append('billing_state', state);
                formData.append('billing_city', city);
                formData.append('billing_pincode', pincode);
                formData.append('billing_countryCode', countryCode);
            }
            else {
                formData.append('billing_country', billing_country);
                formData.append('billing_countryCode', billing_countryCode);
                formData.append('billing_address1', billing_address1);
                formData.append('billing_state', billing_state);
                formData.append('billing_city', billing_city);
                formData.append('billing_pincode', billing_pincode);
            }
        }
        else if(StoreProperties.paymentMethod == 'paypal') {
            var selectedUsers = $('#selectedUsers').val();
            formData.append('selectedUsers', selectedUsers);
        }
        formData.append('duration', StoreProperties.pricingPeriod);
        formData.append('currency', StoreProperties.currencyCode);

        formData.append('_token', $('input[name="_token"]').val());

        if(StoreProperties.purchaseType == 'teamUpdate') {
            $('input#action').val(StoreProperties.action);
        }

        $.ajax({
            method: 'post',
            url: paymentUrl,
            dataType: 'json',
            data: formData,
            contentType : false,
            processData : false,
            beforeSend: function (xhr) {
                blockUI();
            },
            success: function (response) {
                unblockUI();
                //var checkoutForm = document.getElementById('myCCForm');
                //checkoutForm.submit();
				//send form data via ajax
                var checkoutForm = $('#myCCForm');
				$.ajax({
					method: 'post',
					url: checkoutForm.attr('action'),
					dataType: 'json',
					data: checkoutForm.serialize(),
					cache: false,
					beforeSend: function (xhr) {
						blockUI();
					},
					success: function (response) {
						unblockUI();
						if(response.success == "success") {
							//showSuccess('success');
							if(typeof response.redirect_url != null) {
								window.top.location.href = response.redirect_url;
							}
						} else {
							showError(response.message);
						}
					},
					error: function (xhr, textStatus, thrownError) {
						unblockUI();
						showError("Something went wrong. Please Try again later!");
					}
				});
            },
            error: function (xhr, textStatus, thrownError) {
                unblockUI();
                showError("Something went wrong. Please Try again later!");
            }
        });

        // Prevent form from submitting
        return false;
    });
});

function validateTeamMemberUpdateStep1() {
    var action = $('#teamUpdateType').val();
    if(action != 'add' && action != 'remove') {
        showNotification("Select an action");
        return false;
    }

    var members = $('#volume_users').val();
    if(members == '' || !isNumber(members) ) {
        showNotification("Select valid members");
        return false;
    }
    return true;
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function blockUI(target, message) {
    var html = '';
    html = '<div class="loading-message loading-message-boxed"><img src="' + assetsPath + 'loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;' + (message ? message : 'LOADING...') + '</span></div>';
    var centerY = false;

    if (target) { // element blocking
        var el = $(target);
        if (el.height() <= ($(window).height())) {
            centerY = true;
        }
        el.block({
            message: html,
            baseZ: 1000,
            centerY: centerY !== undefined ? centerY : false,
            css: {
                top: '10%',
                border: '0',
                padding: '0',
                backgroundColor: 'none'
            },
            overlayCSS: {
                backgroundColor: '#555',
                opacity: '0.05',
                cursor: 'wait'
            }
        });
    }
    else { // page blocking
        $.blockUI({
            message: html,
            baseZ: 1000,
            css: {
                border: '0',
                padding: '0',
                backgroundColor: 'none'
            },
            overlayCSS: {
                backgroundColor: '#555',
                opacity: 0.05,
                cursor: 'wait'
            }
        });
    }
}

// Metronic function to  un-block element(finish loading)
function unblockUI(target) {
    if (target) {
        $(target).unblock({
            onUnblock: function () {
                $(target).css('position', '');
                $(target).css('zoom', '');
            }
        });
    }
    else { $.unblockUI();}
}
