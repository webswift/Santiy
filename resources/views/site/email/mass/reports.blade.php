@extends('layouts.dashboard')

@section('css')
    {!! Html::style('assets/css/morris.css') !!}
    {!! Html::style('assets/css/jquery.datatables.css') !!}
    <style>
        .table-primary thead tr th {
            background-color: #428BCA!important;
        }

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
    </style>
@stop

@section('title')
    Mass Mail Template Report
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-user"></i>Mass Mail Report</h2>
</div>
<div class="contentpanel">
    <div class="panel panel-default">
        <div class="panel-heading">
            <a href="{{ URL::route('user.email.mass') }}">Back To Template</a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-8">
                    @if($isDataAvailable)
                    <div style="min-height: 300px;" id="bounceRate"></div>
                    @else
                        <p>No Data Available</p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 pull-right">
                    <select class="form-control" id="filter" name="filter">
                        <option value="All">All</option>
                        @if(in_array($template->mail_setting_type, ['Superadmin', 'sparkpost', 'mandrill']))
                            <option value="bounce">Display Bounced Recipients</option>
                        @endif
                        <option value="open">Display Opened Recipients</option>
                        <option value="click">Display Clicked Recipients</option>
                        <option value="unsubscribe">Display Unsubscribed Recipients</option>
                    </select>
                </div>
            </div>
            @if($template->type == 'custom')
                <div class="table-responsive">
                    <table id="customTemplate" class="table table-primary table-striped display">
                        <thead>
                        <tr>
                            <th>Email Address </th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Company Name</th>
                            <th>Sent on</th>
                            <th>Status</th>
                            <th>Opens</th>
                            <th>Clicks</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            @elseif($template->type == 'campaign')
                <div class="table-responsive">
                    <table id="campaignTemplate" class="table table-primary table-striped display" >
                        <thead>
                        <tr>
                            <th>
                                <i class="fa fa-chevron-left pull-left colToggle enable prev" onclick="getPreviousColumn();"></i>
                                Email Address
                                <i class="fa fa-chevron-right pull-right colToggle next enable" onclick="getNextColumn();"></i>
                            </th>
                            <?php $count = 0; ?>
                            @forelse($fields as $field)
                                @if($field != 'First Name' && $field != 'Last Name' && $field != 'Email')
                                    <th class="">
                                        <i class="fa fa-chevron-left pull-left colToggle enable prev" onclick="getPreviousColumn();"></i>
                                        {{ $field }}
                                        <i class="fa fa-chevron-right pull-right colToggle next enable" onclick="getNextColumn();"></i>
                                    </th>
                                    <?php $count++; ?>
                                @endif
                            @empty
                            @endforelse
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Sent on</th>
                            <th>Status</th>
                            <th>Opens</th>
                            <th>Clicks</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('javascript')
<script src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
{!! Html::script('assets/js/flot/jquery.flot.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.resize.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.symbol.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.crosshair.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.spline.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.categories.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.pie.min.js') !!}
{!! HTML::script('assets/js/morris.min.js') !!}
{!! HTML::script('assets/js/raphael-2.1.0.min.js') !!}
{!! HTML::script('assets/js/custom.js') !!}

<script>
var oTable = undefined;

@if($template->type == 'campaign')
var extraCol = {!! $count !!};
var currentCol = 0;
@endif

$( function() {
    @if($isDataAvailable)
    drawConversionChart({!! json_encode($pieData) !!});
    @endif

    @if($template->type == 'custom')
    oTable = $('#customTemplate').dataTable( {
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $thisUrl }}',
            data : function(d) {
                d.filter = $('#filter').val()
            }
        },
        columns: [
            {data: 'email', name: 'email'},
            {data: 'first_name', name: 'first_name'},
            {data: 'last_name', name: 'last_name'},
            {data: 'company_name', name: 'company_name'},
            {data: 'updated_at', name: 'updated_at'},
            {data: '{{ $statusCol }}', name: '{{ $statusCol }}'},
            {data: 'email_open_count', name: 'email_open_count'},
            {data: 'email_click_count', name: 'email_click_count'}
        ],
        searching : false,
        "sPaginationType": "full_numbers"
    });
    @elseif($template->type == 'campaign')
    oTable = $('#campaignTemplate').dataTable( {
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $thisUrl }}',
            data : function(d) {
                d.filter = $('#filter').val()
            }
        },
        columns: [
            {data: 'email', name: 'email', width:'20%'},
            @forelse($fields as $field)
               @if($field != 'First Name' && $field != 'Last Name' && $field != 'Email')
                   {data: '{{ str_replace(".", "\\\\.", $field) }}', name: '{{ str_replace(".", "", $field) }}', width:'20%', visible : false, orderable : false},
                @endif
            @empty
            @endforelse
            {data: 'first_name', name: 'first_name', width:'10%'},
            {data: 'last_name', name: 'last_name', width:'10%'},
            {data: 'updated_at', name: 'updated_at', width:'10%'},
            {data: '{{ $statusCol }}', name: '{{ $statusCol }}', width:'10%'},
            {data: 'email_open_count', name: 'email_open_count', width:'10%'},
            {data: 'email_click_count', name: 'email_click_count', width:'10%'},
            {data: 'action', name: 'action', width:'10%', orderable : false, searchable:true}
    ],
        searching : false,
        "sPaginationType": "full_numbers"
    });
    @endif

    $('#filter').change(function() {
        oTable.fnDraw();
    });


    $('a.toggle-vis').on( 'click', function (e) {
        e.preventDefault();

        // Get the column API object
        var column = oTable.api().column( $(this).attr('data-column') );

        // Toggle the visibility
        column.visible( ! column.visible() );
    } );
});

function drawConversionChart(response) {
    jQuery.plot('#bounceRate', response, {
        series: {
            pie: {
                show: true,
                radius: 1,
                label: {
                    show: true,
                    radius: 3/4,
                    formatter: labelFormatter,
                    threshold: 0.1
                }
            }
        },
        grid: {
            hoverable: true,
            clickable: true
		},
		legend: {
			labelFormatter: function(label, series) {
				return label + ": " + series.data[0][1] + " (" + Math.round(series.percent) + "%)";
			}
		}
    });

    function labelFormatter(label, series) {
        return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label.substring(0, label.indexOf(':')) + "<br/>" + Math.round(series.percent) + "%</div>";
    }
}

function getNextColumn() {
    var nextCol = currentCol + 1;
    if(nextCol > extraCol) {
        return false;
    }
    var currColumn = oTable.api().column( currentCol );
    currColumn.visible(false);

    var column = oTable.api().column( nextCol );

    // Toggle the visibility
    column.visible( ! column.visible() );

    currentCol = nextCol;
}

function getPreviousColumn() {
    var nextCol = currentCol - 1;
    if(nextCol < 0) {
        return false;
    }

    var currColumn = oTable.api().column( currentCol );
    currColumn.visible(false);

    var column = oTable.api().column( nextCol );

    // Toggle the visibility
    column.visible( ! column.visible() );

    currentCol = nextCol;
}
</script>
@stop
