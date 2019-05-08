<?php


namespace App\GitHubApp;


use Illuminate\Support\Str;

class GitHubAppRepo extends GitHubAppBase
{
    protected $repo;
    protected $data;

    public function __construct($repo)
    {
        parent::__construct();
        $this->repo = $this->repoFullNameToRepoName($repo);
        $this->data = new GitHubAppRepoData($repo);
    }

    protected function repoFullNameToRepoName($repoFullName)
    {
        return Str::after($repoFullName, '/');
    }

    public function data()
    {
        return $this->data;
    }

    public function getWorkspacePath($branch, ...$paths)
    {
        return (new GitHubAppPath)->workspace($this->data->repo_full_name(), $branch, ...$paths);
    }


    public function getDefaultWorkspacePath()
    {
        return $this->getWorkspacePath($this->data->repo()->defaultBranch());
    }

    public function defaultWorkspaceExists()
    {
        $path = $this->getDefaultWorkspacePath();
        return \File::exists($path.'/.git');
    }
}