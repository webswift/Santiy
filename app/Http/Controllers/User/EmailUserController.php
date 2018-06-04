<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpleRequest;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\LeadCustomData;
use App\Models\MassEmail;
use App\Models\MassEmailTemplate;
use App\Models\Setting;
use App\Models\User;
use App\Models\FormFields;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;
use File;
use Gate;
use Input;
use Log;
use League\Csv\Reader;
use Redirect;
use Request;
use Session;
use View;
use yajra\Datatables\Datatables;
use Config;
use Exception;

class EmailUserController extends Controller {

	public function home() {
		$data = [
			'geoExportsMenuActive' => 'nav-active active',
			'geoExportsStyleActive' =>  'display: block',
			'settingsEmailStyleActive' =>  'active'
		];

		return View::make('site/email/index', $data);
	}

	public function index() {
		$successMessage = Session::get('successMessage');
		$successMessageClass = Session::get('successMessageClass');

		//Array to send data to view
		$data = [
                'geoExportsMenuActive' => 'nav-active active',
                'geoExportsStyleActive' =>  'display: block',
                'settingsEmailStyleActive' =>  'active',
                'successMessage' => $successMessage,
                'successMessageClass' => $successMessageClass
	        ];

		return View::make('site/email/viewemails', $data);
	}

	public function ajaxEmails() {
        $user = $this->user;

        $emailCreators = [$user->id];

        if ($user->userType == "Team") {
            $emailCreators[] = $user->manager;

	        $managerDefaultTemplate = EmailTemplate::where('emailtemplates.owner', $user->manager)->whereIn('name', ['Follow Up Call', 'Appointment booked'])->lists('id')->all();
        }

		$emails = EmailTemplate::select(['emailtemplates.id', 'emailtemplates.name', 'emailtemplates.timeCreated', 'users.firstName', 'emailtemplates.status', 'emailtemplates.teamMemberStatus'])
							   ->join('users', 'users.id', '=', 'emailtemplates.creator')
							   ->whereIn('emailtemplates.owner', $emailCreators);

		if($user->userType == 'Team') {
			$emails->whereNotIn('emailtemplates.id', $managerDefaultTemplate);
		}

		return Datatables::of($emails)
			->edit_column('timeCreated', function($row) {
				return \Carbon\Carbon::parse($row->timeCreated)->format('d M Y H:i:s');
			})
			->edit_column('name', function($row) {
				if($row->name == 'Appointment booked' || $row->name == 'Follow Up Call') {
					return 'Default Email: ' . $row->name;
				}
				else {
					return $row->name;
				}
			})
			->edit_column('status', function($row) {
				if($row->name == 'Appointment booked' || $row->name == 'Follow Up Call') {
					$str = '<a type="button" onclick="previewTemplate('.$row->id.')" class="btn btn-success btn-xs mr5">	<i class="fa fa-edit"></i> Preview</a>';
					$str .= '<a type="button" href="email/edit/'.$row->id.'" class="btn btn-info btn-xs mr5">	<i class="fa fa-edit"></i> Edit</a>';

					/*
					if($row->status == 'Disable' && $row->teamMemberStatus == 'Disable') {
						$name = 'Enable';
					}
					else {
						$name = 'Disable';
					}
					$str .= '<a type="button" href="javascript:;" class="btn btn-danger btn-xs mr5" onclick="showModal('.$row->id.', \''.$row->status.'\', \''.$row->teamMemberStatus.'\', \''.$row->name.'\');">	<i class="fa fa-edit"></i> '.$name.'</a>';
					 */
					return $str;
				}
				else {
					return '<a type="button" onclick="previewTemplate('. $row->id .')" class="btn btn-success btn-xs mr5">	<i class="fa fa-edit"></i> Preview</a> <a type="button" href="email/edit/'. $row->id .'" class="btn btn-info btn-xs mr5">	<i class="fa fa-edit"></i> Edit</a> <a type="button" onclick="deleteTemplate('. $row->id .')" class="btn btn-danger btn-xs mr5">	<i class="fa fa-edit"></i> Delete</a>';
				}
			})
			->remove_column('id')
			->make();
	}

	public function create() {
		$maxFileSize = Setting::get('maxFileUploadSize');

		$data = [
                'geoExportsMenuActive' => 'nav-active active',
                'geoExportsStyleActive' => 'display: block',
                'settingsEmailStyleActive' => 'active',
				'maxFileSize' => $maxFileSize
	        ];

		return View::make('site/email/createemail', $data);
	}

	public function edit($templateID) {
		$user = $this->user;
		$maxFileSize = Setting::get('maxFileUploadSize');

    	//$formFieldsNames = FormFields::select('fieldName')->distinct()->get();
		$templateDetails = EmailTemplate::find($templateID);
        $owner = $templateDetails->owner;

		if($owner == $user->id || $owner == $user->manager) {
			$attachments = $templateDetails->getEmailAttachment();

			$data = [
				'geoExportsMenuActive' => 'nav-active active',
				'geoExportsStyleActive' => 'display: block',
				'settingsEmailStyleActive' => 'active',
                'templateDetails' =>  $templateDetails,
				'attachments' => $attachments,
				'maxFileSize' => $maxFileSize
	        ];
			return View::make('site/email/editemail', $data);
		}
		else {
			return Redirect::to('user/email');
		}
	}

	public function addNewTemplate() {
		$data = [];

		if(!Input::has('templateName')) {
			return  ['success' => 'error'];
		}

		$user = $this->user;
        $templateName = Input::get('templateName');
        $subject = Input::get('subject');
        $templateContent = Input::get('templateContent');
        $shareWithTeam = Input::get("shareWithTeam");

        if ($shareWithTeam === "true" && $user->userType == "Team") {
            $owner = $user->manager;
        }
        else {
            $owner = $user->id;
        }

        $newEmailtemplate = new EmailTemplate;
        $newEmailtemplate->name = $templateName;
        $newEmailtemplate->templateText = $templateContent;
        $newEmailtemplate->subject = $subject;
        $newEmailtemplate->creator = $user->id;
        $newEmailtemplate->owner = $owner;
        $newEmailtemplate->timeCreated 	= new DateTime;
        $newEmailtemplate->save();

		$templateID = $newEmailtemplate->id;

		//check for attachments
		if(Input::hasFile('file')) {
			$filesToBeRemoved = json_decode(Input::get('fileDeleted'));

			foreach(Input::file('file') as $file) {
				$fileName = $file->getClientOriginalName();
				$extension = $file->getClientOriginalExtension();

				if(($extension != 'php' && $extension != 'js' && $extension != 'exe') && !in_array($fileName, $filesToBeRemoved)) {
					$file->move(storage_path().'/attachments/email/'.$templateID.'/', $fileName);

					$newEmailtemplate->addEmailAttachment($fileName);
				}

                //todo: file size limit
			}
		}

        $data['success'] = 'success';

        Session::flash('successMessage', 'Email Template Successfully Created.');
        Session::flash('successMessageClass', 'success');

    	return $data;
	}

