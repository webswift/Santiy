@extends('layouts.dashboard')

@section('css')
    {!! Html::style('assets/css/morris.css') !!}
	<style>
	.legendLabel {
		padding : 3px;
	}

	.legendColorBox {
		padding-left: 5px;
	}
    </style>
@stop

@section('title')
Campaign Reports
@stop

@section('content')

<div class="pageheader"><h2><i class="fa fa-pie-chart"></i> Campaign Reports</h2></div>

<div class="contentpanel">
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="panel-btns">
				<a href="" class="minimize">&minus;</a>
			</div>
			<div class="form-horizontal">
			<div class="form-group">
				<div class="col-sm-4">
					@if (count($campaignLists) > 0)
						<select class="form-control" id="campaignName">
							@foreach($campaignLists as $campaignList)
								<option value="{{ $campaignList->id }}" >{{ $campaignList->name }}</option>
							@endforeach
						</select>
					@endif
                </div>
                <div class="col-sm-3">
                	<select class="form-control" id="dateRange">
						<option value="10">Last 10 days</option>
						<option value="20">Last 20 days</option>
						<option value="30">Last 30 days</option>
						<option value="10000">From first and Last Recorded date</option>
						<option value="10001">Custom</option>
					</select>
                </div>
				<div class="col-sm-2 hidden">
					<select class="form-control" id="selUserId">
						<option value="All" selected>All members</option>
						@if(sizeof($teamMembers) > 0)
							@foreach($teamMembers as $team)
								<option value="{{$team->id}}">@if($team->id == $user->id){{'Me'}}@else{{$team->firstName.' '.$team->lastName}}@endif</option>
							@endforeach
						@endif
					</select>
				</div>

                @if(count($campaignLists) > 0)
                    <div class="col-sm-4"><label class="control-label"><a href="javascript:;" onclick="shareStatistics();">Click here to share this campaign statistics.</a></label></div>
                @endif
            </div>
			<div class="form-group">
                <div class="col-sm-7 col-md-offset-4 hidden" id="DateRangeDiv">
					<div class="col-sm-5">
						<div class="input-group">
							<input id="customRangeFrom" type="text" class="form-control" placeholder="Date From">
							<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>

					<div class="col-sm-1 text-center"><label class="control-label">to</label></div>

					<div class="col-sm-5">
						<div class="input-group">
							<input id="customRangeTo" type="text" class="form-control" placeholder="Date To">
							<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
						</div>
					</div>
                </div>
            </div>
            </div>
		</div><!-- panel-heading -->

        <div class="panel-body">
        	@if(count($campaignLists) > 0)
        		<div class="row">
        			<div class="col-md-12 mb30">
        				<h5 class="subtitle mb5">CALL VOLUME</h5>
        				<p class="mb15">Your daily call volume throughout the duration of this campaign.</p>
        				<p id="pTotalActionedLeads" class="mb15 hidden"></p>
        				<p id="pTotalEmailsSent" class="mb15 hidden"></p>
        				<div id="barchart" style="width: 100%; height: 300px"></div>
        			</div><!-- col-md-6 -->
				</div>

				<div class="row">
					@if($user->userType != 'Single')
						<div class="col-md-6 mb30">

							<div class="form-group">
								<div class="col-sm-7">
								<h5 class="subtitle mb5">TEAM PERFORMANCE</h5>
									<select class="form-control mb15" id="performanceType">
										<option value="calls" >Call volume</option>
										<option value="appointments" >Appointments</option>
										<option value="interestedCalls" >Positive Calls</option>
									</select>
								</div>
							</div>
							<div id="piechart" style="width: 100%; height: 300px"></div>
						</div><!-- col-md-6 -->
					@endif

					<div @if($user->userType == 'Single') class="col-md-12 mb30" @else class="col-md-6 mb30" @endif id="customCallVolume">
						<select class="form-control" name="teamInterest" id="teamInterest">
							<option value="interest">Positive/Negative/Unreachable</option>
							@if(sizeof($campaignLists) > 0)
								@foreach($campaignLists as $data)
									@if(sizeof($data['formFields']) > 0)
										@foreach($data['formFields'] as $field)
											<option value="{{$field->fieldName}}" id="{{$field->id}}" rel="{{$data->id}}">{{$field->fieldName}}</option>
										@endforeach
									@endif
								@endforeach
							@endif
							</select>
						<div id="basicflot" style="width: 100%; height: 300px"></div>
						<div id="teaminterestlegend" class="form-control"></div>
					</div><!-- col-md-6 -->
				</div>
			@else
				<div class="no-data-placeholder">Statistics are updated within 24 hours of the campaign being started. Please check back a little later</div>
			@endif
		</div><!-- panel-body -->
    </div><!-- panel -->
