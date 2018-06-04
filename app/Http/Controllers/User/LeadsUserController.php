<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Http\Requests\SimpleRequest;
use App\Models\AdminEmailTemplate;
use App\Models\Appointment;
use App\Models\CallBack;
use App\Models\CallHistory;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\EmailTemplate;
use App\Models\Form;
use App\Models\FormFields;
use App\Models\Lead;
use App\Models\LeadAttachment;
use App\Models\LeadCustomData;
use App\Models\MassEmailTemplate;
use App\Models\PostalCode;
use App\Models\SalesMember;
use App\Models\Setting;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Models\LeadInfoEmails;
use Auth;
use Config;
use DateTime;
use DB;
use Exception;
use File;
use Input;
use Redirect;
use Request;
use Response;
use Session;
use URL;
use View;
use Log;

class LeadsUserController extends Controller
{

	private $perPage = 10;

	public function createLead($leadID = null, $edit = null) {
		$user = $this->user;
		$leadLastActioner = null;

		$campaignLists = $user->getStartedCampaigns();

		if ($campaignLists->count() == 0) {
			if(Session::has('referFrom') && Session::get('referFrom') == 'massmail') {
				$lead = Lead::find($leadID);
				$campaign = Campaign::find($lead->campaignID);

				if($campaign->status != "Started") {
					$campaign = $campaign->start();
				}

				Session::forget('referFrom');
			}
			else {
				throw new Exception("No campaign was found for which leads could be generated. Please create a new campaign
                or start a campaign that has already been created.");
			}
		}

		if ($leadID == null) {
			// If no lead ID was sent in url, that means we need to find a lead ID
			$campaign = null;
			foreach ($campaignLists as $campaignList) {
				// Get the first campaign that has remaining calls
				if ($campaignList->callsRemaining > 0) {
					$campaign = $campaignList;
					break;
				}
			}

			if ($campaign != null) {
				// We should have found a campaign
				$lead = $campaign->getLandingLead($this->user);
				return Redirect::to('user/leads/createlead/' . $lead->id);
			}
			else {
				return Redirect::to("user/campaigns/start");
			}
		}
		else {
			// Get lead details whose ID was sent in URL
			$lead = Lead::find($leadID);

			if (!$lead) {
				throw new Exception("Requested lead was not found");
			}
			else {
				$campaign = Campaign::find($lead->campaignID);
				
				$this->authorize('can-see-campaign', $campaign);

				if($campaign->status != "Started") {
					$campaign = $campaign->start();
				}

				if ($lead->firstActioner == null) {
					$lead->firstActioner = $user->id;
				}

				$leadLastActioner = $lead->lastActioner;
				/*
				$lead->lastActioner = $user->id;
				$lead->timeEdited = new DateTime;
				$lead->status = 'Actioned';
				*/
			}
		}


		// Total Number of Lead for selected campaignID
		$totalLeads = $campaign->totalLeads;

		// Compute position of current lead as
		$currentLeadNumber = $lead->getLeadNumber();

		$leadFormFields = $lead->getFormFields();
		$leadCustomData = $lead->getCustomData();

		// We divide form fields into two arrays, for two sections in the form - one above Booking Appointment
		// one below it
		$leadFormData1 = [];
		$leadFormData2 = [];
		$emailFieldExists = false;

		$fieldsToSearchForEmails = [];

		foreach ($leadFormFields as $leadFormField) {
			$currentLeadData = $leadCustomData->filter(function ($modal) use($leadFormField) {
				return $modal->fieldID == $leadFormField->id;
			})->first();

			if(strtolower($leadFormField->fieldName) == 'address') {
				$address = [
					'fieldID' => $leadFormField->id,
					'fieldName' => $leadFormField->fieldName,
					'value' => ($currentLeadData) ? ($currentLeadData->value) : "",
					'isRequired' => $leadFormField->isRequired
				];
			} elseif (strtolower($leadFormField->fieldName) == 'post/zip code') {
				$postalCode = [
					'fieldID' => $leadFormField->id,
					'fieldName' => $leadFormField->fieldName,
					'value' => ($currentLeadData) ? ($currentLeadData->value) : "",
					'isRequired' => $leadFormField->isRequired
				];
			} elseif (strtolower($leadFormField->fieldName) == 'website') {
				$website = [
						'fieldID' => $leadFormField->id,
						'fieldName' => $leadFormField->fieldName,
						'value' => ($currentLeadData) ? ($currentLeadData->value) : "",
						'isRequired' => $leadFormField->isRequired
				];
			}

			if(strtolower($leadFormField->fieldName) == 'notes') {
				$notes = [
					'fieldID' => $leadFormField->id,
					'fieldName' => $leadFormField->fieldName,
					'value' => ($currentLeadData) ? ($currentLeadData->value) : "",
					'isRequired' => $leadFormField->isRequired
				];
			}
			else {
				if($leadFormField->type == 'date') {
					$leadFormData1[] = [
							'fieldID'   => $leadFormField->id,
							'fieldName' => $leadFormField->fieldName,
							'value'     => ($currentLeadData) ? (FormFields::formatDateFieldValue($currentLeadData->value)) : "",
							'type'      =>  $leadFormField->type,
							'defaultValues' => '',
							'isRequired' => $leadFormField->isRequired
						];
				} else {
					$leadFormData1[] = [
							'fieldID'   => $leadFormField->id,
							'fieldName' => $leadFormField->fieldName,
							'value'     => ($currentLeadData) ? ($currentLeadData->value) : "",
							'type'      =>  $leadFormField->type,
							'defaultValues' => explode(',', $leadFormField->values),
							'isRequired' => $leadFormField->isRequired
						];
				}
			}

			if ($currentLeadData && strtolower($currentLeadData->fieldName) == 'email') {
				$emailFieldExists = true;
			}

			//fileds to search emails for cc
			if($leadFormField->type == 'text'
				&& !in_array(strtolower($currentLeadData->fieldName), ['email', 'address', 'post/zip code', 'website', 'notes'])
				&& !in_array($currentLeadData->fieldName, CommonController::getFieldVariations('Mobile No'))
				&& !in_array($currentLeadData->fieldName, CommonController::getFieldVariations('Telephone No'))
			) {
				$fieldsToSearchForEmails[] = $leadFormField->id;
			}
		}

//		if(isset($address)) {
//			$leadFormData2[] = $address;
//		}
//
//		if(isset($postalCode)) {
//			$leadFormData2[] = $postalCode;
//		}

		if(isset($notes)) {
			$leadFormData2[] = $notes;
		}

//		if(isset($website)) {
//			$leadFormData2[] = $website;
//		}

		if(isset($address) && isset($postalCode)) {
			$isAddressLookUp = true;
		} else {
			$isAddressLookUp = false;
		}

		// Get rest of the data for this lead
		$emailTemplates = $this->user->getEmailTemplates();

		// Get mass email template associated with this campaign
		//$massEmailTemplate = $campaign->getTeamMassMailTemplate($this->user);

		if ($this->user->userType == 'Multi' || $this->user->userType == 'Single') {
			$userManager = $this->user->id;
		}
		else {
			$userManager = $this->user->manager;
		}

		$salesMemberLists = $campaign->getSalesMemberList($userManager);

		$callBackUserLists = User::join("campaignmembers", "campaignmembers.userID", "=", "users.id")
				->select("users.*")
				->where("campaignID", $campaign->id)
				->where('users.id', '!=', $user->id)
				->get();

		$data = [
				'leadsMenuActive'   => 'nav-active active',
		         'leadsStyleActive'  => 'display: block',
		         'campaignLists'     => $campaignLists,
		         'leadFormDatas1'    => $leadFormData1,
		         'leadFormDatas2'    => $leadFormData2,
		         'emailTempletes'    => $emailTemplates,
		         'emailFieldExists'  => $emailFieldExists,
		         'totalLeads'        => $totalLeads,
		         'currentLeadNumber' => $currentLeadNumber,
		         'campaignID'        => $campaign->id,
		         'leadDetails'       => $lead,
		         'salesMemberLists'  => $salesMemberLists,
		         'callBackUserLists' => $callBackUserLists,
				//'massMailTemplates' => $massEmailTemplate,
				 'isAddressLookUp' => $isAddressLookUp,
				 'fieldsToSearchForEmails' => implode(',', $fieldsToSearchForEmails),
		];

		$callBack = $lead->getCallBack();

		$data['isCallBackUserExists'] = false;
		$data['callBackUserDetail']['callBackUserID'] = "";

		if ($callBack) {
			$dateTime = new DateTime($callBack->time);
			$time = $dateTime->format('g:i A');
			$date = $dateTime->format('d-m-Y');
			$callBackUserDetail = ['time'           => $time,
			                       'date'           => $date,
			                       'callBackUserID' => $callBack->actioner];
			$data['isCallBackUserExists'] = true;
			$data['callBackUserDetail'] = $callBackUserDetail;
		}

		// Changing Lead status to No if it is NotSet and getting salesman details
		$data['isSalesManExists'] = false;
		$data['salesManDetail']['salesManUserID'] = "";

		if ($lead->bookAppointment == 'NotSet') {
			$lead->bookAppointment = 'No';
		}
		else if ($lead->bookAppointment == 'Yes') {
			$appointment = $lead->getAppointment();
			if(!$appointment) {
				$lead->bookAppointment = 'No';
			} else {
				$dateTime = new DateTime($appointment->time);
				$time = $dateTime->format('g:i A');
				$date = $dateTime->format('d-m-Y');
				$salesManDetail = ['time' => $time,
								   'date' => $date,
								   'salesManUserID' => $appointment->salesMember];
				$data['isSalesManExists'] = true;
				$data['salesManDetail'] = $salesManDetail;
			}
		}

		// Check whether edit action performed or not
		if ($edit === 'edit') {
			// Updating last Actioner for Lead
			$lead->lastActioner = $this->user->id;
			$data['isEditable'] = false;
		}
		else {
			$data['isEditable'] = true;
		}

		// Updating timeEdited for lead
		$lead->timeOpened = DB::raw('NOW()');
		$lead->edited_by_user_id = $this->user->id;


		$data['formFieldsNames'] = $leadFormFields;

		// Sending Last Actioned User ID for this Lead
		if ($leadLastActioner != null) {
			$lastActioner = User::find($leadLastActioner);

			$data['lastActionedUser'] = $lastActioner->firstName . ' ' .$lastActioner->lastName;
		}
		else {
			$data['lastActionedUser'] = "";
		}

		Session::put('lastTimeTaken', $lead->timeTaken);

		//$lead->createLeadCallHistory($this->user->id);
		Session::forget('callHistoryID');

		$lead->save();

		$data['maxFileSize'] = Setting::get('maxFileUploadSize');

		return View::make('site/lead/createlead', $data);
	}

	/**
	 * Create a new blank lead for given campaign
	 * @param $campaignID
	 * @return integer Lead ID of new lead
	 * @throws Exception
	 */
	public function createNewLeadForCampaign($campaignID) {
		$user = $this->user;
	
		$campaign = Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);
	
		if (!$campaign) {
			throw new Exception("Campaign not found.");
		}
	
		//creating New Lead
		$lead = $campaign->createNewLead($user);
		
		return redirect()->route('user.leads.createlead', [$lead->id]);
	}

	//private function checkEmailExitsToSendMail($leadID) {
	//	//Setting leadID in session to send Email
	//	$isEmailFieldExists = LeadCustomData::where('leadID', '=', $leadID)
	//		->where('fieldName', '=', 'Email')
	//		->count();
	//	if ($isEmailFieldExists > 0) {
	//		Session::put('emailSendLeadID', $leadID);
	//	}
	//}

