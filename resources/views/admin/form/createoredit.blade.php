@extends('layouts.admindashboard')

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
		  height: 32px;
		  border-radius: 5px;
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
</style>
@stop

@section('title')
	Create/Edit Forms
@stop

@section('content')

<div class="pageheader">
	<h2><i class="glyphicon glyphicon-edit"></i> Create/Edit forms</h2>
</div>

<div class="contentpanel">
	@if($successMessage != '')
		<div class="row">
			<div class="col-sm-12">
				<div class="alert alert-{{ $successMessageClass or 'success'}}">
					<a class="close" data-dismiss="alert" href="#" aria-hidden="true"><i class="fa fa-times"></i></a>
					{{ $successMessage }}
				</div>
			</div>
		</div>
    @endif

    <div class="row">
    	<div class="col-sm-12">
    	<div id="error1"></div>
    	</div>
    </div>
	<div class="panel">
		<div class="panel-body">
			<div class="form-horizontal">
				<div class="form-group">
					<div class="col-sm-2">
						<label class="control-label">Form Name: </label>
					</div>
					<div class="col-sm-4">
						<input type="text" id="newFormName" class="form-control">
					</div>
					<div class="col-sm-4">
						<select class="form-control mb15" id="editFormName">
							<option value="">Edit Existing Form</option>
                      		@foreach($formLists as $formList)
                        		<option value="{{ $formList->id }}">{{ $formList->formName }}</option>
                        	@endforeach
                        </select>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading"><h4 class="panel-title">Form Builder</h4></div>
				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-6">
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" id="companyName" value="Company Name" />
								<label for="companyName">Company Name</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="First Name" id="firstName" />
								<label for="firstName">First Name</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Last Name" id="lastName" />
								<label for="lastName">Last Name</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Position" id="position" />
								<label for="position">Position</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Email" id="email" />
								<label for="email">Email</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Address" id="address" />
								<label for="address">Address/Post/Zip code</label>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Telephone No" id="telephone" />
								<label for="telephone">Telephone No</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Mobile No" id="mobile" />
								<label for="mobile">Mobile No</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Notes" id="notes" />
								<label for="notes">Notes</label>
							</div>
							<div class="ckbox ckbox-primary">
								<input type="checkbox" class="predefindedFields" value="Website" id="website" />
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
				</div>
				<div class="panel-body">
					<div class="row">
						<ul class="sortable"></ul>
					</div>
				</div>
				<div class="panel-footer">
					<div class="col-sm-12">
						<button onclick="addNewField()" class="btn btn-sm btn-primary">Add New Field</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="panel">
		<div class="panel-body">
			<div class="form-group">
				<div class="col-sm-4">
					<button onclick="showDemoForm()" class="btn btn-xs btn-warning">Preview (Open in new window)</button>
				</div>
				<div class="col-sm-8">
					<label class="control-label"> You can drag and drop the positions of the fields and menus on your form to suit preference </label>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12">
			<p class="pull-right">
				<button id="deleteButton" type="button" class="btn btn-primary" style="display:none">Delete Form</button>&nbsp;
				<button id="addOrEditButton" onclick="addThisForm()" type="button" class="btn btn-primary">Save</button>
			</p>
		</div>
	</div>
</div>
@stop


@section('bootstrapModel')
<div id="addNewField" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
	    	<div class="modal-header">
	        	<button id="bookedModelCancle" aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	            <div id="headerInfo">
		            <h4 class="modal-title">Add New Field</h4>
	        	</div>
	        </div>
	        <div class="modal-body">
	        	<div id="error" ></div>
	        	<div class="row">
	                <div class="col-sm-12">
	                  <div class="form-group">
	                    <label class="control-label">Field Name </label>
	                    <input id="newFieldName" type="text" class="form-control" />
	                  </div>
	                </div>
	            </div>
			</div>
			<div class="panel-footer">
				 <div class="row">
					<div class="col-sm-12">
					  <button id="addThisNewField" class="btn btn-xs btn-success">Add Field</button>
					</div>
				 </div>
			</div>
		</div>
	</div>
