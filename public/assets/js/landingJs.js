var action = 'add';
var logo = '';

$(function() {
    CKEDITOR.replace( 'headerText', {
        filebrowserBrowseUrl:roxyFileman +"/index.html",
        filebrowserImageBrowseUrl:roxyFileman+'/index.html?type=image',
        removeDialogTabs: 'link:upload;image:upload',
        baseDir: roxyFileman
    });

    CKEDITOR.replace( 'footerText', {
        filebrowserBrowseUrl:roxyFileman +"/index.html",
        filebrowserImageBrowseUrl:roxyFileman+'/index.html?type=image',
        removeDialogTabs: 'link:upload;image:upload',
        baseDir: roxyFileman
    });

    jQuery('#colorpickerholder').ColorPicker({
        flat: true,
        onChange: function (hsb, hex, rgb) {
            jQuery('#colorpicker').val('#'+hex);
        }
    });

    $('[data-toggle="tooltip"]').tooltip();
});

$('#managerAllUsers').click(function() {
    if ($(this).is(":checked")) {
        $(".managerUsers").prop('checked', true);
    }
    else {
        $(".managerUsers").prop('checked', false);
    }
});

$('#dropMenudDiv').slimScroll({
    height: '200px',
    size:'4px'
});

$(".predefindedFields").click(function() {
    var value = $(this).val();
    var id = $(this).attr('id');

    var fieldType = $(this).attr('data-type');

    if($(this).is(':checked')) {
        if(value == 'Address') {
            $('.sortable').append('<li class="'+id+'"><span class="selectedFields" rel="text">Post/Zip code</span> <span class="pull-right" style="margin-right: 19px;"><input type="checkbox"></span></li>');
        }
        $('.sortable').append('<li class="'+id+'"><span class="selectedFields" rel="text">'+value+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px;"><input type="checkbox"></span></li>');
        $('.sortable').sortable();
    }
    else {
        $('.'+id).remove();
    }
    $('.sortable').sortable();
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

$('#editFormName').val('');
$('#editFormName').change(function() {
    var editFormID = $(this).val();

    $('.predefindedFields').prop('checked', false);

    $('.sortable').html('');
    $('#error1').html('');
    $('#newFormName').val('');
    CKEDITOR.instances.headerText.setData('');
    CKEDITOR.instances.footerText.setData('');
    $('#preview').attr('src', '');
    jQuery('#colorpicker').val('');
    $('#message').val('');
    $('#colorpickerholder').ColorPickerSetColor('#fffff');

    var input = $("#logo");

    function something_happens() {
        input.replaceWith(input.val('').clone(true));
    };

    if(editFormID == '') {
        $('#newFormName').removeAttr('disabled');

        $('#deleteButton').hide();
        $('#addOrEditButton').html('Add Form');
        $('#addOrEditButton').attr('onclick', 'addThisForm()');

        action = 'add';
    }
    else {
        $('#newFormName').attr('disabled', 'disabled');
        action = 'edit';

        $.ajax({
            type: 'post',
            url: detailUrl,
            cache: false,
            data: {"formID": editFormID},
            dataType : 'json',
            beforeSend : function () {
              blockUI('.contentpanel');
            },
            success: function(obj) {
                unblockUI('.contentpanel');
                if(obj.success == "success") {
                    $('#deleteButton').show();
                    $('#deleteButton').attr('onclick', 'deleteThisForm('+editFormID+')');
                    $('#addOrEditButton').html('Save and Exit');
                    $('#addOrEditButton').attr('onclick', 'editThisForm('+editFormID+')');

                    var ajaxFieldArray = new Array();
                    ajaxFieldArray = obj.formFields;

                    ajaxFieldArray.forEach(function(fields) {
                        var currentFieldName = fields.fieldName;
                        var type = fields.type;
                        if(fields.isRequired == 'Yes'){
                            var required = 'checked';
                        }
                        else{
                            var required = '';
                        }

                        if(currentFieldName == 'Company Name') {
                            var fieldID = "companyName";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Telephone No') {
                            var fieldID = "telephone";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'First Name'){
                            var fieldID = "firstName";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Last Name') {
                            var fieldID = "lastName";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Mobile No') {
                            var fieldID = "mobile";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Position') {
                            var fieldID = "position";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Notes') {
                            var fieldID = "notes";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Address') {
                            var fieldID = "address";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">Post/Zip code</span> <span class="pull-right" style="margin-right: 19px"><input type="checkbox" '+required+'> </span> </li>');
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Post/Zip code') {
                            //Nothing to do
                        }
                        else if(currentFieldName == 'Email') {
                            var fieldID = "email";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else if(currentFieldName == 'Website') {
                            var fieldID = "website";
                            $('#'+fieldID).prop('checked', true);
                            $('.sortable').append('<li class="'+fieldID+'"><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                        }
                        else {
                            if(type == 'text'){
                                $('.sortable').append('<li><span class="selectedFields" rel="'+type+'">'+currentFieldName+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'>  </span></li>');
							} else if(type == 'date'){
								$('.sortable').append('<li><i class="mr5 glyphicon glyphicon-calendar"></i><span class="selectedFields" rel="'+type+'">'+ currentFieldName+
										'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span>' +
										' <span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+
										required+'>  </span></li>');
                            } else if(type == 'dropdown'){
                                $('.sortable').append('<li><span class="selectedFields" rel="'+type+'" data-name="'+currentFieldName+'" data-value="'+fields.values+'">'+currentFieldName+' - '+fields.values+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+required+'> </span></li>');
                            }
                        }
                    });

                    $('.sortable').sortable();

                    //set header, footer etc
                    CKEDITOR.instances.headerText.setData(obj.form.header);
                    CKEDITOR.instances.footerText.setData(obj.form.footer);

                    if(obj.form.logo != '') {
                        $('#preview').attr('src', imagePath + '/'+ obj.form.logo);
                    }

                    jQuery('#colorpicker').val(obj.form.color);
                    $('#message').val(obj.form.thankYouMessage);
                    $('#colorpickerholder').ColorPickerSetColor(obj.form.color);

                    if(obj.form.logo != '') {
						logo = imagePath + '/'+ obj.form.logo;
					} else {
						logo = '';
					}

                }
            },
            error: function(xhr, textStatus, thrownError) {
                unblockUI('.contentpanel');
                showError('Error in getting form details. Please try again!');
            }
        });
    }
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


    $('.selectedFields').each(function() {
        var type = $(this).attr('rel');
        var required = $(this).parent().find('input[type="checkbox"]').prop('checked');

        if(type == 'text' || type == 'date'){
            formFields.push({
                'name' : $(this).html(),
                'type' : type,
                'required' : required
            });
        }
        else if(type == 'dropdown'){
            formFields.push({
                'name' : $(this).attr('data-name'),
                'type' : type,
                'value' : $(this).attr('data-value'),
                'required' : required
            });
        }
    });

    if(formName == '') {
        showFormError('Please enter form name', "error1");
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
    else if(formFields.length <= 0) {
        showFormError('Please select or add fields to forms', "error1");
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
    else{
        var formData = getAttribute(true);
        var newFormFields = JSON.stringify(formFields);

        formData.append('formName', formName);
        formData.append('formFields', newFormFields);
        formData.append('action', 'add');

        $.ajax({
            type: 'post',
            url: editUrl,
            cache: false,
            data: formData,
            processData: false,
            contentType: false,
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
                showError('Something went wrong. Please Try again later!');
            }
        });
    }
}

function editThisForm(formID) {
    hideErrors();


    var formFields = [];

    $('.selectedFields').each(function() {
        var type = $(this).attr('rel');
        var required = $(this).parent().find('input[type="checkbox"]').prop('checked');

        if(type == 'text' || type == 'date'){
            formFields.push({
                'name' : $(this).html(),
                'type' : type,
                'required' : required
            });
        }
        else if(type == 'dropdown'){
            formFields.push({
                'name' : $(this).attr('data-name'),
                'type' : type,
                'value' : $(this).attr('data-value'),
                'required' : required
            });
        }
    });

    if(formFields.length <= 0) {
        showFormError('Please select or add fields to form', "error1");
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    }
    else{
        var formData = getAttribute(false);
        var newFormFields = JSON.stringify(formFields);

        formData.append('formID', formID);
        formData.append('formFields', newFormFields);
        formData.append('action', 'edit');


        $.ajax({
            type: 'post',
            url: editUrl,
            cache: false,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
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
                showError('Something went wrong. Please try again later!');
            }
        });
    }
}

function deleteThisForm(formID) {
    hideErrors();

    $.ajax({
        type: 'post',
        url: deleteUrl,
        cache: false,
        data: {"formID": formID},
        success: function(response) {

            if(response.success == "success") {
                location.reload();
            }
        },
        error: function(xhr, textStatus, thrownError) {
            showError('Something went wrong. Please try again later!');
        }
    });
}

function showDemoForm() {
    var formFields = [];

    $('.selectedFields').each(function() {
        var type = $(this).attr('rel');

        if(type == 'text' || type == 'date'){
            formFields.push({
                'name' : $(this).html(),
                'type' : type
            });
        }
        else if(type == 'dropdown'){
            formFields.push({
                'name' : $(this).attr('data-name'),
                'type' : type,
                'value' : $(this).attr('data-value')
            });
        }
    });

    if(formFields.length <= 0) {
        showFormError('Please select or add fields to forms', "error1");
        $('html, body').animate({ scrollTop: 0 }, 'slow');

        return false;
    }

    if(action == 'add'){
        var formData = getAttribute(true);
    }
    else if(action == 'edit') {
        var formData = getAttribute(false);
        formData.append('logoPath', logo);
    }

    var newFormFields = JSON.stringify(formFields);
    formData.append('formFields', newFormFields);
    formData.append('action', action);

    $.ajax({
        type: 'post',
        url: demoFormUrl,
        cache: false,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            var win=window.open(demoFormUrl);
            with(win.document)
            {
                open();
                write(response);
                close();
            }
        },
        error: function(xhr, textStatus, thrownError) {
            showError('Something went wrong. Please try again later!');
        }
    });
}

$('#addNewField').find('input[type=radio]').change(function(){
    var value = $(this).val();

    if(value == 'forField' || value == 'forDate'){
        $('#fieldDiv').removeClass('hidden');
        $('#dropMenudDiv').addClass('hidden');
    }
    else if(value == 'forDropMenu'){
        $('#fieldDiv').addClass('hidden');
        $('#dropMenudDiv').removeClass('hidden');
    }

});

function addNewOptions(){
    var currentOptions = $('#optionDiv').children().length;
    var newOption = currentOptions + 1;

    var html = '<div class="form-group"><input type="text"  class="form-control input-sm options" name="options[]" placeholder="Option '+newOption+'"/></div>';

    $('#optionDiv').append(html);
}

function resetAddNewFieldModal(){
    $('#addNewField').find('input[type=radio]').prop('checked', false);

    $('#fieldDiv').addClass('hidden');
    $('#dropMenudDiv').addClass('hidden');
    $('#addNewField').find('input[type=text]').val('');
    $('#requiredInput').prop('checked', false);

    //remove options if it is more than two
    var child = $('#optionDiv').children();

    for(var i = 1; i < child.length; i++){
        child[i].remove();
    }
}

$('#addThisNewField').click(function(){
    hideErrors();

    var fieldType = $('#addNewField').find('input[type=radio]:checked').val();

    if(fieldType == 'forField' || fieldType == 'forDate'){
        var fieldValue = $('#fieldDiv').find('#newFieldName').val();

        if(fieldValue == '') {
            showFormError('Please enter field name');
        }
        else {
            if($('#requiredInput').is(':checked')){
                var required = 'checked';
            }
            else{
                var required = '';
            }

			var field_type = 'text';
			if(fieldType == 'forDate') {
				field_type = 'date';
			}

			if(fieldType == 'forField') {
				$('.sortable').append('<li class="inputList"><span class="selectedFields" rel="'+field_type+'">'+fieldValue+
					'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span> <span class="pull-right" style="margin-right: 8px;"><input '+required+
					' type="checkbox"></span></li>');
			} else {
				$('.sortable').append('<li><i class="mr5 glyphicon glyphicon-calendar"></i><span class="selectedFields" rel="'+field_type+'">'+ fieldValue+
						'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span>' +
						' <span class="pull-right" style="margin-right: 8px"><input type="checkbox" '+
						required+'>  </span></li>');
			}
            $('.sortable').sortable();

            $('#fieldDiv').find('#newFieldName').val('');
            $('#addNewField').modal('toggle');
        }
    }
    else if(fieldType == 'forDropMenu'){
        var fieldValue = $('#dropMenudDiv').find('#newFieldName').val();

        if(fieldValue == '') {
            showFormError('Please enter field name');
            return false;
        }

        var options = [];
        var error = false;

        $('#optionDiv').find('input[type=text]').each(function(){
            var val = $(this).val();

            if(val == '' || val == undefined){
                showFormError('Please enter option values');
                error = true;
            }
            else{
                options.push(val);
            }
        });

        if(!error){
            if($('#requiredInput').is(':checked')){
                var required = 'checked';
            }
            else{
                var required = '';
            }

            $('.sortable').append('<li class="inputList"><span class="selectedFields" rel="dropdown" data-required="'+required+'" data-name="'+fieldValue+'" data-value="'+options.join(',')+'">'+fieldValue+' - '+options.join(', ')+'</span> <span onclick="removeThisField(this)" class="pull-right"><i class="fa fa-times"></i></span><span class="pull-right" style="margin-right: 8px;"><input '+required+' type="checkbox"></span></li>');
            $('.sortable').sortable();

            $('#dropMenudDiv').find('#newFieldName').val('');
            $('#addNewField').modal('toggle');
        }
    }

});

function openFormDetailModal(tab){

    $('#formDetail').modal('show');
    $('#formDetail').find('a[href="#'+tab+'"]').trigger('click');
}

function getAttribute(fileValidation){
    var headerText = CKEDITOR.instances.headerText.getData();

    /*if(headerText == undefined || headerText == null || headerText == ''){
        showFormError('Please enter header text', "error1");
        return false;
    }*/
    var footerText = CKEDITOR.instances.footerText.getData();

    /*if(footerText == undefined || footerText == null || footerText == ''){
        showFormError('Please enter footer text', "error1");
        return false;
    }*/

    var fileInput = document.getElementById('logo');
    var file = fileInput.files[0];

    /*if(fileValidation){
        if(file == undefined || file == null || file == ''){
            showFormError('Please select logo', "error1");
            return false;
        }
    }*/

    var message = $('#message').val();

    /*if(message == undefined || message == null || message == ''){
        showFormError('Please enter thank you message', "error1");
        return false;
    }*/

    var color = $('#colorpicker').val();
    /*if(color == undefined || color == null || color == ''){
        showFormError('Please choose color', "error1");
        return false;
    }*/

    var formData = new FormData();

    formData.append('headerText', headerText);
    formData.append('footerText', footerText);
    formData.append('logo', file);
    formData.append('message', message);
    formData.append('color', color);

    return formData;
}

function handleFileSelect(evt) {
    var files = evt.target.files;

    // Loop through the FileList and render image files as thumbnails.
    for (var i = 0, f; f = files[i]; i++) {

        // Only process image files.
        if (!f.type.match('image.*')) {
            continue;
        }

        var reader = new FileReader();

        // Closure to capture the file information.
        reader.onload = (function(theFile) {
            return function(e) {
                // Render thumbnail.
                var preview = document.getElementById('preview');
                preview.setAttribute('src', e.target.result);
            };
        })(f);

        // Read in the image file as a data URL.
        reader.readAsDataURL(f);
    }
}

document.getElementById('logo').addEventListener('change', handleFileSelect, false);


