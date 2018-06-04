@extends('layouts.admindashboard')

@section('title')
    Data Activity
@stop

@section('css')
    {!! Html::style('assets/css/morris.css') !!}
@stop

@section('content')
    <div class="pageheader">
        <h2><i class="fa fa-home"></i> Data Activity <span>7 Days Overview</span></h2>
    </div>

    <div class="contentpanel">
        <div class="panel panel-default panel-morris">
            <div class="panel-heading">
                <div class="panel-btns">
                    <a href="" class="minimize">&minus;</a>
                </div><!-- panel-btns -->
                <h4 class="panel-title">Dashboard </h4>

            </div><!-- panel-heading -->
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12 mb30">
                        <h5 class="subtitle">Volume of Data Collected</h5>
                        <div id="area-chart" class="body-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('modelJavascript')

    {!! HTML::script('assets/js/morris.min.js') !!}
    {!! HTML::script('assets/js/raphael-2.1.0.min.js') !!}

    <script>
        jQuery(document).ready(function() {
            "use strict";

            function showTooltip(x, y, contents) {
                jQuery('<div id="tooltip" class="tooltipflot">' + contents + '</div>').css( {
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 5
                }).appendTo("body").fadeIn(200);
            }


            /***** MORRIS CHARTS *****/
            var volumeCollected = [];
            volumeCollected = {!! json_encode($volumeCollected) !!};

            var m2 = new Morris.Area({
                // ID of the element in which to draw the chart.
                element: 'area-chart',
                // Chart data records -- each entry in this array corresponds to a point on
                // the chart.
                data: volumeCollected,
                xkey: 'y',
                ykeys: ['a'],
                labels: ['Volume'],
                lineColors: ['#1CAF9A'],
                lineWidth: '1px',
                fillOpacity: 0.8,
                smooth: false,
                hideHover: true,
                parseTime: false,
                hoverCallback: function (index, options, content, row) {
                    return "Volume: " + volumeCollected[index].a;
                }
            });
        });

    </script>
@stop