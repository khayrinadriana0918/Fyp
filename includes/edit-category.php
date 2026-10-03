<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];


/* =========================================
ONLY ADMIN 
========================================= */

$adminStmt = $pdo->prepare("
    SELECT admin_code
    FROM administrator
    WHERE user_id = :user_id
");

$adminStmt->execute([
    ':user_id' => $userId
]);

$admin =
    $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Access denied.");
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}


$categoryId =
    $_POST['category_id'] ?? '';

$categoryName =
    trim($_POST['category_name'] ?? '');


if (
    $categoryId === '' ||
    $categoryName === ''
) {
    die("Category information is required.");
}


/* =========================================
   CHECK DUPLICATE NAME
========================================= */

$checkStmt = $pdo->prepare("
    SELECT category_id
    FROM category

    WHERE category_name = :category_name
    AND category_id != :category_id
");

$checkStmt->execute([
    ':category_name' => $categoryName,
    ':category_id' => $categoryId
]);

if ($checkStmt->fetch()) {
    die("Category name already exists.");
}


/* =========================================
   UPDATE
========================================= */

$updateStmt = $pdo->prepare("
    UPDATE category

    SET category_name = :category_name

    WHERE category_id = :category_id
");

$updateStmt->execute([
    ':category_name' => $categoryName,
    ':category_id' => $categoryId
]);


header(
    "Location: ../admin/database_management.php"
);

exit();
