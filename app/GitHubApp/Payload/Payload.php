<?php


namespace App\GitHubApp\Payload;


use Illuminate\Support\Arr;

class Payload extends \ArrayObject
{
    public function __construct($input = array(), $flags = 0, $iterator_class = "ArrayIterator")
    {
        if(is_string($input)) {
            $input = json_decode($input, true);
        }
        parent::__construct($input, $flags, $iterator_class);
    }

    public static function make($input, $optional=null)
    {
        if(is_null($input)) {
            return null;
        }
        return new static($input);
    }

    public function get($key)
    {
        return Arr::get($this, $key);
    }

    public function only($keys)
    {
        return Arr::only((array)$this, $keys);
    }

    /**
     * @param $payloads
     * @return \Illuminate\Support\Collection|static[]
     */
    public static function collect($payloads)
    {
        $collection = collect();
        foreach($payloads as $payload) {
            $collection->push(static::make($payload));
        }
        return $collection;
    }
}