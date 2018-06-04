<?php

/** Admin Routes **/


Route::get('admin', ['as' => 'admin', 'uses' => 'Admin\AdminController@index']);
Route::get('admin/login', ['as' => 'admin.login', 'uses' => 'Admin\AuthController@login']);
Route::post('admin/login/check', ['as' => 'admin.login.check', 'uses' => 'Admin\AuthController@checkLogin']);
Route::get('admin/logout', array('as' => 'admin.logout', 'uses' => 'Admin\AuthController@logout'));

Route::match(['get', 'post'], 'landing/{campaign}/{landingForm}', array('as' => 'landing.signup', 'uses' => 'CampaignsUserController@landingSignUp'));
Route::match(['get', 'post'], 'landing-html-api/{campaign}/{landingForm}', array('as' => 'landing.api.html', 'uses' => 'CampaignsUserController@landingFormHtmlApi'));

Route::group(["middleware" => 'admin', 'prefix' => 'admin'], function() {
    Route::get('dashboard', ['as' => 'admin.dashboard', 'uses' => 'Admin\AdminController@dashboard']);
    Route::post('conversionratio', ['as' => 'admin.dashboard.conversionratio', 'uses' => 'Admin\AdminController@getConversionRatio']);
    Route::get('data', ['as' => 'admin.dashboard.data', 'uses' => 'Admin\AdminController@dataActivity']);
    Route::resource('users', 'Admin\AdminUsersController', ['except' => ['ajaxusers', 'show', 'createUser']]);
    Route::get('users', ["as" => "admin.users", 'uses' => 'Admin\AdminUsersController@index']);
    Route::get('users/ajaxusers', ['as' => 'admin.users.ajaxusers', 'uses' => 'Admin\AdminUsersController@ajaxusers']);
    Route::get('users/ajaxusers/{action}', ['as' => 'admin.users.ajaxusers', 'uses' => 'Admin\AdminUsersController@ajaxusers']);
    Route::post('users/createuser', ['as' => 'admin.users.createuser', 'uses' => 'Admin\AdminUsersController@createUser']);
    Route::get('users/edituser/{userID}', ['as' => 'admin.users.edituser', 'uses' => 'Admin\AdminUsersController@editUser']);
    Route::post('users/updateuser/{userID}', ['as' => 'admin.users.updateuser', 'uses' => 'Admin\AdminUsersController@updateUser']);
    Route::post('users/deleteuser', ['as' => 'admin.users.deleteuser', 'uses' => 'Admin\AdminUsersController@deleteUser']);

    Route::get('users/exportuserdata', ['as' => 'admin.users.exportuserdata', 'uses' => 'Admin\AdminUsersController@exportUserData']);
    Route::post('users/ajaxexportuserdatatable', ['as' => 'admin.users.ajaxexportuserdatatable', 'uses' => 'Admin\AdminUsersController@ajaxExportUserDataTable']);
    Route::get('users/exportuserdatatozip', ['as' => 'admin.users.exportuserdatatozip', 'uses' => 'Admin\AdminUsersController@exportUserDataToZip']);

    Route::resource('licenses', 'Admin\AdminLicensesController', ['except' => ['show', 'searchTransactionResults']]);
    Route::get('licenses/searchtransactions', ['as' => 'admin.licenses.searchtransactions', 'uses' => 'Admin\AdminLicensesController@searchTransactions']);
    Route::get('licenses/searchtransactionresults', ['as' => 'admin.licenses.searchtransactionresults', 'uses' => 'Admin\AdminLicensesController@searchTransactionResults']);
    Route::get('licenses/downloadtransactions', ['as' => 'admin.licenses.downloadtransactions', 'uses' => 'Admin\AdminLicensesController@downloadTransactions']);

    Route::get('licenses/shoppingcart', ['as' => 'admin.licenses.shoppingcart', 'uses' => 'Admin\AdminLicensesController@shoppingCart']);
    Route::post('licenses/shoppingcart/{licenseTypeID}', ['as' => 'admin.licenses.shoppingcart.edit', 'uses' => 'Admin\AdminLicensesController@shoppingCartForm']);
    Route::post('licenses/updateshoppingcart/{licenseTypeID}', ['as' => 'admin.licenses.updateshoppingcart', 'uses' => 'Admin\AdminLicensesController@updateShoppingCart']);
    Route::post('licenses/addonememeberprice', ['as' => 'admin.licenses.addonememeberprice', 'uses' => 'Admin\AdminLicensesController@addOneMemberPrice']);

    Route::post('trial/settings', ['as' => 'admin.trial.settings', 'uses' => 'Admin\AdminLicensesController@trialSettings']);

    Route::get('setting', ['as' => 'admin.setting', 'uses' => 'Admin\AdminController@setting']);
	Route::get('mailsettings', ['as' => 'admin.mailsettings', 'uses' => 'Admin\AdminController@mailSettings']);
	Route::get('massmailsettings', ['as' => 'admin.mass.mailsettings', 'uses' => 'Admin\AdminController@massMailSettings']);
	Route::post('savemassmailsettings', ['as' => 'admin.mass.savemailsettings', 'uses' => 'Admin\AdminController@saveMassMailSettings']);
	Route::post('saveMassMailLimitation', ['as' => 'admin.mass.saveMailLimitation', 'uses' => 'Admin\AdminController@saveMassMailLimitationSettings']);
	Route::post('savemailsettings', ['as' => 'admin.savemailsettings', 'uses' => 'Admin\AdminController@saveMailSettings']);
    Route::post('setting/savesetting', ['as' => 'admin.setting.savesetting', 'uses' => 'Admin\AdminController@saveSetting']);

    Route::get('forms/createoredit', ['as' => 'admin.forms.createoredit', 'uses' => 'Admin\AdminFormsController@createOrEdit']);
    Route::post('forms/formfieldsdetails', ['as' => 'admin.forms.formfieldsdetails', 'uses' => 'Admin\AdminFormsController@formFieldsDetails']);
    Route::post('forms/addoreditform', ['as' => 'admin.forms.addoreditform', 'uses' => 'Admin\AdminFormsController@addOrEditForm']);
    Route::post('forms/deleteform', ['as' => 'admin.forms.deleteform', 'uses' => 'Admin\AdminFormsController@deleteForm']);
    Route::get('forms/showdemoform', ['as' => 'admin.forms.showdemoform', 'uses' => 'Admin\AdminFormsController@showDemoForm']);

    Route::get('emailtemplates', ['as' => 'admin.emailtemplates', 'uses' => 'Admin\AdminController@emailTemplates']);
    Route::post('ajaxemailtemplatedetails', ['as' => 'admin.ajaxemailtemplatedetails', 'uses' => 'Admin\AdminController@ajaxEmailTemplateDetails']);
    Route::post('updateemailtemplate', ['as' => 'admin.updateemailtemplate', 'uses' => 'Admin\AdminController@updateEmailTemplate']);

    Route::get('helptopics/{postedTopicID?}', ['as' => 'admin.helptopics', 'uses' => 'Admin\AdminHelpTopicsController@helpTopics']);
    Route::post('helptopics/addnewtopic', ['as' => 'admin.helptopics.addnewtopic', 'uses' => 'Admin\AdminHelpTopicsController@addNewTopic']);
    Route::post('helptopics/ajaxtopicarticle', ['as' => 'admin.helptopics.ajaxtopicarticle', 'uses' => 'Admin\AdminHelpTopicsController@ajaxTopicArticle']);
    Route::get('helptopics/addoreditarticle/{action}/{id?}', ['as' => 'admin.helptopics.addoreditarticle', 'uses' => 'Admin\AdminHelpTopicsController@addOrEditArticle']);
    Route::post('helptopics/addnewarticle', ['as' => 'admin.helptopics.addnewarticle', 'uses' => 'Admin\AdminHelpTopicsController@addNewArticle']);
    Route::post('helptopics/editarticle', ['as' => 'admin.helptopics.editarticle', 'uses' => 'Admin\AdminHelpTopicsController@editArticle']);
    Route::post('helptopics/deletearticle', ['as' => 'admin.helptopics.deletearticle', 'uses' => 'Admin\AdminHelpTopicsController@deleteArticle']);
    Route::post('helptopics/deletetopic', ['as' => 'admin.helptopics.deletetopic', 'uses' => 'Admin\AdminHelpTopicsController@deleteTopic']);

    Route::get('pushmessage', ['as' => 'admin.pushmessage', 'uses' => 'Admin\AdminController@pushMessage']);
    Route::post('pushmessage/generatemessage', ['as' => 'admin.pushmessage.generatemessage', 'uses' => 'Admin\AdminController@generateMessage']);
});


