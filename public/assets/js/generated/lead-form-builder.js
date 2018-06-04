'use strict';

/* lead form support code
 * resources/views/site/form/createoredit.blade.php
 * this file requires processing via babel (see gulp)
 */

/* jshint esversion: 6 */

$('.sortable').sortable();
$('#editFormName').val('');

$('#managerAllUsers').click(function () {
	if ($(this).is(":checked")) {
		$(".managerUsers").prop('checked', true);
	} else {
		$(".managerUsers").prop('checked', false);
	}
});

$('#dropMenudDiv').slimScroll({
	height: '200px',
	size: '4px'
});

var btnHtmlRemoveField = '<span title="Remove Field" onclick="removeThisField(this)" class="pull-right fieldSmallBtn"><i class="fa fa-times"></i></span>';

$('#editFormName').change(function () {
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
	$('#email').prop('checked', false);

	$('.sortable').html('');
	$('#error1').html('');

	$('#divChangesLog').html('');;

	if (editFormID == '') {
		$('#newFormName').val('');
		$('#newFormName').removeAttr('disabled');

		$('#deleteButton').hide();
		$('#addOrEditButton').html('Add This Form');
		$('#addOrEditButton').attr('onclick', 'addThisForm()');
	} else {
		$('#newFormName').val('');
		$('#newFormName').attr('disabled', 'disabled');

		$.ajax({
			type: 'post',
			url: formDetailsUrl,
			cache: false,
			data: { "formID": editFormID },
			beforeSend: function beforeSend() {
				blockUI('.contentpanel');
			},
			success: function success(response) {
				unblockUI('.contentpanel');
				var obj = jQuery.parseJSON(response);

				if (obj.success == "success") {
					$('#deleteButton').show();
					$('#deleteButton').attr('onclick', 'deleteThisForm(' + editFormID + ')');
					$('#addOrEditButton').html('Save Form');
					$('#addOrEditButton').attr('onclick', 'editThisForm(' + editFormID + ')');

					var ajaxFieldArray = new Array();
					ajaxFieldArray = obj.formFields;

					ajaxFieldArray.forEach(function (fields) {
						var currentFieldName = fields.fieldName;
						var type = fields.type;
						var required = '';

						if (fields.isRequired == 'Yes') {
							required = 'checked';
						}
						var requiredCheckBoxHtml = '<span class="pull-right fieldSmallBtn" style="margin-right: 28px"><input title="Is field required?" type="checkbox" ' + required + '> </span>';
						var requiredCheckBoxCustomHtml = '<span class="pull-right fieldSmallBtn" style="margin-right: 8px"><input title="Is field required?" type="checkbox" ' + required + '> </span>';

						var fieldID;

						if (currentFieldName == 'Company Name') {
							fieldID = "companyName";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Telephone No') {
							fieldID = "telephone";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'First Name') {
							fieldID = "firstName";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Last Name') {
							fieldID = "lastName";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Mobile No') {
							fieldID = "mobile";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Position') {
							fieldID = "position";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Notes') {
							fieldID = "notes";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Address') {
							fieldID = "address";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Post/Zip code') {
							fieldID = "address";
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">Post/Zip code</span>' + '<span class="pull-right fieldSmallBtn" style="margin-right: 39px"><input title="Is field required?" type="checkbox" ' + required + '> </span></li>');
						} else if (currentFieldName == 'Email') {
							fieldID = "email";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else if (currentFieldName == 'Website') {
							fieldID = "website";
							$('#' + fieldID).prop('checked', true);
							$('.sortable').append('<li class="' + fieldID + '"><span class="selectedFields" rel="' + type + '">' + currentFieldName + '</span> ' + btnHtmlRemoveField + requiredCheckBoxHtml + '</li>');
						} else {
							//custom fields
							var removeFieldHtml = btnHtmlRemoveField;

							if (type == 'text' || type == 'date') {
								removeFieldHtml += '<span title="Rename field" onclick="renameThisField(this)" class="pull-right fieldSmallBtn" style="margin-right: 8px">' + '<i class="fa fa-pencil"></i></span>';
							} else {
								removeFieldHtml += '<span title="Edit field" onclick="editDropDownField(this)" class="pull-right fieldSmallBtn" style="margin-right: 8px">' + '<i class="fa fa-pencil"></i></span>';
							}

							if (type == 'text') {

								$('.sortable').append('<li>' + '<span class="selectedFields" rel="' + type + '" data-original-name="' + currentFieldName + '">' + currentFieldName + '</span>' + removeFieldHtml + requiredCheckBoxCustomHtml + '</li>');
							} else if (type == 'date') {

								$('.sortable').append('<li><i class="mr5 glyphicon glyphicon-calendar"></i>' + '<span class="selectedFields" rel="' + type + '" data-original-name="' + currentFieldName + '">' + currentFieldName + '</span>' + removeFieldHtml + requiredCheckBoxCustomHtml + '</li>');
							} else if (type == 'dropdown') {

								$('.sortable').append('<li><span class="selectedFields" rel="' + type + '" data-original-name="' + currentFieldName + '" data-name="' + currentFieldName + '" data-original-value="' + fields.values + '" data-value="' + fields.values + '">' + currentFieldName + ' - ' + fields.values + '</span>' + removeFieldHtml + requiredCheckBoxCustomHtml + '</li>');
							}
						}
					});

					$('.sortable').sortable();
				}
			},
			error: function error(xhr, textStatus, thrownError) {
				unblockUI('.contentpanel');
				showError('Error saving form. Please try again!');
			}
		});
	}
});

function removeThisField(obj) {
	hideErrors();
	bootbox.confirm("Warning, By deleting this field you will lose the data from this field that is stored under any active campaigns.", function (result) {
		if (result) {
			$(obj).parent().remove();
			var parentClass = $(obj).parent().attr('class');

			if (parentClass == 'address') {
				$('.address').remove();
			}

			$('#' + parentClass).prop('checked', false);
			$('.sortable').sortable();
		}
	});
}

$(".predefindedFields").click(function () {
	var value = $(this).val();
	var id = $(this).attr('id');

	var fieldType = $(this).attr('data-type');

	if ($(this).is(':checked')) {
		if (value == 'Address') {
			$('.sortable').append('<li class="' + id + '"><span class="selectedFields" rel="text">Post/Zip code</span>' + '<span class="pull-right fieldSmallBtn" style="margin-right: 38px;"><input title="Is field required?" type="checkbox"></span> </li>');
		}
		$('.sortable').append('<li class="' + id + '"><span class="selectedFields" rel="text">' + value + '</span>' + btnHtmlRemoveField + '<span class="pull-right fieldSmallBtn" style="margin-right: 28px;"><input title="Is field required?" type="checkbox"></span></li>');
		$('.sortable').sortable();
	} else {
		$('.' + id).remove();
	}
	$('.sortable').sortable();
});

function addNewField() {
	hideErrors();
	resetAddNewFieldModal();
	$('#addNewField').modal('show');

	$('.slimScrollDiv').css('width', '100%');
}

function addThisForm() {
	hideErrors();
	var formFields = [];
	var formName = $('#newFormName').val();

	$('.selectedFields').each(function () {
		var type = $(this).attr('rel');
		var required = $(this).parent().find('input[type="checkbox"]').prop('checked');

		if (type == 'text' || type == 'date') {
			formFields.push({
				'name': $(this).html(),
				'type': type,
				'required': required
			});
		} else if (type == 'dropdown') {
			formFields.push({
				'name': $(this).attr('data-name'),
				'type': type,
				'value': $(this).attr('data-value'),
				'required': required
			});
		}
	});

	if (formName == '') {
		showFormError('Please enter form name', "error1");
		$('html, body').animate({ scrollTop: 0 }, 'slow');
	} else if (formFields.length <= 0) {
		showFormError('Please select or add fields to forms', "error1");
		$('html, body').animate({ scrollTop: 0 }, 'slow');
	} else {
		var newFormFields = JSON.stringify(formFields);

		$.ajax({
			type: 'post',
			url: formAddEditUrl,
			cache: false,
			beforeSend: function beforeSend() {
				blockUI('.contentpanel');
			},
			data: { "formName": formName, "formFields": newFormFields, "action": "add" },
			success: function success(response) {
				unblockUI('.contentpanel');
				var obj = jQuery.parseJSON(response);

				if (obj.success == "success") {
					location.reload();
				}
			},
			error: function error(xhr, textStatus, thrownError) {
				unblockUI('.contentpanel');
				showError('Something went wrong. Please Try again later!');
			}
		});
	}
}

function editThisForm(formID) {
	hideErrors();
	var formFields = [];

	$('.selectedFields').each(function () {
		var type = $(this).attr('rel');
		var required = $(this).parent().find('input[type="checkbox"]').prop('checked');

		if (type == 'text' || type == 'date') {

			//original name is set for custom fields only
			var originalName = $(this).data('original-name');
			if (typeof originalName === 'undefined') {
				originalName = $(this).html();
			}

			formFields.push({
				'originalName': originalName,
				'name': $(this).html(),
				'type': type,
				'required': required
			});
		} else if (type == 'dropdown') {
			var optionsChanges = $(this).attr('data-options-changes');
			if (typeof optionsChanges !== "undefined") {
				optionsChanges = JSON.parse(optionsChanges);
			} else {
				optionsChanges = [];
			}
			formFields.push({
				'originalName': $(this).data('original-name'),
				'name': $(this).attr('data-name'),
				'type': type,
				'originalValue': $(this).data('original-value'),
				'value': $(this).attr('data-value'),
				'required': required,
				'optionsChanges': optionsChanges
			});
		}
	});

	if (formFields.length <= 0) {
		showFormError('Please select or add fields to form', "error1");
		$('html, body').animate({ scrollTop: 0 }, 'slow');
	} else {
		var newFormFields = JSON.stringify(formFields);

		$.ajax({
			type: 'post',
			url: formAddEditUrl,
			cache: false,
			data: { "formID": formID, "formFields": newFormFields, "action": "edit" },
			beforeSend: function beforeSend() {
				blockUI('.contentpanel');
			},
			success: function success(response) {
				unblockUI('.contentpanel');
				var obj = jQuery.parseJSON(response);
				if (obj.status == "success") {
					//location.reload();
					$('#editFormName').trigger('change');
				} else if (obj.status == "fail") {
					showError('Error on saving form : ' + obj.message);
				}
			},
			error: function error(xhr, textStatus, thrownError) {
				unblockUI('.contentpanel');
				showError('Something went wrong. Please try again later!');
			}
		});
	}
}

function deleteThisForm(formID) {
	hideErrors();
	bootbox.confirm("Are you sure to delete this form?", function (result) {
		if (result) {
			$.ajax({
				type: 'post',
				url: formDeleteUrl,
				cache: false,
				data: { "formID": formID },
				beforeSend: function beforeSend() {
					blockUI('.contentpanel');
				},
				success: function success(response) {
					unblockUI('.contentpanel');
					var obj = response;

					if (obj.status == "success") {
						location.reload();
					} else if (obj.status == "fail_associated_exists") {
						var campaign_names = '';
						for (var i = 0; i < obj.campaigns.length; i++) {
							campaign_names += obj.campaigns[i] + '<br />';
						}
						var html = 'The Form "' + obj.form_name + '" is currently associated with the following active campaigns:<br /><br />' + campaign_names + '<br />In order to delete this form please mark these campaigns as finished first.';
						bootbox.dialog({
							title: 'Please Note!',
							message: html,
							buttons: {
								success: {
									label: "OK",
									className: "btn-success center-block"
								}
							}
						});
					} else {
						showError("Error: " + obj.message);
					}
				},
				error: function error(xhr, textStatus, thrownError) {
					unblockUI('.contentpanel');
					showError('Something went wrong. Please try again later!');
				}
			});
		}
	});
}

function showDemoForm() {
	var formFields = [];
	var emailFieldExists = false;

	$('.selectedFields').each(function () {
		var type = $(this).attr('rel');

		if (type == 'text' || type == 'date') {
			formFields.push({
				'name': $(this).html(),
				'type': type
			});
		} else if (type == 'dropdown') {
			formFields.push({
				'name': $(this).attr('data-name'),
				'type': type,
				'value': $(this).attr('data-value')
			});
		}

		if ($(this).html() == 'Email') {
			emailFieldExists = true;
		}
	});

	var newFormFields = JSON.stringify(formFields);

	window.open(formShowDemoUrl + "?fieldArray=" + encodeURIComponent(newFormFields) + "&emailFieldExists=" + emailFieldExists, '_blank' // <- This is what makes it open in a new window.
	);
}

$('#addNewField').find('input[type=radio]').change(function () {
	var value = $(this).val();

	if (value == 'forField' || value == 'forDate') {
		$('#fieldDiv').removeClass('hidden');
		$('#dropMenudDiv').addClass('hidden');
	} else if (value == 'forDropMenu') {
		$('#fieldDiv').addClass('hidden');
		$('#dropMenudDiv').removeClass('hidden');
	}
});

function addNewOptions() {
	var currentOptions = $('#optionDiv').children().length;
	var newOption = currentOptions + 1;

	var html = '<div class="form-group"><input type="text"  class="form-control input-sm options" name="options[]" placeholder="Option ' + newOption + '"/></div>';

	$('#optionDiv').append(html);
}

function resetAddNewFieldModal() {
	$('#addNewField').find('input[type=radio]').prop('checked', false);

	$('#fieldDiv').addClass('hidden');
	$('#dropMenudDiv').addClass('hidden');
	$('#addNewField').find('input[type=text]').val('');
	$('#requiredInput').prop('checked', false);

	//remove options if it is more than two
	var child = $('#optionDiv').children();

	for (var i = 1; i < child.length; i++) {
		child[i].remove();
	}
}

$('#addThisNewField').click(function () {
	hideErrors();
	var fieldType = $('#addNewField').find('input[type=radio]:checked').val();

	var removeFieldHtml = btnHtmlRemoveField;

	var fieldValue;
	var required = '';
	var requiredCheckBoxHtml;

	if (fieldType == 'forField' || fieldType == 'forDate') {
		fieldValue = $('#fieldDiv').find('#newFieldName').val();
		//remove inorrect chars
		fieldValue = fieldValue.replace(/[,'"]*/g, '');

		if (fieldValue === '') {
			showError('Please enter field name');
		} else {
			required = '';
			if ($('#requiredInput').is(':checked')) {
				required = 'checked';
			}

			var field_type = 'text';
			if (fieldType == 'forDate') {
				field_type = 'date';
			}

			requiredCheckBoxHtml = '<span class="pull-right fieldSmallBtn" style="margin-right: 8px"><input title="Is field required?" type="checkbox" ' + required + '> </span>';

			removeFieldHtml += '<span title="Rename field" onclick="renameThisField(this)" class="pull-right fieldSmallBtn" style="margin-right: 8px">' + '<i class="fa fa-pencil"></i></span>';

			if (fieldType == 'forField') {
				$('.sortable').append('<li><span class="selectedFields" rel="' + field_type + '" data-original-name="' + fieldValue + '">' + fieldValue + '</span> ' + removeFieldHtml + requiredCheckBoxHtml + '</li>');
			} else {
				$('.sortable').append('<li><i class="mr5 glyphicon glyphicon-calendar"></i><span class="selectedFields" rel="' + field_type + '" data-original-name="' + fieldValue + '">' + fieldValue + '</span> ' + removeFieldHtml + requiredCheckBoxHtml + '</li>');
			}

			$('.sortable').sortable();

			$('#fieldDiv').find('#newFieldName').val('');
			appendEntryToLog('add_new_field', '', fieldValue);
			$('#addNewField').modal('toggle');
		}
	} else if (fieldType == 'forDropMenu') {
		fieldValue = $('#dropMenudDiv').find('#newFieldName').val();

		//remove inorrect chars
		fieldValue = fieldValue.replace(/[,'"]*/g, '');

		if (fieldValue === '') {
			showError('Please enter field name');
			return false;
		}

		var logEntries = [];
		logEntries.push(function () {
			appendEntryToLog('add_new_field', '', fieldValue);
		});

		var options = [];
		var error = false;
		var optionsChanges = [];

		$('#optionDiv').find('input[type=text]').each(function () {
			var val = $(this).val();
			//remove inorrect chars
			val = val.replace(/[,'"]*/g, '');

			if (val === '' || val === undefined) {
				showError('Please enter option values');
				error = true;
			} else {
				options.push(val);

				//new option
				optionsChanges.push({
					'changeType': 'add_option',
					'newValue': val
				});
				logEntries.push(function () {
					appendEntryToLog('add_option', '', val, fieldValue);
				});
			}
		});

		if (!error) {
			required = '';
			if ($('#requiredInput').is(':checked')) {
				required = 'checked';
			}

			requiredCheckBoxHtml = '<span class="pull-right fieldSmallBtn" style="margin-right: 8px"><input title="Is field required?" type="checkbox" ' + required + '> </span>';

			removeFieldHtml += '<span title="Edit field" onclick="editDropDownField(this)" class="pull-right fieldSmallBtn" style="margin-right: 8px">' + '<i class="fa fa-pencil"></i></span>';

			$('.sortable').append('<li><span class="selectedFields" rel="dropdown" data-name="' + fieldValue + '" data-original-name="' + fieldValue + '" data-value="' + options.join(',') + '" data-original-value="" ' + ('data-options-changes=\'' + JSON.stringify(optionsChanges) + '\'') + '>' + fieldValue + ' - ' + options.join(', ') + '</span>' + removeFieldHtml + requiredCheckBoxHtml + '</li>');
			$('.sortable').sortable();

			$('#dropMenudDiv').find('#newFieldName').val('');
			$('#addNewField').modal('toggle');

			logEntries.forEach(function (logCallback) {
				logCallback.call();
			});
		}
	}
});

//renaming fields
var dlgRenameField = $('#dlgRenameField');

dlgRenameField.on('shown.bs.modal', function () {
	dlgRenameField.find('#renamedFieldName').focus();
});

dlgRenameField.find('#btnResetRenamedFieldName').click(function () {
	dlgRenameField.find('#renamedFieldName').val(dlgRenameField.find('#spnOriginalFieldName').html());
});

dlgRenameField.find('#btnSaveRenamedFieldName').click(function () {
	var dlg = dlgRenameField;

	var newName = dlg.find('#renamedFieldName').val();
	//remove inorrect chars
	newName = newName.replace(/[,'"]*/g, '');

	if (newName === '') {
		showError('Please enter new name');
		return;
	}
	var dataSpan = spanEditedField;
	dataSpan.html(newName);
	dlg.modal('toggle');

	//add log
	appendEntryToLog('rename', dataSpan.data('original-name'), dataSpan.html());
});

var spanEditedField;

function renameThisField(obj) {
	hideErrors();
	var dlg = dlgRenameField;
	var dataSpan = $(obj).parent().find('.selectedFields');
	spanEditedField = dataSpan;

	dlg.find('#spnOriginalFieldName').html(dataSpan.data('original-name'));
	dlg.find('#renamedFieldName').val(dataSpan.html());
	dlg.modal('show');
}

//drop-down editor
var dlgDropDownEditor = $('#dlgDropDownEditor');
dlgDropDownEditor.on('shown.bs.modal', function () {
	dlgDropDownEditor.find('#renamedFieldName').focus();
});

dlgDropDownEditor.find('#btnResetRenamedFieldName').click(function () {
	dlgDropDownEditor.find('#renamedFieldName').val(dlgDropDownEditor.find('#spnOriginalFieldName').html());
});

dlgDropDownEditor.on('click', '.btnResetRenamedOption', function () {
	var txtOption = $(this).parent().parent().find('input[type=text]');
	txtOption.val(txtOption.data('original-value'));
	txtOption.focus();
});

dlgDropDownEditor.on('click', '.btnDeleteNewOption', function () {
	var txtOption = $(this).parent().parent().remove();
});

dlgDropDownEditor.find('#btnSaveUpdatedDropDown').click(function () {
	var dlg = dlgDropDownEditor;
	var dataSpan = spanEditedDropDown;
	var newName = dlg.find('#renamedFieldName').val();
	dataSpan.attr('data-name', newName);

	var logEntries = [];

	//add log
	logEntries.push(function () {
		appendEntryToLog('rename', dataSpan.data('original-name'), dataSpan.attr('data-name'));
	});

	var options = [];
	var optionsChanges = [];
	var error = false;

	clearLogForNewOptionsOfField(dataSpan.data('original-name'));

	var optionsDiv = dlg.find('.divDropDownOptions');
	optionsDiv.find('input[type=text]').each(function () {
		var val = $(this).val();
		//remove inorrect chars
		val = val.replace(/[,'"]*/g, '');

		if (val === '' || val === undefined) {
			showError('Please enter option values');
			error = true;
		} else {
			options.push(val);
			var originalValue = $(this).data('original-value');
			if (typeof originalValue !== 'undefined' && originalValue !== '') {
				//known option
				if (originalValue != val) {
					optionsChanges.push({
						'changeType': 'rename_option',
						'originalValue': originalValue,
						'newValue': val
					});
					logEntries.push(function () {
						appendEntryToLog('rename_option', originalValue, val, dataSpan.data('original-name'));
					});
				}
			} else {
				//new option
				optionsChanges.push({
					'changeType': 'add_option',
					'newValue': val
				});
				logEntries.push(function () {
					appendEntryToLog('add_option', '', val, dataSpan.data('original-name'));
				});
			}
		}
	});
	if (!error) {
		dlg.modal('toggle');
		dataSpan.attr('data-value', options.join(','));
		dataSpan.attr('data-options-changes', JSON.stringify(optionsChanges));
		dataSpan.html(newName + ' - ' + options.join(', '));
		logEntries.forEach(function (logCallback) {
			logCallback.call();
		});
	}
});

function buildDropDownEntryEditHtml(placeholder, value, originalValue) {

	var htmlRevertBtn = '';

	if (originalValue !== '') {
		htmlRevertBtn = '\n\t\t\t<div class="col-sm-2">\n\t\t\t\t<button title="Restore original value" class="btnResetRenamedOption btn btn-xs btn-success input-sm">Revert</button>\n\t\t\t</div>\n\t\t';
	} else {
		htmlRevertBtn = '\n\t\t\t<div class="col-sm-2">\n\t\t\t\t<button title="Delete option" class="btnDeleteNewOption btn btn-xs btn-success input-sm">Delete</button>\n\t\t\t</div>\n\t\t';
	}

	var html = '\n\t\t<div class="form-group">\n\t\t\t<div class="col-sm-10">\n\t\t\t<input type="text" data-original-value="' + originalValue + '" value="' + value + '" class="form-control input-sm options" name="options[]" title="' + placeholder + '" placeholder="' + placeholder + '"/>\n\t\t\t</div>\n\t\t\t' + htmlRevertBtn + '\n\t\t</div>\n\t\t';
	return html;
}

dlgDropDownEditor.find('#btnAddNewOptionToExistsDropDown').click(function () {
	var optionsDiv = dlgDropDownEditor.find('.divDropDownOptions');
	var currentOptions = optionsDiv.children().length;
	var newOption = currentOptions + 1;

	optionsDiv.append(buildDropDownEntryEditHtml('Option ' + newOption, '', ''));
});

var spanEditedDropDown;

function editDropDownField(obj) {
	hideErrors();
	var dlg = dlgDropDownEditor;
	var dataSpan = $(obj).parent().find('.selectedFields');
	spanEditedDropDown = dataSpan;

	var originalName = dataSpan.data('original-name');
	dlg.find('#spnOriginalFieldName').html(originalName);
	dlg.find('#renamedFieldName').val(dataSpan.attr('data-name'));

	var originalFieldValues = dataSpan.attr('data-original-value');
	if (originalFieldValues !== '' && originalFieldValues != "undefined") {
		originalFieldValues = originalFieldValues.split(',');
	} else {
		originalFieldValues = [];
	}
	var optionsDiv = dlg.find('.divDropDownOptions');
	optionsDiv.children().remove();

	var optionsChanges = dataSpan.attr('data-options-changes');
	if (typeof optionsChanges !== "undefined") {
		optionsChanges = JSON.parse(optionsChanges);
	} else {
		optionsChanges = [];
	}

	function processOptionChanges(optionOriginalValue) {
		//option might be already edited, restore 
		for (var i = 0; i < optionsChanges.length; i++) {
			var changeItem = optionsChanges[i];
			if (changeItem.originalValue == optionOriginalValue && changeItem.changeType == 'rename_option') {
				return changeItem.newValue;
			}
		}
		return optionOriginalValue;
	}

	//renames
	var optionId = 0;
	for (var i = 0; i < originalFieldValues.length; i++) {
		var optionOriginalValue = originalFieldValues[i];
		var updatedValue = processOptionChanges(optionOriginalValue);
		optionId++;
		optionsDiv.append(buildDropDownEntryEditHtml('Option ' + optionId + ', original value : ' + optionOriginalValue, updatedValue, optionOriginalValue));
	}

	//new 
	for (i = 0; i < optionsChanges.length; i++) {
		var changeItem = optionsChanges[i];
		if (changeItem.changeType == 'add_option') {
			optionId++;
			optionsDiv.append(buildDropDownEntryEditHtml('New option ' + optionId, changeItem.newValue, ''));
		}
	}

	dlg.modal('show');
}

function appendEntryToLog(changeType, originalName, newName, baseFieldName) {

	var log = $('#divChangesLog');
	function buildLogEntryHtml(changeType, originalName, newName, baseFieldName) {
		var message = '';
		var result = '';
		result += '<p ';
		result += 'data-change-type="' + changeType + '" ';
		if ('rename' == changeType) {
			result += 'data-original-field-name="' + originalName + '" ';
			result += 'data-new-field-name="' + newName + '" ';
			message = 'Field "' + originalName + '" renamed to "' + newName + '"';
		} else if ('rename_option' == changeType) {
			result += 'data-original-field-name="' + baseFieldName + '" ';
			result += 'data-original-option-name="' + originalName + '" ';
			result += 'data-new-option-name="' + newName + '" ';
			message = 'Option from field "' + baseFieldName + '" renamed from "' + originalName + '" to "' + newName + '"';
		} else if ('add_option' == changeType) {
			result += 'data-original-field-name="' + baseFieldName + '" ';
			result += 'data-new-option-name="' + newName + '" ';
			message = 'Added option to field "' + baseFieldName + '" name : "' + newName + '"';
		} else if ('add_new_field' == changeType) {
			result += 'data-new-field-name="' + newName + '" ';
			message = 'Added new field "' + newName + '"';
		}
		result += '>';
		result += message;
		result += '</p>';
		return result;
	}

	var changed;
	if ('rename' == changeType) {
		log.find('p[data-change-type="' + changeType + '"][data-original-field-name="' + originalName + '"]').remove();
		changed = originalName != newName;
		if (changed) {
			log.append(buildLogEntryHtml(changeType, originalName, newName));
		}
	} else if ('rename_option' == changeType) {
		log.find('p[data-change-type="' + changeType + '"][data-original-field-name="' + baseFieldName + '"][data-original-option-name="' + originalName + '"]').remove();
		changed = originalName !== '' && originalName != newName;
		if (changed) {
			log.append(buildLogEntryHtml(changeType, originalName, newName, baseFieldName));
		}
	} else if ('add_option' == changeType) {
		log.find('p[data-change-type="' + changeType + '"][data-original-field-name="' + baseFieldName + '"][data-new-option-name="' + newName + '"]').remove();
		log.append(buildLogEntryHtml(changeType, '', newName, baseFieldName));
	} else if ('add_new_field' == changeType) {
		log.find('p[data-change-type="' + changeType + '"][data-new-field-name="' + newName + '"]').remove();
		log.append(buildLogEntryHtml(changeType, '', newName));
	}
}

function clearLogForNewOptionsOfField(baseFieldName) {
	var log = $('#divChangesLog');
	log.find('p[data-change-type="add_option"][data-original-field-name="' + baseFieldName + '"]').remove();
}
//# sourceMappingURL=lead-form-builder.js.map
