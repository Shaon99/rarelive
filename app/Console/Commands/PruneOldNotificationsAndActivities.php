<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

class PruneOldNotificationsAndActivities extends Command
{
    protected $signature = 'admin:prune-old-logs
                            {--days=3 : Delete rows created more than this many days ago}';

    protected $description = 'Delete database notifications and Spatie activity log rows older than the given number of days (default: 3).';

    public function handle(): int
    {
        $days = max(0, (int) $this->option('days'));
        $cutoff = Carbon::now()->subDays($days);

        $notificationsDeleted = Notification::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $activitiesDeleted = Activity::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$notificationsDeleted} notification(s) older than {$days} day(s) (before {$cutoff->toDateTimeString()}).");
        $this->info("Deleted {$activitiesDeleted} activity log row(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
