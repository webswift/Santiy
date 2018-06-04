<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta content='{{ csrf_token() }}' name='csrf-token'>

	<title>Statistics Page</title>

	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
	{!! Html::style('assets/css/style.default.css') !!}
	{!! HTML::style('assets/css/morris.css') !!}
	<style>
	.legendLabel {
		padding : 3px;
	}

	.legendColorBox {
		padding-left: 5px;
	}
    </style>

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	{!! HTML::script("assets/js/html5shiv.js") !!}
    {!! HTML::script("assets/js/respond.min.js") !!}
	<![endif]-->
</head>

<body>
<!-- Preloader -->
<div id="preloader">
    <div id="status"><i class="fa fa-spinner fa-spin"></i></div>
</div>

<section>
	<div class="mainpanel" style="margin-left: 0px;">
    	<div class="headerbar">
    		<div class="logopanel" style="border-right: none">
				<img src="{{ URL::asset("assets/images/logo.png") }}" style="height: 30px;"/>
            </div>
            <h4 style="
                margin: 0;
                padding: 10px;
            ">Campaign Statistics</h4>
        <div>

	</div><!-- header-right -->
	</div><!-- headerbar -->
    	<div class="pageheader"><h2><i class="fa fa-pie-chart"></i> {{$campaign->name}}</h2></div>
    	<div class="contentpanel">
    		<div class="panel panel-default">
				<div class="panel-heading hidden">
					<div class="panel-btns">
						<a href="" class="minimize">&minus;</a>
					</div>
					<div class="form-horizontal">
					<div class="form-group">
						<div class="col-sm-2">
							<select class="form-control" id="selUserId">
								<option value="All" selected>All members</option>
								@if(sizeof($teamMembers) > 0)
									@foreach($teamMembers as $team)
										<option value="{{$team->id}}">@if($team->id == $user->id){{'Me'}}@else{{$team->firstName.' '.$team->lastName}}@endif</option>
									@endforeach
								@endif
							</select>
						</div>

					</div>
					</div>
				</div><!-- panel-heading -->
    			<div class="panel-body">
    				<div class="row">
    					<div class="col-md-12 mb30">
    						<h5 class="subtitle mb5">CALL VOLUME</h5>
    						<p class="mb15">Your daily call volume throughout the duration of this campaign.</p>
							<p id="pTotalActionedLeads" class="mb15 hidden"></p>
							<p id="pTotalEmailsSent" class="mb15 hidden"></p>
    						<div id="barchart" style="width: 100%; height: 300px"></div>
    					</div>
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
							@if(sizeof($campaign['formFields']) > 0)
								@foreach($campaign['formFields'] as $data)
									<option value="{{$data->fieldName}}">{{$data->fieldName}}</option>
								@endforeach
							@endif
						</select>
						<div id="basicflot" style="width: 100%; height: 300px"></div>
						<div id="teaminterestlegend" class="form-control"></div>
					</div><!-- col-md-6 -->
    				</div>
    			</div>
    		</div>
    	</div>
    </div><!-- mainpanel -->
</section>

{!! HTML::script('assets/js/jquery-1.11.1.min.js') !!}
{!! HTML::script('assets/js/jquery-migrate-1.2.1.min.js') !!}
{!! HTML::script('assets/js/jquery-ui-1.10.3.min.js') !!}
{!! HTML::script('assets/js/bootstrap.min.js') !!}
{!! HTML::script('assets/js/modernizr.min.js') !!}
{!! HTML::script('assets/js/jquery.sparkline.min.js') !!}
{!! HTML::script('assets/js/toggles.min.js') !!}
{!! HTML::script('assets/js/retina.min.js') !!}
{!! HTML::script('assets/js/jquery.cookies.js') !!}
{!! HTML::script('/assets/js/jquery.blockui.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.resize.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.symbol.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.crosshair.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.categories.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.pie.min.js') !!}
{!! HTML::script('assets/js/flot/jquery.flot.orderBars.js') !!}
{!! HTML::script('assets/js/morris.min.js') !!}
{!! HTML::script('assets/js/raphael-2.1.0.min.js') !!}
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/select2.min.js') !!}
{!! HTML::script('assets/js/jquery.gritter.min.js') !!}

<script type="text/javascript">
	var assetsPath = "{{ URL::asset("assets") }}/images/";
</script>

<script>
$(function(){
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$('#selUserId').change(function() {
		var userId = $("#selUserId").val();
		$('#teamInterest').val('interest');
		var performanceType = $('#performanceType').val();

		showCallVolume(userId);

		@if($user->userType != 'Single')
			showTeamPerformance(performanceType, userId);
		@endif
	});

	$('#performanceType').change(function() {
		
		var userId = $("#selUserId").val();
		var performanceType = $('#performanceType').val();
		showTeamPerformance(performanceType, userId);
	});

	$('select[name=teamInterest]').change(function(){
		var teamInterest = $(this).val();
		var userId = $("#selUserId").val();
		showCustomCallVolume(teamInterest, userId);
	});

	var userId = $("#selUserId").val();
	var performanceType = $('#performanceType').val();

	@if($user->userType != 'Single')
		showTeamPerformance(performanceType, userId);
	@endif
		
	$('#teamInterest').val('interest');

	showCallVolume(userId);
});

	function showUserStats(userId) 
	{
		$("#selUserId").val(userId);
		$("#selUserId").triggerHandler('change');
		return false;
	}

function showCustomCallVolume(teamInterest, userId){
	$('#basicflot').html('');
	$("#teaminterestlegend").html('');
	//blockUI('#customCallVolume');

	$.post('{{URL::route('statistics.share.showCustomCallVolume')}}',
			{
				'teamInterest' : teamInterest, 
				'userID' : userId, 
				'token' : '{{$token}}', 
				'type' : 'share'
			}
			, function(response){
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

    var performanceType = $('#performanceType').val();
});

function showCallVolume(userId) {
	$('#barchart').html('');
	$('#basicflot').html('');
	$("#teaminterestlegend").html('');
	$('#pTotalActionedLeads').addClass('hidden');
	$('#pTotalEmailsSent').addClass('hidden');

	$.ajax({
		type: 'post',
		url: "{{URL::route('statistics.share.showcallvolume')}}",
		cache: false,
		data: {
			'userID' : userId, 
			"token": '{{$token}}', 
			'type' : 'share'
		},
		success: function(response) {
			var obj = jQuery.parseJSON(response);

			if(obj.success == "success") {
				if(obj.callVolumeCountFound == true) {
					/***** BAR CHART *****/
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
					/*****SIMPLE CHART*****/
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

function showTeamPerformance(performanceType, userId) {
	$('#piechart').html('');
	$.ajax({
		type: 'post',
		url: "{{URL::route('statistics.share.teamperformance')}}",
		cache: false,
		data: {
			"actionType": performanceType, 
			'userID' : userId, 
			"token": '{{$token}}', 
			'type' : 'share'
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
}
</script>
</body>
</html>
