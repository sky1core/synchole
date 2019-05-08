<?php


namespace App\GitHubApp;


use Illuminate\Support\Facades\Storage;

class GitHubAppPath
{
    protected $root;

    public function __construct()
    {
        $this->root = Storage::disk('data')->path('');
    }

    public function root()
    {
        return $this->root;
    }

    public function path(...$paths)
    {
        return path_join($this->root, ...$paths);
    }

    public function workspace(...$paths)
    {
        return $this->path('workspace', ...$paths);
    }

    public function workspaceCache(...$path)
    {
        return $this->path('workspace_cache', ...$path);
    }

}