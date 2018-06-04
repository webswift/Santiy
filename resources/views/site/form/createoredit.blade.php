@extends('layouts.dashboard')

@section('css')
	<style>
		#demos section {
			overflow: hidden;
		}
		.sortable {
			width: 310px;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}
		.sortable.grid {
			overflow: hidden;
		}
		.sortable li {
			  list-style: none;
              border: 1px solid #CCC;
              background: #FFF;
              color: #333;
              margin: 5px;
              padding: 5px 10px;
              min-height:32px;
              border-radius: 5px;
            overflow: hidden;
            width: 300px;
		}
		.sortable.grid li {
			line-height: 80px;
			float: left;
			width: 80px;
			height: 80px;
			text-align: center;
		}
		.handle {
			cursor: move;
		}
		.sortable.connected {
			width: 200px;
			min-height: 100px;
			float: left;
		}
		li.disabled {
			opacity: 0.5;
		}
		li.highlight {
			background: #FEE25F;
		}
		li.sortable-placeholder {
			border: 1px dashed #CCC;
			background: none;
		}
		.selectedFields {
			max-width:220px;
			display: inline-block;
		}
		.fieldSmallBtn {
			cursor: pointer;
			}
	</style>
@stop

@section('title')
	Create/Edit Lead Forms
@stop

@section('content')

<div class="pageheader">
	<h2><i class="glyphicon glyphicon-edit"></i> Create/Edit Lead Forms</h2>
</div>

<div class="contentpanel">
	@if($successMessage != '')
		<div class="row">
			<div class="col-sm-12">
				<div class="alert alert-{{ $successMessageClass or 'success' }}">
					<a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
					{!! $successMessage !!}
				</div>
			</div>
		</div>
    @endif

    <div class="row">
    	<div class="col-sm-12"><div id="error1" class="error"></div></div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <p>Create a form to handle prospect criteria, add the fields you would usually ask a prospect in order to qualify them as a lead/customer during a conversation.</p>
        </div>
    </div>

    <div class="panel">
    	<div class="panel-body">
    		<div class="form-horizontal">
    			<div class="form-group">
    				<div class="col-sm-2">
    					<label class="control-label pull-right">New Form Name:</label>
    				</div>
    				<div class="col-sm-4">
    					<input type="text" id="newFormName" class="form-control"/>
    				</div>
    				<div class="col-sm-1" style="text-align: center">
    					<label class="control-label"><b>OR</b></label>
    				</div>
    				<div class="col-sm-3">
    					<select class="form-control" id="editFormName">
    						<option value="">Edit Existing Form</option>
    						@if(sizeof($formLists) > 0)
    						@foreach($formLists as $formList)
    							<option value="{{ $formList->id }}">{{ $formList->formName }}</option>
                        	@endforeach
                        	@endif
                        </select>
					</div>
                    <div class="col-sm-2">
                        <button class="btn btn-danger" id="deleteButton" style="display:none">Delete Form</button>
                    </div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">Default Fields</h4>
					<label class="control-label"> 
						Use the default options below to start constructing the layout of your environment. 
						Click the Add New Field button to add custom text fields or drop down options that are relevant to your business.
					</label>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-6">
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" id="companyName" value="Company Name" data-type="text" />
								<label for="companyName">Company Name</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="First Name" id="firstName" data-type="text" />
								<label for="firstName">First Name</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Last Name" id="lastName" data-type="text" />
								<label for="lastName">Last Name</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Position" id="position" data-type="text" />
								<label for="position">Position</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Email" id="email" data-type="text" />
								<label for="email">Email</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Address" id="address" data-type="text" />
								<label for="address">Address/Post/Zip code</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Telephone No" id="telephone" data-type="text" />
								<label for="telephone">Telephone No</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Mobile No" id="mobile" data-type="text" />
								<label for="mobile">Mobile No</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Notes" id="notes" data-type="text" />
								<label for="notes">Notes</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Website" id="website" data-type="text" />
								<label for="website">Website</label>
							</div>
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-sm-12">
							<button onclick="addNewField()" class="btn btn-sm btn-primary">Add New Field</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">Form Fields</h4>
					<label class="control-label"> You can drag and drop the positions of the fields and menus on your form to suit preference <br />&nbsp; </label>
				</div>
				<div class="panel-body">
					<div class="row">
						<ul class="sortable"></ul>
					</div>
					<div class="row hidden">
						<div class="panel">
							<div class="panel-body">
								<h5>Unsaved changes :</h5>
								<div id="divChangesLog"></div>
							</div>
						</div>
					</div>
				</div>

				<div class="panel-footer">
					<div class="col-sm-12">
						<button onclick="addNewField()" class="btn btn-sm btn-primary">Add New Field</button>
						<button onclick="showDemoForm()" class="btn btn-sm btn-warning">Preview (Open in new window)</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="panel">
		<div class="panel-body">
			<div class="col-sm-12">
				<p class="pull-right">
					<button id="addOrEditButton" onclick="addThisForm()" type="button" class="btn btn-primary">Save Form</button>
				</p>
			</div>
		</div>
	</div>

