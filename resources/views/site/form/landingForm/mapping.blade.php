<div id="mappingModal" class="modal fade bs-example-modal-static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
{{-- body start --}}


    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
        <div id="headerInfo">
            <h4 class="modal-title">{{ $landingForm->name }} Mapping</h4>
        </div>
    </div>
    <div class="modal-body">
		<input type="hidden" name="landingFormID" value="{{ $landingForm->id }}">
        <div class="row">
            <div class="form-group">
				@if (isset($singleLeadForm))
                <label class="col-sm-4 control-label">Selected Lead Form</label>
                <div class="col-sm-7">
					<input type="hidden" class="leadForm" name="leadFormID" value="{{ $singleLeadForm->id }}">
					<label class="control-label"><b>{{ $singleLeadForm->formName }}</b></label>
                </div>
				@else
                <label class="col-sm-4 control-label">Selected Lead Form</label>
                <div class="col-sm-7">
                    <select class="form-control mb15 leadForm" name="leadFormID">
                        <option value="">Select form</option>
                        @forelse($forms as $form)
                            <option value="{{ $form->id }}"> {{ $form->formName }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
				@endif
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-warning"> <i class="fa fa-info-circle"></i> Any unmapped fields will be copied to Notes.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb30">
                    <thead>
                    <tr>
                        <th>Landing Form Fields</th>
                        <th>Lead Form Fields</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($landingFormFields as $landingFormField)
                        <tr class="fieldDiv">
                            <td>
                                {{ $landingFormField->fieldName }}
                                <input name="landingFieldID" type="hidden" value="{{ $landingFormField->id }}">
                            </td>
                            <td>
                                <select class="form-control mb15 leadFormFields" name="leadFormFieldID">
                                    <option value="">Select</option>
                                    @forelse($forms as $form)
                                        @forelse($form['fields'] as $fields)
											<option rel="{{ $form->id }}_option" 
												class="fields 
													@if (!isset($singleLeadForm))
													hidden 
													@endif
													@if(in_array($form->id, $landingFormField['forms']) && in_array($fields->id, $landingFormField['fields'])) 
													selected 
													@endif
												" value="{{ $fields->id }}"
													@if((isset($singleLeadForm)) && in_array($form->id, $landingFormField['forms']) && in_array($fields->id, $landingFormField['fields'])) 
													selected 
													@endif
												>{{ $fields->fieldName }}</option>
                                        @empty
                                        @endforelse
                                    @empty
                                    @endforelse
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Oops! no field added.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal" class="close" type="button"> Close </button>
        <button class="btn btn-primary" id="mappingBtn" type="button"> Save </button>
    </div>


{{-- body end --}}

		</div>
    </div>
</div>

