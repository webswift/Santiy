<div class="row mt10">
	<div class="col-sm-4">
		<div class="form-group">
			<label class="control-label">Price/User/Month USD</label>
			<input type="text" name="usdPrice" id="usdPrice" class="form-control" value="{{ $licenseTypeDetail->priceUSD }}">
		</div>
		<div class="form-group">
			<label class="control-label">Price/User/Year USD</label>
			<input type="text" name="priceUSD_year" id="priceUSD_year" class="form-control" value="{{ $licenseTypeDetail->priceUSD_year }}">
		</div>
	</div>
	<div class="col-sm-4">
		<div class="form-group">
			<label class="control-label">Price/User/Month Euro</label><br>
			<input type="text" name="euroPrice" id="euroPrice" class="form-control" value="{{ $licenseTypeDetail->priceEuro }}"/>
		</div>
		<div class="form-group">
			<label class="control-label">Price/User/Year Euro</label><br>
			<input type="text" name="priceEuro_year" id="priceEuro_year" class="form-control" value="{{ $licenseTypeDetail->priceEuro_year }}"/>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="form-group">
			<label class="control-label">Price/User/Month GBP</label><br>
			<input type="text" name="gbpPrice" id="gbpPrice" class="form-control" value="{{ $licenseTypeDetail->priceGBP }}"/>
		</div>
		<div class="form-group">
			<label class="control-label">Price/User/Year GBP</label><br>
			<input type="text" name="priceGBP_year" id="priceGBP_year" class="form-control" value="{{ $licenseTypeDetail->priceGBP_year }}"/>
		</div>
	</div>
</div>
<div class="row mt10 mb10">
	<div class="col-sm-12">
		<div class="form-group">
			<label class="control-label">Yearly Discount %</label>
			<input type="text" name="discount" id="discount" class="form-control" value="{{ $licenseTypeDetail->discount }}" />
		</div>
	</div>
</div>

@section('licensesblock_javascript')
<script type="text/javascript">
$(function() {
    jQuery('#discount').spinner({min: 0});

    $("#usdPrice").typeWatch({ highlight:true, wait:500, captureLength: -1, callback: setUSDYearAmount });
    $("#euroPrice").typeWatch({ highlight:true, wait:500, captureLength: -1, callback: setEUROYearAmount });
    $("#gbpPrice").typeWatch({ highlight:true, wait:500, captureLength: -1, callback: setGBPYearAmount });
    $("#discount").typeWatch({ highlight:true, wait:500, captureLength: -1, callback: calculateDiscountAmount });
});

function setUSDYearAmount() {
    var monthPrice = parseInt($('#usdPrice').val());

    if(monthPrice == '') {
        return false;
    }
    monthPrice = parseInt(monthPrice);
    var yearPrice = monthPrice * 12;

    $('#priceUSD_year').val(yearPrice);
    calculateDiscountAmount();
}

function setEUROYearAmount() {
    var monthPrice = parseInt($('#euroPrice').val());

    if(monthPrice == '') {
        return false;
    }
    monthPrice = parseInt(monthPrice);
    var yearPrice = monthPrice * 12;

    $('#priceEuro_year').val(yearPrice);
    calculateDiscountAmount();
}

function setGBPYearAmount() {
    var monthPrice = $('#gbpPrice').val();

    if(monthPrice == '') {
        return false;
    }
    monthPrice = parseInt(monthPrice);
    var yearPrice = monthPrice * 12;

    $('#priceGBP_year').val(yearPrice);
    calculateDiscountAmount();
}

function calculateDiscountAmount() {
    var discount = $('#discount').val();
    if(discount == '') {
        return false;
    }
    discount = parseInt(discount);
    if(discount == undefined || discount == "NaN") {
        return false;
    }
    var usdPrice = parseInt($('#usdPrice').val());
    usdPrice = (usdPrice * 12)  - (usdPrice * 12 * (discount/100));
    $('#priceUSD_year').val(usdPrice);

    var euroPrice = parseInt($('#euroPrice').val());
    euroPrice = (euroPrice * 12)  - (euroPrice * 12 * (discount/100));
    $('#priceEuro_year').val(euroPrice);

    var gbpPrice = parseInt($('#gbpPrice').val());
    gbpPrice = (gbpPrice * 12)  - (gbpPrice * 12 * (discount/100));
    $('#priceGBP_year').val(gbpPrice);
}
</script>
@stop
