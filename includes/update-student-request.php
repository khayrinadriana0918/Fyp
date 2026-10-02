<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/notification.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Access denied.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];

$requestId = $_POST['request_id'] ?? '';
$semester = $_POST['semester'] ?? '';
$title = trim($_POST['title'] ?? '');
$label = trim($_POST['label'] ?? '');
$categoryId = $_POST['c_id'] ?? '';
$description = trim($_POST['desc'] ?? '');

if (
    $requestId === '' ||
    $semester === '' ||
    $title === '' ||
    $categoryId === '' ||
    $description === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please complete all required fields.'
    ]);
    exit();
}
// CHECK REQUEST BELONGS TO LOGGED-IN STUDENT

$checkStmt = $pdo->prepare("
    SELECT
    r.request_id,
    r.semester,
    r.title,
    r.label,
    r.category_id,
    r.description,
    r.request_file
    FROM request r

    INNER JOIN student s
        ON r.student_id = s.student_id

    WHERE r.request_id = :request_id
    AND s.user_id = :user_id
");

$checkStmt->execute([
    ':request_id' => $requestId,
    ':user_id' => $userId
]);

$request = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    echo json_encode([
        'success' => false,
        'message' => 'Request not found or access denied.'
    ]);
    exit();
}
// KEEP EXISTING FILE BY DEFAULT
$fileName = $request['request_file'];

// REPLACE FILE IF STUDENT UPLOADS A NEW ONE
if (
    isset($_FILES['request_file']) &&
    $_FILES['request_file']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['request_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'success' => false,
            'message' => 'File upload failed.'
        ]);
        exit();
    }

    $allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'pdf'
    ];

    $originalName = $_FILES['request_file']['name'];

    $extension = strtolower(
        pathinfo($originalName, PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $allowedExtensions, true)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid file type.'
        ]);
        exit();
    }

    $fileName =
        uniqid('request_', true) .
        '.' .
        $extension;

    $uploadDirectory =
        __DIR__ . '/../uploads/hop_student/';

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0755, true);
    }

    if (!move_uploaded_file(
        $_FILES['request_file']['tmp_name'],
        $uploadDirectory . $fileName
    )) {
        echo json_encode([
            'success' => false,
            'message' => 'Unable to save uploaded file.'
        ]);
        exit();
    }
}
if ($request['request_file'] !== $fileName) {
    $changes[] = 'Attachment';
}


// =========================================================
// UPDATE REQUEST
// =========================================================
$changes = [];

if ((string)$request['semester'] !== (string)$semester) {
    $changes[] = 'Semester';
}

if ($request['title'] !== $title) {
    $changes[] = 'Title';
}

if ($request['label'] !== $label) {
    $changes[] = 'Label';
}

if ((string)$request['category_id'] !== (string)$categoryId) {
    $changes[] = 'Category';
}

if ($request['description'] !== $description) {
    $changes[] = 'Description';
}

$updateStmt = $pdo->prepare("
    UPDATE request

    SET
        semester = :semester,
        title = :title,
        label = :label,
        category_id = :category_id,
        description = :description,
        request_file = :request_file

    WHERE request_id = :request_id
");

$updateStmt->execute([
    ':semester' => $semester,
    ':title' => $title,
    ':label' => $label,
    ':category_id' => $categoryId,
    ':description' => $description,
    ':request_file' => $fileName,
    ':request_id' => $requestId
]);
// for notifications
$hopStmt = $pdo->prepare("
    SELECT h.user_id
    FROM head_of_programme h

    INNER JOIN student s
        ON h.programme_id = s.programme_id

    WHERE s.user_id = :student_user_id
");

$hopStmt->execute([
    ':student_user_id' => $userId
]);

$hop = $hopStmt->fetch(PDO::FETCH_ASSOC);
if ($hop) {
    if ($hop && !empty($changes)) {
        $changedFields= implode(', ', $changes);
        createNotification(
            $pdo,
            $hop['user_id'],
            'Student updated Request #' . $requestId . ': ' .
                $changedFields . '.'
        );
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Request updated successfully.'
]);
