<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}
$userId = $_SESSION['user_id'];

$adminStmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.name,
        a.admin_code
    FROM users u
    INNER JOIN administrator a
        ON u.user_id = a.user_id
    WHERE u.user_id = :user_id
");

$adminStmt->execute([
    ':user_id' => $userId
]);

$userInfo = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("Access denied.");
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
    <title>Admin Dashboard | SIMSAP</title>

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
                        <a href="admin_dashboard.php">
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
                        <a href="submittedHop_requests.php" class="active">
                            Head of Programme Request
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
        <main class="middle-content">
            <section class="request-section">
                <div class="request-content">
                    <div class="request-table-container">
                        <table class="request-table">

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
                                        class="request-row receiver-request-row"
                                        data-request-type="administrator"

                                        data-request-id="<?= htmlspecialchars($request['ar_request_id']); ?>"
                                        data-requester-name="<?= htmlspecialchars($request['requester_name']); ?>"
                                        data-requester-id="<?= htmlspecialchars($request['requester_id']); ?>"
                                        data-title="<?= htmlspecialchars($request['ar_title']); ?>"
                                        data-category="<?= htmlspecialchars($request['category_name']); ?>"
                                        data-priority="<?= htmlspecialchars($request['ar_priority']); ?>"
                                        data-status="<?= htmlspecialchars($request['ar_stats']); ?>"
                                        data-description="<?= htmlspecialchars($request['ar_description']); ?>"
                                        data-file="<?= htmlspecialchars($request['ar_request_file'] ?? ''); ?>"
                                        data-feedback="<?= htmlspecialchars($request['ar_feedback'] ?? ''); ?>"
                                        data-submitted-date="<?= htmlspecialchars($request['ar_submission_date']); ?>"
                                        data-resolved-date="<?= htmlspecialchars($request['ar_resolved_date'] ?? ''); ?>">

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
                    </div>
                    <aside class="dashboard-filter">

                        <?php
                        include __DIR__ . '/../inc_reuse/filter.php';
                        ?>

                    </aside>
                </div>
            </section>
        </main>




        <?php include __DIR__ . '/../inc_reuse/receiver_popup.php'; ?>

        <script src="../JS/receiverPopup.js"></script>
</body>

</html>