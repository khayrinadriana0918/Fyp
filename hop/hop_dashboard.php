<?php
require_once '../includes/config.php';

require_once __DIR__ . '/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}
$user = $_SESSION['user_id'];

$query = "SELECT users.user_id,users.name,users.created_at,head_of_programme.staff_id
FROM users
INNER JOIN head_of_programme ON users.user_id = head_of_programme.user_id
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
                <h1>SIMSAP-Student Issue Management System for Academic Programme</h1>
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
                <li><a href="hop_Dashboard.php">Dashboard</a></li>
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
                    <dt class="info">Staff ID:</dt>
                    <dd>
                        <?php
                        echo htmlspecialchars($userInfo['staff_id']);
                        ?>
                    </dd>
                    <dt>Full Name:</dt>
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
                <button onclick="document.location='hop_admin_requests.php'">Submit Request to Admin</button>
                <button onclick="document.location='student_requests.php'">See Student Requests</button>
                <button onclick="document.location='hop_requests.php'">See My Requests</button>
            </div>
            <div class="Submitted-Req">
                <div>
                    <h2>Recent Students Requests</h2>
                    <div>
                        <!-- show recent submitted request-->
                        <?php
                        $requestQuery = "
                        SELECT
                        r.*,	
                        s.student_id AS requester_id,
                        u.name AS requester_name,
                        c.category_name AS category_name
                        FROM request r
                        INNER JOIN student s ON r.student_id = s.student_id
                        INNER JOIN users u ON s.user_id = u.user_id
                        INNER JOIN category c ON r.category_id = c.category_id
                        ORDER BY r.submission_date DESC
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
                                        <p><?= htmlspecialchars($request['request_id']); ?></p><br>
                                        <h2><?= htmlspecialchars($request['requester_name']); ?></h2>
                                        <h2>(<?= htmlspecialchars($request['requester_id']); ?>)</h2>
                                        <h6><?= htmlspecialchars($request['priority']); ?></h6>


                                        <h1><a href="req_details.php?id=<?= urlencode($request['request_id']); ?>">
                                                <?= htmlspecialchars($request['title']); ?></a>
                                        </h1><br>

                                        <h3><?= htmlspecialchars($request['category_name']); ?></h3>
                                        <h6><?= htmlspecialchars($request['label']); ?></h6>

                                        <h6><?= htmlspecialchars($request['stats']); ?></h6>
                                        <h6><?= htmlspecialchars($request['submission_date']); ?></h6>

                                    </div>
                                    <div class="bottom-card">
                                        <h3>Description</h3>
                                        <p><?= htmlspecialchars($request['description']); ?></p>

                                        <h4>Attachments</h4>
                                        <?= htmlspecialchars($request['request_file']); ?>

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