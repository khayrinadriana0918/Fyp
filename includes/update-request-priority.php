<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$userId = $_SESSION['user_id'];

$requestId = $_POST['request_id'] ?? '';
$requestType = $_POST['request_type'] ?? '';
$priority = $_POST['priority'] ?? '';


$allowedPriorities = [
    'Low',
    'Medium',
    'High',
    'Urgent'
];

if (!in_array($priority, $allowedPriorities, true)) {
    die("Invalid priority.");
}


/* ========================================
   STUDENT → HOP REQUEST
======================================== */

if ($requestType === 'student') {

    $roleStmt = $pdo->prepare("
        SELECT staff_id, programme_id
        FROM head_of_programme
        WHERE user_id = :user_id
    ");

    $roleStmt->execute([
        ':user_id' => $userId
    ]);

    $hop = $roleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$hop) {
        die("Access denied.");
    }

    $checkStmt = $pdo->prepare("
        SELECT r.request_id
        FROM request r

        INNER JOIN student s
            ON r.student_id = s.student_id

        WHERE r.request_id = :request_id
        AND s.programme_id = :programme_id
    ");

    $checkStmt->execute([
        ':request_id' => $requestId,
        ':programme_id' => $hop['programme_id']
    ]);

    $allowedRequest =
        $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$allowedRequest) {
        die("Access denied.");
    }


    // Update priority
    $stmt = $pdo->prepare("
        UPDATE request

        SET priority = :priority

        WHERE request_id = :request_id
    ");

    $stmt->execute([
        ':priority' => $priority,
        ':request_id' => $requestId
    ]);

    exit("success");
}

die("Invalid request type.");