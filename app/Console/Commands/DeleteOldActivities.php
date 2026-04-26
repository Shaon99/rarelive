<?php

namespace App\Console\Commands;

use App\Models\RecycleBin;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOldActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete recycle bin items older than one week (notifications and activity logs are pruned by admin:prune-old-logs).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subWeeks(1);

        $recycleDeleted = RecycleBin::where('created_at', '<', $cutoffDate)->delete();

        $this->info("✅ Deleted {$recycleDeleted} old recycle bin item(s).");

        return 0;
    }
}
