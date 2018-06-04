<div class="table-responsive">
	<div class="mb30 campaigns-list archive-campaigns-list">
		@if(sizeof($recentCampaignLists) > 0)
			<div class="row">
			@foreach($recentCampaignLists as $recentCampaignList)
				<div class="col-md-6">
					@include('site.campaigns.manager.finishedCampaign', ['userCampaignList' => $recentCampaignList])
				</div>
			@endforeach
			</div>
		@else
			<i>No finished campaign found</i>
		@endif
	</div>
	{{--
	<table id="table2" class="table table-striped mb30">
		<thead>
			<tr>
				<th>Campaign Name</th>
				<th>Started On</th>
				<th>Completed On</th>
				<th>Total Leads</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@if(sizeof($recentCampaignLists) > 0)
				@foreach($recentCampaignLists as $recentCampaignList)
					<tr>
						<td>{{ $recentCampaignList->name }}</td>
						<td>@if($recentCampaignList->timeStarted == null){{ '-'}}@else{{\App\Http\Controllers\CommonController::formatDateForDisplay($recentCampaignList->timeStarted)}}@endif</td>
						<td>{{ \App\Http\Controllers\CommonController::formatDateForDisplay($recentCampaignList->completedOn)}}</td>
						@if ($recentCampaignList->totalLeads == 0)
							<td>Fly Campaign</td>
						@else
							<td>{{ $recentCampaignList->totalLeads}}</td>
						@endif
						<td>
							<button onclick="deleteCampaignData({{ $recentCampaignList->id}})" class="btn btn-xs btn-danger" id="deleteCampaignData" >Delete data set</button>
						</td>
					</tr>
				@endforeach
			@else
				<tr><td colspan="4" class="text-center"><i>No finished campaign found</i></td></tr>
			@endif
		</tbody>
	</table>
	--}}
</div>

<div class="row pull-right">
	<div class="col-sm-12">
	 @if(is_object($recentCampaignLists))
		{!! str_replace('/?', '?', $recentCampaignLists->render()) !!}
	 @endif
	</div>
</div>
