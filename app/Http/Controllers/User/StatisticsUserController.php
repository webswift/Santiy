<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\FormFields;
use App\Models\Lead;
use App\Models\User;
use App\Models\LeadInfoEmails;
use Carbon\Carbon;
use DateTime;
use DB;
use Input;
use Request;
use URL;
use View;

class StatisticsUserController extends Controller
{

	public function index() {
		//Array to send data to view
		$data = ['statisticsMenuActive' => 'active'];

		$date = new DateTime;
		$date->modify("-1 days");
		$yesterdayDate = $date->format('Y-m-d H:i:s');

		$userCampaigns = $this->user->getStartedCampaigns();

		$finalUserCampaigns = [];

		foreach ($userCampaigns as $userCampaign) {
//			if (strtotime($userCampaign->timeStarted) <= strtotime($yesterdayDate)) {

				//get dropdown form fields of campaign
				$dropdownFields = FormFields::where('formID', $userCampaign->formID)
					->where('type', 'dropdown')
					->orderBy('type', 'DESC')
					->get();

				$userCampaign['formFields'] = $dropdownFields;

				$finalUserCampaigns[] = $userCampaign;
//			}
		}
		
		//get team members
		$teamMembers = $this->user->getAllTeamMembers();
		
		$data = [
			"campaignLists"	=> $finalUserCampaigns,
			'teamMembers'   => $teamMembers,
		];

		return View::make('site/statistics/campaignstatistics', $data);
	}

	private function getRangeData($campaignID, $dateRange, $customRangeFrom, $customRangeTo) {
		if($dateRange < 10000) {
			$endDate = new DateTime(/*$campaignDetails->timeStarted*/);
			$endDate->modify("+1 days"); //start date is end of range + 1 day

		} else if ($dateRange == 10000) {
			//use activity period of the campaign
			$startEndDates = Lead::select(DB::raw('DATE(MAX(timeEdited)) as endDate, DATE(MIN(timeEdited)) as startDate'))
				->where('campaignID', $campaignID)
				->first();
			
			try {
				$endDate = new DateTime($startEndDates->endDate);
				$endDate->modify("+1 days"); //start date is end of range + 1 day
				
				$startRangeDate = new DateTime($startEndDates->startDate);
				$dateRange = $startRangeDate->diff($endDate)->days;
				if($dateRange == 0) {
					throw new Exception('Wrong dateRange');
				}
			} catch (Exception $e){
				//if dates wrong, return last week
				$endDate = new DateTime;
				$endDate->modify("+1 days"); //start date is end of range + 1 day
				$dateRange = 7;
			}

		} else if ($dateRange == 10001) {
			try {
				$endDate = new DateTime($customRangeTo);
				$endDate->modify("+1 days"); //start date is end of range + 1 day
				
				$startRangeDate = new DateTime($customRangeFrom);

				$dateRange = $startRangeDate->diff($endDate)->days;
				if($dateRange == 0) {
					throw new Exception('Wrong dateRange');
				}
			} catch (Exception $e){
				//if dates wrong, return last week
				$endDate = new DateTime;
				$endDate->modify("+1 days"); //start date is end of range + 1 day
				$dateRange = 7;
			}
		}

		//limit maximal data to 62 days
		//TODO: lift after optimization of queries
		if($dateRange > 62) {
			$dateRange = 62;
		}
		
		$endDateStr = $endDate->format('Y-m-d 00:00:00');

		return ['range' => $dateRange, 'endDate' => $endDateStr];
	}

