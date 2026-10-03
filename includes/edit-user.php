<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.php");
    exit();
}

$adminUserId =
    $_SESSION['user_id'];


/* CHECK ADMIN */

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


$userId =
    $_POST['user_id'] ?? '';

$name =
    trim($_POST['name'] ?? '');

$email =
    trim($_POST['email'] ?? '');


if (
    $userId === '' ||
    $name === '' ||
    $email === ''
) {
    die("User information is required.");
}


if (!filter_var(
    $email,
    FILTER_VALIDATE_EMAIL
)) {
    die("Invalid email address.");
}


/* CHECK USER EXISTS */

$checkStmt = $pdo->prepare("
    SELECT user_id
    FROM users
    WHERE user_id = :user_id
");

$checkStmt->execute([
    ':user_id' => $userId
]);

if (!$checkStmt->fetch()) {
    die("User account not found.");
}


/* CHECK EMAIL DUPLICATE */

$emailStmt = $pdo->prepare("
    SELECT user_id
    FROM users
    WHERE email = :email
    AND user_id != :user_id
");

$emailStmt->execute([
    ':email' => $email,
    ':user_id' => $userId
]);

if ($emailStmt->fetch()) {
    die("Email already registered.");
}


/* UPDATE USER */

$updateStmt = $pdo->prepare("
    UPDATE users

    SET
        name = :name,
        email = :email

    WHERE user_id = :user_id
");

$updateStmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':user_id' => $userId
]);


header("Location: ../admin/database_management.php");

exit();