</div><!-- contentpanel -->

<!-- Modal -->
<div class="modal fade" id="linkModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Shareable link</h4>
      </div>
      <div class="modal-body"></div>
    </div>
  </div>
</div>

@stop


@section('modelJavascript')
{!! Html::script('assets/js/flot/jquery.flot.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.resize.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.symbol.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.crosshair.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.categories.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.pie.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.orderBars.js') !!}
{!! HTML::script('assets/js/morris.min.js') !!}
{!! HTML::script('assets/js/raphael-2.1.0.min.js') !!}
{!! HTML::script('assets/js/custom.js') !!}
@stop

@section('javascript')

@if(count($campaignLists) > 0)
<script>
	function setFormFieldWithCampaign(){
		var campaign = $('#campaignName').val();

		$('select[name=teamInterest]').find('option[value!=interest]').addClass('hidden');
		$('select[name=teamInterest]').find('option[rel='+campaign+']').removeClass('hidden');

		var dateRange = $("#dateRange").val();
		if(dateRange == '10001') {
			//custom range
			$('#DateRangeDiv').removeClass('hidden');
		} else {
			$('#DateRangeDiv').addClass('hidden');
		}
	}

	$(function(){
		$('#customRangeFrom, #customRangeTo').datepicker({
			dateFormat: 'dd-mm-yy' , 
			maxDate : 0
		});
		$('#customRangeFrom').datepicker( "setDate", -6);
		$('#customRangeTo').datepicker( "setDate", new Date());
		

		setFormFieldWithCampaign();

		$('#campaignName, #dateRange, #selUserId').change(function() {
			var campaignID = $("#campaignName").val();
			var userId = $("#selUserId").val();
			var dateRange = $("#dateRange").val();
			var customRangeFrom = $('#customRangeFrom').val();
			var customRangeTo = $('#customRangeTo').val();

			$('#teamInterest').val('interest');
			var performanceType = $('#performanceType').val();

			showCallVolume(campaignID, dateRange, customRangeFrom, customRangeTo, userId);

			@if($user->userType != 'Single')
				showTeamPerformance(performanceType, dateRange, customRangeFrom, customRangeTo, userId);
			@endif

			setFormFieldWithCampaign();
		});

		$('#performanceType').change(function() {
            var dateRange = $("#dateRange").val();
			var userId = $("#selUserId").val();
			var customRangeFrom = $('#customRangeFrom').val();
			var customRangeTo = $('#customRangeTo').val();

			var performanceType = $('#performanceType').val();
			showTeamPerformance(performanceType, dateRange, customRangeFrom, customRangeTo, userId);
		});

		$('select[name=teamInterest]').change(function(){
			var teamInterest = $(this).val();
			var campaignID = $('#campaignName').val();
			var userId = $("#selUserId").val();
			var dateRange = $("#dateRange").val();
			var customRangeFrom = $('#customRangeFrom').val();
			var customRangeTo = $('#customRangeTo').val();

			showCustomCallVolume(teamInterest, campaignID, dateRange, customRangeFrom, customRangeTo, userId);
		});
		
		$('#customRangeFrom, #customRangeTo').change(function(){
			var campaignID = $("#campaignName").val();
			var userId = $("#selUserId").val();
            var dateRange = $("#dateRange").val();
			var customRangeFrom = $('#customRangeFrom').val();
			var customRangeTo = $('#customRangeTo').val();

			var performanceType = $('#performanceType').val();
			$('#teamInterest').val('interest');

			showCallVolume(campaignID, dateRange, customRangeFrom, customRangeTo, userId);
            @if($user->userType != 'Single')
				showTeamPerformance(performanceType, dateRange, customRangeFrom, customRangeTo, userId);
			@endif
			setFormFieldWithCampaign();
		});
	});

	function showUserStats(userId) 
	{
		$("#selUserId").val(userId);
		$("#selUserId").triggerHandler('change');
		return false;
	}

	function showCustomCallVolume(teamInterest, campaignID, dateRange, customRangeFrom, customRangeTo, userId){
		$('#basicflot').html('');
		$("#teaminterestlegend").html('');

		//blockUI('#customCallVolume');

		$.post('{{ URL::route('user.statistics.showCustomCallVolume') }}', 
			{
				'teamInterest' : teamInterest, 
				'campaignID' : campaignID, 
				'userID' : userId, 
				"dateRange": dateRange,
				"customRangeFrom" : customRangeFrom, 
				"customRangeTo" : customRangeTo
			}, function(response){
			//unblockUI('#customCallVolume');

			if(response.success == "success") {
				if(response.dataFound == true) {
					var prepareData = [];

					$.each(response.chartData, function(key, value) {
					  prepareData.push({
					  	'data' : value.data,
					  	'label' : "&nbsp;" + key,
					  	'color' : value.color
					  });
					});

					var plot = jQuery.plot(jQuery("#basicflot"), prepareData, {
						series: {
							lines: {
								show: true,
								fill: true,
								lineWidth: 1,
								fillColor: {
									colors: [ { opacity: 0.1 }, { opacity: 0.1 }, { opacity: 0.1 }]
								}
							},
							points: { show: true },
							shadowSize: 0
						},
						legend: {
							container:$("#teaminterestlegend"), 
							noColumns: 4
						},
						grid: {
							hoverable: true,
							clickable: true,
							borderColor: '#ddd',
							borderWidth: 1,
							labelMargin: 10,
							backgroundColor: '#fff'
						},
						yaxis: {
							min: 0,
//							max: 15,
							color: '#eee',
							//tickSize: 2,
							tickDecimals: 0
						},
						xaxis: {
							color: '#eee',
							tickDecimals: 0
						}
					});

					$("#basicflot").bind("plothover", function (event, pos, item) {
						if (item) {
							var x = item.datapoint[0].toFixed(2),
							y = item.datapoint[1].toFixed(2);

							var posX = item.pageX+5;
							var posY = item.pageY+5;

							var windowWidth = $(window).width();
							if(posX + 150 > windowWidth) {
								posX = windowWidth - 150;
							}

							$("#tooltip").html(item.series.label + " = " + Math.round(y)) 
								.css({top: posY, left: posX}) 
								.fadeIn(200);
						}
						else {
							$("#tooltip").hide();
						}
					});
				}
				else{
					$('#basicflot').html("<div class=\"no-data-placeholder\">No Data Available</div>");
					$("#teaminterestlegend").html('');
				}
			}
		}, 'json');
	}

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

		/*****SIMPLE CHART*****/

        var previousPoint = null;

        jQuery("#basicflot").bind("plothover", function (event, pos, item) {
            jQuery("#x").text(pos.x.toFixed(2));
            jQuery("#y").text(pos.y.toFixed(2));

            if(item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    jQuery("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);

                    showTooltip(item.pageX, item.pageY,
                        item.series.label + " of " + parseInt(x) + " = " + parseInt(y));
                }

            }
            else {
                jQuery("#tooltip").remove();
                previousPoint = null;
            }

        });

        jQuery("#basicflot").bind("plotclick", function (event, pos, item) {
            if (item) {
                plot.highlight(item.series, item.datapoint);
            }
        });


        //Loading a Campaign Data when page load

        var campaignID = $('#campaignName').val();
		var userId = $("#selUserId").val();
        var dateRange = $("#dateRange").val();
		var customRangeFrom = $('#customRangeFrom').val();
		var customRangeTo = $('#customRangeTo').val();

        var performanceType = $('#performanceType').val();
		
		$('#teamInterest').val('interest');

        if(campaignID == '') {
            showError("Sorry no campaigns found of which you are a member.");
        }
        else{
			showCallVolume(campaignID, dateRange, customRangeFrom, customRangeTo, userId);

            @if($user->userType != 'Single')
				showTeamPerformance(performanceType, dateRange, customRangeFrom, customRangeTo, userId);
            @endif
        }
    });
