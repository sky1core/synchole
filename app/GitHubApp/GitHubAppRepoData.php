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

    public function clone_url_with_access_token()
    {
        $access_token = $this->data->access_token();
        $pure_url = sprintf('https://github.com/%s.git', $this->repo_full_name());
        return GitHubAppHelper::makeGitHubUrlWithAccessToken($pure_url, $access_token);
    }

    public function submodule_url_with_access_token($submodule_url)
    {
        $access_token = $this->data->access_token();
        return GitHubAppHelper::makeGitHubUrlWithAccessToken($submodule_url, $access_token);
    }
}