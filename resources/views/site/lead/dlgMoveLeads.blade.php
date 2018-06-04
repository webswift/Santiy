<div id="moveLeadsModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo">
					<h4 class="modal-title">Transfer Data</h4>
				</div>
			</div>
			<div class="modal-body form-horizontal">
				<div class="row">
					<div class="form-group" id="divTargetCampaignId">
						<label class="col-sm-4 control-label">Move Record(s) to: </label>
						<div class="col-md-7">
							<select id="selMoveLeadsTargetCampaign" name="selMoveLeadsTargetCampaign" class="form-control">
								<option value="">Select a Campaign</option>
								@if(sizeof($targetCampaigns) > 0)
									@foreach($targetCampaigns as $targetCampaign)
										<option value="{{ $targetCampaign->id }}">{{ $targetCampaign->name }}</option>
									@endforeach
								@endif
							</select>
						</div>
					</div>
				</div>
				<div class="divFieldMapping row">
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal" class="close" type="button"> Close </button>
				<button class="btn btn-primary hidden" disabled id="btnMoveLeads" type="button"> Transfer </button>
			</div>
		</div>
    </div>
</div>

