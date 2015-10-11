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
    return filter_var($url, FILTER_VALIDATE_URL);
}

function writePlaylist($array, $filename = 'output.m3u')
{
    file_put_contents($filename, '#EXTM3U' . PHP_EOL);
    foreach ($array as $channel) {
        file_put_contents($filename, '#EXTINF:-1,' . $channel['name'] . PHP_EOL, FILE_APPEND);
        file_put_contents($filename, $channel['path'] . PHP_EOL, FILE_APPEND);
    }
}
