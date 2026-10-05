<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

$userStmt = $pdo->prepare("
SELECT u.name,
h.staff_id
FROM users u

INNER JOIN head_of_programme h
ON u.user_id=h.user_id

WHERE u.user_id = :user_id");

$userStmt->execute(['user_id' => $userId]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("User information not found.");
}

if (empty($userInfo['programme_id'])) {
    header("Locatien:hop_dashboard.php");
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
    c.category_name

FROM admin_request ar

INNER JOIN head_of_programme h
    ON ar.staff_id = h.staff_id

INNER JOIN category c
    ON ar.category_id = c.category_id

WHERE h.user_id = :user_id
";


$params = [
    ':user_id' => $userId
];


/* SEARCH */
if ($search !== '') {

    $query .= "
        AND (
            CAST(ar.ar_request_id AS CHAR) LIKE :search
            OR ar.ar_title LIKE :search
        )
    ";

    $params[':search'] =
        '%' . $search . '%';
}


/* PRIORITY */
if ($priority !== '') {

    $query .= "
        AND ar.ar_priority = :priority
    ";

    $params[':priority'] = $priority;
}


/* STATUS */
if ($status !== '') {

    $query .= "
        AND ar.ar_stats = :status
    ";

    $params[':status'] = $status;
}


/* CATEGORY */
if ($category !== '') {

    $query .= "
        AND ar.category_id = :category_id
    ";

    $params[':category_id'] = $category;
}


/* SORT */
switch ($sort) {

    case 'oldest':
        $query .= "
            ORDER BY ar.ar_submission_date ASC
        ";
        break;
    case 'title':
        $query .= "
            ORDER BY ar.ar_title ASC
        ";
        break;
    case 'newest':
    default:
        $query .= "
            ORDER BY ar.ar_submission_date DESC
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
    <link rel="stylesheet" href="../CSS/popup.css">

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
                    <a href="../index.php">Log Out</a>
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
                        <a href="hop_Dashboard.php">Dashboard</a>
                    </li>
                    <li>
                        <a href="../userProfile.php">User Profile</a>
                    </li>
                    <li>
                        <a href="hop_admin_requests.php">Submit Request to Admin</a>
                    </li>
                    <li>
                        <a href="hop_requests.php">My Requests</a>
                    </li>
                    <li>
                        <a href="submittedStud.php" class="active">Students Requests</a>
                    </li>
                    <li>
                        <a href="hop_report.php">Administrative Report</a>
                    </li>
                    <li>
                        <a href="../userManual.html">User Manual</a>
                    </li>
                    <li>
                        <a href="../faq.html">FAQ</a>
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
            <div class="table-container">
                <table class="request-table">

                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Description</th>
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
                                        $request['ar_stats']
                                    )
                                );
                            ?>

                            <tr
                                class="request-row"
                                data-request-type="administrator"

                                data-request-id="<?= htmlspecialchars($request['ar_request_id']); ?>"
                                data-requester-name="<?= htmlspecialchars($userInfo['name']); ?>"
                                data-requester-id="<?= htmlspecialchars($userInfo['staff_id']); ?>"
                                data-semester=""
                                data-title="<?= htmlspecialchars($request['ar_title']); ?>"
                                data-category="<?= htmlspecialchars($request['category_name']); ?>"
                                data-category-id="<?= htmlspecialchars($request['category_id']); ?>"
                                data-label="<?= htmlspecialchars($request['ar_label']); ?>"
                                data-priority="<?= htmlspecialchars($request['ar_priority']); ?>"
                                data-status="<?= htmlspecialchars($request['ar_stats']); ?>"
                                data-description="<?= htmlspecialchars($request['ar_description']); ?>"
                                data-file="<?= htmlspecialchars($request['ar_request_file']); ?>"
                                data-feedback="<?= htmlspecialchars($request['ar_feedback'] ?? ''); ?>"
                                data-submitted-date="<?= htmlspecialchars($request['ar_submission_date']); ?>"
                                data-resolved-date="<?= htmlspecialchars($request['ar_resolved_date']); ?>">

                                <td>
                                    <?= htmlspecialchars($request['ar_request_id']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request['ar_title']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request['category_name']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request['ar_description']); ?>
                                </td>

                                <td>
                                    <span class="status status-<?= htmlspecialchars($statusClass); ?>">
                                        <?= htmlspecialchars($request['ar_stats']); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request['ar_submission_date']); ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($request['ar_resolved_date']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request['ar_priority']); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>
            </div>
        </section>

        <?php include __DIR__ . '/../inc_reuse/requester_popup.php'; ?>

        <aside class="filter-sidebar">
            <?php
            include __DIR__ . '/../inc_reuse/filter.php';
            ?>
        </aside>

    </div>
    <script src="../JS/requesterPopup.js?v=<?= time(); ?>"></script>
</body>

</html>