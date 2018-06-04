<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormFields;
use App\Models\Lead;
use App\Models\LeadCustomData;
use App\Models\User;
use App\Models\Campaign;
use DateTime;
use Input;
use Request;
use Session;
use View;
use DB;
use Log;

class FormsUserController extends Controller {

	public function index() {
		$data = [
			'leadsMenuActive'    =>  'nav-active active',
			'leadsStyleActive'   =>  'display: block',
			'settingsFormStyleActive'   =>  'active'
		];

		return View::make('site/form/index', $data);
	}

	public function createOrEdit() {

        $userType = $this->user->userType;

        if($userType == 'Multi' || $userType == 'Single') {
            $userManager = $this->user->id;
        }
        else if($userType == 'Team') {
            $userManager = $this->user->manager;
        }

        $teamMembers =  $this->user->getTeamMembers();

        $teamMembersIDs = $teamMembers->lists('id')->all();

        $teamMembersIDs[] = $userManager;

        $formLists = Form::whereIn('creator', $teamMembersIDs)->get();

        $data = [
			'leadsMenuActive'    =>  'nav-active active',
			'leadsStyleActive'   =>  'display: block',
            'settingsFormStyleActive' => 'active',
            'formLists' => $formLists
        ];

        if($userType == 'Multi' || $userType == 'Team') {
            $data['allManagerUsers'] = User::select('id', 'firstName')->where('manager', $userManager)->get();
        }

        $data['successMessage'] = Session::get('successMessage');
		$data['successMessageClass'] = Session::get('successMessageClass');

		return View::make('site/form/createoredit', $data);
	}

	public function formFieldsDetails() {
		$data = [];

		if (Request::ajax()) {
			$formID = Input::get('formID');
			$form = Form::find($formID);

    		$formFields = $form->getFormFields();

    		$data['success'] = 'success';
    		$data['formFields'] = $formFields;
    	}

    	return json_encode($data);
	}

