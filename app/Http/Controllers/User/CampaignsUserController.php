<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Setting;
use Cache;
use Carbon\Carbon;
use App\Models\AdminEmailTemplate;
use App\Models\Appointment;
use App\Models\CallBack;
use App\Models\CallHistory;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\FormFields;
use App\Models\Formmapping;
use App\Models\Landingform;
use App\Models\Lead;
use App\Models\LeadCustomData;
use App\Models\PostalCode;
use App\Models\SalesMember;
use App\Models\MassEmailTemplate;
use App\Models\User;
use App\Models\CampaignPrevNextFilter;
use App\Http\Requests\SimpleRequest;
use Config;
use Log;
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
use Validator;
use View;
use ZipStream\ZipStream;
use DocxTemplate\TemplateFactory;


class CampaignsUserController extends Controller{

	public function start() {

		$data = [
			'leadsMenuActive' => 'nav-active active',
			'leadsStyleActive'  => 'display: block',
			'leadsStartActive'  => 'active',
			'successMessage' => Session::get('successMessage')
		];

		return View::make('site/campaigns/startcampaigns', $data);
	}

	function getActiveCampaign() {
		$userCampaignLists = $this->user->getActiveWithLastActionerCampaigns();
		$data['userCampaignLists'] = $userCampaignLists;

		return View::make('site/campaigns/activeCampaigns', $data);
	}

	function getFinishedCampaign() {
		$completedCampaignLists = $this->user->getCompletedCampaigns();
		$data['recentCampaignLists'] = $completedCampaignLists;

		return View::make('site/campaigns/finishCampaigns', $data);
	}

	function getArchivedCampaign() {
		$archivedCampaignLists = $this->user->getArchivedCampaigns();
		$data['archivedCampaignLists'] = $archivedCampaignLists;

		return View::make('site/campaigns/archiveCampaigns', $data);
	}

	function addToArchive() {
		if(Request::ajax()) {
			$id = Input::get('id');
			$campaign = Campaign::find($id);
			$campaign->status = 'Archived';
			$campaign->save();
		}
	}

	function removeArchive() {
		if(Request::ajax()) {
			$id = Input::get('id');
			$campaign = Campaign::find($id);
			$campaign->status = 'Started';
			$campaign->save();
		}
	}

	public function startCampaign() {
		if (Request::ajax()) {
			$output = [];

			$campaignID = Input::get('campaignID');

			$campaign = Campaign::find($campaignID)->start();

			Session::flash('successMessage', 'Campaign successfully started');

			$lead = $campaign->getLandingLead($this->user);

			$output['leadID'] = $lead->id;
			$output['status'] = "success";
			$output['message'] = "Campaign successfully started";

			return $output;
		}
		else {
			return ["status"  => "fail", "message" => "This method cannot be called directly"];
		}
	}

	public function endCampaign() {
		if (Request::ajax()) {
			set_time_limit(0);
			
			$output = [];

			$campaignID = Input::get('campaignID');

			$campaign = Campaign::find($campaignID);
			if(!$campaign) {
				return ["status"  => "fail", "message" => "Campaign not found"];
			}
			$this->authorize('can-see-campaign', $campaign);
			$campaign->end();

			$output['status'] = "success";
			$output["message"] = "Campaign ended successfully";

			return $output;
		}
		else {
			return ["status"  => "fail", "message" => "This method cannot be called directly"];
		}
	}
	
	public function deleteCampaignData() {
		if (Request::ajax()) {
			set_time_limit(0);

			$campaignID = Input::get('campaignID');

			$campaign = Campaign::find($campaignID);
			if(!$campaign) {
				return ["status"  => "fail", "message" => "Campaign not found"];
			}

			$this->authorize('can-see-campaign', $campaign);
			
			if($campaign->status != 'Completed') {
				return ["status"  => "fail", "message" => "Only finished campaigns can be deleted"];
			}

			MassEmailTemplate::where('campaign_id', '=', $campaignID)
				->delete();
			$campaign->delete();
			
			Session::flash('successMessage', 'Campaign deleted successfully');
			Session::flash('successMessageClass', 'success');


			$output = [];
			$output['status'] = "success";
			$output["message"] = "Campaign deleted successfully";
			
			return $output;
		}
		else {
			return ["status"  => "fail", "message" => "This method cannot be called directly"];
		}
	}
	
	public function renameCampaign() {
		if (Request::ajax()) {

			$newName = trim(Input::get('value'));
			if($newName == '') {
				return ["status"  => "fail", "message" => "Name is required"];
			}

			$campaignID = Input::get('pk');

			$campaign = Campaign::find($campaignID);
			if(!$campaign) {
				return ["status"  => "fail", "message" => "Campaign not found"];
			}

			$this->authorize('can-see-campaign', $campaign);
			
			if($this->user->userType == "Team") {
				return ["status"  => "fail", "message" => "Only team admin can rename campaigns"];
			}
			
			if($campaign->status != 'Started') {
				return ["status"  => "fail", "message" => "Only active campaigns can be edited"];
			}

			$campaign->name = $newName;
			$campaign->save();
			
			Session::flash('successMessage', 'Campaign renamed successfully');
			Session::flash('successMessageClass', 'success');


			$output = [];
			$output['status'] = "success";
			$output["message"] = "Campaign renamed successfully";
			
			return $output;
		}
		else {
			return ["status"  => "fail", "message" => "This method cannot be called directly"];
		}
	}

	public function createCampaign() {
		// Basic Menu Settings
		$data = [
			'leadsMenuActive'  => 'nav-active active',
			'leadsStyleActive' => 'display: block',
			'leadsCampaignActive' => 'active'
		];

		$data['allManagerUsers'] = $this->user->getTeamMembers();
		$data['formLayouts'] = $this->user->getAvailableForms();
		$data['landingForms'] = $this->user->getAvailableLandingForms();

		$industries = Setting::get('industry');

		$data['maxFileSize'] = Setting::get('maxFileUploadSize');

		$data['industries'] = explode(',', $industries);

		return View::make('site.campaigns.createcampaigns', $data);
	}

	// Return fileName After creating Csv File

	public function saveCampaign() {

		if(!Input::has('hiddenCampaignData')) {
			return Redirect::to('user/campaigns/createcampaign')->with('message', 'Uploaded csv file in too large to be processed. Please try again');
		}

		$userID = $this->user->id;
		$hiddenCampaignData = Input::get('hiddenCampaignData');
		$hiddenCampaignDataArray = json_decode($hiddenCampaignData, true);
		$hiddenCampaignData = json_decode($hiddenCampaignData, false);
		$allUsersIDs = $hiddenCampaignData->allUsersIDs;

		//Getting And Storing Csv Data for Lead Generation
		$fileName = $this->createNewCsvFileForCampaign();

		//Creating New Campaign
		$newCampaign = new Campaign;
		$newCampaign->name = $hiddenCampaignData->campaignName;
		$newCampaign->formID = $hiddenCampaignData->formLayout;
		$newCampaign->industry = $hiddenCampaignData->industry;
		$newCampaign->timeCreated = new DateTime();
		$newCampaign->totalLeads = 0;
		$newCampaign->callsRemaining = 0;
		$newCampaign->creator = $userID;
		$newCampaign->autoReferencePrefix = $hiddenCampaignData->autoReferencePrefix;
		$newCampaign->autoReferences = $hiddenCampaignData->autoReferenceGenerator;

		if($hiddenCampaignData->isLandingLayout == true) {
			$newCampaign->landingFormID = $hiddenCampaignData->landingLayout;
		}
		else {
			$newCampaign->landingFormID = null;
		}

		$newCampaign->start();
		$newCampaign->save();

		$createdCampaignID = $newCampaign->id;

		try {
			$this->saveCampaignMailSettings($createdCampaignID, $hiddenCampaignDataArray);
		} catch(Exception $e) {
			Log::error("email settings for new campaign are incorrect, ignore error :" . $e->getMessage());
		}

		$insertCampaignMembers = [];
		$allUsers = [];

		//Saving Campaign Members
		foreach ($allUsersIDs as $allUsersID) {
			$insertCampaignMembers[] = [
				'campaignID' => $createdCampaignID,
				'userID' => $allUsersID->id,
			];
			$allUsers[$allUsersID->id] = true;
		}

		//Creating Entry for Manager in CampaignMember Table
		$userManagerId = $this->user->managerID;

		if ($userManagerId == null) {
			$userManagerId = $userID;
		}

		if (!isset($allUsers[$userManagerId])) {
			$insertCampaignMembers[] = [
				'campaignID' => $createdCampaignID,
				'userID' => $userManagerId,
			];
		}

		CampaignMember::insert($insertCampaignMembers);

		Session::put('newCampaignCsvFile', $fileName);
		Session::put('newCampaignFormID', $hiddenCampaignData->formLayout);
		Session::put('newCampaignID', $createdCampaignID);

		return Redirect::to('user/campaigns/step3');
	}

	private function saveCampaignMailSettings($campaignID, $dataArray)
	{
		foreach($dataArray as &$requestValue) {
			if(is_string($requestValue)) {
				$requestValue = trim($requestValue);
			}
		}

		//Log::error(print_r($dataArray, true));
		if($dataArray['setting'] == 'advanced') {
			$validatorArray = [
				'fromEmail' => 'required|email',
				'replyToEmail' => 'required|email',
			];
			
			if($dataArray['smtpSetting'] == 'true') {
				$validatorArray = array_merge($validatorArray, [
					'host' => 'required',
					'port' => 'required',
					'username' => 'required',
					'password' => 'required',
					'security'  => 'required|in:No,tls,ssl',
				]);
			}

			//Validation Rules for user Info
			$validator = Validator::make($dataArray, $validatorArray);

			if ($validator->fails()) {
				//If Validation rule fails
				$error = '';
				$messages = $validator->messages();
				foreach ($messages->all() as $message) {
					$error .= $message . '<br>';
				}

				throw new Exception($error);
			}
		}

		DB::table('campaign_email_settings')
			->where('campaignID', '=', $campaignID)
			->delete();

		//check for advanced and smtp settings
		if($dataArray['setting'] == 'advanced') {
			$settingArray = [];

			$settingArray['fromEmail'] = $dataArray['fromEmail'];
			$settingArray['replyToEmail'] = $dataArray['replyToEmail'];
			$settingArray['smtpSetting'] = 'No';

			if($dataArray['smtpSetting'] == 'true') {
				$settingArray['smtpSetting'] = 'Yes';
				$settingArray['host'] = $dataArray['host'];
				$settingArray['port'] = $dataArray['port'];
				$settingArray['username'] = $dataArray['username'];
				$settingArray['password'] = $dataArray['password'];
				$settingArray['security'] = $dataArray['security'];
			}

			$settingArray['campaignID'] = $campaignID;

			DB::table('campaign_email_settings')->insert($settingArray);
		}

	}

