<div class="people-item" id="archivedCampaign_{{ $userCampaignList->id }}">
	<div class="media">
		<div  class="col-md-2">
			<img alt="" src="{{ URL::asset("assets/images/ngletter") }}/{{substr(strtolower($userCampaignList->name), 0, 1)}}.svg" class="thumbnail media-object">
		</div>
		<div class="media-body col-md-7">
			<h4 class="person-name">{{ $userCampaignList->name }}</h4>

			<div class="text-muted"><i class="fa fa-rocket"></i> Created On: 
				@if(!empty($userCampaignList->timeStarted))  
					{{ \Carbon\Carbon::parse($userCampaignList->timeStarted)->format('m/d/Y') }}
				@endif
			</div>
			
			<div class="text-muted"><i class="fa fa-rocket"></i> Completed On: 
				@if(!empty($userCampaignList->completedOn))  
					{{ \Carbon\Carbon::parse($userCampaignList->completedOn)->format('m/d/Y') }}
				@endif
			</div>

			<div class="text-muted"><i class="fa fa-caret-right"></i>
			@if ($userCampaignList->type == "Fly")
				&nbsp;&nbsp;Fly Campaign
			@else
				&nbsp;&nbsp;Total Contacts:
				<span id="trTotalLeads-{{ $userCampaignList->id }}">
					@if(count($userCampaignList->prevNextFilter) > 0)
					{{ $userCampaignList->totalFilteredLeads }} (filtered) 
					@else
					{{ $userCampaignList->totalLeads }}
					@endif
				</span>
			@endif
			</div>
		</div>
		<div  class="col-md-3">
			<a href="javascript:;" rel="{{$userCampaignList->id}}" class="removeArchive btn btn-warning mt30">
				Re-Activate Campaign
			</a>
		</div>
	</div>
</div>


