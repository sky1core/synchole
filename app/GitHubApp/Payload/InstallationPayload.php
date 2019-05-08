<?php


namespace App\GitHubApp\Payload;


class InstallationPayload extends Payload
{
    public function id()
    {
        return $this->get('id');
    }

    public function repositoryUrl()
    {
        return $this->get('repositories_url');
    }

    public function appId()
    {
        return $this->get('app_id');
    }

    public function targetId()
    {
        return $this->get('target_id');
    }

    public function targetType()
    {
        return $this->get('target_type');
    }

    public function permissions()
    {
        return $this->get('permissions');
    }

    public function events()
    {
        return $this->get('events');
    }
}