/** User Routes **/

// We keep login routes outside the group so that we can apply auth check to rest of the group

Route::get('user/login', array('as' => 'user.login', 'uses' => 'AuthController@login'));
Route::post('user/login/check', array('as' => 'user.login.check', 'uses' => 'AuthController@checkLogin'));
Route::post('user/resetpasswordemail', array('as' => 'user.resetpasswordemail', 'uses' => 'UserController@resetPasswordEmail'));
Route::get('user/logout', array('as' => 'user.logout', 'uses' => 'AuthController@logout'));

Route::get('statistics/share/{token}', array('as' => 'statistics.share', 'uses' => 'StatisticsUserController@share'));
Route::post('statistics/share/showcallvolume', array('as' => 'statistics.share.showcallvolume', 'uses' => 'StatisticsUserController@showCallVolume'));
Route::post('statistics/share/teamperformance', array('as' => 'statistics.share.teamperformance', 'uses' => 'StatisticsUserController@showTeamPerformance'));
Route::post('statistics/share/showCustomCallVolume', array('as' => 'statistics.share.showCustomCallVolume', 'uses' => 'StatisticsUserController@showCustomCallVolume'));

Route::group(array('middleware' => "auth", 'prefix' => 'user'), function()
{
	Route::get('/', ['as' => 'user', 'uses' => 'UserController@index', ['except' => ['show', 'editProfile']]]);
    Route::get('dashboard', array('as' => 'user.dashboard', 'uses' => 'UserController@dashboard'));
    Route::post('dashboard/ajaxtodayperformance', array('as' => 'user.dashboard.ajaxtodayperformance', 'uses' => 'UserController@ajaxTodayPerformance'));
    Route::post('dashboard/ajaxteamperformance', array('as' => 'user.dashboard.ajaxteamperformance', 'uses' => 'UserController@ajaxTeamPerformance'));
    Route::post('dashboard/ajaxinterestedandnotinterested', array('as' => 'user.dashboard.ajaxinterestedandnotinterested', 'uses' => 'UserController@ajaxInterestedAndNotInterested'));
    Route::post('dashboard/donetodo', array('as' => 'user.dashboard.donetodo', 'uses' => 'UserController@doneTodo'));
    Route::post('dashboard/createtodo', array('as' => 'user.dashboard.createtodo', 'uses' => 'UserController@createTodo'));

    Route::get('profile', array('as' => 'user.profile', 'uses' => 'UserController@profile'));
    Route::post('changepassword', array('as' => 'user.changepassword', 'uses' => 'UserController@changePassword'));
    Route::post('resendverificationlink', array('as' => 'user.resendverificatiolink', 'uses' => 'UserController@resendVerificationLink'));

    Route::post('editprofile', array('as' => 'user.editprofile', 'uses' => 'UserController@editProfile'));
    Route::post('defaultemailsettingtoyes', array('as' => 'user.defaultemailsettingtoyes', 'uses' => 'UserController@defaultEmailSettingToYes'));
    Route::post('saveandcheckemailsetting', array('as' => 'user.saveandcheckemailsetting', 'uses' => 'UserController@saveAndCheckEmailSetting'));
	Route::post('dialerSetting', array('as' => 'user.dialerSetting', 'uses' => 'UserController@updateDialerSetting'));
	Route::post('enableTimer', ['as' => 'user.enableTimer', 'uses' => 'UserController@enableTimer']);
	Route::post('account/close', ['as' => 'user.account.close', 'uses' => 'UserController@accountClose']);
	Route::post('profile/addbcc', ['as' => 'user.profile.bcc', 'uses' => 'UserController@addBccEmail']);
	Route::get('payment-details', ['as' => 'user.paymentdetail.edit', 'uses' => 'UserController@editPaymentDetail']);
	Route::post('payment-details', ['as' => 'user.paymentdetail.update', 'uses' => 'UserController@updatePaymentDetail']);
	Route::get('team/upgrade', ['as' => 'user.team.upgrade', 'uses' => 'UserController@upgradeTeamMember']);
	Route::get('payment/resubscribe', ['as' => 'user.payment.resubscribe', 'uses' => 'UserController@resubscribe']);

    Route::post('forms/lead/dontshowinfodlg', array('as' => 'user.forms.lead.dontshowinfodlg', 'uses' => 'UserController@leadFormsDontShowInfoDlg'));

    Route::resource('campaigns', 'CampaignsUserController', array('except' => array('show')));
    Route::get('campaigns/start', array('as' => 'user.campaigns.start', 'uses' => 'CampaignsUserController@start'));
    Route::post('campaigns/startcampaign', array('as' => 'user.campaigns.startcampaign', 'uses' => 'CampaignsUserController@startCampaign'));
    Route::post('campaigns/endcampaign', array('as' => 'user.campaigns.endcampaign', 'uses' => 'CampaignsUserController@endCampaign'));
    Route::post('campaigns/deleteCampaignData', array('as' => 'user.campaigns.deleteCampaignData', 'uses' => 'CampaignsUserController@deleteCampaignData'));
    Route::post('campaigns/renameCampaign', array('as' => 'user.campaigns.renameCampaign', 'uses' => 'CampaignsUserController@renameCampaign'));
    Route::get('campaigns/createcampaign', array('as' => 'user.campaigns.create', 'uses' => 'CampaignsUserController@createCampaign'));
    Route::post('campaigns/savecampaign', array('as' => 'user.campaigns.save', 'uses' => 'CampaignsUserController@saveCampaign'));
    Route::post('campaigns/savewithoutcsv', array('as' => 'user.campaigns.savewithoutcsv', 'uses' => 'CampaignsUserController@saveCampaignWithoutCsv'));
    Route::get('campaigns/step3', array('as' => 'user.campaigns.step3', 'uses' => 'CampaignsUserController@step3'));
    Route::post('campaigns/step4', array('as' => 'user.campaigns.step4', 'uses' => 'CampaignsUserController@step4'));
    Route::post('campaigns/checkstep4status', array('as' => 'user.campaigns.checkstep4status', 'uses' => 'CampaignsUserController@checkStep4Status'));
    Route::get('campaigns/step5', array('as' => 'user.campaigns.step5', 'uses' => 'CampaignsUserController@step5'));
    Route::post('campaigns/cancelimportdata', array('as' => 'user.campaigns.cancelimportdata', 'uses' => 'CampaignsUserController@cancelImportData'));

    Route::get('campaigns/importcampaignleads/{campaignID?}', array('as' => 'user.campaigns.importcampaignleads', 'uses' => 'CampaignsUserController@importCampaignLeads'));
    Route::post('campaigns/importcampaigndatatocsv', array('as' => 'user.campaigns.importcampaigndatatocsv', 'uses' => 'CampaignsUserController@importCampaignDataToCsv'));
    Route::get('campaigns/importcampaigndatatocsv', array('as' => 'user.campaigns.importcampaigndatatocsv', 'uses' => 'CampaignsUserController@importCampaignDataToCsv'));
    Route::get('campaigns/listcampaigns', array('as' => 'user.campaigns.listcampaigns', 'uses' => 'CampaignsUserController@listCampaigns'));
    Route::post('campaigns/ajaxlistcampagins', array('as' => 'user.campaigns.ajaxlistcampagins', 'uses' => 'CampaignsUserController@ajaxListCampagins'));
    Route::get('campaigns/exportcampaignsdata', array('as' => 'user.campaigns.exportcampaignsdata', 'uses' => 'CampaignsUserController@exportCampaignsData'));

    Route::post('campaigns/ajaxcampaignmembers', array('as' => 'user.campaigns.ajaxcampaignmembers', 'uses' => 'CampaignsUserController@ajaxCampaignMembers'));
    Route::post('campaigns/addorremoveuserfromcampaign', array('as' => 'user.campaigns.addorremoveuserfromcampaign', 'uses' => 'CampaignsUserController@addOrRemoveUserFromCampaign'));

	Route::get('campaigns/leads', array('as' => 'user.campaigns.leads', 'uses' => 'CampaignsUserController@leads'));
	Route::get('campaigns/getLeadData', array('as' => 'user.campaigns.getLeadData', 'uses' => 'CampaignsUserController@getLeadData'));
	Route::get('campaigns/leadActions', array('as' => 'user.campaigns.leadActions', 'uses' => 'CampaignsUserController@leadActions'));

	Route::post('campaigns/getActiveCampaign', array('as' => 'user.campaigns.getActiveCampaign', 'uses' => 'CampaignsUserController@getActiveCampaign'));
	Route::post('campaigns/addToArchive', array('as' => 'user.campaigns.addToArchive', 'uses' => 'CampaignsUserController@addToArchive'));
	Route::post('campaigns/getFinishedCampaign', array('as' => 'user.campaigns.getFinishedCampaign', 'uses' => 'CampaignsUserController@getFinishedCampaign'));
	Route::post('campaigns/getArchivedCampaign', array('as' => 'user.campaigns.getArchivedCampaign', 'uses' => 'CampaignsUserController@getArchivedCampaign'));
	Route::post('campaigns/removeArchive', array('as' => 'user.campaigns.removeArchive', 'uses' => 'CampaignsUserController@removeArchive'));

	Route::post('campaigns/verifyEmailSetting', array('as' => 'user.campaigns.verifyEmailSetting', 'uses' => 'CampaignsUserController@verifyEmailSetting'));
	Route::post('campaigns/getLandingPageUrl', array('as' => 'user.campaigns.getLandingPageUrl', 'uses' => 'CampaignsUserController@getLandingPageUrl'));
	Route::get('campaigns/getInformation', array('as' => 'user.campaigns.getInformation', 'uses' => 'CampaignsUserController@getCampaignInformation'));
    Route::post('campaigns/assignLandingForm', array('as' => 'user.campaigns.assignLandingForm', 'uses' => 'CampaignsUserController@assignLandingForm'));
	Route::get('campaigns/getMailSettingsDialog', array('as' => 'user.campaigns.getMailSettingsDialog', 'uses' => 'CampaignsUserController@getMailSettingsDialog'));
	Route::post('campaigns/setMailSettings', array('as' => 'user.campaigns.setMailSettings', 'uses' => 'CampaignsUserController@setMailSettings'));
	Route::get('campaigns/getAddNewLeadDialog', array('as' => 'user.campaigns.getAddNewLeadDialog', 'uses' => 'CampaignsUserController@getAddNewLeadDialog'));
	Route::get('campaigns/getPrevNextLeadFilterDialog', array('as' => 'user.campaigns.getPrevNextLeadFilterDialog', 'uses' => 'CampaignsUserController@getPrevNextLeadFilterDialog'));
	Route::post('campaigns/setPrevNextLeadFilterDialog', array('as' => 'user.campaigns.setPrevNextLeadFilterDialog', 'uses' => 'CampaignsUserController@setPrevNextLeadFilterDialog'));

    Route::resource('leads', 'LeadsUserController', array('except' => array('show')));
    Route::get('leads/createlead/{leadID?}/{edit?}', array('as' => 'user.leads.createlead', 'uses' => 'LeadsUserController@createLead'));
    Route::get('leads/createleadforcampaign/{campaignId?}', array('as' => 'user.leads.createleadforcampaign', 'uses' => 'LeadsUserController@createNewLeadForCampaign'));
    Route::post('leads/userstotalpendingcallbacks', array('as' => 'user.leads.userstotalpendingcallbacks', 'uses' => 'LeadsUserController@usersTotalPendingCallBacks'));
    Route::post('leads/selectedcampaignlead', array('as' => 'user.leads.selectedcampaignlead', 'uses' => 'LeadsUserController@selectedCampaignLead'));
    Route::post('leads/saveleadformdata', array('as' => 'user.leads.saveleadformdata', 'uses' => 'LeadsUserController@saveLeadFormdata'));
    Route::post('leads/bookthissalesman', array('as' => 'user.leads.bookthissalesman', 'uses' => 'LeadsUserController@bookThisSalesman'));
    Route::post('leads/bookappointmentstatusno', array('as' => 'user.leads.bookappointmentstatusno', 'uses' => 'LeadsUserController@bookAppointmentStatusNo'));
    Route::post('leads/changeleadinterest', array('as' => 'user.leads.changeleadinterest', 'uses' => 'LeadsUserController@changeLeadInterest'));
    Route::post('leads/changeemailtemplate', array('as' => 'user.leads.changeemailtemplate', 'uses' => 'LeadsUserController@changeEmailTemplate'));
    Route::post('leads/savereferencenumber', array('as' => 'user.leads.savereferencenumber', 'uses' => 'LeadsUserController@saveReferenceNumber'));
    Route::post('leads/removefollowupcall', array('as' => 'user.leads.removefollowupcall', 'uses' => 'LeadsUserController@removeFollowUpCall'));
    Route::post('leads/followupcall', array('as' => 'user.leads.followupcall', 'uses' => 'LeadsUserController@followUpCall'));
    Route::post('leads/gotoaction', array('as' => 'user.leads.gotoaction', 'uses' => 'LeadsUserController@goToAction'));
    Route::post('leads/skipandelete', array('as' => 'user.leads.skipandelete', 'uses' => 'LeadsUserController@skipAnDelete'));
    Route::post('leads/saveleadtimetaken', array('as' => 'user.leads.saveleadtimetaken', 'uses' => 'LeadsUserController@saveleadTimeTaken'));
    Route::post('leads/saveandexit', array('as' => 'user.leads.saveandexit', 'uses' => 'LeadsUserController@saveAndExit'));
    Route::post('leads/sendnewemail', array('as' => 'user.leads.sendnewemail', 'uses' => 'LeadsUserController@sendNewEmail'));
    Route::post('leads/getemailtemplatedata', array('as' => 'user.leads.getemailtemplatedata', 'uses' => 'LeadsUserController@getEmailTemplateData'));

    Route::get('leads/pendingcallbacks', array('as' => 'user.leads.pendingcallbacks', 'uses' => 'LeadsUserController@pendingCallBacks'));
    Route::post('leads/ajaxpendingdaysdata', array('as' => 'user.leads.ajaxpendingdaysdata', 'uses' => 'LeadsUserController@ajaxPendingDaysData'));
    Route::post('leads/openpendingcallbacklead', array('as' => 'user.leads.openpendingcallbacklead', 'uses' => 'LeadsUserController@openPendingCallBackLead'));
    Route::post('leads/cancelcallback', array('as' => 'user.leads.cancelcallback', 'uses' => 'LeadsUserController@cancelCallBack'));
    Route::post('leads/followupcallsbulkaction', array('as' => 'user.leads.followupcallsbulkaction', 'uses' => 'LeadsUserController@followUpCallBackBulkAction'));
    Route::get('leads/viewlead/{leadID?}', array('as' => 'user.leads.viewlead', 'uses' => 'LeadsUserController@viewLead'));
    Route::post('leads/deletelead', array('as' => 'user.leads.deletelead', 'uses' => 'LeadsUserController@deleteLead'));
    Route::get('leads/inboundcall', array('as' => 'user.leads.inboundcall', 'uses' => 'LeadsUserController@inBoundCall'));
    Route::post('leads/showcampaignformfield', array('as' => 'user.leads.showcampaignformfield', 'uses' => 'LeadsUserController@showCampaignFormField'));
	/* unused
    Route::get('leads/ajaxinboundcalldata', array('as' => 'user.leads.ajaxinboundcalldata', 'uses' => 'LeadsUserController@ajaxInboundCallData'));
	 */
    Route::post('leads/newinboundleadforcampaign', array('as' => 'user.leads.newinboundleadforcampaign', 'uses' => 'LeadsUserController@newInboundLeadForCampaign'));
	Route::post('leads/leads_page_skip_auto', array('as' => 'user.leads.leads_page_skip_auto', 'uses' => 'LeadsUserController@leadsPageSkipAuto'));

    Route::get('leads/appointments', array('as' => 'user.leads.appointments', 'uses' => 'LeadsUserController@appointments'));
    Route::post('leads/calanderappointmentdata', array('as' => 'user.leads.calanderappointmentdata', 'uses' => 'LeadsUserController@calanderAppointmentData'));
    Route::post('leads/cancelappointment', array('as' => 'user.leads.cancelappointment', 'uses' => 'LeadsUserController@cancelappointment'));

	Route::post('leads/updateNotes', array('as' => 'user.leads.updateNotes', 'uses' => 'LeadsUserController@updateNotes'));
	Route::post('leads/callHistory', array('as' => 'user.leads.callHistory', 'uses' => 'LeadsUserController@callHistory'));
	Route::post('leads/attachFiles', array('as' => 'user.leads.attachFiles', 'uses' => 'LeadsUserController@attachFiles'));
	Route::post('leads/attachmentHistory', array('as' => 'user.leads.attachmentHistory', 'uses' => 'LeadsUserController@getAttachmentHistory'));
	Route::post('leads/deleteAttachment', array('as' => 'user.leads.deleteAttachment', 'uses' => 'LeadsUserController@deleteAttachment'));
	Route::get('leads/downloadAttachment/{id}', array('as' => 'user.leads.downloadAttachment', 'uses' => 'LeadsUserController@downloadAttachment'));

	Route::post('leads/notesHistory', array('as' => 'user.leads.notesHistory', 'uses' => 'LeadsUserController@notesHistory'));
	Route::post('leads/isLeadLocked', array('as' => 'user.leads.isLeadLocked', 'uses' => 'LeadsUserController@isLeadLocked'));
	Route::get('leads/landinglead', ['as' => 'user.leads.landingLeadData', 'uses' => 'LeadsUserController@ajaxLandingLeadData']);
	Route::get('leads/landingExport', ['as' => 'user.leads.landingExport', 'uses' => 'LeadsUserController@landingExport']);
	
	Route::get('leads/getMoveLeadsDlg', array('as' => 'user.leads.getMoveLeadsDlg', 'uses' => 'LeadsUserController@getMoveLeadsDlg'));
	Route::get('leads/getMoveMappingDlg', array('as' => 'user.leads.getMoveMappingDlg', 'uses' => 'LeadsUserController@getMoveMappingDlg'));
	Route::post('leads/doMoveLeads', array('as' => 'user.leads.doMoveLeads', 'uses' => 'LeadsUserController@doMoveLeads'));

    Route::get('teams/salesteam', array('as' => 'user.teams.salesteam', 'uses' => 'TeamsUserController@salesTeam'));
    Route::post('teams/deletesalesman', array('as' => 'user.teams.deletesalesman', 'uses' => 'TeamsUserController@deleteSalesman'));
    Route::post('teams/editsalesmaninfo', array('as' => 'user.teams.editsalesmaninfo', 'uses' => 'TeamsUserController@editSalesmanInfo'));
    Route::post('teams/updatesalesmaninfo', array('as' => 'user.teams.updatesalesmaninfo', 'uses' => 'TeamsUserController@updateSalesmanInfo'));
    Route::post('teams/addnewsalesman', array('as' => 'user.teams.addnewsalesman', 'uses' => 'TeamsUserController@addNewSalesman'));

    Route::get('teams/staffteam/{letter?}', array('as' => 'user.teams.staffteam', 'uses' => 'TeamsUserController@staffTeam'));
    Route::post('teams/checkvolumelimit', array('as' => 'user.teams.checkvolumelimit', 'uses' => 'TeamsUserController@checkVolumeLimit'));
    Route::post('teams/addnewstaffmember', array('as' => 'user.teams.addnewstaffmember', 'uses' => 'TeamsUserController@addNewStaffMember'));
    Route::post('teams/editstaffmemberinfo', array('as' => 'user.teams.editstaffmemberinfo', 'uses' => 'TeamsUserController@editStaffMemberInfo'));
    Route::post('teams/deletestaffmember', array('as' => 'user.teams.deletestaffmember', 'uses' => 'TeamsUserController@deleteStaffMember'));
    Route::post('teams/updatestaffmemberinfo', array('as' => 'user.teams.updatestaffmemberinfo', 'uses' => 'TeamsUserController@updateStaffMemberInfo'));

	Route::get('forms', array('as' => 'user.forms', 'uses' => 'FormsUserController@index'));
    Route::get('forms/createoredit', array('as' => 'user.forms.createoredit', 'uses' => 'FormsUserController@createOrEdit'));
    Route::post('forms/formfieldsdetails', array('as' => 'user.forms.formfieldsdetails', 'uses' => 'FormsUserController@formFieldsDetails'));
    Route::post('forms/addoreditform', array('as' => 'user.forms.addoreditform', 'uses' => 'FormsUserController@addOrEditForm'));
    Route::post('forms/deleteform', array('as' => 'user.forms.deleteform', 'uses' => 'FormsUserController@deleteForm'));
    Route::get('forms/showdemoform', array('as' => 'user.forms.showdemoform', 'uses' => 'FormsUserController@showDemoForm'));
	Route::get('forms/landing/createoredit', array('as' => 'user.forms.landing.createoredit', 'uses' => 'LandingFormUserController@index'));
	Route::post('forms/landing/addoreditform', array('as' => 'user.forms.landing.addoreditform', 'uses' => 'LandingFormUserController@addOrEditForm'));
	Route::post('forms/landing/formfieldsdetails', array('as' => 'user.forms.landing.formfieldsdetails', 'uses' => 'LandingFormUserController@formFieldsDetails'));
	Route::post('forms/landing/deleteform', array('as' => 'user.forms.landing.deleteform', 'uses' => 'LandingFormUserController@deleteForm'));
	Route::post('forms/landing/showdemoform', array('as' => 'user.forms.landing.showdemoform', 'uses' => 'LandingFormUserController@showDemoForm'));
	Route::get('forms/landing/getMapping', ['as' => 'user.form.landing.getMapping', 'uses' => 'LandingFormUserController@getMapping']);
	Route::post('forms/landing/setMapping', ['as' => 'user.form.landing.setMapping', 'uses' => 'LandingFormUserController@setMapping']);

    Route::get('email', array("as" => "user.email", 'uses' => 'EmailUserController@index'));
    Route::get('email/ajaxemails', array("as" => "user.email.ajaxemails", 'uses' => 'EmailUserController@ajaxEmails'));
    Route::get('email/create', array("as" => "user.email.create", 'uses' => 'EmailUserController@create'));
    Route::post('email/addnewtemplate', array("as" => "user.email.addnewtemplate", 'uses' => 'EmailUserController@addNewTemplate'));
    Route::get('email/edit/{templateID}', array("as" => "user.email.edit", 'uses' => 'EmailUserController@edit'));
    Route::post('email/updatetemplate', array("as" => "user.email.updatetemplate", 'uses' => 'EmailUserController@updateTemplate'));
    Route::post('email/deletetemplate', array("as" => "user.email.deletetemplate", 'uses' => 'EmailUserController@deleteTemplate'));
    Route::post('email/previewtemplate', array("as" => "user.email.previewtemplate", 'uses' => 'EmailUserController@previewTemplate'));
	Route::post('email/setEmailTemplateStatus', array("as" => "user.email.setEmailTemplateStatus", 'uses' => 'EmailUserController@setStatus'));
	Route::get('email/home', array("as" => "user.email.home", 'uses' => 'EmailUserController@home'));
	Route::get('email/mass/index', array("as" => "user.email.mass", 'uses' => 'EmailUserController@massEmailIndex'));
	Route::get('email/mass/create', array("as" => "user.email.mass.create", 'uses' => 'EmailUserController@massEmailCreate'));
	Route::post('email/mass/store', array("as" => "user.email.mass.store", 'uses' => 'EmailUserController@massEmailStore'));
	Route::post('email/mass/detectEmail', array("as" => "user.email.mass.detectEmail", 'uses' => 'EmailUserController@massDetectEmailAndAdvanceFilter'));
	Route::post('email/mass/campaignEmailWithFilter', array("as" => "user.email.mass.campaignEmailWithFilter", 'uses' => 'EmailUserController@massCampaignEmailWithFilter'));
	Route::post('email/mass/csvFile', array("as" => "user.email.mass.csvFile", 'uses' => 'EmailUserController@uploadCsvFile'));
	Route::post('email/mass/csvFileEmails', array("as" => "user.email.mass.csvFileEmails", 'uses' => 'EmailUserController@massCsvFileEmails'));
	Route::get('email/mass/show/{id}', array("as" => "user.email.mass.show", 'uses' => 'EmailUserController@massEmailShow'));
	Route::post('email/mass/delete/{id}', array("as" => "user.email.mass.delete", 'uses' => 'EmailUserController@massEmailDelete'));
	Route::post('email/mass/server', array("as" => "user.email.mass.server", 'uses' => 'EmailUserController@massEmailServerSetting'));
	Route::post('email/mass/testmail', array("as" => "user.email.mass.testmail", 'uses' => 'EmailUserController@massEmailSendTestMail'));
	Route::get('email/mass/duplicate/{id}', array("as" => "user.email.mass.duplicate", 'uses' => 'EmailUserController@massEmailDuplicate'));
	Route::get('email/mass/edit/{id}', array("as" => "user.email.mass.edit", 'uses' => 'EmailUserController@massEmailEdit'));
	Route::post('email/mass/update/{id}', array("as" => "user.email.mass.update", 'uses' => 'EmailUserController@massEmailUpdate'));
	Route::get('email/mass/reports/{id}', array("as" => "user.email.mass.reports", 'uses' => 'EmailUserController@massEmailReports'));
	Route::get('email/mass/report/openLead/{id}', array("as" => "user.email.mass.report.openLead", 'uses' => 'EmailUserController@massEmailOpenLead'));

    Route::get('statistics', array('as' => 'user.statistics', 'uses' => 'StatisticsUserController@index'));
    Route::post('statistics/showcallvolume', array('as' => 'user.statistics.showcallvolume', 'uses' => 'StatisticsUserController@showCallVolume'));
    Route::post('statistics/teamperformance', array('as' => 'user.statistics.teamperformance', 'uses' => 'StatisticsUserController@showTeamPerformance'));
	Route::post('statistics/showCustomCallVolume', array('as' => 'user.statistics.showCustomCallVolume', 'uses' => 'StatisticsUserController@showCustomCallVolume'));
	Route::post('statistics/getShareLink', array('as' => 'user.statistics.getShareLink', 'uses' => 'StatisticsUserController@getShareLink'));

    Route::post('panel', array('as' => 'user.panel', 'uses' => 'UserController@showLeftPanel'));

    Route::get('pushmessage/{pageNumber?}', array('as' => 'user.pushmessage', 'uses' => 'PushMessageUserController@index'));
    Route::get('pushmessage/messagedetail/{messageID?}', array('as' => 'user.pushmessage.messagedetail', 'uses' => 'PushMessageUserController@messageDetail'));
    Route::post('pushmessage/generatemessage', array('as' => 'user.pushmessage.generatemessage', 'uses' => 'PushMessageUserController@generateMessage'));
    Route::post('pushmessage/pushmessagenotification', array('as' => 'user.pushmessage.pushmessagenotification', 'uses' => 'PushMessageUserController@pushMessageNotification'));
    Route::post('pushmessage/deletepushmessage', array('as' => 'user.pushmessage.deletepushmessage', 'uses' => 'PushMessageUserController@deletePushMessage'));

    Route::get('helptopics/{postedTopicID?}', array('as' => 'user.helptopics', 'uses' => 'UserController@helpTopics'));
    Route::post('helptopics/ajaxtopicarticle', array('as' => 'user.helptopics.ajaxtopicarticle', 'uses' => 'UserController@ajaxTopicArticle'));
    Route::get('helptopics/detail/{articleID?}', array('as' => 'user.helptopics.detail', 'uses' => 'UserController@helpTopicDetails'));

    Route::post('makerenewlicensepayment', array('as' => 'user.makerenewlicensepayment', 'uses' => 'HomeController@makeRenewLicensePayment'));
    Route::post('makeaddmemberpayment', array('as' => 'user.makeaddmemberpayment', 'uses' => 'HomeController@makeAddMemberPayment'));

	
});