	public function updateTemplate() {
		if(!Input::has('templateName')) {
			Session::flash('errorMessage', 'There is some error in email template editing. Please check the files you are uploading (type and size) and try again.');

			return  ['success' => 'error'];
		}

		$templateID = Input::get('templateID');
		$templateName = Input::get('templateName');
		$subject = Input::get('subject');
		$templateContent = Input::get('templateContent');
		$shareWithTeam = Input::get("shareWithTeam");

		$user = $this->user;
		$newEmailTemplate = EmailTemplate::find($templateID);

		if($newEmailTemplate->name != 'Follow Up Call' || $newEmailTemplate->name != 'Appointment booked') {
			$newEmailTemplate->name = $templateName;
		}

		$newEmailTemplate->subject = $subject;

		if ($shareWithTeam === "true" && $user->userType == "Team") {
            $newEmailTemplate->owner = $user->manager;
        }
		else {
            $newEmailTemplate->owner = $newEmailTemplate->creator;
        }

        $newEmailTemplate->templateText = $templateContent;
        $newEmailTemplate->save();

		$attachments = $newEmailTemplate->getEmailAttachment();
		$filesToBeRemoved = json_decode(Input::get('fileDeleted'));

		//remove selected old attachments
		if(sizeof($filesToBeRemoved) > 0) {
			foreach($filesToBeRemoved as $data) {
				if(in_array($data, $attachments)) {
					//delete from both physical and memory storage
					DB::table('email_attachment')->where('templateID', $newEmailTemplate->id)->where('fileName', $data)->delete();

					File::delete(storage_path().'/attachments/email/'.$newEmailTemplate->id.'/'.$data);
				}
			}
		}

		//check for attachments
		if(Input::hasFile('file')) {
			foreach(Input::file('file') as $file) {
				$fileName = $file->getClientOriginalName();
				$extension = $file->getClientOriginalExtension();

				if(($extension != 'php' && $extension != 'js' && $extension != 'exe') && !in_array($fileName, $filesToBeRemoved)){
					$file->move(storage_path().'/attachments/email/'.$templateID.'/', $fileName);

					$newEmailTemplate->addEmailAttachment($fileName);
				}

				//todo: file size limit
			}
		}

        Session::flash('successMessage', 'Email Template Successfully Updated.');
        Session::flash('successMessageClass', 'success');

    	return ['success' => 'success'];
	}

	public function deleteTemplate() {
		if (Request::ajax()) {
			$templateID = Input::get('templateID');

    		$template = EmailTemplate::find($templateID);

			$template->deleteEmailAttachments();
			$template->delete();

    		$data['success'] = 'success';

    		Session::flash('successMessage', 'Email Template Successfully Deleted.');
    		Session::flash('successMessageClass', 'success');
    	}

    	return json_encode($data);
	}

	public function previewTemplate() {
		if (Request::ajax()) {
    		$templateID = Input::get('templateID');

    		$data['templateContent'] = EmailTemplate::find($templateID)->templateText;
    		$data['success'] = 'success';
    	}

    	return json_encode($data);
	}

	public function setStatus() {
		$id = Input::get('email');
		$status = Input::get('status');
		$teamMemberStatus = Input::get('teamMember');

		$email = EmailTemplate::find($id);

		$email->status = $status;
		$email->teamMemberStatus = $teamMemberStatus;
		$email->save();
	}

	public function massEmailIndex() {

		$user = $this->user;
		
		if(Request::ajax()) {
			$query = MassEmailTemplate::whereIn('user_id', $this->user->getTeamMembersWithManagerIDs())
				->where(function($query) use($user) {
						//(
						//	(mass_email_templates.type = 'custom' AND mass_email_templates.user_id = :currentUserId) 
						//	OR 
						//	(mass_email_templates.campaign_id IN (SELECT campaignID FROM campaignmembers WHERE userID = :currentUserId )) 
						//)
						$query
							->where(function($query) use($user) {
								$query
									->where('mass_email_templates.type', 'custom')
									->where('mass_email_templates.user_id', $user->id)
									;
							})
							->orWhereNotNull('campaignmembers.campaignID');
					})
					->join('users', 'users.id', '=','mass_email_templates.user_id')
					->leftJoin('campaignmembers', function ($join) use($user) {
						$join->on('campaignmembers.campaignID', '=', 'mass_email_templates.campaign_id')
							 ->where('campaignmembers.userID', '=', $user->id);
					})
					->select('users.firstName', 'users.lastName', 'mass_email_templates.name', 'mass_email_templates.status', 'mass_email_templates.email_sent',
							'mass_email_templates.id', 'mass_email_templates.schedule_time')
					->orderBy('mass_email_templates.id', 'DESC');

			return Datatables::of($query)
					->editColumn('firstName', function($row) {
						return $row->firstName.' '. $row->lastName;
					})
					->editColumn('status', function($row) {
						if($row->status == 'Scheduled') {
							return $row->status.' ('.Carbon::parse($row->schedule_time)->format('d M Y').')';
						}
						elseif($row->status == 'Sent') {
							$firstMail = MassEmail::where('template_id', $row->id)->where('status', 'Sent')->orderBy('id', 'ASC')->first();
							if($firstMail) {
								return $row->status.' ('.Carbon::parse($firstMail->updated_at)->format('d M Y').')';
							}
						}

						return $row->status;
					})
					->editColumn('email_sent', function($row) {
						return $row->email_sent.' recipients';
					})
					->addColumn('actions', function($row) {
						$btn = '<button class="btn btn-sm btn-success previewBtn" type="button" title="Preview" data-id="'.$row->id.'"><i class="fa fa-edit"></i> Preview</button> ';

						if($row->status == 'Sent' || $row->status == 'Scheduled') {
							$btn .= '<a href="'.\URL::route('user.email.mass.reports', ['id' => $row->id]).'" class="btn btn-sm btn-info reportsBtn" title="Reports"><i class="fa fa-edit"></i> Reports</a> ';
							$btn .= '<a href="'.\URL::route('user.email.mass.duplicate', ['id' => $row->id]).'" class="btn btn-sm btn-warning duplicateBtn" type="button" title="Duplicate" data-id="\'.$row->id.\'"><i class="fa fa-edit"></i> Duplicate</a> ';
						}
						else {
							$btn .= '<a href="'.\URL::route('user.email.mass.edit', ['id' => $row->id]).'" class="btn btn-sm btn-warning editBtn" type="button" title="Edit" data-id="'.$row->id.'"><i class="fa fa-edit"></i> Edit</a> ';
						}

						$btn .= '<button class="btn btn-sm btn-danger deleteBtn" type="button" title="Delete" data-id="'.$row->id.'"><i class="fa fa-trash-o"></i> Delete</button> ';

						return $btn;
					})
					->removeColumn('lastName')
					->make(true);
		}

		$data = [
				'geoExportsMenuActive' => 'nav-active active',
				'geoExportsStyleActive' => 'display: block',
				'settingsEmailStyleActive' => 'active'
		];


		$data['warning'] = '';
		$emailCredits = 'Unlimited';
		if($user->userType == 'Single' || $user->userType == 'Multi') {
			$serverSetting = $this->user->getMassMailServerSetting();
			$data['serverSetting'] = $serverSetting;
			if(!$serverSetting || ($serverSetting->host == '' && $serverSetting->provider != 'mandrill')) {
				$data['warning'] = 'Please add a Mass Email Server to your account';
			}
		}

		if($data['warning'] == '')  {
			$postponeEmails = $user->checkForPostponeEmails();
			if($postponeEmails) {
				$data['warning'] = "You have reached 100% of your email credits, so email sending has been postponed. Process will be resumed as soon as you got email credits. ";
			}
		}

		$data['emailCredits'] = $emailCredits;
		return View::make('site/email/mass/index', $data);
	}

	public function massEmailCreate() {
		$maxFileSize = Setting::get('maxFileUploadSize');
		$data = [
				'geoExportsMenuActive' => 'nav-active active',
				'geoExportsStyleActive' => 'display: block',
				'settingsEmailStyleActive' => 'active',
				'maxFileSize' => $maxFileSize
		];

		$data['campaigns'] = $this->user->getActiveCampaigns();

		return View::make('site/email/mass/create', $data);
	}

