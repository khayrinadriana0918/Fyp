<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$programmeId = $_POST['programme_id'] ?? '';

if (empty($programmeId)) {
    die("Please select a programme.");
}

$programmeStmt = $pdo->prepare("
    SELECT programme_id
    FROM programme
    WHERE programme_id = :programme_id
");

$programmeStmt->execute([
    ':programme_id' => $programmeId
]);

$programme = $programmeStmt->fetch(PDO::FETCH_ASSOC);

if (!$programme) {
    die("Invalid programme.");
}
$roleStmt = $pdo->prepare("
SELECT role
FROM users
WHERE user_id = :user_id");

$roleStmt->execute([
    ':user_id' => $userId
]);

$user = $roleStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}
if ($user['role'] === 'student') {
    $updateStmt = $pdo->prepare("
    UPDATE student
    SET programme_id = :programme_id
    WHERE user_id = :user_id
");

    $updateStmt->execute([
        ':programme_id' => $programmeId,
        ':user_id' => $userId
    ]);

    header("Location: ../Student/student_dashboard.php");
    exit();
} elseif ($user['role'] === 'head_of_programme') {
    $updateStmt = $pdo->prepare("
    UPDATE head_of_programme
    SET programme_id = :programme_id
    WHERE user_id = :user_id
");

    $updateStmt->execute([
        ':programme_id' => $programmeId,
        ':user_id' => $userId
    ]);

    header("Location: ../hop/hop_dashboard.php");
    exit();
}else{
    die("This account cannot select a programme.");
}
