<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];


$adminStmt = $pdo->prepare("
    SELECT admin_code
    FROM administrator
    WHERE user_id = :user_id
");

$adminStmt->execute([
    ':user_id' => $userId
]);

$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);


if (!$admin) {
    die("Access denied.");
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$categoryName = trim(
    $_POST['category_name'] ?? ''
);


if ($categoryName === '') {
    die("Category name is required.");
}


$checkStmt = $pdo->prepare("
    SELECT category_id
    FROM category
    WHERE category_name = :category_name
");

$checkStmt->execute([
    ':category_name' => $categoryName
]);


if ($checkStmt->fetch()) {
    die("Category already exists.");
}


$insertStmt = $pdo->prepare("
    INSERT INTO category (
        category_name
    )
    VALUES (
        :category_name
    )
");

$insertStmt->execute([
    ':category_name' => $categoryName
]);

header(
    "Location: ../admin/database_management.php"
);

exit();