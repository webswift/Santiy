@extends('layouts.dashboard')

@section('css')
	{!! Html::style('assets/css/jquery.datatables.css') !!}
    <style>
        .colToggle{
            margin-top: 3px;
            cursor: pointer;
        }
        .colToggle.enable{
            color: #ffffff;
        }
        .colToggle.disable{
            color: #808080;
        }
        .prev{
            margin-right: 1.6em !important;
        }

        .leadRow{
            cursor: pointer;
        }

        .sorting-div {
            cursor: pointer;
        }

        .highlight {
            color: #bcbcbc;
        }
    </style>
@stop

@section('title')
	Inbound leads
@stop

@section('content')
<div class="pageheader">
    <h2><i class="glyphicon glyphicon-arrow-up"></i> Inbound leads</h2>
</div>

<div class="contentpanel">
    <ul class="nav nav-tabs nav-dark">
        <li class="active"><a data-toggle="tab" href="#landing"><strong>Leads from Web Forms</strong></a></li>
    </ul>
    <div class="tab-content">

        <div id="landing" class="tab-pane active">
            <div class="panel">
                <div class="panel-body">
                    <div class="form-horizontal form-bordered">
                        <div class="form-group">
                            <div id="error" class="error"></div>
                            <div class="col-sm-2">
                                <label class="control-label pull-right" for="campaign">Select Campaign</label>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control" id="campaign">
                                    <option value="all" selected> All Campaigns </option>
                                    @if(sizeof($campaignLists) > 0)
                                        @foreach($campaignLists as $campaignList)
                                            <option value="{{ $campaignList->id }}">{{ $campaignList->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button id="exportBtn" class="btn btn-primary" onclick="exportLeads()">Export</button>
                            </div>
                            <div class="col-sm-3">
								<a href="{{ URL::route('user.forms.landing.createoredit') }}" title="Manage Web Forms" class="btn btn-primary btn-sm pull-right">Manage Web Forms</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <div class="table-responsive" id="landingDiv">
                        <table id="landingTable" class="table table-success">
                            <thead>
                            <tr>
                                <th>Lead received on</th>
                                <th>First Name</th>
                                <th>Telephone</th>
                                <th>Email</th>
                                <th>Post/Zip Code</th>
                                <th>Open</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr><td colspan="6" class="text-center"><i>Select a campaign</i></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}

<script type="text/javascript">
$(document).ready(function() {
    defaultCampaignSelect();
    showLandingLeads();

    $('#exportBtn').hide();


    $('#landingDiv').on('click', '.pagination a', function(response) {
        blockUI('#landingDiv');
        event.preventDefault();

        if ( $(this).attr('href') != '#' ) {
            $("html, #resultDiv").animate({ scrollTop: 0 }, "fast");

            var url = $(this).attr('href');
            $.get(url, function(response) {
                $('#landingDiv').html(response);
                unblockUI('#landingDiv');
            });
        }
    });

    $('#campaign').change(function() {
        var campaign = $(this).val();
        if(campaign == undefined || campaign == '') {
            return;
        }

        if(campaign == 'all') {
            $('#exportBtn').hide();
        }
        else {
            $('#exportBtn').show();
        }
        showLandingLeads();
    });
});

function getFormData() {
    var campaign = $('#campaign').val();
    if(campaign == undefined || campaign == '') {
        showFieldError('Select a campaign', 'campaign');
        return false;
    }
    var formData = {
        'campaign' : campaign
    };

    return formData;
}

function showLandingLeads(sorting, sortingObj) {
    var formData = getFormData();

    if(!formData) {
        return false;
    }
    hideErrors();

    if(sorting == true) {
        //formData = formData.
        $.extend(true, formData, formData, sortingObj);
    }

    $.ajax({
        type: 'get',
        url: "{{ URL::route('user.leads.landingLeadData') }}",
        data: formData,
        cache: false,
        beforeSend: function() {
            blockUI("#landingDiv");
        },
        success: function(data) {
            unblockUI("#landingDiv");
            $('#landingDiv').html(data);
        },
        error: function(xhr, textStatus, thrownError) {
            unblockUI("#landingDiv");
            showError('Error getting data. Please try again!');
        }
    });
}

function exportLeads() {
    var formData = getFormData();

    if(!formData) {
        return false;
    }
    hideErrors();

    window.location.href = '{{ URL::route('user.leads.landingExport') }}?campaign='+formData.campaign;
}

function defaultCampaignSelect() {

    $('.formValueDiv').html('<input type="text" class="form-control" id="fieldValue">');
}

/* validates if lead isn't opened by other member before opening
*/
function openInboundLead(leadID) 
{
	$.post('{{URL::route('user.leads.isLeadLocked')}}', {'leadID' : leadID}, function(response) {
		if(typeof response.status != "undefined" && response.status == "success") {
			var url = "{{URL::route('user.leads.createlead', array('_leadID_', 'edit' => 'edit'))}}";
			url = url.replace('_leadID_', leadID);

			window.location.href = url;
		} else {
			var message = "Unknown error";
			if(typeof response.message != "undefined") {
				message = response.message ;
			}
			showError(message);
		}
	});
	return false;
}

</script>
@stop
