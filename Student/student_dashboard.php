<?php

require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/notification.php';

/* =========================================================
   CHECK LOGIN
========================================================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$user = $_SESSION['user_id'];
$notifications = getNotifs($pdo, $user);
$unreadNotifCount = getUnreadNotifCount($pdo, $user);
/* =========================================================
   GET LOGGED-IN STUDENT INFORMATION
========================================================= */
$query = "
    SELECT
        users.user_id,
        users.name,
        users.created_at,
        s.student_id,
        s.programme_id,
        s.student_id AS role_id
    FROM users

    INNER JOIN student s
        ON users.user_id = s.user_id
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

$roleIdLabel = 'Student ID';
$search =
    trim($_GET['search'] ?? '');
$priority =
    $_GET['priority'] ?? '';
$status =
    $_GET['status'] ?? '';
$category =
    $_GET['category'] ?? '';
$sort =
    $_GET['sort'] ?? 'newest';


$requestQuery = "
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
        ON r.category_id = c.category_id

    WHERE s.user_id = :user_id
";


$requestParams = [
    ':user_id' => $user
];


/* =========================
   SEARCH
========================= */

if ($search !== '') {
    $requestQuery .= "AND (CAST(r.request_id AS CHAR) LIKE :search OR r.title LIKE :search)";
    $requestParams[':search'] =
        '%' . $search . '%';
}

/* =========================
   PRIORITY
========================= */

if ($priority !== '') {
    $requestQuery .= "AND r.priority = :priority";
    $requestParams[':priority'] =$priority;
}

/* =========================
   STATUS
========================= */
if ($status !== '') {
    $requestQuery .= "AND r.stats = :status";
    $requestParams[':status'] =$status;
}
/* =========================
   CATEGORY
========================= */
if ($category !== '') {
    $requestQuery .= "
        AND r.category_id = :category_id
    ";
    $requestParams[':category_id'] =
        $category;
}
/* =========================
   SORT
========================= */
switch ($sort) {
    case 'oldest':
        $requestQuery .= "
            ORDER BY r.submission_date ASC
        ";
        break;
    case 'title':
        $requestQuery .= "
            ORDER BY r.title ASC
        ";
        break;
    case 'newest':
    default:
        $requestQuery .= " ORDER BY r.submission_date DESC ";break;
}


$requestQuery .= "LIMIT 5";


$requestStmt =
    $pdo->prepare($requestQuery);

$requestStmt->execute($requestParams);
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

    FROM request r

    INNER JOIN student s
    ON r.student_id= s.student_id 

    WHERE s.user_id= :user_id
";

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([':user_id' => $user]);
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
    <title>Student Dashboard | SIMSAP</title>

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

                    <div class="notif-container">

                        <button
                            type="button"
                            class="notification-button"
                            id="notification_btn"
                            title="Notifications">

                            <img
                                id="notif_icon"
                                src="<?= $unreadNotifCount > 0
                                            ? '../pictures/haveNotif.png'
                                            : '../pictures/noNotif.png'; ?>"
                                alt="Notification">

                        </button>

                        <div class="notification-dropdown" id="notification_dropdown">
                            <div class="notification-header">
                                <strong>Notifications</strong>
                            </div>

                            <?php if (empty($notifications)): ?>

                                <div class="notification-empty">
                                    No notifications.
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <div class="notification-item <?= $notif['n_mark_read'] == 0 ? 'unread' : ''; ?>"
                                        data-notification-id="<?= htmlspecialchars($notif['n_id']); ?>">
                                        <p>
                                            <?= htmlspecialchars($notif['n_message']); ?>
                                        </p>
                                        <small>
                                            <?= htmlspecialchars(
                                                date(
                                                    'd M Y, h:i A',
                                                    strtotime($notif['n_created_at'])
                                                )
                                            ); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </div>

                    </div>
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
                        <a href="student_dashboard.php" class="active">Dashboard</a>
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
                        <a href="student_requests.php" class="programme-required">
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
         MIDDLE CONTENT
    ====================================================== -->

        <main class="middle-content">
            <!-- =================================================
             WELCOME
        ================================================== -->
            <section class="dashboard-heading">
                <div>

                    <p>
                        Manage and monitor student administrative
                        requests.
                    </p>
                </div>
            </section>

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
                    class="programme-required"
                    data-url="student_hop_requests.php">
                    Submit Request to Head of Programme
                </button>
                <button
                    type="button"
                    class="programme-required"
                    data-url="student_requests.php">
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
                        <h2>Recent Requests</h2>
                        <p>Your latest submitted requests.</p>
                    </div>

                    <a href="student_requests.php" class="programme-required">View All</a>

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
                                    <th>Request ID</th>
                                    <th>Semester</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Priority</th>
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
                                        data-request-id="<?= htmlspecialchars($request['request_id']); ?>"
                                        data-requester-name="<?= htmlspecialchars($userInfo['name']); ?>"
                                        data-requester-id="<?= htmlspecialchars($request['student_id']); ?>"
                                        data-semester="<?= htmlspecialchars($request['semester']); ?>"
                                        data-title="<?= htmlspecialchars($request['title']); ?>"
                                        data-category="<?= htmlspecialchars($request['category_name']); ?>"
                                        data-priority="<?= htmlspecialchars($request['priority']); ?>"
                                        data-status="<?= htmlspecialchars($request['stats']); ?>"
                                        data-description="<?= htmlspecialchars($request['description']); ?>"
                                        data-feedback="<?= htmlspecialchars($request['feedback'] ?? ''); ?>"
                                        data-file="<?= htmlspecialchars($request['request_file']); ?>"
                                        data-submitted-date="<?= htmlspecialchars($request['submission_date']); ?>"
                                        data-resolved-date="<?= htmlspecialchars($request['resolved_date']); ?>">

                                        <!-- REQUEST ID -->
                                        <td class="request-id">
                                            <?= htmlspecialchars(
                                                $request['request_id']
                                            ); ?>
                                        </td>

                                        <!-- SEMESTER -->
                                        <td class="request-semester">
                                            <?= htmlspecialchars($request['semester']); ?>
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
                                        <!-- PRIORITY -->
                                        <td>
                                            <?= htmlspecialchars($request['priority']); ?>
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
         programme
    ====================================================== -->
        <?php if (empty($userInfo['programme_id'])): ?>
            <?php include __DIR__ . '/../inc_reuse/programme.php'; ?><br>
        <?php endif ?>
        <!-- =====================================================
         RIGHT FILTER
    ====================================================== -->
        <aside class="filter-sidebar">
            <?php
            include __DIR__ .
                '/../inc_reuse/filter.php';
            ?>
        </aside>
        <?php include __DIR__ . '/../inc_reuse/requester_popup.php'; ?>
    </div>

    <script>
        $(document).ready(function() {

            const programmeSelected =
                <?= !empty($userInfo['programme_id'])
                    ? 'true' : 'false'; ?>;

            $('.programme-required').on('click', function(event) {
                const destination = $(this).data('url');
                // lock function
                if (!programmeSelected) {

                    event.preventDefault();

                    openForm();
                } else {
                    window.location.href = destination
                }
            });
        });
    </script>
    <script src="../JS/requesterPopup.js"></script>
    <script>
        $('#notification_btn').on('click', function(event) {
            event.stopPropagation();

            $('#notification_dropdown').toggleClass('show');
        });

        $(document).on('click', function() {
            $('#notification_dropdown').removeClass('show');
        });

        $('#notification_dropdown').on('click', function(event) {
            event.stopPropagation();
        });
    </script>
</body>

</html>