<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use Config;
use App\Http\Controllers\CommonController;

use Closure;
use SuperClosure\Serializer;
use Illuminate\Support\Str;
use Illuminate\Contracts\Mail\Mailer;
use Log;
use Exception;

/*send custom email with customized mail settings etc, based on the 
 * https://github.com/laravel/framework/blob/5.3/src/Illuminate/Mail/Jobs/HandleQueuedMessage.php
 */
class SendCustomEmail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * The name of the email view.
     *
     * @var string
     */
    public $view;

    /**
     * The data to be passed to the view.
     *
     * @param  array  $data
     */
    public $data;

    /**
     * The message configuration callback.
     *
     * @var \Closure
     */
    public $callback;


	public $finallyCallback;

	/*save config settings */
	public $mailConfig;

    /**
     * Create a new job instance.
     *
     * @param  string  $view
     * @param  array  $data
     * @param  \Closure  $callback
     * @return void
     */
    public function __construct($view, $data, $callback, $finallyCallback)
    {
        $this->view = $view;
        $this->data = $data;
        $this->callback = $callback;
        $this->finallyCallback = $finallyCallback;
		$this->mailConfig = Config::get("mail");
		//Log::error("mail config save : " . print_r($this->mailConfig, true));
    }

    /**
     * Handle the queued job.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
		try {
			//Log::error("mail config loaded : " . print_r($this->mailConfig, true));
			Config::set("mail", $this->mailConfig);
			CommonController::reloadMailConfig();
			$mailer->send($this->view, $this->data, $this->callback);
			/*
			Log::error("sending info mail: ". 
				"\nmail config: ".print_r($this->mailConfig, true) .
				"\nData: ".print_r($this->data, true) 
			);
			*/
		} catch(Exception $e) {
			Log::error("Error sending info mail: " . $e->getMessage() . 
				"\nmail config: ".print_r($this->mailConfig, true) .
				"\nData: ".print_r($this->data, true) 
			);
		} finally {
			if($finallyCallback = $this->finallyCallback) {
				$finallyCallback();
			}
		}
    }

    /**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        foreach ($this->data as $key => $value) {
            $this->data[$key] = $this->getSerializedPropertyValue($value);
        }

        if ($this->callback instanceof Closure) {
            $this->callback = (new Serializer)->serialize($this->callback);
        }
		
		if ($this->finallyCallback instanceof Closure) {
            $this->finallyCallback = (new Serializer)->serialize($this->finallyCallback);
        }

        return array_keys(get_object_vars($this));
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __wakeup()
    {
        foreach ($this->data as $key => $value) {
            $this->data[$key] = $this->getRestoredPropertyValue($value);
        }

        if (Str::contains($this->callback, 'SerializableClosure')) {
            $this->callback = (new Serializer)->unserialize($this->callback);
        }

        if (Str::contains($this->finallyCallback, 'SerializableClosure')) {
            $this->finallyCallback = (new Serializer)->unserialize($this->finallyCallback);
        }
    }
}
