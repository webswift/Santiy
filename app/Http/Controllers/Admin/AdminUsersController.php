<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Http\Requests\SimpleRequest;
use App\Models\AdminEmailTemplate;
use App\Models\Campaign;
use App\Models\LeadCustomData;
use App\Models\License;
use App\Models\LicenseType;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Datatables;
use DateInterval;
use DateTime;
use DB;
use Exception;
use Faker\Factory;
use Hash;
use Input;
use Log;
use Request;
use Session;
use Validator;
use View;
use ZipStream\ZipStream;
use Illuminate\Support\Facades\Auth;

//use CRM\Models\Setting;

class AdminUsersController extends Controller
{

	public function index() {
		$allLicenseTypes = LicenseType::type('Paid')->get();
		$faker = Factory::create();
		$fakerPassword = substr(Hash::make($faker->name . $faker->year), 10, 8);
		$successMessage = Session::get('successMessage');
		$licenseTypeDetail = LicenseType::where('licenseClass', 'Multi')->where('type', 'Paid')->first();


		//Array to send data to view
		$data = [
				'allLicenseTypes' => $allLicenseTypes,
				'fackerPassword' => $fakerPassword,
				'successMessage' => $successMessage,
				'usersMenuActive' => 'active',
				'licenseTypeDetail' => $licenseTypeDetail,
		];

		return View::make('admin/user/viewusers', $data);
	}

	public function ajaxusers($action = null) {
		$users = User::select(['users.id', 'users.firstName', 'users.lastName', 'users.companyName', 'users.email', 'users.lastLogin', 'users.loginCount', 'users.contactNumber', 'licenses.expireDate', 'users.userType']);

		if ($action == 'all') {
			$users->join('licenses', 'licenses.owner', '=', 'users.id');
		}
		else if ($action == 'active') {
			$users->join('licenses', 'licenses.owner', '=', 'users.id')
					->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
					->where('licensetypes.type', 'Paid')
				->where('users.accountStatus', '=', 'Active');

			/*$users->leftJoin('trial_user_track', 'trial_user_track.user_id', '=', 'users.id')
				->where('trial_user_track.type', 'Converted');*/
		}
		else if ($action == 'blocked') {
			$users->join('licenses', 'licenses.owner', '=', 'users.id')
					->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
					->where('licensetypes.type', 'Paid')
				->where('users.accountStatus', '=', 'Blocked');
		}
		else if ($action == 'expired') {
			$users->join('licenses', 'licenses.owner', '=', 'users.id')
					->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
					->where('licensetypes.type', 'Paid')
				->where('users.accountStatus', '=', 'LicenseExpired');
		}
		else if ($action == 'expiredsoon') {

			// All licenses with expire date between today's date and date 30 days ago
			$date = new DateTime();
			$date->sub(new DateInterval('P30D'));

			$users->join('licenses', 'licenses.owner', '=', 'users.id')
					->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
					->where('licensetypes.type', 'Paid')
				->where('licenses.expireDate', '>=', $date)
				->where('licenses.expireDate', '<=', new DateTime());
		}
		else if($action == 'trial') {
			$users->join('licenses', 'licenses.owner', '=', 'users.id')
					->join('licensetypes', 'licensetypes.id', '=', 'licenses.licenseType')
			      ->where('licensetypes.type', 'Trial');
		}
		else {
			$users->join('licenses', 'licenses.owner', '=', 'users.id');
		}


		return Datatables::of($users)
			->edit_column('firstName', function($row){
				return $row->firstName.' '.$row->lastName;
			})
				->edit_column('lastLogin', function($row){
					if($row->lastLogin == '0000-00-00 00:00:00') {
						return '-';
					}
					return Carbon::parse($row->lastLogin)->format('d M Y H:i:s');
				})
			->remove_column('lastName')
			->add_column('edit', '<a type="button" href="users/edituser/{{ $id }}" class="btn btn-info btn-xs">	<i class="fa fa-edit"></i> Edit</a>')
			->add_column('delete', '<a type="button" href="javascript:void(0)" onclick="adminDeleteUser({{ $id }});return false;" class="btn btn-danger btn-xs">	<i class="fa fa-trash-o"></i> Delete</a>')
			->make();
	}

