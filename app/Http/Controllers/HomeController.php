<?php

namespace App\Http\Controllers;

use App;
use App\Http\Controllers\Controller;
use App\Models\AdminEmailTemplate;
use App\Models\DiscountCode;
use App\Models\License;
use App\Models\LicenseType;
use App\Models\PostalCode;
use App\Models\Setting;
use App\Models\User;
use App\Models\PendingTransaction;
use Auth;
use Carbon\Carbon;
use DateTime;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Input;
use Log;
use Mail;
use PayPal\Api\Agreement;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\Webhook;
use PayPal\Api\WebhookEvent;
use PayPal\Api\WebhookEventType;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Redirect;
use Session;
use URL;
use Validator;
use View;

class HomeController extends Controller
{

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function index() {
		return view('homepage');
	}

	public function page() {
		return view(\Route::currentRouteName());
	}

	public function contactForm() {

		$validator = Validator::make(
				Input::all(),
				["name" => "required|min:5",
				 "email" => "required|email",
				 "request_message" => "required|min:15",
				 'password' => 'honeypot',
				 'confirmPassword' => 'required|honeytime:5']);

		if ($validator->fails()) {
			return Redirect::route('contact')
				->withErrors($validator->errors())
				->with('formName', 'contact');
		}
		else {

			CommonController::loadSMTPSettings("inbound");

			$data["email"] = Input::get("email");
			$data["name"] = Input::get("name");
			$data["message1"] = Input::get("request_message");

			Mail::send('emails.contact', $data, function ($message) {
				$message->subject("New contact request");
				$message->from('support@sanityos.com', 'Sanity OS');
				$message->to('support@sanityos.com');
			});

			return Redirect::route('contact')->with("contactSuccess", "Contact request received successfully")->with('formName', 'contact');
		}

	}

	public function newsletter() 
	{
		$validator = Validator::make(Input::all(),
				["email" => "required|email",
				]);

		if ($validator->fails()) {
			return Redirect::route('newsletter_error')->withErrors($validator->errors())->with('formName', 'newsletter');
		}
		else {
			CommonController::loadSMTPSettings("inbound");

			$data = Input::all();

			Mail::send('emails.newsletter', $data, function ($message) {
				$message->subject("New Newsletter Signup");
				$message->from('support@sanityos.com', 'Sanity OS');
				$message->to('contact@sanityos.com');
			});

			return Redirect::route('newsletter.success')->with("success", "Congratulations! You are now signed up for our newsletter!")->with('formName', 'newsletter');
		}
	}


	public function demo() {
		$validator = Validator::make(Input::all(), [
			"name" => "required",
			"company" => "required",
			"telephone" => "required",
			"country" => "required",
			"email" => "required|email",
			"interestedin" => "required",
		    'password' => 'honeypot',
			'confirmPassword' => 'required|honeytime:5']
		);

		if ($validator->fails()) {
			return Redirect::route('pricing')
				->withErrors($validator->errors());
		}
		else {
			CommonController::loadSMTPSettings("inbound");

			Mail::send('emails.demo', Input::all(), function ($message) {
				$message->subject("New Demo Request");
				$message->from('support@sanityos.com', 'Sanity OS');
				$message->to('support@sanityos.com');
			});

			return Redirect::route('pricing')->with("success", "We have received your request. Will contact you shortly.");
		}

	}

	public function signup() {
		$data['trial_cart'] = Setting::get('trial_cart');

		if($data['trial_cart'] == 'No') {
			$data['licenses'] = LicenseType::select('id', 'licenseClass', 'name', 'free_users')->type('Paid')->where('licenseClass', 'Multi')->get();
		}
		else {
			$data['trialLicences'] = LicenseType::select('id', 'licenseClass', 'name', 'free_users')->type('Trial')->where('licenseClass', 'Multi')->get();
			$data['paidLicences'] = LicenseType::select('id', 'licenseClass', 'name', 'free_users')->type('Paid')->where('licenseClass', 'Multi')->first();

		}

		$data['successMessage'] = Session::get('successMessage');
		$data['errorMessage'] = Session::get('errorMessage');
		$data['errorDetails'] = Session::get('errorDetails');

		return View::make('signup', $data);
	}

