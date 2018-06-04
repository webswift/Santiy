<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Formmapping;
use App\Models\Landingform;
use App\Models\Landingformfields;
use App\Models\Form;
use App\Models\Setting;
use App\Models\User;
use App\Http\Requests\SimpleRequest;
use Input;
use Request;
use Session;
use View;


/**
 * Created by PhpStorm.
 * User: Shalu
 * Date: 20/08/2015
 * Time: 01:04 PM
 */

class LandingFormUserController extends Controller{

	function index() {
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

		$formLists = Landingform::whereIn('creator', $teamMembersIDs)->get();

		$data = [
			'leadsMenuActive'    =>  'nav-active active',
			'leadsStyleActive'   =>  'display: block',
			'settingsFormStyleActive' =>  'active',
			'formLists' =>  $formLists
		];

		if($userType == 'Multi' || $userType == 'Team') {
			$data['allManagerUsers'] = User::select('id', 'firstName')->where('manager', $userManager)->get();
		}

		$data['landingFormLimit'] = Setting::get('maxLandingForm');

		$data['successMessage'] = Session::get('successMessage');
		$data['successMessageClass'] = Session::get('successMessageClass');

		return View::make('site/form/landingForm/createOrEdit', $data);
	}

	public function addOrEditForm() {
		$data = [];

		if (Request::ajax()) {
			$action = Input::get('action');
			$formFields = json_decode(Input::get('formFields'));

			$headerText = Input::get('headerText');
			$footerText = Input::get('footerText');
			$color = Input::get('color');
			$message = Input::get('message');

			if($action == 'add') {
				$formName = Input::get('formName');

				//Creating a Form
				$newForm = new Landingform();
				$newForm->name = $formName;
				$newForm->creator = $this->user->id;

				//store image
				if(Input::hasFile('logo')) {

					$file = Input::file('logo');
					//$mimeType = $file->getMimeType();
					//$fileName = $file->getClientOriginalName();
					$extension = $file->getClientOriginalExtension();

					$newFileName = time().'.'.$extension;

					if(($extension != 'php' && $extension != 'js' && $extension != 'exe')){
						$file->move(public_path().'/assets/uploads/forms/logo/', $newFileName);
					}

					$newForm->logo = $newFileName;
				}


				$newForm->header = $headerText;
				$newForm->footer = $footerText;
				$newForm->color = $color;
				$newForm->thankYouMessage = $message;

				$newForm->save();
				$formID = $newForm->id;

				$count = 0;
				//Inserting new Fields
				foreach($formFields as $formFieldName) {
					//Adding Form Fields
					$newFormFields = new Landingformfields();
					$newFormFields->fieldName = $formFieldName->name;
					$newFormFields->type = $formFieldName->type;
					$newFormFields->formID = $formID;

					if($formFieldName->required == true){
						$newFormFields->isRequired = 'Yes';
					}
					else{
						$newFormFields->isRequired = 'No';
					}

					if($formFieldName->type == 'dropdown'){
						$newFormFields->values = $formFieldName->value;
					}

					$newFormFields->order = $count + 1;
					$newFormFields->save();

					$count++;
				}

				Session::flash('successMessage', 'New Form Successfully Added.');
				Session::flash('successMessageClass', 'success');

			}
			else if($action == 'edit') {
				$formID = Input::get('formID');
				$form = Landingform::find($formID);

				if(Input::hasFile('logo')) {
					$file = Input::file('logo');
					$extension = $file->getClientOriginalExtension();

					$newFileName = time().'.'.$extension;

					if(($extension != 'php' && $extension != 'js' && $extension != 'exe')){
						$file->move(public_path().'/assets/uploads/forms/logo/', $newFileName);
					}

					//unlink old logo
					\File::delete(public_path().'/assets/uploads/forms/logo/'.$form->logo);

					$form->logo = $newFileName;
				}

				$form->header = $headerText;
				$form->footer = $footerText;
				$form->color = $color;
				$form->thankYouMessage = $message;

				$form->save();

				//Getting fields before deleting Form Fields
				$beforeFields = $form->getFormFields()->lists("fieldName")->all();
				$beforeFieldsIds = $form->getFormFields()->lists("id")->all();

				$toBeDeleted = [];
				$fieldNames = [];

				$count = 0;
				//Inserting new Fields
				foreach($formFields as $formFieldName) {
					// Check if a form field with this name is already there

					$fieldName = $formFieldName->name;
					$fieldNames[] = $fieldName;

					if (!in_array($fieldName, $beforeFields)) {
						$newFormFields = new Landingformfields();
						$newFormFields->fieldName = $formFieldName->name;
						$newFormFields->formID = $formID;
						$newFormFields->type = $formFieldName->type;

						if($formFieldName->required == true) {
							$newFormFields->isRequired = 'Yes';
						}
						else {
							$newFormFields->isRequired = 'No';
						}

						if($formFieldName->type == 'dropdown') {
							$newFormFields->values = $formFieldName->value;
						}
						$newFormFields->order = $count + 1;
						$newFormFields->save();
					}
					else {
						//get index of form name and find id
						$index = array_search($fieldName, $beforeFields);
						$id = $beforeFieldsIds[$index];
						$old = Landingformfields::find($id);

						if($formFieldName->required == true){
							$old->isRequired = 'Yes';
						}
						else{
							$old->isRequired = 'No';
						}

						$old->type = $formFieldName->type;

						$old->order = $count + 1;
						$old->save();
					}

					$count++;
				}

				// Now delete those fields which are no longer
				foreach($beforeFields as $beforeField) {
					if (!in_array($beforeField, $fieldNames)) {
							$toBeDeleted[] = $beforeField;
					}
				}

				//delete this form fields
				$form->deleteCustomFormFields($toBeDeleted);

				Session::flash('successMessage', 'Form Successfully Edited.');
				Session::flash('successMessageClass', 'success');
			}

			$data['success'] = 'success';
		}

		return json_encode($data);
	}

