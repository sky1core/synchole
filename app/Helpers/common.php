<?php

if(! function_exists('shell')) {
    function shell() {
        return new \App\Shell\Shell();
    }
}

if(! function_exists('github_repo')) {
    function github_repo() {
        return \GrahamCampbell\GitHub\Facades\GitHub::repo();
    }
}

if(! function_exists('github_app_path')) {
    function github_app_path() {
        return new \App\GitHubApp\GitHubAppPath();
    }
}

if(! function_exists('load_yaml_file')) {
    function load_yaml_file($filename) {
        return \Symfony\Component\Yaml\Yaml::parseFile($filename);
    }
}

if(! function_exists('save_yaml_file')) {
    function save_yaml_file($filename, $php_value) {
        $yaml = \Symfony\Component\Yaml\Yaml::dump($php_value, 5, 2);
        file_put_contents($filename, $yaml);
    }
}

if(! function_exists('path_join')) {
    function path_join(...$paths) {
        return preg_replace('#/+#','/',join('/', $paths));
    }
}

if(! function_exists('parse_labels')) {
    function parse_labels($labels) {
        $parsed = [];
        foreach($labels as $label) {
            list($key, $value) =  array_pad(explode('=', $label), 2, 'true');
            $key = trim($key);
            $value = trim($value);
            $parsed[$key] = $value;
        }
        return $parsed;
    }
}

if(! function_exists('explode_trim')) {
    function explode_trim($delimiter, $string) {
        return array_map('trim', explode($delimiter, $string));
    }
}

if(! function_exists('generate_app_url')) {
    function generate_app_url() {

        $protocols = env('PROTOCOLS');
        $https = in_array("https", explode_trim(",", $protocols));
        $protocol = $https ? 'https' : 'http';
        $app_url = $protocol.'://'.env('MAIN_DOMAIN');
        return $app_url;
    }
}

if(! function_exists('docker_api')) {
    function docker_api() {
        return new \App\Docker\Api();
    }
}

if(! function_exists('bool_str')) {
    function bool_str($value) {
        return (bool)$value === true ? 'true' : 'false';
    }
}