	public function showCallVolume() {
		if (Request::ajax()) {
			$callVolumeCountFound = false;
			$interestedOrNotInterestedFound = false;
			
			$userID = Input::get('userID');
			if($userID != 'All' && !is_numeric($userID)) {
				$userID = 'All';
			}

			if (Input::has('type') && Input::get('type') == 'share') {
				$token = Input::get('token');
				$tokenDetails = $this->getTokenDetails($token);
				$campaignID = $tokenDetails->campaignID;
				$dateRange = $tokenDetails->dateRange;
				$customRangeFrom = $tokenDetails->customRangeFrom;
				$customRangeTo = $tokenDetails->customRangeTo;
			}
			else {
				$campaignID = Input::get('campaignID');
				$dateRange = Input::get('dateRange');
				$customRangeFrom = Input::get('customRangeFrom');
				$customRangeTo = Input::get('customRangeTo');
				$campaign = Campaign::findOrFail($campaignID);
				$this->authorize('can-see-campaign', $campaign);
			}

			$callVolumeArray = [];
			$interestedCalls = [];
			$notInterestedCalls = [];
			$unreachableCalls = [];
			$infoEmailsSentByDate = [];
			$interestedCallsSum = 0;
			$notInterestedCallsSum = 0;
			$unreachableCallsSum = 0;

			$totalCallVolume = 0;

			$date = new DateTime();
			$today = $date->format('Y-m-d 00:00:00');

			$rangeData = $this->getRangeData($campaignID, $dateRange, $customRangeFrom, $customRangeTo);
			$startedDate = $rangeData['endDate'];
			$dateRange = $rangeData['range'];

			$startPeriodDate = new DateTime($startedDate);
			$startPeriodDate->modify("-" . ($dateRange) . " days");

			//info emails sent
			$infoEmailsSent = LeadInfoEmails::select(DB::raw('DATE(created_at) date, COUNT(*) as emails_sent'))
				->where('campaign_id', $campaignID)
				->whereBetween('created_at', [$startPeriodDate, new DateTime($startedDate)])
				->where(function($query) use ($userID) {
					if($userID != 'All') {
						$query->where('user_id', '=', $userID);
					} 
				})
				->groupBy('date')
				->orderBy('date', 'ASC')
				->get()
				;
			
			$totalInfoEmailsSent = $infoEmailsSent->sum('emails_sent');
			$infoEmailsSent = $infoEmailsSent->keyBy('date');

			//actioned leads
			$actionedLeads = Lead::
				select(DB::raw('DATE(timeEdited) date, interested, COUNT(*) as leads_actioned'))
				->where('campaignID', $campaignID)
				->where('status', 'Actioned')
				->whereBetween('timeEdited', [$startPeriodDate, new DateTime($startedDate)])
				->where(function($query) use ($userID) {
					if($userID != 'All') {
						$query->where('lastActioner', '=', $userID);
					} 
				})
				->groupBy(['date', 'interested'])
				->orderBy('date', 'ASC')
				->get()
				;
			
			$totalCallVolume = $actionedLeads->sum('leads_actioned');
			$actionedLeads = $actionedLeads->groupBy('date');

			for ($i = 0; $i < $dateRange; $i++) {
				//For calculating Call Volume
				$date = new DateTime($startedDate);
				$date->modify("-" . ($dateRange - $i) . " days");
				$newDate = $date->format('Y-m-d');

				$day = $i + 1;

				$infoEmailsSentAtDate = $infoEmailsSent->get($newDate);
				if($infoEmailsSentAtDate) {
					$infoEmailsSentAtDate = $infoEmailsSentAtDate['emails_sent'];
				} else {
					$infoEmailsSentAtDate = 0;
				}

				$callVolumeCount = 0;
				$interestedCall = 0;
				$notInterestedCall = 0;
				$unreachableCall = 0;

				$actionedLeadsAtDate = $actionedLeads->get($newDate);
				if($actionedLeadsAtDate) {
					$callVolumeCount = $actionedLeadsAtDate->sum('leads_actioned');

					$actionedLeadsAtDate = $actionedLeadsAtDate->keyBy('interested');

					$interestedCall = $actionedLeadsAtDate->has('Interested') ? $actionedLeadsAtDate->get('Interested')->leads_actioned : 0;
					$notInterestedCall = $actionedLeadsAtDate->has('NotInterested') ? $actionedLeadsAtDate->get('NotInterested')->leads_actioned : 0;
					$unreachableCall = $actionedLeadsAtDate->has('Unreachable') ? $actionedLeadsAtDate->get('Unreachable')->leads_actioned : 0;
				}

				//$callVolumeArray[] = ["Day {$day}", $callVolumeCount];
				$formatDate = Carbon::parse($newDate)->format('d M');
				$callVolumeArray[] = [$formatDate, $callVolumeCount];
				$infoEmailsSentByDate[] = [$formatDate, $infoEmailsSentAtDate];

				$interestedCalls[] = [$day, $interestedCall];
				$notInterestedCalls[] = [$day, $notInterestedCall];
				$unreachableCalls [] = [$day, $unreachableCall];

				$interestedCallsSum += $interestedCall;
				$notInterestedCallsSum += $notInterestedCall;
				$unreachableCallsSum += $unreachableCall;

				if ($i >= 9) {
					if ($newDate >= $today) {
						break;
					}
				}

				if ($callVolumeCount > 0) {
					$callVolumeCountFound = true;
				}

				if ($interestedCall > 0 
					|| $notInterestedCall > 0
					|| $unreachableCall > 0
				) {
					$interestedOrNotInterestedFound = true;
				}
			}


			$data['success'] = 'success';
			$data['callVolumeCountFound'] = $callVolumeCountFound;
			$data['interestedOrNotInterestedFound'] = $interestedOrNotInterestedFound;
			$data['callVolumeArray'] = $callVolumeArray;
			$data['interestedCalls'] = $interestedCalls;
			$data['interestedCallsSum'] = $interestedCallsSum;
			$data['notInterestedCalls'] = $notInterestedCalls;
			$data['notInterestedCallsSum'] = $notInterestedCallsSum;
			$data['unreachableCalls'] = $unreachableCalls;
			$data['unreachableCallsSum'] = $unreachableCallsSum;
			$data['totalCallVolume'] = $totalCallVolume;
			$data['totalInfoEmailsSent'] = $totalInfoEmailsSent;
			$data['infoEmailsSentByDate'] = $infoEmailsSentByDate;
			
			$data['colors'] = CommonController::randColorArray();
		}

		return json_encode($data);
	}

