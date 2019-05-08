<?php


namespace App\GitHubApp;


class GitHubAppBase
{
    protected $app_id;
    protected $username;

    public function __construct()
    {
        $this->app_id = config('synchole.github.app_id');
        $this->username = config('synchole.github.username');
    }

    public function app_id()
    {
        return $this->app_id;
    }

    public function username()
    {
        return $this->username;
    }
}