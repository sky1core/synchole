<?php


namespace App\Shell;


class ShellProcessInfo
{
    public $cwd;
    public $commandLine;
    public $success;
    public $startTime;
    public $endTime;
    public $duration;
    public $output;
    public $code;

    public function trimOutput()
    {
        return trim($this->output);
    }

    public function outputLines()
    {
        return explode_trim("\n", rtrim($this->output));
    }

    public function __toString()
    {
        return json_encode($this);
    }
}