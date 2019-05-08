<?php


namespace App\GitHubApp;

use App\GitHubApp\Payload\BranchPayload;
use App\GitHubApp\Payload\InstallationPayload;
use App\GitHubApp\Payload\PrPayload;
use App\GitHubApp\Payload\RepoPayload;
use Closure;
use Illuminate\Support\Facades\Cache;

class GitHubAppData extends GitHubAppBase
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function cache()
    {
        return Cache::tags(['github.app']);
    }

    protected function get($key, $default = null)
    {
        return $this->cache()->get($key, $default);
    }

    protected function put($key, $value, $ttl = null)
    {
        return $this->cache()->put($key, $value, $ttl);
    }

    protected function forever($key, $value)
    {
        return $this->cache()->forever($key, $value);
    }

    protected function remember($key, $ttl, Closure $callback)
    {
        $value = $this->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $ttl);

        return $value;
    }

    protected function rememberForever($key, Closure $callback)
    {
        $value = $this->get($key);

        if (! is_null($value)) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    public function jwt_token()
    {
        $pemFile = 'file:///run/secrets/github_app_key';

        $jwt = (new \Lcobucci\JWT\Builder)
            ->setIssuer($this->app_id)
            ->setIssuedAt(time())
            ->setExpiration(time() + 60)
            ->sign(new \Lcobucci\JWT\Signer\Rsa\Sha256(),  new \Lcobucci\JWT\Signer\Key($pemFile))
            ->getToken();

        return $jwt;
    }

    protected function jwtClient($apiVersion = null)
    {
        $client = new \Github\Client(null, $apiVersion);
        $jwtToken = $this->jwt_token();
        $client->authenticate($jwtToken, null, \Github\Client::AUTH_JWT);
        return $client;
    }

    protected function client($apiVersion = null)
    {
        $client = new \Github\Client(null, $apiVersion);
        $accessToken = $this->access_token();
        $client->authenticate($accessToken, null, \Github\Client::AUTH_HTTP_TOKEN);
        return $client;
    }

    public function access_token()
    {
        return $this->get('access_token', function() {
            $client = $this->jwtClient('machine-man-preview');
            $tokenInfo = $client->apps()->createInstallationToken($this->installation_id());

            if($tokenInfo) {
                $token = $tokenInfo['token'];
                $expiresAt = $tokenInfo['expires_at'];
                $this->put('access_token', $token, \Carbon\Carbon::create($expiresAt)->subMinute(10));
                return $token;
            }
            return null;
        });
    }

    public function installation_id()
    {
        $installation = $this->installation();
        return data_get($installation, 'id');
    }

    /**
     * @return InstallationPayload|null
     */
    public function installation()
    {
        $installation = $this->rememberForever('installation', function() {

            $client = $this->jwtClient('machine-man-preview');
            $installations = $client->apps()->findInstallations();

            return collect($installations)->keyBy('app_id')->get($this->app_id);
        });
        return InstallationPayload::make($installation);
    }

    public function hasInstallation()
    {
        return $this->cache()->has('installation');
    }

    public function foreverInstallation($installation)
    {
        $this->forever('installation', $installation);
    }

    protected function resolveRepoName($repo)
    {
        if($repo instanceof RepoPayload) {
            return $repo->name();
        }
        return $repo;
    }

    /**
     * @return RepoPayload[]|\Illuminate\Support\Collection
     */
    public function repos()
    {
        $repos = $this->remember('repos', 3600, function() {
            $client = $this->client('machine-man-preview');
            $listRepos = $client->apps()->listRepositories();
            return $listRepos['repositories'];
        });
        return RepoPayload::collect($repos);
    }

    public function forceRepos()
    {
        $client = $this->client('machine-man-preview');
        $listRepos = $client->apps()->listRepositories();
        $repos = $listRepos['repositories'];
        $this->put('repos', $repos, 3600);
    }

    /**
     * @param $repo
     * @return RepoPayload|null
     */
    public function repo($repo)
    {
        $repo = $this->resolveRepoName($repo);
        $repos = $this->repos();
        return $repos->where('name', $repo)->first();
    }

    /**
     * @param $repo
     * @return PrPayload[]|\Illuminate\Support\Collection
     */
    public function prs($repo)
    {
        $repo = $this->resolveRepoName($repo);
        $prs = $this->remember($repo.'.prs', 3600, function() use($repo) {
            return $this->client()->pr()->all($this->username, $repo);
        });
        return PrPayload::collect($prs);
    }

    public function forcePrs($repo)
    {
        $repo = $this->resolveRepoName($repo);
        $prs = $this->client()->pr()->all($this->username, $repo);
        $this->put($repo.'.prs', $prs, 3600);
    }

    /**
     * @param $repo
     * @param $number
     * @return PrPayload|null
     */
    public function pr($repo, $number)
    {
        $prs = $this->prs($repo);
        return $prs->where('number', $number)->first();
    }

    /**
     * @param $repo
     * @return BranchPayload[]|\Illuminate\Support\Collection
     */
    public function branches($repo)
    {
        $repo = $this->resolveRepoName($repo);
        $branches = $this->remember($repo.'.branches', 3600, function() use($repo) {
            return $this->client()->repos()->branches($this->username, $repo);
        });
        return BranchPayload::collect($branches);
    }

    public function forceBranches($repo)
    {
        $repo = $this->resolveRepoName($repo);
        $branches = $this->client()->repos()->branches($this->username, $repo);
        $this->put($repo.'.branches', $branches, 3600);
    }

    /**
     * @param $repo
     * @param $branch
     * @return BranchPayload|null
     */
    public function branch($repo, $branch)
    {
        $branches = $this->branches($repo);
        return $branches->where('name', $branch)->first();
    }
}