/** Site routes **/
// Routes according to requests and URL
/*
	TODO:delete resources
Route::get('/', array('as'=> 'home', 'uses' => 'HomeController@index'));
Route::get('features', array('as'=> 'features', 'uses' => 'HomeController@page'));
Route::get('pricing', array('as'=> 'pricing', 'uses' => 'HomeController@page'));
Route::get('contact', array('as'=> 'contact', 'uses' => 'HomeController@page'));
Route::get('lead-management', array('as'=> 'leadmanagement', 'uses' => 'HomeController@page'));
Route::get('call-center-crm', array('as'=> 'call_center_crm', 'uses' => 'HomeController@page'));
Route::get('sales-tools-for-startups', array('as'=> 'sales_tools_for_startups', 'uses' => 'HomeController@page'));
*/

Route::get('signup', array('as'=> 'signup', 'uses' => 'HomeController@signup'));
Route::get('pricing2', array('as'=> 'pricing2', 'uses' => 'HomeController@page'));
Route::get('faq', array('as'=> 'faq', 'uses' => 'HomeController@page'));
Route::get('sanityos', array('as'=> 'sanityos', 'uses' => 'HomeController@page'));
Route::post('contactform', array('as'=> 'contactform', 'uses' => 'HomeController@contactForm'));
Route::post('newsletter', array('as'=> 'newsletter', 'uses' => 'HomeController@newsletter'));
Route::post('demo', array('as'=> 'demo', 'uses' => 'HomeController@demo'));
Route::any('webhook', ["as" => "webhook", "uses" => "HomeController@webhook"]);

