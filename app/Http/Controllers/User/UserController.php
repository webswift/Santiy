<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SavePaypalCardDetails;
use App\Jobs\SendApprovedTransaction;
use App\Models\AdminEmailTemplate;
use App\Models\Appointment;
use App\Models\Campaign;
use App\Models\HelpArticle;
use App\Models\HelpTopic;
use App\Models\Lead;
use App\Models\License;
use App\Models\LicenseType;
use App\Models\PostalCode;
use App\Models\Setting;
use App\Models\SmtpSetting;
use App\Models\TodoList;
use App\Models\Transaction;
use App\Models\User;
use App\Models\PendingTransaction;
use Auth;
use Carbon\Carbon;
use Config;
use DateTime;
use DB;
use Exception;
use Hash;
use Input;
use PayPal\Api\Address;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Agreement;
use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\Currency;
use PayPal\Api\FundingInstrument;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Redirect;
use Request;
use Session;
use Validator;
use View;
use Mail;

class UserController extends Controller {

    /**
     * This controller holds all the user related functions, like, login, change password, profile edit, etc
     *
     * @return Response
     */
    public function index() {
	    return Redirect::to('user/dashboard');
    }

    public function dashboard() {
	    $user = $this->user;
        $userType = $user->userType;
        $userID = $user->id;
        $userAccountStatus = $user->accountStatus;
        $passwordChangesRequired = $user->passwordChangeRequired;

	    //TODO: Changes to dashboard for multi user

        //Array to send data to view
        $data = [
	        'userAccountStatus'         => $userAccountStatus,
            'dashboardMenuActive'       => 'active',
            'passwordChangesRequired'   => $passwordChangesRequired,
        ];

        $exactPrevious7DaysDate = $this->getPrevious7DaysDate();

        if($userType == 'Multi' || $userType == 'Team') {
	        $teamMembersIDs = $user->getTeamMembersIDs();

	        if($userType == 'Multi') {
                $data['callMade'] = Lead::whereIn('lastActioner', $teamMembersIDs)
                    ->where('status', 'Actioned')
	                ->where('timeEdited', '>=', $exactPrevious7DaysDate)
	                ->count();

		        $totalLeads = Campaign::select(DB::raw('SUM(totalLeads) as total'))
			        ->where('status', 'Started')
			        ->whereIn('creator', $teamMembersIDs)
			        ->first();

                $data['totalLeads'] = ($totalLeads) ? $totalLeads->total : "0";

                $data['activeCampaign'] = Campaign::where('status', 'Started')
	                ->whereIn('creator', $teamMembersIDs)
	                ->where('status', 'Started')
	                ->count();

		        $data['totalTeamMember'] = count($teamMembersIDs) - 1;
            }
	        else if($userType == 'Team') {

                $userCampaigns = $user->getActiveCampaigns();
	            $userCampaignIDs = $userCampaigns->lists("id")->all();

                $data['callMade'] = Lead::where('lastActioner', $userID)
                                        ->where('status', 'Actioned')
                                        ->where('timeEdited', '>=', $exactPrevious7DaysDate)
                                        ->count();

				$data['totalLeads'] = 0;
                if ($userCampaigns->count() > 0) {
                    $totalLeads = Campaign::select(DB::raw('SUM(totalLeads) as total'))
                        ->where('status', 'Started')
                        ->whereIn('id', $userCampaignIDs)
                        ->first();

	                if ($totalLeads) {
						$data['totalLeads'] = $totalLeads->total;
	                }
                }

                $data['appointmentBooked'] = Appointment::where('creator', $userID)
                                                        ->where('timeCreated', '>=', $exactPrevious7DaysDate)
                                                        ->count();

                $timeTaken = Lead::select(DB::raw('AVG(timeTaken) as timeTaken'))
                                               ->where('status', 'Actioned')
                                               ->where('lastActioner', $userID)
                                               ->where('timeEdited', '>=', $exactPrevious7DaysDate)
                                               ->first()
                                               ->timeTaken;

                $sec = floor($timeTaken/1000);
                $minute = floor($sec/60);
                $second = (substr($sec, 0, 2) < 10) ? '0'.substr($sec, 0, 2) : substr($sec, 0, 2);
                $data['averageCallTime']    = $minute . ':' . $second;
            }
        }
        else if($userType == 'Single') {
            $data['callMade'] = Lead::where('lastActioner', $userID)
                                    ->where('status', 'Actioned')
                                    ->where('timeEdited', '>=', $exactPrevious7DaysDate)
                                    ->count();

			$data['totalLeads'] = 0;
            $totalLeads =  Campaign::select(DB::raw('SUM(totalLeads) as total'))
                                                ->where('status', 'Started')
                                                ->where('creator', $userID)
                                                ->first();

	        if ($totalLeads) {
				$data['totalLeads'] = $totalLeads->total;
	        }

            $data['appointmentBooked'] = Appointment::where('creator', $userID)
                                                    ->where('timeCreated', '>=', $exactPrevious7DaysDate)
                                                    ->count();

            $timeTaken =  Lead::select(DB::raw('AVG(timeTaken) as timeTaken'))
                                ->where('status', 'Actioned')
                                ->where('lastActioner', $userID)
                                ->where('timeEdited', '>=', $exactPrevious7DaysDate)
                                ->first()
                                ->timeTaken;


            $sec = floor($timeTaken/1000);
            $minute = floor($sec/60);
            $second = (substr($sec, 0, 2) < 10) ? '0'.substr($sec, 0, 2) : substr($sec, 0, 2);

            $data['averageCallTime']    = $minute . ':' . $second;

            $successfulCampaigns = Campaign::select(['id', 'name'])
                                            ->where('creator', $userID)
                                            ->where('status', 'Started')
                                            ->whereIn('id', function($query) use ($exactPrevious7DaysDate) {
                                                $query->select('campaignID')
                                                    ->from("leads")
                                                    ->where("interested", "Interested")
                                                    ->where("timeEdited", '>=', $exactPrevious7DaysDate)
                                                    ->groupBy("campaignID")
                                                    ->orderBy(DB::raw("count(*)"), "DESC");
                                            })
                                            ->take(5)->get();

            $campaignDetails = [];
            $colorsArray = ["primary", "danger", "success", "warning"];

            foreach($successfulCampaigns as $successfulCampaign) {
                $campaignName = $successfulCampaign->name;
                $campaignID   = $successfulCampaign->id;

                $totalInterested = Lead::where('campaignID', $campaignID)
                                       ->where('interested', 'Interested')
                    ->where("timeEdited", '>=', $exactPrevious7DaysDate)
                                       ->where('status', 'Actioned')
                                       ->count();
                $totalNotInterested = Lead::where('campaignID', $campaignID)
                    ->where('interested', 'NotInterested')
                    ->where("timeEdited", '>=', $exactPrevious7DaysDate)
                    ->where('status', 'Actioned')
                    ->count();

                if($totalInterested > 0) {
                    $totalLeads = Lead::where('campaignID', $campaignID)
                        ->where('status', 'Actioned')
                        ->where("timeEdited", '>=', $exactPrevious7DaysDate)
                        ->count();

                    if($totalLeads == 0) {
                        $percentage = 0;
                    }
                    else {
                        $percentage = ($totalInterested/$totalLeads)*100;
                    }


                    $randomKey = array_rand($colorsArray);
                    $color = $colorsArray[$randomKey];

                    $campaignDetails[] = [
                        'name'              => $campaignName,
                        'totalInterested'   => $totalInterested,
                        'totalLeads'        => $totalLeads,
                        'totalNotInterested' => $totalNotInterested,
                        'percentage'        => $percentage,
                        'color'             => $color
                    ];
                }
            }

             $data['campaigns'] = $campaignDetails;
        }

		//TODO : todo list Not for multi User
        // For todolist
        if($userType == 'Single' || $userType == 'Team') {
            $data['todoLists'] = TodoList::select(['id', 'todoText', 'time'])
                                         ->where('userID', $userID)
                                         ->where('status', 'Pending')
                                         ->orderBy('id', 'desc')
                                         ->get();
        }

        if($userAccountStatus === 'LicenseExpired') {
            return Redirect::to('user/profile');
        }
        else {
            return View::make('site/dashboard/dashboard', $data);
        }
    }

    public function doneTodo() {
        if (Request::ajax()) {
            $todoID = Input::get('todoID');

            TodoList::where('id', $todoID)->update(['status' => 'Done']);

            $data['success'] = "success";

	        return json_encode($data);
        }
    }

    public function createTodo() {
        if (Request::ajax()) {
            //User information from session variable
            $user = $this->user;

            $todoText = Input::get('todoText');
            $date = new DateTime();
            $createdDate = $date->format('M d');

            $newTodoList = new TodoList;
            $newTodoList->todoText = $todoText;
            $newTodoList->userID = $user->id;
            $newTodoList->time = new DateTime;
            $newTodoList->save();

            $todoID = $newTodoList->id;

            $data['todoID'] = $todoID;
            $data['date']  = $createdDate;
            $data['success'] = "success";

	        return json_encode($data);
        }
    }

    public function ajaxTodayPerformance() {
        if (Request::ajax()) {
            //User information from session variable
            $user = $this->user;

            $userManager = $user->getManager();

            $actionType  = Input::get('actionType');
            $memberDetails = [];
            $colors = [];
            $date = new DateTime;
            $today = $date->format('Y-m-d');
            $colorsArray = ["#D9534F", "#1CAF9A", "#428BCA", "#5BC0DE", "#428BCA"];

            $teamMembers = $user->getTeamMembers('manager');

            if($actionType == 'calls') {

	            foreach($teamMembers as $teamMember) {
                    $name = $teamMember->firstName;

                    $totalLeads = Lead::where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->where('status', 'Actioned')
                        ->where('lastActioner', $teamMember->id)
                        ->count();

                    if($totalLeads > 0) {
                        $memberDetails[] = [
                            'label'     => $name,
                            'value'     => $totalLeads
                        ];

                        $randomKey = array_rand($colorsArray);
                        $colors[] = $colorsArray[$randomKey];
                    }

                }

                $data['success'] = 'success';
                $data['memberDetails'] = $memberDetails;
                $data['colors']        = $colors;
            }
            else if($actionType == 'appointments') {
                foreach($teamMembers as $teamMember) {
                    $name = $teamMember->firstName;

                    $totalAppointment = Appointment::where('timeCreated', 'like', '%'.$today.'%')
                                                   ->where('creator', $teamMember->id)
                                                   ->count();

                    if($totalAppointment > 0) {
                        $memberDetails[] = [
                            'label'     => $name,
                            'value'     => $totalAppointment
                        ];

                        $randomKey = array_rand($colorsArray);
                        $colors[] = $colorsArray[$randomKey];
                    }

                }

                $data['success'] = 'success';
                $data['memberDetails'] = $memberDetails;
                $data['colors']        = $colors;

            }
	        return json_encode($data);
        }
    }

