<?php


namespace App\GitHubApp\Payload;


class RepoPayload extends Payload
{
    public function id()
    {
        return $this->get('id');
    }

    public function name()
    {
        return $this->get('name');
    }

    public function fullName()
    {
        return $this->get('full_name');
    }

    public function gitUrl()
    {
        return $this->get('git_url');
    }

    public function sshUrl()
    {
        return $this->get('ssh_url');
    }

    public function defaultBranch()
    {
        return $this->get('default_branch');
    }

    public function isDefaultBranch($branch)
    {
        return $this->defaultBranch() == $branch;
    }
}