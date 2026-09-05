<?php
require_once '../includes/config.php';

require_once __DIR__ . '/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}
$user = $_SESSION['user_id'];

$query = "SELECT users.user_id,users.name,users.created_at,administrator.admin_code 
FROM users
INNER JOIN administrator ON users.user_id = administrator.user_id
WHERE users.user_id = :user_id;";

$stmt = $pdo->prepare($query);

$stmt->execute([
    ':user_id' => $user
]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("User information not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>System</title>
    <link rel="stylesheet" href="css/dashboard.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>

    <div class="layout">
        <header>
            <!-- =============================================== -->
            <!-- HEADER -->
            <!-- =============================================== -->
            <div class="header-content">
                <h1>Student Issue Management System for Academic Programme</h1>
                <img src="" alt="bell-icon">
            </div>
        </header>
        <div>
            <h2>Hi,
                <?php
                echo htmlspecialchars($userInfo['name']);
                ?>
            </h2>
        </div>
        <!-- left content -->
        <div>
            <ul>
                <li><a href="student_Dashboard.php">Dashboard</a></li>
                <li><a href="About.php">About</a></li>
                <li><a href="faq.html">FAQ</a></li>
            </ul>
        </div>
        <!-- left content -->
        <!-- middle content start -->
        <div class="middle-content">
            <div class="user-info">
                <!-- users Information -->
                <dl>
                    <dt>User ID:</dt>
                    <dd>
                        <?php
                        echo htmlspecialchars($userInfo['user_id']);
                        ?>
                    </dd>
                    <dt class="info">Admin Code:</dt>
                    <dd>
                        <?php
                        echo htmlspecialchars($userInfo['admin_code']);
                        ?>
                    </dd>
                    <dt>Administrator Name:</dt>
                    <dd>
                        <?php
                        echo htmlspecialchars($userInfo['name']);
                        ?>
                    </dd>
                    <dt>Account Created:</dt>
                    <dd>
                        <?php
                        echo htmlspecialchars($userInfo['created_at']);
                        ?>
                    </dd>
                </dl>
            </div>
            <div class="button-content">
                <button onclick="document.location='manage_user.php'">Manage User Accounts</button>
                <button onclick="document.location='manage_contents.php'">Manage Contents</button>
                <button onclick="document.location='admin_requests.php'">See All Requests</button>
            </div>
            <div class="Submitted-Req">
                <div>
                    <h2>Recent Submitted Requests</h2>
                    <div>
                        <!-- show recent submitted request-->
                        <?php
                        $requestQuery = "
                        SELECT
                        r.ar_request_id,
                        r.ar_title,
                        r.ar_label,
                        r.ar_priority,
                        r.ar_description,
                        r.ar_request_file,
                        r.ar_stats,
                        r.ar_submission_date,
                        h.staff_id AS requester_id,
                        u.name AS requester_name,
                        c.category_name AS category_name
                        FROM admin_request r
                        INNER JOIN users u ON r.user_id = u.user_id
                        INNER JOIN head_of_programme h ON r.user_id = h.user_id
                        INNER JOIN category c ON r.category_id = c.category_id
                        ORDER BY r.ar_submission_date DESC
                        LIMIT 5
                        ;";

                        $requestStmt = $pdo->prepare($requestQuery);
                        $requestStmt->execute();

                        $recentRequests = $requestStmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if (empty($recentRequests)): ?>
                            <p>No requests have been submitted.</p>
                        <?php else: ?>
                            <?php foreach ($recentRequests as $request): ?>
                                <div class="request-card">
                                    <div class="top-card">
                                        <p><?= htmlspecialchars($request['ar_request_id']); ?></p><br>
                                        <h2><?= htmlspecialchars($request['requester_name']); ?></h2>
                                        <h2>(<?= htmlspecialchars($request['requester_id']); ?>)</h2>
                                        <h6><?= htmlspecialchars($request['ar_priority']); ?></h6>


                                        <h1><a href="req_details.php?id=<?= urlencode($request['ar_request_id']); ?>">
                                                <?= htmlspecialchars($request['ar_title']); ?></a>
                                        </h1><br>

                                        <h3><?= htmlspecialchars($request['category_name']); ?></h3>
                                        <h6><?= htmlspecialchars($request['ar_label']); ?></h6>

                                        <h6><?= htmlspecialchars($request['ar_stats']); ?></h6>
                                        <h6><?= htmlspecialchars($request['ar_submission_date']); ?></h6>

                                    </div>
                                    <div class="bottom-card">
                                        <h3>Description</h3>
                                        <p><?= htmlspecialchars($request['ar_description']); ?></p>

                                        <h4>Attachments</h4>
                                        <?= htmlspecialchars($request['ar_request_file']); ?>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- middle content end -->
        <!-- filter content(rightmost) -->
        <?php include __DIR__. '/../inc_reuse/filter.php'; ?>
    </div>

    <!-- javascript -->

    <!-- javascript end -->
</body>

</html>