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

$query = "
SELECT
r.*,
c.category_name
FROM request r

INNER JOIN student s
ON r.student_id = s.student_id

INNER JOIN category c
ON r.category_id= c.category_id

WHERE s.user_id= :user_id
ORDER BY r.submission_date DESC
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':user_id' => $userId
]);

$reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | SIMSAP</title>
    <link
        rel="stylesheet"
        href="../CSS/tableReq.css">

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
        </header>

        <!-- =====================================================
         LEFT SIDEBAR
    ====================================================== -->
        <aside class="sidebar">
            <nav>
                <ul>
                    <li>
                        <a href="student_dashboard.php" class="active">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="about.php">About</a>
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
                        <a href="student_requests.php" class="programme-required" id="active">
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

        <table>

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

                    <tr
                        class="request-row"
                        data-request-id="<?= htmlspecialchars($request['request_id']); ?>"
                        data-student-id="<?= htmlspecialchars($request['student_id']); ?>"
                        data-semester="<?= htmlspecialchars($request['semester']); ?>"
                        data-title="<?= htmlspecialchars($request['title']); ?>"
                        data-category="<?= htmlspecialchars($request['category_name']); ?>"
                        data-label="<?= htmlspecialchars($request['label']); ?>"
                        data-priority="<?= htmlspecialchars($request['priority']); ?>"
                        data-status="<?= htmlspecialchars($request['stats']); ?>"
                        data-description="<?= htmlspecialchars($request['description']); ?>"
                        data-file="<?= htmlspecialchars($request['request_file']); ?>"
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
                            <?= htmlspecialchars($request['stats']); ?>
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
        <div id="req_popup" class="req-popup">
            <div class="req-popup-content">
                <button type="button" id="close_popup">X</button>
                <div id="request_details">
                    <div class="popup-top">
                        <div>
                            <h3>
                                <?= htmlspecialchars($userInfo['name']); ?>
                                (<span id="popup_student_id"></span>)
                            </h3>

                            <p>Semester: <span id="popup_semester"></span></p>
                        </div>
                        <div>
                            <p>Priority: <span id="popup_priority"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h2 id="popup_title"></h2>
                    <p>Issue Category:
                        <span id="popup_category"></span>
                    </p>
                    <div class="popup-request-info">
                        <div>
                            <p>Labels: <span id="popup_labels"></span></p>
                        </div>
                        <div>
                            <p>Status: <span id="popup_status"></span></p>
                            <p>Date Submitted: <span id="popup_submit_date"></span></p>
                            <p id="resolved_date_container">Date Resolved: <span id="popup_resolved_date"></span></p>
                        </div>
                    </div>
                    <hr>
                    <h3>Description</h3>
                    <p id="popup_desc"></p>

                    <h3>Attachments</h3>
                    <div id="popup_file"></div>

                    <div class="popup-actions">
                        <button type="button" id="edit_request">Edit Form</button>

                        <button type="button" id="view history">See change history</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('.request-row').on('click', function() {
            const requestId = $(this).data('request-id');
            const studentId = $(this).data('student-id');
            const semester = $(this).data('semester');
            const title = $(this).data('title');
            const category = $(this).data('category');
            const label = $(this).data('label');
            const priority = $(this).data('priority');
            const status = $(this).data('status');
            const desc = $(this).data('description');
            const file = $(this).data('file');
            const submitDate = $(this).data('submitted-date');
            const resolved = $(this).data('resolved-date');

            console.log("Request ID:", requestId);

            $('#popup_student_id').text(studentId);
            $('#popup_semester').text(semester);
            $('#popup_title').text(title);
            $('#popup_category').text(category);
            $('#popup_labels').text(label);
            $('#popup_priority').text(priority);
            $('#popup_status').text(status);
            $('#popup_desc').text(desc);
            $('#popup_submit_date').text(submitDate);
            $('#popup_resolved_date').text(resolved);

            if (status === 'completed' && resolved) {
                $('#popup_resolved_date').text(resolved);
                $('#resolved_date_container').show();
            } else {
                $('#popup_resolved_date').text('');
                $('#resolved_date_container').hide('');
            }

            if(file){
                $('#popup_file').html(
            '<a href="../uploads/hop_student/' +
            encodeURIComponent(file) +
            '" target="_blank">View Attachment</a>'
        );
            }else{
                $('#popup_file').text('No attachment');
            }

            $('#req_popup').css('display', 'flex');

        });

        $('#close_popup').on('click', function() {
            $('#req_popup').hide();
        });
    </script>
</body>

</html>