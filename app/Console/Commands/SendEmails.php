<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommonController;
use App\Models\MassEmail;
use App\Models\MassEmailTemplate;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use DB;
use DateTime;
use Illuminate\Console\Command;
use App\Models\MassEmailsLock;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'massmail:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending mass emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

	private function getLeadFromQueue($ignoreUserIds = []) 
	{
		DB::unprepared("SET autocommit=0");
		DB::unprepared("LOCK TABLES mass_emails_locks WRITE, mass_emails READ, users READ");

		$lead = null;

		try {
			$lead = MassEmail::where('mass_emails.status', 'Pending')
				 ->join('users', 'users.id', '=', 'mass_emails.user_id')
				 ->leftJoin('mass_emails_locks', 'mass_emails_locks.mass_email_id', '=', 'mass_emails.id')
				 ->whereNull('mass_emails_locks.mass_email_id')
				 ->where(function ($query) {
					 $query->where('mass_emails.sent', 'Now')
						   ->orWhere(function ($q) {
							   $q->where('mass_emails.sent', 'Later')
								 ->where('mass_emails.after_time', "<=", \DB::raw("NOW()"));
						   });
				 })
				 ->orderBy('mass_emails.id', 'asc')
				 ->select('mass_emails.*')
				 ;
			if(count($ignoreUserIds)) {
				$lead->whereNotIn('mass_emails.user_id', $ignoreUserIds);
			}
			//\Log::error($lead->toSql());
			$lead = $lead->first();

			if($lead) {
				//lock it
				$lock = new MassEmailsLock;
				$lock->created_at = new DateTime();
				$lock->mass_email_id = $lead->id;
				$lock->save();
			}
		} finally {
			DB::commit();
			DB::unprepared("UNLOCK TABLES");
			DB::unprepared("SET autocommit=1");
		}

		return $lead;
	}

	private function unlockMassEmail($mass_email_id) 
	{
		DB::unprepared("SET autocommit=0");
		DB::unprepared("LOCK TABLES mass_emails_locks WRITE");
		try 
		{
			MassEmailsLock::where('mass_email_id', '=', $mass_email_id)
				->delete();

		} finally {
			DB::commit();
			DB::unprepared("UNLOCK TABLES");
			DB::unprepared("SET autocommit=1");
		}
	}

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		//don't freeze on html errors
		libxml_use_internal_errors(true);

		$previousUsersQueued = [];


        do {
			$pid = str_pad(getmypid(), 6) . ' : ' . date('c') . ': ';

            try {
                $this->comment($pid . 'get new lead from queue');

				$lead = null;

				if(count($previousUsersQueued) == 0) {
					$lead = $this->getLeadFromQueue();
					if($lead) {
						//skip user on next loop
						$previousUsersQueued[] = $lead->user_id;
					}
				} else {
					//get lead from second user if possible
					$lead = $this->getLeadFromQueue($previousUsersQueued);
					if($lead) {
						//skip user on next loop
						$previousUsersQueued[] = $lead->user_id;
					} else {
						$previousUsersQueued = [];
						$this->comment($pid . 'no leads from other users, restart');
						continue;
					}
				}

                if (!$lead) {
                    $this->comment($pid . 'No data to be processed');
                    sleep(10);
                    continue;
                }

                $user = User::find($lead->user_id);
                $template = MassEmailTemplate::find($lead->template_id);

                $fromName = $template->from_name;
                $type = $template->type;

                // Check if this emails is unsubscribed
                if ($type == 'custom') {
                    $isUnsubscribe = $lead->checkCustomUnsubscribe($user->getManager());
                }
                elseif ($type == 'campaign') {
                    $isUnsubscribe = $lead->checkCampaignUnsubscribe($template->campaign_id);
                }

                //\DB::beginTransaction();

                if ($isUnsubscribe) {
                    $lead->setStatus('Unsubscribed');
					$this->unlockMassEmail($lead->id);
                    $this->comment($pid . 'Lead is unsubscribed');
                    continue;
                }

                //load mail config
                $this->comment($pid . 'loading config...');
                $configSettings = CommonController::loadMassMailServerSetting($user, $fromName, $template);

                $this->comment($pid . "Email Settings------ ".$configSettings['settingType']);
                $this->comment($pid . 'Check email credits...');
                if ($configSettings['settingType'] == 'superadmin') {
                    // check user email credit
                    $emailCredit = $user->getTeamEmailCredit();
                    $emails = $emailCredit->email;

                    if ($emails <= 0) {
                        // set status postpone
                        $lead->setStatus('Postpone');
						$this->unlockMassEmail($lead->id);
						$this->comment($pid . 'Not enough credits');
                        continue;
                    }
                }

                //prepare email variables
                $this->comment($pid . 'Preparing email variables...');
                $templateText = $template->content;

                $dom = new \DOMDocument();
                $dom->loadHTML($templateText);

                foreach ($dom->getElementsByTagName('a') as $link) {
                    $href = trim($link->getAttribute('href'));
                    $modifiedLink = \Config::get('app.url') . '/email/redirect/' . $lead->id . '?u=' . urlencode($href);

                    $link->setAttribute('href', $modifiedLink);
                }

                $content = $dom->saveHTML();


				$emailInfo = [
					'from'    => $template->from_email != '' ? $template->from_email :  $configSettings['fromMail'], 
					'fromName' => $fromName,
					'replyTo' => $template->reply_to, 
					'subject' => $template->subject, 
					'to' => $lead->email
				];

                $this->comment($pid . 'Email : ' . $emailInfo['to']);

                //prepare form fields
                $this->comment($pid . 'preparing form fields...');

                if ($type == 'custom') {
                    $leadData = $lead->lead_data;
                    if (isset($leadData['first_name'])) {
                        $fieldValues['First Name'] = $leadData['first_name'];
                    }

                    if (isset($leadData['last_name'])) {
                        $fieldValues['Last Name'] = $leadData['last_name'];
                    }

                    if (isset($leadData['last_name'])) {
                        $fieldValues['Last Name'] = $leadData['last_name'];
                    }

                    if (isset($leadData['company_name'])) {
                        $fieldValues['Company Name'] = $leadData['company_name'];
                    }

                    if (isset($leadData['email'])) {
                        $fieldValues['Email'] = $leadData['email'];
                    }
                }
                elseif ($type == 'campaign') {
                    $fieldValues = $lead->lead_data;
                }

                // Get attachments
                $this->comment($pid . 'Getting Attachments...');
                $attachments = $template->getEmailAttachmentsToBeSend();

                $this->comment($pid . 'Prepare for pixel tracking...');
                $link = \Config::get('app.url') . '/email/open/' . $lead->id . '.png';

                $unsubscribeLink = \Config::get('app.url') . '/email/unsubscribe/' . $lead->id;

				$emailVal = [
					'emailText' => $content, 
					'link' => $link, 
					'unsubscribeLink' => $unsubscribeLink,
					'showSanityOsSignature' => $configSettings['settingType'] == 'superadmin',
				];

				$unsubscribeHeader = "<mailto:{$configSettings['fromMail']}?subject=unsubscribe>, <{$unsubscribeLink}?confirm=true>";
				
                $this->comment($pid . 'Sending email...');
                $result = $this->sendMail($emailInfo, $emailVal, $fieldValues, $attachments, $unsubscribeHeader);
                $status = $result['status'];

                $this->comment($pid . "Mail Send: " . $status);

                if ($status == 'success') {
                    $this->comment($pid . 'change status');
                    $lead->setStatus('sent');
                    $template->addEmailCredit();
                    $user->reduceEmailCredit($configSettings['settingType']);
                }
                else {
                    $lead->setStatus('Failed');
                    $lead->setFailReason($result['fail_reason']);
					$this->unlockMassEmail($lead->id);
					$this->comment($pid . 'sending failed reason : ' . $result['fail_reason']);
                    continue;
                }

                if ($type == 'campaign') {
                    // create call history
                    $newLead = $lead->getLeadFromEmail($template->campaign_id);
                    $callID = $newLead->createLeadCallHistory($lead->user_id, true);
                    \DB::table('call_history')->where('id', $callID)->update([
                        'mass_email_id' => $lead->id,
                        'mass_email_template_id' => $template->id,
                        'notes' => 'Mass mail, '.$status
                    ]);
                }

				$this->unlockMassEmail($lead->id);

                //\DB::commit();
            }
            catch(Exception $e) {
                \Log::error("Error sending mail: " . $e->getMessage());
                $this->comment($pid . "failed: ". $e->getMessage());
                //\DB::rollBack();
            }
        } while(true);
    }

    function sendMail($emailInfo, $emailVal, $fieldValues, $attachments, $unsubscribeHeader,  $throw = false) {
        try {
            foreach ($fieldValues as $key => $value) {
                $emailText = $emailVal['emailText'];
                $emailText = str_replace('##' . $key . '##', $value, $emailText);
                $emailVal['emailText'] = $emailText;

                $emailInfo['subject'] = str_replace('##' . $key . '##', $value, $emailInfo['subject']);
            }

            \Mail::send('emails.massemail', $emailVal, function ($message) use ($attachments, $emailInfo, $throw, $unsubscribeHeader) {
                try {
                    if (!empty($emailInfo["from"])) {
                        $message->from($emailInfo["from"], $emailInfo["fromName"]);
                    }

					$swiftMessage = $message->getSwiftMessage();
					$headers = $swiftMessage->getHeaders();
					$headers->addTextHeader('List-Unsubscribe', $unsubscribeHeader);
					$headers->addTextHeader('Precedence', 'bulk');

                    $message->to($emailInfo['to'])
                            ->subject($emailInfo['subject'])
                            ->replyTo($emailInfo["replyTo"]);

                    foreach ($attachments as $attachment) {
                        $arr = explode('/', $attachment);
                        $message->attach(storage_path()  . $attachment, ['as' => $arr[ count($arr) - 1 ]]);
                    }

                }
                catch (Exception $e) {
                    \Log::error("Error sending mail: " . $e->getMessage() . "\nData: ".json_encode($emailInfo));
                    if ($throw) {
                        throw $e;
                    }
                    return ['status' => 'fail', 'fail_reason' => $e->getMessage()];
                }
            });
        }
        catch (Exception $e) {
            \Log::error("Error sending mail: " . $e->getMessage());
            if ($throw) {
                throw $e;
            }
            return ['status' => 'fail', 'fail_reason' => $e->getMessage()];
        }

        return ['status' => 'success'];
    }
}
