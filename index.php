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
$isWiiU = (strpos($ua, 'WiiU') !== false || strpos($ua, 'NintendoBrowser') !== false);

// Use this to load the right stylesheet
$cssFile = $isWiiU ? "cafe-legacy.css" : "cafe-modern.css";
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