<?php


namespace App\GitHubApp\Payload;


class BranchPayload extends Payload
{
    public function name()
    {
        return $this->get('name');
    }

    public function commitSha()
    {
        return $this->get('commit.sha');
    }
}