Route::get('/', array('as'=> 'home', 'uses' => 'PublicHomeController@page'));
Route::get('features', array('as'=> 'features', 'uses' => 'PublicHomeController@page'));
Route::get('about-us', array('as'=> 'about-us', 'uses' => 'PublicHomeController@page'));
Route::get('support', array('as'=> 'support', 'uses' => 'PublicHomeController@page'));
Route::get('pricing', array('as'=> 'pricing', 'uses' => 'PublicHomeController@page'));
Route::get('lead-management', array('as'=> 'lead-management', 'uses' => 'PublicHomeController@page'));
Route::get('sales-pipeline', array('as'=> 'sales-pipeline', 'uses' => 'PublicHomeController@page'));
Route::get('email-tools', array('as'=> 'email-tools', 'uses' => 'PublicHomeController@page'));
Route::get('contact', array('as'=> 'contact', 'uses' => 'PublicHomeController@page'));
Route::get('sales-tools-for-startups', array('as'=> 'sales-tools-for-startups', 'uses' => 'PublicHomeController@page'));
Route::get('call-center-crm', array('as'=> 'call-center-crm', 'uses' => 'PublicHomeController@page'));
Route::get('sales-campaigns', array('as'=> 'sales-campaigns', 'uses' => 'PublicHomeController@page'));
Route::get('appointment-setting', array('as'=> 'appointment-setting', 'uses' => 'PublicHomeController@page'));
Route::get('crm-webforms', array('as'=> 'crm-webforms', 'uses' => 'PublicHomeController@page'));
Route::get('lead-distribution', array('as'=> 'lead-distribution', 'uses' => 'PublicHomeController@page'));
Route::get('testimonials', array('as'=> 'testimonials', 'uses' => 'PublicHomeController@page'));

