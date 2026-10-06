<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$userId = $_SESSION['user_id'];

$userStmt = $pdo->prepare("
SELECT 
u.name,
h.programme_id

FROM users u

INNER JOIN head_of_programme h
ON u.user_id = h.user_id

WHERE u.user_id = :user_id");

$userStmt->execute(['user_id' => $userId]);
$userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("User information not found.");
}

if (empty($userInfo['programme_id'])) {
    header("Location:hop_dashboard.php");
    exit();
}

$search = trim($_GET['search'] ?? '');
$priority = $_GET['priority'] ?? '';
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$query = "
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

WHERE s.programme_id = ?
";

$params = [
    $userInfo['programme_id']
];


/* SEARCH */
if ($search !== '') {

    $query .= "
        AND (
            CAST(r.request_id AS CHAR) LIKE ?
            OR r.title LIKE ?
            OR u.name LIKE ?
            OR s.student_id LIKE ?
        )
    ";

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}


/* PRIORITY */
if ($priority !== '') {

    $query .= "
        AND r.priority = ?
    ";

    $params[] = $priority;
}


/* STATUS */
if ($status !== '') {

    $query .= "
        AND r.stats = ?
    ";

    $params[] = $status;
}


/* CATEGORY */
if ($category !== '') {

    $query .= "
        AND r.category_id = ?
    ";

    $params[] = $category;
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

$reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Head of Programme Dashboard | SIMSAP</title>
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
         MAIN CONTENT
    ====================================================== -->
        <main class="middle-content">
            <section class="request-section">
                <div class="section-header">
                    <h2>Student Requests</h2>
                </div>
                <div class="table-container">
                    <table class="request-table">

                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Semester</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th>Priority</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($reqs as $request): ?>
                                <?php
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
                                    class="request-row receiver-request-row"
                                    data-request-type="student"
                                    data-request-id="<?= htmlspecialchars($request['request_id']); ?>"
                                    data-requester-name="<?= htmlspecialchars($request['requester_name']); ?>"
                                    data-requester-id="<?= htmlspecialchars($request['requester_id']); ?>"
                                    data-semester="<?= htmlspecialchars($request['semester']); ?>"
                                    data-title="<?= htmlspecialchars($request['title']); ?>"
                                    data-category="<?= htmlspecialchars($request['category_name']); ?>"
                                    data-priority="<?= htmlspecialchars($request['priority']); ?>"
                                    data-status="<?= htmlspecialchars($request['stats']); ?>"
                                    data-description="<?= htmlspecialchars($request['description']); ?>"
                                    data-feedback="<?= htmlspecialchars($request['feedback'] ?? ''); ?>"
                                    data-file="<?= htmlspecialchars($request['request_file'] ?? ''); ?>"
                                    data-submitted-date="<?= htmlspecialchars($request['submission_date']); ?>"
                                    data-resolved-date="<?= htmlspecialchars($request['resolved_date'] ?? ''); ?>">

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
                                        <?= htmlspecialchars($request['priority']); ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>
            </section>
        </main>

        <aside class="filter-sidebar">
            <?php
            include __DIR__ . '/../inc_reuse/filter.php';
            ?>
        </aside>

        <?php include __DIR__ . '/../inc_reuse/receiver_popup.php'; ?>

    </div>
    <script src="../JS/receiverPopup.js"></script>
</body>

</html>