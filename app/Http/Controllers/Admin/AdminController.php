<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Models\AdminEmailTemplate;
use App\Models\Campaign;
use App\Models\PushMessage;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Support\Facades\Validator;
use Input;
use Redirect;
use Request;
use Session;
use View;

class AdminController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
	    return \Redirect::route('admin.dashboard');
    }

    public function dashboard() {
        $date = new \DateTime();
        $date->modify("-7 days");
        $previous7Daydate = $date->format('Y-m-d');

        $data['totalUsers'] = User::where('email', '!=', "admin@sanityos.com")
                                  ->whereIn('userType', ['Single', 'Multi'])
                                  ->count();

        $data['newUsers'] = User::where('accountCreationDate', '>=', $previous7Daydate)
                                ->whereIn('userType', ['Single', 'Multi'])
                                ->count();

        $data['revenue'] = Transaction::select(DB::raw('SUM(amount) as total'))
                                      ->where('time', '>', $previous7Daydate )
                                      ->first()
                                      ->total;

        $data['abandonedCart'] = Setting::get('abandonedCart');

	    $data['toDate'] = Carbon::now()->format('d/m/Y');
	    $data['fromDate'] = Carbon::now()->subMonth()->format('d/m/Y');

        /*$volumeCollected = [];
        $volumeCollections = Campaign::select(DB::raw('SUM(totalLeads)as total, MONTH(timeCreated) as month'))
                                   ->groupBy(DB::raw('MONTH(timeCreated)'))
                                   ->orderBy('month')
                                   ->get();

        $monthArray = [];

        for($i = 1; $i <= 12; $i++) {
            $dateObj   = DateTime::createFromFormat('!m', $i);
            $monthName = $dateObj->format('F');

            $monthArray[] = [
                "y" => $i,
                "a" => 0
            ];;
        }

        foreach($volumeCollections as $volumeCollection) {
            $month = $volumeCollection->month;
            $totalLeads = $volumeCollection->total;

            $monthArray[$month - 1]["a"] = $totalLeads;
        }

        $data['volumeCollected'] = $monthArray;*/

        $data['dashboardMenuActive'] = 'active';
        return View::make('admin/dashboard/dashboard', $data);
    }

    public function setting() {
        $settingFields = [];

        $settingFields['adminEmail']        = Setting::get('adminEmail');
        $settingFields['paypalID']          = Setting::get('paypalID');
        $settingFields['apiSignature']      = Setting::get('apiSignature');
        $settingFields['supportEmail']      = Setting::get('supportEmail');
        $settingFields['defaultCurrency']   = Setting::get('defaultCurrency');
        $settingFields['baseCurrency']      = Setting::get('baseCurrency');
        $settingFields['trackingCode']      = Setting::get('trackingCode');
        $settingFields['renewalRemainder']  = Setting::get('renewalRemainder');
        $settingFields['industry']          = Setting::get('industry');
        $settingFields['metaKeywords']      = Setting::get('metaKeywords');
        $settingFields['metaDescription']   = Setting::get('metaDescription');
        $settingFields['gbpToDollar']       = Setting::get('gbpToDollar');
        $settingFields['euroToDollar']      = Setting::get('euroToDollar');
        $settingFields['frontHtml']         = Setting::get('frontHtml');
        $settingFields['loginHtml']         = Setting::get('loginHtml');
        $settingFields['adminLoginHtml']    = Setting::get('adminLoginHtml');
	    $settingFields['maxFileUploadSize'] = Setting::get('maxFileUploadSize');
	    $settingFields['maxLandingForm'] = Setting::get('maxLandingForm');
	    $settingFields['userHelpUrl'] = Setting::get('userHelpUrl');
	    $settingFields['trialRenewalRemainder'] = Setting::get('trialRenewalRemainder');

        $data['settingFields'] = $settingFields;
        $data['settingMenuActive'] = 'active';

        return View::make('admin/setting/setting', $data);
    }

    public function saveSetting() {
        if (Request::ajax()) {
            $output = Input::all();

            foreach($output as $key => $value) {
                //Setting::where('settingName', $key)->update(array('value' => $value));
                Setting::set($key, $value);
	            if($key == 'maxFileUploadSize'){
		            $maxFileSize = $value;
	            }
            }


	        //write to htaccess (file upload size)
	        $oldContent = file_get_contents(storage_path().'/meta/htaccessData.txt');
			$newContent = "\nphp_value upload_max_filesize ".$maxFileSize."M \nphp_value post_max_size ".$maxFileSize."M";
	        $content = $oldContent.$newContent;
	        $f = fopen(public_path()."/.htaccess", "w");
	        fwrite($f, $content);
	        fclose($f);

            $data['success'] = "success";
        }

        return json_encode($data);
    }

    public function emailTemplates() {
        $data['emailTemplateMenuActive'] = "active";

        $data['emailTemplates'] = AdminEmailTemplate::all();

        $data['successMessage'] = Session::get('successMessage');
        $data['successMessageClass'] 	= Session::get('successMessageClass');

        return View::make('admin/email/emailtemplates', $data);
    }

    public function ajaxEmailTemplateDetails()
    {
        if(Request::ajax())
        {
            $templateID = Input::get('templateID');

            $templateDetails = AdminEmailTemplate::where('id', $templateID)->first();

	        $data['templateDetails'] = $templateDetails;
            $data['success'] = "success";
        }

        return json_encode($data);
    }

    public function updateEmailTemplate()
    {
        if(Request::ajax())
        {
            $templateID = Input::get('templateID');
            $name = Input::get('name');
            $description = Input::get('description');
            $from = Input::get('from');
            $replyTo = Input::get('replyTo');
            $subject = Input::get('subject');
            $content = Input::get('content');
            $variables = Input::get('variables');

            $updateArray = [
                'name'          => $name,
                'description'   => $description,
                'from'          => $from,
                'replyTo'       => $replyTo,
                'subject'       => $subject,
                'content'       => $content,
                'variables'     => $variables
            ];

            AdminEmailTemplate::where('id', $templateID)->update($updateArray);

            Session::flash('successMessage', 'Email Template Successfully Updated.');
            Session::flash('successMessageClass', 'success');
            $data['success'] = 'success';
        }

        return json_encode($data);
    }

    public function pushMessage()
    {
        $data['allUsers'] = User::select('users.id', 'users.firstName', 'users.lastName')
                                ->get();

        $data['pushMessageMenuActive'] = 'active';
        return View::make('admin/pushmessage/pushmessage', $data);
    }

    public function generateMessage()
    {
        if(Request::ajax())
        {
	        $admin = CommonController::getAdminUserInfo();
            $userIDs = Input::get('usersIDs');
            $message = Input::get('message');

            foreach($userIDs as $receiver)
            {
                $newMessage = new PushMessage;
                $newMessage->message = $message;
                $newMessage->sender = $admin->id;
                $newMessage->receiver = $receiver;
                $newMessage->time = new DateTime;
                $newMessage->status = "Sent";
                $newMessage->save();
            }

            $data['success'] = 'success';
        }
        return json_encode($data);
    }


	public function mailSettings() {
		$data["outboundMail"] = Setting::get("outboundMail");
		$data["inboundMail"] = Setting::get("inboundMail");

        $data['mailSettingsMenuActive'] = "active";

		return View::make("admin/setting/mailsettings", $data);
	}

	public function saveMailSettings() {
		$type = trim(Input::get("type"));
		$from = trim(Input::get("from"));

		$username = trim(Input::get("username"));
		$password = trim(Input::get("password"));
		$host = trim(Input::get("host"));
		$port = trim(Input::get("port"));
		$requireSSL = trim(Input::get("requireSSL"));

		if ($type == "inbound") {

            $fromName = trim(Input::get("fromName"));
            $replyTo = trim(Input::get("replyTo"));

			$insert = [];
			$insert['from'] = [
				'address' => $from,
				'name' => $fromName
			];
			$insert['replyTo'] = $replyTo;
			$insert['username'] = $username;
			$insert['password'] = $password;
			$insert['host'] = $host;
			$insert['port'] = $port;
			$insert['requireSSL'] = $requireSSL;

			Setting::set('inboundMail', json_encode($insert));

			//Setting::set("inboundMail.from.address", $from);
			//Setting::set("inboundMail.from.name", $fromName);
			//Setting::set("inboundMail.replyTo", $replyTo);
			//Setting::set("inboundMail.username", $username);
			//Setting::set("inboundMail.password", $password);
			//Setting::set("inboundMail.host", $host);
			//Setting::set("inboundMail.port", $port);
			//Setting::set("inboundMail.requireSSL", $requireSSL);
		}
		else {
			$insert = [];
			$insert['from'] = [
				'address' => $from
			];
			$insert['username'] = $username;
			$insert['password'] = $password;
			$insert['host'] = $host;
			$insert['port'] = $port;
			$insert['requireSSL'] = $requireSSL;

			Setting::set('outboundMail', json_encode($insert));

			//Setting::set("outboundMail.from.address", $from);
			//Setting::set("outboundMail.username", $username);
			//Setting::set("outboundMail.password", $password);
			//Setting::set("outboundMail.host", $host);
			//Setting::set("outboundMail.port", $port);
			//Setting::set("outboundMail.requireSSL", $requireSSL);
		}

		Session::flash("message", "Settings saved successfully");

		return Redirect::route("admin.mailsettings");
	}

    function dataActivity() {
        $date = new \DateTime();
        $date->modify("-7 days");
        $previous7Daydate = $date->format('Y-m-d');

        $data['totalUsers'] = User::where('email', '!=', "admin@sanityos.com")
                                  ->whereIn('userType', ['Single', 'Multi'])
                                  ->count();

        $data['newUsers'] = User::where('accountCreationDate', '>=', $previous7Daydate)
                                ->whereIn('userType', ['Single', 'Multi'])
                                ->count();

        $data['revenue'] = Transaction::select(DB::raw('SUM(amount) as total'))
                                      ->where('time', '>', $previous7Daydate )
                                      ->first()
            ->total;

        $data['abandonedCart'] = Setting::get('abandonedCart');

        $volumeCollected = [];
        $volumeCollections = Campaign::select(DB::raw('SUM(totalLeads)as total, MONTH(timeCreated) as month'))
                                     ->groupBy(DB::raw('MONTH(timeCreated)'))
                                     ->orderBy('month')
                                     ->get();

        $monthArray = [];

        for($i = 1; $i <= 12; $i++) {
            $dateObj   = DateTime::createFromFormat('!m', $i);
            $monthName = $dateObj->format('F');

            $monthArray[] = [
                "y" => $i,
                "a" => 0
            ];;
        }

        foreach($volumeCollections as $volumeCollection) {
            $month = $volumeCollection->month;
            $totalLeads = $volumeCollection->total;

            $monthArray[$month - 1]["a"] = $totalLeads;
        }

        $data['volumeCollected'] = $monthArray;
        $data['dataActivityMenuActive'] = 'active';
        return View::make('admin/dashboard/dataactivity', $data);
    }

	function getConversionRatio() {
		$from = Input::get('fromDate');
		$to = Input::get('toDate');

		$fromDate = Carbon::createFromFormat('d/m/Y', $from)->format('Y-m-d 00:00:00');
		$toDate = Carbon::createFromFormat('d/m/Y', $to)->format('Y-m-d 00:00:00');

		// Get trial user sign up
		$trialUsers = DB::table('trial_user_track')->where('created_at', '>=', $fromDate)
			->where('created_at', '<', $toDate)
			->where('type', 'Trial')
			->count();

		// Get converted users
		$convertedUsers = DB::table('trial_user_track')->where('updated_at', '>=', $fromDate)
		                ->where('updated_at', '<', $toDate)
		                ->where('type', 'Converted')
		                ->count();

		// Get expired users
		$expiredUsers = DB::table('trial_user_track')->where('updated_at', '>=', $fromDate)
		                    ->where('updated_at', '<', $toDate)
		                    ->where('type', 'Expired')
		                    ->count();

		if($trialUsers > 0 || $convertedUsers > 0 || $expiredUsers > 0) {
			$data['isData'] = true;
		}
		else {
			$data['isData'] = false;
		}

		$data['pieData'] = [
			['label' => 'Trial users signed up', 'data'=> $trialUsers, 'color' => '#D9534F'],
			['label' => 'Converted to users', 'data'=> $convertedUsers, 'color' => '#1CAF9A'],
			['label' => 'Expired trials', 'data'=> $expiredUsers, 'color' => '#F0AD4E'],
		];

        $data['trialUsers'] = $trialUsers;
        $data['convertedUsers'] = $convertedUsers;
        $data['expiredUsers'] = $expiredUsers;

		if($trialUsers > 0) {
			$data['convertedRatio'] = round((($convertedUsers/$trialUsers) * 100), 0);
		}
		else {
			$data['convertedRatio'] = 0;
		}

		return $data;
	}

    function massMailSettings() {
        $data["massMailServer"] = Setting::get("massMailServer");
        $data["trialUserLimit"] = Setting::get("trialUserLimit");
        $data["singleUserLimit"] = Setting::get("singleUserLimit");
        $data["multiUserLimit"] = Setting::get("multiUserLimit");

        $data['massMailSettingsMenuActive'] = "active";

        return View::make("admin/setting/massmailsettings", $data);
    }

    function saveMassMailSettings(\Illuminate\Http\Request $request) {
        $rules = [
            'accessKey' => 'required',
            'secretKey' => 'required',
            'region' => 'required',
            'fromEmail' => 'required|email',
        ];

        $this->validate($request, $rules);

        $setting = [
            'accessKey' => $request->input('accessKey'),
            'secretKey' => $request->input('secretKey'),
            'region' => $request->input('region'),
            'fromEmail' => $request->input('fromEmail')
        ];

        $setting = json_encode($setting);

        Setting::set('massMailServer', $setting);
        Session::flash("message", "Settings saved successfully");

        return Redirect::route("admin.mass.mailsettings");
    }

    function saveMassMailLimitationSettings(\Illuminate\Http\Request $request) {
		$trialUserSetting = [
			'limitBy' => $request->input('trialLimitBy'),
			'emails' => $request->input('trialEmails'),
			'errorMessage' => $request->input('trialErrorMessage')
		];

	    $singleUserSetting = [
		    'limitBy' => $request->input('singleLimitBy'),
		    'emails' => $request->input('singleEmails'),
		    'errorMessage' => $request->input('singleErrorMessage')
	    ];

	    $multiUserSetting = [
		    'limitBy' => $request->input('multiLimitBy'),
		    'emails' => $request->input('multiEmails'),
		    'errorMessage' => $request->input('multiErrorMessage')
	    ];

	    Setting::set('trialUserLimit', json_encode($trialUserSetting));
	    Setting::set('singleUserLimit', json_encode($singleUserSetting));
	    Setting::set('multiUserLimit', json_encode($multiUserSetting));

	    Session::flash("message", "Settings saved successfully");

	    return Redirect::route("admin.mass.mailsettings");
    }
}
