<div class="row">
	<div class="col-md-12">
		<div class="divMoveError"></div>
	</div>
</div>
<div class="row hidden">
	<label for="selDuplicates" class="control-label col-sm-4">Duplicate rows : </label>
	<div class="col-sm-5">
	<select id="selDuplicates" class="form-control mb5">
		<option value="skip">Skip</option>
		<option value="update">Update</option>
	</select>
	</div>
</div>
<div class="row">
	<label for="selMove" class="control-label col-sm-4">Transfer : </label>
	<div class="col-sm-5">
	<select id="selMove" class="form-control mb5">
		<option value="move">Move Leads</option>
		<option value="copy">Copy Leads</option>
	</select>
	</div>
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
			<th class="hidden"></th>
			<th>Source Campaign Fields</th>
			<th>Target Campaign Fields</th>
		</tr>
		</thead>
		<tbody>
		@forelse($sourceFields as $sourceField)
			<tr class="fieldDiv">
				<td class="hidden">
					<div title="Is part of unique key" class="ckbox ckbox-default form-control-mt7">
						<input title="Is part of unique key" type="checkbox" name="uniqueSourceField" id="uniqueSourceFieldId_{{ $sourceField->id }}">
						<label class="" for="uniqueSourceFieldId_{{ $sourceField->id }}"></label>
					</div>
				</td>
				<td>
					
					<label class="control-label sourceFieldName">{{ $sourceField->fieldName }}</label>
					<input name="sourceFieldID" type="hidden" value="{{ $sourceField->id }}">
				</td>
				<td>
					<select class="form-control targetFormFields" name="targetFieldID">
						<option value="">Add to Notes</option>
						@forelse($targetFields as $targetField)
							<option class="fields" value="{{ $targetField->id }}"
									@if($sourceField->fieldName == $targetField->fieldName) 
									selected 
									@endif
								>{{ $targetField->fieldName }}</option>
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
<div class="row">
	<div class="col-md-12">
		<div title="Include Historical notes & appointments" class="ckbox ckbox-default">
			<input title="Include Historical notes & appointments" type="checkbox" name="chkIncludeNotes" id="chkIncludeNotes">
			<label class="" for="chkIncludeNotes">Include Historical notes &amp; appointments</label>
		</div>
	</div>
</div>

