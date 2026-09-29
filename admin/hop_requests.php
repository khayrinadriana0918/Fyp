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
ON ar.staff_id = h.staff_id

INNER JOIN users u
ON h.user_id = u.user_id

INNER JOIN category c
ON ar.category_id= c.category_id

ORDER BY ar.ar_submission_date DESC
";

$stmt= $pdo->prepare($query);
$stmt->execute();

$reqs= $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table>

    <thead>
        <tr>
            <th>Request ID</th>
            <th>HoP Name</th>
            <th>Staff ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Submitted</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($reqs as $request): ?>

            <tr
                class="request-row"
                onclick="window.location.href=
                'req_details.php?id=<?= urlencode($request['ar_request_id']); ?>'">

                <td>
                    <?= htmlspecialchars($request['ar_request_id']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['requester_name']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['requester_id']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['ar_title']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['category_name']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['ar_priority']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['ar_stats']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['ar_submission_date']); ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </tbody>

</table>