	public function createuser(\Illuminate\Http\Request $request) {
		$output = [];
		ini_set('max_execution_time', 0);
		// Function must be called only via Ajax request
		if ($request->ajax()) {
			$error = '';

			//region create validation rules
			//validation Array
			$validatorArray = [
				'firstName' => 'required',
				'lastName' => 'required',
				'email' => 'required|email|unique:users',
				'contactNumber' => 'required|numeric',
				'country' => 'required',
				//'zipCode' => 'required|exists:postalcodes,postalCode',
				'companyName' => 'required',
				'licenseType' => 'required',
				'expireOn' => 'required|date',
				'transactionNumber' => 'required',
				'usdPrice'		=> 'required|numeric',
				'euroPrice'		=> 'required|numeric',
				'gbpPrice'		=> 'required|numeric',
				'priceUSD_year'	=> 'required|numeric',
				'priceEuro_year'=> 'required|numeric',
				'priceGBP_year'	=> 'required|numeric',
				'discount'  => 'required|numeric',
				'multiUsers' => 'required|numeric|min:1',
				'free_users' => 'required|numeric|min:0',
			];

			$multiUsers = Input::get('multiUsers');
			//$licenseType = Input::get('licenseClass');
			$licenseType = 'Multi';

			if (empty($multiUsers)) {
				$multiUsers = 1;
			}

			//endregion

			//Validation Rules for user Info
			$validator = Validator::make(Input::all(), $validatorArray);

			//region check if validation fails
			if ($validator->fails()) {
				//If Validation rule fails
				$messages = $validator->messages();
				foreach ($messages->all() as $message) {
					$error .= $message . '<br>';
				}

				$output['success'] = false;
				$output['error'] = $error;
			}
			//endregion
			//region validation successful
			else {

				/**
				 *  goes from AdminLicensesController - same as edit license
				 */

				//Today Date
				$expiryDate = Carbon::parse($request->input('expireOn'));
				DB::beginTransaction();

				try {
					// region create new user
					$user = new User();
					$user->firstName = $request->input('firstName');
					$user->lastName = $request->input('lastName');
					$user->email = $request->input('email');
					$user->password = bcrypt($request->input('password'));
					$user->contactNumber = $request->input('contactNumber');
					$user->country = $request->input('country');
					$user->companyName = $request->input('companyName');
					$user->latitude = "0.0";
					$user->longitude = "0.0";
					$user->passwordChangeRequired = "Yes";
					$user->userType = $licenseType;
					$user->accountCreationDate = Carbon::now();
					$user->lastLogin = "0000-00-00 00:00:00";
					$user->existing = "Yes";
					$user->save();
					//endregion

					// region add default email template
					if($user->userType == 'Single' || $user->userType == 'Multi') {
						CommonController::setEmailTemplate($user);
					}
					// endregion

					// region send welcome email to user
					$newAccountTemplate = AdminEmailTemplate::where('id', 'NEWACCOUNTADMIN')->first();
					$message = nl2br(Input::get("message"));
					$message .= "<br/>Your new account's password is: " . (Input::get("password"));

					$fieldValues = [
						'FIRST_NAME' => $user->firstName,
						'LAST_NAME' => $user->lastName,
						'SITE_NAME'  => Setting::get('siteName'),
						'SUPPORT_EMAIL'   => Setting::get('supportEmail'),
						'WELCOME_MESSAGE' => $message
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

					$license = new License();
					$license->owner = $user->id;
					$license->purchaseTime = Carbon::now();
					$license->expireDate = $expiryDate;
					$license->licenseType = $request->input('licenseType');
					$license->licenseClass = $licenseType;
					$license->licenseVolume = $multiUsers - 1;
					$license->free_users = $request->free_users;

					/**
					 *  Store individual price
					 */

					$license->priceUSD = $request->usdPrice;
					$license->priceGBP = $request->gbpPrice;
					$license->priceEuro = $request->euroPrice;

					$license->priceUSD_year = $request->priceUSD_year;
					$license->priceGBP_year = $request->priceGBP_year;
					$license->priceEuro_year = $request->priceEuro_year;

					$license->discount = $request->discount;

					$license->save();

					//endregion

					//amount (admin + team members above free level)
					$paidUsers = ($multiUsers - 1) - $license->free_users;
					if($paidUsers < 0) {
						$paidUsers = 0;
					}
					//add team admin
					$paidUsers++;

					//region create transaction
					$transaction = new Transaction();
					$transaction->id = $request->input('transactionNumber');
					$transaction->type = 'New';
					$transaction->time = Carbon::now();
					$transaction->purchaser = $user->id;
					$transaction->amount = $paidUsers * $request->usdPrice;
					$transaction->discount = 0; 
					$transaction->licenseType = $request->input('licenseType');
					$transaction->currency = "USD";
					$transaction->nextBillingAmount = $transaction->amount;
					$transaction->nextBillingDate = $expiryDate;
					$transaction->save();
					//endregion

					DB::commit();

					Session::flash('successMessage', 'User Successfully Created');
					$output['success'] = true;
					$output['error'] = "User Successfully Created.";
				}
				catch (Exception $e) {
					Log::error($e);
					DB::rollBack();

					Session::flash('errorMessage', 'There is some error. Try again!!');
					$output['success'] = false;
					$output['error'] = $e->getMessage();
				}
			}
			//endregion
		}

		return $output;
	}
	

	/*mysql can't handle circular refererences by self
	 */
	private function deleteUserData($fromUserID) {
		DB::table('mass_emails')
			->where('user_id', $fromUserID)
			->delete();

		DB::table('leads')
			->where('firstActioner', $fromUserID)
			->delete();

		DB::table('leads')
			->where('lastActioner', $fromUserID)
			->delete();

		DB::table('appointments')
			->where('creator', $fromUserID)
			->delete();
		
		DB::table('call_history')
			->where('agent', $fromUserID)
			->delete();
		
		DB::table('call_history')
			->where('callBookedWith', $fromUserID)
			->delete();
		
		DB::table('callbacks')
			->where('actioner', $fromUserID)
			->delete();

		DB::table('callbacks')
			->where('creator', $fromUserID)
			->delete();
		
		DB::table('campaigns')
			->where('creator', $fromUserID)
			->delete();
		
		DB::table('custom_unsubscribes')
			->where('user_id', $fromUserID)
			->delete();

		DB::table('emailtemplates')
			->where('creator', $fromUserID)
			->whereNotIn('name', ['Follow Up Call', 'Appointment booked'])
			->delete();
		
		DB::table('emailtemplates')
			->where('owner', $fromUserID)
			->whereNotIn('name', ['Follow Up Call', 'Appointment booked'])
			->delete();

		DB::table('forms')
			->where('creator', $fromUserID)
			->delete();

		DB::table('landingform')
			->where('creator', $fromUserID)
			->delete();

		DB::table('lead_attachment')
			->where('userID', $fromUserID)
			->delete();

		DB::table('mass_email_templates')
			->where('user_id', $fromUserID)
			->delete();
		
		DB::table('lead_info_emails')
			->where('user_id', $fromUserID)
			->delete();

		DB::table('massmailserver_setting')
			->where('user_id', $fromUserID)
			->delete();

		DB::table('pushmessages')
			->where('receiver', $fromUserID)
			->delete();

		DB::table('pushmessages')
			->where('sender', $fromUserID)
			->delete();

		DB::table('smtpsettings')
			->where('userID', $fromUserID)
			->delete();

		DB::table('statistics_share_info')
			->where('userID', $fromUserID)
			->delete();

		DB::table('todolists')
			->where('userID', $fromUserID)
			->delete();

		DB::table('transactions')
			->where('purchaser', $fromUserID)
			->delete();

	}
	public function deleteUser(\Illuminate\Http\Request $request) 
	{
		$data = [];

    	if ($request->ajax()) {
    		$currentUserID = Auth::admin()->get()->id;
    		$userID = $request->input('userID');
			if($currentUserID == $userID) {
				return ["success"  => "fail", "message" => "You can't delete yourself"];
			}

			$userToDelete = User::FindOrFail($userID);

			if($userToDelete->userType == 'Multi') {
				//delete team members first
				$teamMembers = User::where("manager", $userToDelete->id)->get();
				foreach($teamMembers as $teamMember) {
					$this->deleteUserData($teamMember->id);

					User::where('id', $teamMember->id)
							   ->delete();
				}
			}

			$this->deleteUserData($userToDelete->id);

    		User::where('id', $userToDelete->id)
    				   ->delete();
			
			License::where('owner', '=', $userToDelete->id)
    				   ->delete();

    		Session::flash('successMessage', 'User Successfully Deleted.');
    		Session::flash('successMessageClass', 'success');

    		$data['success'] = 'success';
    	}

    	return $data;
	}

	public function editUser($userID) {

		$user = User::find($userID);
		if(!$user) {
			return "Unknown user";
		}
		if($user->userType == 'Team') {
			return "Editing of team members is not supported";
		}

		$user = User::select([
				'users.*', 
				'licenses.*', 
			])
			->join('licenses', 'licenses.owner', '=', 'users.id')
			->where('users.id', '=', $userID)
			->first();

		$licenceTypeID = $user->licenseType;
		$licenceType = LicenseType::find($licenceTypeID);
		$userType = $licenceType->type;

		//Array to send data to view
		$data = [
				'userDetail' => $user,
				'successMessage' => Session::get('successMessage'),
				'usersMenuActive'  => 'active',
				'userType' => $userType,
				'licenses' => LicenseType::get(),
				'selectedLicenseID' => $licenceTypeID,
				'accountStatuses' => ['Active','Blocked','LicenseExpired'],
				'licenseTypeDetail' => $user,
		];

		$allTransactionDetails = Transaction::where('purchaser', $userID)->orderBy('time', 'DESC')->get();

		if(sizeof($allTransactionDetails) > 0) {
			$latestTransactionID = Transaction::where('purchaser', $userID)->orderBy('time', 'DESC')->first()->id;

			$data['latestTransactionID'] = $latestTransactionID;
			$data['allTransactionDetails'] = $allTransactionDetails;
		}

		return View::make('admin/user/edituser', $data);
	}

	public function updateUser(SimpleRequest $request, $userID) {
		// Function must be called only via Ajax request
		if (Request::ajax()) {
			$output = [];
			$error = '';
			
			$user = User::find($userID);

			//check total number of email in users table
			$checkEmailAlreadyExists = User::where('email', '=', Input::get('email'))
				->where('id', '!=', $userID)
				->count();

			//validation Array
			$validatorArray = [
				'firstName' => 'required',
				'lastName' => 'required',
				'email' => 'required|email',
				'contactNumber' => 'required',
				'country' => 'required',
				'expireDate' => 'required|date_format:*d/m/Y',
				'userAccountStatus' => 'required|in:Active,Blocked,LicenseExpired',
				'userLicenseId' => 'required|exists:licensetypes,id',
				'usdPrice'		=> 'required|numeric',
				'euroPrice'		=> 'required|numeric',
				'gbpPrice'		=> 'required|numeric',
				'priceUSD_year'	=> 'required|numeric',
				'priceEuro_year'=> 'required|numeric',
				'priceGBP_year'	=> 'required|numeric',
				'discount'  => 'required|numeric',
				'volumeOfUsers' => 'required|numeric|min:1',
				'free_users' => 'required|numeric|min:0',
				'max_users' => 'required|numeric|min:0',
			];

			//Validation Rules for user Info
			$validator = Validator::make(Input::all(), $validatorArray);

			if ($validator->fails()) {
				//If Validation rule fails
				$messages = $validator->messages();
				foreach ($messages->all() as $message) {
					$error .= $message . '<br>';
				}

				$output['success'] = false;
				$output['error'] = $error;
			}
			else if ($checkEmailAlreadyExists >= 1) {
				$output['success'] = false;
				$output['error'] = "This Email Already Activated.";
			}
			else if(!$user) {
				$output['success'] = false;
				$output['error'] = "User doesn't exist.";
			}
			else {


				$user->firstName = Input::get('firstName');
				$user->lastName = Input::get('lastName');
				$user->email = Input::get('email');
				$user->contactNumber = Input::get('contactNumber');
				$user->country = Input::get('country');
				//$user->zipcode = Input::get('zipCode');

				if (strlen(Input::get('password')) > 0) {
					$user->password = Hash::make(Input::get('password'));
				}

				if (strlen(Input::get('address')) > 0) {
					$user->address = Input::get('address');
				}
				
				//expire date
				$expireDate = trim(Input::get('expireDate'));
				$expireDate = Carbon::createFromFormat('!d/m/Y', $expireDate);
				
				$latestTransaction = $user->getLatestTransaction();
				if($latestTransaction) {
					Transaction::where('id', $latestTransaction->id)
						->update(['nextBillingDate' => $expireDate]);
				}
				
				//account status
				$user->accountStatus = trim(Input::get('userAccountStatus'));

				//do not modify license status if user blocked
				if($user->accountStatus != 'Blocked') {
					$transactionStatus = $user->accountStatus == 'LicenseExpired' ? 'Expired' : 'Valid';
					License::where('owner', $userID)
						->update(['status' => $transactionStatus]);
				}

				//licenseType
				$licenseTypeID = trim(Input::get('userLicenseId'));
				$licenseType = LicenseType::find($licenseTypeID);
				$user->userType = $licenseType->licenseClass;
				License::where('owner', $userID)
					->update([
						'licenseType' => $licenseTypeID,
						'licenseClass' => $licenseType->licenseClass,
					]);

				$mass_email_limit = trim(Input::get('mass_email_limit'));
				//\Log::error('param:' . $mass_email_limit);
				//\Log::error('user:' . $user->mass_email_limit);
				if($mass_email_limit !== $user->mass_email_limit) {
					$user->mass_email_limit = $mass_email_limit != '' ? $mass_email_limit : null; 
					if($mass_email_limit != '') {
						$emails = intval($mass_email_limit);
					} else {
						//use default value
						if($licenseType->type == 'Trial') {
							$trialUserLimit = Setting::get('trialUserLimit');
							$emails = $trialUserLimit['emails'];
						}
						elseif($licenseType->type == 'Paid') {
							if($user->userType == 'Multi') {
								$multiUserLimit = Setting::get('multiUserLimit');
								$emails = $multiUserLimit['emails'];
							}
							elseif($user->userType == 'Single') {
								$singleUserLimit = Setting::get('singleUserLimit');
								$emails = $singleUserLimit['emails'];
							}
						}
					}
					$userEmailCredit = $user->getUserEmailCredit();
					if(!$userEmailCredit) {
						// create email credit
						$user->createEmailCredit($emails);
					}
					else {
						$user->updateEmailCredit($emails);
						$user->setPendingFromPostponeMassEmail();
					}
				}


				$user->save();

				//update license
				$license = License::where('owner', $userID)->first();
				$license->expireDate = $expireDate;
				$license->licenseVolume = $request->volumeOfUsers - 1;
				$license->free_users = $request->free_users;
				$license->max_users = $request->max_users;

				/**
				 *  Store individual price
				 */

				$license->priceUSD = $request->usdPrice;
				$license->priceGBP = $request->gbpPrice;
				$license->priceEuro = $request->euroPrice;

				$license->priceUSD_year = $request->priceUSD_year;
				$license->priceGBP_year = $request->priceGBP_year;
				$license->priceEuro_year = $request->priceEuro_year;

				$license->discount = $request->discount;

				$license->save();
				
				User::where("manager", $userID)
						->update(['accountStatus' => $user->accountStatus]);

				Session::flash('successMessage', 'User Successfully Updated');
				$output['success'] = true;
				$output['error'] = "User Successfully Updated.";
			}
		}

		return json_encode($output);
	}

	public function exportUserData() {
		$industries = Setting::get('industry');

		$data['industries'] = explode(',', $industries);

		$data['exportUserDataActive'] = 'active';

		return View::make('admin/user/exportuserdata', $data);
	}

	public function ajaxExportUserDataTable() {
		$industry = Input::get('industry');
		$startDate = Input::get('startDate');
		$endDate = Input::get('endDate');

		$dataResults = Campaign::select(DB::raw('users.email, SUM(campaigns.totalLeads) as totalLeads, COUNT(campaigns.id) as totalDatabase, campaigns.industry, campaigns.creator'))
			->join('users', 'users.id', '=', 'campaigns.creator');


		if ($startDate != '' && $endDate != '') {
			$dataResults->whereBetween('timeCreated', [$startDate, $endDate]);
		}
		else if ($startDate != '') {
			$dataResults->where('timeCreated', '>=', $startDate);
		}
		else if ($endDate != '') {
			$dataResults->where('timeCreated', '<=', $endDate);
		}

		$dataResults = $dataResults->where('campaigns.industry', '=', $industry)
			->groupBy('campaigns.creator')
			->get();

		$data['startDate'] = $startDate;
		$data['endDate'] = $endDate;
		$data['dataResults'] = $dataResults;

		return View::make('admin/user/userExportDataTable', $data);

	}

	public function exportUserDataToZip() {
		set_time_limit(0);

		$industry = Input::get('industry');
		$startDate = Input::get('startDate');
		$endDate = Input::get('endDate');
		$creator = Input::get('creator');

		$campaignsLists = Campaign::select(['campaigns.id', 'campaigns.name']);

		if ($startDate != '' && $endDate != '') {
			$campaignsLists->whereBetween('timeCreated', [$startDate, $endDate]);
		}
		else if ($startDate != '') {
			$campaignsLists->where('timeCreated', '>=', $startDate);
		}
		else if ($endDate != '') {
			$campaignsLists->where('timeCreated', '<=', $endDate);
		}

		$campaignsLists = $campaignsLists->where('campaigns.industry', '=', $industry)
			->where('campaigns.creator', '=', $creator)
			->get();

		//$email = User::where('id', $creator)->first()->email;

		# create a new zipstream object
		$zip = new ZipStream('UserData_' . (new DateTime())->format("Y_m_d_H_i_s") . ".zip");

		foreach ($campaignsLists as $campaignsList) {
			$campaignName = $campaignsList->name;

			// Set File attachment headers to force download
			$fileName = $campaignName . '_' . (new DateTime())->format("Y_m_d_H_i_s") . ".csv";

			$campaigns = $campaignsList->id;
			$userID = $creator;

			$selectedArray = ['campaigns.name', 'leads.id as leadID', 'leads.referenceNumber', 'leads.timeCreated',
			                  'leads.timeEdited', 'leads.status', 'leads.interested', 'leads.bookAppointment'];

			$results = Campaign::join('leads', 'leads.campaignID', '=', 'campaigns.id');

			$results = $results->where('campaigns.creator', '=', $userID)
				->where('campaigns.id', $campaigns)
				->get($selectedArray);

			$newInsertedCsvArray = ['Campaign Name', 'Reference Number', 'Time Created', 'Time Edited', 'Status',
			                        'Interested', 'Book Appointment'];


			// Query for extracting unique field name from given campaigns
			$leadDataHeadings = LeadCustomData::select('fieldID', 'fieldName', 'value', 'leadID')
				->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
				->where('leads.campaignID', $campaigns)
				->groupBy('fieldID')
				->orderBy("fieldID")
				->get();

			// Putting these Unique Fields to header
			foreach ($leadDataHeadings as $leadDataHeading) {
				$newInsertedCsvArray[] = $leadDataHeading->fieldName;
			}

			$file = tmpfile();

			fputcsv($file, $newInsertedCsvArray, ",", "\"");

			// TODO: In case of campaigns with multiple forms, the results may not show under their respective columns
			$count = 0;

			// Adding other elements other than heading
			foreach ($results as $result) {
				$newArray = [];
				$newArray[] = $result->name;
				$newArray[] = $result->referenceNumber;
				$newArray[] = $result->timeCreated;
				$newArray[] = $result->timeEdited;
				$newArray[] = $result->status;
				$newArray[] = CommonController::getInterestedType($result->interested);
				$newArray[] = $result->bookAppointment;


				$leadData = LeadCustomData::select(['value'])
					->where('leadID', $result->leadID)
					->orderBy("fieldID")
					->get();

				// Extracting values of CustomFieldData columns
				foreach ($leadData as $leadField) {
					//                $fieldID = $leadField->fieldID;
					$newArray[] = $leadField->value;
				}

				fputcsv($file, $newArray, ",", "\"");

				$count++;

				//                if($count % 50 == 0) {
				//                    flush();
				//                }
			}

			$zip->addFileFromStream($fileName, $file);
			fclose($file);
		}

		//        # finish the zip stream
		$zip->finish();

	}

}
