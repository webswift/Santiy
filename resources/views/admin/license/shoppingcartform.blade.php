{!! Form::open(array('route' => array('admin.licenses.updateshoppingcart', $licenseTypeDetail->id), 'id' => 'updateShoppingCart', 'method' => 'post')) !!}
	<div id="error" ></div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="control-label">Product Name</label>
                <input type="text" name="productName" id="productName" class="form-control input-sm" value="{{ $licenseTypeDetail->name }}">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <label class="control-label">Description</label>
                <textarea class="form-control" name="description" id="description" rows="5" placeholder="Description">{{ $licenseTypeDetail->description }}</textarea>
            </div>
        </div>
    </div>
    <hr>
    <input type="hidden" name="licenseClass" value="{{ $licenseTypeDetail->licenseClass }}">

    @if($licenseTypeDetail->licenseClass == 'Multi')
        <div class="form-group">
            <label class="control-label">Volume of users</label><br>
            Admin + <input type="text" name="volumeOfUsers" id="volumeOfUsers" class="form-control input-sm" value="{{ $licenseTypeDetail->volume }}" />
        </div>
        <hr>
    @endif

    <div class="row">
        <div class="col-sm-4">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label">Price/User/Month USD</label>
                    <input type="text" name="usdPrice" id="usdPrice" class="form-control input-sm" value="{{ $licenseTypeDetail->priceUSD }}">
                </div>
                <div class="col-md-6">
                    <label class="control-label">Price/User/Year USD</label>
                    <input type="text" name="usdPrice" id="usdPrice" class="form-control input-sm" value="{{ $licenseTypeDetail->priceUSD }}">
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label">Price/User/Month Euro</label><br>
                    <input type="text" name="euroPrice" id="euroPrice" class="form-control input-sm" value="{{ $licenseTypeDetail->priceEuro }}"/>
                </div>
                <div class="col-md-6">
                    <label class="control-label">Price/User/Year Euro</label><br>
                    <input type="text" name="euroPrice" id="euroPrice" class="form-control input-sm" value="{{ $licenseTypeDetail->priceEuro }}"/>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label">Price/User/Month GBP</label><br>
                    <input type="text" name="gbpPrice" id="gbpPrice" class="form-control input-sm" value="{{ $licenseTypeDetail->priceGBP }}"/>
                </div>
                <div class="col-md-6">
                    <label class="control-label">Price/User/Year GBP</label><br>
                    <input type="text" name="gbpPrice" id="gbpPrice" class="form-control input-sm" value="{{ $licenseTypeDetail->priceGBP }}"/>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-5">
            <label class="control-label">Discount %</label><br>
            <input type="text" name="discount" id="discount" class="form-control input-sm" />
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">
          <div class="form-group pull-right">
          <br>
            <input type="submit" value="Save Changes" class="btn btn-primary btn-sm" />
            <input type="submit" value="Cancel" class="btn btn-primary btn-sm" />
          </div>
        </div>
    </div>
{!! Form::close() !!}

<script>
$(function () {
    jQuery('#volumeOfUsers').spinner({min: 0});
});

$('#updateShoppingCart').submit(function(e) {
    e.preventDefault();

    var inputtags = JSON.stringify($("#tags").tagsinput('items'));

    $.ajax({
        type: 'post',
        url: $('#updateShoppingCart').attr('action'),
        cache: false,
        data: $('#updateShoppingCart').serialize() + "&inputtags="+inputtags,
        beforeSend: function() {
        $('#error').html('<div class="alert alert-info">Submitting..</div>');
        },
        success: function(data) {
            var obj = jQuery.parseJSON(data);

            if(obj.success === false) {
                $('#error').html('<div class="alert alert-danger"><p>'+obj.error+'</p></div>');
            } else {
                //location.reload();
            }
        },
        error: function(xhr, textStatus, thrownError) {
            alert('Something went wrong. Please try again later!');
        }
    });
    return false;
});
</script>