</div>
@stop

@section('javascript')
{!!  Html::script('assets/js/html5sortable/jquery.sortable.js') !!}

<script type="text/javascript">

$(function () {
    $('.sortable').sortable();

	$('body').on('click', '#addThisNewField', function () {
		var fieldValue = $('#newFieldName').val();
		if(fieldValue == '') {
			$('#error').html('<div class="alert alert-danger">Please Enter Field Name.</div>');
		}
		else {
			$('.sortable').append('<li><span class="selectedFields" >'+fieldValue+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
			$('.sortable').sortable();

			$('#newFieldName').val('');
			$('#addNewField').modal('toggle');
		}
	});

	$('body').on('click', '.predefindedFields', function () {
        var value = $(this).val();
        var id = $(this).attr('id');

        if($(this).is(':checked')) {
            if(value == 'Address') {
                $('.sortable').append('<li class="'+id+'"><span class="selectedFields" >Post/Zip code</span> </li>');
            }

            $('.sortable').append('<li class="'+id+'"><span class="selectedFields" >'+value+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
            $('.sortable').sortable();
        }
        else {
            $('.'+id).remove();
        }
        $('.sortable').sortable();
    });

    $('body').on('click', '#editFormName', function () {
        var editFormID = $(this).val();

        $('#companyName').prop('checked', false);
        $('#telephone').prop('checked', false);
        $('#firstName').prop('checked', false);
        $('#lastName').prop('checked', false);
        $('#address').prop('checked', false);
        $('#mobile').prop('checked', false);
        $('#position').prop('checked', false);
        $('#notes').prop('checked', false);
        $('#website').prop('checked', false);

        $('.sortable').html('');
        $('#error1').html('');

        if(editFormID == '') {
            $('#newFormName').val('');
            $('#newFormName').removeAttr('disabled');

            $('#deleteButton').hide();
            $('#addOrEditButton').attr('onclick', 'addThisForm()');
        }
        else {
            $('#newFormName').val('');
            $('#newFormName').attr('disabled', 'disabled');

            $.ajax({
                type: 'post',
                url: "{{ URL::route('admin.forms.formfieldsdetails') }}",
                cache: false,
                data: {"formID": editFormID},
                beforeSend : function () {
                    blockUI('.contentpanel');
                },
                success: function(response) {
                    unblockUI('.contentpanel');
                    var obj = jQuery.parseJSON(response);

                    if(obj.success == "success") {
                        $('#deleteButton').show();
                        $('#deleteButton').attr('onclick', 'deleteThisForm('+editFormID+')');
                        $('#addOrEditButton').attr('onclick', 'editThisForm('+editFormID+')');

                        var ajaxFieldArray = new Array();
                        ajaxFieldArray = obj.formFields;

                        ajaxFieldArray.forEach(function(fields) {
                            var currentFieldName = fields.fieldName;

                            if(currentFieldName == 'Company Name') {
                                var fieldID = "companyName";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Telephone No') {
                                var fieldID = "telephone";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'First Name') {
                                var fieldID = "firstName";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Last Name') {
                                var fieldID = "lastName";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Mobile No') {
                                var fieldID = "mobile";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Position') {
                                var fieldID = "position";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Notes') {
                                var fieldID = "notes";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Address') {
                                var fieldID = "address";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >Post/Zip code</span> </li>');
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Post/Zip code') {
                                //Nothing to do
                            }
                            else if(currentFieldName == 'Website') {
                                var fieldID = "website";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else if(currentFieldName == 'Email') {
                                var fieldID = "email";
                                $('#'+fieldID).prop('checked', true);
                                $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                            else {
                                $('.sortable').append('<li><span class="selectedFields" >'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span></li>');
                            }
                        });
                        $('.sortable').sortable();
                    }
                },
                error: function(xhr, textStatus, thrownError) {
                    unblockUI('.contentpanel');
                    alert('Something went wrong. Please Try again later!');
                }
            });
        }
    });
});

function removeThisField(obj) {
	$(obj).parent().remove();
	var parentClass = $(obj).parent().attr('class');

	if(parentClass == 'address') {
		$('.address').remove();
	}

	$('#'+parentClass).prop('checked', false);

	$('.sortable').sortable();
}

function addNewField() {
	$('#error').html('');
$('#addNewField').modal('show');
}

function addThisForm() {
	var formFields = new Array();
	var formName = $('#newFormName').val();

	$('.selectedFields').each(function() {
		formFields.push($(this).html());
	});

	if(formName == '') {
		$('#error1').html('<div class="alert alert-danger">Please Enter Form Name.</div>');
		$('html, body').animate({ scrollTop: 0 }, 'slow');
	}
	else if(formFields.length <= 0) {
		$('#error1').html('<div class="alert alert-danger">Please Select Or Add Fields to Forms.</div>');
		$('html, body').animate({ scrollTop: 0 }, 'slow');
	}
	else {
		var newFormFields = JSON.stringify(formFields);

		$.ajax({
			type: 'post',
			url: "{{ URL::route('admin.forms.addoreditform') }}",
			cache: false,
			data: {"formName": formName, "formFields": newFormFields, "action": "add"},
			beforeSend : function () {
				blockUI('.contentpanel');
			},
			success: function(response) {
				unblockUI('.contentpanel');
				var obj = jQuery.parseJSON(response);

				if(obj.success == "success") {
				  location.reload();
				}
			},
			error: function(xhr, textStatus, thrownError) {
				unblockUI('.contentpanel');
				showError('An error occurred while saving form. Please try again!');
			}
		});
	}
}

function editThisForm(formID) {
	var formFields = new Array();

	$('.selectedFields').each(function() {
		formFields.push($(this).html());
	});

	if(formFields.length <= 0) {
		$('#error1').html('<div class="alert alert-danger">Please Select Or Add Fields to Forms.</div>');
		$('html, body').animate({ scrollTop: 0 }, 'slow');
	}
	else {
		var newFormFields = JSON.stringify(formFields);

		$.ajax({
			type: 'post',
			url: "{{ URL::route('admin.forms.addoreditform') }}",
			cache: false,
			data: {"formID": formID, "formFields": newFormFields, "action": "edit"},
			beforeSend : function () {
				blockUI('.contenpanel');
			},
			success: function(response) {
				unblockUI('.contentpanel');
				var obj = jQuery.parseJSON(response);

				if(obj.success == "success") {
				  location.reload();
				}
			},
			error: function(xhr, textStatus, thrownError) {
				unblockUI('.contentpanel');
				showError('An error occurred while saving form. Please try again!');
			}
		});
	}
}

function deleteThisForm(formID) {
	$.ajax({
		type: 'post',
		url: "{{ URL::route('admin.forms.deleteform') }}",
		cache: false,
		data: {"formID": formID},
		beforeSend : function () {
			blockUI('.contentpanel');
		},
		success: function(response) {
			unblockUI('.contentpanel');
			var obj = jQuery.parseJSON(response);

			if(obj.success == "success") {
			  location.reload();
			}
			else {
				showError(obj.message);
			}
		},
		error: function(xhr, textStatus, thrownError) {
			unblockUI('.contentpanel');
			showError('An error occurred deleting form. Please try again!');
		}
	});
}

function showDemoForm() {
	var formFields = new Array();
	var emailFieldExists = false;

	$('.selectedFields').each(function() {
		formFields.push($(this).html());

		if($(this).html() == 'Email') {
			emailFieldExists = true;
		}
	});
	var newFormFields = JSON.stringify(formFields);

	window.open(
	  "{{ URL::route('admin.forms.showdemoform') }}?fieldArray="+ encodeURIComponent(newFormFields)+"&emailFieldExists="+emailFieldExists,
	  '_blank' // <- This is what makes it open in a new window.
	);
}
</script>
@stop
