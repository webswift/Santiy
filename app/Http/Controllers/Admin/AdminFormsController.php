<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Form;
use App\Models\FormFields;
use App\Models\Lead;
use App\Models\LeadCustomData;
use DateTime;
use Input;
use Request;
use Session;
use View;

class AdminFormsController extends Controller {

	public function createOrEdit() {

		$adminUser = CommonController::getAdminUserInfo();
        $formLists = Form::where('creator', $adminUser->id)->get();

        $data = [
		        'FormMenuActive' => "active",
		        'formLists' => $formLists
        ];

        $data['successMessage'] 	= Session::get('successMessage');
		$data['successMessageClass'] 	= Session::get('successMessageClass');

		return View::make('admin/form/createoredit', $data);
	}

	public function formFieldsDetails() {
		$data = [];

    	if (Request::ajax()) {
    		$formID = Input::get('formID');

    		$formFields = FormFields::where('formID', $formID)->get();

    		$data['success'] = 'success';
    		$data['formFields'] = $formFields;
    	}

    	return json_encode($data);
	}

	public function addOrEditForm() {
		$data = [];
		set_time_limit(0);

    	if (Request::ajax()) {
    		$action = Input::get('action');
    		$formFields = json_decode(Input::get('formFields'));

    		if($action == 'add') {
    			$formName 	= Input::get('formName');

			    $adminUser = CommonController::getAdminUserInfo();

    			//Creating a Form
    			$newForm = new Form;
    			$newForm->formName = $formName;
    			$newForm->creator  = $adminUser->id;
    			$newForm->time = new DateTime;
    			$newForm->save();

    			$formID = $newForm->id;

                //Inserting new Fields
                foreach($formFields as $formFieldName) {
                    //Adding Form Fields
                    $newFormFields = new FormFields;
                    $newFormFields->fieldName = $formFieldName;
                    $newFormFields->formID    = $formID;
                    $newFormFields->save();
                }

    			Session::flash('successMessage', 'New Form Successfully Added.');
				Session::flash('successMessageClass', 'success');

    		}
		    else if($action == 'edit') {
    			$formID = Input::get('formID');

                //Getting fields before deleting Form Fields
                $beforeFields = FormFields::where('formID', $formID)->get();
                $beforeFieldsArray = [];

                foreach($beforeFields as $beforeField) {
                    $beforeFieldsArray[$beforeField->fieldName] = $beforeField->id;
                }


                //Deleting Fields before inserting
    			FormFields::where('formID', $formID)->delete();

                //Inserting new Fields
                foreach($formFields as $formFieldName) {
                    //Adding Form Fields
                    $newFormFields = new FormFields;
                    $newFormFields->fieldName = $formFieldName;
                    $newFormFields->formID    = $formID;
                    $newFormFields->save();
                }

                //Getting all Lead which have formID
                $allLeads = Lead::select(['leads.id as leadID'])
                                ->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
                                ->where('campaigns.formID', $formID)
                                ->get();


    			//Getting all fields for Edited Form
    			$alreadyPresentFields = FormFields::where('formID', $formID)->get();
				foreach($alreadyPresentFields as $alreadyPresentField)
    			{
                    $fieldName = $alreadyPresentField->fieldName;
    				$fieldID   = $alreadyPresentField->id;

    				//removing Fields from ajax array if it already present in database
    				if(array_key_exists($fieldName, $beforeFieldsArray))
    				{
                        foreach($allLeads as $allLead)
                        {
                            $leadID = $allLead->leadID;
                            //updating fieldID
                            LeadCustomData::where('leadID', $leadID)
                                          ->where('fieldID', $beforeFieldsArray[$fieldName])
                                          ->update(['fieldID' => $fieldID]);
                        }

    					unset($beforeFieldsArray[$fieldName]);
    				}else{

                        //Putting data to leadcustomdata tables for fields which are new
                        foreach($allLeads as $allLead)
                        {
                            $leadID = $allLead->leadID;

                            $newRow = new LeadCustomData;
                            $newRow->leadID     = $leadID;
                            $newRow->fieldID    = $fieldID;
                            $newRow->fieldName  = $fieldName;
                            $newRow->value      = '';
                            $newRow->save();
                        }

    				}
    			}
				
				//delete form fields from leads' data
				LeadCustomData::whereIn('fieldID', $beforeFieldsArray)
					->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
					->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
					->where('campaigns.status', '!=',  'Completed')
					->where('campaigns.formID', $formID)
					->delete();


    			Session::flash('successMessage', 'Form Successfully Edited.');
				Session::flash('successMessageClass', 'success');
    		}

    		

    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

	function deleteForm() {
		$data = [];

    	if (Request::ajax()) {
    		$formID = Input::get('formID');

		    // Check if any campaign exists with this form

		    $campaignCount = Campaign::where("formID", $formID)->count();

		    if ($campaignCount > 0) {
			    $data["success"] = "fail";
			    $data["message"] = "Campaigns with this form exist. This form cannot be deleted";
		    }
		    else {
			    //Deleteting form fields
			    FormFields::where('formID', $formID)->delete();

			    //Deleting form
			    Form::find($formID)->delete();

			    Session::flash('successMessage', 'Form Successfully Deleted.');
			    Session::flash('successMessageClass', 'danger');

			    $data['success'] = 'success';
		    }
    	}

    	return json_encode($data);
	}

	public function showDemoForm() {
		$formFieldArrays = json_decode(Input::get('fieldArray'));
        $data['emailFieldExists'] = json_decode(Input::get('emailFieldExists'));

		$leadFormDatas1 = [];
		$leadFormDatas2 = [];

		foreach($formFieldArrays as $fieldName)
		{
			if(strtolower($fieldName) == 'address' || strtolower($fieldName) == 'post/zip code' || strtolower($fieldName) == 'notes' || strtolower($fieldName) == 'website')
			{
				$leadFormDatas2[] = [
								 'fieldName'	=> $fieldName
								];
			}else{
				$leadFormDatas1[] = [
								 'fieldName'	=> $fieldName
								];
			}
		}

		$data['leadFormDatas1'] = $leadFormDatas1;
		$data['leadFormDatas2'] = $leadFormDatas2;

		return View::make('admin/form/demoform', $data);
	}

}
