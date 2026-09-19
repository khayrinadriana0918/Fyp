<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$query="
SELECT
ar.*,
h.staff_id AS requester_id,
u.name AS requester_name,
c.category_name
FROM admin_request ar

INNER JOIN head_of_programme h
ON h.user_id= u.user_id

INNER JOIN category c
ON ar.category_id= c.category_id

ORDER BY ar.ar_submission_date DESC
";

$stmt= $pdo->prepare($query);
$stmt->execute();

$reqs= $stmt->fetchAll(PDO::FETCH_ASSOC);
?>