	public function sendNewEmail() {
		$user = $this->user;

		if(!Input::has('leadID')){
			return ['status' => 'error', 'message' => 'There is some error in email attachments(type and size). Please try again.'];
		}

		$leadID = Input::get('leadID');
		$emailText = Input::get('emailText');
		$subject = Input::get("subject");
		$ccEmail = Input::get("email_cc");
		$bccEmail = Input::get("email_bcc");

		if($ccEmail != '') {
			$rules = [
				'email' => 'required|email'
			];
			$ccEmails = explode(',', $ccEmail);
			foreach($ccEmails as $ccEmailInner) {
				$validator = \Validator::make(['email' => $ccEmailInner], 
					$rules
				);

				if($validator->fails()) {
					return ['status' => 'error', 'message' => 'There is error in cc email : ' . $ccEmailInner . ' : ' . $validator->errors()->first('email')];
				}
			}
		}

		if($bccEmail != '') {
			$rules = [
				'email' => 'required|email'
			];
			$bccEmails = explode(',', $bccEmail);
			foreach($bccEmails as $bccEmailInner) {
				$validator = \Validator::make(['email' => $bccEmailInner], 
					$rules
				);

				if($validator->fails()) {
					return ['status' => 'error', 'message' => 'There is error in bcc email : ' . $bccEmailInner . ' : ' . $validator->errors()->first('email')];
				}
			}
		}

		$lead = Lead::find($leadID);
		if(!$lead){
			return ['status' => 'error', 'message' => 'Lead not found.'];
		}

		$this->authorize('can-see-campaign', $lead->campaign);

		// Updating timeEdited for lead
		$lead->timeEdited = new DateTime;
		$lead->lastActioner = $this->user->id;
		$lead->save();

		$fieldValues = [];
		$attachments = [];
		$serverFiles = [];

		$email = '';

		$leadFieldsDetails = LeadCustomData::where('leadID', '=', $leadID)->get();

		foreach ($leadFieldsDetails as $leadFieldsDetail) {
			$fieldValues[ $leadFieldsDetail->fieldName ] = $leadFieldsDetail->value;

			if ($leadFieldsDetail->fieldName == "Email") {
				$email = $leadFieldsDetail->value;
			}
		}

		$fieldValues['Agent'] = $this->user->firstName.' '.$this->user->lastName;

		if($email == '') {
			return ['status' => 'error', 'message' => 'Please set lead\'s email address before sending email.'];
		}

		$filesToBeRemoved = json_decode(Input::get('fileDeleted'));
		if(Input::hasFile('file')) {

			foreach(Input::file('file') as $file){
				$fileName = $file->getClientOriginalName();
				$extension = $file->getClientOriginalExtension();

				if(($extension != 'php' && $extension != 'js' && $extension != 'exe') && !in_array($fileName, $filesToBeRemoved)){
					$file->move(storage_path().'/attachments/temp/', $fileName);
					$attachments[] = '/attachments/temp/'.$fileName;
					$serverFiles[] = storage_path().'/attachments/temp/'.$fileName;
				}
			}
		}
		
		$mailSettings = CommonController::loadSMTPSettings("outbound");

		$generalEmailInfo = [
			'from' => $mailSettings["from"]["address"],
			'fromName' => $user->firstName.' '.$user->lastName,
			'replyTo' => $user->email
		];

		//custom email based on template
		if(Input::has('emailTemplateID')) {
			$emailTemplateID = Input::get('emailTemplateID');
			
			if($emailTemplateID == '' || $emailTemplateID == "writeNew") {
				return ['status' => "fail", 'message' => 'Template is required'];
			} elseif(starts_with($emailTemplateID, 'mass_')) {
				$emailTemplateID = str_replace('mass_', '', $emailTemplateID);
				$emailTemplate = MassEmailTemplate::find($emailTemplateID);
				$generalEmailInfo['replyTo'] = $emailTemplate->reply_to;
				$generalEmailInfo['fromName'] = $emailTemplate->from_name;
			} elseif($emailTemplateID != '') {
				$emailTemplate = EmailTemplate::find($emailTemplateID);
			}
			$files = $emailTemplate->getEmailAttachmentsToBeSend();
			foreach($files as $file) {
				if(!in_array(basename($file), $filesToBeRemoved)){
					$attachments[] = $file;
				}
			}
		}


		Config::set("mail.from.name", $user->firstName . " " . $user->lastName);

		$emailInfo = CommonController::setEmailConfiguration($lead, $user, $generalEmailInfo, /*$forceFromAddressFromCampaign*/ true);

		$emailInfo['subject'] = $subject;
		$emailInfo['to']    = $email;
		$emailInfo['bccEmail'] = $user->bccEmail;
		$emailInfo['ccEmail'] = $ccEmail;
		$emailInfo['bccEmail'] = $bccEmail;

		CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments, /*$throw*/ false, /*$async*/ true, 
			function() use($serverFiles) {
				//Log::error('clearance called');
				//unlink attachments
				if(sizeof($serverFiles) > 0){
					File::delete($serverFiles);
				}
			}
		);


		LeadInfoEmails::insert([
			'lead_id' => $lead->id,
			'campaign_id' => $lead->campaignID,
			'created_at' => new DateTime,
			'user_id' => $this->user->id,
		]);