	private function createNewCsvFileForCampaign() {

		//Getting And Storing Csv Data for Lead Generation
		if (Input::file('uploadedcsvFile')) {
			//$fileExtension = Input::file('uploadedcsvFile')
			//	->guessClientExtension();
			$date = new DateTime();

			// First character in the file name indicates if the file is Comma separated or tab separated
			$fileName = "C" . $this->user->id . '_import_' . $date->getTimestamp() . '.csv';
			Input::file('uploadedcsvFile')->move(storage_path('excel/exports'), $fileName);

			return $fileName;
		}
		else {
			if (Input::has('importLeadTXT')) {
				$date = new DateTime();
				$fileName = "T" . $this->user->id . '_import_' . $date->getTimestamp();
				$content = Input::get('importLeadTXT');

				// Replace multiple tabs with single tab
				//$convertedCSVData = preg_replace("/[\t]+/", "\t", $content);

				File::put(storage_path("excel/exports/") . $fileName, $content);

				return $fileName;
			}
			else {
				return "sanityos.csv";
			}
		}
	}

	public function saveCampaignWithoutCsv() {

		$userID = $this->user->id;
		$hiddenCampaignData = Input::get('hiddenCampaignData1');
		$hiddenCampaignData = json_decode($hiddenCampaignData, true);
		$allUsersIDs = $hiddenCampaignData["allUsersIDs"];

		//Creating New Campaign
		$newCampaign = new Campaign;
		$newCampaign->name = $hiddenCampaignData["campaignName"];
		$newCampaign->formID = $hiddenCampaignData["formLayout"];
		$newCampaign->industry = $hiddenCampaignData["industry"];
		$newCampaign->timeCreated = new DateTime();
		$newCampaign->totalLeads = 0;
		$newCampaign->callsRemaining = 0;
		$newCampaign->creator = $userID;
		$newCampaign->autoReferencePrefix = $hiddenCampaignData["autoReferencePrefix"];
		$newCampaign->autoReferences = $hiddenCampaignData["autoReferenceGenerator"];
		$newCampaign->type = "Fly"; // This is a fly campaign

		if($hiddenCampaignData['isLandingLayout'] == true) {
			$newCampaign->landingFormID = $hiddenCampaignData['landingLayout'];
		}
		else {
			$newCampaign->landingFormID = null;
		}

		$newCampaign->start();
		$newCampaign->save();
		$createdCampaignID = $newCampaign->id;

		try {
			$this->saveCampaignMailSettings($createdCampaignID, $hiddenCampaignData);
		} catch(Exception $e) {
			Log::error("email settings for new campaign are incorrect, ignore error :" . $e->getMessage());
		}

		$insertCampaignMembers = [];
		$allUsers = [];

		//Saving Campaign Members
		foreach ($allUsersIDs as $allUsersID) {
			$insertCampaignMembers[] = [
				'campaignID' => $createdCampaignID,
				'userID' => $allUsersID["id"],
			];
			$allUsers[$allUsersID["id"]] = true;
		}

		//Creating Entry for Manager in CampaignMember Table
		$userManagerId = $this->user->managerID;

		if ($userManagerId == null) {
			$userManagerId = $userID;
		}

		if (!isset($allUsers[$userManagerId])) {
			$insertCampaignMembers[] = [
				'campaignID' => $createdCampaignID,
				'userID' => $userManagerId,
			];
		}

		CampaignMember::insert($insertCampaignMembers);

		return Redirect::to('user/campaigns/start');
	}

	public function step3() {
		$filePath = storage_path('excel/exports/') . Session::get('newCampaignCsvFile');
		$formID = Session::get('newCampaignFormID');

		try {
			$data = $this->csvDataForFilter($filePath, $formID);
		} catch(Exception $e) {
				Session::forget('newCampaignCsvFile');
				Session::forget('newCampaignFormID');
				Session::forget('newCampaignID');
				unlink($filePath);
			return Redirect::to('user/campaigns/importcampaignleads')->with('message', 'Import error : ' . $e->getMessage());
		}

		$data['leadsMenuActive'] = 'nav-active active';
		$data['leadsStyleActive'] = 'display: block';
		$data['leadsCampaignActive'] = 'active';
		$data['is_new_campaign'] = true;

		return View::make('site/campaigns/campaignstep3', $data);
	}

