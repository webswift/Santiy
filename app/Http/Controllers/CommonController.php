<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminEmailTemplate;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\License;
use App\Models\LicenseType;
use App\Models\Transaction;
use App\Models\PostalCode;
use App\Models\Setting;
use App\Models\SmtpSetting;
use App\Models\User;
use Carbon\Carbon;
use Config;
use DateTime;
use Exception;
use Illuminate\Mail\TransportManager;
use Log;
use Mail;
use PayPal\Api\Plan;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use App\Jobs\SendCustomEmail;

class CommonController extends Controller {

	public static function formatDateForDisplay($date) {
		$dateTime = new DateTime($date);

		return $dateTime->format("d-M-Y H:i A");
	}

	public static function sendCustomEmailAsync($view, $data, $callback, $finallyCallback = null) 
	{
		$job = new SendCustomEmail($view, $data, $callback, $finallyCallback);
		app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
	}

	public static function prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments, $throw = false, $async = false, $finallyCallback = null) {
		try {
			foreach ($fieldValues as $key => $value) {
				$emailText = str_replace('##' . $key . '##', $value, $emailText);
				$emailInfo['subject'] = str_replace('##' . $key . '##', $value, $emailInfo['subject']);
			}

			$emailText = preg_replace('/##([A-Z_a-z])+##/', ' ', $emailText);
			$emailInfo['subject'] = preg_replace('/##([A-Z_a-z])+##/', ' ', $emailInfo['subject']);

			if($async) {
				$throw = false;
			}

			$callback = function ($message) use ($attachments, $emailInfo, $throw) {
				try {
					if (!empty($emailInfo["from"])) {
						$message->from($emailInfo["from"], $emailInfo["fromName"]);
					}
					else {
						$message->from("contact@sanityos.com", Setting::get('siteName'));
					}

					$message->to($emailInfo['to'])
						->subject($emailInfo['subject'])
						->replyTo($emailInfo["replyTo"]);

					if(isset($emailInfo['bccEmail']) && $emailInfo['bccEmail'] != '') {
						$bccEmails = explode(',', $emailInfo['bccEmail']);
						foreach($bccEmails as $bccEmail) {
							$message->bcc($bccEmail);
						}
					}

					if(isset($emailInfo['ccEmail']) && $emailInfo['ccEmail'] != '') {
						$ccEmails = explode(',', $emailInfo['ccEmail']);
						foreach($ccEmails as $ccEmail) {
							$message->cc($ccEmail);
						}
					}

					foreach ($attachments as $attachment) {
						$arr = explode('/', $attachment);
						/*$message->attach(storage_path() . '/attachments/' . $attachment, ['as' => $arr[ count($arr) -
							1 ]]);*/
						$message->attach(storage_path()  . $attachment, ['as' => $arr[ count($arr) -
						1 ]]);
					}

					return ['status' => 'success', 'message' => 'sent'];
				}
				catch (Exception $e) {
					Log::error("Error sending mail: " . $e->getMessage() . "\nData: ".json_encode($emailInfo));
					if ($throw) {
						throw $e;
					}

					return ['status' => 'fail', 'message' => $e->getMessage()];
				}
			};

			if($async) {
				self::sendCustomEmailAsync('emails.email', ['emailText' => $emailText], $callback, $finallyCallback);
			} else {
				Mail::send('emails.email', ['emailText' => $emailText], $callback);
			}
		}
		catch (Exception $e) {
			Log::error("Error sending mail: " . $e->getMessage());
			if ($throw) {
				throw $e;
			}

			return ['status' => 'fail', 'message' => $e->getMessage()];
		} finally {
			if(!$async && $finallyCallback) {
				$finallyCallback();
			}
		}
	}

	public static function getLatitudeAndLongitude($postalCode, $countryCode) {
		$points = [];

		$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])//->where('countryCode', $countryCode)
			->where('postalCode', $postalCode)
			->first();

		if ($postalCodeDetails) {
			$points['latitude'] = $postalCodeDetails->latitude;
			$points['longitude'] = $postalCodeDetails->longitude;
			$points['countryCode'] = $postalCodeDetails->countryCode;
		}
		else {
			$ch = curl_init();
			$url = "http://maps.googleapis.com/maps/api/geocode/json?address={$postalCode},%20{$countryCode}";

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$output = curl_exec($ch);
			curl_close($ch);

			$output = json_decode($output, true);
			if (count($output['results']) == 0) {
				throw new Exception("Invalid postal code. Please check and try again");
			}

			foreach ($output['results'][0]['address_components'] as $component) {
				if (in_array("country", $component["types"])) {
					$points['countryCode'] = $component["short_name"];
				}
				else {
					throw new Exception("Invalid postal code. Please check and try again");
				}
			}
			// $points['countryCode'] = $output['results'][0]['address_components'][3]['short_name'];
			$points['latitude'] = $output['results'][0]['geometry']['location']['lat'];
			$points['longitude'] = $output['results'][0]['geometry']['location']['lng'];

			$newPostalCode = new PostalCode;
			$newPostalCode->postalCode = $postalCode;
			$newPostalCode->countryCode = $points['countryCode'];
			$newPostalCode->latitude = $points['latitude'];
			$newPostalCode->longitude = $points['longitude'];
			$newPostalCode->accuracy = 0;
			$newPostalCode->save();

		}

		return $points;
	}

	public static function settings() {
		$rates = CommonController::gbpAndEuroCurrency();

		$gbpToDollar = $rates['gbpRate'];
		$euroToDollar = $rates['euroRate'];

		Setting::set('adminEmail', '');
		Setting::set('paypalID', '');
		Setting::set('apiSignature', '');
		Setting::set('supportEmail', '');
		Setting::set('defaultCurrency', '');
		Setting::set('baseCurrency', '');
		Setting::set('trackingCode', '');
		Setting::set('renewalRemainder', '');
		Setting::set('industry', 'Computer Industry');
		Setting::set('metaKeywords', '');
		Setting::set('metaDescription', '');
		Setting::set('gbpToDollar', $gbpToDollar);
		Setting::set('euroToDollar', $euroToDollar);
	}

	public static function gbpAndEuroCurrency() {
		$data = [];

		$ch = curl_init();
		$url = "http://openexchangerates.org/api/latest.json?app_id=68768307510d48d38939c0390591b2a1";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($ch);
		curl_close($ch);

		$output = json_decode($output, true);

		$data['euroRate'] = $output['rates']['EUR'];
		$data['gbpRate'] = $output['rates']['GBP'];

		return $data;
	}

	public static function getTeamMemberIDs($user) {

		$teamMembersIDs = [];

		if ($user->userType === 'Team' || $user->userType === 'Multi') {
			if ($user->userType === 'Team') {
				$userManger = $user->manager;
			}
			else {
				$userManger = $user->id;
			}

			$teamMembersIDs[] = $userManger;

			$teamMembers = User::select(['users.firstName', 'users.id'])
				->where('manager', $userManger)
				->get();

			foreach ($teamMembers as $teamMember) {
				$teamMembersIDs[] = $teamMember->id;
			}
		}
		else if ($user->userType === 'Single') {
			$teamMembersIDs[] = $user->id;
		}

		return $teamMembersIDs;
	}

	public static function getCampaignMembers($campaignID) {
		return CampaignMember::where("campaignID", $campaignID)->lists("userID")->all();
	}


	/* return iCal format of date
	 * FORM #2: DATE WITH UTC TIME
	 * The date with UTC time, or absolute time, is identified by a LATIN CAPITAL LETTER Z suffix character (US-ASCII decimal 90), the UTC designator, appended to the time value. For example, the following represents January 19, 1998, at 0700 UTC:
	 *
	 * DTSTART:19980119T070000Z
	 *
	 * @input $date - timestamp, in format 'Ymd\THis'
	 */
	public static function convertLocalToUTC($timestamp) 
	{
		$date = DateTime::createFromFormat('Ymd\THis', $timestamp);

        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format('Ymd\THis\Z');
	}

	public static function getTimeInUserTimeZone($date, $user = null) 
	{
		$dateTime = new DateTime();

		$d = date('d', $date);
		$m = date('m', $date);
		$y = date('Y', $date);
		$h = date('H', $date);
		$i = date('i', $date);
		$s = date('s', $date);

		$dateTime->setDate($y, $m, $d);
		$dateTime->setTime($h, $i, $s);

		/*if ($user) {
			$timezone = explode("=", $user->timeZone)[0];
			$dateTime->setTimezone(new DateTimeZone(str_replace(':', '', $timezone)));
		}
		else {
			$dateTime->setTimezone(new DateTimeZone('UTC'));
		}*/

		//$dateTime->setTimezone(new DateTimeZone('UTC'));

		return $dateTime->format('Ymd\THis');
	}
	//
	//public static function timezoneOffsetToTimezone($timezone) {
	//	$timezone= str_replace(":", ".", $timezone);
	//	$offset = preg_replace('/[^0-9.]/', '', $timezone) * 3600;
	//
	//	//$offset *= 3600; // convert hour offset to seconds
	//	$abbrarray = timezone_abbreviations_list();
	//	foreach ($abbrarray as $abbr) {
	//		foreach ($abbr as $city) {
	//			if ($city['offset'] == $offset) {
	//				return $city['timezone_id'];
	//			}
	//		}
	//	}
	//
	//	return false;
	//}

	public static function getTimeFromMillisecond($ms){
		$input = floor($ms / 1000);

		$seconds = $input % 60;
		$input = floor($input / 60);

		$minutes = $input % 60;

		return $minutes . ' mins ' . $minutes . ' s';
	}

	public static function getFieldVariations($fieldName){
		return [strtolower($fieldName), str_replace(' ', '', $fieldName), $fieldName];
	}

	public static function formatBytes($bytes) {
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 1) . ' GB';
		}
		elseif ($bytes >= 1048576) {
			$bytes = number_format($bytes / 1048576, 1) . ' MB';
		}
		elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 1) . ' KB';
		}
		elseif ($bytes > 1) {
			$bytes = $bytes . ' bytes';
		}
		elseif ($bytes == 1) {
			$bytes = $bytes . ' byte';
		}
		else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	public static function getIconForFileType($value, $type){

		if($type == 'path'){
			$mimeType = CommonController::getMimeType($value);
			if($mimeType == 'application/vnd.ms-office') {
				switch(pathinfo($value,  PATHINFO_EXTENSION)) {
					case 'xls' : 
						$mimeType = 'application/vnd.ms-excel';
						break;
					case 'wrd' : 
						$mimeType = 'application/msword';
						break;
					case 'ppt' : 
					case 'pptx' : 
						$mimeType = 'application/vnd.ms-powerpoint';
						break;
				}
			}
		}
		else{
			$mimeType = $value;
		}

		$pdf = ['application/pdf'];
		$zip = ['application/zip', 'application/x-compressed-zip', 'application/x-zip-compressed'];
		$word = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.wordprocessingml.template'];
		$image = ['image/gif', 'image/jpeg', 'image/png'];
		$powerPoint = ['application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'application/vnd.ms-powerpoint',
						'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
		$excel = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
		$txt = ['text/plain'];

		if(in_array($mimeType, $pdf)){
			return 'fa fa-file-pdf-o';
		}

		if(in_array($mimeType, $zip)){
			return 'fa fa-file-archive-o';
		}

		if(in_array($mimeType, $word)){
			return 'fa fa-file-word-o';
		}

		if(in_array($mimeType, $image)){
			return 'fa fa-file-image-o';
		}

		if(in_array($mimeType, $powerPoint)){
			return 'fa fa-file-powerpoint-o';
		}

		if(in_array($mimeType, $excel)){
			return 'fa fa-file-excel-o';
		}

		if(in_array($mimeType, $txt)){
			return 'icon-file-text';
		}
	}


	public static function getMimeType($path){
		return \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser::getInstance()->guess($path);
	}

	public static function getAndSetUserSMTPSettings($user, $emailInfo){

		$settings = SmtpSetting::where('userID', '=', $user->id)->first();

		if ($settings) {
			Config::set("mail.host", $settings->host);
			Config::set("mail.port", $settings->port);
			Config::set("mail.username", $settings->userName);
			Config::set("mail.password", $settings->password);
			Config::set("mail.from.address", $settings->fromEmail);
			Config::set("mail.from.name", $user->firstName . " " . $user->lastName);
			Config::set("mail.driver", 'smtp');
			Config::set("mail.encryption", $settings->security != 'No' ? $settings->security : null);

			if($emailInfo){
				return [
					'emailInfo' => ['from' => $settings->fromEmail, 'replyTo' => $settings->replyToEmail, 'fromName' => $user->firstName . " " . $user->lastName],
					'settings' => true
				];
			}
			else{
				return ['settings' => true];
			}
		}
		else{
			return ['settings' => false];
		}
	}

	public static function setEmailConfiguration($lead, $user, $generalEmailInfo, $forceFromAddressFromCampaign = false){
		//check for campaign smtp settings
		$campaign = Campaign::find($lead->campaignID);
		$campaignSetting = $campaign->getCampaignEmailSettings();

		//if we find campaign email setting

		if(sizeof($campaignSetting) > 0){
			//then set from email and reply to email
			$emailInfo = ['from' => $campaignSetting->fromEmail, 'replyTo'  => $campaignSetting->replyToEmail, 'fromName' => $user->firstName . " " . $user->lastName];

			//now check for campaign smtp setting
			if($campaignSetting->smtpSetting == 'Yes'){
				//if smtp exists then set config
				Config::set("mail.host", $campaignSetting->host);
				Config::set("mail.port", $campaignSetting->port);
				Config::set("mail.username", $campaignSetting->username);
				Config::set("mail.password", $campaignSetting->password);
				Config::set("mail.from.address", $campaignSetting->fromEmail);
				Config::set("mail.from.name", $user->firstName . " " . $user->lastName);
				Config::set("mail.driver", 'smtp');
				Config::set("mail.encryption", $campaignSetting->security != 'No' ? $campaignSetting->security : null);
			} else{
				//if smtp not exists then chekc for user smtp setting
				$userSMTP = CommonController::getAndSetUserSMTPSettings($user, false);
			}
		}
		else{
			//if campaign smtp settings not found then check for user smtp setting
			$userSMTP = CommonController::getAndSetUserSMTPSettings($user, true);
		}

		if($forceFromAddressFromCampaign && sizeof($campaignSetting) > 0) {
			//we have from/replyTo from campaign and forced to use them
		} else if(isset($userSMTP)){
			//update from/replyTo from general user settings
			if($userSMTP['settings'] == false){
				$emailInfo = $generalEmailInfo;
			}
			else if(sizeof($userSMTP['emailInfo']) > 0){
				$emailInfo = $userSMTP['emailInfo'];
			}
		}

		return $emailInfo;
	}

	public static function getAdminUserInfo(){
		return User::where('email', 'admin@sanityos.com')->first();
	}

	public static function randColorArray() {
		/*
		return array(
			"#5bc0eb",
		    "#fa7921",
		    "#114b5f",
		    "#028090",
		    "#456990",
		    "#50514f",
		    "#247ba0",
		    "#70c1b3",
		    "#9bc53d",
		    "#e55934",
		);
		 */
		return [
			"#7fc97f",
			"#beaed4",
			"#fdc086",
			"#ffff99",
			"#386cb0",
			"#e5d8bd",
			"#bf5b17",
			"#1b9e77",
			"#d95f02",
			"#7570b3",
			"#e7298a",
			"#66a61e",
			"#e6ab02",
			"#a6761d",
			"#a6cee3",
			"#1f78b4",
			"#b2df8a",
			"#33a02c",
			"#fb9a99",
			"#e31a1c",
			"#fdbf6f",
			"#66c2a5",
			"#fc8d62",
			"#8da0cb",
			"#e78ac3",
			"#a6d854",
			"#ffd92f",
			"#ffffb3",
		];
	}

	public static function randColor() {
		return '#' . str_pad(dechex(mt_rand(0x003333, 0x006666)), 6, '0', STR_PAD_LEFT);
	}

	public static function loadSMTPSettings($type = "inbound") {
		if ($type == "inbound") {
			$settings = Setting::get("inboundMail");
			Config::set("mail.from.name", $settings["from"]["name"]);
		}
		else {
			$settings = Setting::get("outboundMail");
		}


		Config::set("mail.host", $settings["host"]);
		Config::set("mail.port", $settings["port"]);
		Config::set("mail.encryption", ($settings["requireSSL"] == "on")?"ssl":"");
		Config::set("mail.username", $settings["username"]);
		Config::set("mail.password", $settings["password"]);
		Config::set("mail.from.address", $settings["from"]["address"]);
		Config::set("mail.driver", 'smtp');


		return $settings;
	}

	public static function getInterestedType($interested){
		if($interested == 'Interested'){
			return 'Positive';
		}
		elseif($interested == 'NotInterested'){
			return 'Negative';
		}
		else{
			return $interested;
		}
	}

	public static function setEmailTemplate($user) {
		$followUpTemplate = AdminEmailTemplate::where('id', 'LEADFOLLOWUP')->first();
		$appointmentTemplate = AdminEmailTemplate::where('id', 'LEADAPPOINTMENT')->first();

		//follow up email template
		$followUp = new EmailTemplate();
		$followUp->name = 'Follow Up Call';
		$followUp->templateText = $followUpTemplate->content;
		$followUp->subject = "Follow Up Call";
		$followUp->creator = $user->id;
		$followUp->owner = $user->id;
		$followUp->timeCreated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
		$followUp->status = 'Disable';
		$followUp->save();

		//appointment booked
		$appointment = new EmailTemplate();
		$appointment->name = 'Appointment booked';
		$appointment->templateText = $appointmentTemplate->content;
		$appointment->subject = "Appointment booked";
		$appointment->creator = $user->id;
		$appointment->owner = $user->id;
		$appointment->timeCreated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
		$appointment->status = 'Disable';
		$appointment->save();
	}

	public static function getTotalUnActionedLandingLeads($user){
		$campaignLists = $user->getActiveCampaigns();
		$userCampaigns = $campaignLists->lists('id')->all();

		return Lead::where('status', 'Unactioned')
			->whereIn('campaignID', $userCampaigns)
			->where('leadType', 'landing')->count();
	}

	public static function loadMassMailServerSetting($user, $fromName, $template) {
		$settingType = $template->mail_setting_type;
		$settings = $template->mail_settings;
		$templateFromEmail = $template->from_email;

		if($settings != null && $settings != '') {
			$settings = json_decode($settings, true);
			
			$fromMail = $templateFromEmail != '' ? $templateFromEmail : $settings['from_mail'];

			if(in_array($settingType, ['User', 'sparkpost'])) {
				$host = $settings['host'];
				$port = $settings['port'];
				$username = $settings['username'];
				$password = $settings['password'];

				$settingType = 'user';

				// Load config
				Config::set("mail.driver", 'smtp');
				Config::set("mail.from.address", $fromMail);
				Config::set("mail.from.name", $fromName);
				Config::set("mail.host", $host);
				Config::set("mail.port", $port);
				Config::set("mail.username", $username);
				Config::set("mail.password", $password);

				if($settings['security'] == 'Yes') {
					Config::set("mail.encryption", 'tls');
				} else {
					Config::set("mail.encryption", $settings['security'] != 'No' ? $settings['security'] : null);
				}
			} else if($settingType == 'mandrill') {
				$password = $settings['password'];
				$settingType = 'user';
				// Load config
				Config::set("mail.driver", 'mandrill');
				Config::set("mail.from.address", $fromMail);
				Config::set("mail.from.name", $fromName);
				Config::set("services.mandrill.secret", $password);
			} else {
				$accessKey = $settings['accessKey'];
				$secretKey = $settings['secretKey'];
				$region = $settings['region'];

				$settingType = 'superadmin';

				// Load config
				Config::set("mail.driver", 'ses');
				Config::set("mail.from.address", $fromMail);
				Config::set("mail.from.name", $fromName);
				Config::set("services.ses.key", $accessKey);
				Config::set("services.ses.secret", $secretKey);
				Config::set("services.ses.region", $region);
			}
		}
		else {
			//unused if db is correct, fallback
			$serverSetting = $user->getTeamServerSetting();

			if($serverSetting && $serverSetting->status == 'Enable') {
				$host = $serverSetting->host;
				$port = $serverSetting->port;
				$username = $serverSetting->username;
				$password = $serverSetting->password;
				$fromMail = $serverSetting->from_mail;

				$settingType = 'user';

				if(in_array($serverSetting->provider, ['smtp', 'sparkpost'])) {
					// Load config
					Config::set("mail.driver", 'smtp');
					Config::set("mail.from.address", $fromMail);
					Config::set("mail.from.name", $fromName);
					Config::set("mail.host", $host);
					Config::set("mail.port", $port);
					Config::set("mail.username", $username);
					Config::set("mail.password", $password);

					if($serverSetting->security == 'Yes') {
						Config::set("mail.encryption", 'tls');
					} else {
						Config::set("mail.encryption", $serverSetting->security != 'No' ? $serverSetting->security : null);
					}
				} else if ($serverSetting->provider == 'mandrill') {
					Config::set("mail.driver", 'mandrill');
					Config::set("mail.from.address", $fromMail);
					Config::set("mail.from.name", $fromName);
					Config::set("services.mandrill.secret", $password);
				}
			}
			else {
				$serverSetting = Setting::get('massMailServer');
				$accessKey = $serverSetting['accessKey'];
				$secretKey = $serverSetting['secretKey'];
				$region = $serverSetting['region'];
				$fromMail = $serverSetting['fromEmail'];

				$settingType = 'superadmin';

				// Load config
				Config::set("mail.driver", 'ses');
				Config::set("mail.from.address", $fromMail);
				Config::set("mail.from.name", $fromName);
				Config::set("services.ses.key", $accessKey);
				Config::set("services.ses.secret", $secretKey);
				Config::set("services.ses.region", $region);
			}
		}

		self::reloadMailConfig();

		return ['fromMail' => $fromMail, 'settingType' => $settingType];
	}

	//updates swift mailer
	public static function reloadMailConfig() 
	{
		$app = \App::getInstance();

		$app['swift.transport'] = $app->share(function ($app) {
			return new TransportManager($app);
		});

		$mailer = new \Swift_Mailer($app['swift.transport']->driver());
		if(env('SWIFT_MAIL_LOG', false)) {
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin(new \Oprudkyi\LaravelMailLogger\MailLoggerPlugin));
		}
		Mail::setSwiftMailer($mailer);
	}

	public function test_sendInvoiceEmailToUser() {
		$trans = Transaction::where('purchaser', '=', $this->user->id)
			->orderBy('time', 'desc')
			->first()
			;
		if(!$trans) {
			$trans = Transaction::where('purchaser', '=', 85)
				->orderBy('time', 'desc')
				->first()
				;
		}
		CommonController::sendInvoiceEmailToUser($trans);
		return "ok";
	}

	public static function sendInvoiceEmailToUser($trans) {
		$invoiceNumber = Setting::get('invoiceNumber');
		$invoiceNumber++;
		Setting::set("invoiceNumber", $invoiceNumber);

		$currency = $trans->currency;
		$amount = $trans->amount;

		if($currency == 'USD') { $currencySign = '$'; }
		elseif($currency == 'EUR') { $currencySign = '€'; }
		elseif($currency == 'GBP') { $currencySign = '£'; }

		$invoiceTotal = $currencySign . $amount;
		$invoiceDiscount = $currencySign . 0;
		$total = $currencySign . $amount;

		$user = User::find($trans->purchaser);
		$userPaymentInfo = $user->getPaymentInfo();
		$to = $userPaymentInfo->billing_email;

		if($userPaymentInfo->recurring_type == 'Monthly') { $month = 1; }
		else { $month = 12; }

		$licenseTypeDetail = LicenseType::find($trans->licenseType);


		//Generating invoice and sending it to user
		$invoice['customerName'] = $user->firstName . ' ' . $user->lastName;
		$invoice['companyName'] = $user->companyName;
		$invoice['email'] = $to;
		$invoice['address'] = $user->address;
		$invoice['country'] = $user->country;
		$invoice['licenseName'] = $licenseTypeDetail->name;
		$invoice['licenseMonth'] = $month;
		$invoice['price'] = $invoiceTotal;
		$invoice['discount'] = $invoiceDiscount;
		$invoice['total'] = $total;
		$invoice['invoiceDate'] = Carbon::parse($trans->time)->format('d M Y');
		$invoice['invoiceNumber'] = $invoiceNumber;
		
		$license = License::where('owner', $trans->purchaser)->first();
		$invoice['quantity'] = $license->licenseVolume + 1;

		set_time_limit(0);
		$pdfPath = storage_path() . '/attachments/invoice/invoice_' . $invoiceNumber . '.pdf';
		$pdf = \App::make('dompdf.wrapper');
		$pdf->loadView("site.invoice.invoice", $invoice);
		$pdf->save($pdfPath);

		//Sending Invoice Email with Pdf  to user
		$template = AdminEmailTemplate::where('id', 'INVOICE')->first();
		$fieldValues = ['FIRST_NAME' => $user->firstName, 'LAST_NAME' => $user->lastName, 'SITE_NAME'  => Setting::get('siteName')];
		$emailText = $template->content;

		$mailSettings = CommonController::loadSMTPSettings("inbound");

		$emailInfo = [
			'from'=> $mailSettings["from"]["address"], 
			'fromName' => $mailSettings["from"]["name"], 
			'replyTo' => $mailSettings["replyTo"],
			'subject' => $template->subject, 
			'to' => $to
		];

		$attachments = ['/attachments/invoice/invoice_' . $invoiceNumber . '.pdf'];
		CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
	}

	public static function cancelAgreement($recurringType, $currency) { //return;
		$users = User::join('user_payment_info', 'user_payment_info.user_id', '=', 'users.id')
			->join('transactions', 'transactions.purchaser', '=', 'users.id')
			->where('user_payment_info.recurring_type', $recurringType)
			->where('user_payment_info.currency', $currency)
			->where('transactions.id', 'LIKE', 'I-%')
			->where('users.existing', 'No')
			->select('users.*', 'transactions.plan_id')
			->orderBy('id', 'DESC')
			->get();

		$apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));

		if(sizeof($users) > 0) {
			foreach($users as $user) {
				$planID = $user->plan_id;
				try {
					$userPlan = Plan::get($planID, $apiContext);
					$userPlan->delete($apiContext);

					$user->resubscription = 'Yes';
					$user->save();
				}
				catch(Exception $e) {
					\Log::error("Payment plan get:". $e->getMessage());
				}
			}
		}
	}
}
