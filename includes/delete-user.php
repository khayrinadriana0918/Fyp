<?php

require_once 'config.php';
require_once __DIR__ . '/database.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$adminUserId =
    $_SESSION['user_id'];


/* =========================================
   CHECK ADMIN
========================================= */

$adminStmt = $pdo->prepare("
    SELECT admin_code
    FROM administrator
    WHERE user_id = :user_id
");

$adminStmt->execute([
    ':user_id' => $adminUserId
]);

$admin =
    $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Access denied.");
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}


$targetUserId =
    $_POST['user_id'] ?? '';

if ($targetUserId === '') {
    die("User ID is required.");
}


/* =========================================
   ADMIN CANNOT DELETE THEMSELVES
========================================= */

if ((string)$targetUserId === (string)$adminUserId) {
    die("You cannot delete your own account.");
}


/* =========================================
   GET USER ROLE
========================================= */

$userStmt = $pdo->prepare("
    SELECT
        user_id,
        role

    FROM users
    WHERE user_id = :user_id
");

$userStmt->execute([
    ':user_id' => $targetUserId
]);

$account =
    $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    die("User account not found.");
}


/* =========================================
   STUDENT
========================================= */

if ($account['role'] === 'student') {

    $studentStmt = $pdo->prepare("
        SELECT student_id
        FROM student
        WHERE user_id = :user_id
    ");

    $studentStmt->execute([
        ':user_id' => $targetUserId
    ]);

    $student =
        $studentStmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {

        $requestStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM request
            WHERE student_id = :student_id
        ");

        $requestStmt->execute([
            ':student_id' =>
                $student['student_id']
        ]);

        if ($requestStmt->fetchColumn() > 0) {

            die("This Student account cannot be deleted because it has existing requests.");
        }
    }
}


/* =========================================
   HEAD OF PROGRAMME
========================================= */

elseif (
    $account['role'] === 'head_of_programme'
) {

    $hopStmt = $pdo->prepare("
        SELECT staff_id
        FROM head_of_programme
        WHERE user_id = :user_id
    ");

    $hopStmt->execute([
        ':user_id' => $targetUserId
    ]);

    $hop =
        $hopStmt->fetch(PDO::FETCH_ASSOC);

    if ($hop) {
        $requestStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM admin_request
            WHERE staff_id = :staff_id
        ");

        $requestStmt->execute([
            ':staff_id' =>
                $hop['staff_id']
        ]);

        if ($requestStmt->fetchColumn() > 0) {

            die("This Head of Programme account cannot be deleted because it has existing requests.");
        }
    }
}


/* =========================================
   DELETE ACCOUNT
========================================= */

try {
    $pdo->beginTransaction();

    if ($account['role'] === 'student') {

        $roleDelete = $pdo->prepare("
            DELETE FROM student
            WHERE user_id = :user_id
        ");

    } elseif ($account['role'] === 'head_of_programme') {

        $roleDelete = $pdo->prepare("
            DELETE FROM head_of_programme
            WHERE user_id = :user_id
        ");

    } elseif ($account['role'] === 'system_admin') {

        $roleDelete = $pdo->prepare("
            DELETE FROM administrator
            WHERE user_id = :user_id
        ");

    } else {
        throw new Exception("Invalid user role.");
    }


    $roleDelete->execute([
        ':user_id' => $targetUserId
    ]);


    $userDelete = $pdo->prepare("
        DELETE FROM users
        WHERE user_id = :user_id
    ");

    $userDelete->execute([
        ':user_id' => $targetUserId
    ]);
    $pdo->commit();


} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Unable to delete user account.");
}


header("Location: ../admin/database_management.php");

exit();