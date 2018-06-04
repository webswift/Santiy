<div class="table-responsive">
	<div class="mb30 campaigns-list archive-campaigns-list">
		@if(sizeof($archivedCampaignLists) > 0)
			<div class="row">
			@foreach($archivedCampaignLists as $archiveCampaignList)
				<div class="col-md-6">
					@include('site.campaigns.manager.archivedCampaign', ['userCampaignList' => $archiveCampaignList])
				</div>
			@endforeach
			</div>
		@else
			<i>No archived campaign found</i>
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
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			@if(sizeof($archivedCampaignLists) > 0)
				@foreach($archivedCampaignLists as $archiveCampaignList)
					<tr id="archivedCampaign_{{$archiveCampaignList->id}}">
						<td>{!! $archiveCampaignList->name !!}</td>
						<td>@if($archiveCampaignList->timeStarted == null){{ '-'}}@else{{\App\Http\Controllers\CommonController::formatDateForDisplay($archiveCampaignList->timeStarted)}}@endif</td>
						<td>@if($archiveCampaignList->completedOn == null){{ '-'}}@else{{\App\Http\Controllers\CommonController::formatDateForDisplay($archiveCampaignList->completedOn)}}@endif</td>
						@if ($archiveCampaignList->type == "Fly")
							<td>Fly Campaign</td>
						@else
							<td>{{ $archiveCampaignList->callsRemaining }}</td>
						@endif
						<td><a href="javascript:;" rel="{{$archiveCampaignList->id}}" class="removeArchive">Restore as Active Campaign</a></td>
					</tr>
				@endforeach
			@else
				<tr><td colspan="5" class="text-center"><i>No archived campaign found</i></td></tr>
			@endif
		</tbody>
	</table>
	--}}
</div>

<div class="row pull-right">
	<div class="col-sm-12">
	 @if(is_object($archivedCampaignLists))
		{!!  str_replace('/?', '?', $archivedCampaignLists->render()) !!}
	 @endif
	</div>
</div>
<script>
   // Tooltip
   jQuery('.archive-campaigns-list .tooltips').tooltip({ container: 'body'});
</script>
