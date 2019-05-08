<?php


namespace App\Docker;

use http\Exception\RuntimeException;
use Illuminate\Support\Str;

/**
 * Class Api
 * @package App\Docker
 */
class Api
{
    protected $defaultCmd = "curl -X %s --silent --unix-socket /var/run/docker.sock";
    protected $apiUrl = 'http:/v1.37/';

    public function __construct()
    {
    }

    /**
     * @param $document
     * @param array $query
     * @return \App\Docker\Command
     */
    protected function execute($method, $document, array $query=[])
    {
        $url = $this->apiUrl.$document;
        if($query) {
            $url .= '?'.http_build_query($query);
        }

        $sh = shell();
        $response = $sh->run('sudo', sprintf($this->defaultCmd, $method), $url);
        \Log::debug($response->commandLine);
        if(Str::startsWith($response->output, '{"message":')) {
            throw new RuntimeException($sh->getLastInfo()->commandLine.' failed '.$response->output);
        }
        return json_decode($response->output, true);

    }

    /**
     * list containers
     * query: all, limit, since, before, size, filters
     */
    public function getContainers(array $query=[])
    {
        return $this->execute("GET", "containers/json", $query);
    }

    public function getServices(array $query=[])
    {
        return $this->execute("GET", "services", $query);
    }

    public function getImages(array $query=[])
    {
        return $this->execute("GET", "images/json", $query);
    }

    public function getInspect($id, array $query=[])
    {
        return $this->execute("GET", "containers/$id/json", $query);
    }

    public function getTop($id, array $query=[])
    {
        return $this->execute("GET", "containers/$id/top", $query);
    }

    public function getLogs($id, array $query=[])
    {
        return $this->execute("GET", "containers/$id/logs", $query);
    }

    public function getChanges($id, array $query=[])
    {
        return $this->execute("GET", "containers/$id/changes", $query);
    }

}