<?php

namespace App\Models;

use App\Http\Controllers\CommonController;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Database\Eloquent\Model;
use TijsVerkoyen\CssToInlineStyles\Exception;
use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Log;

/**
 * @property mixed autoReferencePrefix
 * @property mixed callsRemaining
 * @property mixed autoReferences
 */
class Campaign extends Model implements SluggableInterface{

	use SluggableTrait;

	protected $sluggable = [
		'build_from' => 'name',
		'save_to'    => 'slug',
		'separator' => '-',
		'method' => null,
		'max_length' => null,
		'reserved' => null,
		'unique' => true,
		'use_cache' => false,
		'include_trashed' => false
	];

	public $timestamps = false;
	protected $table = 'campaigns';
	protected $fillable = ['id', 'status', 'timeStarted', 'totalLeads', 'callsRemaining', 'completedOn', 'type'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'prevNextFilter' => 'array',
    ];

	public function leads() {
		return $this->hasMany('App\Models\Lead', 'campaignID', 'id');
	}

	//TODO: seems incorrect and unused
	public function customData()
	{
		return $this->hasMany('App\Models\LeadCustomData', 'leadID', 'id');
	}

	function start() {
		$this->status       = "Started";
		$this->timeStarted    = new DateTime();
		$this->save();
		return $this;
	}

	function getUnactionedLeadsCount() {
		return Lead::select(['id'])
			->where('campaignID', $this->id)
			->where("status", "Unactioned")
			->count();
	}

	/** Get lead that is shown to user when user open a campaign from call manager
	 * @param $user
	 * @return Lead
	 * @throws Exception
	 */
	function getLandingLead($user) {
		$userID = $user->id;

		if ($this->status != "Started") {
			if(\Session::has('referFrom') && \Session::get('referFrom') == 'massmail') {
				$this->start();

				\Session::forget('referFrom');
			}
			else {
				throw new Exception("You need to start the campaign first before generating leads for it");
			}
		}

		$lead = Lead::where("leads.status", "Unactioned")
			->where('campaignID', $this->id)
			->whereRaw('(timeOpened IS NULL OR timeOpened < (NOW() - INTERVAL 20 SECOND))')
			->orderBy("leads.id", "ASC")
			->select('leads.*')
			;

		$this->applyPrevNextFilter($lead, $this->getPrevNextFilterForUser($userID));
		$newLead = $lead = $lead->first();

		if (!$newLead) {
			//search for any 
			$newLead = Lead::where('campaignID', $this->id)
				->orderBy("leads.id", "ASC")
				->select('leads.*')
				;

			$this->applyPrevNextFilter($newLead, $this->getPrevNextFilterForUser($userID));
			$newLead = $newLead->first();
			if ($newLead) {
				return $newLead;
			}
		}

		//
		//if ($minUnactioned) {
		//	$userLead = Lead::where('status', 'Actioned')
		//		->where("id", "<", $minUnactioned)
		//		->where('firstActioner', $user->id)
		//		->where('campaignID', $this->id)
		//		->orderBy('id', 'desc')
		//		->first();
		//}
		//else {
		//	$userLead = Lead::where('status', 'Actioned')
		//		->where('firstActioner', $user->id)
		//		->where('campaignID', $this->id)
		//		->orderBy('id', 'desc')
		//		->first();
		//}

		// No unactioned lead was found in this campaign. Generate new lead
		if(!$lead) {
			$lead = $this->createNewLead($user);
		}
		else {
			// Updating timeEdited and Status for lead
			if($this->autoReferences == 'Yes')
			{
				$date = new DateTime();
				$referenceNumber = $this->autoReferencePrefix.$date->getTimestamp();
				$lead->referenceNumber = $referenceNumber;
				$lead->save();
			}

			/*
			$lead->firstActioner = $userID;
			$lead->lastActioner = $userID;
			$lead->timeEdited = new DateTime;
			$lead->status = 'Actioned';
			$lead->save();
			
			$this->save();

			// Update calls remaining as a lead was marked as actioned
			if ($this->callsRemaining > 0) {
				$this->recalculateCallsRemaining();
			}
			*/
		}

		return $lead;
	}

	/** Create a new lead for given campaign
	 * @param $user
	 * @return Lead
	 */
	function createNewLead($user) {
		//creating New Lead
		$lead = new Lead;
		$lead->campaignID = $this->id;
		$lead->timeCreated = new DateTime;

		if($this->autoReferences == 'Yes')
		{
			$date = new DateTime();
			$referenceNumber = $this->autoReferencePrefix.$date->getTimestamp();
			$lead->referenceNumber = $referenceNumber;
		}

		$lead->timeEdited = new DateTime;
		$lead->status = 'Actioned';
		$lead->firstActioner = $user->id;
		$lead->lastActioner = $user->id;
		$lead->save();
		$lead->fillFormData();

		// Change campaign total lead count
		$this->totalLeads = $this->totalLeads + 1;
		$this->save();
		$this->recalculateTotalFilteredLeads();
		$this->recalculateCallsRemaining();

		return $lead;
	}

