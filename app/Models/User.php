<?php

namespace App\Models;

use App\Http\Controllers\CommonController;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Form;
use App\Models\Landingform;
use Carbon\Carbon;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Input;
use Kbwebs\MultiAuth\PasswordResets\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Kbwebs\MultiAuth\PasswordResets\Contracts\CanResetPassword as CanResetPasswordContract;


class User extends Model implements AuthenticatableContract,
		AuthorizableContract,
		CanResetPasswordContract
{

	use Authenticatable, Authorizable, CanResetPassword;

	public $timestamps = false;
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password',
	                     'remember_token'];

	protected $fillable = ['firstName',
	                       'lastName',
	                       'email',
	                       'password',
	                       'contactNumber',
	                       'country',
	                       'zipcode',
	                       'latitude',
	                       'longitude',
	                       'companyName',
	                       'userType',
	                       'accountCreationDate',
	                       'lastLogin'];

	private $userPaymentInfo = null;

	public function licenseType() {
		return $this->belongsTo("App\Models\License", "licenseType", 'licenseType');
	}

	public function licenseExpireDate() {
		return $this->belongsTo("App\Models\License", "expireDate");
	}

	public function scopeActive($query) {
		return $query->where('accountStatus', 'Active');
	}

	public function scopeVerified($query) {
		return $query->whereIn('email_verification', ['Yes', 'NoNeed']);
	}
	
	/* return admin+members
	 * */
	public function getAllTeamMembers() {
		return $this->getTeamMembers('manager');
	}

	/** Get team members of current user
	 * @return User
	 */
	public function getTeamMembers($option=null) {
		//TODO: Active and verified users
		$query = User::where('manager', $this->getManager())
				->orWhere('id', $this->id);

		if($option == 'manager') {
			$query->orWhere('id', $this->getManager());
		}

		return $query->get();
	}

	public function isManager() 
	{
		return $this->userType != "Team";
	}

	/** Get manager of this user. If user is single or multi, he his self manager
	 * @return mixed
	 */
	public function getManagerID() {
		if ($this->userType == "Team") {
			$userManger = $this->manager;
		}
		else {
			$userManger = $this->id;
		}

		return $userManger;
	}
	/* old version*/
	public function getManager() {
		return $this->getManagerID();
	}


	/**
	 * Get forms which user is allowed to access
	 */
	public function getAvailableForms() {
		//$adminUser = CommonController::getAdminUserInfo();
		return Form::whereIn('creator', $this->getTeamMembersIDs())
			->orWhere('creator', $this->getManager())
			//->orWhere('creator', $adminUser->id)
			->get();
	}

	public function getAvailableLandingForms() {
		$adminUser = CommonController::getAdminUserInfo();
		return Landingform::whereIn('creator', $this->getTeamMembersIDs())
		           ->orWhere('creator', $this->getManager())
		           ->orWhere('creator', $adminUser->id)
		           ->get();
	}

	//TODO: Active and verified users
	public function getTeamMembersIDs() {
		$managerId = $this->getManager();
		$userIDs = User::where("manager", $managerId)
			->lists("id")->all();

		//if current user is manager then include him too 
		//if not - then only colleagues 
		if($managerId == $this->id) {
			$userIDs[] = $this->id;
		}
		return $userIDs;
	}
	
	public function getTeamMembersWithManagerIDs() {
		$managerId = $this->getManager();
		$userIDs = User::where("manager", $managerId)
			->lists("id")->all();
		
		//if current user is manager then include him too 
		//if not - then colleagues + manager
		if($managerId == $this->id) {
			$userIDs[] = $this->id;
		} else {
			$userIDs[] = $managerId;
		}

		return $userIDs;
	}

	/* get list of team members available for access, 
	 * i.e. for admin it returns all team, for team member just him
	 */
	public function getAccessibleTeamMembersIds() 
	{
		$teamIds = [];
		if($this->isManager()) {
			$teamIds = $this->getTeamMembersIDs();
		} else {
			$teamIds = [$this->id];
		}
		return $teamIds;
	}


	public function getAllCampaigns() {
		$teamIds = $this->getAccessibleTeamMembersIds();
		$campaigns = Campaign::whereIn('id', function ($query) use($teamIds) {
			$query->select("campaignID")
				->from("campaignmembers")
				->whereIn("userID", $teamIds);
			})
			->orderBy("id", "DESC")
			->groupBy("id");

		return $campaigns->get();
	}

	public function getStartedCampaigns() {
		$teamIds = $this->getAccessibleTeamMembersIds();
		$campaigns = Campaign::whereIn('id', function ($query) use($teamIds) {
			$query->select("campaignID")
				->from("campaignmembers")
				->whereIn("userID", $teamIds);
		})
			->where('status', '=', "Started")
			->orderBy("id", "DESC")
			->groupBy("id");

		return $campaigns->get();
	}

	public function getActiveCampaigns() {
		$teamIds = $this->getAccessibleTeamMembersIds();
		$campaign = Campaign::whereIn('id', function ($query) use($teamIds) {
			$query->select("campaignID")
				->from("campaignmembers")
				->whereIn("userID", $teamIds);
		})
			->whereNotIn('status', ['Completed', 'Archived'])
			->orderBy("id", "DESC")
			->groupBy("id");

		return $campaign->get();
	}

	public function getActiveWithLastActionerCampaigns() {

		$name = Input::get('name');
		$from = Input::get('from');
		$to = Input::get('to');
		$letterFilter = Input::get('letterFilter');

		$teamIds = $this->getAccessibleTeamMembersIds();
		$teamIds = implode(',', $teamIds);

		return Campaign::from(
			DB::raw("
			(
				SELECT campaigns.id, campaigns.name, campaigns.status, campaigns.type, campaigns.timeStarted
					, campaigns.callsRemaining, firstName, lastName, leads.timeEdited
					, campaigns.prevNextFilter
					, campaigns.totalLeads
					, campaigns.totalFilteredLeads
					, campaign_prevnext_filters.prevNextFilter as userPrevNextFilter
					, campaign_prevnext_filters.totalFilteredLeads as userTotalFilteredLeads
				FROM campaigns,
				(
					SELECT campaigns_inner.id, MAX(leads_o.id) AS lead_id
					FROM (
						SELECT campaigns_i.id, MAX(leads_i.timeEdited) max_time_edited
						FROM campaigns AS campaigns_i
						LEFT JOIN leads AS leads_i ON campaigns_i.id = leads_i.campaignID
						JOIN campaignmembers AS campaignmembers_i ON campaigns_i.id = campaignmembers_i.campaignID
							AND campaignmembers_i.userID IN({$teamIds})
						WHERE campaigns_i.status NOT IN ('Completed' , 'Archived')
						GROUP BY campaigns_i.id
						
					) campaigns_inner
					LEFT JOIN leads AS leads_o ON campaigns_inner.id = leads_o.campaignID
							AND campaigns_inner.max_time_edited IS NOT NULL
							AND leads_o.timeEdited = max_time_edited
					GROUP BY campaigns_inner.id

				) as campaigns_outer
				LEFT JOIN leads  ON campaigns_outer.id = leads.campaignID AND campaigns_outer.lead_id IS NOT NULL AND leads.id = campaigns_outer.lead_id
				LEFT JOIN users ON leads.lastActioner = users.id 
				LEFT JOIN campaign_prevnext_filters ON campaign_prevnext_filters.campaign_id = campaigns_outer.id AND campaign_prevnext_filters.user_id = ?
				JOIN campaignmembers AS campaignmembers_o ON campaignmembers_o.userID IN({$teamIds})
				WHERE campaigns.id = campaigns_outer.id
					AND campaigns.id = campaignmembers_o.campaignID
			) as res
			"))
			->addBinding([$this->id])
			//->addBinding([$teamIds, $teamIds])
			->where(function($query) use($name, $from, $to, $letterFilter){
				if($name != null && $name != ''){
					$query->whereRaw('res.name LIKE ' . DB::connection()->getPdo()->quote('%' . $name . '%'));
				}

				if($letterFilter != null && $letterFilter != ''){
					$query->whereRaw('res.name LIKE ' . DB::connection()->getPdo()->quote($letterFilter . '%'));
				}

				if($from != null && $from != ''){
					$query->where('res.timeStarted', '>=', date('Y-m-d h:i:s', strtotime($from)));
				}

				if($to != null && $to != ''){
					$query->where('res.timeStarted', '<=', date('Y-m-d h:i:s', strtotime($to)));
				}
			})
			->whereNotIn('res.status', ['Completed', 'Archived'])
			->orderBy("res.id", "DESC")
			->groupBy("res.id")
			->paginate(10);
	}

	public function getCompletedCampaigns() {
		$teamIds = $this->getAccessibleTeamMembersIds();
		return Campaign::whereIn('id', function ($query) use($teamIds) {
			$query->select("campaignID")
				->from("campaignmembers")
				->whereIn("userID", $teamIds);
		})
			->where('status', '=', 'Completed')
			->orderBy("completedOn", "DESC")
			->groupBy("id")
			->paginate(10);
	}

	public function getArchivedCampaigns() {
		$teamIds = $this->getAccessibleTeamMembersIds();
		return Campaign::whereIn('id', function ($query) use($teamIds) {
			$query->select("campaignID")
				->from("campaignmembers")
				->whereIn("userID", $teamIds);
		})
			->where('status', '=', 'Archived')
			->orderBy("id", "DESC")
			->groupBy("id")
			->paginate(10);
	}

	public function getEmailTemplates() {
		$emailCreators = [$this->id];

		if ($this->userType == "Team") {
			$emailCreators[] = $this->manager;
		}
		// Get rest of the data for this lead
		return EmailTemplate::whereIn('owner', $emailCreators)
			->get();
	}

	public function getUserLicenseStatus() {
		return License::where('owner', $this->id)
			->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
			->value('licensetypes.type');
	}

	public function addTrialUserTrack($insert) {
		DB::table('trial_user_track')->insert($insert);
	}

	public function updateTrialUserTrack($update) {
		DB::table('trial_user_track')->where('user_id', $this->id)->update($update);
	}

	public function getIsTrialAttribute() {
		$trial = User::join('licenses', 'licenses.owner', '=', 'users.id')
				->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
				->where('licensetypes.type', 'Trial')
				->where('users.id', $this->id)
				->first();

		if($trial) {
			return true;
		}
		else {
			return false;
		}
	}

	function getUserEmailCredit() {
		return DB::table('email_credit')->where('user_id', $this->id)->first();
	}

	function createEmailCredit($email) {
		$insert = [
			'user_id' => $this->id,
			'email' => $email,
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		];
		DB::table('email_credit')->insert($insert);
	}

	function updateEmailCredit($email) {
		$update = [
				'email' => $email,
				'updated_at' => Carbon::now()
		];
		DB::table('email_credit')->where('user_id', $this->id)->update($update);
	}

	function getMassMailServerSetting() {
		return DB::table('massmailserver_setting')->where('user_id', $this->id)->first();
	}

	function createMassMailServerSetting($insert) {
		return DB::table('massmailserver_setting')->insert($insert);
	}

	function updateMassMailServerSetting($update) {
		return DB::table('massmailserver_setting')->where('user_id', $this->id)->update($update);
	}

	function getTeamEmailCredit() {
		if($this->userType == 'Single' || $this->userType == 'Multi') {
			return $this->getUserEmailCredit();
		}
		elseif($this->userType == 'Team') {
			$manager = User::find($this->getManager());
			return $manager->getUserEmailCredit();
		}
	}

	//TODO: get team server setting
	function getTeamServerSetting() {
		if($this->userType == 'Single' || $this->userType == 'Multi') {
			return $this->getMassMailServerSetting();
		}
		elseif($this->userType == 'Team') {
			$manager = User::find($this->getManager());
			return $manager->getMassMailServerSetting();
		}
	}

	function reduceEmailCredit($settingType) {
		if($this->userType == 'Single' || $this->userType == 'Multi') {
			if($settingType == 'superadmin') {
				//reduce email credit by 1
				$emailCredit = $this->getTeamEmailCredit();
				$emails = $emailCredit->email;
				$emails = $emails - 1;

				$this->updateEmailCredit($emails);
			}
		}
		elseif($this->userType == 'Team') {
			$manager = User::find($this->getManager());
			if($settingType == 'superadmin') {
				//reduce email credit by 1
				$emailCredit = $this->getTeamEmailCredit();
				$emails = $emailCredit->email;
				$emails = $emails - 1;

				$manager->updateEmailCredit($emails);
			}
		}
	}

	function setPendingFromPostponeMassEmail() {
		MassEmail::user($this->id)->where('status', 'Postpone')->update(['status' => 'Pending']);
	}

	function checkForPostponeEmails() {
		$postponeEmails = MassEmail::user($this->id)->where('status', 'Postpone')->count();

		if($postponeEmails > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	function setPaymentInfo($insert) {
		$details = $this->getPaymentInfo();

		if($details) {
			DB::table('user_payment_info')->where('user_id', $this->id)->update($insert);
		}
		else {
			$insert['user_id'] = $this->id;
			if(!isset($insert['billing_email'])) {
				$insert['billing_email'] = $this->email;
			}
			DB::table('user_payment_info')->insert($insert);
		}
	}

	function getPaymentInfo() {
		return DB::table('user_payment_info')->where('user_id', $this->id)->first();
	}

	function getPaymentInfoAttribute()
	{
		if($this->userPaymentInfo == null) {
			$this->userPaymentInfo = $this->getPaymentInfo();
		}

		return $this->userPaymentInfo;
	}

	function getLatestTransaction($all = null)
	{
		$query = Transaction::where('purchaser', $this->id)->orderBy('time', 'DESC');
		if($all == null) {
			return $query->first();
		}
		else {
			return $query->get();
		}
	}
	
	function getLatestTopTransaction()
	{
		$query = Transaction::where('purchaser', $this->id)
			->where('id', 'LIKE', 'I-%')
			->whereIn('state', ['Active', 'Pending'])
			->orderBy('time', 'DESC');
		return $query->first();
	}
}
