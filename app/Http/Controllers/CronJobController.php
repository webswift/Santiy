<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Models\AdminEmailTemplate;
use App\Models\CallBack;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\LeadCustomData;
use App\Models\License;
use App\Models\Setting;
use App\Models\SmtpSetting;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DB;
use PayPal\Api\Agreement;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use URL;

class CronJobController extends Controller
{
	public function LicenseChecking()
	{
        set_time_limit(0);
        $date = new DateTime;
        $today = $date->format('Y-m-d');

        //Checking whose license expireDate is less than today date
		$users = License::select(['owner', 'expireDate'])
			->where('status', '!=', 'Expired')
			->where('expireDate', '<', $today)
			->get();

        foreach($users as $user) {
            $userID = $user->owner;
            $userObj = User::find($userID);

            if ($userObj) {
                //Finding user Type
                $userType = $userObj->userType;

                //Team Members for current User
                $teamMembersIDs = [];
	            $teamMembersIDs[] = $userID;

                if ($userType === 'Multi') {
                    $userManger = $userID;

                    $teamMembers = User::select('id')->where('manager', $userManger)->get();

                    foreach ($teamMembers as $teamMember) {
                        $teamMembersIDs[] = $teamMember->id;
                    }
                }

                //Setting License to be expired
                License::where('owner', '=', $userID)->update(['status' => 'Expired']);

                if (count($teamMembersIDs) > 0) {
                    //Setting user's accountStatus to be expired
                    User::whereIn('id', $teamMembersIDs)->update(['accountStatus' => 'LicenseExpired']);
                }

	            $userObj->updateTrialUserTrack(['type' => 'Expired', 'updated_at' => Carbon::now()]);
				
				//send email about expiration
				$u = $userObj;

				$fieldValues = [
					'FIRST_NAME'    => $u->firstName,
					'EXPIRY'     => \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $user->expireDate)->format('d M Y'),
					'SITE_NAME'     => Setting::get('siteName'),
				];

				$licenseType = $u->getUserLicenseStatus();

				if($licenseType == 'Trial') {
					$emailTemplate = AdminEmailTemplate::where('id', 'TRIALEXPIRED')->first();
				}
				else {
					$emailTemplate = AdminEmailTemplate::where('id', 'EXPIRED')->first();
				}

				$emailText = $emailTemplate->content;

				$mailSettings = CommonController::loadSMTPSettings("inbound");

				$emailInfo = [
					'from' => $mailSettings["from"]["address"],
					'fromName' => $mailSettings["from"]["name"],
					'replyTo' => $mailSettings["replyTo"]
				];

				$emailInfo['subject'] = $emailTemplate->subject;
				$emailInfo['to'] = $u->email;
				$attachments = [];

				CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
            }
        }
    }

    public function statsUpdate()
    {
        set_time_limit(0);
        $users = User::where("userType", "Multi")->active()->verified()->get();

        $date = new DateTime;

        foreach($users as $u) {
            $userID = $u->id;
            $statisticUpdates = $u->statisticUpdates;

            if($statisticUpdates == 'Daily') {
                $this->prepareAndSendStatsUpdate($userID, $statisticUpdates);
            }
            else if($statisticUpdates == 'Weekly') {
                $dayName = $date->format('D');
                if($dayName == "Mon") {
                    $this->prepareAndSendStatsUpdate($userID, $statisticUpdates);
                }
            }
            else if($statisticUpdates == 'Monthly') {
                $dayDate = $date->format('d');
                if($dayDate == "01") {
                    $this->prepareAndSendStatsUpdate($userID, $statisticUpdates);
                }
            }
        }
    }

    private function prepareAndSendStatsUpdate($userID, $statisticUpdates) {
        set_time_limit(0);
        $date = new DateTime;

        if ($statisticUpdates == "Daily") {
            $date->modify("-1 day");
        }
        else if($statisticUpdates == "Weekly") {
            $date->modify("-7 days");
        }
        else if ($statisticUpdates == "Monthly") {
            $date->modify("-1 month");
        }

        $dateStr = $date->format("Y-m-d H:i:s");
        $stats = "";

        $u = User::find($userID);
        $userFirstName = $u->firstName;
        $email = $u->email;

        $teamMembersIDs =  $u->getTeamMembersIDs();

        if (count($teamMembersIDs) > 0) {
            $callDetails = Lead::select(DB::raw('users.firstName, users.lastName, COUNT(*) AS totalCallMade, inleads.id'))
                ->from(DB::raw("(SELECT * FROM `leads` WHERE `leads`.`status` = 'Actioned' AND `leads`.`timeEdited` > '$dateStr') as inleads"))
                ->rightjoin('users', 'users.id', '=', 'inleads.lastActioner')
                ->whereIn('users.id', $teamMembersIDs)
	            //->where('users.userType', "!=", "Multi")
                ->groupBy('users.id')
                ->orderBy("users.firstName", "ASC")
                ->get();

            $interestedCallDetails = Lead::select(DB::raw('users.firstName, users.lastName, COUNT(*) AS totalCallMade, inleads.id'))
                ->from(DB::raw("(SELECT * FROM `leads` WHERE `leads`.`interested` = 'Interested' AND `leads`.`timeEdited` > '$dateStr') as inleads"))
                ->rightjoin('users', 'users.id', '=', 'inleads.lastActioner')
                ->whereIn('users.id', $teamMembersIDs)
	            //->where('users.userType', "!=", "Multi")
                ->groupBy('users.id')
                ->orderBy("users.firstName", "ASC")
                ->get();

            $notInterestedCallDetails =  Lead::select(DB::raw('users.firstName, users.lastName, COUNT(*) AS totalCallMade, inleads.id'))
                ->from(DB::raw("(SELECT * FROM `leads` WHERE `leads`.`interested` = 'NotInterested' AND `leads`.`timeEdited` > '$dateStr') as inleads"))
                ->rightjoin('users', 'users.id', '=', 'inleads.lastActioner')
                ->whereIn('users.id', $teamMembersIDs)
	            //->where('users.userType', "!=", "Multi")
                ->groupBy('users.id')
                ->orderBy("users.firstName", "ASC")
                ->get();

            $appointmentBooked =  Lead::select(DB::raw('users.firstName, users.lastName, COUNT(*) AS totalCallMade, inleads.id'))
                ->from(DB::raw("(SELECT * FROM `leads` WHERE `leads`.`bookAppointment` = 'Yes' AND `leads`.`timeEdited` > '$dateStr') as inleads"))
                ->rightjoin('users', 'users.id', '=', 'inleads.lastActioner')
                ->whereIn('users.id', $teamMembersIDs)
	            //->where('users.userType', "!=", "Multi")
                ->groupBy('users.id')
                ->orderBy("users.firstName", "ASC")
                ->get();

            for($i = 0; $i < count($callDetails); $i++) {
                $callDetail = $callDetails[$i];
                $interestedCallDetail = $interestedCallDetails[$i];
                $notInterestedCallDetail = $notInterestedCallDetails[$i];
                $appointmentBookedDetail = $appointmentBooked[$i];

                $stats .= "<b>$callDetail->firstName $callDetail->lastName</b><br>";
                if ($callDetails[$i]->id != NULL) {
                    $stats .= "Total Calls: $callDetail->totalCallMade<br>";
                }
                else {
                    $stats .= "Total Calls: 0 <br>";
                }
                if($interestedCallDetails[$i]->id != null) {
                    $stats .= "Total Positive : $interestedCallDetail->totalCallMade<br>";
                }
                else {
                    $stats .= "Total Positive : 0 <br>";
                }

                if ($notInterestedCallDetails[$i]->id != null) {
                    $stats .= "Total Negative : $notInterestedCallDetail->totalCallMade <br>";
                }
                else {
                    $stats .= "Total Negative : 0<br>";
                }

                if ($appointmentBooked[$i]->id != null) {
                    $stats .= "Total Appointments Booked: $appointmentBookedDetail->totalCallMade <br>";
                }
                else {
                    $stats .= "Total Appointments Booked: 0<br>";
                }

                $stats .= "<br>";
            }

            $totalCalls = Lead::whereIn('leads.lastActioner', $teamMembersIDs)
                ->where("leads.timeEdited", ">", $date)
                ->count();

            $totalInterested = Lead::where('interested', '=', 'Interested')
                ->whereIn('leads.lastActioner', $teamMembersIDs)
                ->where("leads.timeEdited", ">", $date)
                ->count();

            $totalNotInterested = Lead::where('interested', '=', 'NotInterested')
                ->whereIn('leads.lastActioner', $teamMembersIDs)
                ->where("leads.timeEdited", ">", $date)
                ->count();

            $totalAppointmentBooked = Lead::where('bookAppointment', '=', 'Yes')
                ->whereIn('leads.lastActioner', $teamMembersIDs)
                ->where("leads.timeEdited", ">", $date)
                ->count();

            $stats .= "<b>Over All</b> <br>";
            $stats .= "Total Calls: $totalCalls <br>";
            $stats .= "Total Positive: $totalInterested <br>";
            $stats .= "Total Negative : $totalNotInterested <br>";
            $stats .= "Total Appointment Booked: $totalAppointmentBooked <br>";

            $today = (new DateTime())->format("Y-m-d");

            //Sending CallBack Detail to Staff Member
            $newAccountTemplate = AdminEmailTemplate::where('id', 'STATS')->first();

            $fieldValues = [
                'DATE' => CommonController::formatDateForDisplay($today),
                'FIRST_NAME' => $userFirstName,
                'STATS' => $stats,
                'SITE_NAME' => Setting::get('siteName')
            ];

            $emailText = $newAccountTemplate->content;

	        $mailSettings = CommonController::loadSMTPSettings("inbound");

            $emailInfo = [
	            'from' => $mailSettings["from"]["address"],
	            'fromName' => $mailSettings["from"]["name"],
	            'replyTo' => $mailSettings["replyTo"],
                'subject' => $newAccountTemplate->subject,
                'to' => $email
            ];

            //$fieldValues['STATS'] = str_replace("\n", '<br/>', $fieldValues['STATS']);

	        if(sizeof($teamMembersIDs) > 10) {
		        foreach ($fieldValues as $key => $value) {
			        $emailText = str_replace('##' . $key . '##', $value, $emailText);
		        }

		        $pdfPath = storage_path() . '/attachments/stats/stats.pdf';
		        $pdf = App::make('dompdf.wrapper');
		        $pdf->loadHTML($emailText);
		        $pdf->save($pdfPath);

		        $attachments = ['/attachments/stats/stats.pdf'];
		        $emailText = 'Your stats are attached.';
		        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
	        }
	        else {
		        $attachments = [];
		        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
	        }
        }
    }

    public function exchangeRateScrap() {
        set_time_limit(0);
        $data = [];

        $ch = curl_init();
        $url = "http://openexchangerates.org/api/latest.json?app_id=68768307510d48d38939c0390591b2a1";

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        $output=curl_exec($ch);
        curl_close($ch);

        $output = json_decode($output, true);

        Setting::set('euroToDollar', $output['rates']['EUR']);
        Setting::set('gbpToDollar', $output['rates']['GBP']);
    }

    public function callBackAlert() {
        set_time_limit(0);
        $CALL_BACKS_URL = URL::route('user.leads.pendingcallbacks');

        $callBacks = CallBack::select('callbacks.id', 'callbacks.creator', 'callbacks.actioner', 'users.firstName',
            'callbacks.time', 'users.email', 'callbacks.emailSent', 'callbacks.leadID')
            ->join('users', 'users.id', '=', 'callbacks.actioner')
            ->where("emailSent", "False")
			->where('status', '!=', 'Completed')
            ->where(DB::raw("TIMESTAMPDIFF(MINUTE, NOW(), callbacks.time)"), "<", 30)
            ->get();

        foreach($callBacks as $callBack) {
            $user = User::find($callBack->creator);

            $time = $callBack->time;
            $callTime = (new DateTime($time))->format('g:i A');
            $callDate =  (new DateTime($time))->format('d-m-Y');

            $leadData = LeadCustomData::where('leadID', $callBack->leadID)->get();

            $detail = "Lead contact information and details are following:<br/>\n";

			foreach ($leadData as $data) {
				$detail .= $data->fieldName . ': ' . $data->value . "<br/>\n";
			}

            //Sending CallBack Detail to Staff Member
            $newAccountTemplate = AdminEmailTemplate::where('id', 'REMINDCALL')->first();

	        $fieldValues = [
                'FIRST_NAME'    => $callBack->firstName,
                'CALL_TIME'     => $callTime,
                'CALL_DATE'     => $callDate,
                'CALL_BACKS_URL'=> URL::route('user.leads.pendingcallbacks'),
                'SITE_NAME'     => Setting::get('siteName'),
                'DETAIL'        => $detail
            ];

            $emailText = $newAccountTemplate->content;

	        $lead = Lead::find($callBack->leadID);
	        $callBackUser = User::find($callBack->actioner);

	        $mailSettings = CommonController::loadSMTPSettings("inbound");

	        $generalInfo = [
		        'from' => $mailSettings["from"]["address"],
		        'fromName' => $mailSettings["from"]["name"],
		        'replyTo' => $mailSettings["replyTo"]
	        ];

	        $emailInfo = CommonController::setEmailConfiguration($lead, $user, $generalInfo);

	        $emailInfo['subject'] = $newAccountTemplate->subject;
	        $emailInfo['to'] = $callBackUser->email;

            $attachments = [];
            CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);

            $saveCallBack = CallBack::find($callBack->id);
            $saveCallBack->emailSent = "True";
            $saveCallBack->save();
        }
    }

    function sendLicenceExpiryEmails() {
        $reminderSetting = Setting::get('renewalRemainder');
        $today = \Carbon\Carbon::now()->format('Y-m-d');

        //get all users whose upcoming expiry date is within one month
        $users = User::join('licenses', 'licenses.owner', '=', 'users.id')
	        ->where('accountStatus', '!=', 'Blocked')
            ->select('users.*', 'licenses.expireDate')
	        ->get();

        foreach($users as $u){
            //print_r($u);die;
            //get difference from today to their expiry dates
            $expiry = \Carbon\Carbon::createFromFormat('Y-m-d', $u->expireDate);
            $difference = $expiry->diffInDays(\Carbon\Carbon::today());

            $fieldValues = [
                'FIRST_NAME'    => $u->firstName,
                'EXPIRY'     => \Carbon\Carbon::createFromFormat('Y-m-d', $u->expireDate)->format('d M Y'),
                'SITE_NAME'     => Setting::get('siteName'),
            ];

            //send mail for both users (expired and about to expired)
            if($u->expireDate < $today) {
	            $licenseType = $u->getUserLicenseStatus();

	            if($licenseType == 'Trial') {
		            $emailTemplate = AdminEmailTemplate::where('id', 'TRIALEXPIRED')->first();
	            }
	            else {
		            $emailTemplate = AdminEmailTemplate::where('id', 'EXPIRED')->first();
	            }

                $emailText = $emailTemplate->content;

                $mailSettings = CommonController::loadSMTPSettings("inbound");

                $emailInfo = [
                    'from' => $mailSettings["from"]["address"],
                    'fromName' => $mailSettings["from"]["name"],
                    'replyTo' => $mailSettings["replyTo"]
                ];

                $emailInfo['subject'] = $emailTemplate->subject;
                $emailInfo['to'] = $u->email;
                $attachments = [];

                if($reminderSetting == 'everyDay'){
                    CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                }
                elseif($reminderSetting == 'everyWeek'){
                    if($difference % 7 == 0){
                        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                    }
                }
                elseif($reminderSetting == 'everyMonth'){
                    if($difference % 30 == 0){
                        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                    }
                }
            }
            else if($difference <= 30) {
	            $licenseType = $u->getUserLicenseStatus();

	            if($licenseType == 'Trial') {
		            $emailTemplate = AdminEmailTemplate::where('id', 'TRIALTOEXPIRE')->first();
	            }
	            else {
		            $emailTemplate = AdminEmailTemplate::where('id', 'TOEXPIRE')->first();
	            }

                $emailText = $emailTemplate->content;

                $mailSettings = CommonController::loadSMTPSettings("inbound");

                $emailInfo = [
                    'from' => $mailSettings["from"]["address"],
                    'fromName' => $mailSettings["from"]["name"],
                    'replyTo' => $mailSettings["replyTo"]
                ];

                $emailInfo['subject'] = $emailTemplate->subject;
                $emailInfo['to'] = $u->email;
                $attachments = [];

                if($reminderSetting == 'everyDay'){
                    CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                }
                elseif($reminderSetting == 'everyWeek'){
                    if($difference == 30){
                        //send mail
                        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                    }
                    elseif($difference < 29 && $difference % 7 == 1){
                        //send mail
                        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                    }
                }
                elseif($reminderSetting == 'everyMonth'){
                    if($difference % 30 == 0 || $difference % 30 == 1){
                        //send mail
                        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                    }
                }
            }
        }
    }

	// Cron job for email credits (update daily/monthly email credits)
	function emailCredits() {
		$users = User::join('licenses', 'licenses.owner', '=', 'users.id')
		             ->join('licensetypes', 'licensetypes.id', '=','licenses.licenseType')
		             ->select('users.*', 'licensetypes.type')
		             ->whereIn('userType', ['Multi', 'Single'])->get();

		if(sizeof($users)) {
            /** @var User $user */
            foreach($users as $user) {
				if($user->type == 'Trial') {
					$trialUserLimit = Setting::get('trialUserLimit');
                    $limitBy = $trialUserLimit['limitBy'];
                    $emails = $trialUserLimit['emails'];
				}
				elseif($user->type == 'Paid') {
					if($user->userType == 'Multi') {
						$multiUserLimit = Setting::get('multiUserLimit');
						$limitBy = $multiUserLimit['limitBy'];
						$emails = $multiUserLimit['emails'];
					}
					elseif($user->userType == 'Single') {
						$singleUserLimit = Setting::get('singleUserLimit');
						$limitBy = $singleUserLimit['limitBy'];
						$emails = $singleUserLimit['emails'];
					}
				}

				//use custom limit if set
				if($user->mass_email_limit != '') {
					$emails = $user->mass_email_limit;
				}

				$userEmailCredit = $user->getUserEmailCredit();
				if(!$userEmailCredit) {
					// create email credit
					$user->createEmailCredit($emails);
				}
				else {
					// update email credit
					$lastUpdate = $userEmailCredit->updated_at;
					$day = Carbon::now()->format("d");

					if($limitBy == 'Day') {
                        $user->updateEmailCredit($emails);
					}
					elseif($limitBy == 'Month' && $day == "01") {
                        $user->updateEmailCredit($emails);
					}

                    $user->setPendingFromPostponeMassEmail();
				}
			}
		}
	}

	// Temporary script for crate default email template to existing users
	function insertTemplates() {
		// Check all team and single users if they have default email template

		$users = User::all();

		if(sizeof($users) > 0){
			foreach($users as $u){
				$templates = EmailTemplate::whereIn('name', ['Appointment booked', 'Follow Up Call'])
					->where('creator', $u->id)
					->get();

				if(sizeof($templates) > 0){
					if(sizeof($templates) == 1){
						//delete already exist template and create
						foreach($templates as $template){
							$template->delete();
						}

						CommonController::setEmailTemplate($u);
					}
					else if(sizeof($templates) > 2){
						//delete already exist template and create
						foreach($templates as $template){
							$template->delete();
						}

						CommonController::setEmailTemplate($u);
					}
				}
				else {
					CommonController::setEmailTemplate($u);
				}
			}
		}
	}

    // Temporary script for email credit
    function  emailCreditForExistingUsers() {
        $users = User::join('licenses', 'licenses.owner', '=', 'users.id')
            ->join('licensetypes', 'licensetypes.id', '=','licenses.licenseType')
            ->select('users.*', 'licensetypes.type')
            ->whereIn('userType', ['Multi', 'Single'])->get();

        foreach($users as $user) {
	        $insert = [];
	        $insert['user_id'] = $user->id;
	        $insert['created_at'] = Carbon::now();
	        $insert['updated_at'] = Carbon::now();

            if($user->type == 'Trial') {
	            $trialUserLimit = Setting::get('trialUserLimit');
	            $insert['email'] = $trialUserLimit['emails'];
            }
	        elseif($user->type == 'Paid') {
		        if($user->userType == 'Multi') {
			        $multiUserLimit = Setting::get('multiUserLimit');
			        $insert['email'] = $multiUserLimit['emails'];
		        }
		        elseif($user->userType == 'Single') {
			        $singleUserLimit = Setting::get('singleUserLimit');
			        $insert['email'] = $singleUserLimit['emails'];
		        }
	        }

	        DB::table('email_credit')->insert($insert);
        }
    }

	public function getTransactionForAgreement() {
		$startDate = Carbon::now()->subDays(5)->format('Y-m-d 00:00:00');
		$endDate = Carbon::now()->addDays(5)->format('Y-m-d 23:59:59');

		$apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));
		$params = array('start_date' => date('Y-m-d', strtotime('-10 days')), 'end_date' => date('Y-m-d', strtotime('+10 days')));

		// Get all transactions
		$transactions = App\Models\Transaction::from(
			DB::raw("
			(
				SELECT t.*
				FROM transactions t,
				(
					SELECT purchaser, MAX(time) max_time
					FROM transactions
					WHERE id LIKE 'I-%'
						AND nextBillingDate >= ?
						AND nextBillingDate <= ?
					GROUP BY purchaser
					ORDER BY max_time DESC
				) t_inner
				WHERE t.purchaser = t_inner.purchaser
					AND t.time = t_inner.max_time
			) as res
			")
			)
			->addBinding([$startDate, $endDate])
			->get();

		//->toSql();
		//\Log::error($transactions);
		//return;

		foreach($transactions as $transaction) {
			try {
				$paypalTransactions = \PayPal\Api\Agreement::searchTransactions($transaction->id, $params, $apiContext)->getAgreementTransactionList();
				foreach($paypalTransactions as $paypalTransaction) {
					$transactionID = $paypalTransaction->getTransactionId();
					$trans = App\Models\Transaction::find($transactionID);

					// If transaction is there then continue, else save this transaction in database and then continue
					if($trans) {
						continue;
					}
					else {
						$agreement = Agreement::get($transaction->id, $apiContext);
						$nextBillingDate = $agreement->getAgreementDetails()->next_billing_date;
						$nextBillingDateFormated = Carbon::parse($nextBillingDate)->format('Y-m-d H:i:s');

						$amount = $paypalTransaction->getAmount();
						$status = $paypalTransaction->getStatus();

						if(strtolower($status) == 'unclaimed' || strtolower($status) == 'complete' || strtolower($status) == 'completed' || strtolower($status) == 'claimed') {
							$trans = new App\Models\Transaction();
							$trans->id = $transactionID;
							$trans->type = 'Renew';
							$trans->time = Carbon::parse($paypalTransaction->getTimeStamp(), $paypalTransaction->getTimeZone());
							$trans->purchaser = $transaction->purchaser;
							$trans->amount = $amount->getValue();
							$trans->licenseType = $transaction->licenseType;
							$trans->plan_id = $transaction->plan_id;
							$trans->payer_id = $transaction->payer_id;
							$trans->state = $status;
							$trans->currency = $amount->getCurrency();
							$trans->nextBillingAmount = $amount->getValue();
							$trans->nextBillingDate = $nextBillingDateFormated;

							$trans->save();

							// Update licence details
							License::where('owner', '=', $transaction->purchaser)->update([
								'purchaseTime' => Carbon::now(),
								'expireDate' => $nextBillingDateFormated
							]);

							//update 'topmost' transaction nextBillingDate
							$transaction->nextBillingDate = $nextBillingDateFormated;
							$transaction->save();


							// send invoice to user
							CommonController::sendInvoiceEmailToUser($trans);
						}
						else {
							// TODO: Failed transaction

							$date = Carbon::parse($paypalTransaction->getTimeStamp(), $paypalTransaction->getTimeZone());
							$user = User::find($transaction->purchaser);
							$userPaymentInfo = $user->getPaymentInfo();

							//region update user status to expired
							$teamMembersIDs = $user->getTeamMembersIDs();

							//Setting License to be expired
							License::where('owner', '=', $user->id)->update(['status' => 'Expired']);

							//Setting user's accountStatus to be expired
							User::whereIn('id', $teamMembersIDs)->update(['accountStatus' => 'LicenseExpired']);
							//endregion

							//region send an email to multi user admin for failed transaction
							$template = AdminEmailTemplate::where('id', 'FAILTRANSACTION')->first();
							$fieldValues = [
								'DATE' => $date,
								'FIRST_NAME' => $user->firstName,
								'RECURRING_TYPE' => strtolower($userPaymentInfo->recurring_type),
								'SITE_NAME'  => Setting::get('siteName')
							];

							$emailText = $template->content;
							$mailSettings = CommonController::loadSMTPSettings("inbound");

							$emailInfo = [
								'from'=> $mailSettings["from"]["address"],
								'fromName' => $mailSettings["from"]["name"],
								'replyTo' => $mailSettings["replyTo"],
								'subject' => $template->subject,
								'to' => $user->email
							];

							$attachments = [];

							CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
							//endregion
						}
					}
				}
			}
			catch (\Exception $e) {
				\Log::error($e->getMessage());
			}
		}
	}

	public function setExistingUsers() {
		$users = User::where('accountCreationDate', '<=', Carbon::parse('2016-03-26'))->get();

		if(sizeof($users) > 0) {
			foreach($users as $user) {
				/**@var User $user **/
				$user->existing = 'Yes';
				$user->save();

				if($user->email == 'admin@froiden.com') {
					continue;
				}

				$licence = License::where('owner', $user->id)->first();

				if($licence) {
					$expiryDate = Carbon::parse($licence->expireDate);
					App\Models\Transaction::where('purchaser', $user->id)->update(
						['nextBillingDate' => $expiryDate, 'currency' => 'USD']
					);
				}
			}
		}
	}
}
