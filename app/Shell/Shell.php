<?php


namespace App\Shell;


use Illuminate\Support\Arr;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class Shell
 * @package App\Shell
 *
 * @method Shell mkdir(...$args)
 * @method Shell pwd(...$args)
 * @method Shell ls(...$args)
 * @method Shell cp(...$args)
 * @method Shell rm(...$args)
 * @method Shell whoami(...$args)
 * @method Shell git(...$args)
 */
class Shell
{
    protected $cwd;
    protected $env = [];

    protected $infos;

    public function __construct()
    {
        $this->infos = collect();
    }

    public function setDir($dir)
    {
        $this->cwd = $dir;
        return $this;
    }

    public function setEnv($key, $value)
    {
        $this->env[$key] = $value;
        return $this;
    }

    public function __call($name, $args)
    {
        return $this->run($name, ...$args);
    }

    public function try_docker(...$args)
    {
        return $this->try('sudo docker', ...$args);
    }

    public function docker(...$args)
    {
        return $this->run('sudo docker', ...$args);
    }

    public function try_docker_compose(...$args)
    {
        return $this->try('sudo docker-compose', ...$args);
    }

    public function docker_compose(...$args)
    {
        return $this->run('sudo docker-compose', ...$args);
    }

    /**
     * @param mixed ...$cmd
     * @return ShellProcessInfo
     */
    public function try(...$cmd) {
        $process = new ShellProcess($cmd, $this->cwd, $this->env);

        $info = $process->run();

        $this->infos->push($info);

        return $info;
    }

    /**
     * @param mixed ...$cmd
     * @return ShellProcessInfo
     */
    public function run(...$cmd)
    {
        $process = new ShellProcess($cmd, $this->cwd, $this->env);

        $info = $process->run();

        $this->infos->push($info);

        if(! $info->success) {
            \Log::error($info);
        }

        if(! $info->success) {
            // @todo: custom exception
            throw new ProcessFailedException($process->process);
        }
        return $info;
    }

    public function getInfos()
    {
        return $this->infos;
    }

    /**
     * @return ShellProcessInfo
     */
    public function getLastInfo()
    {
        return $this->infos->last();
    }

    public function getLastOutput()
    {
        return $this->getLastInfo()->output;
    }

    public function getLastTrimOutput()
    {
        return trim($this->getLastInfo()->trimOutput());
    }
}