	public function addOrEditForm() {

		//run forever
		ignore_user_abort(true);
		set_time_limit(0);

		$data = [];

		if (Request::ajax()) {
			$action = Input::get('action');
    		$formFields = json_decode(Input::get('formFields'));

    		if($action == 'add') {
			    $formName 	= Input::get('formName');

    			//Creating a Form
    			$newForm = new Form;
    			$newForm->formName = $formName;
    			$newForm->creator = $this->user->id;
    			$newForm->time = new DateTime;
    			$newForm->save();

    			$formID = $newForm->id;

			    $isNotes = false;

			    $count = 0;
                //Inserting new Fields
                foreach($formFields as $formFieldName) {

	                if($formFieldName->name == 'Notes'){
		                $isNotes = true;
	                }

                    //Adding Form Fields
                    $newFormFields = new FormFields;
                    $newFormFields->fieldName = $formFieldName->name;
	                $newFormFields->type = $formFieldName->type;
                    $newFormFields->formID = $formID;

	                if($formFieldName->type == 'dropdown') {
						$newFormFields->values = $formFieldName->value;
	                }

	                if($formFieldName->required == true) {
		                $newFormFields->isRequired = 'Yes';
	                }
	                else {
		                $newFormFields->isRequired = 'No';
	                }

	                $newFormFields->order = $count + 1;
                    $newFormFields->save();

	                $count++;
                }

			    if(!$isNotes) {
				    $newFormFields = new FormFields;
				    $newFormFields->fieldName = 'Notes';
				    $newFormFields->type = 'text';
				    $newFormFields->formID = $formID;
				    $newFormFields->order = $count + 1;
					$newFormFields->save();
			    }

    			Session::flash('successMessage', 'New Form Successfully Added.');
				Session::flash('successMessageClass', 'success');
    		}
		    else if($action == 'edit') {
				try 
				{
					DB::beginTransaction();

					$formID = Input::get('formID');

					$form = Form::find($formID);

					// Getting fields before deleting Form Fields
					$fields = $form->getFormFields();
					$beforeFields = $fields->lists("fieldName")->all();
					$beforeFieldsIds = $fields->lists("id")->all();

					$leadIDs = Lead::select("leads.id AS leadID")
							->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
							->where('campaigns.formID', $formID)
							->lists("leadID")
							->all();

					$toBeDeleted = [];
					$toBeInserted = [];
					$toBeRenamed = [];

					$fieldNames = [];

					$isNotes = false;
					$alreadyExistsNotes = false;

					if(in_array('Notes', $beforeFields)) {
						$alreadyExistsNotes = true;
					}

					//check if form

					$count = 0;

					//Inserting new Fields
					foreach($formFields as $formFieldName) {
						// Check if a form field with this name is already there

						$fieldName = $formFieldName->originalName;
						$fieldNames[] = $fieldName;

						if (!in_array($fieldName, $beforeFields)) {
							//new field

							if(!$alreadyExistsNotes && $formFieldName->name == 'Notes'){
								$isNotes = true;
							}

							$newFormFields = new FormFields;
							$newFormFields->fieldName = $formFieldName->name;
							$newFormFields->formID = $formID;
							$newFormFields->type = $formFieldName->type;
							$newFormFields->order = $count + 1;

							if($formFieldName->type == 'dropdown') {
								$newFormFields->values = $formFieldName->value;
							}

							if($formFieldName->required == true) {
								$newFormFields->isRequired = 'Yes';
							}
							else{
								$newFormFields->isRequired = 'No';
							}

							$newFormFields->save();

							foreach($leadIDs as $lead) {
								$toBeInserted[] = ['fieldName' => $formFieldName->name, 'fieldID' => $newFormFields->id, 'value' => '', 'leadID' => $lead];
							}
						}
						else {
							//updated field

							//get index of form name and find id
							$index = array_search($fieldName, $beforeFields);
							$id = $beforeFieldsIds[$index];
							$old = FormFields::find($id);

							if($formFieldName->required == true){
								$old->isRequired = 'Yes';
							}
							else{
								$old->isRequired = 'No';
							}

							$old->type = $formFieldName->type;
							$old->order = $count + 1;

							$fieldNewName = $formFieldName->name;
							if($fieldNewName != $fieldName) {
								Log::error("renaming field '{$fieldName}' to '{$fieldNewName}'");
								$old->fieldName = $fieldNewName;
								$toBeRenamed[] = [
									'oldName' => $fieldName,
									'newName' => $fieldNewName,
									'id' => $id,
								];
							}

							if($formFieldName->type == 'dropdown') {
								if($formFieldName->value != $formFieldName->originalValue) {
									//there were changes, we need to 'replay' them
									$optionChanges = $formFieldName->optionsChanges;
									foreach($optionChanges as $optionChange) {
										if($optionChange->changeType == "add_option") {
											//check duplicates etc
											Log::error("adding option for field '{$fieldName}' name '{$optionChange->newValue}'");
										} elseif($optionChange->changeType == "rename_option") {
											//update leads with new value
											Log::error("renaming option for field '{$fieldName}' from '{$optionChange->originalValue}' to '{$optionChange->newValue}'");
											LeadCustomData::where('fieldName', '=', $fieldName)
												->where('fieldID', '=', $id)
												->where('value', '=', $optionChange->originalValue)
												->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
												->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
												->where('campaigns.status', '!=',  'Completed')
												->where('campaigns.formID', $formID)
												->update(['value' => $optionChange->newValue]);
										}
									}
								}
								$old->values = $formFieldName->value;
							}

							$old->save();
						}

						$count++;
					}

					if(!$alreadyExistsNotes && !$isNotes){
						$newFormFields = new FormFields;
						$newFormFields->fieldName = 'Notes';
						$newFormFields->formID = $formID;
						$newFormFields->type = 'text';
						$newFormFields->order = $count + 1;
						$newFormFields->save();

						foreach($leadIDs as $lead){
							$toBeInserted[] = ['fieldName' => 'Notes', 'fieldID' => $newFormFields->id, 'value' => '', 'leadID' => $lead];
						}
					}

					// Now delete those fields which are no longer
					foreach($beforeFields as $beforeField) {
						if (!in_array($beforeField, $fieldNames)) {
							if($beforeField != 'Notes'){
								$toBeDeleted[] = $beforeField;
							}
						}
					}

					//delete this form fields
					$form->deleteCustomFormFields($toBeDeleted);

					//delete form fields from leads' data
					LeadCustomData::whereIn('fieldName', $toBeDeleted)
						->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
						->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
						->where('campaigns.status', '!=',  'Completed')
						->where('campaigns.formID', $formID)
						->delete();

					//insert new form fields to leads' data
					if(sizeof($toBeInserted) > 0){
						$chunks = array_chunk($toBeInserted, 1000);
						foreach($chunks as $chunk) {
							LeadCustomData::insert($chunk);
						}
					}

					//rename fields
					if(count($toBeRenamed) > 0) {
						foreach($toBeRenamed as $renameData) {
							LeadCustomData::where('fieldName', '=', $renameData['oldName'])
								->where('fieldID', '=', $renameData['id'])
								->join('leads', 'leads.id', '=', 'leadcustomdata.leadID')
								->join('campaigns', 'campaigns.id', '=', 'leads.campaignID')
								->where('campaigns.status', '!=',  'Completed')
								->where('campaigns.formID', $formID)
								->update(['fieldName' => $renameData['newName']]);
						}
					}

					DB::commit();
				}
				catch (\Exception $e) {
					DB::rollBack();
					Log::error("error on updating form : " + $e->getMessage());
					return ['status' => 'fail', 'message' => $e->getMessage(), 'type' => 'exception'];
				}

    			Session::flash('successMessage', 'Form Successfully Edited.');
				Session::flash('successMessageClass', 'success');
    		}

    		$data['status'] = 'success';
    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

	function deleteForm()
	{
    	if (Request::ajax())
    	{
    		$formID = Input::get('formID');

			$form = Form::find($formID);
			if(!$form) {
				return ["status"  => "fail", "message" => "Form not found"];
			}
			
			$this->authorize('can-see-form', $form);

			$associatedCampaigns = Campaign::where('formID', $formID)
				->where('status', '!=', 'Completed')
				->lists('name');

			if(count($associatedCampaigns)) {
				return ["status"  => "fail_associated_exists"
					, "campaigns" => $associatedCampaigns
					, "form_name" => $form->formName
					, "message" => "This method cannot be called directly"
				];
			}
			
			//unlink completed campaigns from form
			/*
			pdo prohibit this. weird \o/
			Campaign::where('formID', $formID)
				->update(['formID' => 'NULL']);
		 	*/
			DB::update('
				UPDATE campaigns 
				SET formID = NULL 
				WHERE formID = ?
			', [$formID]);

    		//Deleteting form fields
    		FormFields::where('formID', $formID)->delete();

    		//Deleting form
    		Form::find($formID)->delete();

    		Session::flash('successMessage', 'Form Successfully Deleted.');
			Session::flash('successMessageClass', 'danger');
			
			return ["status"  => "success", "message" => "Form Successfully Deleted."];
    	}
		else {
			return ["status"  => "fail", "message" => "This method cannot be called directly"];
		}

	}

	public function showDemoForm()
	{
		$formFieldArrays = json_decode(Input::get('fieldArray'));
        $data['emailFieldExists'] = json_decode(Input::get('emailFieldExists'));

		$leadFormDatas1 = [];
		$leadFormDatas2 = [];

		foreach($formFieldArrays as $field) {
			$type = $field->type;
			$fieldName = $field->name;

			if(strtolower($fieldName) == 'notes') {
				$leadFormDatas2[] = ['fieldName' => $fieldName];
			}
			else{
				if($type == 'dropdown'){
					$leadFormDatas1[] = ['fieldName' => $fieldName, 'type' => $type, 'values' => explode(',', $field->value)];
				}
				else{
					$leadFormDatas1[] = ['fieldName' => $fieldName, 'type' => $type];
				}
			}
		}

		$data['leadFormDatas1'] = $leadFormDatas1;
		$data['leadFormDatas2'] = $leadFormDatas2;

		return View::make('site/form/demoform', $data);
	}

}
