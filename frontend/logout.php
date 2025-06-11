<?php
require_once 'includes/api_functions.php';
if (!isLoggedIn()) {
    header('Location: /pengaduan/frontend/index.php');
    exit;
}
logoutUser();
header('Location: /pengaduan/frontend/index.php');
exit;
?>