Route::group(array('prefix' => 'newsletter'), function(){
	Route::post('subscribe', array('as'=> 'newsletter.subscribe', 'uses' => 'HomeController@newsletter'));
	Route::get('newsletter_success', array('as'=> 'newsletter.success', 'uses' => 'PublicHomeController@page'));
	Route::get('newsletter_error', array('as'=> 'newsletter.error', 'uses' => 'PublicHomeController@page'));
});


Route::get('register/verify/{code}', array('as' => 'home.register.verify', 'uses' => 'HomeController@emailVerification'));

Route::group(array('prefix' => 'home'), function(){

    Route::post('showpreferredcurrency', array('as' => 'home.showpreferredcurrency', 'uses' => 'HomeController@showPreferredCurrency'));
    Route::post('makepayment', array('as' => 'home.makepayment', 'uses' => 'HomeController@makepayment'));
    Route::get('paymentreturnurl', array('as' => 'home.paymentreturnurl', 'uses' => 'HomeController@paymentReturnUrl'));

    Route::post('register/trial', array('as' => 'home.register.trial', 'uses' => 'HomeController@registerTrialUser'));
    Route::post('checkemail', array('as' => 'home.checkemail', 'uses' => 'HomeController@checkEmail'));
});


Route::group(array("prefix" => "cron"), function() {
    //moved to console command Route::get("licensecheck", array("uses" => "CronJobController@LicenseChecking"));
    //moved to console command Route::get("statsupdate", array("uses" => "CronJobController@statsUpdate"));
    //moved to console command Route::get("exchangerate", array("uses" => "CronJobController@exchangeRateScrap"));
    //moved to console command RRoute::get("callbackalert", array("uses" => "CronJobController@callBackAlert"));
    //unused Route::get('sendLicenceExpiryEmails', array('uses' => 'CronJobController@sendLicenceExpiryEmails'));
    //moved to console command Route::get('emailCredits', array('uses' => 'CronJobController@emailCredits'));
    //moved to console command Route::get('getTransactions', array('uses' => 'CronJobController@getTransactionForAgreement'));

	// Temporary route
	//unused Route::get('insertTemplates', ['uses' => 'CronJobController@insertTemplates']);
	//unused Route::get('emailCreditForExistingUsers', ['uses' => 'CronJobController@emailCreditForExistingUsers']);
	//unused Route::get('setExistingUser', ['uses' => 'CronJobController@setExistingUsers']);
});

