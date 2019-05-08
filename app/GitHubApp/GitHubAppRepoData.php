<?php


namespace App\GitHubApp;


class GitHubAppRepoData extends GitHubAppBase
{
    protected $repo;
    protected $data;

    public function __construct($repo)
    {
        parent::__construct();

        $this->repo = $repo;
        $this->data = new GitHubAppData();
    }

    public function repo()
    {
        return $this->data->repo($this->repo);
    }

    public function prs()
    {
        return $this->data->prs($this->repo);
    }

    public function pr($number)
    {
        return $this->data->pr($this->repo, $number);
    }

    public function branches()
    {
        return $this->data->branches($this->repo);
    }

    public function branch($branch)
    {
        return $this->data->branch($this->repo, $branch);
    }

    public function repo_full_name()
    {
        return $this->username.'/'.$this->repo;
    }

    public function clone_url()
    {
        $access_token = $this->data->access_token();
        return sprintf("https://x-access-token:%s@github.com/%s.git", $access_token, $this->repo_full_name());
    }
}