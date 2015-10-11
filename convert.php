<?php
include 'vendor/autoload.php';

use M3uParser\M3uParser;

$obj = new M3uParser();
$files = glob('m3u/*.m3u');

$a = [];
foreach ($files as $file) {
    $data = $obj->parseFile($file);

    foreach ($data as $entry) {
        $suffix = '';
        $normalizedChannelName = normalizeChannelName($entry->getName());
        $normalizedChannelKey = normalizeChannelKey($normalizedChannelName);

        // check if URL is valid
        if (!validUrl($entry->getPath())) {
            echo "'$normalizedChannelName' does not have a valid URL; skipping." . PHP_EOL;
            continue;
        }
        // check for duplicate channel names
        if (isset($a[$normalizedChannelKey])) {
            if ($a[$normalizedChannelKey]['path'] == $entry->getPath()) {
                echo "Skipping duplicate entry for '$normalizedChannelName'." . PHP_EOL;
                continue;
            }
            // distinguish them by URL hash
            $suffix = ' | ' . sha1($entry->getPath());
        }
        $a[$normalizedChannelKey . $suffix] = [
            'name' => $normalizedChannelName . $suffix,
            'path' => $entry->getPath(),
        ];
    }
}
ksort($a);
print_r($a);
writePlaylist($a);

function normalizeChannelKey($channelName)
{
    $channelName = preg_replace('/[^\w]/', '', $channelName);
    return strtolower($channelName);
}

function normalizeChannelName($channelName)
{
    $channelName = trim($channelName, ':');
    $channelName = trim($channelName);
    if (strlen($channelName) == 0) {
        $channelName = 'Unknown';
    }
    return $channelName;
}

function validUrl($url)
{

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $components = parse_url($url);

        // enable caching
        static $urlCache = [];
        $cacheKey = $components['scheme'] . $components['host'] . @$components['port'];
        if (isset($urlCache[$cacheKey])) {
            echo "Returning response for $url from cache." . PHP_EOL;
            return $urlCache[$cacheKey];
        }

        $live = pingPort($components['host'], $components['scheme'], @$components['port']);
        $urlCache[$cacheKey] = $live;
        if ($live) {
            return true;
        }
        echo "Url $url is not live." . PHP_EOL;
    }
    return false;
}

function writePlaylist($array, $filename = 'output.m3u')
{
    file_put_contents($filename, '#EXTM3U' . PHP_EOL);
    foreach ($array as $channel) {
        file_put_contents($filename, '#EXTINF:-1,' . $channel['name'] . PHP_EOL, FILE_APPEND);
        file_put_contents($filename, $channel['path'] . PHP_EOL, FILE_APPEND);
    }
}

function pingPort($host, $proto, $port = null)
{
    if ($port === null) {
        switch ($proto) {
            case 'http':
                $port = 80;
                break;
            case 'https':
                $port = 443;
                break;
            case 'rtmp':
                $port = 1935;
                break;
            case 'rtsp':
                $port = 554;
                break;
            default:
                break;
        }
    }

    return (bool) fsockopen($host, $port, $errno, $errstr, 5);
}
