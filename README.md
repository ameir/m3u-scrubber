# m3u-scrubber
Validate all IPTV streams in M3U playlist and generate scrubbed playlist.

## What does this do?
Given an M3U IPTV playlist, this script goes through each entry and determines whether or not it is valid.  It'll then construct a new playlist with the working channels.

## How does it work?
First, a simple TCP test is performed to see if the address is resolvable and the port is reachable.  This result is cached, so URLs hosted on the same IPTV server aren't tested multiple times.  After that, a portion of the stream is requested.

## How do install it?
You will need PHP 7 or greater.  Clone the repo, enter the directory, and run `composer install`.  If you are familiar with Docker, you can avoid dealing with dependencies.  See below for information.

## How do I use it?
Download and copy all of the M3U files you want to test into the `m3u` directory (create it if it doesn't exist).  Note that if you test multiple files at once, they will be merged into one working playlist.  If you do not want to do this, test one playlist at a time.

If you are using the Docker image, you can mount a local path with your M3U playlists and run the script like so (adjust the path before the `:` as needed):
```
docker run -ti --rm -v $PWD/m3u:/var/tmp/m3u ameir/m3u-scrubber
```

If you are not using Docker, simply run `./convert.php`.

## Contributions
Simply file a PR and I'll take a look!
