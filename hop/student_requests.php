<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$query="
SELECT
r.*,
s.student_id AS requester_id,
u.name AS requester_name,
c.category_name
FROM request r

INNER JOIN student s
ON r.student_id = s.student_id

INNER JOIN users u
ON s.user_id = u.user_id

INNER JOIN category c
ON r.category_id= c.category_id

ORDER BY r.r_submission_date DESC
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
                'req_details.php?id=<?= urlencode($request['r_request_id']); ?>'">

                <td>
                    <?= htmlspecialchars($request['request_id']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['requester_name']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['requester_id']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['title']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['category_name']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['priority']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['stats']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['submission_date']); ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </tbody>

</table>
