<?php

require_once '../includes/config.php';

require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();

}

if (!isset($_GET['id'])) {
    die("Request ID is missing.");
}

$requestId = $_GET['id'];

$query = "
    SELECT
        ar.*,
        h.staff_id AS requester_id,
        u.name AS requester_name,
        c.category_name
    FROM admin_request ar
    INNER JOIN head_of_programme h
        ON ar.staff_id = h.staff_id
    INNER JOIN users u
        ON h.user_id = u.user_id
    INNER JOIN category c
        ON ar.category_id = c.category_id
    WHERE ar.ar_request_id = :request_id
";

$stmt= $pdo->prepare($query);

$stmt->execute([':request_id'=> $requestId]);

$request =$stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details | SIMSAP</title>
</head>
<body>
    <h1>Request Details</h1>

    <p>
        <strong>Request ID:</strong>

        <?= htmlspecialchars(
            $request['ar_request_id']
        ); ?>
    </p>


    <p>
        <strong>Submitted By:</strong>

        <?= htmlspecialchars(
            $request['requester_name']
        ); ?>
    </p>


    <p>
        <strong>Staff ID:</strong>

        <?= htmlspecialchars(
            $request['requester_id']
        ); ?>
    </p>


    <p>
        <strong>Title:</strong>

        <?= htmlspecialchars(
            $request['ar_title']
        ); ?>
    </p>


    <p>
        <strong>Category:</strong>

        <?= htmlspecialchars(
            $request['category_name']
        ); ?>
    </p>


    <p>
        <strong>Priority:</strong>

        <?= htmlspecialchars(
            $request['ar_priority']
        ); ?>
    </p>


    <p>
        <strong>Status:</strong>

        <?= htmlspecialchars(
            $request['ar_stats']
        ); ?>
    </p>


    <p>
        <strong>Labels:</strong>

        <?= htmlspecialchars(
            $request['ar_label']
        ); ?>
    </p>


    <div>
        <strong>Description:</strong>
        <p>
            <?= nl2br(
                htmlspecialchars(
                    $request['ar_description']
                )
            ); ?>
        </p>
    </div>


    <?php if (!empty($request['ar_request_file'])): ?>

        <p>
            <strong>Attachment:</strong>

            <a href="../uploads/admin_hop/<?= rawurlencode(
                    $request['ar_request_file']); ?>"target="_blank">
                View Attachment
            </a>
        </p>

    <?php else: ?>

        <p>
            <strong>Attachment:</strong>
            No attachment
        </p>
    <?php endif; ?>

    <p>
        <strong>Submitted:</strong>
        <?= htmlspecialchars($request['ar_submission_date']); ?>
    </p>
    <a href="admin_requests.php">Back to Requests</a>

</body>
</html>