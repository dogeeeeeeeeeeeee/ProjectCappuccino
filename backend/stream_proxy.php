<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$vidID = $_GET['vidID'] ?? '';
$check = !empty($_GET['check']);

if ($vidID === '' && strtolower(PHP_SAPI) != 'cli') {
    header("HTTP/1.1 400 Bad Request");
    die("Missing vidID");
}

$isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// Setup Paths
$cacheDir  = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

if (strtolower(PHP_SAPI) === 'cli') {
    $ttl = 7200; // 1 hour
    $count = 0;

    foreach (glob($cacheDir . '*.mp4') as $mp4) {
        // Skip files still being processed (check for .mp4.lock)
        if (file_exists($mp4 . '.lock')) {
            continue;
        }

        // Check if the MP4 itself is old
        if ((time() - filemtime($mp4)) > $ttl) {
            echo "$mp4 is old\n";
            $basePath = substr($mp4, 0, -4); // path/to/videoID
            
            // Explicitly delete the family of files
            @unlink($mp4);           // .mp4
            @unlink($mp4 . '.lock'); // .mp4.lock
            @unlink($mp4 . '.log');  // .mp4.log
            @unlink($mp4 . '.bat');  // .mp4.bat
            @unlink($mp4 . '.sh');   // .mp4.sh
            
            $count++;
        } else {
            echo "$mp4 is not old\n";
        }
    }
    echo "it is done\n";
    echo "deleted $count sets of files\n";
    exit(0); // Use exit in CLI, not die with a message for better scripting
}

$cacheFile = $cacheDir . $vidID . '.mp4';
$lockFile  = $cacheFile . '.lock';
$scriptFile = $cacheFile . ($isWin ? '.bat' : '.sh');
$logFile   = $cacheFile . '.log';

// Serve if ready
if (file_exists($cacheFile) && !file_exists($lockFile)) {
    if ($check) die("Ready");
    touch($cacheFile); 
    header("Content-Type: video/mp4");
    header("Content-Length: " . filesize($cacheFile));
    readfile($cacheFile);
    exit;
}

if ($check && file_exists($lockFile)) {
    header("HTTP/1.1 425 Too Early");
    die("Still processing");
}

/* --- TRANSCODE START --- */
if (!file_exists($lockFile)) {
    file_put_contents($lockFile, 'running');

    $sourceUrl = $CONFIG['jellyfin_url'] . "/Videos/{$vidID}/stream?static=true&api_key=" . $CONFIG['api_key'];
    $ffmpeg = $CONFIG['ffmpeg'];

    // Build the Script content based on OS
    if ($isWin) {
        $content = "@echo off\r\n" .
                   "\"$ffmpeg\" -i \"$sourceUrl\" -c:v libx264 -profile:v baseline -level 3.0 -preset fast -crf 28 -sn -c:a aac -ac 2 -ar 44100 -b:a 128k -movflags +faststart \"$cacheFile\" > \"$logFile\" 2>&1\r\n" .
                   "del \"$lockFile\"\r\n" .
                   "del \"$scriptFile\"";
    } else {
        $content = "#!/bin/bash\n" .
                   "\"$ffmpeg\" -i \"$sourceUrl\" -c:v libx264 -profile:v baseline -level 3.0 -preset fast -crf 28 -sn -c:a aac -ac 2 -ar 44100 -b:a 128k -movflags +faststart \"$cacheFile\" > \"$logFile\" 2>&1\n" .
                   "rm \"$lockFile\"\n" .
                   "rm \"$scriptFile\"";
    }

    if (file_put_contents($scriptFile, $content) === false) {
        unlink($lockFile);
        die("Permission denied: Cannot write to $cacheDir");
    }

    // Execute background process
    if ($isWin) {
        // The "Poor Man's" background launch for Windows
        pclose(popen("start /B cmd /C \"$scriptFile\" >NUL 2>&1", "r"));
    } else {
        // Linux background launch
        chmod($scriptFile, 0755);
        exec("bash \"$scriptFile\" > /dev/null 2>&1 &");
    }
}

header("HTTP/1.1 202 Accepted");
die("Caching in progress");