	function getTokenDetails($token) {
		return DB::table('statistics_share_info')
			->where('token', $token)
			->first();
	}

	public function showCustomCallVolume() {
		if (Request::ajax()) {

			$userID = Input::get('userID');
			if($userID != 'All' && !is_numeric($userID)) {
				$userID = 'All';
			}

			if (Input::has('type') && Input::get('type') == 'share') {
				$token = Input::get('token');
				$tokenDetails = $this->getTokenDetails($token);
				$campaignID = $tokenDetails->campaignID;
				$dateRange = $tokenDetails->dateRange;
				$customRangeFrom = $tokenDetails->customRangeFrom;
				$customRangeTo = $tokenDetails->customRangeTo;
			}
			else {
				$campaignID = Input::get('campaignID');
				$dateRange = Input::get('dateRange');
				$customRangeFrom = Input::get('customRangeFrom');
				$customRangeTo = Input::get('customRangeTo');
			}
			$teamInterest = Input::get('teamInterest');

			$chartData = [];
			$dataFound = false;

			$campaignDetails = Campaign::find($campaignID);
			
			$rangeData = $this->getRangeData($campaignID, $dateRange, $customRangeFrom, $customRangeTo);
			$startedDate = $rangeData['endDate'];
			$dateRange = $rangeData['range'];

			$startPeriodDate = new DateTime($startedDate);
			$startPeriodDate->modify("-" . ($dateRange) . " days");

			$date = new DateTime();
			$today = $date->format('Y-m-d 00:00:00');

			$displayValues = [];
			$colorsArray = CommonController::randColorArray();
			$interested = [];
			$notInterested = [];
			$unreachable = [];

			$prepareValues = [];

			$valuesSum = [];

			if ($teamInterest == 'interest') {
				//actioned leads
				$actionedLeads = Lead::
					select(DB::raw('DATE(timeEdited) date, interested, COUNT(*) as leads_actioned'))
					->where('campaignID', $campaignID)
					->where('status', 'Actioned')
					->whereBetween('timeEdited', [$startPeriodDate, new DateTime($startedDate)])
					->where(function($query) use ($userID) {
						if($userID != 'All') {
							$query->where('lastActioner', '=', $userID);
						} 
					})
					->groupBy(['date', 'interested'])
					->orderBy('date', 'ASC')
					->get()
					;
				
				$actionedLeads = $actionedLeads->groupBy('date');
			} else {
					// get dropdown values
					$field = FormFields::where('formID', $campaignDetails->formID)
						->where('fieldName', $teamInterest)
						->first();
					$values = explode(',', $field->values);
					$displayValues = $values;
					if (sizeof($values) > 0) {
						$actionedLeads = Lead::
							select(DB::raw('DATE(timeEdited) date, leadcustomdata.value, COUNT(*) as leads_actioned'))
							->where('leads.campaignID', $campaignID)
							->where('leads.status', 'Actioned')
							->whereBetween('timeEdited', [$startPeriodDate, new DateTime($startedDate)])
							->where(function($query) use ($userID) {
								if($userID != 'All') {
									$query->where('lastActioner', '=', $userID);
								} 
							})
							->join('leadcustomdata', 'leadcustomdata.leadID', '=', 'leads.id')
							->where('leadcustomdata.fieldName', $teamInterest)
							->groupBy(['date', 'leadcustomdata.value'])
							->orderBy('date', 'ASC')
							->get()
							;
						$actionedLeads = $actionedLeads->groupBy('date');
					}

			}

			for ($i = 0; $i < $dateRange; $i++) {
				//For calculating Call Volume
				$date = new DateTime($startedDate);
				$date->modify("-" . ($dateRange - $i) . " days");
				$newDate = $date->format('Y-m-d');

				$day = $i + 1;

				if ($teamInterest == 'interest') {

					$interestedCall = 0;
					$notInterestedCall = 0;
					$unreachableCall = 0;

					$actionedLeadsAtDate = $actionedLeads->get($newDate);
					if($actionedLeadsAtDate) {
						$actionedLeadsAtDate = $actionedLeadsAtDate->keyBy('interested');

						$interestedCall = $actionedLeadsAtDate->has('Interested') ? $actionedLeadsAtDate->get('Interested')->leads_actioned : 0;
						$notInterestedCall = $actionedLeadsAtDate->has('NotInterested') ? $actionedLeadsAtDate->get('NotInterested')->leads_actioned : 0;
						$unreachableCall = $actionedLeadsAtDate->has('Unreachable') ? $actionedLeadsAtDate->get('Unreachable')->leads_actioned : 0;
					}


					$interested["data"][] = [$day, $interestedCall];
					$interested["color"] = $colorsArray[0];
					$notInterested["data"][] = [$day, $notInterestedCall];
					$notInterested["color"] = $colorsArray[1];
					$unreachable["data"][] = [$day, $unreachableCall];
					$unreachable["color"] = $colorsArray[8];

					//toDo:display values
					$displayValues = ['Interested', 'Not Interested', 'Unreachable'];

					if ($interestedCall > 0 
						|| $notInterestedCall > 0
						|| $unreachableCall > 0
					) {
						$dataFound = true;
					}

					if(!isset($valuesSum['Interested'])) {
						$valuesSum['Interested'] = 0;
					}
					$valuesSum['Interested'] += $interestedCall;

					if(!isset($valuesSum['Not Interested'])) {
						$valuesSum['Not Interested'] = 0;
					}
					$valuesSum['Not Interested'] += $notInterestedCall;

					if(!isset($valuesSum['Unreachable'])) {
						$valuesSum['Unreachable'] = 0;
					}
					$valuesSum['Unreachable'] += $unreachableCall;
				}
				else {
					
					if (sizeof($values) > 0) {
						$colorCount = 0;

						$actionedLeadsAtDate = $actionedLeads->get($newDate);
						if($actionedLeadsAtDate) {
							$actionedLeadsAtDate = $actionedLeadsAtDate->keyBy('value');
						}
						
						foreach ($values as $value) {
							$count = 0;
							if($actionedLeadsAtDate) {
								$count = $actionedLeadsAtDate->has($value) ? $actionedLeadsAtDate->get($value)->leads_actioned : 0;
							}

							if ($count > 0) {
								$dataFound = true;
							}

							if ($colorCount >= count($colorsArray)) {
								$color = CommonController::randColor();
							}
							else {
								$color = $colorsArray[ $colorCount ];
							}

							$prepareValues[ $value ]["data"][] = [$day, $count];
							$prepareValues[ $value ]["color"] = $color;

							$colorCount++;
							
							if(!isset($valuesSum[$value])) {
								$valuesSum[$value] = 0;
							}
							$valuesSum[$value] += $count;
						}
					}
				}

				if ($i >= 9) {
					if ($newDate >= $today) {
						break;
					}
				}
			}

			if ($teamInterest == 'interest') {
				$chartData = [
					$displayValues[0] . " ({$valuesSum[$displayValues[0]]})" => $interested
					, $displayValues[1] . " ({$valuesSum[$displayValues[1]]})" => $notInterested
					, $displayValues[2] . " ({$valuesSum[$displayValues[2]]})" => $unreachable
				];
			}
			else {
				foreach ($displayValues as $displayValue) {
					$chartData[ $displayValue . " ({$valuesSum[$displayValue]})" ] = $prepareValues[ $displayValue ];
				}
			}

			$data['success'] = 'success';
			$data['dataFound'] = $dataFound;
			$data['chartData'] = $chartData;

			return $data;
		}
	}