		return ['status' => 'success', 'message' => 'Email has been sent successfully.'];
	}

	public function viewLead($leadID = null) {
		$user = $this->user;

		if ($leadID == null) // If no lead ID was sent in url, that means we need to find a lead ID
		{
			throw new Exception("No new lead was found. Please import some lead into a campaign or create a
	        	new campaign and add data to it.");
		}
		else {
			// Get lead details whose ID was sent in URL
			$lead = Lead::find($leadID);

			if (!$lead) {
				throw new Exception("Requested lead was not found");
			}
			else {
				$campaignID = $lead->campaignID;
			}
		}

		// Total Number of Lead for selected campaignID
		$campaign = Campaign::find($campaignID);
		$totalLeads = $campaign->totalLeads;

		// Compute position of current lead as
		$currentLeadNumber = $lead->getLeadNumber();

		$leadFormFields = $lead->getFormFields();
		$leadCustomData = $lead->getCustomData();

		// We divide form fields into two arrays, for two sections in the form - one above Booking Appointment
		// one below it
		$leadFormData1 = [];
		$leadFormData2 = [];
		$emailFieldExists = false;

		foreach ($leadFormFields as $leadFormField) {
			$currentLeadData = $leadCustomData->filter(function ($modal) use($leadFormField) {
				return $modal->fieldID == $leadFormField->id;
			})->first();

			if (strtolower($leadFormField->fieldName) == 'address' ||
				strtolower($leadFormField->fieldName) == 'post/zip code' ||
				strtolower($leadFormField->fieldName) == 'notes' || strtolower($leadFormField->fieldName) == 'website'
			) {
				$leadFormData2[] = ['fieldID'   => $leadFormField->id,
				                    'fieldName' => $leadFormField->fieldName,
				                    'value'     => ($currentLeadData) ? ($currentLeadData->value) : ""];
			}
			else {
				$leadFormData1[] = ['fieldID'   => $leadFormField->id,
				                    'fieldName' => $leadFormField->fieldName,
				                    'value'     => ($currentLeadData) ? ($currentLeadData->value) : ""];
			}

			if ($currentLeadData && strtolower($currentLeadData->fieldName) == 'email') {
				$emailFieldExists = true;
			}

		}

		$teamMemberIDs = $this->user->getTeamMembersIDs();

		// Get rest of the data for this lead
		$emailTemplates = $this->user->getEmailTemplates();

		if ($this->user->userType == 'Multi' || $this->user->userType == 'Single') {
			$userManager = $this->user->id;
		}
		else {
			$userManager = $this->user->manager;
		}

		$salesMemberLists = SalesMember::where('manager', $userManager)->get();

		$callBackUserLists = User::where('manager', $userManager)->where('id', '!=', $user->id)->get();

		$data = [
            'leadsMenuActive'      => 'nav-active active',
            'leadsStyleActive'     =>  'display: block',
            'leadFormDatas1'	   => $leadFormData1,
            'campaignName'         => $campaign->name,
            'leadID'               => $leadID,
            'leadFormDatas2'	   => $leadFormData2,
            'emailTempletes' 	   => $emailTemplates,
            'emailFieldExists'     => $emailFieldExists,
            'totalLeads' 		   => $totalLeads,
            'currentLeadNumber'    => $currentLeadNumber,
            'campaignID' 		   => $campaignID,
            'leadDetails' 		   => $lead,
            'salesMemberLists' 	   => $salesMemberLists,
            'callBackUserLists'	   => $callBackUserLists
        ];


		$callBack = $lead->getCallBack();

		$data['isCallBackUserExists'] = false;
		$data['callBackUserDetail']['callBackUserID'] = "";

		if ($callBack) {
			$dateTime = new DateTime($callBack->time);
			$time = $dateTime->format('g:i A');
			$date = $dateTime->format('d-m-Y');
			$callBackUserDetail = ['time'           => $time,
			                       'date'           => $date,
			                       'callBackUserID' => $callBack->actioner];
			$data['isCallBackUserExists'] = true;
			$data['callBackUserDetail'] = $callBackUserDetail;
		}

		// Changing Lead status to No if it is NotSet and getting salesman details
		$data['isSalesManExists'] = false;
		$data['salesManDetail']['salesManUserID'] = "";

		if ($lead->bookAppointment == 'NotSet') {
			$lead->bookAppointment = 'No';
		}
		else if ($lead->bookAppointment == 'Yes') {
			$appointment = $lead->getAppointment();

			$dateTime = new DateTime($appointment->time);
			$time = $dateTime->format('g:i A');
			$date = $dateTime->format('d-m-Y');

			$salesManDetail = ['time' => $time, 'date' => $date, 'salesManUserID' => $appointment->salesMember];

			$data['isSalesManExists'] = true;
			$data['salesManDetail'] = $salesManDetail;
		}

		$data['formFieldsNames'] = $leadFormFields;

		return View::make('site/lead/viewlead', $data);
	}

	public function saveLeadTimeTaken() {
		if(env('APP_DEBUG', false) == true) {
			\Debugbar::disable();
		}
		set_time_limit(0);
		ignore_user_abort(true);
		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			$timeTaken = Input::get('timeTaken');

			$lead = Lead::find($leadID);
			$lead->timeTaken = $timeTaken;
			$lead->timeOpened = DB::raw('NOW()');
			$lead->edited_by_user_id = $this->user->id;
			$lead->save();

			if(Session::has('callHistoryID')){
				//update time to call history
				$lastTimeTaken = Session::get('lastTimeTaken');
				$newTime = $timeTaken - $lastTimeTaken;
				$lead->updateCallHistory(['callTime' => $newTime]);
			}
		}
	}

	/**
	 * Get a new lead for given campaign. This is called via ajax when user changes the campaign
	 * @return string Json String with lead ID
	 * @throws Exception
	 */
	public function selectedCampaignLead() {
		$data = [];

		if (Request::ajax()) {
			$campaignID = Input::get('campaignID');
			$campaign = Campaign::find($campaignID);
			$user = $this->user;

			$lead = $campaign->getLandingLead($user);

			$leadCount = $campaign->getUnactionedLeadsCount();

			if ($leadCount == 0) {
				// There are no unactioned leads in the campaign
				// So, we need to mark that this campaign should not be ended when leads finish
				Session::put('toBeEnded', false);
			}
			else {
				Session::put('toBeEnded', true);
			}

			$data['status'] = 'success';
			$data['leadID'] = $lead->id;
		}

		return $data;
	}

	public function saveLeadFormdata() {
		set_time_limit(0);
		ignore_user_abort(true);

		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			$fieldID = Input::get('fieldID');
			$fieldValue = Input::get('fieldValue');

			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);

			$field = FormFields::where('id', $fieldID)->first();

			if ($field->fieldName == 'Post/Zip code') {
				$postalCodeDetails = PostalCode::select(['latitude','longitude','countryCode'])
						->where('postalCode', $fieldValue)
						->first();

				if ($postalCodeDetails) {
					$lead->latitude = $postalCodeDetails->latitude;
					$lead->longitude = $postalCodeDetails->longitude;
					$lead->countryCode = $postalCodeDetails->countryCode;
				}
				else {
					//checking whether countryCode is NULL or not
					if ($lead->countryCode != null) {
						$points = CommonController::getLatitudeAndLongitude($fieldValue, $lead->countryCode);

						$lead->latitude = $points['latitude'];
						$lead->longitude = $points['longitude'];
					}
				}
				$lead->save();
			}
			else if ($field->fieldName == 'Address') {
				$ch = curl_init();
				$url = "http://maps.googleapis.com/maps/api/geocode/json?address={$fieldValue}";

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				$output = curl_exec($ch);
				curl_close($ch);

				$output = json_decode($output, true);

				if (!empty($output['results'][0]['address_components'][4]['short_name'])) {
					$postalCode = $output['results'][0]['address_components'][4]['short_name'];
				}
				if (!empty($output['results'][0]['geometry']['location']['lat'])) {
					$latitude = $output['results'][0]['geometry']['location']['lat'];
				}
				if (!empty($output['results'][0]['geometry']['location']['lng'])) {
					$longitude = $output['results'][0]['geometry']['location']['lng'];
				}
				if (!empty($output['results'][0]['address_components'][3]['short_name'])) {
					$countryCode = $output['results'][0]['address_components'][3]['short_name'];
				}

				//if postalCode and Country code exists
				if (!empty($postalCode) && !empty($countryCode)) {
					$postalCodeCount = PostalCode::where('countryCode', $countryCode)
						->where('postalCode', $postalCode)
						->count();

					if ($postalCodeCount == 0) {
						$newPostalCode = new PostalCode;
						$newPostalCode->postalCode = $postalCode;
						$newPostalCode->countryCode = $countryCode;
						$newPostalCode->latitude = $latitude;
						$newPostalCode->longitude = $latitude;
						$newPostalCode->accuracy = 0;
						$newPostalCode->save();
					}
				}

				//if latitude and longitude is not empty then save these points to lead
				if (!empty($latitude) && !empty($longitude)) {
					$lead->latitude = $latitude;
					$lead->longitude = $longitude;
					$lead->save();
				}
			} else if($field->type == 'date') {
				$fieldValue =  FormFields::validateDateFieldValue($fieldValue);
			}

			//calculate lead hash with new value
			$string = "";
			$leadCustomDataValues = LeadCustomData::select(['fieldID', 'value'])
				->where('leadID', '=', $leadID)->orderBy("id", 'ASC')->get();

			foreach ($leadCustomDataValues as $leadCustomDataValue) {
				if($leadCustomDataValue->fieldID == $fieldID){
					$string .= $fieldValue;
				}
				else{
					$string .= $leadCustomDataValue->value;
				}
			}

			//echo $string;die;

			$leadHash = md5($string . $lead->campaignID);

			try{
				// Updating timeEdited for lead
				$lead->timeEdited = new DateTime;
				$this->lastActioner = $this->user->id;

				$lead->leadHash = $leadHash;
				$lead->save();

				LeadCustomData::where('leadID', $leadID)
					->where('fieldID', $fieldID)
					->update(['value' => $fieldValue]);

				return ['status' => 'success'];
			}
			catch(Exception $e){
				Log::error("saveleadformdata error :" . $e->getMessage());
				return ['status' => 'error', 'code' => $e->getCode()];
			}
		}
	}

	public function bookThisSalesman() {
		if (Request::ajax()) {
			$user = $this->user;

			$leadID = Input::get('leadID');
			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);

			$emailExists = Input::get('emailExists') == 'true';

			$salesmanID = Input::get('salesmanID');
			$date1 = Input::get('date1');
			$timepicker1 = Input::get('timepicker1');

			$bookedTime = $date1 . ' ' . $timepicker1;
			$date = new DateTime($bookedTime);
			$bookedTime = $date->format('Y-m-d H:i:s');
			
			$salesMan = SalesMember::find($salesmanID);
			if(!$salesMan) {
				return [
					"status"  => "error", 
					"message" => "Trying to create appointment with unknown salesman",
				];
			}
			$this->authorize('is-my-team-salesman', $salesMan);

			// Find a conflicting appointment

			$prevAppointment = Appointment::where("salesMember", "=", $salesmanID)
				->whereBetween(DB::raw("TIMEDIFF(`time`, '{$bookedTime}')"), ['00:00:00', '00:15:00'])->count();

			if ($prevAppointment > 0) {
				return ["status"  => "error", "message" => "The salesman already has an appointment at that date and time"];
			}

			//Deleting Appointment before Entering New Appointment
			Appointment::where('leadID', $leadID)->delete();

			//Creating new Entry in Appointment Table
			$newSalesMan = new Appointment;
			$newSalesMan->leadID = $leadID;
			$newSalesMan->salesMember = $salesmanID;
			$newSalesMan->time = $bookedTime;
			$newSalesMan->creator = $user->id;
			$newSalesMan->timeCreated = new DateTime;
			$newSalesMan->save();

			$appointmentID = $newSalesMan->id;
			//Updating Booking status to Yes
			$lead->bookAppointment = 'Yes';
			// Updating timeEdited for lead
			$lead->timeEdited = new DateTime;
			$lead->lastActioner = $this->user->id;
			$lead->save();
			//Lead::where('id', $leadID)->update(['bookAppointment' => 'Yes']);


			$salesManFirstName = $salesMan->firstName;
			$salesManLastName = $salesMan->lastName;
			$salesManEmail = $salesMan->email;
			$bookedUserFirstName = $user->firstName;

			//Generating invoice and sending it to user
			$invoice['salesManName'] = $salesManFirstName . ' ' . $salesManLastName;
			$invoice['bookingDate'] = $date1;
			$invoice['bookingTime'] = $timepicker1;
			$invoice['bookingUTCTime'] = \Timezone::convertToUTC($bookedTime, $user->timeZoneName);
			$invoice['leadCustomData'] = LeadCustomData::where('leadID', '=', $leadID)->get();

			set_time_limit(0);
			$pdfPath = storage_path() . '/attachments/appointment/appointment_' . $appointmentID . '.pdf';
			$pdf = App::make('dompdf.wrapper');
			$pdf->loadView("site/appointment/appointment", $invoice);
			$pdf->save($pdfPath);

			//Sending CallBack Message to user
			$newAccountTemplate = AdminEmailTemplate::where('id', 'APPOINTMENT')->first();

			$fieldValues = ['FIRST_NAME'       => $salesManFirstName,
			                'APPOINTMENT_DATE' => $date1,
			                'APPOINTMENT_TIME' => $timepicker1,
			                'APPOINTMENT_UTC_TIMESTAMP' => $invoice['bookingUTCTime'],
			                'BOOKING_USER'     => $bookedUserFirstName,
			                'SITE_NAME'        => Setting::get('siteName')];

			$mailSettings = CommonController::loadSMTPSettings("inbound");

			$generalEmailInfo = [
				'from' => $mailSettings["from"]["address"],
				'fromName' => $mailSettings["from"]["name"],
				'replyTo' => $mailSettings["replyTo"]
			];

			$emailInfo = CommonController::setEmailConfiguration($lead, $user, $generalEmailInfo, /*$forceFromAddressFromCampaign*/ true);

			$emailInfo['subject'] = $newAccountTemplate->subject;
			$emailInfo['to']    = $salesManEmail;
			$emailInfo['bccEmail'] = $user->bccEmail;

			$emailText = $newAccountTemplate->content;
			$attachments = ['/attachments/appointment/appointment_' . $appointmentID . '.pdf'];

			// prepare and add calender info
			//get lead detail (first name and last name)

			$leadDetail = LeadCustomData::getLeadInformation($leadID, ['First Name','lastName']);

			$title = 'Appointment - ' . $leadDetail['First Name'] . ' ' . $leadDetail['lastName'];
			$startDate = CommonController::getTimeInUserTimeZone(strtotime($bookedTime), $this->user);

			$endTime = strtotime("+30 minutes", strtotime($bookedTime));
			$endDate = CommonController::getTimeInUserTimeZone($endTime, $this->user);

			//prepare lead information
			$detail = "You have received a sales appointment booking for the following lead.\n";
			$detail .= "The following appointment was booked by $bookedUserFirstName.\n\n";
			$detail .= "Lead contact information and details are following\n";

			foreach ($invoice['leadCustomData'] as $data) {
				$detail .= $data->fieldName . ': ' . $data->value . "\n";
			}

			//update call history
			$callHistoryData = [
				'appointment' => $salesmanID,
				'appointment_with_sales_name' => $salesManFirstName . ' ' . $salesManLastName,
			];
			if(Session::has('callHistoryID')) {
				$lead->updateCallHistory($callHistoryData);
			}
			else {
				$lead->createLeadCallHistory($this->user->id);
				$lead->updateCallHistory($callHistoryData);
			}

			//$detail = "A new follow-up call has been booked for you.\n\nTo view all pending call backs for you, visit:".URL::route('user.leads.pendingcallbacks');
			# 1. For google calender prepare link

			$googleLink =
				"https://www.google.com/calendar/render?action=TEMPLATE&text=$title&dates=$startDate/$endDate&details=" .
				urlencode($detail) . "&pli=1&uid=&sf=true&output=xml&ctz=" .
				urlencode($user->timeZoneName) . "#eventpage_6";

			# 2. For outlook calender prepare ics fie
			# 3. For iCalender prepare ics file

			$fieldValues['GOOGLE_LINK'] = $googleLink;
			$fieldValues['OUTLOOK_LINK'] =
				URL::route('addCalender') . '?detail=' . urlencode($detail) 
				. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
				. '&dateStart=' .  CommonController::convertLocalToUTC($startDate) 
				. '&title=' . $title;
			$fieldValues['ICAL_LINK'] =
				URL::route('addCalender') . '?detail=' . urlencode($detail) 
				. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
				. '&dateStart=' .  CommonController::convertLocalToUTC($startDate) 
				. '&title=' . $title;

			$fieldValues['Agent'] = $this->user->firstName.' '.$this->user->lastName;

			if(Input::get('sendEmailToSalesman') == 'true'){
				CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments, /*$throw*/ false, /*$async*/ true);
			}

			//send mail to lead if email exists
			if($emailExists && Input::get('sendEmailToLead') == 'true') {
				//prepare and send mail
				#1. first get email template
				$emailFields['AGENT'] = $salesManFirstName . ' ' . $salesManLastName;
				$emailFields['DATE'] = $date1;
				$emailFields['TIME'] = $timepicker1;
				$emailFields['UTC_TIMESTAMP'] = $invoice['bookingUTCTime'];


				$detail = "Further to our conversation today I have scheduled an appointment with you on:\n";
				$detail .= "Date : $date1 \n";
				$detail .= "Time : $timepicker1 \n";
				$detail .= "I look forward to meeting with you \n ";
				$detail .= $emailFields['AGENT'];

				$emailFields['GOOGLE_LINK'] =
					"https://www.google.com/calendar/render?action=TEMPLATE&text=$title&dates=$startDate/$endDate&details=" .
					urlencode($detail) . "&pli=1&uid=&sf=true&output=xml&ctz=" .
					urlencode($user->timeZoneName) . "#eventpage_6";

				$emailFields['OUTLOOK_LINK'] =
					URL::route('addCalender') . '?detail=' . urlencode($detail) 
					. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
					. '&dateStart=' .  CommonController::convertLocalToUTC($startDate) 
					. '&title=' . $title;
				$emailFields['ICAL_LINK'] =
					URL::route('addCalender') . '?detail=' . urlencode($detail) 
					. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
					. '&dateStart=' .  CommonController::convertLocalToUTC($startDate) 
					. '&title=' . $title;

				$this->sendBookingEmailToLead($lead, 'salesman', $emailFields);
			}

			return ["status"  => "success", "message" => "Appointment booked successfully"];
		}
	}

	public function bookAppointmentStatusNo() {
		if (Request::ajax()) {
			$leadID = Input::get('leadID');

			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);
			// Updating timeEdited for lead
			$lead->timeEdited = new DateTime;
			$lead->lastActioner = $this->user->id;
			$lead->bookAppointment = 'No';
			$lead->save();

			Appointment::where('leadID', $leadID)->delete();
		}
	}

	public function changeLeadInterest() {
		set_time_limit(0);
		ignore_user_abort(true);
		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			$intertestType = Input::get('intertestType');

			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);
			// Updating timeEdited for lead
			$lead->timeEdited = new DateTime;
			$lead->lastActioner = $this->user->id;
			$lead->interested = $intertestType;
			$lead->save();
		}
	}

	public function changeEmailTemplate() {
		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			$lead = Lead::find($leadID);

			$emailTemplateID = Input::get('emailTemplateID');

			if($emailTemplateID == '') {
				$emailTemplateID = null;
				$lead->emailTemplate = $emailTemplateID;
				$lead->mass_email_template_id = null;

				$lead->save();
			} elseif(starts_with($emailTemplateID, 'mass_')) {
				$emailTemplateID = str_replace('mass_', '', $emailTemplateID);

				$lead->mass_email_template_id = $emailTemplateID;
				$lead->emailTemplate = null;

				$lead->save();

				//update call history
				if(Session::has('callHistoryID')) {
					$lead->updateCallHistory(['mass_email_template_id' => $emailTemplateID]);
					return ['updated' => "old"];
				}
				else {
					$lead->createLeadCallHistory($this->user->id);
					$lead->updateCallHistory(['mass_email_template_id' => $emailTemplateID]);
					return ['updated' => "new"];
				}
			} else {
				$lead->mass_email_template_id = null;
				$lead->emailTemplate = $emailTemplateID;

				$lead->save();

				//update call history
				$emailTemplate = EmailTemplate::find($emailTemplateID);
				if($emailTemplate) {
					$update = [
						'emailTemplate' => $emailTemplateID,
						'email_template_name' => $emailTemplate->name,
					];
					if(Session::has('callHistoryID')) {
						$lead->updateCallHistory($update);
						return ['updated' => "old"];
					}
					else {
						$lead->createLeadCallHistory($this->user->id);
						$lead->updateCallHistory($update);
						return ['updated' => "new"];
					}
				}
			}
		}
	}

	public function saveReferenceNumber() {
		set_time_limit(0);
		ignore_user_abort(true);
		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			$referenceNumber = Input::get('referenceNumber');

			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);
			// Updating timeEdited for lead
			$lead->timeEdited = new DateTime;
			$lead->lastActioner = $this->user->id;
			$lead->referenceNumber = $referenceNumber;
			$lead->save();
		}
	}

	public function removeFollowUpCall() {
		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			
			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);

			// Updating timeEdited for lead
			$lead->timeEdited = new DateTime;
			$lead->lastActioner = $this->user->id;
			$lead->save();

			//Deleting Appointment before Entering New Appointment
			CallBack::where('leadID', $leadID)->delete();
		}
	}

	public function followUpCall() {
		if (Request::ajax()) {
			$user = $this->user;

			$leadID = Input::get('leadID');
			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);
			$lead->timeEdited = new DateTime;
			$lead->lastActioner = $this->user->id;
			$lead->save();

			$callBackUserID = Input::get('callBackUserID');
			$date2 = Input::get('date2');
			$timepicker2 = Input::get('timepicker2');

			$followTime = $date2 . ' ' . $timepicker2;
			$date = new DateTime($followTime);
			$followTime = $date->format('Y-m-d H:i:s');

			//Deleting Appointment before Entering New Appointment
			CallBack::where('leadID', $leadID)->update(['status' => 'Completed']);
			
			$force = Input::get('force');

			if(!$force) {
				// Find a conflicting appointment
				$prevAppointment = CallBack::where("actioner", "=", $callBackUserID)
					->whereBetween(DB::raw("TIMEDIFF(`time`, '{$followTime}')"), ['00:00:00', '00:15:00'])
					->where('status', '!=', 'Completed')
					->count();

				if ($prevAppointment > 0) {
					return ["status"  => "warn_already_scheduled", "message" => "The user already has an follow up call scheduled at that date and time"];
				}
			}
			
			$callBackUserDetail = User::find($callBackUserID);
			if(!$callBackUserDetail) {
				return [
					"status"  => "error", 
					"message" => "Trying to create follow up call with unknown user"
				];
			}
			$this->authorize('is-my-team-member', $callBackUserDetail);

			//Creating new Entry in Appointment Table
			$newCallback = new CallBack;

			$newCallback->leadID = $leadID;
			$newCallback->time = $followTime;
			$newCallback->actioner = $callBackUserID;
			$newCallback->creator = $user->id;
			$newCallback->timeCreated = new DateTime;
			$newCallback->emailSent = Input::get('sendEmailToLead') == 'true' ? 'False' : 'True'; //disable cron sending as well
			$newCallback->save();

			$callBackUserFirstName = $callBackUserDetail->firstName;
			$callBackUserEmail = $callBackUserDetail->email;
			$callBackUserTimeZoneName = $callBackUserDetail->timeZoneName;

			$followTimeInCallBackUserTimeZone = \Timezone::convertFromUTC(\Timezone::convertToUTC($followTime, $user->timeZoneName), $callBackUserTimeZoneName);
			$followTimestamp = strtotime($followTimeInCallBackUserTimeZone);

			//Sending CallBack Message to user
			$newAccountTemplate = AdminEmailTemplate::where('id', 'CALLBACK')->first();
			
			$fieldValues = ['FIRST_NAME'     => $callBackUserFirstName,
			                'CALL_TIME'      => date('H:i:s', $followTimestamp),
			                'CALL_DATE'      => date('d-m-Y', $followTimestamp),
							'CALL_UTC_TIMESTAMP' => \Timezone::convertToUTC($followTime, $user->timeZoneName),
			                'CALL_BACKS_URL' => URL::route('user.leads.pendingcallbacks'),
			                'SITE_NAME'      => Setting::get('siteName')];

			$emailText = $newAccountTemplate->content;

			$mailSettings = CommonController::loadSMTPSettings("inbound");
			$generalEmailInfo = [
				'from' => $mailSettings["from"]["address"],
				'fromName' => $mailSettings["from"]["name"],
				'replyTo' => $mailSettings["replyTo"]
			];

			$emailInfo = CommonController::setEmailConfiguration($lead, $user, $generalEmailInfo, /*$forceFromAddressFromCampaign*/ true);

			$emailInfo['subject'] = $newAccountTemplate->subject;
			$emailInfo['to']    = $callBackUserEmail;
			$emailInfo['bccEmail'] = $user->bccEmail;

			//update callback user id to call history
			$callHistoryData = [
				'callBookedWith' => $callBackUserID,
				'call_booked_with_user_name' => $callBackUserDetail->firstName . ' ' . $callBackUserDetail->lastName,
			];
			
			if(Session::has('callHistoryID')){
				//update time to call history
				$lead->updateCallHistory($callHistoryData);
			}
			else{
				$lead->createLeadCallHistory($this->user->id);
				$lead->updateCallHistory($callHistoryData);
			}

			// prepare and add calender info

			//get lead detail (first name and last name)

			$leadDetail = LeadCustomData::getLeadInformation($leadID, ['First Name',
			                                                        'lastName']);

			$leadData = LeadCustomData::where('leadID', $leadID)->get();

			$title = 'Follow-Up Call - ' . $leadDetail['First Name'] . ' ' . $leadDetail['lastName'];

			$callBackUser = User::find($callBackUserID);
			$startDate = CommonController::getTimeInUserTimeZone(strtotime($followTime), $callBackUser);

			$endTime = strtotime("+15 minutes", strtotime($followTime));
			$endDate = CommonController::getTimeInUserTimeZone($endTime, $callBackUser);

			$detail =
				"A new follow-up call has been booked for you.\n\nTo view all pending call backs for you, visit: \n" .
				URL::route('user.leads.pendingcallbacks')."\n\n";

			$detail .= "Lead contact information and details are following:\n";

			foreach ($leadData as $data) {
				$detail .= $data->fieldName . ': ' . $data->value . "\n";
			}

			# 1. For google calender prepare link

			$googleLink =
				"https://www.google.com/calendar/render?action=TEMPLATE&text=$title&dates=$startDate/$endDate&details=" .
				urlencode($detail) . "&pli=1&uid=&sf=true&output=xml&ctz=" .
				urlencode($user->timeZoneName) . "#eventpage_6";

			# 2. For outlook calender prepare ics fie
			# 3. For iCalender prepare ics file

			$fieldValues['GOOGLE_LINK'] = $googleLink;
			$fieldValues['OUTLOOK_LINK'] =
				URL::route('addCalender') . '?detail=' . urlencode($detail) 
				. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
				. '&dateStart=' . CommonController::convertLocalToUTC($startDate) .
				'&title=' . $title;
			$fieldValues['ICAL_LINK'] =
				URL::route('addCalender') . '?detail=' . urlencode($detail) 
				. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
				. '&dateStart=' . CommonController::convertLocalToUTC($startDate) .
				'&title=' . $title;

			$fieldValues['Agent'] = $this->user->firstName.' '.$this->user->lastName;

			$attachments = [];
			if(Input::get('sendEmailToMember') == 'true'){
				CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments, /*$throw*/ false, /*$async*/ true);
			}

			$emailExists = Input::get('emailExists') == 'true';

			//send mail to lead if email exists
			if($emailExists && Input::get('sendEmailToLead') == 'true'){
				//prepare and send mail
				#1. first get email template
				$emailFields['AGENT'] = $user->firstName . " " . $user->lastName;
				$emailFields['DATE'] = date('d-m-Y', $followTimestamp);
				$emailFields['TIME'] = date('H:i:s', $followTimestamp);
				$emailFields['UTC_TIMESTAMP'] = \Timezone::convertToUTC($followTime, $user->timeZoneName);

				$detail = "Further to our conversation today I have scheduled a call back on: \n";
				$detail .= "Date : $date2 \n";
				$detail .= "Time : $timepicker2 \n";
				$detail .= "I look forward to speaking to you \n";
				$detail .= $emailFields['AGENT'];
				
				$googleLink =
					"https://www.google.com/calendar/render?action=TEMPLATE&text=$title&dates=$startDate/$endDate&details=" .
					urlencode($detail) . "&pli=1&uid=&sf=true&output=xml&ctz=" .
					urlencode($user->timeZoneName) . "#eventpage_6";

				# 2. For outlook calender prepare ics fie
				# 3. For iCalender prepare ics file

				$emailFields['GOOGLE_LINK'] = $googleLink;
				$emailFields['OUTLOOK_LINK'] =
					URL::route('addCalender') . '?detail=' . urlencode($detail) 
					. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
					. '&dateStart=' .  CommonController::convertLocalToUTC($startDate) 
					. '&title=' . $title;
				$emailFields['ICAL_LINK'] =
					URL::route('addCalender') . '?detail=' . urlencode($detail) 
					. '&dateEnd=' . CommonController::convertLocalToUTC($endDate) 
					. '&dateStart=' .  CommonController::convertLocalToUTC($startDate) 
					. '&title=' . $title;

				$this->sendBookingEmailToLead($lead, 'followUp', $emailFields);
			}
			return ["status"  => "success", "message" => "Follow up call scheduled successfully"];
		}
	}

	public function goToAction() {
		$data = [];

		if (Request::ajax()) {
			$user = $this->user;

			$leadID = Input::get('leadID');
			$actionType = Input::get('actiontype');
			$sendEmailTemplate = Input::get("sendEmailTemplate");

			if ($actionType == 'back') {
				$lead = Lead::find($leadID);
				if(!$lead){
					return [
						'status' => 'error', 
						'message' => 'Lead not found.',
						'success' => 'fail'
					];
				}
				$this->authorize('can-see-campaign', $lead->campaign);

				$lead->markAsActioned($user);
				$previousLead = $lead->campaign->getPreviousLead($user, $lead->id);

				if ($previousLead) {
					$data['success'] = 'success';
					$data['leadID'] = $previousLead->id;

					// User click on back button so forgetting setting Session maxRecordReached variable;
					Session::forget('maxRecordReached');
				}
				else {
					$data['success'] = 'fail';
				}

			} else if ($actionType == 'next' || $actionType == 'skip') {
				// CampaignID of lead
				$lead = Lead::find($leadID);
				if(!$lead){
					return [
						'status' => 'error', 
						'message' => 'Lead not found.',
						'success' => 'fail'
					];
				}
				$this->authorize('can-see-campaign', $lead->campaign);
				
				//$campaignID = $lead->campaignID;
				if ($actionType == 'next') {
					$lead->markAsActioned($user);
				} else {
					$lead->timeOpened = null;
					$lead->edited_by_user_id = null;
					$lead->save();
				}

				$campaign = $lead->campaign;
				$nextLead = $campaign->getNextLead($user, $lead);

				if ($nextLead != null) {
					$data['success'] = 'success';
					$data['leadID'] = $nextLead->id;
				}
				else {
					// We could not find next lead. So, we need to finish this campaign

					//Mark Campaign as finished and redirected it to Campaigns page
					//Counting total Leads
					//$totalLeads = Lead::where('campaignID', $campaignID)->count();

					$data["redirect"] = "redirect";
					$data['success'] = 'success';
				}
			}

			if ($actionType != 'skip' && $sendEmailTemplate != "" && $sendEmailTemplate != "writeNew") {
				$this->sendLeadDetailEmailToUser($leadID);
			}

		}
		return json_encode($data);
	}

	public function getEmailTemplateData() {
		if (Request::ajax()) {

			$emailTemplateID = Input::get('emailTemplateID');

			$data = [];
			if($emailTemplateID == '' || $emailTemplateID == "writeNew") {
				return ['status' => "fail", 'message' => 'Template is required'];
			} elseif(starts_with($emailTemplateID, 'mass_')) {
				$emailTemplateID = str_replace('mass_', '', $emailTemplateID);
				$emailTemplate = MassEmailTemplate::find($emailTemplateID);
				$data['text'] = $emailTemplate->content;
				$data['subject'] = $emailTemplate->subject;

			} else {
				$data = [];
				$emailTemplate = EmailTemplate::find($emailTemplateID);
				$data['text'] = $emailTemplate->templateText;
				$data['subject'] = $emailTemplate->subject;
			}
			
			$files = $emailTemplate->getEmailAttachmentsToBeSend();
			foreach($files as $file) {
				//TODO: add icon
				$data['attachments'][] = [
					'file_path' => $file
					, 'file' => basename($file)
					, 'size' => filesize(storage_path() . $file)
					, 'icon' => CommonController::getIconForFileType(storage_path() . $file, 'path')
				];
			}
			$data['status'] = 'success';
			return $data;
		}
	}

	private function sendLeadDetailEmailToUser($leadID) {
		$user = $this->user;
		set_time_limit(0);

		$lead = Lead::find($leadID);
		if(!$lead) {
			return false;
		}
		$this->authorize('can-see-campaign', $lead->campaign);
		$leadCustomData = $lead->getCustomData();

		// Is Email entered or exists in custom field data
		$isEmailFieldExists = $leadCustomData->filter(function ($model) {
			return $model->fieldName == "Email";
		});

		//check if email template or mass email template exists
		$emailTemplateID = $lead->emailTemplate;
		$massEmailTemplateID = $lead->mass_email_template_id;

		if($emailTemplateID == null && $massEmailTemplateID == null) {
			return false;
		}

		if($isEmailFieldExists->count() <= 0) {
			return false;
		}

		// If email id is not blank
		$fieldValues = [];
		foreach ($leadCustomData as $leadFieldsDetail) {
			$fieldValues[ $leadFieldsDetail->fieldName ] = $leadFieldsDetail->value;

			if ($leadFieldsDetail->fieldName == "Email") {
				$email = $leadFieldsDetail->value;
			}
		}

		$fieldValues['Agent'] = $this->user->firstName.' '.$this->user->lastName;

		$mailSettings = CommonController::loadSMTPSettings("outbound");
		$generalInfo = [
				'from'     => $mailSettings["from"]["address"],
				'fromName' => $user->firstName . " " . $user->lastName,
				'replyTo'  => $user->email
		];

		Config::set("mail.from.name", $user->firstName . " " . $user->lastName);

		$emailInfo = CommonController::setEmailConfiguration($lead, $user, $generalInfo, /*$forceFromAddressFromCampaign*/ true);

		$emailInfo['to'] = $email;
		$emailInfo['bccEmail'] = $user->bccEmail;

		if($emailTemplateID != null) {
			// Checking for Email Setting exists or not in SMTP settings table
			$emailTemplate = EmailTemplate::find($emailTemplateID);
			$emailText = $emailTemplate->templateText;
			$emailInfo['subject'] = $emailTemplate->subject;

			//get email template attachments
			$defaultAttachments = $emailTemplate->getEmailAttachmentsToBeSend();

			CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $defaultAttachments, /*$throw*/ false, /*$async*/ true);
		}
		elseif($massEmailTemplateID != null) {
			// Checking for Email Setting exists or not in SMTP settings table
			$massEmailTemplate = MassEmailTemplate::find($massEmailTemplateID);
			$emailText = $massEmailTemplate->content;
			$emailInfo['subject'] = $massEmailTemplate->subject;

			//get email template attachments
			$defaultAttachments = $massEmailTemplate->getEmailAttachmentsToBeSend();

			$emailInfo['replyTo'] = $massEmailTemplate->reply_to;
			$emailInfo['fromName'] = $massEmailTemplate->from_name;

			CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $defaultAttachments, /*$throw*/ false, /*$async*/ true);
		}


		//reset template
		$lead->emailTemplate = null;
		$lead->mass_email_template_id = null;
		// Updating timeEdited for lead
		$lead->timeEdited = new DateTime;
		$lead->lastActioner = $this->user->id;
		$lead->save();

		LeadInfoEmails::insert([
			'lead_id' => $lead->id,
			'campaign_id' => $lead->campaignID,
			'created_at' => new DateTime,
			'user_id' => $this->user->id,
		]);
	}

	public function skipAnDelete() {
		$data = [];

		if (Request::ajax()) {
			$user = $this->user;;

			$leadID = Input::get('leadID');
			$lead = Lead::find($leadID);

			$leadCampaignDetail = Lead::find($leadID);
			$campaign = $leadCampaignDetail->campaign;

			Lead::where('id', $leadID)->delete();

			// Updating totalLeads in Campaign
			$campaign->totalLeads -= 1;
			$campaign->save();
			$campaign->recalculateCallsRemaining();
			$campaign->recalculateTotalFilteredLeads();

			$nextLead = $campaign->getNextLead($user, $lead);

			if ($nextLead != null) {
				$data['success'] = 'success';
				$data['leadID'] = $nextLead->id;
			}
			else {
				$data['success'] = 'finish';
			}
		}

		return json_encode($data);
	}

	public function saveAndExit() {
		if (Request::ajax()) {
			$leadID = Input::get("leadID");
			$sendEmailTemplate = Input::get("sendEmailTemplate");
			
			$lead = Lead::findOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);

			$user = $this->user;;
			$lead->markAsActioned($user);

			//Sending Lead Detail to Session Email Id which is created when a new lead is generated

			if ( $sendEmailTemplate != "" && $sendEmailTemplate != "writeNew") {
				$this->sendLeadDetailEmailToUser($leadID);
			}

			if(Session::has('lead_opened_via_pending_callbacks')) {
				if(Session::get('lead_opened_via_pending_callbacks') == $lead->id) {
					$data['redirect_link'] = route('user.leads.pendingcallbacks');
				} else {
					$data['redirect_link'] = route('user.campaigns.start');
				}
				Session::forget('lead_opened_via_pending_callbacks');
			} else {
				$data['redirect_link'] = route('user.campaigns.start');
			}


			$data['success'] = 'success';
			return json_encode($data);
		}
	}

	public function usersTotalPendingCallBacks() {
		if (Request::ajax()) {
			$userID = Auth::user()->get()->id;

			$totalPendingCallBacks = CallBack::where('actioner', $userID)
				->where('status', 'Pending')
				->count();

			$data['success'] = "success";
			$data['totalPendingCallBacks'] = $totalPendingCallBacks;
		}

		return json_encode($data);
	}

	public function pendingCallBacks() {
		$userCampaigns = $this->user->getActiveCampaigns();

		$data = [
				'appointmentsMenuActive'  => 'nav-active active',
				'appointmentsStyleActive' => 'display: block',
				'appointmentsPendingStyleActive' => 'active',
				'campaignLists' => $userCampaigns
		];

		if(Session::has('pending_callbacks_filter')) {
			$data['pendingCallbacksFilter'] = Session::get('pending_callbacks_filter');
		}

		return View::make('site/lead/pendingcallbacks', $data);
	}

	public function ajaxPendingDaysData() {
		$campaignID = Input::get('campaignID');
		$pendingDays = Input::get('pendingDays');
		$leadType = Input::get('leadType');

		Session::put('pending_callbacks_filter', 
			[
				'campaignID' => $campaignID,
				'pendingDays' => $pendingDays,
				'leadType' => $leadType,
			]
		);
		
		$pendingDays .= " days";

		$pendingDatas = [];

		$date = new DateTime;
		$today = $date->format('Y-m-d');
		$date->modify($pendingDays);
		$exactPendingDays = $date->format('Y-m-d');

		if($leadType == 'All') {
			$teamMembersIDs = $this->user->getTeamMembersWithManagerIDs();
		}
		else if($leadType == 'Me') {
			$teamMembersIDs = [$this->user->id];
		}

		if($campaignID == 'All') {
			$campaigns = $this->user->getActiveCampaigns()->lists('id')->all();
		}
		else {
			$campaigns = [$campaignID];
		}

		$pendingLeads = CallBack::select(['callbacks.id as callBackID', 'callbacks.leadID', 'campaigns.formID', 'campaigns.name as campaignName',
				'callbacks.time as callBackOn', 'users.firstName', 'users.lastName', 'campaigns.id as campaignID'])
				->join('leads', 'leads.id', '=', 'callbacks.leadID')
				->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
				->join('users', 'users.id', '=', 'callbacks.actioner')
				->whereIn('users.id', $teamMembersIDs)
				->where('callbacks.status', 'Pending');

		$pendingLeads->whereIn('campaigns.id', $campaigns);


		if ($pendingDays == "+0 days") {
			$pendingLeads->where(DB::raw('DATE(callbacks.time)'), '=', $exactPendingDays);
		}
		else if ($pendingDays == "+1 days") {
			$pendingLeads->where(DB::raw('DATE(callbacks.time)'), '=', $exactPendingDays);
		}
		else if ($pendingDays == "+10000 days") {
			$pendingLeads->where('callbacks.time', '<', $today);
		}
		else if ($pendingDays != "-0 days") {
			$pendingLeads->where('callbacks.time', '<=', $exactPendingDays)
				->where('callbacks.time', '>', $today);
		}

		$pendingLeads = $pendingLeads->orderBy('callbacks.time', 'ASC')->paginate($this->perPage);

		$pendingLeads->setPath('ajaxpendingdaysdata');

		//if($campaignID != 'All') {
		/*
			$fieldNames = Campaign::whereIn('campaigns.id', $campaigns)
					->join('formfields', 'formfields.formID', '=', 'campaigns.formID')
					->lists('formfields.fieldName')->all();
		 */
		//}
		$fieldNames = FormFields::getFieldNamesForCampaigns($campaigns);


		$customValues = LeadCustomData::getValues($pendingLeads->pluck('leadID'), $fieldNames);

		foreach ($pendingLeads as $pendingLead) {
			$campaignName = $pendingLead->campaignName;
			$callBackOn = $pendingLead->callBackOn;
			$agent = $pendingLead->firstName . ' ' . $pendingLead->lastName;
			$leadID = $pendingLead->leadID;
			$callBackID = $pendingLead->callBackID;

			$leadInfo = [];

			//if($campaignID == 'All') {
			//	$leadCustomData = LeadCustomData::where("leadID", $leadID)->get();
			//
			//	$companyName = $this->getCustomUserDataByName($leadCustomData, ['Company Name', 'company name', 'comapnyname']);
			//	$firstName = $this->getCustomUserDataByName($leadCustomData, ['First Name', 'first name']);
			//	$lastName = $this->getCustomUserDataByName($leadCustomData, ['Last Name', 'last name']);
			//}
			//else {
			//$leadInfo = LeadCustomData::getLeadInformation($pendingLead->leadID, $fieldNames);
			$leadInfo = $customValues[$pendingLead->leadID];


			$companyName = '-';
			$firstName = '-';
			$lastName = '-';

			if(sizeof($leadInfo > 0)) {
				foreach($leadInfo as $key => $value) {
					if(in_array($key, CommonController::getFieldVariations('Company Name'))){
						$companyName = $value;
					}
					elseif(in_array($key, CommonController::getFieldVariations('First Name'))){
						$firstName = $value;
					}
					elseif(in_array($key, CommonController::getFieldVariations('Last Name'))){
						$lastName = $value;
					}
				}
			}
			//}

			$pendingDatas[] = [
				'campaignName' => $campaignName,
				'callBackOn' => $callBackOn,
				'agent' => $agent,
				'leadID' => $leadID,
				'callBackID' => $callBackID,
				'companyName' => $companyName,
				'firstName' => $firstName,
				'lastName' => $lastName,
				'leadInfo' => $leadInfo
			];
		}

		$data['pendingDatas'] = $pendingDatas;
		$data['pendingLeads'] = $pendingLeads;

		$data['campaignID'] = $campaignID;

		return View::make('site/lead/ajaxpendingdaysdata', $data);
	}

	private function getCustomUserDataByName($leadCustomData, $fieldNameArray, $type = null) {

		$field = $leadCustomData->filter(function ($modal) use ($fieldNameArray) {
			return in_array($modal->fieldName, $fieldNameArray);
		})->first();

		if($type == null) {
			if ($field) {
				return $field->value;
			}
			else {
				return "-";
			}
		}
		else if($type == 'fullData') {
			if ($field) {
				return $field;
			}
			else {
				return null;
			}
		}
	}

	public function cancelCallBack() {
		$data = [];

		if (Request::ajax()) {
			$callBackID = Input::get('callBackID');

			CallBack::where('id', $callBackID)->delete();

			$data['success'] = 'success';
		}

		return json_encode($data);
	}

	public function followUpCallBackBulkAction(SimpleRequest $request) {
		$action = $request->input('action');
		$callbacks = $request->input('callbacks');

		
		if(!is_array($callbacks)) {
			return ["status"  => "error", "message" => "Follow up calls are required"];
		}
		
		if(in_array($action, ['cancel', 'already_contacted'])) {
			CallBack::whereIn('id', $callbacks)
				->whereIn('creator', $this->user->getTeamMembersWithManagerIDs())
				->delete();
			return ["status"  => "success", "message" => "Follow up calls successfully proceed"];
		}

		return ["status"  => "error", "message" => "Bulk operation failed"];
	}

	public function deleteLead() {
		$data = [];

		if (Request::ajax()) {
			$leadID = Input::get('leadID');

			Lead::where('id', $leadID)->delete();

			$data['success'] = 'success';
		}

		return json_encode($data);
	}

	public function inBoundCall() {
		$user = $this->user;

		$campaignLists = $user->getActiveCampaigns();

		/*unused
		$userCampaigns = $campaignLists->lists('id')->all();

		$formFields = Campaign::select(['formfields.fieldName', 'formfields.type', 'formfields.values',
			DB::raw('GROUP_CONCAT(campaigns.id SEPARATOR \' \') AS campaignIDs')])
		                      ->whereIn('campaigns.id', $userCampaigns)
		                      ->join('formfields', 'formfields.formID', '=', 'campaigns.formID')
						   	  ->groupBy(['formfields.type', 'formfields.fieldName', 'formfields.values'])
							  ->orderBy('formfields.fieldName')
							  ->get();
		 */

		$data = [
			'leadsMenuActive'  => 'nav-active active',
			'leadsStyleActive' => 'display: block',
			'leadsInboundStyleActive' => 'active',
			'campaignLists'    => $campaignLists,
		];

		return View::make('site/lead/inboundcall', $data);
	}

	public function showCampaignFormField() {
		if (Request::ajax()) {
			$campaignID = Input::get('campaignID');
			$campaignDetails = Campaign::where('id', $campaignID)->first();
			$campaignFormID = $campaignDetails->formID;

			$formFields = FormFields::where('formID', $campaignFormID)->get();


			$data['formFields'] = $formFields;

			return View::make('site/lead/inboundcampaignformfield', $data);
		}
	}

	/* unused
	public function ajaxInboundCallData() {
		$data = [];

		if (Request::ajax()) {
			$campaignID = Input::get('campaignID');

			if($campaignID == 'All') {
				$campaigns = $this->user->getActiveCampaigns()->lists('id')->all();
			}
			else {
				$campaigns = [$campaignID];
			}

			$customQuery = LeadCustomData::select(['campaigns.id', 'leads.id as leadID', 'campaigns.name as campaignName', 'leads.lastActioner',
				'leads.referenceNumber', 'users.firstName', 'users.lastName', 'leads.referenceNumber'])
				->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
				->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
				->leftJoin('users', 'users.id', '=', 'leads.lastActioner')
				->where(function($query) {
					if(Input::has('formFieldID') && Input::has('value')) {
						$formFieldID = Input::get('formFieldID');
						$value = Input::get('value');
						if ($formFieldID == 'reference') {
							$query->where('leads.referenceNumber', 'LIKE', $value . '%');
						}
						else {
							$query->where('leadcustomdata.fieldName', $formFieldID);
							$query->where('leadcustomdata.value', 'LIKE', $value . '%');
						}
					}
				})
				->groupBy('leads.id');

			if(Input::has('column')) {
				$column = Input::get('column');
				$type = Input::get('type');

				if($column == 'contactPerson') {
					$customQuery->leftJoin('leadcustomdata as customData', function($q) {
						$q->on('customData.leadID', '=', 'leads.id')
							->whereIn('customData.fieldName', CommonController::getFieldVariations('First Name'));
					});
					$customQuery->orderBy('customData.value', $type);

					/*$customQuery->leftJoin('leadcustomdata as customData1', function($q) {
						$q->on('customData1.leadID', '=', 'leads.id')
						  ->whereIn('customData1.fieldName', CommonController::getFieldVariations('Last Name'));
					});
					$customQuery->orderBy('customData1.value', $type);* /
				} elseif($column == 'campaign') {

					$customQuery->orderBy('campaigns.name', $type);

				} elseif($column == 'reference') {
					$customQuery->orderBy('leads.referenceNumber', $type);
				} elseif($column == 'zipCode') {
					$customQuery->leftjoin('leadcustomdata as customData', function($join) {
						$join->on('customData.leadID', '=', 'leads.id');
						$join->where(function($q) {
							$q->whereIn('customData.fieldName', CommonController::getFieldVariations('Post/Zip code'))
							  ->orWhereIn('customData.fieldName', CommonController::getFieldVariations('Zip Code'));
						});
					});

					$customQuery->orderBy('customData.value', $type);
				} elseif($column == 'advisor') {
					$customQuery->orderBy('users.firstName', $type);
					$customQuery->orderBy('users.lastName', $type);
				}

				$data['column'] = $column;
				$data['type'] = $type;
			}

			$customFormDatas = $customQuery->whereIn('campaigns.id', $campaigns)->paginate(10);

			// Get Form fields for all campaigns
			$fieldNames = FormFields::getFieldNamesForCampaigns($campaigns);
			if(!in_array('First Name', $fieldNames)) {
				$fieldNames[] = 'First Name';
			}
			if(!in_array('Last Name', $fieldNames)) {
				$fieldNames[] = 'Last Name';
			}

			$customValues = LeadCustomData::getValues($customFormDatas->pluck('leadID'), $fieldNames);

			for($i = 0; $i < sizeof($customFormDatas); $i++) {
				//$customFormDatas[$i]['leadInfo'] = LeadCustomData::getLeadInformation($customFormDatas[$i]->leadID, $fieldNames);
				$customFormDatas[$i]['leadInfo'] = $customValues[$customFormDatas[$i]->leadID];

				if(sizeof($customFormDatas[$i]['leadInfo']) > 0){
					foreach($customFormDatas[$i]['leadInfo'] as $key => $value){
						if(in_array($key, CommonController::getFieldVariations('Company Name'))){
							$customFormDatas[$i]['companyName'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Post/Zip code')) || in_array($key, CommonController::getFieldVariations('Zip Code'))){
							$customFormDatas[$i]['zipCode'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('First Name'))){
							$firstName = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Last Name'))){
							$lastName = $value;
						}
					}
					$customFormDatas[$i]['contactPerson'] = $firstName . ' ' . (($lastName == '-') ? '' : $lastName);
					$customFormDatas[$i]['advisor'] = $customFormDatas[$i]->firstName . ' ' . $customFormDatas[$i]->lastName;
				}
			}

			$data['inBoundCallDatas'] = $customFormDatas;

			$data['campaignID'] = $campaignID;

			$sortingFormData = '';
			$inputs = Input::except('page');
			foreach($inputs as $key => $value) {
				if($key == '_')
					continue;
				$sortingFormData .= $key.'='.$value.'&';
			}

			$sortingFormData = rtrim($sortingFormData, '&');

			$data['sortingFromData'] = $sortingFormData;

			return View::make('site/lead/ajaxinboundcalldata', $data);
		}
	}
	 */

	public function openPendingCallBackLead() {
		$data = [];
		if (Request::ajax()) {
			$leadID = Input::get('leadID');
			
			$lead = Lead::FindOrFail($leadID);
			$this->authorize('can-see-campaign', $lead->campaign);

			$callback = CallBack::where('leadID', '=', $leadID)
				->where('status', '=', 'Pending')
				->where('time', '<=', DB::raw('(NOW() + INTERVAL 30 SECOND)'))
				->first();

			if($callback) {
				$callback->status = 'Completed';
				$callback->save();
			}

			Session::put('lead_opened_via_pending_callbacks', $lead->id);

			$data['success'] = "success";
		}

		return json_encode($data);
	}

	public function newInboundLeadForCampaign() {
		$data = [];

		if (Request::ajax()) {
			$campaignID = Input::get('campaignID');
			$campaign = Campaign::find($campaignID);

			$lead = $campaign->createNewLead($this->user);

			$data['success'] = 'success';
			$data['leadID'] = $lead->id;
		}

		return json_encode($data);
	}

	public function appointments() {
		$user = $this->user;
		$userManager = $user->manager;
		$selfID = $user->id;
		$userType = $user->userType;

		if ($userType == "Single" || $userType == "Multi") {
			$query = SalesMember::where('manager', $selfID);
		}
		else {
			$query = SalesMember::where('manager', $userManager);
		}

		if(Input::has('campaign')) {
			$query->where(function($query) {
				$query->where('campaignID', Input::has('campaign'))
					->orWhere('campaignID', null)
					->orWhere('campaignID', '');
			});
		}

		$salesMemberLists = $query->get();

		if(Request::ajax()) {
			if ($salesMemberLists->count() == 0) {
				$data['error'] = true;
				$data["message"] = "There are no sales members in your team. Ask your manager to add at least one sales member in your team.";
			}
			else {
				$data['error'] = false;
				$data['salesMemberLists'] = $salesMemberLists;
			}
			return View::make('site/lead/calender', $data);
		}

		if ($salesMemberLists->count() == 0) {
			$data = ['appointmentsMenuActive'  => 'nav-active active',
			         'appointmentsStyleActive' => 'display: block',
			         'appointmentsMenuStyleActive' => 'active'];
			$data["error"] = "There are no sales members in your team. Ask your manager to add at least one sales member in your team.";
			return View::make('site/lead/appointments', $data);
		}
		else {
			$data = ['appointmentsMenuActive'  => 'nav-active active',
			         'appointmentsStyleActive' => 'display: block',
			         'appointmentsMenuStyleActive' => 'active',
			         'salesMemberLists' => $salesMemberLists];

			return View::make('site/lead/appointments', $data);
		}
	}

	public function calanderAppointmentData() {
		$calanderAppointmentData = [];
		$user = $this->user;

		if (Request::ajax()) {
			$userManager = $user->manager;
			$selfID = $user->id;
			$userType = $user->userType;

			if ($userType == "Single") {
				$salesMemberLists = SalesMember::where('manager', $selfID)->get();
			}
			else {
				if ($userType == "Multi") {
					$salesMemberLists = SalesMember::where('manager', $selfID)->get();
				}
				else {
					$salesMemberLists = SalesMember::where('manager', $userManager)->get();
				}
			}

			$mangerSalesmans = [];

			foreach ($salesMemberLists as $salesMemberList) {
				$mangerSalesmans[] = $salesMemberList->id;
			}

			$mangerSalesmans = Input::get('salesmanIDs');

			$appointmentDatas = Appointment::select(['appointments.id as appointmentID',
			                                         'appointments.leadID',
			                                         'appointments.salesMember as salesmanID',
			                                         'appointments.time',
			                                         'salesmembers.firstName',
			                                         'salesmembers.lastName'])
				->join('salesmembers', 'salesmembers.id', '=', 'appointments.salesMember')
				->whereIn('appointments.salesMember', $mangerSalesmans)
				->get();

			foreach ($appointmentDatas as $appointmentData) {
				$times = $appointmentData->time;
				$dateTime = new DateTime($times);
				$time = $dateTime->format('g:i A');
				$date = $dateTime->format('d-m-Y');
				$start = $dateTime->format('Y-m-d');

				$calanderAppointmentData[] = ['id'         => $appointmentData->appointmentID,
				                              'title'      => $appointmentData->firstName,
				                              'start'      => $start,
				                              'newdate'    => $date,
				                              'time'       => $time,
				                              'salesmanID' => $appointmentData->salesmanID,
				                              'leadID'     => $appointmentData->leadID];
			}
		}

		return json_encode($calanderAppointmentData);
	}

	public function cancelAppointment() {
		$data = [];
		if (Request::ajax()) {
			$appointmentID = Input::get('appointmentID');
			$leadID = Input::get('leadID');

			//deleting appointment
			Appointment::where('id', $appointmentID)->delete();

			//Setting booking Appointment to No in Lead
			Lead::where('id', $leadID)
				->update(['bookAppointment' => 'No']);

			$data['success'] = 'success';
		}

		return json_encode($data);
	}

	function prepareAndDownLoadICSFile() {

		header("Content-Type: text/Calendar");
		header("Content-Disposition: inline; filename=calander.ics");

		$str = '';
		$str .= "BEGIN:VCALENDAR\n";
		$str .= "PRODID:-//Microsoft Corporation//Outlook 12.0 MIMEDIR//EN\n";
		$str .= "VERSION:2.0\n";
		$str .= "METHOD:PUBLISH\n";
		$str .= "X-MS-OLK-FORCEINSPECTOROPEN:TRUE\n";
		$str .= "BEGIN:VEVENT\n";
		$str .= "CLASS:PUBLIC\n";
		$str .= "CREATED:20091109T101015Z\n";
		$str .= "DESCRIPTION:" . str_replace('<br />', '\n', str_replace("\n", '\n', Input::get('detail'))) . "\n";
		$str .= "DTEND:" . Input::get('dateEnd') . "\n";
		$str .= "DTSTAMP:" . CommonController::convertLocalToUTC(CommonController::getTimeInUserTimeZone(time(), $this->user)) . "\n";
		$str .= "DTSTART:" . Input::get('dateStart') . "\n";
		$str .= "LAST-MODIFIED:" . CommonController::convertLocalToUTC(CommonController::getTimeInUserTimeZone(time(), $this->user)) . "\n";
		$str .= "LOCATION:\n";
		$str .= "PRIORITY:5\n";
		$str .= "SEQUENCE:0\n";
		$str .= "SUMMARY;LANGUAGE=en-us:" . Input::get('title') . "\n";
		$str .= "TRANSP:OPAQUE\n";
		$str .= "UID:040000008200E00074C5B7101A82E008000000008062306C6261CA01000000000000000\n";
		$str .= "X-MICROSOFT-CDO-BUSYSTATUS:BUSY\n";
		$str .= "X-MICROSOFT-CDO-IMPORTANCE:1\n";
		$str .= "X-MICROSOFT-DISALLOW-COUNTER:FALSE\n";
		$str .= "X-MS-OLK-ALLOWEXTERNCHECK:TRUE\n";
		$str .= "X-MS-OLK-AUTOFILLLOCATION:FALSE\n";
		$str .= "X-MS-OLK-CONFTYPE:0\n";
		//Here is to set the reminder for the event.
		$str .= "BEGIN:VALARM\n";
		$str .= "TRIGGER:-PT1440M\n";
		$str .= "ACTION:DISPLAY\n";
		$str .= "DESCRIPTION:Reminder\n";
		$str .= "END:VALARM\n";
		$str .= "END:VEVENT\n";
		$str .= "END:VCALENDAR\n";

		echo $str;
		exit;
	}

	function updateNotes() {
		if(Request::ajax()) {
			$notes = Input::get('notes');
			$leadID = Input::get('leadID');

			$lead = Lead::find($leadID);

			if(Session::has('callHistoryID')) {
				$lead->updateCallHistory(['notes' => $notes]);
			}
			else {
				$lead->createLeadCallHistory($this->user->id);
				$lead->updateCallHistory(['notes' => $notes]);
			}
			return ['status' => 'success'];
		}
	}

	function callHistory() {
		if(Request::ajax()) {
			$leadID = Input::get('leadID');
			$lead = Lead::find($leadID);
			$data['callHistory'] = $lead->getLeadCallHistory();

			return View::make('site/lead/callHistory', $data);
		}
	}

	function attachFiles() {

		if(!Input::has('leadID')) {
			return [
				'success' => false,
				'message'  => 'Getting some error in uploading attachments. Please check your uploaded files(size and type) and try again...'
			];
		}
		$leadID = Input::get('leadID');

		$fileArray = [];
		$status = [];

		if(Input::hasFile('file')) {
			$count = 0;
			foreach(Input::file('file') as $file) {
				$extension = $file->getClientOriginalExtension();
				$originalFileName = $file->getClientOriginalName();

				//todo: file size limit

				if(strtolower($extension) == 'php' || strtolower($extension) == 'js') {
					$fileArray[] = $originalFileName;
					$status[] = 'Not allowed';
				}
				else {
					$filename = $leadID.'_'.time().'_'.$count.'.'.$extension;

					$file->move(storage_path() . '/attachments/lead/', $filename);

					$lead = Lead::find($leadID);
					$lead->addLeadAttachment($this->user->id, $filename, $originalFileName);

					$fileArray[] = $originalFileName;
					$status[] = 'Uploaded';
				}

				$count++;
			}

			return [
				'files' => $fileArray,
				'status'  => $status,
				'success' => true
			];
		}
	}

	function getAttachmentHistory() {
		if(Request::ajax()) {
			$leadID = Input::get('leadID');

			$lead  = Lead::find($leadID);
			$data['attachments'] = $lead->getLeadAttachment();

			return View::make('site/lead/attachment', $data);
		}
	}

	function deleteAttachment() {
		if(Request::ajax()) {
			$id = Input::get('id');

			DB::table('lead_attachment')->where('id', $id)->delete();
		}
	}

	function downloadAttachment($id) {
		$detail = DB::table('lead_attachment')->where('id', $id)->first();

		return response()->download(storage_path() . '/attachments/lead/'.$detail->fileName, $detail->originalFileName);
	}

	function notesHistory() {
		if(Request::ajax()) {
			$leadID = Input::get('leadID');
			$lead = Lead::find($leadID);

			$data['callHistory'] = $lead->getLeadCallHistory();

			return View::make('site/lead/callHistory', $data);
		}
	}
	
	public function isLeadLocked(SimpleRequest $request) {
		if($request->ajax()) {
			$leadID = $request->input('leadID');
			$lead = Lead::find($leadID);
			
			if(!$lead){
				return ['status' => 'error', 'message' => 'Lead not found.'];
			}
			$this->authorize('can-see-campaign', $lead->campaign);

			$openedTime = $lead->timeOpened;
			if($openedTime) {
				$openedTime = new DateTime($openedTime);
				$currentTime = new DateTime;
				$currentTime->modify("-20 seconds");
						;

				if($openedTime > $currentTime && $lead->edited_by_user_id != $this->user->id) {

					$editorString = "";

					$lastEditor = User::find($lead->edited_by_user_id);
					if($lastEditor) {
						$editorString = " by {$lastEditor->firstName} {$lastEditor->lastName}";
					}
					
					return [
						'status' => 'error', 
						'message' => "This lead is currently being updated{$editorString}. Please try again later."
					];
				}
			}
			
			return [
				"status"  => "success", 
			];
		}
	}

	private function sendBookingEmailToLead($lead, $type, $emailFields) {
		$user = $this->user;
		$leadCustomData = $lead->getCustomData();

		// Is Email entered or exists in custom field data
		$isEmailFieldExists = $leadCustomData->filter(function ($model) {
			return $model->fieldName == "Email";
		});

		// If email id is not blank
		if ($isEmailFieldExists->count()) {

			$fieldValues = [];

			foreach ($leadCustomData as $leadFieldsDetail) {
				$fieldValues[ $leadFieldsDetail->fieldName ] = $leadFieldsDetail->value;

				if ($leadFieldsDetail->fieldName == "Email") {
					$email = $leadFieldsDetail->value;
				}
			}
			foreach($emailFields as $key => $value) {
				$fieldValues[$key] = $value;
			}

			$fieldValues['Agent'] = $this->user->firstName.' '.$this->user->lastName;

			// Checking for Email Setting exists or not in SMTP settings table
			if($type == 'salesman') {
				$emailTemplate = EmailTemplate::where('name', 'Appointment booked')
					->where('creator', $user->id)
					->first();
			}
			else if($type == 'followUp') {
				$emailTemplate = EmailTemplate::where('name', 'Follow Up Call')
					->where('creator', $user->id)
					->first();
			}

			if(!$emailTemplate) {
				return false;
			}

			$emailText = $emailTemplate->templateText;

			$mailSettings = CommonController::loadSMTPSettings("outbound");

			$generalInfo = [
				'from'     => $mailSettings["from"]["address"],
				'fromName' => $user->firstName . " " . $user->lastName,
				'replyTo'  => $user->email
			];

			Config::set("mail.from.name", $user->firstName . " " . $user->lastName);

			$emailInfo = CommonController::setEmailConfiguration($lead, $user, $generalInfo, /*$forceFromAddressFromCampaign*/ true);

			$emailInfo['subject'] = $emailTemplate->subject;
			$emailInfo['to'] = $email;
			$emailInfo['bccEmail'] = $user->bccEmail;

			//get email template attachments
			$defaultAttachments = $emailTemplate->getEmailAttachmentsToBeSend();

			$attachments = $defaultAttachments;
			CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments, /*$throw*/ false, /*$async*/ true);
		}
	}

	public function ajaxLandingLeadData() {
		DB::enableQueryLog();
		if (Request::ajax()) {
			$campaignID = Input::get('campaign');

			if($campaignID != 'all') {
				$campaign = Campaign::findOrFail($campaignID);
				$this->authorize('can-see-campaign', $campaign);
				$campaigns = [$campaignID];
			} else {
				$campaigns = $this->user->getActiveCampaigns()->lists('id')->all();
			}

			$query = Lead::where('leads.leadType', 'landing')
				->where('leads.status', 'Unactioned')
				->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
				->select(DB::raw('leads.*'), 'campaigns.name');

			$query = $query->whereIn('leads.campaignID', $campaigns);

			if(Input::has('column')) {
				$column = Input::get('column');
				$type = Input::get('type');

				if($column == 'firstName') {
					$query = $query->leftjoin('leadcustomdata', function($join) {
						$join->on('leadcustomdata.leadID', '=', 'leads.id');
						$join->where('leadcustomdata.fieldName', '=', 'First Name');
					});

					$query = $query->orderBy('leadcustomdata.value', $type);
				}
				elseif($column == 'telephone') {
					$query = $query->leftjoin('leadcustomdata', function($join) {
						$join->on('leadcustomdata.leadID', '=', 'leads.id');
						$join->whereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Telephone No'));
					});

					$query = $query->orderBy('leadcustomdata.value', $type);
				}
				elseif($column == 'email') {
					$query = $query->leftjoin('leadcustomdata', function($join) {
						$join->on('leadcustomdata.leadID', '=', 'leads.id');
						$join->whereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Email'));
					});

					$query = $query->orderBy('leadcustomdata.value', $type);
				}
				elseif($column == 'zipCode') {
					$query = $query->leftjoin('leadcustomdata', function($join) {
						$join->on('leadcustomdata.leadID', '=', 'leads.id');
						$join->where(function($q) {
							$q->whereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Post/Zip code'))
								->orWhereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Zip Code'));
						});
					});

					$query = $query->orderBy('leadcustomdata.value', $type);
				}
				elseif($column == 'campaign') {
					$query = $query->orderBy('campaigns.name', $type);
				}

				$data['column'] = $column;
				$data['type'] = $type;
			}
			else {
				$query = $query->orderBy('leads.timeCreated', 'DESC');
			}

			$landingLeads = $query->paginate(10);

			// Get Form fields for all campaigns
			$fieldNames = FormFields::getFieldNamesForCampaigns($campaigns);
			$customValues = LeadCustomData::getValues($landingLeads->pluck('id'), $fieldNames);

			for($i = 0; $i < sizeof($landingLeads); $i++) {
				//$landingLeads[$i]['leadInfo'] = LeadCustomData::getLeadInformation($landingLeads[$i]->id, $fieldNames);
				$landingLeads[$i]['leadInfo'] = $customValues[$landingLeads[$i]->id];

				if(sizeof($landingLeads[$i]['leadInfo']) > 0){
					foreach($landingLeads[$i]['leadInfo'] as $key => $value){
						if(in_array($key, CommonController::getFieldVariations('First Name'))){
							$landingLeads[$i]['firstName'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Post/Zip code')) || in_array($key, CommonController::getFieldVariations('Zip Code'))){
							$landingLeads[$i]['zipCode'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Telephone No'))){
							$landingLeads[$i]['telephone'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Email'))){
							$landingLeads[$i]['email'] = $value;
						}
					}
					$landingLeads[$i]['receivedOn'] = \Carbon\Carbon::parse($landingLeads[$i]->timeCreated)->format('d M Y');
					$landingLeads[$i]['campaignName'] = $landingLeads[$i]->name;
				}
			}



			$data['landingLeads'] = $landingLeads;
			$data['campaignID'] = $campaignID;

			return View::make('site/lead/ajaxLandingLeads', $data);
		}
		else {
			return ['status' => 'fail', 'message' => 'Inappropriate request'];
		}
	}

	public function landingExport() {
		$campaignID = Input::get('campaign');

		$orderColumn = 'leads.timeCreated';
		$orderBy = 'DESC';

		$campaign = Campaign::find($campaignID);

		// Set File attachment headers to force download
		$fileName = $campaign->name . '_Unactioned_Landing_' . (new DateTime())->format("Y_m_d_H_i_s") . ".csv";

		header('Content-type: text/csv');
		header('Content-disposition: attachment;filename=' . $fileName);

		$selectedArray = ['campaigns.name', 'leads.id as leadID', 'leads.timeCreated', 'leads.timeEdited', 'leads.status', 'leads.interested','leads.bookAppointment'];

		$results = Campaign::join('leads', 'leads.campaignID', '=', 'campaigns.id');
		$results = $results->where('leads.status', 'Unactioned');
		$results = $results->where('campaigns.id', $campaignID)->orderBy($orderColumn, $orderBy)->get($selectedArray);

		$newInsertedCsvArray = ['Campaign Name', 'Time Created', 'Time Edited', 'Status', 'Interested', 'Book Appointment'];

		$form = Form::find($campaign->formID);

		$leadDataHeadings = $form->getFormFields()->lists('fieldName')->all();

		// Putting these Unique Fields to header
		foreach ($leadDataHeadings as $leadDataHeading) {
			$newInsertedCsvArray[] = $leadDataHeading;
		}

		$file = fopen("php://output", "w");
		fputcsv($file, $newInsertedCsvArray, ",", "\"");

		$count = 0;

		// Adding other elements other than heading
		foreach ($results as $result) {
			$newArray = [];
			$newArray[] = $result->name;
			$newArray[] = $result->timeCreated;
			$newArray[] = $result->timeEdited;
			$newArray[] = $result->status;
			$newArray[] = CommonController::getInterestedType($result->interested);
			$newArray[] = $result->bookAppointment;

			$leadData = LeadCustomData::select(['value'])
			                          ->where('leadID', $result->leadID)
			                          ->orderBy("fieldID")
			                          ->get();

			// Extracting values of CustomFieldData columns
			foreach ($leadData as $leadField) {
				$newArray[] = $leadField->value;
			}

			fputcsv($file, $newArray, ",", "\"");
			$count++;
		}

		flush();
	}

	public function getMoveLeadsDlg(SimpleRequest $request) 
	{
		$campaignID = $request->input('campaignID');

		$campaign =Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);
		$data['campaign'] = $campaign;

		$targetCampaigns = $this->user->getActiveCampaigns()
			->filter(function ($targetCampaign) use($campaign) {
				return $targetCampaign->id  !=  $campaign->id;
			});
		$data['targetCampaigns'] = $targetCampaigns;

		return View::make('site/lead/dlgMoveLeads', $data);
	}
	
	public function getMoveMappingDlg(SimpleRequest $request) 
	{
		$data = [];

		$sourceCampaignId = $request->input('sourceCampaignID');
		$sourceCampaign = Campaign::findOrFail($sourceCampaignId);
		$this->authorize('can-see-campaign', $sourceCampaign);
		$data['sourceCampaign'] = $sourceCampaign;
		$data['sourceFields'] = FormFields::getFieldsForCampaign($sourceCampaignId);

		$targetCampaignId = $request->input('targetCampaignID');
		$targetCampaign = Campaign::findOrFail($targetCampaignId);
		$this->authorize('can-see-campaign', $targetCampaign);
		$data['targetCampaign'] = $targetCampaign;
		$data['targetFields'] = FormFields::getFieldsForCampaign($targetCampaignId);

		return View::make('site/lead/dlgMoveLeadsMapping', $data);
	}
	
	public function doMoveLeads(SimpleRequest $request) 
	{
		//Log::error("doMoveLeads :" . print_r($request->all(), true));

		$sourceCampaignId = $request->input('sourceCampaignID');
		$sourceCampaign = Campaign::findOrFail($sourceCampaignId);
		$this->authorize('can-see-campaign', $sourceCampaign);
		//$sourceFields = FormFields::getFieldsForCampaign($sourceCampaignId);

		$targetCampaignId = $request->input('targetCampaignID');
		$targetCampaign = Campaign::findOrFail($targetCampaignId);
		$this->authorize('can-see-campaign', $targetCampaign);
		$targetFields = FormFields::getFieldsForCampaign($targetCampaignId)->keyBy('id');

		$duplicatesProcessingMode = $request->input('duplicates', '');
		$moveMode = $request->input('move', 'move');
		$includeNotes = $request->input('includeNotes') == 'true';

		$mappingInput = $request->input('mapping');
		$fieldsMapping = [];
		$addToNotes = [];
		$uniqueKeyFields = [];
		$targetFieldsInUse = [];
		foreach($mappingInput as $fieldMapping) {
			if($fieldMapping['targetFieldID'] != '') {
				if(isset($targetFieldsInUse[$fieldMapping['targetFieldID']])) {
					$data['status'] = 'error';
					$data['message'] = 'You can\'t map two fields into one field : ' . $targetFields->get($fieldMapping['targetFieldID'])->fieldName;
					return $data;
				}
				if(isset($fieldsMapping[$fieldMapping['sourceFieldID']])) {
					$data['status'] = 'error';
					$data['message'] = 'You can\'t map one field into two';
					return $data;
				}
				$fieldsMapping[$fieldMapping['sourceFieldID']] = $fieldMapping['targetFieldID'];
				$targetFieldsInUse[$fieldMapping['targetFieldID']] = true;
			} else {
				$addToNotes[$fieldMapping['sourceFieldID']] = true;
			}
			if($fieldMapping['isFieldUnique'] == 'true') {
				$uniqueKeyFields[$fieldMapping['sourceFieldID']] = $fieldMapping['sourceFieldID'];
			}
		}

		$leads = Lead::where('campaignID', '=', $sourceCampaign->id)
			->whereIn('id',	$request->input('leads', []))
			->get();
		
		$data = [];

		DB::beginTransaction();

		$totalLeadCustomDataUpdated = 0;
		$count = 0;
		$duplicates = 0;
		$totalLeadCustomDataAdded = 0;

		try {
			foreach($leads as $lead) {
				$newInsertedDataArray = [];
				$valuesToAddToNotes = [];
				$uniqueCheckFilter = [];

				$customFields = $lead->getCustomData();
				foreach($customFields as $customField) {
					$id = $customField->fieldID;
					if(isset($fieldsMapping[$id])) {
						$newInsertedDataArray[$fieldsMapping[$id]] = [
							'fieldID'   => $fieldsMapping[$id], 
							'fieldName' => $targetFields->get($fieldsMapping[$id])->fieldName, 
							'value'     => $customField->value	
						];
					} else if(isset($addToNotes[$id])) {
						if($customField->value != '') {
							$valuesToAddToNotes[$customField->fieldName] = $customField->value;
						}
					} else {
						$data['status'] = 'error';
						$data['message'] = 'Unmapped field : ' . $customField->fieldName;
						return $data;
					}
					if(isset($uniqueKeyFields[$id])) {
						$uniqueCheckFilter[] = [
							'fieldID'   => $fieldsMapping[$id], 
							'fieldName' => $targetFields->get($fieldsMapping[$id])->fieldName, 
							'value'     => $customField->value	
						];
					}
				}

				$isDuplicateFound = false;
				if(count($uniqueCheckFilter)) {
					//search via unique keys
					$leadDuplicatesQuery = Lead::where('campaignID', '=', $targetCampaign->id)
						->select('leads.*')
						;
					$subjoin_id = 0;
					foreach ($uniqueCheckFilter as $value) {
						$subjoin_id++;
						$leadDuplicatesQuery->where(function($query) use($value, $leadDuplicatesQuery, $subjoin_id) {
							$subjoin_alias = 'flt_customdata_' . $subjoin_id;
							$leadDuplicatesQuery->join('leadcustomdata as ' . $subjoin_alias, $subjoin_alias . '.leadID', '=', 'leads.id');
							$query->where(function($q) use($value, $subjoin_alias) {
								$valueToLookAt = $value['value'];
								$q->where($subjoin_alias . '.fieldName', $value['fieldName'])
								  ->where($subjoin_alias . '.value', $valueToLookAt);
							});
						});
					}
					//Log::error("query : " . $leadDuplicatesQuery->toSql());
					$isDuplicateFound = $leadDuplicatesQuery->exists();
				}

				// Array to hold custom fields data before insert
				$leadCustomDataArray = [];

				// If Duplicate Entry Not found
				if (!$isDuplicateFound || $duplicatesProcessingMode == 'update') {

					if($isDuplicateFound && $duplicatesProcessingMode == 'update') {
						$newLead = $leadDuplicatesQuery->first();
					} else {
						//Generating New Lead
						$newLead = new Lead;
						$newLead->campaignID = $targetCampaign->id;
						$newLead->timeCreated = new DateTime();
					}

					$newLead->latitude = $lead->latitude;
					$newLead->longitude = $lead->longitude;
					$newLead->countryCode = $lead->countryCode;
					$newLead->leadType = $lead->leadType;
					
					if($includeNotes) {
						$newLead->bookAppointment = $lead->bookAppointment;
						$newLead->interested = $lead->interested;
						$newLead->timeTaken = $lead->timeTaken;
						$newLead->timeEdited = $lead->timeEdited;
						$newLead->status = $lead->status;
						$newLead->lastActioner = $lead->lastActioner;
					}

					try {
						$newLead->save();
					} catch(Exception $e) {
						$errorMessage = $e->getMessage();
						if(strpos($errorMessage, 'leads_leadhash_unique') !== FALSE 
							&& strpos($errorMessage, 'Duplicate entry') !== FALSE 
						) {
							Log::error("skipping error on inserting data.: " . $errorMessage);
							$duplicates++;
							continue;
						} else {
							throw $e;
						}
					}
					$leadID = $newLead->id;


					foreach($targetFields as $targetField) {
						if(isset($newInsertedDataArray[$targetField->id])) {
							$value = $newInsertedDataArray[$targetField->id];
							$leadCustomDataArray[] = [
								"leadID" => $leadID,
								"fieldID" => $value["fieldID"],
								"fieldName" => $value["fieldName"],
								"value" => $value["value"]
							];
						} else {
							$leadCustomDataArray[] = [
								"leadID" => $leadID,
								"fieldID" => $targetField->id,
								"fieldName" => $targetField->fieldName,
								"value" => ''
							];
						}
					}

					//notes
					foreach($leadCustomDataArray as $key => $customData) {
						if($customData['fieldName'] == 'Notes') {
							$value = $customData['value'];
							if($value != '') {
								$value .= "\n";
							}
							foreach($valuesToAddToNotes as $fieldName => $fieldValue) {
								$value .=  $fieldName . ' - ' . $fieldValue . "\n";
							}
							$leadCustomDataArray[$key]['value'] = $value;
						}
					}

					if($isDuplicateFound && $duplicatesProcessingMode == 'update') {
						foreach($leadCustomDataArray as $customData) {
							LeadCustomData::where("leadID", '=', $customData['leadID'])
								->where("fieldID", '=', $customData['fieldID'])
								->where("fieldName", '=', $customData['fieldName'])
								->update(["value" => $customData['value']])
								;
						}
						$totalLeadCustomDataUpdated++;
					} else {
						// Bulk insert leads fields
						LeadCustomData::insert($leadCustomDataArray);
						$totalLeadCustomDataAdded++;
					}
					//copy other data
					if($includeNotes) {
						Appointment::where('leadID', $lead->id)
							->update(['leadID' => $leadID]);
						$lead->bookAppointment = 'No';
						$lead->save();

						CallBack::where('leadID', $lead->id)
							->update(['leadID' => $leadID]);
						CallHistory::where('leadID', $lead->id)
							->update([
								'leadID' => $leadID,
								'mass_email_template_id' => null,
								'mass_email_id' => null,
							]);
						LeadAttachment::where('leadID', $lead->id)
							->update(['leadID' => $leadID]);
						LeadInfoEmails::where('lead_id', $lead->id)
							->update(['lead_id' => $leadID, 'campaign_id' => $targetCampaign->id]);
					}

					if($moveMode == 'move') {
						$lead->delete();
					}
				} else {
					$duplicates++;
				}
				$count++;
				//Log::error("newInsertedDataArray :" . print_r($newInsertedDataArray, true));
				//Log::error("valuesToAddToNotes :" . print_r($valuesToAddToNotes, true));
				//Log::error("uniqueCheckFilter :" . print_r($uniqueCheckFilter, true));
				//Log::error("leadCustomDataArray :" . print_r($leadCustomDataArray, true));
			}
			// Updating totalLeads and remainingCalls in Campaign
			$targetCampaign->totalLeads += $totalLeadCustomDataAdded;
			$targetCampaign->save();
			$targetCampaign->recalculateCallsRemaining();
			$targetCampaign->recalculateTotalFilteredLeads();

			if($moveMode == 'move') {
				$sourceCampaign->recalculateCallsRemaining();
				$sourceCampaign->recalculateTotalFilteredLeads();
				$sourceCampaign->recalculateTotalLeads();
			}
			
			$data['status'] = 'success';
			$successMessage = "Transfer successful";
			$successMessage .= "<br />Processed : {$count}";
			if($totalLeadCustomDataAdded > 0) {
				$successMessage .= "<br />Added : {$totalLeadCustomDataAdded}";
			}
			if($totalLeadCustomDataUpdated > 0) {
				$successMessage .= "<br />Updated : {$totalLeadCustomDataUpdated}";
			}
			if($duplicates > 0) {
				$successMessage .= "<br />Skipped : {$duplicates}";
			}
			
			$data['message'] = $successMessage;

			//throw new Exception($successMessage);

			DB::commit();
			return $data;
		}
		catch(Exception $e){
			DB::rollBack();
			Log::error("Error on data transer : " . $e->getMessage());
			$data['status'] = 'error';
			$data['message'] = 'Error on data transer : ' . $e->getMessage();
			return $data;
		}

		$data['status'] = 'success';
		$data['message'] = 'test message';
		return $data;
	}

	public function leadsPageSkipAuto() 
	{
		$this->user->leads_page_skip_auto = 'Yes';
		$this->user->save();
	}

}
