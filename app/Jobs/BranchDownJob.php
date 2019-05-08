<?php

namespace App\Jobs;

use App\GitHubApp\GitHubApp;
use App\GitHubApp\GitHubAppRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BranchDownJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repo;
    protected $branch;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($repo, $branch)
    {
        $this->repo = $repo;
        $this->branch = $branch;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $app = new GitHubApp();

        $labels = [
            'github.username='.$app->username(),
            'github.repo='.$this->repo,
            'github.branch='.$this->branch,
        ];

        $services = docker_api()->getServices(['filters'=>json_encode(['label'=> $labels])]);

        $sh = shell();
        foreach($services as $service) {
            $sh->try_docker('service rm', $service['ID']);
            \Log::debug($sh->getLastInfo()->commandLine);
        }
    }
}
