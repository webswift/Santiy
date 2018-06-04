<div class="modal-header">
    <button aria-hidden="true" class="close closeMappingModal" type="button">&times;</button>
    <h4 class="modal-title">Field Mapping</h4>
</div>
<form id="mappingForm" class="form-horizontal form-bordered">
    <div class="modal-body">
        <div class="form-group">
            <label class="col-md-3 control-label">First Name</label>
            <div class="col-md-6">
                <select name="first_name" id="first_name" class="form-control mapping">
                    <option value="">Select</option>
                    @forelse($fields as $field1)
                        <option value="{{ $field1 }}"> {{ $field1 }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Last Name</label>
            <div class="col-md-6">
                <select name="last_name" id="last_name" class="form-control mapping">
                    <option value="">Select</option>
                    @forelse($fields as $field2)
                        <option value="{{ $field2 }}"> {{ $field2 }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Company Name</label>
            <div class="col-md-6">
                <select name="company_name" id="company_name" class="form-control mapping">
                    <option value="">Select</option>
                    @forelse($fields as $field3)
                        <option value="{{ $field3 }}"> {{ $field3 }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-3 control-label">Email</label>
            <div class="col-md-6">
                <select name="email" id="email" class="form-control mapping" required>
                    <option value="">Select</option>
                    @forelse($fields as $field4)
                        <option value="{{ $field4 }}"> {{ $field4 }}</option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default closeMappingModal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<script>
$.fn.serializeFormJSON = function () {

    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

$('#mappingForm').submit(function(e) {
    e.preventDefault();

    var mapping = $(this).serializeFormJSON();
    MassEmail.setMapping(mapping);
    $('#leadMappingModal').modal('hide');

    MassEmail.getCustomLeadEmailCount(mapping, '{{ URL::route('user.email.mass.csvFileEmails') }}');
});
    
$('.closeMappingModal').click(function () {
    var mapping = MassEmail.getMapping();
    if(mapping == undefined || mapping == '') {
        showError("Something went wrong or mapping is not done. Try again");
        $('#datasetType').val('');
        $('#datasetType').trigger('change');
        $('#leadMappingModal').modal('hide');
        //window.location.reload();
    }
    else {
        $('#leadMappingModal').modal('hide');
    }
});    
</script>