	public function formFieldsDetails() {
		$data = [];

		if (Request::ajax()) {
			$formID = Input::get('formID');
			$form = Landingform::find($formID);

			$formFields = $form->getFormFields();

			$data['success'] = 'success';
			$data['formFields'] = $formFields;
			$data['form'] = $form;
		}

		return $data;
	}

	function deleteForm() {
		$data = [];

		if (Request::ajax()) {
			$formID = Input::get('formID');

			Landingformfields::where('formID', $formID)->delete();
			Landingform::find($formID)->delete();

			Session::flash('successMessage', 'Form Successfully Deleted.');
			Session::flash('successMessageClass', 'success');

			$data['success'] = 'success';
		}

		return $data;
	}

	public function showDemoForm() {
		$formFieldArrays = json_decode(Input::get('formFields'));

		$data['header'] = Input::get('headerText');
		$data['footer'] = Input::get('footerText');

		$action = Input::get('action');

		$data['color'] = Input::get('color');

		if (isset($_FILES['logo'])) {
			$aExtraInfo = getimagesize($_FILES['logo']['tmp_name']);
			$data['logo'] = "data:" . $aExtraInfo["mime"] . ";base64," . base64_encode(file_get_contents($_FILES['logo']['tmp_name']));
		}
		else {
			if($action == 'edit') {
				$data['logo'] = Input::get('logoPath');
			}
		}

		$formData = [];

		foreach($formFieldArrays as $field) {
			$type = $field->type;
			$fieldName = $field->name;

			if(strtolower($fieldName) == 'address' || strtolower($fieldName) == 'notes') {
				$formData[] = ['fieldName' => $fieldName, 'type' => 'textarea'];
			}
			else {
				if($type == 'dropdown') {
					$formData[] = ['fieldName' => $fieldName, 'type' => $type, 'values' => explode(',', $field->value)];
				}
				else {
					$formData[] = ['fieldName' => $fieldName, 'type' => $type];
				}
			}
		}

		$data['formData'] = $formData;

		return View::make('site/form/landingForm/demoForm', $data);
	}

	function getMapping(SimpleRequest $request) {
		$landingFormID = $request->input('form');
		$landingForm = Landingform::findOrFail($landingFormID);
		$landingFormFields = $landingForm->getFormFields();

		if(sizeof($landingFormFields) > 0) {
			for($i = 0 ; $i < sizeof($landingFormFields); $i++) {
				$mapping = $landingFormFields[$i]->getMappingWithLeadForm();
				$landingFormFields[$i]['forms'] = $mapping->lists('formID')->all();
				$landingFormFields[$i]['fields'] = $mapping->lists('formFieldID')->all();
			}
		}
		
		if($request->has('leadFormId')) {
			//we know both forms, just map
			$leadFormId = $request->input('leadFormId');
			$leadForm = Form::findOrFail($leadFormId);
			$forms = [$leadForm];
			$data['singleLeadForm'] = $leadForm;
		} else {
			// Get user's forms
			$forms = $this->user->getAvailableForms();
		}


		for($i = 0; $i < sizeof($forms); $i++) {
			$forms[$i]['fields'] = $forms[$i]->getFormFields();
		}

		$data['landingForm'] = $landingForm;
		$data['landingFormFields'] = $landingFormFields;
		$data['forms'] = $forms;

		return View::make('site/form/landingForm/mapping', $data);
	}

	function setMapping() {
		$leadFormId = Input::get('leadFormID');
		$landingFormID = Input::get('landingFormID');
		$mapping = Input::get('mapping');

		foreach($mapping as $map) {
			$landingField = $map['landingFieldID'];
			$leadFormField = $map['leadFormFieldID'];

			if($leadFormField != null && $leadFormField) {

				// Check whether already an entry exists
				$alreadyMappedField = Formmapping::where('landingFieldID', $landingField)
					->where('formID', $leadFormId)
					->where('landingFormID', $landingFormID)
					->first();

				if($alreadyMappedField) {
					$alreadyMappedField->formFieldID = $leadFormField;
					$alreadyMappedField->save();
				}
				else {
					$formMapping = new Formmapping();
					$formMapping->landingFieldID = $landingField;
					$formMapping->formFieldID = $leadFormField;
					$formMapping->formID = $leadFormId;
					$formMapping->landingFormID = $landingFormID;
					$formMapping->save();
				}
			}
		}

		return [
			'status' => 'success'
		];
	}
}
