<?php


namespace App\GitHubApp;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GitHubApp extends GitHubAppBase
{
    protected $data;

    public function __construct()
    {
        parent::__construct();
        $this->data = new GitHubAppData();
    }

    public function data()
    {
        return $this->data;
    }

    public function repo($repo)
    {
        return new GitHubAppRepo($repo);
    }

    public function workspaceInfos()
    {
        $workspace = Storage::disk('workspace');
        $repoDirs = $workspace->directories($this->username);
        $branchDirs = [];
        foreach($repoDirs as $repoDir) {
            $branchDirs = array_merge($branchDirs, $workspace->directories($repoDir));
        }

        $result = [];
        foreach($branchDirs as $branchDir) {
            list($username, $repo, $branch) = explode('/', $branchDir);
            $lastModifiled = Carbon::createFromTimestamp($workspace->lastModified($branchDir));
            $result[] = [
                'username' => $username,
                'repo' => $repo,
                'branch' => $branch,
                'path' => $branchDir,
                'last_modified' => $lastModifiled,
                'diff_in_hours_modified' => Carbon::now()->diffInHours($lastModifiled),
            ];
        }
        return $result;
    }
}