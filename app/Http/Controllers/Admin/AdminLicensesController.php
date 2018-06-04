<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use App\Models\LicenseType;
use App\Models\Setting;
use App\Models\Transaction;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Input;
use Maatwebsite\Excel\Excel;
use Validator;
use View;
use yajra\Datatables\Datatables;

class AdminLicensesController extends Controller {

	public function searchTransactions() {
        $data = [
            'licensesMenuActive' => 'nav-active active',
            'licensesStyleActive' =>  'display: block'
        ];
		return View::make('admin/license/searchtransaction', $data);
	}

	public function searchTransactionResults() {
		$results = Transaction::select(['transactions.id', 'users.firstName', 'transactions.time', 'transactions.discount', 'transactions.amount'])
							   ->join('users', 'users.id', '=', 'transactions.purchaser');

		if (Input::has('transactionType')) {
           $transactionType = Input::get('transactionType');
           $results->where('transactions.type', $transactionType);
        }
        if (Input::has('country')) {
           $country = Input::get('country');
           $results->where('users.country', $country);
        }
        if (Input::has('startDate')) {
           $startDate = Input::get('startDate');
           $results->where('transactions.time', '>', $startDate);
        }
        if (Input::has('endDate')) {
           $endDate = Input::get('endDate');
           $results->where('transactions.time', '<', $endDate);
        }

		return Datatables::of($results)
            ->edit_column('time', function ($row) {
                $time = CommonController::formatDateForDisplay($row->time);
                return $time;
            })
             ->edit_column('amount',function($row) {
                 if(Setting::get('baseCurrency') == 'USD')
                    $amount = "$". round($row->amount, 2);
                 else if(Setting::get('baseCurrency') == 'EUR')
                     $amount = "€". round($row->amount*Setting::get('euroToDollar'), 2);
                 elseif(Setting::get('baseCurrency') == 'GBP')
                    $amount = "£". round($row->amount*Setting::get('gbpToDollar'), 2);

                 return $amount;
             })
             ->edit_column('discount',function($row) {
                if(Setting::get('baseCurrency') == 'USD')
                    $amount = "$". round($row->discount, 2);
                else if(Setting::get('baseCurrency') == 'EUR')
                    $amount = "€". round($row->discount*Setting::get('euroToDollar'), 2);
                elseif(Setting::get('baseCurrency') == 'GBP')
                    $amount = "£". round($row->discount*Setting::get('gbpToDollar'), 2);

                return $amount;
             })
            ->make();
	}

    public function downloadTransactions() {
        $file = App::make('excel');

        $file->create('Filename', function($excel) {
            if(Setting::get('baseCurrency') == 'USD') {
                $amount = "$";
                $conversion = 1;
            }
            else if(Setting::get('baseCurrency') == 'EUR') {
                $amount = "€";
                $conversion = Setting::get('euroToDollar');
            }
            elseif(Setting::get('baseCurrency') == 'GBP') {
                $amount = "£";
                $conversion = Setting::get('gbpToDollar');
            }

            $results =  Transaction::select(DB::raw("transactions.id as id, CONCAT('".$amount."', TRUNCATE(transactions.amount * ".$conversion.", 2)) AS amount, transactions.time, users.firstName, CONCAT('".$amount."', TRUNCATE(transactions.discount* ".$conversion.", 2)) AS discount"))
                               ->join('users', 'users.id', '=', 'transactions.purchaser');

            if (Input::has('transactionType')) {
               $transactionType = Input::get('transactionType');
               $results->where('transactions.type', $transactionType);
            }
            if (Input::has('country')) {
               $country = Input::get('country');
               $results->where('users.country', $country);
            }
            if (Input::has('startDate')) {
               $startDate = Input::get('startDate');
               $results->where('transactions.time', '>', $startDate);
            }
            if (Input::has('endDate')) {
               $endDate = Input::get('endDate');
               $results->where('transactions.time', '<', $endDate);
            }

            $result = $results->get();

            $excel->sheet('Sheetname', function($sheet) use($result) {
                $sheet->with($result);

            });
            
        })->download('csv');
    }

	public function shoppingCart() {
		//$licenseTypes = LicenseType::type('Paid')->get();
		$successMessage = Session::get('successMessage');

		$data = [
				//'licenseTypes'		       => $licenseTypes,
				'successMessage'	       => $successMessage,
                'licensesMenuActive'       => 'nav-active active',
                'licensesStyleActive'      =>  'display: block',
                'shoppingCartMenuActive'      =>  'active'
			];

        /*$data['dollarPrice'] = Setting::get('perMemberDollarPrice');
        $data['euroPrice'] = Setting::get('perMemberEuroPrice');
        $data['gbpPrice'] = Setting::get('perMemberGbpPrice');*/

		$data['trial_cart'] = Setting::get('trial_cart');
		$data['trial_period'] = Setting::get('trial_period');
		$data['conversion_tracking_code'] = Setting::get('conversion_tracking_code');
		$data['email_verification'] = Setting::get('email_verification');

        $licenseType  = LicenseType::where('licenseClass', 'Multi')->where('type', 'Paid')->first();
        //$discountCodes = DiscountCode::where('licenseType', $licenseTypeID)->get();

        $data['licenseTypeDetail'] = $licenseType;
        /*[
            'licenseTypeDetail'	=> $licenseTypeDetail,
            'discountCodes' => $discountCodes
        ];*/

		return View::make('admin/license/shoppingcart', $data);
	}

