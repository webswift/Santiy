<div class="modal-content">
    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <div id="headerInfo">
            <h4 class="modal-title">Mail Server Settings : {!! $campaign->name !!}</h4>
        </div>
    </div>
    <div class="modal-body">
			@include('site.campaigns.campaignEmailSettings')
    </div>
    <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal" class="close" type="button"> Close </button>
        <button class="btn btn-primary" id="saveCampaignMailSettingsBtn" type="button"> Save </button>
    </div>
</div>

