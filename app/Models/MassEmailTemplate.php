<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MassEmailTemplate extends Model
{
	public function getLeadsAttribute($value)
	{
		return json_decode($value, true);
	}

    public function addEmailAttachment($fileName) {
	    \DB::table('mass_email_attachments')->insert([
		    'template_id' => $this->id,
		    'file_name' => $fileName,
		    'created_at' => Carbon::now(),
		    'updated_at' => Carbon::now()
	    ]);
    }

	public function getEmailAttachment() {
		return \DB::table('mass_email_attachments')->where('template_id' , $this->id)->lists('file_name');
	}

	public function deleteEmailAttachment() {
		$directoryPath = storage_path('attachments/massmail/'.$this->id);
		if(\File::exists($directoryPath)) {
			\File::deleteDirectory($directoryPath);
		}

		\DB::table('mass_email_attachments')->where('template_id' , $this->id)->delete();
	}

	function getEmailAttachmentsToBeSend(){
		return \DB::table('mass_email_attachments')
				->select(\DB::raw('CONCAT(\'/attachments/massmail/\', template_id, \'/\', file_name) AS files'))
		          ->where('template_id', $this->id)
		          ->lists('files');
	}

	function addEmailCredit() {
		\DB::table('mass_email_templates')->whereId($this->id)->increment('email_sent');
		//$this->email_sent = $this->email_sent + 1;
		$this->save();
	}
}
