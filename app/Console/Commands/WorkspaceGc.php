<?php

namespace App\Console\Commands;

use App\GitHubApp\GitHubAppRepo;
use App\GitHubApp\GitHubApp;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WorkspaceGc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspace:gc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'garbage collect warkspace';

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
        $h = config('synchole.gc_hours');
        $app = new GitHubApp();

        $spaces = $app->workspaceInfos();
        foreach ($spaces as $space) {
            $appRepo = $app->repo($space['repo']);
            if($appRepo->data()->repo()->defaultBranch() == $space['branch']) {
                continue;
            }

            $labels = [
                'github.username='.$space['username'],
                'github.repo='.$space['repo'],
                'github.branch='.$space['branch'],
            ];

            $containers = docker_api()->getContainers(['filters'=>json_encode(['label'=> $labels])]);
            if(count($containers)) {
                \Log::debug($space['path'].' active container');
                continue;
            }

            if($space['diff_in_hours_modified'] > $h) {
                \Log::debug($space['path'].' try remove');
                Storage::disk('workspace')->deleteDirectory($space['path']);
            }
        }
    }
}
