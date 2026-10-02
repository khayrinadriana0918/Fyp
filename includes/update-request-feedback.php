<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/notification.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$userId = $_SESSION['user_id'];

$requestId = $_POST['request_id'] ?? '';
$requestType = $_POST['request_type'] ?? '';
$feedback = trim($_POST['feedback'] ?? '');

if ($requestType !== 'student') {
    die("Invalid request type.");
}

if ($requestId === '' || $feedback === '') {
    die("Feedback cannot be empty.");
}


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

// make sure request belong to head of programme
$checkStmt = $pdo->prepare("
    SELECT
    r.request_id,
    r.feedback,
    s.user_id AS student_user_id

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

$allowedRequest = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$allowedRequest) {
    die("Access denied.");
}

// save feedback
$stmt = $pdo->prepare("
    UPDATE request
    SET feedback = :feedback
    WHERE request_id = :request_id
");

$stmt->execute([
    ':feedback' => $feedback,
    ':request_id' => $requestId
]);

if ($allowedRequest['feedback'] !== $feedback) {

    $oldFeedback =
        !empty($allowedRequest['feedback'])
        ? $allowedRequest['feedback']
        : 'No feedback';

    createNotification(
        $pdo,
        $allowedRequest['student_user_id'],
        'Feedback for Request #' .
        $requestId .
        ' changed: "' .
        $oldFeedback .
        '" → "' .
        $feedback .
        '".'
    );
}

echo "success";
