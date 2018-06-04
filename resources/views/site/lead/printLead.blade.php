<html>
<head>
<style type="text/css">
body {
	margin: 10px;
	padding: 0;
	font-family:  sans-serif;
	font-size: 12px;
}
.heading {
	background-color: #ffffff;
	padding: 10px;
	margin: 0;
	text-align: center;
	color: #000000;
	font-size: 16px;
	font-weight: bold;
}
.campaign {
	font-size: 18px;
}

table {
	width: 100%;
	font-family: sans-serif;
}
table td {
	padding: 10px;
	border-bottom: 1px solid #cccccc;
}
table tr {
	
}

.fieldName{
	font-weight: bold;
	width: 150px;
	background: #f3f3f3;
	border-left: 1px solid #cccccc;
	border-right: 1px solid #cccccc;
}
.fieldName.first {
}

.fieldValue.last {
	border-right: 1px solid #cccccc;
}

.footer {
	  bottom: 10px;
	  position: absolute;
	  text-align: center;
	  font-weight: bold;
	  width: 97%;
}
.callTD{
padding-top: 10px;;
}
</style>

</head>
<body>
<div class="heading campaign">{{$leads[0]->name}}</div>
@if(sizeof($leads) > 0)
@for($j = 0; $j < sizeof($leads); $j++)

	<div class="heading" style="border-bottom: 1px solid #cccccc;">Lead #{{$leads[$j]->currentLeadNumber}}</div>
	<table border="0" cellpadding="0" cellspacing="0" style="border-bottom: 1px solid #cccccc;">

	@for($i = 0; $i < sizeof($leads[$j]->leadData); $i++)
	@if(($i != 0) && ($i % 2 == 0))
    	</tr>
	@endif

	@if($i % 2 == 0)
	<tr>
	@endif

	<td class="fieldName @if($i%2 == 0){{'first'}}@endif">{{$leads[$j]->leadData[$i]->fieldName}}</td>
	<td class="fieldValue @if(($i+1)%2 == 0){{'last'}}@endif">@if($leads[$j]->leadData[$i]->value != '' && $leads[$j]->leadData[$i]->value != null){{$leads[$j]->leadData[$i]->value}}@else{{'-'}}@endif</td>
	@endfor
	
	@if($i % 2 != 0)
	<td class="fieldName"></td>
	<td class="fieldValue last"></td>
	@endif
	</tr>

	<tr>
	<td class="fieldName first">Interested</td>
	<td class="fieldValue">{{\App\Http\Controllers\CommonController::getInterestedType($leads[$j]->interested)}}</td>

	<td class="fieldName">Reference</td>
    <td class="fieldValue last">@if($leads[$j]->referenceNumber != null && $leads[$j]->referenceNumber != ''){{$leads[$j]->referenceNumber}}@else{{'-'}}@endif</td>
	</tr>

	<tr>
	<td class="fieldName first">Follow up call</td>
	<td class="fieldValue">@if($leads[$j]->callBack){{$leads[$j]->callBackTime .', '.$leads[$j]->callBackUser->firstName.' '.$leads[$j]->callBackUser->lastName}}@else{{'No'}}@endif</td>

	<td class="fieldName">Book Appointment</td>
	<td class="fieldValue last">@if($leads[$j]->bookAppointment == 'Yes'){{$leads[$j]->appointmentTime .', '.$leads[$j]->salesMemberName}}@else{{'No'}}@endif</td>
	</tr>
    </table>

    <div class="heading" style="border-bottom: 1px solid #cccccc;">Call history</div>

    <table style="font-size: 11px" border="0" cellpadding="0" cellspacing="0">
    	<thead>
    	<tr>
    	<th class="callTD">Date</th>
		<th class="callTD">Agent</th>
		<th class="callTD">Email</th>
		<th class="callTD">Notes</th>
		<th class="callTD">FollowUp Call</th>
		<th class="callTD">Appointment</th>
		<th class="callTD">Call time</th>
		</tr>
    	</thead>
    	<tbody>
    	@if(sizeof($leads[$j]->callHistory) > 0)
    		@foreach($leads[$j]->callHistory as $data)
    		<tr>
				<td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d/m/Y')}}</td>
				<td>{{$data->agentName}}</td>
				<td>@if($data->emailName != null && $data->emailName != ''){{$data->emailName}}@else{{'-'}}@endif</td>
				<td>@if($data->notes != null && $data->notes != ''){!! $data->notes !!}@else{{'-'}}@endif</td>
				<td>@if($data->callBookedWithName != null && $data->callBookedWithName != ''){{$data->callBookedWithName}}@else{{'-'}}@endif</td>
				<td>@if($data->appointmentBookedWith != null && $data->appointmentBookedWith != ''){{$data->appointmentBookedWith}}@else{{'-'}}@endif</td>
				<td>{{\App\Http\Controllers\CommonController::getTimeFromMillisecond($data->callTime)}}</td>
			</tr>
    		@endforeach
    	@else
    	<tr><td colspan="7" style="text-align: center">No call history found</td></tr>
    	@endif
    	</tbody>
    </table>
    <div class="footer">SanityOS.com</div>

	@if($j<(sizeof($leads)-1))
    <p style="page-break-before: always"></p>
    @endif
@endfor
@endif

