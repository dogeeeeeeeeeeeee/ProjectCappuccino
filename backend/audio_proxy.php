<?php
require_once __DIR__ . '/config.php';

$audID = $_GET['audID'] ?? '';
$check = !empty($_GET['check']);

if ($audID === '') {
    header("HTTP/1.1 400 Bad Request");
    die("Missing audID");
}

$isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// Setup Paths
$cacheDirBase = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
$cacheDir  = $cacheDirBase . 'aud' . DIRECTORY_SEPARATOR;
if (!is_dir($cacheDirBase)) {
    mkdir($cacheDirBase, 0777, true);
}
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

$cacheFile  = $cacheDir . $audID . '.m4a'; // M4A is just MP4 audio
$lockFile   = $cacheFile . '.lock';
$scriptFile = $cacheFile . ($isWin ? '.bat' : '.sh');
$logFile    = $cacheFile . '.log';

// Serve if ready
if (file_exists($cacheFile) && !file_exists($lockFile)) {
    if ($check) die("Ready");
    header("HTTP/1.1 200 OK");
    header("Content-Type: audio/mp4"); // Correct type for M4A
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

    // For audio, we use the /Audio/{id}/stream endpoint
    $sourceUrl = $CONFIG['jellyfin_url'] . "/Audio/{$audID}/stream?static=true&api_key=" . $CONFIG['api_key'];
    $ffmpeg = $CONFIG['ffmpeg'];

    // -vn drops video (important if the file has embedded cover art)
    // -c:a aac -b:a 192k is plenty for Wii U
    $ffmpegCmd = "\"$ffmpeg\" -i \"$sourceUrl\" -vn -c:a aac -b:a 192k -ar 44100 -movflags +faststart \"$cacheFile\" > \"$logFile\" 2>&1";

    if ($isWin) {
        $content = "@echo off\r\n" . $ffmpegCmd . "\r\n" . "del \"$lockFile\"\r\n" . "del \"$scriptFile\"";
    } else {
        $content = "#!/bin/bash\n" . $ffmpegCmd . "\n" . "rm \"$lockFile\"\n" . "rm \"$scriptFile\"";
    }

    file_put_contents($scriptFile, $content);

    if ($isWin) {
        pclose(popen("start /B cmd /C \"$scriptFile\" >NUL 2>&1", "r"));
    } else {
        chmod($scriptFile, 0755);
        exec("bash \"$scriptFile\" > /dev/null 2>&1 &");
    }
}

header("HTTP/1.1 202 Accepted");
die("Brewing audio...");