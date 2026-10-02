<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$priority = $_GET['priority'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';


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

WHERE 1 = 1
";


$params = [];


/* SEARCH */
if ($search !== '') {

    $query .= "
        AND (
            CAST(ar.ar_request_id AS CHAR) LIKE :search
            OR ar.ar_title LIKE :search
            OR u.name LIKE :search
            OR h.staff_id LIKE :search
        )
    ";

    $params[':search'] =
        '%' . $search . '%';
}


/* PRIORITY */
if ($priority !== '') {
    $query .= "AND ar.ar_priority = :priority";

    $params[':priority'] = $priority;
}


/* STATUS */
if ($status !== '') {
    $query .= "AND ar.ar_stats = :status";
    $params[':status'] = $status;
}


/* CATEGORY */
if ($category !== '') {
    $query .= "AND ar.category_id = :category_id";
    $params[':category_id'] = $category;
}


/* SORT */
switch ($sort) {

    case 'oldest':
        $query .= "ORDER BY ar.ar_submission_date ASC";

        break;

    case 'title':
        $query .= "ORDER BY ar.ar_title ASC";
        break;

    case 'newest':
    default:
        $query .= "ORDER BY ar.ar_submission_date DESC";
        break;
}


$stmt = $pdo->prepare($query);

$stmt->execute($params);

$reqs =
    $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Submitted HOP Requests | SIMSAP</title>

    <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= time(); ?>">

    <script
        src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js">
    </script>

</head>

<body>

    <div class="layout">
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
        <aside class="filter-sidebar">

            <?php
            include __DIR__ . '/../inc_reuse/filter.php';
            ?>

        </aside>

    </div>

</body>

</html>