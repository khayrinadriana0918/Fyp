<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$userId = $_SESSION['user_id'];

$adminStmt = $pdo->prepare("
SELECT
u.user_id,
u.name,
a.admin_code
FROM user u
INNER JOIN administrator a
ON u.user_id=a.user_id
WHERE u.user_id=:user_id
");

$adminStmt->execute([
    ':user_id' => $userId
]);

$userInfo = $adminStmt->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    die("Access Denied.");
}

$categoryStmt = $pdo->prepare("
SELECT
category_id,
category_name
FROM category
ORDER BY category_name ASC
");
$categoryStmt->execute();

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

$userStmt = $pdo->prepare("
SELECT 
user_id,
name,
email,
role,
created_at
FROM users
ORDER BY created_at DESC
");
$userStmt->execute();

$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management | SIMSAP</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
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
                        <a href="admin_dashboard.php">
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
                        <a href="database_management.php" class="active">
                            Database Management
                        </a>
                    </li>
                    <li>
                        <a href="admin_requests.php">
                            Requests
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
            <section class="management-section">
                <div class="section-heading">
                    <div>
                        <h2>Category Management</h2>

                        <p>Manage request categories used in SIMSAP.</p>
                    </div>
                    <button type="button" id="category_btn">+ Add Category</button>
                </div>
                <div class="table-wrapper">
                    <table>

                        <thead>
                            <tr>
                                <th>Category ID</th>
                                <th>Category Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categories)): ?>

                                <tr>
                                    <td colspan="3">No categories found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars(
                                                $category['category_id']
                                            ); ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(
                                                $category['category_name']
                                            ); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="delete-button">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>

</html>