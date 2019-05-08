<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UserAccept extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:accept {email} {--admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'user accept';

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
        $email = $this->argument('email');
        $admin = $this->option('admin');

        $user = User::where('email', $email)->first();
        if(! $user) {
            $this->warn('not found user');
            return;
        }

        $this->info('name: '. $user->name);
        $this->info('email: '. $user->email);
        $this->info('accept: '. bool_str($user->email_verified_at) .' => '.'true');
        $this->info('admin: '. bool_str($user->admin) .' => '.bool_str($admin));

        if ($this->confirm('Do you wish to continue?')) {
            $user->email_verified_at = Carbon::now();
            if($admin) {
                $user->admin = true;
            }
            $user->save();
        }
    }
}
