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

$requestId =
    $_POST['request_id'] ?? '';

$requestType =
    $_POST['request_type'] ?? '';

$feedback =
    trim($_POST['feedback'] ?? '');


if (
    $requestId === '' ||
    $feedback === ''
) {
    die("Feedback cannot be empty.");
}


/* =========================================
   STUDENT -> HOP
========================================= */

if ($requestType === 'student') {

    $roleStmt = $pdo->prepare("
        SELECT staff_id, programme_id
        FROM head_of_programme
        WHERE user_id = :user_id
    ");

    $roleStmt->execute([
        ':user_id' => $userId
    ]);

    $hop =
        $roleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$hop) {
        die("Access denied.");
    }


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

    $allowedRequest =
        $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$allowedRequest) {
        die("Access denied.");
    }


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

    exit("success");
}


/* =========================================
   HOP -> ADMIN
========================================= */

if ($requestType === 'system_admin') {

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


    $checkStmt = $pdo->prepare("
        SELECT
            ar.ar_request_id,
            ar.ar_feedback,
            h.user_id AS hop_user_id

        FROM admin_request ar

        INNER JOIN head_of_programme h
            ON ar.staff_id = h.staff_id

        WHERE ar.ar_request_id = :request_id
    ");

    $checkStmt->execute([
        ':request_id' => $requestId
    ]);

    $allowedRequest =
        $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$allowedRequest) {
        die("Request not found.");
    }


    $stmt = $pdo->prepare("
        UPDATE admin_request

        SET ar_feedback = :feedback
        WHERE ar_request_id = :request_id
    ");

    $stmt->execute([
        ':feedback' => $feedback,
        ':request_id' => $requestId
    ]);


    if ($allowedRequest['ar_feedback'] !== $feedback) {

        $oldFeedback =
            !empty($allowedRequest['ar_feedback'])
            ? $allowedRequest['ar_feedback']
            : 'No feedback';

        createNotification(
            $pdo,
            $allowedRequest['hop_user_id'],
            'Feedback for Request #' .
            $requestId .
            ' changed: "' .
            $oldFeedback .
            '" → "' .
            $feedback .
            '".'
        );
    }
    exit("success");
}
die("Invalid request type.");