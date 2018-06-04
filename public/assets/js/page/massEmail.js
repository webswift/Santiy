var MassEmail  = function() {
    var roxyFileMan = '';
    var maxFileSize = '';
    var formData = new FormData();
    var filesToBeDeleted = [];
    var detectEmailUrl;
    var advanceFilter;
    var csvFileUrl;
    var mapping;
    var testEmailData = {};
    var scheduleTime;
    var templateType = 'normal';
    var templateID;
	var applyFilterUrl;

    var resetFormData = function () {
      formData = new FormData();
    };

    var resetMapping = function () {
        mapping = undefined;
    };

    var resetAdvanceFilter = function () {
      advanceFilter = undefined;
    };

    var resetAdvanceFilterModal = function () {
        $('#advanceFilterModal').find('.modal-body').html('loading...');
    };

    var resetMappingModal = function () {
        $('#leadMappingModal').find('.modal-body').html('loading...');
    };

    var resetDeletedFiles = function () {
      filesToBeDeleted = [];
    };

    var ckeditorInit = function () {
        CKEDITOR.replace( 'content', {
            filebrowserBrowseUrl:roxyFileMan +"/index.html",
            filebrowserImageBrowseUrl:roxyFileMan+'/index.html?type=image',
            removeDialogTabs: 'link:upload;image:upload',
            baseDir: roxyFileMan
        });
        CKEDITOR.config.allowedContent = true;
    };

    var bytesToSize = function(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes == 0) return 'n/a';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        if (i == 0) return bytes + ' ' + sizes[i];
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
    };

    var uploadFiles = function (files) {
        for (var i = 0, file; file = files[i]; ++i) {
            var fileType = file.type;
            var fileSize = file.size;
            var fileName = file.name;
            var error = false;

            var fileError = $('#fileError');
            fileError.addClass('hidden');

            if(fileSize > (maxFileSize * 1024 * 1024)) {
                fileError.find('p').html(file.name + ' cannot be attached. It exceeds the max file size limit '+maxFileSize+' MB. Try another file.');
                fileError.removeClass('hidden');

                continue;
            }

            var extension = fileName.split('.').pop();

            if(extension == 'php' || extension == 'js' || extension == 'exe') {
                fileError.find('p').html(file.name + ' cannot be attached. File extension js, php and exe are not allowed.');
                fileError.removeClass('hidden');

                return false;
            }

            var fileIcon  = 'fa fa-file-text-o';

            switch (fileType) {
                case 'application/pdf':
                    fileIcon = 'fa fa-file-pdf-o';
                    break;
                case 'application/zip':
                case 'application/x-compressed-zip':
                case 'application/x-zip-compressed':
                    fileIcon = 'fa fa-file-archive-o';
                    break;
                case 'application/msword':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.template':
                    fileIcon = 'fa fa-file-word-o';
                    break;
                case 'image/gif':
                case 'image/jpeg':
                case 'image/png':
                    fileIcon = 'fa fa-file-image-o';
                    break;
                case 'application/vnd.openxmlformats-officedocument.presentationml.slideshow':
                case 'application/vnd.ms-powerpoint':
                case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                    fileIcon = 'fa fa-file-powerpoint-o';
                    break;
                case 'application/vnd.ms-excel':
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    fileIcon = 'fa fa-file-excel-o';
                    break;
                case 'text/plain':
                    fileIcon = 'fa fa-file-text-o';
                    break;
                default :
                    fileIcon = 'fa fa-file-text-o';
            }

            if(error == false) {
                var str = '<p class="col-md-4" rel="'+file.name+'">' +
                    '<label class="pull-left" title="Remove File"><span class="glyphicon glyphicon-remove remove-file" rel="'+file.name+'"></span></label>' +
                    '<label class="wrapContent content"><span><i class="'+fileIcon+'"> </i> '+file.name+'</span></label>' +
                    '<label class="pull-right"><span>'+bytesToSize(file.size)+'</span></label>' +
                    '</p>';

                $('#fileDetails').append(str);
                formData.append('file[]', file, file.name);
            }
        }
    };

    var importLeads = function (files) {
        hideErrors();
        resetLeadEmailCountDiv();
        var file = files[0];
        var fileSize = file.size;
        var fileName = file.name;

        if(fileSize > (maxFileSize * 1024 * 1024)) {
            showInputError('csvFile', file.name + ' cannot be attached. It exceeds the max file size limit '+maxFileSize+' MB. Try another file.');

            return false;
        }

        var extension = fileName.split('.').pop();

        if(extension != 'csv') {
            showInputError('csvFile', file.name + ' cannot be attached. Only csv files are allowed.');

            return false;
        }

        var data = new FormData();
        data.append('csvFile', file);

        $.ajax({
            type: 'post',
            url: csvFileUrl,
            dataType: "html",
            processData: false,
            contentType : false,
            data: data,
            beforeSend : function () {
                resetMappingModal();
                $('#leadMappingModal').modal({
                    keyboard : false,
                    show  :true
                });
            },
            success: function (response) {
                $('#leadMappingModal').find('.modal-content').html(response);
            },
            error: function (xhr, textStatus, thrownError) {

            }
        });
    };

    var fileListenerInit = function () {
        document.querySelector('input[name="attachmentFiles"]').addEventListener('change', function() {
            uploadFiles(this.files);
        }, false);
    };

    var addTextToCKeditor = function(fieldName) {
        CKEDITOR.instances.content.insertText('##'+fieldName+'##');
    };

    var previewTemplate = function() {
        var templateContent = CKEDITOR.instances.content.getData();

        $('#emailPreviewContent').html(templateContent);
        $('#emailPreview').modal('show');

        $('#emailPreview').on('hide.bs.modal', function (e) {
            $('#emailPreviewContent').empty();
        });
    };

    var saveEmailTemplate = function(url) {
        var from_name = $('#from_name').val();
        var from_email = $('#from_email').val();
        var reply_to = $('#reply_to').val();
        var datasetType = $('#datasetType').val();

        if(datasetType == 'campaign') {
            var campaign = $('#campaign').val();
            formData.append('campaign', campaign);
        }
        else if(datasetType == 'custom') {
            formData.append('mapping', JSON.stringify(mapping));
        }

        var name = $('#name').val();
        var subject = $('#subject').val();
        var content = CKEDITOR.instances.content.getData();

        formData.append('from_name', from_name);
        formData.append('from_email', from_email);
        formData.append('reply_to', reply_to);
        formData.append('datasetType', datasetType);
        formData.append('name', name);
        formData.append('subject', subject);
        formData.append('content', content);

        if(advanceFilter != undefined && advanceFilter != '') {
            formData.append('filter', JSON.stringify(advanceFilter));
        }

        formData.append('fileDeleted', JSON.stringify(filesToBeDeleted));
        formData.append('templateType', templateType);

        var thisForm = $('#templateCreateForm');
        $.ajax({
            method: 'post',
            url: url,
            dataType: 'json',
            data: formData,
            contentType : false,
            processData : false,
            beforeSend: function () {
                blockUI('.contentpanel');
                thisForm.find('button[type="submit"]').prop('disabled', true);
            },
            success: function (response) {
                unblockUI('.contentpanel');
                slideToElement(thisForm);
                thisForm.find('button[type="submit"]').prop('disabled', false);

                if(response.status == 'success') {
                    resetFormData();
                    resetDeletedFiles();
                    resetAdvanceFilter();
                    resetMapping();
                    $('#fileDetails').html('');
                    $('#fileError').html('');
                    showSuccess(response.message);

                    if(response.action == 'redirect') {
                        window.location.href = response.url;
                    }
                }
                else if(response.status == 'fail') {
                    if(response.type == 'exception') {
                        showError(response.message);
                    }
                }
            },
            error: function (xhr) {
                unblockUI('.contentpanel');
                slideToElement(thisForm);
                thisForm.find('button[type="submit"]').prop('disabled', false);
                for (var key in xhr.responseJSON) {
                    if (xhr.responseJSON.hasOwnProperty(key)) {
                        var obj = xhr.responseJSON[key];
                        showInputError(key, obj[0]);
                    }
                }
            }
        });
    };

    var detectEmails = function(campaignID) {
        if(templateType == 'edit') {
            var postData = {'campaign' : campaignID, templateID : templateID };
        }
        else {
            var postData = {'campaign' : campaignID};
        }
        $.ajax({
            type: 'post',
            url: detectEmailUrl,
            dataType: "JSON",
            data: postData,
            beforeSend: function() {

            },
            success: function (response) {
                $('#emailCountDiv').html(response.emailText);
                $('#advanceFilterModal').find('.modal-content').html(response.filterText);

				//fill fields
				var formFieldNames = '';
				if(response.formFields.length > 0) {
					for (var i = 0; i < response.formFields.length; i++){
						formFieldNames += '<option value="'+ response.formFields[i] + '">' + response.formFields[i] + '</option>';
					}
				} else {
					formFieldNames += '<option value="">Campaign does not have fields</option>';
				}
				$('#selAddFormFields').empty().append(formFieldNames);

                if(templateType == 'edit') {
                    $('#advanceFilterForm').submit();
                }
            },
            error: function (xhr, textStatus, thrownError) {

            }
        });
    };

    var resetEmailCountDiv = function () {
        $('#emailCountDiv').html('loading...');
        $('#emailCountDiv').addClass('hidden');
    };

    var resetLeadEmailCountDiv = function () {
        $('#leadEmailCountDiv').addClass('hidden');
        $('#leadEmailCountDiv').html('loading...');
    };

    var advanceFilterFunction = function (status, campaign) {
        if(status == 'Started') {
            $('#advanceButton').find('button').attr('rel', campaign);
            $('#advanceButton').removeClass('hidden');
        }
        else {
            $('#advanceButton').addClass('hidden');
        }
    };

    var setTestEmailData = function(key, value) {
        testEmailData[key] = value;
    };

    var getTestEmailData = function() {
        return testEmailData;
    };

    var hideShowFormField = function(action) {
        if(action == 'hide') {
			$('#divFormFields').addClass('hidden');
        }
        else if(action == 'show') {
			$('#divFormFields').removeClass('hidden');
        }
    };

	$('#btnAddFormFieldToTemplate').click(function() {
		var text = $('#selAddFormFields').val();
		if(text !== '') {
			addTextToCKeditor(text);
		}
	});

	$('#btnAddFormFieldToSubject').click(function() {
		var text = $('#selAddFormFields').val();
		if(text !== '') {
			$('#subject').val($('#subject').val() + '##' + text + '##');
		}
	});

    $('#previewTemplate').click(function () {
        previewTemplate();
    });

    $('#templateCreateForm').submit(function (e) {
        e.preventDefault();
        var url = $(this).attr('action');
        saveEmailTemplate(url);
    });

    $('#fileDetails').on('click', '.remove-file', function() {
       removeFile($(this).attr('rel'))
    });

    $('#datasetType').change(function() {
        var datasetType = $(this).val();

        resetEmailCountDiv();
        resetAdvanceFilterModal();
        resetLeadEmailCountDiv();

        $('#campaign').val('');
        var $fileInput = $('#csvFile');
        $fileInput.replaceWith( $fileInput = $fileInput.clone( true ) );

        if(datasetType == '') {
            $('.campaignDiv').addClass('hidden');
            $('.importDiv').addClass('hidden');
            hideShowFormField('show');
        }
        else if(datasetType == 'campaign') {
            $('.campaignDiv').removeClass('hidden');
            $('.importDiv').addClass('hidden');
            hideShowFormField('show');
        }
        else if(datasetType == 'custom') {
            $('.campaignDiv').addClass('hidden');
            $('.importDiv').removeClass('hidden');
            hideShowFormField('hide');
        }
    });
	$('#datasetType').trigger('change');

    $('#campaign').change(function () {
        var campaign = $(this).val();
        var status = $(this).find('option[value='+campaign+']').attr('rel');
        resetEmailCountDiv();
        if(campaign == '') {
            return false;
        }
        detectEmails(campaign);
        $('#emailCountDiv').removeClass('hidden');
        advanceFilterFunction(status, campaign);
    });

    $('#adButton').click( function (e) {
        $('#advanceFilterModal').modal('show');
    });

    $('#advanceFilterModal').on('shown.bs.modal', function () {
        jQuery("#updateFrom").datepicker({
            dateFormat : 'dd-mm-yy'
        });

        jQuery("#updateTo").datepicker({
            dateFormat : 'dd-mm-yy'
        });
    });

    $('#templateCreateForm').on('change', '#csvFile', function () {
        importLeads(this.files);
    });

    $('#testEmailModal').on('shown.bs.modal', function() {
        if($('#testEmails_tagsinput').length == 0) {
            jQuery('#testEmails').tagsInput({width:'auto', height:'auto', defaultText : 'add email'});
        }
    });

    $('#scheduleModal').on('shown.bs.modal', function () {
        var nowDate = new Date();
        var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), nowDate.getHours(), nowDate.getMinutes(), nowDate.getSeconds(), 0);

        $(".form_datetime").datetimepicker({
            autoclose: true,
            format :"dd MM yyyy - hh:ii",
            pickerPosition :"bottom-right",
            startDate : today
        });
    });

    $('#sendTestMessageBtn').click(function () {
        var from_name = $('#from_name').val();
        var from_email = $('#from_email').val();
        var reply_to  = $('#reply_to').val();
        var subject = $('#subject').val();
        var content = CKEDITOR.instances.content.getData();

        if(from_name === undefined || from_name === '') {
            showError('From Name cannot be empty');
            return false;
        }

        if(reply_to === undefined || reply_to === '') {
            showError('Reply To cannot be empty');
            return false;
        }

        if(subject === undefined || subject === '') {
            showError('Subject cannot be empty');
            return false;
        }

        if(content === undefined || content === '') {
            showError('Content cannot be empty');
            return false;
        }

        setTestEmailData('from_name', from_name);
        setTestEmailData('from_email', from_email);
        setTestEmailData('reply_to', reply_to);
        setTestEmailData('subject', subject);
        setTestEmailData('content', content);

        $('#error').html('');

        $('#testEmailModal').modal('show');
    });

    $('#textMessageForm').submit(function(e) {
        e.preventDefault();
        var emails = $('#testEmails').val();

        if(emails == undefined || emails == '') {
            showError('Emails cannot be empty');
            return false;
        }
        setTestEmailData('emails', emails);

        $('#error').html('');

        $.ajax({
            method: 'post',
            url: $(this).attr('action'),
            dataType: 'json',
            data: getTestEmailData(),
            beforeSend: function (xhr) {
                blockUI('#textMessageForm');
            },
            success: function (response) {
                unblockUI('#textMessageForm');

                if(response.isError) {
                    var str = '<div class="alert alert-warning">';
                    var emailResponse = response.emailMessage;
                    for(var i = 0; i < emailResponse.length; i++) {
                        str += '<p>'+emailResponse[i]["email"]+' - '+ emailResponse[i]['message'] +'</p>';
                    }

                    str += '</div>';

                    $('#error').html(str);
                }
                else {
                    showSuccess(response.message);
                    $('#testEmailModal').modal('hide');
                }
            },
            error: function (xhr, textStatus, thrownError) {
                unblockUI('#textMessageForm');
                showError('Something went wrong. Try again!');
            }
        });
    });

    $('.saveTemplate').click(function () {
        hideErrors();
        var btnType = $(this).val();

        if(btnType == 'send') {
            bootbox.confirm({
                title: "Confirm",
                message: "Are you sure you're ready to send?",
                buttons: {
                    'cancel': {
                        label: 'No, Go back a step',
                        className: 'btn-default'
                    },
                    'confirm': {
                        label: 'Send',
                        className: 'btn-primary'
                    }
                },
                callback: function (result) {
                    if(result) {
                        formData.append('btnType', btnType);
                        $('#templateCreateForm').submit();
                    }
                }
            });
        }
        else {
            formData.append('btnType', btnType);
            $('#templateCreateForm').submit();
        }
    });

    $('#scheduleBtn').click(function () {
        $('#scheduleModal').modal('show');
    });

    $('#scheduleModal').on('show.bs.modal', function () {
        hideErrors();
        document.getElementById("scheduleForm").reset();
    });

    $('#scheduleModal').on('submit', '#scheduleForm', function (e) {
        e.preventDefault();

        var scheduleTime =  $('#scheduleTime').val();console.log(scheduleTime);
        if(scheduleTime == '' || scheduleTime == undefined) {
            showInputError('scheduleTime', 'Schedule time is required');
            return false;
        }

        formData.append('btnType', 'schedule');
        formData.append('scheduleTime', scheduleTime);
        $('#scheduleModal').modal('hide');
        $('#templateCreateForm').submit();
    });
	
	//advanced filter for mass 
    $('#advanceFilterModal').on('submit', '#advanceFilterForm', function (e) {
		e.preventDefault();
		var filter = $(this).serializeFormJSON();
		var campaign = $('#campaign').val();
		MassEmail.setAdvanceFilter(filter);
		MassEmail.getCampaignLeadEmailCount(filter, campaign, applyFilterUrl);
		$('#advanceFilterModal').modal('hide');
	});

	
	function chkLastUpdatedLeads_onClick() {
		var txtFromTo = $('#updateFrom, #updateTo');
		txtFromTo.prop('disabled', !this.checked);
		if(!this.checked) {
			txtFromTo
				.val('')
				;
		}
	}

    $('#advanceFilterModal').on('click', '#chkLastUpdatedLeads', function (e) {
		chkLastUpdatedLeads_onClick.call(this);
	});

    $('#advanceFilterModal').on('change', '#selFollowMassEmailTemplate', function (e) {
		var val = $(this).val();
		$('#fldsFollowMassEmailTemplate').prop('disabled', val === '');
		if(val === '') {
			$('#chkFilterOpenedEmails').prop('checked', false);
			$('#chkFilterOpenedEmails').triggerHandler('click');
			$('#chkFilterClickedEmails').prop('checked', false);
			$('#chkFilterClickedEmails').triggerHandler('click');
		}
	});

	function chkFilterOpenedEmails_onClick() {
		var selFilterOpenedEmails = $('#selFilterOpenedEmails');
		selFilterOpenedEmails.prop('disabled', !this.checked);
		if(this.checked) {
			if(selFilterOpenedEmails.val() === '') {
				selFilterOpenedEmails.val('0');
			}
		} else {
			selFilterOpenedEmails
				.val('')
				;
		}
	}

    $('#advanceFilterModal').on('click', '#chkFilterOpenedEmails', function (e) {
		chkFilterOpenedEmails_onClick.call(this);
	});

	function chkFilterClickedEmails_onClick() {
		var selFilterClickedEmails = $('#selFilterClickedEmails');
		selFilterClickedEmails.prop('disabled', !this.checked);
		if(this.checked) {
			if(selFilterClickedEmails.val() === '') {
				selFilterClickedEmails.val('0');
			}
		} else {
			selFilterClickedEmails
				.val('')
				;
		}
	}

    $('#advanceFilterModal').on('click', '#chkFilterClickedEmails', function (e) {
		chkFilterClickedEmails_onClick.call(this);
	});

	$.fn.serializeFormJSON = function () {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function () {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

	function addNewFieldFilterSet() {
		var num = MassEmail.advancedFiltersCount++; 
		var newSet = $('.fieldFilterTemplate')
			.clone()
			.removeClass('fieldFilterTemplate')
			.removeClass('hidden')
			.addClass('fieldFilterSet')
			.attr('data-set-id', num)
			;
		$('#inputFieldFiltersCount').val(MassEmail.advancedFiltersCount);

		newSet.find('.inputFieldFilterType')
			.attr('name', 'inputFieldFilterType_' + num)
			;
		newSet.find('.selFieldFilterField')
			.attr('name', 'selFieldFilterField_' + num)
			.change(function() {
				var parentDiv = $(this).parent().parent();
				var type = $('option:selected', this).attr('data-type');

				var filterSetNumber = parentDiv.attr('data-set-id');		

				parentDiv.find('.inputFieldFilterType').val(type);

				var newHtml = "";
				if(type === '') {
					parentDiv.find('.divFieldFilterValue').html('<input class="form-control " disabled="disabled">');
				} else if(type == 'text') {
					parentDiv.find('.divFieldFilterValue').html('<input name="inputFieldFilterValue_' + filterSetNumber + '" class="form-control inputFieldFilterValue">');
				} else if(type == 'date') {
					newHtml = '<div class="col-sm-6">' + 
						'<div class="col-sm-12 input-group">' + 
						'<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' + 
						'<input title="Starting Date" ' + 
						'name="inputFieldFilterValueFrom_' + filterSetNumber + '" class="form-control customDateField inputFieldFilterValueFrom"></div></div>' +
						'<div class="col-sm-6">' + 
						'<div class="col-sm-12 input-group">' + 
						'<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' + 
						'<input title="Ending Date" ' + 
						'name="inputFieldFilterValueTo_' + filterSetNumber + '" class="form-control customDateField inputFieldFilterValueTo"></div></div>';
					parentDiv.find('.divFieldFilterValue').html(newHtml);
					parentDiv.find('.divFieldFilterValue').find('.customDateField').datepicker({ dateFormat: 'dd-mm-yy' });
				} else if(type == 'dropdown') {
					var values = $('option:selected', this).attr('data-value').split(',');

					newHtml = '<select name="inputFieldFilterValue_' + filterSetNumber + '" class="form-control inputFieldFilterValue selectpicker" multiple><option value="">Select</option>';

					for(var i=0; i<values.length; i++) {
						newHtml += '<option value="'+values[i]+'">'+values[i]+'</option>';
					}
					newHtml += '</select>';

					parentDiv.find('.divFieldFilterValue').html(newHtml);
					parentDiv.find('.divFieldFilterValue').find('.selectpicker').selectpicker();
				}
			});
		$('#divFieldFiltersDynamic').append(newSet);
		
		return newSet;
	}

	function addExistsFieldFilterSet(fieldName, fieldValue, fieldValueFrom, fieldValueTo) {
		var newSet = addNewFieldFilterSet();
		newSet.find('.selFieldFilterField').val(fieldName);
		newSet.find('.selFieldFilterField').trigger('change');
		var type = newSet.find('.inputFieldFilterType').val();
		if(type == 'text') {
			newSet.find('.inputFieldFilterValue').val(fieldValue);
		} else if(type == 'date') {
			newSet.find('.inputFieldFilterValueFrom').val(fieldValueFrom);
			newSet.find('.inputFieldFilterValueTo').val(fieldValueTo);
		} else if(type == 'dropdown') {
			newSet.find('.inputFieldFilterValue').val(fieldValue);
			newSet.find('.inputFieldFilterValue').selectpicker('render');
		}
	}

	function removeFieldFilterSet(buttonObj) {
		var parentDiv = $(buttonObj).parent().parent();
		parentDiv.find('.inputFieldFilterType').val('');
		parentDiv.find('.selFieldFilterField').val('');
		parentDiv.find('.selFieldFilterField').trigger('change');

		var countOfFieldFilters = parentDiv.parent().children().length;
		if(countOfFieldFilters > 1) {
			parentDiv.empty();
			parentDiv.remove();
		}
	}


    return {
		advancedFiltersCount : 0,
		addNewFieldFilterSet : addNewFieldFilterSet,
		removeFieldFilterSet : removeFieldFilterSet,
		addExistsFieldFilterSet : addExistsFieldFilterSet,
		resetMassEmailsAdvanceFilter : null,
		chkLastUpdatedLeads_onClick : chkLastUpdatedLeads_onClick,
		chkFilterOpenedEmails_onClick : chkFilterOpenedEmails_onClick,
		chkFilterClickedEmails_onClick : chkFilterClickedEmails_onClick,
        setApplyFilterUrl : function(url) {
            applyFilterUrl = url;
        },
        init : function() {
            ckeditorInit();
            fileListenerInit();
        },
        setCkeditorPath: function(path) {
            roxyFileMan = path;
        },
        setMaxFileSize: function(size) {
            maxFileSize = size;
        },
        setDetectEmailUrl : function(url) {
            detectEmailUrl = url;
        },
        setAdvanceFilter : function (filter) {
            advanceFilter = filter;
        },
        setCsvFileUrl : function (url) {
            csvFileUrl = url;
        },
        setMapping : function (mapFields) {
            mapping = mapFields;
        },
        getMapping : function () {
            return mapping;
        },
        getCustomLeadEmailCount : function (mapping, url) {
            $.ajax({
                type: 'post',
                url: url,
                dataType: "JSON",
                data: {'mapping' : JSON.stringify(mapping)},
                beforeSend: function() {
                    $('#leadEmailCountDiv').removeClass('hidden').html('loading...');
                },
                success: function (response) {
                    $('#leadEmailCountDiv').html(response.emailText);
                },
                error: function (xhr, textStatus, thrownError) {

                }
            });
        },
        getCampaignLeadEmailCount : function (filter, campaign, url) {
            $.ajax({
                type: 'post',
                url: url,
                dataType: "JSON",
                data: {'filter' : JSON.stringify(advanceFilter), 'campaign' : campaign},
                beforeSend: function() {
                    $('#emailCountDiv').removeClass('hidden').html('loading...');
                },
                success: function (response) {
                    $('#emailCountDiv').html(response.emailText);
					$('#advanceFilterModal').find('.modal-content').html(response.filterText);
                },
                error: function (xhr, textStatus, thrownError) {
                    showError('Something went wrong. Try again!');
                }
            });
        },
        setTemplateType : function (type) {
            templateType = type;
        },
        setCkeditorValue : function(value) {
            CKEDITOR.instances.content.setData(value);
        },
        removeFile : function(fileName) {
            $('#fileDetails').find('p[rel="'+fileName+'"]').remove();
            filesToBeDeleted.push(fileName);
        },
        setDuplicateTemplate : function(value) {
            formData.append('templateID', value);
        },
        setTemplateID : function (value) {
            templateID = value;
        }
    };
}();
