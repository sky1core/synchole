<?php

namespace App\Console\Commands;

use App\Jobs\BranchDownJob;
use Illuminate\Console\Command;

class BranchDown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:down {repo} {branch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'branch services down';

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

        dispatch_now(new BranchDownJob(
            $repo,
            $branch
        ));
    }
}