	public function massEmailStore(\Illuminate\Http\Request $request) {
		if($request->ajax()) {
			if(!$request->has('btnType')) {
				return ['status' => 'error', 'message' => 'There is some error. Check your csv file and try again!'];
			}

			ini_set('max_execution_time', 0);

			$rules = [
				'from_name' => 'required',
				'from_email' => 'email',
				'reply_to' => 'required|email',
				'datasetType' => 'required|in:campaign,custom',
				'name' => 'required',
				'subject' => 'required',
				'content' => 'required'
			];

			if($request->input('datasetType') == 'campaign') {
				$rules['campaign'] = 'required|exists:campaigns,id';
			}

			$this->validate($request, $rules);

			//validate html
			//libxml_use_internal_errors(true);
			try {
				$dom = new \DOMDocument();
				$dom->loadHTML($request->input('content'));
			}
			catch(Exception $e) {
				return [
					'status' => 'fail', 
					'message' => 'Malformed HTML, please fix it first: ' . $e->getMessage(),
					'type' => 'exception',
				];
			}

			try {
				DB::beginTransaction();

				$massEmailTemplate = new MassEmailTemplate();
				$massEmailTemplate->user_id = $this->user->id;
				$massEmailTemplate->from_name = $request->input('from_name');
				$massEmailTemplate->from_email = $request->input('from_email', '');
				$massEmailTemplate->reply_to = $request->input('reply_to');
				$massEmailTemplate->name = $request->input('name');
				$massEmailTemplate->subject = $request->input('subject');
				$massEmailTemplate->content = $request->input('content');
				$massEmailTemplate->type = $request->input('datasetType');

				if ($request->input('datasetType') == 'campaign') {
					$massEmailTemplate->campaign_id = $request->input('campaign');
				}
				else {
					$mapping = collect(json_decode($request->input('mapping'), true))
							->flip()
							->all();
					$csvFile = Session::get('leadCsvFile');
					$reader = Reader::createFromPath($csvFile);
					$leadData = [];
					$header = [];

					foreach ($reader as $index => $row) {
						if ($index == 0) {
							for ($i = 0; $i < sizeof($row); $i++) {
								if (isset($mapping[ $row[ $i ] ])) {
									$header[ $i ] = $mapping[ $row[ $i ] ];
								}
							}
						}
						else {
							$lead = [];
							for ($i = 0; $i < sizeof($row); $i++) {
								if (isset($header[ $i ])) {
									$lead[ $header[ $i ] ] = $row[ $i ];
								}
							}

							$leadData[] = $lead;
						}
					}

					$massEmailTemplate->leads = json_encode($leadData);
				}

				if ($request->has('filter') && $request->input('filter') != 'undefined') {
					$massEmailTemplate->filter = Campaign::fixMassEmailsFilter($request->input('filter'));
				}

				$btnType = $request->input('btnType');
				if ($btnType == 'send') {
					$massEmailTemplate->status = 'Sent';
					$message = 'Email sending started';
				}
				elseif ($btnType == 'draft') {
					$massEmailTemplate->status = 'Save as draft';
					$message = 'Email template saved.';
				}
				elseif ($btnType == 'schedule') {
					$massEmailTemplate->status = 'Scheduled';
					$massEmailTemplate->schedule_time = Carbon::createFromFormat('d M Y - H:i', $request->input('scheduleTime'))
					                                          ->format('Y-m-d H:i:00');
					$message = 'Email template has been scheduled';
				}

				// get mass mail server setting and settings
				$settings = $this->user->getTeamServerSetting();

				if($settings && $settings->status != 'Disable' && ($settings->host != '' || $settings->provider == 'mandrill')) {
					if(in_array($settings->provider, ['sparkpost', 'mandrill'])) {
						$massEmailTemplate->mail_setting_type = $settings->provider;
					} else {
						$massEmailTemplate->mail_setting_type = 'User';
					}

					$mailSetting = [
						'from_mail' => $settings->from_mail,
						'host' => $settings->host,
						'port' => $settings->port,
						'username' => $settings->username,
						'password' => $settings->password,
						'security' => $settings->security,
					];

					$massEmailTemplate->mail_settings = json_encode($mailSetting);
				}
				else {
					return [
						'status' => 'fail', 
						'message' => 'Please add a Mass Email Server to your account',
						'type' => 'exception',
					];
				}

				$massEmailTemplate->save();

				if ($btnType == 'send' || $btnType == 'schedule') {
					// Create a new table add insert all emails with pending status
					$this->insertMassEmailsToSend($massEmailTemplate, $btnType);
				}

				$templateType = $request->input('templateType');
				$filesToBeRemoved = json_decode(Input::get('fileDeleted'));

				if($templateType == 'duplicate') {
					$templateID = $request->input('templateID');
					$oldTemplate = MassEmailTemplate::find($templateID);

					$oldAttachments = $oldTemplate->getEmailAttachment();
					foreach($oldAttachments as $oldAttachment) {
						if(!in_array($oldAttachment, $filesToBeRemoved)) {
							if(!File::isDirectory(storage_path().'/attachments/massmail/'.$massEmailTemplate->id)) {
								File::makeDirectory(storage_path().'/attachments/massmail/'.$massEmailTemplate->id);
							}
							File::copy(storage_path().'/attachments/massmail/'.$oldTemplate->id.'/'.$oldAttachment, storage_path().'/attachments/massmail/'.$massEmailTemplate->id . '/' .$oldAttachment);
						}
					}
				}

				//check for attachments
				if (Input::hasFile('file')) {
					foreach (Input::file('file') as $file) {
						$fileName = $file->getClientOriginalName();
						$extension = $file->getClientOriginalExtension();

						if (($extension != 'php' && $extension != 'js' && $extension != 'exe') &&
								!in_array($fileName, $filesToBeRemoved)
						) {
							$file->move(storage_path() . '/attachments/massmail/' . $massEmailTemplate->id .
									'/', $fileName);

							$massEmailTemplate->addEmailAttachment($fileName);
						}
						//todo: file size limit
					}
				}
			}
			catch (\Exception $e) {
				DB::rollBack();
				return ['status' => 'fail', 'message' => $e->getMessage(), 'type' => 'exception'];
			}
			DB::commit();

			Session::flash('successMessage', $message);
			Session::flash('successMessageClass', 'success');

			return ['status' => 'success', 'message' => $message, 'action' => 'redirect', 'url' => \URL::route('user.email.mass')];
		}

		return ['status' => 'error', 'message' => 'Unauthorised request'];
	}

	private function getAdvanceFilterForm($request, $campaign, $presetFilter = null) {
		$filterText ='';
		if($campaign->status == 'Started') {
			$templatesForSameCampaign = MassEmailTemplate::where('campaign_id', '=', $campaign->id)
				->where('type', '=', 'campaign')
				->where(function($query) {
					$query->where('status', '=', 'sent')
					->orWhere(function($query) {
						$query->where('status', '=', 'Scheduled')
							->where('email_sent', '>', 0);
					});
				})
				->get()
				;

			$campaignFormFields = FormFields::getFieldsForCampaign($campaign->id);

			$filterData = [];
			if(!is_null($presetFilter)) {
				$filter = json_decode($presetFilter, true);
				if($filter != '' && $filter != null && !empty($filter)) {
					$filterData = $filter;
				}
			} elseif($request->has('templateID') && $request->input('templateID') != 'undefined') {
				$template = MassEmailTemplate::find($request->input('templateID'));

				$filter = json_decode($template->filter, true);
				if($filter != '' && $filter != null && !empty($filter)) {
					$filterData = $filter;
				}
			}

			$viewData = [
				'campaignFormFields' => $campaignFormFields, 
				'filter' => $filterData,
				'templatesForSameCampaign' => $templatesForSameCampaign,
			];
			$filterText = View::make('site.email.mass.advanceFilter', $viewData)->render();
		}
		return $filterText;
	}