    public function ajaxTeamPerformance() {
        $data = [];

        if (Request::ajax()) {

            $actionType  = Input::get('actionType');
            $memberDetails = [];
            $exactPrevious7DaysDate=$this->getPrevious7DaysDate();
            $colorsArray = ["primary", "danger", "success", "warning"];

	        //$teamMembersIDs = $this->user->getTeamMembersIDs();
            $teamMembersIDs = $this->user->getTeamMembers('manager')->lists('id')->all();

            if($actionType == 'calls') {
                $totalCallMade = Lead::where('status', 'Actioned')
                                     ->whereIn('lastActioner', $teamMembersIDs)
                                     ->where('timeEdited', '>=', $exactPrevious7DaysDate)
                                     ->count();

                if($totalCallMade > 0) {
                    $usersCalls = Lead::select(DB::raw("COUNT(*) as userTotal, users.firstName"))
                        ->join('users', 'users.id', '=', 'leads.lastActioner')
                        ->where('status', 'Actioned')
                        ->whereIn('leads.lastActioner', $teamMembersIDs)
                        ->where('leads.timeEdited', '>=', $exactPrevious7DaysDate)
                        ->groupBy('leads.lastActioner')
                        ->orderBy('userTotal', 'desc')
                        ->take(5)
                        ->get();

                    foreach($usersCalls as $userCall) {
                        $userLeads = $userCall->userTotal;
                        $name = $userCall->firstName;

                        $percentage = ($userLeads/$totalCallMade)*100;
                        $randomKey = array_rand($colorsArray);
                        $colors = $colorsArray[$randomKey];

                        $memberDetails[] = [
                            'name'     => $name,
                            'done'     => $userLeads,
                            'percentage' => $percentage,
                            'color'    => $colors
                        ];
                    }
                }
                else {
                    $memberDetails = [];
                }

                $data['success'] = 'success';
                $data['memberDetails'] = $memberDetails;
            }
            else if($actionType == 'appointments') {
                $totalAppointment = Appointment::whereIn('creator', $teamMembersIDs)
                                               ->where('timeCreated', '>=', $exactPrevious7DaysDate)
                                               ->count();

                if($totalAppointment > 0) {
                    $userAppointments = Appointment::select(DB::raw("COUNT(*) AS userTotal, users.firstName"))
                                                    ->join('users', 'users.id', '=', 'appointments.creator')
                                                    ->whereIn('appointments.creator', $teamMembersIDs)
                                                    ->where('appointments.timeCreated', '>=', $exactPrevious7DaysDate)
                                                    ->groupBy('appointments.creator')
                                                    ->orderBy('userTotal', 'desc')
                                                    ->take(5)
                                                    ->get();

                    foreach($userAppointments as $userAppointment) {
                        $name = $userAppointment->firstName;
                        $appointment = $userAppointment->userTotal;
                        $percentage = ($appointment/$totalAppointment)*100;
                        $randomKey = array_rand($colorsArray);
                        $colors = $colorsArray[$randomKey];

                        $memberDetails[] = [
                            'name'     => $name,
                            'done'     => $appointment,
                            'percentage' => $percentage,
                            'color'    => $colors
                        ];
                    }

                }
                else {
                    $memberDetails = [];
                }

                $data['success'] = 'success';
                $data['memberDetails'] = $memberDetails;
            }
            else if($actionType == 'callTime') {
                $totalAVGTime = Lead::select(DB::raw('AVG(timeTaken) as totalTime'))
                                 ->where('status', 'Actioned')
                                 ->whereIn('lastActioner', $teamMembersIDs)
                                 ->where('timeEdited', '>=', $exactPrevious7DaysDate)
                                 ->first()
                                 ->totalTime;

                if($totalAVGTime > 0) {
                    $usersTimeTaken = Lead::select(DB::raw('AVG(timeTaken) as totalTime, users.firstName'))
                                        ->join('users', 'users.id', '=', 'leads.lastActioner')
                                        ->where('leads.status', 'Actioned')
                                        ->whereIn('leads.lastActioner', $teamMembersIDs)
                                        ->where('leads.timeEdited', '>=', $exactPrevious7DaysDate)
                                        ->groupBy('leads.lastActioner')
                                        ->orderBy('totalTime', 'desc')
                                        ->get();

                    foreach($usersTimeTaken as $userTimeTaken) {
                        $userAVGTimeTaken = $userTimeTaken->totalTime;
                        $name = $userTimeTaken->firstName;

                        $percentage = ($userAVGTimeTaken / $totalAVGTime) * 100;
                        $randomKey = array_rand($colorsArray);
                        $colors = $colorsArray[$randomKey];

                        $sec = floor($userAVGTimeTaken / 1000);
                        $minute = floor($sec / 60);
                        $second = (substr($sec, 0, 2) < 10) ? '0' . substr($sec, 0, 2) : substr($sec, 0, 2);
                        $exactTimeCall = $minute . ':' . $second;

                        $memberDetails[] = [
                            'name' => $name,
                            'done' => $exactTimeCall,
                            'percentage' => $percentage,
                            'color' => $colors
                        ];
                    }

                }
                else {
                    $memberDetails = [];
                }

                $data['success'] = 'success';
                $data['memberDetails'] = $memberDetails;
            }
        }

        return json_encode($data);
    }

	public function getPrevious7DaysDate(){
        $tempDate = new DateTime();
        $weekDay = $tempDate->format("w");
		//convert from 0-6 where 0 - sunday to 0 is monday 
		$weekDay -= 1;
		if($weekDay < 0) {
			$weekDay = 6;
		}
		$date = (new DateTime())->setTime(0, 0);
        $date->modify("-".$weekDay." days");
        $exactPrevious7DaysDate = $date->format('Y-m-d H:i:s');
        return $exactPrevious7DaysDate;
	}

    public function ajaxInterestedAndNotInterested() {
        $data = [];

        if (Request::ajax()) {
            $user = $this->user;

            $userType = $user->userType;
            $userID = $user->id;
            $elementFound = false;

            $teamMembersIDs = $user->getTeamMembersIDs();
            //$teamMembersIDs = $user->getTeamMembers('manager')->lists('id')->all();

            $interested = [];
            $notInterested = [];
            $count = 0;

            $weekDay = (new DateTime())->format("w");
			//convert from 0-6 where 0 - sunday to 0 is monday 
			$weekDay -= 1;
			if($weekDay < 0) {
				$weekDay = 6;
			}

            for($i = $weekDay; $i >= 0; $i--) {
                $date = new DateTime;
                $date->modify("-".$i." days");
                $newDate = $date->format('Y-m-d');

                $totalInterested = Lead::where(DB::raw('DATE(timeEdited)'), '=', $newDate)
                                       ->where('interested', 'Interested');

                $totalNotInterested = Lead::where(DB::raw('DATE(timeEdited)'), '=', $newDate)
                                       ->where('interested', 'NotInterested');

                //showing leads Interest Status according to userType
                if($userType == 'Multi') {
                    $totalInterested->whereIn('lastActioner', $teamMembersIDs);
                    $totalNotInterested->whereIn('lastActioner', $teamMembersIDs);
                }
                else {
                    $totalInterested->where('lastActioner', $userID);
                    $totalNotInterested->where('lastActioner', $userID);
                }

                $totalInterested1    = $totalInterested->count();
                $totalNotInterested1 = $totalNotInterested->count();

                if($totalInterested1 > 0 || $totalNotInterested1 > 0) {
                    $elementFound = true;
                }

                $interested[] = [$count +1 , $totalInterested1];
                $notInterested[] = [$count +1, $totalNotInterested1];
                $count++;
            }

            // add zero counters for rest of the days

            $data['elementFound'] = $elementFound;
            $data['interested'] = $interested;
            $data['notInterested'] = $notInterested;
            $data['success'] = 'success';
        }

        return json_encode($data);
    }

