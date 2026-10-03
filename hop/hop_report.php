<?php

require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$userId = $_SESSION['user_id'];


/* =========================================
   GET HOP INFORMATION
========================================= */

$userStmt = $pdo->prepare("
    SELECT
        u.name,
        h.staff_id,
        h.programme_id,
        p.programme_name
    FROM users u

    INNER JOIN head_of_programme h
        ON u.user_id = h.user_id

    INNER JOIN programme p
        ON h.programme_id = p.programme_id
    WHERE u.user_id = :user_id
");

$userStmt->execute([
    ':user_id' => $userId
]);

$userInfo =
    $userStmt->fetch(PDO::FETCH_ASSOC);

$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$dateCondition = "";
$dateParams = [
    ':programme_id' => $userInfo['programme_id']
];

if ($startDate !== '' && $endDate !== '') {
    $dateCondition = "
    AND DATE(r.submission_date)
    BETWEEN :start_date AND :end_date";

    $dateParams[':start_date'] = $startDate;
    $dateParams[':end_date'] = $endDate;
}

if (!$userInfo) {
    die("Head of Programme information not found.");
}


/* =========================================
   SUMMARY COUNTS
========================================= */

$summaryStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN r.stats = 'Pending'
                THEN 1
                ELSE 0
            END
        ) AS pending,

        SUM(
            CASE
                WHEN r.stats = 'In Progress'
                THEN 1
                ELSE 0
            END
        ) AS in_progress,

        SUM(
            CASE
                WHEN r.stats = 'Completed'
                THEN 1
                ELSE 0
            END
        ) AS completed,

        SUM(
            CASE
                WHEN r.stats = 'Rejected'
                THEN 1
                ELSE 0
            END
        ) AS rejected
    FROM request r

    INNER JOIN student s
        ON r.student_id = s.student_id
    WHERE s.programme_id = :programme_id

    $dateCondition
");

$summaryStmt->execute($dateParams);

$summary =
    $summaryStmt->fetch(PDO::FETCH_ASSOC);


/* =========================================
   REQUESTS BY CATEGORY
========================================= */

