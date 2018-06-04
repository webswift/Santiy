<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Campaign;
use App\Models\MassEmailTemplate;
use App\Models\CampaignMember;
use App\Models\User;
use App\Models\Form;
use App\Models\SalesMember;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        parent::registerPolicies($gate);

        $gate->define('mass-email', function($user) {
            return $user->get()->userType != 'Team';
        });
		
		$gate->define('can-access-mass-mail-template', function($user, $template) {
			$user = $user->get();
			$result = $user->id == $template->user_id; 
			if(!$result && $template->type == 'campaign') {
				$result = CampaignMember::where('userID', '=', $user->id)
					->where('campaignID', '=', $template->campaign_id)
					->exists();
			}
			return $result;
        });
		
		$gate->define('can-see-campaign', function($user, $campaign) {
			$user = $user->get();
			$teamIds = $user->getAccessibleTeamMembersIds();
			return CampaignMember::whereIn('userID', $teamIds)
				->where('campaignID', '=', $campaign->id)
				->exists();
        });
		
		$gate->define('can-see-form', function($user, $form) {
			$user = $user->get();
			return $form->creator == $user->id  || //I
				$form->creator == $user->getManager()  || //my manager
				User::where('manager', $user->getManager()) //or team member
				->where('id', '=', $form->creator)
				->exists();
        });

		$gate->define('is-my-team-member', function($user, $userFromTeam) {
			$user = $user->get();
			return $userFromTeam->id == $user->id  || //I
				$userFromTeam->id == $user->getManager()  || //my manager
				User::where('manager', $user->getManager()) //or team member
				->where('id', '=', $userFromTeam->id)
				->exists();
        });

		$gate->define('is-my-team-salesman', function($user, $salesMan) {
			$user = $user->get();
			return 
				$salesMan->manager == $user->getManager()  || //my manager
				SalesMember::where('manager', $user->getManager()) //or team member
				->where('id', '=', $salesMan->id)
				->exists();
        });
    }
}