</div>
@stop


@section('bootstrapModel')
<div id="addNewField" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo"><h4 class="modal-title">Add New Field</h4></div>
	        </div>
	        <div class="modal-body mb10">
	        	<p>Would you like to add a text field or a drop menu?</p>
	        	<div class="row-fluid mb10">
					<div class="rdio rdio-default col-sm-4">
						<input type="radio" id="forDropMenu" value="forDropMenu" name="fieldType" />
						<label for="forDropMenu">Drop Menu</label>
					</div>
					<div class="rdio rdio-default col-sm-4">
						<input type="radio" id="forField" value="forField" name="fieldType" />
						<label for="forField">Field</label>
					</div>
					<div class="rdio rdio-default col-sm-4">
						<input type="radio" id="forDate" value="forDate" name="fieldType" />
						<label for="forDate">Field for Date</label>
					</div>
	        	</div>
	        	<div id="error"></div>
	        	<div class="row hidden" id="fieldDiv">
	        		<div class="col-sm-12">
	        			<div class="form-group">
	        				<input id="newFieldName" type="text" name="fieldName" class="form-control input-sm" placeholder="Enter field name"/>
	        			</div>
	        		</div>
	        	</div>
	        	<div class="row hidden" id="dropMenudDiv" style="clear: both;width: 100%;">
					<div class="col-sm-12">
						<div class="form-group">
							<input id="newFieldName" type="text" name="fieldName" class="form-control input-sm" placeholder="Enter field name"/>
						</div>
						<div id="optionDiv">
							<div class="form-group">
								<input type="text" class="form-control input-sm  options" name="options[]" placeholder="Option 1"/>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<button title="Add new option to drop-down item"  class="btn btn-default pull-right" onclick="addNewOptions();"><i class="fa fa-plus"></i></button>
					</div>
				</div>
	        </div>
	        <div class="panel-footer">
	        	<div class="row">
                    <div class="col-sm-4">
                        <button id="addThisNewField" class="btn btn-xs btn-success">Add Field</button>
                    </div>
                    <div class="col-md-8">
                        <div class="ckbox ckbox-default">
                            <input type="checkbox" value="Yes" id="requiredInput">
                            <label for="requiredInput">Make this required</label>
                        </div>
                    </div>
	        	</div>
	        </div>
		</div>
	</div>
</div>

<div id="dlgRenameField" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo"><h4 class="modal-title">Rename Field</h4></div>
	        </div>
	        <div class="modal-body mb10">
				<p>Original name of field : <span id="spnOriginalFieldName"></span></p>
	        	<div class="row" id="fieldDiv">
	        		<div class="col-sm-12">
	        			<div class="form-group">
	        				<input id="renamedFieldName" type="text" name="renamedFieldName" class="form-control input-sm" placeholder="Enter field new name"/>
	        			</div>
	        		</div>
	        	</div>
	        </div>
	        <div class="panel-footer">
	        	<div class="row">
                    <div class="col-sm-8">
                        <button id="btnSaveRenamedFieldName" class="btn btn-xs btn-primary">Rename Field</button>
                        <button id="btnResetRenamedFieldName" class="btn btn-xs btn-success">Restore original name</button>
                    </div>
	        	</div>
	        </div>
		</div>
	</div>