	function massDetectEmailAndAdvanceFilter(\Illuminate\Http\Request $request) {
		$campaignID = $request->input('campaign');
		$campaign = Campaign::find($campaignID);
		if(!$campaign) {
			return ["status"  => "fail", "message" => "Campaign not found"];
		}
		$this->authorize('can-see-campaign', $campaign);


		$leads = Campaign::join('leads', 'leads.campaignID', '=', 'campaigns.id')
				->join('leadcustomdata', 'leadcustomdata.leadID', '=', 'leads.id')
				->where('leadcustomdata.fieldName', 'Email')
				->where('leadcustomdata.value', '!=', '')
				->where('campaigns.id', $campaignID)
				->select('campaigns.*', 'leadcustomdata.value')
				;
		$emailText = $leads->count().' emails detected';

		
		$allFormFields = FormFields::getFieldNamesForCampaigns([$campaignID]);
		sort($allFormFields);

		$filterText = $this->getAdvanceFilterForm($request, $campaign);
		return [
			'status' => 'success', 
			'formFields' => $allFormFields,
			'emailText' => $emailText, 
			'filterText' => $filterText,
		];
	}

	function massCampaignEmailWithFilter(\Illuminate\Http\Request $request)
	{
		if($request->ajax()) {
			$campaignID = $request->input('campaign');
			$filter = $request->input('filter');
			$filter = Campaign::fixMassEmailsFilter($filter);

			$campaign = Campaign::find($campaignID);
			$emailCount = $campaign->countLeadEmailsWithFilter($filter, $this->user);
			
			$filterText = $this->getAdvanceFilterForm($request, $campaign, $filter);

			return [
				'status' => 'success', 
				'emailText' => $emailCount . ' emails detected',
				'filterText' => $filterText,
			];
		}

		return ['status' => 'fail', 'message' => 'Unauthorised Request'];
	}

	function uploadCsvFile(\Illuminate\Http\Request $request) {
		if($request->ajax()) {
			if($request->hasFile('csvFile')) {
				$file = $request->file('csvFile');
				$extension = $file->getClientOriginalExtension();

				$newFileName = md5(time()).'.'.$extension;
				$file->move(storage_path().'/massmail/lead/', $newFileName);

				$csvFile = storage_path().'/massmail/lead/'.$newFileName;
				Session::put('leadCsvFile', $csvFile);

				$reader = Reader::createFromPath($csvFile);
				$data = $reader->fetchOne();
				return view('site.email.mass.mapping', ['fields' => $data]);
			}
		}
	}

	function massCsvFileEmails(\Illuminate\Http\Request $request) {
		if($request->ajax()) {
			$mapping = json_decode($request->input('mapping'), true);
			$csvFile = Session::get('leadCsvFile');

			$emailField = $mapping['email'];
			$reader = Reader::createFromPath($csvFile);
			$count = 0;

			foreach($reader->fetchAssoc([$emailField]) as $email) {
				if($email != '' && $email != null) {
					$count++;
				}
			}
			return ['status' => 'success', 'emailText' => ($count - 1). ' emails detected'];
		}
		return ['status' => 'fail', 'emailText' => 'Unauthorised request'];
	}

	function massEmailShow($id) {
		$email = MassEmailTemplate::find($id);

		return view('site.email.mass.show', ['email' => $email]);
	}

	function massEmailDelete($id) {
		if(Request::ajax()) {
			$template = MassEmailTemplate::find($id);

			if($template) {
				$template->deleteEmailAttachment();
				$template->delete();
			}
			return ['status' => 'success'];
		}
		return ['status' => 'fail', 'message' => 'Unauthorised Request'];
	}

	function massEmailServerSetting(\Illuminate\Http\Request $request) {
		if($request->ajax()) {
			$user = $this->user;

			$sendingProvider = $request->input('sendingProvider');

			$rules = [
				'sendingProvider'  => 'required|in:smtp,sparkpost,mandrill',
				'password' => 'required',
				'from_mail' => 'required|email'
			];

			if($sendingProvider == 'smtp') {
				$rules = array_merge($rules, [
					'host' => 'required',
					'port' => 'required',
					'username' => 'required',
					'security'  => 'required|in:No,tls,ssl',
				]);
			}

			$this->validate($request, $rules);

			$host = '';
			$port = '';
			$username = '';
			$security = '';

			switch($sendingProvider) {
				case 'sparkpost':
					$host = 'smtp.sparkpostmail.com';
					$port = '587';
					$username = 'SMTP_Injection';
					$security = 'tls';
					break;
				case 'mandrill':
					$host = '';
					$port = '';
					$username = '';
					$security = 'No';
					break;
				case 'smtp':
				default:
					$host = $request->host;
					$port = $request->port;
					$username = $request->username;
					$security = $request->security;
					break;
			}

			if(in_array($sendingProvider, ['smtp', 'sparkpost'])) {
				Config::set("mail.driver", 'smtp');
				Config::set("mail.from.address", $request->from_mail);
				Config::set("mail.from.name", '');
				Config::set("mail.host", $host);
				Config::set("mail.port", $port);
				Config::set("mail.username", $username);
				Config::set("mail.password", $request->password);
			} else {
				Config::set("mail.driver", 'mandrill');
				Config::set("mail.from.address", $request->from_mail);
				Config::set("mail.from.name", '');
				Config::set("services.mandrill.secret", $request->password);
			}

			Config::set("mail.encryption", $security != 'No' ? $security : null);

			$user = $this->user;

			try {
				$emailInfo = [
					'from'      => $request->from_mail,
					'fromName'  => 'Sanityos',
					'replyTo'   => $request->from_mail,
					'subject'   => "Testing Email",
					'to'        => $request->from_mail,
					'bccEmail' => $user->bccEmail
				];

				$attachments = [];
				CommonController::reloadMailConfig();
				CommonController::prepareAndSendEmail($emailInfo, "This is a Testing Email.", [], $attachments, true);
			} catch (Exception $e) {
				$data = [];
				$data['status'] = 'fail';
				$data['message'] = "Unable to send email. Please Check following details <br>". $e->getMessage();
				return $data;
			}

			$server = $user->getMassMailServerSetting();
			$update = [ 'host'      => $host, 
						'port' => $port,
						'username'  => $username, 
						'password' => $request->input('password'),
						'from_mail' => $request->input('from_mail'), 
						'updated_at' => Carbon::now(),
						'provider' => $sendingProvider,
						'security' => $security,
						'status' => 'Enable',
				   ];

			if(!$server) {
				// Add server setting
				$update['user_id'] = $user->id;
				$update['created_at'] = Carbon::now();
				$user->createMassMailServerSetting($update);
			}
			else {
				$user->updateMassMailServerSetting($update);
			}

			// If any postpone email then set it to pending
			$user->setPendingFromPostponeMassEmail();

			Session::flash('successMessage', 'Mail server settings saved successfully. Please check mail for test message');

			return ['status' => 'success', 'message' => 'Mail server settings saved successfully', 'action' =>'redirect', 'url' => \URL::route('user.email.mass')];
		}

		return ['status' => 'fail', 'message' => 'Unauthorised Request'];
	}

