<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

/* Make sure logged-in user is Admin */

$adminStmt = $pdo->prepare("
    SELECT admin_code
    FROM administrator
    WHERE user_id = :user_id
");

$adminStmt->execute([
    ':user_id' => $userId
]);

$admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Access denied.");
}


/* Generate unique code */

do {

    $letters =
        chr(random_int(65, 90)) .
        chr(random_int(65, 90));

    $numbers =
        str_pad(
            (string) random_int(0, 99999),
            5,
            '0',
            STR_PAD_LEFT
        );

    $registrationCode =
        $letters . $numbers;

    $checkStmt = $pdo->prepare("
        SELECT code_id
        FROM admin_registration_code
        WHERE registration_code = :registration_code
    ");

    $checkStmt->execute([
        ':registration_code' => $registrationCode
    ]);

    $codeExists =
        $checkStmt->fetch(PDO::FETCH_ASSOC);

} while ($codeExists);


/* Save code */

$stmt = $pdo->prepare("
    INSERT INTO admin_registration_code (
        registration_code,
        created_by
    )
    VALUES (
        :registration_code,
        :created_by
    )
");

$stmt->execute([
    ':registration_code' => $registrationCode,
    ':created_by' => $userId
]);

$_SESSION['generated_admin_code'] =
    $registrationCode;

header("Location: ../admin/database_management.php");
exit();