	public function showTeamPerformance() {
		if (Request::ajax()) {

			$userID = Input::get('userID');
			if($userID != 'All' && !is_numeric($userID)) {
				$userID = 'All';
			}

			if (Input::has('type') && Input::get('type') == 'share') {
				$token = Input::get('token');
				$tokenDetails = $this->getTokenDetails($token);
				$campaignID = $tokenDetails->campaignID;
				$dateRange = $tokenDetails->dateRange;
				$customRangeFrom = $tokenDetails->customRangeFrom;
				$customRangeTo = $tokenDetails->customRangeTo;
			}
			else {
				$campaignID = Input::get('campaignID');
				$dateRange = Input::get('dateRange');
				$customRangeFrom = Input::get('customRangeFrom');
				$customRangeTo = Input::get('customRangeTo');
			}

			if ($dateRange == "") {
				$dateRange = 10;
			}

			$actionType = Input::get('actionType');
			$pieData = [];
			$colorsArray = CommonController::randColorArray();
			$dataFound = false;

			if($userID == 'All') {
				if (isset($tokenDetails)) {
					$user = User::find($tokenDetails->userID);
					$teamMembers = $user->getAllTeamMembers();
				}
				else {
					$teamMembers = $this->user->getAllTeamMembers();
				}
			} else {
				$teamMembers = User::
					where('id', '=', $userID)
					->get();
			}

			//$campaignDetails = Campaign::find($campaignID);
			
			$rangeData = $this->getRangeData($campaignID, $dateRange, $customRangeFrom, $customRangeTo);
			$startedDate = $rangeData['endDate'];
			$dateRange = $rangeData['range'];
			
			$startDate = new DateTime($startedDate);
			$newDate = $startDate->modify("-" . $dateRange . " days");
			$maxDate = $newDate->format("Y-m-d 00:00:00");

			$actionedLeads = null;
			if ($actionType == 'calls') {
				$actionedLeads = Lead::where('timeEdited', '<', $startedDate)
					->where("campaignID", $campaignID)
					->where('timeEdited', ">=", $maxDate)
					->where('status', 'Actioned')
					->select(DB::raw('lastActioner, COUNT(*) as leads_actioned'))
					->where(function($query) use ($userID) {
						if($userID != 'All') {
							$query->where('lastActioner', '=', $userID);
						} 
					})
					->groupBy('lastActioner')
					->get();
			}
			else if ($actionType == 'appointments') {
				$actionedLeads = Lead::where('timeEdited', '<', $startedDate)
					->where("campaignID", $campaignID)
					->where('timeEdited', ">=", $maxDate)
					->where('status', 'Actioned')
					->where('bookAppointment', 'Yes')
					->select(DB::raw('lastActioner, COUNT(*) as leads_actioned'))
					->where(function($query) use ($userID) {
						if($userID != 'All') {
							$query->where('lastActioner', '=', $userID);
						} 
					})
					->groupBy('lastActioner')
					->get();
			}
			else {
				$actionedLeads = Lead::where('timeEdited', '<', $startedDate)
					->where("campaignID", $campaignID)
					->where('timeEdited', ">=", $maxDate)
					->where('status', 'Actioned')
					->where('interested', 'Interested')
					->select(DB::raw('lastActioner, COUNT(*) as leads_actioned'))
					->where(function($query) use ($userID) {
						if($userID != 'All') {
							$query->where('lastActioner', '=', $userID);
						} 
					})
					->groupBy('lastActioner')
					->get();
			}
			$actionedLeads = $actionedLeads->keyBy('lastActioner');

			$data = [];

			foreach ($teamMembers as $teamMember) {
				$name = $teamMember->firstName;

				$queryData = 0;
				$actionedLeadsForActioner = $actionedLeads->get($teamMember->id);
				if($actionedLeadsForActioner) {
					$queryData = $actionedLeadsForActioner->leads_actioned;
				}

				if (count($pieData) > count($colorsArray)) {
					$color = CommonController::randColor();
				}
				else {
					$color = $colorsArray[ count($pieData) ];
				}

				if ($queryData > 0) {
					$dataFound = true;
				}

				if ($queryData > 0) {
					$pieData[] = [
						'label' => $name . " : {$queryData}", 
						'data' => [[1, $queryData]], 
						'color' => $color,
						'user_id' => $teamMember->id,
					];
				}

			}
			
			$data['success'] = "success";
			$data['pieData'] = $pieData;
			$data['dataFound'] = $dataFound;

			return json_encode($data);
		}
		else {
			return json_encode(["status" => "fail", "message" => "This method cannot be called directly"]);
		}

	}

