<?php

// Config - Keep this server-side only!
$config_file = 'backend/config.php';

if (!file_exists($config_file)) {
    header('Location: _Configurator.php');
    exit;
}

require_once $config_file;

if (empty($JELLYFIN_URL) || empty($API_KEY) || empty($USER_ID) || empty($ffmpeg)) {
    header('Location: _Configurator.php');
    exit;
}

$view = isset($_GET['view']) ? $_GET['view'] : 'Library';
$type = isset($_GET['type']) ? $_GET['type'] : 'Movie';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$limit = 24; // 24 items per page is a safe number for Wii U RAM

$ua = $_SERVER['HTTP_USER_AGENT'];

$isWiiU = strpos($ua, 'WiiU') !== false;
$is3DS  = strpos($ua, 'Nintendo 3DS') !== false || strpos($ua, '3DS') !== false;

$isSafari = (
    strpos($ua, 'Safari') !== false &&
    strpos($ua, 'Chrome') === false &&
    strpos($ua, 'Edg') === false &&
    strpos($ua, 'OPR') === false // Opera
);

$isLegacy = false;

if (
    $isWiiU ||
    $is3DS ||
    $isSafari ||
    strpos($ua, 'MSIE') !== false ||
    strpos($ua, 'Trident/') !== false
) {
    $isLegacy = true;
}

// Spoof check for debugging
if (isset($_GET['style_debug'])) {
    if ($_GET['style_debug'] === '3ds') {
        $is3DS = true;
    } elseif ($_GET['style_debug'] === 'wiiu') {
        $isWiiU = true;
    } elseif ($_GET['style_debug'] === 'legacy') {
        $isLegacy = true;
    }
}

// Use this to load the right stylesheet
$cssFile_a = $isLegacy ? "cafe-legacy.css" : "cafe-modern.css";
$cssFile = $is3DS ? "cafe-legacy.css" : $cssFile_a;

if ($is3DS) {
    header('Location: https://google.com');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cappuccino -- <?php echo $view; ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo $cssFile; ?>">
</head>
<body>
    <?php
    include 'components/header.php';
    ?>

    <div id="app">
        <?php
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @get_headers($CONFIG['jellyfin_url'], 1, $context);
        if ($response === false || !preg_match('/^HTTP\/\d\.\d [123]\d{2}/', $response[0])) {
            include 'err/server_down.php';
            exit;
        }

        ?>
        <?php 
            // Get all available views from the views folder
            $views_dir = 'views';
            $available_views = [];
            
            if (is_dir($views_dir)) {
            foreach (scandir($views_dir) as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $available_views[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
            }
            
            // Route to the correct view
            if (in_array(strtolower($view), array_map('strtolower', $available_views))) {
            // Find the matching file (case-insensitive)
            $view_file = null;
            foreach ($available_views as $v) {
                if (strtolower($v) === strtolower($view)) {
                $view_file = $v;
                break;
                }
            }
            include "views/{$view_file}.php";
            } elseif (strtolower($view) === '') { // empty.
                include "views/home.php";
            } else {
                include "err/404.php";
            }
        ?>
    </div>

    <?php
    include 'components/footer.php';
    ?>
</body>
</html>