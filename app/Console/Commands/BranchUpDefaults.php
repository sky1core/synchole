<?php

namespace App\Console\Commands;

use App\GitHubApp\GitHubApp;
use App\Jobs\BranchUpJob;
use Illuminate\Console\Command;

class BranchUpDefaults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:up-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'branch up default-branches';

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
        $app = new GitHubApp();
        $repos = $app->data()->repos();

        foreach ($repos as $repo) {
            dispatch(new BranchUpJob(
                $repo->name(),
                $repo->defaultBranch(),
                $repo->defaultBranch(),
                null
            ));
        }
    }
}