	function massEmailSendTestMail(\Illuminate\Http\Request $request) {
		if($request->ajax()) {
			$fromName = $request->input('from_name');
			$fromEmail = $request->input('from_email');
			$replyTo = $request->input('reply_to');
			$subject = $request->input('subject');
			$content = $request->input('content');
			$emails = explode(',', $request->input('emails'));

			//$mailSettings = CommonController::loadSMTPSettings("outbound");
			$serverSetting = $this->user->getTeamServerSetting();

			if($serverSetting && $serverSetting->status == 'Enable') {
				$host = $serverSetting->host;
				$port = $serverSetting->port;
				$username = $serverSetting->username;
				$password = $serverSetting->password;
				if($fromEmail == '') {
					$fromEmail = $serverSetting->from_mail;
				}

				if(in_array($serverSetting->provider, ['smtp', 'sparkpost'])) {
					// Load config
					Config::set("mail.driver", 'smtp');
					Config::set("mail.from.address", $fromEmail);
					Config::set("mail.from.name", $fromName);
					Config::set("mail.host", $host);
					Config::set("mail.port", $port);
					Config::set("mail.username", $username);
					Config::set("mail.password", $password);

					Config::set("mail.encryption", $serverSetting->security != 'No' ? $serverSetting->security : null);
				} else if ($serverSetting->provider == 'mandrill') {
					Config::set("mail.driver", 'mandrill');
					Config::set("mail.from.address", $fromEmail);
					Config::set("mail.from.name", $fromName);
					Config::set("services.mandrill.secret", $password);
					Config::set("mail.encryption", null);
				}
			} else {
				return [
					'status' => 'error', 
					'message' => 'Please add a Mass Email Server to your account',
					'isError' => true, 
					'emailMessage' => [[
						'email' => '',
						'message' => 'Please add a Mass Email Server to your account',
					]],
				];
			}
			CommonController::reloadMailConfig();

			$generalEmailInfo = [
					'from' => $fromEmail,
					'fromName' => $fromName,
					'replyTo' => $replyTo
			];

			$emailInfo = $generalEmailInfo;
			$emailInfo['subject'] = $subject;

			\Config::set("mail.from.name", $fromName);

			//libxml_use_internal_errors(true);
			try {
				$dom = new \DOMDocument();
				$dom->loadHTML($content);

				foreach ($dom->getElementsByTagName('a') as $link) {
					$href = trim($link->getAttribute('href'));
					$modifiedLink = \Config::get('app.url') . '/email/redirect/0?u=' . urlencode($href);

					$link->setAttribute('href', $modifiedLink);
				}

				$content = $dom->saveHTML();
			}
			catch(Exception $e) {
				return [
					'status' => 'error', 
					'message' => 'Malformed HTML, please fix it first: ' . $e->getMessage(),
					'isError' => true, 
					'emailMessage' => [[
						'email' => '',
						'message' => 'Malformed HTML, please fix it first: ' . $e->getMessage(),
					]],
				];
			}

			$trackinglink = \Config::get('app.url') . '/email/open/0.png';
			$unsubscribeLink = \Config::get('app.url') . '/email/unsubscribe/0';
			$renderVars = [
				'emailText' => $content, 
				'link' => $trackinglink, 
				'unsubscribeLink' => $unsubscribeLink,
				'showSanityOsSignature' => false,
			];
			$content = View::make('emails.massemail', $renderVars)->render();

			$isError = false;
			$emailMessage = [];

			foreach($emails as $email) {
				$emailInfo['to'] = $email;
				$emailResponse = CommonController::prepareAndSendEmail($emailInfo, $content, [], []);

				if($emailResponse['status'] == 'fail') {
					$emailMessage[] = ['email' => $email, 'message' => 'Test email not sent'];
					$isError = true;
				}
				else {
					$emailMessage[] = ['email' => $email, 'message' => 'Test email sent'];
				}
			}

			return ['status' => 'success', 'message' => 'Test email sent', 'isError' => $isError, 'emailMessage' => $emailMessage];
		}

		return ['status' => 'error', 'message' => 'Unauthorised request'];
	}

	public function insertMassEmailsToSend($template, $btnType) {
		$leadType = $template->type;

		if($leadType == 'campaign') {
			$campaignID = $template->campaign_id;

			$campaign = Campaign::find($campaignID);

			$filter = $template->filter;
			$leads = $campaign->detectLeadEmailsWithFilter($filter, $this->user);

			foreach($leads as $lead) {
				$validator = \Validator::make(['email' => $lead->value], [
					'email' => 'required|email|massemailunique:'.$template->id
				]);

				if($validator->fails()) {
					continue;
				}
				else {
					$lead->id = $lead->leadID;
					$leadData = $lead->getCustomData();

					$leadInfo = $leadData->lists('value', 'fieldName')->all();

					if(empty($leadInfo)) {
						$leadInfo = null;
						$jsonLeadInfo = '';
					}
					else {
						$jsonLeadInfo = json_encode($leadInfo);
					}

					$massEmail = new MassEmail();
					$massEmail->email = $lead->value;
					$massEmail->status = 'Pending';

					if($btnType == 'send') {
						$massEmail->sent = 'Now';
					}
					elseif($btnType == 'schedule') {
						$massEmail->sent = 'Later';
						$massEmail->after_time = $template->schedule_time;
					}
					$massEmail->user_id = $this->user->id;
					$massEmail->template_id = $template->id;
					$massEmail->lead_id = $lead->id;
					$massEmail->lead_data = $jsonLeadInfo;

					if(isset($leadInfo['First Name']))
						$massEmail->first_name = $leadInfo['First Name'];

					if(isset($leadInfo['Last Name']))
						$massEmail->last_name = $leadInfo['Last Name'];

					if(isset($leadInfo['Company Name']))
						$massEmail->company_name = $leadInfo['Company Name'];
					$massEmail->save();
				}
			}
		}
		elseif($leadType == 'custom') {
			$leads = $template->leads;
			foreach ($leads as $lead) {
				$validator = \Validator::make($lead, [
						'email' => 'required|email|massemailunique:'.$template->id
				]);

				if($validator->fails()) {
					continue;
				}
				else {
					$massEmail = new MassEmail();

					$massEmail->email = $lead['email'];
					$massEmail->status = 'Pending';

					if($btnType == 'send') {
						$massEmail->sent = 'Now';
					}
					elseif($btnType == 'schedule') {
						$massEmail->sent = 'Later';
						$massEmail->after_time = $template->schedule_time;
					}
					$massEmail->user_id = $this->user->id;
					$massEmail->template_id = $template->id;
					$massEmail->lead_data = json_encode($lead);

					if(isset($lead['first_name']))
						$massEmail->first_name = $lead['first_name'];

					if(isset($lead['last_name']))
						$massEmail->last_name = $lead['last_name'];

					if(isset($lead['company_name']))
						$massEmail->company_name = $lead['company_name'];

					$massEmail->save();
				}
			}
		}
	}

	public function massEmailDuplicate($id)
	{
		$template = MassEmailTemplate::find($id);
		$attachments = $template->getEmailAttachment();

		$maxFileSize = Setting::get('maxFileUploadSize');
		$data = [
			'geoExportsMenuActive' => 'nav-active active',
			'geoExportsStyleActive' => 'display: block',
			'settingsEmailStyleActive' => 'active',
			'maxFileSize' => $maxFileSize,
			'template' => $template,
			'attachments' => $attachments
		];

		$data['campaigns'] = $this->user->getActiveCampaigns();

		return View::make('site/email/mass/duplicate', $data);
	}