</div>
<div id="dlgDropDownEditor" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
				<div id="headerInfo"><h4 class="modal-title">Rename/Edit Drop Menu Field</h4></div>
	        </div>
	        <div class="modal-body mb10">
				<p>Original name of field : <span id="spnOriginalFieldName"></span></p>
	        	<div class="row" id="fieldDiv">
	        		<div class="col-sm-8">
	        			<div class="form-group">
	        				<input id="renamedFieldName" type="text" name="renamedFieldName" class="form-control input-sm" placeholder="Enter field new name"/>
	        			</div>
	        		</div>
                    <div class="col-sm-4">
	        			<div class="form-group">
							<button id="btnResetRenamedFieldName" class="btn btn-xs btn-success input-sm">Restore original name</button>
	        			</div>
                    </div>
	        	</div>
				<p>Add/change options : </p>
	        	<div class="row" id="fieldDiv">
	        		<div class="col-sm-12">
						<div class="divDropDownOptions">
							<div class="form-group">
								<input type="text" class="form-control input-sm  options" name="options[]" placeholder="Option 1"/>
							</div>
						</div>
	        		</div>
					<div class="col-sm-12">
						<button id="btnAddNewOptionToExistsDropDown" title="Add new option to drop-down item" class="btn btn-default pull-right"><i class="fa fa-plus"></i></button>
					</div>
				</div>
	        </div>
	        <div class="panel-footer">
	        	<div class="row">
                    <div class="col-sm-8">
                        <button id="btnSaveUpdatedDropDown" class="btn btn-sm btn-primary">Save</button>
                    </div>
	        	</div>
	        </div>
		</div>
	</div>
</div>

@if($user->show_lead_forms_info_dlg == 'Yes')
	<div id="dlgPageInfo" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
					<div id="headerInfo"><h4 class="modal-title">Here’s how this works</h4></div>
				</div>
				<div class="modal-body mb10">
					<p>
					Build your own CRM layout.
					</p>
					<p>
					Use the default fields in the box on the left and then add your own fields and options by clicking Add new Field. 
					</p>
					<p>
					Once complete give your form a name, Click save and begin importing your database of contacts/leads by creating a new campaign.
					</p>
				</div>
				<div class="panel-footer">
					<div class="row">
						<div class="col-sm-8">
							<div class="ckbox ckbox-default form-control-mt7">
								<input type="checkbox" id="chkDontShowAgain">
								<label for="chkDontShowAgain"> Don’t show again </label>
							</div>
						</div>
						<div class="col-sm-4">
							<button class="btn btn-primary pull-right" data-dismiss="modal" class="close" type="button"> Got it </button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endif

@stop

@section('modelJavascript')
{!! Html::script('assets/js/html5sortable/jquery.sortable.js') !!}
@stop

@section('javascript')
{!! HTML::script('assets/js/custom.js') !!}
{!! HTML::script('assets/js/jquery.slimscroll.js') !!}
{!! HTML::script('assets/js/bootbox.min.js') !!}

<script type="text/javascript">
    var formDetailsUrl = '{{ URL::route('user.forms.formfieldsdetails') }}';
    var formAddEditUrl = '{{ URL::route('user.forms.addoreditform') }}';
    var formDeleteUrl = '{{ URL::route('user.forms.deleteform') }}';
    var formShowDemoUrl = '{{ URL::route('user.forms.showdemoform') }}';

	@if($user->show_lead_forms_info_dlg == 'Yes')
	var fnInfoDlg = function () {
		var dlg = $('#dlgPageInfo');
		dlg.modal('show');
		dlg.on('hidden.bs.modal', function() {
			dlg.off('hidden.bs.modal');
			var dontShowAgain = dlg.find('#chkDontShowAgain:checked').length !== 0;
			if(dontShowAgain) {
				$.post('{{ URL::route('user.forms.lead.dontshowinfodlg') }}')
					.fail(function() {
						showError('Something went wrong. Please try again later!');
					})
				;
			}
			dlg.remove();
		});
	}();
	@endif
</script>
{!! HTML::script('assets/js/generated/lead-form-builder.js') !!}

@stop
