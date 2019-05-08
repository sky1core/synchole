<?php


namespace App\Shell;


use Symfony\Component\Process\Process;

class ShellProcess
{
    public $process;
    protected $info;

    public function __construct($cmd, $cwd=null, $env=null)
    {
        if(is_array($cmd)) {
            $cmd = implode(' ', $cmd);
        }

        $this->info = new ShellProcessInfo();
        $this->process = Process::fromShellCommandline($cmd, $cwd, $env);
        $this->process->setTimeout(60*10);
    }

    public function run()
    {
        $this->info->startTime = \Carbon\Carbon::now();

        \Log::debug($this->process->getCommandLine());
        $code = $this->process->run();

        $this->info->cwd = $this->process->getWorkingDirectory();
        $this->info->endTime = \Carbon\Carbon::now();
        $this->info->duration = $this->info->endTime->diffInMicroseconds($this->info->startTime) / 1000000;
        $this->info->commandLine = $this->process->getCommandLine();

        $this->info->output = $this->process->getOutput();
        $this->info->success = $this->process->isSuccessful();
        $this->info->code = $code;

        return $this->info;
    }
}