$categoryStmt = $pdo->prepare("
    SELECT
        c.category_name,
        COUNT(*) AS total
    FROM request r

    INNER JOIN student s
        ON r.student_id = s.student_id

    INNER JOIN category c
        ON r.category_id = c.category_id
    WHERE s.programme_id = :programme_id

    $dateCondition

    GROUP BY
        c.category_id,
        c.category_name
    ORDER BY total DESC
");

$categoryStmt->execute($dateParams);

$categoryData =
    $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
   REQUESTS BY STATUS
========================================= */

$statusStmt = $pdo->prepare("
    SELECT
        r.stats,
        COUNT(*) AS total
    FROM request r

    INNER JOIN student s
        ON r.student_id = s.student_id
    WHERE s.programme_id = :programme_id

    $dateCondition

    GROUP BY r.stats
");

$statusStmt->execute($dateParams);

$statusData =
    $statusStmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
   REQUESTS BY SEMESTER
========================================= */

$semesterStmt = $pdo->prepare("
    SELECT
        r.semester,
        COUNT(*) AS total
    FROM request r

    INNER JOIN student s
        ON r.student_id = s.student_id

    WHERE s.programme_id = :programme_id

    $dateCondition

    GROUP BY r.semester
    ORDER BY r.semester ASC
");

$semesterStmt->execute($dateParams);

$semesterData =
    $semesterStmt->fetchAll(PDO::FETCH_ASSOC);

$monthStmt = $pdo->prepare("
    SELECT
        DATE_FORMAT(
            r.submission_date,
            '%Y-%m'
        ) AS request_month,

        COUNT(*) AS total

    FROM request r

    INNER JOIN student s
        ON r.student_id = s.student_id

    WHERE s.programme_id = :programme_id

    $dateCondition

    GROUP BY request_month

    ORDER BY request_month ASC
");

$monthStmt->execute($dateParams);

$monthData =
    $monthStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrative Report | SIMSAP</title>

    <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= time(); ?>">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
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
                    <a href="../about.html">About</a>
                </li>
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
                    <a href="submittedStud.php">Students Requests</a>
                </li>
                <li>
                    <a href="hop_report.php" class="active">Administrative Report</a>
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


    <main class="middle-content">

        <section class="dashboard-heading">

            <h2>Administrative Report</h2>

            <p>
                <?= htmlspecialchars(
                    $userInfo['programme_name']
                ); ?>
            </p>

        </section>
        <form method="GET" class="report-date-filter">

            <div>
                <label for="start_date">
                    Start Date
                </label>

                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate); ?>">
            </div>


            <div>
                <label for="end_date">
                    End Date
                </label>

                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate); ?>">
            </div>


            <button type="submit">Generate Report</button>
            <a href="hop_report.php">Clear</a>

        </form>

        <!-- SUMMARY CARDS -->

        <section class="dashboard-cards">

            <div class="stat-card">

                <p>Total Requests</p>

                <h2>
                    <?= htmlspecialchars(
                        $summary['total'] ?? 0
                    ); ?>
                </h2>

            </div>


            <div class="stat-card">

                <p>Pending</p>

                <h2>
                    <?= htmlspecialchars(
                        $summary['pending'] ?? 0
                    ); ?>
                </h2>

            </div>


            <div class="stat-card">

                <p>In Progress</p>

                <h2>
                    <?= htmlspecialchars(
                        $summary['in_progress'] ?? 0
                    ); ?>
                </h2>

            </div>


            <div class="stat-card">

                <p>Completed</p>

                <h2>
                    <?= htmlspecialchars(
                        $summary['completed'] ?? 0
                    ); ?>
                </h2>

            </div>


            <div class="stat-card">

                <p>Rejected</p>

                <h2>
                    <?= htmlspecialchars(
                        $summary['rejected'] ?? 0
                    ); ?>
                </h2>

            </div>

        </section>


        <!-- CHARTS -->

        <section class="report-charts">

            <div class="report-chart">

                <h3>
                    Requests by Category
                </h3>

                <canvas id="categoryChart"></canvas>

            </div>


            <div class="report-chart">

                <h3>
                    Requests by Status
                </h3>

                <canvas id="statusChart"></canvas>

            </div>


            <div class="report-chart">

                <h3>
                    Requests by Semester
                </h3>

                <canvas id="semesterChart"></canvas>

            </div>


            <div class="report-chart">

                <h3>
                    Requests by Submission Month
                </h3>

                <canvas id="monthChart"></canvas>

            </div>

        </section>

    </main>

    </div>
    <script>
        const categoryLabels =
            <?= json_encode(
                array_column(
                    $categoryData,
                    'category_name'
                )
            ); ?>;

        const categoryTotals =
            <?= json_encode(
                array_column(
                    $categoryData,
                    'total'
                ),
                JSON_NUMERIC_CHECK
            ); ?>;


        new Chart(
            document.getElementById(
                'categoryChart'
            ), {
                type: 'bar',

                data: {
                    labels: categoryLabels,

                    datasets: [{
                        label: 'Requests',
                        data: categoryTotals
                    }]
                }
            }
        );


        const statusLabels =
            <?= json_encode(
                array_column(
                    $statusData,
                    'stats'
                )
            ); ?>;

        const statusTotals =
            <?= json_encode(
                array_column(
                    $statusData,
                    'total'
                ),
                JSON_NUMERIC_CHECK
            ); ?>;


        new Chart(
            document.getElementById(
                'statusChart'
            ), {
                type: 'doughnut',

                data: {
                    labels: statusLabels,

                    datasets: [{
                        data: statusTotals
                    }]
                }
            }
        );


        const semesterLabels =
            <?= json_encode(
                array_map(
                    function ($row) {
                        return 'Semester ' .
                            $row['semester'];
                    },
                    $semesterData
                )
            ); ?>;

        const semesterTotals =
            <?= json_encode(
                array_column(
                    $semesterData,
                    'total'
                ),
                JSON_NUMERIC_CHECK
            ); ?>;


        new Chart(
            document.getElementById(
                'semesterChart'
            ), {
                type: 'bar',

                data: {
                    labels: semesterLabels,

                    datasets: [{
                        label: 'Requests',
                        data: semesterTotals
                    }]
                }
            }
        );


        const monthLabels =
            <?= json_encode(
                array_column(
                    $monthData,
                    'request_month'
                )
            ); ?>;

        const monthTotals =
            <?= json_encode(
                array_column(
                    $monthData,
                    'total'
                ),
                JSON_NUMERIC_CHECK
            ); ?>;


        new Chart(
            document.getElementById(
                'monthChart'
            ), {
                type: 'line',

                data: {
                    labels: monthLabels,

                    datasets: [{
                        label: 'Requests Submitted',
                        data: monthTotals
                    }]
                }
            }
        );
    </script>
</body>

</html>