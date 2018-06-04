<div class="people-item" id="campaign_{{ $userCampaignList->id }}">
	<div class="media">
		<a class="col-md-2" href="start/{{ $userCampaignList->name }}" 
				@if($userCampaignList->status === "Stopped")
						onclick="startCampaign( {{ $userCampaignList->id }}, '{{  addslashes($userCampaignList->name) }}'); return false;" 
				@else
						onclick="generateLeadForCampaign({{ $userCampaignList->id }}); return false;" 
				@endif
			class="pull-left">
			<img alt="" src="{{ URL::asset("assets/images/ngletter") }}/{{substr(strtolower($userCampaignList->name), 0, 1)}}.svg" class="thumbnail media-object">
		</a>
		<div class="media-body col-md-9">
			<span class="person-name campaignNameCell">
				@if($userCampaignList->status === "Stopped")
					<a id="startCampaignAnhor-{{ $userCampaignList->id }}" href="start/{{ $userCampaignList->name }}" 
						onclick="startCampaign( {{ $userCampaignList->id }}, '{{  addslashes($userCampaignList->name) }}'); return false;">{{ $userCampaignList->name }}</a>
				@else
					<a id="startCampaignAnhor-{{ $userCampaignList->id }}" href="start/{{ $userCampaignList->name }}" 
						onclick="generateLeadForCampaign({{ $userCampaignList->id }}); return false;">{{ $userCampaignList->name }}</a>
				@endif
				<a id="editCampaignAnhor-{{ $userCampaignList->id }}"
					class="hidden editable editable-click"
				>{{ $userCampaignList->name }}</a>

				@if($user->userType != "Team")
				<div style="display:inline-block; width:20px" class="ml10">
				<a id="editCampaignNameBtn-{{ $userCampaignList->id }}"
					class="hidden editCampaignNameBtn"
					title="Edit name" href="javascript:void(0)" onclick="editCampaignName({{ $userCampaignList->id }})"><i class="fa fa-pencil"></i></a>
				</div>
				@endif
			</span>

			<div class="text-muted"><i class="fa fa-rocket"></i> Created On: 
				@if(!empty($userCampaignList->timeStarted))  
					{{ \Carbon\Carbon::parse($userCampaignList->timeStarted)->format('m/d/Y') }}
				@endif
			</div>
			
			<div class="text-muted"><i class="fa fa-caret-right"></i>
			@if ($userCampaignList->type == "Fly")
				&nbsp;&nbsp;Fly Campaign
			@else
				&nbsp;&nbsp;Total Contacts:
				<span id="trTotalLeads-{{ $userCampaignList->id }}">
					@if($userCampaignList->userPrevNextFilter != '' && count(json_decode($userCampaignList->userPrevNextFilter)) > 0)
					{{ $userCampaignList->userTotalFilteredLeads }} (filtered for user) 
					@elseif(count($userCampaignList->prevNextFilter) > 0)
					{{ $userCampaignList->totalFilteredLeads }} (filtered for all) 
					@else
					{{ $userCampaignList->totalLeads }}
					@endif
				</span>
			@endif
			</div>
			
			@if($user->userType == "Multi" )
				<div class="text-muted last-active-user"><i class="fa fa-briefcase"></i>
					Last Active User:
					@if($userCampaignList->lastName != '' || $userCampaignList->firstName != '')
						{!! $userCampaignList->firstName !!} {!! $userCampaignList->lastName !!}
					@endif
				</div>
			@endif

			<ul class="social-list">
				@if($userCampaignList->type != "Fly")
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Set Lead distribution ('Next' Lead will be selected based on filter)"
						href="javascript:void(0);" 
						onclick="editPrevNextLeadFilter({{ $userCampaignList->id }})"><i class="fa fa-filter"></i></a>
				</li>
				@endif
				@if($user->userType == "Multi")
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add/Remove team users" 
					href="javascript:void(0);" onclick="editCampaignUser({{ $userCampaignList->id }})"><i class="fa fa-edit"></i></a>
				</li>
				@endif
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Add/Edit Webforms" 
						href="javascript:void(0);" onclick="viewCampaignInformation({{ $userCampaignList->id }})">
					<i class="fa fa-info-circle"></i></a>
				</li>
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Mail Server Settings" 
						href="javascript:void(0);" onclick="viewCampaignMailSettings({{ $userCampaignList->id }})">
					<i class="fa fa-wrench"></i></a>
				</li>
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Import New Leads" 
						href="{{ URL::route('user.campaigns.importcampaignleads', $userCampaignList->id) }}" >
					<i class="fa fa-cloud-download"></i></a>
				</li>
				<li class="divider"></li>
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Move campaign to archive" 
					href="javascript:void(0);" onclick="archiveCampaign({{ $userCampaignList->id }})"><i class="fa fa-archive"></i></a>
				</li>
				<li>
					<a class="tooltips" data-toggle="tooltip" data-placement="top" title="" data-original-title="End campaign" 
					href="javascript:void(0);" onclick="endCampaign({{ $userCampaignList->id }})"><i class="fa fa-power-off"></i></a>
				</li>
			</ul>
			<a class="btn btn-success mt10" href="start/{{ $userCampaignList->name }}" aria-haspopup="true" aria-expanded="false"
				@if($userCampaignList->status === "Stopped")
						onclick="startCampaign( {{ $userCampaignList->id }}, '{{  addslashes($userCampaignList->name) }}'); return false;" 
				@else
						onclick="generateLeadForCampaign({{ $userCampaignList->id }}); return false;" 
				@endif
				>
				Start Campaign
			</a>
		</div>
	</div>
</div>

