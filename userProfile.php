<?php
require_once  __DIR__ . '/includes/config.php';

require_once __DIR__ . '/includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];

$query = "
SELECT user_id,
name,
role,
created_at
FROM users
WHERE user_id= :user_id
";

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':user_id' => $userId
]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("User information not found.");
}

if ($userInfo['role'] === 'student') {
    $roleQuery = "
    SELECT
    student_id AS role_id,
    programme_id
    FROM student
    WHERE user_id=:user_id";

    $roleIdLabel = "Student ID";
} elseif ($userInfo['role'] === 'hop') {
    $roleQuery = "
    SELECT
    staff_id AS role_id,
    programme_id
    FROM head_of_programme
    WHERE user_id=:user_id";

    $roleIdLabel = "Staff ID";
} elseif ($userInfo['role'] === 'administrator') {
    $roleQuery = "
    SELECT
    admin_code AS role_id,
    FROM administrator
    WHERE user_id=:user_id";

    $roleIdLabel = "Admin Code";
} else {
    die("Invalid role.");
}

$roleStmt = $pdo->prepare($roleQuery);

$roleStmt->execute([
    ':user_id' => $userId
]);

$roleInfo = $roleStmt->fetch(PDO::FETCH_ASSOC);


if (!$roleInfo) {
    die("Role information not found.");
}


// Add role-specific information into userInfo
$userInfo = array_merge(
    $userInfo,
    $roleInfo
);

if ($userInfo['role'] === 'student') {

    $dashboardLink = "Student/student_dashboard.php";
} elseif ($userInfo['role'] === 'hop') {

    $dashboardLink = "hop/hop_Dashboard.php";
} elseif ($userInfo['role'] === 'administrator') {

    $dashboardLink = "admin/admin_Dashboard.php";
}
?>

<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Student Profile | SIMSAP</title>
    <link
        rel="stylesheet"
        href="CSS/dashboard.css">

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
                        <a href="./Student/student_dashboard.php">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="about.php">About</a>
                    </li>
                    <li>
                        <a href="userProfile.php" class="active">User Profile</a>
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
            <section class="account-section">


                <h2>
                    Account Information
                </h2>

                <div class="account-info">
                    <!-- USER ID -->
                    <div>
                        <span>User ID</span>
                        <strong><?= htmlspecialchars($userInfo['user_id']); ?></strong>
                    </div>

                    <!-- ROLE ID -->
                    <div>
                        <span><?= htmlspecialchars($roleIdLabel); ?></span>
                        <strong><?= htmlspecialchars($userInfo['role_id']); ?></strong>
                    </div>

                    <!-- FULL NAME -->
                    <div>
                        <span>Full Name</span>
                        <strong><?= htmlspecialchars($userInfo['name']); ?></strong>
                    </div>

                    <?php if ($userInfo['role']==='student' || $userInfo['role']==='hop'): ?>
                        <div>
                            <span>Programme</span>

                            <strong>
                                <?= !empty($userInfo['programme_id'])
                                ? htmlspecialchars($userInfo['programme_id'])
                                :'Not selected'; ?>
                            </strong>
                        </div>
                        <?php endif; ?>
                    <!-- ACCOUNT CREATED -->
                    <div>
                        <span>Account Created</span>
                        <strong>
                            <?php
                            echo htmlspecialchars(
                                date(
                                    'd M Y',
                                    strtotime(
                                        $userInfo['created_at']
                                    )
                                )
                            );
                            ?>
                        </strong>
                    </div>
                </div>
            </section>
        </main>
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
</body>

</html>