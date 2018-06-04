/*
 * tools for dialogs to move leads from one campaign to another
 * usage:
*/

var moveLeads2Campaign = function() 
{
	"use strict";

	var urlMoveDlg = '';
	var urlMappingDlg = '';
	var urlSetMapping = '';

	var callbackOnHide;
	var callbackOnSuccess;

	var sourceCampaignId;
	var targetCampaignId;

	function fnInit(pUrlMoveDlg, pUrlMappingDlg, pUrlSetMapping, pCallbackOnHide, pCallbackOnSuccess) {
		urlMoveDlg = pUrlMoveDlg;
		urlMappingDlg = pUrlMappingDlg;
		callbackOnHide = pCallbackOnHide;
		urlSetMapping = pUrlSetMapping;
		callbackOnSuccess = pCallbackOnSuccess;
	}


	function fnShowInitDlg(data) {
		var dlg;
		$('body').append(data);
		dlg = $("#moveLeadsModal");
		dlg.modal('show');
		
		dlg.on('hidden.bs.modal', function() {
			dlg.off('hidden.bs.modal');
			dlg.off('click', '#btnMoveLeads');
			dlg.off('change', '#selMoveLeadsTargetCampaign');
			dlg.remove();
			if(callbackOnHide !== null) {
				callbackOnHide();
			}
		});

		dlg.on('change', '#selMoveLeadsTargetCampaign', function(){
			targetCampaignId = $(this).val();
			dlg.find('.divFieldMapping').html('');
			dlg.find('#btnMoveLeads')
				.prop('disabled', true)
				.addClass('hidden');

			if(targetCampaignId !== '') {
				var url = urlMappingDlg+'?sourceCampaignID='+sourceCampaignId+'&targetCampaignID=' + targetCampaignId;
				$.get(url, function(data) {
						dlg.find('.divFieldMapping').html(data);
						dlg.find('#btnMoveLeads')
							.prop('disabled', false)
							.removeClass('hidden');
					}
				)
				.fail(function( jqXHR, textStatus ) {
					showError("Request failed: " + textStatus);
				});
			}
		});
		
		dlg.on('click', '#btnMoveLeads', function(){
			if(targetCampaignId === '') {
				showError("Target campaign not set");
				return;
			}

			dlg.find(".divMoveError").html('');
			
			var mapping = [];

			var isError = false;
			dlg.find('.fieldDiv').each(function(){
				if(isError) {
					return;
				}
				var isFieldUnique = $(this).find('input[name="uniqueSourceField"]:checked').length !== 0;
				var targedFieldId = $(this).find('select[name="targetFieldID"]').val();
				if(isFieldUnique && targedFieldId === '') {
					var fieldName = $(this).find('.sourceFieldName').html();
					var errorMessage = 'Field ' + fieldName + ' is part of unique key and should be mapped to field from target campaign';
					showError(errorMessage);
					dlg.find(".divMoveError").html('<div class="alert alert-danger">' + errorMessage + '</div>');
					isError = true;
					return;
				}
				mapping.push({
					'sourceFieldID' : $(this).find('input[name="sourceFieldID"]').val(),
					'targetFieldID' : targedFieldId,
					'isFieldUnique' : isFieldUnique
				});
			});

			if(isError) {
				return;
			}

			var leads = [];
			$('#tableDiv').find('.checkBox:checked').each(function(){
				var id = $(this).attr('id');
				leads.push(id.replace('check_', ''));
			});

			var formData = {
				'sourceCampaignID' : sourceCampaignId,
				'targetCampaignID' : targetCampaignId,
				'mapping' : mapping
				, 'duplicates' : dlg.find('#selDuplicates').val()
				, 'move' : dlg.find('#selMove').val()
				, 'leads' : leads
				, 'includeNotes' : dlg.find('input[name="chkIncludeNotes"]:checked').length !== 0
			};

			blockUI(dlg);

			$.ajax({
				type: 'post',
				url: urlSetMapping,
				cache: false,
				data: formData,
				dataType: 'json',
				success: function(response) {
					unblockUI(dlg);
					if(typeof response.status != "undefined") {
						if(response.status == "success") {
							dlg.modal('hide');
							showSuccess(response.message);
							if(callbackOnSuccess !== null) {
								callbackOnSuccess();
							}
							bootbox.dialog({
								title: 'Leads transferred',
								message: response.message,
								buttons: {
									success: {
										label: "OK",
										className: "btn-success center-block"
									}
								}
							});
						} else {
							showError(response.message);
							dlg.find(".divMoveError").html('<div class="alert alert-danger">' + response.message + '</div>');
						}
					} else {
						showError('Something went wrong. Please try again later!');
						dlg.find(".divMoveError").html('<div class="alert alert-danger">Something went wrong. Please try again later!</div>');
					}
				},
				error: function(xhr, textStatus, thrownError) {
					unblockUI(dlg);
					showError('Something went wrong. Please try again later!');
					dlg.find(".divMoveError").html('<div class="alert alert-danger">Something went wrong. Please try again later!</div>');
				}
			});

		});

	}

	function fnStart(campaignID) {
		sourceCampaignId = campaignID;
		if(sourceCampaignId === '') {
			showError("Source campaign not set");
			return;
		}

		var url = urlMoveDlg+'?campaignID='+campaignID;

		$.get(url, fnShowInitDlg)
			.fail(function( jqXHR, textStatus ) {
				showError("Request failed: " + textStatus);
			});
	}

	return {
		init : fnInit
		, start : fnStart
	};
}();