Route::get('addCalender', array('as' => 'addCalender', 'uses' => 'LeadsUserController@prepareAndDownLoadICSFile'));

// pixel tracking (find email open)
Route::get('email/open/{id}.png', ['as' => 'email.track', 'uses' => 'EmailUserController@massPixelTracking']);
Route::get('email/redirect/{id}', ['as' => 'email.redirect', 'uses' => 'EmailUserController@massEmailClick']);
Route::get('email/unsubscribe/{id}', ['as' => 'email.unsubscribe', 'uses' => 'EmailUserController@massEmailUnsubscribe']);

// SNS subscription url
Route::any('aws/subscription', 'EmailUserController@awsSubscription');
Route::any('payment/show/container', ['as' => 'user.payment.showContainer', 'uses' => 'UserController@paymentShowContainer']);
Route::any('payment/show', ['as' => 'user.payment.show', 'uses' => 'UserController@paymentShow']);
Route::post('paymentinfo', ['as' => 'user.payment.info', 'uses' => 'UserController@setPaymentInfo']);
Route::any('payment/process', ['as' => 'user.payment.process', 'uses' => 'UserController@payment']);
Route::get('registerWebhook', 'HomeController@registerWebhook');

//SparkPost webhook
Route::any('webhook/sparkpost', 'EmailUserController@sparkpostWebhook');
Route::any('webhook/mandrill', 'EmailUserController@mandrillWebhook');

Route::group(array("prefix" => "test"), function() {
    Route::get("sendInvoiceEmail", array("uses" => "CommonController@test_sendInvoiceEmailToUser"));
});


/*dump all queries into log, vverry slow
Event::listen('illuminate.query',function($query){
    \Log::error(print_r($query, true));
});
 */
