                <div class="form-group">
                    <label class="col-sm-3 control-label">Select campaign</label>
                    <div class="col-sm-6">
						<select class="form-control" id="selActiveCampaigns">
							<option value="">Please select a campaign</option>
							@if(sizeof($campaignLists) > 0)
							@foreach($campaignLists as $campaignList)
								<option value="{{ $campaignList->id }}">{{ $campaignList->name }}</option>
							@endforeach
							@endif
						</select>
                    </div>
                </div>