	function getShareLink() {
		if (Request::ajax()) {
			$campaignID = Input::get('campaign');
			$dateRange = Input::get('dateRange');
			$userID = $this->user->id;

			//create unique token
			$token = md5(rand(0, 12121212112));

			try {
				$customRangeFrom = new DateTime(Input::get('customRangeFrom'));
				$customRangeTo = new DateTime(Input::get('customRangeTo'));
			} catch (Exception $e){
				//if dates wrong, return last week
				$customRangeTo = new DateTime;
				$customRangeFrom = new DateTime;
				$customRangeFrom->modify("-6 days"); 
			}

			//insert information to table
			DB::table('statistics_share_info')
				->insert([
					'campaignID' => $campaignID
					, 'userID' => $userID
					, 'dateRange' => $dateRange
					, 'token' => $token
					, 'customRangeFrom' => $customRangeFrom
					, 'customRangeTo' => $customRangeTo
				]);

			$link = URL::route('statistics.share', [$token]);

			return ['status' => 'success', 'link' => $link];
		}
	}

	function share($token) {
		if ($token == null || $token == '') {
			App::abort('404');
		}
		else {
			//get token details
			$tokenDetails = DB::table('statistics_share_info')
				->where('token', $token)
				->first();
			$campaignID = $tokenDetails->campaignID;
			$userID = $tokenDetails->userID;

			$campaign = Campaign::find($campaignID);
			$user = User::find($userID);

			//get dropdown form fields of campaign
			$dropdownFields = FormFields::where('formID', $campaign->formID)
				->where('type', 'dropdown')
				->orderBy('type', 'DESC')
				->get();

			$campaign['formFields'] = $dropdownFields;
			$data['user'] = $user;

			$data['campaign'] = $campaign;
			$data['token'] = $token;

			//get team members
			$teamMembers = $user->getAllTeamMembers();
			$data['teamMembers'] = $teamMembers;
			
			return View::make('site/statistics/shareStatistics', $data);
		}
	}
}
