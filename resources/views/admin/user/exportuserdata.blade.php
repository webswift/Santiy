@extends('layouts.admindashboard')

@section('title')
    Export User Data
@stop

@section('content')

<div class="pageheader">
    <h2><i class="glyphicon glyphicon-edit"></i> Export User Data</h2>

</div>

<div class="contentpanel">

    <div class="panel-heading">
        <h3 class="panel-title">Data Bank </h3>
        <p>Export leads uploaded by users</p>
    </div><!-- panel-heading-->

    <div class="panel">
    <div class="panel-body">

    <div id="error" ></div>
    <div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label class="control-label">Industry</label>
            <input type="hidden" value="" id="licenseClass" name="licenseClass">
            <select name="industry" id="industry" class="form-control">
                @foreach($industries as $industry)
                    <option value="{{ $industry }}">{{ $industry }}</option>
                @endforeach
            </select>
        </div>
    </div><!-- col-sm-6 -->
    </div>
    <div class="row">
        <div class="col-sm-4">
            <label class="control-label">Filter Date Range</label>
            <div class="input-group">
                <input type="text" name="startDate" class="form-control" placeholder="yyyy/mm/dd" id="startDate">
                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
            </div>
        </div><!-- col-sm-6 -->
        <div class="col-sm-4">
            <label class="control-label">&nbsp;</label>
            <div class="input-group">
                <input type="text" name="endDate" class="form-control" placeholder="yyyy/mm/dd" id="endDate">
                <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
            </div>

        </div><!-- col-sm-6 -->
    </div><br>
    <div class="row">
        <div class="col-sm-12">
            <div class="form-group">
                <button onclick="searchResult()" class="btn btn-primary mr5" data-toggle="modal" data-target=".bs-example-modal-lg">Search</button>
            </div>
        </div>
    </div>


    </div><!-- panel-body -->
    </div><!-- panel -->

    <div class="panel">

        <div class="panel-body">

            <div id="error"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive" id="tableData">
                        <table class="table table-success mb30">
                            <thead>
                            <tr>
                                <th>Uploaded By</th>
                                <th>Total Leads</th>
                                <th>Total Number of database</th>
                                <th>Industry</th>
                                <th>Export as Zip</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div><!-- table-responsive -->
                </div>


        </div><!-- panel-body -->
    </div><!-- panel -->


</div><!-- contentpanel -->
</div>
@stop

@section('javascript')
<script type="text/javascript">

    // Date Picker
    jQuery('#startDate').datepicker({ dateFormat: 'yy-mm-dd' });
    jQuery('#endDate').datepicker({ dateFormat: 'yy-mm-dd' });


    function searchResult()
    {
        $('#error').html('');
        var industry = $('#industry').val();
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();

        if(startDate == '' && endDate == '')
        {
            $('#error').html('<div class="alert alert-danger">Please Select a Date</div>');
        }else{
            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.users.ajaxexportuserdatatable') }}",
                cache: false,
                data: {"industry": industry, "startDate": startDate, "endDate": endDate},
                success: function(response) {

                    $('#tableData').html(response);
                },
                error: function(xhr, textStatus, thrownError) {
                    alert('Something went wrong. Please Try again later!');
                }
            });
        }

    }

    function exportIt(obj)
    {
        var startDate = $(obj).attr('startdate');
        var endDate = $(obj).attr('endDate');
        var industry = $(obj).attr('industry');
        var creator = $(obj).attr('creator');

        location.href = "{{ URL::route('admin.users.exportuserdatatozip') }}?startDate="+startDate+"&endDate="+endDate+"&industry="+industry+"&creator="+creator;
    }

</script>
@stop