    public function changePassword() {
        // Function must be called only via Ajax request
        if (Request::ajax()) {
            //User information from session variable
            $LoginUserDetail = Session::get('LoginUserDetail');

            $output = [];

            $newPassword = Input::get('newPassword');
            $confirmPassword = Input::get('confirmPassword');

            $error = '';

            $validator = Validator::make(
                Input::all(), [
                    'newPassword' => 'required|min:6',
                    'confirmPassword' => 'required|min:6'
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->messages();
                foreach ($messages->all() as $message) {
                    $error .= $message . '<br>';
                }

                $output['success'] = FALSE;
                $output['error'] = $error;
            }
            else if($newPassword !== $confirmPassword) {
                $output['success'] = FALSE;
                $output['error'] = 'New Password and Confirm Password does not match.';
            }
            else {
                //userId of current loggedIn user
                $id = $LoginUserDetail->id;

                $user = $this->user;
                $user->password = Hash::make($newPassword);
                $user->passwordChangeRequired  = 'No';
                $user->save();

                //Storing user information to session variable
                Session::forget('LoginUserDetail');
                Session::put('LoginUserDetail', Auth::user()->get());

                $output['success'] = true;
                $output['error'] = "Password Changed Successfully.";
            }

            return json_encode($output);
        }
    }

    public function profile() {
        $userDetails = $this->user;
        $userAccountStatus = $userDetails->accountStatus;
	    $userID  = $userDetails->id;

        //Array to send data to view
        $data = [
	        'userAccountStatus'  => $userAccountStatus,
	        'settingsMenuActive'  => 'nav-active active',
	        'settingsStyleActive' =>  'display: block',
	        'settingsMenuStyleActive' =>  'active',
	        'userDetails' => $userDetails
        ];

		$data['errorMessage'] = Session::get('errorMessage');
	    $data['errorDetails'] = Session::get('errorDetails');

	    $data['userPaymentInfo'] = [];
	    $data['cost'] = '';

        $data['userPaymentInfo']['currency'] = 'USD';
        $data['userPaymentInfo']['recurring_type'] = 'Monthly';

        //License Info if userType is Team Or Single
        if($userDetails->userType === 'Multi' || $userDetails->userType === 'Single') {

            $data['licenseDetails'] = $licenseDetail = License::find($userID);

            $expireDate = $licenseDetail->expireDate;
            $expireDate = new DateTime($expireDate);

            $today = new DateTime;

            $dateDifference = $expireDate->diff($today);
            $dateDifferenceDays = $dateDifference->format('%a');

            $data['licenseWillExpire'] = false;

            if($userDetails->accountStatus == 'LicenseExpired') {
                $data['licenseWillExpire'] = true;
            }

            if($userDetails->isTrial && $dateDifferenceDays <= 30) {
                $data['licenseWillExpire'] = true;
            }

	        $data['transaction'] = false;

	        // Check user transactions
	        #1. if user transaction exists the
			$transaction = Transaction::where('licenseType', $licenseDetail->licenseType)->where('purchaser', $userDetails->id)->orderBy('time', 'DESC')->first();
	        if($transaction) {
				$data['transaction'] = $transaction;
	        }

	        $paymentInfo = $userDetails->getPaymentInfo();

	        if($paymentInfo) {
                if($transaction) {
                    if($paymentInfo->currency == 'USD') {
                        $cost = '$'.$transaction->nextBillingAmount .' (USD)';
                    }
                    else if($paymentInfo->currency == 'GBP') {
                        $cost = '£'.$transaction->nextBillingAmount . ' (GBP)';
                    }
                    elseif($paymentInfo->currency == 'EUR') {
                        $cost = '€'.$transaction->nextBillingAmount . ' (EUR)';
                    }
                }
		        else {
                    $cost = 0;
                }
		        $data['cost'] = $cost;

                $data['userPaymentInfo']['info'] = $paymentInfo;

                $data['userPaymentInfo']['recurring_type'] = $paymentInfo->recurring_type;
                $data['userPaymentInfo']['currency'] = $paymentInfo->currency;
	        }

            // for update team user
            # 1. Get free users
            # 2. Get total volume
            # 3. Get current team size
            # 4. set there in an associative array and send

            $freeUsers = $licenseDetail->free_users;
            $totalUsers = $licenseDetail->licenseVolume;
            $currentTeamSize = User::where('manager', $this->user->id)->count();

            $data['teamUsers'] = json_encode([
                'free' => $freeUsers,
                'total' => $totalUsers,
                'current' => $currentTeamSize
            ]);

            // Set currency wise per user price
            $data['pricing'] = json_encode($licenseDetail->currencyWisePricing());
        }

	    //$data['licensePrice'] = LicenseType::where('licenseClass', $userDetails->userType)->value('priceUSD');

        //email setting exists or not for this user
        $emailSettingExists = SmtpSetting::where('userID', '=', $userID)->count();

        if($emailSettingExists > 0) {
            $data['emailSettingExists'] = true;
            $data['emailSettingDetail'] = SmtpSetting::where('userID', '=', $userID)->first();
        }
        else {
            $data['emailSettingExists'] = false;
        }

        $data['successMessage'] = Session::get('successMessage');
        return View::make('site/profile', $data);
    }

    public function editProfile() {
        if (Request::ajax()) {
            $userID = $this->user->id;

            $output = [];
            $error = '';

            //check total number of email in users table
            $checkEmailAlreadyExists = User::where('email', '=', Input::get('email'))
                                           ->where('id', '!=', $userID)
                                           ->count();

            //validation Array
            $validatorArray = [
	            'firstName' => 'required',
	            'lastName' => 'required',
	            'email' => 'required|email',
	            'timeZoneName' => 'required'
            ];

            if(Input::has('skypID')) {
                $validatorArray['skypID'] = 'required';
            }

            //Validation Rules for user Info
            $validator = Validator::make( Input::all(), $validatorArray);

            if ($validator->fails()) {
                //If Validation rule fails
                $messages = $validator->messages();
                foreach ($messages->all() as $message) {
                    $error .= $message . '<br>';
                }

                $output['success'] = FALSE;
                $output['error'] = $error;
            }
            else if($checkEmailAlreadyExists >= 1) {
                $output['success'] = FALSE;
                $output['error'] = "Oops! This email looks like it belongs to someone else";
            }
            else {
                $user = $this->user;

                $user->firstName        = Input::get('firstName');
                $user->lastName         = Input::get('lastName');
                $user->email            = Input::get('email');
                $user->contactNumber    = Input::get('contactNumber');
                $user->timeZoneName     = Input::get('timeZoneName');

                if(Input::has('statisticUpdates')) {
                    $user->statisticUpdates = Input::get('statisticUpdates');
                }

                if(Input::has('skypID')) {
                    $user->skypeID    = Input::get('skypID');
                }

                $user->save();

                Session::flash('successMessage', 'Profile Successfully Updated');

                //Resetting User Session variables
                Session::put('LoginUserDetail', Auth::user()->get());
                $output['success'] = true;
                $output['error'] = "Profile Successfully Updated.";
            }
        }

        return json_encode($output);
    }

    public function showLeftPanel() {

	    //TODO: Changes to left panel for multi user

        if(Request::ajax()) {
            if(Session::has('sessionLeftPanelHour')) {
                $sessionLeftPanelHour = Session::get('sessionLeftPanelHour');
            }
            else {
                $sessionLeftPanelHour = "";
            }

            $date = new DateTime;
            $currentHour = $date->format('H');

            if($sessionLeftPanelHour != $currentHour) {
                //User information from session variable
                $LoginUserDetail = Session::get('LoginUserDetail');

                //$userID = $LoginUserDetail->id;
                $userID = $this->user->id;
                $date = new DateTime;
                $today = $date->format('Y-m-d');
                $hour = (new DateTime)->format('H');

                //$userType = $LoginUserDetail->userType;
                $userType = $this->user->userType;
                $teamMembersIDs = [];

                $callMadeTodayArray = [];
                $interestedLeadTodayArray = [];
                $bookAppointmentTodayArray = [];
                $topPerformerArray = [];

                for($i = 0; $i <= $hour; $i++) {
                    $callMadeTodayArray[$i] = 0;
                    $interestedLeadTodayArray[$i] = 0;
                    $bookAppointmentTodayArray[$i] = 0;
                    $topPerformerArray[$i] = 0;
                }

                if($userType === 'Team' || $userType === 'Multi') {
                    if($userType === 'Team') {
                        $userManger  = $this->user->manager;
                    }
                    else {
                        $userManger = $userID;
                    }

                    $teamMembers =  User::select(['users.firstName', 'users.id'])
                        ->where('manager', $userManger)
                        ->get();

                    foreach($teamMembers as $teamMember) {
                        $teamMembersIDs[] = $teamMember->id;
                    }

                    $teamMembersIDs[] = $userManger;
                }
                else if($userType === 'Single') {
                    $teamMembersIDs[] = $userID;
                }

                $totalCallMade = 0;
                $totalInterest = 0;
                $totalBookAppointment = 0;

                if($userType == "Multi") {
                    // Multi user should see stats of his whole team
                    // Calls Made Today = Total Actioned Leads today
                    $callMadeToday = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->whereIn('lastActioner', $teamMembersIDs)
                        ->where(DB::raw("HOUR(timeEdited)"), "!=", DB::raw("HOUR(NOW())")) // Exclude current hour from stats
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();

                    // Total Interested Leads = Actioned leads which have Interested set as YES
                    $todayInterestedLead = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->whereIn('lastActioner', $teamMembersIDs)
                        ->where('interested', 'Interested')
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();

                    // Booked Appointment = Leads with a book appointment
                    $todayBookedAppointment = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->whereIn('lastActioner', $teamMembersIDs)
                        ->where('bookAppointment', 'Yes')
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();

                }
                else {

                    // Calls Made Today = Total Actioned Leads today
                    $callMadeToday = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->whereIn('lastActioner', $teamMembersIDs)
                        ->where(DB::raw("HOUR(timeEdited)"), "!=", DB::raw("HOUR(NOW())")) // Exclude current hour from stats
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();

                    // Total Interested Lead`s = Actioned leads which have Interested set as YES
                    $todayInterestedLead = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->whereIn('lastActioner', $teamMembersIDs)
                        ->where('interested', 'Interested')
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();

                    // Booked Appointment = Leads with a book appointment
                    $todayBookedAppointment = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->whereIn('lastActioner', $teamMembersIDs)
                        ->where('bookAppointment', 'Yes')
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();
                }

                if($userType == 'Single') {
                    $weekStartingDate = (new DateTime)->modify("this week")->format('Y-m-d');

                    $weekTopPerformer = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, DATE(leads.timeEdited) AS topDate'))
                        ->where('leads.lastActioner', $userID)
                        ->where(DB::raw("DATE(timeEdited)"), '<=', $today)
                        ->where(DB::raw("DATE(timeEdited)"), '>=', $weekStartingDate)
                        ->where('interested', 'Interested')
                        ->groupBy(DB::raw('DATE(leads.timeEdited)'))
                        ->orderBy('totalLeads', 'DESC')
                        ->first();

                    if($weekTopPerformer) {
                        $weekTopPerformerDate = $weekTopPerformer->topDate;
                    }
                    else {
                        $weekTopPerformerDate = $today;
                    }

                    if($weekTopPerformerDate < $today) {
                        for($i=0; $i<= 23; $i++) {
                            $topPerformerArray[$i] = 0;
                        }
                    }

                    $topPerformerInterestedCall = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                        ->where('lastActioner', $userID)
                        ->where('interested', 'Interested')
                        ->where(DB::raw("DATE(timeEdited)"), '=', $weekTopPerformerDate)
                        ->groupBy(DB::raw('HOUR(timeEdited)'))
                        ->orderBy('hourValue', 'ASC')
                        ->get();

                    foreach($topPerformerInterestedCall as $topPerformer) {
                        $topPerformerArray[$topPerformer->hourValue] = $topPerformer->totalLeads;
                    }

                    if ($topPerformerInterestedCall->count() > 0) {
                        $topPerformerName = (new DateTime($weekTopPerformerDate))->format('l');
                    }
                    else {
                        $topPerformerName = "";
                    }
                }
                else {
                    // User who booked most appointments
                    $todayTopPerformer = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, users.firstName, leads.lastActioner'))
                        ->join('users', 'users.id', '=', 'leads.lastActioner')
                        ->whereIn('leads.lastActioner', $teamMembersIDs)
                        ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                        ->where('interested', 'Interested')
                        ->groupBy('leads.lastActioner')
                        ->orderBy('totalLeads', 'DESC')
                        ->first();

                    if($todayTopPerformer) {
                        $topPerformerID = $todayTopPerformer->lastActioner;
                        $topPerformerCallMade = Lead::select(DB::raw('COUNT(leads.id) AS totalLeads, HOUR(timeEdited) as hourValue'))
                            ->where('lastActioner', $topPerformerID)
                            ->where(DB::raw('DATE(timeEdited)'), '=', $today)
                            ->groupBy(DB::raw('HOUR(timeEdited)'))
                            ->orderBy('hourValue', 'ASC')
                            ->get();

                        foreach($topPerformerCallMade as $topPerformer) {
                            $topPerformerArray[$topPerformer->hourValue] = $topPerformer->totalLeads;
                        }

                        $topPerformerName = ($topPerformerID == '')?'':(User::find($topPerformerID)->firstName);
                    }
                    else {
                        $topPerformerName = '';
                        $topPerformerCallMade = 0;
                    }
                }

                // TODO: Top Performer and last sidebar task remaining

                foreach($callMadeToday as $calls) {
                    $callMadeTodayArray[$calls->hourValue] = $calls->totalLeads;
                    $totalCallMade += $calls->totalLeads;
                }

                foreach($todayInterestedLead as $interest) {
                    $interestedLeadTodayArray[$interest->hourValue] = $interest->totalLeads;
                    $totalInterest += $interest->totalLeads;
                }

                foreach($todayBookedAppointment as $bookAppointment) {
                    $bookAppointmentTodayArray[$bookAppointment->hourValue] = $bookAppointment->totalLeads;
                    $totalBookAppointment += $bookAppointment->totalLeads;
                }

                if($userType == 'Multi' || $userType == 'Team') {
                    //Success Percentage
                    if($totalCallMade > 0) {
                        $successPercentage = ($totalInterest/$totalCallMade)*100;
                        $successPercentage = round($successPercentage, 2) . ' %';
                    }
                    else {
                        $successPercentage = 0;
                    }
                }
                else {
                    $timeTaken =  Lead::select(DB::raw('AVG(timeTaken) as timeTaken'))
                        ->where('status', 'Actioned')
                        ->where('lastActioner', $userID)
                        ->where('timeEdited', 'LIKE', '%'.$today.'%')
                        ->first()
                        ->timeTaken;

                    if($timeTaken > 0) {
                        $sec = floor($timeTaken/1000);
                        $minute = floor($sec/60);
                        $second = (substr($sec, 0, 2) < 10) ? '0'.substr($sec, 0, 2) : substr($sec, 0, 2);
                        $successPercentage    = $minute . ':' . $second;
                    }
                    else {
                        $successPercentage = 0;
                    }
                }

                //Putting Session Hour Value
                $date = new DateTime;
                $hour = $date->format('H');
                Session::put('sessionLeftPanelHour', $hour);

                Session::forget('leftPanel');

                //Setting all values to Session
                Session::push('leftPanel.todayCallMade', $callMadeTodayArray);
                Session::push('leftPanel.totalCallMade', $totalCallMade);
                Session::push('leftPanel.todayInterest', $interestedLeadTodayArray);
                Session::push('leftPanel.totalInterest', $totalInterest);
                Session::push('leftPanel.todayBookAppointment', $bookAppointmentTodayArray);
                Session::push('leftPanel.totalBookAppointment', $totalBookAppointment);
                Session::push('leftPanel.successPercentage', $successPercentage);
                Session::push('leftPanel.topPerformerName', $topPerformerName);
                Session::push('leftPanel.topPerformerArray', $topPerformerArray);
            }

            $data['success'] = 'success';
            $data['todayCallMade'] = Session::get('leftPanel.todayCallMade');
            $data['totalCallMade'] = Session::get('leftPanel.totalCallMade')[0];
            $data['todayInterest'] = Session::get('leftPanel.todayInterest');
            $data['totalInterest'] = Session::get('leftPanel.totalInterest');
            $data['todayBookAppointment'] = Session::get('leftPanel.todayBookAppointment');
            $data['totalBookAppointment'] = Session::get('leftPanel.totalBookAppointment');
            $data['successPercentage'] = Session::get('leftPanel.successPercentage');
            $data['topPerformerName'] = Session::get('leftPanel.topPerformerName');
            $data['topPerformerArray'] = Session::get('leftPanel.topPerformerArray');

            return json_encode($data);
        }
    }

    public function helpTopics($postedTopicID = null) {

        $data['postedTopicID'] = $postedTopicID;

        $data['helpTopicMenuActive'] = "active";
        $data['topicLists'] = HelpTopic::all();

        $topicArticles = HelpArticle::all();
        $data['topicArticles'] = $topicArticles;
        $data['totalResultCount'] = $topicArticles->count();

        return View::make('site/helptopic/helptopics', $data);
    }

    public function ajaxTopicArticle() {
        if(Request::ajax()) {
            $topicID = Input::get('topicID');
            $topicArticles = HelpArticle::where('topicID', $topicID)->get();

            $data['topicArticles'] = $topicArticles;
            $output['totalResultCount'] = $topicArticles->count();
            $output['success'] = "success";
        }

        $output['results'] = View::make('site/helptopic/ajaxtopicarticle', $data)->render();

        return json_encode($output);
    }

    public function helpTopicDetails($articleID) {
        $data['helpTopicMenuActive'] = "active";
        $data['topicLists'] = HelpTopic::all();

        $data['articleDetail'] = HelpArticle::find($articleID);

        return View::make('site/helptopic/helptopicdetail', $data);
    }

    public function resetPasswordEmail() {
        if(Request::ajax()) {
            $email = Input::get('email');
            $user = User::where('email', $email)->first();

            if($user) {
                $date = new DateTime();
                $time = $date->getTimestamp();

                $alphabets = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

                $password = substr(md5($time.$alphabets), 13, 6);
                $newPassword = Hash::make($password);

                $user->password = $newPassword;
                $user->passwordChangeRequired = 'Yes';
                $user->save();

                $fieldValues = [
                    'FIRST_NAME'    => $user->firstName,
                    'NEW_PASSWORD'  => $password,
                    'SITE_NAME'     => Setting::get('siteName')
                ];

                $newAccountTemplate = AdminEmailTemplate::where('id', 'FORGOTPASS')->first();
                $emailText = $newAccountTemplate->content;

	            $mailSettings = CommonController::loadSMTPSettings("inbound");

                $emailInfo = [
	                'from' => $mailSettings["from"]["address"],
	                'fromName' => $mailSettings["from"]["name"],
	                'replyTo' => $mailSettings["replyTo"],
                    'subject'   => $newAccountTemplate->subject,
                    'to'        => $email,
                    'bccEmail' => ''
                ];
                $attachments = [];
                CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);

                $data['success'] = "success";
                $data['error'] = "New password send to this email id.";
            }
            else {
                $data['success'] = "fail";
                $data['error'] = "This is not a registered email id.";
            }
        }

        return json_encode($data);
    }

