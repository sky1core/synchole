<?php

namespace App\Jobs;

use App\GitHubApp\GitHubAppRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DefaultBranchPull implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repo;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($repo)
    {
        $this->repo = $repo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $app = new GitHubAppRepo($this->repo);
        $path = $app->getDefaultWorkspacePath();

        $sh = shell();

        $sh->mkdir('-p', $path);
        $sh->setDir($path);

        $cloneUrl = $app->data()->clone_url_with_access_token();
        if(! $app->defaultWorkspaceExists()) {
            $sh->git('clone', $cloneUrl, '.');
        }

        $sh->git('config remote.origin.url', $cloneUrl);
        $sh->git('config remote.origin.fetch', "+refs/heads/*:refs/remotes/origin/*");

        $sh->git('fetch --all');
        $sh->git('checkout -f', $app->data()->repo()->defaultBranch());
        $sh->git('pull');

        if(\File::exists($path.'/.gitmodules')) {
            $sh->git('submodule init');
            $submodules = $sh->git('config --file .gitmodules --get-regexp url')->outputLines();

            foreach ($submodules as $submodule) {
                list($key, $url) = explode(' ', $submodule);
                $url = $app->data()->submodule_url_with_access_token($url);

                $sh->git('config', $key, $url);
            }
            $sh->git('submodule update');
        }
    }
}
