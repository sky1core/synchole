<?php


namespace App\Docker\Config;


use Illuminate\Support\Arr;

class Labels extends \ArrayObject
{
    public function __construct($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        $parsed = [];
        foreach($input as $label) {
            list($key, $value) =  array_pad(explode('=', $label), 2, 'true');
            $key = trim($key);
            $value = trim($value);
            $parsed[$key] = $value;
        }

        parent::__construct($parsed, $flags, $iterator_class);
    }

    public function get($key, $default=null)
    {
        return Arr::get($this, $key, $default);
    }

    public function set($key, $value)
    {
        $this[$key] = $value;
    }

    public function convert()
    {
        $labels = [];
        foreach ($this as $key=>$value) {
            $labels[] = $key.'='.$value;
        }
        return $labels;
    }
}