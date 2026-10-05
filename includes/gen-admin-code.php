<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];


/* =========================================
   ONLY ADMIN CAN GENERATE CODE
========================================= */

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


/* =========================================
   ONLY POST REQUEST
========================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ../admin/database_management.php");
    exit();
}


/* =========================================
   GENERATE UNIQUE CODE
   2 CAPITAL LETTERS + 5 NUMBERS
========================================= */

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


    /* Check whether code already exists */

    $checkStmt = $pdo->prepare("
        SELECT code_id
        FROM admin_registration_code
        WHERE registration_code = :registration_code
    ");

    $checkStmt->execute([
        ':registration_code' => $registrationCode
    ]);

    $codeExists = $checkStmt->fetch(PDO::FETCH_ASSOC);
} while ($codeExists);


/* =========================================
   SAVE CODE
========================================= */

try {

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


    /* Store generated code for display */

    $_SESSION['generated_admin_code'] =
        $registrationCode;


    header(
        "Location: ../admin/database_management.php"
    );

    exit();
} catch (PDOException $e) {

    die("Unable to generate registration code.");
}