	function getNextLead($user, $lead) 
	{
		//search for next unactioned
		$newLead = Lead::where("leads.status", "Unactioned")
			->where('campaignID', $this->id)
			->where('leads.id', '>', $lead->id)
			->whereRaw('(timeOpened IS NULL OR timeOpened < (NOW() - INTERVAL 20 SECOND))')
			->orderBy("leads.id", "ASC")
			->select('leads.*')
			;

		$this->applyPrevNextFilter($newLead, $this->getPrevNextFilterForUser($user->id));
		$newLead = $newLead->first();
		
		if (!$newLead) {
			//search for any unactioned that isn't this one
			$newLead = Lead::where("leads.status", "Unactioned")
				->where('campaignID', $this->id)
				->where('leads.id', '!=', $lead->id)
				->whereRaw('(timeOpened IS NULL OR timeOpened < (NOW() - INTERVAL 20 SECOND))')
				->orderBy("leads.id", "ASC")
				->select('leads.*')
				;

			$this->applyPrevNextFilter($newLead, $this->getPrevNextFilterForUser($user->id));
			$newLead = $newLead->first();
		}

		if (!$newLead) {
			//search for next that isn't this one
			$newLead = Lead::where('campaignID', $this->id)
				->where('leads.id', '>', $lead->id)
				->orderBy("leads.id", "ASC")
				->select('leads.*')
				;

			$this->applyPrevNextFilter($newLead, $this->getPrevNextFilterForUser($user->id));
			$newLead = $newLead->first();
			if ($newLead) {
				return $newLead;
			}
		}

		if (!$newLead) {
			//search for any that isn't this one
			$newLead = Lead::where('campaignID', $this->id)
				->where('leads.id', '!=', $lead->id)
				->orderBy("leads.id", "ASC")
				->select('leads.*')
				;

			$this->applyPrevNextFilter($newLead, $this->getPrevNextFilterForUser($user->id));
			$newLead = $newLead->first();
			if ($newLead) {
				return $newLead;
			}
		}

		if (!$newLead) {
			//no leads found, return this one
			return $lead;
		}

		/*
		$newLead = Lead::select(['id'])
			->where('id', '>', $lead->id)
			->where(function ($query) use ($user) {
				$query->where('lastActioner', $user->id);
				$query->orWhereNull('lastActioner');

			})
			->where('campaignID', $this->id)
			->orderBy('id', 'asc')
			->first();
		 */

		if (!$newLead) {
			// So could not find an unactioned lead
			// -- Check if campaign is a fly campaign. If yes, create a new lead else return null
			// We now need to return new lead for all campaigns
			$campaign = Campaign::find($lead->campaignID);

			//if ($campaign->type == "Fly") {
				$lead = $campaign->createNewLead($user);
				return $lead;
			//}
			//else {
			//	return null;
			//}
		}

		if($this->autoReferences == 'Yes')
		{
			$date = new DateTime();
			$referenceNumber = $this->autoReferencePrefix.$date->getTimestamp();
			$newLead->referenceNumber = $referenceNumber;
			$newLead->save();
		}

		/*
		$newLead->firstActioner = $user->id;
		$newLead->lastActioner = $user->id;
		$newLead->timeEdited = new DateTime;
		$newLead->status = 'Actioned';
		$newLead->save();

		$this->save();

		if ($this->callsRemaining > 0) {
			$this->recalculateCallsRemaining();
		}
		*/

		return $newLead;
	}

	private function filterLeadsByDropDownFilter($leadQuery, $filter) 
	{
		$formFields = FormFields::getDropDownNamesForCampaign($this->id);

		$conditionCount = 0;
		foreach($formFields as $formField) {
			if(isset($filter[$formField]) && $filter[$formField] != '') {
				$conditionCount++;
			}
		}

		if($conditionCount > 0) {
			$leadQuery->where(function($query) use($formFields, $filter, $conditionCount, $leadQuery) {
				$subjoin_id = 0;
				foreach($formFields as $formField) {
					if(isset($filter[$formField]) && $filter[$formField] != '') {
						$subjoin_id++;
						$subjoin_alias = 'flt_customdata_' . $subjoin_id;
						$leadQuery->join('leadcustomdata as ' . $subjoin_alias, $subjoin_alias . '.leadID', '=', 'leads.id');
						$query->where(function($q) use($formField, $filter, $subjoin_alias) {
							$q->where($subjoin_alias . '.fieldName', $formField)
							  ->where($subjoin_alias . '.value', $filter[$formField]);
						});
					}
				}
			});
		}
		
	}
	
	private function applyPrevNextFilter($leadQuery, $filter) 
	{
		if(count($filter) > 0) {
			$this->filterLeadsByDropDownFilter($leadQuery, $filter);
			
			if(isset($filter['txtTextFilter'])) {
				$textFilter = $filter['txtTextFilter'];

				if(trim($textFilter) != '') {
					$quickFilters = explode(',', $textFilter);
					foreach($quickFilters as $quickFilter) {
						if(trim($quickFilter) != '') {
							$leadQuery->whereExists(function ($query) use($quickFilter) {
								$query->select(DB::raw(1))
									->from('leadcustomdata as flt_customdata_text')
									->whereRaw('flt_customdata_text.leadID = leads.id')
									->whereRaw('value LIKE ' . DB::connection()->getPdo()->quote('%' . trim($quickFilter) . '%'))
									;
							});
						}
					}

				}
			}
			$this->filterLeadsByMassMailTemplate($leadQuery, $filter, 'selMassEmailTemplate');
		
			//filter by positive/negative/unreachable
			$formField = 'outcome';
			if(isset($filter[$formField]) && in_array(strtolower($filter[$formField]), ['interested','notinterested','unreachable','notset'])) {
				$leadQuery->where('interested', $filter[$formField]);
			}
		}
	}

	public function recalculateCallsRemaining()
	{
		$leadQuery = Lead::where("leads.status", "Unactioned")
					->where('campaignID', $this->id);

		$this->applyPrevNextFilter($leadQuery, $this->prevNextFilter);

		$count = $leadQuery->count();
		$this->callsRemaining = $count;
		$this->save();
	}

