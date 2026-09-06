<?php

require_once 'config.php';
require_once __DIR__ . '/../database.php';

header('Content-type:application/json');

if($_SERVER['REQUEST_METHOD']!=='POST'){
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}
if(!isset($_SESSION['user_id'])){
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in'
    ]);
    exit();
}

$user = $_SESSION['user_id'];

$title=trim($_POST['title']??'');
$label=trim($_POST['label']??'');
$c_Id=trim($_POST['category_id']??'');
$priority=trim($_POST['priority']??'Low');
$desc=trim($_POST['description']??'');

if (
    $title === ''||
    $c_Id === ''||
    $desc === ''||
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit();
}

?>