	public function showPreferredCurrency(Request $request) {
		if ($request->ajax()) {
			if ($request->has('licenseType')) {
				$license = $request->input('licenseType');
			}
			else {
				$user = $this->user;

				if ($user->userType == "Team") {
					$licenseObj = License::where('owner', $user->manager)->first();
				}
				else {
					$licenseObj = License::where('owner', $user->id)->first();
				}

				if ($licenseObj) {
					$license = $licenseObj->licenseType;
				}
				else {
					$license = "";
				}

				if($user->isTrial) {
					$userType = $licenseObj->licenseClass;

					$license = LicenseType::where('licenseClass', $userType)->where('type', 'Paid')->first()->id;
				}
			}

			$preferredCurrency = Input::get('preferredCurrency');

			$price = LicenseType::find($license)->$preferredCurrency;

			if(Input::has('couponCode')) {
				$couponCode = Input::get('couponCode');

				$couponCodeDetail = DiscountCode::select('discountPercent')
						->where('code', '=', $couponCode)
						->where('licenseType', '=', $license)
						->first();

				if ($couponCodeDetail) {
					$discountPercent = $couponCodeDetail->discountPercent;

					$discount = round(($discountPercent / 100) * $price, 2);

					$data['price'] = sprintf("%0.2f", round($price - $discount, 2));
					$data['couponSuccess'] = true;
				}
				else {
					$data['price'] = $price;
					$data['couponSuccess'] = false;
				}
			}
			else {
				$data['price'] = $price;
				$data['couponSuccess'] = false;
			}
			$data['success'] = 'success';

		}

		return $data;
	}

	public function paymentReturnUrl() {
		$action = Input::get('action');
		$success = Input::get('success');

		if(Input::has('pendingtr')) {
			$pendingTransactionId = Input::get('pendingtr');
			if($pendingTransactionId) {
				Log::error("pp return, delete pending transaction id : " . $pendingTransactionId);
				PendingTransaction::where('id', '=', $pendingTransactionId)->delete();		
			}
		}
		
		
		if ($action == "new" && $success == "true") {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

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
				$user->address = $agreementInfo['billingAddress'];
				$user->country = $userInfo['country'];
				$user->zipCode = $agreementInfo['postalCode'];
				$user->existing = 'No';

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}

				$user->accountCreationDate = new DateTime;
				$user->timeZoneName = $userInfo['timeZoneName'];
				$user->save();

				//endregion

				// region Set User Payment Info
				$userPaymentInfo = Session::get('userPaymentInfo');

				$address = json_encode([
					'country' => $user->country,
					'address1' => $agreementInfo['addressLine1'],
					'city' => $agreementInfo['city'],
					'state' => $agreementInfo['state'],
					'pincode' => $agreementInfo['postalCode']
				]);
				$userPaymentInfo['name'] = $agreementInfo['payer_fname'] . ' ' . $agreementInfo['payer_lname'];
				$userPaymentInfo['billing_details'] = $address;
				$userPaymentInfo['address_details'] = $address;
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];

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
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "New";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');
				$transaction->time = Carbon::now();
				$transaction->discount = Session::get('discount');
				$transaction->licenseType = $userInfo['licenseType'];
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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
				Session::forget('paypalPlanId');
				Session::forget('expiryDate');
				Session::forget('volume');
				//endregion

				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion

