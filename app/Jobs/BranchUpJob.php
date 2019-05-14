<?php

namespace App\Jobs;

use App\Docker\Config\Labels;
use App\GitHubApp\GitHubAppRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BranchUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repo;
    protected $branch;
    protected $headSha;
    protected $baseRef;

    /**
     * BranchUpJob constructor.
     * @param $repo
     * @param $branch
     * @param $headSha
     * @param $baseRef
     */
    public function __construct($repo, $branch, $headSha, $baseRef)
    {
        $this->repo = $repo;
        $this->branch = $branch;
        $this->headSha = $headSha;
        $this->baseRef = $baseRef;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $app = new GitHubAppRepo($this->repo);

        if($this->branch === null) {
            $this->branch = $app->data()->repo()->defaultBranch();
        }

        $mainDomain = config('synchole.main_domain');


        $branchDomain = kebab_strict($this->repo).'.'.$mainDomain;

        if(! $app->data()->repo()->isDefaultBranch($this->branch)) {

            $branchDomain = kebab_strict($this->branch).'-'.$branchDomain;
        }


        $path = $app->getWorkspacePath($this->branch);

        if($this->branch == $app->data()->repo()->defaultBranch()) {
            dispatch_now(new DefaultBranchPull($this->repo));
        } else {
            dispatch_now(new BranchPullJob($this->repo, $this->branch, $this->headSha, $this->baseRef));
        }

        $sh = shell();

        $configFile = 'synchole.yml';
        $tempConfigFile = 'synchole.temp.yml';

        $sh->setDir($path);

        $config = load_yaml_file($app->getWorkspacePath($this->branch, $configFile));

        $newServices = [];
        $domainNetwork = 'main_web';

        $builds = [];

        foreach($config['services'] as $serviceName=>$service) {
            Arr::add($service, 'networks', []);
            Arr::add($service, 'labels', []);

            if(! Arr::has($service, 'deploy')) {
                continue;
            }
            Arr::add($service, 'deploy', []);
            Arr::add($service, 'deploy.labels', []);

            $service['labels'][] = 'github.username='.$app->username();
            $service['labels'][] = 'github.repo='.$this->repo;
            $service['labels'][] = 'github.branch='.$this->branch;

            $identifier = kebab_strict(sprintf("%s-%s-%s", $this->repo, $this->branch, $serviceName));
            $identifiers[] = $identifier;

            $labels = new Labels($service['deploy']['labels']);

            $domainEnable = $labels->get('synchole.domain.enable');
            $dockerfile = $labels->get('synchole.build') ?? $labels->get('synchole.build.file');
            $buildContext = $labels->get('synchole.build.context', '.');
            $authGateEnable = $labels->get('synchole.auth.gate.enable');

            if($dockerfile) {
                $newImageName = $service['image'].':'.$this->branch;
                $service['image'] = $newImageName;
                $builds[] = [
                    'identifier' => $identifier,
                    'image_name' => $newImageName,
                    'file' => $dockerfile,
                    'context' => $buildContext,
                ];
            }

            if($domainEnable == 'true') {

                $domainPrefix = $labels->get('synchole.domain.prefix');
                if($domainPrefix) {
                    $domain = $domainPrefix.'-'.$branchDomain;
                } else {
                    $domain = $branchDomain;
                }

                $port = $labels->get('synchole.port', '80');


                $labels->set('traefik.enable', 'true');
                $labels->set('traefik.docker.network', $domainNetwork);
                $labels->set('traefik.port', $port);
                $labels->set('traefik.protocol', 'http');
                $labels->set('traefik.frontend.entryPoints', config('synchole.protocols'));
                $labels->set('traefik.backend', $identifier);
                $labels->set('traefik.frontend.rule', 'Host:'.$domain);

                $labels->set('github.username', $app->username());
                $labels->set('github.repo', $this->repo);
                $labels->set('github.branch', $this->branch);

                $service['networks'][] = $domainNetwork;
                $service['hostname'] = $domain;
            }

            if($authGateEnable == 'true') {
                $labels->set('traefik.frontend.auth.forward.address','http://synchole/auth/gate');
                $labels->set('traefik.frontend.auth.forward.authResponseHeaders', 'X-Auth-User');
            }
            Arr::set($service, 'deploy.labels', $labels->convert());
            $newServices[$identifier] = $service;
        }
        $config['services'] = $newServices;

        Arr::set($config, 'networks.'.$domainNetwork.'.external.name', 'main_web');

        save_yaml_file($app->getWorkspacePath($this->branch, $tempConfigFile), $config);

        foreach($builds as $build) {
            $image = $build['image_name'];
            $file = path_join($build['context'], $build['file']);
            $context = $build['context'];
            if(! $sh->try_docker('build', '-t', $image, '-f', $file, $context)->success) {
                $sh->docker('build', '-t', $image, '-f', $file, '--no-cache', $context);
            }
        }

        $sh->docker('stack deploy -c', $tempConfigFile, 'main');

        foreach($builds as $build) {
            $image = $build['image_name'];
            $identifier = $build['identifier'];
            $sh->docker('service update', 'main_'.$identifier, '--image', $image, '--force');
        }
    }
}
