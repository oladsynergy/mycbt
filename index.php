<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

include 'includes/header.php';

$file_path = 'pages/' . $page . '.php';
if (file_exists($file_path)) {
    include $file_path;
} else {
    include 'pages/404.php';
}

include 'includes/footer.php';
?>