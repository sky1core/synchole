<?php


namespace App\GitHubApp;


use Illuminate\Support\Str;

class GitHubAppHelper
{
    public static function makeGitHubUrlWithAccessToken($url, $accessToken)
    {
        if(! Str::startsWith($url, 'https://x-access-token:')) {
            $url = str_replace('https://', sprintf('https://x-access-token:%s@', $accessToken), $url);
        } else {
            $url = sprintf('https://x-access-token:%s@%s', $accessToken, Str::after($url, '@'));
        }

        return $url;
    }
}