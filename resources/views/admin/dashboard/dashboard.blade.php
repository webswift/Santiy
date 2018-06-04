@extends('layouts.admindashboard')

@section('title')
	Dashboard
@stop

@section('css')
{!! Html::style('assets/css/morris.css') !!}
{!! HTML::style('assets/css/bootstrap-timepicker.min.css') !!}
@stop

@section('content')
<div class="pageheader">
    <h2><i class="fa fa-home"></i> Dashboard <span>7 Days Overview</span></h2>
</div>

<div class="contentpanel">
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-success panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-user.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">TOTAL USERS</small>
                                <h1>{{ $totalUsers }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="panel panel-danger panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-document.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">NEW USERS</small>
                                <h1>{{ $newUsers }}</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="panel panel-primary panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-document.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label"> ABANDONED CARTS </small>
                                <h1>  {{ $abandonedCart }} </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="panel panel-dark panel-stat">
                <div class="panel-heading">
                    <div class="stat">
                        <div class="row">
                            <div class="col-xs-4">
                                <img src="{{asset('assets/images/is-money.png')}}" alt="" />
                            </div>
                            <div class="col-xs-8">
                                <small class="stat-label">  REVENUE </small>
                                <h1>
                                @if((\App\Models\Setting::get('baseCurrency')) == 'USD')
                                    ${{ intval($revenue) }}
                                @elseif((\App\Models\Setting::get('baseCurrency')) == 'EUR')
                                    €{{ intval($revenue * (\App\Models\Setting::get('euroToDollar'))) }}
                                @elseif(\App\Models\Setting::get('baseCurrency') == 'GBP')
                                    £{{ intval($revenue * (\App\Models\Setting::get('gbpToDollar'))) }}
                                @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default panel-morris">
        <div class="panel-heading">
            <div class="panel-btns">
                <a href="" class="minimize">&minus;</a>
            </div>
            <h4 class="panel-title">Conversion Ratio </h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-8">
                            <div style="min-height: 300px;" id="conversionChart"></div>
                        </div>
                        <div class="col-md-4">
                            <h4>Results for Date:</h4>
                            <p>Trial users signed up: <span id="trialUsers">20</span></p>
                            <p>Converted to users: <span id="convertedUsers">20</span></p>
                            <p>Expired Trials: <span id="expiredUsers">20</span></p> <br>
                            <h4><strong>Conversion Ratio :</strong> <span id="ratio"></span>%</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <form class="" method="post" action="{{ URL::route('admin.dashboard.conversionratio') }}" id="conversionRatioForm">
                        <div class="form-group">
                            <div class="col-md-12"><label>Date Range</label></div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-6">
                                <div class="input-group mb15">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                    <input title="Form" type="text" placeholder="From" id="fromDate" class="form-control" name="fromDate" value="{{ $fromDate }}" />
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="input-group mb15">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                    <input title="To" type="text" placeholder="To" id="toDate" class="form-control" name="toDate" value="{{ $toDate }}"  />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <button id="submitBtn" type="submit" class="btn btn-primary">Apply</button>
                                <a href="{{ URL::route('admin.dashboard') }}" class="btn btn-default">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('modelJavascript')

{!! Html::script('assets/js/flot/jquery.flot.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.resize.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.symbol.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.crosshair.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.spline.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.categories.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.pie.min.js') !!}
{!! HTML::script('assets/js/morris.min.js') !!}
{!! HTML::script('assets/js/raphael-2.1.0.min.js') !!}
{!! HTML::script('assets/js/bootstrap-timepicker.min.js') !!}
{!! HTML::script('assets/js/jquery.maskedinput.min.js') !!}

<script>
jQuery(document).ready(function() {
    jQuery("#fromDate").mask("99/99/9999");
    jQuery("#fromDate").datepicker({
        dateFormat : 'dd/mm/yy'
    });
    jQuery("#toDate").mask("99/99/9999");
    jQuery("#toDate").datepicker({
        dateFormat : 'dd/mm/yy'
    });


    $('#conversionRatioForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: 'post',
            url: $(this).attr('action'),
            dataType: "JSON",
            data: $(this).serialize(),
            beforeSend : function() {
                blockUI('.panel-morris')
            },
            success: function (response) {
                unblockUI('.panel-morris');
                drawConversionChart(response);

                $('#ratio').html(response.convertedRatio);
                $('#trialUsers').html(response.trialUsers);
                $('#convertedUsers').html(response.convertedUsers);
                $('#expiredUsers').html(response.expiredUsers);
            },
            error: function (xhr, textStatus, thrownError) {
                unblockUI('.panel-morris');
                showError("There is some error. Reload the page and try again!");
            }
        });
    });

    $('#submitBtn').trigger('click');
});

function drawConversionChart(response) {

    if(response.isData == false) {
        $('#conversionChart').html('No data available');
        return false;
    }

    jQuery.plot('#conversionChart', response.pieData, {
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
        }
    });

    function labelFormatter(label, series) {
        return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
    }
}

</script>
@stop