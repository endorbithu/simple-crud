<?php

namespace DelocalZrt\SimpleCrud\Console\Commands;

use DelocalZrt\SimpleCrud\Models\SimpleCrudActivityLog;
use Illuminate\Console\Command;

class DeleteOldActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simplecrud:deleteoldactivitylog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'deleteoldactivitylog';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SimpleCrudActivityLog::query()->where('created_at', '<', \Carbon\Carbon::now()->subDays(config('simplecrud.activity_log_keep_days', 90)))->delete();
        return 0;
    }
}
