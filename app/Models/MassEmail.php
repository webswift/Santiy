<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MassEmail extends Model
{
    protected $table = 'mass_emails';
    protected $customUnsubscibeTable = 'custom_unsubscribes';
    protected $campaignUnsubscibeTable = 'campaign_unsubscribes';

    public function getLeadDataAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('jS F Y, g:i a');
    }

    public function getOpenedAtAttribute($value)
    {
        if($value == null) {
            return '-';
        }
        return Carbon::parse($value)->format('jS F Y, g:i a');
    }

    public function scopeUser($query, $user_id) {
        return $query->where('user_id', $user_id);
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    public function setFailReason($reason)
    {
        $this->fail_reason = $reason;
        $this->save();
    }

    public function customUnsubscribe($manager)
    {
        $alreadyExist = \DB::table($this->customUnsubscibeTable)->where('email', $this->email)->where('user_id', $manager)->count();

        if($alreadyExist <= 0) {
            \DB::table($this->customUnsubscibeTable)->insert(['email' => $this->email, 'user_id' => $manager]);
        }
    }

    public function campaignUnsubscribe($campaignID)
    {
        $alreadyExist = \DB::table($this->campaignUnsubscibeTable)->where('email', $this->email)->where('campaign_id', $campaignID)->count();

        if($alreadyExist <= 0) {
            \DB::table($this->campaignUnsubscibeTable)
               ->insert(['email' => $this->email, 'campaign_id' => $campaignID]);
        }
    }

    public function checkCustomUnsubscribe($manager)
    {
        return \DB::table($this->customUnsubscibeTable)->where('user_id', $manager)->where('email', $this->email)->first();
    }

    public function checkCampaignUnsubscribe($campaign)
    {
        return \DB::table($this->campaignUnsubscibeTable)->where('campaign_id', $campaign)->where('email', $this->email)->first();
    }

    public function getLeadFromEmail($campaignID) {
        return Lead::join('leadcustomdata', 'leadcustomdata.leadID', '=', 'leads.id')
            ->where('leadcustomdata.fieldName', 'Email')
            ->where('leadcustomdata.value', $this->email)
            ->where('leads.campaignID', $campaignID)
            ->select('leads.*')
            ->first();
    }

    public function getCallHistory() {
        return \DB::table('call_history')->where('mass_email_id', $this->id)->first();
    }

    public function updateCallHistoryNotes() {
        $notes = 'Mass mail, ';

        if($this->status == 'Pending' && $this->aws_status == 'Pending') {
            $status = 'Undelivered';
        }
        elseif($this->status != 'Pending' && $this->aws_status == 'Bounce') {
            $status = 'Bounced';
        }
        elseif($this->status != 'Pending' && $this->aws_status == 'Delivery') {
            $status = 'Delivered';
        }
        elseif($this->status != 'Pending' && $this->aws_status == 'Complaint') {
            $status = 'Complaint';
        }
        elseif($this->aws_status == 'Pending' && $this->status != 'Pending') {
            $status = $this->status;
        }

        $notes .=  $status;

        if($this->is_email_open) {
            $notes .= ', Opened '.$this->email_open_count.' times ';
        }
        if($this->is_email_click) {
            $notes .= ', Clicked '.$this->email_click_count.' times ';
        }

        \DB::table('call_history')->where('mass_email_id', $this->id)->update(['notes' => $notes]);
    }
}