	public function massEmailEdit($id) {
		$template = MassEmailTemplate::find($id);

		if(!$template) {
			return \App::abort('404', 'Requested template not found');
		}
		$attachments = $template->getEmailAttachment();

		$maxFileSize = Setting::get('maxFileUploadSize');
		$data = [
				'geoExportsMenuActive' => 'nav-active active',
				'geoExportsStyleActive' => 'display: block',
				'settingsEmailStyleActive' => 'active',
				'maxFileSize' => $maxFileSize,
				'template' => $template,
				'attachments' => $attachments
		];

		$data['campaigns'] = $this->user->getActiveCampaigns();

		if($template->type == 'custom') {
			$leads = $template->leads;
			$data['emailCount'] = count($leads) . ' emails detected';
		}

		return View::make('site/email/mass/edit', $data);
	}

	public function massEmailUpdate(\Illuminate\Http\Request $request, $id) {
		if($request->ajax()) {
			if(!$request->has('btnType')) {
				return ['status' => 'error', 'message' => 'There is some error. Check your csv file and try again!'];
			}

			$rules = [
				'from_name' => 'required',
				'from_email' => 'email',
				'reply_to' => 'required|email',
				'datasetType' => 'required|in:campaign,custom',
				'name' => 'required',
				'subject' => 'required',
				'content' => 'required'
			];

			if($request->input('datasetType') == 'campaign') {
				$rules['campaign'] = 'required|exists:campaigns,id';
			}

			$this->validate($request, $rules);

			//validate html
			//libxml_use_internal_errors(true);
			try {
				$dom = new \DOMDocument();
				$dom->loadHTML($request->input('content'));
			}
			catch(Exception $e) {
				return [
					'status' => 'fail', 
					'message' => 'Malformed HTML, please fix it first: ' . $e->getMessage(),
					'type' => 'exception',
				];
			}

			try {
				DB::beginTransaction();

				$massEmailTemplate = MassEmailTemplate::find($id);

				$massEmailTemplate->from_name = $request->input('from_name');
				$massEmailTemplate->from_email = $request->input('from_email', '');
				$massEmailTemplate->reply_to = $request->input('reply_to');
				$massEmailTemplate->name = $request->input('name');
				$massEmailTemplate->subject = $request->input('subject');
				$massEmailTemplate->content = $request->input('content');
				$massEmailTemplate->type = $request->input('datasetType');

				if ($request->input('datasetType') == 'campaign') {
					$massEmailTemplate->campaign_id = $request->input('campaign');
					$massEmailTemplate->leads = null;

					if ($request->has('filter') && $request->input('filter') != 'undefined') {
						$massEmailTemplate->filter = Campaign::fixMassEmailsFilter($request->input('filter'));
					}
				}
				else {
					$mapping = collect(json_decode($request->input('mapping'), true))
							->flip()
							->all();
					$csvFile = Session::get('leadCsvFile');
					$reader = Reader::createFromPath($csvFile);
					$leadData = [];
					$header = [];

					foreach ($reader as $index => $row) {
						if ($index == 0) {
							for ($i = 0; $i < sizeof($row); $i++) {
								if (isset($mapping[ $row[ $i ] ])) {
									$header[ $i ] = $mapping[ $row[ $i ] ];
								}
							}
						}
						else {
							$lead = [];
							for ($i = 0; $i < sizeof($row); $i++) {
								if (isset($header[ $i ])) {
									$lead[ $header[ $i ] ] = $row[ $i ];
								}
							}

							$leadData[] = $lead;
						}
					}

					$massEmailTemplate->leads = json_encode($leadData);
					$massEmailTemplate->filter = null;
					$massEmailTemplate->campaign_id = null;
				}

				$btnType = $request->input('btnType');
				if ($btnType == 'send') {
					$massEmailTemplate->status = 'Sent';
					$message = 'Email sending started';
				}
				elseif ($btnType == 'draft') {
					$massEmailTemplate->status = 'Save as draft';
					$message = 'Email template updated.';
				}
				elseif ($btnType == 'schedule') {
					$massEmailTemplate->status = 'Scheduled';
					$massEmailTemplate->schedule_time = Carbon::createFromFormat('d M Y - H:i', $request->input('scheduleTime'))
					                                          ->format('Y-m-d H:i:00');
					$message = 'Email template has been scheduled';
				}

				$massEmailTemplate->save();

				if ($btnType == 'send' || $btnType == 'schedule') {
					// Create a new table add insert all emails with pending status
					$this->insertMassEmailsToSend($massEmailTemplate, $btnType);
				}

				$filesToBeRemoved = json_decode(Input::get('fileDeleted'));
				$attachments = $massEmailTemplate->getEmailAttachment();

				if(sizeof($filesToBeRemoved) > 0) {
					foreach($filesToBeRemoved as $data) {
						if(in_array($data, $attachments)) {
							//delete from both physical and memory storage
							DB::table('mass_email_attachments')->where('template_id', $massEmailTemplate->id)->where('file_name', $data)->delete();

							File::delete(storage_path().'/attachments/massmail/'.$massEmailTemplate->id.'/'.$data);
						}
					}
				}

				//check for attachments
				if (Input::hasFile('file')) {
					foreach (Input::file('file') as $file) {
						$fileName = $file->getClientOriginalName();
						$extension = $file->getClientOriginalExtension();

						if (($extension != 'php' && $extension != 'js' && $extension != 'exe') &&
								!in_array($fileName, $filesToBeRemoved)
						) {
							$file->move(storage_path() . '/attachments/massmail/' . $massEmailTemplate->id .
									'/', $fileName);

							$massEmailTemplate->addEmailAttachment($fileName);
						}
						//todo: file size limit
					}
				}
			}
			catch (\Exception $e) {
				DB::rollBack();
				return ['status' => 'fail', 'message' => $e->getMessage(), 'type' => 'exception'];
			}
			DB::commit();

			Session::flash('successMessage', $message);
			Session::flash('successMessageClass', 'success');

			return ['status' => 'success', 'message' => $message, 'action' => 'redirect', 'url' => \URL::route('user.email.mass')];
		}
		return ['status' => 'fail', 'error' => 'Unauthorised Request'];
	}

	public function massPixelTracking($id) {
		$lead = MassEmail::find($id);
		if($lead) {
			$lead->is_email_open = 'Yes';
			$lead->email_open_count = $lead->email_open_count + 1;
			$lead->opened_at = Carbon::now();

			$lead->save();

			$history = $lead->getCallHistory();
			if($history) {
				$lead->updateCallHistoryNotes();
			}
		}

		$response = \Response::make(File::get('assets/images/pix.png'), 200);
		$response->header('Content-Type', 'image/png');

		// We return our image here.
		return $response;
	}

	public function massEmailClick($id) {
		$lead = MassEmail::find($id);
		$lead->is_email_click = 'Yes';
		$lead->email_click_count = $lead->email_click_count + 1;

		$lead->save();

		$history = $lead->getCallHistory();
		if($history) {
			$lead->updateCallHistoryNotes();
		}

		$url = urldecode(Input::get('u'));
		return Redirect::guest($url);
	}

