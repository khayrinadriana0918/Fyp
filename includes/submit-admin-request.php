<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';

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
$ar_title = trim($_POST['title'] ?? '');
$ar_label = trim($_POST['label'] ?? '');
$category_id = trim($_POST['c_id'] ?? '');
$ar_priority = trim($_POST['priority'] ?? 'Low');
$ar_description = trim($_POST['desc'] ?? '');

if (
    $ar_title === '' ||
    $category_id === '' ||
    $ar_description === ''
) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit();
}

$stmt = $pdo->prepare("
SELECT staff_id
FROM head_of_programme
WHERE user_id = :user_id
");

$stmt->execute([
    ':user_id' => $user
]);

$hop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hop) {
    echo json_encode([
        'success' => false,
        'message' => 'Head of programme account not found'
    ]);
    exit();
}
$staff_id = $hop['staff_id'];

$ar_request_file = null;

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
    $ud = __DIR__ . '/../uploads/admin_hop/';

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

    $ar_request_file = $nfn;
}
$stmt = $pdo->prepare("
INSERT INTO admin_request(
staff_id,
category_id,
ar_title,
ar_label,
ar_priority,
ar_description,
ar_request_file
)VALUES
(
:staff_id,
:category_id,
:ar_title,
:ar_label,
:ar_priority,
:ar_description,
:ar_request_file
);");

//named parameters
$stmt->bindParam(":staff_id", $staff_id);
$stmt->bindParam(":category_id", $category_id);
$stmt->bindParam(":ar_title", $ar_title);
$stmt->bindParam(":ar_label", $ar_label);
$stmt->bindParam(":ar_priority", $ar_priority);
$stmt->bindParam(":ar_description", $ar_description);
$stmt->bindParam(":ar_request_file", $ar_request_file);


$stmt->execute();

echo json_encode([
    'success' => true,
    'message' => 'Request submitted successfully.'
]);
exit();
