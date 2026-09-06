<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';

header('Content-type:application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in'
    ]);
    exit();
}

$user = $_SESSION['user_id'];

$title = trim($_POST['title'] ?? '');
$label = trim($_POST['label'] ?? '');
$c_Id = trim($_POST['category_id'] ?? '');
$priority = trim($_POST['priority'] ?? 'Low');
$desc = trim($_POST['description'] ?? '');

if (
    $title === '' ||
    $c_Id === '' ||
    $desc === ''
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

$req_file = null;

if (!isset($_FILES['request_file']) && $_FILES['request_file']['error'] !== UPLOAD_ERR_NO_FILE) {
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
    $ud = __DIR__ . '/../uploads/admin_requests/';

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

    $req_file= $nfn;
}
$stmt=$pdo->prepare("
INSERT INTO admin_request(
staff_id,
category_id,
ar_title,
ar_label,
ar_description,
ar_request_file,
ar_priority 
)VALUES
(

);")
