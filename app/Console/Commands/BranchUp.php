<?php

namespace App\Console\Commands;

use App\Jobs\BranchUpJob;
use Illuminate\Console\Command;

class BranchUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:up {repo} {branch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'branch services up';

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
        $repo = $this->argument('repo');
        $branch = $this->argument('branch');

        dispatch(new BranchUpJob(
            $repo,
            $branch,
            $branch,
            null
        ));
    }
}
