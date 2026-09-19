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
    <link rel="stylesheet" href="../CSS/dashboard.css">

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
        <div class="user-name">
            <h2>Welcome,
                <?php
                echo htmlspecialchars($userInfo['name']);
                ?>
            </h2>
        </div>
        <!-- left content -->
        <div>
            <ul>
                <li><a href="admin_Dashboard.php">Dashboard</a></li>
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
                        r.*,
                        h.staff_id AS requester_id,
                        u.name AS requester_name,
                        c.category_name AS category_name

                        FROM admin_request r
                        INNER JOIN head_of_programme h ON r.staff_id = h.staff_id
                        INNER JOIN users u ON h.user_id = u.user_id
                        INNER JOIN category c ON r.category_id = c.category_id
                        ORDER BY r.ar_submission_date DESC
                        LIMIT 5
                        ;";

                        $requestStmt = $pdo->prepare($requestQuery);
                        $requestStmt->execute();

                        $recentRequests = $requestStmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <table>
                            <tr>
                                <th>Status</th>
                                <th>Req. ID</th>
                                <th>Name</th>
                                <th>User ID</th>
                                <th>Issue Category</th>
                                <th>Files</th>
                                <th>Description</th>
                                <th>Last Updated</th>
                            </tr>
                        </table>
                        <?php if (empty($recentRequests)): ?>
                            <p>No requests have been submitted.</p>
                        <?php else: ?>
                            <!-- change into table that display request id, staff id, name, and submitted date(resolved date if stats is complete) -->
                            <?php foreach ($recentRequests as $request): ?>
                                <div class="request-row">
                                    <table>
                                        <tr>
                                            <!-- grab from databases hop req that sent to admin -->
                                            <td><!--if pending put 🔴, incomplete put 🟡, complete put 🟢, image or emoji itself--></td>
                                            <td><!--Request ID from user--></td>
                                            <td><!--name--></td>
                                            <td><!--User ID--></td>
                                            <td><!--Issue Category--></td>
                                            <td><!--Files if there--></td>
                                            <td><!--First max 50 words from description--></td>

                                        </tr>
                                    </table>
                                    <!-- request id|student id|name|desc(max 50 words)|recent date(if finished, resolved date with '(resolved)') -->
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- middle content end -->
        <!-- filter content(rightmost) -->
        <?php include __DIR__ . '/../inc_reuse/filter.php'; ?>
    </div>

    <!-- javascript -->

    <!-- javascript end -->
</body>

</html>