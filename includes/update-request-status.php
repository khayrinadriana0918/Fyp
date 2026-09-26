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

$requestId = $_POST['request_id'] ?? '';
$requestType = $_POST['request_type'] ?? '';
$status = $_POST['status'] ?? '';

$allowedStatuses = [
    'Pending',
    'In Progress',
    'Completed',
    'Rejected'
];
if (!in_array($status, $allowedStatuses, true)) {
    die("Invalid Status");
}
// =====================
// hop-> admin request
// =====================

if ($requestType === 'administrator') {
    // Check that logged-in user is Admin
    $roleStmt = $pdo->prepare("
        SELECT admin_code
        FROM administrator
        WHERE user_id = :user_id
    ");

    $roleStmt->execute([
        ':user_id' => $userId
    ]);

    $admin = $roleStmt->fetch(PDO::FETCH_ASSOC);


    if (!$admin) {
        die("Access denied.");
    }

    $query = "
    UPDATE admin_request
    SET ar_stats=:status
    WHERE ar_request_id=:request_id";

    $returnPage =
        "../admin/req_details.php?id=" . urlencode($requestId);
}

// =====================
// student-> hop request
// =====================

elseif ($requestType === 'student') {

    // Check that logged-in user is a HoP
    $roleStmt = $pdo->prepare("
        SELECT staff_id
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

    $query = "
    UPDATE request
    SET stats=:status
    WHERE request_id=:request_id";

    $returnPage =
        "../hop/req_details.php?id=" . urlencode($requestId);
} else {
    die("Inavlid request type.");
}

$stmt = $pdo->prepare($query);

$stmt->execute([
    ':status' => $status,
    ':request_id' => $requestId
]);

header("Location: " . $returnPage);
exit();