	public function recalculateTotalFilteredLeads()
	{
		if(count($this->prevNextFilter)) {
			$leadQuery = Lead::where('campaignID', $this->id);

			$this->applyPrevNextFilter($leadQuery, $this->prevNextFilter);

			$count = $leadQuery->count();
		} else {
			$count = 0;
		}
		$this->totalFilteredLeads = $count;
		$this->save();

		//recalc user filters as well
		$userFilters = CampaignPrevNextFilter::where('campaign_id', $this->id)
			->get();

		foreach($userFilters as $userFilter) {
			$filter = $userFilter->prevNextFilter;
			if(count($filter)) {
				$leadQuery = Lead::where('campaignID', $this->id);
				$this->applyPrevNextFilter($leadQuery, $filter);
				$userFilter->totalFilteredLeads = $leadQuery->count();
			} else {
				$userFilter->totalFilteredLeads = 0;
			}
			$userFilter->save();
		}
	}

	public function getTotalLeadsForUser($userID, &$isFilter = null, &$isUserFilter = null) 
	{
		//check if there exists user filter
		$userFilters = CampaignPrevNextFilter::where('campaign_id', $this->id)
			->where('user_id', '=', $userID)
			->first();
		if($userFilters && count($userFilters->prevNextFilter)) {
			$isUserFilter = true;
			$isFilter = true;
			return $userFilters->totalFilteredLeads;
		} else if(count($this->prevNextFilter)) {
			$isUserFilter = false;
			$isFilter = true;
			return $this->totalFilteredLeads;
		} else {
			$isUserFilter = false;
			$isFilter = false;
			return $this->totalLeads;
		}
	}

	public function getPrevNextFilterForUser($userID) 
	{
		$userFilters = CampaignPrevNextFilter::where('campaign_id', $this->id)
			->where('user_id', '=', $userID)
			->first();

		if($userFilters && count($userFilters->prevNextFilter)) {
			return $userFilters->prevNextFilter;
		} else if(count($this->prevNextFilter)) {
			return $this->prevNextFilter;
		} else {
			return [];
		}
	}

	public function recalculateTotalLeads()
	{
		$leadQuery = Lead::where('campaignID', $this->id);
		$count = $leadQuery->count();
		$this->totalLeads = $count;
		$this->save();
	}

	public function getMembersIDs() {
		return CampaignMember::where('campaignID', '=', $this->id)
			->lists("userID")->all();
	}

	function getMembers($all = null) {
		if($all == null){
			return User::join("campaignmembers", "campaignmembers.userID", "=", "users.id")
				->where("campaignID", $this->id)
				->where("userType", "!=", "Multi")
				->get(["users.*"]);
		}
		else if($all == 'all'){
			return User::join("campaignmembers", "campaignmembers.userID", "=", "users.id")
				->where("campaignID", $this->id)
				->get(["users.*"]);
		}
	}

	function removeMember($userID) {
		CampaignMember::where('userID', '=', $userID)
			->where('campaignID', '=', $this->id)
			->delete();
	}

	function getPreviousLead($user, $leadID) {
		$previousLead = Lead::where('id', '<', $leadID)
			->where('lastActioner', $user->id)
			->where('campaignID', $this->id)
			->orderBy('id', 'desc')
			->first();
		return $previousLead;
	}