	public function massEmailUnsubscribe(SimpleRequest $request, $id) {

		//some bots ? check urls in email and unsubscribe automatically
		$ua = $request->header('User-Agent');
		$skipUnsubscribe = stripos($ua, 'Gecko/20100101 Firefox/27.0') !== false;
		//$skipUnsubscribe = stripos($ua, 'Chrome/54') !== false;

		if ( $request->has('confirm') ){

			if(!$skipUnsubscribe) {
				//Log::error('!$skipUnsubscribe');
				$lead = MassEmail::find($id);
				if($lead) {
					$template = MassEmailTemplate::find($lead->template_id);

					$lead->setStatus('Unsubscribed');

					if($template->type == 'custom') {
						$user = User::find($template->user_id);
						$lead->customUnsubscribe($user->getManager());
						$message = 'You are successfully unsubscribed from this team of sanity os';
					}
					elseif($template->type == 'campaign') {
						$campaignID = $template->campaign_id;
						$lead->campaignUnsubscribe($campaignID);

						$message = 'You are successfully unsubscribed from this campaign';
					}
				}
			}

		}


		
		return File::get(public_path() . '/public_home/unsubscribe.html');
		//return view('unsubscribe', ['message' => $message]);
	}

	public function massEmailReports($id) {
		$template = MassEmailTemplate::find($id);

		if(!$template) {
			return \App::abort('404', 'Requested template not found');
		}
		
		$this->authorize('can-access-mass-mail-template', $template);

		if(in_array($template->mail_setting_type, ['Superadmin', 'sparkpost', 'mandrill'])) {
			$status = 'aws_status';
		} else {
			$status = 'status';
		}

		$canOpenLeads = true;

		$type = $template->type;
		if($type == 'campaign') {
			$lead = MassEmail::where('template_id', $template->id)->first();
			$lead_data = $lead->lead_data;
			$fields = collect($lead_data)->keys();

			$campaign = Campaign::find($template->campaign_id);
			if(!$campaign) {
				return \App::abort('404', 'Campaign not found');
			}

			$canOpenLeads = $campaign->status != 'Completed';
		} else {
			$fields = [];
		}

		$isDataAvailable = true;


		if($status == 'aws_status') {
			$sent = MassEmail::where('template_id', $template->id)
					->where('aws_status', 'Delivery')->count();
			$bounce = MassEmail::where('template_id', $template->id)
			                 ->where('aws_status', 'Bounce')->count();


		}
		else {
			$sent = MassEmail::where('template_id', $template->id)
			                 ->where('status', 'Sent')->count();
			$bounce = 0;
		}

		$emailOpened = MassEmail::where('template_id', $template->id)
								->where('is_email_open', 'Yes')
								->where('status', '!=', 'Unsubscribed')
								->count();

		$emailClicked = MassEmail::where('template_id', $template->id)
									->where('is_email_click', 'Yes')
									->where('status', '!=', 'Unsubscribed')
									->count();
		
		$emailUnsubscribed = MassEmail::where('template_id', $template->id)
									->where('status', '=', 'Unsubscribed')
									->count();

		$deliveredCount = $sent - $emailOpened;
		if($deliveredCount < 0) {
			$deliveredCount = 0;
		}

		$emailOpenedCount = $emailOpened - $emailClicked;
		if($emailOpenedCount < 0) {
			$emailOpenedCount = 0;
		}

		$pieData = [];
		$pieData[] = ['label' => 'Delivered', 'data'=> $deliveredCount, 'color' => '#D9534F'];
		$pieData[] = ['label' => 'Opens', 'data'=> $emailOpenedCount, 'color' => '#1CAF9A'];
		$pieData[] = ['label' => 'Clicks', 'data'=> $emailClicked, 'color' => '#F0AD4E'];

		if ($bounce > 0) {
			$pieData[] = ['label' => 'Bounce', 'data' => $bounce, 'color' => '#5BC0DE'];
		}

		if($emailUnsubscribed > 0) {
			$pieData[] = ['label' => 'Unsubscribes', 'data' => $emailUnsubscribed, 'color' => '#583f26'];
		}

		/*if(Request::has('filter')) {
			if(Request::get('filter') == "unsubscribe") {
				$status = 'status';
			}
		}*/

		if(Request::ajax()) {
			$fn_processStatusColumn = function($row, $statusColumnName) {
				if($row->opened_at == '-') {
					$statusValue = $row->{$statusColumnName};
					switch($statusValue) {
						case 'Bounce':
							$statusValue = 'Bounced';
							break;
						case 'Delivery':
							$statusValue = 'Delivered';
							break;
					}

					return $statusValue . '</br>' . $row->updated_at; 
				} else {
					return 'Opened</br>' . $row->opened_at; 
				}
			};

			if($type == 'custom') {
				$query = MassEmail::where('template_id', $template->id)
						->select('email', $status, 'updated_at', 'first_name', 'last_name', 'company_name', 'email_click_count', 'email_open_count', 'opened_at', 'status as status_orig');

				return Datatables::of($query)
						->editColumn('email', function($row) {
							return \Html::mailto($row->email);
						})
						->editColumn('email_click_count', function($row) {
							return $row->status_orig == 'Unsubscribed' ? 0 : $row->email_click_count;
						})
						->editColumn($status, function($row) use ($status, $fn_processStatusColumn) {
							if(Request::has('filter')) {
								$filter = Request::get('filter');
								if($filter == "unsubscribe") {
									return 'Unsubscribed';
								}
							}
							return $fn_processStatusColumn($row, $status);
						})
						->filter(function($query) {
							if(Request::has('filter')) {
								$filter = Request::get('filter');
								if($filter == 'bounce') {
									$query->where('aws_status', 'Bounce');
								} elseif($filter == 'open') {
									$query->where('is_email_open', 'Yes')
										->where('status', '!=', 'Unsubscribed')
										;
								} elseif($filter == 'click') {
									$query->where('is_email_click', 'Yes')
										->where('status', '!=', 'Unsubscribed')
										;
								} elseif($filter == "unsubscribe") {
									$query->where('status', 'Unsubscribed');
								}
							}
						})
						->make(true);
			} elseif ($type == 'campaign') {
				$query = MassEmail::where('template_id', $template->id)
						->select('email', $status, 'updated_at', 'first_name', 'last_name', 'email_click_count', 'email_open_count', 'opened_at', 'lead_data', 'id', 'status as status_orig');

				$tableQuery = Datatables::of($query)
						->editColumn('email', function($row) {
							return \Html::mailto($row->email);
						})
						->editColumn('email_click_count', function($row) {
							return $row->status_orig == 'Unsubscribed' ? 0 : $row->email_click_count;
						})
						->editColumn($status, function($row) use ($status, $fn_processStatusColumn) {
							if(Request::has('filter')) {
								$filter = Request::get('filter');
								if($filter == "unsubscribe") {
									return 'Unsubscribed';
								}
							}
							return $fn_processStatusColumn($row, $status);
						});

				foreach ($fields as $field) {
					$tableQuery = $tableQuery->addColumn($field, function($row) use($field) {
						$leadData = $row->lead_data;
						if(isset($leadData[$field])) {
							return $leadData[$field];
						} else {
							return '-';
						}
					});
				}

				$tableQuery = $tableQuery->filter(function($query) {
					if(Request::has('filter')) {
						$filter = Request::get('filter');
						if($filter == 'bounce') {
							$query->where('aws_status', 'Bounce');
						} elseif($filter == 'open') {
							$query->where('is_email_open', 'Yes')
								->where('status', '!=', 'Unsubscribed')
								;
						} elseif($filter == 'click') {
							$query->where('is_email_click', 'Yes')
								->where('status', '!=', 'Unsubscribed');
						} elseif($filter == "unsubscribe") {
							$query->where('status', 'Unsubscribed');
						}
					}
				});

				if($canOpenLeads) {
					$tableQuery = $tableQuery->addColumn('action', function($row) {
						return '<a href="'.\URL::route('user.email.mass.report.openLead', ['id' => $row->id]).'" target="_blank" class="btn btn-primary" title="Open Lead">Open Lead</a>';
					});
				} else {
					$tableQuery = $tableQuery->addColumn('action', '');
				}

				return $tableQuery->make(true);
			}
		}

		if($sent <= 0 && $bounce <= 0 && $emailClicked <= 0 && $emailOpened <= 0) {
			$isDataAvailable = false;
		}

		$data = [
				'geoExportsMenuActive' => 'nav-active active',
				'geoExportsStyleActive' => 'display: block',
				'settingsEmailStyleActive' => 'active',
				'template' => $template,
				'thisUrl' => \URL::current(),
				'statusCol' => $status,
				'fields' => $fields,
				'pieData' => $pieData,
				'isDataAvailable' => $isDataAvailable
		];

		return View::make('site/email/mass/reports', $data);
	}