</script>

<script>

function showCallVolume(campaignID, dateRange, customRangeFrom, customRangeTo, userId) {
    $('#barchart').html('');
    $('#basicflot').html('');
	$("#teaminterestlegend").html('');
	$('#pTotalActionedLeads').addClass('hidden');
	$('#pTotalEmailsSent').addClass('hidden');

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.statistics.showcallvolume') }}",
        cache: false,
		data: {
			"campaignID": campaignID,
			'userID' : userId, 
			"dateRange": dateRange,
			"customRangeFrom" : customRangeFrom, 
			"customRangeTo" : customRangeTo
		},
        success: function(response) {
            var obj = jQuery.parseJSON(response);

            if(obj.success == "success") {
                if(obj.callVolumeCountFound == true) {
                    var bardata = obj.callVolumeArray;
					var plotData = [
						{
							data: bardata,
							label: "Actioned Leads",
							color: "#428BCA",
							bars: {
                                show: true,
                                barWidth: obj.totalInfoEmailsSent > 0 ? 0.3 : 0.5,
								order:0
							}
						}
					];
					if(obj.totalInfoEmailsSent > 0) {
						plotData.push(
							{
								data: obj.infoEmailsSentByDate,
								label: "Info Emails Sent",
								color: "#9BBB59",
								bars: {
									show: obj.totalInfoEmailsSent > 0,
									fillColor: "#9BBB59",
									barWidth: 0.3,
									order:1
								}
							}
						);
					}
					jQuery.plot("#barchart", plotData,
					{
                        series: {
                            lines: {
                                lineWidth: 1
                            },
                            bars: {
                                show: true,
                                barWidth: 0.5,
                                lineWidth: 0,
                                fillColor: "#428BCA"
                            }
                        },
                        grid: {
                            borderColor: '#ddd',
                            borderWidth: 1,
                            labelMargin: 10
                        },
                        xaxis: {
                            mode: "categories",
                            tickLength: 0
                        },
                        yaxis: {
                            tickDecimals: 0
						},
						legend: {
							noColumns: 4,
							position: "nw",
							show: obj.totalInfoEmailsSent > 0
						}
                    });

					$('#pTotalActionedLeads').removeClass('hidden').html('Total Leads actioned: ' + obj.totalCallVolume);
					if(obj.totalInfoEmailsSent > 0) {
						$('#pTotalEmailsSent').removeClass('hidden').html('Emails Sent: ' + obj.totalInfoEmailsSent);
					}
                }
                else{
                    $('#barchart').html("<div class=\"no-data-placeholder\">No Data Available</div>");
                }

                if(obj.interestedOrNotInterestedFound == true) {
                    var interested = obj.interestedCalls;
                    var notInterested = obj.notInterestedCalls;

                    var plot = jQuery.plot(jQuery("#basicflot"),
						[
							{ data: interested,
								label: "&nbsp;Positive&nbsp;(" + obj.interestedCallsSum + ")",
								color: obj.colors[0]
							}
                            ,{ data: notInterested,
                                label: "&nbsp;Negative&nbsp;(" + obj.notInterestedCallsSum + ")",
                                color: obj.colors[1]
                            }
                            ,{ data: obj.unreachableCalls,
                                label: "&nbsp;Unreachable&nbsp;(" + obj.unreachableCallsSum + ")",
                                color: obj.colors[8]
                            }
                        ],
                        {
                            series: {
                                lines: {
                                    show: true,
                                    fill: true,
                                    lineWidth: 1,
                                    fillColor: {
										colors: [ 
											{ opacity: 0.1 }
                                            ,{ opacity: 0.1 }
                                            ,{ opacity: 0.1 }
                                        ]
                                    }
                                },
                                points: {
                                    show: true
                                },
                                shadowSize: 0
                            },
							legend: {
								container:$("#teaminterestlegend"), 
								noColumns: 4
							},
                            grid: {
                                hoverable: true,
                                clickable: true,
                                borderColor: '#ddd',
                                borderWidth: 1,
                                labelMargin: 10,
                                backgroundColor: '#fff'
                            },
                            yaxis: {
                                 min: 0,
                                color: '#eee',
                                //tickSize: 2,
                                tickDecimals: 0
                            },
                            xaxis: {
                                color: '#eee',
                                tickDecimals: 0
                              }
                        });

                        $("#basicflot").bind("plothover", function (event, pos, item) {
                            if (item) {
                                var x = item.datapoint[0].toFixed(2),
                                    y = item.datapoint[1].toFixed(2);

								var posX = item.pageX+5;
								var posY = item.pageY+5;

								var windowWidth = $(window).width();
								if(posX + 150 > windowWidth) {
									posX = windowWidth - 150;
								}

								$("#tooltip").html(item.series.label + " = " + Math.round(y)) 
									.css({top: posY, left: posX}) 
									.fadeIn(200);
                            } else {
                                $("#tooltip").hide();
                            }
                        });
                }
                else{
                    $('#basicflot').html("<div class=\"no-data-placeholder\">No Data Available</div>");
					$("#teaminterestlegend").html('');
                }
            }
        },
        error: function(xhr, textStatus, thrownError) {
            showError('Error loading stats for call volume. Please refresh to try again.');
        }
    });
}

