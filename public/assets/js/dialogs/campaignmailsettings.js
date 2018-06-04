//helper functions for resources/views/site/campaigns/campaignEmailSettings.blade.php

var campaignEmailSettings = function() 
{
	"use strict";

	var emailVerified = false;

	function fnReset() {
		emailVerified = false;
	}

	function fnValidate() {
		var setting = $('input[name=setting]:checked').val();
		var smptSetting = $('#smtpSetting').prop('checked');
		
		if(setting == 'advanced'){
			var fromEmail = $('#fromEmail').val();
			var replyToEmail = $('#replyEmail').val();

			if(fromEmail === ''){
				showFieldError('Please Enter From Email', 'fromEmail');
				return false;
			}
			else if(replyToEmail === ''){
				showFieldError('Please Enter Reply to Email', 'replyEmail');
				return false;
			}

			if(smptSetting){
				var host = $('#host').val();
				var port = $('#port').val();
				var username = $('#username').val();
				var password = $('#password').val();

				if(host === ''){
					showFieldError('Please Enter Host', 'host');
					return false;
				}
				else if(port === ''){
					showFieldError('Please Enter Port', 'port');
					return false;
				}
				else if(username === ''){
					showFieldError('Please Enter username', 'username');
					return false;
				}
				else if(password === ''){
					showFieldError('Please Enter Password', 'password');
					return false;
				}
				else if(!emailVerified){
					showError('Please verify email settings');
					return false;
				}
			}
		}

		return true;
	}

	function fnFillFormData(formData) {
		var setting = $('input[name=setting]:checked').val();
		formData.setting = setting;

		if(setting == 'advanced'){
			var fromEmail = $('#fromEmail').val();
			var replyToEmail = $('#replyEmail').val();

			formData.fromEmail = fromEmail;
			formData.replyToEmail = replyToEmail;

			var smptSetting = $('#smtpSetting').prop('checked');
			formData.smtpSetting = smptSetting;

			if(smptSetting){
				var host = $('#host').val();
				var port = $('#port').val();
				var username = $('#username').val();
				var password = $('#password').val();
				var security = $('#security').val();

				formData.host = host;
				formData.port = port;
				formData.username = username;
				formData.password = password;
				formData.security = security;
			}
		}

		return formData;
	}

	function fnVeryfyEmailSettings(url, callback) 
	{
		var host = $('#host').val();
		var port = $('#port').val();
		var username = $('#username').val();
		var password = $('#password').val();

		var fromEmail = $('#fromEmail').val();
		var replyToEmail = $('#replyEmail').val();
		var security = $('#security').val();

		var ajaxCall = false;
		if(fromEmail === ''){
			showFieldError('Please Enter From Email', 'fromEmail');
		}
		else if(replyToEmail === ''){
			showFieldError('Please Enter Reply to Email', 'replyEmail');
		}
		else if(host === ''){
			showFieldError('Please Enter Host', 'host');
		}
		else if(port === ''){
			showFieldError('Please Enter Port', 'port');
		}
		else if(username === ''){
			showFieldError('Please Enter username', 'username');
		}
		else if(password === ''){
			showFieldError('Please Enter Password', 'password');
		}
		else {
			ajaxCall = true;
			$.post(
					url, 
					{
						'fromEmail' : fromEmail ,
						'replyToEmail' : replyToEmail , 
						'host' : host , 
						'port' : port , 
						'username' : username , 
						'password' : password , 
						'security' : security
					}, 
					function(response){
						callback();
						if(response.status == 'error'){
							emailVerified = false;
							showError(response.message);
							showFieldError(response.message, 'smtpErrorLabel');
						}
						else{
							emailVerified = true;
							showSuccess(response.message);
						}
					} , 
				'json'
			)
			.fail(function( jqXHR, textStatus ) {
				showError("Request failed: " + textStatus);
				showFieldError("Request failed: " + textStatus, 'smtpErrorLabel');
				callback();
			});
		}
		if(!ajaxCall) {
			callback();
		}
	}
	
	function fnSwitchDefaultSettings()
	{
		var val = $('input[name=setting]:checked').val();

		if(val == 'advanced'){
			$('#advancedDiv').removeClass('hidden');
		}
		else{
			$('#advancedDiv').addClass('hidden');
		}
		return false;
	}

	function fnSwitchSmtpSettings()
	{
		if($('#smtpSetting').prop('checked')){
			$('#smtpDiv').removeClass('hidden');
		}
		else{
			$('#smtpDiv').addClass('hidden');
		}
	}

	return {
		reset : fnReset,
		validate : fnValidate,
		fillFormData : fnFillFormData,
		veryfyEmailSettings : fnVeryfyEmailSettings,
		switchDefaultSettings : fnSwitchDefaultSettings,
		switchSmtpSettings : fnSwitchSmtpSettings
	};
}();
