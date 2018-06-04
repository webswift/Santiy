<?php

namespace App\Providers;

use App\Models\MassEmail;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::created(function ($user) {
            if($user->userType == 'Multi') {
                $multiUserSetting = Setting::get('multiUserLimit');
                $emails = $multiUserSetting['emails'];

                $user->createEmailCredit($emails);
            }
            elseif($user->userType == 'Single') {
                $singleUserSetting = Setting::get('singleUserLimit');
                $emails = $singleUserSetting['emails'];

                $user->createEmailCredit($emails);
            }
        });

        \Validator::extend('massemailunique', function($attribute, $value, $parameters, $validator) {
            $massEmail = MassEmail::where('template_id', $parameters[0])->where('email', $value)->first();
            if($massEmail) {
                return false;
            }
            else {
                return true;
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*Blade::setRawTags('{{', '}}');
        Blade::setContentTags('{{{', '}}}');
        Blade::setEscapedContentTags('{{{', '}}}');*/
    }
}