function showTeamPerformance(performanceType, dateRange, customRangeFrom, customRangeTo, userId) {
    var campaignID = $('#campaignName').val();
    $('#piechart').html('');

    $.ajax({
        type: 'post',
        url: "{{ URL::route('user.statistics.teamperformance') }}",
        cache: false,
		data: {
			"actionType": performanceType, 
			"campaignID": campaignID,
			'userID' : userId, 
			"dateRange": dateRange,
			"customRangeFrom" : customRangeFrom, 
			"customRangeTo" : customRangeTo
		},
        success: function(response) {
            var obj = jQuery.parseJSON(response);

            if(obj.success == "success") {
                if(obj.dataFound == true) {
                    /***** PIE CHART *****/

                    var piedata = obj.pieData;

                    jQuery.plot('#piechart', piedata, {
                        series: {
                            pie: {
                                show: true,
                                radius: 1,
                                label: {
                                    show: true,
                                    radius: 2/3,
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
							labelFormatter: teamPerformanceLegendFormater
						}
                    });

                }else{
                    $('#piechart').html("<div class=\"no-data-placeholder\">No Data Available</div>");
                }

            }
        },
        error: function(xhr, textStatus, thrownError) {
            showError('Error loading stats for team performance. Please refresh to try again.');
        }
    });
}

jQuery(document).ready(function() {
	"use strict";
	$('#piechart').bind("plotclick", function(event, pos, obj) {
		if (!obj) {
			return;
		}

		showUserStats(obj.series.user_id);
	});
});

function teamPerformanceLegendFormater(label, series) {
	var userId = $("#selUserId").val();

	var html = '';
	if(userId != 'All') {
		html = label;
		html += "</td></tr><tr><td></td><td>"; //ugly hack to add new row into legend
		html += '<a href="javascript:void(0);" onclick="showUserStats(' + "'All'" + ');">Display All</a>';
	} else {
		html = '<a href="javascript:void(0);" onclick="showUserStats(' + series.user_id + ');">' + label + ' (' + Math.round(series.percent)  + '%)</a>';
	}

	return html;
}


function labelFormatter(label, series) {
    return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + series.label + "<br/>" + Math.round(series.percent) + "%</div>";
}

function shareStatistics(){
    var campaign = $('#campaignName').val();
    var dateRange = $("#dateRange").val();
	var customRangeFrom = $('#customRangeFrom').val();
	var customRangeTo = $('#customRangeTo').val();

    blockUI('.contentpanel');

	$.post('{{URL::route('user.statistics.getShareLink')}}', 
	{
		'campaign' : campaign, 
		'dateRange': dateRange,
		"customRangeFrom" : customRangeFrom, 
		"customRangeTo" : customRangeTo
	}, function(response){
        unblockUI('.contentpanel');

        if(response.status == 'success') {
            $('#linkModal').find('.modal-body').html('');
            $('#linkModal').find('.modal-body').html('<a href="'+response.link+'" target="_blank">'+response.link+'</a>');

            $('#linkModal').modal('show');
        }
    }, 'json');
}
</script>
@endif
@stop
