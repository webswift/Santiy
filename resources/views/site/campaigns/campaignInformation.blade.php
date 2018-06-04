<div class="modal-content">
    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <div id="headerInfo">
            <h4 class="modal-title">{!! $campaign->name !!}</h4>
        </div>
    </div>
    <div class="modal-body">
		<input type="hidden" id="leadFormID" name="leadFormID" value="{{ $leadFormId }}">
        @if($error == false)
		<input type="hidden" id="landingFormID" name="landingFormID" value="{{ $landingFormID }}">
        <div class="row">
            <div class="col-md-9">
                <p><strong>Landing Form: </strong> <a href="{{ URL::route('landing.signup', [$campaign->slug, $landingForm->slug]) }}">{{ URL::route('landing.signup', [$campaign->slug, $landingForm->slug]) }}</a></p>
            </div>
            <div class="col-md-3">
                <button class="btn btn-success btn-block" id="embedBtn">Embed Code</button>
            </div>
        </div>
        <div class="row hidden" id="embedCodeDiv">
            <div class="col-md-12">
                <textarea class="form-control ma5" readonly><iframe src="{{ URL::route('landing.signup', [$campaign->slug, $landingForm->slug]) }}" height="100" width="100"></iframe></textarea>
            </div>
        </div>
        <div class="row mt5">
            <div class="col-md-offset-9 col-md-3">
                <a class="btn btn-success btn-block" id="embedHtmlBtn" target="_blank" href="{{ URL::route('landing.api.html', [$campaign->slug, $landingForm->slug]) }}">API</a>
            </div>
        </div>
        @else
        <div class="row">
			<div class="" id="landingFormDiv">
				<p class="text-danger ml10">This campaign does not have landing form.</p>
				<div class="col-md-7">
					<select id="landingFormID" name="landingFormID" class="form-control input-sm mb15 pull-left">
						<option value="">Associate a signup page/Landing form</option>
						@if(sizeof($landingForms) > 0)
							@foreach($landingForms as $landingForm)
								<option value="{{ $landingForm->id }}">{{ $landingForm->name }}</option>
							@endforeach
						@endif
					</select>
				</div>
				<button id="createMapping" disabled type="button" class="pull-left btn btn-info hidden" data-toggle="tooltip" data-placement="left" 
					title="Remap fields of landing and lead forms">MAP Form</button>
			</div>
        </div>
        @endif
    </div>
    <div class="modal-footer">
        @if($error == false)
        <button title="Remap fields of landing and lead forms" class="btn btn-primary pull-left" id="createMapping" type="button"> Remap </button>
        <button title="Detach landing form from the campaign" class="btn btn-primary pull-left" id="deleteMapping" type="button"> Delete </button>
        @endif
		<a href="{{ URL::route('user.forms.landing.createoredit') }}" title="Manage Web Forms" class="btn btn-primary pull-left">Manage Web Forms</a>
        <button class="btn btn-default" data-dismiss="modal" class="close" type="button"> Close </button>
        @if($error)
        <button class="btn btn-primary hidden" disabled id="assignLandingForm2CampaignBtn" type="button"> Save </button>
        @endif
    </div>
</div>
