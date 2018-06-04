/*
 * tools for dialog to map landing form fields to lead form fields
 * usage:
 * Landing2LeadFieldsMapping.create(LandingformID);
 * Landing2LeadFieldsMapping.edit(landingFormId, leadFormId);
*/

var Landing2LeadFieldsMapping = {

run : function(url, callbackOnHide)
{
	if($("#mappingModal").length) {
		showError('Something went wrong. #mappingModal already in use');
		return;
	}

	var dlg;

	var showDlg = function(data) {
		$('body').append(data);
		dlg = $("#mappingModal");
		dlg.modal('show');

		dlg.on('hidden.bs.modal', function() {
			dlg.off('hidden.bs.modal');
			dlg.off('click', '#mappingBtn');
			dlg.off('change', '.leadForm');
			dlg.remove(); 
			if(callbackOnHide != null) {
				callbackOnHide();
			}
		});

		dlg.on('change', '.leadForm', function(){
			var val = $(this).val();
			dlg.find('.leadFormFields').find('.fields').addClass('hidden');
			dlg.find('.leadFormFields').find('.fields').prop('selected', false);
			dlg.find('.leadFormFields').find('option[rel="'+val+'_option"]').removeClass('hidden');

			dlg.find('.leadFormFields').find('option[rel="'+val+'_option"]').each(function(){
				if($(this).hasClass('selected')){
					$(this).prop('selected', true);
				}
			});
		});

		dlg.on('click', '#mappingBtn', function(){
			var leadFormID = dlg.find('.leadForm').val();

			if(leadFormID == undefined || leadFormID == ''){
				showError('Select lead form');
				return false;
			}

			var landingFormID = dlg.find('input[name="landingFormID"]').val();
			var mapping = [];

			dlg.find('.fieldDiv').each(function(){
				mapping.push({
					'landingFieldID' : $(this).find('input[name="landingFieldID"]').val(),
					'leadFormFieldID' : $(this).find('select[name="leadFormFieldID"]').val()
				});
			});

			var formData = {
				'leadFormID' : leadFormID,
				'mapping' : mapping,
				'landingFormID' : landingFormID
			};

			blockUI(dlg);

			$.ajax({
				type: 'post',
				url: setMappingUrl,
				cache: false,
				data: formData,
				dataType: 'json',
				success: function(response) {
					unblockUI(dlg);
					dlg.modal('hide');
					showSuccess('Form Fields has been mapped');
				},
				error: function(xhr, textStatus, thrownError) {
					unblockUI(dlg);
					showError('Something went wrong. Please try again later!');
				}
			});
		});
	};

	$.get(url,showDlg)
		.fail(function( jqXHR, textStatus ) {
			showError("Request failed: " + textStatus);
		});
}
,
create : function(landingFormId, callbackOnHide) 
{
    if(landingFormId == ''){
        showError('landingFormId is required');
        return;
    }

	if (typeof mappingInfoUrl === 'undefined' || mappingInfoUrl == '')	{
        showError('mappingInfoUrl is required');
        return;
	}

    var url = mappingInfoUrl+'?form='+landingFormId;

	this.run(url, callbackOnHide);

}
,
edit : function(landingFormId, leadFormId, callbackOnHide) 
{
    if(landingFormId == ''){
        showError('landingFormId is required');
        return;
    }
    
	if(leadFormId == ''){
        showError('leadFormId is required');
        return;
    }

	if (typeof mappingInfoUrl === 'undefined' || mappingInfoUrl == '')	{
        showError('mappingInfoUrl is required');
        return;
	}

    var url = mappingInfoUrl+'?form='+landingFormId + '&leadFormId=' + leadFormId;

	this.run(url, callbackOnHide);

}

}