    public function defaultEmailSettingToYes() {
        $defaultSetting = Input::get('defaultSetting');

        if($defaultSetting == "Yes") {
            $userCount = SmtpSetting::where('userID', '=', Auth::user()->get()->id)->count();

            if($userCount > 0) {
                SmtpSetting::where('userID', '=', Auth::user()->get()->id)->delete();
                Session::flash('successMessage', 'Email Setting Successfully Updated');
            }
        }

        $data['success'] = "success";
        return json_encode($data);
    }

	public function saveAndCheckEmailSetting() {
        $error = "";

        //validation Array
        $validatorArray = [
            'fromEmail' => 'required|email',
            'replyToEmail' => 'required|email',
            'host' => 'required',
            'port' => 'required',
            'username' => 'required',
            'password' => 'required',
			'security'  => 'required|in:No,tls,ssl',
        ];

        //Validation Rules for user Info
        $validator = Validator::make(Input::all(), $validatorArray);

        if ($validator->fails()) {
            //If Validation rule fails
            $messages = $validator->messages();
            foreach ($messages->all() as $message) {
                $error .= $message . '<br>';
            }

            $data['success'] = "fail";
            $data['error'] = $error;
        }
        else {
            $fromEmail = Input::get('fromEmail');
            $replyToEmail = Input::get('replyToEmail');
            $host = Input::get('host');
            $port = Input::get('port');
            $username = Input::get('username');
            $password = Input::get('password');
            $security = Input::get('security');

            Config::set("mail.host", $host);
            Config::set("mail.port", $port);
            Config::set("mail.username", $username);
            Config::set("mail.password", $password);
			Config::set("mail.encryption", $security != 'No' ? $security : null);

            $user = $this->user;

            try {
                $emailInfo = [
                    'from'      => $fromEmail,
                    'fromName'  => 'Sanityos',
                    'replyTo'   => $replyToEmail,
                    'subject'   => "Testing Email",
                    'to'        => $fromEmail,
                    'bccEmail' => $user->bccEmail
                ];

                $attachments = [];
				CommonController::reloadMailConfig();
                CommonController::prepareAndSendEmail($emailInfo, "This is a Testing Email.", [], $attachments, true);

				SmtpSetting::where('userID', '=', $user->id)->delete();

                //Saving setting
                $newSetting = new SmtpSetting();
                $newSetting->userID = $user->id;
                $newSetting->fromEmail = $fromEmail;
                $newSetting->replyToEmail = $replyToEmail;
                $newSetting->host = $host;
                $newSetting->port = $port;
                $newSetting->userName = $username;
                $newSetting->password = $password;
                $newSetting->security = $security;
                $newSetting->save();

                Session::flash('successMessage', 'Email Setting Successfully Updated');
                $data['success'] = "success";

            }
            catch (Exception $e) {
                $data['success'] = "fail";
                $data['error'] = "Unable to send email. Please Check following details <br>". $e->getMessage();
            }
        }

        return json_encode($data);
    }

	function updateDialerSetting() {
		if(Request::ajax()) {
			$enableCall = 'No';
			$enablePowerDial = 'No';

            $callOption = Input::get('callOption');

            if($callOption == 'enableCall'){
                $enableCall = 'Yes';
            }
            else if($callOption == 'enablePowerDial'){
                $enablePowerDial = 'Yes';
            }

			$user = $this->user;

			$user->enablePowerDial = $enablePowerDial;
			$user->enableCall = $enableCall;
			$user->save();

			Session::flash('successMessage', 'Dialer Settings Successfully Updated');

			return ['success' => 'success'];
		}
	}

	function enableTimer(){
		$enableTimer = Input::get('enableTimer');

		$user = $this->user;
		$user->enableCallTimer = $enableTimer;
		$user->save();

		return ['status' => 'success'];
	}

    function accountClose() {
        $user = $this->user;
        $user->accountStatus = 'Blocked';
        $user->save();

        Session::put('action', 'accountBlocked');

        return ['status' => 'success', 'action' => 'redirect', 'url' => \URL::route('user.logout')];
    }

    function addBccEmail() {
        $validator = Validator::make(Input::all(), ['bccEmail' => 'email']);

        if($validator->fails()) {
            return ['status' => 'fail', 'error' => $validator->errors()];
        }
        else {
            $user = $this->user;
            $user->bccEmail = Input::get('bccEmail');
            $user->save();

            return ['status' => 'success', 'message' => 'Bcc Email updated'];
        }
    }

    function resendVerificationLink() {
        $user = $this->user;
        $user->email_verification = 'No';
        $user->verification_code = md5(time().$user->firstName);
        $user->save();

        $template = AdminEmailTemplate::where('id', 'EMAIL_VERIFICATION')->first();

        $fieldValues = [
            'FIRST_NAME' => $user->firstName,
            'LAST_NAME' => $user->lastName,
            'SITE_NAME' => Setting::get('siteName'),
            'SUPPORT_EMAIL' => Setting::get('supportEmail'),
            'LINK' => \URL::route('home.register.verify', [$user->verification_code])
        ];

        $emailText = $template->content;
        $attachments = [];

        $mailSettings = CommonController::loadSMTPSettings("inbound");

        $emailInfo = [
            'from' => $mailSettings["from"]["address"],
            'fromName' => $mailSettings["from"]["name"],
            'replyTo' => $mailSettings["replyTo"],
            'subject' => $template->subject,
            'to' => $user->email
        ];

        CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);

