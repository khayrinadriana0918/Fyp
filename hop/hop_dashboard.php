<?php

require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

/* =========================================================
   CHECK LOGIN
========================================================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$user = $_SESSION['user_id'];
/* =========================================================
   GET LOGGED-IN HOP INFORMATION
========================================================= */
$query = "
    SELECT
        users.user_id,
        users.name,
        users.created_at,
        head_of_programme.staff_id AS role_id
    FROM users
    INNER JOIN head_of_programme
        ON users.user_id = head_of_programme.user_id
    WHERE users.user_id = :user_id
";

$stmt = $pdo->prepare($query);

$stmt->execute([
    ':user_id' => $user
]);

$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$userInfo) {
    die("User information not found.");
}

$roleIdLabel = 'Staff ID';
/* =========================================================
   GET RECENT STUDENT REQUESTS
========================================================= */
$requestQuery = "
    SELECT
        r.request_id,
        r.title,
        r.stats,
        r.priority,
        r.submission_date,
        s.student_id AS requester_id,
        u.name AS requester_name,
        c.category_name
    FROM request r
    INNER JOIN student s
        ON r.student_id = s.student_id
    INNER JOIN users u
        ON s.user_id = u.user_id
    INNER JOIN category c
        ON r.category_id = c.category_id
    ORDER BY r.submission_date DESC
    LIMIT 5
";


$requestStmt = $pdo->prepare($requestQuery);
$requestStmt->execute();
$recentRequests =
    $requestStmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   GET DASHBOARD REQUEST COUNTS
