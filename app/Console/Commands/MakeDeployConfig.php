<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeDeployConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:deploy-config {dev?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make Deploy Config YAML';

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
        $dev = $this->argument('dev');
        echo view('deploy.config')
            ->with('PROTOCOLS', env('PROTOCOLS', 'http,https'))
            ->with('MAIN_DOMAIN', env('MAIN_DOMAIN', 'localhost'))
            ->with('USE_GOOGLE_AUTH', env('USE_GOOGLE_AUTH', false))
            ->with('LOG_LEVEL', env('LOG_LEVEL', 'warning'))
            ->with('EMAIL', env('EMAIL'))
            ->with('MOUNT_DEV', $dev)
            ->with('GITHUB_APP_KEY', env('GITHUB_APP_KEY'))
            ->render();
    }
}