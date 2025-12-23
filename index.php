<?php

// Config - Keep this server-side only!
$config_file = 'backend/config.php';

if (!file_exists($config_file)) {
    header('Location: configurator/index.php');
    exit;
}

include $config_file;

if (empty($JELLYFIN_URL) || empty($API_KEY) || empty($USER_ID)) {
    header('Location: configurator/index.php');
    exit;
}

$CONFIG = [
    'jellyfin_url' => $JELLYFIN_URL,
    'api_key' => $API_KEY,
    'user_id' => $USER_ID
];

$view = isset($_GET['view']) ? $_GET['view'] : 'Library';
$type = isset($_GET['type']) ? $_GET['type'] : 'Movie';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$limit = 24; // 24 items per page is a safe number for Wii U RAM
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cappuccino -- <?php echo $view; ?></title>
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <?php
    include 'components/header.php';
    ?>

    <div id="app">
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