				return Redirect::route('user.login', ["payment" => "true"])->with('message', 'You are successfully '.strtolower($userPaymentInfo['recurring_type']).' subscribed for sanityos license. Please login with your new account');
			}
			catch (Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::to("/");
			}
		}
		else if ($action == "trialrenew" && $success == "true") {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

				\DB::beginTransaction();

				$user = $this->user;

				//region Update user information
				$user->address = $agreementInfo['billingAddress'];
				$user->zipCode = $agreementInfo['postalCode'];

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}

				$user->existing = 'No';
				$user->save();
				//endregion

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
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];
				$user->setPaymentInfo($userPaymentInfo);
				$userPaymentInfo = $userPaymentInfo;
				// endregion

				// region Update user transaction
				$transaction = new \App\Models\Transaction();
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "TrialRenew";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');
				$transaction->time = Carbon::now();
				$transaction->discount = Session::get('discount');
				$transaction->licenseType = $licenseType;
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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

				//$this->sendInvoiceEmailToUser($user, $userPaymentInfo, $licenseTypeDetail);
				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion
				Session::flash('successMessage', 'Payment successful and your license renewed successfully');
				return Redirect::to('user/profile');
			}
			catch(Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::to('user/profile');
			}
		}
		else if ($action == "renew" && $success == "true") {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

				\DB::beginTransaction();

				$user = $this->user;

				//region Update user information
				$user->address = $agreementInfo['billingAddress'];
				$user->zipCode = $agreementInfo['postalCode'];

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}

				$user->resubscription = 'No';
				$user->existing = 'No';
				$user->save();
				//endregion

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
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];
				$user->setPaymentInfo($userPaymentInfo);
				$userPaymentInfo = $userPaymentInfo;
				// endregion

				// region Update user transaction
				$transaction = new \App\Models\Transaction();
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "Renew";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');
				$transaction->time = Carbon::now();
				$transaction->discount = Session::get('discount');
				$transaction->licenseType = $licenseType;
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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

				//$this->sendInvoiceEmailToUser($user, $userPaymentInfo, $licenseTypeDetail);
				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion
				Session::flash('successMessage', 'Payment successful and your license renewed successfully');
				return Redirect::to('user/profile');
			}
			catch(Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::to('user/profile');
			}
		}
		else if ($action == "capacityincrease" && $success == "true") {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

				\DB::beginTransaction();

				$user = $this->user;

				//region Update user information
				$user->address = $agreementInfo['billingAddress'];
				$user->zipCode = $agreementInfo['postalCode'];

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}
				$user->save();
				//endregion

				//region get user licence and payment info
				$license = License::where('owner', $user->id)->first();
				$licenseType = $license->licenseType;

				$userPaymentInfo = (array) $user->getPaymentInfo();
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];
				$user->setPaymentInfo($userPaymentInfo);
				$userPaymentInfo = $userPaymentInfo;
				//endregion

				//region Update transaction
				$transaction = new App\Models\Transaction();
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "CapacityIncrease";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');;
				$transaction->time = Carbon::now();
				$transaction->discount = 0;
				$transaction->licenseType = $licenseType;
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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
				//endregion

				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion

				/*if($userPaymentInfo['recurring_type'] == 'Annually') {
					$this->sendInvoiceEmailToUser($user, $userPaymentInfo, $licenceTypeDetail);
				}*/

				Session::flash('successMessage', 'Payment plan is updated and team members successfully added in your team');

				return Redirect::to('user/profile');
			}
			catch (Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::to("user/profile");
			}
		}
		else if ($action == "capacitydecrease" && $success == "true") {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

				\DB::beginTransaction();

				$user = $this->user;

				// region Update user information
				$user->address = $agreementInfo['billingAddress'];
				$user->zipCode = $agreementInfo['postalCode'];

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}
				$user->save();
				//endregion

				//region Get Licence details and payment info
				$license = License::where('owner', $user->id)->first();
				$licenseType = $license->licenseType;

				$userPaymentInfo = (array) $user->getPaymentInfo();
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];
				$user->setPaymentInfo($userPaymentInfo);
				$userPaymentInfo = $userPaymentInfo;
				//endregion

				//region update transaction
				$transaction = new App\Models\Transaction();
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "CapacityDecrease";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');
				$transaction->time = Carbon::now();
				$transaction->discount = 0;
				$transaction->licenseType = $licenseType;
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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
				//endregion

				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion

				Session::flash('successMessage', 'Payment plan is updated and team members successfully removed from your team');

				return Redirect::to('user/profile');
			}
			catch (Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::to("user/profile");
			}
		}
		else if ($action == "detailchange" && $success == "true") {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

				\DB::beginTransaction();

				$user = $this->user;

				//region Update user information
				$user->address = $agreementInfo['billingAddress'];
				$user->zipCode = $agreementInfo['postalCode'];

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}
				$user->save();
				//endregion

				//region get user licence and payment info
				$license = License::where('owner', $user->id)->first();
				$licenseType = $license->licenseType;

				$userPaymentInfo = Session::get('userPaymentInfo');
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];
				$user->setPaymentInfo($userPaymentInfo);

				$userPaymentInfo = (array) $user->getPaymentInfo();
				//endregion

				//region Update transaction
				$transaction = new App\Models\Transaction();
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "PlanDetailChange";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');;
				$transaction->time = Carbon::now();
				$transaction->discount = 0;
				$transaction->licenseType = $licenseType;
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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
				Session::forget('licencePrice');
				Session::forget('paypalPlanId');
				Session::forget('nextBillingAmount');
				Session::forget('expiryDate');
				Session::forget('userPaymentInfo');
				//endregion

				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion

				Session::flash('successMessage', 'Payment plan is updated successfully');

				return Redirect::route('user.paymentdetail.update');
			}
			catch (Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::route('user.paymentdetail.update');
			}
		}
		elseif($action == 'resubscribe' && $success == 'true') {
			try {
				$agreementInfo = $this->getAgreementInformation($_GET['token']);

				$postalCodeDetails = PostalCode::select(['latitude', 'longitude', 'countryCode'])
					->where('postalCode', $agreementInfo['postalCode'])
					->where('countryCode', $agreementInfo['countryCode'])
					->first();

				\DB::beginTransaction();

				$user = $this->user;

				//region Update user information
				$user->address = $agreementInfo['billingAddress'];
				$user->zipCode = $agreementInfo['postalCode'];

				if ($postalCodeDetails) {
					$user->latitude = $postalCodeDetails->latitude;
					$user->longitude = $postalCodeDetails->longitude;
				}
				else {
					$user->latitude = null;
					$user->longitude = null;
				}
				$user->resubscription = 'No';
				$user->save();
				//endregion

				// region Get user license details and payment info
				$license = License::where('owner', $user->id)->first();

				$licenseType = $license->licenseType;
				$licenseTypeDetail = LicenseType::find($licenseType);

				$userPaymentInfo = (array) $user->getPaymentInfo();
				$userPaymentInfo['paypal_email'] = $agreementInfo['payer_email'];
				$user->setPaymentInfo($userPaymentInfo);
				$userPaymentInfo = $userPaymentInfo;
				// endregion

				// region Update user transaction
				$transaction = new \App\Models\Transaction();
				$transaction->id = $agreementInfo['payment_id'];
				$transaction->type = "Resubscribe";
				$transaction->purchaser = $user->id;
				$transaction->amount = Session::get('licencePrice');
				$transaction->time = Carbon::now();
				$transaction->discount = Session::get('discount');
				$transaction->licenseType = $licenseType;
				$transaction->payer_id = $agreementInfo['payer_id'];
				$transaction->plan_id = Session::get('paypalPlanId');
				$transaction->state = $agreementInfo['payment_status'];
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

				//$this->sendInvoiceEmailToUser($user, $userPaymentInfo, $licenseTypeDetail);
				//region Add transaction to queue for approved transaction
				$trans = App\Models\Transaction::find($agreementInfo['payment_id']);

				$job = (new App\Jobs\SendApprovedTransaction($trans))->delay(300);
				$this->dispatch($job);
				//endregion
				Session::flash('successMessage', 'Payment successful and your license resubscribed successfully');
				return Redirect::to('user/profile');
			}
			catch(Exception $e) {
				Log::error($e);
				Session::flash("errorDetails", $e->getTraceAsString());
				Session::flash("errorMessage", "An error occurred processing payment. Error: " . $e->getMessage());

				return Redirect::to('user/profile');
			}
		}
		elseif ($action == "new" && $success == "false") {
			Session::flash('successMessage', 'Payment Cancelled.');
			$abandonedCart = Setting::get('abandonedCart');
			$totalAbandonedCart = $abandonedCart + 1;
			Setting::set('abandonedCart', $totalAbandonedCart);

			return Redirect::to('/');
		}
		elseif ($action == "trialrenew" && $success == "false") {
			Session::flash('successMessage', 'Payment Cancelled.');

			return Redirect::to('user/profile');
		}
		elseif ($action == "renew" && $success == "false") {
			Session::flash('successMessage', 'Payment Cancelled.');
			return Redirect::to('user/profile');
		}
		elseif ($action == "capacityincrease" && $success == "false") {
			Session::flash('successMessage', 'Payment Cancelled.');
			return Redirect::to('user/profile');
		}
		elseif ($action == "capacitydecrease" && $success == "false") {
			Session::flash('successMessage', 'Payment Cancelled.');
			return Redirect::to('user/profile');
		}
		elseif ($action == "detailchange" && $success == "false") {
			Session::flash('successMessage', 'Payment Cancelled.');
			return Redirect::route('user.paymentdetail.update');
		}
		elseif($action == 'resubscribe' && $success == 'false') {
			Session::flash('successMessage', 'Payment Cancelled.');

			return Redirect::to('user/profile');
		}
	}

	public function makePayment(Request $request) {
		if ($request->ajax()) {
			set_time_limit(0);

			$email = $request->input('email');
			$user = User::where("email", "=", $email)->first();

			if ($user) {
				// This email is already registered
				return ["status" => "error", "error" => "USEREXIST", "message" => "This email is already registered"];
			}

			// set all input values in session and redirect user to payment page
			Session::put('userInfo', $request->all());
			return ['status' => 'success', 'action' => 'redirect', 'url' => URL::route('user.payment.show')];
		}

		return ['status' => 'fail', 'message' => 'Unauthorised Access'];
	}

	/* seems like broken and is not used */
	public function makeRenewLicensePayment(Request $request) {
		if ($request->ajax()) {
			set_time_limit(0);
			$allVariables = Input::all();
			extract($allVariables);

			$total = (float)$request->input('total');

			$user = $this->user;
			$userID = $user->id;

			//$licenseType = License::where('owner', $userID)->first()->licenseType;
			$license = License::where('owner', $userID)->first();

			if($user->isTrial) {
				$licenseClass = $license->licenseClass;
				$licenseType = LicenseType::where('licenseClass', $licenseClass)->where('type', 'Paid')->first()->id;
			}
			else {
				$licenseType = $license->licenseType;
			}

			//Checking whether total amount is correct or not
			$couponCodeDetail = DiscountCode::select('discountPercent')
				->where('code', '=', $couponCode)
				->where('licenseType', '=', $licenseType)
				->first();

			$price = LicenseType::find($licenseType)->$preferredCurrency;

			if ($couponCodeDetail) {
				$discountPercent = $couponCodeDetail->discountPercent;

				$discount = ($discountPercent / 100) * $price;

				$actualTotal = $price - $discount;
			}
			else {
				$actualTotal = $price;
				$discount = 0;
			}

			$actualTotal = (float)$actualTotal;

			if (number_format($total, 2) == number_format($actualTotal, 2)) {
				$apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));

				$payer = new Payer();
				$payer->setPaymentMethod("paypal");

				$currencyType = explode('price', $preferredCurrency);
				$currencyType = trim($currencyType[1]);
				if ($currencyType == 'Euro') {
					$currencyType = 'EUR';
				}

				$item1 = new Item();
				$item1->setName("Upgrade License.");
				$item1->setCurrency($currencyType);
				$item1->setQuantity(1);
				$item1->setPrice($total);

				$itemList = new ItemList();
				$itemList->setItems([$item1]);

				$amount = new Amount();
				$amount->setCurrency($currencyType);
				$amount->setTotal($total);

				$transaction = new Transaction();
				$transaction->setAmount($amount);
				$transaction->setItemList($itemList);
				$transaction->setDescription("Payment description");

				if($user->isTrial) {
					Session::put('renewalType', 'trial');
					$returnUrlLink = URL::route('home.paymentreturnurl') . '?success=true&action=renew';
					$cancelUrlLink = URL::route('home.paymentreturnurl') . '?success=false&action=renew';
				}
				else {
					Session::put('renewalType', 'paid');
					$returnUrlLink = URL::route('home.paymentreturnurl') . '?success=true&action=renew';
					$cancelUrlLink = URL::route('home.paymentreturnurl') . '?success=false&action=renew';
				}


				$redirectUrls = new RedirectUrls();
				$redirectUrls->setReturnUrl($returnUrlLink);
				$redirectUrls->setCancelUrl($cancelUrlLink);

				$payment = new Payment();
				$payment->setIntent("sale");
				$payment->setPayer($payer);
				$payment->setRedirectUrls($redirectUrls);
				$payment->setTransactions([$transaction]);

				try {
					$payment->create($apiContext);
				}
				catch (Exception $ex) {
					return ["status"  => "error", "error" => "SOMETHINGWRONG","message" => "Error: " . ($ex->getMessage())];
				}

				//Setting all variable to Session variable
				Session::put("renewUserInfo", $allVariables);
				Session::put("renewUserDiscount", $discount);

				$approvalUrl = $payment->getApprovalLink();

				$data['success'] = "success";
				$data['redirectUrl'] = $approvalUrl;

			}
			else {
				return ["status"  => "error", "error" => "SOMETHINGWRONG","message" => "Error: Payment amount mismatch. Please try again!"];
			}
		}

		return json_encode($data);
	}

	public function makeAddMemberPayment(Request $request) {
		if ($request->ajax()) {
			set_time_limit(0);
			$allVariables = Input::all();
			extract($allVariables);

			$userID = Auth::user()->get()->id;

			$licenseType = License::select('licenseType')->where('owner', $userID)->first()->licenseType;

			if ($totalMembers == 0) {
				return ["status"  => "error", "error" => "SOMETHINGWRONG", "message" => "Error: You should add at least one member"];
			}

			if ($preferredCurrency == "USD") {
				$actualTotal = $totalMembers * Setting::get('perMemberDollarPrice');
			}
			else if ($preferredCurrency == "EUR") {
				$actualTotal = $totalMembers * Setting::get('perMemberEuroPrice');
			}
			else if ($preferredCurrency == "GBP") {
				$actualTotal = $totalMembers * Setting::get('perMemberGbpPrice');
			}

			if ($total == $actualTotal) {
				$apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));

				$payer = new Payer();
				$payer->setPaymentMethod("paypal");

				$currencyType = $preferredCurrency;
				if ($currencyType == 'Euro') {
					$currencyType = 'EUR';
				}

				$item1 = new Item();
				$item1->setName("Add Member to License.");
				$item1->setCurrency($currencyType);
				$item1->setQuantity("1");
				$item1->setPrice($total);

				$itemList = new ItemList();
				$itemList->setItems([$item1]);

				$amount = new Amount();
				$amount->setCurrency($currencyType);
				$amount->setTotal($total);

				$transaction = new Transaction();
				$transaction->setAmount($amount);
				$transaction->setItemList($itemList);
				$transaction->setDescription("Payment description");
				//->setInvoiceNumber(uniqid());

				$returnUrlLink = URL::route('home.paymentreturnurl') . '?success=true&action=capacityincrease';
				$cancelUrlLink = URL::route('home.paymentreturnurl') . '?success=false&action=capacityincrease';


				$redirectUrls = new RedirectUrls();
				$redirectUrls->setReturnUrl($returnUrlLink);
				$redirectUrls->setCancelUrl($cancelUrlLink);

				$payment = new Payment();
				$payment->setIntent("sale");
				$payment->setPayer($payer);
				$payment->setRedirectUrls($redirectUrls);
				$payment->setTransactions([$transaction]);

				try {
					$payment->create($apiContext);
				}
				catch (Exception $ex) {
					return ["status"  => "error", "error" => "SOMETHINGWRONG","message" => "Error: " . ($ex->getMessage())];
				}

				//Setting all variable to Session variable
				Session::put("addMemberLicenseInfo", $allVariables);

				$approvalUrl = $payment->getApprovalLink();

				$data['success'] = "success";
				$data['redirectUrl'] = $approvalUrl;

			}
			else {
				return ["status"  => "error", "error" => "SOMETHINGWRONG", "message" => "Error: Payment amount mismatch. Please try again!"];
			}
		}

		return json_encode($data);
	}

	public function checkEmail(Request $request) {
		$data = [];
		if ($request->ajax()) {
			$email = Input::get('email');

			$isEmailExists = User::where('email', '=', $email)->count();

			if ($isEmailExists > 0) {
				$data['emailFound'] = true;
			}

			$data['success'] = "success";
		}

		return $data;
	}

	private function cardType($number) {
		$number = preg_replace('/[^\d]/', '', $number);
		if (preg_match('/^3[47][0-9]{13}$/', $number)) {
			return 'amex';
		}
		elseif (preg_match('/^6(?:011|5[0-9][0-9])[0-9]{12}$/', $number)) {
			return 'discover';
		}
		elseif (preg_match('/^5[1-5][0-9]{14}$/', $number)) {
			return 'mastercard';
		}
		elseif (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $number)) {
			return 'visa';
		}
		else {
			return 'unknown';
		}
	}

	function registerTrialUser(Request $request) {

		$validation = [
			'firstName' => 'required',
			'email' => 'required|email|unique:users,email',
			'password' => 'required|min:6',
			'companyName' => 'required',
			'contactNumber' => 'required',
			'country' => 'required'
		];

		$this->validate($request, $validation);

		$licenceTypeID = $request->input('licenseType');
		$licenseType = LicenseType::find($licenceTypeID);

		$isEmailVerification = Setting::get('email_verification');

		$userType = $licenseType->licenseClass;

		// Postal code not found, Address not found

		// Create trial user

		$user = new User();
		$user->firstName = $request->input('firstName');
		$user->lastName = $request->input('lastName');
		$user->email = $request->input('email');
		$user->password = Hash::make($request->input('password'));
		$user->contactNumber = $request->input('contactNumber');
		$user->userType = $userType;
		$user->companyName = $request->input('companyName');
		$user->country = $request->input('country');
		$user->latitude = null;
		$user->longitude = null;

		if($isEmailVerification == 'Yes') {
			$user->email_verification = 'No';
			$user->verification_code = md5(time().$request->input('firstName'));
		}
		else {
			$user->email_verification = 'NoNeed';
		}

		$user->timeZoneName = $request->input('timeZoneName');

		$user->accountCreationDate = Carbon::now();
		$user->save();

		// set template
		CommonController::setEmailTemplate($user);

		// Add user to trial user track
		$trialTrack = [
			'user_id' => $user->id,
			'type' => 'Trial',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now()
		];

		$user->addTrialUserTrack($trialTrack);

		//add email credits

		$trialUserSetting = Setting::get('trialUserLimit');
		$emailCount = $trialUserSetting['emails'];

		$user->updateEmailCredit($emailCount);

		// Admin Email and User email (or verification email in case admin email this feature)
		if($isEmailVerification == 'Yes') {
			$template = AdminEmailTemplate::where('id', 'EMAIL_VERIFICATION')->first();

			$fieldValues = [
					'FIRST_NAME' => $user->firstName,
					'LAST_NAME' => $user->lastName,
					'SITE_NAME' => Setting::get('siteName'),
					'SUPPORT_EMAIL' => Setting::get('supportEmail'),
					'LINK' => URL::route('home.register.verify', [$user->verification_code])
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
		}
		else {
			$template = AdminEmailTemplate::where('id', 'NEWACCOUNT')->first();

			$fieldValues = [
					'FIRST_NAME' => $user->firstName,
					'LAST_NAME' => $user->lastName,
					'SITE_NAME' => Setting::get('siteName'),
					'SUPPORT_EMAIL' => Setting::get('supportEmail')
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
		}


		// No Need to create Transaction

		$licenceExpireDate = Carbon::now()->addDays(Setting::get('trial_period'));

		$license = new License();
		$license->owner = $user->id;
		$license->purchaseTime = Carbon::now();
		$license->expireDate = $licenceExpireDate;
		$license->licenseType = $licenceTypeID;
		$license->licenseClass = $userType;

		//copy default values
		$licenseTypeDetail = LicenseType::where('licenseClass', 'Multi')->where('type', 'Paid')->first();
		$license->priceUSD = $licenseTypeDetail->priceUSD;
		$license->priceGBP = $licenseTypeDetail->priceGBP;
		$license->priceEuro = $licenseTypeDetail->priceEuro;

		$license->priceUSD_year = $licenseTypeDetail->priceUSD_year;
		$license->priceGBP_year = $licenseTypeDetail->priceGBP_year;
		$license->priceEuro_year = $licenseTypeDetail->priceEuro_year;
		$license->discount = intval($licenseTypeDetail->discount);
		
		$license->licenseVolume = $licenseTypeDetail->free_users; 
		$license->free_users = $licenseTypeDetail->free_users;
		$license->max_users = $licenseType->max_users;

		$license->save();

		// No need to generate invoice
		$message = 'asfsadg';

		if($isEmailVerification == 'Yes') {
			$message = 'Thank you for registering with SanityOS, please check your email and verify your account before logging in.';
		}
		else {
			$message = 'Thank you for registering with SanityOS, please login to start your free trial.';
		}

		Session::put('successMessage1', $message);
		Session::put('trial', true);

		return [
			'status' => 'success',
			'redirectUrl' => URL::route('user.login')
		];
	}

	function emailVerification($code) {
		if($code == null || $code == '') {
			App::abort('404', 'Verification code not found');
		}
		$user = User::where('verification_code', $code)->first();

		if(!$user) {
			App::abort('404', 'User not found');
		}

		$user->email_verification = 'Yes';
		$user->save();

		return Redirect::route('user.login')->with('successMessage1', 'Thank you for verifying your email, Please sign in to continue.');
	}

	private function getAgreementInformation($token) {
		$data = [];
		$apiContext = new ApiContext(new OAuthTokenCredential(Setting::get('paypalID'), Setting::get('apiSignature')));

		$agreement = new Agreement();
		$agreement->execute($token, $apiContext);

		$agreement = Agreement::get($agreement->getId(), $apiContext);
		\Log::error("confirmed paypal agreement : " . print_r($agreement, true));

		$data['payment_id'] = $agreement->id;

		$data['payment_status'] = $agreement->state;
		$data['payer_fname'] = $agreement->payer->payer_info->first_name;
		$data['payer_lname'] = $agreement->payer->payer_info->last_name;
		$data['payer_id'] = $agreement->payer->payer_info->payer_id;
		$data['payer_email'] = $agreement->payer->payer_info->email;

		$data['postalCode'] = $agreement->payer->payer_info->shipping_address->postal_code;
		$data['countryCode'] = $agreement->payer->payer_info->shipping_address->country_code;
		$data['addressLine1'] = $agreement->payer->payer_info->shipping_address->line1;
		$data['city'] = $agreement->payer->payer_info->shipping_address->city;
		$data['state'] = $agreement->payer->payer_info->shipping_address->state;

		$data['billingAddress'] = $data['addressLine1'] . ', ' . $data['city'] . ', ' . $data['state'];

		return $data;
	}

}
