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
   GET LOGGED-IN ADMINISTRATOR INFORMATION
========================================================= */
$query = "
    SELECT
        users.user_id,
        users.name,
        users.created_at,
        a.admin_code AS role_id
    FROM users

    INNER JOIN administrator a
        ON users.user_id = a.user_id
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

$roleIdLabel = 'Admin Code';

$search = trim($_GET['search'] ?? '');
$priority = $_GET['priority'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$requestQuery = "
    SELECT
        r.*,
        h.staff_id AS requester_id,
        u.name AS requester_name,
        c.category_name

    FROM admin_request r

    INNER JOIN head_of_programme h
        ON r.staff_id = h.staff_id

    INNER JOIN users u
        ON h.user_id = u.user_id

    INNER JOIN category c
        ON r.category_id = c.category_id

    WHERE 1=1

";

$params = [];

//SEARCH//
if ($search !== '') {
    $requestQuery .= "
    AND (
    CAST(r.ar_request_id AS CHAR) LIKE ?
    OR r.ar_title LIKE ?
    OR u.name LIKE ?
    OR h.staff_id LIKE ?
    )";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}

if ($priority !== '') {
    $requestQuery .= "
    AND r.ar_priority = ?";

    $params[] = $priority;
}
if ($status !== '') {
    $requestQuery .= "
    AND r.ar_stats = ?";

    $params[] = $status;
}
if ($category !== '') {
    $requestQuery .= "
    AND r.category_id = ?";

    $params[] = $category;
}

switch ($sort) {
    case 'oldest':
        $requestQuery .= "
        ORDER BY r.ar_submission_date ASC
        ";
        break;

    case 'title':
        $requestQuery .= "
        ORDER BY r.ar_title ASC
        ";
        break;

    case 'newest':
        $requestQuery .= "
        ORDER BY r.ar_submission_date DESC
        ";
        break;
}

$requestQuery .= " LIMIT 5";

$requestStmt = $pdo->prepare($requestQuery);
$requestStmt->execute($params);

$recentRequests =
    $requestStmt->fetchAll(PDO::FETCH_ASSOC);
/* =========================================================
   GET DASHBOARD REQUEST OVERVIEW
========================================================= */
$countQuery = "
    SELECT

        COUNT(*) AS total,
        SUM(
            CASE
                WHEN ar_stats = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending,

        SUM(
            CASE
                WHEN ar_stats = 'In Progress'
                THEN 1
                ELSE 0
            END
        ) AS in_progress,

        SUM(
            CASE
                WHEN ar_stats = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed

    FROM admin_request r
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
    <title>Admin Dashboard | SIMSAP</title>

    <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= time(); ?>">
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
                    <a href="admin_dashboard.php" class="header-name">
                        <?= htmlspecialchars($userInfo['name']); ?>
                    </a>
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

                        <div
                            class="notification-dropdown"
                            id="notification_dropdown">

                            <div class="notification-header">
                                <strong>Notifications</strong>
                            </div>

                            <?php if (empty($notifications)): ?>

                                <div class="notification-empty">
                                    No notifications.
                                </div>

                            <?php else: ?>

                                <?php foreach ($notifications as $notif): ?>

                                    <div
                                        class="notification-item <?= $notif['n_mark_read'] == 0 ? 'unread' : ''; ?>"
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
                        <a href="admin_dashboard.php" class="active">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="../userProfile.php">User Profile</a>
                    </li>
                    <li>
                        <a href="database_management.php">
                            Database Management
                        </a>
                    </li>
                    <li>
                        <a href="submittedHop_requests.php">
                            Head of Programme Requests
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
                        Manage and monitor system requests.
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
             RECENT HOP REQUESTS
        ================================================== -->
            <section class="request-section">
                <!-- Request heading -->
                <div class="section-header">

                    <div>
                        <h2>Recent Requests</h2>
                        <p>Your latest submitted requests.</p>
                    </div>

                    <a href="submittedHop_requests.php">View All</a>

                </div>

                <!-- =================================================
                 NO REQUESTS
            ================================================== -->
                <?php if (empty($recentRequests)): ?>
                    <div class="empty-message">
                        <p>No requests have been submitted.</p>
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
                                    <th>Name</th>
                                    <th>Staff ID</th>
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
                                                $request['ar_stats']
                                            )
                                        );
                                    ?>
                                    <tr class="request-row receiver-request-row"

                                        data-request-type="administrator"

                                        data-request-id="<?= htmlspecialchars($request['ar_request_id']); ?>"

                                        data-requester-name="<?= htmlspecialchars($request['requester_name']); ?>"
                                        data-requester-id="<?= htmlspecialchars($request['requester_id']); ?>"
                                        data-semester=""
                                        data-title="<?= htmlspecialchars($request['ar_title']); ?>"
                                        data-category="<?= htmlspecialchars($request['category_name']); ?>"
                                        data-priority="<?= htmlspecialchars($request['ar_priority']); ?>"
                                        data-status="<?= htmlspecialchars($request['ar_stats']); ?>"
                                        data-description="<?= htmlspecialchars($request['ar_description'] ?? ''); ?>"
                                        data-file="<?= htmlspecialchars($request['ar_request_file'] ?? ''); ?>"
                                        data-feedback="<?= htmlspecialchars($request['ar_feedback'] ?? ''); ?>"
                                        data-submitted-date="<?= htmlspecialchars($request['ar_submission_date']); ?>"
                                        data-resolved-date="<?= htmlspecialchars($request['ar_resolved_date'] ?? ''); ?>">

                                        <!-- REQUEST ID -->
                                        <td class="request-id">
                                            <?= htmlspecialchars(
                                                $request['ar_request_id']
                                            ); ?>
                                        </td>

                                        <!-- HOP NAME -->
                                        <td>
                                            <?= htmlspecialchars(
                                                $request['requester_name']
                                            ); ?>
                                        </td>

                                        <!-- STAFF ID -->
                                        <td>
                                            <?= htmlspecialchars($request['requester_id']); ?>
                                        </td>

                                        <!-- TITLE -->
                                        <td class="request-title">
                                            <?= htmlspecialchars($request['ar_title']); ?>
                                        </td>

                                        <!-- CATEGORY -->
                                        <td>
                                            <?= htmlspecialchars($request['category_name']); ?>
                                        </td>

                                        <!-- STATUS -->
                                        <td>
                                            <span class="status status-<?= htmlspecialchars($statusClass); ?>">
                                                <?= htmlspecialchars($request['ar_stats']); ?>
                                            </span>
                                        </td>

                                        <!-- LAST UPDATED -->
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                date(
                                                    'd M Y',
                                                    strtotime(
                                                        $request['ar_submission_date']
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
                    <!-- =====================================================
                       RIGHT FILTER
                    ====================================================== -->
                    <aside class="dashboard-filter">
                        <?php
                        include __DIR__ .
                            '/../inc_reuse/filter.php';
                        ?>
                    </aside>
                <?php endif; ?>
            </section>
        </main>
        <?php include __DIR__ . '/../inc_reuse/receiver_popup.php'; ?>
    </div>
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
    <script src="../JS/receiverPopup.js"></script>
</body>

</html>