        return ['status' => 'success', 'message' => 'Email verification mail has been sent to your email address.'];
    }

	public function setPaymentInfo(\Illuminate\Http\Request $request) {
		if($request->ajax()) {
			$duration = trim($request->input('duration'));
			$currency = trim($request->input('currency'));

            $payment_method = $request->input('paymentMethod');

            if($payment_method == 'card') {
                $firstname = trim($request->input('name'));
                $lastname = trim($request->input('lastname'));
                $phone = trim($request->input('phone'));

                $cardNumber = trim($request->input('cardNumber'));
                $cvv = trim($request->input('cvv'));
                $expMonth = trim($request->input('expMonth'));
                $expYear = trim($request->input('expYear'));

                $country = trim($request->input('country'));
                $address1 = trim($request->input('address1'));
                $state = trim($request->input('state'));
                $city = trim($request->input('city'));
                $pincode = trim($request->input('pincode'));
                $countryCode = trim($request->input('countryCode'));

                $billing_country = trim($request->input('billing_country'));
                $billing_address1 = trim($request->input('billing_address1'));
                $billing_state = trim($request->input('billing_state'));
                $billing_city = trim($request->input('billing_city'));
                $billing_pincode = trim($request->input('billing_pincode'));
                $billing_countryCode = trim($request->input('billing_countryCode'));

                $name = $firstname.'|'.$lastname;

                $card_details = [
                    'cardNumber' => $cardNumber,
                    'cvv' => $cvv,
                    'expMonth' => $expMonth,
                    'expYear' => $expYear
                ];

                $billing_details = [
                    'country' => $billing_country,
                    'address1' => $billing_address1,
                    'city' => $billing_city,
                    'state' => $billing_state,
                    'pincode' => $billing_pincode,
                    'countryCode' => $billing_countryCode,
                ];

                $address_details = [
                    'country' => $country,
                    'address1' => $address1,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode,
                    'countryCode' => $countryCode
                ];

                $encryptCardValue = \Crypt::encrypt(json_encode($card_details));
				Session::put('card_details', $encryptCardValue);

                $insert = [
                    'recurring_type' => $duration,
                    'currency' => $currency,
                    'name' => $name,
                    'phone_no' => $phone,
                    'card_details' => 'xxxxxxxxxxxx'.substr($cardNumber, -4),
                    'billing_details' => json_encode($billing_details),
                    'address_details' => json_encode($address_details),
                    'payment_method' => 'Card'
                ];
            }
            else {
                $insert = [
                    'recurring_type' => $duration,
                    'currency' => $currency,
                    'name' => '',
                    'phone_no' => '',
                    'card_details' => '',
                    'billing_details' => '',
                    'address_details' => '',
                    'payment_method' => 'Paypal'
                ];
            }


			if($this->user) {
				$this->user->setPaymentInfo($insert);
			} else {
				Session::put('userPaymentInfo', $insert);
			}
            return ['status' => 'success'];
		}
	}

    public function payment(\Illuminate\Http\Request $request) {
	    $columnNames = [
		    "USD" => ['Monthly' => 'priceUSD', 'Annually' => 'priceUSD_year'],
		    "GBP" => ['Monthly' => 'priceGBP', 'Annually' => 'priceGBP_year'],
		    "EUR" => ['Monthly' => 'priceEuro', 'Annually' => 'priceEuro_year'],
	    ];

        ini_set('max_execution_time', 0);

	    $currentTeamSize = 0;
        $action = '';
        $payment_method = 'Paypal';

        if($request->has('action')) {
            $action = $request->get('action');
        }

	    if($this->user) {
		    $user = $this->user;
		    $payment_info = $user->getPaymentInfo();

		    $recurring_type = $payment_info->recurring_type;
		    $currency = $payment_info->currency;

		    $licenseDetail = License::find($user->id);
		    $totalUsers = $licenseDetail->licenseVolume; //doesn't include admin

		    if($user->isTrial) {
                $userLicenceType = 'TrialRenew';
		    }
		    else {
                $userLicenceType = 'Renew';
		    }

		    $currentTeamSize = User::where('manager', $this->user->id)->count();

            $action = $request->get('action');

            if($action == 'add') $userLicenceType = 'CapacityIncrease';
            elseif($action == 'remove') $userLicenceType = 'CapacityDecrease';
            elseif($action == 'resubscribe') $userLicenceType = 'Resubscribe';

            $payment_method = $payment_info->payment_method;

            $payment_info = (array) $payment_info;
            $user_info = $this->user->toArray();
	    }
	    else {

			throw new Exception('Unsupported');
		    $userLicenceType = 'New';
		    $payment_info = Session::get('userPaymentInfo');
		    $user_info = Session::get('userInfo');

		    $recurring_type = $payment_info['recurring_type'];
		    $currency = $payment_info['currency'];

		    $licenceType = $user_info['licenseType'];
		    $licenceTypeDetail = LicenseType::find($licenceType);
            $payment_method = $payment_info['payment_method'];

		    $totalUsers = $licenceTypeDetail->volume;
	    }

        $perUserPrice = $licenseDetail->{$columnNames[$currency][$recurring_type]};
        $discount = 0;

        $selectedUsers = $request->input('selectedUsers'); //delta, for trial - all users without admin
        $freeUsers = $licenseDetail->free_users;

		$fullPrice = 0;

        if($action == 'add') {
            $licencePrice = $selectedUsers * $perUserPrice;
            $actualPrice = $licencePrice;

            $volume = $totalUsers + $selectedUsers;

            if($volume <= $freeUsers) {
				$fullPrice = $perUserPrice; //just admin
			} else {
				$extraUsers = $volume - $freeUsers;
                $fullPrice = $perUserPrice * ($extraUsers + 1); //add admin price
			}
            if($volume < $freeUsers) {
                $volume = $freeUsers;
            }
        }
        elseif ($action == 'remove') {
            $remainingUsers = $totalUsers - $selectedUsers;
            if($remainingUsers < $freeUsers) {
                $selectedUsers = $totalUsers - $freeUsers;
            }
            $licencePrice = $selectedUsers * $perUserPrice;
            $actualPrice = $licencePrice;

            $volume = $totalUsers - $selectedUsers;

            if($volume <= $freeUsers) {
				$fullPrice = $perUserPrice; //just admin
			} else {
				$extraUsers = $volume - $freeUsers;
                $fullPrice = $perUserPrice * ($extraUsers + 1); //add admin price
			}
            if($volume < $freeUsers) {
                $volume = $freeUsers;
            }
        }
        else {

            if($selectedUsers < $currentTeamSize) {
                return ['status' => 'fail', 'message' => 'Remove team member first'];
            }

            if($selectedUsers <= $freeUsers) {
                $licencePrice = $perUserPrice;//just admin
                $actualPrice = $licencePrice;

                if($recurring_type == 'Annually') {
                    $actualPrice = $licenseDetail->{$columnNames[$currency]['Monthly']} * 12;
                    $discount = $actualPrice - $licencePrice;
                }
            }
            else {
				$extraUsers = $selectedUsers - $freeUsers;
                $licencePrice = $perUserPrice * ($extraUsers + 1); //add admin price

                $actualPrice = $licencePrice;

                if($recurring_type == 'Annually') {
                    $actualPrice = $licenseDetail->{$columnNames[$currency]['Monthly']} * 12 * ($extraUsers + 1);
                    $discount = $actualPrice - $licencePrice;
                }
            }

            $volume = $selectedUsers;

            if($selectedUsers < $freeUsers) {
                $volume = $freeUsers;
            }
			$fullPrice = $licencePrice;
        }

        Session::put('volume', $volume);
		Session::put('licencePrice', $licencePrice);
		Session::put('discount', $discount);
		Session::put('actualPrice', $actualPrice);

        if(strtolower($payment_method) == 'paypal') {
            $payer = new Payer();
            $payer->setPaymentMethod("paypal");

            $paypalResult = $this->processPaypalTransaction($licenseDetail, $recurring_type, $licencePrice, $fullPrice, $currency, $userLicenceType, $action, 'paypal', $payer);
            if(!$paypalResult['success']) {
                return $this->handlePaypalException($paypalResult['exception']);
            }
			return [
				'success' => 'success',
				'redirect_url' => $paypalResult['agreement']->getApprovalLink()
			];
        }
        else {
            $addressDetails = json_decode($payment_info['address_details'], true);

            $billingAddress = new Address();
            $billingAddress->setLine1($addressDetails['address1'])
                ->setCity($addressDetails['city'])
				->setState($addressDetails['state'])
                ->setCountryCode($addressDetails['countryCode'])
                ->setPostalCode($addressDetails['pincode']);
            \Log::error("CC details : " . print_r($billingAddress, true));

            $payer = new Payer();
            $payer->setPaymentMethod('credit_card')
                  ->setPayerInfo(new PayerInfo(['email' => $user_info['email']]));

            $cardDetails = json_decode(\Crypt::decrypt(Session::get('card_details')), true);

            $name = explode('|', $payment_info['name']);

			$cardValidate = \CreditCardValidator::validCreditCard($cardDetails['cardNumber']);
			if(!$cardValidate['valid']) {
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Incorrect credit card number",
				];
			}

			if(!in_array($cardValidate['type'], ['visa', 'mastercard', 'discover', 'amex'])) {
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Unsupported credit card type : {$cardValidate['type']}",
				];
			}

            $creditCard = new CreditCard();
            $creditCard->setType($cardValidate['type'])
                ->setNumber($cardDetails['cardNumber'])
                ->setExpireMonth($cardDetails['expMonth'])
                ->setExpireYear($cardDetails['expYear'])
                ->setCvv2($cardDetails['cvv']);

            if(isset($name[0]))
                $creditCard->setFirstName($name[0]);
            else
                $creditCard->setFirstName($user_info['firstName']);

            if(isset($name[1]))
                $creditCard->setLastName($name[1]);
            else
                $creditCard->setLastName($user_info['lastName']);

            $creditCard->setBillingAddress($billingAddress);

            $fundingInstrument = new FundingInstrument();
            $fundingInstrument->setCreditCard($creditCard);

            $payer->setFundingInstruments(array($fundingInstrument));

            $paypalResult = $this->processPaypalTransaction($licenseDetail, $recurring_type, $licencePrice, $fullPrice, $currency, $userLicenceType, $action, 'credit_card', $payer);
			
			if(!$paypalResult['success']) {
                return $this->handlePaypalException($paypalResult['exception']);
            }

			//if credit card agreement isn't active then it's canceled for some reason 
			if($paypalResult['agreement']->state != 'Active') {
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Please check credit card details" 
				];
			}

            return $this->executePaymentAgreement($paypalResult['agreement'], $userLicenceType);
        }
    }

    public function paymentShowContainer() {
        return view("site.payment.showcontainer");
    }

	public function paymentShow() {
        $user = $this->user;

		$cardNumber = [];
		$addressDetails = [];
		$billingAddress = [];

        $firstName = '';
        $lastName = '';

		$data['currency'] = 'USD';
        $data['action'] = '';

		$currentTeamSize = 0;

		if($user) {
			$payment_info = $user->payment_info;
			if($payment_info) {
				//$cardNumber = (isset($payment_info->card_details) && $payment_info->card_details != '') ? json_decode(\Crypt::decrypt($payment_info->card_details), true) : [];
				$addressDetails = json_decode($payment_info->address_details, true);
				$billingAddress = json_decode($payment_info->billing_details, true);
				$data['currency'] = $payment_info->currency;

                $name = explode('|', $payment_info->name);

                if(isset($name[0]))
                    $firstName = $name[0];

                if(isset($name[1]))
                    $lastName = $name[1];
			}

			$licenseDetail = License::find($user->id);
			$freeUsers  = $licenseDetail->free_users;
			$totalUsers = $licenseDetail->licenseVolume;

			if($user->isTrial) {
                $data['purchaseType'] = 'new';
			}
			else {
                $data['purchaseType'] = 'renew';
			}

			$currentTeamSize = User::where('manager', $this->user->id)->count();

            if(Session::has('action')) {
                $action = Session::get('action');

                if($action == 'teamUpdate') {
                    $data['action'] = 'teamUpdate';
                    $data['purchaseType'] = 'teamUpdate';
                }
                elseif($action == 'resubscribe') {
                    $data['action'] = 'resubscribe';
                    $data['purchaseType'] = 'resubscribe';
                }
            }
		}
		else {
			throw new Exception('Unsupported');

			$userInfo = Session::get('userInfo');
			if($userInfo['preferredCurrency'] == 'priceUSD') $data['currency'] = 'USD';
			elseif($userInfo['preferredCurrency'] == 'priceGBP') $data['currency'] = 'GBP';
			elseif($userInfo['preferredCurrency'] == 'priceEuro') $data['currency'] = 'EUR';

            $data['purchaseType'] = 'new';
		}

        $paidUsers = $totalUsers - $freeUsers;

        $data['freeUsers'] = $freeUsers;
        $data['paidUsers'] = $paidUsers;
        $data['currentTeamSize'] = $currentTeamSize;
        $data['totalUsers'] = $totalUsers;

        $data['licenceTypeDetail'] = $licenseDetail;

        $pricing = $licenseDetail->currencyWisePricing();
        $data['pricing'] = json_encode($pricing);

        $data['teamUser'] = json_encode([
            'free' => $freeUsers,
            'paid' => $paidUsers,
            'total' => $totalUsers,
            'teamSize' => $currentTeamSize
        ]);

		$data['user'] = $user;
        $data['cardNumber'] = $cardNumber;
        $data['addressDetails'] = $addressDetails;
        $data['billingAddress'] = $billingAddress;
        $data['firstName'] = $firstName;
        $data['lastName'] = $lastName;

		if(Session::has('error')) {
			$data['error'] = Session::get('error');
		}

		return view('site.payment.show', $data);
	}

	//return json response with message
	private function handlePaypalException(PayPalConnectionException $e)
	{
		$message = "An error occurred processing payment. Error: " . $e->getMessage();

		//try to decode paypal error 
		$data = json_decode($e->getData(), true);
		if($data) {
			if(isset($data['name'])) {
				if($data['name'] == 'VALIDATION_ERROR') {
					$message = 'Validation error :<br/>';
					if(isset($data['details'])) {
						foreach($data['details'] as $detail) {
							if(isset($detail['field']) && isset($detail['issue'])) {
								$field = str_replace('payer.funding_instruments[0].', '', $detail['field']);
								$message  .= "Error in field : {$field}, issue : {$detail['issue']}<br/>";
							} elseif(isset($detail['issue'])) {
								$message  .= "issue : {$detail['issue']}<br/>";
							} elseif(isset($detail['field'])) {
								$message  .= "Error in field : {$detail['field']}<br/>";
							}
						}
					}
				} elseif($data['name'] == 'SUBSCRIPTION_UNMAPPED_ERROR' && isset($data['message'])) {
					$message = 'Error : ' . $data['message'] . '<br/>';
				} else {
					$message = 'Error type : ' . $data['name'] . '<br/>';
					if(isset($data['message'])) {
						$message .= 'Error : ' . $data['message'] . '<br/>';
					}
				}
			}
		}

		return [
			'success' => 'fail',
			'message' => $message
		];
	}

	private function processPaypalTransaction(License $licenseDetail, $recurring_type, /*delta*/$licencePrice, /*new full price*/$fullPrice, $currency, $userLicenceType, $action, $payment_method, $payer) 
	{
			/* testing error
			$exception = new PayPalConnectionException(
					'https://api.sandbox.paypal.com/v1/payments/billing-agreements/', 
					'Got Http response code 400 when accessing https://api.sandbox.paypal.com/v1/payments/billing-agreements/'
				);

			$exception->setData('{"name":"VALIDATION_ERROR","details":[{"field":"payer.funding_instruments[0].credit_card","issue":"Invalid expiration (cannot be in the past)"},
	{"field":"payer.funding_instruments[0].credit_card.number","issue":"Value is invalid"}],"message":"Invalid request - see details",
"information_link":"https://developer.paypal.com/webapps/developer/docs/api/#VALIDATION_ERROR","debug_id":"74132fd5853ae"}');

			return [
				'success' => false,
				'exception' =>$exception 
			];
			*/

        try {
            $apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));
            $setupFee = true;
            $setupFeePrice = $licencePrice;

            // region Calculate license's expiry date
            $today = Carbon::today();
            if($recurring_type == 'Monthly') {
                $startDate = Carbon::now()->addMonth()->toIso8601String();
                Session::put('expiryDate', Carbon::now()->addMonth());
            }
            elseif($recurring_type == 'Annually') {
                $startDate = Carbon::now()->addYear()->toIso8601String();
                Session::put('expiryDate', Carbon::now()->addYear());
            }

            if($this->user && !$this->user->isTrial) {
                $transaction = $this->user->getLatestTransaction();
                if($transaction) {
                    $nextBillingDate = $transaction->nextBillingDate;

                    if($today >= Carbon::parse($nextBillingDate)) {
                        if($recurring_type == 'Monthly') {
                            $billingDate = Carbon::parse($nextBillingDate)->addMonth();
							if($today >= $billingDate) {
								//user expired longer then month, start from today
								$billingDate = Carbon::now()->addMonth();
							}
							Session::put('expiryDate', $billingDate);
							$startDate = $billingDate->toIso8601String();
                        }
                        elseif($recurring_type == 'annually') {
                            $billingDate = Carbon::parse($nextBillingDate)->addYear();
							if($today >= $billingDate) {
								//user expired longer then month, start from today
								$billingDate = Carbon::now()->addYear();
							}
                            Session::put('expiryDate', $billingDate);
                            $startDate = $billingDate->toIso8601String();
                        }
                    }
                    else {
                        Session::put('expiryDate', Carbon::parse($nextBillingDate));
                        $startDate = Carbon::parse($nextBillingDate)->toIso8601String();
                    }
                }
            }
            //endregion

            if(in_array($action, ['add', 'remove', 'resubscribe'])) {
				$planId_toDelete = 0;
				$transactionId_toCancel = 0;

                $transaction = $this->user->getLatestTransaction();
				
				$planId_toDelete = $transaction->plan_id;
				$topTransaction = $this->user->getLatestTopTransaction();
				if($topTransaction) {
					$transactionId_toCancel = $topTransaction->id;
				}
			}

			//code below calculates setup fee
            if($action == 'add') {

                if($recurring_type == 'Monthly') {
					//setup fee is partial till next 'big' payment
					$setupFee = true;
                    $diff = Carbon::parse($nextBillingDate)->diffInDays(Carbon::now());
                    $setupFeePrice = ($licencePrice / 30 ) * $diff;

					//current amount + added price
					$licencePrice = $fullPrice;

					//if user is expired then pay full amount
                    if($today >= Carbon::parse($nextBillingDate)) {
						$setupFeePrice = $licencePrice;
                    }
                }
                elseif($recurring_type == 'Annually') {
					//setup fee is partial till next 'big' payment
					$setupFee = true;
                    $diff = Carbon::parse($nextBillingDate)->diffInMonths(Carbon::now());
                    $setupFeePrice = ($licencePrice / 12 ) * $diff;

					//current amount + added price
                    $licencePrice = $fullPrice;

					//if user is expired then pay full amount
                    if($today >= Carbon::parse($nextBillingDate)) {
                        $setupFeePrice = $licencePrice;
                    }
                }
                Session::put('nextBillingAmount', $licencePrice);
            }
            elseif ($action == 'remove') {
                $setupFee = false;

                $licencePrice = $fullPrice;

                if($today >= Carbon::parse($nextBillingDate)) {
                    $setupFee = true;
                    $setupFeePrice = $licencePrice;
                }

                Session::put('nextBillingAmount', $licencePrice);
            }
            elseif ($action == 'resubscribe') {

                if($today < $nextBillingDate) {
                    $setupFee = false;
                }
                else {
                    $setupFee = true;
                    $setupFeePrice = $fullPrice;
                }

                $licencePrice = $fullPrice;
                Session::put('nextBillingAmount', $licencePrice);
            }

			$paidLicenseType = $licenseDetail->getPaidLicenseType();
			$agreementDescription = str_limit($paidLicenseType->description, 120);
			$agreementDescription .= ' (' . $currency . ' ' .  $licencePrice;
			
			if(in_array($recurring_type, ['Monthly', 'Annually'])) {
				$agreementDescription .= ' ' . $recurring_type;
				if($setupFee) {
					if(in_array($action, ['add', 'remove'])) {
						$agreementDescription .= ' starting from ' . Carbon::parse($startDate)->toDateString();
						if(in_array($action, ['add'])) {
							$agreementDescription .= ', payment for new users : ' . $currency . ' ' .  $setupFeePrice;
						}
					}
				}
			}
			$agreementDescription .= ')';


            $plan = new Plan();
            $plan->setName($paidLicenseType->name)
                ->setDescription($agreementDescription)
                ->setType('INFINITE');

            // Payment definition
            if($recurring_type == 'Monthly') { $frequency = 'MONTH'; }
            elseif($recurring_type == 'Annually') { $frequency = 'YEAR'; }

            $payment_definition = new PaymentDefinition();
            $payment_definition->setName($agreementDescription)
                ->setType('REGULAR')
                ->setFrequencyInterval("1")
                ->setFrequency($frequency)
                ->setCycles(0)
                ->setAmount(new Currency(['value' => $licencePrice, 'currency' => $currency]));

            // Merchant Preference
            $merchant_preference = new MerchantPreferences();

            $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=new';
            $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=new';

            if($userLicenceType == 'TrialRenew') {
                $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=trialrenew';
                $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=trialrenew';
            }
            elseif($userLicenceType == 'CapacityIncrease') {
                $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=capacityincrease';
                $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=capacityincrease';
            }
            elseif($userLicenceType == 'CapacityDecrease') {
                $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=capacitydecrease';
                $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=capacitydecrease';
            }
            elseif($userLicenceType == 'Renew') {
                $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=renew';
                $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=renew';
            }
            elseif($userLicenceType == 'Resubscribe') {
                $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=resubscribe';
                $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=resubscribe';
            }
			
			if($payment_method == 'paypal') {
				$pendingTransaction = new PendingTransaction;
				$pendingTransaction->start_date = Carbon::parse($startDate)->format('Y-m-d H:i:s');
				$pendingTransaction->user_id = $this->user->id;
				$pendingTransaction->currency = $currency;
				$pendingTransaction->amount = $licencePrice;
				$pendingTransaction->type = $userLicenceType;
				
				$pendingTransaction->save();

				$returnUrl .= "&pendingtr=" . $pendingTransaction->id;
				$cancelUrl .= "&pendingtr=" . $pendingTransaction->id;
			}


            $merchant_preference->setReturnUrl($returnUrl);
            $merchant_preference->setCancelUrl($cancelUrl);
            $merchant_preference->setMaxFailAttempts("3");
			$merchant_preference->setInitialFailAmountAction('CANCEL');

            if($setupFee === true) {
                $merchant_preference->setSetupFee(new Currency(['value' => $setupFeePrice, 'currency' => $currency]));
            }

            $plan->setPaymentDefinitions([$payment_definition])
                ->setMerchantPreferences($merchant_preference);

            $plan->create($apiContext);

            $patch = new Patch();
            $value = new PayPalModel('{"state" : "ACTIVE"}');

            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);

            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);
            $plan->update($patchRequest, $apiContext);

            // Create plan agreement

            /*$startDate = Carbon::now()->addDay()->toIso8601String();
            Session::put('expiryDate', Carbon::now()->addDay());*/


            $agreement = new Agreement();
            $agreement->setName($paidLicenseType->name)
                      ->setDescription($agreementDescription)
                      ->setStartDate($startDate);

            $createdPlan = new Plan();
            $createdPlan->setId($plan->getId());

            Session::put('paypalPlanId', $plan->getId());

            $agreement->setPlan($createdPlan);
            $agreement->setPayer($payer);
			
			//\Log::error("create paypal agreement : " . print_r($agreement, true));

            $agreement->create($apiContext);
			
			\Log::error("created paypal agreement : " . print_r($agreement, true));
			
			if($payment_method == 'paypal') {
				$pendingTransaction->start_date = Carbon::parse($agreement->getStartDate())->format('Y-m-d H:i:s');
				$pendingTransaction->plan_id = $agreement->getPlan()->getId();
				
				$pendingTransaction->save();
			}
			
			
			//delete old plan
			if(in_array($action, ['add', 'remove', 'resubscribe'])) {
				$this->deletePaypalAgreement($apiContext, $planId_toDelete, $transactionId_toCancel, 'User has changed subscription (users has been added/removed)');
			}

			return [
				'success' => true,
				'agreement' => $agreement
			];
        } catch(PayPalConnectionException $e) {
            \Log::error('paypal error : ' . $e->getMessage()." ".$e->getData());
            \Log::error($e->getTraceAsString());
            //Session::flash('errorMessage', 'Payment failed: try again!!');
			return [
				'success' => false,
				'exception' => $e
			];
        }
    }

	function deletePaypalAgreement($apiContext, $planId_toDelete, $transactionId_toCancel, $description) 
	{
		//delete old plan
		try {
			if($planId_toDelete) {
				$oldPlan = null;
				try {
					$oldPlan = Plan::get($planId_toDelete, $apiContext);
				} catch(PayPalConnectionException $e) {
					//plan not found, ignore it
				}
				if($oldPlan) {
					$oldPlan->delete($apiContext);
				}
			}

			if($transactionId_toCancel) {
				$oldAgreement = Agreement::get($transactionId_toCancel, $apiContext);
				$status = $oldAgreement->getState();
				//\Log::error('status : ' . $status);
				if($status != 'Cancel' && $status != 'Cancelled') {
					$stateDescriptor = new AgreementStateDescriptor();
					$stateDescriptor->setNote('Canceled by SanityOS. ' . $description);
					$oldAgreement->cancel($stateDescriptor, $apiContext);
				}
			}
			
			/* test exception
			$exception = new PayPalConnectionException(
					'https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-SX5TT1FFL4KW/cancel', 
					'Got Http response code 400 when accessing https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-SX5TT1FFL4KW/cancel'
				);
			$exception->setData('{"name":"STATUS_INVALID","message":"Invalid profile status for cancel action; profile should be active or suspended",
				"information_link":"https://developer.paypal.com/webapps/developer/docs/api/#STATUS_INVALID","debug_id":"3f11ab40bafb7"}');
			throw $exception;
			 */

		} catch(PayPalConnectionException $e) {
			\Log::error('error on deletion of old plan/agreement : ' . $e->getMessage()." ".$e->getData());
			\Log::error($e->getTraceAsString());
			
			CommonController::loadSMTPSettings("inbound");

			$data['emailText'] = "Message : " . $e->getMessage() . "<br/>\n";
			$data['emailText'] .= "User : " . $this->user->email . " , id : " . $this->user->id . "<br/>\n";
			$data['emailText'] .= "Error data : " . $e->getData() . "<br/>\n";
			$data['emailText'] .= "Trace : " . $e->getTraceAsString() . "<br/>\n";

			Mail::send('emails.email', $data, function ($message) {
				$message->subject("error on deletion of old plan/agreement");
				$message->from('support@sanityos.com', 'Sanity OS');
				$message->to('support@sanityos.com');
			});
		}
	}

    function editPaymentDetail() {
        $user = $this->user;
        if($user->isTrial) {
            return Redirect::back();
        }

        $paymentInfo = $user->getPaymentInfo();

        $data['paymentInfo'] = [];

        $firstName = '';
        $lastName = '';

        if($paymentInfo) {
            $data['paymentInfo'] = $paymentInfo;
            $data['cardNumber'] = (isset($paymentInfo->card_details) && $paymentInfo->card_details != '') ? $paymentInfo->card_details : '';
            $data['addressDetails'] = json_decode($paymentInfo->address_details, true);

            $name = explode('|', $paymentInfo->name);

            if(isset($name[0]))
                $firstName = $name[0];

            if(isset($name[1]))
                $lastName = $name[1];
        }

        $data['transaction'] = Transaction::where('id', 'LIKE', 'I-%')
            ->where('purchaser', $user->id)
            ->orderBy('time', 'DESC')
            ->first();

        $data['errorMessage'] = Session::get('errorMessage');
        $data['errorDetails'] = Session::get('errorDetails');

        $data['firstName'] = $firstName;
        $data['lastName'] = $lastName;

        return view('site.payment.edit', $data);
    }

    function updatePaymentDetail(\Illuminate\Http\Request $request) {
        ini_set('max_execution_time', 0);
		$rules = [
			'payment_method' => 'required|in:card,paypal',
			'billing_email' => 'required|email',
		];

        $this->validate($request, $rules);

        $payment_method = $request->input('payment_method');

        $user = $this->user;

        $insert = [];

        if($payment_method == 'card') {
            $rules = [
                'card_number' => 'required',
                'cvv' => 'required',
                'month' => 'required',
                'year' => 'required',
                'fname' => 'required',
                'lname' => 'required',
                'country' => 'required',
                'address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'zipcode' => 'required',
            ];

            $this->validate($request, $rules);
            $fname = trim($request->input('fname'));
            $lname = trim($request->input('lname'));

            $name = $fname.'|'.$lname;

            $cardNumber = trim($request->input('card_number'));
            $cvv = trim($request->input('cvv'));
            $expMonth = trim($request->input('month'));
            $expYear = trim($request->input('year'));

            $country = trim($request->input('country'));
            $countryArr = explode('|', $country);
            $country = $countryArr[0];
            $countryCode = $countryArr[1];
            $address1 = trim($request->input('address'));
            $state = trim($request->input('state'));
            $city = trim($request->input('city'));
            $pincode = trim($request->input('zipcode'));

            $card_details = [
                'cardNumber' => $cardNumber,
                'cvv' => $cvv,
                'expMonth' => $expMonth,
                'expYear' => $expYear
            ];

            $address_details = [
                'country' => $country,
                'address1' => $address1,
                'city' => $city,
                'state' => $state,
                'pincode' => $pincode,
                'countryCode' => $countryCode
            ];

            $encryptCardValue = \Crypt::encrypt(json_encode($card_details));

            $insert = [
                'name' => $name,
                'card_details' => 'xxxxxxxxxxxx'.substr($cardNumber, -4),
                'billing_details' => json_encode($address_details),
                'address_details' => json_encode($address_details),
                'payment_method' => 'Card'
            ];
        }
        else {
            $rules = [
                'paypal_email' => 'required|email'
            ];

            $this->validate($request, $rules);
            $insert['payment_method'] = 'Paypal';
            $insert['paypal_email'] = $request->input('paypal_email');
        }

        $oldPaymentInfo = $user->getPaymentInfo();
        # case 1 When payment method same and details different
        # case 2 When details are different
        $oldPaymentMethod = $oldPaymentInfo->payment_method;
        $newPaymentMethod = $insert['payment_method'];

        $isChangeRequired = false;

        if($oldPaymentMethod == $newPaymentMethod) {
            if($oldPaymentInfo->payment_method == 'Card') {
                // if payment method same check the card details
                $oldCardNumber = $oldPaymentInfo->card_details;
                if($oldCardNumber != $insert['card_details']) {
                    $isChangeRequired = true;
                }
            }
            elseif($oldPaymentInfo->payment_method == 'Paypal') {
                if($oldPaymentInfo->paypal_email != $insert['paypal_email']) {
                    $isChangeRequired = true;
                }
            }
        }
        else {
            $isChangeRequired = true;
        }
		
		$insert['billing_email'] = $request->input('billing_email');

        if(!$isChangeRequired) {
            $this->user->setPaymentInfo($insert);

			/*
			//test
			$trans = Transaction::where('purchaser', '=', $this->user->id)
				->orderBy('time', 'desc')
				->first()
				;
			CommonController::sendInvoiceEmailToUser($trans);
			 */

            return Redirect::route('user.paymentdetail.edit')->with('successMessage', 'Payment details updated');
        }

        try {
            $apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));

			$planId_toDelete = 0;
			$transactionId_toCancel = 0;

            #1.Fetch last transaction
            $latestTransaction = $user->getLatestTransaction();
            $amount = $latestTransaction->nextBillingAmount;
			
			$planId_toDelete = $latestTransaction->plan_id;
			$topTransaction = $user->getLatestTopTransaction();
			if($topTransaction) {
				$transactionId_toCancel = $topTransaction->id;
				$amount = $topTransaction->nextBillingAmount;
			}


            Session::put('licencePrice', $amount);
            Session::put('nextBillingAmount', $amount);

            $billingDate = Carbon::parse($latestTransaction->nextBillingDate);
            $today = Carbon::today();

            $diff = $today->diffInDays($billingDate);
            $setupFee = false;
            $nextBillingDate = $billingDate;

            if($diff <= 0) {
                $setupFee = true;
                $setUpPrice = $amount;

                if($oldPaymentInfo->recurring_type == 'Annually') {
                    $nextBillingDate = $billingDate->addYear();
                }
                else {
                    $nextBillingDate = $billingDate->addMonth();
                }
            }

            Session::put('expiryDate', $nextBillingDate);

            $planID = $latestTransaction->plan_id;

            try {
                $userPlan = Plan::get($planID, $apiContext);
                $planName = $userPlan->getName();
                $description = 'cloning ' . $userPlan->getDescription();
                $userPlan->delete($apiContext);
            }
            catch(Exception $e) {
                \Log::error("Payment plan get:". $e->getMessage());
                $planName = "Multi User Licence";
                $description = "Multi User";
            }

            $plan = new Plan();
            $plan->setName($planName)
                ->setDescription($description)
                ->setType('INFINITE');

            if($oldPaymentInfo->recurring_type == 'Monthly') { $frequency = 'MONTH'; }
            elseif($oldPaymentInfo->recurring_type == 'Annually') { $frequency = 'YEAR'; }

            $payment_definition = new PaymentDefinition();
            $payment_definition->setName($description)
                ->setType('REGULAR')
                ->setFrequencyInterval("1")
                ->setFrequency($frequency)
                ->setCycles(0)
                ->setAmount(new Currency(['value' => $amount, 'currency' => $oldPaymentInfo->currency]));

            $merchant_preference = new MerchantPreferences();

            $returnUrl = \URL::route('home.paymentreturnurl') . '?success=true&action=detailchange';
            $cancelUrl = \URL::route('home.paymentreturnurl') . '?success=false&action=detailchange';
			
            if($insert['payment_method'] == 'Paypal') {
				$pendingTransaction = new PendingTransaction;
				$pendingTransaction->start_date = Carbon::parse($nextBillingDate)->format('Y-m-d H:i:s');
				$pendingTransaction->user_id = $this->user->id;
				$pendingTransaction->currency = $oldPaymentInfo->currency;
				$pendingTransaction->amount = $amount;
				$pendingTransaction->type = 'PlanDetailChange';
				
				$pendingTransaction->save();

				$returnUrl .= "&pendingtr=" . $pendingTransaction->id;
				$cancelUrl .= "&pendingtr=" . $pendingTransaction->id;
			}

            $merchant_preference->setReturnUrl($returnUrl);
            $merchant_preference->setCancelUrl($cancelUrl);
            $merchant_preference->setMaxFailAttempts("3");
			$merchant_preference->setInitialFailAmountAction('CANCEL');

            if($setupFee === true) {
                $merchant_preference->setSetupFee(new Currency(['value' => $setUpPrice, 'currency' => $oldPaymentInfo->currency]));
            }

            $plan->setPaymentDefinitions([$payment_definition])
                ->setMerchantPreferences($merchant_preference);

            $plan->create($apiContext);

            $patch = new Patch();
            $value = new PayPalModel('{"state" : "ACTIVE"}');

            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);

            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);
            $plan->update($patchRequest, $apiContext);

            $agreement = new Agreement();
            $agreement->setName($planName)
                ->setDescription($description)
                ->setStartDate($nextBillingDate->toIso8601String());

            $createdPlan = new Plan();
            $createdPlan->setId($plan->getId());

            Session::put('paypalPlanId', $plan->getId());

            $agreement->setPlan($createdPlan);

            if($insert['payment_method'] == 'Paypal') {
                $payer = new Payer();
                $payer->setPaymentMethod("paypal");
                $agreement->setPayer($payer);

				\Log::error("create paypal agreement : " . print_r($agreement, true));

                $agreement->create($apiContext);

				\Log::error("created paypal agreement : " . print_r($agreement, true));

                $approvalUrl = $agreement->getApprovalLink();

                Session::put('userPaymentInfo', $insert);

				$pendingTransaction->start_date = Carbon::parse($agreement->getStartDate())->format('Y-m-d H:i:s');
				$pendingTransaction->plan_id = $agreement->getPlan()->getId();
				$pendingTransaction->save();
				
				$this->deletePaypalAgreement($apiContext, $planId_toDelete, $transactionId_toCancel, 'User has changed payment method');

                return Redirect::to($approvalUrl);
            }
            elseif($insert['payment_method'] == 'Card') {
                $payer = new Payer();
                $payer->setPaymentMethod('credit_card')
                      ->setPayerInfo(new PayerInfo(['email' => $this->user->email]));

				$cardValidate = \CreditCardValidator::validCreditCard($card_details['cardNumber']);
				if(!$cardValidate['valid']) {
					throw new Exception('Incorrect credit card number');
				}

				if(!in_array($cardValidate['type'], ['visa', 'mastercard', 'discover', 'amex'])) {
					throw new Exception("Unsupported credit card type : {$cardValidate['type']}");
				}

                $creditCard = new CreditCard();
                $creditCard->setType($cardValidate['type'])
                    ->setNumber($card_details['cardNumber'])
                    ->setExpireMonth($card_details['expMonth'])
                    ->setExpireYear($card_details['expYear'])
                    ->setCvv2($card_details['cvv'])
                    ->setFirstName($fname)
                    ->setLastName($lname);

                $billingAddress = new Address();
                $billingAddress->setLine1($address1)
                    ->setCity($city)
                    ->setState($state)
                    ->setCountryCode($countryCode)
                    ->setPostalCode($pincode);
				\Log::error("CC details : " . print_r($billingAddress, true));

                $creditCard->setBillingAddress($billingAddress);

                $fundingInstrument = new FundingInstrument();
                $fundingInstrument->setCreditCard($creditCard);
                $payer->setFundingInstruments(array($fundingInstrument));

                $agreement->setPayer($payer);

				\Log::error("create paypal agreement : " . str_replace($card_details['cardNumber'], str_repeat('X', strlen($card_details['cardNumber'])), print_r($agreement, true)));

                $agreement->create($apiContext);
				
				\Log::error("created paypal agreement : " . print_r($agreement, true));

                $response = $this->executePaymentAgreement($agreement, 'detailchange');

                $user->setPaymentInfo($insert);

				$this->deletePaypalAgreement($apiContext, $planId_toDelete, $transactionId_toCancel, 'User has changed payment method');

                return $response;
            }
        }
        catch(Exception $e) {
            \Log::error($e);
            Session::flash("errorDetails", $e->getTraceAsString());
            Session::flash("errorMessage", "An error occurred during payment approval. Error: " . $e->getMessage());

            return $this->handleException('detailchange');
        }
    }

    function upgradeTeamMember() {
        Session::put('action', 'teamUpdate');
        return Redirect::route('user.payment.showContainer');
    }

    private function executePaymentAgreement($agreement, $userLicenceType) {
        if ($userLicenceType == "New") {
            try {
                // Register User
                $userInfo = Session::get('userInfo');
                $licenseTypeDetails = LicenseType::find($userInfo['licenseType']);

                \DB::beginTransaction();

                // region user save
                $user = new User();
                $user->firstName = $userInfo['firstName'];
                $user->lastName = $userInfo['lastName'];
                $user->email = $userInfo['email'];
                $user->password = bcrypt($userInfo['password']);
                $user->contactNumber = $userInfo['contactNumber'];
                $user->userType = $licenseTypeDetails->licenseClass;
                $user->companyName = $userInfo['companyName'];
                $user->country = $userInfo['country'];
                $user->latitude = null;
                $user->longitude = null;
                $user->existing = 'No';


                $user->accountCreationDate = new DateTime;
				$user->timeZoneName = $userInfo['timeZoneName'];
                $user->save();
                //endregion

                // region Set User Payment Info
                $userPaymentInfo = Session::get('userPaymentInfo');
                $user->setPaymentInfo($userPaymentInfo);
                // endregion

                // region set user default email templates and send welcome email
                CommonController::setEmailTemplate($user);

                // New user Account and Sending Email
                $newAccountTemplate = AdminEmailTemplate::where('id', 'NEWACCOUNT')->first();

                $fieldValues = [
                    'FIRST_NAME' => $user->firstName,
                    'LAST_NAME' => $user->lastName,
                    'SITE_NAME' => Setting::get('siteName'),
                    'SUPPORT_EMAIL' => Setting::get('supportEmail')
                ];

                $emailText = $newAccountTemplate->content;
                $attachments = [];

                $mailSettings = CommonController::loadSMTPSettings("inbound");

                $emailInfo = [
                    'from' => $mailSettings["from"]["address"],
                    'fromName' => $mailSettings["from"]["name"],
                    'replyTo' => $mailSettings["replyTo"],
                    'subject'  => $newAccountTemplate->subject,
                    'to' => $user->email
                ];

                CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);
                //endregion

                // region Set Transaction of created agreement
                $transaction = new \App\Models\Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "New";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');
                $transaction->time = Carbon::now();
                $transaction->discount = Session::get('discount');
                $transaction->licenseType = $userInfo['licenseType'];
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('licencePrice');
                $transaction->nextBillingDate = Session::get('expiryDate');

                $transaction->save();
                // endregion

                //region  set license
                $license = new License();
                $license->owner = $user->id;
                $license->purchaseTime = Carbon::now();
                $license->expireDate = Session::get('expiryDate');
                $license->licenseType = $userInfo['licenseType'];
                $license->licenseClass = $user->userType;
                $license->licenseVolume = Session::get('volume');

				//copy default values
				$licenseTypeDetail = LicenseType::where('licenseClass', 'Multi')->where('type', 'Paid')->first();
				$license->priceUSD = $licenseTypeDetail->priceUSD;
				$license->priceGBP = $licenseTypeDetail->priceGBP;
				$license->priceEuro = $licenseTypeDetail->priceEuro;

				$license->priceUSD_year = $licenseTypeDetail->priceUSD_year;
				$license->priceGBP_year = $licenseTypeDetail->priceGBP_year;
				$license->priceEuro_year = $licenseTypeDetail->priceEuro_year;
				$license->discount = intval($licenseTypeDetail->discount);
				$license->free_users = intval($licenseTypeDetail->free_users);

                $license->save();
                //endregion

                \DB::commit();

                // region forgot session
                Session::forget('userInfo');
                Session::forget('userPaymentInfo');
                Session::forget('licencePrice');
                Session::forget('discount');
                Session::forget('paypalPlanId');
                Session::forget('expiryDate');
                Session::forget('volume');
                //endregion

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion

                Session::flash("message", 'You are successfully '.strtolower($userPaymentInfo['recurring_type']).' subscribed for sanityos license. Please login with your new account');
				return [
					'success' => 'success',
					'redirect_url' => \URL::route('user.login', ["payment" => "true"])
				];

                //return Redirect::route('user.login', ["payment" => "true"])->with('message', 'You are successfully '.strtolower($userPaymentInfo['recurring_type']).' subscribed for sanityos license. Please login with your new account');
            }
            catch (Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());
                //return $this->handleException($userLicenceType);
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Error: " . $e->getMessage()
				];

            }
        }
        else if ($userLicenceType == "TrialRenew") {
            try {
                \DB::beginTransaction();

                $user = $this->user;

                $user->existing = 'No';
                $user->save();

                // region Get user license details and payment info
                $license = License::where('owner', $user->id)->first();

                if($user->isTrial) {
                    $licenseClass = $license->licenseClass;
                    $licenseTypeDetail = LicenseType::where('licenseClass', $licenseClass)->where('type', 'Paid')->first();
                    $licenseType = $licenseTypeDetail->id;
                }
                else {
                    $licenseType = $license->licenseType;
                    $licenseTypeDetail = LicenseType::find($licenseType);
                }

                $userPaymentInfo = (array) $user->getPaymentInfo();
                // endregion

                // region Update user transaction
                $transaction = new Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "TrialRenew";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');
                $transaction->time = Carbon::now();
                $transaction->discount = Session::get('discount');
                $transaction->licenseType = $licenseType;
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('licencePrice');
                $transaction->nextBillingDate = Session::get('expiryDate');

                $transaction->save();
                //endregion

                //region Update Licence
                License::where('owner', '=', $user->id)->update([
                    'status' => 'Valid',
                    'licenseType' => $licenseType,
                    'purchaseTime' => Carbon::now(),
                    'licenseVolume' => Session::get('volume'),
                    'licenseClass' => $licenseTypeDetail->licenseClass,
                    'expireDate' => Session::get('expiryDate')
                ]);
                //endregion

                //region Update trial user
                $updateTrialUserTrack = [ 'type' => 'Converted', 'updated_at' => Carbon::now() ];
                $user->updateTrialUserTrack($updateTrialUserTrack);
                //endregion

                //region forgot not used session
                Session::forget('paypalPlanId');
                Session::forget('expiryDate');
                Session::forget('volume');
                Session::forget('licencePrice');
                Session::forget('discount');
                //endregion

                //region Set active all team member's account
                $userType = $user->userType;
                $userID = $user->id;

                //Team Members for current User
                $teamMembersIDs = [];
                $teamMembersIDs[] = $userID;
                if ($userType === 'Multi') {
                    $teamMembers = User::select('id')->where('manager', $userID)->get();

                    foreach ($teamMembers as $teamMember) {
                        $teamMembersIDs[] = $teamMember->id;
                    }
                }
                //Setting user's accountStatus to be Active
                User::whereIn('id', $teamMembersIDs)->update(['accountStatus' => 'Active']);
                //endregion

                \DB::commit();

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion
                Session::flash('successMessage', 'Payment successful and your license renewed successfully');
                //return Redirect::to('user/profile');
				return [
					'success' => 'success',
					'redirect_url' => \URL::route('user.profile')
				];
            }
            catch(Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

                //return $this->handleException($userLicenceType);
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Error: " . $e->getMessage()
				];
            }
        }
        else if ($userLicenceType == "Renew") {
            try {
                $user = $this->user;
                \DB::beginTransaction();

                $user->resubscription = 'No';
                $user->existing = 'No';
                $user->save();

                // region Get user license details and payment info
                $license = License::where('owner', $user->id)->first();

                if($user->isTrial) {
                    $licenseClass = $license->licenseClass;
                    $licenseTypeDetail = LicenseType::where('licenseClass', $licenseClass)->where('type', 'Paid')->first();
                    $licenseType = $licenseTypeDetail->id;
                }
                else {
                    $licenseType = $license->licenseType;
                    $licenseTypeDetail = LicenseType::find($licenseType);
                }

                $userPaymentInfo = (array) $user->getPaymentInfo();
                // endregion

                // region Update user transaction
                $transaction = new Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "Renew";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');
                $transaction->time = Carbon::now();
                $transaction->discount = Session::get('discount');
                $transaction->licenseType = $licenseType;
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('licencePrice');
                $transaction->nextBillingDate = Session::get('expiryDate');

                $transaction->save();
                //endregion

                //region Update Licence
                License::where('owner', '=', $user->id)->update([
                    'status' => 'Valid',
                    'licenseType' => $licenseType,
                    'purchaseTime' => Carbon::now(),
                    'licenseVolume' => Session::get('volume'),
                    'licenseClass' => $licenseTypeDetail->licenseClass,
                    'expireDate' => Session::get('expiryDate')
                ]);
                //endregion

                //region forgot not used session
                Session::forget('paypalPlanId');
                Session::forget('expiryDate');
                Session::forget('volume');
                Session::forget('licencePrice');
                Session::forget('discount');
                //endregion

                //region Set active all team member's account
                $userType = $user->userType;
                $userID = $user->id;

                //Team Members for current User
                $teamMembersIDs = [];
                $teamMembersIDs[] = $userID;
                if ($userType === 'Multi') {
                    $teamMembers = User::select('id')->where('manager', $userID)->get();

                    foreach ($teamMembers as $teamMember) {
                        $teamMembersIDs[] = $teamMember->id;
                    }
                }
                //Setting user's accountStatus to be Active
                User::whereIn('id', $teamMembersIDs)->update(['accountStatus' => 'Active']);
                //endregion

                \DB::commit();

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion
                Session::flash('successMessage', 'Payment successful and your license renewed successfully');
                //return Redirect::to('user/profile');
				return [
					'success' => 'success',
					'redirect_url' => \URL::route('user.profile')
				];
            }
            catch(Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

                //return $this->handleException($userLicenceType);
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Error: " . $e->getMessage()
				];
            }
        }
        else if ($userLicenceType == "CapacityIncrease") {
            try {
                $user = $this->user;

                \DB::beginTransaction();

                //region get user licence and payment info
                $license = License::where('owner', $user->id)->first();
                $licenseType = $license->licenseType;

                $userPaymentInfo = (array) $user->getPaymentInfo();
                //endregion

                //region Update transaction
                $transaction = new Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "CapacityIncrease";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');;
                $transaction->time = Carbon::now();
                $transaction->discount = 0;
                $transaction->licenseType = $licenseType;
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('nextBillingAmount');
                $transaction->nextBillingDate = Session::get('expiryDate');
                $transaction->save();
                //endregion

                //region update Licence
                License::where('owner', '=', $user->id)->update([
                    'status' => 'Valid',
                    'licenseType' => $licenseType,
                    'purchaseTime' => Carbon::now(),
                    'licenseVolume' => Session::get('volume'),
                ]);
                //endregion

                \DB::commit();

                //region forgot unused sessions
                Session::forget('expiryDate');
                Session::forget('volume');
                Session::forget('paypalPlanId');
                Session::forget('nextBillingAmount');
                Session::forget('action');
                Session::forget('licencePrice');
                //endregion

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion

                Session::flash('successMessage', 'Payment plan is updated and team members successfully added in your team');

                //return Redirect::to('user/profile');
				return [
					'success' => 'success',
					'redirect_url' => \URL::route('user.profile')
				];
            }
            catch (Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

                //return $this->handleException($userLicenceType);
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Error: " . $e->getMessage()
				];
            }
        }
        else if ($userLicenceType == "CapacityDecrease") {
            try {
                $user = $this->user;

                \DB::beginTransaction();

                //region Get Licence details and payment info
                $license = License::where('owner', $user->id)->first();
                $licenseType = $license->licenseType;

                $userPaymentInfo = (array) $user->getPaymentInfo();
                //endregion

                //region update transaction
                $transaction = new Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "CapacityDecrease";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');
                $transaction->time = Carbon::now();
                $transaction->discount = 0;
                $transaction->licenseType = $licenseType;
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('nextBillingAmount');
                $transaction->nextBillingDate = Session::get('expiryDate');
                $transaction->save();
                //endregion

                //region update Licence
                License::where('owner', '=', $user->id)->update([
                    'status' => 'Valid',
                    'licenseType' => $licenseType,
                    'purchaseTime' => Carbon::now(),
                    'licenseVolume' => Session::get('volume'),
                ]);
                //endregion

                \DB::commit();

                //region forgot unused sessions
                Session::forget('expiryDate');
                Session::forget('volume');
                Session::forget('paypalPlanId');
                Session::forget('nextBillingAmount');
                Session::forget('action');
                Session::forget('licencePrice');
                //endregion

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion

                Session::flash('successMessage', 'Payment plan is updated and team members successfully removed from your team');

                //return Redirect::to('user/profile');
				return [
					'success' => 'success',
					'redirect_url' => \URL::route('user.profile')
				];
            }
            catch (Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

                //return $this->handleException($userLicenceType);
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Error: " . $e->getMessage()
				];
            }
        }
        else if ($userLicenceType == "detailchange") {
            try {
                $user = $this->user;

                \DB::beginTransaction();

                //region get user licence and payment info
                $license = License::where('owner', $user->id)->first();
                $licenseType = $license->licenseType;

                $userPaymentInfo = (array) $user->getPaymentInfo();
                //endregion

                //region Update transaction
                $transaction = new Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "PlanDetailChange";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');
                $transaction->time = Carbon::now();
                $transaction->discount = 0;
                $transaction->licenseType = $licenseType;
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('nextBillingAmount');
                $transaction->nextBillingDate = Session::get('expiryDate');
                $transaction->save();
                //endregion

                //region Update Licence
                License::where('owner', '=', $user->id)->update([
                    'status' => 'Valid',
                    'purchaseTime' => Carbon::now(),
                    'expireDate' => Session::get('expiryDate')
                ]);
                //endregion

                \DB::commit();

                //region forgot unused sessions
                Session::forget('expiryDate');
                Session::forget('paypalPlanId');
                Session::forget('nextBillingAmount');
                Session::forget('licencePrice');
                //endregion

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion

                Session::flash('successMessage', 'Payment plan is updated successfully');

				//still used redirect
                return Redirect::route('user.paymentdetail.update');
            }
            catch (Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred during payment approval. Error: " . $e->getMessage());

				//still used redirect
                return $this->handleException($userLicenceType);
            }
        }
        else if ($userLicenceType == "Resubscribe") {
            try {
                $user = $this->user;
                \DB::beginTransaction();

                $user->resubscription = 'No';
                $user->save();

                // region Get user license details and payment info
                $license = License::where('owner', $user->id)->first();

                $licenseType = $license->licenseType;
                $licenseTypeDetail = LicenseType::find($licenseType);

                $userPaymentInfo = (array) $user->getPaymentInfo();
                // endregion

                // region Update user transaction
                $transaction = new Transaction();
                $transaction->id = $agreement->id;
                $transaction->type = "Resubscribe";
                $transaction->purchaser = $user->id;
                $transaction->amount = Session::get('licencePrice');
                $transaction->time = Carbon::now();
                $transaction->discount = Session::get('discount');
                $transaction->licenseType = $licenseType;
                $transaction->payer_id = '';
                $transaction->plan_id = Session::get('paypalPlanId');
                $transaction->state = $agreement->state;
                $transaction->currency = $userPaymentInfo['currency'];
                $transaction->nextBillingAmount = Session::get('licencePrice');
                $transaction->nextBillingDate = Session::get('expiryDate');

                $transaction->save();
                //endregion

                //region Update Licence
                License::where('owner', '=', $user->id)->update([
                    'status' => 'Valid',
                    'licenseType' => $licenseType,
                    'purchaseTime' => Carbon::now(),
                    'licenseVolume' => Session::get('volume'),
                    'licenseClass' => $licenseTypeDetail->licenseClass,
                    'expireDate' => Session::get('expiryDate')
                ]);
                //endregion

                //region forgot not used session
                Session::forget('paypalPlanId');
                Session::forget('expiryDate');
                Session::forget('volume');
                Session::forget('licencePrice');
                Session::forget('discount');
                Session::forget('action');
                //endregion

                //region Set active all team member's account
                $userType = $user->userType;
                $userID = $user->id;

                //Team Members for current User
                $teamMembersIDs = [];
                $teamMembersIDs[] = $userID;
                if ($userType === 'Multi') {
                    $teamMembers = User::select('id')->where('manager', $userID)->get();

                    foreach ($teamMembers as $teamMember) {
                        $teamMembersIDs[] = $teamMember->id;
                    }
                }
                //Setting user's accountStatus to be Active
                User::whereIn('id', $teamMembersIDs)->update(['accountStatus' => 'Active']);
                //endregion

                \DB::commit();

                //region Add transaction to queue for approved transaction
                $trans = Transaction::find($agreement->id);

                $job = (new SendApprovedTransaction($trans))->delay(300);
                $this->dispatch($job);
                //endregion
                Session::flash('successMessage', 'Payment successful and your license resubscribed successfully');
                //return Redirect::to('user/profile');
				return [
					'success' => 'success',
					'redirect_url' => \URL::route('user.profile')
				];
            }
            catch(Exception $e) {
                \Log::error($e);
                Session::flash("errorDetails", $e->getTraceAsString());
                Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

                //return $this->handleException($userLicenceType);
				return [
					'success' => 'fail',
					'message' => "An error occurred processing payment. Error: " . $e->getMessage()
				];
            }
        }
    }

    private function handleException($userLicenceType) {
        if ($userLicenceType == "New") {
            return Redirect::to("signup");
        }
        else if ($userLicenceType == "TrialRenew") {
            return Redirect::to("user/profile");
        }
        else if ($userLicenceType == "Renew") {
            return Redirect::to("user/profile");
        }
        else if ($userLicenceType == "CapacityIncrease") {
            return Redirect::to("user/profile");
        }
        else if ($userLicenceType == "CapacityDecrease") {
            return Redirect::to("user/profile");
        }
        elseif($userLicenceType == 'detailchange') {
            return Redirect::route("user.paymentdetail.update");
        }
        elseif($userLicenceType == 'Resubscribe') {
            return Redirect::to("user/profile");
        }
    }

    public function resubscribe() {
        Session::put('action', 'resubscribe');
        return Redirect::route('user.payment.showContainer');
    }

	public function leadFormsDontShowInfoDlg() {
		$this->user->show_lead_forms_info_dlg = 'No';
		$this->user->save();
	}
}
