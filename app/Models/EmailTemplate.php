<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model {

	public $timestamps = false;
	protected $table = 'emailtemplates';
	protected $fillable = ['id', 'name', 'templateText', 'subject', 'creator', 'timeCreated'];


	function addEmailAttachment($fileName){
		\DB::table('email_attachment')
			->insert(['templateID' => $this->id, 'fileName' => $fileName]);
	}

	function deleteEmailAttachments(){
		//delete attachments
		\DB::table('email_attachment')->where('templateID', $this->id)->delete();

		//delete physical attachments
		\File::deleteDirectory(storage_path().'/attachments/email/'.$this->id.'/');
	}

	function getEmailAttachment(){
		return \DB::table('email_attachment')
			->where('templateID', $this->id)
			->lists('fileName');
	}

	function getEmailAttachmentsToBeSend(){
		return \DB::table('email_attachment')
			->select(\DB::raw('CONCAT(\'/attachments/email/\', templateID, \'/\', fileName) AS files'))
			->where('templateID', $this->id)
			->lists('files');
	}
}