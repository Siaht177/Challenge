<?php 
// Ini setting 
ini_set("session.use_strict_mode", 1);
ini_set("session.use_only_cookies", 1);
ini_set("session.use_trans_sid", 0);
ini_set("session.cookie_httponly", "On");
ini_set("default_charshet", "utf-8");

// DB credentials.
define('DB_HOST','localhost:3306');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','sample');

// Site variables
define('SITE_NAME', 'Share file system');
define('UPLOAD_FOLDER', 'uploads/');
define('DOWNLOAD_URL', (isset($_SERVER['HTTPS']))?"https://":"http://" . $_SERVER['SERVER_NAME'] . "/download.php");

// List all pages need to navigation and include
$GLOBALS['pages'] = [
    'home',
    'user-detail',
    'update-profile',
    'change-password',
    'upload-file',
    'file-detail',
    'update-file',
    'my-folder',
    'trash',
    'dashboard'
];