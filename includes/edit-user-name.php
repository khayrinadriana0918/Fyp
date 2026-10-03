<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.php");
    exit();
}

$adminUserId =
    $_SESSION['user_id'];


/* =========================================
   CHECK ADMIN
========================================= */

$adminStmt = $pdo->prepare("
    SELECT admin_code
    FROM administrator
    WHERE user_id = :user_id
");

$adminStmt->execute([
    ':user_id' => $adminUserId
]);

$admin =
    $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Access denied.");
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}


$targetUserId =
    $_POST['user_id'] ?? '';

$name =
    trim($_POST['name'] ?? '');


if (
    $targetUserId === '' ||
    $name === ''
) {
    die("User information is required.");
}


/* =========================================
   MAKE SURE USER EXISTS
========================================= */

$checkStmt = $pdo->prepare("
    SELECT user_id
    FROM users
    WHERE user_id = :user_id
");

$checkStmt->execute([
    ':user_id' => $targetUserId
]);

if (!$checkStmt->fetch()) {
    die("User account not found.");
}


/* =========================================
   UPDATE NAME
========================================= */

$updateStmt = $pdo->prepare("
    UPDATE users
    SET name = :name
    WHERE user_id = :user_id
");

$updateStmt->execute([
    ':name' => $name,
    ':user_id' => $targetUserId
]);


header(
    "Location: ../admin/database_management.php"
);

exit();
