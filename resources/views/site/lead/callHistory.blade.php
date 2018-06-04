<div class="table-responsive">
	<table class="table table-hover mb30">
		<thead>
			<tr>
				<th>Date</th>
                <th>Agent</th>
                <th>Email template</th>
                <th>Mass Email template</th>
                <th style="width: 18%">Notes</th>
                <th>Follow up call booked</th>
                <th>Appointment booked with</th>
                <th>Call time</th>
			</tr>
		</thead>
		<tbody>
			@if(sizeof($callHistory) > 0)
				@foreach($callHistory as $data)
					<tr>
						<td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data->created_at)->format('d/m/Y H:i:s')}}</td>
						<td>{{$data->agentName}}</td>
						<td>@if($data->emailName != null && $data->emailName != ''){{$data->emailName}}@else{{'-'}}@endif</td>
						<td>@if($data->massEmailName != null && $data->massEmailName != ''){{$data->massEmailName}}@else{{'-'}}@endif</td>
						<td>@if($data->notes != null && $data->notes != ''){{$data->notes}}@else{{'-'}}@endif</td>
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
</div>
