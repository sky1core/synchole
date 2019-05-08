<?php

namespace App\Console\Commands;

use App\GitHubApp\GitHubApp;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GitHubAppStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github-app:status {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GitHub App Status';

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
        $force = $this->option('force');

        $app = new GitHubApp();

        $this->info('username: '.$app->username());
        $this->info('app_id: '.$app->app_id());
        $this->info('installation_id: '.$app->data()->installation_id());
        $this->info('access_token: '.$app->data()->access_token());

        $this->info('---- app repos ----');
        if($force) {
            $app->data()->forceRepos();
        }
        $repos = $app->data()->repos();
        $repoKeys = ['id', 'name', 'git_url', 'default_branch'];
        $this->table($repoKeys, $repos->map->only($repoKeys));

        foreach($repos as $repo) {
            $repoName = $repo['name'];

            if($force) {
                $app->data()->forceBranches($repo);
                $app->data()->forcePrs($repo);
            }

            $this->info("---- $repoName.branches ----");
            $branches = $app->data()->branches($repo);
            $branchKeys = ['name'];
            $this->table($branchKeys, $branches->map->only($branchKeys));

            $this->info("---- $repoName.prs ----");
            $prs = $app->data()->prs($repo);
            $prKeys = ['number', 'state', 'title'];
            $this->table($prKeys, $prs->map->only($prKeys));
        }
    }
}
