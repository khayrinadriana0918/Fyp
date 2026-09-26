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

$updateStmt = $pdo->prepare("
    UPDATE student
    SET programme_id = :programme_id
    WHERE user_id = :user_id
");

$updateStmt->execute([
    ':programme_id' => $programmeId,
    ':user_id' => $userId
]);

$_SESSION['programme_id'] = $programmeId;

header("Location: ../Student/student_dashboard.php");
exit();