	public function shoppingCartForm($licenseTypeID) {
		$licenseTypeDetail  = LicenseType::find($licenseTypeID);
        $discountCodes = DiscountCode::where('licenseType', $licenseTypeID)->get();

		$data = [
				'licenseTypeDetail'	=> $licenseTypeDetail,
                'discountCodes' => $discountCodes
			];
		return View::make('admin/license/shoppingcartform', $data);
	}

	public function updateShoppingCart($licenseTypeID) {
		ini_set('max_execution_time', 0);
		// Function must be called only via Ajax request
        if (\Request::ajax()) {
            $error = '';

            //validation Array
            $validatorArray = [
                'productName'	=> 'required',
                'description' 	=> 'required',
                'usdPrice'		=> 'required|numeric',
                'euroPrice'		=> 'required|numeric',
                'gbpPrice'		=> 'required|numeric',
                'priceUSD_year'	=> 'required|numeric',
                'priceEuro_year'=> 'required|numeric',
                'priceGBP_year'	=> 'required|numeric',
				'discount'  => 'required|numeric',
            ];

            if(Input::get('licenseClass') === 'Multi')  {
            	$validatorArray['freeUsers'] = 'required'; 
            }
			//Validation Rules for user Info
            $validator = Validator::make( Input::all(), $validatorArray);

            if ($validator->fails()) {
				//If Validation rule fails
                $messages = $validator->messages();
                foreach ($messages->all() as $message) {
                    $error .= $message . '<br>';
                }

                return ['status' => 'fail', 'message' => $error];
            }
            else {
            	$licenseType = LicenseType::find($licenseTypeID);

            	$licenseType->name = Input::get('productName');
            	$licenseType->description = Input::get('description');

	            $priceUSD = Input::get('usdPrice');
	            $priceGBP = Input::get('gbpPrice');
	            $priceEUR = Input::get('euroPrice');
	            $priceUSD_year = Input::get('priceUSD_year');
	            $priceGBP_year = Input::get('priceGBP_year');
	            $priceEuro_year = Input::get('priceEuro_year');

				/* don't cancel exists agreements
	            if($priceUSD != $licenseType->priceUSD) {
					$currency = "USD";
		            $recurringType = 'Monthly';

		            CommonController::cancelAgreement($recurringType, $currency);
	            }

	            if($priceGBP != $licenseType->priceGBP) {
		            $currency = "GBP";
		            $recurringType = 'Monthly';

		            CommonController::cancelAgreement($recurringType, $currency);
	            }

	            if($priceEUR != $licenseType->priceEUR) {
		            $currency = "EUR";
		            $recurringType = 'Monthly';

		            CommonController::cancelAgreement($recurringType, $currency);
	            }

	            if($priceUSD_year != $licenseType->priceUSD_year) {
		            $currency = "USD";
		            $recurringType = 'Annually';

		            CommonController::cancelAgreement($recurringType, $currency);
	            }

	            if($priceGBP_year != $licenseType->priceGBP_year) {
		            $currency = "GBP";
		            $recurringType = 'Annually';

		            CommonController::cancelAgreement($recurringType, $currency);
	            }

	            if($priceEuro_year != $licenseType->priceEuro_year) {
		            $currency = "EUR";
		            $recurringType = 'Annually';

		            CommonController::cancelAgreement($recurringType, $currency);
	            }
				*/

            	$licenseType->priceUSD = $priceUSD;
            	$licenseType->priceGBP = $priceGBP;
            	$licenseType->priceEuro = $priceEUR;
                $licenseType->priceUSD_year = $priceUSD_year;
                $licenseType->priceGBP_year = $priceGBP_year;
                $licenseType->priceEuro_year = $priceEuro_year;

                if(Input::has('freeUsers')) {
                    $licenseType->free_users = Input::get('freeUsers');
                }

                if(Input::has('discount')) {
                    $licenseType->discount = Input::get('discount');
                }

            	$licenseType->save();

                //$discountCodes = json_decode(Input::get('inputtags'));

                //Deleting Rows before inserting rows 
                /*DiscountCode::where('licenseType', $licenseTypeID)->delete();

                foreach($discountCodes as $discountCode) {
                    $discount = new DiscountCode;
	                $discount->licenseType      = $licenseTypeID;
                    $discount->code             = $discountCode->code; 
                    $discount->discountPercent  = $discountCode->discount;
                    $discount->added = new DateTime;

                    $discount->save();
                }*/

                return ['status' => 'success', 'message' => 'License information successfully updated'];
            }
        }

        return ['status' => 'fail', 'message' => 'Unauthorised request'];
	}

    public function addOneMemberPrice() {
        $data = [];
        if(\Request::ajax()) {
            $dollarPrice = Input::get('dollarPrice');
            $euroPrice = Input::get('euroPrice');
            $gbpPrice = Input::get('gbpPrice');

            Setting::set('perMemberDollarPrice', $dollarPrice);
            Setting::set('perMemberEuroPrice', $euroPrice);
            Setting::set('perMemberGbpPrice', $gbpPrice);

            Session::flash('successMessage', 'Per Member Price Saved Successfully.');

            $data['success'] = "success";
        }

        return json_encode($data);
    }

    function trialSettings(Request $request) {
        if($request->has('trial_cart')) {
	        Setting::set('trial_cart', 'Yes');
        }
	    else {
		    Setting::set('trial_cart', 'No');
	    }

	    Setting::set('trial_period', $request->input('trial_period'));
	    Setting::set('conversion_tracking_code', $request->input('conversion_tracking_code'));

	    if($request->has('email_verification')) {
		    Setting::set('email_verification', 'Yes');
	    }
	    else {
		    Setting::set('email_verification', 'No');
	    }

	    return ['status' => 'success', 'message' => 'Trial settings updated'];
    }

}