	function getLeadDataForGivenCampaign($campaign, $leadType, $user) {
		$leads = [];
		$formLeadIds = [];
		$leadIDs = [];

		$reference = '';
		$actionType = '';

		if(\Input::has('salespersonAppointment')) {
			$salesPerson = \Input::get('salespersonAppointment');

			//get lead served by this salesperson
			$leads = (is_array($salesPerson)) ? Appointment::whereIn('salesMember', $salesPerson)->distinct()->lists('leadID')->all() :
                Appointment::where('salesMember', $salesPerson)->distinct()->lists('leadID')->all();
		}

		if(\Input::has('formCriteria')){
			$fieldName = \Input::get('formCriteria');
			$fieldValue = \Input::get('formValue');
			$fieldExact = \Input::get('formExact');

			if ( is_array($fieldName) ){



				$reference = array();
				$filteredResults = array();

				foreach ($fieldName as $k=>$v){

					if($v == 'reference'){
						$reference = array_merge($reference, [ $fieldValue[$k] ]);
					} else {

						if ( intval($fieldExact[$k]) ){
							$_flIds = LeadCustomData::where('fieldName', $v)
								->where('value', $fieldValue[$k] )
								->distinct()
								->lists('leadID')
								->all();
						} else {
							$_flIds = LeadCustomData::where('fieldName', $v)
								->where('value', 'LIKE', $fieldValue[$k] .'%' )
								->distinct()
								->lists('leadID')
								->all();
						}

						if ( isset( $filteredResults[$v]) ){
							$filteredResults[$v] = array_merge( $filteredResults[$v], $_flIds );
						} else {
							$filteredResults[$v] = $_flIds;
						}

					}
				}

				if ( count($filteredResults) > 1 ){
					// more like 1 filter - search crossed values
					$allCustom = call_user_func_array('array_intersect', $filteredResults);
					$formLeadIds = array_merge($formLeadIds, $allCustom);
				} else {
					// 1 custom field, so add them directly

					$formLeadIds = array_merge($formLeadIds, reset($filteredResults) );
				}





			} else {
				if($fieldName == 'reference'){
					$reference = $fieldValue;
				}
				else{
					//get lead served by this salesperson

					if ( intval($fieldExact) ){
						$formLeadIds = LeadCustomData::where('fieldName', $fieldName)
							->whereRaw('value = ' . DB::connection()->getPdo()->quote($fieldValue))
							->distinct()
							->lists('leadID')
							->all();
					} else {
						$formLeadIds = LeadCustomData::where('fieldName', $fieldName)
							->whereRaw('value LIKE ' . DB::connection()->getPdo()->quote($fieldValue . '%'))
							->distinct()
							->lists('leadID')
							->all();
					}

				}
			}

		}

		if(\Input::has('formCriteriaDate')){

		    $fieldName = \Input::get('formCriteriaDate');

			if ( is_array($fieldName) ){

				$fieldValueFrom = \Input::get('formValueFrom');
				$fieldValueTo   = \Input::get('formValueTo');

				foreach ($fieldName as $k=>$v){
					$fvFrom	= FormFields::validateDateFieldValue( $fieldValueFrom[$k] );
					$fvTo	= FormFields::validateDateFieldValue( $fieldValueTo[$k] );

					if($fvFrom != '' || $fvTo != '') {

						$_flIds = LeadCustomData::where('fieldName', $v);

						if($fvFrom != '') {
							$_flIds->where('value', '>=', $fvFrom);
						}

						if($fvTo != '') {
							$_flIds->where('value', '!=', '');
							$_flIds->where('value', '<=', $fvTo);
						}

						$_ids = $_flIds->distinct()->lists('leadID')->all();

						$formLeadIds = array_merge( $formLeadIds,  $_ids );
					}
				}


			} else {

				$fieldValueFrom = FormFields::validateDateFieldValue(\Input::get('formValueFrom'));
				$fieldValueTo = FormFields::validateDateFieldValue(\Input::get('formValueTo'));

				if($fieldValueFrom != '' || $fieldValueTo != '') {
					$formLeadIds = LeadCustomData::where('fieldName', $fieldName);

					if($fieldValueFrom != '') {
						$formLeadIds->where('value', '>=', $fieldValueFrom);
					}

					if($fieldValueTo != '') {
						$formLeadIds->where('value', '!=', '');
						$formLeadIds->where('value', '<=', $fieldValueTo);
					}

					$formLeadIds = 	$formLeadIds->distinct()->lists('leadID')->all();
				}
			}


		}
		
		if(\Input::has('quickSearch')){
			$fieldValue = \Input::get('quickSearch');

			if(trim($fieldValue) != '') {
				$quickFilters = explode(',', $fieldValue);
				$quickFiltersQuery = Lead::whereIn('leads.campaignID', $campaign)
					->select('leads.id')
					->distinct()
					;
				$joinIdx = 0;
				foreach($quickFilters as $quickFilter) {
					if(trim($quickFilter) != '') {
						$joinName = 'join_' . $joinIdx;
						++$joinIdx;

						$quickFiltersQuery
							->join('leadcustomdata as ' . $joinName, $joinName . '.leadID', '=', 'leads.id')
							->whereRaw($joinName . '.value LIKE ' . DB::connection()->getPdo()->quote('%' . trim($quickFilter) . '%'));
					}
				}
				if($joinIdx > 0 ) {
					$formLeadIds = $quickFiltersQuery
						->take(100)
						->lists('id');
				}
			}
		}

		if(\Input::has('actionType')){
			$actionType = \Input::get('actionType');
		}



		if(count($formLeadIds) && \Input::has('salespersonAppointment')){
			$leadIDs = array_intersect($leads, $formLeadIds);
		}
		else if(count($formLeadIds)){
			$leadIDs = $formLeadIds;
		}
		else if(\Input::has('salespersonAppointment')){
			$leadIDs = $leads;
		}

		$perPageResult = \Input::get('perPage');
		
		$leadDataQuery = Lead::select(['leads.id'
			, 'leads.timeEdited' 
			, 'leads.timeCreated'
			, 'leads.status'
			, 'campaigns.name as campaignName',
			\DB::raw('CONCAT(users.firstName, \' \', users.lastName) AS lastContact'),
			\DB::raw('
					COALESCE (
					(
						SELECT COUNT(*) AS count 
						FROM call_history 
						WHERE call_history.leadID = leads.id
						GROUP BY leadID
					)
					, 0) AS count
				'
			)])
					->whereIn('leads.campaignID', $campaign)
					->where(function($query) use ($leadType){

						if (\Input::has('_outcome')){
							$outcomeFilter = \Input::get('_outcome');
							if ( is_array($outcomeFilter) && count($outcomeFilter) ){
								$query->whereIn('leads.interested', $outcomeFilter);
							}
						}


						if($leadType == 'Interested'){
							$query->where('leads.interested', 'Interested');
						}
						else if($leadType == 'NotInterested'){
							$query->where('leads.interested', 'NotInterested');
						}
						else if($leadType == 'Unreachable'){
							$query->where('leads.interested', 'Unreachable');
						}
						else if($leadType == 'booked'){
							$query->where('leads.bookAppointment', 'Yes');
						}
						else if($leadType == 'unactioned'){
							$query->where('leads.status', 'Unactioned');
						}
						if($leadType != 'unactioned' && $leadType != 'all' && !is_null($leadType)) {
							$query->where('leads.status', 'Actioned');
						}
					})
					->where(function($query) use ($actionType, $user){
						if($actionType == 'my'){
							$query->where('leads.lastActioner', $user);
						}
					})
					->where(function($query) use ($reference){
						if($reference != '' && $reference != null){
							if (is_array($reference)){

								foreach ($reference as $ri){
									$query->where('leads.referenceNumber','LIKE', "$ri%");
								}

							} else {
								$query->where('leads.referenceNumber','LIKE', "$reference%");
							}
						}
					})
					->where(function($query){
						if(\Input::has('lastCallMember')){
							$lastCallMember = \Input::get('lastCallMember');

							if (is_array($lastCallMember)){

								$query->whereIn('leads.lastActioner', $lastCallMember);

							} else {
								if($lastCallMember != 'All'){
									$query->where('leads.lastActioner', $lastCallMember);
								}
							}
						}
					})
					->where(function($query){
						if(\Input::has('from')){
							$from = \Input::get('from');
							if($from != ''){
								$query->where(\DB::raw('DATE(leads.timeEdited)'), '>=', date('Y-m-d', strtotime($from)));
							}
						}
					})
					->where(function($query){
						if(\Input::has('to')){
							$to = \Input::get('to');
							if($to != ''){
								$query->where(\DB::raw('DATE(leads.timeEdited)'), '<=', date('Y-m-d', strtotime($to)));
							}
						}
					})
					->where(function($query){
						if(\Input::has('callMade')){
							$callMade = \Input::get('callMade');
							if($callMade != 0){
								$query->where('leads.timeEdited', '>=', Carbon::now()->subDays($callMade));
							}
						}
					})
					->where(function($query) use ($leadIDs){
						if(!empty($leadIDs)){
							$query->whereIn('leads.id', $leadIDs);
						}
						else if (\Input::has('quickSearch') || \Input::has('formCriteria') || \Input::has('formCriteriaDate') || \Input::has('salespersonAppointment')){
							$query->where(\DB::raw("false"));
						}
					})
					->join('campaigns', 'campaigns.id', '=', 'leads.campaignID');

					if($leadType == 'unactioned' || $leadType == 'all' || is_null($leadType)) {
						$leadDataQuery->leftJoin('users', 'users.id', '=', 'leads.lastActioner');
					} else {
						$leadDataQuery->join('users', 'users.id', '=', 'leads.lastActioner');
					}

		if(\Input::has('column')) {
			$column = \Input::get('column');
			$type = \Input::get('type');

			if($column == 'firstName') {
				$leadDataQuery->leftjoin('leadcustomdata', function($join) {
					$join->on('leadcustomdata.leadID', '=', 'leads.id');
					$join->where('leadcustomdata.fieldName', '=', 'First Name');
				});

				$leadDataQuery->orderBy('leadcustomdata.value', $type);

			} elseif ($column == 'Company Name') {
				$leadDataQuery->leftjoin('leadcustomdata', function($join) {
					$join->on('leadcustomdata.leadID', '=', 'leads.id');
					$join->whereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Company Name'));
				});

				$leadDataQuery->orderBy('leadcustomdata.value', $type);

			} elseif ($column == 'lastName') {
				$leadDataQuery->leftjoin('leadcustomdata', function($join) {
					$join->on('leadcustomdata.leadID', '=', 'leads.id');
					$join->whereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Last Name'));
				});

				$leadDataQuery->orderBy('leadcustomdata.value', $type);

			} elseif ($column == 'lastContacted') {
				$leadDataQuery->orderBy('leads.timeEdited', $type);

			} elseif ($column == 'contactedBy') {
				$leadDataQuery->orderBy('lastContact', $type);

			} elseif ($column == 'campaign') {
				$leadDataQuery->orderBy('campaigns.name', $type);

			} elseif ($column == 'contacted') {
				$leadDataQuery->orderBy('count', $type);

			} elseif($column == 'notes') {
				$leadDataQuery->leftjoin('leadcustomdata', function($join) {
					$join->on('leadcustomdata.leadID', '=', 'leads.id');
					$join->whereIn('leadcustomdata.fieldName', CommonController::getFieldVariations('Notes'));
				});

				$leadDataQuery->orderBy('leadcustomdata.value', $type);
			} elseif (!empty(trim($column))) {
				$leadDataQuery->leftjoin('leadcustomdata', function($join) use($column) {
					$join->on('leadcustomdata.leadID', '=', 'leads.id');
					$join->where('leadcustomdata.fieldName', '=', trim($column));
				});

				$leadDataQuery->orderBy('leadcustomdata.value', $type);
			}

		}

		if($perPageResult != 'All') {
			$leadData = $leadDataQuery->paginate($perPageResult);
		}
		else {
			$leadData = $leadDataQuery->get();
		}

		//get custom lead data

		$fieldNames = FormFields::getFieldNamesForCampaigns($campaign);

		if(sizeof($leadData) > 0){
			$customValues = LeadCustomData::getValues($leadData->pluck('id'), $fieldNames);
			for($i = 0; $i < sizeof($leadData); $i++){
				//$leadData[$i]['leadInfo'] = LeadCustomData::getLeadInformation($leadData[$i]->id, $fieldNames);
				$leadData[$i]['leadInfo'] = $customValues[$leadData[$i]->id];

				if(sizeof($leadData[$i]['leadInfo']) > 0){
					foreach($leadData[$i]['leadInfo'] as $key => $value){
						if(in_array($key, CommonController::getFieldVariations('Company Name'))){
							$leadData[$i]['Company Name'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('First Name'))){
							$leadData[$i]['First Name'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Last Name'))){
							$leadData[$i]['Last Name'] = $value;
						}
						elseif(in_array($key, CommonController::getFieldVariations('Notes'))){
							$leadData[$i]['Notes'] = $value;
						}
					}
				}
			}
		}

		return [
			'leadData' => $leadData,
		];
	}


	public function end() {

		$lead = Lead::where('campaignID', $this->id)
				->where('status', 'Unactioned')
				->sharedLock()
				->first();
		
		// Delete unactioned leads
		Lead::where('campaignID', $this->id)
			->where('status', 'Unactioned')
			->delete();

		DB::commit();
		/*
		$countOfErrors = 0;
		$pdo = DB::connection()->getPdo();

		try {
			DB::statement(DB::raw('SET autocommit=0'));
			//DB::statement(DB::raw('LOCK TABLES leads WRITE'));
			$pdo->exec('LOCK TABLES leads WRITE');

			// Delete unactioned leads
			Lead::where('campaignID', $this->id)
				->where('status', 'Unactioned')
				->delete();
			
			DB::commit();
			//DB::statement(DB::raw('UNLOCK TABLES'));
			$pdo->exec('UNLOCK TABLES');
			DB::statement(DB::raw('SET autocommit=1'));
		} catch(Exception $e) {
			$message = $e->getMessage();
			if(strpos($message, "Lock wait timeout exceeded") !== false) {
				$countOfErrors++;
				if($countOfErrors >= 5) {
					throw $e;
				}
			} else {
				throw $e;
			}
		}
		 */

		//Counting total Leads
		$totalLeads = Lead::where('campaignID', $this->id)->count();

		$this->totalLeads        = $totalLeads;
		$this->callsRemaining    = 0;
		$this->status            = 'Completed';
		$this->completedOn       = new DateTime();
		$this->save();

		$this->recalculateTotalFilteredLeads();
	}

	function getFormFields(){
		//TODO: add order by order
		return FormFields::where('formID', $this->formID)->orderBy('type', 'DESC')->get();
	}

	function getCampaignEmailSettings(){
		return \DB::table('campaign_email_settings')
			->where('campaignID', $this->id)
			->first();
	}

	function getSalesMemberList($userManager)
	{
		return SalesMember::select('salesmembers.*')
			->where('manager', $userManager)
			->join('salesmembers_campaigns', 'salesmembers.id', '=', 'salesmembers_campaigns.salesmember_id')
			->where('salesmembers_campaigns.campaign_id', $this->id)
			->get();
	}

	function prepareSlug($campaignName)
	{
		$slug = $this->generateSlug($campaignName);
		$slug = $this->validateSlug($slug);
		$slug = $this->makeSlugUnique($slug);

		return $slug;
	}

	public function detectLeadEmailsWithFilter($filter, $user) 
	{
		$leadQuery = Lead::join('leadcustomdata', 'leadcustomdata.leadID', '=', 'leads.id')
				->select('leads.id', 'leadcustomdata.fieldName', 'leadcustomdata.value', 'leadcustomdata.leadID')
				->where('leads.campaignID', $this->id);
		$this->applyMassEmailsFilter($leadQuery, $filter, $user);

		$leads = $leadQuery->get();
		return $leads;
	}

	public function countLeadEmailsWithFilter($filter, $user) 
	{
		$leadQuery = Lead::join('leadcustomdata', 'leadcustomdata.leadID', '=', 'leads.id')
				->select('leads.id')
				->where('leads.campaignID', $this->id);
		$this->applyMassEmailsFilter($leadQuery, $filter, $user);

		$count = $leadQuery->count();
		return $count;
	}

	/* validates advanced mass emails filter and rebuild it
	 */
	public static function fixMassEmailsFilter($filter) 
	{
		$result = null;
		if($filter != null && $filter != '' && $filter != 'undefined') {
			$filter = json_decode($filter, true);
			//get fixed list of values
			$result = array_intersect_key($filter, 
				[
					'outcome' => 1,
					'leadType' => 1,
					'updateFrom' => 1,
					'updateTo' => 1,
					'selFilterOpenedEmails' => 1,
					'selFilterClickedEmails' => 1,
					"selFollowMassEmailTemplate" => "",
					"chkFilterClickedEmails" => "",
					"chkFilterOpenedEmails" => "",
					"chkLastUpdatedLeads" => "",
				]
			);
			//get dynamic field props
			if(isset($filter['inputFieldFiltersCount']) 
				&& $filter['inputFieldFiltersCount'] != ''
				&& is_numeric($filter['inputFieldFiltersCount'])
			) {
				$fieldFiltersCount = intval($filter['inputFieldFiltersCount']);
				if($fieldFiltersCount > 100) {
					$fieldFiltersCount = 100;
				}
				$new_num = 0;
				for($i = 0; $i < $fieldFiltersCount; $i++) {
					$fieldName = trim(str_replace([',','"',"'"], '',isset($filter['selFieldFilterField_' . $i]) ? $filter['selFieldFilterField_' . $i] : ''));
					if($fieldName == '' || $fieldName == null) {
						continue;
					}
					$fieldType = isset($filter['inputFieldFilterType_' . $i]) ? $filter['inputFieldFilterType_' . $i] : '';
					if(!in_array($fieldType, ['text','date','dropdown'])) {
						continue;
					}
					if($fieldType == 'text') {
						$fieldValue = trim(str_replace('"', '', isset($filter['inputFieldFilterValue_' . $i]) ? $filter['inputFieldFilterValue_' . $i] : ''));
						if($fieldValue == '' || $fieldValue == null) {
							continue;
						}
						$result['inputFieldFilterValue_' . $new_num] = $fieldValue;
					} elseif($fieldType == 'dropdown') {
						if(!isset($filter['inputFieldFilterValue_' . $i])) {
							continue;
						}
						if(!is_array($filter['inputFieldFilterValue_' . $i])) {
							$fieldValue = trim(str_replace('"', '', isset($filter['inputFieldFilterValue_' . $i]) ? $filter['inputFieldFilterValue_' . $i] : ''));
							if($fieldValue == '' || $fieldValue == null) {
								continue;
							}
							$result['inputFieldFilterValue_' . $new_num] = $fieldValue;
						} else {
							$newFieldValues = [];
							$fieldValues = $filter['inputFieldFilterValue_' . $i];
							foreach($fieldValues as $fieldValue) {
								$fieldValue = trim(str_replace('"', '', $fieldValue));
								if($fieldValue == '' || $fieldValue == null) {
									continue;
								}
								$newFieldValues[] = $fieldValue;
							}
							if(!count($newFieldValues)) {
								continue;
							}
							$result['inputFieldFilterValue_' . $new_num] = $newFieldValues;
						}

					} elseif($fieldType == 'date') {
						$fieldValueFrom = trim(str_replace('"', '', isset($filter['inputFieldFilterValueFrom_' . $i]) ? $filter['inputFieldFilterValueFrom_' . $i] : ''));
						$fieldValueTo = trim(str_replace('"', '', isset($filter['inputFieldFilterValueTo_' . $i]) ? $filter['inputFieldFilterValueTo_' . $i] : ''));
						if(($fieldValueFrom == '' || $fieldValueFrom == null) && ($fieldValueTo == '' || $fieldValueTo == null)) {
							continue;
						}
						if(!($fieldValueFrom == '' || $fieldValueFrom == null)) {
							$result['inputFieldFilterValueFrom_' . $new_num] = $fieldValueFrom;
						}
						if(!($fieldValueTo == '' || $fieldValueTo == null)) {
							$result['inputFieldFilterValueTo_' . $new_num] = $fieldValueTo;
						}
					}
					$result['selFieldFilterField_' . $new_num] = $fieldName;
					$result['inputFieldFilterType_' . $new_num] = $fieldType;
					$new_num++;
				}
				if($new_num > 0) {
					$result['inputFieldFiltersCount'] = $new_num;
				}
			}
		}
		return count($result) ? json_encode($result): null;;
	}



	private function applyMassEmailsFilter($leadQuery, $filter, $user)
	{
		$leadQuery->where(function($q) {
			$q->where('leadcustomdata.fieldName', 'Email')
			  ->where('leadcustomdata.value', '!=', '');
		});

		if($filter != null && $filter != '' && $filter != 'undefined') {
			$filter = json_decode($filter, true);
			if($filter['outcome'] != '') {
				$leadQuery->where('leads.interested', $filter['outcome']);
			}

			if($filter['leadType'] == 'me') {
				$leadQuery->where('leads.lastActioner', $user->id);
			}

			if(isset($filter['updateFrom']) && $filter['updateFrom'] != '') {
				$leadQuery->where('leads.timeEdited', '>=', 
					Carbon::createFromFormat(strpos($filter['updateFrom'], '/') !== false ? 'd/m/Y' :  'd-m-Y', $filter['updateFrom'])->format('Y-m-d 00:00:00'));
			}

			if(isset($filter['updateTo']) && $filter['updateTo'] != '') {
				$leadQuery->where('leads.timeEdited', '<=', 
					Carbon::createFromFormat(strpos($filter['updateTo'], '/') !== false ? 'd/m/Y' :  'd-m-Y', $filter['updateTo'])->format('Y-m-d 23:59:59'));
			}

			$this->filterLeadsByDropDownFilter($leadQuery, $filter);

			$this->filterLeadsByFieldFilter($leadQuery, $filter);

			$this->filterLeadsByMassMailTemplate($leadQuery, $filter, 'selFollowMassEmailTemplate');
		}
	}

	private function filterLeadsByFieldFilter($leadQuery, $filter) 
	{
		$campaignFormFields = FormFields::getFieldsForCampaign($this->id)
			->keyBy('fieldName')
			;

		$queryFilters = [];
		if(isset($filter['inputFieldFiltersCount']) 
			&& $filter['inputFieldFiltersCount'] != ''
			&& is_numeric($filter['inputFieldFiltersCount'])
		) {
			$fieldFiltersCount = intval($filter['inputFieldFiltersCount']);
			if($fieldFiltersCount > 100) {
				$fieldFiltersCount = 100;
			}
			for($i = 0; $i < $fieldFiltersCount; $i++) {

				$queryFilter = [];


				$fieldName = trim(str_replace([',','"',"'"], '',isset($filter['selFieldFilterField_' . $i]) ? $filter['selFieldFilterField_' . $i] : ''));
				if($fieldName == '' || $fieldName == null) {
					continue;
				}

				if(!$campaignFormFields->has($fieldName)) {
					continue;
				}

				$fieldType = isset($filter['inputFieldFilterType_' . $i]) ? $filter['inputFieldFilterType_' . $i] : '';
				if(!in_array($fieldType, ['text','date','dropdown'])) {
					continue;
				}
				if($fieldType == 'text') {
					$fieldValue = trim(str_replace('"', '', isset($filter['inputFieldFilterValue_' . $i]) ? $filter['inputFieldFilterValue_' . $i] : ''));
					if($fieldValue == '' || $fieldValue == null) {
						continue;
					}
					$queryFilter['fieldValue'] = $fieldValue;
				} elseif($fieldType == 'dropdown') {
					if(!isset($filter['inputFieldFilterValue_' . $i])) {
						continue;
					}
					if(!is_array($filter['inputFieldFilterValue_' . $i])) {
						$fieldValue = trim(str_replace('"', '', isset($filter['inputFieldFilterValue_' . $i]) ? $filter['inputFieldFilterValue_' . $i] : ''));
						if($fieldValue == '' || $fieldValue == null) {
							continue;
						}
						$queryFilter['fieldValue'] = $fieldValue;
					} else {
						$newFieldValues = [];
						$fieldValues = $filter['inputFieldFilterValue_' . $i];
						foreach($fieldValues as $fieldValue) {
							$fieldValue = trim(str_replace('"', '', $fieldValue));
							if($fieldValue == '' || $fieldValue == null) {
								continue;
							}
							$newFieldValues[] = $fieldValue;
						}
						if(!count($newFieldValues)) {
							continue;
						}
						$queryFilter['fieldValue'] = $newFieldValues;
					}

				} elseif($fieldType == 'date') {
					$fieldValueFrom = trim(str_replace('"', '', isset($filter['inputFieldFilterValueFrom_' . $i]) ? $filter['inputFieldFilterValueFrom_' . $i] : ''));
					$fieldValueTo = trim(str_replace('"', '', isset($filter['inputFieldFilterValueTo_' . $i]) ? $filter['inputFieldFilterValueTo_' . $i] : ''));
					if(($fieldValueFrom == '' || $fieldValueFrom == null) && ($fieldValueTo == '' || $fieldValueTo == null)) {
						continue;
					}
					if(!($fieldValueFrom == '' || $fieldValueFrom == null)) {
						$queryFilter['fieldValueFrom'] = $fieldValueFrom;
					}
					if(!($fieldValueTo == '' || $fieldValueTo == null)) {
						$queryFilter['fieldValueTo'] = $fieldValueTo;
					}
				}
				$queryFilter['fieldName'] = $fieldName;
				$queryFilter['fieldType'] = $fieldType;
				$queryFilters[] = $queryFilter;
			}
		}

		if(count($queryFilters) > 0) {
			$leadQuery->where(function($query) use($queryFilters, $leadQuery) {
				$subjoin_id = 0;
				foreach($queryFilters as $queryFilter) {
					$subjoin_id++;
					$subjoin_alias = 'flta_customdata_' . $subjoin_id;
					$leadQuery->join('leadcustomdata as ' . $subjoin_alias, $subjoin_alias . '.leadID', '=', 'leads.id');
					$query->where(function($q) use($queryFilter, $subjoin_alias) {
						$q->where($subjoin_alias . '.fieldName', $queryFilter['fieldName']);

						if($queryFilter['fieldType'] == 'text') {
							$q->whereRaw($subjoin_alias . '.value LIKE ' . DB::connection()->getPdo()->quote('%' . $queryFilter['fieldValue'] . '%'));
						} else if($queryFilter['fieldType'] == 'dropdown') {
							if(is_array($queryFilter['fieldValue'])) {
								$q->whereIn($subjoin_alias . '.value', $queryFilter['fieldValue']);
							} else {
								$q->where($subjoin_alias . '.value', $queryFilter['fieldValue']);
							}
						} else {
							if(isset($queryFilter['fieldValueFrom']) && $queryFilter['fieldValueFrom'] != '') {
								$q->where($subjoin_alias . '.value', '>=', 
									Carbon::createFromFormat(strpos($queryFilter['fieldValueFrom'], '/') !== false ? 'd/m/Y' :  'd-m-Y', $queryFilter['fieldValueFrom'])->format('Y-m-d'));
							}

							if(isset($queryFilter['fieldValueTo']) && $queryFilter['fieldValueTo'] != '') {
								$q->where($subjoin_alias . '.value', '<=', 
									Carbon::createFromFormat(strpos($queryFilter['fieldValueTo'], '/') !== false ? 'd/m/Y' :  'd-m-Y', $queryFilter['fieldValueTo'])->format('Y-m-d'));
							}
						}
					});
				}
			});
		}
	}
	

	private function filterLeadsByMassMailTemplate($leadQuery, $filter, $massMailTemplateSelector)  
	{
		if(isset($filter[$massMailTemplateSelector])) {
			$massEmailFilter = intval($filter[$massMailTemplateSelector]);
			if($massEmailFilter > 0) {
				$leadQuery->join('mass_emails', function ($join) use($massEmailFilter) {
					$join->on('mass_emails.lead_id', '=', 'leads.id')
						->where('mass_emails.template_id', '=', $massEmailFilter)
						->where('mass_emails.status', '=', 'Sent')
						->whereNotIn('mass_emails.aws_status', ['Bounce','Complaint'])
						;
				});
				if(isset($filter['selFilterOpenedEmails']) && $filter['selFilterOpenedEmails'] != '') {
					$filterOpenedEmails = $filter['selFilterOpenedEmails'];
					if($filterOpenedEmails == 0) {
						$leadQuery->where('mass_emails.email_open_count', '=', 0);
					} elseif (in_array($filterOpenedEmails, [1,2,3,5])) {
						$leadQuery->where('mass_emails.email_open_count', '>=', $filterOpenedEmails);
					}
				}
				
				if(isset($filter['selFilterClickedEmails']) && $filter['selFilterClickedEmails'] != '') {
					$filterOpenedEmails = $filter['selFilterClickedEmails'];
					if($filterOpenedEmails == 0) {
						$leadQuery->where('mass_emails.email_click_count', '=', 0);
					} elseif (in_array($filterOpenedEmails, [1,2,3,5])) {
						$leadQuery->where('mass_emails.email_click_count', '>=', $filterOpenedEmails);
					}
				}
			}
		}
	}


	function getTeamMassMailTemplate($user) {
		return MassEmailTemplate::where('campaign_id', $this->id)
				->whereIn('user_id', $user->getTeamMembersWithManagerIDs())
				->get();
	}
}
