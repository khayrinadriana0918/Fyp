<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/notification.php';

header('Content-type:application/json');
//avoid someone directy visits this php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
} //log in user only
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in'
    ]);
    exit();
}

$user = $_SESSION['user_id'];

//connect to name attributes
$semester = trim($_POST['semester'] ?? '');
$title = trim($_POST['title'] ?? '');
$category_id = trim($_POST['c_id'] ?? '');
$priority = trim($_POST['priority'] ?? 'Low');
$description = trim($_POST['desc'] ?? '');

if (
    $title === '' ||
    $category_id === '' ||
    $description === '' ||
    $semester === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit();
}
$semester = filter_var(
    $semester,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1, 'max_range' => 20]]
);
if ($semester === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid Semester'
    ]);
    exit();
}

$studStmt = $pdo->prepare("
SELECT student_id,
programme_id
FROM student
WHERE user_id = :user_id
");

$studStmt->execute([
    ':user_id' => $user
]);

$stud = $studStmt->fetch(PDO::FETCH_ASSOC);

if (!$stud) {
    echo json_encode([
        'success' => false,
        'message' => 'Student account not found'
    ]);
    exit();
}
if (empty($stud['programme_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please select your programme first'
    ]);
    exit();
}
$student_id = $stud['student_id'];

$request_file = null;

//file field exists AND user select a file, check if PHP receive the upload
if (
    isset($_FILES['request_file']) &&
    $_FILES['request_file']['error'] !== UPLOAD_ERR_NO_FILE
) {
    if ($_FILES['request_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'File upload failed'
        ]);
        exit();
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

    $originalName = $_FILES['request_file']['name'];

    $extension = strtolower(
        pathinfo($originalName, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $allowedExtensions, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Only JPG, JPEG, PNG, and PDF files are allowed'
        ]);
        exit();
    }
    // $newfilename
    $nfn = uniqid('request_', true) . '.' . $extension;

    // $uploaddirectory
    $ud = __DIR__ . '/../uploads/hop_student/';

    if (!is_dir($ud)) {
        mkdir($ud, 0755, true);
    }

    #$destination
    $dest = $ud . $nfn;

    if (!move_uploaded_file(
        $_FILES['request_file']['tmp_name'],
        $dest
    )) {
        echo json_encode([
            'success' => false,
            'message' => 'Unable to save uploaded file'
        ]);
        exit();
    }

    $request_file = $nfn;
}
$stmt = $pdo->prepare("
INSERT INTO request(
student_id,
semester,
category_id,
title,
priority,
description,
request_file
)VALUES
(
:student_id,
:semester,
:category_id,
:title,
:priority,
:description,
:request_file
);");

//named parameters
$stmt->bindParam(":student_id", $student_id);
$stmt->bindParam(":semester", $semester);
$stmt->bindParam(":category_id", $category_id);
$stmt->bindParam(":title", $title);
$stmt->bindParam(":priority", $priority);
$stmt->bindParam(":description", $description);
$stmt->bindParam(":request_file", $request_file);


$stmt->execute();

$requestId = $pdo->lastInsertId();

$hopStmt = $pdo->prepare("
    SELECT user_id
    FROM head_of_programme
    WHERE programme_id = :programme_id
");

$hopStmt->execute([
    ':programme_id' => $stud['programme_id']
]);

$hopUser = $hopStmt->fetch(PDO::FETCH_ASSOC);

if ($hopUser) {

    $message =
        'Student submitted new Request #' .
        $requestId .
        ': "' .
        $title .
        '".';

    if (!empty($request_file)) {
        $message .=
            ' Attachment: ' .
            $request_file .
            '.';
    }

    createNotification(
        $pdo,
        $hopUser['user_id'],
        $message
    );
}

echo json_encode([
    'success' => true,
    'message' => 'Request submitted successfully.'
]);
exit();