	private function csvDataForFilter($filePath, $formID) {
		$csvFields = [];
		$csvHeadingFields = [];
		$leadFields = [];
		$formColumnDetailsByID = [];

		$csvOrTsvIndicator = substr($filePath, strrpos($filePath, "/") + 1, 1);

		$delimiter = ",";

		if ($csvOrTsvIndicator == "T") {
			$delimiter = "\t";
		}

		$formColumns = FormFields::select('id', 'fieldName')->where('formID', $formID)->get();

		foreach ($formColumns as $formColumn) {
			$leadFields[] = ['id'   => $formColumn->id, 'name' => $formColumn->fieldName];
			$formColumnDetailsByID[ $formColumn->id ] = $formColumn->fieldName;
		}

		//$mimeType = \File::mimeType($filePath);
		$mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE, "/usr/share/file/magic"), $filePath);

		if(!in_array($mimeType, ['text/csv', 'text/tab-separated-values', 'text/plain'])) {
			throw new Exception("Incorrect file type : {$mimeType}. The csv is required");
		}

		// Opening file for Reading and fetching Data
		$file = fopen($filePath, "r");

		$count = 1;
		while (!feof($file)) {

			if ($delimiter == "\t") {
				$line = fgets($file);
				$words = explode("\t", $line);
				$finalLine = "";
				foreach ($words as $word) {
					$finalLine = $finalLine . (($finalLine == "") ? "" : ",") . "\"" . addslashes($word) . "\"";
				}
				$newRows = str_getcsv($finalLine);
			}
			else {
				$newRows = fgetcsv($file);
			}

			if (!empty($newRows)) {
				if ($count == 1) {
					foreach ($newRows as $key => $value) {
						$csvHeadingFields[ $key ] = $value;
					}
				}
				else {
					foreach ($newRows as $key => $value) {
						$csvFields[ $key ][] = $value;
					}
				}
			}

			if ($count == 6) {
				break;
			}

			$count++;

		}
		fclose($file);

		$matchedColumns = array_fill(0, count($csvHeadingFields), false);
		$matchedColumnsDetail = array_fill(0, count($csvHeadingFields), -1);
		$matchCount = 0;
		$leadMatchedColumns = [];

		foreach ($csvHeadingFields as $key => $value) {
			$currentCsvHeadingField = strtolower(str_replace([' ', '_'], '', trim($value)));

			foreach ($formColumns as $key1 => $value1) {
				$currentFromColumnField = strtolower(str_replace([' ', '_'], '', trim($value1->fieldName)));

				if ($currentCsvHeadingField == $currentFromColumnField) {
					$matchedColumns[ $key ] = true;
					$matchedColumnsDetail[ $key ] = $value1['id'];
					$leadMatchedColumns[ $value1['id'] ] = 1;
					$matchCount++;
					break;
				}
			}
		}

		$result = [];
		$result['csvFields'] = $csvFields;
		$result['csvHeadingFields'] = $csvHeadingFields;
		$result['leadFields'] = $leadFields;
		$result['formColumnDetailsByID'] = $formColumnDetailsByID;
		$result['matchedColumns'] = $matchedColumns;
		$result['unmatchCount'] = count($leadFields) - $matchCount;
		$result['matchedColumnsDetail'] = $matchedColumnsDetail;
		$result['matchCount'] = $matchCount;
		$result['leadMatchedColumns'] = $leadMatchedColumns;

		return $result;
	}

	function cancelImportData() {
		if (Request::ajax()) {
			//Removing Session Variables
			if (Session::get('newCampaignCsvFile')) {
				$campaignID = Session::get('newCampaignID');

				$filePath = storage_path('excel/exports/') . Session::get('newCampaignCsvFile');
				Session::forget('newCampaignCsvFile');
				Session::forget('newCampaignFormID');
				Session::forget('newCampaignID');

				//Deleting Campaign
				Campaign::destroy($campaignID);
				unlink($filePath);
				$data['status'] = 'success';
				$data['message'] = 'Campaign deleted successfully';
			}
			else {
				if (Session::get('newImportDataCsvFile')) {
					$filePath = storage_path('excel/exports/') . Session::get('newImportDataCsvFile');
					Session::forget('newImportDataCsvFile');
					Session::forget('newImportDataCampaignID');
					Session::forget('newImportDataFormID');
					unlink($filePath);
					$data['status'] = 'success';
					$data['message'] = 'Campaign deleted successfully';
				}
				else {
					$data['status'] = 'fail';
					$data['message'] = 'error deleting campaign';
				}
			}

			return $data;
		}
		else {
			return ["status"  => "fail", "message" => "This method cannot be called directly"];
		}
	}

	public function step4(SimpleRequest $request) {
		set_time_limit(0);

		$date = new DateTime();
		Session::put('step4StartingTime', $date);

		$mapping = Input::get("sorting");
		$postalCodeFieldFound = false; //Assuming postalCode is not in mapping array

		//Getting Csv File and campaignID From Session Variable
		if (Session::get('newCampaignCsvFile')) {
			$filePath = storage_path('excel/exports/') . Session::get('newCampaignCsvFile');
			$formID = Session::get('newCampaignFormID');
			$campaignID = Session::get('newCampaignID');
		}
		else {
			if (Session::get('newImportDataCsvFile')) {
				$filePath = storage_path('excel/exports/') . Session::get('newImportDataCsvFile');
				$formID = Session::get('newImportDataFormID');
				$campaignID = Session::get('newImportDataCampaignID');
			}
			else {
				return Redirect::to('user/campaigns/createcampaign');
			}
		}

		// Key to store progress in cache
		$cacheKey = "importProgress" . $campaignID;
		$expire = Carbon::now()->addMinutes(360);
		Cache::put($cacheKey, "0", $expire);
		

		$duplicates = 0;
		if (!empty($filePath)) {
			//Array From Step 3
			$mappingArray = json_decode($mapping, false);

			$duplicatesProcessingMode = $request->input('duplicates', '');
			$uniqueColumnsArray = json_decode($request->input('uniqueKeys', '[]'), false);

			//Array contains fields Name from form table associated with index
			$mappingFieldArray = [];
			$uniqueFieldNames = [];

			foreach ($mappingArray as $key => $value) {
				if ($value == "-1" || $value == "-2") {
					$mappingFieldArray[] = $value;
				}
				else {
					$formIDName = FormFields::select(['fieldName'])->where('id', $value)->get()->first()->fieldName;
					$mappingFieldArray[] = $formIDName;
					if(isset($uniqueColumnsArray[$key]) && $uniqueColumnsArray[$key]) {
						$uniqueFieldNames[] = $formIDName;
					}
				}
			}

			//Log::error("duplicates : " . $duplicatesProcessingMode);
			//Log::error("unique keys: " . implode(',', $uniqueFieldNames));

			//Array Store FormsID which are not selected
			$remainingMappingArray = [];
			$doneMappingArray = [];

			$formColumns = FormFields::select('id', 'fieldName', 'type')->where('formID', $formID)->get();
			$fieldId2typeMapping = [];

			foreach ($formColumns as $formColumn) {
				if (in_array($formColumn->id, $mappingArray) == false) {
					$remainingMappingArray[ $formColumn->id ] = $formColumn->fieldName;
				}
				else {
					$doneMappingArray[] = $formColumn->id;
				}
				$fieldId2typeMapping[$formColumn->id] = $formColumn->type;
			}

			$csvOrTsvIndicator = substr($filePath, strrpos($filePath, "/") + 1, 1);

			$delimiter = ",";

			if ($csvOrTsvIndicator == "T") {
				$delimiter = "\t";
			}

			//Opening file for Reading and fetching Data
			$file = fopen($filePath, "r");

			// We measure progress as number of bytes processed
			$fileBytes = filesize($filePath);

			$count = 1;
			$totalLeadCustomDataAdded = 0;
			$totalLeadCustomDataUpdated = 0;

			DB::beginTransaction();

			try {
				while (!feof($file)) {
					$newRows = fgetcsv($file, 0, $delimiter);

					if (!empty($newRows) && $count > 1) {
						// Array which store those field which may be inserted
						$newInsertedDataArray = [];

						foreach ($newRows as $key => $value) {
							if ($mappingFieldArray[ $key ] == "-1" || $mappingFieldArray[ $key ] == "-2") {
								continue;
							}
							else {
								$fieldID = $mappingArray[ $key ];
								$fieldName = $mappingFieldArray[ $key ];
								$newInsertedDataArray[ $fieldID ] = ['fieldID'   => $fieldID, 'fieldName' => $fieldName, 'value'     => $value];

								//Checking postal code Field is found or not
								if ($formIDName == 'Post/Zip code') {
									$postalCodeFieldFound = true;
									$postalCodeFieldValue = $value;
								}

							}
						}

						//sort this array by index
						$fieldString = "";
						foreach ($newInsertedDataArray as $value) {
							$fieldString .= $value['value'];
						}

						$fieldStringHash = md5($fieldString . $campaignID);

						$isLeadHashFound = Lead::where('leadHash', '=', $fieldStringHash)
							->where('campaignID', '=', $campaignID)
							->exists() ? 1 : 0;

						$isDuplicateFound = false;
						if($isLeadHashFound == 0 && count($uniqueFieldNames)) {
							//search via unique keys
							$leadDuplicatesQuery = Lead::where('campaignID', '=', $campaignID)
								->select('leads.*')
								;
							$subjoin_id = 0;
							foreach ($newInsertedDataArray as $value) {
								if(in_array($value['fieldName'], $uniqueFieldNames)) {
									$subjoin_id++;
									$leadDuplicatesQuery->where(function($query) use($value, $leadDuplicatesQuery, $subjoin_id) {
										$subjoin_alias = 'flt_customdata_' . $subjoin_id;
										$leadDuplicatesQuery->join('leadcustomdata as ' . $subjoin_alias, $subjoin_alias . '.leadID', '=', 'leads.id');
										$query->where(function($q) use($value, $subjoin_alias) {
											$valueToLookAt = $value['value'];
											//fix dates on import
											if(isset($fieldId2typeMapping[$value['fieldID']])
												&& $fieldId2typeMapping[$value['fieldID']] == 'date'
											) {
												$valueToLookAt = FormFields::validateDateFieldValue($valueToLookAt);
											}
											$q->where($subjoin_alias . '.fieldName', $value['fieldName'])
											  ->where($subjoin_alias . '.value', $valueToLookAt);
										});
									});
								}
							}
							//Log::error("query : " . $leadDuplicatesQuery->toSql());
							$isDuplicateFound = $leadDuplicatesQuery->exists();
						}

						// If Duplicate Entry Not found
						if ($isLeadHashFound == 0 && (!$isDuplicateFound || $duplicatesProcessingMode == 'update')) {

							if($isDuplicateFound && $duplicatesProcessingMode == 'update') {
								$newLead = $leadDuplicatesQuery->first();
							} else {
								//Generating New Lead
								$newLead = new Lead;
								$newLead->campaignID = $campaignID;
								$newLead->timeCreated = new DateTime();
							}

							if ($postalCodeFieldFound) {
								$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])->where('postalCode', $postalCodeFieldValue)->first();

								if ($postalCodeDetails) {
									$newLead->latitude = $postalCodeDetails->latitude;
									$newLead->longitude = $postalCodeDetails->longitude;
									$newLead->countryCode = $postalCodeDetails->countryCode;
								}
								else {
									$newLead->latitude = null;
									$newLead->longitude = null;
									$newLead->countryCode = null;
								}
							}

							$newLead->leadHash = $fieldStringHash;
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

							// Array to hold custom fields data before insert
							$leadCustomDataArray = [];

							foreach ($newRows as $key => $value) {
								if (!($mappingFieldArray[ $key ] == "-1" || $mappingFieldArray[ $key ] == "-2")) {

									//fix dates on import
									if(isset($fieldId2typeMapping[$mappingArray[ $key ]])
										&& $fieldId2typeMapping[$mappingArray[ $key ]] == 'date'
									) {
										$value = FormFields::validateDateFieldValue($value);
									}

									$leadCustomDataArray[] = [
											"leadID" => $leadID,
											"fieldID" => $mappingArray[ $key ],
											"fieldName" => $mappingFieldArray[ $key ],
											"value" => $value
									];
								}
							}

							// Enter Data for skipped column with null value
							foreach ($remainingMappingArray as $key => $name) {
								$leadCustomDataArray[] = [
										"leadID" => $leadID,
										"fieldID" => $key,
										"fieldName" => $name,
										"value" => ''
								];
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
						}
						else {
							$duplicates++;
						}
					}
					$count++;

					$currentPosition = ftell($file);
					$processingCompleted = ($currentPosition / $fileBytes) * 100;

					Cache::put($cacheKey, $processingCompleted, $expire);
				}

				DB::commit();
			}
			catch(Exception $e){
				DB::rollBack();
				Log::error("Error importing data.: " . $e->getMessage());
				return Response::make("Error importing data. No changes were made. Please contact support with error: ". $e->getMessage(), 500);
			}

			fclose($file);

			// Updating totalLeads and remainingCalls in Campaign
			$currentCampaign = Campaign::find($campaignID);
			$currentCampaign->totalLeads += $totalLeadCustomDataAdded;
			$currentCampaign->save();
			$currentCampaign->recalculateCallsRemaining();
			$currentCampaign->recalculateTotalFilteredLeads();

		}

		Session::put('doneMappingArray', $doneMappingArray);
		Session::put('duplicates', $duplicates);
		Session::put('duplicates_updated', $totalLeadCustomDataUpdated);

		$data = [];
		$data['success'] = 'success';

		return json_encode($data);
	}

	public function checkStep4Status() {
		$campaignID = Session::get('newCampaignID');

		if (empty($campaignID)) {
			$campaignID = Session::get('newImportDataCampaignID');
		}

		$key = "importProgress" . $campaignID;
		$processingCompleted = Cache::get($key);

		return $processingCompleted;
	}

	public function step5() {
		// Getting CSV File and campaignID From Session Variable
		if (Session::get('newCampaignCsvFile')) {
			$filePath = storage_path('excel/exports/') . Session::get('newCampaignCsvFile');
			$campaignID = Session::get('newCampaignID');
		}
		else {
			if (Session::get('newImportDataCsvFile')) {
				$filePath = storage_path('excel/exports/') . Session::get('newImportDataCsvFile');
				$campaignID = Session::get('newImportDataCampaignID');
			}
			else {
				return Redirect::to('user/campaigns/createcampaign');
			}
		}

		$doneMappingArray = Session::get('doneMappingArray');
		$date = Session::get('step4StartingTime');
		$duplicates = Session::get('duplicates');

		// Unset other session variables
		Session::forget('doneMappingArray');
		Session::forget('step4StartingTime');
		Session::forget('duplicates');

		unlink($filePath);

		if (Session::get('newCampaignCsvFile')) {
			Session::forget('newCampaignCsvFile');
			Session::forget('newCampaignFormID');
			Session::forget('newCampaignID');
		}
		else {
			if (Session::get('newImportDataCsvFile')) {
				Session::forget('newImportDataCsvFile');
				Session::forget('newImportDataCampaignID');
				Session::forget('newImportDataFormID');
			}
		}

		// Basic Menu Settings
		$data = ['leadsMenuActive'  => 'nav-active active', 'leadsStyleActive' => 'display: block', 'leadsCampaignActive' => 'active'];

		$data["duplicates"] = $duplicates;
		$data["duplicates_updated"] = Session::get('duplicates_updated');
		Session::forget('duplicates_updated');

		if (count($doneMappingArray) == 0) {
			// No column was inserted
			$data['nocolumn'] = "true";
		}
		else {

			$formID = Campaign::find($campaignID)->formID;

			$data['tableHeaders'] = FormFields::select(["fieldName"])
				->where("formID", "=", $formID)
				->get();

			$fieldCount = count($data['tableHeaders']);

			// Now we need only first 100 leads
			$tableDatas = LeadCustomData::select('fieldName', 'value', 'leadID')
				->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
				->where('leads.timeCreated', '>=', $date)
				->orderBy("leadID", "DESC")
				->limit(100 * $fieldCount)
				->get();

			$newTableDatas = [];
			foreach ($tableDatas as $tableData) {
				$newTableDatas[ $tableData->leadID ][ $tableData->fieldName ] = $tableData->value;
			}

			$data['newTableDatas'] = $newTableDatas;
		}

		return View::make('site/campaigns/campaignstep4', $data);
	}

	public function importCampaignLeads($selectedCampaignID = null)
	{

		if (Session::get('newImportDataCsvFile')) {
			$filePath = storage_path('excel/exports/') . Session::get('newImportDataCsvFile');
			if(file_exists($filePath)) {
				@unlink($filePath); //can be deleted by other place ?
			}
			Session::forget('newImportDataCsvFile');
			Session::forget('newImportDataCampaignID');
		}

		//Basic Menu Settings
		$data = [
			'leadsMenuActive'  => 'nav-active active'
			,'leadsStyleActive' => 'display: block'
			,'leadsImportActive' => 'active'
			,'selectedCampaignID' => $selectedCampaignID
		];

		$userCampaigns = $this->user->getActiveCampaigns();

		if ($userCampaigns) {
			$data['userCampaignLists'] = $userCampaigns;
		}
		else {
			$data['userCampaignLists'] = [];
		}

		$data['maxFileSize'] = Setting::get('maxFileUploadSize');

		return View::make('site/campaigns/importCampaignLeadData', $data);
	}

	public function importCampaignDataToCsv() {
		//First Time when file will be upladed by post method
		if (Request::isMethod('post')) {

			if(!Input::has('campaignName')) {
				return Redirect::to('user/campaigns/importcampaignleads')->with('message', 'Uploaded csv file in too large to be processed. Please try again');
			}

			$campaignID = Input::get('campaignName');

			$fileName = $this->createNewCsvFileForCampaign();
			$filePath = storage_path('excel/exports/') . $fileName;

			$formID = Campaign::where('id', $campaignID)->get(['formID'])->first()->formID;

			try {
				$data = $this->csvDataForFilter($filePath, $formID);
			} catch(Exception $e) {
				return Redirect::to('user/campaigns/importcampaignleads')->with('message', 'Import error : ' . $e->getMessage());
			}

			Session::put('newImportDataCsvFile', $fileName);
			Session::put('newImportDataCampaignID', $campaignID);
			Session::put('newImportDataFormID', $formID);
			Session::put('duplicateDataFilter', true);
		}
		else {
			if (Session::get('newImportDataCsvFile') && Session::get('newImportDataCampaignID')) {

				$campaignID = Session::get('newImportDataCampaignID');

				$fileName = Session::get('newImportDataCsvFile');
				$filePath = storage_path('excel/exports/') . $fileName;

				$formID = Campaign::where('id', $campaignID)->get(['formID'])->first()->formID;
				$data = $this->csvDataForFilter($filePath, $formID);
			}
			else {
				return Redirect::to('user/campaigns/importcampaignleads');
			}
		}

		$data['leadsMenuActive'] = 'nav-active active';
		$data['leadsStyleActive'] = 'display: block';
		$data['leadsCampaignActive'] = 'active';
		$data['is_new_campaign'] = false;

		return View::make('site/campaigns/campaignstep3', $data);
	}

	public function listCampaigns() {
		$campaignLists = $this->user->getActiveCampaigns();
		$teamMembers = $this->user->getTeamMembers();

		if(sizeof($campaignLists) > 0) {
			for($i = 0; $i < sizeof($campaignLists); $i++) {
				$campaignLists[$i]['formFields'] = $campaignLists[$i]->getFormFields();
			}
		}

		$data = [
			'leadsMenuActive' => 'nav-active active',
			'leadsStyleActive' => 'display: block',
			'leadsExportActive' => 'active',
			'campaignLists' => $campaignLists,
			'teamMembers' => $teamMembers
		];

		return View::make('site/campaigns/listcampaigns', $data);
	}

	public function ajaxListCampagins() {
		if (Request::ajax()) {
			$selectedValue = Input::get('selectedValue');

			if ($selectedValue == 'Completed') {
				$campaignLists = $this->user->getCompletedCampaigns();
			}
			else {
				if ($selectedValue == 'Started') {
					$campaignLists = $this->user->getStartedCampaigns();
				}
				else {
					if ($selectedValue == 'All') {
						$campaignLists = $this->user->getAllCampaigns();
					}
					else {
						Redirect::to('campaigns/listcampaigns');
						return null;
					}
				}
			}

			$data['campaignLists'] = $campaignLists;
			return View::make('site/campaigns/ajaxlistcampaigns', $data);
		}
		else {
			return Redirect::to('campaigns/listcampaigns');
		}
	}

	/**
	 * Export campaign data according to provided criteria. Export data functions require making lot of custom queries.
	 * So, we can place the queries in controller here
	 */
	public function exportCampaignsData() {
		set_time_limit(0);

		$type = Input::get('type');

		if($type == 'simple') {
			$exportType = Input::get('exportType');
			$campaigns = json_decode(Input::get('campaigns'));

			# create a new zipStream object
			$zip = new ZipStream('CampaignData_' . (new DateTime())->format("Y_m_d_H_i_s") . ".zip");

			foreach ($campaigns as $campaign) {
				$campaignData = Campaign::find($campaign);

				// Set File attachment headers to force download
				$fileName = $campaignData->name . '_' . (new DateTime())->format("Y_m_d_H_i_s") . ".csv";

				$campaignID = $campaignData->id;

				$selectedArray = ['campaigns.name', 'leads.id as leadID', 'leads.referenceNumber', 'leads.timeCreated', 'leads.timeEdited', 'leads.status',
					'leads.interested','leads.bookAppointment',
					DB::raw("CONCAT(actioners.firstName, ' ',actioners.lastName) as actionerName"), 
				];

				$results = Campaign::join('leads', 'leads.campaignID', '=', 'campaigns.id');

				// Add conditions according to export type
				if ($exportType == 'all') {
					//do nothing
				}
				else if ($exportType == 'interested') {
					$results->where('leads.interested', 'interested');
				}
				else if ($exportType == 'notinterested') {
					$results->where('leads.interested', 'notinterested');
				}
				else if ($exportType == 'outstanding') {
					$results->where('leads.status', 'Unactioned');
				}
				else if ($exportType == 'callsmade') {
					$results->where('leads.status', 'Actioned');
				}
				else if ($exportType == 'booked') {
					$selectedArray[] = 'salesmembers.firstName';
					$selectedArray[] = 'salesmembers.lastName';
					$selectedArray[] = 'appointments.time';
					$results->join('appointments', 'appointments.leadID', '=', 'leads.id')
					        ->join('salesmembers', 'salesmembers.id', '=', 'appointments.salesMember')
					        ->where('leads.bookAppointment', 'Yes');
				}
				
				$selectedArray[] = \DB::raw('
						(SELECT COUNT(*) AS count FROM callbacks WHERE status =\'Completed\' and leadID=leads.id) AS callCount
				');
				
				$selectedArray[] = \DB::raw('
						(SELECT COUNT(*) AS count FROM call_history WHERE leadID=leads.id) AS callHistoryCount
				');

				$results->leftJoin('users as actioners', 'actioners.id', '=', 'leads.lastActioner');

				$results = $results->where('campaigns.id', $campaignID)->get($selectedArray);

				// Array to store CSV headers
				if ($exportType == 'booked') {
					$newInsertedCsvArray = ['Campaign Name', 'Reference Number', 'Time Created', 'Time Edited', 'Status', 'Interested', 'Book Appointment',
						'Sales Member Name', 'Appointment Time'];
				}
				else {
					$newInsertedCsvArray = ['Campaign Name', 'Reference Number', 'Time Created', 'Time Edited', 'Status', 'Interested', 'Book Appointment'];
				}

				// Query for extracting unique field name from given campaigns
				$leadDataHeadings = LeadCustomData::select('fieldID', 'fieldName', 'value', 'leadID')
				                                  ->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
				                                  ->where('leads.campaignID', $campaignID)
				                                  ->groupBy('fieldID')
				                                  ->orderBy("fieldID")
				                                  ->get();

				// Putting these Unique Fields to header
				foreach ($leadDataHeadings as $leadDataHeading) {
					$newInsertedCsvArray[] = $leadDataHeading->fieldName;
				}
				
				$newInsertedCsvArray = array_merge($newInsertedCsvArray, ['Contacted', 'Historical Notes']);
				$newInsertedCsvArray = array_merge($newInsertedCsvArray, ['Agent']);
				$newInsertedCsvArray = array_merge($newInsertedCsvArray, ['Follow up call']);

				$file = tmpfile();
				fputcsv($file, $newInsertedCsvArray, ",", "\"");

				// TODO: In case of campaigns with multiple forms, the results may not show under their respective columns
				$count = 0;

				// Adding other elements other than heading
				foreach ($results as $result) {
					$newArray = [];
					$newArray[] = $result->name;
					$newArray[] = $result->referenceNumber;
					$newArray[] = $result->timeCreated;
					$newArray[] = $result->timeEdited;
					$newArray[] = $result->status;
					$newArray[] = CommonController::getInterestedType($result->interested);
					$newArray[] = $result->bookAppointment;

					if ($exportType == 'booked') {
						$newArray[] = $result->firstName . ' ' . $result->lastName;
						$newArray[] = $result->time;
					}

					$leadData = LeadCustomData::select(['value'])
					                          ->where('leadID', $result->leadID)
					                          ->orderBy("fieldID")
					                          ->get();

					// Extracting values of CustomFieldData columns
					foreach ($leadData as $leadField) {
						$newArray[] = $leadField->value;
					}
					
					if($result->status == 'Actioned') {
						$newArray[] = ($result->callCount + 1) . ' times';
					}
					else {
						$newArray[] = $result->callCount . ' times';
					}
					
					if($result->callHistoryCount > 0) {
						$notes = CallHistory::select(['created_at', 'notes'])
					                          ->where('leadID', $result->leadID)
					                          ->orderBy("id")
					                          ->get();
						if(sizeof($notes) > 0) {
							$noteTxt = '';
							foreach($notes as $note) {
								$noteTxt .= 'Notes on '.date('jS M Y', strtotime($note->created_at));
								$noteTxt .= "\n".$note->notes;
								$noteTxt .= "\n\n";
							}

							$newArray[] = $noteTxt;
						}
					} else {
						$newArray[] = '';
					}

					$newArray[] = $result->status == 'Actioned' ? $result->actionerName : '';

					$callBack = Lead::find($result->leadID)->getCallBack();
					if ( $callBack ) {
						$newArray[] = $callBack->time;
					} else {
						$newArray[] = '';
					}



					fputcsv($file, $newArray, ",", "\"");
					$count++;
				}

				$zip->addFileFromStream($fileName, $file);
				fclose($file);
			}

			// Finish the zip stream
			$zip->finish();
		}
		else if($type == 'advance') {
			$campaignID = Input::get('campaign');
			$campaign = Campaign::find($campaignID);

			$interested = Input::get('leadType');
			$dateFrom = Input::get('from');
			$dateTo = Input::get('to');
			$lastActioner = Input::get('teamMember');

			$formFields = $campaign->getFormFields();

			$where = [];
			$displayArray = [];

			$leadQuery = new LeadCustomData();

			if(sizeof($formFields) > 0) {
				foreach($formFields as $formField) {
					$name = str_replace(' ', '_', $formField->fieldName);
					if(Input::has('check_'.$name)){
						$displayArray[] = $formField->fieldName;

						if($formField->type == 'dropdown' && Input::get($name) != 'All') {
							array_push($where, ['fieldName' => $formField->fieldName, 'value' => Input::get($name)]);
							$leadQuery = $leadQuery->orWhere(function($query) use($formField, $name) {
								$query->where('fieldName', $formField->fieldName)->where('value', Input::get($name));
							});
						}
					}
				}
			}

			if(sizeof($where)  > 0) {
				$leadIDs = $leadQuery->groupBy('leadID')
	                     ->having(DB::raw('COUNT(leadID)'), '=', sizeof($where))
	                     ->lists('leadID')->all();
			}
			else {
				$leadIDs = [];
			}

			$selectedArray = ['campaigns.name', 'leads.id as leadID', 'leads.referenceNumber', 'leads.timeCreated', 'leads.timeEdited', 'leads.status',
				'leads.interested','leads.bookAppointment',
				DB::raw("CONCAT(actioners.firstName, ' ',actioners.lastName) as actionerName"), 
			];

			$sqlQuery = Campaign::join('leads', 'leads.campaignID', '=', 'campaigns.id')
				->where('campaigns.id', $campaignID)
				->where(function($query) use($interested) {
					if($interested != 'All') {
						$query->where('leads.interested', $interested);
					}
				})
				->where(function($query) use($lastActioner) {
					if($lastActioner != 'All') {
						$query->where('leads.lastActioner', $lastActioner);
					}
				})
				->where(function($query) use($dateFrom, $dateTo) {
					if($dateFrom != '') {
						$query->where(DB::raw('leads.timeEdited'), '>=', date('Y-m-d 00:00:00', strtotime($dateFrom)));
					}

					if($dateTo != '') {
						$query->where(DB::raw('leads.timeEdited'), '<=', date('Y-m-d 23:59:59', strtotime($dateTo)));
					}
				})
				->where(function($query) {
					if(Input::has('appointmentBooked')) {
						if(Input::get('appointmentBooked') == 'Yes') {
							$query->where('leads.bookAppointment', 'Yes');
						}
					}
				})
				->where(function($query) use($where, $leadIDs) {
					if(sizeof($where)  > 0) {
						$query->whereIn('leads.id', $leadIDs);
					}
				})
				->leftJoin('users as actioners', 'actioners.id', '=', 'leads.lastActioner')
				;

			$zip = new ZipStream($campaign->name . '_' . (new DateTime())->format("Y_m_d_H_i_s") . ".zip");
			$csvArray = ['Campaign Name', 'Reference Number', 'Time Created', 'Time Edited', 'Status', 'Interested', 'Book Appointment'];


			if(Input::has('contacted')) {
				if(Input::get('contacted') == 'Yes') {
					$selectedArray = array_merge($selectedArray, [
						\DB::raw('
							COALESCE (
							(
								SELECT COUNT(*) AS count
								FROM callbacks
								WHERE callbacks.leadID = leads.id
								 AND  status = \'Completed\'
								GROUP BY leadID
							)
							, 0) AS callCount

					')]);
					$csvArray = array_merge($csvArray, ['Contacted']);
				}
			}

			$csvArray = array_merge($csvArray, $displayArray);

			$results = $sqlQuery->get($selectedArray);

			if(Input::has('historicalNotes')) {
				if(Input::get('historicalNotes') == 'Yes') {
					$csvArray = array_merge($csvArray, ['Historical Notes']);
				}
			}

			if(Input::has('appointmentBooked')) {
				if(Input::get('appointmentBooked') == 'Yes') {
					$csvArray = array_merge($csvArray, ['Sales Member Name', 'Appointment Time']);
				}
			}

			$csvArray = array_merge($csvArray, ['Agent']);

			if (Input::has('followupcalls')) {
				if(Input::get('followupcalls') == 'Yes') {
					$csvArray = array_merge($csvArray, ['Follow up call']);
				}
			}

			$file = tmpfile();
			fputcsv($file, $csvArray, ',', "\"");
			//print_r($csvArray); die;

			if(sizeof($results) > 0) {
				foreach($results as $result) {
					$data = [];

					$lead = Lead::find($result->leadID);

					$data[] = $result->name;
					$data[] = $result->referenceNumber;
					$data[] = $result->timeCreated;
					$data[] = $result->timeEdited;
					$data[] = $result->status;
					$data[] = CommonController::getInterestedType($result->interested);
					$data[] = $result->bookAppointment;

					if(in_array('Contacted', $csvArray)) {
						if($lead->status == 'Actioned') {
							$data[] = ($result->callCount + 1) . ' times';
						}
						else {
							$data[] = $result->callCount . ' times';
						}
					}

					//for custom fields
					$customFields = $lead->getCustomData();
					//print_r($customFields);die;

					if(sizeof($customFields)) {
						foreach($displayArray as $arr) {
							foreach($customFields as $fields) {
								if($fields->fieldName == $arr) {
									$data[] = $fields->value;
								}
							}
						}
					}

					//check for attachments and historical notes
					if(Input::has('attachments')) {
						if(Input::get('attachments') == 'Yes') {
							$attachments = $lead->getLeadAttachment();
							if(sizeof($attachments) > 0) {
								foreach($attachments as $attachment) {
									$fileName = 'attachments/lead_'.$result->referenceNumber.'/'.$attachment->originalFileName;
									$zip->addFileFromPath($fileName, storage_path().'/attachments/lead/'.$attachment->fileName);
								}
							}
						}
					}

					if(Input::has('historicalNotes')) {
						if(Input::get('historicalNotes') == 'Yes') {
							$notes = $lead->getCallHistoryNotes();
							if(sizeof($notes) > 0) {
								$noteTxt = '';
								foreach($notes as $note) {
									$noteTxt .= 'Notes on '.date('jS M Y', strtotime($note->created_at));
									$noteTxt .= "\n".$note->notes;
									$noteTxt .= "\n\n";
								}

								$data[] = $noteTxt;
							}
						}
					}

					if(Input::has('appointmentBooked')) {
						if(Input::get('appointmentBooked') == 'Yes') {
							if($result->bookAppointment == 'Yes') {
								$appointment = Appointment::where('leadID', $result->leadID)
									->join('salesmembers', 'salesmembers.id', '=', 'appointments.salesMember')
									->select(['salesmembers.firstName', 'salesmembers.lastName', 'appointments.time'])
									->first();

								if(sizeof($appointment) > 0) {
									$data[] = $appointment->firstName . ' ' . $appointment->lastName;
									$data[] = $appointment->time;
								}
							}
						}
					}

					$data[] = $lead->status == 'Actioned' ? $result->actionerName : '';


					if (Input::has('followupcalls')) {
						//TODO: add followup calls
						$callBack = $lead->getCallBack();
						if ( $callBack ) {
							$data[] = $callBack->time;
						} else {
							$data[] = '';
						}
					}


					fputcsv($file, $data, ',', "\"");
				}
			}

			$zip->addFileFromStream('leads.csv', $file);
			fclose($file);

			$zip->finish();
		}
	}

	public function ajaxCampaignMembers() {
		if (Request::ajax()) {
			$campaignID = Input::get('campaignID');

			if ($this->user->userType != "Multi") {
				return ["status"  => "error", "message" => "You are not allowed to edit members"];
			}

			$teamMembers = $this->user->getTeamMembers();
			$campaignMembers = Campaign::find($campaignID)->getMembers();

			$text = "";

			foreach ($teamMembers as $teamMember) {
				//TODO: user type multi user
				if ($teamMember->userType == "Multi")
					continue;

				$isCampaignMember = $campaignMembers->find($teamMember->id);
				$teamMembersID = $teamMember->id;
				$name = $teamMember->firstName . ' ' . $teamMember->lastName;

				$text .= "<div class='ckbox ckbox-primary  col-sm-4'><input type='checkbox' id='campaignUserMember_{$teamMembersID}' value='{$teamMembersID}' onclick='addOrRemoveCampaignUser({$teamMembersID},{$campaignID})' ";

				if ($isCampaignMember) {
					$text .= "checked";
				}

				$text .= "><label for='campaignUserMember_{$teamMembersID}'>{$name}</label></div>";
			}

			$data['status'] = "success";
			$data['text'] = $text;

			return $data;
		}
	}

	function addOrRemoveUserFromCampaign() {
		if (Request::ajax()) {
			$actionedUserID = Input::get('userID');
			$campaignID = Input::get('campaignID');
			$action = Input::get('action');

			$campaign = Campaign::find($campaignID);
			$campaignMembers = $campaign->getMembers();

			if ($this->user->userType != "Multi") {
				$data['status'] = "fail";
				$data['message'] = "You are not allowed to do this action";
			}
			else {

				// Checking user already added or not
				$userAlreadyInCampaign = $campaignMembers->filter(function ($model) use ($actionedUserID) {
					return $actionedUserID == $model->id;
				});

				if ($action == "Add") {
					if ($userAlreadyInCampaign->count() > 0) {
						$data['status'] = "fail";
						$data['message'] = "This member is already member in this campaign";
					}
					else {
						$newCampaignMember = new CampaignMember;
						$newCampaignMember->userID = $actionedUserID;
						$newCampaignMember->campaignID = $campaignID;
						$newCampaignMember->save();

						$data['status'] = "success";
						$data['message'] = "Team member successfully added to this campaign";
					}
				}
				else {
					if ($action = "Remove") {
						if ($userAlreadyInCampaign->count() > 0) {
							$campaign->removeMember($actionedUserID);
							$data['status'] = "success";
							$data['message'] = "Member successfully removed from this campaign";
						}
						else {
							$data['status'] = "fail";
							$data['message'] = "Team member not associated to this campaign";
						}
					}
				}
			}
			return $data;
		}
	}

	function leads() {
		$campaignLists = $this->user->getStartedCampaigns();
		$userCampaigns = $campaignLists->lists('id')->all();

		$user = $this->user;

		//get sales person
		$salesPerson = SalesMember::where('manager', $user->getManager())->get();

		//get form criteria
		$formFields = Campaign::select(['formfields.fieldName', 'formfields.type', 'formfields.values', DB::raw('GROUP_CONCAT(campaigns.id SEPARATOR \' \') AS campaignIDs')])
			->whereIn('campaigns.id', $userCampaigns)
			->join('formfields', 'formfields.formID', '=', 'campaigns.formID')
			->groupBy(['formfields.type', 'formfields.fieldName', 'formfields.values'])
			->orderBy('formfields.fieldName')
			->get();

		//get team members
		$teamMembers = $user->getTeamMembers('manager');

		//get the most recently updated campaign 
		$recentlyUpdatedCampaign = Lead::select(['campaignID', 'timeEdited as updatedDate'])
			->whereIn('campaignID', $userCampaigns)
			->orderBy('timeEdited', 'desc')
			->take(1)
			->union(
				Lead::select(['campaignID', 'timeCreated as updatedDate'])
				->whereIn('campaignID', $userCampaigns)
				->orderBy('timeCreated', 'desc')
				->take(1)
			)
			->orderBy('updatedDate', 'desc')
			->first();

		$recentlyUpdatedCampaignId = 0;
		if($recentlyUpdatedCampaign) {
			$recentlyUpdatedCampaignId = $recentlyUpdatedCampaign->campaignID;
		}

		$data = [
				'leadsMenuActive' => 'nav-active active',
				'leadsStyleActive' => 'display: block',
				'leadsListingActive' => 'active',
				'campaignList'     => $campaignLists,
				'salesPerson'      => $salesPerson,
				'formFields'       => $formFields,
				'teamMembers'      => $teamMembers,
				'recentlyUpdatedCampaignId'      => $recentlyUpdatedCampaignId,
		];

		return View::make('site/campaigns/leads', $data);
	}

	function getLeadData() {
		$leadType = Input::get('leadType');

		if (Input::has('campaign')) {
			$campaignID = Input::get('campaign');

			if ($campaignID == 'All') {
				$campaignLists = $this->user->getStartedCampaigns();
				$campaignIDs = $campaignLists->lists('id')->all();
			}
			else {
				$campaign = Campaign::findOrFail($campaignID);
				$this->authorize('can-see-campaign', $campaign);
				$campaignIDs = [$campaignID];
			}
		}
		else {
			$campaignLists = $this->user->getStartedCampaigns();
			$campaignIDs = $campaignLists->lists('id')->all();
		}

		$campaign = new Campaign();
		$data = $campaign->getLeadDataForGivenCampaign($campaignIDs, $leadType, $this->user->id);

		$data['campaignID'] = $campaignID;
		$data['leadType'] = $leadType;

		if(Input::has('column')) {
			$data['column'] = Input::get('column');
			$data['type'] = Input::get('type');
		}

		return View::make('site/campaigns/leadTableAllCampaigns', $data);
		//return View::make(count($campaignIDs) == 1 ? 'site/campaigns/leadTable' :  'site/campaigns/leadTableAllCampaigns', $data);
	}

	function leadActions() {
		//print, save and delete
		$task = Input::get('task');
		$leads = explode(',', Input::get('leads'));

		if ($task == 'delete') {
			$campaigns = $this->user->getAllCampaigns();

			$campaignsIdsToUpdate = Lead::select('campaignID')
				->whereIn('campaignID', $campaigns->pluck('id'))
				->whereIn('id', $leads)
				->groupBy('campaignID')
				->get()
				->pluck('campaignID')
				;

			Lead::whereIn('id', $leads)
				->whereIn('campaignID', $campaignsIdsToUpdate)
				->delete();

			Campaign::whereIn('id', $campaignsIdsToUpdate)
				->get()
				->each(function ($campaign) {
					$campaign->recalculateCallsRemaining();
					$campaign->recalculateTotalFilteredLeads();
					$campaign->recalculateTotalLeads();
				});

			return ['status'  => 'success', 'message' => "Selected leads has been deleted"];
		}
		else {
			if ($task == 'savePDF' || $task == 'print' || $task == 'savePDFWithAttachment') {
				// Get lead details of leads
				$leadDetails = Lead::select(['leads.id', 'leads.interested', 'leads.bookAppointment', 'leads.campaignID', 'leads.emailTemplate',
				                             'leads.referenceNumber', 'leads.timeTaken', 'campaigns.name', 'campaigns.totalLeads'])
					->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
					->whereIn('leads.id', $leads)
					->get();

				if($task == 'savePDFWithAttachment') {
					$zip = new ZipStream('UserData_'.(new DateTime())->format("Y_m_d_H_i_s").".zip");
				}

				if (sizeof($leadDetails) > 0) {
					for ($i = 0; $i < sizeof($leadDetails); $i++) {
						// Compute position of current lead as
						$leadDetails[ $i ]->currentLeadNumber = $leadDetails[$i]->getLeadNumber();

						$leadDetails[ $i ]->leadData = $leadDetails[$i]->getCustomData();

						if ($leadDetails[$i]->bookAppointment == 'Yes') {
							$currentLeadSalesmanDetails = Appointment::join('salesmembers', 'salesmembers.id', '=', 'appointments.salesMember')
								->where('leadID', $leadDetails[$i]->id)->first();

							$leadDetails[$i]->appointmentTime = CommonController::formatDateForDisplay($currentLeadSalesmanDetails->time);

							$leadDetails[$i]->salesMember = $currentLeadSalesmanDetails->salesMember;
							$leadDetails[$i]->salesMemberName = $currentLeadSalesmanDetails->firstName . " ". $currentLeadSalesmanDetails->lastName;
						}

						$leadDetails[$i]->callBack = false;

						$callBack = $leadDetails[$i]->getCallBack();

						if(sizeof($callBack) > 0) {
							$leadDetails[$i]->callBack = true;
							$leadDetails[$i]->callBackTime = CommonController::formatDateForDisplay($callBack->time);
							$leadDetails[$i]->callBackUser = $callBack->callBackUser;
						}

						$leadDetails[$i]->callHistory = $leadDetails[$i]->getLeadCallHistory();

						if($task == 'savePDFWithAttachment') {
							//get lead attachments for zip file
							$attachments = $leadDetails[$i]->getLeadAttachment();
							if(sizeof($attachments) > 0) {
								foreach($attachments as $attachment) {
									$fileName = 'leadNumber_'.$leadDetails[$i]->currentLeadNumber.'/'.$attachment->originalFileName;
									$zip->addFileFromPath($fileName, storage_path().'/attachments/lead/'.$attachment->fileName);
								}
							}
						}
					}

					$data['leads'] = $leadDetails;

					set_time_limit(0);
					$pdfName = 'lead_'.date("Y_m_d_H_i_s", time()).'.pdf';
					$pdfPath = storage_path() . '/attachments/lead/'.$pdfName;
					$pdf = App::make('dompdf.wrapper');
					$pdf->loadView("site/lead/printLead", $data);

					if($task == 'print') {
						return $pdf->stream($pdfName);
					}
					else if($task == 'savePDF') {
						return $pdf->download($pdfName);
					}
					else {
						$pdf->save($pdfPath);
						$zip->addFileFromPath('leadDetails.pdf', $pdfPath);
						$zip->finish();
					}
				}
			} elseif ($task == 'saveDOCX') {

				$leadDetails = Lead::select(['leads.id', 'leads.interested', 'leads.bookAppointment', 'leads.campaignID', 'leads.emailTemplate',
					'leads.referenceNumber', 'leads.timeTaken', 'campaigns.name', 'campaigns.totalLeads'])
					->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
					->whereIn('leads.id', $leads)
					->get();


				if (sizeof($leadDetails) > 0  && file_exists(base_path('resources/views/layouts') . DIRECTORY_SEPARATOR . 'lead_template.docx') ) {
					for ($i = 0; $i < sizeof($leadDetails); $i++) {
						// Compute position of current lead as
						$leadDetails[ $i ]->currentLeadNumber = $leadDetails[$i]->getLeadNumber();

						$leadDetails[ $i ]->leadData = $leadDetails[$i]->getCustomData();

						if ($leadDetails[$i]->bookAppointment == 'Yes') {
							$currentLeadSalesmanDetails = Appointment::join('salesmembers', 'salesmembers.id', '=', 'appointments.salesMember')
								->where('leadID', $leadDetails[$i]->id)->first();

							$leadDetails[$i]->appointmentTime = CommonController::formatDateForDisplay($currentLeadSalesmanDetails->time);

							$leadDetails[$i]->salesMember = $currentLeadSalesmanDetails->salesMember;
							$leadDetails[$i]->salesMemberName = $currentLeadSalesmanDetails->firstName . " ". $currentLeadSalesmanDetails->lastName;
						}

						$leadDetails[$i]->callBack = false;

						$callBack = $leadDetails[$i]->getCallBack();

						if(sizeof($callBack) > 0) {
							$leadDetails[$i]->callBack = true;
							$leadDetails[$i]->callBackTime = CommonController::formatDateForDisplay($callBack->time);
							$leadDetails[$i]->callBackUser = $callBack->callBackUser;
						}

						$leadDetails[$i]->callHistory = $leadDetails[$i]->getLeadCallHistory();

					}




					$leadFiles = array();


					foreach ($leadDetails as $iter=>$lead) {

						$template = TemplateFactory::load( base_path('resources/views/layouts') . DIRECTORY_SEPARATOR . 'lead_template.docx' );
						$template->assign([
							'leadHeader'	=> $leadDetails[ $iter ]->name,
						]);

						$fileName = 'lead_'.$iter."_".date("Y_m_d_H_i_s", time()).'.docx';
						$filePath = storage_path() . '/attachments/lead/'.$fileName;

						$data = $lead->leadData;
						$dataAssociated = array();

						$index = 0;
						foreach( $data as $i=>$v){
							if ($i%2 == 0) {
								$dataAssociated [$index]['fieldnamea']	= $v->fieldName;
								$dataAssociated [$index]['fieldvala']	= ($v->value != '' && $v->value != null) ? $v->value : '-';
								$dataAssociated [$index]['fieldnameb']	= '';
								$dataAssociated [$index]['fieldvalb']	= '';
							} else {
								$dataAssociated [$index]['fieldnameb']	= $v->fieldName;
								$dataAssociated [$index]['fieldvalb']	= ($v->value != '' && $v->value != null) ? $v->value : '-';
								$index++;
							}
						}

						$template->loop('coop', $dataAssociated );

						$tLoop = array(
							'leadName'		=> 'Lead #' . $lead->currentLeadNumber,

							'varinterested'	=> \App\Http\Controllers\CommonController::getInterestedType($lead->interested),
							'varref'		=> ($lead->referenceNumber != null && $lead->referenceNumber != '') ? $lead->referenceNumber : '-',
							'varfollow'		=> ($lead->callBack) ? $lead->callBackTime .', '.$lead->callBackUser->firstName.' '.$lead->callBackUser->lastName : 'No',
							'varbookapp'	=> ($lead->bookAppointment == 'Yes') ? $lead->appointmentTime .', '.$lead->salesMemberName : 'No'

						);

						$tHLoop = array();
						$lHistory = $lead->callHistory;

						if ( count($lHistory) ){
							foreach ($lHistory as $i=>$ch){
								$tHLoop[] = array(
									'varCT1'	=> \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ch->created_at)->format('d/m/Y') ."\r\n" ,
									'varCT2'	=> $ch->agentName . "\n",
									'varCT3'	=> ($ch->emailName != null && $ch->emailName != '') ? ($ch->emailName ."\n") : "- \n",
									'varCT4'	=> ($ch->notes != null && $ch->notes != '') ? ($ch->notes ."\n") : "- \n",
									'varCT5'	=> ($ch->callBookedWithName != null && $ch->callBookedWithName != '') ? ($ch->callBookedWithName ."\n") : "- \n",
									'varCT6'	=> ($ch->appointmentBookedWith != null && $ch->appointmentBookedWith != '') ? ($ch->appointmentBookedWith ."\n") : "- \n",
									'varCT7'	=> \App\Http\Controllers\CommonController::getTimeFromMillisecond($ch->callTime) . "\n",
								);
							}
						} else {
							$tHLoop[] = array(
								'varCT1'	=> '',
								'varCT2'	=> '',
								'varCT3'	=> '',
								'varCT4'	=> '',
								'varCT5'	=> '',
								'varCT6'	=> '',
								'varCT7'	=> '',
							);
						}

						$template->assign($tLoop);
						$template->loop('loop', $tHLoop);
						$template->save( $filePath );

						$leadFiles[] = [
							'name' => $fileName,
							'path' => $filePath
						];

					}

						$zip = new ZipStream( 'lead_'.date("Y_m_d_H_i_s", time()).".zip");
						foreach ($leadFiles as $k=>$v){
							if (file_exists($v['path'])) $zip->addFileFromPath( $v['name'], $v['path'] );
						}
						$zip->finish();

						foreach ($leadFiles as $k=>$v){
							if (file_exists($v['path'])) @unlink($v['path']);
						}

				}



			}
		}
	}

	function verifyEmailSetting(SimpleRequest $request) {
		set_time_limit(0);

		if(Request::ajax()) {
			$fromEmail = trim(Input::get('fromEmail'));
			$replyToEmail = trim(Input::get('replyToEmail'));
			$host = trim(Input::get('host'));
			$port = trim(Input::get('port'));
			$username = trim(Input::get('username'));
			$password = trim(Input::get('password'));
			$security = Input::get('security');

			$error = '';

			$validatorArray = [
				'fromEmail' => 'required|email',
				'replyToEmail' => 'required|email',
				'host' => 'required',
				'port' => 'required',
				'username' => 'required',
				'password' => 'required',
				'security'  => 'required|in:No,tls,ssl',
			];

			$requestData = $request->all();
			foreach($requestData as &$requestValue) {
				$requestValue = trim($requestValue);
			}

			//Validation Rules for user Info
			$validator = Validator::make($requestData, $validatorArray);

			if ($validator->fails()) {
				//If Validation rule fails
				$messages = $validator->messages();
				foreach ($messages->all() as $message) {
					$error .= $message . '<br>';
				}

				$data['status'] = "error";
				$data['message'] = $error;
			}
			else {
				Config::set("mail.driver", 'smtp');
				Config::set("mail.host", $host);
				Config::set("mail.port", $port);
				Config::set("mail.username", $username);
				Config::set("mail.password", $password);
				Config::set("mail.encryption", $security != 'No' ? $security : null);

				//reload config
				CommonController::reloadMailConfig();

				try {
					$emailInfo = [
						'from'      => $fromEmail,
						'fromName'  => 'Sanityos',
						'replyTo'   => $replyToEmail,
						'subject'   => "Testing Email",
						'to'        => $fromEmail,
						'bccEmail' => $this->user->bccEmail
					];
					$attachments = [];
					CommonController::prepareAndSendEmail($emailInfo, "This is a Testing Email.", [], $attachments, true);

					$data['status'] = "success";
					$data['message'] = 'Email setting verified successfully';
				}
				catch (Exception $e) {
					$data['status'] = "error";
					$data['message'] = "Unable to send email. Please Check following details <br>". $e->getMessage();
				}
			}
			return $data;
		}
	}

	function getLandingPageUrl() {
		$campaignName = Input::get('campaignName');
		$landingFormID = Input::get('landingForm');

		$landingForm = Landingform::find($landingFormID);
		$campaign = new Campaign();
		$slug = $campaign->prepareSlug($campaignName);

		return [ 'status' => 'success', 'url' => URL::route('landing.signup', [$slug, $landingForm->slug])];
	}

	function assignLandingForm(SimpleRequest $request) {
		$campaignID = $request->input('campaignID');
		$campaign =Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);

		$landingFormID = $request->input('landingFormID', null);
		if(!is_null($landingFormID)) {
			//check if exists
			Landingform::findOrFail($landingFormID);
		}

		$campaign->landingFormID = $landingFormID;
		$campaign->resluggify();
		$campaign->save();
	}

	function getCampaignInformation()
	{
		$campaignID = Input::get('campaignID');

		$campaign =Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);
		$data['campaign'] = $campaign;

		$data['leadFormId'] = $campaign->formID;
		if($campaign->landingFormID == null || $campaign->landingFormID == ''){
			$data['error'] = true;
			$data['landingForms'] = $this->user->getAvailableLandingForms();
		}
		else{
			$data['error'] = false;
			$landingForm = Landingform::find($campaign->landingFormID);
			$data['landingForm'] = $landingForm;
			$data['landingFormID'] = $campaign->landingFormID;
		}

		return View::make('site/campaigns/campaignInformation', $data);
	}

	public function setMailSettings(SimpleRequest $request)
	{
		$campaignID = $request->input('campaignID');
		$campaign =Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);

		$allData = $request->all();
		
		$data = [];
		try {
			$data['status'] = "success";
			$this->saveCampaignMailSettings($campaignID, $allData);
		} catch(Exception $e) {
			$data['status'] = "error";
			$data['message'] = $e->getMessage();
		}
		return $data;
	}


	public function getMailSettingsDialog(SimpleRequest $request)
	{
		$campaignID = $request->input('campaignID');

		$campaign =Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);
		$data['campaign'] = $campaign;
		$campaignMailSettings = $campaign->getCampaignEmailSettings();
		if($campaignMailSettings) {
			$data['mailSettings'] = $campaignMailSettings;
		}

		return View::make('site/campaigns/campaignMailSettingsDialog', $data);
	}
	
	public function getPrevNextLeadFilterDialog(SimpleRequest $request)
	{
		/*
		if($this->user->userType != "Multi") {
			abort(403, 'Only Team Admin is allowed to edit filters');
		}
		 */
		$campaignID = $request->input('campaignID');

		$campaign =Campaign::findOrFail($campaignID);
		$this->authorize('can-see-campaign', $campaign);

		if($campaign->type != 'Normal') {
			abort(403, 'Only non-fly campaigns are supported');
		}
		
		$massEmailTemplates = MassEmailTemplate::where('campaign_id', '=', $campaignID)
			->where('type', '=', 'campaign')
			->where(function($query) {
				$query->where('status', '=', 'sent')
				->orWhere(function($query) {
					$query->where('status', '=', 'Scheduled')
						->where('email_sent', '>', 0);
				});
			})
			->orderBy('id', 'DESC')
			->orderBy('name', 'ASC')
			->get()
			;

		$formFields = FormFields::getDropDownsForCampaign($campaignID);

		$filterData = [];

		$filter = $campaign->prevNextFilter;
		if(count($filter) > 0) {
			$filterData = $filter;
		}

		$managerID = $this->user->getManagerID();
		$perUserFilters = User::whereIn('users.id', $this->user->getTeamMembersWithManagerIDs())
			->select('users.id as user_id', 'users.firstName', 'users.lastName', 'campaign_prevnext_filters.prevNextFilter')
			->where(function($query) use ($campaignID, $managerID) {
				$query->where('users.id', '=', $managerID)
					->orWhereRaw('users.id = campaignmembers.userID');
			})
			->leftJoin('campaignmembers', function($join) use($campaignID) {
				$join->on('campaignmembers.userID', '=', 'users.id')
					->where('campaignmembers.campaignID', '=', $campaignID);
			})
			->leftJoin('campaign_prevnext_filters', function($join) use($campaignID) {
				$join->on('campaign_prevnext_filters.user_id', '=', 'users.id')
					->where('campaign_prevnext_filters.campaign_id', '=', $campaignID);
			})
			->orderBy('users.lastName')
			->orderBy('users.firstName')
			->get()
			;

		//Log::error(print_r($perUserFilters, true));
		//Log::error(json_encode($filterData));

		$data = [
			'formFields' => $formFields, 
			'filter' => $filterData,
			'perUserFilters' => $perUserFilters,
			'campaign' => $campaign,
			'renderDlgContent' => true,
			'massEmailTemplates' => $massEmailTemplates
		];

		return View::make('site/campaigns/dlgPrevNextFilter', $data);
	}

	public function setPrevNextLeadFilterDialog(SimpleRequest $request)
	{
		/*
		if($this->user->userType != "Multi") {
			return ["status"  => "fail", "message" => "Only Team Admin is allowed to edit filters"];
		}
		 */
		$campaignID = $request->input('campaignID');

		$campaign = Campaign::find($campaignID);

		if(!$campaign) {
			return ["status"  => "fail", "message" => "Campaign not found"];
		}

		$this->authorize('can-see-campaign', $campaign);

		if($campaign->type != 'Normal') {
			return ["status"  => "fail", "message" => "Only non-fly campaigns are supported"];
		}
		
		if ($request->has('filters')) {
			$perUserFilters = $request->input('filters');
			//Log::error('filters', $perUserFilters);
			//Log::error('filters : ' . print_r($perUserFilters, true));
			$allowedUserIds = $campaign->getMembersIDs();
			$allowedUserIds[] = $this->user->id;

			DB::beginTransaction();
			CampaignPrevNextFilter::where('campaign_id', '=', $campaignID)
				->delete();

			foreach($perUserFilters as $userID => $perUserFilter) {
				$filter = [];
				foreach($perUserFilter as $key => $value) {
					if($value != '') {
						$filter[$key] = $value;
					}
				}
				//Log::error('filter', $filter);
				if($userID == 'all') {
					$campaign->prevNextFilter = $filter;
					$campaign->save();
				} else {
					if(in_array($userID,$allowedUserIds) && count($filter)) {
						$userFilter = CampaignPrevNextFilter::firstOrNew(['campaign_id' => $campaignID, 'user_id' => $userID]);
						$userFilter->prevNextFilter = $filter;
						$userFilter->save();
					}	
				}
			}

			$campaign->recalculateCallsRemaining();
			$campaign->recalculateTotalFilteredLeads();
			DB::commit();

			$isUserFilter = null;
			$isFilter = null;
			$totalCount = $campaign->getTotalLeadsForUser($this->user->id, $isFilter, $isUserFilter);
			return [
				"status"  => "success",
				'totalLeadsString' => $totalCount . ($isFilter ? ($isUserFilter ? ' (filtered for user)' : ' (filtered for all)') : '') 
			];
		}
		return ["status"  => "success"];
		
	}

	public function landingFormHtmlApi($campaignSlug, $landingSlug) 
	{
		$result = $this->landingFormProcessing($campaignSlug, $landingSlug, 'site/campaigns/landingFormHtml');
		if(Request::isMethod('post') && Input::has('redirectUrl')) {
			return redirect(Input::get('redirectUrl'));
		} else {
			return $result;
		}
	}

	public function landingSignUp($campaignSlug, $landingSlug) 
	{
		return $this->landingFormProcessing($campaignSlug, $landingSlug, 'site/campaigns/landingForm');
	}

	private function landingFormProcessing($campaignSlug, $landingSlug, $formTemplate)
	{
		if($campaignSlug == '' || $campaignSlug == null || $landingSlug == '' || $landingSlug == null) {
			return App::abort(404, 'Requested page not found');
		}

		$campaign = Campaign::findBySlug($campaignSlug);
		if(!$campaign) {
			return App::abort(404, 'Requested page not found');
		}

		$landingForm = Landingform::findBySlug($landingSlug);
		if(!$landingForm) {
			return App::abort(404, 'Requested page not found');
		}

		if($campaign->landingFormID != $landingForm->id) {
			return App::abort(404, 'Requested page not found');
		}

		$landingFormFields = $landingForm->getFormFields();
		
		if(Request::isMethod('get')) {
			$formData = [];

			foreach($landingFormFields as $field) {

				$type = $field->type;
				$fieldName = $field->fieldName;

				$fieldData = [
					'fieldName' => $fieldName, 
					'type' => $type,
					'required' => $field->isRequired, 
					'name' => 'fld:' . $fieldName,
				];

				if(strtolower($fieldName) == 'address' || strtolower($fieldName) == 'notes') {
					$fieldData['type'] = 'textarea';
				}
				elseif($type == 'dropdown') {
					$fieldData['values'] = explode(',', $field->values);
				}
				$formData[] = $fieldData;
			}
			$data['formData'] = $formData;
		}

		$data['finish'] = false;

		if(Request::isMethod('post')) {
			//TODO: add this form into campaign lead

			DB::beginTransaction();

			$lead = new Lead();
			$lead->campaignID = $campaign->id;
			$lead->timeCreated = Carbon::now()->format('Y-m-d H:i:s');
			$lead->timeEdited = Carbon::now()->format('Y-m-d H:i:s');
			$lead->leadType = 'landing';
			$lead->save();

			$campaign->totalLeads = $campaign->totalLeads + 1;
			$campaign->save();

			$mapping = Formmapping::where('formID', $campaign->formID)
				->where('landingFormID', $campaign->landingFormID)
				->orderBy('formFieldID')
				->get();

			$leadForm = Form::find($campaign->formID);
			$leadFormFields = $leadForm->getFormFields();

			$visitedLeadFormIDs = [];
			$visitedLandingFormIDs = [];

			//follows laravel's rules
			$fnEncodeFieldName = function($fieldName) {
				return 'fld:' . str_replace(' ', '_', $fieldName);
			};

			foreach($mapping as $map) {
				$visitedLandingFormIDs[] = $map->landingFieldID;

				$leadFormField = $leadFormFields->where('id', $map->formFieldID)->first();

				$fieldName = $leadFormField->fieldName;
				$type = $leadFormField->type;

				$landingFormField = $landingFormFields->where('id', $map->landingFieldID)->first();

				$value = Input::get($fnEncodeFieldName($landingFormField->fieldName));

				if(in_array($fieldName, CommonController::getFieldVariations('Notes'))) {
					$value = $fieldName . ' - ' . $value . "\n";
				}

				if($type == 'dropdown') {
					$values = explode(',' , $leadFormField->values);

					if(!in_array($value, $values)){
						$value = '';
					}
				} elseif($type == 'date') {
					$value = FormFields::validateDateFieldValue($value);
				}

				if(in_array($map->formFieldID, $visitedLeadFormIDs)) {
					//find that row and update value
					$row = LeadCustomData::where('leadID', $lead->id)
						->where('fieldID', $map->formFieldID)
						->first();

					if($row) {
						$oldValue = $row->value;
						if(in_array($fieldName, CommonController::getFieldVariations('Notes'))) {
							$newValue = $value . $oldValue . "\n";
						}
						else {
							$newValue = $value .' ' . $oldValue;
						}

						$row->value = $newValue;
						$row->save();
					}
					else {
						$leadCustomData = new LeadCustomData();
						$leadCustomData->leadID = $lead->id;
						$leadCustomData->fieldID = $map->formFieldID;
						$leadCustomData->fieldName = $fieldName;
						$leadCustomData->value = $value;
						$leadCustomData->save();
					}
				}
				else {
					$visitedLeadFormIDs[] = $map->formFieldID;

					$leadCustomData = new LeadCustomData();
					$leadCustomData->leadID = $lead->id;
					$leadCustomData->fieldID = $map->formFieldID;
					$leadCustomData->fieldName = $fieldName;
					$leadCustomData->value = $value;
					$leadCustomData->save();
				}
			}

			$notesID = '';

			//for unmapped lead form fields
			foreach($leadFormFields as $field) {
				if(in_array($field->fieldName, CommonController::getFieldVariations('Notes'))) {
					$notesID = $field->id;
				}

				if(!in_array($field->id, $visitedLeadFormIDs)) {
					$leadCustomData = new LeadCustomData();
					$leadCustomData->leadID = $lead->id;
					$leadCustomData->fieldID = $field->id;
					$leadCustomData->fieldName = $field->fieldName;
					$leadCustomData->value = '';
					$leadCustomData->save();
				}
			}

			// For unmapped landing form fields
			foreach($landingFormFields as $landingField) {
				if(!in_array($landingField->id, $visitedLandingFormIDs)) {
					//copied it to notes
					$row = LeadCustomData::where('leadID', $lead->id)
						->where('fieldID', $notesID)
						->first();

					$oldValue = $row->value;
					$newValue = $landingField->fieldName . ' - ' . Input::get($fnEncodeFieldName($landingField->fieldName)) . "\n" . $oldValue . "\n";
					$row->value = $newValue;
					$row->save();
				}
			}

			$campaign->recalculateTotalFilteredLeads();

			DB::commit();

			set_time_limit(0);

			//sent a mail to campaign members
			$campaignMembers = $campaign->getMembers('all');
			$emailTemplate = AdminEmailTemplate::where('id', 'NEWLEAD')->first();

			$mailSettings = CommonController::loadSMTPSettings("inbound");

			$generalEmailInfo = [
				'from' => $mailSettings["from"]["address"],
				'fromName' => $mailSettings["from"]["name"],
				'replyTo' => $mailSettings["replyTo"]
			];

			$fieldValues = [
				'CAMPAIGN_NAME' => $campaign->name,
				'SITE_NAME' => Setting::get('siteName')
			];

			$emailText = $emailTemplate->content;
			$attachments = [];

			$emailInfo = $generalEmailInfo;

			foreach($campaignMembers as $member) {
				$emailInfo['subject'] = $emailTemplate->subject;
				$emailInfo['to'] = $member->email;

				CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
			}

			//thank you message
			if($landingForm->thankYouMessage != '' && $landingForm->thankYouMessage != null) {
				$data['message'] = $landingForm->thankYouMessage;
			}
			else {
				$data['message'] = "Thank you for submitting request. We will get back to you shortly";
			}
			$data['finish'] = true;
		}

		$data['landingForm'] = $landingForm;
		$data['campaign'] = $campaign;

		return View::make($formTemplate, $data);
	}

	public function getAddNewLeadDialog()
	{
		$campaignLists = $this->user->getStartedCampaigns();

        $data = [
			 'campaignLists'     => $campaignLists,
        ];

		return View::make('site/campaigns/dlgAddNewLead', $data);
	}
}
