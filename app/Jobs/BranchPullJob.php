<?php

namespace App\Jobs;

use App\GitHubApp\GitHubAppRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BranchPullJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repo;
    protected $branch;
    protected $headSha;
    protected $baseRef;

    /**
     * BranchPullJob constructor.
     * @param $repo
     * @param $branch
     * @param $headSha
     * @param $baseRef
     */
    public function __construct($repo, $branch, $headSha, $baseRef)
    {
        $this->repo = $repo;
        $this->branch = $branch;
        $this->headSha = $headSha;
        $this->baseRef = $baseRef;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $app = new GitHubAppRepo($this->repo);
        if($this->branch === null) {
            $this->branch = $app->data()->repo()->defaultBranch();
        }

        if($this->branch == $app->data()->repo()->defaultBranch()) {
            dispatch_now(new DefaultBranchPull($this->repo));
            return;
        }

        $defaultPath = $app->getDefaultWorkspacePath();
        $path = $app->getWorkspacePath($this->branch);

        $sh = shell();

        if(! $app->defaultWorkspaceExists()) {
            dispatch_now(new DefaultBranchPull($this->repo));
        }

        $cloneUrl = $app->data()->clone_url();

        $alredyExists = \File::exists($path.'/.git');
        if(! $alredyExists) {
            $sh->mkdir('-p ', $path);
            $sh->cp('-f -R -p', $defaultPath.'/.', $path);
        }


        $sh->setDir($path);

        $sh->git('config remote.origin.url', $cloneUrl);
        $sh->git('config remote.origin.fetch', "+refs/heads/*:refs/remotes/origin/*");

        if(! $alredyExists) {
            $sh->git('clean -f');
        }


        $sh->git('fetch --all');

        if($this->baseRef) {
            $sh->git('checkout -f', $this->baseRef);
            $sh->git('pull', $cloneUrl);
        }

        $sh->git('checkout -f', $this->branch);
        if($this->headSha) {
            $sh->git('checkout -f', $this->headSha);
        }


        if($this->baseRef) {
            $sh->git('merge --no-edit', $this->baseRef);
        }
    }
}
