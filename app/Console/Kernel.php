<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SendEmails::class,
        \App\Console\Commands\ClearMassEmailsLocks::class,
		\App\Console\Commands\CronStatsUpdate::class,
		\App\Console\Commands\CronLicenseCheck::class,
		\App\Console\Commands\CronUpdateExchangeRate::class,
		\App\Console\Commands\CronUpdateEmailCredits::class,
		\App\Console\Commands\CronUpdatePaypalTransactions::class,
		\App\Console\Commands\CronFollowupsReminder::class,
		\App\Console\Commands\UtilityRecalculateFilteredLeads::class,
		\App\Console\Commands\UtilityRecalculateTotalLeads::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('massmail:clear-locks')
				->everyMinute()
				->withoutOverlapping()
				->appendOutputTo('storage/logs/massmail-clear-locks.log')
				;
		$schedule->command('backup:clean')->daily()->at('01:00');
		$schedule->command('backup:run')->daily()->at('02:00');
		$schedule->command('cron:statsupdate')->daily();
		$schedule->command('cron:licensecheck')->dailyAt('23:20');
		$schedule->command('cron:exchangerate')->daily();
		$schedule->command('cron:update-emails-credits')->daily();
		$schedule->command('cron:update-paypal-transactions')->cron('10 * * * *'); //every hour
		$schedule->command('cron:followup-reminder')->everyThirtyMinutes();
	}
}
