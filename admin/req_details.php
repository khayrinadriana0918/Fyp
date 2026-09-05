<?php

require_once '../includes/config.php';

require_once __DIR__ . '/../includes/database.php';

if (!isset($_GET['id'])) {
    die("Request ID is missing.");
}

$requestId = $_GET['id'];


?>