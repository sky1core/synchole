<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DockerGc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docker:gc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'garbage collect docker';

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

        $sh = shell();
        $pruneFilter = sprintf("\"until=%sh\"", $h);

        \Log::debug('docker prune...');
        $sh->try_docker('container prune', '--filter', $pruneFilter, '--force');
        $sh->try_docker('image prune', '--filter', $pruneFilter, '--force');
        $sh->try_docker('volume prune', '--force');
        $sh->try_docker('network prune', '--filter', $pruneFilter, '--force');
    }
}
