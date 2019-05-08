<?php


namespace App\GitHubApp\Payload;


use Illuminate\Support\Arr;

class PrPayload extends Payload
{
    public function getTags()
    {
    }

    public static function makeFromPrNumber($repo, $number)
    {
        $username = config('synchole.github.username');
        $pr = \GitHub::pr()->show($username, $repo, $number);

        return new static($pr);
    }

    public static function makeFromContainerName(string $name)
    {

    }

    public function headRepoName()
    {
        return $this->get('head.repo.name');
    }

    public function headRepoFullName()
    {
        return $this->get('head.repo.full_name');
    }


    public function baseRepoName()
    {
        return $this->get('base.repo.name');
    }

    public function number()
    {
        return $this->get('number');
    }

    /*
    public function repoAndNumber()
    {
        return [$this->repoName(), $this->number()];
    }
    */

    public function headRef()
    {
        return $this->get('head.ref');
    }

    public function baseRef()
    {
        return $this->get('base.ref');
    }

    public function headSha()
    {
        return $this->get('head.sha');
    }

    public function baseSha()
    {
        return $this->get('base.sha');
    }

    public function mergeCommitSha()
    {
        return $this->get('merge_commit_sha');
    }

    public function labels()
    {
        return $this->get('labels');
    }

    /**
     * open, closed
     * @return mixed
     */
    public function state()
    {
        return $this->get('state');
    }

    public function merged()
    {
        return $this->get('merged_at') != null;
    }

    public function htmlUrl()
    {
        return $this->get('html_url');
    }

    public function diffUrl()
    {
        return $this->get('diff_url');
    }

    public function title()
    {
        return $this->get('title');
    }

    public function userName()
    {
        return $this->get('user.login');
    }

    public function headRepoCloneUrl()
    {
        return $this->get('head.repo.clone_url');
    }

    public function headRepoHtmlUrl()
    {
        return $this->get('head.repo.html_url');
    }
}