	public function awsSubscription()
	{
		$post = json_decode(file_get_contents('php://input'), true);

		$type = $post['Type'];
		if($type == 'SubscriptionConfirmation') {
			$topicArn = $post['TopicArn'];
			$subscriptionUrl = $post['SubscribeURL'];

			DB::table('aws_subscription_url')->insert([
				'topic_arn' => $topicArn,
				'url' => $subscriptionUrl
			]);
		}
		elseif($type == 'Notification') {
			$message = $post['Message'];
			$message = json_decode($message, true);
			$notificationType = $message['notificationType'];
			$mail = $message['mail'];
			$timestamp = $mail['timestamp'];
			$emailAddress = $mail['destination'][0];

			$toDate = Carbon::parse($timestamp)->setTimeZone(date_default_timezone_get())->addMinute(2)->format('Y-m-d H:i:s');
			$fromDate = Carbon::parse($timestamp)->setTimeZone(date_default_timezone_get())->subMinute(2)->format('Y-m-d H:i:s');

			$lead = MassEmail::where('email', $emailAddress)
			                 ->where('updated_at', '>=', $fromDate)
			                 ->where('updated_at', '<=', $toDate)
			                 ->first();

			if($lead) {
				$lead->aws_status = $notificationType;
				$lead->save();

				$history = $lead->getCallHistory();
				if($history) {
					$lead->updateCallHistoryNotes();
				}
			}
		}
	}
	
	public function sparkpostWebhook() 
	{
		$post = json_decode(file_get_contents('php://input'), true);
		//Log::error('sparkpost hook : ' . print_r($post, true));
		foreach($post as $event) {
			if(isset($event['msys']) && isset($event['msys']['message_event'])) {
				$data = $event['msys']['message_event'];
				$eventType = '';
				if(isset($data['type'])) {
					$eventType = $data['type'];
				}
				$eventReason = '';
				if(isset($data['reason'])) {
					$eventReason = $data['reason'];
				}
				$emailAddress = '';
				if(isset($data['rcpt_to'])) {
					$emailAddress = $data['rcpt_to'];
				}
				$timestamp = 0;
				if(isset($data['timestamp'])) {
					$timestamp = intval($data['timestamp']);
				}
				//Log::error("sparkpost event, type : {$eventType}, receiver : {$emailAddress}, timestamp : {$timestamp}, reason : {$eventReason} ");
				if(in_array($eventType, 
					[
						'bounce',
						'delivery',
						'injection',
						'spam_complaint',
						'out_of_band',
						'policy_rejection',
						'delay',
					]) && $emailAddress != '') 
				{
					Log::error("sparkpost event, type : {$eventType}, receiver : {$emailAddress}, timestamp : {$timestamp}, reason : {$eventReason} ");
					//Log::error("sparkpost event, date : " . date("c", $timestamp));
					$toDate = Carbon::parse(date("c", $timestamp))->setTimeZone(date_default_timezone_get())->addMinute(3)->format('Y-m-d H:i:s');
					$fromDate = Carbon::parse(date("c", $timestamp))->setTimeZone(date_default_timezone_get())->subMinute(3)->format('Y-m-d H:i:s');

					$lead = MassEmail::where('email', $emailAddress)
									 ->where('updated_at', '>=', $fromDate)
									 ->where('updated_at', '<=', $toDate)
									 ->first();

					if($lead) {
						$notificationType = 'Delivery';
						if(in_array($eventType, ['bounce', 'out_of_band', 'policy_rejection', 'delay'])) {
							$notificationType = 'Bounce';
						} else if(in_array($eventType, ['spam_complaint'])) {
							$notificationType = 'Complaint';
						}
						$lead->aws_status = $notificationType;
						$lead->save();

						$history = $lead->getCallHistory();
						if($history) {
							$lead->updateCallHistoryNotes();
						}
					}
				}
			}
		}
	}

	public function mandrillWebhook(\Illuminate\Http\Request $request) 
	{
		if($request->has('mandrill_events') && $request->input('mandrill_events') != '') {
			$post = json_decode($request->input('mandrill_events'), true);
			//Log::error('mandrill hook : ' . print_r($post, true));
			foreach($post as $event) {
				if(isset($event['event']) && isset($event['msg'])) {
					$data = $event['msg'];
					$eventType = $event['event'];

					$eventReason = '';
					if(isset($data['diag'])) {
						$eventReason = $data['diag'];
					}
					$emailAddress = '';
					if(isset($data['email'])) {
						$emailAddress = $data['email'];
					}
					$timestamp = 0;
					//$data['ts'] is also available
					if(isset($event['ts'])) {
						$timestamp = intval($event['ts']);
					}
					//Log::error("mandrill event, type : {$eventType}, receiver : {$emailAddress}, timestamp : {$timestamp}, reason : {$eventReason} ");
					if(in_array($eventType, 
						[
							'send',
							'deferral',
							'hard_bounce',
							'soft_bounce',
							'spam',
							'reject',
						]) && $emailAddress != '') 
					{
						Log::error("mandrill event, type : {$eventType}, receiver : {$emailAddress}, timestamp : {$timestamp}, reason : {$eventReason} ");
						//Log::error("sparkpost event, date : " . date("c", $timestamp));
						$toDate = Carbon::parse(date("c", $timestamp))->setTimeZone(date_default_timezone_get())->addMinute(3)->format('Y-m-d H:i:s');
						$fromDate = Carbon::parse(date("c", $timestamp))->setTimeZone(date_default_timezone_get())->subMinute(3)->format('Y-m-d H:i:s');

						$lead = MassEmail::where('email', $emailAddress)
										 ->where('updated_at', '>=', $fromDate)
										 ->where('updated_at', '<=', $toDate)
										 ->first();

						if($lead) {
							$notificationType = 'Delivery';
							if(in_array($eventType, ['hard_bounce', 'soft_bounce', 'deferral', 'reject'])) {
								$notificationType = 'Bounce';
							} else if(in_array($eventType, ['spam'])) {
								$notificationType = 'Complaint';
							}
							$lead->aws_status = $notificationType;
							$lead->save();

							$history = $lead->getCallHistory();
							if($history) {
								$lead->updateCallHistoryNotes();
							}
						}
					}
				}
			}

		}
	}

	public function massEmailOpenLead($id) {
		$massEmail = MassEmail::find($id);

		if(!$massEmail) {
			return \App::abort('404', 'Requested lead not found');
		}

		$template = MassEmailTemplate::find($massEmail->template_id);
		if($template->type == 'campaign') {

			$lead = $massEmail->getLeadFromEmail($template->campaign_id);
			Session::put('referFrom', 'massmail');
			return Redirect::route('user.leads.createlead', [$lead->id]);
		} else {
			return \App::abort('404', 'Requested lead not found');
		}
	}
}
