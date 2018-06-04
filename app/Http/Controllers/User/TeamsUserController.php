<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminEmailTemplate;
use App\Models\License;
use App\Models\SalesMember;
use App\Models\Setting;
use App\Models\User;
use Auth;
use DB;
use DateTime;
use Faker\Factory;
use Hash;
use Input;
use Redirect;
use Request;
use Session;
use URL;
use View;

class TeamsUserController extends Controller {

	public function salesTeam() {

		$user = $this->user;
		$userType = $user->userType;
		$userID = $user->id;

		if($userType == 'Single' || $userType == 'Multi') {
			$salesMembers = SalesMember::where('manager', $userID)->paginate(10);;
			$successMessage = Session::get('successMessage');
			$successMessageClass = Session::get('successMessageClass');

			$campaign = $user->getActiveCampaigns();

			$data = [
                'teamsMenuActive'      => 'nav-active active',
                'teamsStyleActive'     =>  'display: block',
                'teamsSalesStyleActive'     =>  'active',
                'salesMembers'		   => $salesMembers,
                'successMessage'	   => $successMessage,
                'successMessageClass'  => $successMessageClass,
				'campaigns'             => $campaign
	        ];

            /*if($userType == "Multi") {
                $data['settingsMenuActive'] = 'nav-active active';
                $data['settingsStyleActive'] = 'display: block';
            }*/
	    	return View::make('site/team/salesteam', $data);
		}
		else{
			return Redirect::to('user/dashboard');
		}
	}

