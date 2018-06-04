function getPriceOfAllCurrencies(pricing, payPeriod, label, amount, multiplier, showServiceDiscountedPrice) {
    var resultJSON = {},
        keys = Object.keys(pricing);
    return $.each(keys, function(i, key) {
        if (amount || 0 === amount) resultJSON[key] = amount;
        else {
            var priceJSON = pricing[key][payPeriod];
            showServiceDiscountedPrice || (priceJSON = priceJSON && priceJSON.fullprice ? priceJSON.fullprice : priceJSON);
            var tempPricing = "object" == typeof priceJSON[label] ? priceJSON[label][0].price : priceJSON[label];
            resultJSON[key] = multiplier ? multiplier * tempPricing : tempPricing
        }
    }), resultJSON
}

function getPricingObj(payperiod, fullPrice) {
    var json = StoreProperties.pricing[StoreProperties.currencyCode][payperiod];
    return fullPrice && json && json.fullprice ? json.fullprice : json
}

function buildRenderingJSON(action, obj, showServiceDiscountedPrice) {
    var pricingJSON, plantypes, oldAddOnId, newAddOnId, period, addOnId, planType, payPeriod, selectedPlan = StoreProperties.selectedPlan,
        userPlan = StoreProperties.userPlan,
        freeplanId = "";
    "frequencyChange" === action ? (period = obj.period, pricingJSON = getPricingObj(period), payPeriod = period) : "addonChange" === action ? (pricingJSON = getPricingObj(selectedPlan.payPeriod), oldAddOnId = obj.oldAddOnId, newAddOnId = obj.newAddOnId, payPeriod = selectedPlan.payPeriod) : "addonFrequencyChange" === action && (pricingJSON = getPricingObj(selectedPlan.payPeriod), addOnId = obj.addOnId, payPeriod = selectedPlan.payPeriod), renderJSON = StoreProperties.renderingJSON, plantypes = renderJSON.planTypes, planType = plantypes[0].type;
    for (var i = 0; i < plantypes.length; i++) {
        var plans = plantypes[i].plans,
            addons = {};
        plantypes[i].selectedPayPeriod = "YEAR" === period ? "yearly" : "monthly";
        for (var k = 0; k < plans.length; k++) {
            var plan = plans[k];
            if ("UB" === planType || "SUB" === planType) {
                if ("user" === obj.type) {
                    var userAddOnId = "SUB" === planType ? newAddOnId : plan.addonid;
                    Number(userPlan.plan) !== freeplanId && selectedPlan[userAddOnId] > 0 ? (plan.opted = !0, plan.value = selectedPlan[newAddOnId]) : (plan.opted = !1, plan.value = 0)
                }
                if (plan.price = "UB" === planType && plan.addonid ? getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, plan.addonid, null, null, showServiceDiscountedPrice) : getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, void 0, 0, null, showServiceDiscountedPrice), "SUB" === planType && plan.plan === plantypes[i].selectedPlan) {
                    for (var secondaryLicenses = plan.secondaryLicenseDetails.licenses, a = 0; a < secondaryLicenses.length; a++) {
                        var license = secondaryLicenses[a];
                        license.price = getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, license.pricing, null, null, showServiceDiscountedPrice), secondaryLicenses[a] = license
                    }
                    plan.secondaryLicenseDetails.licenses = secondaryLicenses
                }
            } else plan.price = "PDB-UUB" === planType ? pricingJSON[plan.plan] ? getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, plan.plan, null, null, !0) : getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, void 0, 0, null, !0) : pricingJSON[plan.plan] ? getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, plan.plan, null, null, showServiceDiscountedPrice) : getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, void 0, 0, null, showServiceDiscountedPrice);
            if ("frequencyChange" === action) {
                if (plan.frequency = "YEAR" === period ? i18n["zohostore.pricing.frequency.year"] : i18n["zohostore.pricing.frequency.month"], plan.addOn) {
                    for (var addons = plan.addOn, addonsList = addons.list, a = 0; a < addonsList.length; a++) {
                        var addon = addonsList[a];
                        addon.price = getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, null, null, showServiceDiscountedPrice)
                    }
                    addons.list = addonsList, plan.addOn = addons
                }
            } else if ("addonChange" === action) {
                if (plan.addOn) {
                    addons = plan.addOn;
                    for (var addonsList = addons.list, a = 0; a < addonsList.length; a++) {
                        var addon = addonsList[a];
                        addon.addonid === oldAddOnId ? (addon.opted = !1, addon.value = 0) : addon.addonid === newAddOnId && (addon.price = getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, null, null, showServiceDiscountedPrice), selectedPlan[addon.addonid] ? (addon.opted = !0, addon.value = selectedPlan[addon.addonid]) : (addon.opted = !1, addon.value = selectedPlan[addon.addonid]))
                    }
                    addons.list = addonsList, plan.addOn = addons
                }
            } else if ("addonFrequencyChange" === action && plan.addOn) {
                addons = plan.addOn;
                for (var addonsList = addons.list, a = 0; a < addonsList.length; a++) {
                    var addon = addonsList[a];
                    if (addon.addonid === addOnId) {
                        var addonVal = selectedPlan[addon.addonid];
                        "enum" === addon.type && addon.value ? (addon.value.purchasedOptionsAddOn.value = addonVal, addon.value.purchasedOptionsAddOn.price = "true" === addon.sameprice ? getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, void 0, addonVal, showServiceDiscountedPrice) : getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, null, null, showServiceDiscountedPrice)) : addon.value = addonVal, addon.opted = addonVal > 0 ? !0 : !1
                    }
                }
                addons.list = addonsList, plan.addOn = addons
            }
            plans[k] = plan
        }
        plantypes[i].plans = plans
    }
    if (renderJSON.addOn) {
        addons = renderJSON.addOn;
        for (var addonsList = addons.list, b = 0; b < addonsList.length; b++) {
            var addon = addonsList[b];
            if (addon.addonid === addOnId) {
                var addonVal = selectedPlan[addon.addonid];
                "enum" === addon.type && addon.value ? (addon.value.purchasedOptionsAddOn.value = addonVal, addon.value.purchasedOptionsAddOn.price = "true" === addon.sameprice ? getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, void 0, addonVal, showServiceDiscountedPrice) : getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, null, null, showServiceDiscountedPrice)) : addon.value = addonVal, addon.opted = addonVal > 0 ? !0 : !1
            }
            if ("frequencyChange" === action && "enum" === addon.type && "false" === addon.sameprice)
                for (var addonOpt = addon.option, c = 0; c < addonOpt.length; c++) addonOpt[c].price = getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addonOpt[c].addonid, null, null, showServiceDiscountedPrice);
            addon.price = getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, addon.addonid, null, null, showServiceDiscountedPrice), addonsList[b] = addon
        }
        addons.list = addonsList, renderJSON.addOn = addons
    }
    if (renderJSON.onetimeaddon) {
        var onetimeaddon = renderJSON.onetimeaddon;
        onetimeaddon.price = getPriceOfAllCurrencies(StoreProperties.pricing, payPeriod, onetimeaddon.addonid, null, null, showServiceDiscountedPrice), renderJSON.onetimeaddon = onetimeaddon
    }
    return renderJSON
}

function showSpecialDiscountInfo() {
    if (StoreProperties.specialDiscount) {
        var i18n = JSON.parse(StoreProperties.i18n);
        $(".announce_shdr").html(i18n["store.SpecialDiscount.info"].replace("{0}", StoreUtil.getSpecialDiscountString())), $("#messageinfo").show()
    }
}

function isDiscountApplied(plan, specialDiscount) {
    var recDueUserPlan = StoreProperties.userPlan && StoreProperties.userPlan.recurringDue ? StoreProperties.userPlan.recurringDue : 0,
        recDueSelPlan = StoreProperties.selectedPlan && StoreProperties.selectedPlan.recurringDue ? StoreProperties.selectedPlan.recurringDue : 0;
    plan = plan || StoreProperties.selectedPlan, specialDiscount = specialDiscount || StoreProperties.specialDiscount;
    var reflection = specialDiscount && null !== specialDiscount.REFLECTION && void 0 !== specialDiscount.REFLECTION && "" !== specialDiscount.REFLECTION ? specialDiscount.REFLECTION : "",
        isDiscountAlreadyAvailed = specialDiscount && null !== specialDiscount.ISAVAILED && void 0 !== specialDiscount.ISAVAILED && "1" === specialDiscount.ISAVAILED ? !0 : !1,
        isDiscountAvailedInPurchase = null !== StoreProperties.isDiscountAvailedInPurchase && void 0 !== StoreProperties.isDiscountAvailedInPurchase && StoreProperties.isDiscountAvailedInPurchase ? !0 : !1;
    return "2" === reflection ? isDiscountAvailedInPurchase ? !0 : !1 : recDueUserPlan === recDueSelPlan ? isDiscountAlreadyAvailed ? !0 : !1 : (isDiscountAlreadyAvailed = isDiscountAvailedInPurchase ? isDiscountAlreadyAvailed : !1, specialDiscount && (StoreProperties.isDiscountAvailedInPurchase || isDiscountAlreadyAvailed) ? !0 : !1)
}

function getTotalDiscountPrice(plan) {
    plan = plan || StoreProperties.selectedPlan || StoreProperties.userPlan;
    var priceSplitUp = {},
        discount = 0;
    if ($(".totalDiscountPercent").text(""), isDiscountApplied(plan) ? (priceSplitUp = StoreUtil.getDiscountedPriceSplitUp(), $(".totalDiscountPercent").text(i18n["store.specialdiscount"] + " ")) : (priceSplitUp = StoreUtil.getPriceSplitUp(plan, !0), $(".totalDiscountPercent").text(i18n["store.yearlydiscount"] + " ")), priceSplitUp)
        for (var key in priceSplitUp) discount += priceSplitUp[key].discount;
    return StoreUtil.roundup(discount, StoreProperties.currency.decimals.total)
}

function showDiscountPrice(totalPrice, plan) {
    plan = plan || StoreProperties.selectedPlan || StoreProperties.userPlan, $(".totalRow, .discountRow,.discountSeperator").hide(), totalPrice = parseFloat(String(totalPrice).replace(/,/g, ""));
    var discountAmnt = getTotalDiscountPrice(plan);
    return $(".totalRow .totalPrice").text(formatAmount(totalPrice)), $(".discountRow .discountPrice").text(formatAmount(discountAmnt)), discountAmnt > 0 && $(".totalRow, .discountRow,.discountSeperator").show(), totalPrice -= discountAmnt, StoreUtil.roundup(totalPrice, StoreProperties.currency.decimals.total)
}

function getCurrencySymbol(currencyCode) {
    var currencySymbols = currencyJSON.currencySymbols;
    return currencySymbols ? currencySymbols[currencyCode] : void 0
}

function getCurrencyCode(currencyId) {
    var currencyCodes = currencyJSON.currency_code;
    return currencyCodes ? currencyCodes[currencyId] : void 0
}

function getCurrencyCountry(currencyId) {
    var currencyCountries = currencyJSON.id_country;
    return currencyCountries ? currencyCountries[currencyId] : void 0
}

function getCurrency(country) {
    return currencyJSON[country]
}

function getSalesToolFreeNo() {
    var salesToolFreeNo = "+1 888 900 9646";
    return StoreProperties.product_domain && "manageengine" === StoreProperties.product_domain ? salesToolFreeNo = "+1 800 443 6694" : StoreProperties.product_domain && "site24x7" === StoreProperties.product_domain && (salesToolFreeNo = "+1 408 352 9117"), salesToolFreeNo
}

function encodeString(val) {
    if (val) {
        val = val.replace(/(\r\n|\n|\r)/gm, ""), val = val.replace(/\t+/g, ""), val = val.replace(/"/g, ""), val = encodeURIComponent(val);
        for (var replaceChar = ["'", "-", "_", ".", "!", "~", "*", "(", ")"], percentEncodedVal = ["%27", "%2D", "%5F", "%2E", "%21", "%7E", "%2A", "%28", "%29"], i = 0; i < replaceChar.length; i++) val = val.replaceAll(replaceChar[i], percentEncodedVal[i])
    }
    return val
}

function formatDate(d) {
    var dateObj = null;
    return d instanceof Date ? dateObj = d : (dateObj = new Date(d), isNaN(dateObj.getDate()) && (d = d.replace(/-/g, "/"), dateObj = new Date(d))), dateObj.getDate() + " " + getMonthName(dateObj) + " " + dateObj.getFullYear()
}

function decimalNumber(price) {
    var price = price.toString().indexOf(".") > -1 ? parseFloat(price).toFixed(2) : price;
    return price
}

function formatAmount(amt, nonDecimal) {
    amt = null !== amt || void 0 !== amt ? amt : 0;
    var price = decimalNumber(amt).toString(),
        afterPoint = "";
    if (price.indexOf(".") > 0 ? (afterPoint = price.substring(price.indexOf("."), price.length), nonDecimal && -1 !== afterPoint.indexOf(".00") && (afterPoint = "")) : (afterPoint = ".00", nonDecimal && -1 !== afterPoint.indexOf(".00") && (afterPoint = "")), price = price >= 0 ? Math.floor(price) : Math.ceil(price), price = price.toString(), "INR" == activeCurrency) {
        var lastThree = price.substring(price.length - 3),
            otherNumbers = price.substring(0, price.length - 3);
        return "" != otherNumbers && (lastThree = "," + lastThree), otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint
    }
    return "USD" == activeCurrency ? price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + afterPoint : price
}

function respConfirm(newPayperiod) {
    var response = confirm("Do you want to change pay period to " + newPayperiod + "?");
    return response ? !0 : !1
}

function payPeriod(period, newPayperiod) {
    var period = null != newPayperiod ? newPayperiod : period,
        totalprice = 0;
    "yearly" == period ? ($("#payPeriod").attr("class", "yearly fCaps").text(i18n["zohostore.pricing.frequency.yearly"]), $(".payPeriod").text(i18n["zohostore.pricing.frequency.year"]), $(".addOnTable,.ManageTable").find("[period=monthlyonly]").addClass("hide"), $(".addOnTable,.ManageTable").find("[period=yearlyonly]").removeClass("hide")) : "monthly" == period && ($("#payPeriod").attr("class", "monthly fCaps").text(i18n["zohostore.pricing.frequency.monthly"]), $(".payPeriod").text(i18n["zohostore.pricing.frequency.month"]), $(".addOnTable,.ManageTable").find("[period=monthlyonly]").removeClass("hide"), $(".addOnTable,.ManageTable").find("[period=yearlyonly]").addClass("hide")), $(".addOnTable tr[rows]:visible").each(function() {
        var addon = $(this).attr("rows"),
            price = "" !== $(this).attr("price") ? $(this).attr("price") : 0,
            type = $(this).attr("type"),
            unitVal = 0,
            slabPricing = !1;
        if ("unit" == type || "user" == type) {
            if (unitVal = null !== $(this).find("." + addon + "UnitVal").html() && "" !== $(this).find("." + addon + "UnitVal").html() && "false" !== $(this).attr("opted") ? $(this).find("." + addon + "UnitVal").html() : 0, "true" === $(this).attr("slabpricing")) {
                slabPricing = !0;
                var id = $(this).attr("addonid"),
                    count = unitVal,
                    pricing = StoreUtil.getPricingObj(StoreProperties.selectedPlan.payPeriod);
                price = StoreUtil.calculateAddonPrice(count, pricing[id])
            }
        } else "subscription" == type ? unitVal = "true" == $(this).attr("opted") ? 1 : 0 : "enum" == type ? "false" === $(this).attr("sameprice") ? (unitVal = null !== $(this).find(".enumVal").html() ? 1 : 0, price = void 0 !== $(this).attr("eNumPrice") && "" !== $(this).attr("eNumPrice") ? $(this).attr("eNumPrice") : price) : (unitVal = null === $(this).find(".enumVal").html() || isNaN(parseInt($(this).find(".enumVal").text())) ? 0 : 1, price = void 0 !== $(this).attr("eNumPrice") && "" !== $(this).attr("eNumPrice") ? $(this).attr("eNumPrice") : price) : unitVal = $(this).attr("planid") ? 1 : 0;
        unitVal = void 0 === unitVal || isNaN(unitVal) ? 0 : unitVal;
        var addonprice = slabPricing ? parseFloat(price) : parseFloat(price) * parseFloat(unitVal);
        "0" === price ? $(this).find(".planPrice").parents("td").html("-") : $(this).find(".planPrice,.addonunitPrice").text(formatCurrency(price)), $(this).find(".addonPrice").text(formatAmount(addonprice)), totalprice += addonprice
    }), totalprice = showDiscountPrice(totalprice), $(".nNetPrc").text(formatAmount(totalprice))
}

function formatCurrency(price, decimal) {
    price = decimalNumber(price).toString();
    var afterPoint = "";
    if (price.indexOf(".") > 0 ? afterPoint = price.substring(price.indexOf("."), price.length) : decimal && (afterPoint = ".00"), price = Math.floor(price), price = price.toString(), "INR" == activeCurrency) {
        var lastThree = price.substring(price.length - 3),
            otherNumbers = price.substring(0, price.length - 3);
        return "" != otherNumbers && "-" != otherNumbers && (lastThree = "," + lastThree), otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint
    }
    return "USD" == activeCurrency ? price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + afterPoint : price
}

function editBillingAddress() {
    StoreProperties.cc = StoreProperties.paymentdetails.card_details, StoreProperties.cc && StoreUtil.cc.set(StoreProperties.cc), setDefaultDetails("billingDetails"), $(".dispAdrs,.billing-address-box").slideUp(200), $(".changeAdrs").slideDown(200, function() {
        $(".changeAdrs").find("textarea:first").focus();
        var billAdrs = $(".billAdrs"),
            bodyParent = $(window),
            body = $("body"),
            bodyH = bodyParent.innerHeight() + body.scrollTop(),
            billAdrsTH = billAdrs.offset().top + billAdrs.innerHeight();
        billAdrsTH > bodyH && body.animate({
            scrollTop: billAdrsTH - bodyH + 20
        }, 200)
    })
}

function cardMsg(left, top) {
    var msg = null != msg ? msg : "enter valid input";
    $(".cErrMsg").css({
        left: left + 20 + "px",
        top: top + 35 + "px"
    }).fadeIn(300).html("<span></span>" + msg)
}

function calculatePrice(el, calculatePrice) {
    var per = $("#" + el).attr("price"),
        discount = $("#" + el).attr("yearlydiscount"),
        slabPricing = !1;
    if ("true" === $("#" + el).attr("slabPricing") && ($("#" + el).hasClass("clPrice") || $("#" + el).hasClass("clUnitPrice"))) {
        slabPricing = !0;
        var id = $("#" + el).attr("addonid"),
            count = $("#" + el).val(),
            pricing = StoreUtil.getPricingObj(StoreProperties.selectedPlan.payPeriod);
        per = StoreUtil.calculateAddonPrice(count, pricing[id])
    } else $("#" + el).hasClass("clEnumPrice") && "false" === $("#" + el).attr("sameprice") && (per = $("#" + el).find("option:selected").attr("addonid") ? StoreUtil.getItemPrice(StoreProperties.pricing, StoreProperties.selectedPlan.payPeriod, activeCurrency, $("#" + el).find("option:selected").attr("addonid")) : 0);
    per = parseFloat(per);
    var n = $("#" + el).val(),
        cval = slabPricing ? per : per * n;
    if (cval = $(".payperiodDiv #yearly").is(":checked") && null != discount && "" != discount ? 12 * cval - 12 * cval * discount / 100 : cval, $('[addonname="' + el + '"]').show().find(".addOnPrice").html(formatAmount(cval)), "text" == $("#" + el).attr("type")) {
        var getName = $("#" + el).attr("name");
        $("#" + el).hasClass("cPlanInpPrice") ? ($("#planUnderList").find(".confUnit").html(n), $("#planUnderList").find(".addOnPrice").html(formatAmount(cval)), $("[addonconfirm='" + getName + "']").show()) : "" !== $("#" + el).val() && 0 !== $("#" + el).val() ? ($("[addon='" + getName + "'],[addonconfirm='" + getName + "']").show().find(".confUnit").html(n), $("[addon='" + getName + "'],[addonconfirm='" + getName + "']").find(".addOnPrice").html(formatAmount(cval))) : ($("[addon='" + getName + "'],[addonconfirm='" + getName + "']").hide().find(".confUnit").html(n), $("[addon='" + getName + "'],[addonconfirm='" + getName + "']").find(".addOnPrice").html(formatAmount(cval))), "" === $("#" + el).val() && 0 === $("#" + el).val() && $("[addon='" + getName + "']").hide()
    } else if ("checkbox" == $("#" + el).attr("type")) {
        var getName = $("#" + el).attr("name");
        if ($("#" + el).is(":checked")) $("[addon='" + getName + "']").show().find(".confUnit").html(n), $("[addonconfirm='" + getName + "']").show().find(".addOnPrice").html(formatAmount(cval));
        else {
            $("[addonconfirm='" + getName + "']").hide(), $("#" + el).parent().next(".fPrice").find(".addOnPrice").html(formatAmount(0));
            for (var addonTotal = $("span.planDetailAddon:visible"), countTo = 0, i = 0; i < addonTotal.length; i++) countTo += parseFloat($(addonTotal[i]).html().replace(/,/g, ""));
            $(".addonTotal").html(formatAmount(countTo))
        }
    } else {
        var getName = $("#" + el).attr("name"),
            setEnumUnitVal = $("#" + el + " option:selected").attr("optionVal");
        0 != $("#" + el).prop("selectedIndex") ? ($("[addon='" + getName + "'],[addonconfirm='" + getName + "']").show().find(".confUnit").html($("#" + el).hasClass("clEnumPrice") && "false" === $("#" + el).attr("sameprice") ? setEnumUnitVal : n), $("[addon='" + getName + "'],[addonconfirm='" + getName + "']").show().find(".addOnPrice").html(formatAmount(cval))) : $("[addon='" + getName + "'],[addonconfirm='" + getName + "']").hide()
    }
    for (var addonTotal = $("span.planDetailAddon:visible"), countTo = 0, i = 0; i < addonTotal.length; i++) "UB" !== $(addonTotal[i]).attr("plantype") && "SUB" !== $(addonTotal[i]).attr("plantype") && (countTo += parseFloat($(addonTotal[i]).text().replace(/\,/g, "")));
    var planPrice = null != $(".planPrice").html() ? $(".planPrice").html().replace(/\,/g, "") : 0;
    parseFloat(planPrice) + countTo > 0 ? $(".confirmAddon").removeClass("hide") : $(".confirmAddon").addClass("hide"), $(".addonTotal").html(formatAmount(countTo)), $(".totalToPay").html(formatAmount(parseFloat(countTo) + parseFloat(planPrice)));
    var a = formatAmount(parseFloat(countTo) + parseFloat(planPrice)),
        b = formatAmount(parseFloat(StoreProperties.selectedPlan.recurringDue)),
        period = "MONT" === StoreProperties.selectedPlan.payPeriod ? "month" : "year";
    a !== b && void 0 !== StoreProperties.licenseInfo.discount && 0 !== StoreProperties.licenseInfo.discount ? ($(".totalToPay").html(b), $(".totalAmountBG").html(i18n["zohostore.newsubscription.amounttobepaidper"] + " <span class='period'>" + period + "</span> after 20% discount on Bugtracker <strike  class='cfff fW400'>$" + a + "</strike>")) : 18e4 === StoreProperties.serviceId && StoreProperties.existingOrgList && Object.keys(StoreProperties.existingOrgList).length > 0 && StoreProperties.existingOrgList.TOTALDISCOUNT > 0 ? ($(".totalToPay").html(StoreProperties.selectedPlan.dueNow), $(".totalAmountBG").html("Amount to be paid after Existing Subscriptions $" + StoreProperties.existingOrgList.TOTALDISCOUNT.toFixed(2) + " discount <strike  class='cfff fW400'>$" + a + "</strike>")) : $(".totalAmountBG").html(void 0 !== StoreProperties.selectedPlan.freeOTP && StoreProperties.selectedPlan.freeOTP ? i18n["store.amount.to.be.paid"] : i18n["zohostore.newsubscription.netamount.tobepaidper"] + " <span class='period'>" + period + "</span>"), $(".totalToPay").html(formatAmount(showDiscountPrice($(".totalToPay").html()))), currencyAlign("addOnPrice", calculatePrice)
}

function selectedDisableAddon() {
    var portalSpecificcAddon = StoreProperties.licenseInfo.portalspecificaddon;
    if (portalSpecificcAddon)
        for (var i = 0; i < portalSpecificcAddon.length; i++) {
            var addonid = parseInt(portalSpecificcAddon[i].id);
            $("input.clSubPrice[addonid = " + addonid + "]").closest("tr").addClass("c66").show(), $("input[addonid = " + addonid + "]").trigger("click").prop("disabled", "disabled")
        }
}

function selectedDisableAddonYrly() {
    var portalSpecificcAddon = StoreProperties.licenseInfo.portalspecificaddon;
    if (portalSpecificcAddon)
        for (var i = 0; i < portalSpecificcAddon.length; i++) {
            var addonid = parseInt(portalSpecificcAddon[i].id),
                cancelSubscription = portalSpecificcAddon[i].cancelsubscription;
            $(".addOnRow[rows=" + addonid + "], .ManageTable tr[rows=" + addonid + "]").show(), cancelSubscription && ($(".ManageTable tr[rows=" + addonid + "]").find("div[popcont=" + addonid + "]").addClass("cancelSubscription").attr("onclick", "cancelSubscription(" + addonid + ")"), $(".ManageTable [popcont=" + addonid + "]").find(".chCont").text(i18n["zohostore.cancelsubscription"]))
        }
}

function cardMsg(left, top, msg, changecc) {
    var msg = null != msg ? msg : "enter valid input";
    $(".cErrMsg").css({
        left: left + 20 + "px",
        top: top + 38 + "px"
    }).fadeIn(300).html("<span></span>" + msg), changecc && $(".cErrMsg").css({
        left: left + 287 + "px",
        top: top + 70 + "px",
        "z-index": "99999999999"
    }).fadeIn(300).html("<span></span>" + msg)
}

function checkCCard(number) {
    var luhnArr = [
            [0, 2, 4, 6, 8, 1, 3, 5, 7, 9],
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
        ],
        sum = 0;
    return number.replace(/\D+/g, "").replace(/[\d]/g, function(c, p, o) {
        sum += luhnArr[o.length - p & 1][parseInt(c, 10)]
    }), sum % 10 === 0 && sum > 0
}

function scrollToDiv(element, extra, time) {
    var extra = null != extra ? extra : 0,
        time = null != time ? time : "slow";
    $("html, body").animate({
        scrollTop: $("." + element).offset().top + parseInt(extra)
    }, time)
}

function scrollToElement(element, extra, time) {
    var extra = null != extra ? extra : 0,
        time = null != time ? time : "slow";
    $("html, body").animate({
        scrollTop: element.offset().top + parseInt(extra)
    }, time)
}

function ExpDate(mon, yr) {
    var month = ($("." + mon + " option:selected").text(), $("." + yr + " option:selected").text(), parseInt($(".eMon").val(), 10) - 1),
        year = parseInt($(".eYear").val(), 10),
        today = new Date;
    return month < today.getMonth() && year === parseInt(today.getFullYear().toString().slice(2))
}

function getCardImg(cardtype) {
    var cardimg = "visaIc";
    return cardtype = cardtype.toLowerCase(), -1 != cardtype.indexOf("visa") ? cardimg = "visaIc" : -1 != cardtype.indexOf("master") ? cardimg = "masterIc" : -1 != cardtype.indexOf("discover") ? cardimg = "discoverIc" : -1 != cardtype.indexOf("jcb") ? cardimg = "jcbIc" : -1 != cardtype.indexOf("amex") ? cardimg = "amexIc" : -1 != cardtype.indexOf("diners") && (cardimg = "dinersclubIc"), cardimg
}

function addionalS() {
    var UnitCont = $(".unitSign"),
        UnitCont = null != $(UnitCont).html() ? UnitCont : $(".unitSign"),
        findUnits = $(UnitCont).html().substr($(UnitCont).html().length - 1);
    $(".numValue").val() > 1 ? "s" != findUnits && $(UnitCont).append("s") : "s" == findUnits && $(UnitCont).html($(UnitCont).html().replace(/(\s+)?.$/, ""))
}

function getCardImgName(cardtype) {
    var cardimg = "visa-cc";
    return cardtype = cardtype.toLowerCase(), -1 !== cardtype.indexOf("visa") ? cardimg = "visa-cc" : -1 !== cardtype.indexOf("master") ? cardimg = "master-cc" : -1 !== cardtype.indexOf("discover") ? cardimg = "discover-cc" : -1 !== cardtype.indexOf("jcb") ? cardimg = "jcb-cc" : -1 !== cardtype.indexOf("amex") ? cardimg = "amex-cc" : -1 !== cardtype.indexOf("diners") && (cardimg = "dinner-cc"), cardimg
}

function removeCardImg(obj) {
    $(obj).removeClass("visaIc").removeClass("masterIc").removeClass("discoverIc").removeClass("jcbIc").removeClass("amexIc").removeClass("dinersclubIc")
}

function getSupportedCards() {
    return supportedCards = ["Visa", "MasterCard", "Discover", "JCB", "DinersClub"];
}

function getSupportedPaymentMethods() {
    var supportedPaymentMethods;
    return supportedPaymentMethods = StoreProperties.cc && StoreProperties.cc.possible_switchs && StoreProperties.cc.possible_switchs ? StoreProperties.cc.possible_switchs : StoreUtil.getSupportedPaymentMethods()
}

function loadSupportedCards() {
    $("#cardTable .cTypeTd span").removeClass("inlineBlock hide").addClass("hide");
    var supportedCards = getSupportedCards();
    supportedCards && supportedCards.length > 0 ? $.each(supportedCards, function(index, value) {
        var cardImg = getCardImg(value);
        $("#cardTable .cTypeTd ." + cardImg).removeClass("hide").addClass("inlineBlock")
    }) : $("#cardTable .cTypeTd span").removeClass("hide").addClass("inlineBlock")
}

function setSupportedCards(cards) {
    $("#cardTable .cTypeTd span").removeClass("inlineBlock hide").addClass("hide"), cards && cards.length > 0 ? $.each(cards, function(index, value) {
        var cardImg = getCardImg(value);
        $("#cardTable .cTypeTd ." + cardImg).removeClass("hide").addClass("inlineBlock")
    }) : $("#cardTable .cTypeTd span").removeClass("hide").addClass("inlineBlock")
}

function getCardNotSupportedMsg(cardtype) {
    var cardErrMsg = "{0} is not supported for {1} purchase.";
    return cardtype = cardtype.toLowerCase(), cardErrMsg = -1 != cardtype.indexOf("visa") ? cardErrMsg.replace("{0}", "VISA card") : -1 != cardtype.indexOf("master") ? cardErrMsg.replace("{0}", "Master card") : -1 != cardtype.indexOf("discover") ? cardErrMsg.replace("{0}", "Discover card") : -1 != cardtype.indexOf("jcb") ? cardErrMsg.replace("{0}", "JCB card") : -1 != cardtype.indexOf("amex") ? cardErrMsg.replace("{0}", "American Express card") : -1 != cardtype.indexOf("diners") ? cardErrMsg.replace("{0}", "DinersClub card") : cardErrMsg.replace("{0}", "Card type"), cardErrMsg = StoreProperties.cc && StoreProperties.cc.currency ? cardErrMsg.replace("{1}", StoreProperties.cc.currency) : cardErrMsg.replace("{1}", StoreProperties.currencyCode)
}

function CountTo(spin) {
    for (var $element = $(".nNetPrc"), addonTotal = $("span.addonPrice:visible"), countTo = 0, i = 0; i < addonTotal.length; i++) countTo += parseFloat($(addonTotal[i]).html().replace(/,/g, ""));
    if (countTo = showDiscountPrice(countTo), "noSpin" == spin);
    else {
        {
            $element.html()
        }
        $element.animate({
            counter: countTo
        }, {
            queue: !0,
            duration: 1e3,
            easing: "easeInOutExpo",
            step: function(currentValue) {
                $(this).html(formatAmount(currentValue)).digits()
            }
        })
    }
}

function showInlineErrorMsg(position, msg, cActionTxt1, cActionLink1, cancel, cancelAction, cActionTxt2, cActionLink2) {
    $(".showInlineErrorContent").html(msg), cActionTxt1 && ($(".showInlineErrorToAction > .cAction1").text(cActionTxt1).attr("onclick", cActionLink1).removeClass("hide"), $(".showInlineErrorToAction").removeClass("hide")), cancel && ($(".showInlineErrorToAction > .showInlineErCancel").text(cancel).removeClass("hide").attr("onclick", cancelAction), $(".showInlineErrorToAction").removeClass("hide")), cActionTxt2 && $(".showInlineErrorToAction > .cAction2").text(cActionTxt2).attr("onclick", cActionLink2).removeClass("hide"), $("#showInlineErrorMsg").fadeIn(300).css({
        top: position.top + 32,
        left: position.left
    })
}

function getParamsFromURL() {
    var href = window.location.href,
        queryJSON = {};
    return href.indexOf("?") > -1 && href.indexOf("#") > -1 && (queryStr = href.split("?")[1], queryStr.indexOf("serviceId=") > -1 && (queryJSON.serviceId = queryStr.split("serviceId=")[1].split("&")[0]), queryStr.indexOf("customId=") > -1 && (queryJSON.customId = queryStr.split("customId=")[1].split("&")[0])), queryJSON
}

function setInvoiceURL(invoiceId, transProfileId) {
    var params = getParamsFromURL(),
        profileId = transProfileId && "-" !== transProfileId ? transProfileId : hisProfileId,
        invoiceSrc = "/ExportAction.do?hidMode=generateInvoice&invoiceId=" + invoiceId;
    invoiceSrc += "&serviceid=" + params.serviceId + "&zId=" + StoreProperties.userDetails.customId + "&profileId=" + profileId + "&roles=false", $(".iframe").attr("src", invoiceSrc).attr("id", "INV" + invoiceId)
}

function capitaliseFirstLetter(str) {
    return str.charAt(0).toUpperCase() + str.slice(1)
}

function populateCardDetails(cards) {
    cards && ($("#cardContainer #chooseccard").append("<option data-text='Select Card'>Select Card</option>"), $.each(cards, function(key, val) {
        var option = "<option data-text=\"<span class='dataText'><span class='fW400'>" + val.type + " **** " + val.lastFourDigits + "</span><span class='f13'>expires on " + val.expiryMonth + " / " + val.expiryYear + "</span>\" value='" + val.type + "_" + key + "' data-icon='" + val.type.toLowerCase() + "Ic' profile='" + key + "' card='" + val.type + "'>";
        option += "</option>", $("#cardContainer #chooseccard").append(option)
    }), $("#cardContainer #chooseccard").append("<option value='newcard' data-text='Use new card' data-icon='add-card' card='newcard' data-title='newcard'>Use new card</option>"), $("#chooseccard.selectBoxIt").selectBoxIt("refresh"))
}

function setPaymentMethod() {
    return false;
    $(".cardSelection,.payment-details-menu,#payer-authentication-container").hide(), $(".payment-details-menu [tab=paypalContainer],.payment-details-menu [tab=poContainer]").hide(), $(".cardSelection .ccardTbl").removeClass("pT25"), $("#cardTable,#billAddrSplit,#billAddrTable").hide();
    var choosePaymentOption = !1,
        supportedPaymentMethods = getSupportedPaymentMethods(),
        multiCardOpened = !1;
    if (supportedPaymentMethods && supportedPaymentMethods.CC_MULTIPLE_PRODUCT_PURCHASE) {
        var cards = StoreProperties.cards;
        cards ? ($("#cardContainer #chooseccard option").length <= 0 && populateCardDetails(cards), choosePaymentOption = !0, multiCardOpened = !0, $(".cardSelection").show(), $(".cardSelection .ccardTbl").addClass("pT25"), $(".subscriptionGBtn").attr("selectedcard", $("#cardContainer #chooseccard").val()), $("#chooseccard option:selected").trigger("change")) : ($(".subscriptionGBtn").attr("selectedcard", "newcard"), $(".billing-address-split").removeClass("mT200"), $("#cardTable,#billAddrSplit").slideDown(300), checkInvoiceBillingAddress("billAddrTable"))
    }
    supportedPaymentMethods && supportedPaymentMethods.PAYPAL_EXPRESSCHECKOUT && (choosePaymentOption = !0, $(".payment-details-menu,.payment-details-menu [tab=paypalContainer]").show()), 3 !== StoreProperties.serviceId || !StoreProperties.selectedPlan || "YEAR" !== StoreProperties.selectedPlan.payPeriod || StoreProperties.selectedPlan.freeOTP || StoreProperties.inactiveProfile || (choosePaymentOption = !0, $(".payment-details-menu,.payment-details-menu [tab=poContainer]").show()), choosePaymentOption && multiCardOpened || ($(".subscriptionGBtn").attr("selectedcard", "newcard"), $(".billing-address-split").removeClass("mT200"), $("#cardTable,#billAddrSplit").slideDown(300), checkInvoiceBillingAddress("billAddrTable"), supportedPaymentMethods && supportedPaymentMethods.PAYER_AUTHENTICATION_AGREEMENT && $("#payer-authentication-container").show()), StoreProperties.renderingJSON.resellerFlow && ($(".cardSelection,.payment-details-menu,#contBtnTable,#aboutSubMsg").addClass("hide"), $(".cardSelection .ccardTbl").addClass("pT25"))
}

function nextRenewal() {
    if ("monthly" === paymentDuration) {
        var nextRenewal = new Date(2014, today.getMonth() + 1, today.getDate());
        totalDays = new Date(2014, today.getMonth() + 1, 0).getDate()
    } else {
        var nextRenewal = new Date(2015, today.getMonth(), today.getDate());
        totalDays = 365
    }
    remainingDays = (Date.UTC(nextRenewal.getYear(), nextRenewal.getMonth(), nextRenewal.getDate()) - Date.UTC(today.getYear(), today.getMonth(), today.getDate())) / 864e5
}

function discountInTotal(id, price, qty, discount) {
    var planPrice = jsonPath(subscriptionInfo, "$.planTypes[*].plans[?(@.planID == " + id + ")].price[" + activeCurrency + "]");
    tPrice = planPrice ? planPrice : jsonPath(subscriptionInfo, "$.planTypes[*].plans[?(@.addOnID == " + id + "	)].price[" + activeCurrency + "]"), tPrice = tPrice ? tPrice : jsonPath(subscriptionInfo, "$.addOn[*][?(@.addOnID == " + id + "	)].price[" + activeCurrency + "]"), $(".clEnumPrice[addonid=" + id + "]").length > 0 && !tPrice && (tPrice = price), yearlyPrice = 12 * tPrice, discount = "" != discount ? discount : subscriptionInfo.yearlyDiscount;
    var discountPrice = discount.toString().indexOf("%") > -1 ? yearlyPrice * discount.slice(0, -1) / 100 : discount;
    "INR" === activeCurrency ? $(".payment-details-menu").find('li[tab="paypalContainer"]').hide() : "USD" === activeCurrency && $(".payment-details-menu").find('li[tab="paypalContainer"]').show();
    var discountTotal = 0,
        previousContainer = $(".addonContainer .discount-amount"),
        previousDiscount = previousContainer.first().text(),
        previousDiscount = "" != previousDiscount ? previousDiscount : 0,
        totalDiscount = discountPrice * qty;
    discountObj[id] = totalDiscount;
    for (var key in discountObj) discountTotal += discountObj[key];
    priceTickerComponent(parseInt(previousDiscount), parseInt(discountTotal), previousContainer), discountTotal > 0 ? "monthly" == paymentDuration ? ($(".yearly-discount").fadeIn(300), $(".yearly-discount-gained").hide()) : ($(".yearly-discount-gained").fadeIn(300), $(".yearly-discount").hide()) : $(".yearly-discount-gained,.yearly-discount").hide()
}

function convertCurrency(activeCurrency) {
    "INR" == activeCurrency ? ($(".currencySign").html(getCurrencySymbol(activeCurrency)), $(".planPriceDolr").addClass("inrupee"), $(".payment-details-menu").hide(), $("#ccardContainer").addClass("pT25")) : "USD" == activeCurrency && ($(".currencySign").html(getCurrencySymbol(activeCurrency)), $(".planPriceDolr").removeClass("inrupee"), $(".payment-details-menu").show(), $("#ccardContainer").removeClass("pT25"))
}

function serviceAddonList() {
    $(".serAddonList li").length ? $(".closeAddonBox").show() : $(".closeAddonBox").hide()
}

function setMaximumOptionInDropDown(value) {
    if ($(".multiAddonSelection select.multiaddon_clEnumPrice option").show(), StoreProperties.renderingJSON.multipleAddonsPurchase) {
        value = value ? value : 0;
        var enumList = $(".multiAddonSelection select.multiaddon_clEnumPrice");
        $(".multiAddonSelection select.multiaddon_clEnumPrice").removeAttr("disabled");
        for (var i = 0; i < enumList.length; i++) {
            var id = $(enumList[i]).attr("addon"),
                userAddOnId = ($(enumList[i]).attr("name"), StoreProperties.plans && StoreProperties.plans.usersMap ? StoreProperties.plans.usersMap[StoreProperties.selectedPlan.plan] : null),
                selectedAddOnID = parseInt(id);
            if ("204" !== id && "645" !== id && "646" !== id && "644" !== id && selectedAddOnID !== userAddOnId) {
                var maxCount = Number(value) + Number(StoreProperties.service.getMaximumAddonCount(id));
                $(".multiAddonSelection select.multiaddon_clEnumPrice[addon=" + id + "] option:gt(" + maxCount + ")").hide(), 0 == maxCount && $(".multiAddonSelection select.multiaddon_clEnumPrice[addon=" + id + "]").attr("disabled", !0)
            }
        }
        $(".multiAddonSelection .selectpicker").selectpicker("refresh")
    }
}

function setPurchasedAddOnCount() {
    if ($(".multiAddonReduceSelection select.multiaddon_clEnumPrice option").show(), 2 === StoreProperties.serviceId) {
        for (var enumList = $(".multiAddonReduceSelection select.multiaddon_clEnumPrice"), userAddOnId = StoreProperties.plans && StoreProperties.plans.usersMap ? StoreProperties.plans.usersMap[StoreProperties.selectedPlan.plan] : null, i = 0; i < enumList.length; i++) {
            var id = $(enumList[i]).attr("addon"),
                count = parseInt(StoreProperties.userPlan[id]);
            if (count)
                if ("204" === id) $(".multiAddonReduceSelection select.multiaddon_clEnumPrice[addon=" + id + "] option:gt(" + count / 5 + ")").hide();
                else if (userAddOnId && parseInt(userAddOnId) === parseInt(id)) {
                var minimumVal = void 0 !== $(enumList[i]).attr("minuser") && "" !== $(enumList[i]).attr("minuser") ? $(enumList[i]).attr("minuser") : 0;
                count -= minimumVal, $(".multiAddonReduceSelection select.multiaddon_clEnumPrice[addon=" + id + "] option:gt(" + count + ")").hide()
            } else count += 1, $(".multiAddonReduceSelection select.multiaddon_clEnumPrice[addon=" + id + "] option:gt(" + count + ")").hide()
        }
        $(".multiAddonReduceSelection .selectpicker").selectpicker("refresh")
    }
}

function resetMultiAddonForm() {
    $("#multiaddon_clEnumPrice").prop("selectedIndex", 0), $("input.multiaddon_clUnitPrice").val(""), $(".multiAddonReduceSelection .selectpicker").selectpicker("refresh")
}

function multiAddonAddReduce(fields) {
    displayFreezeDiv(), $(".freezeDiv"), $("html, body").animate({
        scrollTop: 80
    }, 100), setPerAddonPrice(), $(".multiAddonContainer").show(), setTimeout(function() {
        $("input.multiaddon_clUnitPrice").focus()
    }, 100), $(".multiAddonReduceSelection .selectpicker").selectpicker({
        selectedText: "cat"
    }), $(".changeAddon span,.changeAddon a").live("click", function() {
        if ($("#multiAddonPopup .servContainer").hide(), scrollToDiv("multiaddon-manage-header", 5, 300), $(this).hasClass("reduce_")) $(".multiAddonSelection,#hconfClose").hide(), $(".multiAddonReduceSelection").show(), setPurchasedAddOnCount(), $(".confTitle span").text(i18n["crm.subscription.reduce"]), $(".confTitleMsg").text(i18n["store.reduceSubscription.hint"]);
        else if ($(this).hasClass("add_")) {
            $("#multiAddonPopup").find(".panel").addClass("flippanel"), $(".multiAddonReduceSelection").hide(), $(".multiAddonSelection").show(), setMaximumOptionInDropDown(), $(".confTitle span").text("Add"), $(".confTitleMsg").text(i18n["store.addSubscription.hint"]), $(".curr" + fields).show(), $(".newPlanAmt").html(formatAmount(StoreProperties.selectedPlan.recurringDue));
            var freeStoragePerUser = $(".freeStoragePerUser").text(),
                freeStorageUnit = $(".freeStorageUnit").text();
            "" !== freeStoragePerUser ? $(".total-freeStorage-container .total-freeStorage").text("Free storage available: " + freeStoragePerUser + " " + freeStorageUnit + " /user") : $(".total-freeStorage-container").hide()
        }
    }), resetMultiAddonPopup()
}

function validateMultiAddons(element, mode) {
    var clEnumPrice;
    "add" === mode ? clEnumPrice = $(".multiAddonSelection select.multiaddon_clEnumPrice") : "reduce" === mode && (clEnumPrice = $(".multiAddonReduceSelection select.multiaddon_clEnumPrice"));
    var result = !1;
    element.hasClass("multiaddon_clSubPrice") ? result = element.is(":checked") : element.hasClass("multiaddon_clUnitPrice") ? result = "" !== element.val() ? !0 : !1 : element.hasClass("multiaddon_clEnumPrice") && clEnumPrice.each(function() {
        $(this).val() > 0 && (result = !0)
    }), result ? $(".reviewPay,.downgradeGyBtn").removeAttr("disabled").removeClass("disabledReview peNone") : $(".reviewPay,.downgradeGyBtn").attr("disabled", "disabled").addClass("disabledReview peNone")
}

function setTabPanel(tabPanelType, panel) {
    "tab-panel-1" === tabPanelType ? $(panel).parent("ul").siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current") : "tab-panel-2" === tabPanelType && $(panel).parent("ul").siblings("#store-tab-panel-2").addClass("current").siblings("#store-tab-panel-1").removeClass("current")
}

function resetPaymentMethod(clickedTabVal) {
    clickedTabVal = clickedTabVal.trim(), "Credit card" === clickedTabVal ? ($(".changeCard").find("input").removeClass("invalidEntry"), $(".ccConfirmFooter").find(".primaryButton").val(i18n["store.admin.update"]).html(i18n["store.admin.update"]).removeClass("payPalContinuebtn")) : "PayPal" == clickedTabVal && $(".ccConfirmFooter").find(".primaryButton").val(i18n["store.continue"]).html(i18n["store.continue"]).addClass("payPalContinuebtn")
}

function cancelPMChange() {
    var updateOnly = sessionStorage.getItem("updatePaymentMethod");
    if (void 0 !== updateOnly && "open" === updateOnly) {
        sessionStorage.removeItem("updatePaymentMethod");
        var backUrl = localStorage.getItem("backUrl");
        localStorage.removeItem("backUrl"), null !== backUrl && (location.href = backUrl)
    } else {
        $(".cErrMsg,#showInlineErrorMsg").hide(), $("label[for='3dsecure']").removeClass("labelfocus");
        var parentTab = $(".view-subscribers-payment-details");
        $(".view-subscribers-payment-details").hide(), $(".freezeDiv").hide(), $(".ccConfirmFooter").find(".primaryButton").val(i18n["store.admin.update"]), parentTab.find("#store-tab-panel-1,#store-tab-panel-2").removeClass("current"), parentTab.find("#store-tab-panel-1,#store-tab-panel-2").removeClass("current"), parentTab.find("ul.store-tabs-controller").find("li:nth-child(2),li:nth-child(3)").remove(), resetDetails(parentTab.find("#store-tab-panel-1")), location.hash = ""
    }
}

function cancelAdrDetails(parentPage) {
    var updateOnly = sessionStorage.getItem("updatePaymentMethod");
    if (void 0 !== updateOnly && "open" === updateOnly && parentPage) {
        sessionStorage.removeItem("updatePaymentMethod");
        var backUrl = localStorage.getItem("backUrl");
        localStorage.removeItem("backUrl"), null !== backUrl && (location.href = backUrl)
    } else {
        $(".billing-address-box,.subscribers-biliing-address").slideDown(50), $(".changeAdrs").slideUp(200);
        var parentTab = $("#invoice-billing-address");
        resetDetails(parentTab)
    }
}

function closeViewSubcripiton() {
    var tabHolderPP = $("#py-model-3-container .subscribed-pro-listing-container"),
        tabHolderCC = $("#py-model-2-container .subscribed-pro-listing-container");
    $("#py-model-2-container,#py-model-3-container").hide(), $(".freezeDiv").hide(), tabHolderPP.find("#store-tab-panel-1,#store-tab-panel-2").html("").removeClass("current"), tabHolderCC.find("#store-tab-panel-1,#store-tab-panel-2").html("").removeClass("current"), $(".subscribed-pro-listing-container").find("ul.store-tabs-controller").find("li:nth-child(2),li:nth-child(3)").remove()
}

function constructViewSubscritionPage(paymentType, paymentContainer, cardIndex, currentCard) {
    var table1, table2 = "",
        table1 = "<table class='subscribed-pro-listing-table monthlySeevices'><tbody>",
        table2 = "<table class='subscribed-pro-listing-table yearlySeevices'><tbody>",
        serviceList = currentCard[cardIndex].service_details,
        yearlySubs = !1,
        monthlySubs = !1;
    if (serviceList.length > 0)
        for (var c in serviceList) {
            var serviceName = serviceList[c].service_name,
                dipServiceName = serviceList[c].service_display_name,
                imgPath = "/images/store/",
                localPayPeriod = serviceList[c].payperiod;
            "MONT" === localPayPeriod ? (monthlySubs = !0, table1 += "<tr><td>", table1 += "<span class='product-img-holder'>", table1 += "<img width=40' alt=" + serviceName + " src=" + imgPath + serviceName.toLowerCase() + "-m.png></span></td>", table1 += "<td><div class='fl'><ul><li><span class='subscribed-pro-name'>" + dipServiceName + "</span></li>", table1 += "<li><span class='store-label-control subscribed-pro-detail'>" + serviceList[c].planname + "-" + serviceList[c].addon_string + "</span></li></ul></div>", table1 += "&#36;" == currentCard[cardIndex].currency_symbol ? "<div class='subscribed-pro-price'><span style='font-family:lato' class='dollor-symbol'>" + currentCard[cardIndex].currency_symbol + "</span>" + serviceList[c].next_payment_amount + "</div></td></tr>" : "<div class='subscribed-pro-price'><span class='dollor-symbol'>" + currentCard[cardIndex].currency_symbol + "</span>" + serviceList[c].next_payment_amount + "</div></td></tr>") : "YEAR" === localPayPeriod && (yearlySubs = !0, table2 += "<tr><td>", table2 += "<span class='product-img-holder'>", table2 += "<img width=40' alt=" + serviceName + " src=" + imgPath + serviceName.toLowerCase() + "-m.png></span></td>", table2 += "<td><div class='fl'><ul><li><span class='subscribed-pro-name'>" + dipServiceName + "</span></li>", table2 += "<li><span class='store-label-control subscribed-pro-detail'>" + serviceList[c].planname + "-" + serviceList[c].addon_string + "</span></li></ul></div>", table2 += "&#36;" == currentCard[cardIndex].currency_symbol ? "<div class='subscribed-pro-price'><span style='font-family:lato' class='dollor-symbol'>" + currentCard[cardIndex].currency_symbol + "</span>" + serviceList[c].next_payment_amount + "</div></td>" : "<div class='subscribed-pro-price'><span class='dollor-symbol'>" + currentCard[cardIndex].currency_symbol + "</span>" + serviceList[c].next_payment_amount + "</div></td>")
        }
    table1 += "</tbody></table>", table2 += "</tbody></table>", paymentContainer.find("#store-tab-panel-1").html(table1).siblings("#store-tab-panel-2").html(table2), constructTab(paymentType, monthlySubs, yearlySubs)
}

function constructTab(paymentType, monthlySubs, yearlySubs) {
    var tabHolderPP = $("#py-model-3-container .subscribed-pro-listing-container").find("ul.store-tabs-controller"),
        tabHolderCC = $("#py-model-2-container .subscribed-pro-listing-container").find("ul.store-tabs-controller");
    yearlySubs && !monthlySubs ? "credit_card" === paymentType ? (tabHolderCC.append("<li><span>" + i18n["store.payPeriod.YEAR"] + "</span></li>"), tabHolderCC.siblings("#store-tab-panel-2").addClass("current").siblings("#store-tab-panel-1").removeClass("current")) : "PayPal" === paymentType && (tabHolderPP.append("<li><span>" + i18n["store.payPeriod.YEAR"] + "</span></li>"), tabHolderPP.siblings("#store-tab-panel-2").addClass("current").siblings("#store-tab-panel-1").removeClass("current")) : !yearlySubs && monthlySubs ? "credit_card" === paymentType ? (tabHolderCC.append("<li><span>" + i18n["store.payPeriod.MONT"] + "</span></li>"), tabHolderCC.siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current")) : "PayPal" === paymentType && (tabHolderPP.append("<li><span>" + i18n["store.payPeriod.MONT"] + "</span></li>"), tabHolderPP.siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current")) : monthlySubs && yearlySubs && ("credit_card" === paymentType ? (tabHolderCC.append("<li class='tab-controller-link current' data-tab='tab-panel-1'>" + i18n["store.payPeriod.MONT"] + "</li>"), tabHolderCC.append("<li class='tab-controller-link' data-tab='tab-panel-2'>" + i18n["store.payPeriod.YEAR"] + "</li>"), tabHolderCC.siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current")) : "PayPal" === paymentType && (tabHolderPP.append("<li class='tab-controller-link current' data-tab='tab-panel-1'>" + i18n["store.payPeriod.MONT"] + "</li>"), tabHolderPP.append("<li class='tab-controller-link' data-tab='tab-panel-2'>" + i18n["store.payPeriod.YEAR"] + "</li>"), tabHolderPP.siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current")))
}

function constructPaymentTab(paymentSwitchOpt) {
    var parentTab = $(".view-subscribers-payment-details .store-tabs-controller");
    paymentSwitchOpt.CREDITCARD && paymentSwitchOpt.PAYPAL_EXPRESSCHECKOUT ? (parentTab.append("<li class='tab-controller-link current' data-tab='tab-panel-1'>" + i18n["zohostore.manageplan.addon.creditcard"] + "</li>"), parentTab.append("<li class='tab-controller-link' data-tab='tab-panel-2'>" + i18n["zohostore.manageplan.addon.paypaltext"] + "</li>"), parentTab.siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current")) : paymentSwitchOpt.CREDITCARD ? (parentTab.append("<li><span>" + i18n["zohostore.manageplan.addon.creditcard"] + "</span></li>"), parentTab.siblings("#store-tab-panel-1").addClass("current").siblings("#store-tab-panel-2").removeClass("current"), $(".ccConfirmFooter").find(".primaryButton").val(i18n["store.admin.update"]).html(i18n["store.admin.update"]).removeClass("payPalContinuebtn"), parentTab.hide()) : paymentSwitchOpt.PAYPAL_EXPRESSCHECKOUT && (parentTab.append("<li><span>" + i18n["zohostore.manageplan.addon.paypaltext"] + "</span></li>"), parentTab.siblings("#store-tab-panel-2").addClass("current").siblings("#store-tab-panel-1").removeClass("current"), $(".ccConfirmFooter").find(".primaryButton").val(i18n["store.continue"]).html(i18n["store.continue"]).addClass("payPalContinuebtn"), parentTab.hide()), alignPopContLeft($(".view-subscribers-payment-details")), $(".view-subscribers-payment-details").css("top", "32px"), paymentSwitchOpt.CREDITCARD && paymentSwitchOpt.PAYER_AUTHENTICATION_AGREEMENT ? $("#payer-authentication-container").show() : $("#payer-authentication-container").hide()
}

function setDefaultDetails(element, details) {
    if ("credit_card" === element.billing_type) {
        var cardDiv = $("#cardTable");
        cardDiv.find("select[name=cardCountry]").val(element.country), $(".country").html(element.country), "United States" === element.country ? ($(".notUsCard,.notUsCardstInf").hide(), $(".usCardTd,.usCardstInf").show().find("span.cardStateSel").css("display", "inline-block"), cardDiv.find(".usStreetAddress").val(element.address), $(".streetAddress").html(element.address), cardDiv.find("select[name=cc_card_state]").val(element.state), $(".state").html(element.state), cardDiv.find("select").trigger("change"), cardDiv.find(".usCardCity").val(element.city), $(".city").html(element.city), cardDiv.find(".usCardZCode").val(element.zip_code), $(".zipCode").html(element.zip_code)) : ("India" === element.country ? (cardDiv.find("span.indStates").show(), cardDiv.find(".cardStateInp").hide(), cardDiv.find("select[name=cardState].indStates").val(element.state), $(".state").html(element.state)) : (cardDiv.find("span.indStates").hide(), cardDiv.find(".cardStateInp").show()), cardDiv.find(".notUsCard,.notUsCardstInf").show(), cardDiv.find(".usCardTd,.usCardstInf").hide().find("span.cardStateSel").css("display", "inline-block"), cardDiv.find(".billAddress").val(element.address), $(".streetAddress").html(element.address), cardDiv.find(".cardCity").val(element.city), $(".city").html(element.city), cardDiv.find(".cardStateInp").val(element.state), $(".state").html(element.state), cardDiv.find(".cardZCode").val(element.zip_code), $(".zipCode").html(element.zip_code))
    } else if ("billingDetails" === element) {
        var billingDetails = StoreProperties.paymentdetails.billing_details,
            changeAddressDiv = $("#invoice-billing-address");
        changeAddressDiv.find("select[name=country]").val(billingDetails.country), changeAddressDiv.find(".phoneNo").val(billingDetails.phone), billingDetails.zip_code = void 0 !== billingDetails.zip_code ? billingDetails.zip_code : billingDetails.zipcode, changeAddressDiv.find("#companyname").val(billingDetails.invoice_billingName), "United States" === billingDetails.country ? ($(".notUsCard,.notUsCardstInf").hide(), $(".usCardTd,.usCardstInf").show().find("span.stateSel").css("display", "inline-block"), changeAddressDiv.find(".usStAddress").val(billingDetails.address), changeAddressDiv.find("select[name=card_state]").val(billingDetails.state), changeAddressDiv.find("select").trigger("change"), changeAddressDiv.find(".usCity").val(billingDetails.city), changeAddressDiv.find(".suite").val(billingDetails.apt_suite), changeAddressDiv.find(".usZCode").val(billingDetails.zip_code)) : ("India" === billingDetails.country ? (changeAddressDiv.find("span.indStates").show(), changeAddressDiv.find(".stateInp").hide(), changeAddressDiv.find("select[name=cardState].indStates").val(billingDetails.state), changeAddressDiv.find(".usCardTd,.usCardstInf").hide()) : (changeAddressDiv.find("span.indStates").hide(), changeAddressDiv.find(".stateInp").show()), changeAddressDiv.find(".notUsCard,.notUsCardstInf").show(), changeAddressDiv.find(".usCardTd,.usCardstInf").hide().find("span.stateSel").css("display", "inline-block"), changeAddressDiv.find(".stAddress").val(billingDetails.address), changeAddressDiv.find(".city").val(billingDetails.city), changeAddressDiv.find(".stateInp").val(billingDetails.state), changeAddressDiv.find(".zCode").val(billingDetails.zip_code))
    } else if ("billingAddress" === element) {
        StoreProperties.paymentdetails.billing_details = details, StoreProperties.paymentdetails.billing_details.invoice_billingName = details.invoice_billingName, details.suite && (StoreProperties.paymentdetails.billing_details.apt_suite = details.suite);
        var billingAddress = details,
            AddressDiv = $(".dispAdrs");
        AddressDiv.find("#companyName").html(billingAddress.invoice_billingName), AddressDiv.find("#billingAddress").html(void 0 !== billingAddress.suite && "" !== billingAddress.suite ? billingAddress.address + " " + billingAddress.suite : billingAddress.address), AddressDiv.find("#city").html(billingAddress.city), AddressDiv.find("#state").html(billingAddress.state), AddressDiv.find("#zipCode").html(billingAddress.zipcode), AddressDiv.find("#country").html(billingAddress.country), AddressDiv.find("#phone").html(billingAddress.phone)
    }
    $("select").trigger("change")
}

function resetDetails(parentTab) {
    parentTab.find("input,textarea").val(""), parentTab.find($("select").prop("selectedIndex", 0)), $(".selectpicker").selectpicker("refresh"), $(".notUsCard,.notUsCardstInf").show(), $(".usCardTd,.usCardstInf").hide().find("div.cardStateSel").css("display", "inline-block")
}

function getPaymentTab() {
    var paymentTab = "";
    return $(".payment-method").each(function() {
        return $(this).is(":visible") ? paymentTab = " #" + $(this).attr("id") : void 0
    }), paymentTab
}

function isMandatory(addonId) {
    for (var planId = StoreProperties.selectedPlan.plan, mandatory = !1, addons = Object.keys(StoreProperties.plans[planId].addons), i = 0; i < addons.length; i++)
        if (parseInt(StoreProperties.plans[planId].addons[addons[i]].addon_id) === parseInt(addonId)) {
            mandatory = StoreProperties.plans[planId].addons[addons[i]].mandatory ? StoreProperties.plans[planId].addons[addons[i]].mandatory : !1;
            break
        }
    return mandatory
}

function getAddOnDisplayName(addonId) {
    for (var planId = StoreProperties.selectedPlan.plan, addonName = "", addons = Object.keys(StoreProperties.plans[planId].addons), i = 0; i < addons.length; i++)
        if (parseInt(StoreProperties.plans[planId].addons[addons[i]].addon_id) === parseInt(addonId)) {
            addonName = StoreProperties.plans[planId].addons[addons[i]].displayName ? i18n[StoreProperties.plans[planId].addons[addons[i]].displayName] : !1;
            break
        }
    return addonName
}

function populateSubsDetail(subscriptionPage, mode, subscription) {
    try {
        if (subscriptionPage) {
            if (subscription.addons && parseInt(subscription.plan) === parseInt(StoreProperties.userPlan.plan) && subscription.payPeriod === StoreProperties.userPlan.payPeriod)
                for (var addonid in subscription.addons) {
                    if (subscription.addons[addonid]) {
                        var addonRow = $('.addOnTable tr[rows="' + addonid + '"]');
                        addonRow && $(addonRow).is(":visible") ? ($(addonRow).addClass("highlightBG animateBG").trigger("mouseenter"), setTimeout(function() {
                            $(addonRow).removeClass("animateBG"), $('div[popCont="' + addonid + '"]').trigger("mouseenter")
                        }, 2500)) : (addonRow = $('.addOnTable tr[rows] .enumRow [addonid="' + addonid + '"]'), addonRow && $(addonRow).closest("tr").is(":visible") && ($(addonRow).closest("tr").addClass("highlightBG animateBG").trigger("mouseenter"), setTimeout(function() {
                            $(addonRow).closest("tr").removeClass("animateBG"), $('div[popCont="' + $(addonRow).closest("tr").attr("rows") + '"]').trigger("mouseenter")
                        }, 2500)))
                    }
                    break
                }
        } else {
            var currency, plansType = StoreProperties.renderingJSON.planTypes[0].type;
            subscription.currency ? currency = subscription.currency : subscription.currencyId && (currency = StoreUtil.getCurrencyCode(subscription.currencyId)), currency && StoreProperties.supportedCurrencies.length > 1 && -1 !== StoreProperties.supportedCurrencies.indexOf(currency) && ("OA" === plansType ? $(".planDetailsCont .currencycontainer .taggleDiv[currency=" + currency + "]").trigger("click") : $(".planTitleDiv .currencycontainer .taggleDiv[currency=" + currency + "]").trigger("click")), subscription.secondary_planid && $(".selectPlan .secondaryLicense input[secondary_planid=" + subscription.secondary_planid + "]").trigger("click");
            var priceSwitch = $("#payperiodChange").is(":visible");
            priceSwitch && ("MONT" === subscription.payPeriod || 1 === subscription.frequency ? ($(".priceSwitching").removeClass("active"), $(".priceSwitching.nsMonth").trigger("mousedown")) : ("YEAR" === subscription.payPeriod || 4 === subscription.frequency) && ($(".priceSwitching").removeClass("active"), $(".priceSwitching.nsYear").trigger("mousedown")));
            var planid = subscription.plan ? subscription.plan : subscription.planid;
            planid = subscription.bugsId ? subscription.bugsId : planid;
            var planElem = $(subscription.secondary_planid ? ".upgradeBtn[secondary_planid=" + subscription.secondary_planid + "]" : ".upgradeBtn[planid=" + planid + "]");
            (planElem.length > 0 || "OA" === plansType) && ($(planElem).trigger("click"), priceSwitch || ("MONT" === subscription.payPeriod || 1 === subscription.frequency ? ($(".payperiodDiv input[name=duration]").prop("checked", !1), $(".payperiodDiv #monthly").trigger("click")) : ("YEAR" === subscription.payPeriod || 4 === subscription.frequency) && ($(".payperiodDiv input[name=duration]").prop("checked", !1), $(".payperiodDiv #yearly").trigger("click"))), (("FROMPRODUCT" === mode || "GRANNYUPGRADE" === mode) && subscription.addons || "TRANSACTIONFAILURE" === mode) && $(".clSubPrice,.clEnumPrice,input.addons").each(function(i, elem) {
                var addonid = $(elem).attr("addonid"),
                    elemType = $(elem).prop("type");
                if ("select-one" === elemType && ($(elem).is(":visible") || $("button[data-id=" + $(elem).attr("id") + "]").is(":visible")) && "false" === $(elem).attr("sameprice")) $("select#" + $(elem).attr("id") + " option").each(function(j, option) {
                    var option_addonid = $(option).attr("addonid");
                    if (option_addonid) {
                        var addonCount;
                        "FROMPRODUCT" !== mode && "GRANNYUPGRADE" !== mode || !subscription.addons[option_addonid] ? "TRANSACTIONFAILURE" === mode && subscription[option_addonid] && (addonCount = subscription[option_addonid]) : addonCount = subscription.addons[option_addonid], addonCount && ($("select#" + $(elem).attr("id") + " option[addonid=" + option_addonid + "]").attr("selected", "selected"), $("select#" + $(elem).attr("id")).trigger("change"))
                    }
                });
                else {
                    var addonCount;
                    "FROMPRODUCT" !== mode && "GRANNYUPGRADE" !== mode || !subscription.addons[addonid] ? "TRANSACTIONFAILURE" === mode && subscription[addonid] && (addonCount = subscription[addonid]) : addonCount = subscription.addons[addonid], addonCount && ("select-one" === elemType && ($(elem).is(":visible") || $("button[data-id=" + $(elem).attr("id") + "]").is(":visible")) ? ($("select#" + $(elem).attr("id") + " option[value=" + addonCount + "]").attr("selected", "selected"), $("select#" + $(elem).attr("id")).trigger("change")) : "text" === elemType && $(elem).is(":visible") ? $(elem).val(addonCount).trigger("keyup") : "checkbox" === elemType && $(elem).next("label").is(":visible") && $(elem).prop("checked", !0).trigger("change"))
                }
            }), $(".plOrder").trigger("click"), "TRANSACTIONFAILURE" === mode && ($(".confirmContainer .confirmOrder").trigger("click"), $(".payment-menu[tab=paypalContainer]").trigger("click"))), setPopulateSubsAction()
        }
    } catch (e2) {} finally {
        $(".freezeDiv,.loadingMsg,.loadingBG").hide(), $(".loadingMsg").removeAttr("style")
    }
}

function setPopulateSubsAction(action) {
    if ("samepricing" === action && ($(".freezeDiv,.loadingMsg,.loadingBG").hide(), $(".loadingMsg").removeAttr("style")), StoreProperties.populateSubscription_RecurringFailure) {
        var switchAction, href = location.href,
            subscriptionId = getUrlParamValue(href, "subscriptionId");
        subscriptionId && StoreProperties.populateSubscription && StoreProperties.populateSubscription.id === StoreProperties.populateSubscription.id ? (switchAction = "old", href = changeUrlParamValue(href, "subscriptionId")) : subscriptionId && StoreProperties.populateSubscription && StoreProperties.populateSubscription.id === StoreProperties.populateSubscription_RecurringFailure.id ? (switchAction = "new", href = changeUrlParamValue(href, "subscriptionId", StoreProperties.populateSubscription.encryptedId)) : (switchAction = "new", href = changeUrlParamValue(href, "subscriptionId", StoreProperties.populateSubscription_RecurringFailure.encryptedId)), "new" === switchAction ? ($(".planTitleDiv .planEditBtn").hide(), $(".orderSumryHdr #oldEditionInfo").remove(), $(".orderSumryHdr").append('<span id="oldEditionInfo" style="font-weight: 300; font-size: 14px;"> ( Your Subscription Details before expiry )</span>'), $("#grannySwitch .chCont").html(i18n["zohostore.newsubscription.switchnewplan"])) : ($(".orderSumryHdr #oldEditionInfo").remove(), $("#grannySwitch .chCont").html(i18n["zohostore.actionmsg.reactivateOldEdition"])), $("#grannySwitch").attr("switchurl", href).removeClass("hide")
    }
    StoreProperties.renderingJSON.denyPopulatedPlanChange && $(".planTitleDiv .planEditBtn").hide()
}

function animateFeatures() {
    $(".highlightBG").addClass("animateBG"), animateBG = setTimeout(function() {
        $(".highlightBG").removeClass("animateBG")
    }, 3e3)
}

function billingAddressView() {
    var billingAddress = StoreProperties.paymentdetails.billing_details,
        $billingDetails = $(".billing-address-detail");
    billingAddress.apt_suite && (aptSuite = billingAddress.apt_suite), $billingDetails.find(".streetAddress").html(void 0 !== billingAddress.apt_suite && "" !== billingAddress.apt_suite ? billingAddress.address + "  " + aptSuite : billingAddress.address), $billingDetails.find(".companyName").text(billingAddress.invoice_billingName), $billingDetails.find(".phoneNo").text(" " + billingAddress.phone), $billingDetails.find(".city").html(billingAddress.city), $billingDetails.find(".state").html(billingAddress.state), $billingDetails.find(".zipCode").html(billingAddress.zipcode), $billingDetails.find(".country").html(billingAddress.country)
}

function staticPage() {
    
}

function togglePaymentDetails(action) {
    return false;
    "hide" === action && ($(".cardSelection,.payment-details-menu").hide(), $(".cardSelection .ccardTbl").removeClass("pT25"), $(".subscriptionGBtn").attr("selectedcard", "newcard"), $("#cardTable,#billAddrSplit,#billAddrTable").slideDown(300), $("div.ccDetailsDiv,.cardSelection").hide())
}

function setCurrencyPicker(action) {
    action ? StoreProperties.multicurrency && StoreProperties.supportedCurrencies && StoreProperties.supportedCurrencies.length > 1 ? "select-plan" === action ? $(".planTitleDiv .currencycontainer").show() : "plan-details" === action && StoreProperties.renderingJSON.signUpFlow ? $(".planDetailHd .currencycontainer").show() : "plan-details" === action && "OA" === StoreProperties.renderingJSON.planTypes[0].type && $(".planDetailsCont .currencycontainer").show() : $(".currencycontainer").hide() : StoreProperties.multicurrency && StoreProperties.supportedCurrencies && StoreProperties.supportedCurrencies.length > 1 ? StoreProperties.renderingJSON.signUpFlow ? $(".planDetailHd .currencycontainer").show() : "OA" === StoreProperties.renderingJSON.planTypes[0].type ? $(".planDetailsCont .currencycontainer").show() : $(".planTitleDiv .currencycontainer").show() : $(".currencycontainer").hide()
}

function planDetailsOptionSkip() {
    if (StoreProperties.renderingJSON.noPlanDetails) {
        var stepDiv = $(".planDetailHd").nextAll(".subCDiv");
        $(stepDiv).each(function() {
            var planNoObj = $(this).find(".planNos");
            $(planNoObj).text($(planNoObj).attr("stepno") - 1)
        }), $(".planDetailHd,.planDetails").remove()
    }
}

function paymentOptionSkip() {
    StoreProperties.userPlan && StoreProperties.userPlan.recurringDue > 0 && ($(".paymentDetails,.ccardDiv").removeClass("hide").addClass("hide").remove(), $(".confirmContainer .placeOrder1").val(i18n["zohostore.newsubscription.makepayment"]).attr("id", "placeOrder").removeClass("confirmOrder"), $(".currencycontainer").remove(), $(".paymentDuration").hide())
}

function onlyAddonTypeOnLoad() {
    $(".planTitleDiv,.editionCont,.sitesHide").remove(), $(".planNos").each(function() {
        $(this).text($(this).attr("stepno") - 1)
    }), $(".websiteUrl").show().text(StoreProperties.licenseInfo.companyname), $(".planDetails").show(), $(".planDetailHd .hwinAc").text(i18n["sites.plandetails.label"]), addOnPage();
    var payPeriod = StoreProperties.dynamic_pricing ? "MONT" === StoreProperties.dynamic_pricing ? "monthly" : "yearly" : "monthly";
    $(".payperiodDiv #" + payPeriod).prop("checked", !0), confirmPage(), $(".onlyAddonPayPeriodSwitch").removeClass("hide"), $(".clEnumPrice").selectpicker()
}

function EnumRowUpdate(addOn, period) {
    var addOnID = addOn,
        $addonList = $("select.clEnumPrice[addonid=" + addOnID + "]");
    calculatePrice($addonList.attr("name"), "totalToPay");
    var enumList = $addonList.find("option");
    period = null !== period && "monthly" === period.toLowerCase() ? "month" : "year";
    var optionPrice = 0;
    for (i = 1; i < enumList.length; i++) {
        var getElement = enumList.filter(":eq(" + i + ")");
        "true" === $addonList.attr("sameprice") ? 0 != getElement.attr("name") && (addOnPrice = null !== period && "month" === period.toLowerCase() ? StoreProperties.pricing[StoreProperties.currencyCode].MONT[addOnID] : StoreProperties.pricing[StoreProperties.currencyCode].YEAR[addOnID], optionPrice = getElement.attr("name") * addOnPrice) : (addOnPrice = null !== period && "month" === period.toLowerCase() ? StoreProperties.pricing[StoreProperties.currencyCode].MONT[getElement.attr("addonid")] : StoreProperties.pricing[StoreProperties.currencyCode].YEAR[getElement.attr("addonid")], optionPrice = addOnPrice);
        var currencySymbol = getCurrencySymbol(StoreProperties.currencyCode);
        getElement.data("subtext", "<span class='currencySign'>" + currencySymbol + "</span><span class='enumPrice'>" + formatAmount(optionPrice, !0) + "</span> /<span class='payPeriod'>" + period + "</span>")
    }
    $addonList.selectpicker("refresh")
}

function changeInvoiceAddressOption() {
    var $billingAddress = $(".invoice-billing-address");
    if ($("#card-address,#paypal-address").is(":checked")) $billingAddress.slideUp();
    else {
        $billingAddress.slideDown();
        var billingCountry = $billingAddress.find("selectCountry").val();
        "United States" == billingCountry ? ($(".notUsCard,.notUsCardstInf").hide(), $(".usCardTd,.usCardstInf").show().find("div.stateSel").css("display", "inline-block")) : ($(".notUsCard,.notUsCardstInf").show(), $(".usCardTd,.usCardstInf").hide().find("div.stateSel").css("display", "inline-block"))
    }
}

function blockNotificationMsg(appendTo, message) {
    var container = "<div class='block-notification-container'>" + message + "</div>";
    $(container).prependTo(appendTo).hide().fadeTo(100, 1).slideDown(50)
}

function getIPInfo() {
    var ipLocationUrl = StoreProperties.ipLocationUrl;
    ipLocationUrl = -1 !== ipLocationUrl.indexOf("https") ? ipLocationUrl : ipLocationUrl.replace("http", "https"), $.ajax({
        url: ipLocationUrl + "/getipinfo?type=jsonp&callback=ipdetails",
        jsonp: "callback",
        dataType: "jsonp",
        data: {
            format: "json"
        },
        success: function(response) {
            response && (StoreProperties.ipInfo = response, populateIPInfo(StoreProperties.ipInfo))
        },
        fail: function() {
            return null
        }
    })
}

function populateIPInfo(ipInfo) {
   
}

function checkInvoiceBillingAddress(ele) {
    var billingDetails = StoreProperties.renderingJSON.billing_details;
    null !== billingDetails && void 0 !== billingDetails ? ($(".invoiceNote").show(), $("#" + ele).hide(), "paypal-billAddrTable" === ele && $(".alreadyInvicedPaypalMessage").show().siblings(".newcardPaypalMessage").hide()) : ($(".invoiceNote").hide(), $("#" + ele).show(), "paypal-billAddrTable" === ele && $(".newcardPaypalMessage").show().siblings(".alreadyInvicedPaypalMessage").hide())
}

function getCardType() {
    var cardTypeNo = 1;
    return void 0 !== StoreProperties.renderingJSON.resellerFlow && StoreProperties.renderingJSON.resellerFlow === !0 && (cardTypeNo = $("#resellercard").is(":checked") ? 0 : 1), cardTypeNo
}

function updateUserFeature(notificationType) {
    var data = {
        type: notificationType
    };
    $.ajax({
        url: "/store/service.do?method=updateNotification",
        type: "POST",
        data: data,
        success: function(result) {
            StoreProperties.renderingJSON.notifications = result.notifications
        },
        error: function() {
            return !0
        },
        dataType: "JSON"
    })
}

function GTMConversion(service, mode, plan) {
    dataLayer.push({
        event: "gaEvent",
        service: service,
        mode: mode,
        plan: plan
    })
}

function trackClickEvent(eventName, properties) {
 return false;  
}

function AdwordsConversion(conversionId, conversionLabel) {
    $("body").append('<div style="display:inline;"><img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/' + conversionId + "/?value=1&amp;label=" + conversionLabel + '&amp;guid=ON&amp;script=0"/></div>')
}

function pushtoGA() {}

function pushTranstoGA() {}

function validate(amount) {
    var validTrans = !1,
        fomattedAmt = amount,
        validationRes = {};
    return StoreProperties.currencyId !== StoreProperties.defaultCurrencyId && amount > 0 ? fomattedAmt = StoreUtil.getFormattedAmount(amount) : validTrans = !0, validationRes.valid = validTrans, validationRes.amount = fomattedAmt, validationRes
}

function parseBigInt(str, r) {
    return new BigInteger(str, r)
}

function linebrk(s, n) {
    for (var ret = "", i = 0; i + n < s.length;) ret += s.substring(i, i + n) + "\n", i += n;
    return ret + s.substring(i, s.length)
}

function byte2Hex(b) {
    return 16 > b ? "0" + b.toString(16) : b.toString(16)
}

function pkcs1pad2(s, n) {
    if (n < s.length + 11) return alert("Message too long for RSA"), null;
    for (var ba = new Array, i = s.length - 1; i >= 0 && n > 0;) {
        var c = s.charCodeAt(i--);
        128 > c ? ba[--n] = c : c > 127 && 2048 > c ? (ba[--n] = 63 & c | 128, ba[--n] = c >> 6 | 192) : (ba[--n] = 63 & c | 128, ba[--n] = c >> 6 & 63 | 128, ba[--n] = c >> 12 | 224)
    }
    ba[--n] = 0;
    for (var rng = new SecureRandom, x = new Array; n > 2;) {
        for (x[0] = 0; 0 == x[0];) rng.nextBytes(x);
        ba[--n] = x[0]
    }
    return ba[--n] = 2, ba[--n] = 0, new BigInteger(ba)
}

function RSAKey() {
    this.n = null, this.e = 0, this.d = null, this.p = null, this.q = null, this.dmp1 = null, this.dmq1 = null, this.coeff = null
}

function RSASetPublic(N, E) {
    null != N && null != E && N.length > 0 && E.length > 0 ? (this.n = parseBigInt(N, 16), this.e = parseInt(E, 16)) : alert("Invalid RSA public key")
}

function RSADoPublic(x) {
    return x.modPowInt(this.e, this.n)
}

function RSAEncrypt(text) {
    var m = pkcs1pad2(text, this.n.bitLength() + 7 >> 3);
    if (null == m) return null;
    var c = this.doPublic(m);
    if (null == c) return null;
    var h = c.toString(16);
    return 0 == (1 & h.length) ? h : "0" + h
}

function BigInteger(a, b, c) {
    null != a && ("number" == typeof a ? this.fromNumber(a, b, c) : null == b && "string" != typeof a ? this.fromString(a, 256) : this.fromString(a, b))
}

function nbi() {
    return new BigInteger(null)
}

function am1(i, x, w, j, c, n) {
    for (; --n >= 0;) {
        var v = x * this[i++] + w[j] + c;
        c = Math.floor(v / 67108864), w[j++] = 67108863 & v
    }
    return c
}

function am2(i, x, w, j, c, n) {
    for (var xl = 32767 & x, xh = x >> 15; --n >= 0;) {
        var l = 32767 & this[i],
            h = this[i++] >> 15,
            m = xh * l + h * xl;
        l = xl * l + ((32767 & m) << 15) + w[j] + (1073741823 & c), c = (l >>> 30) + (m >>> 15) + xh * h + (c >>> 30), w[j++] = 1073741823 & l
    }
    return c
}

function am3(i, x, w, j, c, n) {
    for (var xl = 16383 & x, xh = x >> 14; --n >= 0;) {
        var l = 16383 & this[i],
            h = this[i++] >> 14,
            m = xh * l + h * xl;
        l = xl * l + ((16383 & m) << 14) + w[j] + c, c = (l >> 28) + (m >> 14) + xh * h, w[j++] = 268435455 & l
    }
    return c
}

function int2char(n) {
    return BI_RM.charAt(n)
}

function intAt(s, i) {
    var c = BI_RC[s.charCodeAt(i)];
    return null == c ? -1 : c
}

function bnpCopyTo(r) {
    for (var i = this.t - 1; i >= 0; --i) r[i] = this[i];
    r.t = this.t, r.s = this.s
}

function bnpFromInt(x) {
    this.t = 1, this.s = 0 > x ? -1 : 0, x > 0 ? this[0] = x : -1 > x ? this[0] = x + DV : this.t = 0
}

function nbv(i) {
    var r = nbi();
    return r.fromInt(i), r
}

function bnpFromString(s, b) {
    var k;
    if (16 == b) k = 4;
    else if (8 == b) k = 3;
    else if (256 == b) k = 8;
    else if (2 == b) k = 1;
    else if (32 == b) k = 5;
    else {
        if (4 != b) return void this.fromRadix(s, b);
        k = 2
    }
    this.t = 0, this.s = 0;
    for (var i = s.length, mi = !1, sh = 0; --i >= 0;) {
        var x = 8 == k ? 255 & s[i] : intAt(s, i);
        0 > x ? "-" == s.charAt(i) && (mi = !0) : (mi = !1, 0 == sh ? this[this.t++] = x : sh + k > this.DB ? (this[this.t - 1] |= (x & (1 << this.DB - sh) - 1) << sh, this[this.t++] = x >> this.DB - sh) : this[this.t - 1] |= x << sh, sh += k, sh >= this.DB && (sh -= this.DB))
    }
    8 == k && 0 != (128 & s[0]) && (this.s = -1, sh > 0 && (this[this.t - 1] |= (1 << this.DB - sh) - 1 << sh)), this.clamp(), mi && BigInteger.ZERO.subTo(this, this)
}

function bnpClamp() {
    for (var c = this.s & this.DM; this.t > 0 && this[this.t - 1] == c;) --this.t
}

function bnToString(b) {
    if (this.s < 0) return "-" + this.negate().toString(b);
    var k;
    if (16 == b) k = 4;
    else if (8 == b) k = 3;
    else if (2 == b) k = 1;
    else if (32 == b) k = 5;
    else {
        if (4 != b) return this.toRadix(b);
        k = 2
    }
    var d, km = (1 << k) - 1,
        m = !1,
        r = "",
        i = this.t,
        p = this.DB - i * this.DB % k;
    if (i-- > 0)
        for (p < this.DB && (d = this[i] >> p) > 0 && (m = !0, r = int2char(d)); i >= 0;) k > p ? (d = (this[i] & (1 << p) - 1) << k - p, d |= this[--i] >> (p += this.DB - k)) : (d = this[i] >> (p -= k) & km, 0 >= p && (p += this.DB, --i)), d > 0 && (m = !0), m && (r += int2char(d));
    return m ? r : "0"
}

function bnNegate() {
    var r = nbi();
    return BigInteger.ZERO.subTo(this, r), r
}

function bnAbs() {
    return this.s < 0 ? this.negate() : this
}

function bnCompareTo(a) {
    var r = this.s - a.s;
    if (0 != r) return r;
    var i = this.t;
    if (r = i - a.t, 0 != r) return r;
    for (; --i >= 0;)
        if (0 != (r = this[i] - a[i])) return r;
    return 0
}

function nbits(x) {
    var t, r = 1;
    return 0 != (t = x >>> 16) && (x = t, r += 16), 0 != (t = x >> 8) && (x = t, r += 8), 0 != (t = x >> 4) && (x = t, r += 4), 0 != (t = x >> 2) && (x = t, r += 2), 0 != (t = x >> 1) && (x = t, r += 1), r
}

function bnBitLength() {
    return this.t <= 0 ? 0 : this.DB * (this.t - 1) + nbits(this[this.t - 1] ^ this.s & this.DM)
}

function bnpDLShiftTo(n, r) {
    var i;
    for (i = this.t - 1; i >= 0; --i) r[i + n] = this[i];
    for (i = n - 1; i >= 0; --i) r[i] = 0;
    r.t = this.t + n, r.s = this.s
}

function bnpDRShiftTo(n, r) {
    for (var i = n; i < this.t; ++i) r[i - n] = this[i];
    r.t = Math.max(this.t - n, 0), r.s = this.s
}

function bnpLShiftTo(n, r) {
    var i, bs = n % this.DB,
        cbs = this.DB - bs,
        bm = (1 << cbs) - 1,
        ds = Math.floor(n / this.DB),
        c = this.s << bs & this.DM;
    for (i = this.t - 1; i >= 0; --i) r[i + ds + 1] = this[i] >> cbs | c, c = (this[i] & bm) << bs;
    for (i = ds - 1; i >= 0; --i) r[i] = 0;
    r[ds] = c, r.t = this.t + ds + 1, r.s = this.s, r.clamp()
}

function bnpRShiftTo(n, r) {
    r.s = this.s;
    var ds = Math.floor(n / this.DB);
    if (ds >= this.t) return void(r.t = 0);
    var bs = n % this.DB,
        cbs = this.DB - bs,
        bm = (1 << bs) - 1;
    r[0] = this[ds] >> bs;
    for (var i = ds + 1; i < this.t; ++i) r[i - ds - 1] |= (this[i] & bm) << cbs, r[i - ds] = this[i] >> bs;
    bs > 0 && (r[this.t - ds - 1] |= (this.s & bm) << cbs), r.t = this.t - ds, r.clamp()
}

function bnpSubTo(a, r) {
    for (var i = 0, c = 0, m = Math.min(a.t, this.t); m > i;) c += this[i] - a[i], r[i++] = c & this.DM, c >>= this.DB;
    if (a.t < this.t) {
        for (c -= a.s; i < this.t;) c += this[i], r[i++] = c & this.DM, c >>= this.DB;
        c += this.s
    } else {
        for (c += this.s; i < a.t;) c -= a[i], r[i++] = c & this.DM, c >>= this.DB;
        c -= a.s
    }
    r.s = 0 > c ? -1 : 0, -1 > c ? r[i++] = this.DV + c : c > 0 && (r[i++] = c), r.t = i, r.clamp()
}

function bnpMultiplyTo(a, r) {
    var x = this.abs(),
        y = a.abs(),
        i = x.t;
    for (r.t = i + y.t; --i >= 0;) r[i] = 0;
    for (i = 0; i < y.t; ++i) r[i + x.t] = x.am(0, y[i], r, i, 0, x.t);
    r.s = 0, r.clamp(), this.s != a.s && BigInteger.ZERO.subTo(r, r)
}

function bnpSquareTo(r) {
    for (var x = this.abs(), i = r.t = 2 * x.t; --i >= 0;) r[i] = 0;
    for (i = 0; i < x.t - 1; ++i) {
        var c = x.am(i, x[i], r, 2 * i, 0, 1);
        (r[i + x.t] += x.am(i + 1, 2 * x[i], r, 2 * i + 1, c, x.t - i - 1)) >= x.DV && (r[i + x.t] -= x.DV, r[i + x.t + 1] = 1)
    }
    r.t > 0 && (r[r.t - 1] += x.am(i, x[i], r, 2 * i, 0, 1)), r.s = 0, r.clamp()
}

function bnpDivRemTo(m, q, r) {
    var pm = m.abs();
    if (!(pm.t <= 0)) {
        var pt = this.abs();
        if (pt.t < pm.t) return null != q && q.fromInt(0), void(null != r && this.copyTo(r));
        null == r && (r = nbi());
        var y = nbi(),
            ts = this.s,
            ms = m.s,
            nsh = this.DB - nbits(pm[pm.t - 1]);
        nsh > 0 ? (pm.lShiftTo(nsh, y), pt.lShiftTo(nsh, r)) : (pm.copyTo(y), pt.copyTo(r));
        var ys = y.t,
            y0 = y[ys - 1];
        if (0 != y0) {
            var yt = y0 * (1 << this.F1) + (ys > 1 ? y[ys - 2] >> this.F2 : 0),
                d1 = this.FV / yt,
                d2 = (1 << this.F1) / yt,
                e = 1 << this.F2,
                i = r.t,
                j = i - ys,
                t = null == q ? nbi() : q;
            for (y.dlShiftTo(j, t), r.compareTo(t) >= 0 && (r[r.t++] = 1, r.subTo(t, r)), BigInteger.ONE.dlShiftTo(ys, t), t.subTo(y, y); y.t < ys;) y[y.t++] = 0;
            for (; --j >= 0;) {
                var qd = r[--i] == y0 ? this.DM : Math.floor(r[i] * d1 + (r[i - 1] + e) * d2);
                if ((r[i] += y.am(0, qd, r, j, 0, ys)) < qd)
                    for (y.dlShiftTo(j, t), r.subTo(t, r); r[i] < --qd;) r.subTo(t, r)
            }
            null != q && (r.drShiftTo(ys, q), ts != ms && BigInteger.ZERO.subTo(q, q)), r.t = ys, r.clamp(), nsh > 0 && r.rShiftTo(nsh, r), 0 > ts && BigInteger.ZERO.subTo(r, r)
        }
    }
}

function bnMod(a) {
    var r = nbi();
    return this.abs().divRemTo(a, null, r), this.s < 0 && r.compareTo(BigInteger.ZERO) > 0 && a.subTo(r, r), r
}

function Classic(m) {
    this.m = m
}

function cConvert(x) {
    return x.s < 0 || x.compareTo(this.m) >= 0 ? x.mod(this.m) : x
}

function cRevert(x) {
    return x
}

function cReduce(x) {
    x.divRemTo(this.m, null, x)
}

function cMulTo(x, y, r) {
    x.multiplyTo(y, r), this.reduce(r)
}

function cSqrTo(x, r) {
    x.squareTo(r), this.reduce(r)
}

function bnpInvDigit() {
    if (this.t < 1) return 0;
    var x = this[0];
    if (0 == (1 & x)) return 0;
    var y = 3 & x;
    return y = y * (2 - (15 & x) * y) & 15, y = y * (2 - (255 & x) * y) & 255, y = y * (2 - ((65535 & x) * y & 65535)) & 65535, y = y * (2 - x * y % this.DV) % this.DV, y > 0 ? this.DV - y : -y
}

function Montgomery(m) {
    this.m = m, this.mp = m.invDigit(), this.mpl = 32767 & this.mp, this.mph = this.mp >> 15, this.um = (1 << m.DB - 15) - 1, this.mt2 = 2 * m.t
}

function montConvert(x) {
    var r = nbi();
    return x.abs().dlShiftTo(this.m.t, r), r.divRemTo(this.m, null, r), x.s < 0 && r.compareTo(BigInteger.ZERO) > 0 && this.m.subTo(r, r), r
}

function montRevert(x) {
    var r = nbi();
    return x.copyTo(r), this.reduce(r), r
}

function montReduce(x) {
    for (; x.t <= this.mt2;) x[x.t++] = 0;
    for (var i = 0; i < this.m.t; ++i) {
        var j = 32767 & x[i],
            u0 = j * this.mpl + ((j * this.mph + (x[i] >> 15) * this.mpl & this.um) << 15) & x.DM;
        for (j = i + this.m.t, x[j] += this.m.am(0, u0, x, i, 0, this.m.t); x[j] >= x.DV;) x[j] -= x.DV, x[++j]++
    }
    x.clamp(), x.drShiftTo(this.m.t, x), x.compareTo(this.m) >= 0 && x.subTo(this.m, x)
}

function montSqrTo(x, r) {
    x.squareTo(r), this.reduce(r)
}

function montMulTo(x, y, r) {
    x.multiplyTo(y, r), this.reduce(r)
}

function bnpIsEven() {
    return 0 == (this.t > 0 ? 1 & this[0] : this.s)
}

function bnpExp(e, z) {
    if (e > 4294967295 || 1 > e) return BigInteger.ONE;
    var r = nbi(),
        r2 = nbi(),
        g = z.convert(this),
        i = nbits(e) - 1;
    for (g.copyTo(r); --i >= 0;)
        if (z.sqrTo(r, r2), (e & 1 << i) > 0) z.mulTo(r2, g, r);
        else {
            var t = r;
            r = r2, r2 = t
        }
    return z.revert(r)
}

function bnModPowInt(e, m) {
    var z;
    return z = 256 > e || m.isEven() ? new Classic(m) : new Montgomery(m), this.exp(e, z)
}

function Arcfour() {
    this.i = 0, this.j = 0, this.S = new Array
}

function ARC4init(key) {
    var i, j, t;
    for (i = 0; 256 > i; ++i) this.S[i] = i;
    for (j = 0, i = 0; 256 > i; ++i) j = j + this.S[i] + key[i % key.length] & 255, t = this.S[i], this.S[i] = this.S[j], this.S[j] = t;
    this.i = 0, this.j = 0
}

function ARC4next() {
    var t;
    return this.i = this.i + 1 & 255, this.j = this.j + this.S[this.i] & 255, t = this.S[this.i], this.S[this.i] = this.S[this.j], this.S[this.j] = t, this.S[t + this.S[this.i] & 255]
}

function prng_newstate() {
    return new Arcfour
}

function rng_seed_int(x) {
    rng_pool[rng_pptr++] ^= 255 & x, rng_pool[rng_pptr++] ^= x >> 8 & 255, rng_pool[rng_pptr++] ^= x >> 16 & 255, rng_pool[rng_pptr++] ^= x >> 24 & 255, rng_pptr >= rng_psize && (rng_pptr -= rng_psize)
}

function rng_seed_time() {
    rng_seed_int((new Date).getTime())
}

function rng_get_byte() {
    if (null == rng_state) {
        for (rng_seed_time(), rng_state = prng_newstate(), rng_state.init(rng_pool), rng_pptr = 0; rng_pptr < rng_pool.length; ++rng_pptr) rng_pool[rng_pptr] = 0;
        rng_pptr = 0
    }
    return rng_state.next()
}

function rng_get_bytes(ba) {
    var i;
    for (i = 0; i < ba.length; ++i) ba[i] = rng_get_byte()
}

function SecureRandom() {}

function getRSAKey() {
    return rsaKey ? rsaKey : (rsaKey = new RSAKey, rsaKey.setPublic(public_key.modulus, public_key.exponent), rsaKey)
}! function() {

}(),
function(e, t) {
    function i(t, n) {
        var r, i, o, u = t.nodeName.toLowerCase();
        return "area" === u ? (r = t.parentNode, i = r.name, t.href && i && "map" === r.nodeName.toLowerCase() ? (o = e("img[usemap=#" + i + "]")[0], !!o && s(o)) : !1) : (/input|select|textarea|button|object/.test(u) ? !t.disabled : "a" === u ? t.href || n : n) && s(t)
    }

    function s(t) {
        return e.expr.filters.visible(t) && !e(t).parents().andSelf().filter(function() {
            return "hidden" === e.css(this, "visibility")
        }).length
    }
    var n = 0,
        r = /^ui-id-\d+$/;
    e.ui = e.ui || {}, e.ui.version || (e.extend(e.ui, {
        version: "1.9.2",
        keyCode: {
            BACKSPACE: 8,
            COMMA: 188,
            DELETE: 46,
            DOWN: 40,
            END: 35,
            ENTER: 13,
            ESCAPE: 27,
            HOME: 36,
            LEFT: 37,
            NUMPAD_ADD: 107,
            NUMPAD_DECIMAL: 110,
            NUMPAD_DIVIDE: 111,
            NUMPAD_ENTER: 108,
            NUMPAD_MULTIPLY: 106,
            NUMPAD_SUBTRACT: 109,
            PAGE_DOWN: 34,
            PAGE_UP: 33,
            PERIOD: 190,
            RIGHT: 39,
            SPACE: 32,
            TAB: 9,
            UP: 38
        }
    }), e.fn.extend({
        _focus: e.fn.focus,
        focus: function(t, n) {
            return "number" == typeof t ? this.each(function() {
                var r = this;
                setTimeout(function() {
                    e(r).focus(), n && n.call(r)
                }, t)
            }) : this._focus.apply(this, arguments)
        },
        scrollParent: function() {
            var t;
            return t = e.ui.ie && /(static|relative)/.test(this.css("position")) || /absolute/.test(this.css("position")) ? this.parents().filter(function() {
                return /(relative|absolute|fixed)/.test(e.css(this, "position")) && /(auto|scroll)/.test(e.css(this, "overflow") + e.css(this, "overflow-y") + e.css(this, "overflow-x"))
            }).eq(0) : this.parents().filter(function() {
                return /(auto|scroll)/.test(e.css(this, "overflow") + e.css(this, "overflow-y") + e.css(this, "overflow-x"))
            }).eq(0), /fixed/.test(this.css("position")) || !t.length ? e(document) : t
        },
        zIndex: function(n) {
            if (n !== t) return this.css("zIndex", n);
            if (this.length)
                for (var i, s, r = e(this[0]); r.length && r[0] !== document;) {
                    if (i = r.css("position"), ("absolute" === i || "relative" === i || "fixed" === i) && (s = parseInt(r.css("zIndex"), 10), !isNaN(s) && 0 !== s)) return s;
                    r = r.parent()
                }
            return 0
        },
        uniqueId: function() {
            return this.each(function() {
                this.id || (this.id = "ui-id-" + ++n)
            })
        },
        removeUniqueId: function() {
            return this.each(function() {
                r.test(this.id) && e(this).removeAttr("id")
            })
        }
    }), e.extend(e.expr[":"], {
        data: e.expr.createPseudo ? e.expr.createPseudo(function(t) {
            return function(n) {
                return !!e.data(n, t)
            }
        }) : function(t, n, r) {
            return !!e.data(t, r[3])
        },
        focusable: function(t) {
            return i(t, !isNaN(e.attr(t, "tabindex")))
        },
        tabbable: function(t) {
            var n = e.attr(t, "tabindex"),
                r = isNaN(n);
            return (r || n >= 0) && i(t, !r)
        }
    }), e(function() {
        var t = document.body,
            n = t.appendChild(n = document.createElement("div"));
        n.offsetHeight, e.extend(n.style, {
            minHeight: "100px",
            height: "auto",
            padding: 0,
            borderWidth: 0
        }), e.support.minHeight = 100 === n.offsetHeight, e.support.selectstart = "onselectstart" in n, t.removeChild(n).style.display = "none"
    }), e("<a>").outerWidth(1).jquery || e.each(["Width", "Height"], function(n, r) {
        function u(t, n, r, s) {
            return e.each(i, function() {
                n -= parseFloat(e.css(t, "padding" + this)) || 0, r && (n -= parseFloat(e.css(t, "border" + this + "Width")) || 0), s && (n -= parseFloat(e.css(t, "margin" + this)) || 0)
            }), n
        }
        var i = "Width" === r ? ["Left", "Right"] : ["Top", "Bottom"],
            s = r.toLowerCase(),
            o = {
                innerWidth: e.fn.innerWidth,
                innerHeight: e.fn.innerHeight,
                outerWidth: e.fn.outerWidth,
                outerHeight: e.fn.outerHeight
            };
        e.fn["inner" + r] = function(n) {
            return n === t ? o["inner" + r].call(this) : this.each(function() {
                e(this).css(s, u(this, n) + "px")
            })
        }, e.fn["outer" + r] = function(t, n) {
            return "number" != typeof t ? o["outer" + r].call(this, t) : this.each(function() {
                e(this).css(s, u(this, t, !0, n) + "px")
            })
        }
    }), e("<a>").data("a-b", "a").removeData("a-b").data("a-b") && (e.fn.removeData = function(t) {
        return function(n) {
            return arguments.length ? t.call(this, e.camelCase(n)) : t.call(this)
        }
    }(e.fn.removeData)), function() {
        var t = /msie ([\w.]+)/.exec(navigator.userAgent.toLowerCase()) || [];
        e.ui.ie = t.length ? !0 : !1, e.ui.ie6 = 6 === parseFloat(t[1], 10)
    }(), e.fn.extend({
        disableSelection: function() {
            return this.bind((e.support.selectstart ? "selectstart" : "mousedown") + ".ui-disableSelection", function(e) {
                e.preventDefault()
            })
        },
        enableSelection: function() {
            return this.unbind(".ui-disableSelection")
        }
    }), e.extend(e.ui, {
        plugin: {
            add: function(t, n, r) {
                var i, s = e.ui[t].prototype;
                for (i in r) s.plugins[i] = s.plugins[i] || [], s.plugins[i].push([n, r[i]])
            },
            call: function(e, t, n) {
                var r, i = e.plugins[t];
                if (i && e.element[0].parentNode && 11 !== e.element[0].parentNode.nodeType)
                    for (r = 0; r < i.length; r++) e.options[i[r][0]] && i[r][1].apply(e.element, n)
            }
        },
        contains: e.contains,
        hasScroll: function(t, n) {
            if ("hidden" === e(t).css("overflow")) return !1;
            var r = n && "left" === n ? "scrollLeft" : "scrollTop",
                i = !1;
            return t[r] > 0 ? !0 : (t[r] = 1, i = t[r] > 0, t[r] = 0, i)
        },
        isOverAxis: function(e, t, n) {
            return e > t && t + n > e
        },
        isOver: function(t, n, r, i, s, o) {
            return e.ui.isOverAxis(t, r, s) && e.ui.isOverAxis(n, i, o)
        }
    }))
}(jQuery),
function(e, t) {
    var n = 0,
        r = Array.prototype.slice,
        i = e.cleanData;
    e.cleanData = function(t) {
        for (var r, n = 0; null != (r = t[n]); n++) try {
            e(r).triggerHandler("remove")
        } catch (s) {}
        i(t)
    }, e.widget = function(t, n, r) {
        var i, s, o, u, a = t.split(".")[0];
        t = t.split(".")[1], i = a + "-" + t, r || (r = n, n = e.Widget), e.expr[":"][i.toLowerCase()] = function(t) {
            return !!e.data(t, i)
        }, e[a] = e[a] || {}, s = e[a][t], o = e[a][t] = function(e, t) {
            return this._createWidget ? void(arguments.length && this._createWidget(e, t)) : new o(e, t)
        }, e.extend(o, s, {
            version: r.version,
            _proto: e.extend({}, r),
            _childConstructors: []
        }), u = new n, u.options = e.widget.extend({}, u.options), e.each(r, function(t, i) {
            e.isFunction(i) && (r[t] = function() {
                var e = function() {
                        return n.prototype[t].apply(this, arguments)
                    },
                    r = function(e) {
                        return n.prototype[t].apply(this, e)
                    };
                return function() {
                    var s, t = this._super,
                        n = this._superApply;
                    return this._super = e, this._superApply = r, s = i.apply(this, arguments), this._super = t, this._superApply = n, s
                }
            }())
        }), o.prototype = e.widget.extend(u, {
            widgetEventPrefix: s ? u.widgetEventPrefix : t
        }, r, {
            constructor: o,
            namespace: a,
            widgetName: t,
            widgetBaseClass: i,
            widgetFullName: i
        }), s ? (e.each(s._childConstructors, function(t, n) {
            var r = n.prototype;
            e.widget(r.namespace + "." + r.widgetName, o, n._proto)
        }), delete s._childConstructors) : n._childConstructors.push(o), e.widget.bridge(t, o)
    }, e.widget.extend = function(n) {
        for (var u, a, i = r.call(arguments, 1), s = 0, o = i.length; o > s; s++)
            for (u in i[s]) a = i[s][u], i[s].hasOwnProperty(u) && a !== t && (n[u] = e.isPlainObject(a) ? e.isPlainObject(n[u]) ? e.widget.extend({}, n[u], a) : e.widget.extend({}, a) : a);
        return n
    }, e.widget.bridge = function(n, i) {
        var s = i.prototype.widgetFullName || n;
        e.fn[n] = function(o) {
            var u = "string" == typeof o,
                a = r.call(arguments, 1),
                f = this;
            return o = !u && a.length ? e.widget.extend.apply(null, [o].concat(a)) : o, this.each(u ? function() {
                var r, i = e.data(this, s);
                return i ? e.isFunction(i[o]) && "_" !== o.charAt(0) ? (r = i[o].apply(i, a), r !== i && r !== t ? (f = r && r.jquery ? f.pushStack(r.get()) : r, !1) : void 0) : e.error("no such method '" + o + "' for " + n + " widget instance") : e.error("cannot call methods on " + n + " prior to initialization; attempted to call method '" + o + "'")
            } : function() {
                var t = e.data(this, s);
                t ? t.option(o || {})._init() : e.data(this, s, new i(o, this))
            }), f
        }
    }, e.Widget = function() {}, e.Widget._childConstructors = [], e.Widget.prototype = {
        widgetName: "widget",
        widgetEventPrefix: "",
        defaultElement: "<div>",
        options: {
            disabled: !1,
            create: null
        },
        _createWidget: function(t, r) {
            r = e(r || this.defaultElement || this)[0], this.element = e(r), this.uuid = n++, this.eventNamespace = "." + this.widgetName + this.uuid, this.options = e.widget.extend({}, this.options, this._getCreateOptions(), t), this.bindings = e(), this.hoverable = e(), this.focusable = e(), r !== this && (e.data(r, this.widgetName, this), e.data(r, this.widgetFullName, this), this._on(!0, this.element, {
                remove: function(e) {
                    e.target === r && this.destroy()
                }
            }), this.document = e(r.style ? r.ownerDocument : r.document || r), this.window = e(this.document[0].defaultView || this.document[0].parentWindow)), this._create(), this._trigger("create", null, this._getCreateEventData()), this._init()
        },
        _getCreateOptions: e.noop,
        _getCreateEventData: e.noop,
        _create: e.noop,
        _init: e.noop,
        destroy: function() {
            this._destroy(), this.element.unbind(this.eventNamespace).removeData(this.widgetName).removeData(this.widgetFullName).removeData(e.camelCase(this.widgetFullName)), this.widget().unbind(this.eventNamespace).removeAttr("aria-disabled").removeClass(this.widgetFullName + "-disabled ui-state-disabled"), this.bindings.unbind(this.eventNamespace), this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus")
        },
        _destroy: e.noop,
        widget: function() {
            return this.element
        },
        option: function(n, r) {
            var s, o, u, i = n;
            if (0 === arguments.length) return e.widget.extend({}, this.options);
            if ("string" == typeof n)
                if (i = {}, s = n.split("."), n = s.shift(), s.length) {
                    for (o = i[n] = e.widget.extend({}, this.options[n]), u = 0; u < s.length - 1; u++) o[s[u]] = o[s[u]] || {}, o = o[s[u]];
                    if (n = s.pop(), r === t) return o[n] === t ? null : o[n];
                    o[n] = r
                } else {
                    if (r === t) return this.options[n] === t ? null : this.options[n];
                    i[n] = r
                }
            return this._setOptions(i), this
        },
        _setOptions: function(e) {
            var t;
            for (t in e) this._setOption(t, e[t]);
            return this
        },
        _setOption: function(e, t) {
            return this.options[e] = t, "disabled" === e && (this.widget().toggleClass(this.widgetFullName + "-disabled ui-state-disabled", !!t).attr("aria-disabled", t), this.hoverable.removeClass("ui-state-hover"), this.focusable.removeClass("ui-state-focus")), this
        },
        enable: function() {
            return this._setOption("disabled", !1)
        },
        disable: function() {
            return this._setOption("disabled", !0)
        },
        _on: function(t, n, r) {
            var i, s = this;
            "boolean" != typeof t && (r = n, n = t, t = !1), r ? (n = i = e(n), this.bindings = this.bindings.add(n)) : (r = n, n = this.element, i = this.widget()), e.each(r, function(r, o) {
                function u() {
                    return t || s.options.disabled !== !0 && !e(this).hasClass("ui-state-disabled") ? ("string" == typeof o ? s[o] : o).apply(s, arguments) : void 0
                }
                "string" != typeof o && (u.guid = o.guid = o.guid || u.guid || e.guid++);
                var a = r.match(/^(\w+)\s*(.*)$/),
                    f = a[1] + s.eventNamespace,
                    l = a[2];
                l ? i.delegate(l, f, u) : n.bind(f, u)
            })
        },
        _off: function(e, t) {
            t = (t || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace, e.unbind(t).undelegate(t)
        },
        _delay: function(e, t) {
            function n() {
                return ("string" == typeof e ? r[e] : e).apply(r, arguments)
            }
            var r = this;
            return setTimeout(n, t || 0)
        },
        _hoverable: function(t) {
            this.hoverable = this.hoverable.add(t), this._on(t, {
                mouseenter: function(t) {
                    e(t.currentTarget).addClass("ui-state-hover")
                },
                mouseleave: function(t) {
                    e(t.currentTarget).removeClass("ui-state-hover")
                }
            })
        },
        _focusable: function(t) {
            this.focusable = this.focusable.add(t), this._on(t, {
                focusin: function(t) {
                    e(t.currentTarget).addClass("ui-state-focus")
                },
                focusout: function(t) {
                    e(t.currentTarget).removeClass("ui-state-focus")
                }
            })
        },
        _trigger: function(t, n, r) {
            var i, s, o = this.options[t];
            if (r = r || {}, n = e.Event(n), n.type = (t === this.widgetEventPrefix ? t : this.widgetEventPrefix + t).toLowerCase(), n.target = this.element[0], s = n.originalEvent, s)
                for (i in s) i in n || (n[i] = s[i]);
            return this.element.trigger(n, r), !(e.isFunction(o) && o.apply(this.element[0], [n].concat(r)) === !1 || n.isDefaultPrevented())
        }
    }, e.each({
        show: "fadeIn",
        hide: "fadeOut"
    }, function(t, n) {
        e.Widget.prototype["_" + t] = function(r, i, s) {
            "string" == typeof i && (i = {
                effect: i
            });
            var o, u = i ? i === !0 || "number" == typeof i ? n : i.effect || n : t;
            i = i || {}, "number" == typeof i && (i = {
                duration: i
            }), o = !e.isEmptyObject(i), i.complete = s, i.delay && r.delay(i.delay), o && e.effects && (e.effects.effect[u] || e.uiBackCompat !== !1 && e.effects[u]) ? r[t](i) : u !== t && r[u] ? r[u](i.duration, i.easing, s) : r.queue(function(n) {
                e(this)[t](), s && s.call(r[0]), n()
            })
        }
    }), e.uiBackCompat !== !1 && (e.Widget.prototype._getCreateOptions = function() {
        return e.metadata && e.metadata.get(this.element[0])[this.widgetName]
    })
}(jQuery),
function(e) {
    var n = !1;
    e(document).mouseup(function() {
        n = !1
    }), e.widget("ui.mouse", {
        version: "1.9.2",
        options: {
            cancel: "input,textarea,button,select,option",
            distance: 1,
            delay: 0
        },
        _mouseInit: function() {
            var t = this;
            this.element.bind("mousedown." + this.widgetName, function(e) {
                return t._mouseDown(e)
            }).bind("click." + this.widgetName, function(n) {
                return !0 === e.data(n.target, t.widgetName + ".preventClickEvent") ? (e.removeData(n.target, t.widgetName + ".preventClickEvent"), n.stopImmediatePropagation(), !1) : void 0
            }), this.started = !1
        },
        _mouseDestroy: function() {
            this.element.unbind("." + this.widgetName), this._mouseMoveDelegate && e(document).unbind("mousemove." + this.widgetName, this._mouseMoveDelegate).unbind("mouseup." + this.widgetName, this._mouseUpDelegate)
        },
        _mouseDown: function(t) {
            if (!n) {
                this._mouseStarted && this._mouseUp(t), this._mouseDownEvent = t;
                var r = this,
                    i = 1 === t.which,
                    s = "string" == typeof this.options.cancel && t.target.nodeName ? e(t.target).closest(this.options.cancel).length : !1;
                return i && !s && this._mouseCapture(t) ? (this.mouseDelayMet = !this.options.delay, this.mouseDelayMet || (this._mouseDelayTimer = setTimeout(function() {
                    r.mouseDelayMet = !0
                }, this.options.delay)), this._mouseDistanceMet(t) && this._mouseDelayMet(t) && (this._mouseStarted = this._mouseStart(t) !== !1, !this._mouseStarted) ? (t.preventDefault(), !0) : (!0 === e.data(t.target, this.widgetName + ".preventClickEvent") && e.removeData(t.target, this.widgetName + ".preventClickEvent"), this._mouseMoveDelegate = function(e) {
                    return r._mouseMove(e)
                }, this._mouseUpDelegate = function(e) {
                    return r._mouseUp(e)
                }, e(document).bind("mousemove." + this.widgetName, this._mouseMoveDelegate).bind("mouseup." + this.widgetName, this._mouseUpDelegate), t.preventDefault(), n = !0, !0)) : !0
            }
        },
        _mouseMove: function(t) {
            return !e.ui.ie || document.documentMode >= 9 || t.button ? this._mouseStarted ? (this._mouseDrag(t), t.preventDefault()) : (this._mouseDistanceMet(t) && this._mouseDelayMet(t) && (this._mouseStarted = this._mouseStart(this._mouseDownEvent, t) !== !1, this._mouseStarted ? this._mouseDrag(t) : this._mouseUp(t)), !this._mouseStarted) : this._mouseUp(t)
        },
        _mouseUp: function(t) {
            return e(document).unbind("mousemove." + this.widgetName, this._mouseMoveDelegate).unbind("mouseup." + this.widgetName, this._mouseUpDelegate), this._mouseStarted && (this._mouseStarted = !1, t.target === this._mouseDownEvent.target && e.data(t.target, this.widgetName + ".preventClickEvent", !0), this._mouseStop(t)), !1
        },
        _mouseDistanceMet: function(e) {
            return Math.max(Math.abs(this._mouseDownEvent.pageX - e.pageX), Math.abs(this._mouseDownEvent.pageY - e.pageY)) >= this.options.distance
        },
        _mouseDelayMet: function() {
            return this.mouseDelayMet
        },
        _mouseStart: function() {},
        _mouseDrag: function() {},
        _mouseStop: function() {},
        _mouseCapture: function() {
            return !0
        }
    })
}(jQuery),
function(e, t) {
    function h(e, t, n) {
        return [parseInt(e[0], 10) * (l.test(e[0]) ? t / 100 : 1), parseInt(e[1], 10) * (l.test(e[1]) ? n / 100 : 1)]
    }

    function p(t, n) {
        return parseInt(e.css(t, n), 10) || 0
    }
    e.ui = e.ui || {};
    var n, r = Math.max,
        i = Math.abs,
        s = Math.round,
        o = /left|center|right/,
        u = /top|center|bottom/,
        a = /[\+\-]\d+%?/,
        f = /^\w+/,
        l = /%$/,
        c = e.fn.position;
    e.position = {
            scrollbarWidth: function() {
                if (n !== t) return n;
                var r, i, s = e("<div style='display:block;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>"),
                    o = s.children()[0];
                return e("body").append(s), r = o.offsetWidth, s.css("overflow", "scroll"), i = o.offsetWidth, r === i && (i = s[0].clientWidth), s.remove(), n = r - i
            },
            getScrollInfo: function(t) {
                var n = t.isWindow ? "" : t.element.css("overflow-x"),
                    r = t.isWindow ? "" : t.element.css("overflow-y"),
                    i = "scroll" === n || "auto" === n && t.width < t.element[0].scrollWidth,
                    s = "scroll" === r || "auto" === r && t.height < t.element[0].scrollHeight;
                return {
                    width: i ? e.position.scrollbarWidth() : 0,
                    height: s ? e.position.scrollbarWidth() : 0
                }
            },
            getWithinInfo: function(t) {
                var n = e(t || window),
                    r = e.isWindow(n[0]);
                return {
                    element: n,
                    isWindow: r,
                    offset: n.offset() || {
                        left: 0,
                        top: 0
                    },
                    scrollLeft: n.scrollLeft(),
                    scrollTop: n.scrollTop(),
                    width: r ? n.width() : n.outerWidth(),
                    height: r ? n.height() : n.outerHeight()
                }
            }
        }, e.fn.position = function(t) {
            if (!t || !t.of) return c.apply(this, arguments);
            t = e.extend({}, t);
            var n, l, d, v, m, g = e(t.of),
                y = e.position.getWithinInfo(t.within),
                b = e.position.getScrollInfo(y),
                w = g[0],
                E = (t.collision || "flip").split(" "),
                S = {};
            return 9 === w.nodeType ? (l = g.width(), d = g.height(), v = {
                top: 0,
                left: 0
            }) : e.isWindow(w) ? (l = g.width(), d = g.height(), v = {
                top: g.scrollTop(),
                left: g.scrollLeft()
            }) : w.preventDefault ? (t.at = "left top", l = d = 0, v = {
                top: w.pageY,
                left: w.pageX
            }) : (l = g.outerWidth(), d = g.outerHeight(), v = g.offset()), m = e.extend({}, v), e.each(["my", "at"], function() {
                var n, r, e = (t[this] || "").split(" ");
                1 === e.length && (e = o.test(e[0]) ? e.concat(["center"]) : u.test(e[0]) ? ["center"].concat(e) : ["center", "center"]), e[0] = o.test(e[0]) ? e[0] : "center", e[1] = u.test(e[1]) ? e[1] : "center", n = a.exec(e[0]), r = a.exec(e[1]), S[this] = [n ? n[0] : 0, r ? r[0] : 0], t[this] = [f.exec(e[0])[0], f.exec(e[1])[0]]
            }), 1 === E.length && (E[1] = E[0]), "right" === t.at[0] ? m.left += l : "center" === t.at[0] && (m.left += l / 2), "bottom" === t.at[1] ? m.top += d : "center" === t.at[1] && (m.top += d / 2), n = h(S.at, l, d), m.left += n[0], m.top += n[1], this.each(function() {
                var o, u, a = e(this),
                    f = a.outerWidth(),
                    c = a.outerHeight(),
                    w = p(this, "marginLeft"),
                    x = p(this, "marginTop"),
                    T = f + w + p(this, "marginRight") + b.width,
                    N = c + x + p(this, "marginBottom") + b.height,
                    C = e.extend({}, m),
                    k = h(S.my, a.outerWidth(), a.outerHeight());
                "right" === t.my[0] ? C.left -= f : "center" === t.my[0] && (C.left -= f / 2), "bottom" === t.my[1] ? C.top -= c : "center" === t.my[1] && (C.top -= c / 2), C.left += k[0], C.top += k[1], e.support.offsetFractions || (C.left = s(C.left), C.top = s(C.top)), o = {
                    marginLeft: w,
                    marginTop: x
                }, e.each(["left", "top"], function(r, i) {
                    e.ui.position[E[r]] && e.ui.position[E[r]][i](C, {
                        targetWidth: l,
                        targetHeight: d,
                        elemWidth: f,
                        elemHeight: c,
                        collisionPosition: o,
                        collisionWidth: T,
                        collisionHeight: N,
                        offset: [n[0] + k[0], n[1] + k[1]],
                        my: t.my,
                        at: t.at,
                        within: y,
                        elem: a
                    })
                }), e.fn.bgiframe && a.bgiframe(), t.using && (u = function(e) {
                    var n = v.left - C.left,
                        s = n + l - f,
                        o = v.top - C.top,
                        u = o + d - c,
                        h = {
                            target: {
                                element: g,
                                left: v.left,
                                top: v.top,
                                width: l,
                                height: d
                            },
                            element: {
                                element: a,
                                left: C.left,
                                top: C.top,
                                width: f,
                                height: c
                            },
                            horizontal: 0 > s ? "left" : n > 0 ? "right" : "center",
                            vertical: 0 > u ? "top" : o > 0 ? "bottom" : "middle"
                        };
                    f > l && i(n + s) < l && (h.horizontal = "center"), c > d && i(o + u) < d && (h.vertical = "middle"), h.important = r(i(n), i(s)) > r(i(o), i(u)) ? "horizontal" : "vertical", t.using.call(this, e, h)
                }), a.offset(e.extend(C, {
                    using: u
                }))
            })
        }, e.ui.position = {
            fit: {
                left: function(e, t) {
                    var f, n = t.within,
                        i = n.isWindow ? n.scrollLeft : n.offset.left,
                        s = n.width,
                        o = e.left - t.collisionPosition.marginLeft,
                        u = i - o,
                        a = o + t.collisionWidth - s - i;
                    t.collisionWidth > s ? u > 0 && 0 >= a ? (f = e.left + u + t.collisionWidth - s - i, e.left += u - f) : e.left = a > 0 && 0 >= u ? i : u > a ? i + s - t.collisionWidth : i : u > 0 ? e.left += u : a > 0 ? e.left -= a : e.left = r(e.left - o, e.left)
                },
                top: function(e, t) {
                    var f, n = t.within,
                        i = n.isWindow ? n.scrollTop : n.offset.top,
                        s = t.within.height,
                        o = e.top - t.collisionPosition.marginTop,
                        u = i - o,
                        a = o + t.collisionHeight - s - i;
                    t.collisionHeight > s ? u > 0 && 0 >= a ? (f = e.top + u + t.collisionHeight - s - i, e.top += u - f) : e.top = a > 0 && 0 >= u ? i : u > a ? i + s - t.collisionHeight : i : u > 0 ? e.top += u : a > 0 ? e.top -= a : e.top = r(e.top - o, e.top)
                }
            },
            flip: {
                left: function(e, t) {
                    var p, d, n = t.within,
                        r = n.offset.left + n.scrollLeft,
                        s = n.width,
                        o = n.isWindow ? n.scrollLeft : n.offset.left,
                        u = e.left - t.collisionPosition.marginLeft,
                        a = u - o,
                        f = u + t.collisionWidth - s - o,
                        l = "left" === t.my[0] ? -t.elemWidth : "right" === t.my[0] ? t.elemWidth : 0,
                        c = "left" === t.at[0] ? t.targetWidth : "right" === t.at[0] ? -t.targetWidth : 0,
                        h = -2 * t.offset[0];
                    0 > a ? (p = e.left + l + c + h + t.collisionWidth - s - r, (0 > p || p < i(a)) && (e.left += l + c + h)) : f > 0 && (d = e.left - t.collisionPosition.marginLeft + l + c + h - o, (d > 0 || i(d) < f) && (e.left += l + c + h))
                },
                top: function(e, t) {
                    var d, v, n = t.within,
                        r = n.offset.top + n.scrollTop,
                        s = n.height,
                        o = n.isWindow ? n.scrollTop : n.offset.top,
                        u = e.top - t.collisionPosition.marginTop,
                        a = u - o,
                        f = u + t.collisionHeight - s - o,
                        l = "top" === t.my[1],
                        c = l ? -t.elemHeight : "bottom" === t.my[1] ? t.elemHeight : 0,
                        h = "top" === t.at[1] ? t.targetHeight : "bottom" === t.at[1] ? -t.targetHeight : 0,
                        p = -2 * t.offset[1];
                    0 > a ? (v = e.top + c + h + p + t.collisionHeight - s - r, e.top + c + h + p > a && (0 > v || v < i(a)) && (e.top += c + h + p)) : f > 0 && (d = e.top - t.collisionPosition.marginTop + c + h + p - o, e.top + c + h + p > f && (d > 0 || i(d) < f) && (e.top += c + h + p))
                }
            },
            flipfit: {
                left: function() {
                    e.ui.position.flip.left.apply(this, arguments), e.ui.position.fit.left.apply(this, arguments)
                },
                top: function() {
                    e.ui.position.flip.top.apply(this, arguments), e.ui.position.fit.top.apply(this, arguments)
                }
            }
        },
        function() {
            var t, n, r, i, s, o = document.getElementsByTagName("body")[0],
                u = document.createElement("div");
            t = document.createElement(o ? "div" : "body"), r = {
                visibility: "hidden",
                width: 0,
                height: 0,
                border: 0,
                margin: 0,
                background: "none"
            }, o && e.extend(r, {
                position: "absolute",
                left: "-1000px",
                top: "-1000px"
            });
            for (s in r) t.style[s] = r[s];
            t.appendChild(u), n = o || document.documentElement, n.insertBefore(t, n.firstChild), u.style.cssText = "position: absolute; left: 10.7432222px;", i = e(u).offset().left, e.support.offsetFractions = i > 10 && 11 > i, t.innerHTML = "", n.removeChild(t)
        }(), e.uiBackCompat !== !1 && function(e) {
            var n = e.fn.position;
            e.fn.position = function(r) {
                if (!r || !r.offset) return n.call(this, r);
                var i = r.offset.split(" "),
                    s = r.at.split(" ");
                return 1 === i.length && (i[1] = i[0]), /^\d/.test(i[0]) && (i[0] = "+" + i[0]), /^\d/.test(i[1]) && (i[1] = "+" + i[1]), 1 === s.length && (/left|center|right/.test(s[0]) ? s[1] = "center" : (s[1] = s[0], s[0] = "center")), n.call(this, e.extend(r, {
                    at: s[0] + i[0] + " " + s[1] + i[1],
                    offset: t
                }))
            }
        }(jQuery)
}(jQuery),
function(e) {
    var n = 0,
        r = {},
        i = {};
    r.height = r.paddingTop = r.paddingBottom = r.borderTopWidth = r.borderBottomWidth = "hide", i.height = i.paddingTop = i.paddingBottom = i.borderTopWidth = i.borderBottomWidth = "show", e.widget("ui.accordion", {
        version: "1.9.2",
        options: {
            active: 0,
            animate: {},
            collapsible: !1,
            event: "click",
            header: "> li > :first-child,> :not(li):even",
            heightStyle: "auto",
            icons: {
                activeHeader: "ui-icon-triangle-1-s",
                header: "ui-icon-triangle-1-e"
            },
            activate: null,
            beforeActivate: null
        },
        _create: function() {
            var t = this.accordionId = "ui-accordion-" + (this.element.attr("id") || ++n),
                r = this.options;
            this.prevShow = this.prevHide = e(), this.element.addClass("ui-accordion ui-widget ui-helper-reset"), this.headers = this.element.find(r.header).addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-all"), this._hoverable(this.headers), this._focusable(this.headers), this.headers.next().addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom").hide(), !r.collapsible && (r.active === !1 || null == r.active) && (r.active = 0), r.active < 0 && (r.active += this.headers.length), this.active = this._findActive(r.active).addClass("ui-accordion-header-active ui-state-active").toggleClass("ui-corner-all ui-corner-top"), this.active.next().addClass("ui-accordion-content-active").show(), this._createIcons(), this.refresh(), this.element.attr("role", "tablist"), this.headers.attr("role", "tab").each(function(n) {
                var r = e(this),
                    i = r.attr("id"),
                    s = r.next(),
                    o = s.attr("id");
                i || (i = t + "-header-" + n, r.attr("id", i)), o || (o = t + "-panel-" + n, s.attr("id", o)), r.attr("aria-controls", o), s.attr("aria-labelledby", i)
            }).next().attr("role", "tabpanel"), this.headers.not(this.active).attr({
                "aria-selected": "false",
                tabIndex: -1
            }).next().attr({
                "aria-expanded": "false",
                "aria-hidden": "true"
            }).hide(), this.active.length ? this.active.attr({
                "aria-selected": "true",
                tabIndex: 0
            }).next().attr({
                "aria-expanded": "true",
                "aria-hidden": "false"
            }) : this.headers.eq(0).attr("tabIndex", 0), this._on(this.headers, {
                keydown: "_keydown"
            }), this._on(this.headers.next(), {
                keydown: "_panelKeyDown"
            }), this._setupEvents(r.event)
        },
        _getCreateEventData: function() {
            return {
                header: this.active,
                content: this.active.length ? this.active.next() : e()
            }
        },
        _createIcons: function() {
            var t = this.options.icons;
            t && (e("<span>").addClass("ui-accordion-header-icon ui-icon " + t.header).prependTo(this.headers), this.active.children(".ui-accordion-header-icon").removeClass(t.header).addClass(t.activeHeader), this.headers.addClass("ui-accordion-icons"))
        },
        _destroyIcons: function() {
            this.headers.removeClass("ui-accordion-icons").children(".ui-accordion-header-icon").remove()
        },
        _destroy: function() {
            var e;
            this.element.removeClass("ui-accordion ui-widget ui-helper-reset").removeAttr("role"), this.headers.removeClass("ui-accordion-header ui-accordion-header-active ui-helper-reset ui-state-default ui-corner-all ui-state-active ui-state-disabled ui-corner-top").removeAttr("role").removeAttr("aria-selected").removeAttr("aria-controls").removeAttr("tabIndex").each(function() {
                /^ui-accordion/.test(this.id) && this.removeAttribute("id")
            }), this._destroyIcons(), e = this.headers.next().css("display", "").removeAttr("role").removeAttr("aria-expanded").removeAttr("aria-hidden").removeAttr("aria-labelledby").removeClass("ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content ui-accordion-content-active ui-state-disabled").each(function() {
                /^ui-accordion/.test(this.id) && this.removeAttribute("id")
            }), "content" !== this.options.heightStyle && e.css("height", "")
        },
        _setOption: function(e, t) {
            return "active" === e ? void this._activate(t) : ("event" === e && (this.options.event && this._off(this.headers, this.options.event), this._setupEvents(t)), this._super(e, t), "collapsible" === e && !t && this.options.active === !1 && this._activate(0), "icons" === e && (this._destroyIcons(), t && this._createIcons()), "disabled" === e && this.headers.add(this.headers.next()).toggleClass("ui-state-disabled", !!t), void 0)
        },
        _keydown: function(t) {
            if (!t.altKey && !t.ctrlKey) {
                var n = e.ui.keyCode,
                    r = this.headers.length,
                    i = this.headers.index(t.target),
                    s = !1;
                switch (t.keyCode) {
                    case n.RIGHT:
                    case n.DOWN:
                        s = this.headers[(i + 1) % r];
                        break;
                    case n.LEFT:
                    case n.UP:
                        s = this.headers[(i - 1 + r) % r];
                        break;
                    case n.SPACE:
                    case n.ENTER:
                        this._eventHandler(t);
                        break;
                    case n.HOME:
                        s = this.headers[0];
                        break;
                    case n.END:
                        s = this.headers[r - 1]
                }
                s && (e(t.target).attr("tabIndex", -1), e(s).attr("tabIndex", 0), s.focus(), t.preventDefault())
            }
        },
        _panelKeyDown: function(t) {
            t.keyCode === e.ui.keyCode.UP && t.ctrlKey && e(t.currentTarget).prev().focus()
        },
        refresh: function() {
            var t, n, r = this.options.heightStyle,
                i = this.element.parent();
            "fill" === r ? (e.support.minHeight || (n = i.css("overflow"), i.css("overflow", "hidden")), t = i.height(), this.element.siblings(":visible").each(function() {
                var n = e(this),
                    r = n.css("position");
                "absolute" !== r && "fixed" !== r && (t -= n.outerHeight(!0))
            }), n && i.css("overflow", n), this.headers.each(function() {
                t -= e(this).outerHeight(!0)
            }), this.headers.next().each(function() {
                e(this).height(Math.max(0, t - e(this).innerHeight() + e(this).height()))
            }).css("overflow", "auto")) : "auto" === r && (t = 0, this.headers.next().each(function() {
                t = Math.max(t, e(this).css("height", "").height())
            }).height(t))
        },
        _activate: function(t) {
            var n = this._findActive(t)[0];
            n !== this.active[0] && (n = n || this.active[0], this._eventHandler({
                target: n,
                currentTarget: n,
                preventDefault: e.noop
            }))
        },
        _findActive: function(t) {
            return "number" == typeof t ? this.headers.eq(t) : e()
        },
        _setupEvents: function(t) {
            var n = {};
            t && (e.each(t.split(" "), function(e, t) {
                n[t] = "_eventHandler"
            }), this._on(this.headers, n))
        },
        _eventHandler: function(t) {
            var n = this.options,
                r = this.active,
                i = e(t.currentTarget),
                s = i[0] === r[0],
                o = s && n.collapsible,
                u = o ? e() : i.next(),
                a = r.next(),
                f = {
                    oldHeader: r,
                    oldPanel: a,
                    newHeader: o ? e() : i,
                    newPanel: u
                };
            t.preventDefault(), s && !n.collapsible || this._trigger("beforeActivate", t, f) === !1 || (n.active = o ? !1 : this.headers.index(i), this.active = s ? e() : i, this._toggle(f), r.removeClass("ui-accordion-header-active ui-state-active"), n.icons && r.children(".ui-accordion-header-icon").removeClass(n.icons.activeHeader).addClass(n.icons.header), s || (i.removeClass("ui-corner-all").addClass("ui-accordion-header-active ui-state-active ui-corner-top"), n.icons && i.children(".ui-accordion-header-icon").removeClass(n.icons.header).addClass(n.icons.activeHeader), i.next().addClass("ui-accordion-content-active")))
        },
        _toggle: function(t) {
            var n = t.newPanel,
                r = this.prevShow.length ? this.prevShow : t.oldPanel;
            this.prevShow.add(this.prevHide).stop(!0, !0), this.prevShow = n, this.prevHide = r, this.options.animate ? this._animate(n, r, t) : (r.hide(), n.show(), this._toggleComplete(t)), r.attr({
                "aria-expanded": "false",
                "aria-hidden": "true"
            }), r.prev().attr("aria-selected", "false"), n.length && r.length ? r.prev().attr("tabIndex", -1) : n.length && this.headers.filter(function() {
                return 0 === e(this).attr("tabIndex")
            }).attr("tabIndex", -1), n.attr({
                "aria-expanded": "true",
                "aria-hidden": "false"
            }).prev().attr({
                "aria-selected": "true",
                tabIndex: 0
            })
        },
        _animate: function(e, t, n) {
            var s, o, u, a = this,
                f = 0,
                l = e.length && (!t.length || e.index() < t.index()),
                c = this.options.animate || {},
                h = l && c.down || c,
                p = function() {
                    a._toggleComplete(n)
                };
            return "number" == typeof h && (u = h), "string" == typeof h && (o = h), o = o || h.easing || c.easing, u = u || h.duration || c.duration, t.length ? e.length ? (s = e.show().outerHeight(), t.animate(r, {
                duration: u,
                easing: o,
                step: function(e, t) {
                    t.now = Math.round(e)
                }
            }), e.hide().animate(i, {
                duration: u,
                easing: o,
                complete: p,
                step: function(e, n) {
                    n.now = Math.round(e), "height" !== n.prop ? f += n.now : "content" !== a.options.heightStyle && (n.now = Math.round(s - t.outerHeight() - f), f = 0)
                }
            }), void 0) : t.animate(r, u, o, p) : e.animate(i, u, o, p)
        },
        _toggleComplete: function(e) {
            var t = e.oldPanel;
            t.removeClass("ui-accordion-content-active").prev().removeClass("ui-corner-top").addClass("ui-corner-all"), t.length && (t.parent()[0].className = t.parent()[0].className), this._trigger("activate", null, e)
        }
    }), e.uiBackCompat !== !1 && (function(e, t) {
        e.extend(t.options, {
            navigation: !1,
            navigationFilter: function() {
                return this.href.toLowerCase() === location.href.toLowerCase()
            }
        });
        var n = t._create;
        t._create = function() {
            if (this.options.navigation) {
                var t = this,
                    r = this.element.find(this.options.header),
                    i = r.next(),
                    s = r.add(i).find("a").filter(this.options.navigationFilter)[0];
                s && r.add(i).each(function(n) {
                    return e.contains(this, s) ? (t.options.active = Math.floor(n / 2), !1) : void 0
                })
            }
            n.call(this)
        }
    }(jQuery, jQuery.ui.accordion.prototype), function(e, t) {
        e.extend(t.options, {
            heightStyle: null,
            autoHeight: !0,
            clearStyle: !1,
            fillSpace: !1
        });
        var n = t._create,
            r = t._setOption;
        e.extend(t, {
            _create: function() {
                this.options.heightStyle = this.options.heightStyle || this._mergeHeightStyle(), n.call(this)
            },
            _setOption: function(e) {
                ("autoHeight" === e || "clearStyle" === e || "fillSpace" === e) && (this.options.heightStyle = this._mergeHeightStyle()), r.apply(this, arguments)
            },
            _mergeHeightStyle: function() {
                var e = this.options;
                return e.fillSpace ? "fill" : e.clearStyle ? "content" : e.autoHeight ? "auto" : void 0
            }
        })
    }(jQuery, jQuery.ui.accordion.prototype), function(e, t) {
        e.extend(t.options.icons, {
            activeHeader: null,
            headerSelected: "ui-icon-triangle-1-s"
        });
        var n = t._createIcons;
        t._createIcons = function() {
            this.options.icons && (this.options.icons.activeHeader = this.options.icons.activeHeader || this.options.icons.headerSelected), n.call(this)
        }
    }(jQuery, jQuery.ui.accordion.prototype), function(e, t) {
        t.activate = t._activate;
        var n = t._findActive;
        t._findActive = function(e) {
            return -1 === e && (e = !1), e && "number" != typeof e && (e = this.headers.index(this.headers.filter(e)), -1 === e && (e = !1)), n.call(this, e)
        }
    }(jQuery, jQuery.ui.accordion.prototype), jQuery.ui.accordion.prototype.resize = jQuery.ui.accordion.prototype.refresh, function(e, t) {
        e.extend(t.options, {
            change: null,
            changestart: null
        });
        var n = t._trigger;
        t._trigger = function(e, t, r) {
            var i = n.apply(this, arguments);
            return i ? ("beforeActivate" === e ? i = n.call(this, "changestart", t, {
                oldHeader: r.oldHeader,
                oldContent: r.oldPanel,
                newHeader: r.newHeader,
                newContent: r.newPanel
            }) : "activate" === e && (i = n.call(this, "change", t, {
                oldHeader: r.oldHeader,
                oldContent: r.oldPanel,
                newHeader: r.newHeader,
                newContent: r.newPanel
            })), i) : !1
        }
    }(jQuery, jQuery.ui.accordion.prototype), function(e, t) {
        e.extend(t.options, {
            animate: null,
            animated: "slide"
        });
        var n = t._create;
        t._create = function() {
            var e = this.options;
            null === e.animate && (e.animate = e.animated ? "slide" === e.animated ? 300 : "bounceslide" === e.animated ? {
                duration: 200,
                down: {
                    easing: "easeOutBounce",
                    duration: 1e3
                }
            } : e.animated : !1), n.call(this)
        }
    }(jQuery, jQuery.ui.accordion.prototype))
}(jQuery),
function(e) {
    var n = 0;
    e.widget("ui.autocomplete", {
        version: "1.9.2",
        defaultElement: "<input>",
        options: {
            appendTo: "body",
            autoFocus: !1,
            delay: 300,
            minLength: 1,
            position: {
                my: "left top",
                at: "left bottom",
                collision: "none"
            },
            source: null,
            change: null,
            close: null,
            focus: null,
            open: null,
            response: null,
            search: null,
            select: null
        },
        pending: 0,
        _create: function() {
            var t, n, r;
            this.isMultiLine = this._isMultiLine(), this.valueMethod = this.element[this.element.is("input,textarea") ? "val" : "text"], this.isNewMenu = !0, this.element.addClass("ui-autocomplete-input").attr("autocomplete", "off"), this._on(this.element, {
                keydown: function(i) {
                    if (this.element.prop("readOnly")) return t = !0, r = !0, n = !0, void 0;
                    t = !1, r = !1, n = !1;
                    var s = e.ui.keyCode;
                    switch (i.keyCode) {
                        case s.PAGE_UP:
                            t = !0, this._move("previousPage", i);
                            break;
                        case s.PAGE_DOWN:
                            t = !0, this._move("nextPage", i);
                            break;
                        case s.UP:
                            t = !0, this._keyEvent("previous", i);
                            break;
                        case s.DOWN:
                            t = !0, this._keyEvent("next", i);
                            break;
                        case s.ENTER:
                        case s.NUMPAD_ENTER:
                            this.menu.active && (t = !0, i.preventDefault(), this.menu.select(i));
                            break;
                        case s.TAB:
                            this.menu.active && this.menu.select(i);
                            break;
                        case s.ESCAPE:
                            this.menu.element.is(":visible") && (this._value(this.term), this.close(i), i.preventDefault());
                            break;
                        default:
                            n = !0, this._searchTimeout(i)
                    }
                },
                keypress: function(r) {
                    if (t) return t = !1, void r.preventDefault();
                    if (!n) {
                        var i = e.ui.keyCode;
                        switch (r.keyCode) {
                            case i.PAGE_UP:
                                this._move("previousPage", r);
                                break;
                            case i.PAGE_DOWN:
                                this._move("nextPage", r);
                                break;
                            case i.UP:
                                this._keyEvent("previous", r);
                                break;
                            case i.DOWN:
                                this._keyEvent("next", r)
                        }
                    }
                },
                input: function(e) {
                    return r ? (r = !1, void e.preventDefault()) : void this._searchTimeout(e)
                },
                focus: function() {
                    this.selectedItem = null, this.previous = this._value()
                },
                blur: function(e) {
                    return this.cancelBlur ? void delete this.cancelBlur : (clearTimeout(this.searching), this.close(e), this._change(e), void 0)
                }
            }), this._initSource(), this.menu = e("<ul>").addClass("ui-autocomplete").appendTo(this.document.find(this.options.appendTo || "body")[0]).menu({
                input: e(),
                role: null
            }).zIndex(this.element.zIndex() + 1).hide().data("menu"), this._on(this.menu.element, {
                mousedown: function(t) {
                    t.preventDefault(), this.cancelBlur = !0, this._delay(function() {
                        delete this.cancelBlur
                    });
                    var n = this.menu.element[0];
                    e(t.target).closest(".ui-menu-item").length || this._delay(function() {
                        var t = this;
                        this.document.one("mousedown", function(r) {
                            r.target !== t.element[0] && r.target !== n && !e.contains(n, r.target) && t.close()
                        })
                    })
                },
                menufocus: function(t, n) {
                    if (this.isNewMenu && (this.isNewMenu = !1, t.originalEvent && /^mouse/.test(t.originalEvent.type))) return this.menu.blur(), void this.document.one("mousemove", function() {
                        e(t.target).trigger(t.originalEvent)
                    });
                    var r = n.item.data("ui-autocomplete-item") || n.item.data("item.autocomplete");
                    !1 !== this._trigger("focus", t, {
                        item: r
                    }) ? t.originalEvent && /^key/.test(t.originalEvent.type) && this._value(r.value) : this.liveRegion.text(r.value)
                },
                menuselect: function(e, t) {
                    var n = t.item.data("ui-autocomplete-item") || t.item.data("item.autocomplete"),
                        r = this.previous;
                    this.element[0] !== this.document[0].activeElement && (this.element.focus(), this.previous = r, this._delay(function() {
                        this.previous = r, this.selectedItem = n
                    })), !1 !== this._trigger("select", e, {
                        item: n
                    }) && this._value(n.value), this.term = this._value(), this.close(e), this.selectedItem = n
                }
            }), this.liveRegion = e("<span>", {
                role: "status",
                "aria-live": "polite"
            }).addClass("ui-helper-hidden-accessible").insertAfter(this.element), e.fn.bgiframe && this.menu.element.bgiframe(), this._on(this.window, {
                beforeunload: function() {
                    this.element.removeAttr("autocomplete")
                }
            })
        },
        _destroy: function() {
            clearTimeout(this.searching), this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete"), this.menu.element.remove(), this.liveRegion.remove()
        },
        _setOption: function(e, t) {
            this._super(e, t), "source" === e && this._initSource(), "appendTo" === e && this.menu.element.appendTo(this.document.find(t || "body")[0]), "disabled" === e && t && this.xhr && this.xhr.abort()
        },
        _isMultiLine: function() {
            return this.element.is("textarea") ? !0 : this.element.is("input") ? !1 : this.element.prop("isContentEditable")
        },
        _initSource: function() {
            var t, n, r = this;
            e.isArray(this.options.source) ? (t = this.options.source, this.source = function(n, r) {
                r(e.ui.autocomplete.filter(t, n.term))
            }) : "string" == typeof this.options.source ? (n = this.options.source, this.source = function(t, i) {
                r.xhr && r.xhr.abort(), r.xhr = e.ajax({
                    url: n,
                    data: t,
                    dataType: "json",
                    success: function(e) {
                        i(e)
                    },
                    error: function() {
                        i([])
                    }
                })
            }) : this.source = this.options.source
        },
        _searchTimeout: function(e) {
            clearTimeout(this.searching), this.searching = this._delay(function() {
                this.term !== this._value() && (this.selectedItem = null, this.search(null, e))
            }, this.options.delay)
        },
        search: function(e, t) {
            return e = null != e ? e : this._value(), this.term = this._value(), e.length < this.options.minLength ? this.close(t) : this._trigger("search", t) !== !1 ? this._search(e) : void 0
        },
        _search: function(e) {
            this.pending++, this.element.addClass("ui-autocomplete-loading"), this.cancelSearch = !1, this.source({
                term: e
            }, this._response())
        },
        _response: function() {
            var e = this,
                t = ++n;
            return function(r) {
                t === n && e.__response(r), e.pending--, e.pending || e.element.removeClass("ui-autocomplete-loading")
            }
        },
        __response: function(e) {
            e && (e = this._normalize(e)), this._trigger("response", null, {
                content: e
            }), !this.options.disabled && e && e.length && !this.cancelSearch ? (this._suggest(e), this._trigger("open")) : this._close()
        },
        close: function(e) {
            this.cancelSearch = !0, this._close(e)
        },
        _close: function(e) {
            this.menu.element.is(":visible") && (this.menu.element.hide(), this.menu.blur(), this.isNewMenu = !0, this._trigger("close", e))
        },
        _change: function(e) {
            this.previous !== this._value() && this._trigger("change", e, {
                item: this.selectedItem
            })
        },
        _normalize: function(t) {
            return t.length && t[0].label && t[0].value ? t : e.map(t, function(t) {
                return "string" == typeof t ? {
                    label: t,
                    value: t
                } : e.extend({
                    label: t.label || t.value,
                    value: t.value || t.label
                }, t)
            })
        },
        _suggest: function(t) {
            var n = this.menu.element.empty().zIndex(this.element.zIndex() + 1);
            this._renderMenu(n, t), this.menu.refresh(), n.show(), this._resizeMenu(), n.position(e.extend({
                of: this.element
            }, this.options.position)), this.options.autoFocus && this.menu.next()
        },
        _resizeMenu: function() {
            var e = this.menu.element;
            e.outerWidth(Math.max(e.width("").outerWidth() + 1, this.element.outerWidth()))
        },
        _renderMenu: function(t, n) {
            var r = this;
            e.each(n, function(e, n) {
                r._renderItemData(t, n)
            })
        },
        _renderItemData: function(e, t) {
            return this._renderItem(e, t).data("ui-autocomplete-item", t)
        },
        _renderItem: function(t, n) {
            return e("<li>").append(e("<a>").text(n.label)).appendTo(t)
        },
        _move: function(e, t) {
            return this.menu.element.is(":visible") ? this.menu.isFirstItem() && /^previous/.test(e) || this.menu.isLastItem() && /^next/.test(e) ? (this._value(this.term), void this.menu.blur()) : void this.menu[e](t) : void this.search(null, t)
        },
        widget: function() {
            return this.menu.element
        },
        _value: function() {
            return this.valueMethod.apply(this.element, arguments)
        },
        _keyEvent: function(e, t) {
            (!this.isMultiLine || this.menu.element.is(":visible")) && (this._move(e, t), t.preventDefault())
        }
    }), e.extend(e.ui.autocomplete, {
        escapeRegex: function(e) {
            return e.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&")
        },
        filter: function(t, n) {
            var r = new RegExp(e.ui.autocomplete.escapeRegex(n), "i");
            return e.grep(t, function(e) {
                return r.test(e.label || e.value || e)
            })
        }
    }), e.widget("ui.autocomplete", e.ui.autocomplete, {
        options: {
            messages: {
                noResults: "No search results.",
                results: function(e) {
                    return e + (e > 1 ? " results are" : " result is") + " available, use up and down arrow keys to navigate."
                }
            }
        },
        __response: function(e) {
            var t;
            this._superApply(arguments), this.options.disabled || this.cancelSearch || (t = e && e.length ? this.options.messages.results(e.length) : this.options.messages.noResults, this.liveRegion.text(t))
        }
    })
}(jQuery),
function(e) {
    var n, r, i, s, o = "ui-button ui-widget ui-state-default ui-corner-all",
        u = "ui-state-hover ui-state-active ",
        a = "ui-button-icons-only ui-button-icon-only ui-button-text-icons ui-button-text-icon-primary ui-button-text-icon-secondary ui-button-text-only",
        f = function() {
            var t = e(this).find(":ui-button");
            setTimeout(function() {
                t.button("refresh")
            }, 1)
        },
        l = function(t) {
            var n = t.name,
                r = t.form,
                i = e([]);
            return n && (i = r ? e(r).find("[name='" + n + "']") : e("[name='" + n + "']", t.ownerDocument).filter(function() {
                return !this.form
            })), i
        };
    e.widget("ui.button", {
        version: "1.9.2",
        defaultElement: "<button>",
        options: {
            disabled: null,
            text: !0,
            label: null,
            icons: {
                primary: null,
                secondary: null
            }
        },
        _create: function() {
            this.element.closest("form").unbind("reset" + this.eventNamespace).bind("reset" + this.eventNamespace, f), "boolean" != typeof this.options.disabled ? this.options.disabled = !!this.element.prop("disabled") : this.element.prop("disabled", this.options.disabled), this._determineButtonType(), this.hasTitle = !!this.buttonElement.attr("title");
            var t = this,
                u = this.options,
                a = "checkbox" === this.type || "radio" === this.type,
                c = a ? "" : "ui-state-active",
                h = "ui-state-focus";
            null === u.label && (u.label = "input" === this.type ? this.buttonElement.val() : this.buttonElement.html()), this._hoverable(this.buttonElement), this.buttonElement.addClass(o).attr("role", "button").bind("mouseenter" + this.eventNamespace, function() {
                u.disabled || this === n && e(this).addClass("ui-state-active")
            }).bind("mouseleave" + this.eventNamespace, function() {
                u.disabled || e(this).removeClass(c)
            }).bind("click" + this.eventNamespace, function(e) {
                u.disabled && (e.preventDefault(), e.stopImmediatePropagation())
            }), this.element.bind("focus" + this.eventNamespace, function() {
                t.buttonElement.addClass(h)
            }).bind("blur" + this.eventNamespace, function() {
                t.buttonElement.removeClass(h)
            }), a && (this.element.bind("change" + this.eventNamespace, function() {
                s || t.refresh()
            }), this.buttonElement.bind("mousedown" + this.eventNamespace, function(e) {
                u.disabled || (s = !1, r = e.pageX, i = e.pageY)
            }).bind("mouseup" + this.eventNamespace, function(e) {
                u.disabled || (r !== e.pageX || i !== e.pageY) && (s = !0)
            })), "checkbox" === this.type ? this.buttonElement.bind("click" + this.eventNamespace, function() {
                return u.disabled || s ? !1 : (e(this).toggleClass("ui-state-active"), void t.buttonElement.attr("aria-pressed", t.element[0].checked))
            }) : "radio" === this.type ? this.buttonElement.bind("click" + this.eventNamespace, function() {
                if (u.disabled || s) return !1;
                e(this).addClass("ui-state-active"), t.buttonElement.attr("aria-pressed", "true");
                var n = t.element[0];
                l(n).not(n).map(function() {
                    return e(this).button("widget")[0]
                }).removeClass("ui-state-active").attr("aria-pressed", "false")
            }) : (this.buttonElement.bind("mousedown" + this.eventNamespace, function() {
                return u.disabled ? !1 : (e(this).addClass("ui-state-active"), n = this, t.document.one("mouseup", function() {
                    n = null
                }), void 0)
            }).bind("mouseup" + this.eventNamespace, function() {
                return u.disabled ? !1 : void e(this).removeClass("ui-state-active")
            }).bind("keydown" + this.eventNamespace, function(t) {
                return u.disabled ? !1 : void((t.keyCode === e.ui.keyCode.SPACE || t.keyCode === e.ui.keyCode.ENTER) && e(this).addClass("ui-state-active"))
            }).bind("keyup" + this.eventNamespace, function() {
                e(this).removeClass("ui-state-active")
            }), this.buttonElement.is("a") && this.buttonElement.keyup(function(t) {
                t.keyCode === e.ui.keyCode.SPACE && e(this).click()
            })), this._setOption("disabled", u.disabled), this._resetButton()
        },
        _determineButtonType: function() {
            var e, t, n;
            this.type = this.element.is("[type=checkbox]") ? "checkbox" : this.element.is("[type=radio]") ? "radio" : this.element.is("input") ? "input" : "button", "checkbox" === this.type || "radio" === this.type ? (e = this.element.parents().last(), t = "label[for='" + this.element.attr("id") + "']", this.buttonElement = e.find(t), this.buttonElement.length || (e = e.length ? e.siblings() : this.element.siblings(), this.buttonElement = e.filter(t), this.buttonElement.length || (this.buttonElement = e.find(t))), this.element.addClass("ui-helper-hidden-accessible"), n = this.element.is(":checked"), n && this.buttonElement.addClass("ui-state-active"), this.buttonElement.prop("aria-pressed", n)) : this.buttonElement = this.element
        },
        widget: function() {
            return this.buttonElement
        },
        _destroy: function() {
            this.element.removeClass("ui-helper-hidden-accessible"), this.buttonElement.removeClass(o + " " + u + " " + a).removeAttr("role").removeAttr("aria-pressed").html(this.buttonElement.find(".ui-button-text").html()), this.hasTitle || this.buttonElement.removeAttr("title")
        },
        _setOption: function(e, t) {
            return this._super(e, t), "disabled" === e ? void(t ? this.element.prop("disabled", !0) : this.element.prop("disabled", !1)) : void this._resetButton()
        },
        refresh: function() {
            var t = this.element.is("input, button") ? this.element.is(":disabled") : this.element.hasClass("ui-button-disabled");
            t !== this.options.disabled && this._setOption("disabled", t), "radio" === this.type ? l(this.element[0]).each(function() {
                e(this).is(":checked") ? e(this).button("widget").addClass("ui-state-active").attr("aria-pressed", "true") : e(this).button("widget").removeClass("ui-state-active").attr("aria-pressed", "false")
            }) : "checkbox" === this.type && (this.element.is(":checked") ? this.buttonElement.addClass("ui-state-active").attr("aria-pressed", "true") : this.buttonElement.removeClass("ui-state-active").attr("aria-pressed", "false"))
        },
        _resetButton: function() {
            if ("input" === this.type) return void(this.options.label && this.element.val(this.options.label));
            var t = this.buttonElement.removeClass(a),
                n = e("<span></span>", this.document[0]).addClass("ui-button-text").html(this.options.label).appendTo(t.empty()).text(),
                r = this.options.icons,
                i = r.primary && r.secondary,
                s = [];
            r.primary || r.secondary ? (this.options.text && s.push("ui-button-text-icon" + (i ? "s" : r.primary ? "-primary" : "-secondary")), r.primary && t.prepend("<span class='ui-button-icon-primary ui-icon " + r.primary + "'></span>"), r.secondary && t.append("<span class='ui-button-icon-secondary ui-icon " + r.secondary + "'></span>"), this.options.text || (s.push(i ? "ui-button-icons-only" : "ui-button-icon-only"), this.hasTitle || t.attr("title", e.trim(n)))) : s.push("ui-button-text-only"), t.addClass(s.join(" "))
        }
    }), e.widget("ui.buttonset", {
        version: "1.9.2",
        options: {
            items: "button, input[type=button], input[type=submit], input[type=reset], input[type=checkbox], input[type=radio], a, :data(button)"
        },
        _create: function() {
            this.element.addClass("ui-buttonset")
        },
        _init: function() {
            this.refresh()
        },
        _setOption: function(e, t) {
            "disabled" === e && this.buttons.button("option", e, t), this._super(e, t)
        },
        refresh: function() {
            var t = "rtl" === this.element.css("direction");
            this.buttons = this.element.find(this.options.items).filter(":ui-button").button("refresh").end().not(":ui-button").button().end().map(function() {
                return e(this).button("widget")[0]
            }).removeClass("ui-corner-all ui-corner-left ui-corner-right").filter(":first").addClass(t ? "ui-corner-right" : "ui-corner-left").end().filter(":last").addClass(t ? "ui-corner-left" : "ui-corner-right").end().end()
        },
        _destroy: function() {
            this.element.removeClass("ui-buttonset"), this.buttons.map(function() {
                return e(this).button("widget")[0]
            }).removeClass("ui-corner-left ui-corner-right").end().button("destroy")
        }
    })
}(jQuery),
function($, undefined) {
    function Datepicker() {
        this.debug = !1, this._curInst = null, this._keyEvent = !1, this._disabledInputs = [], this._datepickerShowing = !1, this._inDialog = !1, this._mainDivId = "ui-datepicker-div", this._inlineClass = "ui-datepicker-inline", this._appendClass = "ui-datepicker-append", this._triggerClass = "ui-datepicker-trigger", this._dialogClass = "ui-datepicker-dialog", this._disableClass = "ui-datepicker-disabled", this._unselectableClass = "ui-datepicker-unselectable", this._currentClass = "ui-datepicker-current-day", this._dayOverClass = "ui-datepicker-days-cell-over", this.regional = [], this.regional[""] = {
            closeText: "Done",
            prevText: "Prev",
            nextText: "Next",
            currentText: "Today",
            monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            dayNamesMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
            weekHeader: "Wk",
            dateFormat: "mm/dd/yy",
            firstDay: 0,
            isRTL: !1,
            showMonthAfterYear: !1,
            yearSuffix: ""
        }, this._defaults = {
            showOn: "focus",
            showAnim: "fadeIn",
            showOptions: {},
            defaultDate: null,
            appendText: "",
            buttonText: "...",
            buttonImage: "",
            buttonImageOnly: !1,
            hideIfNoPrevNext: !1,
            navigationAsDateFormat: !1,
            gotoCurrent: !1,
            changeMonth: !1,
            changeYear: !1,
            yearRange: "c-10:c+10",
            showOtherMonths: !1,
            selectOtherMonths: !1,
            showWeek: !1,
            calculateWeek: this.iso8601Week,
            shortYearCutoff: "+10",
            minDate: null,
            maxDate: null,
            duration: "fast",
            beforeShowDay: null,
            beforeShow: null,
            onSelect: null,
            onChangeMonthYear: null,
            onClose: null,
            numberOfMonths: 1,
            showCurrentAtPos: 0,
            stepMonths: 1,
            stepBigMonths: 12,
            altField: "",
            altFormat: "",
            constrainInput: !0,
            showButtonPanel: !1,
            autoSize: !1,
            disabled: !1
        }, $.extend(this._defaults, this.regional[""]), this.dpDiv = bindHover($('<div id="' + this._mainDivId + '" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>'))
    }

    function bindHover(e) {
        var t = "button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a";
        return e.delegate(t, "mouseout", function() {
            $(this).removeClass("ui-state-hover"), -1 != this.className.indexOf("ui-datepicker-prev") && $(this).removeClass("ui-datepicker-prev-hover"), -1 != this.className.indexOf("ui-datepicker-next") && $(this).removeClass("ui-datepicker-next-hover")
        }).delegate(t, "mouseover", function() {
            $.datepicker._isDisabledDatepicker(instActive.inline ? e.parent()[0] : instActive.input[0]) || ($(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover"), $(this).addClass("ui-state-hover"), -1 != this.className.indexOf("ui-datepicker-prev") && $(this).addClass("ui-datepicker-prev-hover"), -1 != this.className.indexOf("ui-datepicker-next") && $(this).addClass("ui-datepicker-next-hover"))
        })
    }

    function extendRemove(e, t) {
        $.extend(e, t);
        for (var n in t)(null == t[n] || t[n] == undefined) && (e[n] = t[n]);
        return e
    }
    $.extend($.ui, {
        datepicker: {
            version: "1.9.2"
        }
    });
    var PROP_NAME = "datepicker",
        dpuuid = (new Date).getTime(),
        instActive;
    $.extend(Datepicker.prototype, {
        markerClassName: "hasDatepicker",
        maxRows: 4,
        log: function() {
            this.debug && console.log.apply("", arguments)
        },
        _widgetDatepicker: function() {
            return this.dpDiv
        },
        setDefaults: function(e) {
            return extendRemove(this._defaults, e || {}), this
        },
        _attachDatepicker: function(target, settings) {
            var inlineSettings = null;
            for (var attrName in this._defaults) {
                var attrValue = target.getAttribute("date:" + attrName);
                if (attrValue) {
                    inlineSettings = inlineSettings || {};
                    try {
                        inlineSettings[attrName] = eval(attrValue)
                    } catch (err) {
                        inlineSettings[attrName] = attrValue
                    }
                }
            }
            var nodeName = target.nodeName.toLowerCase(),
                inline = "div" == nodeName || "span" == nodeName;
            target.id || (this.uuid += 1, target.id = "dp" + this.uuid);
            var inst = this._newInst($(target), inline);
            inst.settings = $.extend({}, settings || {}, inlineSettings || {}), "input" == nodeName ? this._connectDatepicker(target, inst) : inline && this._inlineDatepicker(target, inst)
        },
        _newInst: function(e, t) {
            var n = e[0].id.replace(/([^A-Za-z0-9_-])/g, "\\\\$1");
            return {
                id: n,
                input: e,
                selectedDay: 0,
                selectedMonth: 0,
                selectedYear: 0,
                drawMonth: 0,
                drawYear: 0,
                inline: t,
                dpDiv: t ? bindHover($('<div class="' + this._inlineClass + ' ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>')) : this.dpDiv
            }
        },
        _connectDatepicker: function(e, t) {
            var n = $(e);
            t.append = $([]), t.trigger = $([]), n.hasClass(this.markerClassName) || (this._attachments(n, t), n.addClass(this.markerClassName).keydown(this._doKeyDown).keypress(this._doKeyPress).keyup(this._doKeyUp).bind("setData.datepicker", function(e, n, r) {
                t.settings[n] = r
            }).bind("getData.datepicker", function(e, n) {
                return this._get(t, n)
            }), this._autoSize(t), $.data(e, PROP_NAME, t), t.settings.disabled && this._disableDatepicker(e))
        },
        _attachments: function(e, t) {
            var n = this._get(t, "appendText"),
                r = this._get(t, "isRTL");
            t.append && t.append.remove(), n && (t.append = $('<span class="' + this._appendClass + '">' + n + "</span>"), e[r ? "before" : "after"](t.append)), e.unbind("focus", this._showDatepicker), t.trigger && t.trigger.remove();
            var i = this._get(t, "showOn");
            if (("focus" == i || "both" == i) && e.focus(this._showDatepicker), "button" == i || "both" == i) {
                var s = this._get(t, "buttonText"),
                    o = this._get(t, "buttonImage");
                t.trigger = $(this._get(t, "buttonImageOnly") ? $("<img/>").addClass(this._triggerClass).attr({
                    src: o,
                    alt: s,
                    title: s
                }) : $('<button type="button"></button>').addClass(this._triggerClass).html("" == o ? s : $("<img/>").attr({
                    src: o,
                    alt: s,
                    title: s
                }))), e[r ? "before" : "after"](t.trigger), t.trigger.click(function() {
                    return $.datepicker._datepickerShowing && $.datepicker._lastInput == e[0] ? $.datepicker._hideDatepicker() : $.datepicker._datepickerShowing && $.datepicker._lastInput != e[0] ? ($.datepicker._hideDatepicker(), $.datepicker._showDatepicker(e[0])) : $.datepicker._showDatepicker(e[0]), !1
                })
            }
        },
        _autoSize: function(e) {
            if (this._get(e, "autoSize") && !e.inline) {
                var t = new Date(2009, 11, 20),
                    n = this._get(e, "dateFormat");
                if (n.match(/[DM]/)) {
                    var r = function(e) {
                        for (var t = 0, n = 0, r = 0; r < e.length; r++) e[r].length > t && (t = e[r].length, n = r);
                        return n
                    };
                    t.setMonth(r(this._get(e, n.match(/MM/) ? "monthNames" : "monthNamesShort"))), t.setDate(r(this._get(e, n.match(/DD/) ? "dayNames" : "dayNamesShort")) + 20 - t.getDay())
                }
                e.input.attr("size", this._formatDate(e, t).length)
            }
        },
        _inlineDatepicker: function(e, t) {
            var n = $(e);
            n.hasClass(this.markerClassName) || (n.addClass(this.markerClassName).append(t.dpDiv).bind("setData.datepicker", function(e, n, r) {
                t.settings[n] = r
            }).bind("getData.datepicker", function(e, n) {
                return this._get(t, n)
            }), $.data(e, PROP_NAME, t), this._setDate(t, this._getDefaultDate(t), !0), this._updateDatepicker(t), this._updateAlternate(t), t.settings.disabled && this._disableDatepicker(e), t.dpDiv.css("display", "block"))
        },
        _dialogDatepicker: function(e, t, n, r, i) {
            var s = this._dialogInst;
            if (!s) {
                this.uuid += 1;
                var o = "dp" + this.uuid;
                this._dialogInput = $('<input type="text" id="' + o + '" style="position: absolute; top: -100px; width: 0px;"/>'), this._dialogInput.keydown(this._doKeyDown), $("body").append(this._dialogInput), s = this._dialogInst = this._newInst(this._dialogInput, !1), s.settings = {}, $.data(this._dialogInput[0], PROP_NAME, s)
            }
            if (extendRemove(s.settings, r || {}), t = t && t.constructor == Date ? this._formatDate(s, t) : t, this._dialogInput.val(t), this._pos = i ? i.length ? i : [i.pageX, i.pageY] : null, !this._pos) {
                var u = document.documentElement.clientWidth,
                    a = document.documentElement.clientHeight,
                    f = document.documentElement.scrollLeft || document.body.scrollLeft,
                    l = document.documentElement.scrollTop || document.body.scrollTop;
                this._pos = [u / 2 - 100 + f, a / 2 - 150 + l]
            }
            return this._dialogInput.css("left", this._pos[0] + 20 + "px").css("top", this._pos[1] + "px"), s.settings.onSelect = n, this._inDialog = !0, this.dpDiv.addClass(this._dialogClass), this._showDatepicker(this._dialogInput[0]), $.blockUI && $.blockUI(this.dpDiv), $.data(this._dialogInput[0], PROP_NAME, s), this
        },
        _destroyDatepicker: function(e) {
            var t = $(e),
                n = $.data(e, PROP_NAME);
            if (t.hasClass(this.markerClassName)) {
                var r = e.nodeName.toLowerCase();
                $.removeData(e, PROP_NAME), "input" == r ? (n.append.remove(), n.trigger.remove(), t.removeClass(this.markerClassName).unbind("focus", this._showDatepicker).unbind("keydown", this._doKeyDown).unbind("keypress", this._doKeyPress).unbind("keyup", this._doKeyUp)) : ("div" == r || "span" == r) && t.removeClass(this.markerClassName).empty()
            }
        },
        _enableDatepicker: function(e) {
            var t = $(e),
                n = $.data(e, PROP_NAME);
            if (t.hasClass(this.markerClassName)) {
                var r = e.nodeName.toLowerCase();
                if ("input" == r) e.disabled = !1, n.trigger.filter("button").each(function() {
                    this.disabled = !1
                }).end().filter("img").css({
                    opacity: "1.0",
                    cursor: ""
                });
                else if ("div" == r || "span" == r) {
                    var i = t.children("." + this._inlineClass);
                    i.children().removeClass("ui-state-disabled"), i.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", !1)
                }
                this._disabledInputs = $.map(this._disabledInputs, function(t) {
                    return t == e ? null : t
                })
            }
        },
        _disableDatepicker: function(e) {
            var t = $(e),
                n = $.data(e, PROP_NAME);
            if (t.hasClass(this.markerClassName)) {
                var r = e.nodeName.toLowerCase();
                if ("input" == r) e.disabled = !0, n.trigger.filter("button").each(function() {
                    this.disabled = !0
                }).end().filter("img").css({
                    opacity: "0.5",
                    cursor: "default"
                });
                else if ("div" == r || "span" == r) {
                    var i = t.children("." + this._inlineClass);
                    i.children().addClass("ui-state-disabled"), i.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", !0)
                }
                this._disabledInputs = $.map(this._disabledInputs, function(t) {
                    return t == e ? null : t
                }), this._disabledInputs[this._disabledInputs.length] = e
            }
        },
        _isDisabledDatepicker: function(e) {
            if (!e) return !1;
            for (var t = 0; t < this._disabledInputs.length; t++)
                if (this._disabledInputs[t] == e) return !0;
            return !1
        },
        _getInst: function(e) {
            try {
                return $.data(e, PROP_NAME)
            } catch (t) {
                throw "Missing instance data for this datepicker"
            }
        },
        _optionDatepicker: function(e, t, n) {
            var r = this._getInst(e);
            if (2 == arguments.length && "string" == typeof t) return "defaults" == t ? $.extend({}, $.datepicker._defaults) : r ? "all" == t ? $.extend({}, r.settings) : this._get(r, t) : null;
            var i = t || {};
            if ("string" == typeof t && (i = {}, i[t] = n), r) {
                this._curInst == r && this._hideDatepicker();
                var s = this._getDateDatepicker(e, !0),
                    o = this._getMinMaxDate(r, "min"),
                    u = this._getMinMaxDate(r, "max");
                extendRemove(r.settings, i), null !== o && i.dateFormat !== undefined && i.minDate === undefined && (r.settings.minDate = this._formatDate(r, o)), null !== u && i.dateFormat !== undefined && i.maxDate === undefined && (r.settings.maxDate = this._formatDate(r, u)), this._attachments($(e), r), this._autoSize(r), this._setDate(r, s), this._updateAlternate(r), this._updateDatepicker(r)
            }
        },
        _changeDatepicker: function(e, t, n) {
            this._optionDatepicker(e, t, n)
        },
        _refreshDatepicker: function(e) {
            var t = this._getInst(e);
            t && this._updateDatepicker(t)
        },
        _setDateDatepicker: function(e, t) {
            var n = this._getInst(e);
            n && (this._setDate(n, t), this._updateDatepicker(n), this._updateAlternate(n))
        },
        _getDateDatepicker: function(e, t) {
            var n = this._getInst(e);
            return n && !n.inline && this._setDateFromField(n, t), n ? this._getDate(n) : null
        },
        _doKeyDown: function(e) {
            var t = $.datepicker._getInst(e.target),
                n = !0,
                r = t.dpDiv.is(".ui-datepicker-rtl");
            if (t._keyEvent = !0, $.datepicker._datepickerShowing) switch (e.keyCode) {
                case 9:
                    $.datepicker._hideDatepicker(), n = !1;
                    break;
                case 13:
                    var i = $("td." + $.datepicker._dayOverClass + ":not(." + $.datepicker._currentClass + ")", t.dpDiv);
                    i[0] && $.datepicker._selectDay(e.target, t.selectedMonth, t.selectedYear, i[0]);
                    var s = $.datepicker._get(t, "onSelect");
                    if (s) {
                        var o = $.datepicker._formatDate(t);
                        s.apply(t.input ? t.input[0] : null, [o, t])
                    } else $.datepicker._hideDatepicker();
                    return !1;
                case 27:
                    $.datepicker._hideDatepicker();
                    break;
                case 33:
                    $.datepicker._adjustDate(e.target, e.ctrlKey ? -$.datepicker._get(t, "stepBigMonths") : -$.datepicker._get(t, "stepMonths"), "M");
                    break;
                case 34:
                    $.datepicker._adjustDate(e.target, e.ctrlKey ? +$.datepicker._get(t, "stepBigMonths") : +$.datepicker._get(t, "stepMonths"), "M");
                    break;
                case 35:
                    (e.ctrlKey || e.metaKey) && $.datepicker._clearDate(e.target), n = e.ctrlKey || e.metaKey;
                    break;
                case 36:
                    (e.ctrlKey || e.metaKey) && $.datepicker._gotoToday(e.target), n = e.ctrlKey || e.metaKey;
                    break;
                case 37:
                    (e.ctrlKey || e.metaKey) && $.datepicker._adjustDate(e.target, r ? 1 : -1, "D"), n = e.ctrlKey || e.metaKey, e.originalEvent.altKey && $.datepicker._adjustDate(e.target, e.ctrlKey ? -$.datepicker._get(t, "stepBigMonths") : -$.datepicker._get(t, "stepMonths"), "M");
                    break;
                case 38:
                    (e.ctrlKey || e.metaKey) && $.datepicker._adjustDate(e.target, -7, "D"), n = e.ctrlKey || e.metaKey;
                    break;
                case 39:
                    (e.ctrlKey || e.metaKey) && $.datepicker._adjustDate(e.target, r ? -1 : 1, "D"), n = e.ctrlKey || e.metaKey, e.originalEvent.altKey && $.datepicker._adjustDate(e.target, e.ctrlKey ? +$.datepicker._get(t, "stepBigMonths") : +$.datepicker._get(t, "stepMonths"), "M");
                    break;
                case 40:
                    (e.ctrlKey || e.metaKey) && $.datepicker._adjustDate(e.target, 7, "D"), n = e.ctrlKey || e.metaKey;
                    break;
                default:
                    n = !1
            } else 36 == e.keyCode && e.ctrlKey ? $.datepicker._showDatepicker(this) : n = !1;
            n && (e.preventDefault(), e.stopPropagation())
        },
        _doKeyPress: function(e) {
            var t = $.datepicker._getInst(e.target);
            if ($.datepicker._get(t, "constrainInput")) {
                var n = $.datepicker._possibleChars($.datepicker._get(t, "dateFormat")),
                    r = String.fromCharCode(e.charCode == undefined ? e.keyCode : e.charCode);
                return e.ctrlKey || e.metaKey || " " > r || !n || n.indexOf(r) > -1
            }
        },
        _doKeyUp: function(e) {
            var t = $.datepicker._getInst(e.target);
            if (t.input.val() != t.lastVal) try {
                var n = $.datepicker.parseDate($.datepicker._get(t, "dateFormat"), t.input ? t.input.val() : null, $.datepicker._getFormatConfig(t));
                n && ($.datepicker._setDateFromField(t), $.datepicker._updateAlternate(t), $.datepicker._updateDatepicker(t))
            } catch (r) {
                $.datepicker.log(r)
            }
            return !0
        },
        _showDatepicker: function(e) {
            if (e = e.target || e, "input" != e.nodeName.toLowerCase() && (e = $("input", e.parentNode)[0]), !$.datepicker._isDisabledDatepicker(e) && $.datepicker._lastInput != e) {
                var t = $.datepicker._getInst(e);
                $.datepicker._curInst && $.datepicker._curInst != t && ($.datepicker._curInst.dpDiv.stop(!0, !0), t && $.datepicker._datepickerShowing && $.datepicker._hideDatepicker($.datepicker._curInst.input[0]));
                var n = $.datepicker._get(t, "beforeShow"),
                    r = n ? n.apply(e, [e, t]) : {};
                if (r !== !1) {
                    extendRemove(t.settings, r), t.lastVal = null, $.datepicker._lastInput = e, $.datepicker._setDateFromField(t), $.datepicker._inDialog && (e.value = ""), $.datepicker._pos || ($.datepicker._pos = $.datepicker._findPos(e), $.datepicker._pos[1] += e.offsetHeight);
                    var i = !1;
                    $(e).parents().each(function() {
                        return i |= "fixed" == $(this).css("position"), !i
                    });
                    var s = {
                        left: $.datepicker._pos[0],
                        top: $.datepicker._pos[1]
                    };
                    if ($.datepicker._pos = null, t.dpDiv.empty(), t.dpDiv.css({
                            position: "absolute",
                            display: "block",
                            top: "-1000px"
                        }), $.datepicker._updateDatepicker(t), s = $.datepicker._checkOffset(t, s, i), t.dpDiv.css({
                            position: $.datepicker._inDialog && $.blockUI ? "static" : i ? "fixed" : "absolute",
                            display: "none",
                            left: s.left + "px",
                            top: s.top + "px"
                        }), !t.inline) {
                        var o = $.datepicker._get(t, "showAnim"),
                            u = $.datepicker._get(t, "duration"),
                            a = function() {
                                var e = t.dpDiv.find("iframe.ui-datepicker-cover");
                                if (e.length) {
                                    var n = $.datepicker._getBorders(t.dpDiv);
                                    e.css({
                                        left: -n[0],
                                        top: -n[1],
                                        width: t.dpDiv.outerWidth(),
                                        height: t.dpDiv.outerHeight()
                                    })
                                }
                            };
                        t.dpDiv.zIndex($(e).zIndex() + 1), $.datepicker._datepickerShowing = !0, $.effects && ($.effects.effect[o] || $.effects[o]) ? t.dpDiv.show(o, $.datepicker._get(t, "showOptions"), u, a) : t.dpDiv[o || "show"](o ? u : null, a), (!o || !u) && a(), t.input.is(":visible") && !t.input.is(":disabled") && t.input.focus(), $.datepicker._curInst = t
                    }
                }
            }
        },
        _updateDatepicker: function(e) {
            this.maxRows = 4;
            var t = $.datepicker._getBorders(e.dpDiv);
            instActive = e, e.dpDiv.empty().append(this._generateHTML(e)), this._attachHandlers(e);
            var n = e.dpDiv.find("iframe.ui-datepicker-cover");
            !n.length || n.css({
                left: -t[0],
                top: -t[1],
                width: e.dpDiv.outerWidth(),
                height: e.dpDiv.outerHeight()
            }), e.dpDiv.find("." + this._dayOverClass + " a").mouseover();
            var r = this._getNumberOfMonths(e),
                i = r[1],
                s = 17;
            if (e.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width(""), i > 1 && e.dpDiv.addClass("ui-datepicker-multi-" + i).css("width", s * i + "em"), e.dpDiv[(1 != r[0] || 1 != r[1] ? "add" : "remove") + "Class"]("ui-datepicker-multi"), e.dpDiv[(this._get(e, "isRTL") ? "add" : "remove") + "Class"]("ui-datepicker-rtl"), e == $.datepicker._curInst && $.datepicker._datepickerShowing && e.input && e.input.is(":visible") && !e.input.is(":disabled") && e.input[0] != document.activeElement && e.input.focus(), e.yearshtml) {
                var o = e.yearshtml;
                setTimeout(function() {
                    o === e.yearshtml && e.yearshtml && e.dpDiv.find("select.ui-datepicker-year:first").replaceWith(e.yearshtml), o = e.yearshtml = null
                }, 0)
            }
        },
        _getBorders: function(e) {
            var t = function(e) {
                return {
                    thin: 1,
                    medium: 2,
                    thick: 3
                }[e] || e
            };
            return [parseFloat(t(e.css("border-left-width"))), parseFloat(t(e.css("border-top-width")))]
        },
        _checkOffset: function(e, t, n) {
            var r = e.dpDiv.outerWidth(),
                i = e.dpDiv.outerHeight(),
                s = e.input ? e.input.outerWidth() : 0,
                o = e.input ? e.input.outerHeight() : 0,
                u = document.documentElement.clientWidth + (n ? 0 : $(document).scrollLeft()),
                a = document.documentElement.clientHeight + (n ? 0 : $(document).scrollTop());
            return t.left -= this._get(e, "isRTL") ? r - s : 0, t.left -= n && t.left == e.input.offset().left ? $(document).scrollLeft() : 0, t.top -= n && t.top == e.input.offset().top + o ? $(document).scrollTop() : 0, t.left -= Math.min(t.left, t.left + r > u && u > r ? Math.abs(t.left + r - u) : 0), t.top -= Math.min(t.top, t.top + i > a && a > i ? Math.abs(i + o) : 0), t
        },
        _findPos: function(e) {
            for (var t = this._getInst(e), n = this._get(t, "isRTL"); e && ("hidden" == e.type || 1 != e.nodeType || $.expr.filters.hidden(e));) e = e[n ? "previousSibling" : "nextSibling"];
            var r = $(e).offset();
            return [r.left, r.top]
        },
        _hideDatepicker: function(e) {
            var t = this._curInst;
            if (t && (!e || t == $.data(e, PROP_NAME)) && this._datepickerShowing) {
                var n = this._get(t, "showAnim"),
                    r = this._get(t, "duration"),
                    i = function() {
                        $.datepicker._tidyDialog(t)
                    };
                $.effects && ($.effects.effect[n] || $.effects[n]) ? t.dpDiv.hide(n, $.datepicker._get(t, "showOptions"), r, i) : t.dpDiv["slideDown" == n ? "slideUp" : "fadeIn" == n ? "fadeOut" : "hide"](n ? r : null, i), n || i(), this._datepickerShowing = !1;
                var s = this._get(t, "onClose");
                s && s.apply(t.input ? t.input[0] : null, [t.input ? t.input.val() : "", t]), this._lastInput = null, this._inDialog && (this._dialogInput.css({
                    position: "absolute",
                    left: "0",
                    top: "-100px"
                }), $.blockUI && ($.unblockUI(), $("body").append(this.dpDiv))), this._inDialog = !1
            }
        },
        _tidyDialog: function(e) {
            e.dpDiv.removeClass(this._dialogClass).unbind(".ui-datepicker-calendar")
        },
        _checkExternalClick: function(e) {
            if ($.datepicker._curInst) {
                var t = $(e.target),
                    n = $.datepicker._getInst(t[0]);
                (t[0].id != $.datepicker._mainDivId && 0 == t.parents("#" + $.datepicker._mainDivId).length && !t.hasClass($.datepicker.markerClassName) && !t.closest("." + $.datepicker._triggerClass).length && $.datepicker._datepickerShowing && (!$.datepicker._inDialog || !$.blockUI) || t.hasClass($.datepicker.markerClassName) && $.datepicker._curInst != n) && $.datepicker._hideDatepicker()
            }
        },
        _adjustDate: function(e, t, n) {
            var r = $(e),
                i = this._getInst(r[0]);
            this._isDisabledDatepicker(r[0]) || (this._adjustInstDate(i, t + ("M" == n ? this._get(i, "showCurrentAtPos") : 0), n), this._updateDatepicker(i))
        },
        _gotoToday: function(e) {
            var t = $(e),
                n = this._getInst(t[0]);
            if (this._get(n, "gotoCurrent") && n.currentDay) n.selectedDay = n.currentDay, n.drawMonth = n.selectedMonth = n.currentMonth, n.drawYear = n.selectedYear = n.currentYear;
            else {
                var r = new Date;
                n.selectedDay = r.getDate(), n.drawMonth = n.selectedMonth = r.getMonth(), n.drawYear = n.selectedYear = r.getFullYear()
            }
            this._notifyChange(n), this._adjustDate(t)
        },
        _selectMonthYear: function(e, t, n) {
            var r = $(e),
                i = this._getInst(r[0]);
            i["selected" + ("M" == n ? "Month" : "Year")] = i["draw" + ("M" == n ? "Month" : "Year")] = parseInt(t.options[t.selectedIndex].value, 10), this._notifyChange(i), this._adjustDate(r)
        },
        _selectDay: function(e, t, n, r) {
            var i = $(e);
            if (!$(r).hasClass(this._unselectableClass) && !this._isDisabledDatepicker(i[0])) {
                var s = this._getInst(i[0]);
                s.selectedDay = s.currentDay = $("a", r).html(), s.selectedMonth = s.currentMonth = t, s.selectedYear = s.currentYear = n, this._selectDate(e, this._formatDate(s, s.currentDay, s.currentMonth, s.currentYear))
            }
        },
        _clearDate: function(e) {
            {
                var t = $(e);
                this._getInst(t[0])
            }
            this._selectDate(t, "")
        },
        _selectDate: function(e, t) {
            var n = $(e),
                r = this._getInst(n[0]);
            t = null != t ? t : this._formatDate(r), r.input && r.input.val(t), this._updateAlternate(r);
            var i = this._get(r, "onSelect");
            i ? i.apply(r.input ? r.input[0] : null, [t, r]) : r.input && r.input.trigger("change"), r.inline ? this._updateDatepicker(r) : (this._hideDatepicker(), this._lastInput = r.input[0], "object" != typeof r.input[0] && r.input.focus(), this._lastInput = null)
        },
        _updateAlternate: function(e) {
            var t = this._get(e, "altField");
            if (t) {
                var n = this._get(e, "altFormat") || this._get(e, "dateFormat"),
                    r = this._getDate(e),
                    i = this.formatDate(n, r, this._getFormatConfig(e));
                $(t).each(function() {
                    $(this).val(i)
                })
            }
        },
        noWeekends: function(e) {
            var t = e.getDay();
            return [t > 0 && 6 > t, ""]
        },
        iso8601Week: function(e) {
            var t = new Date(e.getTime());
            t.setDate(t.getDate() + 4 - (t.getDay() || 7));
            var n = t.getTime();
            return t.setMonth(0), t.setDate(1), Math.floor(Math.round((n - t) / 864e5) / 7) + 1
        },
        parseDate: function(e, t, n) {
            if (null == e || null == t) throw "Invalid arguments";
            if (t = "object" == typeof t ? t.toString() : t + "", "" == t) return null;
            var r = (n ? n.shortYearCutoff : null) || this._defaults.shortYearCutoff;
            r = "string" != typeof r ? r : (new Date).getFullYear() % 100 + parseInt(r, 10);
            for (var i = (n ? n.dayNamesShort : null) || this._defaults.dayNamesShort, s = (n ? n.dayNames : null) || this._defaults.dayNames, o = (n ? n.monthNamesShort : null) || this._defaults.monthNamesShort, u = (n ? n.monthNames : null) || this._defaults.monthNames, a = -1, f = -1, l = -1, c = -1, h = !1, p = function(t) {
                    var n = y + 1 < e.length && e.charAt(y + 1) == t;
                    return n && y++, n
                }, d = function(e) {
                    var n = p(e),
                        r = "@" == e ? 14 : "!" == e ? 20 : "y" == e && n ? 4 : "o" == e ? 3 : 2,
                        i = new RegExp("^\\d{1," + r + "}"),
                        s = t.substring(g).match(i);
                    if (!s) throw "Missing number at position " + g;
                    return g += s[0].length, parseInt(s[0], 10)
                }, v = function(e, n, r) {
                    var i = $.map(p(e) ? r : n, function(e, t) {
                            return [
                                [t, e]
                            ]
                        }).sort(function(e, t) {
                            return -(e[1].length - t[1].length)
                        }),
                        s = -1;
                    if ($.each(i, function(e, n) {
                            var r = n[1];
                            return t.substr(g, r.length).toLowerCase() == r.toLowerCase() ? (s = n[0], g += r.length, !1) : void 0
                        }), -1 != s) return s + 1;
                    throw "Unknown name at position " + g
                }, m = function() {
                    if (t.charAt(g) != e.charAt(y)) throw "Unexpected literal at position " + g;
                    g++
                }, g = 0, y = 0; y < e.length; y++)
                if (h) "'" != e.charAt(y) || p("'") ? m() : h = !1;
                else switch (e.charAt(y)) {
                    case "d":
                        l = d("d");
                        break;
                    case "D":
                        v("D", i, s);
                        break;
                    case "o":
                        c = d("o");
                        break;
                    case "m":
                        f = d("m");
                        break;
                    case "M":
                        f = v("M", o, u);
                        break;
                    case "y":
                        a = d("y");
                        break;
                    case "@":
                        var b = new Date(d("@"));
                        a = b.getFullYear(), f = b.getMonth() + 1, l = b.getDate();
                        break;
                    case "!":
                        var b = new Date((d("!") - this._ticksTo1970) / 1e4);
                        a = b.getFullYear(), f = b.getMonth() + 1, l = b.getDate();
                        break;
                    case "'":
                        p("'") ? m() : h = !0;
                        break;
                    default:
                        m()
                }
                if (g < t.length) {
                    var w = t.substr(g);
                    if (!/^\s+/.test(w)) throw "Extra/unparsed characters found in date: " + w
                }
            if (-1 == a ? a = (new Date).getFullYear() : 100 > a && (a += (new Date).getFullYear() - (new Date).getFullYear() % 100 + (r >= a ? 0 : -100)), c > -1)
                for (f = 1, l = c;;) {
                    var E = this._getDaysInMonth(a, f - 1);
                    if (E >= l) break;
                    f++, l -= E
                }
            var b = this._daylightSavingAdjust(new Date(a, f - 1, l));
            if (b.getFullYear() != a || b.getMonth() + 1 != f || b.getDate() != l) throw "Invalid date";
            return b
        },
        ATOM: "yy-mm-dd",
        COOKIE: "D, dd M yy",
        ISO_8601: "yy-mm-dd",
        RFC_822: "D, d M y",
        RFC_850: "DD, dd-M-y",
        RFC_1036: "D, d M y",
        RFC_1123: "D, d M yy",
        RFC_2822: "D, d M yy",
        RSS: "D, d M y",
        TICKS: "!",
        TIMESTAMP: "@",
        W3C: "yy-mm-dd",
        _ticksTo1970: 24 * (718685 + Math.floor(492.5) - Math.floor(19.7) + Math.floor(4.925)) * 60 * 60 * 1e7,
        formatDate: function(e, t, n) {
            if (!t) return "";
            var r = (n ? n.dayNamesShort : null) || this._defaults.dayNamesShort,
                i = (n ? n.dayNames : null) || this._defaults.dayNames,
                s = (n ? n.monthNamesShort : null) || this._defaults.monthNamesShort,
                o = (n ? n.monthNames : null) || this._defaults.monthNames,
                u = function(t) {
                    var n = h + 1 < e.length && e.charAt(h + 1) == t;
                    return n && h++, n
                },
                a = function(e, t, n) {
                    var r = "" + t;
                    if (u(e))
                        for (; r.length < n;) r = "0" + r;
                    return r
                },
                f = function(e, t, n, r) {
                    return u(e) ? r[t] : n[t]
                },
                l = "",
                c = !1;
            if (t)
                for (var h = 0; h < e.length; h++)
                    if (c) "'" != e.charAt(h) || u("'") ? l += e.charAt(h) : c = !1;
                    else switch (e.charAt(h)) {
                        case "d":
                            l += a("d", t.getDate(), 2);
                            break;
                        case "D":
                            l += f("D", t.getDay(), r, i);
                            break;
                        case "o":
                            l += a("o", Math.round((new Date(t.getFullYear(), t.getMonth(), t.getDate()).getTime() - new Date(t.getFullYear(), 0, 0).getTime()) / 864e5), 3);
                            break;
                        case "m":
                            l += a("m", t.getMonth() + 1, 2);
                            break;
                        case "M":
                            l += f("M", t.getMonth(), s, o);
                            break;
                        case "y":
                            l += u("y") ? t.getFullYear() : (t.getYear() % 100 < 10 ? "0" : "") + t.getYear() % 100;
                            break;
                        case "@":
                            l += t.getTime();
                            break;
                        case "!":
                            l += 1e4 * t.getTime() + this._ticksTo1970;
                            break;
                        case "'":
                            u("'") ? l += "'" : c = !0;
                            break;
                        default:
                            l += e.charAt(h)
                    }
                    return l
        },
        _possibleChars: function(e) {
            for (var t = "", n = !1, r = function(t) {
                    var n = i + 1 < e.length && e.charAt(i + 1) == t;
                    return n && i++, n
                }, i = 0; i < e.length; i++)
                if (n) "'" != e.charAt(i) || r("'") ? t += e.charAt(i) : n = !1;
                else switch (e.charAt(i)) {
                    case "d":
                    case "m":
                    case "y":
                    case "@":
                        t += "0123456789";
                        break;
                    case "D":
                    case "M":
                        return null;
                    case "'":
                        r("'") ? t += "'" : n = !0;
                        break;
                    default:
                        t += e.charAt(i)
                }
                return t
        },
        _get: function(e, t) {
            return e.settings[t] !== undefined ? e.settings[t] : this._defaults[t]
        },
        _setDateFromField: function(e, t) {
            if (e.input.val() != e.lastVal) {
                var i, s, n = this._get(e, "dateFormat"),
                    r = e.lastVal = e.input ? e.input.val() : null;
                i = s = this._getDefaultDate(e);
                var o = this._getFormatConfig(e);
                try {
                    i = this.parseDate(n, r, o) || s
                } catch (u) {
                    this.log(u), r = t ? "" : r
                }
                e.selectedDay = i.getDate(), e.drawMonth = e.selectedMonth = i.getMonth(), e.drawYear = e.selectedYear = i.getFullYear(), e.currentDay = r ? i.getDate() : 0, e.currentMonth = r ? i.getMonth() : 0, e.currentYear = r ? i.getFullYear() : 0, this._adjustInstDate(e)
            }
        },
        _getDefaultDate: function(e) {
            return this._restrictMinMax(e, this._determineDate(e, this._get(e, "defaultDate"), new Date))
        },
        _determineDate: function(e, t, n) {
            var r = function(e) {
                    var t = new Date;
                    return t.setDate(t.getDate() + e), t
                },
                i = function(t) {
                    try {
                        return $.datepicker.parseDate($.datepicker._get(e, "dateFormat"), t, $.datepicker._getFormatConfig(e))
                    } catch (n) {}
                    for (var r = (t.toLowerCase().match(/^c/) ? $.datepicker._getDate(e) : null) || new Date, i = r.getFullYear(), s = r.getMonth(), o = r.getDate(), u = /([+-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g, a = u.exec(t); a;) {
                        switch (a[2] || "d") {
                            case "d":
                            case "D":
                                o += parseInt(a[1], 10);
                                break;
                            case "w":
                            case "W":
                                o += 7 * parseInt(a[1], 10);
                                break;
                            case "m":
                            case "M":
                                s += parseInt(a[1], 10), o = Math.min(o, $.datepicker._getDaysInMonth(i, s));
                                break;
                            case "y":
                            case "Y":
                                i += parseInt(a[1], 10), o = Math.min(o, $.datepicker._getDaysInMonth(i, s))
                        }
                        a = u.exec(t)
                    }
                    return new Date(i, s, o)
                },
                s = null == t || "" === t ? n : "string" == typeof t ? i(t) : "number" == typeof t ? isNaN(t) ? n : r(t) : new Date(t.getTime());
            return s = s && "Invalid Date" == s.toString() ? n : s, s && (s.setHours(0), s.setMinutes(0), s.setSeconds(0), s.setMilliseconds(0)), this._daylightSavingAdjust(s)
        },
        _daylightSavingAdjust: function(e) {
            return e ? (e.setHours(e.getHours() > 12 ? e.getHours() + 2 : 0), e) : null
        },
        _setDate: function(e, t, n) {
            var r = !t,
                i = e.selectedMonth,
                s = e.selectedYear,
                o = this._restrictMinMax(e, this._determineDate(e, t, new Date));
            e.selectedDay = e.currentDay = o.getDate(), e.drawMonth = e.selectedMonth = e.currentMonth = o.getMonth(), e.drawYear = e.selectedYear = e.currentYear = o.getFullYear(), (i != e.selectedMonth || s != e.selectedYear) && !n && this._notifyChange(e), this._adjustInstDate(e), e.input && e.input.val(r ? "" : this._formatDate(e))
        },
        _getDate: function(e) {
            var t = !e.currentYear || e.input && "" == e.input.val() ? null : this._daylightSavingAdjust(new Date(e.currentYear, e.currentMonth, e.currentDay));
            return t
        },
        _attachHandlers: function(e) {
            var t = this._get(e, "stepMonths"),
                n = "#" + e.id.replace(/\\\\/g, "\\");
            e.dpDiv.find("[data-handler]").map(function() {
                var e = {
                    prev: function() {
                        window["DP_jQuery_" + dpuuid].datepicker._adjustDate(n, -t, "M")
                    },
                    next: function() {
                        window["DP_jQuery_" + dpuuid].datepicker._adjustDate(n, +t, "M")
                    },
                    hide: function() {
                        window["DP_jQuery_" + dpuuid].datepicker._hideDatepicker()
                    },
                    today: function() {
                        window["DP_jQuery_" + dpuuid].datepicker._gotoToday(n)
                    },
                    selectDay: function() {
                        return window["DP_jQuery_" + dpuuid].datepicker._selectDay(n, +this.getAttribute("data-month"), +this.getAttribute("data-year"), this), !1
                    },
                    selectMonth: function() {
                        return window["DP_jQuery_" + dpuuid].datepicker._selectMonthYear(n, this, "M"), !1
                    },
                    selectYear: function() {
                        return window["DP_jQuery_" + dpuuid].datepicker._selectMonthYear(n, this, "Y"), !1
                    }
                };
                $(this).bind(this.getAttribute("data-event"), e[this.getAttribute("data-handler")])
            })
        },
        _generateHTML: function(e) {
            var t = new Date;
            t = this._daylightSavingAdjust(new Date(t.getFullYear(), t.getMonth(), t.getDate()));
            var n = this._get(e, "isRTL"),
                r = this._get(e, "showButtonPanel"),
                i = this._get(e, "hideIfNoPrevNext"),
                s = this._get(e, "navigationAsDateFormat"),
                o = this._getNumberOfMonths(e),
                u = this._get(e, "showCurrentAtPos"),
                a = this._get(e, "stepMonths"),
                f = 1 != o[0] || 1 != o[1],
                l = this._daylightSavingAdjust(e.currentDay ? new Date(e.currentYear, e.currentMonth, e.currentDay) : new Date(9999, 9, 9)),
                c = this._getMinMaxDate(e, "min"),
                h = this._getMinMaxDate(e, "max"),
                p = e.drawMonth - u,
                d = e.drawYear;
            if (0 > p && (p += 12, d--), h) {
                var v = this._daylightSavingAdjust(new Date(h.getFullYear(), h.getMonth() - o[0] * o[1] + 1, h.getDate()));
                for (v = c && c > v ? c : v; this._daylightSavingAdjust(new Date(d, p, 1)) > v;) p--, 0 > p && (p = 11, d--)
            }
            e.drawMonth = p, e.drawYear = d;
            var m = this._get(e, "prevText");
            m = s ? this.formatDate(m, this._daylightSavingAdjust(new Date(d, p - a, 1)), this._getFormatConfig(e)) : m;
            var g = this._canAdjustMonth(e, -1, d, p) ? '<a class="ui-datepicker-prev ui-corner-all" data-handler="prev" data-event="click" title="' + m + '"><span class="ui-icon ui-icon-circle-triangle-' + (n ? "e" : "w") + '">' + m + "</span></a>" : i ? "" : '<a class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="' + m + '"><span class="ui-icon ui-icon-circle-triangle-' + (n ? "e" : "w") + '">' + m + "</span></a>",
                y = this._get(e, "nextText");
            y = s ? this.formatDate(y, this._daylightSavingAdjust(new Date(d, p + a, 1)), this._getFormatConfig(e)) : y;
            var b = this._canAdjustMonth(e, 1, d, p) ? '<a class="ui-datepicker-next ui-corner-all" data-handler="next" data-event="click" title="' + y + '"><span class="ui-icon ui-icon-circle-triangle-' + (n ? "w" : "e") + '">' + y + "</span></a>" : i ? "" : '<a class="ui-datepicker-next ui-corner-all ui-state-disabled" title="' + y + '"><span class="ui-icon ui-icon-circle-triangle-' + (n ? "w" : "e") + '">' + y + "</span></a>",
                w = this._get(e, "currentText"),
                E = this._get(e, "gotoCurrent") && e.currentDay ? l : t;
            w = s ? this.formatDate(w, E, this._getFormatConfig(e)) : w;
            var S = e.inline ? "" : '<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all" data-handler="hide" data-event="click">' + this._get(e, "closeText") + "</button>",
                x = r ? '<div class="ui-datepicker-buttonpane ui-widget-content">' + (n ? S : "") + (this._isInRange(e, E) ? '<button type="button" class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" data-handler="today" data-event="click">' + w + "</button>" : "") + (n ? "" : S) + "</div>" : "",
                T = parseInt(this._get(e, "firstDay"), 10);
            T = isNaN(T) ? 0 : T;
            for (var N = this._get(e, "showWeek"), C = this._get(e, "dayNames"), L = (this._get(e, "dayNamesShort"), this._get(e, "dayNamesMin")), A = this._get(e, "monthNames"), O = this._get(e, "monthNamesShort"), M = this._get(e, "beforeShowDay"), _ = this._get(e, "showOtherMonths"), D = this._get(e, "selectOtherMonths"), H = (this._get(e, "calculateWeek") || this.iso8601Week, this._getDefaultDate(e)), B = "", j = 0; j < o[0]; j++) {
                var F = "";
                this.maxRows = 4;
                for (var I = 0; I < o[1]; I++) {
                    var q = this._daylightSavingAdjust(new Date(d, p, e.selectedDay)),
                        R = " ui-corner-all",
                        U = "";
                    if (f) {
                        if (U += '<div class="ui-datepicker-group', o[1] > 1) switch (I) {
                            case 0:
                                U += " ui-datepicker-group-first", R = " ui-corner-" + (n ? "right" : "left");
                                break;
                            case o[1] - 1:
                                U += " ui-datepicker-group-last", R = " ui-corner-" + (n ? "left" : "right");
                                break;
                            default:
                                U += " ui-datepicker-group-middle", R = ""
                        }
                        U += '">'
                    }
                    U += '<div class="ui-datepicker-header ui-widget-header ui-helper-clearfix' + R + '">' + (/all|left/.test(R) && 0 == j ? n ? b : g : "") + (/all|right/.test(R) && 0 == j ? n ? g : b : "") + this._generateMonthYearHeader(e, p, d, c, h, j > 0 || I > 0, A, O) + '</div><table class="ui-datepicker-calendar"><thead><tr>';
                    for (var z = N ? '<th class="ui-datepicker-week-col">' + this._get(e, "weekHeader") + "</th>" : "", W = 0; 7 > W; W++) {
                        var X = (W + T) % 7;
                        z += "<th" + ((W + T + 6) % 7 >= 5 ? ' class="ui-datepicker-week-end"' : "") + '><span title="' + C[X] + '">' + L[X] + "</span></th>"
                    }
                    U += z + "</tr></thead><tbody>";
                    var V = this._getDaysInMonth(d, p);
                    d == e.selectedYear && p == e.selectedMonth && (e.selectedDay = Math.min(e.selectedDay, V));
                    var J = (this._getFirstDayOfMonth(d, p) - T + 7) % 7,
                        K = Math.ceil((J + V) / 7),
                        Q = f && this.maxRows > K ? this.maxRows : K;
                    this.maxRows = Q;
                    for (var G = this._daylightSavingAdjust(new Date(d, p, 1 - J)), Y = 0; Q > Y; Y++) {
                        U += "<tr>";
                        for (var Z = N ? '<td class="ui-datepicker-week-col">' + this._get(e, "calculateWeek")(G) + "</td>" : "", W = 0; 7 > W; W++) {
                            var et = M ? M.apply(e.input ? e.input[0] : null, [G]) : [!0, ""],
                                tt = G.getMonth() != p,
                                nt = tt && !D || !et[0] || c && c > G || h && G > h;
                            Z += '<td class="' + ((W + T + 6) % 7 >= 5 ? " ui-datepicker-week-end" : "") + (tt ? " ui-datepicker-other-month" : "") + (G.getTime() == q.getTime() && p == e.selectedMonth && e._keyEvent || H.getTime() == G.getTime() && H.getTime() == q.getTime() ? " " + this._dayOverClass : "") + (nt ? " " + this._unselectableClass + " ui-state-disabled" : "") + (tt && !_ ? "" : " " + et[1] + (G.getTime() == l.getTime() ? " " + this._currentClass : "") + (G.getTime() == t.getTime() ? " ui-datepicker-today" : "")) + '"' + (tt && !_ || !et[2] ? "" : ' title="' + et[2] + '"') + (nt ? "" : ' data-handler="selectDay" data-event="click" data-month="' + G.getMonth() + '" data-year="' + G.getFullYear() + '"') + ">" + (tt && !_ ? "&#xa0;" : nt ? '<span class="ui-state-default">' + G.getDate() + "</span>" : '<a class="ui-state-default' + (G.getTime() == t.getTime() ? " ui-state-highlight" : "") + (G.getTime() == l.getTime() ? " ui-state-active" : "") + (tt ? " ui-priority-secondary" : "") + '" href="#">' + G.getDate() + "</a>") + "</td>", G.setDate(G.getDate() + 1), G = this._daylightSavingAdjust(G)
                        }
                        U += Z + "</tr>"
                    }
                    p++, p > 11 && (p = 0, d++), U += "</tbody></table>" + (f ? "</div>" + (o[0] > 0 && I == o[1] - 1 ? '<div class="ui-datepicker-row-break"></div>' : "") : ""), F += U
                }
                B += F
            }
            return B += x + ($.ui.ie6 && !e.inline ? '<iframe src="javascript:false;" class="ui-datepicker-cover" frameborder="0"></iframe>' : ""), e._keyEvent = !1, B
        },
        _generateMonthYearHeader: function(e, t, n, r, i, s, o, u) {
            var a = this._get(e, "changeMonth"),
                f = this._get(e, "changeYear"),
                l = this._get(e, "showMonthAfterYear"),
                c = '<div class="ui-datepicker-title">',
                h = "";
            if (s || !a) h += '<span class="ui-datepicker-month">' + o[t] + "</span>";
            else {
                var p = r && r.getFullYear() == n,
                    d = i && i.getFullYear() == n;
                h += '<select class="ui-datepicker-month" data-handler="selectMonth" data-event="change">';
                for (var v = 0; 12 > v; v++)(!p || v >= r.getMonth()) && (!d || v <= i.getMonth()) && (h += '<option value="' + v + '"' + (v == t ? ' selected="selected"' : "") + ">" + u[v] + "</option>");
                h += "</select>"
            }
            if (l || (c += h + (!s && a && f ? "" : "&#xa0;")), !e.yearshtml)
                if (e.yearshtml = "", s || !f) c += '<span class="ui-datepicker-year">' + n + "</span>";
                else {
                    var m = this._get(e, "yearRange").split(":"),
                        g = (new Date).getFullYear(),
                        y = function(e) {
                            var t = e.match(/c[+-].*/) ? n + parseInt(e.substring(1), 10) : e.match(/[+-].*/) ? g + parseInt(e, 10) : parseInt(e, 10);
                            return isNaN(t) ? g : t
                        },
                        b = y(m[0]),
                        w = Math.max(b, y(m[1] || ""));
                    for (b = r ? Math.max(b, r.getFullYear()) : b, w = i ? Math.min(w, i.getFullYear()) : w, e.yearshtml += '<select class="ui-datepicker-year" data-handler="selectYear" data-event="change">'; w >= b; b++) e.yearshtml += '<option value="' + b + '"' + (b == n ? ' selected="selected"' : "") + ">" + b + "</option>";
                    e.yearshtml += "</select>", c += e.yearshtml, e.yearshtml = null
                }
            return c += this._get(e, "yearSuffix"), l && (c += (!s && a && f ? "" : "&#xa0;") + h), c += "</div>"
        },
        _adjustInstDate: function(e, t, n) {
            var r = e.drawYear + ("Y" == n ? t : 0),
                i = e.drawMonth + ("M" == n ? t : 0),
                s = Math.min(e.selectedDay, this._getDaysInMonth(r, i)) + ("D" == n ? t : 0),
                o = this._restrictMinMax(e, this._daylightSavingAdjust(new Date(r, i, s)));
            e.selectedDay = o.getDate(), e.drawMonth = e.selectedMonth = o.getMonth(), e.drawYear = e.selectedYear = o.getFullYear(), ("M" == n || "Y" == n) && this._notifyChange(e)
        },
        _restrictMinMax: function(e, t) {
            var n = this._getMinMaxDate(e, "min"),
                r = this._getMinMaxDate(e, "max"),
                i = n && n > t ? n : t;
            return i = r && i > r ? r : i
        },
        _notifyChange: function(e) {
            var t = this._get(e, "onChangeMonthYear");
            t && t.apply(e.input ? e.input[0] : null, [e.selectedYear, e.selectedMonth + 1, e])
        },
        _getNumberOfMonths: function(e) {
            var t = this._get(e, "numberOfMonths");
            return null == t ? [1, 1] : "number" == typeof t ? [1, t] : t
        },
        _getMinMaxDate: function(e, t) {
            return this._determineDate(e, this._get(e, t + "Date"), null)
        },
        _getDaysInMonth: function(e, t) {
            return 32 - this._daylightSavingAdjust(new Date(e, t, 32)).getDate()
        },
        _getFirstDayOfMonth: function(e, t) {
            return new Date(e, t, 1).getDay()
        },
        _canAdjustMonth: function(e, t, n, r) {
            var i = this._getNumberOfMonths(e),
                s = this._daylightSavingAdjust(new Date(n, r + (0 > t ? t : i[0] * i[1]), 1));
            return 0 > t && s.setDate(this._getDaysInMonth(s.getFullYear(), s.getMonth())), this._isInRange(e, s)
        },
        _isInRange: function(e, t) {
            var n = this._getMinMaxDate(e, "min"),
                r = this._getMinMaxDate(e, "max");
            return (!n || t.getTime() >= n.getTime()) && (!r || t.getTime() <= r.getTime())
        },
        _getFormatConfig: function(e) {
            var t = this._get(e, "shortYearCutoff");
            return t = "string" != typeof t ? t : (new Date).getFullYear() % 100 + parseInt(t, 10), {
                shortYearCutoff: t,
                dayNamesShort: this._get(e, "dayNamesShort"),
                dayNames: this._get(e, "dayNames"),
                monthNamesShort: this._get(e, "monthNamesShort"),
                monthNames: this._get(e, "monthNames")
            }
        },
        _formatDate: function(e, t, n, r) {
            t || (e.currentDay = e.selectedDay, e.currentMonth = e.selectedMonth, e.currentYear = e.selectedYear);
            var i = t ? "object" == typeof t ? t : this._daylightSavingAdjust(new Date(r, n, t)) : this._daylightSavingAdjust(new Date(e.currentYear, e.currentMonth, e.currentDay));
            return this.formatDate(this._get(e, "dateFormat"), i, this._getFormatConfig(e))
        }
    }), $.fn.datepicker = function(e) {
        if (!this.length) return this;
        $.datepicker.initialized || ($(document).mousedown($.datepicker._checkExternalClick).find(document.body).append($.datepicker.dpDiv), $.datepicker.initialized = !0);
        var t = Array.prototype.slice.call(arguments, 1);
        return "string" != typeof e || "isDisabled" != e && "getDate" != e && "widget" != e ? "option" == e && 2 == arguments.length && "string" == typeof arguments[1] ? $.datepicker["_" + e + "Datepicker"].apply($.datepicker, [this[0]].concat(t)) : this.each(function() {
            "string" == typeof e ? $.datepicker["_" + e + "Datepicker"].apply($.datepicker, [this].concat(t)) : $.datepicker._attachDatepicker(this, e)
        }) : $.datepicker["_" + e + "Datepicker"].apply($.datepicker, [this[0]].concat(t))
    }, $.datepicker = new Datepicker, $.datepicker.initialized = !1, $.datepicker.uuid = (new Date).getTime(), $.datepicker.version = "1.9.2", window["DP_jQuery_" + dpuuid] = $
}(jQuery),
function(e, t) {
    var n = "ui-dialog ui-widget ui-widget-content ui-corner-all ",
        r = {
            buttons: !0,
            height: !0,
            maxHeight: !0,
            maxWidth: !0,
            minHeight: !0,
            minWidth: !0,
            width: !0
        },
        i = {
            maxHeight: !0,
            maxWidth: !0,
            minHeight: !0,
            minWidth: !0
        };
    e.widget("ui.dialog", {
        version: "1.9.2",
        options: {
            autoOpen: !0,
            buttons: {},
            closeOnEscape: !0,
            closeText: "close",
            dialogClass: "",
            draggable: !0,
            hide: null,
            height: "auto",
            maxHeight: !1,
            maxWidth: !1,
            minHeight: 150,
            minWidth: 150,
            modal: !1,
            position: {
                my: "center",
                at: "center",
                of: window,
                collision: "fit",
                using: function(t) {
                    var n = e(this).css(t).offset().top;
                    0 > n && e(this).css("top", t.top - n)
                }
            },
            resizable: !0,
            show: null,
            stack: !0,
            title: "",
            width: 300,
            zIndex: 1e3
        },
        _create: function() {
            this.originalTitle = this.element.attr("title"), "string" != typeof this.originalTitle && (this.originalTitle = ""), this.oldPosition = {
                parent: this.element.parent(),
                index: this.element.parent().children().index(this.element)
            }, this.options.title = this.options.title || this.originalTitle;
            var s, o, u, a, f, t = this,
                r = this.options,
                i = r.title || "&#160;";
            s = (this.uiDialog = e("<div>")).addClass(n + r.dialogClass).css({
                display: "none",
                outline: 0,
                zIndex: r.zIndex
            }).attr("tabIndex", -1).keydown(function(n) {
                r.closeOnEscape && !n.isDefaultPrevented() && n.keyCode && n.keyCode === e.ui.keyCode.ESCAPE && (t.close(n), n.preventDefault())
            }).mousedown(function(e) {
                t.moveToTop(!1, e)
            }).appendTo("body"), this.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(s), o = (this.uiDialogTitlebar = e("<div>")).addClass("ui-dialog-titlebar  ui-widget-header  ui-corner-all  ui-helper-clearfix").bind("mousedown", function() {
                s.focus()
            }).prependTo(s), u = e("<a href='#'></a>").addClass("ui-dialog-titlebar-close  ui-corner-all").attr("role", "button").click(function(e) {
                e.preventDefault(), t.close(e)
            }).appendTo(o), (this.uiDialogTitlebarCloseText = e("<span>")).addClass("ui-icon ui-icon-closethick").text(r.closeText).appendTo(u), a = e("<span>").uniqueId().addClass("ui-dialog-title").html(i).prependTo(o), f = (this.uiDialogButtonPane = e("<div>")).addClass("ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"), (this.uiButtonSet = e("<div>")).addClass("ui-dialog-buttonset").appendTo(f), s.attr({
                role: "dialog",
                "aria-labelledby": a.attr("id")
            }), o.find("*").add(o).disableSelection(), this._hoverable(u), this._focusable(u), r.draggable && e.fn.draggable && this._makeDraggable(), r.resizable && e.fn.resizable && this._makeResizable(), this._createButtons(r.buttons), this._isOpen = !1, e.fn.bgiframe && s.bgiframe(), this._on(s, {
                keydown: function(t) {
                    if (r.modal && t.keyCode === e.ui.keyCode.TAB) {
                        var n = e(":tabbable", s),
                            i = n.filter(":first"),
                            o = n.filter(":last");
                        return t.target !== o[0] || t.shiftKey ? t.target === i[0] && t.shiftKey ? (o.focus(1), !1) : void 0 : (i.focus(1), !1)
                    }
                }
            })
        },
        _init: function() {
            this.options.autoOpen && this.open()
        },
        _destroy: function() {
            var e, t = this.oldPosition;
            this.overlay && this.overlay.destroy(), this.uiDialog.hide(), this.element.removeClass("ui-dialog-content ui-widget-content").hide().appendTo("body"), this.uiDialog.remove(), this.originalTitle && this.element.attr("title", this.originalTitle), e = t.parent.children().eq(t.index), e.length && e[0] !== this.element[0] ? e.before(this.element) : t.parent.append(this.element)
        },
        widget: function() {
            return this.uiDialog
        },
        close: function(t) {
            var r, i, n = this;
            if (this._isOpen && !1 !== this._trigger("beforeClose", t)) return this._isOpen = !1, this.overlay && this.overlay.destroy(), this.options.hide ? this._hide(this.uiDialog, this.options.hide, function() {
                n._trigger("close", t)
            }) : (this.uiDialog.hide(), this._trigger("close", t)), e.ui.dialog.overlay.resize(), this.options.modal && (r = 0, e(".ui-dialog").each(function() {
                this !== n.uiDialog[0] && (i = e(this).css("z-index"), isNaN(i) || (r = Math.max(r, i)))
            }), e.ui.dialog.maxZ = r), this
        },
        isOpen: function() {
            return this._isOpen
        },
        moveToTop: function(t, n) {
            var i, r = this.options;
            return r.modal && !t || !r.stack && !r.modal ? this._trigger("focus", n) : (r.zIndex > e.ui.dialog.maxZ && (e.ui.dialog.maxZ = r.zIndex), this.overlay && (e.ui.dialog.maxZ += 1, e.ui.dialog.overlay.maxZ = e.ui.dialog.maxZ, this.overlay.$el.css("z-index", e.ui.dialog.overlay.maxZ)), i = {
                scrollTop: this.element.scrollTop(),
                scrollLeft: this.element.scrollLeft()
            }, e.ui.dialog.maxZ += 1, this.uiDialog.css("z-index", e.ui.dialog.maxZ), this.element.attr(i), this._trigger("focus", n), this)
        },
        open: function() {
            if (!this._isOpen) {
                var t, n = this.options,
                    r = this.uiDialog;
                return this._size(), this._position(n.position), r.show(n.show), this.overlay = n.modal ? new e.ui.dialog.overlay(this) : null, this.moveToTop(!0), t = this.element.find(":tabbable"), t.length || (t = this.uiDialogButtonPane.find(":tabbable"), t.length || (t = r)), t.eq(0).focus(), this._isOpen = !0, this._trigger("open"), this
            }
        },
        _createButtons: function(t) {
            var n = this,
                r = !1;
            this.uiDialogButtonPane.remove(), this.uiButtonSet.empty(), "object" == typeof t && null !== t && e.each(t, function() {
                return !(r = !0)
            }), r ? (e.each(t, function(t, r) {
                var i, s;
                r = e.isFunction(r) ? {
                    click: r,
                    text: t
                } : r, r = e.extend({
                    type: "button"
                }, r), s = r.click, r.click = function() {
                    s.apply(n.element[0], arguments)
                }, i = e("<button></button>", r).appendTo(n.uiButtonSet), e.fn.button && i.button()
            }), this.uiDialog.addClass("ui-dialog-buttons"), this.uiDialogButtonPane.appendTo(this.uiDialog)) : this.uiDialog.removeClass("ui-dialog-buttons")
        },
        _makeDraggable: function() {
            function r(e) {
                return {
                    position: e.position,
                    offset: e.offset
                }
            }
            var t = this,
                n = this.options;
            this.uiDialog.draggable({
                cancel: ".ui-dialog-content, .ui-dialog-titlebar-close",
                handle: ".ui-dialog-titlebar",
                containment: "document",
                start: function(n, i) {
                    e(this).addClass("ui-dialog-dragging"), t._trigger("dragStart", n, r(i))
                },
                drag: function(e, n) {
                    t._trigger("drag", e, r(n))
                },
                stop: function(i, s) {
                    n.position = [s.position.left - t.document.scrollLeft(), s.position.top - t.document.scrollTop()], e(this).removeClass("ui-dialog-dragging"), t._trigger("dragStop", i, r(s)), e.ui.dialog.overlay.resize()
                }
            })
        },
        _makeResizable: function(n) {
            function u(e) {
                return {
                    originalPosition: e.originalPosition,
                    originalSize: e.originalSize,
                    position: e.position,
                    size: e.size
                }
            }
            n = n === t ? this.options.resizable : n;
            var r = this,
                i = this.options,
                s = this.uiDialog.css("position"),
                o = "string" == typeof n ? n : "n,e,s,w,se,sw,ne,nw";
            this.uiDialog.resizable({
                cancel: ".ui-dialog-content",
                containment: "document",
                alsoResize: this.element,
                maxWidth: i.maxWidth,
                maxHeight: i.maxHeight,
                minWidth: i.minWidth,
                minHeight: this._minHeight(),
                handles: o,
                start: function(t, n) {
                    e(this).addClass("ui-dialog-resizing"), r._trigger("resizeStart", t, u(n))
                },
                resize: function(e, t) {
                    r._trigger("resize", e, u(t))
                },
                stop: function(t, n) {
                    e(this).removeClass("ui-dialog-resizing"), i.height = e(this).height(), i.width = e(this).width(), r._trigger("resizeStop", t, u(n)), e.ui.dialog.overlay.resize()
                }
            }).css("position", s).find(".ui-resizable-se").addClass("ui-icon ui-icon-grip-diagonal-se")
        },
        _minHeight: function() {
            var e = this.options;
            return "auto" === e.height ? e.minHeight : Math.min(e.minHeight, e.height)
        },
        _position: function(t) {
            var i, n = [],
                r = [0, 0];
            t ? (("string" == typeof t || "object" == typeof t && "0" in t) && (n = t.split ? t.split(" ") : [t[0], t[1]], 1 === n.length && (n[1] = n[0]), e.each(["left", "top"], function(e, t) {
                +n[e] === n[e] && (r[e] = n[e], n[e] = t)
            }), t = {
                my: n[0] + (r[0] < 0 ? r[0] : "+" + r[0]) + " " + n[1] + (r[1] < 0 ? r[1] : "+" + r[1]),
                at: n.join(" ")
            }), t = e.extend({}, e.ui.dialog.prototype.options.position, t)) : t = e.ui.dialog.prototype.options.position, i = this.uiDialog.is(":visible"), i || this.uiDialog.show(), this.uiDialog.position(t), i || this.uiDialog.hide()
        },
        _setOptions: function(t) {
            var n = this,
                s = {},
                o = !1;
            e.each(t, function(e, t) {
                n._setOption(e, t), e in r && (o = !0), e in i && (s[e] = t)
            }), o && this._size(), this.uiDialog.is(":data(resizable)") && this.uiDialog.resizable("option", s)
        },
        _setOption: function(t, r) {
            var i, s, o = this.uiDialog;
            switch (t) {
                case "buttons":
                    this._createButtons(r);
                    break;
                case "closeText":
                    this.uiDialogTitlebarCloseText.text("" + r);
                    break;
                case "dialogClass":
                    o.removeClass(this.options.dialogClass).addClass(n + r);
                    break;
                case "disabled":
                    r ? o.addClass("ui-dialog-disabled") : o.removeClass("ui-dialog-disabled");
                    break;
                case "draggable":
                    i = o.is(":data(draggable)"), i && !r && o.draggable("destroy"), !i && r && this._makeDraggable();
                    break;
                case "position":
                    this._position(r);
                    break;
                case "resizable":
                    s = o.is(":data(resizable)"), s && !r && o.resizable("destroy"), s && "string" == typeof r && o.resizable("option", "handles", r), !s && r !== !1 && this._makeResizable(r);
                    break;
                case "title":
                    e(".ui-dialog-title", this.uiDialogTitlebar).html("" + (r || "&#160;"))
            }
            this._super(t, r)
        },
        _size: function() {
            var t, n, r, i = this.options,
                s = this.uiDialog.is(":visible");
            this.element.show().css({
                width: "auto",
                minHeight: 0,
                height: 0
            }), i.minWidth > i.width && (i.width = i.minWidth), t = this.uiDialog.css({
                height: "auto",
                width: i.width
            }).outerHeight(), n = Math.max(0, i.minHeight - t), "auto" === i.height ? e.support.minHeight ? this.element.css({
                minHeight: n,
                height: "auto"
            }) : (this.uiDialog.show(), r = this.element.css("height", "auto").height(), s || this.uiDialog.hide(), this.element.height(Math.max(r, n))) : this.element.height(Math.max(i.height - t, 0)), this.uiDialog.is(":data(resizable)") && this.uiDialog.resizable("option", "minHeight", this._minHeight())
        }
    }), e.extend(e.ui.dialog, {
        uuid: 0,
        maxZ: 0,
        getTitleId: function(e) {
            var t = e.attr("id");
            return t || (this.uuid += 1, t = this.uuid), "ui-dialog-title-" + t
        },
        overlay: function(t) {
            this.$el = e.ui.dialog.overlay.create(t)
        }
    }), e.extend(e.ui.dialog.overlay, {
        instances: [],
        oldInstances: [],
        maxZ: 0,
        events: e.map("focus,mousedown,mouseup,keydown,keypress,click".split(","), function(e) {
            return e + ".dialog-overlay"
        }).join(" "),
        create: function(t) {
            0 === this.instances.length && (setTimeout(function() {
                e.ui.dialog.overlay.instances.length && e(document).bind(e.ui.dialog.overlay.events, function(t) {
                    return e(t.target).zIndex() < e.ui.dialog.overlay.maxZ ? !1 : void 0
                })
            }, 1), e(window).bind("resize.dialog-overlay", e.ui.dialog.overlay.resize));
            var n = this.oldInstances.pop() || e("<div>").addClass("ui-widget-overlay");
            return e(document).bind("keydown.dialog-overlay", function(r) {
                var i = e.ui.dialog.overlay.instances;
                0 !== i.length && i[i.length - 1] === n && t.options.closeOnEscape && !r.isDefaultPrevented() && r.keyCode && r.keyCode === e.ui.keyCode.ESCAPE && (t.close(r), r.preventDefault())
            }), n.appendTo(document.body).css({
                width: this.width(),
                height: this.height()
            }), e.fn.bgiframe && n.bgiframe(), this.instances.push(n), n
        },
        destroy: function(t) {
            var n = e.inArray(t, this.instances),
                r = 0; - 1 !== n && this.oldInstances.push(this.instances.splice(n, 1)[0]), 0 === this.instances.length && e([document, window]).unbind(".dialog-overlay"), t.height(0).width(0).remove(), e.each(this.instances, function() {
                r = Math.max(r, this.css("z-index"))
            }), this.maxZ = r
        },
        height: function() {
            var t, n;
            return e.ui.ie ? (t = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight), n = Math.max(document.documentElement.offsetHeight, document.body.offsetHeight), n > t ? e(window).height() + "px" : t + "px") : e(document).height() + "px"
        },
        width: function() {
            var t, n;
            return e.ui.ie ? (t = Math.max(document.documentElement.scrollWidth, document.body.scrollWidth), n = Math.max(document.documentElement.offsetWidth, document.body.offsetWidth), n > t ? e(window).width() + "px" : t + "px") : e(document).width() + "px"
        },
        resize: function() {
            var t = e([]);
            e.each(e.ui.dialog.overlay.instances, function() {
                t = t.add(this)
            }), t.css({
                width: 0,
                height: 0
            }).css({
                width: e.ui.dialog.overlay.width(),
                height: e.ui.dialog.overlay.height()
            })
        }
    }), e.extend(e.ui.dialog.overlay.prototype, {
        destroy: function() {
            e.ui.dialog.overlay.destroy(this.$el)
        }
    })
}(jQuery),
function(e) {
    e.widget("ui.draggable", e.ui.mouse, {
        version: "1.9.2",
        widgetEventPrefix: "drag",
        options: {
            addClasses: !0,
            appendTo: "parent",
            axis: !1,
            connectToSortable: !1,
            containment: !1,
            cursor: "auto",
            cursorAt: !1,
            grid: !1,
            handle: !1,
            helper: "original",
            iframeFix: !1,
            opacity: !1,
            refreshPositions: !1,
            revert: !1,
            revertDuration: 500,
            scope: "default",
            scroll: !0,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            snap: !1,
            snapMode: "both",
            snapTolerance: 20,
            stack: !1,
            zIndex: !1
        },
        _create: function() {
            "original" == this.options.helper && !/^(?:r|a|f)/.test(this.element.css("position")) && (this.element[0].style.position = "relative"), this.options.addClasses && this.element.addClass("ui-draggable"), this.options.disabled && this.element.addClass("ui-draggable-disabled"), this._mouseInit()
        },
        _destroy: function() {
            this.element.removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled"), this._mouseDestroy()
        },
        _mouseCapture: function(t) {
            var n = this.options;
            return this.helper || n.disabled || e(t.target).is(".ui-resizable-handle") ? !1 : (this.handle = this._getHandle(t), this.handle ? (e(n.iframeFix === !0 ? "iframe" : n.iframeFix).each(function() {
                e('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({
                    width: this.offsetWidth + "px",
                    height: this.offsetHeight + "px",
                    position: "absolute",
                    opacity: "0.001",
                    zIndex: 1e3
                }).css(e(this).offset()).appendTo("body")
            }), !0) : !1)
        },
        _mouseStart: function(t) {
            var n = this.options;
            return this.helper = this._createHelper(t), this.helper.addClass("ui-draggable-dragging"), this._cacheHelperProportions(), e.ui.ddmanager && (e.ui.ddmanager.current = this), this._cacheMargins(), this.cssPosition = this.helper.css("position"), this.scrollParent = this.helper.scrollParent(), this.offset = this.positionAbs = this.element.offset(), this.offset = {
                top: this.offset.top - this.margins.top,
                left: this.offset.left - this.margins.left
            }, e.extend(this.offset, {
                click: {
                    left: t.pageX - this.offset.left,
                    top: t.pageY - this.offset.top
                },
                parent: this._getParentOffset(),
                relative: this._getRelativeOffset()
            }), this.originalPosition = this.position = this._generatePosition(t), this.originalPageX = t.pageX, this.originalPageY = t.pageY, n.cursorAt && this._adjustOffsetFromHelper(n.cursorAt), n.containment && this._setContainment(), this._trigger("start", t) === !1 ? (this._clear(), !1) : (this._cacheHelperProportions(), e.ui.ddmanager && !n.dropBehaviour && e.ui.ddmanager.prepareOffsets(this, t), this._mouseDrag(t, !0), e.ui.ddmanager && e.ui.ddmanager.dragStart(this, t), !0)
        },
        _mouseDrag: function(t, n) {
            if (this.position = this._generatePosition(t), this.positionAbs = this._convertPositionTo("absolute"), !n) {
                var r = this._uiHash();
                if (this._trigger("drag", t, r) === !1) return this._mouseUp({}), !1;
                this.position = r.position
            }
            return this.options.axis && "y" == this.options.axis || (this.helper[0].style.left = this.position.left + "px"), this.options.axis && "x" == this.options.axis || (this.helper[0].style.top = this.position.top + "px"), e.ui.ddmanager && e.ui.ddmanager.drag(this, t), !1
        },
        _mouseStop: function(t) {
            var n = !1;
            e.ui.ddmanager && !this.options.dropBehaviour && (n = e.ui.ddmanager.drop(this, t)), this.dropped && (n = this.dropped, this.dropped = !1);
            for (var r = this.element[0], i = !1; r && (r = r.parentNode);) r == document && (i = !0);
            if (!i && "original" === this.options.helper) return !1;
            if ("invalid" == this.options.revert && !n || "valid" == this.options.revert && n || this.options.revert === !0 || e.isFunction(this.options.revert) && this.options.revert.call(this.element, n)) {
                var s = this;
                e(this.helper).animate(this.originalPosition, parseInt(this.options.revertDuration, 10), function() {
                    s._trigger("stop", t) !== !1 && s._clear()
                })
            } else this._trigger("stop", t) !== !1 && this._clear();
            return !1
        },
        _mouseUp: function(t) {
            return e("div.ui-draggable-iframeFix").each(function() {
                this.parentNode.removeChild(this)
            }), e.ui.ddmanager && e.ui.ddmanager.dragStop(this, t), e.ui.mouse.prototype._mouseUp.call(this, t)
        },
        cancel: function() {
            return this.helper.is(".ui-draggable-dragging") ? this._mouseUp({}) : this._clear(), this
        },
        _getHandle: function(t) {
            var n = this.options.handle && e(this.options.handle, this.element).length ? !1 : !0;
            return e(this.options.handle, this.element).find("*").andSelf().each(function() {
                this == t.target && (n = !0)
            }), n
        },
        _createHelper: function(t) {
            var n = this.options,
                r = e.isFunction(n.helper) ? e(n.helper.apply(this.element[0], [t])) : "clone" == n.helper ? this.element.clone().removeAttr("id") : this.element;
            return r.parents("body").length || r.appendTo("parent" == n.appendTo ? this.element[0].parentNode : n.appendTo), r[0] != this.element[0] && !/(fixed|absolute)/.test(r.css("position")) && r.css("position", "absolute"), r
        },
        _adjustOffsetFromHelper: function(t) {
            "string" == typeof t && (t = t.split(" ")), e.isArray(t) && (t = {
                left: +t[0],
                top: +t[1] || 0
            }), "left" in t && (this.offset.click.left = t.left + this.margins.left), "right" in t && (this.offset.click.left = this.helperProportions.width - t.right + this.margins.left), "top" in t && (this.offset.click.top = t.top + this.margins.top), "bottom" in t && (this.offset.click.top = this.helperProportions.height - t.bottom + this.margins.top)
        },
        _getParentOffset: function() {
            this.offsetParent = this.helper.offsetParent();
            var t = this.offsetParent.offset();
            return "absolute" == this.cssPosition && this.scrollParent[0] != document && e.contains(this.scrollParent[0], this.offsetParent[0]) && (t.left += this.scrollParent.scrollLeft(), t.top += this.scrollParent.scrollTop()), (this.offsetParent[0] == document.body || this.offsetParent[0].tagName && "html" == this.offsetParent[0].tagName.toLowerCase() && e.ui.ie) && (t = {
                top: 0,
                left: 0
            }), {
                top: t.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: t.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            }
        },
        _getRelativeOffset: function() {
            if ("relative" == this.cssPosition) {
                var e = this.element.position();
                return {
                    top: e.top - (parseInt(this.helper.css("top"), 10) || 0) + this.scrollParent.scrollTop(),
                    left: e.left - (parseInt(this.helper.css("left"), 10) || 0) + this.scrollParent.scrollLeft()
                }
            }
            return {
                top: 0,
                left: 0
            }
        },
        _cacheMargins: function() {
            this.margins = {
                left: parseInt(this.element.css("marginLeft"), 10) || 0,
                top: parseInt(this.element.css("marginTop"), 10) || 0,
                right: parseInt(this.element.css("marginRight"), 10) || 0,
                bottom: parseInt(this.element.css("marginBottom"), 10) || 0
            }
        },
        _cacheHelperProportions: function() {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            }
        },
        _setContainment: function() {
            var t = this.options;
            if ("parent" == t.containment && (t.containment = this.helper[0].parentNode), ("document" == t.containment || "window" == t.containment) && (this.containment = ["document" == t.containment ? 0 : e(window).scrollLeft() - this.offset.relative.left - this.offset.parent.left, "document" == t.containment ? 0 : e(window).scrollTop() - this.offset.relative.top - this.offset.parent.top, ("document" == t.containment ? 0 : e(window).scrollLeft()) + e("document" == t.containment ? document : window).width() - this.helperProportions.width - this.margins.left, ("document" == t.containment ? 0 : e(window).scrollTop()) + (e("document" == t.containment ? document : window).height() || document.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top]), /^(document|window|parent)$/.test(t.containment) || t.containment.constructor == Array) t.containment.constructor == Array && (this.containment = t.containment);
            else {
                var n = e(t.containment),
                    r = n[0];
                if (!r) return;
                var s = (n.offset(), "hidden" != e(r).css("overflow"));
                this.containment = [(parseInt(e(r).css("borderLeftWidth"), 10) || 0) + (parseInt(e(r).css("paddingLeft"), 10) || 0), (parseInt(e(r).css("borderTopWidth"), 10) || 0) + (parseInt(e(r).css("paddingTop"), 10) || 0), (s ? Math.max(r.scrollWidth, r.offsetWidth) : r.offsetWidth) - (parseInt(e(r).css("borderLeftWidth"), 10) || 0) - (parseInt(e(r).css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left - this.margins.right, (s ? Math.max(r.scrollHeight, r.offsetHeight) : r.offsetHeight) - (parseInt(e(r).css("borderTopWidth"), 10) || 0) - (parseInt(e(r).css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top - this.margins.bottom], this.relative_container = n
            }
        },
        _convertPositionTo: function(t, n) {
            n || (n = this.position);
            var r = "absolute" == t ? 1 : -1,
                s = (this.options, "absolute" != this.cssPosition || this.scrollParent[0] != document && e.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent : this.offsetParent),
                o = /(html|body)/i.test(s[0].tagName);
            return {
                top: n.top + this.offset.relative.top * r + this.offset.parent.top * r - ("fixed" == this.cssPosition ? -this.scrollParent.scrollTop() : o ? 0 : s.scrollTop()) * r,
                left: n.left + this.offset.relative.left * r + this.offset.parent.left * r - ("fixed" == this.cssPosition ? -this.scrollParent.scrollLeft() : o ? 0 : s.scrollLeft()) * r
            }
        },
        _generatePosition: function(t) {
            var n = this.options,
                r = "absolute" != this.cssPosition || this.scrollParent[0] != document && e.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent : this.offsetParent,
                i = /(html|body)/i.test(r[0].tagName),
                s = t.pageX,
                o = t.pageY;
            if (this.originalPosition) {
                var u;
                if (this.containment) {
                    if (this.relative_container) {
                        var a = this.relative_container.offset();
                        u = [this.containment[0] + a.left, this.containment[1] + a.top, this.containment[2] + a.left, this.containment[3] + a.top]
                    } else u = this.containment;
                    t.pageX - this.offset.click.left < u[0] && (s = u[0] + this.offset.click.left), t.pageY - this.offset.click.top < u[1] && (o = u[1] + this.offset.click.top), t.pageX - this.offset.click.left > u[2] && (s = u[2] + this.offset.click.left), t.pageY - this.offset.click.top > u[3] && (o = u[3] + this.offset.click.top)
                }
                if (n.grid) {
                    var f = n.grid[1] ? this.originalPageY + Math.round((o - this.originalPageY) / n.grid[1]) * n.grid[1] : this.originalPageY;
                    o = u && (f - this.offset.click.top < u[1] || f - this.offset.click.top > u[3]) ? f - this.offset.click.top < u[1] ? f + n.grid[1] : f - n.grid[1] : f;
                    var l = n.grid[0] ? this.originalPageX + Math.round((s - this.originalPageX) / n.grid[0]) * n.grid[0] : this.originalPageX;
                    s = u && (l - this.offset.click.left < u[0] || l - this.offset.click.left > u[2]) ? l - this.offset.click.left < u[0] ? l + n.grid[0] : l - n.grid[0] : l
                }
            }
            return {
                top: o - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + ("fixed" == this.cssPosition ? -this.scrollParent.scrollTop() : i ? 0 : r.scrollTop()),
                left: s - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + ("fixed" == this.cssPosition ? -this.scrollParent.scrollLeft() : i ? 0 : r.scrollLeft())
            }
        },
        _clear: function() {
            this.helper.removeClass("ui-draggable-dragging"), this.helper[0] != this.element[0] && !this.cancelHelperRemoval && this.helper.remove(), this.helper = null, this.cancelHelperRemoval = !1
        },
        _trigger: function(t, n, r) {
            return r = r || this._uiHash(), e.ui.plugin.call(this, t, [n, r]), "drag" == t && (this.positionAbs = this._convertPositionTo("absolute")), e.Widget.prototype._trigger.call(this, t, n, r)
        },
        plugins: {},
        _uiHash: function() {
            return {
                helper: this.helper,
                position: this.position,
                originalPosition: this.originalPosition,
                offset: this.positionAbs
            }
        }
    }), e.ui.plugin.add("draggable", "connectToSortable", {
        start: function(t, n) {
            var r = e(this).data("draggable"),
                i = r.options,
                s = e.extend({}, n, {
                    item: r.element
                });
            r.sortables = [], e(i.connectToSortable).each(function() {
                var n = e.data(this, "sortable");
                n && !n.options.disabled && (r.sortables.push({
                    instance: n,
                    shouldRevert: n.options.revert
                }), n.refreshPositions(), n._trigger("activate", t, s))
            })
        },
        stop: function(t, n) {
            var r = e(this).data("draggable"),
                i = e.extend({}, n, {
                    item: r.element
                });
            e.each(r.sortables, function() {
                this.instance.isOver ? (this.instance.isOver = 0, r.cancelHelperRemoval = !0, this.instance.cancelHelperRemoval = !1, this.shouldRevert && (this.instance.options.revert = !0), this.instance._mouseStop(t), this.instance.options.helper = this.instance.options._helper, "original" == r.options.helper && this.instance.currentItem.css({
                    top: "auto",
                    left: "auto"
                })) : (this.instance.cancelHelperRemoval = !1, this.instance._trigger("deactivate", t, i))
            })
        },
        drag: function(t, n) {
            var r = e(this).data("draggable"),
                i = this;
            e.each(r.sortables, function() {
                var o = !1,
                    u = this;
                this.instance.positionAbs = r.positionAbs, this.instance.helperProportions = r.helperProportions, this.instance.offset.click = r.offset.click, this.instance._intersectsWith(this.instance.containerCache) && (o = !0, e.each(r.sortables, function() {
                    return this.instance.positionAbs = r.positionAbs, this.instance.helperProportions = r.helperProportions, this.instance.offset.click = r.offset.click, this != u && this.instance._intersectsWith(this.instance.containerCache) && e.ui.contains(u.instance.element[0], this.instance.element[0]) && (o = !1), o
                })), o ? (this.instance.isOver || (this.instance.isOver = 1, this.instance.currentItem = e(i).clone().removeAttr("id").appendTo(this.instance.element).data("sortable-item", !0), this.instance.options._helper = this.instance.options.helper, this.instance.options.helper = function() {
                    return n.helper[0]
                }, t.target = this.instance.currentItem[0], this.instance._mouseCapture(t, !0), this.instance._mouseStart(t, !0, !0), this.instance.offset.click.top = r.offset.click.top, this.instance.offset.click.left = r.offset.click.left, this.instance.offset.parent.left -= r.offset.parent.left - this.instance.offset.parent.left, this.instance.offset.parent.top -= r.offset.parent.top - this.instance.offset.parent.top, r._trigger("toSortable", t), r.dropped = this.instance.element, r.currentItem = r.element, this.instance.fromOutside = r), this.instance.currentItem && this.instance._mouseDrag(t)) : this.instance.isOver && (this.instance.isOver = 0, this.instance.cancelHelperRemoval = !0, this.instance.options.revert = !1, this.instance._trigger("out", t, this.instance._uiHash(this.instance)), this.instance._mouseStop(t, !0), this.instance.options.helper = this.instance.options._helper, this.instance.currentItem.remove(), this.instance.placeholder && this.instance.placeholder.remove(), r._trigger("fromSortable", t), r.dropped = !1)
            })
        }
    }), e.ui.plugin.add("draggable", "cursor", {
        start: function() {
            var r = e("body"),
                i = e(this).data("draggable").options;
            r.css("cursor") && (i._cursor = r.css("cursor")), r.css("cursor", i.cursor)
        },
        stop: function() {
            var r = e(this).data("draggable").options;
            r._cursor && e("body").css("cursor", r._cursor)
        }
    }), e.ui.plugin.add("draggable", "opacity", {
        start: function(t, n) {
            var r = e(n.helper),
                i = e(this).data("draggable").options;
            r.css("opacity") && (i._opacity = r.css("opacity")), r.css("opacity", i.opacity)
        },
        stop: function(t, n) {
            var r = e(this).data("draggable").options;
            r._opacity && e(n.helper).css("opacity", r._opacity)
        }
    }), e.ui.plugin.add("draggable", "scroll", {
        start: function() {
            var r = e(this).data("draggable");
            r.scrollParent[0] != document && "HTML" != r.scrollParent[0].tagName && (r.overflowOffset = r.scrollParent.offset())
        },
        drag: function(t) {
            var r = e(this).data("draggable"),
                i = r.options,
                s = !1;
            r.scrollParent[0] != document && "HTML" != r.scrollParent[0].tagName ? (i.axis && "x" == i.axis || (r.overflowOffset.top + r.scrollParent[0].offsetHeight - t.pageY < i.scrollSensitivity ? r.scrollParent[0].scrollTop = s = r.scrollParent[0].scrollTop + i.scrollSpeed : t.pageY - r.overflowOffset.top < i.scrollSensitivity && (r.scrollParent[0].scrollTop = s = r.scrollParent[0].scrollTop - i.scrollSpeed)), i.axis && "y" == i.axis || (r.overflowOffset.left + r.scrollParent[0].offsetWidth - t.pageX < i.scrollSensitivity ? r.scrollParent[0].scrollLeft = s = r.scrollParent[0].scrollLeft + i.scrollSpeed : t.pageX - r.overflowOffset.left < i.scrollSensitivity && (r.scrollParent[0].scrollLeft = s = r.scrollParent[0].scrollLeft - i.scrollSpeed))) : (i.axis && "x" == i.axis || (t.pageY - e(document).scrollTop() < i.scrollSensitivity ? s = e(document).scrollTop(e(document).scrollTop() - i.scrollSpeed) : e(window).height() - (t.pageY - e(document).scrollTop()) < i.scrollSensitivity && (s = e(document).scrollTop(e(document).scrollTop() + i.scrollSpeed))), i.axis && "y" == i.axis || (t.pageX - e(document).scrollLeft() < i.scrollSensitivity ? s = e(document).scrollLeft(e(document).scrollLeft() - i.scrollSpeed) : e(window).width() - (t.pageX - e(document).scrollLeft()) < i.scrollSensitivity && (s = e(document).scrollLeft(e(document).scrollLeft() + i.scrollSpeed)))), s !== !1 && e.ui.ddmanager && !i.dropBehaviour && e.ui.ddmanager.prepareOffsets(r, t)
        }
    }), e.ui.plugin.add("draggable", "snap", {
        start: function() {
            var r = e(this).data("draggable"),
                i = r.options;
            r.snapElements = [], e(i.snap.constructor != String ? i.snap.items || ":data(draggable)" : i.snap).each(function() {
                var t = e(this),
                    n = t.offset();
                this != r.element[0] && r.snapElements.push({
                    item: this,
                    width: t.outerWidth(),
                    height: t.outerHeight(),
                    top: n.top,
                    left: n.left
                })
            })
        },
        drag: function(t, n) {
            for (var r = e(this).data("draggable"), i = r.options, s = i.snapTolerance, o = n.offset.left, u = o + r.helperProportions.width, a = n.offset.top, f = a + r.helperProportions.height, l = r.snapElements.length - 1; l >= 0; l--) {
                var c = r.snapElements[l].left,
                    h = c + r.snapElements[l].width,
                    p = r.snapElements[l].top,
                    d = p + r.snapElements[l].height;
                if (o > c - s && h + s > o && a > p - s && d + s > a || o > c - s && h + s > o && f > p - s && d + s > f || u > c - s && h + s > u && a > p - s && d + s > a || u > c - s && h + s > u && f > p - s && d + s > f) {
                    if ("inner" != i.snapMode) {
                        var v = Math.abs(p - f) <= s,
                            m = Math.abs(d - a) <= s,
                            g = Math.abs(c - u) <= s,
                            y = Math.abs(h - o) <= s;
                        v && (n.position.top = r._convertPositionTo("relative", {
                            top: p - r.helperProportions.height,
                            left: 0
                        }).top - r.margins.top), m && (n.position.top = r._convertPositionTo("relative", {
                            top: d,
                            left: 0
                        }).top - r.margins.top), g && (n.position.left = r._convertPositionTo("relative", {
                            top: 0,
                            left: c - r.helperProportions.width
                        }).left - r.margins.left), y && (n.position.left = r._convertPositionTo("relative", {
                            top: 0,
                            left: h
                        }).left - r.margins.left)
                    }
                    var b = v || m || g || y;
                    if ("outer" != i.snapMode) {
                        var v = Math.abs(p - a) <= s,
                            m = Math.abs(d - f) <= s,
                            g = Math.abs(c - o) <= s,
                            y = Math.abs(h - u) <= s;
                        v && (n.position.top = r._convertPositionTo("relative", {
                            top: p,
                            left: 0
                        }).top - r.margins.top), m && (n.position.top = r._convertPositionTo("relative", {
                            top: d - r.helperProportions.height,
                            left: 0
                        }).top - r.margins.top), g && (n.position.left = r._convertPositionTo("relative", {
                            top: 0,
                            left: c
                        }).left - r.margins.left), y && (n.position.left = r._convertPositionTo("relative", {
                            top: 0,
                            left: h - r.helperProportions.width
                        }).left - r.margins.left)
                    }!r.snapElements[l].snapping && (v || m || g || y || b) && r.options.snap.snap && r.options.snap.snap.call(r.element, t, e.extend(r._uiHash(), {
                        snapItem: r.snapElements[l].item
                    })), r.snapElements[l].snapping = v || m || g || y || b
                } else r.snapElements[l].snapping && r.options.snap.release && r.options.snap.release.call(r.element, t, e.extend(r._uiHash(), {
                    snapItem: r.snapElements[l].item
                })), r.snapElements[l].snapping = !1
            }
        }
    }), e.ui.plugin.add("draggable", "stack", {
        start: function() {
            var r = e(this).data("draggable").options,
                i = e.makeArray(e(r.stack)).sort(function(t, n) {
                    return (parseInt(e(t).css("zIndex"), 10) || 0) - (parseInt(e(n).css("zIndex"), 10) || 0)
                });
            if (i.length) {
                var s = parseInt(i[0].style.zIndex) || 0;
                e(i).each(function(e) {
                    this.style.zIndex = s + e
                }), this[0].style.zIndex = s + i.length
            }
        }
    }), e.ui.plugin.add("draggable", "zIndex", {
        start: function(t, n) {
            var r = e(n.helper),
                i = e(this).data("draggable").options;
            r.css("zIndex") && (i._zIndex = r.css("zIndex")), r.css("zIndex", i.zIndex)
        },
        stop: function(t, n) {
            var r = e(this).data("draggable").options;
            r._zIndex && e(n.helper).css("zIndex", r._zIndex)
        }
    })
}(jQuery),
function(e) {
    e.widget("ui.droppable", {
        version: "1.9.2",
        widgetEventPrefix: "drop",
        options: {
            accept: "*",
            activeClass: !1,
            addClasses: !0,
            greedy: !1,
            hoverClass: !1,
            scope: "default",
            tolerance: "intersect"
        },
        _create: function() {
            var t = this.options,
                n = t.accept;
            this.isover = 0, this.isout = 1, this.accept = e.isFunction(n) ? n : function(e) {
                return e.is(n)
            }, this.proportions = {
                width: this.element[0].offsetWidth,
                height: this.element[0].offsetHeight
            }, e.ui.ddmanager.droppables[t.scope] = e.ui.ddmanager.droppables[t.scope] || [], e.ui.ddmanager.droppables[t.scope].push(this), t.addClasses && this.element.addClass("ui-droppable")
        },
        _destroy: function() {
            for (var t = e.ui.ddmanager.droppables[this.options.scope], n = 0; n < t.length; n++) t[n] == this && t.splice(n, 1);
            this.element.removeClass("ui-droppable ui-droppable-disabled")
        },
        _setOption: function(t, n) {
            "accept" == t && (this.accept = e.isFunction(n) ? n : function(e) {
                return e.is(n)
            }), e.Widget.prototype._setOption.apply(this, arguments)
        },
        _activate: function(t) {
            var n = e.ui.ddmanager.current;
            this.options.activeClass && this.element.addClass(this.options.activeClass), n && this._trigger("activate", t, this.ui(n))
        },
        _deactivate: function(t) {
            var n = e.ui.ddmanager.current;
            this.options.activeClass && this.element.removeClass(this.options.activeClass), n && this._trigger("deactivate", t, this.ui(n))
        },
        _over: function(t) {
            var n = e.ui.ddmanager.current;
            n && (n.currentItem || n.element)[0] != this.element[0] && this.accept.call(this.element[0], n.currentItem || n.element) && (this.options.hoverClass && this.element.addClass(this.options.hoverClass), this._trigger("over", t, this.ui(n)))
        },
        _out: function(t) {
            var n = e.ui.ddmanager.current;
            n && (n.currentItem || n.element)[0] != this.element[0] && this.accept.call(this.element[0], n.currentItem || n.element) && (this.options.hoverClass && this.element.removeClass(this.options.hoverClass), this._trigger("out", t, this.ui(n)))
        },
        _drop: function(t, n) {
            var r = n || e.ui.ddmanager.current;
            if (!r || (r.currentItem || r.element)[0] == this.element[0]) return !1;
            var i = !1;
            return this.element.find(":data(droppable)").not(".ui-draggable-dragging").each(function() {
                var t = e.data(this, "droppable");
                return t.options.greedy && !t.options.disabled && t.options.scope == r.options.scope && t.accept.call(t.element[0], r.currentItem || r.element) && e.ui.intersect(r, e.extend(t, {
                    offset: t.element.offset()
                }), t.options.tolerance) ? (i = !0, !1) : void 0
            }), i ? !1 : this.accept.call(this.element[0], r.currentItem || r.element) ? (this.options.activeClass && this.element.removeClass(this.options.activeClass), this.options.hoverClass && this.element.removeClass(this.options.hoverClass), this._trigger("drop", t, this.ui(r)), this.element) : !1
        },
        ui: function(e) {
            return {
                draggable: e.currentItem || e.element,
                helper: e.helper,
                position: e.position,
                offset: e.positionAbs
            }
        }
    }), e.ui.intersect = function(t, n, r) {
        if (!n.offset) return !1;
        var i = (t.positionAbs || t.position.absolute).left,
            s = i + t.helperProportions.width,
            o = (t.positionAbs || t.position.absolute).top,
            u = o + t.helperProportions.height,
            a = n.offset.left,
            f = a + n.proportions.width,
            l = n.offset.top,
            c = l + n.proportions.height;
        switch (r) {
            case "fit":
                return i >= a && f >= s && o >= l && c >= u;
            case "intersect":
                return a < i + t.helperProportions.width / 2 && s - t.helperProportions.width / 2 < f && l < o + t.helperProportions.height / 2 && u - t.helperProportions.height / 2 < c;
            case "pointer":
                var h = (t.positionAbs || t.position.absolute).left + (t.clickOffset || t.offset.click).left,
                    p = (t.positionAbs || t.position.absolute).top + (t.clickOffset || t.offset.click).top,
                    d = e.ui.isOver(p, h, l, a, n.proportions.height, n.proportions.width);
                return d;
            case "touch":
                return (o >= l && c >= o || u >= l && c >= u || l > o && u > c) && (i >= a && f >= i || s >= a && f >= s || a > i && s > f);
            default:
                return !1
        }
    }, e.ui.ddmanager = {
        current: null,
        droppables: {
            "default": []
        },
        prepareOffsets: function(t, n) {
            var r = e.ui.ddmanager.droppables[t.options.scope] || [],
                i = n ? n.type : null,
                s = (t.currentItem || t.element).find(":data(droppable)").andSelf();
            e: for (var o = 0; o < r.length; o++)
                if (!(r[o].options.disabled || t && !r[o].accept.call(r[o].element[0], t.currentItem || t.element))) {
                    for (var u = 0; u < s.length; u++)
                        if (s[u] == r[o].element[0]) {
                            r[o].proportions.height = 0;
                            continue e
                        }
                    r[o].visible = "none" != r[o].element.css("display"), r[o].visible && ("mousedown" == i && r[o]._activate.call(r[o], n), r[o].offset = r[o].element.offset(), r[o].proportions = {
                        width: r[o].element[0].offsetWidth,
                        height: r[o].element[0].offsetHeight
                    })
                }
        },
        drop: function(t, n) {
            var r = !1;
            return e.each(e.ui.ddmanager.droppables[t.options.scope] || [], function() {
                this.options && (!this.options.disabled && this.visible && e.ui.intersect(t, this, this.options.tolerance) && (r = this._drop.call(this, n) || r), !this.options.disabled && this.visible && this.accept.call(this.element[0], t.currentItem || t.element) && (this.isout = 1, this.isover = 0, this._deactivate.call(this, n)))
            }), r
        },
        dragStart: function(t, n) {
            t.element.parentsUntil("body").bind("scroll.droppable", function() {
                t.options.refreshPositions || e.ui.ddmanager.prepareOffsets(t, n)
            })
        },
        drag: function(t, n) {
            t.options.refreshPositions && e.ui.ddmanager.prepareOffsets(t, n), e.each(e.ui.ddmanager.droppables[t.options.scope] || [], function() {
                if (!this.options.disabled && !this.greedyChild && this.visible) {
                    var r = e.ui.intersect(t, this, this.options.tolerance),
                        i = r || 1 != this.isover ? r && 0 == this.isover ? "isover" : null : "isout";
                    if (i) {
                        var s;
                        if (this.options.greedy) {
                            var o = this.options.scope,
                                u = this.element.parents(":data(droppable)").filter(function() {
                                    return e.data(this, "droppable").options.scope === o
                                });
                            u.length && (s = e.data(u[0], "droppable"), s.greedyChild = "isover" == i ? 1 : 0)
                        }
                        s && "isover" == i && (s.isover = 0, s.isout = 1, s._out.call(s, n)), this[i] = 1, this["isout" == i ? "isover" : "isout"] = 0, this["isover" == i ? "_over" : "_out"].call(this, n), s && "isout" == i && (s.isout = 0, s.isover = 1, s._over.call(s, n))
                    }
                }
            })
        },
        dragStop: function(t, n) {
            t.element.parentsUntil("body").unbind("scroll.droppable"), t.options.refreshPositions || e.ui.ddmanager.prepareOffsets(t, n)
        }
    }
}(jQuery), jQuery.effects || function(e, t) {
        var n = e.uiBackCompat !== !1,
            r = "ui-effects-";
        e.effects = {
                effect: {}
            },
            function(t, n) {
                function p(e, t, n) {
                    var r = a[t.type] || {};
                    return null == e ? n || !t.def ? null : t.def : (e = r.floor ? ~~e : parseFloat(e), isNaN(e) ? t.def : r.mod ? (e + r.mod) % r.mod : 0 > e ? 0 : r.max < e ? r.max : e)
                }

                function d(e) {
                    var n = o(),
                        r = n._rgba = [];
                    return e = e.toLowerCase(), h(s, function(t, i) {
                        var s, o = i.re.exec(e),
                            a = o && i.parse(o),
                            f = i.space || "rgba";
                        return a ? (s = n[f](a), n[u[f].cache] = s[u[f].cache], r = n._rgba = s._rgba, !1) : void 0
                    }), r.length ? ("0,0,0,0" === r.join() && t.extend(r, c.transparent), n) : c[e]
                }

                function v(e, t, n) {
                    return n = (n + 1) % 1, 1 > 6 * n ? e + (t - e) * n * 6 : 1 > 2 * n ? t : 2 > 3 * n ? e + (t - e) * (2 / 3 - n) * 6 : e
                }
                var c, r = "backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor".split(" "),
                    i = /^([\-+])=\s*(\d+\.?\d*)/,
                    s = [{
                        re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d+(?:\.\d+)?)\s*)?\)/,
                        parse: function(e) {
                            return [e[1], e[2], e[3], e[4]]
                        }
                    }, {
                        re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d+(?:\.\d+)?)\s*)?\)/,
                        parse: function(e) {
                            return [2.55 * e[1], 2.55 * e[2], 2.55 * e[3], e[4]]
                        }
                    }, {
                        re: /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,
                        parse: function(e) {
                            return [parseInt(e[1], 16), parseInt(e[2], 16), parseInt(e[3], 16)]
                        }
                    }, {
                        re: /#([a-f0-9])([a-f0-9])([a-f0-9])/,
                        parse: function(e) {
                            return [parseInt(e[1] + e[1], 16), parseInt(e[2] + e[2], 16), parseInt(e[3] + e[3], 16)]
                        }
                    }, {
                        re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d+(?:\.\d+)?)\s*)?\)/,
                        space: "hsla",
                        parse: function(e) {
                            return [e[1], e[2] / 100, e[3] / 100, e[4]]
                        }
                    }],
                    o = t.Color = function(e, n, r, i) {
                        return new t.Color.fn.parse(e, n, r, i)
                    },
                    u = {
                        rgba: {
                            props: {
                                red: {
                                    idx: 0,
                                    type: "byte"
                                },
                                green: {
                                    idx: 1,
                                    type: "byte"
                                },
                                blue: {
                                    idx: 2,
                                    type: "byte"
                                }
                            }
                        },
                        hsla: {
                            props: {
                                hue: {
                                    idx: 0,
                                    type: "degrees"
                                },
                                saturation: {
                                    idx: 1,
                                    type: "percent"
                                },
                                lightness: {
                                    idx: 2,
                                    type: "percent"
                                }
                            }
                        }
                    },
                    a = {
                        "byte": {
                            floor: !0,
                            max: 255
                        },
                        percent: {
                            max: 1
                        },
                        degrees: {
                            mod: 360,
                            floor: !0
                        }
                    },
                    f = o.support = {},
                    l = t("<p>")[0],
                    h = t.each;
                l.style.cssText = "background-color:rgba(1,1,1,.5)", f.rgba = l.style.backgroundColor.indexOf("rgba") > -1, h(u, function(e, t) {
                    t.cache = "_" + e, t.props.alpha = {
                        idx: 3,
                        type: "percent",
                        def: 1
                    }
                }), o.fn = t.extend(o.prototype, {
                    parse: function(r, i, s, a) {
                        if (r === n) return this._rgba = [null, null, null, null], this;
                        (r.jquery || r.nodeType) && (r = t(r).css(i), i = n);
                        var f = this,
                            l = t.type(r),
                            v = this._rgba = [];
                        return i !== n && (r = [r, i, s, a], l = "array"), "string" === l ? this.parse(d(r) || c._default) : "array" === l ? (h(u.rgba.props, function(e, t) {
                            v[t.idx] = p(r[t.idx], t)
                        }), this) : "object" === l ? (r instanceof o ? h(u, function(e, t) {
                            r[t.cache] && (f[t.cache] = r[t.cache].slice())
                        }) : h(u, function(t, n) {
                            var i = n.cache;
                            h(n.props, function(e, t) {
                                if (!f[i] && n.to) {
                                    if ("alpha" === e || null == r[e]) return;
                                    f[i] = n.to(f._rgba)
                                }
                                f[i][t.idx] = p(r[e], t, !0)
                            }), f[i] && e.inArray(null, f[i].slice(0, 3)) < 0 && (f[i][3] = 1, n.from && (f._rgba = n.from(f[i])))
                        }), this) : void 0
                    },
                    is: function(e) {
                        var t = o(e),
                            n = !0,
                            r = this;
                        return h(u, function(e, i) {
                            var s, o = t[i.cache];
                            return o && (s = r[i.cache] || i.to && i.to(r._rgba) || [], h(i.props, function(e, t) {
                                return null != o[t.idx] ? n = o[t.idx] === s[t.idx] : void 0
                            })), n
                        }), n
                    },
                    _space: function() {
                        var e = [],
                            t = this;
                        return h(u, function(n, r) {
                            t[r.cache] && e.push(n)
                        }), e.pop()
                    },
                    transition: function(e, t) {
                        var n = o(e),
                            r = n._space(),
                            i = u[r],
                            s = 0 === this.alpha() ? o("transparent") : this,
                            f = s[i.cache] || i.to(s._rgba),
                            l = f.slice();
                        return n = n[i.cache], h(i.props, function(e, r) {
                            var i = r.idx,
                                s = f[i],
                                o = n[i],
                                u = a[r.type] || {};
                            null !== o && (null === s ? l[i] = o : (u.mod && (o - s > u.mod / 2 ? s += u.mod : s - o > u.mod / 2 && (s -= u.mod)), l[i] = p((o - s) * t + s, r)))
                        }), this[r](l)
                    },
                    blend: function(e) {
                        if (1 === this._rgba[3]) return this;
                        var n = this._rgba.slice(),
                            r = n.pop(),
                            i = o(e)._rgba;
                        return o(t.map(n, function(e, t) {
                            return (1 - r) * i[t] + r * e
                        }))
                    },
                    toRgbaString: function() {
                        var e = "rgba(",
                            n = t.map(this._rgba, function(e, t) {
                                return null == e ? t > 2 ? 1 : 0 : e
                            });
                        return 1 === n[3] && (n.pop(), e = "rgb("), e + n.join() + ")"
                    },
                    toHslaString: function() {
                        var e = "hsla(",
                            n = t.map(this.hsla(), function(e, t) {
                                return null == e && (e = t > 2 ? 1 : 0), t && 3 > t && (e = Math.round(100 * e) + "%"), e
                            });
                        return 1 === n[3] && (n.pop(), e = "hsl("), e + n.join() + ")"
                    },
                    toHexString: function(e) {
                        var n = this._rgba.slice(),
                            r = n.pop();
                        return e && n.push(~~(255 * r)), "#" + t.map(n, function(e) {
                            return e = (e || 0).toString(16), 1 === e.length ? "0" + e : e
                        }).join("")
                    },
                    toString: function() {
                        return 0 === this._rgba[3] ? "transparent" : this.toRgbaString()
                    }
                }), o.fn.parse.prototype = o.fn, u.hsla.to = function(e) {
                    if (null == e[0] || null == e[1] || null == e[2]) return [null, null, null, e[3]];
                    var l, c, t = e[0] / 255,
                        n = e[1] / 255,
                        r = e[2] / 255,
                        i = e[3],
                        s = Math.max(t, n, r),
                        o = Math.min(t, n, r),
                        u = s - o,
                        a = s + o,
                        f = .5 * a;
                    return l = o === s ? 0 : t === s ? 60 * (n - r) / u + 360 : n === s ? 60 * (r - t) / u + 120 : 60 * (t - n) / u + 240, c = 0 === f || 1 === f ? f : .5 >= f ? u / a : u / (2 - a), [Math.round(l) % 360, c, f, null == i ? 1 : i]
                }, u.hsla.from = function(e) {
                    if (null == e[0] || null == e[1] || null == e[2]) return [null, null, null, e[3]];
                    var t = e[0] / 360,
                        n = e[1],
                        r = e[2],
                        i = e[3],
                        s = .5 >= r ? r * (1 + n) : r + n - r * n,
                        o = 2 * r - s;
                    return [Math.round(255 * v(o, s, t + 1 / 3)), Math.round(255 * v(o, s, t)), Math.round(255 * v(o, s, t - 1 / 3)), i]
                }, h(u, function(e, r) {
                    var s = r.props,
                        u = r.cache,
                        a = r.to,
                        f = r.from;
                    o.fn[e] = function(e) {
                        if (a && !this[u] && (this[u] = a(this._rgba)), e === n) return this[u].slice();
                        var r, i = t.type(e),
                            l = "array" === i || "object" === i ? e : arguments,
                            c = this[u].slice();
                        return h(s, function(e, t) {
                            var n = l["object" === i ? e : t.idx];
                            null == n && (n = c[t.idx]), c[t.idx] = p(n, t)
                        }), f ? (r = o(f(c)), r[u] = c, r) : o(c)
                    }, h(s, function(n, r) {
                        o.fn[n] || (o.fn[n] = function(s) {
                            var l, o = t.type(s),
                                u = "alpha" === n ? this._hsla ? "hsla" : "rgba" : e,
                                a = this[u](),
                                f = a[r.idx];
                            return "undefined" === o ? f : ("function" === o && (s = s.call(this, f), o = t.type(s)), null == s && r.empty ? this : ("string" === o && (l = i.exec(s), l && (s = f + parseFloat(l[2]) * ("+" === l[1] ? 1 : -1))), a[r.idx] = s, this[u](a)))
                        })
                    })
                }), h(r, function(e, n) {
                    t.cssHooks[n] = {
                        set: function(e, r) {
                            var i, s, u = "";
                            if ("string" !== t.type(r) || (i = d(r))) {
                                if (r = o(i || r), !f.rgba && 1 !== r._rgba[3]) {
                                    for (s = "backgroundColor" === n ? e.parentNode : e;
                                        ("" === u || "transparent" === u) && s && s.style;) try {
                                        u = t.css(s, "backgroundColor"), s = s.parentNode
                                    } catch (a) {}
                                    r = r.blend(u && "transparent" !== u ? u : "_default")
                                }
                                r = r.toRgbaString()
                            }
                            try {
                                e.style[n] = r
                            } catch (l) {}
                        }
                    }, t.fx.step[n] = function(e) {
                        e.colorInit || (e.start = o(e.elem, n), e.end = o(e.end), e.colorInit = !0), t.cssHooks[n].set(e.elem, e.start.transition(e.end, e.pos))
                    }
                }), t.cssHooks.borderColor = {
                    expand: function(e) {
                        var t = {};
                        return h(["Top", "Right", "Bottom", "Left"], function(n, r) {
                            t["border" + r + "Color"] = e
                        }), t
                    }
                }, c = t.Color.names = {
                    aqua: "#00ffff",
                    black: "#000000",
                    blue: "#0000ff",
                    fuchsia: "#ff00ff",
                    gray: "#808080",
                    green: "#008000",
                    lime: "#00ff00",
                    maroon: "#800000",
                    navy: "#000080",
                    olive: "#808000",
                    purple: "#800080",
                    red: "#ff0000",
                    silver: "#c0c0c0",
                    teal: "#008080",
                    white: "#ffffff",
                    yellow: "#ffff00",
                    transparent: [null, null, null, 0],
                    _default: "#ffffff"
                }
            }(jQuery),
            function() {
                function i() {
                    var r, i, t = this.ownerDocument.defaultView ? this.ownerDocument.defaultView.getComputedStyle(this, null) : this.currentStyle,
                        n = {};
                    if (t && t.length && t[0] && t[t[0]])
                        for (i = t.length; i--;) r = t[i], "string" == typeof t[r] && (n[e.camelCase(r)] = t[r]);
                    else
                        for (r in t) "string" == typeof t[r] && (n[r] = t[r]);
                    return n
                }

                function s(t, n) {
                    var s, o, i = {};
                    for (s in n) o = n[s], t[s] !== o && !r[s] && (e.fx.step[s] || !isNaN(parseFloat(o))) && (i[s] = o);
                    return i
                }
                var n = ["add", "remove", "toggle"],
                    r = {
                        border: 1,
                        borderBottom: 1,
                        borderColor: 1,
                        borderLeft: 1,
                        borderRight: 1,
                        borderTop: 1,
                        borderWidth: 1,
                        margin: 1,
                        padding: 1
                    };
                e.each(["borderLeftStyle", "borderRightStyle", "borderBottomStyle", "borderTopStyle"], function(t, n) {
                    e.fx.step[n] = function(e) {
                        ("none" !== e.end && !e.setAttr || 1 === e.pos && !e.setAttr) && (jQuery.style(e.elem, n, e.end), e.setAttr = !0)
                    }
                }), e.effects.animateClass = function(t, r, o, u) {
                    var a = e.speed(r, o, u);
                    return this.queue(function() {
                        var u, r = e(this),
                            o = r.attr("class") || "",
                            f = a.children ? r.find("*").andSelf() : r;
                        f = f.map(function() {
                            var t = e(this);
                            return {
                                el: t,
                                start: i.call(this)
                            }
                        }), u = function() {
                            e.each(n, function(e, n) {
                                t[n] && r[n + "Class"](t[n])
                            })
                        }, u(), f = f.map(function() {
                            return this.end = i.call(this.el[0]), this.diff = s(this.start, this.end), this
                        }), r.attr("class", o), f = f.map(function() {
                            var t = this,
                                n = e.Deferred(),
                                r = jQuery.extend({}, a, {
                                    queue: !1,
                                    complete: function() {
                                        n.resolve(t)
                                    }
                                });
                            return this.el.animate(this.diff, r), n.promise()
                        }), e.when.apply(e, f.get()).done(function() {
                            u(), e.each(arguments, function() {
                                var t = this.el;
                                e.each(this.diff, function(e) {
                                    t.css(e, "")
                                })
                            }), a.complete.call(r[0])
                        })
                    })
                }, e.fn.extend({
                    _addClass: e.fn.addClass,
                    addClass: function(t, n, r, i) {
                        return n ? e.effects.animateClass.call(this, {
                            add: t
                        }, n, r, i) : this._addClass(t)
                    },
                    _removeClass: e.fn.removeClass,
                    removeClass: function(t, n, r, i) {
                        return n ? e.effects.animateClass.call(this, {
                            remove: t
                        }, n, r, i) : this._removeClass(t)
                    },
                    _toggleClass: e.fn.toggleClass,
                    toggleClass: function(n, r, i, s, o) {
                        return "boolean" == typeof r || r === t ? i ? e.effects.animateClass.call(this, r ? {
                            add: n
                        } : {
                            remove: n
                        }, i, s, o) : this._toggleClass(n, r) : e.effects.animateClass.call(this, {
                            toggle: n
                        }, r, i, s)
                    },
                    switchClass: function(t, n, r, i, s) {
                        return e.effects.animateClass.call(this, {
                            add: n,
                            remove: t
                        }, r, i, s)
                    }
                })
            }(),
            function() {
                function i(t, n, r, i) {
                    return e.isPlainObject(t) && (n = t, t = t.effect), t = {
                        effect: t
                    }, null == n && (n = {}), e.isFunction(n) && (i = n, r = null, n = {}), ("number" == typeof n || e.fx.speeds[n]) && (i = r, r = n, n = {}), e.isFunction(r) && (i = r, r = null), n && e.extend(t, n), r = r || n.duration, t.duration = e.fx.off ? 0 : "number" == typeof r ? r : r in e.fx.speeds ? e.fx.speeds[r] : e.fx.speeds._default, t.complete = i || n.complete, t
                }

                function s(t) {
                    return !t || "number" == typeof t || e.fx.speeds[t] ? !0 : "string" != typeof t || e.effects.effect[t] ? !1 : n && e.effects[t] ? !1 : !0
                }
                e.extend(e.effects, {
                    version: "1.9.2",
                    save: function(e, t) {
                        for (var n = 0; n < t.length; n++) null !== t[n] && e.data(r + t[n], e[0].style[t[n]])
                    },
                    restore: function(e, n) {
                        var i, s;
                        for (s = 0; s < n.length; s++) null !== n[s] && (i = e.data(r + n[s]), i === t && (i = ""), e.css(n[s], i))
                    },
                    setMode: function(e, t) {
                        return "toggle" === t && (t = e.is(":hidden") ? "show" : "hide"), t
                    },
                    getBaseline: function(e, t) {
                        var n, r;
                        switch (e[0]) {
                            case "top":
                                n = 0;
                                break;
                            case "middle":
                                n = .5;
                                break;
                            case "bottom":
                                n = 1;
                                break;
                            default:
                                n = e[0] / t.height
                        }
                        switch (e[1]) {
                            case "left":
                                r = 0;
                                break;
                            case "center":
                                r = .5;
                                break;
                            case "right":
                                r = 1;
                                break;
                            default:
                                r = e[1] / t.width
                        }
                        return {
                            x: r,
                            y: n
                        }
                    },
                    createWrapper: function(t) {
                        if (t.parent().is(".ui-effects-wrapper")) return t.parent();
                        var n = {
                                width: t.outerWidth(!0),
                                height: t.outerHeight(!0),
                                "float": t.css("float")
                            },
                            r = e("<div></div>").addClass("ui-effects-wrapper").css({
                                fontSize: "100%",
                                background: "transparent",
                                border: "none",
                                margin: 0,
                                padding: 0
                            }),
                            i = {
                                width: t.width(),
                                height: t.height()
                            },
                            s = document.activeElement;
                        try {
                            s.id
                        } catch (o) {
                            s = document.body
                        }
                        return t.wrap(r), (t[0] === s || e.contains(t[0], s)) && e(s).focus(), r = t.parent(), "static" === t.css("position") ? (r.css({
                            position: "relative"
                        }), t.css({
                            position: "relative"
                        })) : (e.extend(n, {
                            position: t.css("position"),
                            zIndex: t.css("z-index")
                        }), e.each(["top", "left", "bottom", "right"], function(e, r) {
                            n[r] = t.css(r), isNaN(parseInt(n[r], 10)) && (n[r] = "auto")
                        }), t.css({
                            position: "relative",
                            top: 0,
                            left: 0,
                            right: "auto",
                            bottom: "auto"
                        })), t.css(i), r.css(n).show()
                    },
                    removeWrapper: function(t) {
                        var n = document.activeElement;
                        return t.parent().is(".ui-effects-wrapper") && (t.parent().replaceWith(t), (t[0] === n || e.contains(t[0], n)) && e(n).focus()), t
                    },
                    setTransition: function(t, n, r, i) {
                        return i = i || {}, e.each(n, function(e, n) {
                            var s = t.cssUnit(n);
                            s[0] > 0 && (i[n] = s[0] * r + s[1])
                        }), i
                    }
                }), e.fn.extend({
                    effect: function() {
                        function a(n) {
                            function u() {
                                e.isFunction(i) && i.call(r[0]), e.isFunction(n) && n()
                            }
                            var r = e(this),
                                i = t.complete,
                                s = t.mode;
                            (r.is(":hidden") ? "hide" === s : "show" === s) ? u(): o.call(r[0], t, u)
                        }
                        var t = i.apply(this, arguments),
                            r = t.mode,
                            s = t.queue,
                            o = e.effects.effect[t.effect],
                            u = !o && n && e.effects[t.effect];
                        return e.fx.off || !o && !u ? r ? this[r](t.duration, t.complete) : this.each(function() {
                            t.complete && t.complete.call(this)
                        }) : o ? s === !1 ? this.each(a) : this.queue(s || "fx", a) : u.call(this, {
                            options: t,
                            duration: t.duration,
                            callback: t.complete,
                            mode: t.mode
                        })
                    },
                    _show: e.fn.show,
                    show: function(e) {
                        if (s(e)) return this._show.apply(this, arguments);
                        var t = i.apply(this, arguments);
                        return t.mode = "show", this.effect.call(this, t)
                    },
                    _hide: e.fn.hide,
                    hide: function(e) {
                        if (s(e)) return this._hide.apply(this, arguments);
                        var t = i.apply(this, arguments);
                        return t.mode = "hide", this.effect.call(this, t)
                    },
                    __toggle: e.fn.toggle,
                    toggle: function(t) {
                        if (s(t) || "boolean" == typeof t || e.isFunction(t)) return this.__toggle.apply(this, arguments);
                        var n = i.apply(this, arguments);
                        return n.mode = "toggle", this.effect.call(this, n)
                    },
                    cssUnit: function(t) {
                        var n = this.css(t),
                            r = [];
                        return e.each(["em", "px", "%", "pt"], function(e, t) {
                            n.indexOf(t) > 0 && (r = [parseFloat(n), t])
                        }), r
                    }
                })
            }(),
            function() {
                var t = {};
                e.each(["Quad", "Cubic", "Quart", "Quint", "Expo"], function(e, n) {
                    t[n] = function(t) {
                        return Math.pow(t, e + 2)
                    }
                }), e.extend(t, {
                    Sine: function(e) {
                        return 1 - Math.cos(e * Math.PI / 2)
                    },
                    Circ: function(e) {
                        return 1 - Math.sqrt(1 - e * e)
                    },
                    Elastic: function(e) {
                        return 0 === e || 1 === e ? e : -Math.pow(2, 8 * (e - 1)) * Math.sin((80 * (e - 1) - 7.5) * Math.PI / 15)
                    },
                    Back: function(e) {
                        return e * e * (3 * e - 2)
                    },
                    Bounce: function(e) {
                        for (var t, n = 4; e < ((t = Math.pow(2, --n)) - 1) / 11;);
                        return 1 / Math.pow(4, 3 - n) - 7.5625 * Math.pow((3 * t - 2) / 22 - e, 2)
                    }
                }), e.each(t, function(t, n) {
                    e.easing["easeIn" + t] = n, e.easing["easeOut" + t] = function(e) {
                        return 1 - n(1 - e)
                    }, e.easing["easeInOut" + t] = function(e) {
                        return .5 > e ? n(2 * e) / 2 : 1 - n(-2 * e + 2) / 2
                    }
                })
            }()
    }(jQuery),
    function(e) {
        var n = /up|down|vertical/,
            r = /up|left|vertical|horizontal/;
        e.effects.effect.blind = function(t, i) {
            var v, m, g, s = e(this),
                o = ["position", "top", "bottom", "left", "right", "height", "width"],
                u = e.effects.setMode(s, t.mode || "hide"),
                a = t.direction || "up",
                f = n.test(a),
                l = f ? "height" : "width",
                c = f ? "top" : "left",
                h = r.test(a),
                p = {},
                d = "show" === u;
            s.parent().is(".ui-effects-wrapper") ? e.effects.save(s.parent(), o) : e.effects.save(s, o), s.show(), v = e.effects.createWrapper(s).css({
                overflow: "hidden"
            }), m = v[l](), g = parseFloat(v.css(c)) || 0, p[l] = d ? m : 0, h || (s.css(f ? "bottom" : "right", 0).css(f ? "top" : "left", "auto").css({
                position: "absolute"
            }), p[c] = d ? g : m + g), d && (v.css(l, 0), h || v.css(c, g + m)), v.animate(p, {
                duration: t.duration,
                easing: t.easing,
                queue: !1,
                complete: function() {
                    "hide" === u && s.hide(), e.effects.restore(s, o), e.effects.removeWrapper(s), i()
                }
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.bounce = function(t, n) {
            var m, g, y, r = e(this),
                i = ["position", "top", "bottom", "left", "right", "height", "width"],
                s = e.effects.setMode(r, t.mode || "effect"),
                o = "hide" === s,
                u = "show" === s,
                a = t.direction || "up",
                f = t.distance,
                l = t.times || 5,
                c = 2 * l + (u || o ? 1 : 0),
                h = t.duration / c,
                p = t.easing,
                d = "up" === a || "down" === a ? "top" : "left",
                v = "up" === a || "left" === a,
                b = r.queue(),
                w = b.length;
            for ((u || o) && i.push("opacity"), e.effects.save(r, i), r.show(), e.effects.createWrapper(r), f || (f = r["top" === d ? "outerHeight" : "outerWidth"]() / 3), u && (y = {
                    opacity: 1
                }, y[d] = 0, r.css("opacity", 0).css(d, v ? 2 * -f : 2 * f).animate(y, h, p)), o && (f /= Math.pow(2, l - 1)), y = {}, y[d] = 0, m = 0; l > m; m++) g = {}, g[d] = (v ? "-=" : "+=") + f, r.animate(g, h, p).animate(y, h, p), f = o ? 2 * f : f / 2;
            o && (g = {
                opacity: 0
            }, g[d] = (v ? "-=" : "+=") + f, r.animate(g, h, p)), r.queue(function() {
                o && r.hide(), e.effects.restore(r, i), e.effects.removeWrapper(r), n()
            }), w > 1 && b.splice.apply(b, [1, 0].concat(b.splice(w, c + 1))), r.dequeue()
        }
    }(jQuery),
    function(e) {
        e.effects.effect.clip = function(t, n) {
            var h, p, d, r = e(this),
                i = ["position", "top", "bottom", "left", "right", "height", "width"],
                s = e.effects.setMode(r, t.mode || "hide"),
                o = "show" === s,
                u = t.direction || "vertical",
                a = "vertical" === u,
                f = a ? "height" : "width",
                l = a ? "top" : "left",
                c = {};
            e.effects.save(r, i), r.show(), h = e.effects.createWrapper(r).css({
                overflow: "hidden"
            }), p = "IMG" === r[0].tagName ? h : r, d = p[f](), o && (p.css(f, 0), p.css(l, d / 2)), c[f] = o ? d : 0, c[l] = o ? 0 : d / 2, p.animate(c, {
                queue: !1,
                duration: t.duration,
                easing: t.easing,
                complete: function() {
                    o || r.hide(), e.effects.restore(r, i), e.effects.removeWrapper(r), n()
                }
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.drop = function(t, n) {
            var c, r = e(this),
                i = ["position", "top", "bottom", "left", "right", "opacity", "height", "width"],
                s = e.effects.setMode(r, t.mode || "hide"),
                o = "show" === s,
                u = t.direction || "left",
                a = "up" === u || "down" === u ? "top" : "left",
                f = "up" === u || "left" === u ? "pos" : "neg",
                l = {
                    opacity: o ? 1 : 0
                };
            e.effects.save(r, i), r.show(), e.effects.createWrapper(r), c = t.distance || r["top" === a ? "outerHeight" : "outerWidth"](!0) / 2, o && r.css("opacity", 0).css(a, "pos" === f ? -c : c), l[a] = (o ? "pos" === f ? "+=" : "-=" : "pos" === f ? "-=" : "+=") + c, r.animate(l, {
                queue: !1,
                duration: t.duration,
                easing: t.easing,
                complete: function() {
                    "hide" === s && r.hide(), e.effects.restore(r, i), e.effects.removeWrapper(r), n()
                }
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.explode = function(t, n) {
            function y() {
                c.push(this), c.length === r * i && b()
            }

            function b() {
                s.css({
                    visibility: "visible"
                }), e(c).remove(), u || s.hide(), n()
            }
            var h, p, d, v, m, g, r = t.pieces ? Math.round(Math.sqrt(t.pieces)) : 3,
                i = r,
                s = e(this),
                o = e.effects.setMode(s, t.mode || "hide"),
                u = "show" === o,
                a = s.show().css("visibility", "hidden").offset(),
                f = Math.ceil(s.outerWidth() / i),
                l = Math.ceil(s.outerHeight() / r),
                c = [];
            for (h = 0; r > h; h++)
                for (v = a.top + h * l, g = h - (r - 1) / 2, p = 0; i > p; p++) d = a.left + p * f, m = p - (i - 1) / 2, s.clone().appendTo("body").wrap("<div></div>").css({
                    position: "absolute",
                    visibility: "visible",
                    left: -p * f,
                    top: -h * l
                }).parent().addClass("ui-effects-explode").css({
                    position: "absolute",
                    overflow: "hidden",
                    width: f,
                    height: l,
                    left: d + (u ? m * f : 0),
                    top: v + (u ? g * l : 0),
                    opacity: u ? 0 : 1
                }).animate({
                    left: d + (u ? 0 : m * f),
                    top: v + (u ? 0 : g * l),
                    opacity: u ? 1 : 0
                }, t.duration || 500, t.easing, y)
        }
    }(jQuery),
    function(e) {
        e.effects.effect.fade = function(t, n) {
            var r = e(this),
                i = e.effects.setMode(r, t.mode || "toggle");
            r.animate({
                opacity: i
            }, {
                queue: !1,
                duration: t.duration,
                easing: t.easing,
                complete: n
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.fold = function(t, n) {
            var d, v, r = e(this),
                i = ["position", "top", "bottom", "left", "right", "height", "width"],
                s = e.effects.setMode(r, t.mode || "hide"),
                o = "show" === s,
                u = "hide" === s,
                a = t.size || 15,
                f = /([0-9]+)%/.exec(a),
                l = !!t.horizFirst,
                c = o !== l,
                h = c ? ["width", "height"] : ["height", "width"],
                p = t.duration / 2,
                m = {},
                g = {};
            e.effects.save(r, i), r.show(), d = e.effects.createWrapper(r).css({
                overflow: "hidden"
            }), v = c ? [d.width(), d.height()] : [d.height(), d.width()], f && (a = parseInt(f[1], 10) / 100 * v[u ? 0 : 1]), o && d.css(l ? {
                height: 0,
                width: a
            } : {
                height: a,
                width: 0
            }), m[h[0]] = o ? v[0] : a, g[h[1]] = o ? v[1] : 0, d.animate(m, p, t.easing).animate(g, p, t.easing, function() {
                u && r.hide(), e.effects.restore(r, i), e.effects.removeWrapper(r), n()
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.highlight = function(t, n) {
            var r = e(this),
                i = ["backgroundImage", "backgroundColor", "opacity"],
                s = e.effects.setMode(r, t.mode || "show"),
                o = {
                    backgroundColor: r.css("backgroundColor")
                };
            "hide" === s && (o.opacity = 0), e.effects.save(r, i), r.show().css({
                backgroundImage: "none",
                backgroundColor: t.color || "#ffff99"
            }).animate(o, {
                queue: !1,
                duration: t.duration,
                easing: t.easing,
                complete: function() {
                    "hide" === s && r.hide(), e.effects.restore(r, i), n()
                }
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.pulsate = function(t, n) {
            var p, r = e(this),
                i = e.effects.setMode(r, t.mode || "show"),
                s = "show" === i,
                o = "hide" === i,
                u = s || "hide" === i,
                a = 2 * (t.times || 5) + (u ? 1 : 0),
                f = t.duration / a,
                l = 0,
                c = r.queue(),
                h = c.length;
            for ((s || !r.is(":visible")) && (r.css("opacity", 0).show(), l = 1), p = 1; a > p; p++) r.animate({
                opacity: l
            }, f, t.easing), l = 1 - l;
            r.animate({
                opacity: l
            }, f, t.easing), r.queue(function() {
                o && r.hide(), n()
            }), h > 1 && c.splice.apply(c, [1, 0].concat(c.splice(h, a + 1))), r.dequeue()
        }
    }(jQuery),
    function(e) {
        e.effects.effect.puff = function(t, n) {
            var r = e(this),
                i = e.effects.setMode(r, t.mode || "hide"),
                s = "hide" === i,
                o = parseInt(t.percent, 10) || 150,
                u = o / 100,
                a = {
                    height: r.height(),
                    width: r.width(),
                    outerHeight: r.outerHeight(),
                    outerWidth: r.outerWidth()
                };
            e.extend(t, {
                effect: "scale",
                queue: !1,
                fade: !0,
                mode: i,
                complete: n,
                percent: s ? o : 100,
                from: s ? a : {
                    height: a.height * u,
                    width: a.width * u,
                    outerHeight: a.outerHeight * u,
                    outerWidth: a.outerWidth * u
                }
            }), r.effect(t)
        }, e.effects.effect.scale = function(t, n) {
            var r = e(this),
                i = e.extend(!0, {}, t),
                s = e.effects.setMode(r, t.mode || "effect"),
                o = parseInt(t.percent, 10) || (0 === parseInt(t.percent, 10) ? 0 : "hide" === s ? 0 : 100),
                u = t.direction || "both",
                a = t.origin,
                f = {
                    height: r.height(),
                    width: r.width(),
                    outerHeight: r.outerHeight(),
                    outerWidth: r.outerWidth()
                },
                l = {
                    y: "horizontal" !== u ? o / 100 : 1,
                    x: "vertical" !== u ? o / 100 : 1
                };
            i.effect = "size", i.queue = !1, i.complete = n, "effect" !== s && (i.origin = a || ["middle", "center"], i.restore = !0), i.from = t.from || ("show" === s ? {
                height: 0,
                width: 0,
                outerHeight: 0,
                outerWidth: 0
            } : f), i.to = {
                height: f.height * l.y,
                width: f.width * l.x,
                outerHeight: f.outerHeight * l.y,
                outerWidth: f.outerWidth * l.x
            }, i.fade && ("show" === s && (i.from.opacity = 0, i.to.opacity = 1), "hide" === s && (i.from.opacity = 1, i.to.opacity = 0)), r.effect(i)
        }, e.effects.effect.size = function(t, n) {
            var r, i, s, o = e(this),
                u = ["position", "top", "bottom", "left", "right", "width", "height", "overflow", "opacity"],
                a = ["position", "top", "bottom", "left", "right", "overflow", "opacity"],
                f = ["width", "height", "overflow"],
                l = ["fontSize"],
                c = ["borderTopWidth", "borderBottomWidth", "paddingTop", "paddingBottom"],
                h = ["borderLeftWidth", "borderRightWidth", "paddingLeft", "paddingRight"],
                p = e.effects.setMode(o, t.mode || "effect"),
                d = t.restore || "effect" !== p,
                v = t.scale || "both",
                m = t.origin || ["middle", "center"],
                g = o.css("position"),
                y = d ? u : a,
                b = {
                    height: 0,
                    width: 0,
                    outerHeight: 0,
                    outerWidth: 0
                };
            "show" === p && o.show(), r = {
                height: o.height(),
                width: o.width(),
                outerHeight: o.outerHeight(),
                outerWidth: o.outerWidth()
            }, "toggle" === t.mode && "show" === p ? (o.from = t.to || b, o.to = t.from || r) : (o.from = t.from || ("show" === p ? b : r), o.to = t.to || ("hide" === p ? b : r)), s = {
                from: {
                    y: o.from.height / r.height,
                    x: o.from.width / r.width
                },
                to: {
                    y: o.to.height / r.height,
                    x: o.to.width / r.width
                }
            }, ("box" === v || "both" === v) && (s.from.y !== s.to.y && (y = y.concat(c), o.from = e.effects.setTransition(o, c, s.from.y, o.from), o.to = e.effects.setTransition(o, c, s.to.y, o.to)), s.from.x !== s.to.x && (y = y.concat(h), o.from = e.effects.setTransition(o, h, s.from.x, o.from), o.to = e.effects.setTransition(o, h, s.to.x, o.to))), ("content" === v || "both" === v) && s.from.y !== s.to.y && (y = y.concat(l).concat(f), o.from = e.effects.setTransition(o, l, s.from.y, o.from), o.to = e.effects.setTransition(o, l, s.to.y, o.to)), e.effects.save(o, y), o.show(), e.effects.createWrapper(o), o.css("overflow", "hidden").css(o.from), m && (i = e.effects.getBaseline(m, r), o.from.top = (r.outerHeight - o.outerHeight()) * i.y, o.from.left = (r.outerWidth - o.outerWidth()) * i.x, o.to.top = (r.outerHeight - o.to.outerHeight) * i.y, o.to.left = (r.outerWidth - o.to.outerWidth) * i.x), o.css(o.from), ("content" === v || "both" === v) && (c = c.concat(["marginTop", "marginBottom"]).concat(l), h = h.concat(["marginLeft", "marginRight"]), f = u.concat(c).concat(h), o.find("*[width]").each(function() {
                var n = e(this),
                    r = {
                        height: n.height(),
                        width: n.width(),
                        outerHeight: n.outerHeight(),
                        outerWidth: n.outerWidth()
                    };
                d && e.effects.save(n, f), n.from = {
                    height: r.height * s.from.y,
                    width: r.width * s.from.x,
                    outerHeight: r.outerHeight * s.from.y,
                    outerWidth: r.outerWidth * s.from.x
                }, n.to = {
                    height: r.height * s.to.y,
                    width: r.width * s.to.x,
                    outerHeight: r.height * s.to.y,
                    outerWidth: r.width * s.to.x
                }, s.from.y !== s.to.y && (n.from = e.effects.setTransition(n, c, s.from.y, n.from), n.to = e.effects.setTransition(n, c, s.to.y, n.to)), s.from.x !== s.to.x && (n.from = e.effects.setTransition(n, h, s.from.x, n.from), n.to = e.effects.setTransition(n, h, s.to.x, n.to)), n.css(n.from), n.animate(n.to, t.duration, t.easing, function() {
                    d && e.effects.restore(n, f)
                })
            })), o.animate(o.to, {
                queue: !1,
                duration: t.duration,
                easing: t.easing,
                complete: function() {
                    0 === o.to.opacity && o.css("opacity", o.from.opacity), "hide" === p && o.hide(), e.effects.restore(o, y), d || ("static" === g ? o.css({
                        position: "relative",
                        top: o.to.top,
                        left: o.to.left
                    }) : e.each(["top", "left"], function(e, t) {
                        o.css(t, function(t, n) {
                            var r = parseInt(n, 10),
                                i = e ? o.to.left : o.to.top;
                            return "auto" === n ? i + "px" : r + i + "px"
                        })
                    })), e.effects.removeWrapper(o), n()
                }
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.shake = function(t, n) {
            var m, r = e(this),
                i = ["position", "top", "bottom", "left", "right", "height", "width"],
                s = e.effects.setMode(r, t.mode || "effect"),
                o = t.direction || "left",
                u = t.distance || 20,
                a = t.times || 3,
                f = 2 * a + 1,
                l = Math.round(t.duration / f),
                c = "up" === o || "down" === o ? "top" : "left",
                h = "up" === o || "left" === o,
                p = {},
                d = {},
                v = {},
                g = r.queue(),
                y = g.length;
            for (e.effects.save(r, i), r.show(), e.effects.createWrapper(r), p[c] = (h ? "-=" : "+=") + u, d[c] = (h ? "+=" : "-=") + 2 * u, v[c] = (h ? "-=" : "+=") + 2 * u, r.animate(p, l, t.easing), m = 1; a > m; m++) r.animate(d, l, t.easing).animate(v, l, t.easing);
            r.animate(d, l, t.easing).animate(p, l / 2, t.easing).queue(function() {
                "hide" === s && r.hide(), e.effects.restore(r, i), e.effects.removeWrapper(r), n()
            }), y > 1 && g.splice.apply(g, [1, 0].concat(g.splice(y, f + 1))), r.dequeue()
        }
    }(jQuery),
    function(e) {
        e.effects.effect.slide = function(t, n) {
            var l, r = e(this),
                i = ["position", "top", "bottom", "left", "right", "width", "height"],
                s = e.effects.setMode(r, t.mode || "show"),
                o = "show" === s,
                u = t.direction || "left",
                a = "up" === u || "down" === u ? "top" : "left",
                f = "up" === u || "left" === u,
                c = {};
            e.effects.save(r, i), r.show(), l = t.distance || r["top" === a ? "outerHeight" : "outerWidth"](!0), e.effects.createWrapper(r).css({
                overflow: "hidden"
            }), o && r.css(a, f ? isNaN(l) ? "-" + l : -l : l), c[a] = (o ? f ? "+=" : "-=" : f ? "-=" : "+=") + l, r.animate(c, {
                queue: !1,
                duration: t.duration,
                easing: t.easing,
                complete: function() {
                    "hide" === s && r.hide(), e.effects.restore(r, i), e.effects.removeWrapper(r), n()
                }
            })
        }
    }(jQuery),
    function(e) {
        e.effects.effect.transfer = function(t, n) {
            var r = e(this),
                i = e(t.to),
                s = "fixed" === i.css("position"),
                o = e("body"),
                u = s ? o.scrollTop() : 0,
                a = s ? o.scrollLeft() : 0,
                f = i.offset(),
                l = {
                    top: f.top - u,
                    left: f.left - a,
                    height: i.innerHeight(),
                    width: i.innerWidth()
                },
                c = r.offset(),
                h = e('<div class="ui-effects-transfer"></div>').appendTo(document.body).addClass(t.className).css({
                    top: c.top - u,
                    left: c.left - a,
                    height: r.innerHeight(),
                    width: r.innerWidth(),
                    position: s ? "fixed" : "absolute"
                }).animate(l, t.duration, t.easing, function() {
                    h.remove(), n()
                })
        }
    }(jQuery),
    function(e) {
        var n = !1;
        e.widget("ui.menu", {
            version: "1.9.2",
            defaultElement: "<ul>",
            delay: 300,
            options: {
                icons: {
                    submenu: "ui-icon-carat-1-e"
                },
                menus: "ul",
                position: {
                    my: "left top",
                    at: "right top"
                },
                role: "menu",
                blur: null,
                focus: null,
                select: null
            },
            _create: function() {
                this.activeMenu = this.element, this.element.uniqueId().addClass("ui-menu ui-widget ui-widget-content ui-corner-all").toggleClass("ui-menu-icons", !!this.element.find(".ui-icon").length).attr({
                    role: this.options.role,
                    tabIndex: 0
                }).bind("click" + this.eventNamespace, e.proxy(function(e) {
                    this.options.disabled && e.preventDefault()
                }, this)), this.options.disabled && this.element.addClass("ui-state-disabled").attr("aria-disabled", "true"), this._on({
                    "mousedown .ui-menu-item > a": function(e) {
                        e.preventDefault()
                    },
                    "click .ui-state-disabled > a": function(e) {
                        e.preventDefault()
                    },
                    "click .ui-menu-item:has(a)": function(t) {
                        var r = e(t.target).closest(".ui-menu-item");
                        !n && r.not(".ui-state-disabled").length && (n = !0, this.select(t), r.has(".ui-menu").length ? this.expand(t) : this.element.is(":focus") || (this.element.trigger("focus", [!0]), this.active && 1 === this.active.parents(".ui-menu").length && clearTimeout(this.timer)))
                    },
                    "mouseenter .ui-menu-item": function(t) {
                        var n = e(t.currentTarget);
                        n.siblings().children(".ui-state-active").removeClass("ui-state-active"), this.focus(t, n)
                    },
                    mouseleave: "collapseAll",
                    "mouseleave .ui-menu": "collapseAll",
                    focus: function(e, t) {
                        var n = this.active || this.element.children(".ui-menu-item").eq(0);
                        t || this.focus(e, n)
                    },
                    blur: function(t) {
                        this._delay(function() {
                            e.contains(this.element[0], this.document[0].activeElement) || this.collapseAll(t)
                        })
                    },
                    keydown: "_keydown"
                }), this.refresh(), this._on(this.document, {
                    click: function(t) {
                        e(t.target).closest(".ui-menu").length || this.collapseAll(t), n = !1
                    }
                })
            },
            _destroy: function() {
                this.element.removeAttr("aria-activedescendant").find(".ui-menu").andSelf().removeClass("ui-menu ui-widget ui-widget-content ui-corner-all ui-menu-icons").removeAttr("role").removeAttr("tabIndex").removeAttr("aria-labelledby").removeAttr("aria-expanded").removeAttr("aria-hidden").removeAttr("aria-disabled").removeUniqueId().show(), this.element.find(".ui-menu-item").removeClass("ui-menu-item").removeAttr("role").removeAttr("aria-disabled").children("a").removeUniqueId().removeClass("ui-corner-all ui-state-hover").removeAttr("tabIndex").removeAttr("role").removeAttr("aria-haspopup").children().each(function() {
                    var t = e(this);
                    t.data("ui-menu-submenu-carat") && t.remove()
                }), this.element.find(".ui-menu-divider").removeClass("ui-menu-divider ui-widget-content")
            },
            _keydown: function(t) {
                function a(e) {
                    return e.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&")
                }
                var n, r, i, s, o, u = !0;
                switch (t.keyCode) {
                    case e.ui.keyCode.PAGE_UP:
                        this.previousPage(t);
                        break;
                    case e.ui.keyCode.PAGE_DOWN:
                        this.nextPage(t);
                        break;
                    case e.ui.keyCode.HOME:
                        this._move("first", "first", t);
                        break;
                    case e.ui.keyCode.END:
                        this._move("last", "last", t);
                        break;
                    case e.ui.keyCode.UP:
                        this.previous(t);
                        break;
                    case e.ui.keyCode.DOWN:
                        this.next(t);
                        break;
                    case e.ui.keyCode.LEFT:
                        this.collapse(t);
                        break;
                    case e.ui.keyCode.RIGHT:
                        this.active && !this.active.is(".ui-state-disabled") && this.expand(t);
                        break;
                    case e.ui.keyCode.ENTER:
                    case e.ui.keyCode.SPACE:
                        this._activate(t);
                        break;
                    case e.ui.keyCode.ESCAPE:
                        this.collapse(t);
                        break;
                    default:
                        u = !1, r = this.previousFilter || "", i = String.fromCharCode(t.keyCode), s = !1, clearTimeout(this.filterTimer), i === r ? s = !0 : i = r + i, o = new RegExp("^" + a(i), "i"), n = this.activeMenu.children(".ui-menu-item").filter(function() {
                            return o.test(e(this).children("a").text())
                        }), n = s && -1 !== n.index(this.active.next()) ? this.active.nextAll(".ui-menu-item") : n, n.length || (i = String.fromCharCode(t.keyCode), o = new RegExp("^" + a(i), "i"), n = this.activeMenu.children(".ui-menu-item").filter(function() {
                            return o.test(e(this).children("a").text())
                        })), n.length ? (this.focus(t, n), n.length > 1 ? (this.previousFilter = i, this.filterTimer = this._delay(function() {
                            delete this.previousFilter
                        }, 1e3)) : delete this.previousFilter) : delete this.previousFilter
                }
                u && t.preventDefault()
            },
            _activate: function(e) {
                this.active.is(".ui-state-disabled") || (this.active.children("a[aria-haspopup='true']").length ? this.expand(e) : this.select(e))
            },
            refresh: function() {
                var t, n = this.options.icons.submenu,
                    r = this.element.find(this.options.menus);
                r.filter(":not(.ui-menu)").addClass("ui-menu ui-widget ui-widget-content ui-corner-all").hide().attr({
                    role: this.options.role,
                    "aria-hidden": "true",
                    "aria-expanded": "false"
                }).each(function() {
                    var t = e(this),
                        r = t.prev("a"),
                        i = e("<span>").addClass("ui-menu-icon ui-icon " + n).data("ui-menu-submenu-carat", !0);
                    r.attr("aria-haspopup", "true").prepend(i), t.attr("aria-labelledby", r.attr("id"))
                }), t = r.add(this.element), t.children(":not(.ui-menu-item):has(a)").addClass("ui-menu-item").attr("role", "presentation").children("a").uniqueId().addClass("ui-corner-all").attr({
                    tabIndex: -1,
                    role: this._itemRole()
                }), t.children(":not(.ui-menu-item)").each(function() {
                    var t = e(this);
                    /[^\-—–\s]/.test(t.text()) || t.addClass("ui-widget-content ui-menu-divider")
                }), t.children(".ui-state-disabled").attr("aria-disabled", "true"), this.active && !e.contains(this.element[0], this.active[0]) && this.blur()
            },
            _itemRole: function() {
                return {
                    menu: "menuitem",
                    listbox: "option"
                }[this.options.role]
            },
            focus: function(e, t) {
                var n, r;
                this.blur(e, e && "focus" === e.type), this._scrollIntoView(t), this.active = t.first(), r = this.active.children("a").addClass("ui-state-focus"), this.options.role && this.element.attr("aria-activedescendant", r.attr("id")), this.active.parent().closest(".ui-menu-item").children("a:first").addClass("ui-state-active"), e && "keydown" === e.type ? this._close() : this.timer = this._delay(function() {
                    this._close()
                }, this.delay), n = t.children(".ui-menu"), n.length && /^mouse/.test(e.type) && this._startOpening(n), this.activeMenu = t.parent(), this._trigger("focus", e, {
                    item: t
                })
            },
            _scrollIntoView: function(t) {
                var n, r, i, s, o, u;
                this._hasScroll() && (n = parseFloat(e.css(this.activeMenu[0], "borderTopWidth")) || 0, r = parseFloat(e.css(this.activeMenu[0], "paddingTop")) || 0, i = t.offset().top - this.activeMenu.offset().top - n - r, s = this.activeMenu.scrollTop(), o = this.activeMenu.height(), u = t.height(), 0 > i ? this.activeMenu.scrollTop(s + i) : i + u > o && this.activeMenu.scrollTop(s + i - o + u))
            },
            blur: function(e, t) {
                t || clearTimeout(this.timer), this.active && (this.active.children("a").removeClass("ui-state-focus"), this.active = null, this._trigger("blur", e, {
                    item: this.active
                }))
            },
            _startOpening: function(e) {
                clearTimeout(this.timer), "true" === e.attr("aria-hidden") && (this.timer = this._delay(function() {
                    this._close(), this._open(e)
                }, this.delay))
            },
            _open: function(t) {
                var n = e.extend({
                    of: this.active
                }, this.options.position);
                clearTimeout(this.timer), this.element.find(".ui-menu").not(t.parents(".ui-menu")).hide().attr("aria-hidden", "true"), t.show().removeAttr("aria-hidden").attr("aria-expanded", "true").position(n)
            },
            collapseAll: function(t, n) {
                clearTimeout(this.timer), this.timer = this._delay(function() {
                    var r = n ? this.element : e(t && t.target).closest(this.element.find(".ui-menu"));
                    r.length || (r = this.element), this._close(r), this.blur(t), this.activeMenu = r
                }, this.delay)
            },
            _close: function(e) {
                e || (e = this.active ? this.active.parent() : this.element), e.find(".ui-menu").hide().attr("aria-hidden", "true").attr("aria-expanded", "false").end().find("a.ui-state-active").removeClass("ui-state-active")
            },
            collapse: function(e) {
                var t = this.active && this.active.parent().closest(".ui-menu-item", this.element);
                t && t.length && (this._close(), this.focus(e, t))
            },
            expand: function(e) {
                var t = this.active && this.active.children(".ui-menu ").children(".ui-menu-item").first();
                t && t.length && (this._open(t.parent()), this._delay(function() {
                    this.focus(e, t)
                }))
            },
            next: function(e) {
                this._move("next", "first", e)
            },
            previous: function(e) {
                this._move("prev", "last", e)
            },
            isFirstItem: function() {
                return this.active && !this.active.prevAll(".ui-menu-item").length
            },
            isLastItem: function() {
                return this.active && !this.active.nextAll(".ui-menu-item").length
            },
            _move: function(e, t, n) {
                var r;
                this.active && (r = "first" === e || "last" === e ? this.active["first" === e ? "prevAll" : "nextAll"](".ui-menu-item").eq(-1) : this.active[e + "All"](".ui-menu-item").eq(0)), r && r.length && this.active || (r = this.activeMenu.children(".ui-menu-item")[t]()), this.focus(n, r)
            },
            nextPage: function(t) {
                var n, r, i;
                return this.active ? void(this.isLastItem() || (this._hasScroll() ? (r = this.active.offset().top, i = this.element.height(), this.active.nextAll(".ui-menu-item").each(function() {
                    return n = e(this), n.offset().top - r - i < 0
                }), this.focus(t, n)) : this.focus(t, this.activeMenu.children(".ui-menu-item")[this.active ? "last" : "first"]()))) : void this.next(t)
            },
            previousPage: function(t) {
                var n, r, i;
                return this.active ? void(this.isFirstItem() || (this._hasScroll() ? (r = this.active.offset().top, i = this.element.height(), this.active.prevAll(".ui-menu-item").each(function() {
                    return n = e(this), n.offset().top - r + i > 0
                }), this.focus(t, n)) : this.focus(t, this.activeMenu.children(".ui-menu-item").first()))) : void this.next(t)
            },
            _hasScroll: function() {
                return this.element.outerHeight() < this.element.prop("scrollHeight")
            },
            select: function(t) {
                this.active = this.active || e(t.target).closest(".ui-menu-item");
                var n = {
                    item: this.active
                };
                this.active.has(".ui-menu").length || this.collapseAll(t, !0), this._trigger("select", t, n)
            }
        })
    }(jQuery),
    function(e, t) {
        e.widget("ui.progressbar", {
            version: "1.9.2",
            options: {
                value: 0,
                max: 100
            },
            min: 0,
            _create: function() {
                this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({
                    role: "progressbar",
                    "aria-valuemin": this.min,
                    "aria-valuemax": this.options.max,
                    "aria-valuenow": this._value()
                }), this.valueDiv = e("<div class='ui-progressbar-value ui-widget-header ui-corner-left'></div>").appendTo(this.element), this.oldValue = this._value(), this._refreshValue()
            },
            _destroy: function() {
                this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow"), this.valueDiv.remove()
            },
            value: function(e) {
                return e === t ? this._value() : (this._setOption("value", e), this)
            },
            _setOption: function(e, t) {
                "value" === e && (this.options.value = t, this._refreshValue(), this._value() === this.options.max && this._trigger("complete")), this._super(e, t)
            },
            _value: function() {
                var e = this.options.value;
                return "number" != typeof e && (e = 0), Math.min(this.options.max, Math.max(this.min, e))
            },
            _percentage: function() {
                return 100 * this._value() / this.options.max
            },
            _refreshValue: function() {
                var e = this.value(),
                    t = this._percentage();
                this.oldValue !== e && (this.oldValue = e, this._trigger("change")), this.valueDiv.toggle(e > this.min).toggleClass("ui-corner-right", e === this.options.max).width(t.toFixed(0) + "%"), this.element.attr("aria-valuenow", e)
            }
        })
    }(jQuery),
    function(e) {
        e.widget("ui.resizable", e.ui.mouse, {
            version: "1.9.2",
            widgetEventPrefix: "resize",
            options: {
                alsoResize: !1,
                animate: !1,
                animateDuration: "slow",
                animateEasing: "swing",
                aspectRatio: !1,
                autoHide: !1,
                containment: !1,
                ghost: !1,
                grid: !1,
                handles: "e,s,se",
                helper: !1,
                maxHeight: null,
                maxWidth: null,
                minHeight: 10,
                minWidth: 10,
                zIndex: 1e3
            },
            _create: function() {
                var t = this,
                    n = this.options;
                if (this.element.addClass("ui-resizable"), e.extend(this, {
                        _aspectRatio: !!n.aspectRatio,
                        aspectRatio: n.aspectRatio,
                        originalElement: this.element,
                        _proportionallyResizeElements: [],
                        _helper: n.helper || n.ghost || n.animate ? n.helper || "ui-resizable-helper" : null
                    }), this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i) && (this.element.wrap(e('<div class="ui-wrapper" style="overflow: hidden;"></div>').css({
                        position: this.element.css("position"),
                        width: this.element.outerWidth(),
                        height: this.element.outerHeight(),
                        top: this.element.css("top"),
                        left: this.element.css("left")
                    })), this.element = this.element.parent().data("resizable", this.element.data("resizable")), this.elementIsWrapper = !0, this.element.css({
                        marginLeft: this.originalElement.css("marginLeft"),
                        marginTop: this.originalElement.css("marginTop"),
                        marginRight: this.originalElement.css("marginRight"),
                        marginBottom: this.originalElement.css("marginBottom")
                    }), this.originalElement.css({
                        marginLeft: 0,
                        marginTop: 0,
                        marginRight: 0,
                        marginBottom: 0
                    }), this.originalResizeStyle = this.originalElement.css("resize"), this.originalElement.css("resize", "none"), this._proportionallyResizeElements.push(this.originalElement.css({
                        position: "static",
                        zoom: 1,
                        display: "block"
                    })), this.originalElement.css({
                        margin: this.originalElement.css("margin")
                    }), this._proportionallyResize()), this.handles = n.handles || (e(".ui-resizable-handle", this.element).length ? {
                        n: ".ui-resizable-n",
                        e: ".ui-resizable-e",
                        s: ".ui-resizable-s",
                        w: ".ui-resizable-w",
                        se: ".ui-resizable-se",
                        sw: ".ui-resizable-sw",
                        ne: ".ui-resizable-ne",
                        nw: ".ui-resizable-nw"
                    } : "e,s,se"), this.handles.constructor == String) {
                    "all" == this.handles && (this.handles = "n,e,s,w,se,sw,ne,nw");
                    var r = this.handles.split(",");
                    this.handles = {};
                    for (var i = 0; i < r.length; i++) {
                        var s = e.trim(r[i]),
                            o = "ui-resizable-" + s,
                            u = e('<div class="ui-resizable-handle ' + o + '"></div>');
                        u.css({
                            zIndex: n.zIndex
                        }), "se" == s && u.addClass("ui-icon ui-icon-gripsmall-diagonal-se"), this.handles[s] = ".ui-resizable-" + s, this.element.append(u)
                    }
                }
                this._renderAxis = function(t) {
                    t = t || this.element;
                    for (var n in this.handles) {
                        if (this.handles[n].constructor == String && (this.handles[n] = e(this.handles[n], this.element).show()), this.elementIsWrapper && this.originalElement[0].nodeName.match(/textarea|input|select|button/i)) {
                            var r = e(this.handles[n], this.element),
                                i = 0;
                            i = /sw|ne|nw|se|n|s/.test(n) ? r.outerHeight() : r.outerWidth();
                            var s = ["padding", /ne|nw|n/.test(n) ? "Top" : /se|sw|s/.test(n) ? "Bottom" : /^e$/.test(n) ? "Right" : "Left"].join("");
                            t.css(s, i), this._proportionallyResize()
                        }
                        e(this.handles[n]).length
                    }
                }, this._renderAxis(this.element), this._handles = e(".ui-resizable-handle", this.element).disableSelection(), this._handles.mouseover(function() {
                    if (!t.resizing) {
                        if (this.className) var e = this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i);
                        t.axis = e && e[1] ? e[1] : "se"
                    }
                }), n.autoHide && (this._handles.hide(), e(this.element).addClass("ui-resizable-autohide").mouseenter(function() {
                    n.disabled || (e(this).removeClass("ui-resizable-autohide"), t._handles.show())
                }).mouseleave(function() {
                    n.disabled || t.resizing || (e(this).addClass("ui-resizable-autohide"), t._handles.hide())
                })), this._mouseInit()
            },
            _destroy: function() {
                this._mouseDestroy();
                var t = function(t) {
                    e(t).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").removeData("ui-resizable").unbind(".resizable").find(".ui-resizable-handle").remove()
                };
                if (this.elementIsWrapper) {
                    t(this.element);
                    var n = this.element;
                    this.originalElement.css({
                        position: n.css("position"),
                        width: n.outerWidth(),
                        height: n.outerHeight(),
                        top: n.css("top"),
                        left: n.css("left")
                    }).insertAfter(n), n.remove()
                }
                return this.originalElement.css("resize", this.originalResizeStyle), t(this.originalElement), this
            },
            _mouseCapture: function(t) {
                var n = !1;
                for (var r in this.handles) e(this.handles[r])[0] == t.target && (n = !0);
                return !this.options.disabled && n
            },
            _mouseStart: function(t) {
                var r = this.options,
                    i = this.element.position(),
                    s = this.element;
                this.resizing = !0, this.documentScroll = {
                    top: e(document).scrollTop(),
                    left: e(document).scrollLeft()
                }, (s.is(".ui-draggable") || /absolute/.test(s.css("position"))) && s.css({
                    position: "absolute",
                    top: i.top,
                    left: i.left
                }), this._renderProxy();
                var o = n(this.helper.css("left")),
                    u = n(this.helper.css("top"));
                r.containment && (o += e(r.containment).scrollLeft() || 0, u += e(r.containment).scrollTop() || 0), this.offset = this.helper.offset(), this.position = {
                    left: o,
                    top: u
                }, this.size = this._helper ? {
                    width: s.outerWidth(),
                    height: s.outerHeight()
                } : {
                    width: s.width(),
                    height: s.height()
                }, this.originalSize = this._helper ? {
                    width: s.outerWidth(),
                    height: s.outerHeight()
                } : {
                    width: s.width(),
                    height: s.height()
                }, this.originalPosition = {
                    left: o,
                    top: u
                }, this.sizeDiff = {
                    width: s.outerWidth() - s.width(),
                    height: s.outerHeight() - s.height()
                }, this.originalMousePosition = {
                    left: t.pageX,
                    top: t.pageY
                }, this.aspectRatio = "number" == typeof r.aspectRatio ? r.aspectRatio : this.originalSize.width / this.originalSize.height || 1;
                var a = e(".ui-resizable-" + this.axis).css("cursor");
                return e("body").css("cursor", "auto" == a ? this.axis + "-resize" : a), s.addClass("ui-resizable-resizing"), this._propagate("start", t), !0
            },
            _mouseDrag: function(e) {
                var t = this.helper,
                    s = (this.options, this.originalMousePosition),
                    o = this.axis,
                    u = e.pageX - s.left || 0,
                    a = e.pageY - s.top || 0,
                    f = this._change[o];
                if (!f) return !1;
                var l = f.apply(this, [e, u, a]);
                return this._updateVirtualBoundaries(e.shiftKey), (this._aspectRatio || e.shiftKey) && (l = this._updateRatio(l, e)), l = this._respectSize(l, e), this._propagate("resize", e), t.css({
                    top: this.position.top + "px",
                    left: this.position.left + "px",
                    width: this.size.width + "px",
                    height: this.size.height + "px"
                }), !this._helper && this._proportionallyResizeElements.length && this._proportionallyResize(), this._updateCache(l), this._trigger("resize", e, this.ui()), !1
            },
            _mouseStop: function(t) {
                this.resizing = !1;
                var n = this.options,
                    r = this;
                if (this._helper) {
                    var i = this._proportionallyResizeElements,
                        s = i.length && /textarea/i.test(i[0].nodeName),
                        o = s && e.ui.hasScroll(i[0], "left") ? 0 : r.sizeDiff.height,
                        u = s ? 0 : r.sizeDiff.width,
                        a = {
                            width: r.helper.width() - u,
                            height: r.helper.height() - o
                        },
                        f = parseInt(r.element.css("left"), 10) + (r.position.left - r.originalPosition.left) || null,
                        l = parseInt(r.element.css("top"), 10) + (r.position.top - r.originalPosition.top) || null;
                    n.animate || this.element.css(e.extend(a, {
                        top: l,
                        left: f
                    })), r.helper.height(r.size.height), r.helper.width(r.size.width), this._helper && !n.animate && this._proportionallyResize()
                }
                return e("body").css("cursor", "auto"), this.element.removeClass("ui-resizable-resizing"), this._propagate("stop", t), this._helper && this.helper.remove(), !1
            },
            _updateVirtualBoundaries: function(e) {
                var n, i, s, o, u, t = this.options;
                u = {
                    minWidth: r(t.minWidth) ? t.minWidth : 0,
                    maxWidth: r(t.maxWidth) ? t.maxWidth : 1 / 0,
                    minHeight: r(t.minHeight) ? t.minHeight : 0,
                    maxHeight: r(t.maxHeight) ? t.maxHeight : 1 / 0
                }, (this._aspectRatio || e) && (n = u.minHeight * this.aspectRatio, s = u.minWidth / this.aspectRatio, i = u.maxHeight * this.aspectRatio, o = u.maxWidth / this.aspectRatio, n > u.minWidth && (u.minWidth = n), s > u.minHeight && (u.minHeight = s), i < u.maxWidth && (u.maxWidth = i), o < u.maxHeight && (u.maxHeight = o)), this._vBoundaries = u
            },
            _updateCache: function(e) {
                this.options;
                this.offset = this.helper.offset(), r(e.left) && (this.position.left = e.left), r(e.top) && (this.position.top = e.top), r(e.height) && (this.size.height = e.height), r(e.width) && (this.size.width = e.width)
            },
            _updateRatio: function(e) {
                var i = (this.options, this.position),
                    s = this.size,
                    o = this.axis;
                return r(e.height) ? e.width = e.height * this.aspectRatio : r(e.width) && (e.height = e.width / this.aspectRatio), "sw" == o && (e.left = i.left + (s.width - e.width), e.top = null), "nw" == o && (e.top = i.top + (s.height - e.height), e.left = i.left + (s.width - e.width)), e
            },
            _respectSize: function(e, t) {
                var i = (this.helper, this._vBoundaries),
                    o = (this._aspectRatio || t.shiftKey, this.axis),
                    u = r(e.width) && i.maxWidth && i.maxWidth < e.width,
                    a = r(e.height) && i.maxHeight && i.maxHeight < e.height,
                    f = r(e.width) && i.minWidth && i.minWidth > e.width,
                    l = r(e.height) && i.minHeight && i.minHeight > e.height;
                f && (e.width = i.minWidth), l && (e.height = i.minHeight), u && (e.width = i.maxWidth), a && (e.height = i.maxHeight);
                var c = this.originalPosition.left + this.originalSize.width,
                    h = this.position.top + this.size.height,
                    p = /sw|nw|w/.test(o),
                    d = /nw|ne|n/.test(o);
                f && p && (e.left = c - i.minWidth), u && p && (e.left = c - i.maxWidth), l && d && (e.top = h - i.minHeight), a && d && (e.top = h - i.maxHeight);
                var v = !e.width && !e.height;
                return v && !e.left && e.top ? e.top = null : v && !e.top && e.left && (e.left = null), e
            },
            _proportionallyResize: function() {
                this.options;
                if (this._proportionallyResizeElements.length)
                    for (var n = this.helper || this.element, r = 0; r < this._proportionallyResizeElements.length; r++) {
                        var i = this._proportionallyResizeElements[r];
                        if (!this.borderDif) {
                            var s = [i.css("borderTopWidth"), i.css("borderRightWidth"), i.css("borderBottomWidth"), i.css("borderLeftWidth")],
                                o = [i.css("paddingTop"), i.css("paddingRight"), i.css("paddingBottom"), i.css("paddingLeft")];
                            this.borderDif = e.map(s, function(e, t) {
                                var n = parseInt(e, 10) || 0,
                                    r = parseInt(o[t], 10) || 0;
                                return n + r
                            })
                        }
                        i.css({
                            height: n.height() - this.borderDif[0] - this.borderDif[2] || 0,
                            width: n.width() - this.borderDif[1] - this.borderDif[3] || 0
                        })
                    }
            },
            _renderProxy: function() {
                var t = this.element,
                    n = this.options;
                if (this.elementOffset = t.offset(), this._helper) {
                    this.helper = this.helper || e('<div style="overflow:hidden;"></div>');
                    var r = e.ui.ie6 ? 1 : 0,
                        i = e.ui.ie6 ? 2 : -1;
                    this.helper.addClass(this._helper).css({
                        width: this.element.outerWidth() + i,
                        height: this.element.outerHeight() + i,
                        position: "absolute",
                        left: this.elementOffset.left - r + "px",
                        top: this.elementOffset.top - r + "px",
                        zIndex: ++n.zIndex
                    }), this.helper.appendTo("body").disableSelection()
                } else this.helper = this.element
            },
            _change: {
                e: function(e, t) {
                    return {
                        width: this.originalSize.width + t
                    }
                },
                w: function(e, t) {
                    var i = (this.options, this.originalSize),
                        s = this.originalPosition;
                    return {
                        left: s.left + t,
                        width: i.width - t
                    }
                },
                n: function(e, t, n) {
                    var i = (this.options, this.originalSize),
                        s = this.originalPosition;
                    return {
                        top: s.top + n,
                        height: i.height - n
                    }
                },
                s: function(e, t, n) {
                    return {
                        height: this.originalSize.height + n
                    }
                },
                se: function(t, n, r) {
                    return e.extend(this._change.s.apply(this, arguments), this._change.e.apply(this, [t, n, r]))
                },
                sw: function(t, n, r) {
                    return e.extend(this._change.s.apply(this, arguments), this._change.w.apply(this, [t, n, r]))
                },
                ne: function(t, n, r) {
                    return e.extend(this._change.n.apply(this, arguments), this._change.e.apply(this, [t, n, r]))
                },
                nw: function(t, n, r) {
                    return e.extend(this._change.n.apply(this, arguments), this._change.w.apply(this, [t, n, r]))
                }
            },
            _propagate: function(t, n) {
                e.ui.plugin.call(this, t, [n, this.ui()]), "resize" != t && this._trigger(t, n, this.ui())
            },
            plugins: {},
            ui: function() {
                return {
                    originalElement: this.originalElement,
                    element: this.element,
                    helper: this.helper,
                    position: this.position,
                    size: this.size,
                    originalSize: this.originalSize,
                    originalPosition: this.originalPosition
                }
            }
        }), e.ui.plugin.add("resizable", "alsoResize", {
            start: function() {
                var r = e(this).data("resizable"),
                    i = r.options,
                    s = function(t) {
                        e(t).each(function() {
                            var t = e(this);
                            t.data("resizable-alsoresize", {
                                width: parseInt(t.width(), 10),
                                height: parseInt(t.height(), 10),
                                left: parseInt(t.css("left"), 10),
                                top: parseInt(t.css("top"), 10)
                            })
                        })
                    };
                "object" != typeof i.alsoResize || i.alsoResize.parentNode ? s(i.alsoResize) : i.alsoResize.length ? (i.alsoResize = i.alsoResize[0], s(i.alsoResize)) : e.each(i.alsoResize, function(e) {
                    s(e)
                })
            },
            resize: function(t, n) {
                var r = e(this).data("resizable"),
                    i = r.options,
                    s = r.originalSize,
                    o = r.originalPosition,
                    u = {
                        height: r.size.height - s.height || 0,
                        width: r.size.width - s.width || 0,
                        top: r.position.top - o.top || 0,
                        left: r.position.left - o.left || 0
                    },
                    a = function(t, r) {
                        e(t).each(function() {
                            var t = e(this),
                                i = e(this).data("resizable-alsoresize"),
                                s = {},
                                o = r && r.length ? r : t.parents(n.originalElement[0]).length ? ["width", "height"] : ["width", "height", "top", "left"];
                            e.each(o, function(e, t) {
                                var n = (i[t] || 0) + (u[t] || 0);
                                n && n >= 0 && (s[t] = n || null)
                            }), t.css(s)
                        })
                    };
                "object" != typeof i.alsoResize || i.alsoResize.nodeType ? a(i.alsoResize) : e.each(i.alsoResize, function(e, t) {
                    a(e, t)
                })
            },
            stop: function() {
                e(this).removeData("resizable-alsoresize")
            }
        }), e.ui.plugin.add("resizable", "animate", {
            stop: function(t) {
                var r = e(this).data("resizable"),
                    i = r.options,
                    s = r._proportionallyResizeElements,
                    o = s.length && /textarea/i.test(s[0].nodeName),
                    u = o && e.ui.hasScroll(s[0], "left") ? 0 : r.sizeDiff.height,
                    a = o ? 0 : r.sizeDiff.width,
                    f = {
                        width: r.size.width - a,
                        height: r.size.height - u
                    },
                    l = parseInt(r.element.css("left"), 10) + (r.position.left - r.originalPosition.left) || null,
                    c = parseInt(r.element.css("top"), 10) + (r.position.top - r.originalPosition.top) || null;
                r.element.animate(e.extend(f, c && l ? {
                    top: c,
                    left: l
                } : {}), {
                    duration: i.animateDuration,
                    easing: i.animateEasing,
                    step: function() {
                        var n = {
                            width: parseInt(r.element.css("width"), 10),
                            height: parseInt(r.element.css("height"), 10),
                            top: parseInt(r.element.css("top"), 10),
                            left: parseInt(r.element.css("left"), 10)
                        };
                        s && s.length && e(s[0]).css({
                            width: n.width,
                            height: n.height
                        }), r._updateCache(n), r._propagate("resize", t)
                    }
                })
            }
        }), e.ui.plugin.add("resizable", "containment", {
            start: function() {
                var i = e(this).data("resizable"),
                    s = i.options,
                    o = i.element,
                    u = s.containment,
                    a = u instanceof e ? u.get(0) : /parent/.test(u) ? o.parent().get(0) : u;
                if (a)
                    if (i.containerElement = e(a), /document/.test(u) || u == document) i.containerOffset = {
                        left: 0,
                        top: 0
                    }, i.containerPosition = {
                        left: 0,
                        top: 0
                    }, i.parentData = {
                        element: e(document),
                        left: 0,
                        top: 0,
                        width: e(document).width(),
                        height: e(document).height() || document.body.parentNode.scrollHeight
                    };
                    else {
                        var f = e(a),
                            l = [];
                        e(["Top", "Right", "Left", "Bottom"]).each(function(e, t) {
                            l[e] = n(f.css("padding" + t))
                        }), i.containerOffset = f.offset(), i.containerPosition = f.position(), i.containerSize = {
                            height: f.innerHeight() - l[3],
                            width: f.innerWidth() - l[1]
                        };
                        var c = i.containerOffset,
                            h = i.containerSize.height,
                            p = i.containerSize.width,
                            d = e.ui.hasScroll(a, "left") ? a.scrollWidth : p,
                            v = e.ui.hasScroll(a) ? a.scrollHeight : h;
                        i.parentData = {
                            element: a,
                            left: c.left,
                            top: c.top,
                            width: d,
                            height: v
                        }
                    }
            },
            resize: function(t) {
                var r = e(this).data("resizable"),
                    i = r.options,
                    o = (r.containerSize, r.containerOffset),
                    a = (r.size, r.position),
                    f = r._aspectRatio || t.shiftKey,
                    l = {
                        top: 0,
                        left: 0
                    },
                    c = r.containerElement;
                c[0] != document && /static/.test(c.css("position")) && (l = o), a.left < (r._helper ? o.left : 0) && (r.size.width = r.size.width + (r._helper ? r.position.left - o.left : r.position.left - l.left), f && (r.size.height = r.size.width / r.aspectRatio), r.position.left = i.helper ? o.left : 0), a.top < (r._helper ? o.top : 0) && (r.size.height = r.size.height + (r._helper ? r.position.top - o.top : r.position.top), f && (r.size.width = r.size.height * r.aspectRatio), r.position.top = r._helper ? o.top : 0), r.offset.left = r.parentData.left + r.position.left, r.offset.top = r.parentData.top + r.position.top;
                var h = Math.abs((r._helper ? r.offset.left - l.left : r.offset.left - l.left) + r.sizeDiff.width),
                    p = Math.abs((r._helper ? r.offset.top - l.top : r.offset.top - o.top) + r.sizeDiff.height),
                    d = r.containerElement.get(0) == r.element.parent().get(0),
                    v = /relative|absolute/.test(r.containerElement.css("position"));
                d && v && (h -= r.parentData.left), h + r.size.width >= r.parentData.width && (r.size.width = r.parentData.width - h, f && (r.size.height = r.size.width / r.aspectRatio)), p + r.size.height >= r.parentData.height && (r.size.height = r.parentData.height - p, f && (r.size.width = r.size.height * r.aspectRatio))
            },
            stop: function() {
                var r = e(this).data("resizable"),
                    i = r.options,
                    o = (r.position, r.containerOffset),
                    u = r.containerPosition,
                    a = r.containerElement,
                    f = e(r.helper),
                    l = f.offset(),
                    c = f.outerWidth() - r.sizeDiff.width,
                    h = f.outerHeight() - r.sizeDiff.height;
                r._helper && !i.animate && /relative/.test(a.css("position")) && e(this).css({
                    left: l.left - u.left - o.left,
                    width: c,
                    height: h
                }), r._helper && !i.animate && /static/.test(a.css("position")) && e(this).css({
                    left: l.left - u.left - o.left,
                    width: c,
                    height: h
                })
            }
        }), e.ui.plugin.add("resizable", "ghost", {
            start: function() {
                var r = e(this).data("resizable"),
                    i = r.options,
                    s = r.size;
                r.ghost = r.originalElement.clone(), r.ghost.css({
                    opacity: .25,
                    display: "block",
                    position: "relative",
                    height: s.height,
                    width: s.width,
                    margin: 0,
                    left: 0,
                    top: 0
                }).addClass("ui-resizable-ghost").addClass("string" == typeof i.ghost ? i.ghost : ""), r.ghost.appendTo(r.helper)
            },
            resize: function() {
                {
                    var r = e(this).data("resizable");
                    r.options
                }
                r.ghost && r.ghost.css({
                    position: "relative",
                    height: r.size.height,
                    width: r.size.width
                })
            },
            stop: function() {
                {
                    var r = e(this).data("resizable");
                    r.options
                }
                r.ghost && r.helper && r.helper.get(0).removeChild(r.ghost.get(0))
            }
        }), e.ui.plugin.add("resizable", "grid", {
            resize: function(t) {
                {
                    var r = e(this).data("resizable"),
                        i = r.options,
                        s = r.size,
                        o = r.originalSize,
                        u = r.originalPosition,
                        a = r.axis;
                    i._aspectRatio || t.shiftKey
                }
                i.grid = "number" == typeof i.grid ? [i.grid, i.grid] : i.grid;
                var l = Math.round((s.width - o.width) / (i.grid[0] || 1)) * (i.grid[0] || 1),
                    c = Math.round((s.height - o.height) / (i.grid[1] || 1)) * (i.grid[1] || 1);
                /^(se|s|e)$/.test(a) ? (r.size.width = o.width + l, r.size.height = o.height + c) : /^(ne)$/.test(a) ? (r.size.width = o.width + l, r.size.height = o.height + c, r.position.top = u.top - c) : /^(sw)$/.test(a) ? (r.size.width = o.width + l, r.size.height = o.height + c, r.position.left = u.left - l) : (r.size.width = o.width + l, r.size.height = o.height + c, r.position.top = u.top - c, r.position.left = u.left - l)
            }
        });
        var n = function(e) {
                return parseInt(e, 10) || 0
            },
            r = function(e) {
                return !isNaN(parseInt(e, 10))
            }
    }(jQuery),
    function(e) {
        e.widget("ui.selectable", e.ui.mouse, {
            version: "1.9.2",
            options: {
                appendTo: "body",
                autoRefresh: !0,
                distance: 0,
                filter: "*",
                tolerance: "touch"
            },
            _create: function() {
                var t = this;
                this.element.addClass("ui-selectable"), this.dragged = !1;
                var n;
                this.refresh = function() {
                    n = e(t.options.filter, t.element[0]), n.addClass("ui-selectee"), n.each(function() {
                        var t = e(this),
                            n = t.offset();
                        e.data(this, "selectable-item", {
                            element: this,
                            $element: t,
                            left: n.left,
                            top: n.top,
                            right: n.left + t.outerWidth(),
                            bottom: n.top + t.outerHeight(),
                            startselected: !1,
                            selected: t.hasClass("ui-selected"),
                            selecting: t.hasClass("ui-selecting"),
                            unselecting: t.hasClass("ui-unselecting")
                        })
                    })
                }, this.refresh(), this.selectees = n.addClass("ui-selectee"), this._mouseInit(), this.helper = e("<div class='ui-selectable-helper'></div>")
            },
            _destroy: function() {
                this.selectees.removeClass("ui-selectee").removeData("selectable-item"), this.element.removeClass("ui-selectable ui-selectable-disabled"), this._mouseDestroy()
            },
            _mouseStart: function(t) {
                var n = this;
                if (this.opos = [t.pageX, t.pageY], !this.options.disabled) {
                    var r = this.options;
                    this.selectees = e(r.filter, this.element[0]), this._trigger("start", t), e(r.appendTo).append(this.helper), this.helper.css({
                        left: t.clientX,
                        top: t.clientY,
                        width: 0,
                        height: 0
                    }), r.autoRefresh && this.refresh(), this.selectees.filter(".ui-selected").each(function() {
                        var r = e.data(this, "selectable-item");
                        r.startselected = !0, !t.metaKey && !t.ctrlKey && (r.$element.removeClass("ui-selected"), r.selected = !1, r.$element.addClass("ui-unselecting"), r.unselecting = !0, n._trigger("unselecting", t, {
                            unselecting: r.element
                        }))
                    }), e(t.target).parents().andSelf().each(function() {
                        var r = e.data(this, "selectable-item");
                        if (r) {
                            var i = !t.metaKey && !t.ctrlKey || !r.$element.hasClass("ui-selected");
                            return r.$element.removeClass(i ? "ui-unselecting" : "ui-selected").addClass(i ? "ui-selecting" : "ui-unselecting"), r.unselecting = !i, r.selecting = i, r.selected = i, i ? n._trigger("selecting", t, {
                                selecting: r.element
                            }) : n._trigger("unselecting", t, {
                                unselecting: r.element
                            }), !1
                        }
                    })
                }
            },
            _mouseDrag: function(t) {
                var n = this;
                if (this.dragged = !0, !this.options.disabled) {
                    var r = this.options,
                        i = this.opos[0],
                        s = this.opos[1],
                        o = t.pageX,
                        u = t.pageY;
                    if (i > o) {
                        var a = o;
                        o = i, i = a
                    }
                    if (s > u) {
                        var a = u;
                        u = s, s = a
                    }
                    return this.helper.css({
                        left: i,
                        top: s,
                        width: o - i,
                        height: u - s
                    }), this.selectees.each(function() {
                        var a = e.data(this, "selectable-item");
                        if (a && a.element != n.element[0]) {
                            var f = !1;
                            "touch" == r.tolerance ? f = !(a.left > o || a.right < i || a.top > u || a.bottom < s) : "fit" == r.tolerance && (f = a.left > i && a.right < o && a.top > s && a.bottom < u), f ? (a.selected && (a.$element.removeClass("ui-selected"), a.selected = !1), a.unselecting && (a.$element.removeClass("ui-unselecting"), a.unselecting = !1), a.selecting || (a.$element.addClass("ui-selecting"), a.selecting = !0, n._trigger("selecting", t, {
                                selecting: a.element
                            }))) : (a.selecting && ((t.metaKey || t.ctrlKey) && a.startselected ? (a.$element.removeClass("ui-selecting"), a.selecting = !1, a.$element.addClass("ui-selected"), a.selected = !0) : (a.$element.removeClass("ui-selecting"), a.selecting = !1, a.startselected && (a.$element.addClass("ui-unselecting"), a.unselecting = !0), n._trigger("unselecting", t, {
                                unselecting: a.element
                            }))), a.selected && !t.metaKey && !t.ctrlKey && !a.startselected && (a.$element.removeClass("ui-selected"), a.selected = !1, a.$element.addClass("ui-unselecting"), a.unselecting = !0, n._trigger("unselecting", t, {
                                unselecting: a.element
                            })))
                        }
                    }), !1
                }
            },
            _mouseStop: function(t) {
                var n = this;
                this.dragged = !1;
                this.options;
                return e(".ui-unselecting", this.element[0]).each(function() {
                    var r = e.data(this, "selectable-item");
                    r.$element.removeClass("ui-unselecting"), r.unselecting = !1, r.startselected = !1, n._trigger("unselected", t, {
                        unselected: r.element
                    })
                }), e(".ui-selecting", this.element[0]).each(function() {
                    var r = e.data(this, "selectable-item");
                    r.$element.removeClass("ui-selecting").addClass("ui-selected"), r.selecting = !1, r.selected = !0, r.startselected = !0, n._trigger("selected", t, {
                        selected: r.element
                    })
                }), this._trigger("stop", t), this.helper.remove(), !1
            }
        })
    }(jQuery),
    function(e) {
        var n = 5;
        e.widget("ui.slider", e.ui.mouse, {
            version: "1.9.2",
            widgetEventPrefix: "slide",
            options: {
                animate: !1,
                distance: 0,
                max: 100,
                min: 0,
                orientation: "horizontal",
                range: !1,
                step: 1,
                value: 0,
                values: null
            },
            _create: function() {
                var t, r, i = this.options,
                    s = this.element.find(".ui-slider-handle").addClass("ui-state-default ui-corner-all"),
                    o = "<a class='ui-slider-handle ui-state-default ui-corner-all' href='#'></a>",
                    u = [];
                for (this._keySliding = !1, this._mouseSliding = !1, this._animateOff = !0, this._handleIndex = null, this._detectOrientation(), this._mouseInit(), this.element.addClass("ui-slider ui-slider-" + this.orientation + " ui-widget ui-widget-content ui-corner-all" + (i.disabled ? " ui-slider-disabled ui-disabled" : "")), this.range = e([]), i.range && (i.range === !0 && (i.values || (i.values = [this._valueMin(), this._valueMin()]), i.values.length && 2 !== i.values.length && (i.values = [i.values[0], i.values[0]])), this.range = e("<div></div>").appendTo(this.element).addClass("ui-slider-range ui-widget-header" + ("min" === i.range || "max" === i.range ? " ui-slider-range-" + i.range : ""))), r = i.values && i.values.length || 1, t = s.length; r > t; t++) u.push(o);
                this.handles = s.add(e(u.join("")).appendTo(this.element)), this.handle = this.handles.eq(0), this.handles.add(this.range).filter("a").click(function(e) {
                    e.preventDefault()
                }).mouseenter(function() {
                    i.disabled || e(this).addClass("ui-state-hover")
                }).mouseleave(function() {
                    e(this).removeClass("ui-state-hover")
                }).focus(function() {
                    i.disabled ? e(this).blur() : (e(".ui-slider .ui-state-focus").removeClass("ui-state-focus"), e(this).addClass("ui-state-focus"))
                }).blur(function() {
                    e(this).removeClass("ui-state-focus")
                }), this.handles.each(function(t) {
                    e(this).data("ui-slider-handle-index", t)
                }), this._on(this.handles, {
                    keydown: function(t) {
                        var r, i, s, o, u = e(t.target).data("ui-slider-handle-index");
                        switch (t.keyCode) {
                            case e.ui.keyCode.HOME:
                            case e.ui.keyCode.END:
                            case e.ui.keyCode.PAGE_UP:
                            case e.ui.keyCode.PAGE_DOWN:
                            case e.ui.keyCode.UP:
                            case e.ui.keyCode.RIGHT:
                            case e.ui.keyCode.DOWN:
                            case e.ui.keyCode.LEFT:
                                if (t.preventDefault(), !this._keySliding && (this._keySliding = !0, e(t.target).addClass("ui-state-active"), r = this._start(t, u), r === !1)) return
                        }
                        switch (o = this.options.step, i = s = this.options.values && this.options.values.length ? this.values(u) : this.value(), t.keyCode) {
                            case e.ui.keyCode.HOME:
                                s = this._valueMin();
                                break;
                            case e.ui.keyCode.END:
                                s = this._valueMax();
                                break;
                            case e.ui.keyCode.PAGE_UP:
                                s = this._trimAlignValue(i + (this._valueMax() - this._valueMin()) / n);
                                break;
                            case e.ui.keyCode.PAGE_DOWN:
                                s = this._trimAlignValue(i - (this._valueMax() - this._valueMin()) / n);
                                break;
                            case e.ui.keyCode.UP:
                            case e.ui.keyCode.RIGHT:
                                if (i === this._valueMax()) return;
                                s = this._trimAlignValue(i + o);
                                break;
                            case e.ui.keyCode.DOWN:
                            case e.ui.keyCode.LEFT:
                                if (i === this._valueMin()) return;
                                s = this._trimAlignValue(i - o)
                        }
                        this._slide(t, u, s)
                    },
                    keyup: function(t) {
                        var n = e(t.target).data("ui-slider-handle-index");
                        this._keySliding && (this._keySliding = !1, this._stop(t, n), this._change(t, n), e(t.target).removeClass("ui-state-active"))
                    }
                }), this._refreshValue(), this._animateOff = !1
            },
            _destroy: function() {
                this.handles.remove(), this.range.remove(), this.element.removeClass("ui-slider ui-slider-horizontal ui-slider-vertical ui-slider-disabled ui-widget ui-widget-content ui-corner-all"), this._mouseDestroy()
            },
            _mouseCapture: function(t) {
                var n, r, i, s, o, u, a, f, l = this,
                    c = this.options;
                return c.disabled ? !1 : (this.elementSize = {
                    width: this.element.outerWidth(),
                    height: this.element.outerHeight()
                }, this.elementOffset = this.element.offset(), n = {
                    x: t.pageX,
                    y: t.pageY
                }, r = this._normValueFromMouse(n), i = this._valueMax() - this._valueMin() + 1, this.handles.each(function(t) {
                    var n = Math.abs(r - l.values(t));
                    i > n && (i = n, s = e(this), o = t)
                }), c.range === !0 && this.values(1) === c.min && (o += 1, s = e(this.handles[o])), u = this._start(t, o), u === !1 ? !1 : (this._mouseSliding = !0, this._handleIndex = o, s.addClass("ui-state-active").focus(), a = s.offset(), f = !e(t.target).parents().andSelf().is(".ui-slider-handle"), this._clickOffset = f ? {
                    left: 0,
                    top: 0
                } : {
                    left: t.pageX - a.left - s.width() / 2,
                    top: t.pageY - a.top - s.height() / 2 - (parseInt(s.css("borderTopWidth"), 10) || 0) - (parseInt(s.css("borderBottomWidth"), 10) || 0) + (parseInt(s.css("marginTop"), 10) || 0)
                }, this.handles.hasClass("ui-state-hover") || this._slide(t, o, r), this._animateOff = !0, !0))
            },
            _mouseStart: function() {
                return !0
            },
            _mouseDrag: function(e) {
                var t = {
                        x: e.pageX,
                        y: e.pageY
                    },
                    n = this._normValueFromMouse(t);
                return this._slide(e, this._handleIndex, n), !1
            },
            _mouseStop: function(e) {
                return this.handles.removeClass("ui-state-active"), this._mouseSliding = !1, this._stop(e, this._handleIndex), this._change(e, this._handleIndex), this._handleIndex = null, this._clickOffset = null, this._animateOff = !1, !1
            },
            _detectOrientation: function() {
                this.orientation = "vertical" === this.options.orientation ? "vertical" : "horizontal"
            },
            _normValueFromMouse: function(e) {
                var t, n, r, i, s;
                return "horizontal" === this.orientation ? (t = this.elementSize.width, n = e.x - this.elementOffset.left - (this._clickOffset ? this._clickOffset.left : 0)) : (t = this.elementSize.height, n = e.y - this.elementOffset.top - (this._clickOffset ? this._clickOffset.top : 0)), r = n / t, r > 1 && (r = 1), 0 > r && (r = 0), "vertical" === this.orientation && (r = 1 - r), i = this._valueMax() - this._valueMin(), s = this._valueMin() + r * i, this._trimAlignValue(s)
            },
            _start: function(e, t) {
                var n = {
                    handle: this.handles[t],
                    value: this.value()
                };
                return this.options.values && this.options.values.length && (n.value = this.values(t), n.values = this.values()), this._trigger("start", e, n)
            },
            _slide: function(e, t, n) {
                var r, i, s;
                this.options.values && this.options.values.length ? (r = this.values(t ? 0 : 1), 2 === this.options.values.length && this.options.range === !0 && (0 === t && n > r || 1 === t && r > n) && (n = r), n !== this.values(t) && (i = this.values(), i[t] = n, s = this._trigger("slide", e, {
                    handle: this.handles[t],
                    value: n,
                    values: i
                }), r = this.values(t ? 0 : 1), s !== !1 && this.values(t, n, !0))) : n !== this.value() && (s = this._trigger("slide", e, {
                    handle: this.handles[t],
                    value: n
                }), s !== !1 && this.value(n))
            },
            _stop: function(e, t) {
                var n = {
                    handle: this.handles[t],
                    value: this.value()
                };
                this.options.values && this.options.values.length && (n.value = this.values(t), n.values = this.values()), this._trigger("stop", e, n)
            },
            _change: function(e, t) {
                if (!this._keySliding && !this._mouseSliding) {
                    var n = {
                        handle: this.handles[t],
                        value: this.value()
                    };
                    this.options.values && this.options.values.length && (n.value = this.values(t), n.values = this.values()), this._trigger("change", e, n)
                }
            },
            value: function(e) {
                return arguments.length ? (this.options.value = this._trimAlignValue(e), this._refreshValue(), this._change(null, 0), void 0) : this._value()
            },
            values: function(t, n) {
                var r, i, s;
                if (arguments.length > 1) return this.options.values[t] = this._trimAlignValue(n), this._refreshValue(), this._change(null, t), void 0;
                if (!arguments.length) return this._values();
                if (!e.isArray(arguments[0])) return this.options.values && this.options.values.length ? this._values(t) : this.value();
                for (r = this.options.values, i = arguments[0], s = 0; s < r.length; s += 1) r[s] = this._trimAlignValue(i[s]), this._change(null, s);
                this._refreshValue()
            },
            _setOption: function(t, n) {
                var r, i = 0;
                switch (e.isArray(this.options.values) && (i = this.options.values.length), e.Widget.prototype._setOption.apply(this, arguments), t) {
                    case "disabled":
                        n ? (this.handles.filter(".ui-state-focus").blur(), this.handles.removeClass("ui-state-hover"), this.handles.prop("disabled", !0), this.element.addClass("ui-disabled")) : (this.handles.prop("disabled", !1), this.element.removeClass("ui-disabled"));
                        break;
                    case "orientation":
                        this._detectOrientation(), this.element.removeClass("ui-slider-horizontal ui-slider-vertical").addClass("ui-slider-" + this.orientation), this._refreshValue();
                        break;
                    case "value":
                        this._animateOff = !0, this._refreshValue(), this._change(null, 0), this._animateOff = !1;
                        break;
                    case "values":
                        for (this._animateOff = !0, this._refreshValue(), r = 0; i > r; r += 1) this._change(null, r);
                        this._animateOff = !1;
                        break;
                    case "min":
                    case "max":
                        this._animateOff = !0, this._refreshValue(), this._animateOff = !1
                }
            },
            _value: function() {
                var e = this.options.value;
                return e = this._trimAlignValue(e)
            },
            _values: function(e) {
                var t, n, r;
                if (arguments.length) return t = this.options.values[e], t = this._trimAlignValue(t);
                for (n = this.options.values.slice(), r = 0; r < n.length; r += 1) n[r] = this._trimAlignValue(n[r]);
                return n
            },
            _trimAlignValue: function(e) {
                if (e <= this._valueMin()) return this._valueMin();
                if (e >= this._valueMax()) return this._valueMax();
                var t = this.options.step > 0 ? this.options.step : 1,
                    n = (e - this._valueMin()) % t,
                    r = e - n;
                return 2 * Math.abs(n) >= t && (r += n > 0 ? t : -t), parseFloat(r.toFixed(5))
            },
            _valueMin: function() {
                return this.options.min
            },
            _valueMax: function() {
                return this.options.max
            },
            _refreshValue: function() {
                var t, n, r, i, s, o = this.options.range,
                    u = this.options,
                    a = this,
                    f = this._animateOff ? !1 : u.animate,
                    l = {};
                this.options.values && this.options.values.length ? this.handles.each(function(r) {
                    n = (a.values(r) - a._valueMin()) / (a._valueMax() - a._valueMin()) * 100, l["horizontal" === a.orientation ? "left" : "bottom"] = n + "%", e(this).stop(1, 1)[f ? "animate" : "css"](l, u.animate), a.options.range === !0 && ("horizontal" === a.orientation ? (0 === r && a.range.stop(1, 1)[f ? "animate" : "css"]({
                        left: n + "%"
                    }, u.animate), 1 === r && a.range[f ? "animate" : "css"]({
                        width: n - t + "%"
                    }, {
                        queue: !1,
                        duration: u.animate
                    })) : (0 === r && a.range.stop(1, 1)[f ? "animate" : "css"]({
                        bottom: n + "%"
                    }, u.animate), 1 === r && a.range[f ? "animate" : "css"]({
                        height: n - t + "%"
                    }, {
                        queue: !1,
                        duration: u.animate
                    }))), t = n
                }) : (r = this.value(), i = this._valueMin(), s = this._valueMax(), n = s !== i ? (r - i) / (s - i) * 100 : 0, l["horizontal" === this.orientation ? "left" : "bottom"] = n + "%", this.handle.stop(1, 1)[f ? "animate" : "css"](l, u.animate), "min" === o && "horizontal" === this.orientation && this.range.stop(1, 1)[f ? "animate" : "css"]({
                    width: n + "%"
                }, u.animate), "max" === o && "horizontal" === this.orientation && this.range[f ? "animate" : "css"]({
                    width: 100 - n + "%"
                }, {
                    queue: !1,
                    duration: u.animate
                }), "min" === o && "vertical" === this.orientation && this.range.stop(1, 1)[f ? "animate" : "css"]({
                    height: n + "%"
                }, u.animate), "max" === o && "vertical" === this.orientation && this.range[f ? "animate" : "css"]({
                    height: 100 - n + "%"
                }, {
                    queue: !1,
                    duration: u.animate
                }))
            }
        })
    }(jQuery),
    function(e) {
        e.widget("ui.sortable", e.ui.mouse, {
            version: "1.9.2",
            widgetEventPrefix: "sort",
            ready: !1,
            options: {
                appendTo: "parent",
                axis: !1,
                connectWith: !1,
                containment: !1,
                cursor: "auto",
                cursorAt: !1,
                dropOnEmpty: !0,
                forcePlaceholderSize: !1,
                forceHelperSize: !1,
                grid: !1,
                handle: !1,
                helper: "original",
                items: "> *",
                opacity: !1,
                placeholder: !1,
                revert: !1,
                scroll: !0,
                scrollSensitivity: 20,
                scrollSpeed: 20,
                scope: "default",
                tolerance: "intersect",
                zIndex: 1e3
            },
            _create: function() {
                var e = this.options;
                this.containerCache = {}, this.element.addClass("ui-sortable"), this.refresh(), this.floating = this.items.length ? "x" === e.axis || /left|right/.test(this.items[0].item.css("float")) || /inline|table-cell/.test(this.items[0].item.css("display")) : !1, this.offset = this.element.offset(), this._mouseInit(), this.ready = !0
            },
            _destroy: function() {
                this.element.removeClass("ui-sortable ui-sortable-disabled"), this._mouseDestroy();
                for (var e = this.items.length - 1; e >= 0; e--) this.items[e].item.removeData(this.widgetName + "-item");
                return this
            },
            _setOption: function(t, n) {
                "disabled" === t ? (this.options[t] = n, this.widget().toggleClass("ui-sortable-disabled", !!n)) : e.Widget.prototype._setOption.apply(this, arguments)
            },
            _mouseCapture: function(t, n) {
                var r = this;
                if (this.reverting) return !1;
                if (this.options.disabled || "static" == this.options.type) return !1;
                this._refreshItems(t); {
                    var i = null;
                    e(t.target).parents().each(function() {
                        return e.data(this, r.widgetName + "-item") == r ? (i = e(this), !1) : void 0
                    })
                }
                if (e.data(t.target, r.widgetName + "-item") == r && (i = e(t.target)), !i) return !1;
                if (this.options.handle && !n) {
                    var o = !1;
                    if (e(this.options.handle, i).find("*").andSelf().each(function() {
                            this == t.target && (o = !0)
                        }), !o) return !1
                }
                return this.currentItem = i, this._removeCurrentsFromItems(), !0
            },
            _mouseStart: function(t, n, r) {
                var i = this.options;
                if (this.currentContainer = this, this.refreshPositions(), this.helper = this._createHelper(t), this._cacheHelperProportions(), this._cacheMargins(), this.scrollParent = this.helper.scrollParent(), this.offset = this.currentItem.offset(), this.offset = {
                        top: this.offset.top - this.margins.top,
                        left: this.offset.left - this.margins.left
                    }, e.extend(this.offset, {
                        click: {
                            left: t.pageX - this.offset.left,
                            top: t.pageY - this.offset.top
                        },
                        parent: this._getParentOffset(),
                        relative: this._getRelativeOffset()
                    }), this.helper.css("position", "absolute"), this.cssPosition = this.helper.css("position"), this.originalPosition = this._generatePosition(t), this.originalPageX = t.pageX, this.originalPageY = t.pageY, i.cursorAt && this._adjustOffsetFromHelper(i.cursorAt), this.domPosition = {
                        prev: this.currentItem.prev()[0],
                        parent: this.currentItem.parent()[0]
                    }, this.helper[0] != this.currentItem[0] && this.currentItem.hide(), this._createPlaceholder(), i.containment && this._setContainment(), i.cursor && (e("body").css("cursor") && (this._storedCursor = e("body").css("cursor")), e("body").css("cursor", i.cursor)), i.opacity && (this.helper.css("opacity") && (this._storedOpacity = this.helper.css("opacity")), this.helper.css("opacity", i.opacity)), i.zIndex && (this.helper.css("zIndex") && (this._storedZIndex = this.helper.css("zIndex")), this.helper.css("zIndex", i.zIndex)), this.scrollParent[0] != document && "HTML" != this.scrollParent[0].tagName && (this.overflowOffset = this.scrollParent.offset()), this._trigger("start", t, this._uiHash()), this._preserveHelperProportions || this._cacheHelperProportions(), !r)
                    for (var s = this.containers.length - 1; s >= 0; s--) this.containers[s]._trigger("activate", t, this._uiHash(this));
                return e.ui.ddmanager && (e.ui.ddmanager.current = this), e.ui.ddmanager && !i.dropBehaviour && e.ui.ddmanager.prepareOffsets(this, t), this.dragging = !0, this.helper.addClass("ui-sortable-helper"), this._mouseDrag(t), !0
            },
            _mouseDrag: function(t) {
                if (this.position = this._generatePosition(t), this.positionAbs = this._convertPositionTo("absolute"), this.lastPositionAbs || (this.lastPositionAbs = this.positionAbs), this.options.scroll) {
                    var n = this.options,
                        r = !1;
                    this.scrollParent[0] != document && "HTML" != this.scrollParent[0].tagName ? (this.overflowOffset.top + this.scrollParent[0].offsetHeight - t.pageY < n.scrollSensitivity ? this.scrollParent[0].scrollTop = r = this.scrollParent[0].scrollTop + n.scrollSpeed : t.pageY - this.overflowOffset.top < n.scrollSensitivity && (this.scrollParent[0].scrollTop = r = this.scrollParent[0].scrollTop - n.scrollSpeed), this.overflowOffset.left + this.scrollParent[0].offsetWidth - t.pageX < n.scrollSensitivity ? this.scrollParent[0].scrollLeft = r = this.scrollParent[0].scrollLeft + n.scrollSpeed : t.pageX - this.overflowOffset.left < n.scrollSensitivity && (this.scrollParent[0].scrollLeft = r = this.scrollParent[0].scrollLeft - n.scrollSpeed)) : (t.pageY - e(document).scrollTop() < n.scrollSensitivity ? r = e(document).scrollTop(e(document).scrollTop() - n.scrollSpeed) : e(window).height() - (t.pageY - e(document).scrollTop()) < n.scrollSensitivity && (r = e(document).scrollTop(e(document).scrollTop() + n.scrollSpeed)), t.pageX - e(document).scrollLeft() < n.scrollSensitivity ? r = e(document).scrollLeft(e(document).scrollLeft() - n.scrollSpeed) : e(window).width() - (t.pageX - e(document).scrollLeft()) < n.scrollSensitivity && (r = e(document).scrollLeft(e(document).scrollLeft() + n.scrollSpeed))), r !== !1 && e.ui.ddmanager && !n.dropBehaviour && e.ui.ddmanager.prepareOffsets(this, t)
                }
                this.positionAbs = this._convertPositionTo("absolute"), this.options.axis && "y" == this.options.axis || (this.helper[0].style.left = this.position.left + "px"), this.options.axis && "x" == this.options.axis || (this.helper[0].style.top = this.position.top + "px");
                for (var i = this.items.length - 1; i >= 0; i--) {
                    var s = this.items[i],
                        o = s.item[0],
                        u = this._intersectsWithPointer(s);
                    if (u && s.instance === this.currentContainer && o != this.currentItem[0] && this.placeholder[1 == u ? "next" : "prev"]()[0] != o && !e.contains(this.placeholder[0], o) && ("semi-dynamic" == this.options.type ? !e.contains(this.element[0], o) : !0)) {
                        if (this.direction = 1 == u ? "down" : "up", "pointer" != this.options.tolerance && !this._intersectsWithSides(s)) break;
                        this._rearrange(t, s), this._trigger("change", t, this._uiHash());
                        break
                    }
                }
                return this._contactContainers(t), e.ui.ddmanager && e.ui.ddmanager.drag(this, t), this._trigger("sort", t, this._uiHash()), this.lastPositionAbs = this.positionAbs, !1
            },
            _mouseStop: function(t, n) {
                if (t) {
                    if (e.ui.ddmanager && !this.options.dropBehaviour && e.ui.ddmanager.drop(this, t), this.options.revert) {
                        var r = this,
                            i = this.placeholder.offset();
                        this.reverting = !0, e(this.helper).animate({
                            left: i.left - this.offset.parent.left - this.margins.left + (this.offsetParent[0] == document.body ? 0 : this.offsetParent[0].scrollLeft),
                            top: i.top - this.offset.parent.top - this.margins.top + (this.offsetParent[0] == document.body ? 0 : this.offsetParent[0].scrollTop)
                        }, parseInt(this.options.revert, 10) || 500, function() {
                            r._clear(t)
                        })
                    } else this._clear(t, n);
                    return !1
                }
            },
            cancel: function() {
                if (this.dragging) {
                    this._mouseUp({
                        target: null
                    }), "original" == this.options.helper ? this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper") : this.currentItem.show();
                    for (var t = this.containers.length - 1; t >= 0; t--) this.containers[t]._trigger("deactivate", null, this._uiHash(this)), this.containers[t].containerCache.over && (this.containers[t]._trigger("out", null, this._uiHash(this)), this.containers[t].containerCache.over = 0)
                }
                return this.placeholder && (this.placeholder[0].parentNode && this.placeholder[0].parentNode.removeChild(this.placeholder[0]), "original" != this.options.helper && this.helper && this.helper[0].parentNode && this.helper.remove(), e.extend(this, {
                    helper: null,
                    dragging: !1,
                    reverting: !1,
                    _noFinalSort: null
                }), this.domPosition.prev ? e(this.domPosition.prev).after(this.currentItem) : e(this.domPosition.parent).prepend(this.currentItem)), this
            },
            serialize: function(t) {
                var n = this._getItemsAsjQuery(t && t.connected),
                    r = [];
                return t = t || {}, e(n).each(function() {
                    var n = (e(t.item || this).attr(t.attribute || "id") || "").match(t.expression || /(.+)[-=_](.+)/);
                    n && r.push((t.key || n[1] + "[]") + "=" + (t.key && t.expression ? n[1] : n[2]))
                }), !r.length && t.key && r.push(t.key + "="), r.join("&")
            },
            toArray: function(t) {
                var n = this._getItemsAsjQuery(t && t.connected),
                    r = [];
                return t = t || {}, n.each(function() {
                    r.push(e(t.item || this).attr(t.attribute || "id") || "")
                }), r
            },
            _intersectsWith: function(e) {
                var t = this.positionAbs.left,
                    n = t + this.helperProportions.width,
                    r = this.positionAbs.top,
                    i = r + this.helperProportions.height,
                    s = e.left,
                    o = s + e.width,
                    u = e.top,
                    a = u + e.height,
                    f = this.offset.click.top,
                    l = this.offset.click.left,
                    c = r + f > u && a > r + f && t + l > s && o > t + l;
                return "pointer" == this.options.tolerance || this.options.forcePointerForContainers || "pointer" != this.options.tolerance && this.helperProportions[this.floating ? "width" : "height"] > e[this.floating ? "width" : "height"] ? c : s < t + this.helperProportions.width / 2 && n - this.helperProportions.width / 2 < o && u < r + this.helperProportions.height / 2 && i - this.helperProportions.height / 2 < a
            },
            _intersectsWithPointer: function(t) {
                var n = "x" === this.options.axis || e.ui.isOverAxis(this.positionAbs.top + this.offset.click.top, t.top, t.height),
                    r = "y" === this.options.axis || e.ui.isOverAxis(this.positionAbs.left + this.offset.click.left, t.left, t.width),
                    i = n && r,
                    s = this._getDragVerticalDirection(),
                    o = this._getDragHorizontalDirection();
                return i ? this.floating ? o && "right" == o || "down" == s ? 2 : 1 : s && ("down" == s ? 2 : 1) : !1
            },
            _intersectsWithSides: function(t) {
                var n = e.ui.isOverAxis(this.positionAbs.top + this.offset.click.top, t.top + t.height / 2, t.height),
                    r = e.ui.isOverAxis(this.positionAbs.left + this.offset.click.left, t.left + t.width / 2, t.width),
                    i = this._getDragVerticalDirection(),
                    s = this._getDragHorizontalDirection();
                return this.floating && s ? "right" == s && r || "left" == s && !r : i && ("down" == i && n || "up" == i && !n)
            },
            _getDragVerticalDirection: function() {
                var e = this.positionAbs.top - this.lastPositionAbs.top;
                return 0 != e && (e > 0 ? "down" : "up")
            },
            _getDragHorizontalDirection: function() {
                var e = this.positionAbs.left - this.lastPositionAbs.left;
                return 0 != e && (e > 0 ? "right" : "left")
            },
            refresh: function(e) {
                return this._refreshItems(e), this.refreshPositions(), this
            },
            _connectWith: function() {
                var e = this.options;
                return e.connectWith.constructor == String ? [e.connectWith] : e.connectWith
            },
            _getItemsAsjQuery: function(t) {
                var n = [],
                    r = [],
                    i = this._connectWith();
                if (i && t)
                    for (var s = i.length - 1; s >= 0; s--)
                        for (var o = e(i[s]), u = o.length - 1; u >= 0; u--) {
                            var a = e.data(o[u], this.widgetName);
                            a && a != this && !a.options.disabled && r.push([e.isFunction(a.options.items) ? a.options.items.call(a.element) : e(a.options.items, a.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), a])
                        }
                r.push([e.isFunction(this.options.items) ? this.options.items.call(this.element, null, {
                    options: this.options,
                    item: this.currentItem
                }) : e(this.options.items, this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), this]);
                for (var s = r.length - 1; s >= 0; s--) r[s][0].each(function() {
                    n.push(this)
                });
                return e(n)
            },
            _removeCurrentsFromItems: function() {
                var t = this.currentItem.find(":data(" + this.widgetName + "-item)");
                this.items = e.grep(this.items, function(e) {
                    for (var n = 0; n < t.length; n++)
                        if (t[n] == e.item[0]) return !1;
                    return !0
                })
            },
            _refreshItems: function(t) {
                this.items = [], this.containers = [this];
                var n = this.items,
                    r = [
                        [e.isFunction(this.options.items) ? this.options.items.call(this.element[0], t, {
                            item: this.currentItem
                        }) : e(this.options.items, this.element), this]
                    ],
                    i = this._connectWith();
                if (i && this.ready)
                    for (var s = i.length - 1; s >= 0; s--)
                        for (var o = e(i[s]), u = o.length - 1; u >= 0; u--) {
                            var a = e.data(o[u], this.widgetName);
                            a && a != this && !a.options.disabled && (r.push([e.isFunction(a.options.items) ? a.options.items.call(a.element[0], t, {
                                item: this.currentItem
                            }) : e(a.options.items, a.element), a]), this.containers.push(a))
                        }
                for (var s = r.length - 1; s >= 0; s--)
                    for (var f = r[s][1], l = r[s][0], u = 0, c = l.length; c > u; u++) {
                        var h = e(l[u]);
                        h.data(this.widgetName + "-item", f), n.push({
                            item: h,
                            instance: f,
                            width: 0,
                            height: 0,
                            left: 0,
                            top: 0
                        })
                    }
            },
            refreshPositions: function(t) {
                this.offsetParent && this.helper && (this.offset.parent = this._getParentOffset());
                for (var n = this.items.length - 1; n >= 0; n--) {
                    var r = this.items[n];
                    if (r.instance == this.currentContainer || !this.currentContainer || r.item[0] == this.currentItem[0]) {
                        var i = this.options.toleranceElement ? e(this.options.toleranceElement, r.item) : r.item;
                        t || (r.width = i.outerWidth(), r.height = i.outerHeight());
                        var s = i.offset();
                        r.left = s.left, r.top = s.top
                    }
                }
                if (this.options.custom && this.options.custom.refreshContainers) this.options.custom.refreshContainers.call(this);
                else
                    for (var n = this.containers.length - 1; n >= 0; n--) {
                        var s = this.containers[n].element.offset();
                        this.containers[n].containerCache.left = s.left, this.containers[n].containerCache.top = s.top, this.containers[n].containerCache.width = this.containers[n].element.outerWidth(), this.containers[n].containerCache.height = this.containers[n].element.outerHeight()
                    }
                return this
            },
            _createPlaceholder: function(t) {
                t = t || this;
                var n = t.options;
                if (!n.placeholder || n.placeholder.constructor == String) {
                    var r = n.placeholder;
                    n.placeholder = {
                        element: function() {
                            var n = e(document.createElement(t.currentItem[0].nodeName)).addClass(r || t.currentItem[0].className + " ui-sortable-placeholder").removeClass("ui-sortable-helper")[0];
                            return r || (n.style.visibility = "hidden"), n
                        },
                        update: function(e, i) {
                            (!r || n.forcePlaceholderSize) && (i.height() || i.height(t.currentItem.innerHeight() - parseInt(t.currentItem.css("paddingTop") || 0, 10) - parseInt(t.currentItem.css("paddingBottom") || 0, 10)), i.width() || i.width(t.currentItem.innerWidth() - parseInt(t.currentItem.css("paddingLeft") || 0, 10) - parseInt(t.currentItem.css("paddingRight") || 0, 10)))
                        }
                    }
                }
                t.placeholder = e(n.placeholder.element.call(t.element, t.currentItem)), t.currentItem.after(t.placeholder), n.placeholder.update(t, t.placeholder)
            },
            _contactContainers: function(t) {
                for (var n = null, r = null, i = this.containers.length - 1; i >= 0; i--)
                    if (!e.contains(this.currentItem[0], this.containers[i].element[0]))
                        if (this._intersectsWith(this.containers[i].containerCache)) {
                            if (n && e.contains(this.containers[i].element[0], n.element[0])) continue;
                            n = this.containers[i], r = i
                        } else this.containers[i].containerCache.over && (this.containers[i]._trigger("out", t, this._uiHash(this)), this.containers[i].containerCache.over = 0);
                if (n)
                    if (1 === this.containers.length) this.containers[r]._trigger("over", t, this._uiHash(this)), this.containers[r].containerCache.over = 1;
                    else {
                        for (var s = 1e4, o = null, u = this.containers[r].floating ? "left" : "top", a = this.containers[r].floating ? "width" : "height", f = this.positionAbs[u] + this.offset.click[u], l = this.items.length - 1; l >= 0; l--)
                            if (e.contains(this.containers[r].element[0], this.items[l].item[0]) && this.items[l].item[0] != this.currentItem[0]) {
                                var c = this.items[l].item.offset()[u],
                                    h = !1;
                                Math.abs(c - f) > Math.abs(c + this.items[l][a] - f) && (h = !0, c += this.items[l][a]), Math.abs(c - f) < s && (s = Math.abs(c - f), o = this.items[l], this.direction = h ? "up" : "down")
                            }
                        if (!o && !this.options.dropOnEmpty) return;
                        this.currentContainer = this.containers[r], o ? this._rearrange(t, o, null, !0) : this._rearrange(t, null, this.containers[r].element, !0), this._trigger("change", t, this._uiHash()), this.containers[r]._trigger("change", t, this._uiHash(this)), this.options.placeholder.update(this.currentContainer, this.placeholder), this.containers[r]._trigger("over", t, this._uiHash(this)), this.containers[r].containerCache.over = 1
                    }
            },
            _createHelper: function(t) {
                var n = this.options,
                    r = e.isFunction(n.helper) ? e(n.helper.apply(this.element[0], [t, this.currentItem])) : "clone" == n.helper ? this.currentItem.clone() : this.currentItem;
                return r.parents("body").length || e("parent" != n.appendTo ? n.appendTo : this.currentItem[0].parentNode)[0].appendChild(r[0]), r[0] == this.currentItem[0] && (this._storedCSS = {
                    width: this.currentItem[0].style.width,
                    height: this.currentItem[0].style.height,
                    position: this.currentItem.css("position"),
                    top: this.currentItem.css("top"),
                    left: this.currentItem.css("left")
                }), ("" == r[0].style.width || n.forceHelperSize) && r.width(this.currentItem.width()), ("" == r[0].style.height || n.forceHelperSize) && r.height(this.currentItem.height()), r
            },
            _adjustOffsetFromHelper: function(t) {
                "string" == typeof t && (t = t.split(" ")), e.isArray(t) && (t = {
                    left: +t[0],
                    top: +t[1] || 0
                }), "left" in t && (this.offset.click.left = t.left + this.margins.left), "right" in t && (this.offset.click.left = this.helperProportions.width - t.right + this.margins.left), "top" in t && (this.offset.click.top = t.top + this.margins.top), "bottom" in t && (this.offset.click.top = this.helperProportions.height - t.bottom + this.margins.top)
            },
            _getParentOffset: function() {
                this.offsetParent = this.helper.offsetParent();
                var t = this.offsetParent.offset();
                return "absolute" == this.cssPosition && this.scrollParent[0] != document && e.contains(this.scrollParent[0], this.offsetParent[0]) && (t.left += this.scrollParent.scrollLeft(), t.top += this.scrollParent.scrollTop()), (this.offsetParent[0] == document.body || this.offsetParent[0].tagName && "html" == this.offsetParent[0].tagName.toLowerCase() && e.ui.ie) && (t = {
                    top: 0,
                    left: 0
                }), {
                    top: t.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                    left: t.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
                }
            },
            _getRelativeOffset: function() {
                if ("relative" == this.cssPosition) {
                    var e = this.currentItem.position();
                    return {
                        top: e.top - (parseInt(this.helper.css("top"), 10) || 0) + this.scrollParent.scrollTop(),
                        left: e.left - (parseInt(this.helper.css("left"), 10) || 0) + this.scrollParent.scrollLeft()
                    }
                }
                return {
                    top: 0,
                    left: 0
                }
            },
            _cacheMargins: function() {
                this.margins = {
                    left: parseInt(this.currentItem.css("marginLeft"), 10) || 0,
                    top: parseInt(this.currentItem.css("marginTop"), 10) || 0
                }
            },
            _cacheHelperProportions: function() {
                this.helperProportions = {
                    width: this.helper.outerWidth(),
                    height: this.helper.outerHeight()
                }
            },
            _setContainment: function() {
                var t = this.options;
                if ("parent" == t.containment && (t.containment = this.helper[0].parentNode), ("document" == t.containment || "window" == t.containment) && (this.containment = [0 - this.offset.relative.left - this.offset.parent.left, 0 - this.offset.relative.top - this.offset.parent.top, e("document" == t.containment ? document : window).width() - this.helperProportions.width - this.margins.left, (e("document" == t.containment ? document : window).height() || document.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top]), !/^(document|window|parent)$/.test(t.containment)) {
                    var n = e(t.containment)[0],
                        r = e(t.containment).offset(),
                        i = "hidden" != e(n).css("overflow");
                    this.containment = [r.left + (parseInt(e(n).css("borderLeftWidth"), 10) || 0) + (parseInt(e(n).css("paddingLeft"), 10) || 0) - this.margins.left, r.top + (parseInt(e(n).css("borderTopWidth"), 10) || 0) + (parseInt(e(n).css("paddingTop"), 10) || 0) - this.margins.top, r.left + (i ? Math.max(n.scrollWidth, n.offsetWidth) : n.offsetWidth) - (parseInt(e(n).css("borderLeftWidth"), 10) || 0) - (parseInt(e(n).css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left, r.top + (i ? Math.max(n.scrollHeight, n.offsetHeight) : n.offsetHeight) - (parseInt(e(n).css("borderTopWidth"), 10) || 0) - (parseInt(e(n).css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top]
                }
            },
            _convertPositionTo: function(t, n) {
                n || (n = this.position);
                var r = "absolute" == t ? 1 : -1,
                    s = (this.options, "absolute" != this.cssPosition || this.scrollParent[0] != document && e.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent : this.offsetParent),
                    o = /(html|body)/i.test(s[0].tagName);
                return {
                    top: n.top + this.offset.relative.top * r + this.offset.parent.top * r - ("fixed" == this.cssPosition ? -this.scrollParent.scrollTop() : o ? 0 : s.scrollTop()) * r,
                    left: n.left + this.offset.relative.left * r + this.offset.parent.left * r - ("fixed" == this.cssPosition ? -this.scrollParent.scrollLeft() : o ? 0 : s.scrollLeft()) * r
                }
            },
            _generatePosition: function(t) {
                var n = this.options,
                    r = "absolute" != this.cssPosition || this.scrollParent[0] != document && e.contains(this.scrollParent[0], this.offsetParent[0]) ? this.scrollParent : this.offsetParent,
                    i = /(html|body)/i.test(r[0].tagName);
                "relative" == this.cssPosition && (this.scrollParent[0] == document || this.scrollParent[0] == this.offsetParent[0]) && (this.offset.relative = this._getRelativeOffset());
                var s = t.pageX,
                    o = t.pageY;
                if (this.originalPosition && (this.containment && (t.pageX - this.offset.click.left < this.containment[0] && (s = this.containment[0] + this.offset.click.left), t.pageY - this.offset.click.top < this.containment[1] && (o = this.containment[1] + this.offset.click.top), t.pageX - this.offset.click.left > this.containment[2] && (s = this.containment[2] + this.offset.click.left), t.pageY - this.offset.click.top > this.containment[3] && (o = this.containment[3] + this.offset.click.top)), n.grid)) {
                    var u = this.originalPageY + Math.round((o - this.originalPageY) / n.grid[1]) * n.grid[1];
                    o = this.containment && (u - this.offset.click.top < this.containment[1] || u - this.offset.click.top > this.containment[3]) ? u - this.offset.click.top < this.containment[1] ? u + n.grid[1] : u - n.grid[1] : u;
                    var a = this.originalPageX + Math.round((s - this.originalPageX) / n.grid[0]) * n.grid[0];
                    s = this.containment && (a - this.offset.click.left < this.containment[0] || a - this.offset.click.left > this.containment[2]) ? a - this.offset.click.left < this.containment[0] ? a + n.grid[0] : a - n.grid[0] : a
                }
                return {
                    top: o - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + ("fixed" == this.cssPosition ? -this.scrollParent.scrollTop() : i ? 0 : r.scrollTop()),
                    left: s - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + ("fixed" == this.cssPosition ? -this.scrollParent.scrollLeft() : i ? 0 : r.scrollLeft())
                }
            },
            _rearrange: function(e, t, n, r) {
                n ? n[0].appendChild(this.placeholder[0]) : t.item[0].parentNode.insertBefore(this.placeholder[0], "down" == this.direction ? t.item[0] : t.item[0].nextSibling), this.counter = this.counter ? ++this.counter : 1;
                var i = this.counter;
                this._delay(function() {
                    i == this.counter && this.refreshPositions(!r)
                })
            },
            _clear: function(t, n) {
                this.reverting = !1;
                var r = [];
                if (!this._noFinalSort && this.currentItem.parent().length && this.placeholder.before(this.currentItem), this._noFinalSort = null, this.helper[0] == this.currentItem[0]) {
                    for (var i in this._storedCSS)("auto" == this._storedCSS[i] || "static" == this._storedCSS[i]) && (this._storedCSS[i] = "");
                    this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")
                } else this.currentItem.show();
                this.fromOutside && !n && r.push(function(e) {
                    this._trigger("receive", e, this._uiHash(this.fromOutside))
                }), (this.fromOutside || this.domPosition.prev != this.currentItem.prev().not(".ui-sortable-helper")[0] || this.domPosition.parent != this.currentItem.parent()[0]) && !n && r.push(function(e) {
                    this._trigger("update", e, this._uiHash())
                }), this !== this.currentContainer && (n || (r.push(function(e) {
                    this._trigger("remove", e, this._uiHash())
                }), r.push(function(e) {
                    return function(t) {
                        e._trigger("receive", t, this._uiHash(this))
                    }
                }.call(this, this.currentContainer)), r.push(function(e) {
                    return function(t) {
                        e._trigger("update", t, this._uiHash(this))
                    }
                }.call(this, this.currentContainer))));
                for (var i = this.containers.length - 1; i >= 0; i--) n || r.push(function(e) {
                    return function(t) {
                        e._trigger("deactivate", t, this._uiHash(this))
                    }
                }.call(this, this.containers[i])), this.containers[i].containerCache.over && (r.push(function(e) {
                    return function(t) {
                        e._trigger("out", t, this._uiHash(this))
                    }
                }.call(this, this.containers[i])), this.containers[i].containerCache.over = 0);
                if (this._storedCursor && e("body").css("cursor", this._storedCursor), this._storedOpacity && this.helper.css("opacity", this._storedOpacity), this._storedZIndex && this.helper.css("zIndex", "auto" == this._storedZIndex ? "" : this._storedZIndex), this.dragging = !1, this.cancelHelperRemoval) {
                    if (!n) {
                        this._trigger("beforeStop", t, this._uiHash());
                        for (var i = 0; i < r.length; i++) r[i].call(this, t);
                        this._trigger("stop", t, this._uiHash())
                    }
                    return this.fromOutside = !1, !1
                }
                if (n || this._trigger("beforeStop", t, this._uiHash()), this.placeholder[0].parentNode.removeChild(this.placeholder[0]), this.helper[0] != this.currentItem[0] && this.helper.remove(), this.helper = null, !n) {
                    for (var i = 0; i < r.length; i++) r[i].call(this, t);
                    this._trigger("stop", t, this._uiHash())
                }
                return this.fromOutside = !1, !0
            },
            _trigger: function() {
                e.Widget.prototype._trigger.apply(this, arguments) === !1 && this.cancel()
            },
            _uiHash: function(t) {
                var n = t || this;
                return {
                    helper: n.helper,
                    placeholder: n.placeholder || e([]),
                    position: n.position,
                    originalPosition: n.originalPosition,
                    offset: n.positionAbs,
                    item: n.currentItem,
                    sender: t ? t.element : null
                }
            }
        })
    }(jQuery),
    function(e) {
        function t(e) {
            return function() {
                var t = this.element.val();
                e.apply(this, arguments), this._refresh(), t !== this.element.val() && this._trigger("change")
            }
        }
        e.widget("ui.spinner", {
            version: "1.9.2",
            defaultElement: "<input>",
            widgetEventPrefix: "spin",
            options: {
                culture: null,
                icons: {
                    down: "ui-icon-triangle-1-s",
                    up: "ui-icon-triangle-1-n"
                },
                incremental: !0,
                max: null,
                min: null,
                numberFormat: null,
                page: 10,
                step: 1,
                change: null,
                spin: null,
                start: null,
                stop: null
            },
            _create: function() {
                this._setOption("max", this.options.max), this._setOption("min", this.options.min), this._setOption("step", this.options.step), this._value(this.element.val(), !0), this._draw(), this._on(this._events), this._refresh(), this._on(this.window, {
                    beforeunload: function() {
                        this.element.removeAttr("autocomplete")
                    }
                })
            },
            _getCreateOptions: function() {
                var t = {},
                    n = this.element;
                return e.each(["min", "max", "step"], function(e, r) {
                    var i = n.attr(r);
                    void 0 !== i && i.length && (t[r] = i)
                }), t
            },
            _events: {
                keydown: function(e) {
                    this._start(e) && this._keydown(e) && e.preventDefault()
                },
                keyup: "_stop",
                focus: function() {
                    this.previous = this.element.val()
                },
                blur: function(e) {
                    return this.cancelBlur ? void delete this.cancelBlur : (this._refresh(), void(this.previous !== this.element.val() && this._trigger("change", e)))
                },
                mousewheel: function(e, t) {
                    return t ? this.spinning || this._start(e) ? (this._spin((t > 0 ? 1 : -1) * this.options.step, e), clearTimeout(this.mousewheelTimer), this.mousewheelTimer = this._delay(function() {
                        this.spinning && this._stop(e)
                    }, 100), e.preventDefault(), void 0) : !1 : void 0
                },
                "mousedown .ui-spinner-button": function(t) {
                    function r() {
                        var e = this.element[0] === this.document[0].activeElement;
                        e || (this.element.focus(), this.previous = n, this._delay(function() {
                            this.previous = n
                        }))
                    }
                    var n;
                    n = this.element[0] === this.document[0].activeElement ? this.previous : this.element.val(), t.preventDefault(), r.call(this), this.cancelBlur = !0, this._delay(function() {
                        delete this.cancelBlur, r.call(this)
                    }), this._start(t) !== !1 && this._repeat(null, e(t.currentTarget).hasClass("ui-spinner-up") ? 1 : -1, t)
                },
                "mouseup .ui-spinner-button": "_stop",
                "mouseenter .ui-spinner-button": function(t) {
                    return e(t.currentTarget).hasClass("ui-state-active") ? this._start(t) === !1 ? !1 : void this._repeat(null, e(t.currentTarget).hasClass("ui-spinner-up") ? 1 : -1, t) : void 0
                },
                "mouseleave .ui-spinner-button": "_stop"
            },
            _draw: function() {
                var e = this.uiSpinner = this.element.addClass("ui-spinner-input").attr("autocomplete", "off").wrap(this._uiSpinnerHtml()).parent().append(this._buttonHtml());
                this.element.attr("role", "spinbutton"), this.buttons = e.find(".ui-spinner-button").attr("tabIndex", -1).button().removeClass("ui-corner-all"), this.buttons.height() > Math.ceil(.5 * e.height()) && e.height() > 0 && e.height(e.height()), this.options.disabled && this.disable()
            },
            _keydown: function(t) {
                var n = this.options,
                    r = e.ui.keyCode;
                switch (t.keyCode) {
                    case r.UP:
                        return this._repeat(null, 1, t), !0;
                    case r.DOWN:
                        return this._repeat(null, -1, t), !0;
                    case r.PAGE_UP:
                        return this._repeat(null, n.page, t), !0;
                    case r.PAGE_DOWN:
                        return this._repeat(null, -n.page, t), !0
                }
                return !1
            },
            _uiSpinnerHtml: function() {
                return "<span class='ui-spinner ui-widget ui-widget-content ui-corner-all'></span>"
            },
            _buttonHtml: function() {
                return "<a class='ui-spinner-button ui-spinner-up ui-corner-tr'><span class='ui-icon " + this.options.icons.up + "'>&#9650;</span></a><a class='ui-spinner-button ui-spinner-down ui-corner-br'><span class='ui-icon " + this.options.icons.down + "'>&#9660;</span></a>"
            },
            _start: function(e) {
                return this.spinning || this._trigger("start", e) !== !1 ? (this.counter || (this.counter = 1), this.spinning = !0, !0) : !1
            },
            _repeat: function(e, t, n) {
                e = e || 500, clearTimeout(this.timer), this.timer = this._delay(function() {
                    this._repeat(40, t, n)
                }, e), this._spin(t * this.options.step, n)
            },
            _spin: function(e, t) {
                var n = this.value() || 0;
                this.counter || (this.counter = 1), n = this._adjustValue(n + e * this._increment(this.counter)), this.spinning && this._trigger("spin", t, {
                    value: n
                }) === !1 || (this._value(n), this.counter++)
            },
            _increment: function(t) {
                var n = this.options.incremental;
                return n ? e.isFunction(n) ? n(t) : Math.floor(t * t * t / 5e4 - t * t / 500 + 17 * t / 200 + 1) : 1
            },
            _precision: function() {
                var e = this._precisionOf(this.options.step);
                return null !== this.options.min && (e = Math.max(e, this._precisionOf(this.options.min))), e
            },
            _precisionOf: function(e) {
                var t = e.toString(),
                    n = t.indexOf(".");
                return -1 === n ? 0 : t.length - n - 1
            },
            _adjustValue: function(e) {
                var t, n, r = this.options;
                return t = null !== r.min ? r.min : 0, n = e - t, n = Math.round(n / r.step) * r.step, e = t + n, e = parseFloat(e.toFixed(this._precision())), null !== r.max && e > r.max ? r.max : null !== r.min && e < r.min ? r.min : e
            },
            _stop: function(e) {
                this.spinning && (clearTimeout(this.timer), clearTimeout(this.mousewheelTimer), this.counter = 0, this.spinning = !1, this._trigger("stop", e))
            },
            _setOption: function(e, t) {
                if ("culture" === e || "numberFormat" === e) {
                    var n = this._parse(this.element.val());
                    return this.options[e] = t, void this.element.val(this._format(n))
                }("max" === e || "min" === e || "step" === e) && "string" == typeof t && (t = this._parse(t)), this._super(e, t), "disabled" === e && (t ? (this.element.prop("disabled", !0), this.buttons.button("disable")) : (this.element.prop("disabled", !1), this.buttons.button("enable")))
            },
            _setOptions: t(function(e) {
                this._super(e), this._value(this.element.val())
            }),
            _parse: function(e) {
                return "string" == typeof e && "" !== e && (e = window.Globalize && this.options.numberFormat ? Globalize.parseFloat(e, 10, this.options.culture) : +e), "" === e || isNaN(e) ? null : e
            },
            _format: function(e) {
                return "" === e ? "" : window.Globalize && this.options.numberFormat ? Globalize.format(e, this.options.numberFormat, this.options.culture) : e
            },
            _refresh: function() {
                this.element.attr({
                    "aria-valuemin": this.options.min,
                    "aria-valuemax": this.options.max,
                    "aria-valuenow": this._parse(this.element.val())
                })
            },
            _value: function(e, t) {
                var n;
                "" !== e && (n = this._parse(e), null !== n && (t || (n = this._adjustValue(n)), e = this._format(n))), this.element.val(e), this._refresh()
            },
            _destroy: function() {
                this.element.removeClass("ui-spinner-input").prop("disabled", !1).removeAttr("autocomplete").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow"), this.uiSpinner.replaceWith(this.element)
            },
            stepUp: t(function(e) {
                this._stepUp(e)
            }),
            _stepUp: function(e) {
                this._spin((e || 1) * this.options.step)
            },
            stepDown: t(function(e) {
                this._stepDown(e)
            }),
            _stepDown: function(e) {
                this._spin((e || 1) * -this.options.step)
            },
            pageUp: t(function(e) {
                this._stepUp((e || 1) * this.options.page)
            }),
            pageDown: t(function(e) {
                this._stepDown((e || 1) * this.options.page)
            }),
            value: function(e) {
                return arguments.length ? void t(this._value).call(this, e) : this._parse(this.element.val())
            },
            widget: function() {
                return this.uiSpinner
            }
        })
    }(jQuery),
    function(e, t) {
        function i() {
            return ++n
        }

        function s(e) {
            return e.hash.length > 1 && e.href.replace(r, "") === location.href.replace(r, "").replace(/\s/g, "%20")
        }
        var n = 0,
            r = /#.*$/;
        e.widget("ui.tabs", {
            version: "1.9.2",
            delay: 300,
            options: {
                active: null,
                collapsible: !1,
                event: "click",
                heightStyle: "content",
                hide: null,
                show: null,
                activate: null,
                beforeActivate: null,
                beforeLoad: null,
                load: null
            },
            _create: function() {
                var t = this,
                    n = this.options,
                    r = n.active,
                    i = location.hash.substring(1);
                this.running = !1, this.element.addClass("ui-tabs ui-widget ui-widget-content ui-corner-all").toggleClass("ui-tabs-collapsible", n.collapsible).delegate(".ui-tabs-nav > li", "mousedown" + this.eventNamespace, function(t) {
                    e(this).is(".ui-state-disabled") && t.preventDefault()
                }).delegate(".ui-tabs-anchor", "focus" + this.eventNamespace, function() {
                    e(this).closest("li").is(".ui-state-disabled") && this.blur()
                }), this._processTabs(), null === r && (i && this.tabs.each(function(t, n) {
                    return e(n).attr("aria-controls") === i ? (r = t, !1) : void 0
                }), null === r && (r = this.tabs.index(this.tabs.filter(".ui-tabs-active"))), (null === r || -1 === r) && (r = this.tabs.length ? 0 : !1)), r !== !1 && (r = this.tabs.index(this.tabs.eq(r)), -1 === r && (r = n.collapsible ? !1 : 0)), n.active = r, !n.collapsible && n.active === !1 && this.anchors.length && (n.active = 0), e.isArray(n.disabled) && (n.disabled = e.unique(n.disabled.concat(e.map(this.tabs.filter(".ui-state-disabled"), function(e) {
                    return t.tabs.index(e)
                }))).sort()), this.active = this.options.active !== !1 && this.anchors.length ? this._findActive(this.options.active) : e(), this._refresh(), this.active.length && this.load(n.active)
            },
            _getCreateEventData: function() {
                return {
                    tab: this.active,
                    panel: this.active.length ? this._getPanelForTab(this.active) : e()
                }
            },
            _tabKeydown: function(t) {
                var n = e(this.document[0].activeElement).closest("li"),
                    r = this.tabs.index(n),
                    i = !0;
                if (!this._handlePageNav(t)) {
                    switch (t.keyCode) {
                        case e.ui.keyCode.RIGHT:
                        case e.ui.keyCode.DOWN:
                            r++;
                            break;
                        case e.ui.keyCode.UP:
                        case e.ui.keyCode.LEFT:
                            i = !1, r--;
                            break;
                        case e.ui.keyCode.END:
                            r = this.anchors.length - 1;
                            break;
                        case e.ui.keyCode.HOME:
                            r = 0;
                            break;
                        case e.ui.keyCode.SPACE:
                            return t.preventDefault(), clearTimeout(this.activating), this._activate(r), void 0;
                        case e.ui.keyCode.ENTER:
                            return t.preventDefault(), clearTimeout(this.activating), this._activate(r === this.options.active ? !1 : r), void 0;
                        default:
                            return
                    }
                    t.preventDefault(), clearTimeout(this.activating), r = this._focusNextTab(r, i), t.ctrlKey || (n.attr("aria-selected", "false"), this.tabs.eq(r).attr("aria-selected", "true"), this.activating = this._delay(function() {
                        this.option("active", r)
                    }, this.delay))
                }
            },
            _panelKeydown: function(t) {
                this._handlePageNav(t) || t.ctrlKey && t.keyCode === e.ui.keyCode.UP && (t.preventDefault(), this.active.focus())
            },
            _handlePageNav: function(t) {
                return t.altKey && t.keyCode === e.ui.keyCode.PAGE_UP ? (this._activate(this._focusNextTab(this.options.active - 1, !1)), !0) : t.altKey && t.keyCode === e.ui.keyCode.PAGE_DOWN ? (this._activate(this._focusNextTab(this.options.active + 1, !0)), !0) : void 0
            },
            _findNextTab: function(t, n) {
                function i() {
                    return t > r && (t = 0), 0 > t && (t = r), t
                }
                for (var r = this.tabs.length - 1; - 1 !== e.inArray(i(), this.options.disabled);) t = n ? t + 1 : t - 1;
                return t
            },
            _focusNextTab: function(e, t) {
                return e = this._findNextTab(e, t), this.tabs.eq(e).focus(), e
            },
            _setOption: function(e, t) {
                return "active" === e ? void this._activate(t) : "disabled" === e ? void this._setupDisabled(t) : (this._super(e, t), "collapsible" === e && (this.element.toggleClass("ui-tabs-collapsible", t), !t && this.options.active === !1 && this._activate(0)), "event" === e && this._setupEvents(t), "heightStyle" === e && this._setupHeightStyle(t), void 0)
            },
            _tabId: function(e) {
                return e.attr("aria-controls") || "ui-tabs-" + i()
            },
            _sanitizeSelector: function(e) {
                return e ? e.replace(/[!"$%&'()*+,.\/:;<=>?@\[\]\^`{|}~]/g, "\\$&") : ""
            },
            refresh: function() {
                var t = this.options,
                    n = this.tablist.children(":has(a[href])");
                t.disabled = e.map(n.filter(".ui-state-disabled"), function(e) {
                    return n.index(e)
                }), this._processTabs(), t.active !== !1 && this.anchors.length ? this.active.length && !e.contains(this.tablist[0], this.active[0]) ? this.tabs.length === t.disabled.length ? (t.active = !1, this.active = e()) : this._activate(this._findNextTab(Math.max(0, t.active - 1), !1)) : t.active = this.tabs.index(this.active) : (t.active = !1, this.active = e()), this._refresh()
            },
            _refresh: function() {
                this._setupDisabled(this.options.disabled), this._setupEvents(this.options.event), this._setupHeightStyle(this.options.heightStyle), this.tabs.not(this.active).attr({
                    "aria-selected": "false",
                    tabIndex: -1
                }), this.panels.not(this._getPanelForTab(this.active)).hide().attr({
                    "aria-expanded": "false",
                    "aria-hidden": "true"
                }), this.active.length ? (this.active.addClass("ui-tabs-active ui-state-active").attr({
                    "aria-selected": "true",
                    tabIndex: 0
                }), this._getPanelForTab(this.active).show().attr({
                    "aria-expanded": "true",
                    "aria-hidden": "false"
                })) : this.tabs.eq(0).attr("tabIndex", 0)
            },
            _processTabs: function() {
                var t = this;
                this.tablist = this._getList().addClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all").attr("role", "tablist"), this.tabs = this.tablist.find("> li:has(a[href])").addClass("ui-state-default ui-corner-top").attr({
                    role: "tab",
                    tabIndex: -1
                }), this.anchors = this.tabs.map(function() {
                    return e("a", this)[0]
                }).addClass("ui-tabs-anchor").attr({
                    role: "presentation",
                    tabIndex: -1
                }), this.panels = e(), this.anchors.each(function(n, r) {
                    var i, o, u, a = e(r).uniqueId().attr("id"),
                        f = e(r).closest("li"),
                        l = f.attr("aria-controls");
                    s(r) ? (i = r.hash, o = t.element.find(t._sanitizeSelector(i))) : (u = t._tabId(f), i = "#" + u, o = t.element.find(i), o.length || (o = t._createPanel(u), o.insertAfter(t.panels[n - 1] || t.tablist)), o.attr("aria-live", "polite")), o.length && (t.panels = t.panels.add(o)), l && f.data("ui-tabs-aria-controls", l), f.attr({
                        "aria-controls": i.substring(1),
                        "aria-labelledby": a
                    }), o.attr("aria-labelledby", a)
                }), this.panels.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").attr("role", "tabpanel")
            },
            _getList: function() {
                return this.element.find("ol,ul").eq(0)
            },
            _createPanel: function(t) {
                return e("<div>").attr("id", t).addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").data("ui-tabs-destroy", !0)
            },
            _setupDisabled: function(t) {
                e.isArray(t) && (t.length ? t.length === this.anchors.length && (t = !0) : t = !1);
                for (var r, n = 0; r = this.tabs[n]; n++) t === !0 || -1 !== e.inArray(n, t) ? e(r).addClass("ui-state-disabled").attr("aria-disabled", "true") : e(r).removeClass("ui-state-disabled").removeAttr("aria-disabled");
                this.options.disabled = t
            },
            _setupEvents: function(t) {
                var n = {
                    click: function(e) {
                        e.preventDefault()
                    }
                };
                t && e.each(t.split(" "), function(e, t) {
                    n[t] = "_eventHandler"
                }), this._off(this.anchors.add(this.tabs).add(this.panels)), this._on(this.anchors, n), this._on(this.tabs, {
                    keydown: "_tabKeydown"
                }), this._on(this.panels, {
                    keydown: "_panelKeydown"
                }), this._focusable(this.tabs), this._hoverable(this.tabs)
            },
            _setupHeightStyle: function(t) {
                var n, r, i = this.element.parent();
                "fill" === t ? (e.support.minHeight || (r = i.css("overflow"), i.css("overflow", "hidden")), n = i.height(), this.element.siblings(":visible").each(function() {
                    var t = e(this),
                        r = t.css("position");
                    "absolute" !== r && "fixed" !== r && (n -= t.outerHeight(!0))
                }), r && i.css("overflow", r), this.element.children().not(this.panels).each(function() {
                    n -= e(this).outerHeight(!0)
                }), this.panels.each(function() {
                    e(this).height(Math.max(0, n - e(this).innerHeight() + e(this).height()))
                }).css("overflow", "auto")) : "auto" === t && (n = 0, this.panels.each(function() {
                    n = Math.max(n, e(this).height("").height())
                }).height(n))
            },
            _eventHandler: function(t) {
                var n = this.options,
                    r = this.active,
                    i = e(t.currentTarget),
                    s = i.closest("li"),
                    o = s[0] === r[0],
                    u = o && n.collapsible,
                    a = u ? e() : this._getPanelForTab(s),
                    f = r.length ? this._getPanelForTab(r) : e(),
                    l = {
                        oldTab: r,
                        oldPanel: f,
                        newTab: u ? e() : s,
                        newPanel: a
                    };
                t.preventDefault(), s.hasClass("ui-state-disabled") || s.hasClass("ui-tabs-loading") || this.running || o && !n.collapsible || this._trigger("beforeActivate", t, l) === !1 || (n.active = u ? !1 : this.tabs.index(s), this.active = o ? e() : s, this.xhr && this.xhr.abort(), !f.length && !a.length && e.error("jQuery UI Tabs: Mismatching fragment identifier."), a.length && this.load(this.tabs.index(s), t), this._toggle(t, l))
            },
            _toggle: function(t, n) {
                function o() {
                    r.running = !1, r._trigger("activate", t, n)
                }

                function u() {
                    n.newTab.closest("li").addClass("ui-tabs-active ui-state-active"), i.length && r.options.show ? r._show(i, r.options.show, o) : (i.show(), o())
                }
                var r = this,
                    i = n.newPanel,
                    s = n.oldPanel;
                this.running = !0, s.length && this.options.hide ? this._hide(s, this.options.hide, function() {
                    n.oldTab.closest("li").removeClass("ui-tabs-active ui-state-active"), u()
                }) : (n.oldTab.closest("li").removeClass("ui-tabs-active ui-state-active"), s.hide(), u()), s.attr({
                    "aria-expanded": "false",
                    "aria-hidden": "true"
                }), n.oldTab.attr("aria-selected", "false"), i.length && s.length ? n.oldTab.attr("tabIndex", -1) : i.length && this.tabs.filter(function() {
                    return 0 === e(this).attr("tabIndex")
                }).attr("tabIndex", -1), i.attr({
                    "aria-expanded": "true",
                    "aria-hidden": "false"
                }), n.newTab.attr({
                    "aria-selected": "true",
                    tabIndex: 0
                })
            },
            _activate: function(t) {
                var n, r = this._findActive(t);
                r[0] !== this.active[0] && (r.length || (r = this.active), n = r.find(".ui-tabs-anchor")[0], this._eventHandler({
                    target: n,
                    currentTarget: n,
                    preventDefault: e.noop
                }))
            },
            _findActive: function(t) {
                return t === !1 ? e() : this.tabs.eq(t)
            },
            _getIndex: function(e) {
                return "string" == typeof e && (e = this.anchors.index(this.anchors.filter("[href$='" + e + "']"))), e
            },
            _destroy: function() {
                this.xhr && this.xhr.abort(), this.element.removeClass("ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-collapsible"), this.tablist.removeClass("ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all").removeAttr("role"), this.anchors.removeClass("ui-tabs-anchor").removeAttr("role").removeAttr("tabIndex").removeData("href.tabs").removeData("load.tabs").removeUniqueId(), this.tabs.add(this.panels).each(function() {
                    e.data(this, "ui-tabs-destroy") ? e(this).remove() : e(this).removeClass("ui-state-default ui-state-active ui-state-disabled ui-corner-top ui-corner-bottom ui-widget-content ui-tabs-active ui-tabs-panel").removeAttr("tabIndex").removeAttr("aria-live").removeAttr("aria-busy").removeAttr("aria-selected").removeAttr("aria-labelledby").removeAttr("aria-hidden").removeAttr("aria-expanded").removeAttr("role")
                }), this.tabs.each(function() {
                    var t = e(this),
                        n = t.data("ui-tabs-aria-controls");
                    n ? t.attr("aria-controls", n) : t.removeAttr("aria-controls")
                }), this.panels.show(), "content" !== this.options.heightStyle && this.panels.css("height", "")
            },
            enable: function(n) {
                var r = this.options.disabled;
                r !== !1 && (n === t ? r = !1 : (n = this._getIndex(n), r = e.isArray(r) ? e.map(r, function(e) {
                    return e !== n ? e : null
                }) : e.map(this.tabs, function(e, t) {
                    return t !== n ? t : null
                })), this._setupDisabled(r))
            },
            disable: function(n) {
                var r = this.options.disabled;
                if (r !== !0) {
                    if (n === t) r = !0;
                    else {
                        if (n = this._getIndex(n), -1 !== e.inArray(n, r)) return;
                        r = e.isArray(r) ? e.merge([n], r).sort() : [n]
                    }
                    this._setupDisabled(r)
                }
            },
            load: function(t, n) {
                t = this._getIndex(t);
                var r = this,
                    i = this.tabs.eq(t),
                    o = i.find(".ui-tabs-anchor"),
                    u = this._getPanelForTab(i),
                    a = {
                        tab: i,
                        panel: u
                    };
                s(o[0]) || (this.xhr = e.ajax(this._ajaxSettings(o, n, a)), this.xhr && "canceled" !== this.xhr.statusText && (i.addClass("ui-tabs-loading"), u.attr("aria-busy", "true"), this.xhr.success(function(e) {
                    setTimeout(function() {
                        u.html(e), r._trigger("load", n, a)
                    }, 1)
                }).complete(function(e, t) {
                    setTimeout(function() {
                        "abort" === t && r.panels.stop(!1, !0), i.removeClass("ui-tabs-loading"), u.removeAttr("aria-busy"), e === r.xhr && delete r.xhr
                    }, 1)
                })))
            },
            _ajaxSettings: function(t, n, r) {
                var i = this;
                return {
                    url: t.attr("href"),
                    beforeSend: function(t, s) {
                        return i._trigger("beforeLoad", n, e.extend({
                            jqXHR: t,
                            ajaxSettings: s
                        }, r))
                    }
                }
            },
            _getPanelForTab: function(t) {
                var n = e(t).attr("aria-controls");
                return this.element.find(this._sanitizeSelector("#" + n))
            }
        }), e.uiBackCompat !== !1 && (e.ui.tabs.prototype._ui = function(e, t) {
            return {
                tab: e,
                panel: t,
                index: this.anchors.index(e)
            }
        }, e.widget("ui.tabs", e.ui.tabs, {
            url: function(e, t) {
                this.anchors.eq(e).attr("href", t)
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                ajaxOptions: null,
                cache: !1
            },
            _create: function() {
                this._super();
                var t = this;
                this._on({
                    tabsbeforeload: function(n, r) {
                        return e.data(r.tab[0], "cache.tabs") ? void n.preventDefault() : void r.jqXHR.success(function() {
                            t.options.cache && e.data(r.tab[0], "cache.tabs", !0)
                        })
                    }
                })
            },
            _ajaxSettings: function(t, n, r) {
                var i = this.options.ajaxOptions;
                return e.extend({}, i, {
                    error: function(e, t) {
                        try {
                            i.error(e, t, r.tab.closest("li").index(), r.tab[0])
                        } catch (n) {}
                    }
                }, this._superApply(arguments))
            },
            _setOption: function(e, t) {
                "cache" === e && t === !1 && this.anchors.removeData("cache.tabs"), this._super(e, t)
            },
            _destroy: function() {
                this.anchors.removeData("cache.tabs"), this._super()
            },
            url: function(e) {
                this.anchors.eq(e).removeData("cache.tabs"), this._superApply(arguments)
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            abort: function() {
                this.xhr && this.xhr.abort()
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                spinner: "<em>Loading&#8230;</em>"
            },
            _create: function() {
                this._super(), this._on({
                    tabsbeforeload: function(e, t) {
                        if (e.target === this.element[0] && this.options.spinner) {
                            var n = t.tab.find("span"),
                                r = n.html();
                            n.html(this.options.spinner), t.jqXHR.complete(function() {
                                n.html(r)
                            })
                        }
                    }
                })
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                enable: null,
                disable: null
            },
            enable: function(t) {
                var r, n = this.options;
                (t && n.disabled === !0 || e.isArray(n.disabled) && -1 !== e.inArray(t, n.disabled)) && (r = !0), this._superApply(arguments), r && this._trigger("enable", null, this._ui(this.anchors[t], this.panels[t]))
            },
            disable: function(t) {
                var r, n = this.options;
                (t && n.disabled === !1 || e.isArray(n.disabled) && -1 === e.inArray(t, n.disabled)) && (r = !0), this._superApply(arguments), r && this._trigger("disable", null, this._ui(this.anchors[t], this.panels[t]))
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                add: null,
                remove: null,
                tabTemplate: "<li><a href='#{href}'><span>#{label}</span></a></li>"
            },
            add: function(n, r, i) {
                i === t && (i = this.anchors.length);
                var s, o, u = this.options,
                    a = e(u.tabTemplate.replace(/#\{href\}/g, n).replace(/#\{label\}/g, r)),
                    f = n.indexOf("#") ? this._tabId(a) : n.replace("#", "");
                return a.addClass("ui-state-default ui-corner-top").data("ui-tabs-destroy", !0), a.attr("aria-controls", f), s = i >= this.tabs.length, o = this.element.find("#" + f), o.length || (o = this._createPanel(f), s ? i > 0 ? o.insertAfter(this.panels.eq(-1)) : o.appendTo(this.element) : o.insertBefore(this.panels[i])), o.addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").hide(), s ? a.appendTo(this.tablist) : a.insertBefore(this.tabs[i]), u.disabled = e.map(u.disabled, function(e) {
                    return e >= i ? ++e : e
                }), this.refresh(), 1 === this.tabs.length && u.active === !1 && this.option("active", 0), this._trigger("add", null, this._ui(this.anchors[i], this.panels[i])), this
            },
            remove: function(t) {
                t = this._getIndex(t);
                var n = this.options,
                    r = this.tabs.eq(t).remove(),
                    i = this._getPanelForTab(r).remove();
                return r.hasClass("ui-tabs-active") && this.anchors.length > 2 && this._activate(t + (t + 1 < this.anchors.length ? 1 : -1)), n.disabled = e.map(e.grep(n.disabled, function(e) {
                    return e !== t
                }), function(e) {
                    return e >= t ? --e : e
                }), this.refresh(), this._trigger("remove", null, this._ui(r.find("a")[0], i[0])), this
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            length: function() {
                return this.anchors.length
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                idPrefix: "ui-tabs-"
            },
            _tabId: function(t) {
                var n = t.is("li") ? t.find("a[href]") : t;
                return n = n[0], e(n).closest("li").attr("aria-controls") || n.title && n.title.replace(/\s/g, "_").replace(/[^\w\u00c0-\uFFFF\-]/g, "") || this.options.idPrefix + i()
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                panelTemplate: "<div></div>"
            },
            _createPanel: function(t) {
                return e(this.options.panelTemplate).attr("id", t).addClass("ui-tabs-panel ui-widget-content ui-corner-bottom").data("ui-tabs-destroy", !0)
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            _create: function() {
                var e = this.options;
                null === e.active && e.selected !== t && (e.active = -1 === e.selected ? !1 : e.selected), this._super(), e.selected = e.active, e.selected === !1 && (e.selected = -1)
            },
            _setOption: function(e, t) {
                if ("selected" !== e) return this._super(e, t);
                var n = this.options;
                this._super("active", -1 === t ? !1 : t), n.selected = n.active, n.selected === !1 && (n.selected = -1)
            },
            _eventHandler: function() {
                this._superApply(arguments), this.options.selected = this.options.active, this.options.selected === !1 && (this.options.selected = -1)
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                show: null,
                select: null
            },
            _create: function() {
                this._super(), this.options.active !== !1 && this._trigger("show", null, this._ui(this.active.find(".ui-tabs-anchor")[0], this._getPanelForTab(this.active)[0]))
            },
            _trigger: function(e, t, n) {
                var r, i, s = this._superApply(arguments);
                return s ? ("beforeActivate" === e ? (r = n.newTab.length ? n.newTab : n.oldTab, i = n.newPanel.length ? n.newPanel : n.oldPanel, s = this._super("select", t, {
                    tab: r.find(".ui-tabs-anchor")[0],
                    panel: i[0],
                    index: r.closest("li").index()
                })) : "activate" === e && n.newTab.length && (s = this._super("show", t, {
                    tab: n.newTab.find(".ui-tabs-anchor")[0],
                    panel: n.newPanel[0],
                    index: n.newTab.closest("li").index()
                })), s) : !1
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            select: function(e) {
                if (e = this._getIndex(e), -1 === e) {
                    if (!this.options.collapsible || -1 === this.options.selected) return;
                    e = this.options.selected
                }
                this.anchors.eq(e).trigger(this.options.event + this.eventNamespace)
            }
        }), function() {
            var t = 0;
            e.widget("ui.tabs", e.ui.tabs, {
                options: {
                    cookie: null
                },
                _create: function() {
                    var t, e = this.options;
                    null == e.active && e.cookie && (t = parseInt(this._cookie(), 10), -1 === t && (t = !1), e.active = t), this._super()
                },
                _cookie: function(n) {
                    var r = [this.cookie || (this.cookie = this.options.cookie.name || "ui-tabs-" + ++t)];
                    return arguments.length && (r.push(n === !1 ? -1 : n), r.push(this.options.cookie)), e.cookie.apply(null, r)
                },
                _refresh: function() {
                    this._super(), this.options.cookie && this._cookie(this.options.active, this.options.cookie)
                },
                _eventHandler: function() {
                    this._superApply(arguments), this.options.cookie && this._cookie(this.options.active, this.options.cookie)
                },
                _destroy: function() {
                    this._super(), this.options.cookie && this._cookie(null, this.options.cookie)
                }
            })
        }(), e.widget("ui.tabs", e.ui.tabs, {
            _trigger: function(t, n, r) {
                var i = e.extend({}, r);
                return "load" === t && (i.panel = i.panel[0], i.tab = i.tab.find(".ui-tabs-anchor")[0]), this._super(t, n, i)
            }
        }), e.widget("ui.tabs", e.ui.tabs, {
            options: {
                fx: null
            },
            _getFx: function() {
                var t, n, r = this.options.fx;
                return r && (e.isArray(r) ? (t = r[0], n = r[1]) : t = n = r), r ? {
                    show: n,
                    hide: t
                } : null
            },
            _toggle: function(e, t) {
                function o() {
                    n.running = !1, n._trigger("activate", e, t)
                }

                function u() {
                    t.newTab.closest("li").addClass("ui-tabs-active ui-state-active"), r.length && s.show ? r.animate(s.show, s.show.duration, function() {
                        o()
                    }) : (r.show(), o())
                }
                var n = this,
                    r = t.newPanel,
                    i = t.oldPanel,
                    s = this._getFx();
                return s ? (n.running = !0, void(i.length && s.hide ? i.animate(s.hide, s.hide.duration, function() {
                    t.oldTab.closest("li").removeClass("ui-tabs-active ui-state-active"), u()
                }) : (t.oldTab.closest("li").removeClass("ui-tabs-active ui-state-active"), i.hide(), u()))) : this._super(e, t)
            }
        }))
    }(jQuery),
    function(e) {
        function n(t, n) {
            var r = (t.attr("aria-describedby") || "").split(/\s+/);
            r.push(n), t.data("ui-tooltip-id", n).attr("aria-describedby", e.trim(r.join(" ")))
        }

        function r(t) {
            var n = t.data("ui-tooltip-id"),
                r = (t.attr("aria-describedby") || "").split(/\s+/),
                i = e.inArray(n, r); - 1 !== i && r.splice(i, 1), t.removeData("ui-tooltip-id"), r = e.trim(r.join(" ")), r ? t.attr("aria-describedby", r) : t.removeAttr("aria-describedby")
        }
        var t = 0;
        e.widget("ui.tooltip", {
            version: "1.9.2",
            options: {
                content: function() {
                    return e(this).attr("title")
                },
                hide: !0,
                items: "[title]:not([disabled])",
                position: {
                    my: "left top+15",
                    at: "left bottom",
                    collision: "flipfit flip"
                },
                show: !0,
                tooltipClass: null,
                track: !1,
                close: null,
                open: null
            },
            _create: function() {
                this._on({
                    mouseover: "open",
                    focusin: "open"
                }), this.tooltips = {}, this.parents = {}, this.options.disabled && this._disable()
            },
            _setOption: function(t, n) {
                var r = this;
                return "disabled" === t ? (this[n ? "_disable" : "_enable"](), void(this.options[t] = n)) : (this._super(t, n), void("content" === t && e.each(this.tooltips, function(e, t) {
                    r._updateContent(t)
                })))
            },
            _disable: function() {
                var t = this;
                e.each(this.tooltips, function(n, r) {
                    var i = e.Event("blur");
                    i.target = i.currentTarget = r[0], t.close(i, !0)
                }), this.element.find(this.options.items).andSelf().each(function() {
                    var t = e(this);
                    t.is("[title]") && t.data("ui-tooltip-title", t.attr("title")).attr("title", "")
                })
            },
            _enable: function() {
                this.element.find(this.options.items).andSelf().each(function() {
                    var t = e(this);
                    t.data("ui-tooltip-title") && t.attr("title", t.data("ui-tooltip-title"))
                })
            },
            open: function(t) {
                var n = this,
                    r = e(t ? t.target : this.element).closest(this.options.items);
                r.length && !r.data("ui-tooltip-id") && (r.attr("title") && r.data("ui-tooltip-title", r.attr("title")), r.data("ui-tooltip-open", !0), t && "mouseover" === t.type && r.parents().each(function() {
                    var r, t = e(this);
                    t.data("ui-tooltip-open") && (r = e.Event("blur"), r.target = r.currentTarget = this, n.close(r, !0)), t.attr("title") && (t.uniqueId(), n.parents[this.id] = {
                        element: this,
                        title: t.attr("title")
                    }, t.attr("title", ""))
                }), this._updateContent(r, t))
            },
            _updateContent: function(e, t) {
                var n, r = this.options.content,
                    i = this,
                    s = t ? t.type : null;
                return "string" == typeof r ? this._open(t, e, r) : (n = r.call(e[0], function(n) {
                    e.data("ui-tooltip-open") && i._delay(function() {
                        t && (t.type = s), this._open(t, e, n)
                    })
                }), void(n && this._open(t, e, n)))
            },
            _open: function(t, r, i) {
                function f(e) {
                    a.of = e, s.is(":hidden") || s.position(a)
                }
                var s, o, u, a = e.extend({}, this.options.position);
                if (i) {
                    if (s = this._find(r), s.length) return void s.find(".ui-tooltip-content").html(i);
                    r.is("[title]") && (t && "mouseover" === t.type ? r.attr("title", "") : r.removeAttr("title")), s = this._tooltip(r), n(r, s.attr("id")), s.find(".ui-tooltip-content").html(i), this.options.track && t && /^mouse/.test(t.type) ? (this._on(this.document, {
                        mousemove: f
                    }), f(t)) : s.position(e.extend({
                        of: r
                    }, this.options.position)), s.hide(), this._show(s, this.options.show), this.options.show && this.options.show.delay && (u = setInterval(function() {
                        s.is(":visible") && (f(a.of), clearInterval(u))
                    }, e.fx.interval)), this._trigger("open", t, {
                        tooltip: s
                    }), o = {
                        keyup: function(t) {
                            if (t.keyCode === e.ui.keyCode.ESCAPE) {
                                var n = e.Event(t);
                                n.currentTarget = r[0], this.close(n, !0)
                            }
                        },
                        remove: function() {
                            this._removeTooltip(s)
                        }
                    }, t && "mouseover" !== t.type || (o.mouseleave = "close"), t && "focusin" !== t.type || (o.focusout = "close"), this._on(!0, r, o)
                }
            },
            close: function(t) {
                var n = this,
                    i = e(t ? t.currentTarget : this.element),
                    s = this._find(i);
                this.closing || (i.data("ui-tooltip-title") && i.attr("title", i.data("ui-tooltip-title")), r(i), s.stop(!0), this._hide(s, this.options.hide, function() {
                    n._removeTooltip(e(this))
                }), i.removeData("ui-tooltip-open"), this._off(i, "mouseleave focusout keyup"), i[0] !== this.element[0] && this._off(i, "remove"), this._off(this.document, "mousemove"), t && "mouseleave" === t.type && e.each(this.parents, function(t, r) {
                    e(r.element).attr("title", r.title), delete n.parents[t]
                }), this.closing = !0, this._trigger("close", t, {
                    tooltip: s
                }), this.closing = !1)
            },
            _tooltip: function(n) {
                var r = "ui-tooltip-" + t++,
                    i = e("<div>").attr({
                        id: r,
                        role: "tooltip"
                    }).addClass("ui-tooltip ui-widget ui-corner-all ui-widget-content " + (this.options.tooltipClass || ""));
                return e("<div>").addClass("ui-tooltip-content").appendTo(i), i.appendTo(this.document[0].body), e.fn.bgiframe && i.bgiframe(), this.tooltips[r] = n, i
            },
            _find: function(t) {
                var n = t.data("ui-tooltip-id");
                return n ? e("#" + n) : e()
            },
            _removeTooltip: function(e) {
                e.remove(), delete this.tooltips[e.attr("id")]
            },
            _destroy: function() {
                var t = this;
                e.each(this.tooltips, function(n, r) {
                    var i = e.Event("blur");
                    i.target = i.currentTarget = r[0], t.close(i, !0), e("#" + n).remove(), r.data("ui-tooltip-title") && (r.attr("title", r.data("ui-tooltip-title")), r.removeData("ui-tooltip-title"))
                })
            }
        })
    }(jQuery), ! function(a) {
        "use strict";
        a(window.jQuery, window, document)
    }(function(a, b, c, d) {
        "use strict";
        a.widget("selectBox.selectBoxIt", {
            VERSION: "3.8.1",
            options: {
                showEffect: "none",
                showEffectOptions: {},
                showEffectSpeed: "medium",
                hideEffect: "none",
                hideEffectOptions: {},
                hideEffectSpeed: "medium",
                showFirstOption: !0,
                defaultText: "",
                defaultIcon: "",
                downArrowIcon: "",
                theme: "default",
                keydownOpen: !0,
                isMobile: function() {
                    var a = navigator.userAgent || navigator.vendor || b.opera;
                    return /iPhone|iPod|iPad|Silk|Android|BlackBerry|Opera Mini|IEMobile/.test(a)
                },
                "native": !1,
                aggressiveChange: !1,
                selectWhenHidden: !0,
                viewport: a(b),
                similarSearch: !1,
                copyAttributes: ["title", "rel"],
                copyClasses: "button",
                nativeMousedown: !1,
                customShowHideEvent: !1,
                autoWidth: !0,
                html: !0,
                populate: "",
                dynamicPositioning: !0,
                hideCurrent: !1
            },
            getThemes: function() {
                var b = this,
                    c = a(b.element).attr("data-theme") || "c";
                return {
                    bootstrap: {
                        focus: "active",
                        hover: "",
                        enabled: "enabled",
                        disabled: "disabled",
                        arrow: "caret",
                        button: "btn",
                        list: "dropdown-menu",
                        container: "bootstrap",
                        open: "open"
                    },
                    jqueryui: {
                        focus: "ui-state-focus",
                        hover: "ui-state-hover",
                        enabled: "ui-state-enabled",
                        disabled: "ui-state-disabled",
                        arrow: "ui-icon ui-icon-triangle-1-s",
                        button: "ui-widget ui-state-default",
                        list: "ui-widget ui-widget-content",
                        container: "jqueryui",
                        open: "selectboxit-open"
                    },
                    jquerymobile: {
                        focus: "ui-btn-down-" + c,
                        hover: "ui-btn-hover-" + c,
                        enabled: "ui-enabled",
                        disabled: "ui-disabled",
                        arrow: "ui-icon ui-icon-arrow-d ui-icon-shadow",
                        button: "ui-btn ui-btn-icon-right ui-btn-corner-all ui-shadow ui-btn-up-" + c,
                        list: "ui-btn ui-btn-icon-right ui-btn-corner-all ui-shadow ui-btn-up-" + c,
                        container: "jquerymobile",
                        open: "selectboxit-open"
                    },
                    "default": {
                        focus: "selectboxit-focus",
                        hover: "selectboxit-hover",
                        enabled: "selectboxit-enabled",
                        disabled: "selectboxit-disabled",
                        arrow: "selectboxit-default-arrow",
                        button: "selectboxit-btn",
                        list: "selectboxit-list",
                        container: "selectboxit-container",
                        open: "selectboxit-open"
                    }
                }
            },
            isDeferred: function(b) {
                return a.isPlainObject(b) && b.promise && b.done
            },
            _create: function(b) {
                var d = this,
                    e = d.options.populate,
                    f = d.options.theme;
                return d.element.is("select") ? (d.widgetProto = a.Widget.prototype, d.originalElem = d.element[0], d.selectBox = d.element, d.options.populate && d.add && !b && d.add(e), d.selectItems = d.element.find("option"), d.firstSelectItem = d.selectItems.slice(0, 1), d.documentHeight = a(c).height(), d.theme = a.isPlainObject(f) ? a.extend({}, d.getThemes()["default"], f) : d.getThemes()[f] ? d.getThemes()[f] : d.getThemes()["default"], d.currentFocus = 0, d.blur = !0, d.textArray = [], d.currentIndex = 0, d.currentText = "", d.flipped = !1, b || (d.selectBoxStyles = d.selectBox.attr("style")), d._createDropdownButton()._createUnorderedList()._copyAttributes()._replaceSelectBox()._addClasses(d.theme)._eventHandlers(), d.originalElem.disabled && d.disable && d.disable(), d._ariaAccessibility && d._ariaAccessibility(), d.isMobile = d.options.isMobile(), d._mobile && d._mobile(), d.options["native"] && this._applyNativeSelect(), d.triggerEvent("create"), d) : void 0
            },
            _createDropdownButton: function() {
                var b = this,
                    c = b.originalElemId = b.originalElem.id || "",
                    d = b.originalElemValue = b.originalElem.value || "",
                    e = b.originalElemName = b.originalElem.name || "",
                    f = b.options.copyClasses,
                    g = b.selectBox.attr("class") || "";
                return b.dropdownText = a("<span/>", {
                    id: c && c + "SelectBoxItText",
                    "class": "selectboxit-text",
                    unselectable: "on",
                    text: b.firstSelectItem.text()
                }).attr("data-val", d), b.dropdownImageContainer = a("<span/>", {
                    "class": "selectboxit-option-icon-container"
                }), b.dropdownImage = a("<i/>", {
                    id: c && c + "SelectBoxItDefaultIcon",
                    "class": "selectboxit-default-icon",
                    unselectable: "on"
                }), b.dropdown = a("<span/>", {
                    id: c && c + "SelectBoxIt",
                    "class": "selectboxit " + ("button" === f ? g : "") + " " + (b.selectBox.prop("disabled") ? b.theme.disabled : b.theme.enabled),
                    name: e,
                    tabindex: b.selectBox.attr("tabindex") || "0",
                    unselectable: "on"
                }).append(b.dropdownImageContainer.append(b.dropdownImage)).append(b.dropdownText), b.dropdownContainer = a("<span/>", {
                    id: c && c + "SelectBoxItContainer",
                    "class": "selectboxit-container " + b.theme.container + " " + ("container" === f ? g : "")
                }).append(b.dropdown), b
            },
            _createUnorderedList: function() {
                var b, c, d, e, f, g, h, i, j, k, l, m, n, o = this,
                    p = "",
                    q = o.originalElemId || "",
                    r = a("<ul/>", {
                        id: q && q + "SelectBoxItOptions",
                        "class": "selectboxit-options",
                        tabindex: -1
                    });
                if (o.options.showFirstOption || (o.selectItems.first().attr("disabled", "disabled"), o.selectItems = o.selectBox.find("option").slice(1)), o.selectItems.each(function(q) {
                        m = a(this), c = "", d = "", b = m.prop("disabled"), e = m.attr("data-icon") || "", f = m.attr("data-iconurl") || "", g = f ? "selectboxit-option-icon-url" : "", h = f ? "style=\"background-image:url('" + f + "');\"" : "", i = m.attr("data-selectedtext"), j = m.attr("data-text"), l = j ? j : m.text(), n = m.parent(), n.is("optgroup") && (c = "selectboxit-optgroup-option", 0 === m.index() && (d = '<span class="selectboxit-optgroup-header ' + n.first().attr("class") + '"data-disabled="true">' + n.first().attr("label") + "</span>")), m.attr("value", this.value), p += d + '<li data-id="' + q + '" data-val="' + this.value + '" data-disabled="' + b + '" class="' + c + " selectboxit-option " + (a(this).attr("class") || "") + '"><a class="selectboxit-option-anchor"><span class="selectboxit-option-icon-container"><i class="selectboxit-option-icon ' + e + " " + (g || o.theme.container) + '"' + h + "></i></span>" + (o.options.html ? l : o.htmlEscape(l)) + "</a></li>", k = m.attr("data-search"), o.textArray[q] = b ? "" : k ? k : l, this.selected && (o._setText(o.dropdownText, i || l), o.currentFocus = q)
                    }), o.options.defaultText || o.selectBox.attr("data-text")) {
                    var s = o.options.defaultText || o.selectBox.attr("data-text");
                    o._setText(o.dropdownText, s), o.options.defaultText = s
                }
                return r.append(p), o.list = r, o.dropdownContainer.append(o.list), o.listItems = o.list.children("li"), o.listAnchors = o.list.find("a"), o.listItems.first().addClass("selectboxit-option-first"), o.listItems.last().addClass("selectboxit-option-last"), o.list.find("li[data-disabled='true']").not(".optgroupHeader").addClass(o.theme.disabled), o.dropdownImage.addClass(o.selectBox.attr("data-icon") || o.options.defaultIcon || o.listItems.eq(o.currentFocus).find("i").attr("class")), o.dropdownImage.attr("style", o.listItems.eq(o.currentFocus).find("i").attr("style")), o
            },
            _replaceSelectBox: function() {
                var b, c, e, f = this,
                    g = f.originalElem.id || "",
                    h = f.selectBox.attr("data-size"),
                    i = f.listSize = h === d ? "auto" : "0" === h ? "auto" : +h;
                return f.selectBox.css("display", "none").after(f.dropdownContainer), f.dropdownContainer.appendTo("body").addClass("selectboxit-rendering"), b = f.dropdown.height(), f.downArrow = a("<i/>", {
                    id: g && g + "SelectBoxItArrow",
                    "class": "selectboxit-arrow",
                    unselectable: "on"
                }), f.downArrowContainer = a("<span/>", {
                    id: g && g + "SelectBoxItArrowContainer",
                    "class": "selectboxit-arrow-container",
                    unselectable: "on"
                }).append(f.downArrow), f.dropdown.append(f.downArrowContainer), f.listItems.removeClass("selectboxit-selected").eq(f.currentFocus).addClass("selectboxit-selected"), c = f.downArrowContainer.outerWidth(!0), e = f.dropdownImage.outerWidth(!0), f.options.autoWidth && (f.dropdown.css({
                    width: "auto"
                }).css({
                    width: f.list.outerWidth(!0) + c + e
                }), f.list.css({
                    "min-width": f.dropdown.width()
                })), f.dropdownText.css({
                    "max-width": f.dropdownContainer.outerWidth(!0) - (c + e)
                }), f.selectBox.after(f.dropdownContainer), f.dropdownContainer.removeClass("selectboxit-rendering"), "number" === a.type(i) && (f.maxHeight = f.listAnchors.outerHeight(!0) * i), f
            },
            _scrollToView: function(a) {
                var b = this,
                    c = b.listItems.eq(b.currentFocus),
                    d = b.list.scrollTop(),
                    e = c.height(),
                    f = c.position().top,
                    g = Math.abs(f),
                    h = b.list.height();
                return "search" === a ? e > h - f ? b.list.scrollTop(d + (f - (h - e))) : -1 > f && b.list.scrollTop(f - e) : "up" === a ? -1 > f && b.list.scrollTop(d - g) : "down" === a && e > h - f && b.list.scrollTop(d + (g - h + e)), b
            },
            _callbackSupport: function(b) {
                var c = this;
                return a.isFunction(b) && b.call(c, c.dropdown), c
            },
            _setText: function(a, b) {
                var c = this;
                return c.options.html ? a.html(b) : a.text(b), c
            },
            open: function(a) {
                var b = this,
                    c = b.options.showEffect,
                    d = b.options.showEffectSpeed,
                    e = b.options.showEffectOptions,
                    f = b.options["native"],
                    g = b.isMobile;
                return !b.listItems.length || b.dropdown.hasClass(b.theme.disabled) ? b : (f || g || this.list.is(":visible") || (b.triggerEvent("open"), b._dynamicPositioning && b.options.dynamicPositioning && b._dynamicPositioning(), "none" === c ? b.list.show() : "show" === c || "slideDown" === c || "fadeIn" === c ? b.list[c](d) : b.list.show(c, e, d), b.list.promise().done(function() {
                    b._scrollToView("search"), b.triggerEvent("opened")
                })), b._callbackSupport(a), b)
            },
            close: function(a) {
                var b = this,
                    c = b.options.hideEffect,
                    d = b.options.hideEffectSpeed,
                    e = b.options.hideEffectOptions,
                    f = b.options["native"],
                    g = b.isMobile;
                return f || g || !b.list.is(":visible") || (b.triggerEvent("close"), "none" === c ? b.list.hide() : "hide" === c || "slideUp" === c || "fadeOut" === c ? b.list[c](d) : b.list.hide(c, e, d), b.list.promise().done(function() {
                    b.triggerEvent("closed")
                })), b._callbackSupport(a), b
            },
            toggle: function() {
                var a = this,
                    b = a.list.is(":visible");
                b ? a.close() : b || a.open()
            },
            _keyMappings: {
                38: "up",
                40: "down",
                13: "enter",
                8: "backspace",
                9: "tab",
                32: "space",
                27: "esc"
            },
            _keydownMethods: function() {
                var a = this,
                    b = a.list.is(":visible") || !a.options.keydownOpen;
                return {
                    down: function() {
                        a.moveDown && b && a.moveDown()
                    },
                    up: function() {
                        a.moveUp && b && a.moveUp()
                    },
                    enter: function() {
                        var b = a.listItems.eq(a.currentFocus);
                        a._update(b), "true" !== b.attr("data-preventclose") && a.close(), a.triggerEvent("enter")
                    },
                    tab: function() {
                        a.triggerEvent("tab-blur"), a.close()
                    },
                    backspace: function() {
                        a.triggerEvent("backspace")
                    },
                    esc: function() {
                        a.close()
                    }
                }
            },
            _eventHandlers: function() {
                var b, c, d = this,
                    e = d.options.nativeMousedown,
                    f = d.options.customShowHideEvent,
                    g = d.focusClass,
                    h = d.hoverClass,
                    i = d.openClass;
                return this.dropdown.on({
                    "click.selectBoxIt": function() {
                        d.dropdown.trigger("focus", !0), d.originalElem.disabled || (d.triggerEvent("click"), e || f || d.toggle())
                    },
                    "mousedown.selectBoxIt": function() {
                        a(this).data("mdown", !0), d.triggerEvent("mousedown"), e && !f && d.toggle()
                    },
                    "mouseup.selectBoxIt": function() {
                        d.triggerEvent("mouseup")
                    },
                    "blur.selectBoxIt": function() {
                        d.blur && (d.triggerEvent("blur"), d.close(), a(this).removeClass(g))
                    },
                    "focus.selectBoxIt": function(b, c) {
                        var e = a(this).data("mdown");
                        a(this).removeData("mdown"), e || c || setTimeout(function() {
                            d.triggerEvent("tab-focus")
                        }, 0), c || (a(this).hasClass(d.theme.disabled) || a(this).addClass(g), d.triggerEvent("focus"))
                    },
                    "keydown.selectBoxIt": function(a) {
                        var b = d._keyMappings[a.keyCode],
                            c = d._keydownMethods()[b];
                        c && (c(), !d.options.keydownOpen || "up" !== b && "down" !== b || d.open()), c && "tab" !== b && a.preventDefault()
                    },
                    "keypress.selectBoxIt": function(a) {
                        var b = a.charCode || a.keyCode,
                            c = d._keyMappings[a.charCode || a.keyCode],
                            e = String.fromCharCode(b);
                        d.search && (!c || c && "space" === c) && d.search(e, !0, !0), "space" === c && a.preventDefault()
                    },
                    "mouseenter.selectBoxIt": function() {
                        d.triggerEvent("mouseenter")
                    },
                    "mouseleave.selectBoxIt": function() {
                        d.triggerEvent("mouseleave")
                    }
                }), d.list.on({
                    "mouseover.selectBoxIt": function() {
                        d.blur = !1
                    },
                    "mouseout.selectBoxIt": function() {
                        d.blur = !0
                    },
                    "focusin.selectBoxIt": function() {
                        d.dropdown.trigger("focus", !0)
                    }
                }), d.list.on({
                    "mousedown.selectBoxIt": function() {
                        d._update(a(this)), d.triggerEvent("option-click"), "false" === a(this).attr("data-disabled") && "true" !== a(this).attr("data-preventclose") && d.close(), setTimeout(function() {
                            d.dropdown.trigger("focus", !0)
                        }, 0)
                    },
                    "focusin.selectBoxIt": function() {
                        d.listItems.not(a(this)).removeAttr("data-active"), a(this).attr("data-active", "");
                        var b = d.list.is(":hidden");
                        (d.options.searchWhenHidden && b || d.options.aggressiveChange || b && d.options.selectWhenHidden) && d._update(a(this)), a(this).addClass(g)
                    },
                    "mouseup.selectBoxIt": function() {
                        e && !f && (d._update(a(this)), d.triggerEvent("option-mouseup"), "false" === a(this).attr("data-disabled") && "true" !== a(this).attr("data-preventclose") && d.close())
                    },
                    "mouseenter.selectBoxIt": function() {
                        "false" === a(this).attr("data-disabled") && (d.listItems.removeAttr("data-active"), a(this).addClass(g).attr("data-active", ""), d.listItems.not(a(this)).removeClass(g), a(this).addClass(g), d.currentFocus = +a(this).attr("data-id"))
                    },
                    "mouseleave.selectBoxIt": function() {
                        "false" === a(this).attr("data-disabled") && (d.listItems.not(a(this)).removeClass(g).removeAttr("data-active"), a(this).addClass(g), d.currentFocus = +a(this).attr("data-id"))
                    },
                    "blur.selectBoxIt": function() {
                        a(this).removeClass(g)
                    }
                }, ".selectboxit-option"), d.list.on({
                    "click.selectBoxIt": function(a) {
                        a.preventDefault()
                    }
                }, "a"), d.selectBox.on({
                    "change.selectBoxIt, internal-change.selectBoxIt": function(a, e) {
                        var f, g;
                        e || (f = d.list.find('li[data-val="' + d.originalElem.value + '"]'), f.length && (d.listItems.eq(d.currentFocus).removeClass(d.focusClass), d.currentFocus = +f.attr("data-id"))), f = d.listItems.eq(d.currentFocus), g = f.attr("data-selectedtext"), b = f.attr("data-text"), c = b ? b : f.find("a").text(), d._setText(d.dropdownText, g || c), d.dropdownText.attr("data-val", d.originalElem.value), f.find("i").attr("class") && (d.dropdownImage.attr("class", f.find("i").attr("class")).addClass("selectboxit-default-icon"), d.dropdownImage.attr("style", f.find("i").attr("style"))), d.triggerEvent("changed")
                    },
                    "disable.selectBoxIt": function() {
                        d.dropdown.addClass(d.theme.disabled)
                    },
                    "enable.selectBoxIt": function() {
                        d.dropdown.removeClass(d.theme.disabled)
                    },
                    "open.selectBoxIt": function() {
                        var a, b = d.list.find("li[data-val='" + d.dropdownText.attr("data-val") + "']");
                        b.length || (b = d.listItems.not("[data-disabled=true]").first()), d.currentFocus = +b.attr("data-id"), a = d.listItems.eq(d.currentFocus), d.dropdown.addClass(i).removeClass(h).addClass(g), d.listItems.removeClass(d.selectedClass).removeAttr("data-active").not(a).removeClass(g), a.addClass(d.selectedClass).addClass(g), d.options.hideCurrent && (d.listItems.show(), a.hide())
                    },
                    "close.selectBoxIt": function() {
                        d.dropdown.removeClass(i)
                    },
                    "blur.selectBoxIt": function() {
                        d.dropdown.removeClass(g)
                    },
                    "mouseenter.selectBoxIt": function() {
                        a(this).hasClass(d.theme.disabled) || d.dropdown.addClass(h)
                    },
                    "mouseleave.selectBoxIt": function() {
                        d.dropdown.removeClass(h)
                    },
                    destroy: function(a) {
                        a.preventDefault(), a.stopPropagation()
                    }
                }), d
            },
            _update: function(a) {
                var b, c, d, e = this,
                    f = e.options.defaultText || e.selectBox.attr("data-text"),
                    g = e.listItems.eq(e.currentFocus);
                "false" === a.attr("data-disabled") && (b = e.listItems.eq(e.currentFocus).attr("data-selectedtext"), c = g.attr("data-text"), d = c ? c : g.text(), (f && e.options.html ? e.dropdownText.html() === f : e.dropdownText.text() === f) && e.selectBox.val() === a.attr("data-val") ? e.triggerEvent("change") : (e.selectBox.val(a.attr("data-val")), e.currentFocus = +a.attr("data-id"), e.originalElem.value !== e.dropdownText.attr("data-val") && e.triggerEvent("change")))
            },
            _addClasses: function(a) {
                var b = this,
                    c = (b.focusClass = a.focus, b.hoverClass = a.hover, a.button),
                    d = a.list,
                    e = a.arrow,
                    f = a.container;
                return b.openClass = a.open, b.selectedClass = "selectboxit-selected", b.downArrow.addClass(b.selectBox.attr("data-downarrow") || b.options.downArrowIcon || e), b.dropdownContainer.addClass(f), b.dropdown.addClass(c), b.list.addClass(d), b
            },
            refresh: function(a, b) {
                var c = this;
                return c._destroySelectBoxIt()._create(!0), b || c.triggerEvent("refresh"), c._callbackSupport(a), c
            },
            htmlEscape: function(a) {
                return String(a).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
            },
            triggerEvent: function(a) {
                var b = this,
                    c = b.options.showFirstOption ? b.currentFocus : b.currentFocus - 1 >= 0 ? b.currentFocus : 0;
                return b.selectBox.trigger(a, {
                    selectbox: b.selectBox,
                    selectboxOption: b.selectItems.eq(c),
                    dropdown: b.dropdown,
                    dropdownOption: b.listItems.eq(b.currentFocus)
                }), b
            },
            _copyAttributes: function() {
                var a = this;
                return a._addSelectBoxAttributes && a._addSelectBoxAttributes(), a
            },
            _realOuterWidth: function(a) {
                if (a.is(":visible")) return a.outerWidth(!0);
                var b, c = a.clone();
                return c.css({
                    visibility: "hidden",
                    display: "block",
                    position: "absolute"
                }).appendTo("body"), b = c.outerWidth(!0), c.remove(), b
            }
        });
        var e = a.selectBox.selectBoxIt.prototype;
        e.add = function(b, c) {
            this._populate(b, function(b) {
                var d, e, f = this,
                    g = a.type(b),
                    h = 0,
                    i = [],
                    j = f._isJSON(b),
                    k = j && f._parseJSON(b);
                if (b && ("array" === g || j && k.data && "array" === a.type(k.data)) || "object" === g && b.data && "array" === a.type(b.data)) {
                    for (f._isJSON(b) && (b = k), b.data && (b = b.data), e = b.length; e - 1 >= h; h += 1) d = b[h], a.isPlainObject(d) ? i.push(a("<option/>", d)) : "string" === a.type(d) && i.push(a("<option/>", {
                        text: d,
                        value: d
                    }));
                    f.selectBox.append(i)
                } else b && "string" === g && !f._isJSON(b) ? f.selectBox.append(b) : b && "object" === g ? f.selectBox.append(a("<option/>", b)) : b && f._isJSON(b) && a.isPlainObject(f._parseJSON(b)) && f.selectBox.append(a("<option/>", f._parseJSON(b)));
                return f.dropdown ? f.refresh(function() {
                    f._callbackSupport(c)
                }, !0) : f._callbackSupport(c), f
            })
        }, e._parseJSON = function(b) {
            return JSON && JSON.parse && JSON.parse(b) || a.parseJSON(b)
        }, e._isJSON = function(a) {
            var b, c = this;
            try {
                return b = c._parseJSON(a), !0
            } catch (d) {
                return !1
            }
        }, e._populate = function(b, c) {
            var d = this;
            return b = a.isFunction(b) ? b.call() : b, d.isDeferred(b) ? b.done(function(a) {
                c.call(d, a)
            }) : c.call(d, b), d
        }, e._ariaAccessibility = function() {
            var b = this,
                c = a("label[for='" + b.originalElem.id + "']");
            return b.dropdownContainer.attr({
                role: "combobox",
                "aria-autocomplete": "list",
                "aria-haspopup": "true",
                "aria-expanded": "false",
                "aria-owns": b.list[0].id
            }), b.dropdownText.attr({
                "aria-live": "polite"
            }), b.dropdown.on({
                "disable.selectBoxIt": function() {
                    b.dropdownContainer.attr("aria-disabled", "true")
                },
                "enable.selectBoxIt": function() {
                    b.dropdownContainer.attr("aria-disabled", "false")
                }
            }), c.length && b.dropdownContainer.attr("aria-labelledby", c[0].id), b.list.attr({
                role: "listbox",
                "aria-hidden": "true"
            }), b.listItems.attr({
                role: "option"
            }), b.selectBox.on({
                "open.selectBoxIt": function() {
                    b.list.attr("aria-hidden", "false"), b.dropdownContainer.attr("aria-expanded", "true")
                },
                "close.selectBoxIt": function() {
                    b.list.attr("aria-hidden", "true"), b.dropdownContainer.attr("aria-expanded", "false")
                }
            }), b
        }, e._addSelectBoxAttributes = function() {
            var b = this;
            return b._addAttributes(b.selectBox.prop("attributes"), b.dropdown), b.selectItems.each(function(c) {
                b._addAttributes(a(this).prop("attributes"), b.listItems.eq(c))
            }), b
        }, e._addAttributes = function(b, c) {
            var d = this,
                e = d.options.copyAttributes;
            return b.length && a.each(b, function(b, d) {
                var f = d.name.toLowerCase(),
                    g = d.value;
                "null" === g || -1 === a.inArray(f, e) && -1 === f.indexOf("data") || c.attr(f, g)
            }), d
        }, e.destroy = function(a) {
            var b = this;
            return b._destroySelectBoxIt(), b.widgetProto.destroy.call(b), b._callbackSupport(a), b
        }, e._destroySelectBoxIt = function() {
            var b = this;
            return b.dropdown.off(".selectBoxIt"), a.contains(b.dropdownContainer[0], b.originalElem) && b.dropdownContainer.before(b.selectBox), b.dropdownContainer.remove(), b.selectBox.removeAttr("style").attr("style", b.selectBoxStyles), b.triggerEvent("destroy"), b
        }, e.disable = function(a) {
            var b = this;
            return b.options.disabled || (b.close(), b.selectBox.attr("disabled", "disabled"), b.dropdown.removeAttr("tabindex").removeClass(b.theme.enabled).addClass(b.theme.disabled), b.setOption("disabled", !0), b.triggerEvent("disable")), b._callbackSupport(a), b
        }, e.disableOption = function(b, c) {
            var d, e, f, g = this,
                h = a.type(b);
            return "number" === h && (g.close(), d = g.selectBox.find("option").eq(b), g.triggerEvent("disable-option"), d.attr("disabled", "disabled"), g.listItems.eq(b).attr("data-disabled", "true").addClass(g.theme.disabled), g.currentFocus === b && (e = g.listItems.eq(g.currentFocus).nextAll("li").not("[data-disabled='true']").first().length, f = g.listItems.eq(g.currentFocus).prevAll("li").not("[data-disabled='true']").first().length, e ? g.moveDown() : f ? g.moveUp() : g.disable())), g._callbackSupport(c), g
        }, e._isDisabled = function() {
            var a = this;
            return a.originalElem.disabled && a.disable(), a
        }, e._dynamicPositioning = function() {
            var b = this;
            if ("number" === a.type(b.listSize)) b.list.css("max-height", b.maxHeight || "none");
            else {
                var c = b.dropdown.offset().top,
                    d = b.list.data("max-height") || b.list.outerHeight(),
                    e = b.dropdown.outerHeight(),
                    f = b.options.viewport,
                    g = f.height(),
                    h = a.isWindow(f.get(0)) ? f.scrollTop() : f.offset().top,
                    i = g + h >= c + e + d,
                    j = !i;
                if (b.list.data("max-height") || b.list.data("max-height", b.list.outerHeight()), j)
                    if (b.dropdown.offset().top - h >= d) b.list.css("max-height", d), b.list.css("top", b.dropdown.position().top - b.list.outerHeight());
                    else {
                        var k = Math.abs(c + e + d - (g + h)),
                            l = Math.abs(b.dropdown.offset().top - h - d);
                        l > k ? (b.list.css("max-height", d - k - e / 2), b.list.css("top", "auto")) : (b.list.css("max-height", d - l - e / 2), b.list.css("top", b.dropdown.position().top - b.list.outerHeight()))
                    }
                else b.list.css("max-height", d), b.list.css("top", "auto")
            }
            return b
        }, e.enable = function(a) {
            var b = this;
            return b.options.disabled && (b.triggerEvent("enable"), b.selectBox.removeAttr("disabled"), b.dropdown.attr("tabindex", 0).removeClass(b.theme.disabled).addClass(b.theme.enabled), b.setOption("disabled", !1), b._callbackSupport(a)), b
        }, e.enableOption = function(b, c) {
            var d, e = this,
                f = a.type(b);
            return "number" === f && (d = e.selectBox.find("option").eq(b), e.triggerEvent("enable-option"), d.removeAttr("disabled"), e.listItems.eq(b).attr("data-disabled", "false").removeClass(e.theme.disabled)), e._callbackSupport(c), e
        }, e.moveDown = function(a) {
            var b = this;
            b.currentFocus += 1;
            var c = "true" === b.listItems.eq(b.currentFocus).attr("data-disabled") ? !0 : !1,
                d = b.listItems.eq(b.currentFocus).nextAll("li").not("[data-disabled='true']").first().length;
            if (b.currentFocus === b.listItems.length) b.currentFocus -= 1;
            else {
                if (c && d) return b.listItems.eq(b.currentFocus - 1).blur(), void b.moveDown();
                c && !d ? b.currentFocus -= 1 : (b.listItems.eq(b.currentFocus - 1).blur().end().eq(b.currentFocus).focusin(), b._scrollToView("down"), b.triggerEvent("moveDown"))
            }
            return b._callbackSupport(a), b
        }, e.moveUp = function(a) {
            var b = this;
            b.currentFocus -= 1;
            var c = "true" === b.listItems.eq(b.currentFocus).attr("data-disabled") ? !0 : !1,
                d = b.listItems.eq(b.currentFocus).prevAll("li").not("[data-disabled='true']").first().length;
            if (-1 === b.currentFocus) b.currentFocus += 1;
            else {
                if (c && d) return b.listItems.eq(b.currentFocus + 1).blur(), void b.moveUp();
                c && !d ? b.currentFocus += 1 : (b.listItems.eq(this.currentFocus + 1).blur().end().eq(b.currentFocus).focusin(), b._scrollToView("up"), b.triggerEvent("moveUp"))
            }
            return b._callbackSupport(a), b
        }, e._setCurrentSearchOption = function(a) {
            var b = this;
            return (b.options.aggressiveChange || b.options.selectWhenHidden || b.listItems.eq(a).is(":visible")) && b.listItems.eq(a).data("disabled") !== !0 && (b.listItems.eq(b.currentFocus).blur(), b.currentIndex = a, b.currentFocus = a, b.listItems.eq(b.currentFocus).focusin(), b._scrollToView("search"), b.triggerEvent("search")), b
        }, e._searchAlgorithm = function(a, b) {
            var c, d, e, f, g = this,
                h = !1,
                i = g.textArray,
                j = g.currentText;
            for (c = a, e = i.length; e > c; c += 1) {
                for (f = i[c], d = 0; e > d; d += 1) - 1 !== i[d].search(b) && (h = !0, d = e);
                if (h || (g.currentText = g.currentText.charAt(g.currentText.length - 1).replace(/[|()\[{.+*?$\\]/g, "\\$0"), j = g.currentText), b = new RegExp(j, "gi"), j.length < 3) {
                    if (b = new RegExp(j.charAt(0), "gi"), -1 !== f.charAt(0).search(b)) return g._setCurrentSearchOption(c), (f.substring(0, j.length).toLowerCase() !== j.toLowerCase() || g.options.similarSearch) && (g.currentIndex += 1), !1
                } else if (-1 !== f.search(b)) return g._setCurrentSearchOption(c), !1;
                if (f.toLowerCase() === g.currentText.toLowerCase()) return g._setCurrentSearchOption(c), g.currentText = "", !1
            }
            return !0
        }, e.search = function(a, b, c) {
            var d = this;
            c ? d.currentText += a.replace(/[|()\[{.+*?$\\]/g, "\\$0") : d.currentText = a.replace(/[|()\[{.+*?$\\]/g, "\\$0");
            var e = d._searchAlgorithm(d.currentIndex, new RegExp(d.currentText, "gi"));
            return e && d._searchAlgorithm(0, d.currentText), d._callbackSupport(b), d
        }, e._updateMobileText = function() {
            var a, b, c, d = this;
            a = d.selectBox.find("option").filter(":selected"), b = a.attr("data-text"), c = b ? b : a.text(), d._setText(d.dropdownText, c), d.list.find('li[data-val="' + a.val() + '"]').find("i").attr("class") && d.dropdownImage.attr("class", d.list.find('li[data-val="' + a.val() + '"]').find("i").attr("class")).addClass("selectboxit-default-icon")
        }, e._applyNativeSelect = function() {
            var a = this;
            return a.dropdownContainer.append(a.selectBox), a.dropdown.attr("tabindex", "-1"), a.selectBox.css({
                display: "block",
                visibility: "visible",
                width: a._realOuterWidth(a.dropdown),
                height: a.dropdown.outerHeight(),
                opacity: "0",
                position: "absolute",
                top: "0",
                left: "0",
                cursor: "pointer",
                "z-index": "999999",
                margin: a.dropdown.css("margin"),
                padding: "0",
                "-webkit-appearance": "menulist-button"
            }), a.originalElem.disabled && a.triggerEvent("disable"), this
        }, e._mobileEvents = function() {
            var a = this;
            a.selectBox.on({
                "changed.selectBoxIt": function() {
                    a.hasChanged = !0, a._updateMobileText(), a.triggerEvent("option-click")
                },
                "mousedown.selectBoxIt": function() {
                    a.hasChanged || !a.options.defaultText || a.originalElem.disabled || (a._updateMobileText(), a.triggerEvent("option-click"))
                },
                "enable.selectBoxIt": function() {
                    a.selectBox.removeClass("selectboxit-rendering")
                },
                "disable.selectBoxIt": function() {
                    a.selectBox.addClass("selectboxit-rendering")
                }
            })
        }, e._mobile = function() {
            var a = this;
            return a.isMobile && (a._applyNativeSelect(), a._mobileEvents()), this
        }, e.remove = function(b, c) {
            var d, e, f = this,
                g = a.type(b),
                h = 0,
                i = "";
            if ("array" === g) {
                for (e = b.length; e - 1 >= h; h += 1) d = b[h], "number" === a.type(d) && (i += i.length ? ", option:eq(" + d + ")" : "option:eq(" + d + ")");
                f.selectBox.find(i).remove()
            } else "number" === g ? f.selectBox.find("option").eq(b).remove() : f.selectBox.find("option").remove();
            return f.dropdown ? f.refresh(function() {
                f._callbackSupport(c)
            }, !0) : f._callbackSupport(c), f
        }, e.selectOption = function(b, c) {
            var d = this,
                e = a.type(b);
            return "number" === e ? d.selectBox.val(d.selectItems.eq(b).val()).change() : "string" === e && d.selectBox.val(b).change(), d._callbackSupport(c), d
        }, e.setOption = function(b, c, d) {
            var e = this;
            return "string" === a.type(b) && (e.options[b] = c), e.refresh(function() {
                e._callbackSupport(d)
            }, !0), e
        }, e.setOptions = function(b, c) {
            var d = this;
            return a.isPlainObject(b) && (d.options = a.extend({}, d.options, b)), d.refresh(function() {
                d._callbackSupport(c)
            }, !0), d
        }, e.wait = function(a, b) {
            var c = this;
            return c.widgetProto._delay.call(c, b, a), c
        }
    });
var paymentDuration = "monthly",
    numberOnly = new RegExp("^[0-9]+$"),
    today = new Date,
    remainingDays, totalDays, emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/,
    date = today.getDate(),
    cardSS = StoreProperties.cards,
    checkLicense = !0,
    alignLoading = $(".loadingMsg"),
    activeCurrency = StoreProperties.currencyCode ? StoreProperties.currencyCode : "USD";
StoreProperties.multiAddonValues = {}, getMonthName = function(date) {
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    return monthNames[date.getMonth()]
}, $("#bp").on("focus", "select.selectBoxIt", function() {
    $(this).next("span").find(".selectboxit").addClass("inputActive")
}).on("blur", "select.selectBoxIt", function() {
    $(this).next("span").find(".selectboxit").removeClass("inputActive")
});
var service = window.location.href.split("?serv=")[1];
if (service) {
    var page = null != service ? service.split("#")[0] : "";
    $(".serviceName").html("Zoho " + StoreProperties.serviceDisplayName);
    var script = document.createElement("script");
    script.type = "text/javascript", script.src = "json/" + page + "-json.js", $("head").append(script)
}
$(".ccEdit").on("click", function() {
    StoreProperties.cc && StoreUtil.cc.set(StoreProperties.cc), $(".cardDisp").slideUp(200), $(".cTypeTd span").css("opacity", ""), loadSupportedCards(), setPaymentMethod(), $(".changeCard").slideDown(200, function() {
        $(".changeCard").find("input:first").focus()
    })
});
var currencyJSON = StoreProperties.currencyJSON;
String.prototype.replaceAll = function(search, replace) {
    return void 0 === replace ? this.toString() : this.replace(new RegExp("[" + search + "]", "g"), replace)
};
var oneOpened = 0;
$(".usr").bind("click", function() {
    var eleU = $(this);
    $(eleU).addClass("HeaderMenuHover").find(".usrNameId,ul").show(100), 1 == oneOpened && $(".help").removeClass("HeaderMenuHover").find("ul").hide(100);
    var eleUcl = $(this).parent().find(".usrthumbcl");
    eleUcl.css("z-index", "1").show(), oneOpened = 1, $(window).bind("mousedown.hideMenu", function() {
        var eleUW = eleU;
        oneOpened = 0, eleUcl.css("z-index", "").hide(), $(eleUW).removeClass("HeaderMenuHover").find(".usrNameId,ul").hide(100), $(this).unbind("mousedown.hideMenu")
    })
}).bind("mousedown", function() {
    return !1
}).find("li:not(.not)").bind("click", function() {
    return $(window).trigger("mousedown.hideMenu"), !1
}), $(".help").bind("click", function() {
    var eleH = $(this);
    $(eleH).addClass("HeaderMenuHover").find("ul").show(100), 1 == oneOpened && $(".usr").removeClass("HeaderMenuHover").find(".usrNameId,ul").hide(100);
    var eleHcl = $(this).parent().find(".helpthumbcl");
    eleHcl.css("z-index", "1").show(), oneOpened = 1, $(window).bind("mousedown.hideMenu", function() {
        var eleHW = eleH;
        oneOpened = 0, eleHcl.css("z-index", "").hide(), $(eleHW).removeClass("HeaderMenuHover").find("ul").hide(100), $(this).unbind("mousedown.hideMenu")
    })
}).bind("mousedown", function() {
    return !1
}).find("li:not(.not)").bind("click", function() {
    return $(window).trigger("mousedown.hideMenu"), !1
}), merge = function(obj1, obj2) {
    var obj3 = [];
    for (attr1 = 0; attr1 < obj2.length; attr1++) obj3.push(obj1[attr1]);
    for (attr2 = 0; attr2 < obj2.length; attr2++) obj3.push(obj2[attr2]);
    return obj3
}, "function" != typeof String.prototype.startsWith && (String.prototype.startsWith = function(str) {
    return 0 === this.indexOf(str)
}), searchFilter = function(searchEle) {
    var svalue = $(searchEle).val();
    if (0 == svalue.length) $(searchEle).focus(), $("#searchTerm").hide(), $("#period").attr("val", "period").removeClass("disabledbut");
    else {
        $("#searchTerm").show().find("span").text(svalue), $("#period").attr("val", "").addClass("disabledbut");
        var tbl = $(".filterRow table");
        $(tbl).each(function() {
            if ("none" != $(this).css("display")) {
                var table = $(this);
                table.find("tr:not(.notHide)").hide(), $("#noresultfound").show();
                var eachObject = $(this).find(".transId");
                $(eachObject).each(function() {
                    $(this).text().startsWith("" + svalue) && ($(this).parent().show(), $("#noresultfound").hide())
                })
            }
        })
    }
}, clearSearchFilter = function() {
    var searFil = $("#searchbox");
    searFil.val(""), searchFilter(searFil), searFil.parent().removeClass("focustyComb")
}, $("#searchbox").bind("focus", function() {
    $(this).parent().addClass("focustyComb")
}).bind("focusout", function() {
    0 == $(this).val().length ? $(this).parent().removeClass("focustyComb") : $(this).parent().addClass("focustyComb")
}).bind("keyup", function(e) {
    13 == e.which && searchFilter($(this))
});
var onlyNumbers = /^[0-9]+$/;
isNotEmpty = function(str) {
    var temp = $.trim(str);
    return temp.length > 0 ? !0 : !1
}, ccNumberCheck = function(ev) {
    var keyCode = window.event ? ev.keyCode : ev.which,
        ctrlDown = ev.ctrlKey || ev.metaKey;
    return ctrlDown || !(48 > keyCode || keyCode > 57) ? !0 : 0 == keyCode || 8 == keyCode || 13 == keyCode || ev.ctrlKey || 32 == keyCode || 45 == keyCode ? void 0 : (ev.preventDefault(), !1)
}, $(".ccardNo,.cvvIn").on("keypress", function(ev) {
    ccNumberCheck(ev)
});
var cardTypeClass = "";
$(".ccardNo").on("keyup", function() {
    var nm = $(this).val(),
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
    $("#cctype").val(ctype)
}), $(document).on("click", ".planEditBtn:not(.editCredits)", function() {
    var stepHead = $(this).parents(".subAcHead");
    $(".subDetDiv,.cErrMsg,#showInlineErrorMsg").hide(), $("label[for='3dsecure']").removeClass("labelfocus"), stepHead.nextAll(".subAcHead"), $(".subCDiv").hasClass(".sfLink") || $(".subCDiv").addClass("subAcHead").removeClass("whiteBG"), stepHead.next(".subDetDiv").slideDown(), $("html, body").animate({
        scrollTop: 0
    }, "slow"), stepHead.removeClass("subAcHead").addClass("whiteBG"), stepHead.nextAll(".subAcHead").removeClass("subAcHead c444"), stepHead.hasClass("planTitleDiv") ? (setCurrencyPicker("select-plan"), trackClickEvent("Payments - Edit - Select Plan")) : stepHead.hasClass("planDetailHd") ? ($(".orderSumryHdr #oldEditionInfo").remove(), setCurrencyPicker("plan-details"), trackClickEvent("Payments - Edit - Place Order")) : trackClickEvent("Payments - Edit - Confirm Order")
}),cardDetailsCheck = function(changecc) {
    var cardCheck = !0,
        position = $(".ccardNo").position(),
        cardType = $("#cctype").val(),
        supportedCards = getSupportedCards();
    if (isNotEmpty($(".ccardNo").val()) && onlyNumbers.test($(".ccardNo").val()))
        if ($(".ccardNo").val().length < 13) $(".ccardNo").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".ccardNo").attr("label") + " is not valid"), changecc && cardMsg(position.left - 13, position.top + 1, $(".ccardNo").attr("label") + " is not valid", changecc), cardCheck = !1;
        else if (supportedCards && -1 === supportedCards.indexOf(cardType)) $(".ccardNo").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, getCardNotSupportedMsg(cardType)), changecc && cardMsg(position.left - 13, position.top + 1, getCardNotSupportedMsg(cardType), changecc), cardCheck = !1;
    else if (0 == $(".eMon").prop("selectedIndex")) {
        var position = $("span.eMon:visible").parent().position();
        $(".eMon").focus(), cardMsg(position.left - 13, position.top + 3, $(".eMon").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 3, $(".eMon").attr("label") + " is required", changecc), cardCheck = !1
    } else if (0 == $(".eYear").prop("selectedIndex")) {
        var position = $("span.eYear:visible").parent().position();
        $(".eYear").focus(), cardMsg(position.left - 13, position.top + 3, $(".eYear").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 3, $(".eYear").attr("label") + " is required", changecc), cardCheck = !1
    } else if (ExpDate("eMon", "eYear")) {
        var position = $("span.eMon:visible").parent().position();
        $(".eYear").focus(), cardMsg(position.left - 13, position.top + 3, "You've entered a past date. Please check whether your credit card has expired or you've entered incorrectly."), changecc && cardMsg(position.left - 13, position.top + 3, "You've entered a past date. Please check whether your credit card has expired or you've entered incorrectly.", changecc), cardCheck = !1
    } else if (isNotEmpty($(".cvvIn").val()) && onlyNumbers.test($(".cvvIn").val())) {
        if ("" == $("select.cardSelCountry").val()) {
            var position = $("span.cardSelCountry:visible").parent().position();
            cardMsg(position.left - 13, position.top + 3, $(".cardSelCountry").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 3, $(".cardSelCountry").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".billAddress").is(":visible") && !isNotEmpty($(".billAddress").val())) {
            var position = $(".billAddress").position();
            $(".billAddress").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 51, $(".billAddress").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 51, $(".billAddress").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".usStreetAddress").is(":visible") && !isNotEmpty($(".usStreetAddress").val())) {
            var position = $(".usStreetAddress").position();
            $(".usStreetAddress").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".usStreetAddress").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".usStAddress").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".cardCity").is(":visible") && !isNotEmpty($(".cardCity").val())) {
            var position = $(".cardCity").position();
            $(".cardCity").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".cardCity").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".cardCity").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".usCardCity").is(":visible") && !isNotEmpty($(".usCardCity").val())) {
            var position = $(".usCardCity").position();
            $(".usCardCity").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".usCardCity").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".usCardCity").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".cardStateInp").is(":visible") && !isNotEmpty($(".cardStateInp").val())) {
            var position = $(".cardStateInp").position();
            $(".cardStateInp").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".cardStateInp").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".cardStateInp").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".cardStateSel").is(":visible") && 0 == $(".cardStateSel").prop("selectedIndex")) {
            var position = $("span.cardStateSel:visible").parent().position();
            $(".cardStateSel").focus(), cardMsg(position.left - 13, position.top + 3, $(".cardStateSel").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 3, $(".cardStateSel").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".cardIndStates").is(":visible") && 0 == $(".cardIndStates").prop("selectedIndex")) {
            var position = $("span.cardIndStates:visible").parent().position();
            $(".cardIndStates").focus(), cardMsg(position.left - 13, position.top + 3, $(".cardIndStates").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 3, $(".cardIndStates").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".cardZCode").is(":visible") && !isNotEmpty($(".cardZCode").val())) {
            var position = $(".cardZCode").position();
            $(".cardZCode").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".cardZCode").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".cardZCode").attr("label") + " is required", changecc), cardCheck = !1
        } else if ($(".usCardZCode").is(":visible") && !isNotEmpty($(".usCardZCode").val())) {
            var position = $(".usCardZCode").position();
            $(".usCardZCode").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".usCardZCode").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".usCardZCode").attr("label") + " is required", changecc), cardCheck = !1
        }
    } else {
        var position = $(".cvvIn").position();
        $(".cvvIn").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".cvvIn").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".cvvIn").attr("label") + " is required", changecc), cardCheck = !1
    } else $(".ccardNo").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".ccardNo").attr("label") + " is required"), changecc && cardMsg(position.left - 13, position.top + 1, $(".ccardNo").attr("label") + " is required", changecc), cardCheck = !1;
    return cardCheck
}, billingAdCheck = function() {
    {
        var addressCheck = !0;
        $(".invoice-billAddress:visible"), $(".bootstrap-select")
    }
    if ($(".companyname").is(":visible") && !isNotEmpty($(".companyname:visible").val())) {
        var position = $(".companyname:visible").position();
        $(".companyname:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".companyname").attr("label") + " is required"), addressCheck = !1
    } else if ($(".phoneNo").is(":visible") && $(".phoneNo:visible").val().length < 7) {
        var position = $(".phoneNo:visible").position();
        $(".phoneNo:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".phoneNo").attr("label") + " is not valid"), addressCheck = !1
    } else if ($(".stAddress").is(":visible") && !isNotEmpty($(".stAddress:visible").val())) {
        var position = $(".stAddress:visible").position();
        $(".stAddress:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 51, $(".stAddress").attr("label") + " is required"), addressCheck = !1
    } else if ($(".usStAddress").is(":visible") && !isNotEmpty($(".usStAddress:visible").val())) {
        var position = $(".usStAddress:visible").position();
        $(".usStAddress:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".usStAddress").attr("label") + " is required"), addressCheck = !1
    } else if ($(".city").is(":visible") && !isNotEmpty($(".city:visible").val())) {
        var position = $(".city:visible").position();
        $(".city:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".city").attr("label") + " is required"), addressCheck = !1
    } else if ($(".usCity").is(":visible") && !isNotEmpty($(".usCity:visible").val())) {
        var position = $(".usCity:visible").position();
        $(".usCity:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".usCity").attr("label") + " is required"), addressCheck = !1
    } else if ($(".stateInp").is(":visible") && !isNotEmpty($(".stateInp:visible").val())) {
        var position = $(".stateInp:visible").position();
        $(".stateInp:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".stateInp").attr("label") + " is required"), addressCheck = !1
    } else if ($(".stateSel").not(".indStates").is(":visible") && 0 == $(".stateSel").not(".indStates").prop("selectedIndex")) {
        var position = $("span.stateSel:visible").parent().position();
        $(".stateSel").focus(), cardMsg(position.left - 13, position.top + 1, $(".stateSel").attr("label") + " is required"), addressCheck = !1
    } else if ($(".stateSel.indStates").is(":visible") && 0 == $(".stateSel.indStates").prop("selectedIndex")) {
        var position = $("span.stateSel:visible").parent().position();
        $(".stateSel.indStates").focus(), cardMsg(position.left - 13, position.top + 1, $(".stateSel").attr("label") + " is required"), addressCheck = !1
    } else if ($(".paypalSelect").not(".indStates").is(":visible") && 0 == $(".paypalSelect").not(".indStates").prop("selectedIndex")) {
        var position = $("span.stateSel:visible").parent().position();
        $(".stateSel").focus(), cardMsg(position.left - 13, position.top + 1, $(".paypalSelect").attr("label") + " is required"), addressCheck = !1
    } else if ($(".paypalSelect.indStates").is(":visible") && 0 == $(".paypalSelect.indStates").prop("selectedIndex")) {
        var position = $("span.paypalSelect:visible").parent().position();
        $(".paypalSelect.indStates").focus(), cardMsg(position.left - 13, position.top + 1, $(".paypalSelect").attr("label") + " is required"), addressCheck = !1
    } else if ($(".zCode").is(":visible") && !isNotEmpty($(".zCode:visible").val())) {
        var position = $(".zCode:visible").position();
        $(".zCode:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".zCode").attr("label") + " is required"), addressCheck = !1
    } else if ($(".usZCode").is(":visible") && !isNotEmpty($(".usZCode:visible").val())) {
        var position = $(".usZCode:visible").position();
        $(".usZCode:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".usZCode").attr("label") + " is required"), addressCheck = !1
    } else if ($(".phoneNo").is(":visible") && !isNotEmpty($(".phoneNo:visible").val())) {
        var position = $(".phoneNo:visible").position();
        $(".phoneNo:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".phoneNo").attr("label") + " is required"), addressCheck = !1
    }
    return addressCheck
}, invoiceAddress = function() {
    var addressCheck = !0,
        $invoiceBillAddress = ($(".billAddress:visible"), $(".invoice-billAddress:visible")),
        $bootstrapSelect = $(".bootstrap-select");
    if ($(".invoice-billAddress").is(":visible") && "" !== $(".invoice-billAddress").val()) {
        var position = $invoiceBillAddress.position();
        $invoiceBillAddress.focus(), cardMsg(position.left - 13, position.top + 1, $invoiceBillAddress.attr("label") + " is required"), addressCheck = !1
    } else if ($(".invoice-suite").is(":visible") && "" !== $(".invoice-suite").val()) {
        var position = $(".invoice-suite").position();
        $(".invoice-suite").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".invoice-suite").attr("label") + " is required"), addressCheck = !1
    } else if ($(".invoice-city").is(":visible") && "" !== $(".invoice-city").val()) {
        var position = $(".invoice-city:visible").position();
        $(".invoice-city:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".invoice-city").attr("label") + " is required"), addressCheck = !1
    } else if ($(".invoice-stateInp").is(":visible") && "" !== $(".invoice-stateInp").val()) {
        var position = $(".invoice-stateInp:visible").position();
        $(".invoice-stateInp:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".invoice-stateInp").attr("label") + " is required"), addressCheck = !1
    } else if ($(".invoice-stateSel").is(":visible") && 0 == $(".invoice-stateSel").prop("selectedIndex")) {
        $bootstrapSelect.addClass("static");
        var position = $(".invoice-stateSel button:visible").parent("span").position();
        $(".invoice-stateSel button").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".invoice-stateSel button").attr("label") + " is required"), addressCheck = !1
    } else if ($(".invoice-zCode").is(":visible") && "" !== $(".invoice-zCode").val()) {
        var position = $(".invoice-zCode:visible").position();
        $(".invoice-zCode:visible").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".invoice-zCode").attr("label") + " is required"), addressCheck = !1
    } else if (isNotEmpty($(".companyname").val()))
        if (isNotEmpty($(".phoneNo").val())) {
            if ($(".phoneNo").val().length < 7) {
                var position = $(".phoneNo").position();
                $(".phoneNo").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".phoneNo").attr("label") + " is not valid"), addressCheck = !1
            }
        } else {
            var position = $(".phoneNo").position();
            $(".phoneNo").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".phoneNo").attr("label") + " is required"), addressCheck = !1
        }
    else {
        var position = $(".companyname").position();
        $(".companyname").focus().addClass("invalidEntry"), cardMsg(position.left - 13, position.top + 1, $(".companyname").attr("label") + " is required"), addressCheck = !1
    }
    return addressCheck ? addressCheck : void $bootstrapSelect.removeClass("static")
};
var CardTypeRegex = {
    Visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
    MasterCard: /^5[1-5][0-9]{14}$/,
    DinersClub: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
    AMEX: /^3[47][0-9]{13}$/,
    Discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
    JCB: /^35[2-8][0-9]{13}$/
}; hideAddonListTile = function() {
    0 === $(".list-Addon:visible").length ? $("tr.seperAddon").hide() : $(".list-Addon:visible").length <= 3 && $(".list-Addon:visible").length > 0 && "OA" !== StoreProperties.renderingJSON.planTypes[0].type ? ($("tr.seperAddon").show(), $("tr.addon-Tot").hide()) : "OA" !== StoreProperties.renderingJSON.planTypes[0].type && $("tr.seperAddon,tr.addon-Tot").show()
}, clearSearch = function(el) {
    if ($(el).hasClass("inputFtext")) {
        var getVal = $(el).val();
        $(el).val("").removeClass("inputFtext")
    }
    $(el).blur(function() {
        "" == $(el).val() && $(el).val(getVal).addClass("inputFtext")
    })
}, $(".viewplanBtn").click(function() {
    openOverlay()
}), openOverlay = function() {
    $("#overlay,#bodyContent").css({
        height: 800,
        overflow: "hidden"
    }), $("#bodyContent").addClass("bodyContentEffect"), $("#overlay").fadeIn(200), $(window).bind("keyup.closeOverlay", function(e) {
        27 == e.which && closeOverlay()
    })
}, closeOverlay = function() {
    $("#overlay,#bodyContent").removeAttr("style"), $("#bodyContent").removeClass("bodyContentEffect"), $(".planTitleDiv .hwinAc").show(), $(".planTitleDiv .hdDet").hide(), $("#overlay").fadeOut(200, function() {})
}, closePopUp = function(id) {
    "feedbackPop" === id && StoreProperties.selectedPlan && 0 === parseInt(StoreProperties.selectedPlan.recurringDue) && trackClickEvent("Payments - Click Downgrade To Free Cancel"), "confirmPopup" === id && $("#confirmPopup .closeTxt").hasClass("downToFree") ? ($("#message_notification, #messageinfo").hide(), $("#productupgrade").attr("src", "../../images/store/productupgrade/" + StoreProperties.serviceName + "_upgrade-screenshot.png"), $("#" + id + ",.freezeDiv").fadeOut(300), $(".closePopUp").css("visibility", "visible"), $(".managePlanAddon").hide(), $("#freeplanMsg").show()) : location.reload()
}, clsPop = function(id) {
    $("#" + id).fadeOut(100)
}, checkYearlyBenefits = function() {
    $(".ylySubscBenefits").is(":visible") && clsPop("ylySubscBenefits")
}, changeOpted = function(addOn, value) {
    var getProductAddon = StoreProperties.renderingJSON.addOn ? !0 : !1;
    if (getProductAddon)
        for (var getProductAddon = StoreProperties.renderingJSON.addOn.length, i = 0; getProductAddon > i; i++)
            if (StoreProperties.renderingJSON.addOn[i].name == addOn) {
                StoreProperties.renderingJSON.addOn[i].opted = value;
                break
            }
}, checkOpted = function(addOn, type) {
    if (type) {
        for (var plans = StoreProperties.renderingJSON.planTypes[0].plans.length, i = 0; plans > i; i++)
            if (StoreProperties.renderingJSON.planTypes[0].plans[i] == addOn) return StoreProperties.renderingJSON.planTypes[0].plans[i].opted
    } else {
        var getProductAddon = StoreProperties.renderingJSON.addOn ? !0 : !1;
        if (getProductAddon)
            for (var getProductAddon = StoreProperties.renderingJSON.addOn.length, i = 0; getProductAddon > i; i++)
                if (StoreProperties.renderingJSON.addOn[i].name == addOn) return StoreProperties.renderingJSON.addOn[i].opted
    }
}, updateAddon = function(addOn, value, UBuser, planBasedAddon) {
    var type = $('[rows="' + addOn + '"]').attr("type");
    if (null != UBuser)
        for (var PlanAddon = StoreProperties.renderingJSON.planTypes[0].plans.length, k = 0; PlanAddon > k; k++) "subscription" == type ? StoreProperties.renderingJSON.planTypes[0].plans[k].opted = JSON.parse(value) : StoreProperties.renderingJSON.planTypes[0].plans[k].value = parseInt(value);
    else if (null != planBasedAddon) {
        for (var getPlans = StoreProperties.renderingJSON.planTypes[0].plans.length, getPlansAddon = StoreProperties.renderingJSON.planTypes[0].plans[1].addOn.length, j = 0; getPlans > j; j++)
            if (null != StoreProperties.renderingJSON.planTypes[0].plans[j].addOn)
                for (var p = 0; getPlansAddon > p; p++) StoreProperties.renderingJSON.planTypes[0].plans[j].addOn[p].name == addOn && ("subscription" == type ? StoreProperties.renderingJSON.planTypes[0].plans[j].addOn[p].opted = JSON.parse(value) : StoreProperties.renderingJSON.planTypes[0].plans[j].addOn[0].value = parseInt(value))
    } else {
        var getProductAddon = StoreProperties.renderingJSON.addOn ? !0 : !1;
        if (getProductAddon)
            for (var getProductAddon = StoreProperties.renderingJSON.addOn.length, i = 0; getProductAddon > i; i++)
                if (StoreProperties.renderingJSON.addOn[i].name == addOn) {
                    "subscription" == type ? StoreProperties.renderingJSON.addOn[i].opted = JSON.parse(value) : StoreProperties.renderingJSON.addOn[i].value = value;
                    break
                }
    }
}, updatePlan = function(plan, planID) {
    if ("reports" == page) {
        var currentPlan = StoreProperties.renderingJSON.planTypes[0].selectedPlan;
        StoreProperties.renderingJSON.planTypes[0].selectedPlan = planID, "Enterprise" == currentPlan ? ($(".addonPriceChange,.priceUpdated").show(), $('[rows="Users"] td').append('<div class="gainarrow">')) : "Enterprise" == plan && ($(".addonPriceChange,.priceGain").show(), $('[rows="Users"] td').append('<div class="gainarrow">'))
    } else {
        var currentPlan = StoreProperties.renderingJSON.planTypes[0].selectedPlan;
        StoreProperties.renderingJSON.planTypes[0].selectedPlan = planID, amtDiffJSON = StoreProperties.service.checkAddonAmountDiffers(), amtDiffJSON.mismatch ? ("upgrade" === amtDiffJSON.mode ? ($(".priceUpdated #priceUpdateCont").html(amtDiffJSON.amountDiffersMsg), $("#message_notification").hide(), $(".addonPriceChange,.priceUpdated").show(), $('[rows="Users"] td').append('<div class="gainarrow">')) : "Free" === plan || "free" === plan || StoreProperties.renderingJSON.showPopUpCon ? $("#confirmDownFree .confMsg").html(amtDiffJSON.amountDiffersMsg) : ($(".priceUpdated #priceUpdateCont").html(amtDiffJSON.amountDiffersMsg), $("#message_notification").hide(), $(".addonPriceChange,.priceUpdated").show(), $('[rows="Users"] td').append('<div class="gainarrow">')), StoreProperties.amtDiffersAddons = amtDiffJSON.addons, StoreProperties.amtDiffersMismatch = !0, StoreProperties.showSuggestionInfo = amtDiffJSON.showSuggestionInfo) : (StoreProperties.amtDiffersAddons = new Array, StoreProperties.amtDiffersMismatch = !1)
    }
}, currencyAlign = function(el, totalToPay) {
    $(".currencySign").html(getCurrencySymbol(activeCurrency)), $("." + el).parent().removeAttr("style");
    var netTotalwidth = $(".addonAmt,.nNetPrc").parent().width(),
        setPriceWidth = Math.max.apply(Math, $("." + el).parent().map(function() {
            return $(this).width()
        }).get());
    if (totalToPay) {
        $("." + totalToPay).parent().removeAttr("style");
        var setPriceWidth = $("." + totalToPay).parent().width()
    }
    if (StoreProperties.subscription) {
        var setPriceWidth = setPriceWidth > netTotalwidth ? setPriceWidth : netTotalwidth;
        $("." + el + ",.addonTotal,.addOnPrice,.nNetPrc,.planPrice,.totalToPay,.totalCalculatedPrice,.discountPrice").parent().css("width", setPriceWidth)
    } else setPriceWidth > 0 && $("." + el + ",.addonTotal,.addOnPrice,.nNetPrc,.planPrice,.totalToPay,.totalCalculatedPrice").parent().css("width", setPriceWidth)
}, $.fn.digits = function() {
    return this.each(function() {
        $(this).text($(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"))
    })
}, planChartDisplay = function() {
    var planCount = $(".plansTd").length;
    if (1 == planCount) StoreProperties.renderingJSON.customPlanTitle ? $(".planTitleDiv .chPlanHead").html(i18n[StoreProperties.renderingJSON.customPlanTitle]) : $(".chPlanHead").html("Upgrade to " + $(".planName").html() + " Plan"), $(".plansTd > div").css("width", "290px"), $(".serviceAddon").width(440), $(".addonCont").addClass("twoPlanAddon"), $(".selectPlan > div").hasClass("serviceAddon") && ($(".selectPlan").addClass("singlePlan").removeClass("ib"), $(".serviceAddon").css("margin", "200px 60px 0 0"), $(".serviceAddon table").height(60));
    else if (2 == planCount) {
        $(".plansTd > div").css("width", "250px"), $(".serviceAddon").width(440), $(".plansCont").addClass("fl"), $(".addonCont").addClass("twoPlanAddon"), $(".addonHeader").addClass("addonLHeader"), $(".editionCont").removeAttr("align");
        var setplanHeight = $(".plansTd").height();
        $(".addonCont").height(setplanHeight), $(".editionCont").children().hasClass("addonCont") || $(".editionCont").attr("align", "center")
    } else 3 === planCount || 4 === planCount ? ($(".addonCont").parent().removeClass("mL20 fl"), $(".plansTd:last >div").removeClass("mR10"), $(".addonCont").addClass("pB10 block"), 4 === planCount && $(".planContainer").hasClass("NUB") && ($(".plansTd > div ").width("auto"), $(".upb-features-label").addClass("rel"), $(".upb-features").addClass("abs").css("left", "-203px", "bottom", "0"))) : planCount >= 5 && ($(".plansTd > div").css({
        width: "210px",
        "min-width": "200px"
    }), $(".addonCont").parent().removeClass("mL20 fl"), $(".plansTd:last >div").removeClass("mR10"), $(".addonCont").addClass("pB10 block"))
}, planChartMore = function() {
    $('.featureContainer:not(".UPB") ul,.featureCont ul').each(function() {
        var max = 6,
            gtmax = max - 1;
        if ($(this).find("li").length > max) {
            var featureListOpen = void 0 !== StoreProperties.renderingJSON.featureMoreMaxCount ? StoreProperties.renderingJSON.featureMoreMaxCount : !1;
            featureListOpen || $(this).find("li:gt(" + gtmax + ")").hide().end().append($('<li class="borderNone tar"><span class="fmore ib pointer"><span class="fmoreDots ib"></span><span class="fmoreDots ib"></span><span class="fmoreDots ib"></span></span></li>').click(function() {
                showallFeatures()
            }))
        } else $(".featureContainer ul li").hasClass("singlePlanFeatures") || setplanChartHeight();
        $(".featureList li:last-child").addClass("borderNone")
    })
}, showallFeatures = function() {
    $(".featureContainer li:hidden,.featureCont li:hidden").show(), $(".featureContainer ul,.featureCont ul").removeAttr("style"), $(".fmore").parent("li").hide(), setplanChartHeight(), $(".fmore").parents().hasClass("choosePlan") && ($(".freezeDiv").css("height", $(document).height()).fadeIn(100), displayFreezeDiv())
}, showInfoBanner = function(icon, content, cActionTxt1, cActionLink1, cancel, cActionTxt2, cActionLink2) {
    $(".bannerIcon").addClass(icon), $(".bannerContent").html(content), cActionTxt1 && $(".bannerCallToAction > .cAction1").text(cActionTxt1).attr("onclick", cActionLink1).removeClass("hide"), cancel && $("bannerCallToAction > .bannerCancel").removeClass("hide"), cActionTxt2 && $("bannerCallToAction > .cAction2").text(cActionTxt2).attr("onclick", cActionLink2).removeClass("hide"), $(".bannerContainer").removeClass("hide")
}, showPopUpMsg = function(icon, content, cActionTxt1, cActionLink1, cancel, cancelAction, cActionTxt2, cActionLink2) {
    var container = $("#showPopUpMsg");
    $(".popUpMsgIcon").addClass(icon), $(".popUpMsgContent").html(content), cActionTxt1 && ($(".popUpMsgCallToAction > .cAction1").text(cActionTxt1).attr("onclick", cActionLink1).removeClass("hide"), $(".popUpMsgCallToAction").removeClass("hide")), cancel ? ($(".popUpMsgCallToAction > .cAction1").removeClass("rel"), $(".popUpMsgCallToAction > .popUpMsgCancel").text(cancel).removeClass("hide").attr("onclick", cancelAction), $(".popUpMsgCallToAction").removeClass("hide")) : ($(".popUpMsgCallToAction > .popUpMsgCancel").addClass("hide"), $(".popUpMsgCallToAction > .cAction1").addClass("rel").css("left", "38%")), cActionTxt2 && $(".popUpMsgCallToAction > .cAction2").text(cActionTxt2).attr("onclick", cActionLink2).removeClass("hide"), container.removeClass("hide")
}, alignPopcontainer = function(e) {
    var docHeight = $(window).height(),
        docWidth = $(window).width(),
        elementHeight = e.height(),
        elementWidth = e.width(),
        top = (docHeight - elementHeight) / 2,
        left = (docWidth - elementWidth) / 2;
    e.css({
        top: top,
        left: left
    })
}, alignPopContLeft = function(e) {
    var docWidth = $(document).width(),
        elementWidth = e.width(),
        left = (docWidth - elementWidth) / 2;
    e.css({
        top: "20%",
        left: left
    })
}, popupPositioning = function(e) {
    alignPopContLeft(e), $(".freezeDiv").css("z-index", "999999"), e.css("z-index", "1000000")
}, $(".bannerCancel").click(function() {
    loadPlan(), $(".bannerContainer,#inlineErrorMsg").addClass("hide")
}), setplanChartHeight = function() {
    var setFeatureHeight = Math.max.apply(Math, $(".featureContainer ul,.featureCont ul").map(function() {
        return $(this).height()
    }).get());
    $(".featureContainer").animate({
        height: setFeatureHeight + "px"
    }, 200).end().remove()
}, setPlanHeight = function() {
    var setPlanHeight = Math.max.apply(Math, $("ul.featureList").map(function() {
        return $(this).height()
    }).get());
    601 !== StoreProperties.serviceId && 4601 !== StoreProperties.serviceId && $(".featureList").parent().css("height", setPlanHeight + 15)
}, setHeaderHeight = function() {
    var setHeaderHeight = Math.max.apply(Math, $(".etitle").map(function() {
        return $(this).height()
    }).get());
    $(".etitle").animate({
        height: setHeaderHeight + 10 + "px"
    }, 200)
}, setPanelHeight = function() {
    var footer = parseInt($(window).innerHeight()) - (parseInt($("#hp").outerHeight()) + 210);
    $(".contentPanel,.cofirmMsgContainer,#bp,.cofirmMsgDiv").css("min-height", footer)
}, $(document).ready(setPanelHeight), $(window).resize(setPanelHeight), $(document).click(function(e) {
    1 === e.which && $(".choosePlan,.hisSort,.dtSort").is(":visible") && ($(".choosePlan,.freezeDiv,.hisSort,.dtSort").fadeOut(300), $("#pageContainer").hasClass("scaleIn") && $("#pageContainer").removeClass("scaleIn"))
}), hidePlanPopUp = function() {
    $(".choosePlan,.offlinetoonline").is(":visible") && ($(".choosePlan,.freezeDiv,.offlinetoonline").fadeOut(300), $("#pageContainer").removeClass("scaleIn"), planChartMore())
}, $(document).keyup(function(e) {
    27 === e.keyCode && hidePlanPopUp()
}), payerAuthenticationAgreementCheck = function(changecc) {
    var inpElement = $("#payer-authentication-container #3dsecure"),
        labelElement = $("#payer-authentication-container label[for='3dsecure']");
    if ($(labelElement).is(":visible") && !$(inpElement).is(":checked")) {
        $(inpElement).addClass("invalidEntry"), $("#payer-authentication-container label[for='3dsecure']").focus();
        var position = $(labelElement).position(),
            top = $(labelElement).hasClass("mT20") ? position.top + 15 : position.top - 5;
        return changecc ? cardMsg(position.left - 25, top, i18n["zohostore.agree.inr.3dsecure"], changecc) : cardMsg(position.left - 28, top, i18n["zohostore.agree.inr.3dsecure"]), !1
    }
    return !0
}; $.extend({
    setNextDueDate: function(payPeriod) {
        var nextDueDate = null;
        "YEAR" == payPeriod ? (StoreProperties.selectedPlan.payPeriod = "YEAR", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("years", 1).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate) : "SMYR" == payPeriod ? (StoreProperties.selectedPlan.payPeriod = "SMYR", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("months", 6).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate) : "QTER" == payPeriod ? (StoreProperties.selectedPlan.payPeriod = "QTER", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("months", 3).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate) : (StoreProperties.selectedPlan.payPeriod = "MONT", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("months", 1).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate)
    },
    resetBillingDetails: function() {
        $(".subscriptionGBtn").val("Continue");
    },
    fetchDetails: function(ary, index) {
        return ary[index]
    },
    loadpaypalDetails: function(index) {
        var paypalbaids = StoreProperties.renderingJSON.paypalbaids;
        $.fillData($.fetchDetails(paypalbaids, index))
    },
    fillData: function(details) {
        var nextrenewaldate, email = details.paypal_email,
            baid = details.baid,
            curency = details.currency_symbol,
            servicedetails = details.service_details;
        StoreProperties.clonebaid = details.baid, StoreProperties.cloneDetails.payperiod = StoreProperties.selectedPlan.payPeriod, StoreProperties.cloneDetails.next_payment_date = StoreProperties.selectedPlan.nextDueDate, nextrenewaldate = moment(StoreProperties.selectedPlan.nextDueDate).format("DD MMM YYYY"), $.each(servicedetails, function(index, value) {
            return StoreProperties.cloneCard = !0, value.payperiod === StoreProperties.selectedPlan.payPeriod ? (StoreProperties.cloneProfileId = value.profile_id, StoreProperties.selectedPlan.nextDueDate = value.next_payment_date, StoreProperties.cloneDetails.next_payment_date = value.next_payment_date, nextrenewaldate = value.display_next_payment_date, !1) : void 0
        }), $(".paywith-ppacc #ppemail").html(email), $(".paywith-ppacc #ppdaid").html(baid), $(".nxt-renewal-on #nxt-renewal-date").html(nextrenewaldate), $(".pro-rate #amountsymble").html(curency);
        var dueNow = StoreUtil.prorate(StoreProperties.selectedPlan);
        $(".pro-rate #pro-data-amt").html(formatAmount(dueNow.amount)), StoreProperties.selectedPlan.dueNow = dueNow.amount
    }
}), $("body").on("change", "#choosepaypal", function() {
    var selectedoption = $("#choosepaypal option:selected"),
        selectedaccount = selectedoption.attr("account"),
        index = selectedoption.attr("currentpaypal");
    "addaccount" === selectedaccount ? ($(".paypalDetails").removeClass("hide"), $(".paypalContentTitle").html(i18n["zohostore.newsubscription.addaccount"]).removeClass("mT35"), $(".pp-baid-details").addClass("hide"), $.resetBillingDetails()) : void 0 === selectedaccount || null === selectedaccount || "" === selectedaccount ? ($(".paypalDetails").addClass("hide"), $(".pp-baid-details").addClass("hide"), $.resetBillingDetails()) : ($.resetBillingDetails(), $(".subscriptionGBtn").val("Make Payment"), $(".paypalDetails").addClass("hide"), $(".pp-baid-details").removeClass("hide"), $.loadpaypalDetails(index))
});
var discountObj = {};
var payer_authentication;
$("body").on("mouseenter", ".payer-authentication", function() {
    authentication = setTimeout(function() {
        $(".payer-authentication-description").fadeIn(300)
    }, 400)
}).on("mouseleave", ".payer-authentication,.payer-authentication-description", function() {
    clearTimeout(authentication), $(".payer-authentication-description").fadeOut()
});
var about_payer_authentication;
$("body").on("mouseenter", ".about-payer-authentication", function() {
    aboutauthentication = setTimeout(function() {
        $(".about-payer-authentication-description").fadeIn(300)
    }, 400)
}).on("mouseleave", ".about-payer-authentication,.about-payer-authentication-description", function() {
    clearTimeout(aboutauthentication), $(".about-payer-authentication-description").fadeOut()
}), $(document).on("keydown", ".headslide #searchbox", function(e) {
    9 == e.which && e.preventDefault()
}), $(document).on({
    mouseenter: function() {
        var tPosition = $(".featureContainer .planFeaturesPlus").position(),
            lPosition = $(this).position(),
            splitHeight = $(".featuresSplit").is(":visible") ? -30 : -27;
        $("#freeplan").removeClass("hide").css({
            top: tPosition.top + splitHeight,
            left: lPosition.left + 30
        })
    },
    mouseleave: function() {
        $("#freeplan").addClass("hide")
    }
}, "#showFreeFeat");
var salesToolFreeNo = getSalesToolFreeNo();
if ($(".salesToolFree").html(salesToolFreeNo), $(".dropdown-menu input, .dropdown-menu label").click(function() {
        $(".dropup").removeClass("open")
    }), $("body").on("click", ".multiAddonSelection,.multiAddonReduceSelection", function() {
        $(".bootstrap-select.open").removeClass("open")
    }), $(document).on("click", ".reviewPay", function() {
        $(".confTitle").text(i18n["zohostore.newsubscription.confirmorder"]).addClass("mT10"), $(".freezeDiv").removeClass("manageplanFreeze"), $(".confTitleMsg").hide(), $(".multiAddonSelection,#hconfClose").hide(), $(".reviewAndPay").show(), $("html, body").animate({
            scrollTop: 60
        }, 100)
    }), $("input.cardInpt").on("blur", function() {
        $(this).removeClass("invalidEntry")
    }), $.fn.spin = function(opts) {
        return this.each(function() {
            var $this = $(this),
                data = $this.data();
            data.spinner && (data.spinner.stop(), delete data.spinner), opts !== !1 && (window.s = data.spinner = new Spinner($.extend({
                color: $this.css("color")
            }, opts)).spin(this))
        }), this
    }, buttonPreLoader = function() {
        var opts = {
            lines: 9,
            width: 4,
            scale: 1,
            radius: 4.7,
            corners: 1,
            length: 0,
            opacity: 0,
            color: "#fff",
            left: "auto"
        };
        $(".primaryButton-loader").spin(opts)
    }, $(document).ready(function() {
        "true" === sessionStorage.getItem("changeBillingDetails") && setTimeout(function() {
            scrollToDiv("billing-address-container", 0), editBillingAddress(), sessionStorage.removeItem("changeBillingDetails")
        }, 1e3), alignPopcontainer(alignLoading)
    }), $(function() {
        $.fn.textWidth = function() {
            var _text = $(this),
                _textValue = _text.html();
            "INPUT" == _text[0].nodeName ? _textValue = _text.val() : "SELECT" == _text[0].nodeName && (_textValue = _text.val());
            var _textContainer = "<span>" + _textValue + "</span>";
            $("body").append(_textContainer);
            var _textSpan = $("span").last();
            _textSpan.css({
                "font-size": _text.css("font-size"),
                "font-family": _text.css("font-family"),
                "font-weight": _text.css("font-weight"),
                "font-style": _text.css("font-style")
            });
            var width = _textSpan.width() + 5;
            return _textSpan.remove(), width
        }
    }));

var naviUser = navigator.userAgent,
    planType = StoreProperties.renderingJSON.planTypes[0].type,
    servId = StoreProperties.serviceId;
    $(document).ready(function() {
    var selPayPeriod = StoreProperties.renderingJSON.planTypes[0].selectedPayPeriod;
    $(".payPeriod").text("monthly" === selPayPeriod ? i18n["zohostore.pricing.frequency.month"] : i18n["zohostore.pricing.frequency.year"]), serviceAddonList();
    var items = [];
    $.each(StoreProperties.countryJSON, function(i, val) {
        "IN" != val.countryCode ? items.push("<option value='" + val.countryName + "' country-code='" + val.countryCode + "' country-name='" + val.countryName + "' dial-code='" + val.dialCode + "' data-icon='glyphicon flag-" + val.countryCode.toLowerCase() + "'>" + val.countryName + "</option>") : (items.push("<option value='" + val.countryName + "' country-code='" + val.countryCode + "' country-name='" + val.countryName + "' dial-code='" + val.dialCode + "' data-icon='glyphicon flag-" + val.countryCode.toLowerCase() + "'>" + val.countryName + "</option>"), items.push("<option data-divider='true' disabled></option>"))
    }), $(".stepCount").text($(".planNos:visible").length), $("<select/>", {
        html: items,
        "class": "selectBoxIt cardInpt p2 cardSelCountry w270 cardSelectCountry selectCountry",
        label: "Country",
        name: "cardCountry",
        mandatory: "mandatory",
        maxlength: "50",
        "data-size": "10"
    }).appendTo("#cardTable .countryTd"), $("<select/>", {
        html: items,
        "class": "selectBoxIt cardInpt p2 selCountry w270 selectCountry invoice-billing-country",
        label: "Country",
        name: "country",
        mandatory: "mandatory",
        maxlength: "50",
        "data-size": "10"
    }).appendTo("#billAddrTable .countryTd"), $("<select/>", {
        html: items,
        "class": "selectBoxIt cardInpt p2 selCountry w270 selectCountry invoice-billing-country",
        label: "Country",
        name: "country",
        mandatory: "mandatory",
        maxlength: "50",
        "data-size": "10"
    }).appendTo("#paypal-billAddrTable .countryTd"), setCurrencyPicker(), $(".currencycontainer").is(":visible") && ($(".currencycontainer .taggleDiv").removeClass("active"), $(".currencycontainer [currency='" + activeCurrency + "']").addClass("active")), $(".currencySign").html(getCurrencySymbol(activeCurrency)), "zohoeu" === StoreProperties.paymentsServer && $(".crmplusCurrency").html(getCurrencySymbol(activeCurrency)), $(".currencySign").removeClass("inrupee"), "USD" === activeCurrency ? ($(".abtSubscriptionMsg").html(i18n["zohostore.newsubscription.aboutsubscription.brief.description.dollar"]), togglePaymentDetails("show")) : "INR" === activeCurrency ? ($(".abtSubscriptionMsg").html(i18n["zohostore.newsubscription.aboutsubscription.brief.description.inr"]), $(".currencySign").addClass("inrupee"), togglePaymentDetails("hide")) : ($(".abtSubscriptionMsg").html(i18n["zohostore.newsubscription.aboutsubscription.brief.description.dollar"]), togglePaymentDetails("hide"));
    var alignLoading = $(".loadingMsg");
    alignPopcontainer(alignLoading)
}), $("#freeplan").css({
    top: $(".etitle").height() + $("#showFreeFeat").height() + 5,
    left: $(".featureList").width() - 20
}); 
var swprice = $(".singlePlanUpgrade").attr("price"),
    swprice1 = $(".pprice").html();
var flow = window.location.href.split("?Flow=")[1];

var OptedTrialPlanId, OptedTrial = 0,
    userTrials = StoreProperties.userTrial,
    notifyTrial = !0;
if (void 0 != StoreProperties.plans && null != StoreProperties.serviceJSON.TrialOpt && "NA" != StoreProperties.serviceJSON.TrialOpt) {
    var trialPlans = StoreProperties.plans.TrialPlans,
        trialPlanId = null;
    if (null != userTrials && ($.each(userTrials, function(key, value) {
            for (var i = 0; i < trialPlans.length; ++i)
                if (key == trialPlans[i] && 0 == value.trialstatus) {
                    trialPlanId = key;
                    break
                }
        }), null != trialPlanId && (2201 !== parseInt(StoreProperties.serviceId) && 4501 !== parseInt(StoreProperties.serviceId) || null === StoreProperties.licenseInfo || !StoreProperties.licenseInfo.activeUsers || (OptedTrialPlanId = StoreProperties.plans.TrialPlansMap[trialPlanId], OptedTrial = StoreProperties.licenseInfo.activeUsers), parseInt(userTrials[trialPlanId].trialremainingdays) < 0 || "NV" === StoreProperties.serviceJSON.TrialOpt ? notifyTrial = !1 : ($(".announce_shdr #edition").text(userTrials[trialPlanId].trialname), $(".announce_shdr #amsg").text("0" === userTrials[trialPlanId].trialremainingdays ? i18n["store.trial.expires.today.message"] : i18n["store.complete.trial.message"].replace("{0}", userTrials[trialPlanId].trialremainingdays))), notifyTrial)))
        if (1 === StoreProperties.serviceId) {
            $("#messageinfo").hide();
            var href = window.location.href,
                queryStr = null;
            if (-1 !== href.indexOf("?") && -1 !== href.indexOf("#")) {
                queryStr = href.split("?")[1], bugtracker = -1 !== queryStr.indexOf("bugtracker=") ? queryStr.split("bugtracker=")[1].split("&")[0] : "false";
                var addId = userTrials[trialPlanId].addonId;
                "true" === bugtracker && -1 !== addId ? $("#messageinfo").show() : "false" === bugtracker && -1 === addId && $("#messageinfo").show()
            }
        } else $("#messageinfo").show()
}
$(".payment-menu").click(function() {
    $this = $(this), $this.hasClass("payment-active") || $.resetBillingDetails(), $this.addClass("payment-active").siblings(".payment-menu").removeClass("payment-active");
    var selTab = $this.attr("tab");
    $(".ccardDiv").find(".payment-method").hide(), $(".ccardDiv").find("#" + selTab).fadeIn(300), "ccardContainer" == $this.attr("tab") ? ($(".safeSecure i").removeClass("payPalLogo").addClass("pciLogo"), $(".safeSecureMsg").html(i18n["zohostore.newsubscription.safeandsecured.paymentgateway"]), $(".subscriptionGBtn").attr("id", "makepayment").removeClass("payPalContinue").val("Make Payment"), $(".ccardNo").removeClass("invalidEntry").focus(), $("#paypal-billAddrTable").hide(), $(".invoicemsg .used-card").text(i18n["zohostore.newsubscription.creditcard.account.shipping"]), $("#card-address").attr("checked", "checked"), changeInvoiceAddressOption(), checkInvoiceBillingAddress("billAddrTable"), StoreProperties.cards ? ($(".subscriptionGBtn").attr("selectedcard", $("#cardContainer #chooseccard").val()), $("#cardContainer #chooseccard").trigger("change")) : $(".subscriptionGBtn").attr("selectedcard", "newcard")) : "paypalContainer" == $this.attr("tab") ? ($("#choosepaypal").prop("selectedIndex", 0).change(), $(".safeSecure i").removeClass("pciLogo").addClass("payPalLogo"), $(".invoicemsg .used-card").text(i18n["zohostore.newsubscription.paypal.account.shipping"]), StoreProperties.renderingJSON.paypalbaids && void 0 !== StoreProperties.renderingJSON.paypalbaids || $(".paypalDetails").removeClass("hide"), $("#paypal-address").attr("checked", "checked"), $(".billing-address-split").removeClass("mT200"), $(".choose-existing-ccard li:first-child a").trigger("click"), $(".safeSecureMsg").html(i18n["zohostore.newsubscription.safeandsecured.payments"]), $("#paypal-billAddrTable").show(), checkInvoiceBillingAddress("paypal-billAddrTable"), $(".subscriptionGBtn").removeClass("ccCheck").addClass("payPalContinue").val("Continue"), $(".subscriptionGBtn").attr("id", "paypalbutton").removeAttr("selectedcard"), populateIPInfo(StoreProperties.ipInfo), changeInvoiceAddressOption(), $(".subscriptionGBtn").removeAttr("profile"), StoreProperties.cloneCard = !1, delete StoreProperties.cloneProfileId, StoreProperties.cards && ($(".safeSecure,#contBtnSplit").show(), $("#makepayment,#paypalbutton").addClass("fr"), $("#contBtnTable").removeClass("mB20")),
        //StoreProperties.service.setDueNow(),
        trackClickEvent("Payments - Click - Use Paypal")) : "poContainer" == $this.attr("tab") && ($(".safeSecure i").removeClass("pciLogo").removeClass("payPalLogo"), $(".choose-existing-ccard li:first-child a").trigger("click"), $(".subscriptionGBtn").removeClass("ccCheck payPalContinue").addClass("purchaseOrderContinue").val("Submit"), $(".subscriptionGBtn").removeAttr("profile"), $(".safeSecure").hide(), $(".subscriptionGBtn").attr("id", "purchaseOrder").removeAttr("selectedcard"), StoreProperties.service.setDueNow())
}), $(".confirmContainer").on("click", ".confirmOrder", function() {
    $("#aboutSubMsg .abtSubscriptionMsg").html("credit" === StoreProperties.purchaseType ? i18n["zohostore.newsubscription.aboutsubscription.brief.onetime.description1"] : i18n["zohostore.newsubscription.aboutsubscription.brief.description1"]), trackClickEvent("Payments - Click - Confirm Order"), $(".prices_in").text(i18n["zohostore.prices.in." + StoreProperties.currencyCode.toLowerCase()]), setPaymentMethod(), loadSupportedCards(), scrollToDiv("confirmOrderTitle", 0), $(".confirmOrderTitle").addClass("peNone"), setTimeout(function() {
        $(".ccardNo").focus()
    }, 500), StoreProperties.ipInfo ? populateIPInfo(StoreProperties.ipInfo) : getIPInfo(), checkInvoiceBillingAddress("billAddrTable")
});
var aboutBillingTimer;
$("body").on("mouseenter", ".about-billing-address", function() {
        aboutBillingTimer = setTimeout(function() {
            $(".billing-details-pop").fadeIn(300)
        }, 400)
    }).on("mouseleave", ".about-billing-address,.billing-details-pop", function() {
        clearTimeout(aboutBillingTimer), $(".billing-details-pop").fadeOut()
    }), $("body").on("mouseenter", ".nxt-renewal-on .aboutIcn,.pro-rate .aboutIcn", function() {
        var ele = $(this);
        tooltipPopup = setTimeout(function() {
            $(ele).find(".pop-hover").fadeIn(300)
        }, 400)
    }).on("mouseleave", ".nxt-renewal-on .aboutIcn,.pro-rate .aboutIcn,.pop-hover", function() {
        clearTimeout(tooltipPopup), $(".pop-hover").fadeOut()
    }), $(".selectPlan").on("hover", ".featuresValue li", function() {
        var el = $(this).index();
        $(".featuresValue,.featureListT1").find("li:eq(" + el + ")").addClass("upb-features-hover")
    }).on("mouseout", function() {
        $(".featuresValue,.featureListT1").find("li").removeClass("upb-features-hover")
    }), placeSecondaryLicense = function() {
        var secondaryLicense = $("ul.secondaryLicense").find("input:checked").attr("licenses");
        $("#planUnderList .optedLicense").html(secondaryLicense)
    }, secondaryLicense = function() {
        var secondaryLicense = $("ul.secondaryLicense").find("input:checked");
        if (secondaryLicense.length) {
            var licenses = secondaryLicense.attr("licenses"),
                price = secondaryLicense.attr("price");
            return $(".secondary-license-upgrade").attr("price", price), $(".choose-secondary-license option[licenses=" + licenses + "]").attr("selected", "selected").change(), $(".confirmOrderTitle").hasClass("peNone") && $(".confirmOrderTitle").removeClass("peNone"), !0
        }
        var label = $("ul.secondaryLicense").attr("label"),
            content = "Please select no. of " + label + " and upgrade.",
            appendTo = $(".plansCont");
        return $(".block-notification-container").length || blockNotificationMsg(appendTo, content), !1
    }, $(".selectPlan").on("mouseenter", ".secondaryLicense li", function() {
        var hover = $(this).attr("class");
        $(".primaryLicense li." + hover).addClass("primaryLicense-hover")
    }).on("mouseleave", ".secondaryLicense li", function() {
        var hover = $(this).attr("class");
        $(".primaryLicense li." + hover).removeClass("primaryLicense-hover")
    }), $(".selectPlan").on("click", ".secondaryLicense input", function() {
        var element = $(this).attr("labeled");
        $(".secondaryLicense li,.primaryLicense li").removeClass("bgf3 primaryLicense-hover"), $("." + element).addClass("bgf3"), $(".upgradeBtn.secondary-license-upgrade").attr("planid", $(this).attr("plan")), $(this).attr("secondary_planid") && $(".upgradeBtn.secondary-license-upgrade").attr("secondary_planid", $(this).attr("secondary_planid"))
    }), $(".addonContainer").on("click", ".participants-list li,.choose-secondary-license", function() {
        var $this = $(this),
            selPlanId = $this.attr("planid"),
            price = ($this.parent().attr("discount"), $this.attr("price")),
            $addonList = $(".addonsList[addonid=" + selPlanId + "]");
        $(".cPlanInpPrice").attr({
            planid: selPlanId,
            plan: selPlanId,
            addonid: $this.attr("addonid"),
            price: price
        }), $(".cPlanPrice").attr({
            planid: selPlanId,
            addonid: $this.attr("addonid")
        }), $(".planDetailAddon").attr({
            addonid: $this.attr("addonid")
        }), StoreProperties.service.setPlan("upgradePlan", selPlanId), $addonList.find(".detailsAddOnPrice,.detailsAddOnInput").text(price).attr("price", price), $(".cPlanPrice").html(price);
        var $licenseInput = $(".cPlanInpPrice");
        $licenseInput.val() && $(".cPlanInpPrice").keyup(), $(".planDetails").is(":visible") && ($addonList.find(".addon-details-price").addClass("highlightBG"), animateFeatures());
        var selectWidh = $this.textWidth();
        $this.css("width", selectWidh + 5)
    }), $(".subscriptionDiv").on("click", "ul.secondaryLicense input", function() {
        $(".block-notification-container").fadeTo(100, 0).slideUp(150)
    }), openDrop = function() {
        $(".participants-list").removeClass("hide"), $(".participants-count").addClass("before-arrow"), $(".seconday-license-unit").css("padding-left", "14px")
    }, closedrop = function() {
        $(".participants-count").removeClass("before-arrow"), $(".participants-list").addClass("hide"), $(".seconday-license-unit").removeAttr("style")
    }, $(".addonContainer").on("click", ".participants-count", function(event) {
        var $participantsContainer = $(".participants-dropdown"),
            $participantsDropdown = $participantsContainer.find("ul"),
            $participantsList = $participantsContainer.find("li");
        $participantsDropdown.hasClass("hide") ? openDrop() : closedrop(), $participantsList.on("click", function() {
            var selectedLicenses = $(this).attr("licenses"),
                price = $(this).attr("price");
            $(".participants-count").html(selectedLicenses).attr({
                price: price,
                licenses: selectedLicenses
            }), closedrop()
        }), $(document).on("click", function() {
            closedrop()
        }), event.stopImmediatePropagation()
    }),
    function(a) {
        function b(a, b, c) {
            switch (arguments.length) {
                case 2:
                    return null != a ? a : b;
                case 3:
                    return null != a ? a : null != b ? b : c;
                default:
                    throw new Error("Implement me")
            }
        }

        function c(a, b) {
            return Bb.call(a, b)
        }

        function d() {
            return {
                empty: !1,
                unusedTokens: [],
                unusedInput: [],
                overflow: -2,
                charsLeftOver: 0,
                nullInput: !1,
                invalidMonth: null,
                invalidFormat: !1,
                userInvalidated: !1,
                iso: !1
            }
        }

        function e(a) {
            vb.suppressDeprecationWarnings === !1 && "undefined" != typeof console && console.warn && console.warn("Deprecation warning: " + a)
        }

        function f(a, b) {
            var c = !0;
            return o(function() {
                return c && (e(a), c = !1), b.apply(this, arguments)
            }, b)
        }

        function g(a, b) {
            sc[a] || (e(b), sc[a] = !0)
        }

        function h(a, b) {
            return function(c) {
                return r(a.call(this, c), b)
            }
        }

        function i(a, b) {
            return function(c) {
                return this.localeData().ordinal(a.call(this, c), b)
            }
        }

        function j(a, b) {
            var c, d, e = 12 * (b.year() - a.year()) + (b.month() - a.month()),
                f = a.clone().add(e, "months");
            return 0 > b - f ? (c = a.clone().add(e - 1, "months"), d = (b - f) / (f - c)) : (c = a.clone().add(e + 1, "months"), d = (b - f) / (c - f)), -(e + d)
        }

        function k(a, b, c) {
            var d;
            return null == c ? b : null != a.meridiemHour ? a.meridiemHour(b, c) : null != a.isPM ? (d = a.isPM(c), d && 12 > b && (b += 12), d || 12 !== b || (b = 0), b) : b
        }

        function l() {}

        function m(a, b) {
            b !== !1 && H(a), p(this, a), this._d = new Date(+a._d), uc === !1 && (uc = !0, vb.updateOffset(this), uc = !1)
        }

        function n(a) {
            var b = A(a),
                c = b.year || 0,
                d = b.quarter || 0,
                e = b.month || 0,
                f = b.week || 0,
                g = b.day || 0,
                h = b.hour || 0,
                i = b.minute || 0,
                j = b.second || 0,
                k = b.millisecond || 0;
            this._milliseconds = +k + 1e3 * j + 6e4 * i + 36e5 * h, this._days = +g + 7 * f, this._months = +e + 3 * d + 12 * c, this._data = {}, this._locale = vb.localeData(), this._bubble()
        }

        function o(a, b) {
            for (var d in b) c(b, d) && (a[d] = b[d]);
            return c(b, "toString") && (a.toString = b.toString), c(b, "valueOf") && (a.valueOf = b.valueOf), a
        }

        function p(a, b) {
            var c, d, e;
            if ("undefined" != typeof b._isAMomentObject && (a._isAMomentObject = b._isAMomentObject), "undefined" != typeof b._i && (a._i = b._i), "undefined" != typeof b._f && (a._f = b._f), "undefined" != typeof b._l && (a._l = b._l), "undefined" != typeof b._strict && (a._strict = b._strict), "undefined" != typeof b._tzm && (a._tzm = b._tzm), "undefined" != typeof b._isUTC && (a._isUTC = b._isUTC), "undefined" != typeof b._offset && (a._offset = b._offset), "undefined" != typeof b._pf && (a._pf = b._pf), "undefined" != typeof b._locale && (a._locale = b._locale), Kb.length > 0)
                for (c in Kb) d = Kb[c], e = b[d], "undefined" != typeof e && (a[d] = e);
            return a
        }

        function q(a) {
            return 0 > a ? Math.ceil(a) : Math.floor(a)
        }

        function r(a, b, c) {
            for (var d = "" + Math.abs(a), e = a >= 0; d.length < b;) d = "0" + d;
            return (e ? c ? "+" : "" : "-") + d
        }

        function s(a, b) {
            var c = {
                milliseconds: 0,
                months: 0
            };
            return c.months = b.month() - a.month() + 12 * (b.year() - a.year()), a.clone().add(c.months, "M").isAfter(b) && --c.months, c.milliseconds = +b - +a.clone().add(c.months, "M"), c
        }

        function t(a, b) {
            var c;
            return b = M(b, a), a.isBefore(b) ? c = s(a, b) : (c = s(b, a), c.milliseconds = -c.milliseconds, c.months = -c.months), c
        }

        function u(a, b) {
            return function(c, d) {
                var e, f;
                return null === d || isNaN(+d) || (g(b, "moment()." + b + "(period, number) is deprecated. Please use moment()." + b + "(number, period)."), f = c, c = d, d = f), c = "string" == typeof c ? +c : c, e = vb.duration(c, d), v(this, e, a), this
            }
        }

        function v(a, b, c, d) {
            var e = b._milliseconds,
                f = b._days,
                g = b._months;
            d = null == d ? !0 : d, e && a._d.setTime(+a._d + e * c), f && pb(a, "Date", ob(a, "Date") + f * c), g && nb(a, ob(a, "Month") + g * c), d && vb.updateOffset(a, f || g)
        }

        function w(a) {
            return "[object Array]" === Object.prototype.toString.call(a)
        }

        function x(a) {
            return "[object Date]" === Object.prototype.toString.call(a) || a instanceof Date
        }

        function y(a, b, c) {
            var d, e = Math.min(a.length, b.length),
                f = Math.abs(a.length - b.length),
                g = 0;
            for (d = 0; e > d; d++)(c && a[d] !== b[d] || !c && C(a[d]) !== C(b[d])) && g++;
            return g + f
        }

        function z(a) {
            if (a) {
                var b = a.toLowerCase().replace(/(.)s$/, "$1");
                a = lc[a] || mc[b] || b
            }
            return a
        }

        function A(a) {
            var b, d, e = {};
            for (d in a) c(a, d) && (b = z(d), b && (e[b] = a[d]));
            return e
        }

        function B(b) {
            var c, d;
            if (0 === b.indexOf("week")) c = 7, d = "day";
            else {
                if (0 !== b.indexOf("month")) return;
                c = 12, d = "month"
            }
            vb[b] = function(e, f) {
                var g, h, i = vb._locale[b],
                    j = [];
                if ("number" == typeof e && (f = e, e = a), h = function(a) {
                        var b = vb().utc().set(d, a);
                        return i.call(vb._locale, b, e || "")
                    }, null != f) return h(f);
                for (g = 0; c > g; g++) j.push(h(g));
                return j
            }
        }

        function C(a) {
            var b = +a,
                c = 0;
            return 0 !== b && isFinite(b) && (c = b >= 0 ? Math.floor(b) : Math.ceil(b)), c
        }

        function D(a, b) {
            return new Date(Date.UTC(a, b + 1, 0)).getUTCDate()
        }

        function E(a, b, c) {
            return jb(vb([a, 11, 31 + b - c]), b, c).week
        }

        function F(a) {
            return G(a) ? 366 : 365
        }

        function G(a) {
            return a % 4 === 0 && a % 100 !== 0 || a % 400 === 0
        }

        function H(a) {
            var b;
            a._a && -2 === a._pf.overflow && (b = a._a[Db] < 0 || a._a[Db] > 11 ? Db : a._a[Eb] < 1 || a._a[Eb] > D(a._a[Cb], a._a[Db]) ? Eb : a._a[Fb] < 0 || a._a[Fb] > 24 || 24 === a._a[Fb] && (0 !== a._a[Gb] || 0 !== a._a[Hb] || 0 !== a._a[Ib]) ? Fb : a._a[Gb] < 0 || a._a[Gb] > 59 ? Gb : a._a[Hb] < 0 || a._a[Hb] > 59 ? Hb : a._a[Ib] < 0 || a._a[Ib] > 999 ? Ib : -1, a._pf._overflowDayOfYear && (Cb > b || b > Eb) && (b = Eb), a._pf.overflow = b)
        }

        function I(b) {
            return null == b._isValid && (b._isValid = !isNaN(b._d.getTime()) && b._pf.overflow < 0 && !b._pf.empty && !b._pf.invalidMonth && !b._pf.nullInput && !b._pf.invalidFormat && !b._pf.userInvalidated, b._strict && (b._isValid = b._isValid && 0 === b._pf.charsLeftOver && 0 === b._pf.unusedTokens.length && b._pf.bigHour === a)), b._isValid
        }

        function J(a) {
            return a ? a.toLowerCase().replace("_", "-") : a
        }

        function K(a) {
            for (var b, c, d, e, f = 0; f < a.length;) {
                for (e = J(a[f]).split("-"), b = e.length, c = J(a[f + 1]), c = c ? c.split("-") : null; b > 0;) {
                    if (d = L(e.slice(0, b).join("-"))) return d;
                    if (c && c.length >= b && y(e, c, !0) >= b - 1) break;
                    b--
                }
                f++
            }
            return null
        }

        function L(a) {
            var b = null;
            if (!Jb[a] && Lb) try {
                b = vb.locale(), require("./locale/" + a), vb.locale(b)
            } catch (c) {}
            return Jb[a]
        }

        function M(a, b) {
            var c, d;
            return b._isUTC ? (c = b.clone(), d = (vb.isMoment(a) || x(a) ? +a : +vb(a)) - +c, c._d.setTime(+c._d + d), vb.updateOffset(c, !1), c) : vb(a).local()
        }

        function N(a) {
            return a.match(/\[[\s\S]/) ? a.replace(/^\[|\]$/g, "") : a.replace(/\\/g, "")
        }

        function O(a) {
            var b, c, d = a.match(Pb);
            for (b = 0, c = d.length; c > b; b++) d[b] = rc[d[b]] ? rc[d[b]] : N(d[b]);
            return function(e) {
                var f = "";
                for (b = 0; c > b; b++) f += d[b] instanceof Function ? d[b].call(e, a) : d[b];
                return f
            }
        }

        function P(a, b) {
            return a.isValid() ? (b = Q(b, a.localeData()), nc[b] || (nc[b] = O(b)), nc[b](a)) : a.localeData().invalidDate()
        }

        function Q(a, b) {
            function c(a) {
                return b.longDateFormat(a) || a
            }
            var d = 5;
            for (Qb.lastIndex = 0; d >= 0 && Qb.test(a);) a = a.replace(Qb, c), Qb.lastIndex = 0, d -= 1;
            return a
        }

        function R(a, b) {
            var c, d = b._strict;
            switch (a) {
                case "Q":
                    return _b;
                case "DDDD":
                    return bc;
                case "YYYY":
                case "GGGG":
                case "gggg":
                    return d ? cc : Tb;
                case "Y":
                case "G":
                case "g":
                    return ec;
                case "YYYYYY":
                case "YYYYY":
                case "GGGGG":
                case "ggggg":
                    return d ? dc : Ub;
                case "S":
                    if (d) return _b;
                case "SS":
                    if (d) return ac;
                case "SSS":
                    if (d) return bc;
                case "DDD":
                    return Sb;
                case "MMM":
                case "MMMM":
                case "dd":
                case "ddd":
                case "dddd":
                    return Wb;
                case "a":
                case "A":
                    return b._locale._meridiemParse;
                case "x":
                    return Zb;
                case "X":
                    return $b;
                case "Z":
                case "ZZ":
                    return Xb;
                case "T":
                    return Yb;
                case "SSSS":
                    return Vb;
                case "MM":
                case "DD":
                case "YY":
                case "GG":
                case "gg":
                case "HH":
                case "hh":
                case "mm":
                case "ss":
                case "ww":
                case "WW":
                    return d ? ac : Rb;
                case "M":
                case "D":
                case "d":
                case "H":
                case "h":
                case "m":
                case "s":
                case "w":
                case "W":
                case "e":
                case "E":
                    return Rb;
                case "Do":
                    return d ? b._locale._ordinalParse : b._locale._ordinalParseLenient;
                default:
                    return c = new RegExp($(Z(a.replace("\\", "")), "i"))
            }
        }

        function S(a) {
            a = a || "";
            var b = a.match(Xb) || [],
                c = b[b.length - 1] || [],
                d = (c + "").match(jc) || ["-", 0, 0],
                e = +(60 * d[1]) + C(d[2]);
            return "+" === d[0] ? e : -e
        }

        function T(a, b, c) {
            var d, e = c._a;
            switch (a) {
                case "Q":
                    null != b && (e[Db] = 3 * (C(b) - 1));
                    break;
                case "M":
                case "MM":
                    null != b && (e[Db] = C(b) - 1);
                    break;
                case "MMM":
                case "MMMM":
                    d = c._locale.monthsParse(b, a, c._strict), null != d ? e[Db] = d : c._pf.invalidMonth = b;
                    break;
                case "D":
                case "DD":
                    null != b && (e[Eb] = C(b));
                    break;
                case "Do":
                    null != b && (e[Eb] = C(parseInt(b.match(/\d{1,2}/)[0], 10)));
                    break;
                case "DDD":
                case "DDDD":
                    null != b && (c._dayOfYear = C(b));
                    break;
                case "YY":
                    e[Cb] = vb.parseTwoDigitYear(b);
                    break;
                case "YYYY":
                case "YYYYY":
                case "YYYYYY":
                    e[Cb] = C(b);
                    break;
                case "a":
                case "A":
                    c._meridiem = b;
                    break;
                case "h":
                case "hh":
                    c._pf.bigHour = !0;
                case "H":
                case "HH":
                    e[Fb] = C(b);
                    break;
                case "m":
                case "mm":
                    e[Gb] = C(b);
                    break;
                case "s":
                case "ss":
                    e[Hb] = C(b);
                    break;
                case "S":
                case "SS":
                case "SSS":
                case "SSSS":
                    e[Ib] = C(1e3 * ("0." + b));
                    break;
                case "x":
                    c._d = new Date(C(b));
                    break;
                case "X":
                    c._d = new Date(1e3 * parseFloat(b));
                    break;
                case "Z":
                case "ZZ":
                    c._useUTC = !0, c._tzm = S(b);
                    break;
                case "dd":
                case "ddd":
                case "dddd":
                    d = c._locale.weekdaysParse(b), null != d ? (c._w = c._w || {}, c._w.d = d) : c._pf.invalidWeekday = b;
                    break;
                case "w":
                case "ww":
                case "W":
                case "WW":
                case "d":
                case "e":
                case "E":
                    a = a.substr(0, 1);
                case "gggg":
                case "GGGG":
                case "GGGGG":
                    a = a.substr(0, 2), b && (c._w = c._w || {}, c._w[a] = C(b));
                    break;
                case "gg":
                case "GG":
                    c._w = c._w || {}, c._w[a] = vb.parseTwoDigitYear(b)
            }
        }

        function U(a) {
            var c, d, e, f, g, h, i;
            c = a._w, null != c.GG || null != c.W || null != c.E ? (g = 1, h = 4, d = b(c.GG, a._a[Cb], jb(vb(), 1, 4).year), e = b(c.W, 1), f = b(c.E, 1)) : (g = a._locale._week.dow, h = a._locale._week.doy, d = b(c.gg, a._a[Cb], jb(vb(), g, h).year), e = b(c.w, 1), null != c.d ? (f = c.d, g > f && ++e) : f = null != c.e ? c.e + g : g), i = kb(d, e, f, h, g), a._a[Cb] = i.year, a._dayOfYear = i.dayOfYear
        }

        function V(a) {
            var c, d, e, f, g = [];
            if (!a._d) {
                for (e = X(a), a._w && null == a._a[Eb] && null == a._a[Db] && U(a), a._dayOfYear && (f = b(a._a[Cb], e[Cb]), a._dayOfYear > F(f) && (a._pf._overflowDayOfYear = !0), d = fb(f, 0, a._dayOfYear), a._a[Db] = d.getUTCMonth(), a._a[Eb] = d.getUTCDate()), c = 0; 3 > c && null == a._a[c]; ++c) a._a[c] = g[c] = e[c];
                for (; 7 > c; c++) a._a[c] = g[c] = null == a._a[c] ? 2 === c ? 1 : 0 : a._a[c];
                24 === a._a[Fb] && 0 === a._a[Gb] && 0 === a._a[Hb] && 0 === a._a[Ib] && (a._nextDay = !0, a._a[Fb] = 0), a._d = (a._useUTC ? fb : eb).apply(null, g), null != a._tzm && a._d.setUTCMinutes(a._d.getUTCMinutes() - a._tzm), a._nextDay && (a._a[Fb] = 24)
            }
        }

        function W(a) {
            var b;
            a._d || (b = A(a._i), a._a = [b.year, b.month, b.day || b.date, b.hour, b.minute, b.second, b.millisecond], V(a))
        }

        function X(a) {
            var b = new Date;
            return a._useUTC ? [b.getUTCFullYear(), b.getUTCMonth(), b.getUTCDate()] : [b.getFullYear(), b.getMonth(), b.getDate()]
        }

        function Y(b) {
            if (b._f === vb.ISO_8601) return void ab(b);
            b._a = [], b._pf.empty = !0;
            var c, d, e, f, g, h = "" + b._i,
                i = h.length,
                j = 0;
            for (e = Q(b._f, b._locale).match(Pb) || [], c = 0; c < e.length; c++) f = e[c], d = (h.match(R(f, b)) || [])[0], d && (g = h.substr(0, h.indexOf(d)), g.length > 0 && b._pf.unusedInput.push(g), h = h.slice(h.indexOf(d) + d.length), j += d.length), rc[f] ? (d ? b._pf.empty = !1 : b._pf.unusedTokens.push(f), T(f, d, b)) : b._strict && !d && b._pf.unusedTokens.push(f);
            b._pf.charsLeftOver = i - j, h.length > 0 && b._pf.unusedInput.push(h), b._pf.bigHour === !0 && b._a[Fb] <= 12 && (b._pf.bigHour = a), b._a[Fb] = k(b._locale, b._a[Fb], b._meridiem), V(b), H(b)
        }

        function Z(a) {
            return a.replace(/\\(\[)|\\(\])|\[([^\]\[]*)\]|\\(.)/g, function(a, b, c, d, e) {
                return b || c || d || e
            })
        }

        function $(a) {
            return a.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&")
        }

        function _(a) {
            var b, c, e, f, g;
            if (0 === a._f.length) return a._pf.invalidFormat = !0, void(a._d = new Date(0 / 0));
            for (f = 0; f < a._f.length; f++) g = 0, b = p({}, a), null != a._useUTC && (b._useUTC = a._useUTC), b._pf = d(), b._f = a._f[f], Y(b), I(b) && (g += b._pf.charsLeftOver, g += 10 * b._pf.unusedTokens.length, b._pf.score = g, (null == e || e > g) && (e = g, c = b));
            o(a, c || b)
        }

        function ab(a) {
            var b, c, d = a._i,
                e = fc.exec(d);
            if (e) {
                for (a._pf.iso = !0, b = 0, c = hc.length; c > b; b++)
                    if (hc[b][1].exec(d)) {
                        a._f = hc[b][0] + (e[6] || " ");
                        break
                    }
                for (b = 0, c = ic.length; c > b; b++)
                    if (ic[b][1].exec(d)) {
                        a._f += ic[b][0];
                        break
                    }
                d.match(Xb) && (a._f += "Z"), Y(a)
            } else a._isValid = !1
        }

        function bb(a) {
            ab(a), a._isValid === !1 && (delete a._isValid, vb.createFromInputFallback(a))
        }

        function cb(a, b) {
            var c, d = [];
            for (c = 0; c < a.length; ++c) d.push(b(a[c], c));
            return d
        }

        function db(b) {
            var c, d = b._i;
            d === a ? b._d = new Date : x(d) ? b._d = new Date(+d) : null !== (c = Mb.exec(d)) ? b._d = new Date(+c[1]) : "string" == typeof d ? bb(b) : w(d) ? (b._a = cb(d.slice(0), function(a) {
                return parseInt(a, 10)
            }), V(b)) : "object" == typeof d ? W(b) : "number" == typeof d ? b._d = new Date(d) : vb.createFromInputFallback(b)
        }

        function eb(a, b, c, d, e, f, g) {
            var h = new Date(a, b, c, d, e, f, g);
            return 1970 > a && h.setFullYear(a), h
        }

        function fb(a) {
            var b = new Date(Date.UTC.apply(null, arguments));
            return 1970 > a && b.setUTCFullYear(a), b
        }

        function gb(a, b) {
            if ("string" == typeof a)
                if (isNaN(a)) {
                    if (a = b.weekdaysParse(a), "number" != typeof a) return null
                } else a = parseInt(a, 10);
            return a
        }

        function hb(a, b, c, d, e) {
            return e.relativeTime(b || 1, !!c, a, d)
        }

        function ib(a, b, c) {
            var d = vb.duration(a).abs(),
                e = Ab(d.as("s")),
                f = Ab(d.as("m")),
                g = Ab(d.as("h")),
                h = Ab(d.as("d")),
                i = Ab(d.as("M")),
                j = Ab(d.as("y")),
                k = e < oc.s && ["s", e] || 1 === f && ["m"] || f < oc.m && ["mm", f] || 1 === g && ["h"] || g < oc.h && ["hh", g] || 1 === h && ["d"] || h < oc.d && ["dd", h] || 1 === i && ["M"] || i < oc.M && ["MM", i] || 1 === j && ["y"] || ["yy", j];
            return k[2] = b, k[3] = +a > 0, k[4] = c, hb.apply({}, k)
        }

        function jb(a, b, c) {
            var d, e = c - b,
                f = c - a.day();
            return f > e && (f -= 7), e - 7 > f && (f += 7), d = vb(a).add(f, "d"), {
                week: Math.ceil(d.dayOfYear() / 7),
                year: d.year()
            }
        }

        function kb(a, b, c, d, e) {
            var f, g, h = fb(a, 0, 1).getUTCDay();
            return h = 0 === h ? 7 : h, c = null != c ? c : e, f = e - h + (h > d ? 7 : 0) - (e > h ? 7 : 0), g = 7 * (b - 1) + (c - e) + f + 1, {
                year: g > 0 ? a : a - 1,
                dayOfYear: g > 0 ? g : F(a - 1) + g
            }
        }

        function lb(b) {
            var c, d = b._i,
                e = b._f;
            return b._locale = b._locale || vb.localeData(b._l), null === d || e === a && "" === d ? vb.invalid({
                nullInput: !0
            }) : ("string" == typeof d && (b._i = d = b._locale.preparse(d)), vb.isMoment(d) ? new m(d, !0) : (e ? w(e) ? _(b) : Y(b) : db(b), c = new m(b), c._nextDay && (c.add(1, "d"), c._nextDay = a), c))
        }

        function mb(a, b) {
            var c, d;
            if (1 === b.length && w(b[0]) && (b = b[0]), !b.length) return vb();
            for (c = b[0], d = 1; d < b.length; ++d) b[d][a](c) && (c = b[d]);
            return c
        }

        function nb(a, b) {
            var c;
            return "string" == typeof b && (b = a.localeData().monthsParse(b), "number" != typeof b) ? a : (c = Math.min(a.date(), D(a.year(), b)), a._d["set" + (a._isUTC ? "UTC" : "") + "Month"](b, c), a)
        }

        function ob(a, b) {
            return a._d["get" + (a._isUTC ? "UTC" : "") + b]()
        }

        function pb(a, b, c) {
            return "Month" === b ? nb(a, c) : a._d["set" + (a._isUTC ? "UTC" : "") + b](c)
        }

        function qb(a, b) {
            return function(c) {
                return null != c ? (pb(this, a, c), vb.updateOffset(this, b), this) : ob(this, a)
            }
        }

        function rb(a) {
            return 400 * a / 146097
        }

        function sb(a) {
            return 146097 * a / 400
        }

        function tb(a) {
            vb.duration.fn[a] = function() {
                return this._data[a]
            }
        }

        function ub(a) {
            "undefined" == typeof ender && (wb = zb.moment, zb.moment = a ? f("Accessing Moment through the global scope is deprecated, and will be removed in an upcoming release.", vb) : vb)
        }
        for (var vb, wb, xb, yb = "2.9.0", zb = "undefined" == typeof global || "undefined" != typeof window && window !== global.window ? this : global, Ab = Math.round, Bb = Object.prototype.hasOwnProperty, Cb = 0, Db = 1, Eb = 2, Fb = 3, Gb = 4, Hb = 5, Ib = 6, Jb = {}, Kb = [], Lb = "undefined" != typeof module && module && module.exports, Mb = /^\/?Date\((\-?\d+)/i, Nb = /(\-)?(?:(\d*)\.)?(\d+)\:(\d+)(?:\:(\d+)\.?(\d{3})?)?/, Ob = /^(-)?P(?:(?:([0-9,.]*)Y)?(?:([0-9,.]*)M)?(?:([0-9,.]*)D)?(?:T(?:([0-9,.]*)H)?(?:([0-9,.]*)M)?(?:([0-9,.]*)S)?)?|([0-9,.]*)W)$/, Pb = /(\[[^\[]*\])|(\\)?(Mo|MM?M?M?|Do|DDDo|DD?D?D?|ddd?d?|do?|w[o|w]?|W[o|W]?|Q|YYYYYY|YYYYY|YYYY|YY|gg(ggg?)?|GG(GGG?)?|e|E|a|A|hh?|HH?|mm?|ss?|S{1,4}|x|X|zz?|ZZ?|.)/g, Qb = /(\[[^\[]*\])|(\\)?(LTS|LT|LL?L?L?|l{1,4})/g, Rb = /\d\d?/, Sb = /\d{1,3}/, Tb = /\d{1,4}/, Ub = /[+\-]?\d{1,6}/, Vb = /\d+/, Wb = /[0-9]*['a-z\u00A0-\u05FF\u0700-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+|[\u0600-\u06FF\/]+(\s*?[\u0600-\u06FF]+){1,2}/i, Xb = /Z|[\+\-]\d\d:?\d\d/gi, Yb = /T/i, Zb = /[\+\-]?\d+/, $b = /[\+\-]?\d+(\.\d{1,3})?/, _b = /\d/, ac = /\d\d/, bc = /\d{3}/, cc = /\d{4}/, dc = /[+-]?\d{6}/, ec = /[+-]?\d+/, fc = /^\s*(?:[+-]\d{6}|\d{4})-(?:(\d\d-\d\d)|(W\d\d$)|(W\d\d-\d)|(\d\d\d))((T| )(\d\d(:\d\d(:\d\d(\.\d+)?)?)?)?([\+\-]\d\d(?::?\d\d)?|\s*Z)?)?$/, gc = "YYYY-MM-DDTHH:mm:ssZ", hc = [
                ["YYYYYY-MM-DD", /[+-]\d{6}-\d{2}-\d{2}/],
                ["YYYY-MM-DD", /\d{4}-\d{2}-\d{2}/],
                ["GGGG-[W]WW-E", /\d{4}-W\d{2}-\d/],
                ["GGGG-[W]WW", /\d{4}-W\d{2}/],
                ["YYYY-DDD", /\d{4}-\d{3}/]
            ], ic = [
                ["HH:mm:ss.SSSS", /(T| )\d\d:\d\d:\d\d\.\d+/],
                ["HH:mm:ss", /(T| )\d\d:\d\d:\d\d/],
                ["HH:mm", /(T| )\d\d:\d\d/],
                ["HH", /(T| )\d\d/]
            ], jc = /([\+\-]|\d\d)/gi, kc = ("Date|Hours|Minutes|Seconds|Milliseconds".split("|"), {
                Milliseconds: 1,
                Seconds: 1e3,
                Minutes: 6e4,
                Hours: 36e5,
                Days: 864e5,
                Months: 2592e6,
                Years: 31536e6
            }), lc = {
                ms: "millisecond",
                s: "second",
                m: "minute",
                h: "hour",
                d: "day",
                D: "date",
                w: "week",
                W: "isoWeek",
                M: "month",
                Q: "quarter",
                y: "year",
                DDD: "dayOfYear",
                e: "weekday",
                E: "isoWeekday",
                gg: "weekYear",
                GG: "isoWeekYear"
            }, mc = {
                dayofyear: "dayOfYear",
                isoweekday: "isoWeekday",
                isoweek: "isoWeek",
                weekyear: "weekYear",
                isoweekyear: "isoWeekYear"
            }, nc = {}, oc = {
                s: 45,
                m: 45,
                h: 22,
                d: 26,
                M: 11
            }, pc = "DDD w W M D d".split(" "), qc = "M D H h m s w W".split(" "), rc = {
                M: function() {
                    return this.month() + 1
                },
                MMM: function(a) {
                    return this.localeData().monthsShort(this, a)
                },
                MMMM: function(a) {
                    return this.localeData().months(this, a)
                },
                D: function() {
                    return this.date()
                },
                DDD: function() {
                    return this.dayOfYear()
                },
                d: function() {
                    return this.day()
                },
                dd: function(a) {
                    return this.localeData().weekdaysMin(this, a)
                },
                ddd: function(a) {
                    return this.localeData().weekdaysShort(this, a)
                },
                dddd: function(a) {
                    return this.localeData().weekdays(this, a)
                },
                w: function() {
                    return this.week()
                },
                W: function() {
                    return this.isoWeek()
                },
                YY: function() {
                    return r(this.year() % 100, 2)
                },
                YYYY: function() {
                    return r(this.year(), 4)
                },
                YYYYY: function() {
                    return r(this.year(), 5)
                },
                YYYYYY: function() {
                    var a = this.year(),
                        b = a >= 0 ? "+" : "-";
                    return b + r(Math.abs(a), 6)
                },
                gg: function() {
                    return r(this.weekYear() % 100, 2)
                },
                gggg: function() {
                    return r(this.weekYear(), 4)
                },
                ggggg: function() {
                    return r(this.weekYear(), 5)
                },
                GG: function() {
                    return r(this.isoWeekYear() % 100, 2)
                },
                GGGG: function() {
                    return r(this.isoWeekYear(), 4)
                },
                GGGGG: function() {
                    return r(this.isoWeekYear(), 5)
                },
                e: function() {
                    return this.weekday()
                },
                E: function() {
                    return this.isoWeekday()
                },
                a: function() {
                    return this.localeData().meridiem(this.hours(), this.minutes(), !0)
                },
                A: function() {
                    return this.localeData().meridiem(this.hours(), this.minutes(), !1)
                },
                H: function() {
                    return this.hours()
                },
                h: function() {
                    return this.hours() % 12 || 12
                },
                m: function() {
                    return this.minutes()
                },
                s: function() {
                    return this.seconds()
                },
                S: function() {
                    return C(this.milliseconds() / 100)
                },
                SS: function() {
                    return r(C(this.milliseconds() / 10), 2)
                },
                SSS: function() {
                    return r(this.milliseconds(), 3)
                },
                SSSS: function() {
                    return r(this.milliseconds(), 3)
                },
                Z: function() {
                    var a = this.utcOffset(),
                        b = "+";
                    return 0 > a && (a = -a, b = "-"), b + r(C(a / 60), 2) + ":" + r(C(a) % 60, 2)
                },
                ZZ: function() {
                    var a = this.utcOffset(),
                        b = "+";
                    return 0 > a && (a = -a, b = "-"), b + r(C(a / 60), 2) + r(C(a) % 60, 2)
                },
                z: function() {
                    return this.zoneAbbr()
                },
                zz: function() {
                    return this.zoneName()
                },
                x: function() {
                    return this.valueOf()
                },
                X: function() {
                    return this.unix()
                },
                Q: function() {
                    return this.quarter()
                }
            }, sc = {}, tc = ["months", "monthsShort", "weekdays", "weekdaysShort", "weekdaysMin"], uc = !1; pc.length;) xb = pc.pop(), rc[xb + "o"] = i(rc[xb], xb);
        for (; qc.length;) xb = qc.pop(), rc[xb + xb] = h(rc[xb], 2);
        rc.DDDD = h(rc.DDD, 3), o(l.prototype, {
            set: function(a) {
                var b, c;
                for (c in a) b = a[c], "function" == typeof b ? this[c] = b : this["_" + c] = b;
                this._ordinalParseLenient = new RegExp(this._ordinalParse.source + "|" + /\d{1,2}/.source)
            },
            _months: "January_February_March_April_May_June_July_August_September_October_November_December".split("_"),
            months: function(a) {
                return this._months[a.month()]
            },
            _monthsShort: "Jan_Feb_Mar_Apr_May_Jun_Jul_Aug_Sep_Oct_Nov_Dec".split("_"),
            monthsShort: function(a) {
                return this._monthsShort[a.month()]
            },
            monthsParse: function(a, b, c) {
                var d, e, f;
                for (this._monthsParse || (this._monthsParse = [], this._longMonthsParse = [], this._shortMonthsParse = []), d = 0; 12 > d; d++) {
                    if (e = vb.utc([2e3, d]), c && !this._longMonthsParse[d] && (this._longMonthsParse[d] = new RegExp("^" + this.months(e, "").replace(".", "") + "$", "i"), this._shortMonthsParse[d] = new RegExp("^" + this.monthsShort(e, "").replace(".", "") + "$", "i")), c || this._monthsParse[d] || (f = "^" + this.months(e, "") + "|^" + this.monthsShort(e, ""), this._monthsParse[d] = new RegExp(f.replace(".", ""), "i")), c && "MMMM" === b && this._longMonthsParse[d].test(a)) return d;
                    if (c && "MMM" === b && this._shortMonthsParse[d].test(a)) return d;
                    if (!c && this._monthsParse[d].test(a)) return d
                }
            },
            _weekdays: "Sunday_Monday_Tuesday_Wednesday_Thursday_Friday_Saturday".split("_"),
            weekdays: function(a) {
                return this._weekdays[a.day()]
            },
            _weekdaysShort: "Sun_Mon_Tue_Wed_Thu_Fri_Sat".split("_"),
            weekdaysShort: function(a) {
                return this._weekdaysShort[a.day()]
            },
            _weekdaysMin: "Su_Mo_Tu_We_Th_Fr_Sa".split("_"),
            weekdaysMin: function(a) {
                return this._weekdaysMin[a.day()]
            },
            weekdaysParse: function(a) {
                var b, c, d;
                for (this._weekdaysParse || (this._weekdaysParse = []), b = 0; 7 > b; b++)
                    if (this._weekdaysParse[b] || (c = vb([2e3, 1]).day(b), d = "^" + this.weekdays(c, "") + "|^" + this.weekdaysShort(c, "") + "|^" + this.weekdaysMin(c, ""), this._weekdaysParse[b] = new RegExp(d.replace(".", ""), "i")), this._weekdaysParse[b].test(a)) return b
            },
            _longDateFormat: {
                LTS: "h:mm:ss A",
                LT: "h:mm A",
                L: "MM/DD/YYYY",
                LL: "MMMM D, YYYY",
                LLL: "MMMM D, YYYY LT",
                LLLL: "dddd, MMMM D, YYYY LT"
            },
            longDateFormat: function(a) {
                var b = this._longDateFormat[a];
                return !b && this._longDateFormat[a.toUpperCase()] && (b = this._longDateFormat[a.toUpperCase()].replace(/MMMM|MM|DD|dddd/g, function(a) {
                    return a.slice(1)
                }), this._longDateFormat[a] = b), b
            },
            isPM: function(a) {
                return "p" === (a + "").toLowerCase().charAt(0)
            },
            _meridiemParse: /[ap]\.?m?\.?/i,
            meridiem: function(a, b, c) {
                return a > 11 ? c ? "pm" : "PM" : c ? "am" : "AM"
            },
            _calendar: {
                sameDay: "[Today at] LT",
                nextDay: "[Tomorrow at] LT",
                nextWeek: "dddd [at] LT",
                lastDay: "[Yesterday at] LT",
                lastWeek: "[Last] dddd [at] LT",
                sameElse: "L"
            },
            calendar: function(a, b, c) {
                var d = this._calendar[a];
                return "function" == typeof d ? d.apply(b, [c]) : d
            },
            _relativeTime: {
                future: "in %s",
                past: "%s ago",
                s: "a few seconds",
                m: "a minute",
                mm: "%d minutes",
                h: "an hour",
                hh: "%d hours",
                d: "a day",
                dd: "%d days",
                M: "a month",
                MM: "%d months",
                y: "a year",
                yy: "%d years"
            },
            relativeTime: function(a, b, c, d) {
                var e = this._relativeTime[c];
                return "function" == typeof e ? e(a, b, c, d) : e.replace(/%d/i, a)
            },
            pastFuture: function(a, b) {
                var c = this._relativeTime[a > 0 ? "future" : "past"];
                return "function" == typeof c ? c(b) : c.replace(/%s/i, b)
            },
            ordinal: function(a) {
                return this._ordinal.replace("%d", a)
            },
            _ordinal: "%d",
            _ordinalParse: /\d{1,2}/,
            preparse: function(a) {
                return a
            },
            postformat: function(a) {
                return a
            },
            week: function(a) {
                return jb(a, this._week.dow, this._week.doy).week
            },
            _week: {
                dow: 0,
                doy: 6
            },
            firstDayOfWeek: function() {
                return this._week.dow
            },
            firstDayOfYear: function() {
                return this._week.doy
            },
            _invalidDate: "Invalid date",
            invalidDate: function() {
                return this._invalidDate
            }
        }), vb = function(b, c, e, f) {
            var g;
            return "boolean" == typeof e && (f = e, e = a), g = {}, g._isAMomentObject = !0, g._i = b, g._f = c, g._l = e, g._strict = f, g._isUTC = !1, g._pf = d(), lb(g)
        }, vb.suppressDeprecationWarnings = !1, vb.createFromInputFallback = f("moment construction falls back to js Date. This is discouraged and will be removed in upcoming major release. Please refer to https://github.com/moment/moment/issues/1407 for more info.", function(a) {
            a._d = new Date(a._i + (a._useUTC ? " UTC" : ""))
        }), vb.min = function() {
            var a = [].slice.call(arguments, 0);
            return mb("isBefore", a)
        }, vb.max = function() {
            var a = [].slice.call(arguments, 0);
            return mb("isAfter", a)
        }, vb.utc = function(b, c, e, f) {
            var g;
            return "boolean" == typeof e && (f = e, e = a), g = {}, g._isAMomentObject = !0, g._useUTC = !0, g._isUTC = !0, g._l = e, g._i = b, g._f = c, g._strict = f, g._pf = d(), lb(g).utc()
        }, vb.unix = function(a) {
            return vb(1e3 * a)
        }, vb.duration = function(a, b) {
            var d, e, f, g, h = a,
                i = null;
            return vb.isDuration(a) ? h = {
                ms: a._milliseconds,
                d: a._days,
                M: a._months
            } : "number" == typeof a ? (h = {}, b ? h[b] = a : h.milliseconds = a) : (i = Nb.exec(a)) ? (d = "-" === i[1] ? -1 : 1, h = {
                y: 0,
                d: C(i[Eb]) * d,
                h: C(i[Fb]) * d,
                m: C(i[Gb]) * d,
                s: C(i[Hb]) * d,
                ms: C(i[Ib]) * d
            }) : (i = Ob.exec(a)) ? (d = "-" === i[1] ? -1 : 1, f = function(a) {
                var b = a && parseFloat(a.replace(",", "."));
                return (isNaN(b) ? 0 : b) * d
            }, h = {
                y: f(i[2]),
                M: f(i[3]),
                d: f(i[4]),
                h: f(i[5]),
                m: f(i[6]),
                s: f(i[7]),
                w: f(i[8])
            }) : null == h ? h = {} : "object" == typeof h && ("from" in h || "to" in h) && (g = t(vb(h.from), vb(h.to)), h = {}, h.ms = g.milliseconds, h.M = g.months), e = new n(h), vb.isDuration(a) && c(a, "_locale") && (e._locale = a._locale), e
        }, vb.version = yb, vb.defaultFormat = gc, vb.ISO_8601 = function() {}, vb.momentProperties = Kb, vb.updateOffset = function() {}, vb.relativeTimeThreshold = function(b, c) {
            return oc[b] === a ? !1 : c === a ? oc[b] : (oc[b] = c, !0)
        }, vb.lang = f("moment.lang is deprecated. Use moment.locale instead.", function(a, b) {
            return vb.locale(a, b)
        }), vb.locale = function(a, b) {
            var c;
            return a && (c = "undefined" != typeof b ? vb.defineLocale(a, b) : vb.localeData(a), c && (vb.duration._locale = vb._locale = c)), vb._locale._abbr
        }, vb.defineLocale = function(a, b) {
            return null !== b ? (b.abbr = a, Jb[a] || (Jb[a] = new l), Jb[a].set(b), vb.locale(a), Jb[a]) : (delete Jb[a], null)
        }, vb.langData = f("moment.langData is deprecated. Use moment.localeData instead.", function(a) {
            return vb.localeData(a)
        }), vb.localeData = function(a) {
            var b;
            if (a && a._locale && a._locale._abbr && (a = a._locale._abbr), !a) return vb._locale;
            if (!w(a)) {
                if (b = L(a)) return b;
                a = [a]
            }
            return K(a)
        }, vb.isMoment = function(a) {
            return a instanceof m || null != a && c(a, "_isAMomentObject")
        }, vb.isDuration = function(a) {
            return a instanceof n
        };
        for (xb = tc.length - 1; xb >= 0; --xb) B(tc[xb]);
        vb.normalizeUnits = function(a) {
            return z(a)
        }, vb.invalid = function(a) {
            var b = vb.utc(0 / 0);
            return null != a ? o(b._pf, a) : b._pf.userInvalidated = !0, b
        }, vb.parseZone = function() {
            return vb.apply(null, arguments).parseZone()
        }, vb.parseTwoDigitYear = function(a) {
            return C(a) + (C(a) > 68 ? 1900 : 2e3)
        }, vb.isDate = x, o(vb.fn = m.prototype, {
            clone: function() {
                return vb(this)
            },
            valueOf: function() {
                return +this._d - 6e4 * (this._offset || 0)
            },
            unix: function() {
                return Math.floor(+this / 1e3)
            },
            toString: function() {
                return this.clone().locale("en").format("ddd MMM DD YYYY HH:mm:ss [GMT]ZZ")
            },
            toDate: function() {
                return this._offset ? new Date(+this) : this._d
            },
            toISOString: function() {
                var a = vb(this).utc();
                return 0 < a.year() && a.year() <= 9999 ? "function" == typeof Date.prototype.toISOString ? this.toDate().toISOString() : P(a, "YYYY-MM-DD[T]HH:mm:ss.SSS[Z]") : P(a, "YYYYYY-MM-DD[T]HH:mm:ss.SSS[Z]")
            },
            toArray: function() {
                var a = this;
                return [a.year(), a.month(), a.date(), a.hours(), a.minutes(), a.seconds(), a.milliseconds()]
            },
            isValid: function() {
                return I(this)
            },
            isDSTShifted: function() {
                return this._a ? this.isValid() && y(this._a, (this._isUTC ? vb.utc(this._a) : vb(this._a)).toArray()) > 0 : !1
            },
            parsingFlags: function() {
                return o({}, this._pf)
            },
            invalidAt: function() {
                return this._pf.overflow
            },
            utc: function(a) {
                return this.utcOffset(0, a)
            },
            local: function(a) {
                return this._isUTC && (this.utcOffset(0, a), this._isUTC = !1, a && this.subtract(this._dateUtcOffset(), "m")), this
            },
            format: function(a) {
                var b = P(this, a || vb.defaultFormat);
                return this.localeData().postformat(b)
            },
            add: u(1, "add"),
            subtract: u(-1, "subtract"),
            diff: function(a, b, c) {
                var d, e, f = M(a, this),
                    g = 6e4 * (f.utcOffset() - this.utcOffset());
                return b = z(b), "year" === b || "month" === b || "quarter" === b ? (e = j(this, f), "quarter" === b ? e /= 3 : "year" === b && (e /= 12)) : (d = this - f, e = "second" === b ? d / 1e3 : "minute" === b ? d / 6e4 : "hour" === b ? d / 36e5 : "day" === b ? (d - g) / 864e5 : "week" === b ? (d - g) / 6048e5 : d), c ? e : q(e)
            },
            from: function(a, b) {
                return vb.duration({
                    to: this,
                    from: a
                }).locale(this.locale()).humanize(!b)
            },
            fromNow: function(a) {
                return this.from(vb(), a)
            },
            calendar: function(a) {
                var b = a || vb(),
                    c = M(b, this).startOf("day"),
                    d = this.diff(c, "days", !0),
                    e = -6 > d ? "sameElse" : -1 > d ? "lastWeek" : 0 > d ? "lastDay" : 1 > d ? "sameDay" : 2 > d ? "nextDay" : 7 > d ? "nextWeek" : "sameElse";
                return this.format(this.localeData().calendar(e, this, vb(b)))
            },
            isLeapYear: function() {
                return G(this.year())
            },
            isDST: function() {
                return this.utcOffset() > this.clone().month(0).utcOffset() || this.utcOffset() > this.clone().month(5).utcOffset()
            },
            day: function(a) {
                var b = this._isUTC ? this._d.getUTCDay() : this._d.getDay();
                return null != a ? (a = gb(a, this.localeData()), this.add(a - b, "d")) : b
            },
            month: qb("Month", !0),
            startOf: function(a) {
                switch (a = z(a)) {
                    case "year":
                        this.month(0);
                    case "quarter":
                    case "month":
                        this.date(1);
                    case "week":
                    case "isoWeek":
                    case "day":
                        this.hours(0);
                    case "hour":
                        this.minutes(0);
                    case "minute":
                        this.seconds(0);
                    case "second":
                        this.milliseconds(0)
                }
                return "week" === a ? this.weekday(0) : "isoWeek" === a && this.isoWeekday(1), "quarter" === a && this.month(3 * Math.floor(this.month() / 3)), this
            },
            endOf: function(b) {
                return b = z(b), b === a || "millisecond" === b ? this : this.startOf(b).add(1, "isoWeek" === b ? "week" : b).subtract(1, "ms")
            },
            isAfter: function(a, b) {
                var c;
                return b = z("undefined" != typeof b ? b : "millisecond"), "millisecond" === b ? (a = vb.isMoment(a) ? a : vb(a), +this > +a) : (c = vb.isMoment(a) ? +a : +vb(a), c < +this.clone().startOf(b))
            },
            isBefore: function(a, b) {
                var c;
                return b = z("undefined" != typeof b ? b : "millisecond"), "millisecond" === b ? (a = vb.isMoment(a) ? a : vb(a), +a > +this) : (c = vb.isMoment(a) ? +a : +vb(a), +this.clone().endOf(b) < c)
            },
            isBetween: function(a, b, c) {
                return this.isAfter(a, c) && this.isBefore(b, c)
            },
            isSame: function(a, b) {
                var c;
                return b = z(b || "millisecond"), "millisecond" === b ? (a = vb.isMoment(a) ? a : vb(a), +this === +a) : (c = +vb(a), +this.clone().startOf(b) <= c && c <= +this.clone().endOf(b))
            },
            min: f("moment().min is deprecated, use moment.min instead. https://github.com/moment/moment/issues/1548", function(a) {
                return a = vb.apply(null, arguments), this > a ? this : a
            }),
            max: f("moment().max is deprecated, use moment.max instead. https://github.com/moment/moment/issues/1548", function(a) {
                return a = vb.apply(null, arguments), a > this ? this : a
            }),
            zone: f("moment().zone is deprecated, use moment().utcOffset instead. https://github.com/moment/moment/issues/1779", function(a, b) {
                return null != a ? ("string" != typeof a && (a = -a), this.utcOffset(a, b), this) : -this.utcOffset()
            }),
            utcOffset: function(a, b) {
                var c, d = this._offset || 0;
                return null != a ? ("string" == typeof a && (a = S(a)), Math.abs(a) < 16 && (a = 60 * a), !this._isUTC && b && (c = this._dateUtcOffset()), this._offset = a, this._isUTC = !0, null != c && this.add(c, "m"), d !== a && (!b || this._changeInProgress ? v(this, vb.duration(a - d, "m"), 1, !1) : this._changeInProgress || (this._changeInProgress = !0, vb.updateOffset(this, !0), this._changeInProgress = null)), this) : this._isUTC ? d : this._dateUtcOffset()
            },
            isLocal: function() {
                return !this._isUTC
            },
            isUtcOffset: function() {
                return this._isUTC
            },
            isUtc: function() {
                return this._isUTC && 0 === this._offset
            },
            zoneAbbr: function() {
                return this._isUTC ? "UTC" : ""
            },
            zoneName: function() {
                return this._isUTC ? "Coordinated Universal Time" : ""
            },
            parseZone: function() {
                return this._tzm ? this.utcOffset(this._tzm) : "string" == typeof this._i && this.utcOffset(S(this._i)), this
            },
            hasAlignedHourOffset: function(a) {
                return a = a ? vb(a).utcOffset() : 0, (this.utcOffset() - a) % 60 === 0
            },
            daysInMonth: function() {
                return D(this.year(), this.month())
            },
            dayOfYear: function(a) {
                var b = Ab((vb(this).startOf("day") - vb(this).startOf("year")) / 864e5) + 1;
                return null == a ? b : this.add(a - b, "d")
            },
            quarter: function(a) {
                return null == a ? Math.ceil((this.month() + 1) / 3) : this.month(3 * (a - 1) + this.month() % 3)
            },
            weekYear: function(a) {
                var b = jb(this, this.localeData()._week.dow, this.localeData()._week.doy).year;
                return null == a ? b : this.add(a - b, "y")
            },
            isoWeekYear: function(a) {
                var b = jb(this, 1, 4).year;
                return null == a ? b : this.add(a - b, "y")
            },
            week: function(a) {
                var b = this.localeData().week(this);
                return null == a ? b : this.add(7 * (a - b), "d")
            },
            isoWeek: function(a) {
                var b = jb(this, 1, 4).week;
                return null == a ? b : this.add(7 * (a - b), "d")
            },
            weekday: function(a) {
                var b = (this.day() + 7 - this.localeData()._week.dow) % 7;
                return null == a ? b : this.add(a - b, "d")
            },
            isoWeekday: function(a) {
                return null == a ? this.day() || 7 : this.day(this.day() % 7 ? a : a - 7)
            },
            isoWeeksInYear: function() {
                return E(this.year(), 1, 4)
            },
            weeksInYear: function() {
                var a = this.localeData()._week;
                return E(this.year(), a.dow, a.doy)
            },
            get: function(a) {
                return a = z(a), this[a]()
            },
            set: function(a, b) {
                var c;
                if ("object" == typeof a)
                    for (c in a) this.set(c, a[c]);
                else a = z(a), "function" == typeof this[a] && this[a](b);
                return this
            },
            locale: function(b) {
                var c;
                return b === a ? this._locale._abbr : (c = vb.localeData(b), null != c && (this._locale = c), this)
            },
            lang: f("moment().lang() is deprecated. Instead, use moment().localeData() to get the language configuration. Use moment().locale() to change languages.", function(b) {
                return b === a ? this.localeData() : this.locale(b)
            }),
            localeData: function() {
                return this._locale
            },
            _dateUtcOffset: function() {
                return 15 * -Math.round(this._d.getTimezoneOffset() / 15)
            }
        }), vb.fn.millisecond = vb.fn.milliseconds = qb("Milliseconds", !1), vb.fn.second = vb.fn.seconds = qb("Seconds", !1), vb.fn.minute = vb.fn.minutes = qb("Minutes", !1), vb.fn.hour = vb.fn.hours = qb("Hours", !0), vb.fn.date = qb("Date", !0), vb.fn.dates = f("dates accessor is deprecated. Use date instead.", qb("Date", !0)), vb.fn.year = qb("FullYear", !0), vb.fn.years = f("years accessor is deprecated. Use year instead.", qb("FullYear", !0)), vb.fn.days = vb.fn.day, vb.fn.months = vb.fn.month, vb.fn.weeks = vb.fn.week, vb.fn.isoWeeks = vb.fn.isoWeek, vb.fn.quarters = vb.fn.quarter, vb.fn.toJSON = vb.fn.toISOString, vb.fn.isUTC = vb.fn.isUtc, o(vb.duration.fn = n.prototype, {
            _bubble: function() {
                var a, b, c, d = this._milliseconds,
                    e = this._days,
                    f = this._months,
                    g = this._data,
                    h = 0;
                g.milliseconds = d % 1e3, a = q(d / 1e3), g.seconds = a % 60, b = q(a / 60), g.minutes = b % 60, c = q(b / 60), g.hours = c % 24, e += q(c / 24), h = q(rb(e)), e -= q(sb(h)), f += q(e / 30), e %= 30, h += q(f / 12), f %= 12, g.days = e, g.months = f, g.years = h
            },
            abs: function() {
                return this._milliseconds = Math.abs(this._milliseconds), this._days = Math.abs(this._days), this._months = Math.abs(this._months), this._data.milliseconds = Math.abs(this._data.milliseconds), this._data.seconds = Math.abs(this._data.seconds), this._data.minutes = Math.abs(this._data.minutes), this._data.hours = Math.abs(this._data.hours), this._data.months = Math.abs(this._data.months), this._data.years = Math.abs(this._data.years), this
            },
            weeks: function() {
                return q(this.days() / 7)
            },
            valueOf: function() {
                return this._milliseconds + 864e5 * this._days + this._months % 12 * 2592e6 + 31536e6 * C(this._months / 12)
            },
            humanize: function(a) {
                var b = ib(this, !a, this.localeData());
                return a && (b = this.localeData().pastFuture(+this, b)), this.localeData().postformat(b)
            },
            add: function(a, b) {
                var c = vb.duration(a, b);
                return this._milliseconds += c._milliseconds, this._days += c._days, this._months += c._months, this._bubble(), this
            },
            subtract: function(a, b) {
                var c = vb.duration(a, b);
                return this._milliseconds -= c._milliseconds, this._days -= c._days, this._months -= c._months, this._bubble(), this
            },
            get: function(a) {
                return a = z(a), this[a.toLowerCase() + "s"]()
            },
            as: function(a) {
                var b, c;
                if (a = z(a), "month" === a || "year" === a) return b = this._days + this._milliseconds / 864e5, c = this._months + 12 * rb(b), "month" === a ? c : c / 12;
                switch (b = this._days + Math.round(sb(this._months / 12)), a) {
                    case "week":
                        return b / 7 + this._milliseconds / 6048e5;
                    case "day":
                        return b + this._milliseconds / 864e5;
                    case "hour":
                        return 24 * b + this._milliseconds / 36e5;
                    case "minute":
                        return 24 * b * 60 + this._milliseconds / 6e4;
                    case "second":
                        return 24 * b * 60 * 60 + this._milliseconds / 1e3;
                    case "millisecond":
                        return Math.floor(24 * b * 60 * 60 * 1e3) + this._milliseconds;
                    default:
                        throw new Error("Unknown unit " + a)
                }
            },
            lang: vb.fn.lang,
            locale: vb.fn.locale,
            toIsoString: f("toIsoString() is deprecated. Please use toISOString() instead (notice the capitals)", function() {
                return this.toISOString()
            }),
            toISOString: function() {
                var a = Math.abs(this.years()),
                    b = Math.abs(this.months()),
                    c = Math.abs(this.days()),
                    d = Math.abs(this.hours()),
                    e = Math.abs(this.minutes()),
                    f = Math.abs(this.seconds() + this.milliseconds() / 1e3);
                return this.asSeconds() ? (this.asSeconds() < 0 ? "-" : "") + "P" + (a ? a + "Y" : "") + (b ? b + "M" : "") + (c ? c + "D" : "") + (d || e || f ? "T" : "") + (d ? d + "H" : "") + (e ? e + "M" : "") + (f ? f + "S" : "") : "P0D"
            },
            localeData: function() {
                return this._locale
            },
            toJSON: function() {
                return this.toISOString()
            }
        }), vb.duration.fn.toString = vb.duration.fn.toISOString;
        for (xb in kc) c(kc, xb) && tb(xb.toLowerCase());
        vb.duration.fn.asMilliseconds = function() {
            return this.as("ms")
        }, vb.duration.fn.asSeconds = function() {
            return this.as("s")
        }, vb.duration.fn.asMinutes = function() {
            return this.as("m")
        }, vb.duration.fn.asHours = function() {
            return this.as("h")
        }, vb.duration.fn.asDays = function() {
            return this.as("d")
        }, vb.duration.fn.asWeeks = function() {
            return this.as("weeks")
        }, vb.duration.fn.asMonths = function() {
            return this.as("M")
        }, vb.duration.fn.asYears = function() {
            return this.as("y")
        }, vb.locale("en", {
            ordinalParse: /\d{1,2}(th|st|nd|rd)/,
            ordinal: function(a) {
                var b = a % 10,
                    c = 1 === C(a % 100 / 10) ? "th" : 1 === b ? "st" : 2 === b ? "nd" : 3 === b ? "rd" : "th";
                return a + c
            }
        }), Lb ? module.exports = vb : "function" == typeof define && define.amd ? (define(function(a, b, c) {
            return c.config && c.config() && c.config().noGlobal === !0 && (zb.moment = wb), vb
        }), ub(!0)) : ub()
    }.call(this);
var StoreUtil = function() {
    function createPAEnrollForm(acsUrl, PaReq, MD, TermUrl) {
        $("form[name=PAEnrollForm]").remove();
        var form = document.createElement("form");
        form.setAttribute("method", "post"), form.setAttribute("action", acsUrl), form.setAttribute("target", "paInlineFrame"), form.setAttribute("name", "PAEnrollForm");
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "PaReq"), hiddenField.setAttribute("value", PaReq), hiddenField.setAttribute("type", "hidden"), form.appendChild(hiddenField);
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "TermUrl"), hiddenField.setAttribute("value", TermUrl), hiddenField.setAttribute("type", "hidden"), form.appendChild(hiddenField);
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "MD"), hiddenField.setAttribute("value", MD), hiddenField.setAttribute("type", "hidden"), form.appendChild(hiddenField), document.body.appendChild(form)
    }

    function getCurrencyCountry(currencyId) {
        var currencyIdCountries = StoreProperties.currencyJSON.id_country;
        return currencyIdCountries ? currencyIdCountries[currencyId] : void 0
    }

    function getCurrencyCountryObj(country) {
        var currencyCountryObj = StoreProperties.currencyJSON[country];
        return currencyCountryObj ? currencyCountryObj : void 0
    }
    var i18n = JSON.parse(StoreProperties.i18n),
        serviceId = StoreProperties.serviceId,
        adminUrl = !1,
        orgcreatedtime = null;
    if (StoreProperties.plans) {
        var trialPlans = StoreProperties.plans.TrialPlans,
            subscriptionPage = StoreProperties.subscription,
            trialPlansMap = StoreProperties.plans.TrialPlansMap,
            trialOrderMap = StoreProperties.plans.TrialOrderMap,
            userTrials = StoreProperties.userTrial,
            trialPlanId = null;
        freeTrialEnabled = !1
    }
    adminUrl = "admin" == location.pathname.substring(location.pathname.lastIndexOf("/") + 1, location.pathname.indexOf(".")) ? !0 : !1;
    var securityKey = StoreProperties.securityKey,
        getCompanyName = function() {
            var companyName = void 0 != StoreProperties.licenseInfo.companyname ? StoreProperties.licenseInfo.companyname : void 0 != StoreProperties.cc.companyName ? StoreProperties.cc.companyName : "";
            return companyName = companyName.replace("+", "")
        };
    null != StoreProperties.licenseInfo && (StoreProperties.cc.companyName = getCompanyName(), orgcreatedtime = void 0 !== StoreProperties.licenseInfo.orgcreatedtime ? StoreProperties.licenseInfo.orgcreatedtime : null), StoreProperties.origCC = $.extend({}, StoreProperties.cc), $("#changeCreditCard").click(function() {
        StoreProperties.cc = $.extend({}, StoreProperties.origCC), StoreUtil.cc.change()
    }), $("#paInlineFrame").ready(function() {
        $("#paInlineFrame").css("background-color", "white")
    }), activeUserCheckEnabled = !1, void 0 != StoreProperties.depends && $.each(StoreProperties.depends, function(index, value) {
        "activeUsersCheck" == value && (activeUserCheckEnabled = !0)
    });
    var confirm = function() {
        StoreProperties.service.confirm()
    };
    $(document).on("click", ".enterCreditCardBtn,#modifyCreditCard", function() {
        return $(this).hasClass("enterCreditCardBtn") && void 0 != StoreProperties.depends && activeUserCheckEnabled && !StoreProperties.service.activeUserHandling("upgrade", StoreProperties.selectedPlan.plan) ? !1 : $(this).hasClass("enterCreditCardBtn") && (0 == StoreProperties.service.recurringDue() && !StoreProperties.sameBuyer || StoreProperties.cc && StoreProperties.cc.number) ? (confirm(), !1) : (creditCardAction.val(i18n["store.confirm.order"]), StoreProperties.cc && StoreUtil.cc.set(StoreProperties.cc), creditCardAction.off("click.saveCard click.confirmOrder").on("click.confirmOrder", function() {
            StoreProperties.cc = StoreUtil.cc.get(), StoreProperties.cc && confirm()
        }), void creditCardPopup.popup())
    }), $(document).on("click", ".confirmOrderBtn", function() {
        if ($("#confirmOrderPopup .inlineError").text("").hide(), "downgrade" == $(this).attr("action"))
            if (activeUserCheckEnabled && StoreProperties.userPlan.plan != StoreProperties.selectedPlan.plan && StoreProperties.service.getFreePlanId() != StoreProperties.selectedPlan.plan) {
                if (!StoreProperties.service.activeUserHandling("downgrade", StoreProperties.selectedPlan.plan)) return !1;
                StoreProperties.service.downgradeAction($(this).attr("popup")), placeOrder()
            } else StoreProperties.service.downgradeAction($(this).attr("popup")), placeOrder();
        else confirm()
    }), $("#subscriptionHistoryLink").click(function() {
        if ($("#subscriptionHistoryPopup").popup(), void 0 != StoreProperties.serviceJSON.subscriptionHistoryUrl) StoreProperties.service.showSubscriptionHistory();
        else {
            var data = {
                serviceId: serviceId,
                zId: StoreProperties.userDetails.customId
            };
            void 0 != StoreProperties.userPlan.profileId && (data.profileId = StoreProperties.userPlan.profileId), historyURI = "/store/RestAPIPurchaseHistory.do";
            var urlArr = location.pathname.substring(1).split("/");
            historyURI = ("store" != urlArr[0] ? "/" + urlArr[0] : "") + historyURI, $.ajax({
                url: historyURI,
                type: "POST",
                data: data,
                success: function(result) {
                    $("#subscriptionHistoryPopup").css("top", "10px"), $("#subscriptionHistoryDiv").html(result)
                }
            })
        }
    }), $(".historyclose").click(function() {
        $("#subscriptionHistoryPopup").popup = null, $("#subscriptionHistoryDiv").html(null)
    });
    var payPeriodIdMap = {
            MONT: 1,
            QTER: 2,
            SMYR: 3,
            YEAR: 4
        },
        payperiodSwitchCheck = function(payperiod) {
            var currentPayPeriodId = payPeriodIdMap[StoreProperties.userPlan.payPeriod],
                payPeriodId = payPeriodIdMap[payperiod];
            return payPeriodId - currentPayPeriodId > 0 ? !0 : !1
        },
        changeDuration = function(payPeriod) {
            var error = (StoreProperties.serviceJSON.payPeriod_switch, !1);
            if (("YEAR" == payPeriod || "SMYR" == payPeriod || "QTER" == payPeriod || "MONT" == payPeriod) && (error = !0), error) {
                $("#upgradeDurationPopup #upgradePaymentDuration .cdError").remove();
                var nextDueDate = null;
                void 0 !== StoreProperties.userPlan && void 0 !== StoreProperties.userPlan.profileId && StoreProperties.userPlan.payPeriod === payPeriod && (nextDueDate = StoreProperties.userPlan.nextDueDate), "YEAR" == payPeriod ? (StoreProperties.selectedPlan.payPeriod = "YEAR", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("years", 1).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate, StoreProperties.service.setRecurringDue(), StoreProperties.selectedPlan.recurringDue = StoreProperties.service.recurringDue()) : "SMYR" == payPeriod ? (StoreProperties.selectedPlan.payPeriod = "SMYR", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("months", 6).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate, StoreProperties.service.setRecurringDue(), StoreProperties.selectedPlan.recurringDue = StoreProperties.service.recurringDue()) : "QTER" == payPeriod ? (StoreProperties.selectedPlan.payPeriod = "QTER", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("months", 3).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate, StoreProperties.service.setRecurringDue(), StoreProperties.selectedPlan.recurringDue = StoreProperties.service.recurringDue()) : (StoreProperties.selectedPlan.payPeriod = "MONT", nextDueDate = null !== nextDueDate ? moment(nextDueDate).format("YYYY-MM-DD") : moment(StoreProperties.today).add("months", 1).format("YYYY-MM-DD"), StoreProperties.selectedPlan.nextDueDate = nextDueDate, StoreProperties.service.setRecurringDue(), StoreProperties.selectedPlan.recurringDue = StoreProperties.service.recurringDue())
            } else $("#upgradeDurationPopup #upgradePaymentDuration").append("<div class='cdError'>" + i18n["store.payperiod.change.error"] + "</div>")
        };
    $("#upgradeDurationPopup .confirmOrderBtn").click(function() {
        var payPeriod = $("#upgradeDurationPopup #upgradePaymentDuration select").val(),
            action = payperiodSwitchCheck(payPeriod);
        return "Downgrade" != StoreProperties.serviceJSON.payPeriod_switch ? action ? !0 : ($("#upgradeDurationPopup #upgradePaymentDuration .cdError").remove(), $("#upgradeDurationPopup #upgradePaymentDuration").append("<div class='cdError'>" + i18n["store.payperiod.change.error"] + "</div>"), !1) : !0
    }), $("#upgradePaymentDuration select").change(function() {
        changeDuration($(this).val()), StoreUtil.upgradeDurationHandling(), $(".upgradeDurationRedHint").remove();
        var selectedPayPeriod = $(this).val();
        "YEAR" != selectedPayPeriod && "" != selectedPayPeriod ? $("#upgradePaymentDuration").append("<div class = 'upgradeDurationRedHint'>" + StoreUtil.getPricingObj("YEAR").discount + "% " + i18n["store.plans.year.discount"] + "</div>") : null != $(this).closest(".upgradeDurationRedHint") && $(".upgradeDurationRedHint").remove();
        var dueNow = StoreUtil.prorate().amount;
        "" != $(this).val() ? $("#upgradeTotalAmount").parent().show() : $("#upgradeTotalAmount").parent().hide(), $("#upgradeTotalAmount").text(StoreUtil.currencyFormat(dueNow))
    });
    var notify_msg = "";
    if (StoreProperties.validation.success) {
        if (StoreProperties.error) notify_msg = StoreProperties.error;
        else if (StoreProperties.userPlan && StoreProperties.userPlan.retry) notify_msg = i18n["validation.graceperiod"].replace("{0}", "/html/store/billing-details.html");
        else if (StoreProperties.cc && StoreProperties.userPlan) {
            var status = StoreProperties.userPlan.STATUS;
            if (status && "active" === status.toLowerCase() && "PayPal" !== StoreProperties.cc.type && "mobile" !== StoreProperties.cc.type) {
                var expiryMonth = parseInt(StoreProperties.cc.expiryMonth),
                    expiryYear = parseInt(StoreProperties.cc.expiryYear),
                    cc_expired = !1;
                if (expiryMonth > 0 && expiryYear > 0) {
                    var today = new Date(StoreProperties.today),
                        today_month = today.getMonth() + 1,
                        today_year = today.getFullYear();
                    expiryYear = 2 === expiryYear.toString().length ? 2e3 + expiryYear : expiryYear, today_year > expiryYear ? cc_expired = !0 : expiryYear === today_year && today_month >= expiryMonth && (cc_expired = !0)
                }
                if (cc_expired) {
                    var card_expiry = 10 > expiryMonth ? "0" + expiryMonth + " / " + expiryYear : expiryMonth + " / " + expiryYear;
                    notify_msg = i18n["validation.checkCardExpiry"].replace("{0}", card_expiry).replace("{1}", "/html/store/billing-details.html")
                }
            }
        }
    } else {
        document.cookie = "store.errors=;path=/;expires=Thu, 01 Jan 1970 00:00:01 GMT;";
        var error, validateResults = StoreProperties.validation;
        if (error = StoreProperties.error, validateResults.emailConfirmation && !notify_msg && (notify_msg = i18n[validateResults.emailConfirmation]), validateResults.orgUser && !notify_msg && (notify_msg = i18n[validateResults.orgUser]), validateResults.superAdmin && !notify_msg && (notify_msg = i18n[validateResults.superAdmin]), validateResults.crmOrgUser && !notify_msg && (notify_msg = i18n[validateResults.crmOrgUser]), validateResults.crmOrgAdmin && !notify_msg && (notify_msg = i18n[validateResults.crmOrgAdmin]), validateResults.crmOrgPrimaryContact && !notify_msg && (notify_msg = i18n[validateResults.crmOrgPrimaryContact]), !notify_msg) {
            if (validateResults.maintenanceMode) {
                var maintenanceModeError = validateResults.maintenanceMode;
                void 0 !== error ? error.maintenanceModeError || (error = error + "<br/>" + maintenanceModeError) : error = maintenanceModeError
            }
            if (validateResults.nsProfile) {
                var nsProfileError = validateResults.nsProfile;
                void 0 !== error ? error.nsProfileError || (error = error + "<br/>" + nsProfileError) : error = nsProfileError, void 0 !== StoreProperties.offlineToOnline && StoreProperties.offlineToOnline && (error = error + "<br/>" + i18n["zohostore.subscription.offlinetoOnlineMsg"])
            }
            if (validateResults.mobileSubscription) {
                var mobileSubscriptionError = validateResults.mobileSubscription;
                void 0 !== error ? error.mobileSubscriptionError || (error = error + "<br/>" + mobileSubscriptionError) : error = mobileSubscriptionError
            }
            if (validateResults.resellerCardProfile) {
                var resellerCardProfileError = validateResults.resellerCardProfile;
                void 0 !== error ? error.resellerCardProfileError || (error = error + "<br/>" + resellerCardProfileError) : error = resellerCardProfileError
            }
            if (validateResults.ospProfile) {
                var ospProfileError = validateResults.ospProfile;
                void 0 !== error ? error.ospProfileError || (error = error + "<br/>" + ospProfileError) : error = ospProfileError
            }
            if (validateResults.activeUsersCheck) {
                var activeUsersCountError = validateResults.activeUsersCheck;
                void 0 !== error ? error.activeUsersCountError || (error = error + "<br/>" + activeUsersCountError) : error = activeUsersCountError
            }
            if (validateResults.staleServiceData) {
                var staleDataError = validateResults.staleServiceData;
                void 0 !== error ? error.staleDataError || (error = error + "<br/>" + staleDataError) : error = staleDataError
            }
        }
        error && (notify_msg = error)
    }
    return {
        getPurchaseType: function() {
            var purchaseType = "recurring";
            if (StoreProperties.selectedPlan && StoreProperties.plans) {
                var plan = StoreProperties.selectedPlan.plan;
                if (StoreProperties.plans[plan]) {
                    var planObj = StoreProperties.plans[plan];
                    planObj.purchaseType && "credit" === planObj.purchaseType && (purchaseType = "credit")
                }
            }
            return purchaseType
        },
        setPayPeriod: function(payPeriod, fullPrice) {
            return changeDuration(payPeriod), StoreUtil.getPricingObj(payPeriod, fullPrice)
        },
        setAddon: function(id, name, value, type, classification, action) {
            "subscription" == type ? (StoreProperties.service.setAddon(id, name, value, type), StoreProperties.service.setDueNow()) : "enum" == type ? (StoreProperties.service.setAddon(id, name, value, type, classification, action), StoreProperties.service.setDueNow()) : "unit" == type ? (StoreProperties.service.setAddon(id, name, value, type, classification, action), StoreProperties.service.setDueNow()) : "user" == type && (StoreProperties.service.setAddon(id, name, value, type, classification, action), StoreProperties.service.setDueNow())
        },
        stopCancelEvent: function() {
            var data = {
                serviceId: serviceId,
                currencyId: StoreProperties.currencyId,
                country: StoreProperties.country,
                newPlan: JSON.stringify(StoreProperties.selectedPlan),
                addons: JSON.stringify(StoreProperties.addonsRecurringDue ? StoreProperties.addonsRecurringDue : {})
            };
            data[securityKey.csrfParamName] = securityKey.csrfToken, StoreProperties.userPlan && StoreProperties.userPlan.recurringDue > 0 && (data.oldPlan = JSON.stringify(StoreProperties.userPlan)), $.ajax({
                url: "/store/service.do?method=order&customId=" + StoreProperties.userDetails.customId,
                type: "POST",
                data: data,
                success: function(result) {
                    $(".freezeDiv").css("height", $(document).height()), (result.success || result.successDwn || result.provisioningError) && (StoreUtil.transaction(result), document.cookie = "STORECOUPONCODE=;path=/store/;expires=Thu, 01 Jan 1970 00:00:01 GMT;")
                },
                dataType: "JSON"
            })
        },
        renewSubscription: function() {
            var data = {
                serviceId: serviceId,
                newPlan: JSON.stringify(StoreProperties.selectedPlan)
            };
            data[securityKey.csrfParamName] = securityKey.csrfToken, StoreProperties.userPlan && StoreProperties.userPlan.recurringDue > 0 && (data.oldPlan = JSON.stringify(StoreProperties.userPlan)), $.ajax({
                url: "/store/service.do?method=renewSubscription&customId=" + StoreProperties.userDetails.customId,
                type: "POST",
                data: data,
                success: function(result) {
                    $(".freezeDiv").css("height", $(document).height()), (result.success || result.successDwn || result.provisioningError) && (StoreProperties.service.transaction(result), document.cookie = "STORECOUPONCODE=;path=/store/;expires=Thu, 01 Jan 1970 00:00:01 GMT;")
                },
                dataType: "JSON"
            })
        },
        switchToRecurring: function() {
            var data = {
                serviceId: serviceId,
                newPlan: JSON.stringify(StoreProperties.selectedPlan)
            };
            data[securityKey.csrfParamName] = securityKey.csrfToken, StoreProperties.userPlan && StoreProperties.userPlan.recurringDue > 0 && (data.oldPlan = JSON.stringify(StoreProperties.userPlan)), $.ajax({
                url: "/store/service.do?method=switchToRecurring&customId=" + StoreProperties.userDetails.customId,
                type: "POST",
                data: data,
                success: function(result) {
                    $(".freezeDiv").css("height", $(document).height()), (result.success || result.successDwn || result.provisioningError) && (StoreProperties.service.transaction(result), document.cookie = "STORECOUPONCODE=;path=/store/;expires=Thu, 01 Jan 1970 00:00:01 GMT;")
                },
                dataType: "JSON"
            })
        },
        offlineToOnline: function() {
            alignPopcontainer($(".processingMsg")), delete StoreProperties.selectedPlan.dueNowTooltip, StoreProperties.service.serviceDataCheck(), StoreProperties.cloneCard && (StoreProperties.selectedPlan.profileId = StoreProperties.cloneProfileId, delete StoreProperties.cc), 0 === StoreProperties.userPlan.recurringDue && (StoreProperties.selectedPlan.orgcreatedtime = orgcreatedtime);
            var data = {
                serviceId: serviceId,
                currencyId: StoreProperties.currencyId,
                country: StoreProperties.country,
                newPlan: JSON.stringify(StoreProperties.selectedPlan),
                addons: JSON.stringify(StoreProperties.addonsRecurringDue ? StoreProperties.addonsRecurringDue : {})
            };
            data[securityKey.csrfParamName] = securityKey.csrfToken, StoreProperties.userPlan && StoreProperties.userPlan.recurringDue > 0 && (data.oldPlan = JSON.stringify(StoreProperties.userPlan)), StoreProperties.cc && StoreProperties.cc.number && StoreProperties.cc.cvv && (StoreProperties.cc.number = encrypt(StoreProperties.cc.number), StoreProperties.cc.cvv = encrypt(StoreProperties.cc.cvv), data.ccDetails = JSON.stringify(StoreProperties.cc));
            var freezeDiv = $(".freezeDiv");
            freezeDiv.css("height", $(document).height()), $("html, body").animate({
                scrollTop: 60
            }, 300), $(".freezeDiv,.processingMsg,.processingBG").fadeIn(100), $(".offlinetoonline").css("z-index", freezeDiv.css("z-index") - 1), $.ajax({
                url: "/store/service.do?method=offlineToOnline&customId=" + StoreProperties.userDetails.customId,
                type: "POST",
                data: data,
                success: function(result) {
                    if ($(".freezeDiv").css("height", $(document).height()), result.success || result.successDwn || result.provisioningError) StoreUtil.transaction(result), document.cookie = "STORECOUPONCODE=;path=/store/;expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                    else if (result.sameBuyer) StoreUtil.showMessage(result.sameBuyer), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.transactionLimit) StoreUtil.showMessage(result.transactionLimit), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.paymentsError) StoreUtil.showMessage(result.paymentsError), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.activeUsersCheck) StoreUtil.showMessage(result.activeUsersCheck), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.checkCardCount) StoreUtil.showMessage(result.checkCardCount, !0), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.cardVerification) StoreUtil.showMessage(result.cardVerification, !0), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else {
                        var errors = "";
                        for (var error in result) "profileId" !== error && "transactionId" != error && "invoiceId" != error && (errors.length > 0 && (errors += "<br/>"), errors += i18n[result[error]] || result[error]);
                        $(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100);
                        var supportedPaymentMethods = StoreUtil.getSupportedPaymentMethods();
                        supportedPaymentMethods && supportedPaymentMethods.PAYPAL_EXPRESSCHECKOUT ? (trackClickEvent("Payments - Card Failure Page"), $("#transCardFailedPop,.newTransFailedPopup").show()) : (trackClickEvent("Payments - Failure Page"), $("#transFailedPop").show()), document.cookie = "store.errors=" + encodeURIComponent(errors) + ";path=/"
                    }
                },
                error: function() {
                    $(".freezeDiv").css("height", $(document).height()), $(".processingMsg,.processingBG").fadeOut(100);
                    var supportedPaymentMethods = StoreUtil.getSupportedPaymentMethods();
                    supportedPaymentMethods && supportedPaymentMethods.PAYPAL_EXPRESSCHECKOUT ? (trackClickEvent("Payments - Card Failure Page"), $("#transCardFailedPop,.newTransFailedPopup").show()) : (trackClickEvent("Payments - Failure Page"), $("#transFailedPop").show())
                },
                dataType: "JSON"
            })
        },
        placeOrder: function(confirmButton) {
            StoreUtil.setSpecialDiscountedValue(), delete StoreProperties.selectedPlan.dueNowTooltip, StoreProperties.service.serviceDataCheck(), StoreProperties.cloneCard && (StoreProperties.selectedPlan.profileId = StoreProperties.cloneProfileId, delete StoreProperties.cc), 0 === StoreProperties.userPlan.recurringDue && (StoreProperties.selectedPlan.orgcreatedtime = orgcreatedtime);
            var data = {
                serviceId: serviceId,
                currencyId: StoreProperties.currencyId,
                country: StoreProperties.country,
                newPlan: JSON.stringify(StoreProperties.selectedPlan),
                addons: JSON.stringify(StoreProperties.addonsRecurringDue ? StoreProperties.addonsRecurringDue : {})
            };
            data[securityKey.csrfParamName] = securityKey.csrfToken, StoreProperties.userPlan && StoreProperties.userPlan.recurringDue > 0 && (data.oldPlan = JSON.stringify(StoreProperties.userPlan)), StoreProperties.cc && StoreProperties.cc.number && StoreProperties.cc.cvv && (StoreProperties.cc.signedPaRes || (StoreProperties.cc.number = encrypt(StoreProperties.cc.number), StoreProperties.cc.cvv = encrypt(StoreProperties.cc.cvv)), data.ccDetails = JSON.stringify(StoreProperties.cc));
            var landingpage = StoreProperties.landingpagekey;
            "newpurchase" === landingpage ? ($(".freezeDiv").css({
                height: $(document).height(),
                opacity: 1
            }).fadeIn(100), $("html, body").animate({
                scrollTop: 60
            }, 300), $(".processingMsg,.processingBG").fadeIn(100), alignPopContLeft($(".processingMsg")), $(".processingMsg").css({
                top: "35%"
            })) : ($(confirmButton).addClass("primaryButton-loader"), buttonPreLoader(), $(confirmButton).prop("disabled", !0).parents(".confirmDiv").css("pointer-events", "none")), $.ajax({
                url: "/store/service.do?method=order&customId=" + StoreProperties.userDetails.customId,
                type: "POST",
                data: data,
                success: function(result) {
                    if ($("#placeOrder").removeClass("primaryButton-loader"), $("#placeOrder").prop("disabled", !1), $(".freezeDiv").removeClass("manageplanFreeze"), displayFreezeDiv(), $("html, body").animate({
                            scrollTop: 60
                        }, 300), result.success || result.successDwn || result.provisioningError) StoreUtil.transaction(result), document.cookie = "STORECOUPONCODE=;path=/store/;expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                    else if (result.mode && "AUTHENTICATION" === result.mode) StoreProperties.cc.MERCHANT_REFERENCE = result.MERCHANT_REFERENCE, StoreProperties.cc.PAYERAUTH_REF_ID = result.PAYERAUTH_REF_ID, StoreProperties.cc.PAREQ_XID = result.XID, createPAEnrollForm(result.ACS_URL, result.PAREQ, result.XID, result.TERM_URL), $("form[name=PAEnrollForm]").submit(), $(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), $(".iframe-loader,#paInlineFrame").fadeIn(100).css("z-index", "100"), document.cookie = "STORECOUPONCODE=;path=/store/;expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                    else if (result.sameBuyer) StoreUtil.showMessage(result.sameBuyer), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.transactionLimit) StoreUtil.showMessage(result.transactionLimit), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.paymentsError) StoreUtil.showMessage(result.paymentsError), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.activeUsersCheck) StoreUtil.showMessage(result.activeUsersCheck), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.checkCardCount) StoreUtil.showMessage(result.checkCardCount, !0), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else if (result.cardVerification) StoreUtil.showMessage(result.cardVerification, !0), $("#placeOrder").live("click", placeOrder), $("#placeOrder").removeClass("graybtn").addClass("greenButton"), $("#placeOrder").removeAttr("disabled"), $(".cancelLink").css("visibility", "hidden");
                    else {
                        var errors = "";
                        for (var error in result) "profileId" !== error && "transactionId" != error && "invoiceId" != error && (errors.length > 0 && (errors += "<br/>"), errors += i18n[result[error]] || result[error]);
                        $(".freezeDiv").css("height", $(document).height()), $(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100);
                        var supportedPaymentMethods = StoreUtil.getSupportedPaymentMethods();
                        supportedPaymentMethods && supportedPaymentMethods.PAYPAL_EXPRESSCHECKOUT ? (trackClickEvent("Payments - Card Failure Page"), $("#transCardFailedPop,.newTransFailedPopup").show()) : (trackClickEvent("Payments - Failure Page"), $("#transFailedPop").show()), document.cookie = "store.errors=" + encodeURIComponent(errors) + ";path=/"
                    }
                },
                error: function() {
                    $("#placeOrder").removeClass("primaryButton-loader"), $("html, body").animate({
                        scrollTop: 60
                    }, 300), $(".freezeDiv").css("height", $(document).height()), $(".freezeDiv").fadeIn(100), $(".processingMsg,.processingBG").fadeOut(100);
                    var supportedPaymentMethods = StoreUtil.getSupportedPaymentMethods();
                    supportedPaymentMethods && supportedPaymentMethods.PAYPAL_EXPRESSCHECKOUT ? (trackClickEvent("Payments - Card Failure Page"), $("#transCardFailedPop,.newTransFailedPopup").show()) : (trackClickEvent("Payments - Failure Page"), $("#transFailedPop").show(), $(".freezeDiv").addClass("manageplanFreeze"))
                },
                dataType: "JSON"
            })
        },
        transaction: function(result) {
            var trans_result = {
                result: result
            };
            StoreProperties.transaction = trans_result, StoreProperties.service.transaction(result)
        },
        showTransactionPopup: function(transactionMsgJSON) {
            if (transactionMsgJSON) {
                try {
                    if (null != window.opener && !window.opener.closed) {
                        var msg = {};
                        msg.mode = "SubscriptionChange", window.opener.postMessage(msg, "*")
                    }
                } catch (e) {}
                var conversionValue, conversionMode;
                if (transactionMsgJSON.amount = isDiscountApplied() ? StoreProperties.selectedPlan.discDueNow : transactionMsgJSON.amount, "downgradeToFree" !== transactionMsgJSON.type || subscriptionPage)
                    if ("downgrade" === transactionMsgJSON.type || "downgradeToFree" === transactionMsgJSON.type) $(".freezeDiv").css("opacity", 1), $("#confirmPopup div:first-child").removeAttr("style"), conversionMode = "downgradeToFree" === transactionMsgJSON.type ? "CANCEL" : "DOWNGRADE", conversionValue = "downgrade" === transactionMsgJSON.type ? StoreProperties.userPlan.recurringDue - StoreProperties.selectedPlan.recurringDue : StoreProperties.userPlan.recurringDue, trackClickEvent("Payments - Manage - Success Page"), $(".transactionDetailsTable").hide(), void 0 !== transactionMsgJSON.transactionId ? $(".successMsg .transactionId").html(transactionMsgJSON.transactionId).parent("tr").show() : $(".successMsg .transactionId").html("-"), $(".successMsg .amountPaid").hide(), $(".confPop").find(".confTitle").html(transactionMsgJSON.confTitle).show(), void 0 !== transactionMsgJSON.confTitleMsg ? $(".confPop").find(".confTitleMsg").html(transactionMsgJSON.confTitleMsg).show() : $(".confPop").find(".confTitleMsg").hide(), "downgradeToFree" === transactionMsgJSON.type ? (void 0 !== i18n["zohostore.manageplan." + StoreProperties.serviceName + ".freeplanmsg.hint"] && $("#freePlanMsg #upgradehint").text(i18n["zohostore.manageplan." + StoreProperties.serviceName + ".freeplanmsg.hint"]), $("#freePlanMsg .serviceName").text(i18n["store.vm." + StoreProperties.serviceName + ".name"]), $("#confirmPopup .closeTxt").addClass("downToFree"), $(".successMsg").siblings("table").removeClass("mT20"), $(".successMsg").hide(), $(".successMsg .date").parent("tr").hide(), $("#feedbackPop, #confirmCancelMsg").fadeOut(100), $("#messageinfo").hide()) : ($("#confirmPopup .closeTxt").removeClass("downToFree"), $(".successMsg").siblings("table").addClass("mT20"), $(".successMsg").show(), $(".successMsg .date").html(formatDate(transactionMsgJSON.nextDueDate)).parent("tr").show(), $("#feedbackPop, #confirmCancelMsg").fadeOut(100)), void 0 !== StoreProperties.renderingJSON.multipleAddonsPurchase && ($("#confirmPopup .confTitle").addClass("reducedColor mT10"), $(".downgradeDetailTitle").html("Downgrade Details:"));
                    else if ("upgrade" === transactionMsgJSON.type) {
                    if (conversionMode = "UPGRADE", conversionValue = StoreProperties.selectedPlan.dueNow, trackClickEvent("Payments - Manage - Success Page"), void 0 !== transactionMsgJSON.transactionId ? $(".successMsg .transactionId").html(transactionMsgJSON.transactionId).parent("tr").show() : $(".successMsg .transactionId").html("-"), $(".successMsg .date").html(formatDate(StoreProperties.today)).parent("tr").show(), $(".confPop").find(".confTitle").html(transactionMsgJSON.confTitle).show(), void 0 !== transactionMsgJSON.confTitleMsg ? $(".confPop").find(".confTitleMsg").html(transactionMsgJSON.confTitleMsg).show() : $(".confPop").find(".confTitleMsg").hide(), transactionMsgJSON.chargeOnNextRetry ? (StoreProperties.userPlan.retry && StoreProperties.userPlan.retryDate ? $("#confirmPopup .infoMsg").text(i18n["zohostore.transactionsuccess.chargeOnNextRetry"].replace("{0}", formatDate(StoreProperties.userPlan.retryDate))).show() : $("#confirmPopup .infoMsg").text(i18n["zohostore.transactionsuccess.chargeOnNextRenewal"]).show(), $(".successMsg .amountPaid").hide()) : ($("#confirmPopup .infoMsg").hide(), transactionMsgJSON.amount ? ($(".successMsg .amountPaid .pRata").html(transactionMsgJSON.amount), $(".successMsg .amountPaid").show()) : $(".successMsg .amountPaid").hide()), void 0 !== StoreProperties.renderingJSON.multipleAddonsPurchase) {
                        $(".transactionDetailsTable").hide(), $(".multiAddonConfirmDetails").show(), $(".multiAddonConfirmDetails .nxtRenewalDate").html(formatDate(StoreProperties.selectedPlan.nextDueDate));
                        var recurringDueAmount = isDiscountApplied() ? StoreProperties.selectedPlan.discRecurringDue : StoreProperties.selectedPlan.recurringDue;
                        $("#confirmPopup .nextRenewalAmount").html(formatAmount(recurringDueAmount, !0)), $(".multiAddonConfirmDetails .transactionId").html(transactionMsgJSON.transactionId), $(".multiAddonConfirmDetails .pRata").html(formatAmount(transactionMsgJSON.amount, !0))
                    }
                    $("#confirmPopup .closeTxt").removeClass("downToFree")
                } else if ("offlinetoonline" === transactionMsgJSON.type) conversionMode = "RENEWAL", conversionValue = StoreProperties.selectedPlan.recurringDue, trackClickEvent("Payments - Offline to Online"), void 0 !== transactionMsgJSON.transactionId ? $(".successMsg .transactionId").html(transactionMsgJSON.transactionId).parent("tr").show() : $(".successMsg .transactionId").html("-"), $(".successMsg .date").html(formatDate(transactionMsgJSON.nextDueDate)).parent("tr").show(), $(".confPop").find(".confTitle").html(transactionMsgJSON.confTitle).show(), void 0 !== transactionMsgJSON.confTitleMsg ? $(".confPop").find(".confTitleMsg").html(transactionMsgJSON.confTitleMsg).show() : $(".confPop").find(".confTitleMsg").hide(), transactionMsgJSON.chargeOnNextRetry ? (StoreProperties.userPlan.retry && StoreProperties.userPlan.retryDate ? $("#confirmPopup .infoMsg").text(i18n["zohostore.transactionsuccess.chargeOnNextRetry"].replace("{0}", formatDate(StoreProperties.userPlan.retryDate))).show() : $("#confirmPopup .infoMsg").text(i18n["zohostore.transactionsuccess.chargeOnNextRenewal"]).show(), $(".successMsg .amountPaid").hide()) : ($(".transHeader .amountPaid").html(i18n["zohostore.newsubscription.card.nextrenewalamount"]), $("#confirmPopup .infoMsg").hide(), $(".successMsg .amountPaid .pRata").html(StoreProperties.userPlan.recurringDue), $(".successMsg .amountPaid").show()), $("#confirmPopup .closeTxt").removeClass("downToFree");
                else {
                    0 === StoreProperties.userPlan.recurringDue && (conversionMode = StoreProperties.inactiveProfile ? "REACTIVATE" : "PURCHASE", conversionValue = StoreProperties.selectedPlan.recurringDue, trackClickEvent("Payments - New - Success Page")), void 0 !== transactionMsgJSON.transactionId ? $(".cofirmMsgDiv .transactionId").html(transactionMsgJSON.transactionId).parent("tr").show() : $(".cofirmMsgDiv .transactionId").html("-"), $(".cofirmMsgDiv .confTitle").html(transactionMsgJSON.confTitle), $(".cofirmMsgDiv .confTitleMsg").html(transactionMsgJSON.confTitleMsg), $(".cofirmMsgDiv .serviceName").html(transactionMsgJSON.serviceName), $(".cofirmMsgDiv .planName").html(transactionMsgJSON.planName), $(".cofirmMsgDiv .transactionType").html(i18n["zohostore.newsubscription.purchase"]), $(".cofirmMsgDiv .nextDueDate").html(formatDate(transactionMsgJSON.nextDueDate)), $(".cofirmMsgDiv .transactionDate").html(formatDate(StoreProperties.today));
                    var purchaseMsg, thankyouMsg;
                    1 === StoreProperties.paymentType && "credit" !== StoreProperties.purchaseType ? (purchaseMsg = StoreProperties.selectedPlan.freeOTP ? i18n["zohostore.newsubscription.purchase.onetimeSuccessMsg"] : i18n["zohostore.newsubscription.purchase.paypalSuccessmsg"].replace("{0}", transactionMsgJSON.SubFreq).replace("{1}", formatDate(transactionMsgJSON.nextDueDate)), thankyouMsg = i18n["zohostore.newsubscription.purchase.thankyou"]) : "credit" !== StoreProperties.purchaseType && (purchaseMsg = StoreProperties.selectedPlan.freeOTP ? i18n["zohostore.newsubscription.purchase.onetimeSuccessMsg"] : StoreProperties.selectedPlan.purchaseOrder ? i18n["zohostore.newsubscription.purchase.purchaseOrderSuccessMsg"].replace("{0}", transactionMsgJSON.SubFreq).replace("{1}", formatDate(transactionMsgJSON.nextDueDate)) : StoreProperties.renderingJSON.edition ? i18n["zohostore.newsubscription.purchase.success.msg"].replace("{0}", transactionMsgJSON.SubFreq).replace("{1}", formatDate(transactionMsgJSON.nextDueDate)) : i18n["zohostore.newsubscription.purchase.successmsg"].replace("{0}", transactionMsgJSON.SubFreq).replace("{1}", formatDate(transactionMsgJSON.nextDueDate)), thankyouMsg = i18n["zohostore.newsubscription.purchase.thankyou"]), purchaseMsg = purchaseMsg ? purchaseMsg : "", thankyouMsg = thankyouMsg ? thankyouMsg : "", $(".cofirmMsgDiv .purchaseMsg").html(purchaseMsg), $(".cofirmMsgDiv .thankyouMsg").html(thankyouMsg), $(".cofirmMsgDiv .paypalPurchaseMsg").html(purchaseMsg);
                    var serviceUrl = void 0 !== StoreProperties.successUrl && "" !== StoreProperties.successUrl ? StoreProperties.serviceURL + decodeURIComponent(StoreProperties.successUrl) : StoreProperties.serviceJSON.homeUrlPart ? StoreProperties.serviceURL + StoreProperties.serviceJSON.homeUrlPart : StoreProperties.serviceURL;
                    if (void 0 !== StoreProperties.renderingJSON.signUpFlow && StoreProperties.renderingJSON.signUpFlow === !0 ? ($(".cofirmMsgDiv #viewPlanDetails").hide(), $(".cofirmMsgDiv #serviceLink").attr("href", serviceUrl).html(i18n["zohostore.newsubscription.purchase.gotoservice"].replace("{0}", i18n["store.vm." + StoreProperties.serviceName + ".name"])), $(".cofirmMsgDiv #serviceLink").removeAttr("target")) : (void 0 !== StoreProperties.serviceJSON.backToService && StoreProperties.serviceJSON.backToService && (serviceUrl = StoreProperties.serviceJSON.backToServiceURL, $(".cofirmMsgDiv #serviceLink").removeAttr("target")), $(".cofirmMsgDiv #serviceLink").attr("href", serviceUrl).html(i18n["zohostore.newsubscription.purchase.gotoservice"].replace("{0}", i18n["store.vm." + StoreProperties.serviceName + ".name"])), "payment" === getURLHash() && $(".cofirmMsgDiv #viewPlanDetails").attr("href", "#subscription?serviceId=" + StoreProperties.resJSON.serviceId + "&customId=" + StoreProperties.resJSON.customId + "&view=" + StoreProperties.resJSON.newPlan.view), "credit" === StoreProperties.purchaseType && $(".cofirmMsgDiv #viewPlanDetails").hide()), $(".cofirmMsgDiv .amount").html(transactionMsgJSON.amount).closest("tr").show(), $("#confirmPopup .closeTxt").removeClass("downToFree"), $("#logoImg,.logoImg").attr("src", "../../images/store/" + StoreProperties.serviceName.toLowerCase() + "-m.png"), void 0 !== StoreProperties.renderingJSON.multipleAddonsPurchase) {
                        $(".transactionDetailsTable").show(), $(".multiAddonConfirmDetails").show(), $(".multiAddonConfirmDetails .date").html(formatDate(StoreProperties.today));
                        var recurringDueAmount = isDiscountApplied() ? StoreProperties.selectedPlan.discRecurringDue : StoreProperties.selectedPlan.recurringDue;
                        $("#confirmPopup .nextRenewalAmount").html(formatAmount(recurringDueAmount, !0)), $(".multiAddonConfirmDetails .transactionId").html(transactionMsgJSON.transactionId), $(".multiAddonConfirmDetails .pRata").html(transactionMsgJSON.amount)
                    }
                } else conversionMode = "DOWNGRADE", conversionValue = StoreProperties.userPlan.recurringDue, trackClickEvent("Payments - Manage - Success Page"), $(".cofirmMsgDiv .transactionId").parent("tr").hide(), $(".cofirmMsgDiv .confTitle").html(transactionMsgJSON.confTitle), $(".cofirmMsgDiv .confTitleMsg").html(transactionMsgJSON.confTitleMsg), $(".cofirmMsgDiv .serviceName").parent("tr").hide(), $(".cofirmMsgDiv .planName").parent("tr").hide(), $(".cofirmMsgDiv .transactionType").parent("tr").hide(), $(".cofirmMsgDiv .paypalPurchaseMsg").parent("tr").hide(), $(".cofirmMsgDiv .purchaseMsg").parent("tr").hide(), $(".cofirmMsgDiv #serviceLink").attr("href", StoreProperties.serviceURL).html(i18n["zohostore.newsubscription.purchase.gotoservice"].replace("{0}", i18n["store.vm." + StoreProperties.serviceName + ".name"])), $(".cofirmMsgDiv .amount").closest("tr").hide(), $("#confirmPopup .closeTxt").removeClass("downToFree"), $("#logoImg,.logoImg").attr("src", "../../images/store/" + StoreProperties.serviceName.toLowerCase() + "-m.png"), $("#viewPlanDetails, #serviceLink").hide(), $("#messageinfo").hide(), void 0 !== StoreProperties.renderingJSON.multipleAddonsPurchase && ($("#confirmPopup .confTitle").addClass("reducedColor mT10"), $(".downgradeDetailTitle").html("Downgrade Details:"))
            }
            try {
                if (conversionMode) {
                    var newPlanStr = StoreProperties.serviceName + ".plan." + StoreProperties.selectedPlan.plan;
                    GTMConversion(StoreProperties.serviceId, conversionMode, i18n[newPlanStr])
                }
                if (StoreProperties.adwordsconversion && conversionMode) {
                    conversionMode = "CANCEL" === conversionMode ? "DOWNGRADE" : conversionMode;
                    var conversionLabel, conversionLabelKey = "PURCHASE" === conversionMode || "REACTIVATE" === conversionMode ? "CONVERSION_VALUE_" + conversionMode + "_" + StoreProperties.selectedPlan.payPeriod : "CONVERSION_VALUE_" + conversionMode;
                    StoreProperties.adwordsconversion[conversionLabelKey] ? conversionLabel = StoreProperties.adwordsconversion[conversionLabelKey] : ("PURCHASE" === conversionMode || "REACTIVATE" === conversionMode) && (conversionLabelKey = "CONVERSION_VALUE_" + conversionMode, conversionLabel = StoreProperties.adwordsconversion[conversionLabelKey]), AdwordsConversion(StoreProperties.adwordsconversion.CONVERSION_ID, conversionLabel, conversionValue)
                }
                if (StoreProperties.googleanalytics && conversionMode) {
                    var orderid = transactionMsgJSON.transactionId ? transactionMsgJSON.transactionId : -1,
                        amount = transactionMsgJSON.amount && transactionMsgJSON.amount > 0 ? transactionMsgJSON.amount : 0,
                        currPlanStr = StoreProperties.serviceName + ".plan." + StoreProperties.userPlan.plan,
                        currPlan = i18n[currPlanStr],
                        newPlanStr = StoreProperties.serviceName + ".plan." + StoreProperties.selectedPlan.plan,
                        newPlan = i18n[newPlanStr];
                    pushTranstoGA(StoreProperties.serviceName, StoreProperties.googleanalytics.SERVICE_CODE, orderid, void 0, currPlan, newPlan, amount, conversionMode)
                }
            } catch (e) {}
            if ($(".freezeDiv").css("height", $(document).height()), "purchase" === transactionMsgJSON.type || "downgradeToFree" === transactionMsgJSON.type && !subscriptionPage) $(".freezeDiv").fadeIn(100), $(".processingMsg,.processingBG").fadeOut(100), $("html,body").animate({
                scrollTop: 0
            }, "slow"), $(".subscriptionDiv").hide(), $(".freezeDiv,#footer_content").fadeOut(300), $(".cofirmMsgDiv").addClass("confirmMsgPopup").fadeIn(300);
            else {
                $(".successMsg .invoice").closest("tr").hide(), $(".processingMsg,.processingBG").fadeOut(100), $(".hConfPop").fadeIn(200);
                var addOn = $(this).attr("name");
                $(".manageAddonCont,.increaseBG,.confirmDiv,.addonPriceChange,.addonPriceChange > div").hide(), alignPopContLeft($("#confirmPopup")), $(".confPop").fadeIn(400), popupPositioning($(".confPop")), $(".curr" + addOn).find(".n" + addOn + "Price").html(formatAmount(0)), $(".hConfPop").attr("addon", addOn)
            }
        },
        getPricingObj: function() {
            return StoreProperties.pricing[StoreProperties.currencyCode]
        },
        getPricingObj: function(payperiod, fullPrice) {
            var json = StoreProperties.pricing[StoreProperties.currencyCode][payperiod];
            return fullPrice && json && json.fullprice ? json.fullprice : json
        },
        getCurrencyCodePricingObj: function(currencyCode) {
            return StoreProperties.pricing[currencyCode]
        },
        getCurrencyCode: function(currencyId) {
            var currencyCodes = StoreProperties.currencyJSON.currency_code;
            return currencyCodes ? currencyCodes[currencyId] : void 0
        },
        getCurrencySymbol: function(currencyCode) {
            var currencySymbols = StoreProperties.currencyJSON.currencySymbols;
            return currencySymbols ? currencySymbols[currencyCode] : void 0
        },
        getCurrencyId: function(currencyCode) {
            var currencyCodes = StoreProperties.currencyJSON.currencycode_id;
            return currencyCodes ? currencyCodes[currencyCode] : void 0
        },
        getFormattedAmount: function(amount) {
            var formattedAmt = amount,
                conversionFactor = StoreProperties.conversionFactor;
            if (conversionFactor) {
                var currencyConvFact = conversionFactor[StoreProperties.currencyCode];
                currencyConvFact && 1 !== parseFloat(currencyConvFact) && (formattedAmt = amount * parseFloat(currencyConvFact), formattedAmt = StoreUtil.round(formattedAmt, StoreProperties.currency.decimals.total))
            }
            return formattedAmt
        },
        getPaymentGatewayId: function(serviceId, country, platform) {
            var paymentGatewayId = 0,
                gateWayInfo = StoreProperties.gatewayMapping;
            if (gateWayInfo[country]) {
                var servicePropMapping = gateWayInfo[country];
                if (servicePropMapping[serviceId]) {
                    var paymentsProp, platformConfig = servicePropMapping[serviceId];
                    paymentsProp = platform && platformConfig[platform] ? platformConfig[platform] : platformConfig.WEB, paymentGatewayId = paymentsProp.GATEWAYCODE
                }
            }
            return paymentGatewayId
        },
        getSupportedCards: function(currencyId, serviceId) {
            var country, supportedCards = ["Visa", "MasterCard", "AMEX", "Discover", "JCB", "DinersClub"],
                paymentGateways = StoreProperties.paymentGatewayJSON.id_paymentgateways;
            if (currencyId) {
                var cc_country = getCurrencyCountry(currencyId);
                country = cc_country.toLowerCase()
            } else StoreProperties.country && (country = StoreProperties.country.toLowerCase());
            var service = serviceId ? serviceId : StoreProperties.serviceId,
                paymentGatewayId = StoreUtil.getPaymentGatewayId(service, country);
            if (paymentGateways[paymentGatewayId]) {
                var paymentGatewayJSON = paymentGateways[paymentGatewayId],
                    suppCards = paymentGatewayJSON.supportedCards;
                suppCards && suppCards[country] && (supportedCards = suppCards[country])
            }
            return supportedCards
        },
        getPaymentGatewaySupportedCards: function(currencyId, paymentGatewayId) {
            var country, supportedCards = ["Visa", "MasterCard", "AMEX", "Discover", "JCB", "DinersClub"],
                paymentGateways = StoreProperties.paymentGatewayJSON.id_paymentgateways;
            if (currencyId) {
                var cc_country = getCurrencyCountry(currencyId);
                country = cc_country.toLowerCase()
            } else StoreProperties.country && (country = StoreProperties.country.toLowerCase());
            if (paymentGatewayId && paymentGateways[paymentGatewayId]) {
                var paymentGatewayJSON = paymentGateways[paymentGatewayId],
                    suppCards = paymentGatewayJSON.supportedCards;
                suppCards && suppCards[country] && (supportedCards = suppCards[country])
            }
            return supportedCards
        },
        getSupportedPaymentMethods: function(currencyId, serviceId) {
            var supportedPaymentMethods = {
                PAYPAL_EXPRESSCHECKOUT: !1,
                CC_MULTIPLE_PRODUCT_PURCHASE: !1
            };
            if (StoreProperties.renderingJSON.resellerFlow) return supportedPaymentMethods;
            var country, paymentGateways = StoreProperties.paymentGatewayJSON.id_paymentgateways;
            if (currencyId) {
                var cc_country = getCurrencyCountry(currencyId);
                country = cc_country.toLowerCase()
            } else StoreProperties.country && (country = StoreProperties.country.toLowerCase());
            var service = serviceId ? serviceId : StoreProperties.serviceId,
                paymentGatewayId = StoreUtil.getPaymentGatewayId(service, country);
            if (paymentGateways[paymentGatewayId]) {
                var paymentGatewayJSON = paymentGateways[paymentGatewayId],
                    suppPayMethods = paymentGatewayJSON.supportedPaymentMethods;
                suppPayMethods && suppPayMethods[country] && (supportedPaymentMethods = suppPayMethods[country])
            }
            return supportedPaymentMethods
        },
        switchCurrency: function(currencyCode) {
            var currencySupported = !1;
            if ($.each(StoreProperties.supportedCurrencies, function(i, obj) {
                    obj === currencyCode && (currencySupported = !0)
                }), currencyCode && currencySupported) {
                StoreProperties.currencyCode = currencyCode;
                var currencyId = StoreUtil.getCurrencyId(currencyCode);
                StoreProperties.currencyId = currencyId;
                var country = getCurrencyCountry(currencyId);
                StoreProperties.country = country, StoreProperties.currency = getCurrencyCountryObj(country)
            }
            return currencySupported
        },
        getItemPrice: function(pricing, payPeriod, currency, item, count) {
            var priceJSON = pricing[currency][payPeriod];
            return priceJSON = priceJSON && priceJSON.fullprice ? priceJSON.fullprice : priceJSON, count ? priceJSON[item] * count : priceJSON[item]
        },
        getPriceOfAllCurrencies: function(pricing, payPeriod, label, amount, multiplier) {
            var resultJSON = {},
                keys = Object.keys(pricing);
            return $.each(keys, function(i, key) {
                if (amount || 0 === amount) resultJSON[key] = amount;
                else {
                    var priceJSON = pricing[key][payPeriod];
                    priceJSON = priceJSON && priceJSON.fullprice ? priceJSON.fullprice : priceJSON;
                    var tempPricing = "object" == typeof priceJSON[label] ? priceJSON[label][0].price : priceJSON[label];
                    resultJSON[key] = multiplier ? multiplier * tempPricing : tempPricing
                }
            }), resultJSON
        },
        applyValidationAction: function() {
            notify_msg && ($("#message_notification #errormsg").html(notify_msg), $("#message_notification").removeClass("hide")), StoreProperties.readOnlyMode && ($("tr[rows]").addClass("hoverInactive"), $(".upgradeBtn").die(), $(".changeperiod").addClass("disChangePeriod").remove(), $(".trynow").remove(), $(".priceSwitching").die(), $("#cancelSubscription, .buyCredits, .managePlanAddon .welcomeScr, .managePlanAddon .buyCredits, .managePlanAddon #payperiodChange").remove(), $(".managePlanAddon .nubsinglePlanFeature").addClass("t0")), StoreProperties.validation.success || (StoreProperties.validation.staleServiceData || StoreProperties.validation.nsProfile || StoreProperties.validation.resellerCardProfile || StoreProperties.validation.maintenanceMode || StoreProperties.validation.ospProfile || StoreProperties.validation.mobileSubscription) && ($("tr[rows]").addClass("hoverInactive"), $(".changeperiod").addClass("disChangePeriod").remove(), $("#cancelSubscription, .buyCredits, .managePlanAddon .welcomeScr, .managePlanAddon .buyCredits, .managePlanAddon #payperiodChange").remove(), $(".managePlanAddon .nubsinglePlanFeature").addClass("t0"), (StoreProperties.validation.nsProfile || StoreProperties.validation.resellerCardProfile || StoreProperties.validation.ospProfile) && ($("#payperiodChange").remove(), $(".upgradeBtn").die(), $(".trynow").remove(), $(".priceSwitching").die())), StoreProperties.cancelAtEndProfile && ($("tr[rows]").addClass("hoverInactive"), $(".changeperiod").addClass("disChangePeriod").remove(), $("#cancelSubscription, .buyCredits, .managePlanAddon .welcomeScr, .managePlanAddon .buyCredits, .managePlanAddon #payperiodChange").remove(), $(".managePlanAddon .nubsinglePlanFeature").addClass("t0"))
        },
        switchPricing: function(period) {
            for (var pricingJSON = StoreUtil.getPricingObj(period), newPayPeriod = "YEAR" == period ? "yearly" : "monthly", plantypes = StoreProperties.renderingJSON.planTypes, i = 0; i < plantypes.length; i++)
                for (var plans = plantypes[i].plans, k = 0; k < plans.length; k++) {
                    var plan = plans[k];
                    if (plan.frequency = "YEAR" == period ? "year" : "month", plan.price = pricingJSON[plan.plan] ? pricingJSON[plan.plan] : 0, plan.features = plan[newPayPeriod + "_features"] ? plan[newPayPeriod + "_features"] : plan.features, plan.addOn) {
                        for (var addons = plan.addOn, a = 0; a < addons.length; a++) {
                            var addon = addons[a];
                            addon.price = pricingJSON[addon.addonid]
                        }
                        plan.addOn = addons
                    }
                }
            subscriptionInfo.planTypes = plantypes
        },
        isEligible: function(totalCount, activeCount) {
            return totalCount >= activeCount ? !0 : !1
        },
        trialHandling: function() {
            if (void 0 !== StoreProperties.plans && void 0 !== StoreProperties.serviceJSON.TrialOpt && "NA" !== StoreProperties.serviceJSON.TrialOpt) {
                if (void 0 !== userTrials && ($.each(userTrials, function(key, value) {
                        for (var i = 0; i < trialPlans.length; ++i)
                            if (parseInt(key) === parseInt(trialPlans[i]) && 0 === parseInt(value.trialstatus)) {
                                trialPlanId = key;
                                break
                            }
                    }), $.each(userTrials, function(key, value) {
                        for (var i = 0; i < trialPlans.length; ++i)
                            if (parseInt(key) === parseInt(trialPlans[i]) && 1 === parseInt(value.trialstatus) && -1 !== value.profileId.indexOf("DP")) {
                                freeTrialEnabled = !0;
                                break
                            }
                    })), void 0 !== StoreProperties.plans && !subscriptionPage)
                    if (void 0 !== StoreProperties.userPlan && -1 !== StoreProperties.serviceJSON.TrialOpt.indexOf("PT"))
                        if (void 0 !== userTrials && null !== trialPlanId) 0 === parseInt(userTrials[trialPlanId].trialremainingdays) ? $(".trialNotifier[planid=" + [trialPlanId] + "]").text(i18n["store.trial.message.today"]) : parseInt(userTrials[trialPlanId].trialremainingdays) > 0 && $(".trialNotifier[planid=" + [trialPlanId] + "]").text(i18n["store.trial.message"].replace("{0}", userTrials[trialPlanId].trialremainingdays));
                        else {
                            var subOrder = trialOrderMap[trialPlansMap[StoreProperties.userPlan.plan]];
                            $(".trialNotifier").each(function() {
                                trialOrderMap[$(this).attr("planid")] > subOrder && ($(this).text(i18n["store.trial.startfree"]), $(this).addClass("pointer trynow"), $(this).removeClass("hide"))
                            })
                        }
                else void 0 === userTrials || null === trialPlanId || void 0 !== StoreProperties.userPlan && (void 0 === StoreProperties.userPlan || void 0 !== StoreProperties.userPlan.profileId) || freeTrialEnabled ? void 0 !== StoreProperties.userPlan && (void 0 === StoreProperties.userPlan || void 0 !== StoreProperties.userPlan.profileId) || -1 === StoreProperties.serviceJSON.TrialOpt.indexOf("FTE") || freeTrialEnabled || $(".trialNotifier").each(function() {
                    $(this).text(i18n["store.trial.startfree"]), $(this).addClass("pointer trynow"), $(this).removeClass("hide")
                }) : ((-1 !== StoreProperties.serviceJSON.TrialOpt.indexOf("FTV") || -1 !== StoreProperties.serviceJSON.TrialOpt.indexOf("FTE")) && (0 === parseInt(userTrials[trialPlanId].trialremainingdays) ? $(".trialNotifier[planid=" + [trialPlansMap[trialPlanId]] + "]").text(i18n["store.trial.message.today"]).removeClass("hide").addClass("tried") : parseInt(userTrials[trialPlanId].trialremainingdays) > 0 && $(".trialNotifier[planid=" + [trialPlansMap[trialPlanId]] + "]").text(i18n["store.trial.message"].replace("{0}", userTrials[trialPlanId].trialremainingdays)).removeClass("hide").addClass("tried")), -1 !== StoreProperties.serviceJSON.TrialOpt.indexOf("FTE") && $(".trialNotifier").each(function() {
                    $(this).attr("planid") !== trialPlansMap[trialPlanId] && ($(this).text(i18n["store.trial.startfree"]), $(this).addClass("pointer trynow"), $(this).removeClass("hide"))
                }));
                $(".trynow").live("click", function() {
                    $(".trynow").die("click");
                    var trialdata = {
                        serviceid: serviceId,
                        action: "NewTrial",
                        zId: StoreProperties.userDetails.customId,
                        emailid: StoreProperties.userDetails.EMAIL
                    };
                    trialdata.planid = trialPlansMap[$(this).attr("planid")];
                    var currPlanId = StoreProperties.userPlan.plan;
                    "Free" !== StoreProperties.plans[currPlanId].name ? trialdata.profileid = StoreProperties.userPlan.profileId : void 0 !== userTrials && null !== trialPlanId && (trialdata.profileid = userTrials[trialPlanId].profileId), StoreUtil.enableTrial(trialdata)
                })
            }
        },
        prorateTooltip: function() {
            {
                var toolTipData = {},
                    newDueDate = moment(StoreProperties.selectedPlan.nextDueDate),
                    today = moment(StoreProperties.today),
                    newRemainingDays = newDueDate.diff(today, "days"),
                    lastDueDate = moment(StoreProperties.userPlan.prevDueDate),
                    newPayDays = newDueDate.diff(lastDueDate, "days"),
                    oldDueDate = moment(StoreProperties.userPlan.nextDueDate),
                    oldRemainingDays = oldDueDate.diff(today, "days"),
                    oldPayDays = oldDueDate.diff(lastDueDate, "days"),
                    nxtRenewalAmt = StoreProperties.selectedPlan.recurringDue - StoreProperties.userPlan.recurringDue;
                StoreUtil.roundup(StoreProperties.selectedPlan.recurringDue, StoreProperties.currency.decimals.total) * newRemainingDays / newPayDays, StoreProperties.userPlan.recurringDue * oldRemainingDays / oldPayDays
            }
            toolTipData.nxtRenewalDate = StoreProperties.selectedPlan.nextDueDate, toolTipData.oldRemainingDays = oldRemainingDays, toolTipData.oldPayDays = oldPayDays, toolTipData.oldPlanAmt = StoreProperties.userPlan.recurringDue, toolTipData.newRemainingDays = newRemainingDays, toolTipData.newPayDays = newPayDays, toolTipData.newPlanAmt = StoreProperties.selectedPlan.recurringDue, toolTipData.nxtRenual = nxtRenewalAmt, StoreProperties.selectedPlan.payPeriod !== StoreProperties.userPlan.payPeriod ? toolTipData.change = "frequencyChange" : StoreProperties.selectedPlan.plan !== StoreProperties.userPlan.plan ? toolTipData.change = "planChange" : delete toolTipData.change;
            var isDisApplied = !1;
            if (StoreProperties.specialDiscount) {
                var disType = null != StoreProperties.specialDiscount.DISCOUNTTYPE && "" != StoreProperties.specialDiscount.DISCOUNTTYPE ? StoreProperties.specialDiscount.DISCOUNTTYPE : "";
                if ("3" !== disType) {
                    if ((StoreProperties.userPlan.plan !== StoreProperties.selectedPlan.plan && null !== StoreProperties.specialDiscount.PLANID && "" !== StoreProperties.specialDiscount.PLANID && "0" !== StoreProperties.specialDiscount.PLANID && "-1" !== StoreProperties.specialDiscount.PLANID || StoreProperties.userPlan.payPeriod !== StoreProperties.selectedPlan.payPeriod && null !== StoreProperties.specialDiscount.PAYPERIOD && "" !== StoreProperties.specialDiscount.PAYPERIOD && "10" !== StoreProperties.specialDiscount.PAYPERIOD) && (toolTipData.nxtRenual = StoreProperties.selectedPlan.discRecurringDue - StoreProperties.userPlan.actualRecurringDue, toolTipData.oldPlanAmt = StoreProperties.userPlan.actualRecurringDue), isDiscountApplied()) {
                        var reflection = null != StoreProperties.specialDiscount.REFLECTION && "" != StoreProperties.specialDiscount.REFLECTION ? StoreProperties.specialDiscount.REFLECTION : "";
                        toolTipData.nxtRenual = "2" === reflection && StoreProperties.userPlan.discRecurringDue ? StoreProperties.selectedPlan.discRecurringDue > StoreProperties.userPlan.discRecurringDue ? StoreProperties.selectedPlan.discRecurringDue - StoreProperties.userPlan.discRecurringDue : 0 : StoreProperties.selectedPlan.discRecurringDue > StoreProperties.userPlan.recurringDue ? StoreProperties.selectedPlan.discRecurringDue - StoreProperties.userPlan.recurringDue : 0, isDisApplied = !0
                    }
                    isDisApplied && (toolTipData.newPlanAmt = StoreProperties.selectedPlan.discRecurringDue)
                }
            }
            return toolTipData.isDiscountApplied = isDisApplied, toolTipData
        },
        sendFeedback: function(reason, comments) {
            var data = {
                    serviceId: serviceId
                },
                feedbackJSON = {
                    oldPlan: StoreProperties.userPlan.plan,
                    newPlan: StoreProperties.selectedPlan.plan,
                    downgradeType: "Edition downgrade"
                };
            data[securityKey.csrfParamName] = securityKey.csrfToken, reason && (feedbackJSON.downgradeReason = reason), comments && (feedbackJSON.downgradeComments = comments), StoreProperties.transaction && StoreProperties.transaction.result && StoreProperties.transaction.result.historyId && (feedbackJSON.historyId = StoreProperties.transaction.result.historyId), data.feedbackData = JSON.stringify(feedbackJSON), $.ajax({
                url: "/store/service.do?method=feedback&customId=" + StoreProperties.userDetails.customId,
                type: "POST",
                data: data,
                success: function() {
                    window.location.reload()
                }
            })
        },
        deformat: function(format, value) {
            return value = value.replace(/,/g, ""), format = format.replace("{0}", ""), value = value.replace(format, "")
        },
        format: function(a, b) {
            return "undefined" == typeof b || null == b ? a : 1 == arguments.length ? function() {
                var b = $.makeArray(arguments);
                return b.unshift(a), StoreUtil.format.apply(this, b)
            } : (arguments.length > 2 && b.constructor != Array && (b = $.makeArray(arguments).slice(1)), b.constructor != Array && (b = [b]), $.each(b, function(b, c) {
                a = a.replace(new RegExp("\\{" + b + "\\}", "g"), c)
            }), a)
        },
        roundup: function(number, scale) {
            return scale = Math.pow(10, scale), Math.round(number * scale) / (1 * scale)
        },
        round: function(number, scale, floor) {
            return floor ? (scale = Math.pow(10, scale), parseInt(number * scale, 10) / (1 * scale)) : parseFloat(number.toFixed(scale))
        },
        currency: function(n, c, d, t, f) {
            var p = Math.pow(10, c),
                m = n || 0,
                n = f ? parseInt(Math.round(m * p), 10) / p : m,
                d = void 0 == d ? "." : d,
                t = void 0 == t ? "," : t,
                s = 0 > n ? "-" : "",
                i = parseInt(n = Math.abs(+n || 0).toFixed(c), 10) + "",
                j = (j = i.length) > 3 ? j % 3 : 0;
            return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "")
        },
        currencyFormat: function(amount, scale, i18n, currency) {
            return i18n = JSON.parse(i18n || StoreProperties.i18n), currency = currency || StoreProperties.currency, scale = scale || currency.decimals.total, StoreUtil.format(i18n["store.pricing.value"], StoreUtil.currency(amount, scale, ".", ",", currency.floor))
        },
        numberFormat: function(number) {
            if (!number) return number;
            var parts = number.toString().split(".");
            return parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ","), parts.join(".")
        },
        storageFormat: function(storage, i18n) {
            return i18n = i18n || StoreProperties.i18n, storage = storage || 0, StoreUtil.format(i18n["store.storage.value"], this.numberFormat(storage))
        },
        hideSubscriptionLinks: function() {
            $("#upgradeUsersLink, #downgradeUsersLink, #upgradeStorageLink, #downgradeStorageLink, #upgradeUsersBtn").hide(), $(".upgradeHint, .fbgraylink").hide(), $("#paymentMethod").closest("tr").addClass("hidden"), $(".purchaseDetailsTable").find("[href]").hide()
        },
        calculateRecurringDue: function(plan, fullPrice) {
            var result = {},
                amount = 0,
                priceSplitUp = {};
            if (plan) {
                var payPeriod = plan.payPeriod;
                if (payPeriod && "none" !== payPeriod.toLowerCase()) {
                    var plansObj = StoreProperties.plans,
                        selectedPlan = plan.plan;
                    if (plansObj && plansObj[selectedPlan]) {
                        var selectedPlanObj = plansObj[selectedPlan],
                            pricing = StoreUtil.getPricingObj(payPeriod, fullPrice),
                            discountPricing = StoreUtil.getPricingObj(payPeriod).discountprice,
                            ontTimeAddons = plansObj.OneTimeAddOns;
                        amount += pricing[selectedPlan], priceSplitUp[selectedPlan] = {
                            amount: pricing[selectedPlan],
                            discount: discountPricing[selectedPlan]
                        };
                        var addonslist = selectedPlanObj.addonslist;
                        if (addonslist)
                            for (var single in addonslist) {
                                var addon = addonslist[single];
                                plan[addon] && (amount += plan[addon] * pricing[addon], (!ontTimeAddons || ontTimeAddons && -1 === ontTimeAddons.indexOf(addon)) && (priceSplitUp[addon] = {
                                    amount: plan[addon] * pricing[addon],
                                    discount: plan[addon] * discountPricing[addon]
                                }))
                            }
                    }
                }
            }
            return result.amount = StoreUtil.roundup(amount, StoreProperties.currency.decimals.total), result.priceSplitUp = priceSplitUp, result
        },
        setDiscountedRecurringDue: function() {
            var result = {},
                discountedPriceSplitUp = {},
                specialDiscount = StoreProperties.specialDiscount,
                discRecDue = 0,
                totalAddonAmount = 0;
            if (void 0 !== specialDiscount && null !== specialDiscount && "" !== specialDiscount) {
                var disType = null != specialDiscount.DISCOUNTTYPE && void 0 !== specialDiscount.DISCOUNTTYPE && "" != specialDiscount.DISCOUNTTYPE ? specialDiscount.DISCOUNTTYPE : "",
                    disStatus = null != specialDiscount.DISCOUNTSTATUS && void 0 != specialDiscount.DISCOUNTSTATUS && "" != specialDiscount.DISCOUNTSTATUS ? specialDiscount.DISCOUNTSTATUS : "",
                    onDiscountedPrice = StoreUtil.discountApplyOnDiscountedPrice();
                if ("1" === disType && "1" === disStatus) {
                    var priceSplitUp = {};
                    if (onDiscountedPrice ? (priceSplitUp = StoreUtil.calculateRecurringDue(StoreProperties.selectedPlan).priceSplitUp, StoreProperties.priceSplitUp = priceSplitUp) : priceSplitUp = StoreUtil.calculateRecurringDue(StoreProperties.selectedPlan, !0).priceSplitUp, priceSplitUp) {
                        for (var id in priceSplitUp) {
                            var addonAmount = priceSplitUp[id].amount;
                            totalAddonAmount += addonAmount;
                            var discountAmnt = StoreUtil.applySpecialDiscount(id, addonAmount);
                            discRecDue += discountAmnt, discountedPriceSplitUp[id] = {
                                amount: discountAmnt,
                                discount: addonAmount - discountAmnt
                            }
                        }
                        StoreProperties.discountedPriceSplitUp = discountedPriceSplitUp
                    }
                    discRecDue = StoreUtil.roundup(discRecDue, StoreProperties.currency.decimals.total), totalAddonAmount && discRecDue && totalAddonAmount - discRecDue > 0 ? (StoreProperties.isDiscountAvailedInPurchase = !0, StoreProperties.selectedPlan.discRecurringDue = discRecDue) : (StoreProperties.isDiscountAvailedInPurchase = !1, StoreProperties.selectedPlan.discRecurringDue = StoreProperties.selectedPlan.recurringDue)
                }
            }
            return result.amount = discRecDue, result.discountedPriceSplitUp = discountedPriceSplitUp, result
        },
        prorate: function(selectedPlan, userPlan, i18n) {
            i18n = JSON.parse(i18n || StoreProperties.i18n), selectedPlan = selectedPlan || StoreProperties.selectedPlan, userPlan = userPlan || StoreProperties.userPlan;
            var specialDiscount = void 0 !== StoreProperties.specialDiscount && null !== StoreProperties.specialDiscount && "" !== StoreProperties.specialDiscount ? !0 : !1;
            if (userPlan.actualRecurringDue = specialDiscount ? userPlan.actualRecurringDue || userPlan.recurringDue : userPlan.recurringDue, onetimepurchase = !1, onetimepurchaseamt = 0, StoreUtil.getPricingObj(selectedPlan.payPeriod)) {
                StoreProperties.service.recurringDue(selectedPlan), pricing = StoreUtil.getPricingObj(selectedPlan.payPeriod), modelid = pricing.modelid;
                var freeOTP = void 0 !== selectedPlan.freeOTP ? selectedPlan.freeOTP : !1;
                modelid && $.each(modelid, function(key, val) {
                    (3 === Number(val) || 4 === Number(val)) && selectedPlan[key] && userPlan && userPlan[key] && !freeOTP ? (onetimepurchase = !0, onetimepurchaseamt += selectedPlan[key] * pricing[key], selectedPlan.recurringDue = selectedPlan.recurringDue - selectedPlan[key] * pricing[key]) : 3 !== Number(val) && 4 !== Number(val) || !selectedPlan[key] || freeOTP || (onetimepurchase = !0, onetimepurchaseamt += selectedPlan[key] * pricing[key], selectedPlan.recurringDue = selectedPlan.recurringDue - selectedPlan[key] * pricing[key])
                })
            }
            if (userPlan && (userPlan.retry || userPlan.recurringFallsOn)) {
                var amtToBeCharged = selectedPlan.recurringDue - userPlan.actualRecurringDue > 0 ? selectedPlan.recurringDue - userPlan.actualRecurringDue : 0;
                return userPlan.actualRecurringDue = void 0 !== selectedPlan.actualRecurringDue && null !== selectedPlan.actualRecurringDue ? selectedPlan.actualRecurringDue : userPlan.actualRecurringDue, {
                    amount: amtToBeCharged
                }
            }
            if (!userPlan || !userPlan.recurringDue) {
                var cards = StoreProperties.cloneDetails;
                if (StoreProperties.cloneProfileId && selectedPlan.payPeriod == cards.payperiod && "credit" !== StoreProperties.purchaseType) {
                    selectedPlan.nextDueDate = cards.next_payment_date;
                    var newDueDate = moment(selectedPlan.nextDueDate),
                        today = moment(StoreProperties.today),
                        newRemainingDays = newDueDate.diff(today, "days"),
                        newPayDays = StoreUtil.getTotalDays(selectedPlan.payPeriod, selectedPlan.nextDueDate),
                        newPlanAmt = StoreUtil.roundup(selectedPlan.recurringDue, StoreProperties.currency.decimals.total) * newRemainingDays / newPayDays;
                    newPlanAmt = StoreUtil.roundup(newPlanAmt, StoreProperties.currency.decimals.total), $("#mul_nextpaydate").html(selectedPlan.nextDueDate), specialDiscount && selectedPlan.discRecurringDue && (selectedPlan.discDueNow = StoreUtil.roundup(selectedPlan.discRecurringDue, StoreProperties.currency.decimals.total) * newRemainingDays / newPayDays)
                } else if (18e4 === StoreProperties.serviceId && StoreProperties.existingOrgList && Object.keys(StoreProperties.existingOrgList).length > 0 && StoreProperties.existingOrgList.TOTALDISCOUNT > 0) {
                    var totDiscount = StoreProperties.existingOrgList.TOTALDISCOUNT;
                    newPlanAmt = selectedPlan.recurringDue > totDiscount ? selectedPlan.recurringDue - totDiscount : 0, newPlanAmt = StoreUtil.roundup(newPlanAmt, StoreProperties.currency.decimals.total), specialDiscount && selectedPlan.discRecurringDue && (selectedPlan.discDueNow = selectedPlan.discRecurringDue > totDiscount ? selectedPlan.discRecurringDue - totDiscount : 0)
                } else newPlanAmt = selectedPlan.recurringDue, onetimepurchase && (newPlanAmt += onetimepurchaseamt), specialDiscount && selectedPlan.discRecurringDue && (selectedPlan.discDueNow = onetimepurchase ? selectedPlan.discRecurringDue + onetimepurchaseamt : selectedPlan.discRecurringDue);
                return selectedPlan.discDueNow = StoreUtil.roundup(selectedPlan.discDueNow, StoreProperties.currency.decimals.total), {
                    amount: newPlanAmt
                }
            }
            if ("none" === selectedPlan.payPeriod || "NONE" === selectedPlan.payPeriod) return userPlan.actualRecurringDue = void 0 !== selectedPlan.actualRecurringDue && null !== selectedPlan.actualRecurringDue ? selectedPlan.actualRecurringDue : userPlan.actualRecurringDue, {
                amount: 0
            };
            var newPayDays, newDueDate = moment(selectedPlan.nextDueDate),
                today = moment(StoreProperties.today),
                newRemainingDays = newDueDate.diff(today, "days"),
                lastDueDate = moment(userPlan.prevDueDate),
                oldDueDate = moment(userPlan.nextDueDate),
                oldRemainingDays = oldDueDate.diff(today, "days"),
                oldPayDays = oldDueDate.diff(lastDueDate, "days");
            if (newPayDays = userPlan.payPeriod !== selectedPlan.payPeriod ? newDueDate.diff(today, "days") : newDueDate.diff(lastDueDate, "days"), specialDiscount && selectedPlan.discRecurringDue) {
                var oldPlanAmt = (userPlan.discRecurringDue ? userPlan.discRecurringDue : 0) * oldRemainingDays / oldPayDays;
                (parseInt(userPlan.plan) !== parseInt(selectedPlan.plan) && null !== StoreProperties.specialDiscount.PLANID && "" !== StoreProperties.specialDiscount.PLANID && "0" !== StoreProperties.specialDiscount.PLANID && "-1" !== StoreProperties.specialDiscount.PLANID || userPlan.payPeriod !== selectedPlan.payPeriod && null !== StoreProperties.specialDiscount.PAYPERIOD && "" !== StoreProperties.specialDiscount.PAYPERIOD && "10" !== StoreProperties.specialDiscount.PAYPERIOD) && (oldPlanAmt = userPlan.actualRecurringDue * oldRemainingDays / oldPayDays);
                var newPlanAmt = StoreUtil.roundup(selectedPlan.discRecurringDue, StoreProperties.currency.decimals.total) * newRemainingDays / newPayDays,
                    dueNow = newPlanAmt - oldPlanAmt;
                dueNow = dueNow > 0 ? dueNow : 0, selectedPlan.discDueNow = newRemainingDays === newPayDays && oldRemainingDays === oldPayDays ? StoreUtil.roundup(dueNow, StoreProperties.currency.decimals.total) : StoreUtil.round(dueNow, StoreProperties.currency.decimals.total, StoreProperties.currency.floor), onetimepurchase && (selectedPlan.discDueNow += onetimepurchaseamt)
            }
            var newPlanAmt = StoreUtil.roundup(selectedPlan.recurringDue, StoreProperties.currency.decimals.total) * newRemainingDays / newPayDays,
                oldPlanAmt = 0;
            if (void 0 !== StoreProperties.renderingJSON.resellerFlow && StoreProperties.renderingJSON.resellerFlow === !0 && void 0 != StoreProperties.userPlan && void 0 !== StoreProperties.cardType && "0" === StoreProperties.cardType) {
                var amount = userPlan.actualRecurringDue;
                amount = (100 - StoreProperties.renderingJSON.resellerDetails.resellerDiscount) * amount / 100, amount = StoreUtil.roundup(amount, StoreProperties.currency.decimals.total), oldPlanAmt = amount * oldRemainingDays / oldPayDays
            } else oldPlanAmt = userPlan.actualRecurringDue * oldRemainingDays / oldPayDays;
            var dueNow = newPlanAmt - oldPlanAmt,
                prorate = !0;
            return newRemainingDays == newPayDays && oldRemainingDays == oldPayDays ? (prorate = !1, dueNow = StoreUtil.roundup(dueNow, StoreProperties.currency.decimals.total)) : dueNow = StoreUtil.round(dueNow, StoreProperties.currency.decimals.total, StoreProperties.currency.floor), onetimepurchase && (dueNow += onetimepurchaseamt), userPlan.actualRecurringDue = void 0 !== selectedPlan.actualRecurringDue && null !== selectedPlan.actualRecurringDue ? selectedPlan.actualRecurringDue : userPlan.actualRecurringDue, dueNow > 0 ? {
                tooltip: "<table width='100%'><tr><td>" + i18n["store.confirm.amount.due.new.plan"] + "</td><td style='text-align: right'>" + this.currencyFormat(selectedPlan.recurringDue) + "*" + newRemainingDays + "/" + newPayDays + "</td><td style='text-align: right'>" + this.currencyFormat(newPlanAmt, StoreProperties.currency.decimals.line) + "</td></tr><tr><td>" + i18n["store.confirm.amount.unused.old.plan"] + "</td><td style='text-align: right'>-" + this.currencyFormat(userPlan.actualRecurringDue) + "*" + oldRemainingDays + "/" + oldPayDays + "</td><td style='text-align: right'>-" + this.currencyFormat(oldPlanAmt, StoreProperties.currency.decimals.line) + "</td></tr><tr><td colspan='2'>" + i18n["store.due.now"] + "</td><td style='text-align: right'>" + this.currencyFormat(dueNow) + "</td></tr></table>",
                prorate: prorate,
                amount: this.deformat(i18n["store.pricing.value"], this.currencyFormat(dueNow))
            } : {
                amount: 0
            }
        },
        addonDueNow: function(selectedPlan, userPlan, addonPrice) {
            selectedPlan = selectedPlan || StoreProperties.selectedPlan, userPlan = userPlan || StoreProperties.userPlan;
            var newDueDate = moment(selectedPlan.nextDueDate),
                today = moment(StoreProperties.today),
                newRemainingDays = newDueDate.diff(today, "days"),
                lastDueDate = moment(userPlan.prevDueDate),
                newPayDays = newDueDate.diff(lastDueDate, "days"),
                oldDueDate = moment(userPlan.nextDueDate),
                oldRemainingDays = oldDueDate.diff(today, "days"),
                oldPayDays = oldDueDate.diff(lastDueDate, "days"),
                oldAddonAmount = addonPrice.oldAddonAmount,
                addonAmount = addonPrice.addonAmount,
                newAddonAmt = StoreUtil.roundup(addonAmount, StoreProperties.currency.decimals.total) * newRemainingDays / newPayDays,
                oldAddonAmt = oldAddonAmount * oldRemainingDays / oldPayDays,
                addonDue = void 0 !== StoreProperties.userDetails.retryCount && StoreProperties.userDetails.retryCount > 0 ? newAddonAmt : newAddonAmt - oldAddonAmt;
            return addonDue = newRemainingDays == newPayDays && oldRemainingDays == oldPayDays ? StoreUtil.roundup(addonDue, StoreProperties.currency.decimals.total) : StoreUtil.round(addonDue, StoreProperties.currency.decimals.total, StoreProperties.currency.floor)
        },
        cc: {
            get: function(action, billingAddrOption) {
                var cc = {},
                    multipleElem = ["cardAddress", "cardCity", "cardZipCode", "cardState", "streetAddress", "city", "state", "zipCode"];
                $("." + action + " input[type='text'],." + action + " input[type='password'],." + action + " input[type='hidden'],." + action + " select,." + action + " textarea").each(function(index, ccInput) {
                    ccInput = $(ccInput);
                    var checkVisiblity = -1 !== multipleElem.indexOf(ccInput.attr("name")) ? !0 : !1;
                    checkVisiblity ? ccInput.is("select") && ccInput.next("span").is(":visible") ? cc[ccInput.attr("name")] = ccInput.val() : !ccInput.is("select") && ccInput.is(":visible") && (cc[ccInput.attr("name")] = ccInput.val()) : cc[ccInput.attr("name")] = ccInput.val()
                }), "changeCard" === action && StoreProperties.cc ? (cc.state = StoreProperties.cc.state, cc.cardState = "United States" === cc.cardCountry ? void 0 !== $("." + action + " [name=cc_card_state]").val() ? $("." + action + " [name=cc_card_state]").val() : cc.cardState ? cc.cardState : "none" : "India" === cc.cardCountry ? void 0 !== $("." + action + " .indStates").val() ? $("." + action + " .indStates").val() : cc.cardState ? cc.cardState : "none" : void 0 !== $("." + action + " [name=cardState]").val() ? $("." + action + " [name=cardState]").val() : cc.cardState ? cc.cardState : "none", cc.number && (StoreProperties.cc.lastFourDigits = cc.number.substring(cc.number.length - 4))) : "changeAdrs" === action && StoreProperties.cc ? (cc.cardState = StoreProperties.cc.cardState, "United States" === cc.country ? cc.state = void 0 !== $("." + action + " [name=card_state]").val() ? $("." + action + " [name=card_state]").val() : cc.state ? cc.state : "none" : "India" === cc.cardCountry ? cc.cardState = void 0 !== $("." + action + " .indStates").val() ? $("." + action + " .indStates").val() : cc.state ? cc.state : "none" : cc.state = void 0 !== $("." + action + " [name=state]").val() ? $("." + action + " [name=state]").val() : cc.state ? cc.state : "none") : (cc.state = "United States" === cc.country ? void 0 !== $("." + action + " [name=card_state]").val() ? $("." + action + " [name=card_state]").val() : cc.state ? cc.state : "none" : "India" === cc.cardCountry ? void 0 !== $("." + action + " .indStates").val() ? $("." + action + " .indStates").val() : cc.state ? cc.state : "none" : void 0 !== $("." + action + " [name=state]").val() ? $("." + action + " [name=state]").val() : cc.state ? cc.state : "none", cc.cardState = "United States" === cc.cardCountry ? void 0 !== $("." + action + " [name=cc_card_state]").val() ? $("." + action + " [name=cc_card_state]").val() : cc.cardState ? cc.cardState : "none" : "India" === cc.cardCountry ? void 0 !== $("." + action + " .indStates").val() ? $("." + action + " .indStates").val() : cc.cardState ? cc.cardState : "none" : void 0 !== $("." + action + " [name=cardState]").val() ? $("." + action + " [name=cardState]").val() : cc.cardState ? cc.cardState : "none"), "card-address" === billingAddrOption && (cc.country = cc.cardCountry, cc.state = cc.cardState, cc.city = cc.cardCity, cc.streetAddress = cc.cardAddress, cc.zipCode = cc.cardZipCode), cc.expiryYear = parseInt(cc.expiryYear), null != StoreProperties.licenseInfo && (cc.companyName = getCompanyName()), cc.billingName = cc.billingName ? cc.billingName : cc.invoice_billingName;
                var billingDetails = StoreProperties.renderingJSON.billing_details;
                return null !== billingDetails && void 0 !== billingDetails && (cc.country = billingDetails.country, cc.streetAddress = billingDetails.address, cc.companyName = getCompanyName(), cc.billingName = billingDetails.customer_name, cc.state = billingDetails.state, cc.city = billingDetails.city, cc.zipCode = billingDetails.zip_code, cc.phone = billingDetails.phone, cc.suite = billingDetails.apt_suite, cc.invoice_billingName = billingDetails.invoice_billingName), cc
            },
            set: function(cc) {
                if (StoreProperties.sameBuyer) {
                    $(".changeCard").find("input[type=text], input[type=password], textarea, select").val(""), $(".changeAdrs").find("input[type=text], input[type=password], textarea, select").val("");
                    for (var key in cc) {
                        var value = cc[key];
                        "expiryMonth" !== key || value.length || (value = 10 > value ? "0" + value : value), $("[name=" + key + "]").val(value).change()
                    }
                    "United States" === cc.country ? $("[name=card_state]").val(cc.state).change() : $("[name=state]").val(cc.state).change(), "United States" === cc.cardCountry ? $("[name=cc_card_state]").val(cc.cardState).change() : $("[name=cardState]").val(cc.cardState).change()
                }
            },
            change: function(action) {
                var ccDetails = StoreUtil.cc.get(action);
                if (ccDetails) {
                    var newCCDetails = ccDetails;
                    if ($.each(ccDetails, function(key, value) {
                            newCCDetails[key] = value
                        }), newCCDetails.billingName = StoreProperties.cc.first_name, "changeAdrs" === action && (delete newCCDetails.expiryYear, delete newCCDetails.expiryMonth, delete newCCDetails.type), delete newCCDetails.card_state, newCCDetails && newCCDetails.number && newCCDetails.cvv) {
                        "United States" === newCCDetails.cardCountry ? (newCCDetails.cardCity = newCCDetails.usCardCity, newCCDetails.cardZipCode = $(".usCardZCode").val()) : newCCDetails.cardZipCode = $(".cardZCode").val();
                        var newPaymentDetails = {},
                            oldPaymentDetails = StoreProperties.cc;
                        if (newCCDetails.number) {
                            var newfourdigit = newCCDetails.number.slice(newCCDetails.number.length - 4);
                            newPaymentDetails.cardnumber_four_digit = newfourdigit, newPaymentDetails.type = newCCDetails.type
                        } else newCCDetails.paypal_email && (newPaymentDetails = newCCDetails);
                        StoreProperties.cc.signedPaRes || (newCCDetails.number = encrypt(newCCDetails.number), newCCDetails.cvv = encrypt(newCCDetails.cvv))
                    }
                    for (var cybersourceParams = ["MERCHANT_REFERENCE", "PAYERAUTH_REF_ID", "PAREQ_XID", "PARES_XID", "signedPaRes"], c = 0; c < cybersourceParams.length; c++) {
                        var param = cybersourceParams[c];
                        StoreProperties.cc[param] && (newCCDetails[param] = StoreProperties.cc[param])
                    }
                    void 0 !== StoreProperties.renderingJSON && void 0 !== StoreProperties.renderingJSON.resellerFlow && StoreProperties.renderingJSON.resellerFlow === !0 && (newCCDetails.cardType = getCardType());
                    var JSONString = {};
                    JSONString.card_details = newCCDetails, JSONString.update_type = "creditcard", JSONString.profiles = StoreProperties.cc.profiles;
                    var reqdata = {
                        JSONString: JSON.stringify(JSONString)
                    };
                    reqdata[securityKey.csrfParamName] = securityKey.csrfToken, popupPositioning($(".confPop")), $.ajax({
                        url: "/restapi/private/v1/json/billingdetails",
                        type: "POST",
                        dataType: "JSON",
                        data: reqdata,
                        success: function(res) {
                            $(".primaryButton").removeClass("primaryButton-loader"), res ? (StoreProperties.currentPopup = null, 0 === res.code ? ($(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), "changeAdrs" === action ? ($(".freezeDiv").hide(), $(".billing-address-box,.subscribers-biliing-address").slideDown(50), $(".changeAdrs").slideUp(200), $("#billingSuccessPop").show(), setTimeout(function() {
                                $("#billingSuccessPop").hide()
                            }, 2e3)) : ($(".cardDisp").slideDown(200), $(".changeCard,.view-subscribers-payment-details").hide(), fillBillingDetails(newPaymentDetails, oldPaymentDetails), $("#transSuccessPop").show())) : res.mode && "AUTHENTICATION" === res.mode ? (StoreProperties.cc.MERCHANT_REFERENCE = res.MERCHANT_REFERENCE, StoreProperties.cc.PAYERAUTH_REF_ID = res.PAYERAUTH_REF_ID, StoreProperties.cc.PAREQ_XID = res.XID, createPAEnrollForm(res.ACS_URL, res.PAREQ, res.XID, res.TERM_URL), $("form[name=PAEnrollForm]").submit(), $(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), $(".changeCard,.view-subscribers-payment-details").hide(), $(".iframe-loader,#paInlineFrame").fadeIn(100).css("z-index", "10000000")) : ($(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), "changeAdrs" === action ? $("#transCardFailedPop #billdet,.newTransFailedPopup #billdet").show() : $("#transCardFailedPop #carddet,.newTransFailedPopup #carddet").show(), $(".changeCard,.view-subscribers-payment-details").hide(), $("#transCardFailedPop").show(), scrollToDiv("transFailedPop", -150, 500))) : ($(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), "changeAdrs" === action ? $("#transCardFailedPop #billdet,.newTransFailedPopup #billdet").show() : $("#transCardFailedPop #carddet,.newTransFailedPopup #carddet").show(), $(".changeCard,.view-subscribers-payment-details").hide(), $("#transCardFailedPop,.newTransFailedPopup").show(), scrollToDiv("transFailedPop", -150, 500)), $(".saveCard").prop("disabled", !0), $(".cancelCard").attr("onclick", "cancelPMChange()")
                        },
                        error: function() {
                            $(".primaryButton").removeClass("primaryButton-loader"), "changeAdrs" === action ? $("#transCardFailedPop #billdet,.newTransFailedPopup #billdet").show() : $("#transCardFailedPop #carddet,.newTransFailedPopup #carddet").show(), $(".changeCard,.view-subscribers-payment-details").hide(), $("#transCardFailedPop,.newTransFailedPopup").show(), scrollToDiv("transFailedPop", -150, 500), $(".saveCard").prop("disabled", !0), $(".cancelCard").attr("onclick", "cancelPMChange()")
                        }
                    })
                }
            },
            check: function(number, type) {
                var luhnArr = [
                        [0, 2, 4, 6, 8, 1, 3, 5, 7, 9],
                        [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                    ],
                    sum = 0;
                return number.replace(/\D+/g, "").replace(/[\d]/g, function(c, p, o) {
                    sum += luhnArr[o.length - p & 1][parseInt(c, 10)]
                }), sum % 10 === 0 && sum > 0 && CardTypeRegex[type].test(number)
            },
            reset: function() {
                $("#cardTable,#billAddrSplit,#billAddrTable").find("input[type=text], input[type=password], textarea, select").val(""), $("#ccardContainer .selCountry,#ccardContainer .eMon,#ccardContainer .eYear").prop("selectedIndex", "0")
            }
        },
        billingAddress: {
            change: function() {
                popupPositioning($(".popupAlign"));
                var billing_address = {},
                    action = "changeAdrs";
                $("." + action + " input[type='text'],." + action + " input[type='password'],." + action + " input[type='hidden'],." + action + " select,." + action + " textarea").each(function(index, bAInput) {
                    bAInput = $(bAInput), billing_address[bAInput.attr("name")] = bAInput.val()
                }), "United States" === billing_address.country ? (billing_address.address = $(".usStAddress").val(), billing_address.state = billing_address.card_state, billing_address.city = $(".usCity").val(), billing_address.zipcode = $(".usZCode").val()) : ("India" === billing_address.country && (billing_address.state = $(".indStates").val()), billing_address.city = $(".city").val(), billing_address.zipcode = $(".zCode").val(), delete billing_address.suite), delete billing_address.card_state;
                var JSONString = {};
                JSONString.billing_address = billing_address;
                var reqdata = {
                    JSONString: JSON.stringify(JSONString)
                };
                $.ajax({
                    url: "/restapi/private/v1/json/billingaddressdetails",
                    type: "POST",
                    dataType: "JSON",
                    data: reqdata,
                    success: function(result) {
                        result && 0 === result.code ? ($(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), $(".freezeDiv").hide(), $(".changeAdrs").find(".primaryButton-loader").removeClass("primaryButton-loader"), $(".saveAddr").prop("disabled", !1), $(".cancelAddr").attr("onclick", "cancelAdrDetails()"), setDefaultDetails("billingAddress", billing_address), $(".billing-address-box,.subscribers-biliing-address").slideDown(50), $(".changeAdrs").slideUp(200), null !== localStorage.getItem("backUrl") ? $(".throughMultiAddonProductSuccess").show() : $("#billingSuccessPop").show(), setTimeout(function() {
                            $("#billingSuccessPop, .throughMultiAddonProductSuccess").hide(), $(".payment-controllor-container").show()
                        }, 2e3)) : (displayFreezeDiv(), $(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100), $("#transCardFailedPop #billdet,#transCardFailedPop,.newTransFailedPopup").fadeIn(100), scrollToDiv("transFailedPop", -150, 500)), $("#invoice-billing-address input,#invoice-billing-address select,#invoice-billing-address textarea").removeAttr("disabled")
                    },
                    error: function() {
                        displayFreezeDiv(), $("#transCardFailedPop #billdet,#transCardFailedPop,.newTransFailedPopup").fadeIn(500), scrollToDiv("transFailedPop", -150, 500), $("#invoice-billing-address input,#invoice-billing-address select,#invoice-billing-address textarea").removeAttr("disabled")
                    }
                })
            }
        },
        showMessage: function(msg, customMsg) {
            $(".processingMsg,.processingBG,.feedbackPop,.confirmCancelMsg").fadeOut(100);
            var supportedPaymentMethods = StoreUtil.getSupportedPaymentMethods();
            $(".multiAddonContainer").is(":visible") && $(".multiAddonContainer").hide(), supportedPaymentMethods && supportedPaymentMethods.PAYPAL_EXPRESSCHECKOUT ? (trackClickEvent("Payments - Card Failure Page"), customMsg && $("#transCardFailedPop .primaryMsg").html(msg), $("#transCardFailedPop,.newTransFailedPopup").show()) : (trackClickEvent("Payments - Failure Page"), customMsg && $("#transFailedPop .primaryMsg").html(msg), $("#transFailedPop").show(), $(".freezeDiv").addClass("manageplanFreeze")), document.cookie = "store.errors=" + encodeURIComponent(msg) + ";path=/"
        },
        goTo: function(url) {
            var currentUrl = decodeURIComponent(window.location.href);
            url && (StoreProperties.currentPopup = null, window.location = url + "#~err", -1 != currentUrl.indexOf(url) && (window.location.reload(), $(".transMsg").show()))
        },
        nextDueDate: function(date, payPeriod) {
            switch (date = moment(date), payPeriod) {
                case "YEAR":
                    date = date.add("years", 1);
                    break;
                case "MONT":
                    date = date.add("months", 1);
                    break;
                case "SMYR":
                    date = date.add("months", 6);
                    break;
                case "QTER":
                    date = date.add("months", 4)
            }
            return date.format("YYYY-MM-DD")
        },
        qtip: function(text) {
            return {
                content: {
                    text: text
                },
                style: {
                    classes: "ui-tooltip-youtube",
                    width: "350px"
                },
                position: {
                    my: "bottom left",
                    at: "top center",
                    viewport: $(window)
                },
                hide: {
                    event: "unfocus"
                },
                show: {
                    solo: !0
                }
            }
        },
        getSubscriptionHistory: function(zuid) {
            var url = "?method=history&customId=" + StoreProperties.userDetails.customId + "&serviceId=" + serviceId,
                historyDiv = $("#subscriptionHistoryDiv");
            zuid && (url = "/store/admin.do?method=history&customId=" + StoreProperties.userDetails.customId + "&serviceId=" + StoreProperties.serviceId + "&zuid=" + zuid), historyDiv.empty().append(i18n["store.loading"]), $.ajax({
                url: url,
                type: "GET",
                dataType: "JSON",
                success: function(historyArr) {
                    if (historyDiv.empty(), historyArr && historyArr.length) {
                        historyDiv.append("<div><div class='ftxt cell bold historyColumn'>" + i18n["store.subscription.history.date"] + "</div><div class='ftxt cell bold historyColumn'>" + i18n["store.subscription.history.type"] + "</div><div class='ftxt cell bold historyColumn'>" + i18n["store.subscription.history.ref"] + "</div><div class='ftxt cell bold historyColumn'>" + i18n["store.subscription.history.expiration"] + "</div><div class='ftxt cell bold historyColumn'>" + i18n["store.subscription.history.amount"] + "</div><div class='ftxt cell bold historyDetails'>" + i18n["store.subscription.history.details"] + "</div></div>");
                        for (var hbody = $("<div class='historyBody'></div>").appendTo(historyDiv), i = 0; i < historyArr.length; i++) {
                            var history = historyArr[i],
                                details = history.details,
                                detailsHTML = "-",
                                type = StoreProperties.historyType[history.type];
                            details.detailsText ? detailsHTML = details.detailsText : "upgrade.free" === type || "upgrade" === type || "downgrade" === type || "modify" === type || "renewal" === type || "cancel" === type || "auto.cancel" === type ? (detailsHTML = StoreProperties.service.history(details, type), "upgrade.free" === type && (detailsHTML = detailsHTML + "<br/>" + i18n["store.subscription.history.billing.name"] + details.billingName + ", " + i18n["store.subscription.history.company.name"] + details.companyName)) : "credit.card.change" === type && (detailsHTML = i18n["store.subscription.history.billing.name"] + details.billingName + ", " + i18n["store.subscription.history.company.name"] + details.companyName), zuid && (detailsHTML = detailsHTML + "<br/>" + i18n["store.subscription.history.profile.id"] + details.profileId, details.transactionId && (detailsHTML = detailsHTML + ", " + i18n["store.subscription.history.transaction.id"] + details.transactionId), detailsHTML = detailsHTML + ", " + i18n["store.subscription.history.buyer.zuid"] + details.buyerZuid), hbody.append("<div><div class='ftxt cell historyColumn'>" + history.date + "</div><div class='ftxt cell historyColumn'>" + i18n["store.subscription.history." + type] + "</div><div class='ftxt cell historyColumn'>" + history.ref + "</div><div class='ftxt cell historyColumn'>" + (history.expirationDate ? history.expirationDate : "-") + "</div><div class='ftxt cell historyColumn'>" + (history.amount ? StoreUtil.currencyFormat(history.amount) : "-") + "</div><div class='ftxt cell historyDetails'>" + detailsHTML + (details.invoiceId ? "<a href='?method=invoice&customId=" + StoreProperties.userDetails.customId + "&serviceId=" + serviceId + "&invoiceId=" + details.invoiceId + "' target='_blank'>" + i18n["store.print.invoice"] + "</a>" : "") + "</div></div>")
                        }
                    } else historyDiv.append("<div class='ftxt cell center'>" + i18n["store.subscription.history.no.data"] + "</div>");
                    setTimeout(repositionPopup, 1)
                },
                error: function() {
                    historyDiv.empty().append("<div class='ftxt cell center'>" + i18n["store.subscription.history.error"] + "</div>")
                }
            })
        },
        enableTrial: function(data) {
            var reqdata = {
                method: "trial",
                customId: StoreProperties.userDetails.customId,
                trialData: JSON.stringify(data)
            };
            reqdata[securityKey.csrfParamName] = securityKey.csrfToken, $.ajax({
                url: "/store/service.do",
                type: "POST",
                data: reqdata,
                success: function(result) {
                    result.success || result.successDwn || result.provisioningError ? location.reload() : ($(".transMsg").html("<img src='/images/closeimg.png' alt='close' class='closebut' onclick='$(this).closest('div').addClass('msgHide')'>" + i18n["store.trial.failure"]), $(".transMsg").removeClass("msgHide"))
                },
                error: function() {
                    $(".transMsg").html("<img src='/images/closeimg.png' alt='close' class='closebut' onclick='$(this).closest('div').addClass('msgHide')'>" + i18n["store.trial.failure"]), $(".transMsg").removeClass("msgHide")
                },
                dataType: "JSON"
            })
        },
        resetPayPeriod: function(payPeriod) {
            payPeriod != StoreProperties.selectedPlan.payPeriod && changeDuration(payPeriod)
        },
        applyFreeze: function() {
            void 0 != StoreProperties.serviceJSON && "iframe" == StoreProperties.serviceJSON.calling_mode ? (parent.$("#storefreez").show(), parent.$("#paymentiframe").parent().css("z-index", "10002"), $("#ajaxLoader").css("z-index", "10003"), parent.$("#paymentiframe").css("top", "-20px"), parent.$("html, body").animate({
                scrollTop: 0
            }, "slow")) : $("html, body").animate({
                scrollTop: 0
            }, "slow")
        },
        removeFreeze: function() {
            $("#popupOverlay").fadeOut(300), StoreProperties.currentPopup = null, void 0 != StoreProperties.serviceJSON && "iframe" == StoreProperties.serviceJSON.calling_mode && (parent.$("#storefreez").fadeOut(300), parent.$("#paymentiframe").parent().css("z-index", ""), $("#ajaxLoader").css("z-index", ""))
        },
        getMonths: function(payPeriod) {
            return "QTER" == payPeriod ? 3 : "SMYR" == payPeriod ? 6 : "YEAR" == payPeriod ? 12 : 1
        },
        getTotalDays: function(payperiod, date) {
            var today = moment(StoreProperties.today),
                later = moment(StoreProperties.today);
            return today = moment(date), "MONT" === payperiod ? (today.add("months", -1), later = today) : "QTER" === payperiod ? (today.add("months", -3), later = today) : "SMYR" === payperiod ? (today.add("months", -6), later = today) : "YEAR" === payperiod && (today.add("years", -1), later = today), today = moment(date), today.diff(later, "days")
        },
        getCardImg: function(cardtype) {
            var cardimg = "visa";
            return cardtype = cardtype.toLowerCase(), -1 != cardtype.indexOf("visa") ? cardimg = "visa" : -1 != cardtype.indexOf("master") ? cardimg = "master" : -1 != cardtype.indexOf("discover") ? cardimg = "discover" : -1 != cardtype.indexOf("jcb") ? cardimg = "jcb" : -1 != cardtype.indexOf("amex") ? cardimg = "amex" : -1 != cardtype.indexOf("diners") && (cardimg = "dinersclub"), cardimg
        },
        backToService: function() {
            if (StoreProperties.serviceJSON && StoreProperties.serviceJSON.backToService) {
                var productDomain = "Zoho";
                if (StoreProperties.product_domain && "manageengine" === StoreProperties.product_domain ? productDomain = "ManageEngine" : StoreProperties.product_domain && "site24x7" === StoreProperties.product_domain && (productDomain = ""), StoreProperties.renderingJSON.multipleAddonsPurchase) {
                    var backToMsg = i18n["zohostore.backtoapp"].replace("{0}", StoreProperties.serviceJSON.backToServiceURL);
                    $(".backToApp").html("<div>" + backToMsg + "</div>").find(".serviceName").text(StoreProperties.serviceName), $(".backToApp").show().find(".domainName").html(productDomain)
                } else {
                    var backToMsg = i18n["zohostore.backtoservice"].replace("{0}", StoreProperties.serviceJSON.backToServiceURL);
                    $(".backToService").html(backToMsg).find(".serviceName").text(StoreProperties.serviceName), $(".backToService").show().find(".domainName").html(productDomain)
                }
            }
        },
        upgradeDurationHandling: function() {
            var selected = !1,
                currentElement = $("#upgradeDurationPopup #upgradePaymentDuration select");
            "" != currentElement.val() && void 0 != currentElement.val() && (selected = !0), selected ? ($("#upgradeDurationPopup .confirmOrderBtn").removeClass("graybtn").addClass("greenButton"), $("#upgradeDurationPopup .confirmOrderBtn").removeAttr("disabled")) : ($("#upgradeDurationPopup .confirmOrderBtn").attr("disabled", "disabled"), $("#upgradeDurationPopup .confirmOrderBtn").removeClass("greenButton").addClass("graybtn"))
        },
        discountApplyOnDiscountedPrice: function() {
            var specialDiscount = StoreProperties.specialDiscount;
            if (specialDiscount) {
                var onDiscountedPrice = null !== specialDiscount.ONDISCOUNTEDPRICE && void 0 !== specialDiscount.ONDISCOUNTEDPRICE && "" !== specialDiscount.ONDISCOUNTEDPRICE ? specialDiscount.ONDISCOUNTEDPRICE : "";
                if ("1" === onDiscountedPrice) return !0
            }
            return !1
        },
        applySpecialDiscount: function(id, amount) {
            var specialDiscount = StoreProperties.specialDiscount;
            return this.isSpecialDiscountAvailable(specialDiscount, id) && (amount = this.applyMappedSpecialDiscount(specialDiscount, amount)), amount
        },
        isSpecialDiscountAvailable: function(specialDiscount, id) {
            var disAvailable = !1,
                disNature = null !== specialDiscount.DISCOUNTNATURE && "" !== specialDiscount.DISCOUNTNATURE ? specialDiscount.DISCOUNTNATURE : "",
                disAddonId = null !== specialDiscount.ADDONID && "" !== specialDiscount.ADDONID ? specialDiscount.ADDONID : "",
                disPlanId = null !== specialDiscount.PLANID && "" !== specialDiscount.PLANID ? specialDiscount.PLANID : "",
                disSerId = null !== specialDiscount.SERVICEID && "" !== specialDiscount.SERVICEID ? specialDiscount.SERVICEID : "";
            if (this.checkPayPeriodForSpecialDiscout(specialDiscount) && StoreProperties.serviceId === parseInt(disSerId))
                if ("" !== disNature && "ADDON" === disNature) {
                    var addonCheck = !1;
                    if ("0" !== disAddonId && "-1" !== disAddonId && null != id) {
                        for (var addonIdSplit = disAddonId.split(","), i = 0; i < addonIdSplit.length; i++)
                            if (parseInt(id) === parseInt(addonIdSplit[i])) {
                                addonCheck = !0;
                                break
                            }
                    } else addonCheck = !0;
                    !addonCheck || "0" !== disPlanId && "-1" !== disPlanId && parseInt(StoreProperties.selectedPlan.plan) !== parseInt(disPlanId) || (disAvailable = !0)
                } else "" !== disNature && "PLAN" === disNature ? ("0" === disPlanId || "-1" === disPlanId || parseInt(StoreProperties.selectedPlan.plan) === parseInt(disPlanId)) && (disAvailable = !0) : "" !== disNature && "SERVICE" === disNature && (disAvailable = !0);
            return disAvailable
        },
        checkPayPeriodForSpecialDiscout: function(specialDiscount) {
            var payPeriodCheck = !1,
                disPayPeriod = null !== specialDiscount.PAYPERIOD && "" !== specialDiscount.PAYPERIOD ? specialDiscount.PAYPERIOD : "",
                payPeriod = StoreProperties.selectedPlan.payPeriod;
            return payPeriod = "MONT" === payPeriod ? "1" : "YEAR" === payPeriod ? "4" : "10", "" !== disPayPeriod && "10" === disPayPeriod ? payPeriodCheck = !0 : disPayPeriod === payPeriod && (payPeriodCheck = !0), payPeriodCheck
        },
        applyMappedSpecialDiscount: function(specialDiscount, amount) {
            var disType = null != specialDiscount.DISCOUNTTYPE && "" !== specialDiscount.DISCOUNTTYPE ? specialDiscount.DISCOUNTTYPE : "";
            return "" !== disType && "0" === disType && (amount = this.applyAmountSpecialDiscount(specialDiscount, amount)), "" !== disType && "1" === disType && (amount = this.applyPercentageSpecialDiscount(specialDiscount, amount)), amount
        },
        applyPercentageSpecialDiscount: function(specialDiscount, amount) {
            var disStatus = null !== specialDiscount.DISCOUNTSTATUS && "" !== specialDiscount.DISCOUNTSTATUS ? specialDiscount.DISCOUNTSTATUS : "";
            if ("1" === disStatus) {
                var discount = null !== specialDiscount.DISCOUNT && "" !== specialDiscount.DISCOUNT ? specialDiscount.DISCOUNT : "",
                    discountedAmount = parseFloat(discount) * parseFloat(amount) / 100;
                return StoreUtil.roundup(parseFloat(amount) - parseFloat(discountedAmount), StoreProperties.currency.decimals.total)
            }
        },
        applyAmountSpecialDiscount: function(specialDiscount, amount) {
            var disStatus = null !== specialDiscount.DISCOUNTSTATUS && "" !== specialDiscount.DISCOUNTSTATUS ? specialDiscount.DISCOUNTSTATUS : "";
            if ("1" === disStatus) {
                var discount = null !== specialDiscount.DISCOUNT && "" !== specialDiscount.DISCOUNT ? specialDiscount.DISCOUNT : "",
                    maxPer = null !== specialDiscount.THRESHOLD_PERCENTAGE && "" !== specialDiscount.THRESHOLD_PERCENTAGE ? specialDiscount.THRESHOLD_PERCENTAGE : "0",
                    amntAftThrshold = parseFloat(amount) * parseFloat(maxPer) / 100;
                return parseFloat(amntAftThrshold) >= parseFloat(discount) ? parseFloat(amount) - parseFloat(discount) : parseFloat(amount)
            }
        },
        applyDaysSpecialDiscount: function(specialDiscount, nextDueDate) {
            var days = null !== specialDiscount.DISCOUNT && "" !== specialDiscount.DISCOUNT ? specialDiscount.DISCOUNT : "",
                disNature = null !== specialDiscount.DISCOUNTNATURE && "" !== specialDiscount.DISCOUNTNATURE ? specialDiscount.DISCOUNTNATURE : "";
            if ("" !== disNature && "ADDON" === disNature) {
                var planId = StoreProperties.selectedPlan.plan,
                    planAddonJson = StoreProperties.plans[planId];
                for (var obj in planAddonJson)
                    if (StoreProperties.selectedPlan.hasOwnProperty(obj) && StoreProperties.selectedPlan[obj] > 0 && this.isSpecialDiscountAvailable(specialDiscount, obj)) {
                        StoreProperties.selectedPlan.nextDueDate = this.convertToDBFormatDate(nextDueDate, days);
                        break
                    }
            } else this.isSpecialDiscountAvailable(specialDiscount) && (StoreProperties.selectedPlan.nextDueDate = this.convertToDBFormatDate(nextDueDate, days))
        },
        setSpecialDiscountedValue: function() {
            var specialDiscount = StoreProperties.specialDiscount;
            if (void 0 !== specialDiscount && null !== specialDiscount && "" !== specialDiscount) {
                var disType = null != specialDiscount.DISCOUNTTYPE && "" != specialDiscount.DISCOUNTTYPE ? specialDiscount.DISCOUNTTYPE : "",
                    reflection = null != specialDiscount.REFLECTION && "" != specialDiscount.REFLECTION ? specialDiscount.REFLECTION : "";
                "3" === disType && "0" === reflection ? StoreUtil.applyDaysSpecialDiscount(specialDiscount, StoreProperties.selectedPlan.nextDueDate) : "1" !== disType || "0" !== reflection && "2" !== reflection || (StoreProperties.selectedPlan.discRecurringDue = StoreProperties.selectedPlan.recurringDue)
            }
        },
        convertToDBFormatDate: function(inputDate, days) {
            var d = new Date(inputDate);
            d.setDate(d.getDate() + parseInt(days));
            var month = d.getMonth() + 1,
                year = d.getFullYear(),
                day = d.getDate();
            return 10 > month && (month = "0" + month), 10 > day && (day = "0" + day), [year, month, day].join("-")
        },
        getSpecialDiscountString: function() {
            var disString = "",
                specialDiscount = StoreProperties.specialDiscount,
                disType = null != specialDiscount.DISCOUNTTYPE && "" != specialDiscount.DISCOUNTTYPE ? specialDiscount.DISCOUNTTYPE : "",
                discount = null != specialDiscount.DISCOUNT && "" != specialDiscount.DISCOUNT ? specialDiscount.DISCOUNT : "";
            return "1" === disType ? (discount = StoreUtil.round(Number(discount), StoreProperties.currency.decimals.total), disString = discount + " %") : "3" === disType && (discount = StoreUtil.round(Number(discount), StoreProperties.currency.decimals.total), disString = discount > 1 ? discount + " days" : discount + " day"), disString
        },
        getPriceSplitUp: function(plan, fullPrice) {
            return StoreUtil.calculateRecurringDue(plan, fullPrice).priceSplitUp
        },
        getDiscountedPriceSplitUp: function() {
            return StoreUtil.setDiscountedRecurringDue().discountedPriceSplitUp
        },
        calculateAddonPrice: function(addonCount, pricing) {
            for (var addonPrice = 0, packageQty = 0, lastSlot = !1, i = 0; i < pricing.length; i++) {
                var pricingSlot = pricing[i],
                    pricingType = pricingSlot.type;
                if (lastSlot = i + 1 === pricing.length ? !0 : !1, 1 === pricingType) {
                    var endRange = pricingSlot.endRange,
                        slotPrice = pricingSlot.price;
                    if (endRange === addonCount) {
                        addonPrice = slotPrice;
                        break
                    }
                    if (endRange > addonCount) {
                        var itemPrice = (addonCount - packageQty) * (slotPrice - addonPrice) / (endRange - packageQty);
                        addonPrice += itemPrice;
                        break
                    }
                    lastSlot && (addonPrice = endRange > 0 ? slotPrice / endRange * addonCount : endRange * addonCount), addonPrice = slotPrice, packageQty = endRange
                } else 2 === pricingType ? addonPrice = 0 : 3 === pricingType && (addonPrice = 0)
            }
            return addonPrice
        }
    }
}();

$(".planEditBtn")
    .on("click", function() {
        var stepHead = $(this)
            .parents(".subAcHead");
        $(".subDetDiv,.cErrMsg,#showInlineErrorMsg")
            .hide(), $("label[for='3dsecure']")
            .removeClass("labelfocus"), stepHead.nextAll(".subAcHead")
             , $(".subCDiv")
            .hasClass(".sfLink") || $(".subCDiv")
            .addClass("subAcHead")
            .removeClass("whiteBG"), stepHead.next(".subDetDiv")
            .slideDown(), $("html, body")
            .animate({
                scrollTop: 0
            }, "slow"), stepHead.removeClass("subAcHead")
            .addClass("whiteBG"), stepHead.nextAll(".subAcHead")
            .removeClass("subAcHead c444"), stepHead.hasClass("planTitleDiv") ? (setCurrencyPicker("select-plan"), trackClickEvent("Payments - Edit - Select Plan")) : stepHead.hasClass("planDetailHd") ? ($(".orderSumryHdr #oldEditionInfo")
                .remove(), setCurrencyPicker("plan-details"), trackClickEvent("Payments - Edit - Place Order")) : trackClickEvent("Payments - Edit - Confirm Order");
    })