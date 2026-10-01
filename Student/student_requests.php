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
c.category_name
FROM request r

INNER JOIN student s
ON r.student_id = s.student_id

INNER JOIN category c
ON r.category_id= c.category_id

WHERE s.user_id= :user_id
ORDER BY r.submission_date DESC
";

$stmt= $pdo->prepare($query);
$stmt->execute([
    'user_id'=> $_SESSION['user_id']
]);

$reqs= $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table>

    <thead>
        <tr>
            <th>Request ID</th>
            <th>Semester</th>
            <th>Student ID</th>
            <th>Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Date Submitted</th>
            <th>Priority</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($reqs as $request): ?>

            <tr
                class="request-row"
                data-request-id="<?= htmlspecialchars($request['request_id']); ?>">

                <td>
                    <?= htmlspecialchars($request['request_id']); ?>
                </td>

                <td>
                    <?= htmlspecialchars($request['semester']); ?>
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