	function deleteSalesman() {
		$data = [];

    	if (Request::ajax()) {
    		$userID = Auth::user()->get()->id;
    		$salesmanID = Input::get('salesmanID');

    		SalesMember::where('id', $salesmanID)
    				   ->where('manager', $userID)
    				   ->delete();

    		Session::flash('successMessage', 'User Successfully Deleted.');
    		Session::flash('successMessageClass', 'success');

    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

	//Method to get information for user which will be edited
	function editSalesmanInfo() {
		$data = [];

    	if (Request::ajax()) {
    		$userID = Auth::user()->get()->id;
    		$salesmanID = Input::get('salesmanID');

			$salesMan = SalesMember::where('id', $salesmanID)
								->where('manager', $userID)
								->first();

			$selectedCampaigns = $salesMan->getCampaigns();
    		$salesmanDetails = $salesMan->toArray();


    		$data['success'] = 'success';
    		$data['salesmanDetails'] = $salesmanDetails;
    		$data['campaigns'] = $selectedCampaigns;
    	}

    	return json_encode($data);
	}

	//Method for updateing salesman information
	function updateSalesmanInfo() {
		$data = [];

    	if (Request::ajax()) {
    		$salesmanID = Input::get('salesmanID');

			$newSalesman = SalesMember::where('id', $salesmanID)
								->where('manager', $this->user->id)
								->first();
    		$newSalesman->firstName 	= Input::get('firstName');
    		$newSalesman->lastName 		= Input::get('lastName');
    		$newSalesman->email 		= Input::get('email');
    		$newSalesman->contactNumber = Input::get('telephone');
    		$newSalesman->skypeID 		= Input::get('skypeID');
    		$newSalesman->gender 		= Input::get('gender');

			$newSalesman->save();
			
			$campaigns = Input::get('campaign');
			$newSalesman->setCampaigns($campaigns);

			Session::flash('successMessage', 'User Successfully Edited.');
			Session::flash('successMessageClass', 'success');

    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

	function addNewSalesman() {
		$data = [];

    	if (Request::ajax()) {
    		$userID = $this->user->id;

            $salesmanEmail = Input::get('email');
            $isEmailAlreadyExists = SalesMember::where('email', '=', $salesmanEmail)->count();

            if($isEmailAlreadyExists == 0) {
                $newSalesman = new SalesMember;
                $newSalesman->firstName = Input::get('firstName');
                $newSalesman->lastName = Input::get('lastName');
                $newSalesman->email = Input::get('email');
                $newSalesman->contactNumber = Input::get('telephone');
                $newSalesman->skypeID = Input::get('skypeID');
                $newSalesman->gender = Input::get('gender');
                $newSalesman->manager = $userID;
                $newSalesman->creationDate = new DateTime;

                $newSalesman->save();

				$campaigns = Input::get('campaign');
				$newSalesman->setCampaigns($campaigns);

                Session::flash('successMessage', 'New User Successfully Added.');
                Session::flash('successMessageClass', 'success');

                $data['success'] = 'success';
            }
            else {
                $data['emailFound'] = true;
            }
    	}
    	return json_encode($data);
	}

	public function staffTeam($letter = null) {
		$user = $this->user;
		$userType = $user->userType;
		$userID = $user->id;

		$successMessage = Session::get('successMessage');
		$successMessageClass = Session::get('successMessageClass');

		if($userType == 'Multi') {
			if($letter == null) {
				$teamMembers = User::where('manager', $userID)->get();
				$faker = Factory::create();
				$fackerPassword = substr(Hash::make($faker->name . $faker->year), 10, 8);

				$data = [
	                'teamsMenuActive' => 'nav-active active',
	                'teamsStyleActive' =>  'display: block',
	                'teamsStaffStyleActive' =>  'active',
	                'teamMembers' => $teamMembers,
	                'fackerPassword' => $fackerPassword,
	                'letter' => 'all',
	                'successMessage' => $successMessage,
                	'successMessageClass' => $successMessageClass
		        ];
		    }
			else if(!empty($letter)) {
		    	$teamMembers = User::where('manager', $userID)
		    					   ->where('firstName', 'LIKE', $letter.'%')
		    					   ->get();
				$faker = Factory::create();
				$fackerPassword = substr(Hash::make($faker->name . $faker->year), 10, 8);

				$data = [
	                'teamsMenuActive' => 'nav-active active',
	                'teamsStyleActive' =>  'display: block',
	                'teamsStaffStyleActive' =>  'active',
	                'teamMembers' => $teamMembers,
	                'fackerPassword' => $fackerPassword,
	                'letter' => $letter,
	                'successMessage' => $successMessage,
                	'successMessageClass' => $successMessageClass
		        ];

		    }
			else {
		    	return Redirect::to('user/dashboard');
		    }

			$data['memberCounts'] = User::where('manager', $userID)->count();
			$data['volumeLimit'] = License::where('owner', $userID)
			                      ->where('expireDate', '>', (new DateTime)->modify('-2 days'))
			                      ->first()->licenseVolume;

			$data['remainingMembers'] = $data['volumeLimit'] - $data['memberCounts'];

	    	return View::make('site/team/staffteam', $data);
		}
		else {
			return Redirect::to('user/dashboard');
		}
	}

	function checkVolumeLimit() {
		if (Request::ajax()) {
    		$userID = $this->user->id;

    		$memberCounts = User::where('manager', $userID)->count();
    		$volumeLimit = License::where('owner', $userID)
    							   ->where('expireDate', '>', (new DateTime)->modify('-2 days'))
    							   ->first()
    							   ->licenseVolume;

    		if($memberCounts < $volumeLimit) {
    			$data['success'] = 'success';
    		}
		    else {
    			$data['success'] = 'fail';
    		}
    	}

    	$data['volumeLimit'] = $volumeLimit;
    	return json_encode($data);
	}

	function addNewStaffMember() {
		$data = [];
    	if (Request::ajax()) {
		    $user = $this->user;
    		$userID = $user->id;

    		$memberCounts = User::where('manager', $userID)->count();
    		$volumeLimit = License::where('owner', $userID)
    							   ->where('expireDate', '>', (new DateTime)->modify('-2 days'))
    							   ->first()
    							   ->licenseVolume;

    		if($memberCounts < $volumeLimit) {
                $userEmail = Input::get('email');

                $isEmailAlreadyExists = User::where('email', '=', $userEmail)->count();

                if($isEmailAlreadyExists == 0) {
                    $newuser = new User;
                    $newuser->firstName = Input::get('firstName');
                    $newuser->lastName = Input::get('lastName');
                    $newuser->email = Input::get('email');
                    $newuser->password = Hash::make(Input::get('password'));
                    $newuser->contactNumber = Input::get('telephone');
                    $newuser->skypeID = Input::get('skypeID');
                    $newuser->userType = 'Team';
                    $newuser->companyName = Auth::user()->get()->companyName;
                    $newuser->country = Auth::user()->get()->country;
                    $newuser->zipcode = Auth::user()->get()->zipcode;
                    $newuser->accountCreationDate = new DateTime;
                    $newuser->passwordChangeRequired = 'Yes';
                    $newuser->manager = $userID;
                    $newuser->save();

	                //set email template
	                CommonController::setEmailTemplate($newuser);

                    //Sending Welcome Message to created user
                    $newAccountTemplate = AdminEmailTemplate::where('id', 'STAFFWELCOME')->first();
                    $fieldValues = [
                        'FIRST_NAME' => Input::get('firstName'),
                        'WELCOME_MESSAGE' => Input::get('message'),
                        'PASSWORD' => Input::get('password'),
                        'EMAIL' => Input::get('email'),
                        'SITE_NAME' => Setting::get('siteName'),
                        'LOGIN_PAGE_LINK' => URL::route('user.login')
                    ];
                    $emailText = $newAccountTemplate->content;

	                $mailSettings = CommonController::loadSMTPSettings("inbound");

                    $emailInfo = [
	                    'from' => $mailSettings["from"]["address"],
	                    'fromName' => $mailSettings["from"]["name"],
	                    'replyTo' => $mailSettings["replyTo"],
                        'subject' => $newAccountTemplate->subject,
                        'to' => Input::get('email'),
	                    'bccEmail' => $user->bccEmail
                    ];

                    $attachments = [];
                    CommonController::prepareAndSendEmail($emailInfo, $emailText, $fieldValues, $attachments);

                    Session::flash('successMessage', 'New user successfully added');
                    Session::flash('successMessageClass', 'success');

                    $data['success'] = 'success';
                }
                else {
                    $data['emailFound'] = true;
                }

    		}
		    else {
    			$data['success'] = "fail";
    		}
    	}
    	return json_encode($data);
	}

	function editStaffMemberInfo() {
		$data = [];

    	if (Request::ajax()) {
    		$userID = $this->user->id;
    		$staffMemberID = Input::get('staffMemberID');

    		$staffMemberDetails = User::find($staffMemberID)->toArray();

    		$data['success'] = 'success';
    		$data['staffMemberDetails'] = $staffMemberDetails;
    	}

    	return json_encode($data);
	}

	private function remapDataToAnotherUser($fromUserID, $toUserID) {
		DB::table('appointments')
			->where('creator', $fromUserID)
			->update(['creator' => $toUserID]);
		
		DB::table('call_history')
			->where('agent', $fromUserID)
			->update(['agent' => $toUserID]);
		
		DB::table('call_history')
			->where('callBookedWith', $fromUserID)
			->update(['callBookedWith' => $toUserID]);
		
		DB::table('callbacks')
			->where('actioner', $fromUserID)
			->update(['actioner' => $toUserID]);

		DB::table('callbacks')
			->where('creator', $fromUserID)
			->update(['creator' => $toUserID]);
		
		DB::table('campaigns')
			->where('creator', $fromUserID)
			->update(['creator' => $toUserID]);
		
		DB::table('custom_unsubscribes')
			->where('user_id', $fromUserID)
			->update(['user_id' => $toUserID]);

		DB::table('emailtemplates')
			->where('creator', $fromUserID)
			->whereNotIn('name', ['Follow Up Call', 'Appointment booked'])
			->update(['creator' => $toUserID]);
		
		DB::table('emailtemplates')
			->where('owner', $fromUserID)
			->whereNotIn('name', ['Follow Up Call', 'Appointment booked'])
			->update(['owner' => $toUserID]);

		DB::table('forms')
			->where('creator', $fromUserID)
			->update(['creator' => $toUserID]);

		DB::table('landingform')
			->where('creator', $fromUserID)
			->update(['creator' => $toUserID]);

		DB::table('lead_attachment')
			->where('userID', $fromUserID)
			->update(['userID' => $toUserID]);

		DB::table('leads')
			->where('firstActioner', $fromUserID)
			->update(['firstActioner' => $toUserID]);

		DB::table('leads')
			->where('lastActioner', $fromUserID)
			->update(['lastActioner' => $toUserID]);

		DB::table('mass_email_templates')
			->where('user_id', $fromUserID)
			->update(['user_id' => $toUserID]);

		DB::table('mass_emails')
			->where('user_id', $fromUserID)
			->update(['user_id' => $toUserID]);
		
		DB::table('lead_info_emails')
			->where('user_id', $fromUserID)
			->update(['user_id' => $toUserID]);

		/*user might get 2 sets
		DB::table('massmailserver_setting')
			->where('user_id', $fromUserID)
			->update(['user_id' => $toUserID]);
		*/

		DB::table('pushmessages')
			->where('receiver', $fromUserID)
			->update(['receiver' => $toUserID]);

		DB::table('pushmessages')
			->where('sender', $fromUserID)
			->update(['sender' => $toUserID]);

		DB::table('smtpsettings')
			->where('userID', $fromUserID)
			->update(['userID' => $toUserID]);

		DB::table('statistics_share_info')
			->where('userID', $fromUserID)
			->update(['userID' => $toUserID]);

		DB::table('todolists')
			->where('userID', $fromUserID)
			->update(['userID' => $toUserID]);

		DB::table('transactions')
			->where('purchaser', $fromUserID)
			->update(['purchaser' => $toUserID]);

	}

	function deleteStaffMember() {
		$data = [];

    	if (Request::ajax()) {
    		$userID = Auth::user()->get()->id;
    		$staffMemberID = Input::get('staffMemberID');

			$userToDelete = User::FindOrFail($staffMemberID);
			if($userToDelete->manager != $userID) {
				return json_encode(["success"  => "fail", "message" => "Only team admin can delete team members"]);
			}

			$this->remapDataToAnotherUser($staffMemberID, $userID);

    		User::where('id', $staffMemberID)
    				   ->where('manager', $userID)
    				   ->delete();

    		Session::flash('successMessage', 'User Successfully Deleted.');
    		Session::flash('successMessageClass', 'success');

    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

	function updateStaffMemberInfo() {
		$data = [];

    	if (Request::ajax()) {
    		$staffMemberID = Input::get('staffMemberID');
            $password = Input::get('password');

		    $existingUser = User::where("email", Input::get("email"))
			    ->where("id", "!=", $staffMemberID)->first();

		    if ($existingUser) {
			    return json_encode([
				    "success" => "fail",
			        "message" => "A user with this email already exists"
			    ]);
		    }

    		$staffMember = User::find($staffMemberID);
    		$staffMember->firstName 	= Input::get('firstName');
    		$staffMember->lastName 		= Input::get('lastName');
    		$staffMember->email 		= Input::get('email');
    		$staffMember->contactNumber = Input::get('telephone');
    		$staffMember->skypeID 		= Input::get('skypeID');

            if($password != "") {
                $staffMember->password = Hash::make($password);
            }
			$staffMember->save();

			Session::flash('successMessage', 'User Successfully Edited.');
			Session::flash('successMessageClass', 'success');

    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

}
