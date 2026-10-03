<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/notification.php';

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

    $checkStmt= $pdo->prepare("
    SELECT
    ar.ar_request_id,
    ar.ar_stats,
    h.user_id AS hop_user_id
    
    FROM admin_request ar
    
    INNER JOIN head_of_programme h
    ON ar.staff_id = h.staff_id
    
    WHERE ar.ar_request_id = :request_id
    ");
    $checkStmt->execute([
        ':request_id'=> $requestId
    ]);

    $allowedRequest=
    $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$allowedRequest) {
        die("Request not found.");
    }
    
    $query = "
    UPDATE admin_request
    SET ar_stats=:status
    WHERE ar_request_id=:request_id";

}

// =====================
// student-> hop request
// =====================

elseif ($requestType === 'student') {

    // Check that logged-in user is a HoP
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
    SELECT
    r.request_id,
    r.stats,
    s.user_id AS student_user_id
    FROM request r
    
    INNER JOIN student s
    ON r.student_id=s.student_id
    
    WHERE r.request_id=:request_id
    AND s.programme_id=:programme_id"
    );
    $checkStmt->execute([
        ':request_id' => $requestId,
        ':programme_id' => $hop['programme_id']
    ]);
    $allowedRequest = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$allowedRequest) {
        die("Access denied.");
    }

    if ($status === 'Completed') {
        $query = "
        UPDATE request
        SET stats =:status,
        resolved_date=NOW()
        WHERE request_id=:request_id
        ";
    } else {
        $query = "
        UPDATE request
        SET
         stats=:status,
         resolved_date=NULL
        WHERE request_id=:request_id
        ";
    }

    $returnPage =
        "../hop/hop_dashboard.php?id=" . urlencode($requestId);
} else {
    die("Inavlid request type.");
}

$stmt = $pdo->prepare($query);

$stmt->execute([
    ':status' => $status,
    ':request_id' => $requestId
]);
if ($requestType === 'administrator') {
    exit("success");
}
if (
    $requestType === 'student' &&
    $allowedRequest['stats'] !== $status
) {

    createNotification(
        $pdo,
        $allowedRequest['student_user_id'],
        'Request #' .
        $requestId .
        ' status changed: ' .
        $allowedRequest['stats'] .
        ' → ' .
        $status .
        '.'
    );
}

header("Location: " . $returnPage);
exit();
