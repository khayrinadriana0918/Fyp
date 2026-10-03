<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];


/* =========================================
CHECK ADMIN
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

if ($categoryId === '') {
    die("Category ID is required.");
}


/* =========================================
CHECK STUDENT REQUESTS
========================================= */

$requestStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM request
    WHERE category_id = :category_id
");

$requestStmt->execute([
    ':category_id' => $categoryId
]);

$studentRequestCount =
    $requestStmt->fetchColumn();


/* =========================================
CHECK HOP-> ADMIN REQUESTS
========================================= */

$adminRequestStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM admin_request
    WHERE category_id = :category_id
");

$adminRequestStmt->execute([
    ':category_id' => $categoryId
]);

$adminRequestCount =
    $adminRequestStmt->fetchColumn();


if (
    $studentRequestCount > 0 ||
    $adminRequestCount > 0
) {
    die("This category cannot be deleted because it is already used by requests.");
}


/* =========================================
DELETE CATEGORY
========================================= */

$deleteStmt = $pdo->prepare("
    DELETE FROM category
    WHERE category_id = :category_id
");

$deleteStmt->execute([
    ':category_id' => $categoryId
]);


header(
    "Location: ../admin/database_management.php"
);

exit();
