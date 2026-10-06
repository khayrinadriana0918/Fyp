<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

$userStmt = $pdo->prepare("
SELECT name
FROM users
WHERE user_id = :user_id");

$userStmt->execute(['user_id' => $userId]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("User information not found.");
}

$search = trim($_GET['search'] ?? '');
$priority = $_GET['priority'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';


$query = "
SELECT
    r.*,
    c.category_name

FROM request r

INNER JOIN student s
    ON r.student_id = s.student_id

INNER JOIN category c
    ON r.category_id = c.category_id

WHERE s.user_id = :user_id
";


$params = [
    ':user_id' => $userId
];


/* SEARCH */
if ($search !== '') {

    $query .= "
        AND (
            CAST(r.request_id AS CHAR) LIKE :search
            OR r.title LIKE :search
        )
    ";

    $params[':search'] =
        '%' . $search . '%';
}


/* PRIORITY */
if ($priority !== '') {

    $query .= "
        AND r.priority = :priority
    ";

    $params[':priority'] = $priority;
}


/* STATUS */
if ($status !== '') {

    $query .= "
        AND r.stats = :status
    ";

    $params[':status'] = $status;
}


/* CATEGORY */
if ($category !== '') {

    $query .= "
        AND r.category_id = :category_id
    ";

    $params[':category_id'] = $category;
}


/* SORT */
switch ($sort) {

    case 'oldest':
        $query .= "
            ORDER BY r.submission_date ASC
        ";
        break;
    case 'title':
        $query .= "
            ORDER BY r.title ASC
        ";
        break;
    case 'newest':
    default:
        $query .= "
            ORDER BY r.submission_date DESC
        ";
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
    <title>Student Requests | SIMSAP</title>
    <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../CSS/req-table.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../CSS/popup.css?v=<?= time(); ?>">

    <script
        src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js">
    </script>

</head>

<body>
    <div class="layout">
        <!-- =====================================================
         HEADER
    ====================================================== -->
        <header>
            <div class="header-top">
                <div class="system-name">
                    <h1>
                        SIMSAP - Student Issue Management System
                        for Academic Programme
                    </h1>
                </div>
                <div class="header-user">
                    <span class="header-name">
                        <?= htmlspecialchars($userInfo['name']); ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- =====================================================
         LEFT SIDEBAR
    ====================================================== -->
        <aside class="sidebar">
            <nav>
                <ul>
                    <li>
                        <a href="student_dashboard.php">
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="../userProfile.php">User Profile</a>
                    </li>
                    <li>
                        <a href="student_hop_requests.php" class="programme-required">
                            Submit Request to Head of Programme
                        </a>
                    </li>
                    <li>
                        <a href="student_requests.php" class="programme-required active">
                            My Requests
                        </a>
                    </li>
                    <li>
                        <a href="../userManual.html">
                            User Manual
                        </a>
                    </li>
                    <li>
                        <a href="../faq.html">
                            FAQ
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- =====================================================
         TABLE REQUESTS
    ====================================================== -->
        <section class="request-section">
            <div class="section-header">
                <h2>My Requests</h2>
            </div>
            <div class="request-content">
                <div class="request-table-container">
                    <table class="request-table">

                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Semester</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th>Date Resolved</th>
                                <th>Priority</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($reqs as $request): ?>
                                <?php

                                //  Convert status into CSS class.

                                $statusClass =
                                    strtolower(
                                        str_replace(
                                            ' ',
                                            '-',
                                            $request['stats']
                                        )
                                    );
                                ?>

                                <tr
                                    class="request-row"
                                    data-request-type="student"

                                    data-request-id="<?= htmlspecialchars($request['request_id']); ?>"
                                    data-requester-name="<?= htmlspecialchars($userInfo['name']); ?>"
                                    data-requester-id="<?= htmlspecialchars($request['student_id']); ?>"
                                    data-semester="<?= htmlspecialchars($request['semester']); ?>"
                                    data-title="<?= htmlspecialchars($request['title']); ?>"
                                    data-category="<?= htmlspecialchars($request['category_name']); ?>"
                                    data-category-id="<?= htmlspecialchars($request['category_id']); ?>"
                                    data-priority="<?= htmlspecialchars($request['priority']); ?>"
                                    data-status="<?= htmlspecialchars($request['stats']); ?>"
                                    data-description="<?= htmlspecialchars($request['description']); ?>"
                                    data-file="<?= htmlspecialchars($request['request_file']); ?>"
                                    data-feedback="<?= htmlspecialchars($request['feedback'] ?? ''); ?>"
                                    data-submitted-date="<?= htmlspecialchars($request['submission_date']); ?>"
                                    data-resolved-date="<?= htmlspecialchars($request['resolved_date']); ?>">

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
                                        <span class="status status-<?= htmlspecialchars($statusClass); ?>">
                                            <?= htmlspecialchars($request['stats']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($request['submission_date']); ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($request['resolved_date']); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($request['priority']); ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>

                <aside class="dashboard-filter">
                    <?php
                    include __DIR__ . '/../inc_reuse/filter.php';
                    ?>
                </aside>

            </div>

        </section>

        <?php include __DIR__ . '/../inc_reuse/requester_popup.php'; ?>



    </div>
    <script src="../JS/requesterPopup.js"></script>
</body>

</html>