========================================================= */
$countQuery = "
    SELECT

        COUNT(*) AS total,
        SUM(
            CASE
                WHEN stats = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending,

        SUM(
            CASE
                WHEN stats = 'In Progress'
                THEN 1
                ELSE 0
            END
        ) AS in_progress,

        SUM(
            CASE
                WHEN stats = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed

    FROM request
";

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute();
$requestCounts =
    $countStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>HoP Dashboard | SIMSAP</title>
    <link
        rel="stylesheet"
        href="../CSS/dashboard.css">

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
                        Hi, <?= htmlspecialchars($userInfo['name']); ?>
                    </span>
                    <button
                        type="button"
                        class="notification-button"
                        title="Notifications">
                        🔔
                    </button>
                    <a href="../includes/logout.php" class="logout">
                        Log out
                    </a>
                </div>
            </div>

            <div class="top-nav">
                <a href="about.php">About</a>
            </div>
        </header>

        <!-- =====================================================
         LEFT SIDEBAR
    ====================================================== -->
        <aside class="sidebar">
            <nav>
                <ul>
                    <li>
                        <a href="hop_Dashboard.php" class="active">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="hop_admin_requests.php">
                            Submit Request to Admin
                        </a>
                    </li>
                    <li>
                        <a href="hop_requests.php">
                            My Requests
                        </a>
                    </li>
                    <li>
                        <a href="userManual.html">
                            User Manual
                        </a>
                    </li>
                    <li>
                        <a href="faq.html">
                            FAQ
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- =====================================================
         MIDDLE CONTENT
    ====================================================== -->

        <main class="middle-content">
            <!-- =================================================
             WELCOME
        ================================================== -->
            <section class="dashboard-heading">
                <div>
                    <h2>
                        Hi,
                        <?= htmlspecialchars(
                            $userInfo['name']
                        ); ?>
                    </h2>
                    <p>
                        Manage and monitor student administrative
                        requests.
                    </p>
                </div>
            </section>

            <!-- =================================================
             USER INFORMATION
        ================================================== -->

            <?php include __DIR__ . '/../inc_reuse/account_info.php'; ?><br>
<!-- other profile.php -->
            <!-- =================================================
             DASHBOARD STATISTICS
        ================================================== -->
            <section class="dashboard-cards">
                <!-- TOTAL -->
                <div class="stat-card">
                    <p>Total Requests</p>
                    <h2>
                        <?= htmlspecialchars(
                            $requestCounts['total'] ?? 0
                        ); ?>
                    </h2>
                </div>

                <!-- PENDING -->
                <div class="stat-card">
                    <p>Pending</p>
                    <h2>
                        <?= htmlspecialchars($requestCounts['pending'] ?? 0); ?>
                    </h2>
                </div>

                <!-- IN PROGRESS -->
                <div class="stat-card">
                    <p>In Progress</p>
                    <h2>
                        <?= htmlspecialchars($requestCounts['in_progress'] ?? 0); ?>
                    </h2>
                </div>

                <!-- COMPLETED -->
                <div class="stat-card">
                    <p>Completed</p>
                    <h2>
                        <?= htmlspecialchars($requestCounts['completed'] ?? 0); ?>
                    </h2>
                </div>
            </section>

            <!-- =================================================
             QUICK ACTIONS
        ================================================== -->
            <section class="quick-actions">
                <button
                    type="button"
                    onclick="document.location='hop_admin_requests.php'">
                    Submit Request to Admin
                </button>
                <button
                    type="button"
                    onclick="document.location='hop_requests.php'">
                    See My Requests
                </button>

            </section>

            <!-- =================================================
             RECENT STUDENT REQUESTS
        ================================================== -->
            <section class="request-section">
                <!-- Request heading -->
                <div class="section-header">

                    <div>
                        <h2>Recent Student Requests</h2>
                        <p>Latest administrative requests submitted by students.</p>
                    </div>

                    <a href="student_admin_requests.php" class="view-all">View All</a>

                </div>

                <!-- =================================================
                 NO REQUESTS
            ================================================== -->
                <?php if (empty($recentRequests)): ?>
                    <div class="empty-message">
                        <p>No student requests have been submitted.</p>
                    </div>

                <?php else: ?>
                    <!-- =================================================
                     REQUEST TABLE
                ================================================== -->
                    <div class="table-container">
                        <table class="request-table">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Name</th>
                                    <th>Student ID</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($recentRequests as $request): ?>
                                    <?php
                                    /*
                             * Convert status into CSS class.
                             */
                                    $statusClass =
                                        strtolower(
                                            str_replace(
                                                ' ',
                                                '-',
                                                $request['stats']
                                            )
                                        );
                                    ?>
                                    <tr class="request-row"
                                        onclick="window.location.href ='req_details.php?id=<?= urlencode($request['request_id']); ?>';">

                                        <!-- REQUEST ID -->
                                        <td class="request-id">
                                            <?= htmlspecialchars(
                                                $request['request_id']
                                            ); ?>
                                        </td>

                                        <!-- STUDENT NAME -->
                                        <td>
                                            <?= htmlspecialchars(
                                                $request['requester_name']
                                            ); ?>
                                        </td>

                                        <!-- STUDENT ID -->
                                        <td>
                                            <?= htmlspecialchars($request['requester_id']); ?>
                                        </td>

                                        <!-- TITLE -->
                                        <td class="request-title">
                                            <?= htmlspecialchars($request['title']); ?>
                                        </td>

                                        <!-- CATEGORY -->
                                        <td>
                                            <?= htmlspecialchars($request['category_name']); ?>
                                        </td>

                                        <!-- STATUS -->
                                        <td>
                                            <span class="status status-<?= htmlspecialchars($statusClass); ?>">
                                                <?= htmlspecialchars($request['stats']); ?>
                                            </span>
                                        </td>

                                        <!-- LAST UPDATED -->
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                date(
                                                    'd M Y',
                                                    strtotime(
                                                        $request['submission_date']
                                                    )
                                                )
                                            );
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        <!-- =====================================================
         RIGHT FILTER
    ====================================================== -->
        <aside class="filter-sidebar">
            <?php
            include __DIR__ .
                '/../inc_reuse/filter.php';
            ?>
        </aside>
    </div>
</body>

</html>