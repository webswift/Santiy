<div class="table-responsive">
	{{--
	<p id="searchResults" class="pull-left hidden">Showing Search results for given filters <button onclick="getActiveCampaign(true);" class="btn btn-default btn-sm">Reset Results</button></p>
	<div class="form-horizontal">
	<div class="form-group">
		<div class="col-sm-8">
			<a href="{{ URL::route('user.forms.createoredit') }}" title="Manage Lead Forms" class="btn btn-primary btn-sm">Manage Lead Forms</a>
			<a href="{{ URL::route('user.campaigns.create') }}" title="Create a New Campaign" class="btn btn-primary btn-sm">Create a New Campaign</a>
		</div>
		<div class="col-sm-4">
			<button class="btn btn-primary btn-sm pull-right mr5" id="campaignSearch">Search</button>
		</div>
	</div>
	</div>
	--}}
	<ul id="ulLetterList" class="letter-list mb10 hidden">
		<li><a href="">All</a></li>
		<li><a href="">a</a></li>
		<li><a href="">b</a></li>
		<li><a href="">c</a></li>
		<li><a href="">d</a></li>
		<li><a href="">e</a></li>
		<li><a href="">f</a></li>
		<li><a href="">g</a></li>
		<li><a href="">h</a></li>
		<li><a href="">i</a></li>
		<li><a href="">j</a></li>
		<li><a href="">k</a></li>
		<li><a href="">l</a></li>
		<li><a href="">m</a></li>
		<li><a href="">n</a></li>
		<li><a href="">o</a></li>
		<li><a href="">p</a></li>
		<li><a href="">q</a></li>
		<li><a href="">r</a></li>
		<li><a href="">s</a></li>
		<li><a href="">t</a></li>
		<li><a href="">u</a></li>
		<li><a href="">v</a></li>
		<li><a href="">w</a></li>
		<li><a href="">x</a></li>
		<li><a href="">y</a></li>
		<li><a href="">z</a></li>
	</ul>
	<div class="mb30 campaigns-list active-campaigns-list">
		@if(sizeof($userCampaignLists) > 0)
			<div class="row">
			@foreach($userCampaignLists as $userCampaignList)
				<div class="col-md-6">
					@include('site.campaigns.manager.activeCampaign', ['userCampaignList' => $userCampaignList])
				</div>
			@endforeach
			</div>
		@else
			<i>No active campaign found</i>
		@endif
	</div>

	{{--
	<table class="table table-striped mb30">
		<thead>
			<tr>
				<th>Campaign Name</th>
				<th>Started On</th>
				<th>Remaining calls</th>
				@if($user->userType == "Multi")
					<th>Last Active Agent</th>
				@endif
				<th style="min-width:200px">More Options</th>
			</tr>
		</thead>
		<tbody>
		@if(sizeof($userCampaignLists) > 0)
			@foreach($userCampaignLists as $userCampaignList)
				<tr id="campaign_{{ $userCampaignList->id }}">
					<td class="campaignNameCell">
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
					</td>
					<td>@if(!empty($userCampaignList->timeStarted)) {{ \Carbon\Carbon::parse($userCampaignList->timeStarted)->format('d M Y H:i') }} @else - @endif</td>
					@if ($userCampaignList->type == "Fly")
						<td>Fly Campaign</td>
					@else
						<td id="trCallsRemaining-{{ $userCampaignList->id }}">
							{{ $userCampaignList->callsRemaining }}
							@if(count($userCampaignList->prevNextFilter) > 0) (filtered) @endif
						</td>
					@endif
					@if($user->userType == "Multi")
						<td>{!! $userCampaignList->firstName !!} {!! $userCampaignList->lastName !!}</td>
					@endif
					<td>
						<div class="dropdown">
							<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								Options
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								@if($user->userType != "Team" && $userCampaignList->type != "Fly")
								<li>
									<a title="Set Lead distribution ('Next' Lead will be selected based on filter)" href="javascript:void(0);" 
										onclick="editPrevNextLeadFilter({{ $userCampaignList->id }})"><i class="fa fa-filter"></i> Set Lead distribution</a>
								</li>
								@endif
								@if($user->userType == "Multi")
								<li>
									<a title="Edit campaign users" href="javascript:void(0);" onclick="editCampaignUser({{ $userCampaignList->id }})"><i class="fa fa-edit"></i> Add/Remove team users</a>
								</li>
								@endif
								<li>
									<a title="Add/Edit Webforms" href="javascript:void(0);" onclick="viewCampaignInformation({{ $userCampaignList->id }})">
									<i class="fa fa-info-circle"></i> Add/Edit Webforms</a>
								</li>
								<li>
									<a title="Mail Server Settings" href="javascript:void(0);" onclick="viewCampaignMailSettings({{ $userCampaignList->id }})">
									<i class="fa fa-wrench"></i> Mail Server Settings</a>
								</li>
								<li>
									<a title="Import New Leads" href="{{ URL::route('user.campaigns.importcampaignleads', $userCampaignList->id) }}" >
									<i class="fa fa-cloud-download"></i> Import New Leads</a>
								</li>
								<li class="divider"></li>
								<li>
									<a title="Move campaign to archive" href="javascript:void(0);" onclick="archiveCampaign({{ $userCampaignList->id }})"><i class="fa fa-archive"></i> Park Campaign</a>
								</li>
								<li>
									<a title="End campaign" href="javascript:void(0);" onclick="endCampaign({{ $userCampaignList->id }})"><i class="fa fa-power-off"></i> End campaign</a>
								</li>
							</ul>
						</div>
					</td>
				</tr>
			@endforeach
		@else
			<tr><td colspan="@if($user->userType == "Multi"){{"7"}}@else{{"5"}}@endif" class="text-center"><i>No active campaign found</i></td></tr>
		@endif
		</tbody>
	</table>
	--}}
</div>

<div class="row pull-right">
	<div class="col-sm-12">
	 @if(is_object($userCampaignLists))
		{!! str_replace('/?', '?', $userCampaignLists->render()) !!}
	 @endif
	</div>
</div>
<script>
   // Tooltip
   jQuery('.active-campaigns-list .tooltips').tooltip({ container: 'body'});
</script>
