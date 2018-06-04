<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTime;
use Session;

class Lead extends Model {

	public $timestamps = false;
	protected $table = 'leads';
	protected $fillable = ['id'];

	public function customData() {
		return $this->hasMany('App\Models\LeadCustomData', 'leadID', 'id');
	}

	public function fillFormData() {
		// Filling LeadCustom Data
		$formColumns = FormFields::select('id', 'fieldName')
			->where('formID',$this->campaign->formID)
			->get();

		foreach($formColumns as $formColumn)
		{
			$leadcustomdata = new LeadCustomData;
			$leadcustomdata->leadID = $this->id;
			$leadcustomdata->fieldID = $formColumn->id;
			$leadcustomdata->fieldName = $formColumn->fieldName;
			$leadcustomdata->value = '';
			$leadcustomdata->save();
		}
	}

	public function getCallBack() {
		return CallBack::where('callbacks.leadID', $this->id)
			->where('status', 'Pending')
			->orderBy("time", "DESC")
			->first();
	}

	public function getAppointment() {
		return Appointment::where('leadID', $this->id)
			->first();
	}

	public function getFormFields() {
		return FormFields::where('formID', $this->campaign->formID)->orderBy('order', 'ASC')->get();
	}

	public function getCustomData() {
		return LeadCustomData::where('leadcustomdata.leadID', $this->id)
				->join('formfields', 'formfields.id', '=', 'leadcustomdata.fieldID')
				->orderBy("formfields.order", "ASC")
				->orderBy("leadcustomdata.fieldName", "ASC")
				->select('leadcustomdata.*')
				->get();
	}

	public function getLeadNumber() {
		return Lead::where('campaignID', $this->campaignID)
			->where('status', 'Actioned')
			->where('id', '<', $this->id)
			->count() + 1;
	}

	public function campaign () {
		return $this->belongsTo("App\Models\Campaign", "campaignID", "id");
	}

	public function createLeadCallHistory($agentID, $return = false)
	{
		//check for email exists
		$emailExist = LeadCustomData::where('fieldName', 'Email')
			->where('leadID', $this->id)
			->count();

		$agentName = User::where('id', '=', $agentID)
			->select([\DB::raw('CONCAT(users.firstName, \' \', users.lastName) AS agentName')])
			->value('agentName')
			;

		if($emailExist > 0) {
			$callID = \DB::table('call_history')
	             ->insertGetId([
		             'leadID' => $this->id,
					 'agent_name' => $agentName,
		             'agent' => $agentID,
		             'emailTemplate' => $this->emailTemplate
	             ]);
		} else {
			$callID = \DB::table('call_history')
				->insertGetId([
		             'leadID' => $this->id,
					 'agent_name' => $agentName,
		             'agent' => $agentID
	             ]);
		}

		//if (!App::runningInConsole()) {
		if (strpos(php_sapi_name(), 'cli') === false) {
			//don't run under cli
			Session::put('callHistoryID', $callID);
		}

		if($return) {
			return $callID;
		}
	}

	public function updateCallHistory($info)
	{
		if(Session::has('callHistoryID')) {
			\DB::table('call_history')
			   ->where('id', \Session::get('callHistoryID'))
			   ->update($info);
		}
	}

	public function getLeadCallHistory()
	{
		return \DB::table('call_history')
			->where('call_history.leadID', $this->id)
			->select([
				'agent_name AS agentName', 
				'call_history.created_at',
				'email_template_name as emailName', 
				'mass_email_templates.name as massEmailName', 
				'call_history.notes', 
				'call_history.callTime', 
				'call_booked_with_user_name AS callBookedWithName',
				'appointment_with_sales_name AS appointmentBookedWith',
			])
			->leftJoin('mass_email_templates', function($join){
				$join->on('mass_email_templates.id', '=', 'call_history.mass_email_template_id');
			})
			->get();
	}

	public function addLeadAttachment($userID, $fileName, $original)
	{
		\DB::table('lead_attachment')
			->insert([
				'leadID'    =>  $this->id,
				'userID'    =>  $userID,
				'fileName'  =>  $fileName,
				'originalFileName'  => $original
			]);
	}

	public function getLeadAttachment()
	{
		return \DB::table('lead_attachment')
			->where('lead_attachment.leadID', $this->id)
			->join('users', 'users.id', '=', 'lead_attachment.userID')
			->select([\DB::raw('CONCAT(users.firstName, \' \', users.lastName) AS userName'), 'lead_attachment.fileName', 'lead_attachment.time', 'lead_attachment.originalFileName', 'lead_attachment.id'])
			->orderBy('lead_attachment.id', 'DESC')
			->get();
	}

	public function getCallHistoryNotes()
	{
		return \DB::table('call_history')
			->where('call_history.leadID', $this->id)
			->select(['call_history.created_at', 'call_history.notes'])
			->get();
	}

	public function markAsActioned($user) {
		$this->firstActioner = $user->id;
		$this->lastActioner = $user->id;
		$this->timeEdited = new DateTime;
		$this->status = 'Actioned';
		$this->save();

		if ($this->campaign->callsRemaining > 0) {
			$this->campaign->recalculateCallsRemaining();
		}
	}
}
