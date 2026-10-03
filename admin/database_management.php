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
FROM users u
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
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SIMSAP</title>

    <link rel="stylesheet" href="../CSS/dashboard.css?v=<?= time(); ?>">
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
                        <a href="../about.html">About</a>
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
                                        <td><?= htmlspecialchars($category['category_id']); ?>
                                        </td>
                                        <td>

                                            <form method="POST" action="../includes/edit-category.php" class="inline-edit-form">
                                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['category_id']); ?>">

                                                <input type="text" name="category_name" value="<?= htmlspecialchars($category['category_name']); ?>"
                                                    class="edit-input"
                                                    readonly
                                                    required>

                                                <button type="button" class="edit-toggle-button">Edit</button>

                                            </form>

                                        </td>
                                        <td>
                                            <form method="POST" action="../includes/delete-category.php" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['category_id']); ?>">

                                                <button type="submit" class="delete-button">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="category_form" style="display: none;">
                    <form
                        method="POST"
                        action="../includes/add-category.php">

                        <label for="category_name">
                            Category Name
                        </label>
                        <input type="text" id="category_name" name="category_name" placeholder="Enter category name" required>

                        <button type="submit">Add</button>
                        <button type="button" id="cancel_category">Cancel</button>
                    </form>
                </div>
            </section>
            <section class="management-section">
                <div class="section-heading">
                    <div>
                        <h2>User Account Management</h2>

                        <p>
                            View and Manage registered SIMSAP accounts.
                        </p>
                    </div>

                </div>
                <div class="table wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Account created</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6">No user accounts found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $account): ?>
                                    <tr>

                                        <td>
                                            <?= htmlspecialchars($account['user_id']); ?>
                                        </td>

                                        <td>
                                            <input type="text" name="name" form="edit-user-<?= htmlspecialchars($account['user_id']); ?>"
                                                value="<?= htmlspecialchars($account['name']); ?>"
                                                class="edit-input" readonly required>
                                        </td>

                                        <td>
                                            <input type="email" name="email" form="edit-user-<?= htmlspecialchars($account['user_id']); ?>"
                                                value="<?= htmlspecialchars($account['email']); ?>"
                                                class="edit-input" readonly required>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($account['role']); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                date(
                                                    'd M Y',
                                                    strtotime($account['created_at'])
                                                )
                                            ); ?>
                                        </td>

                                        <td>

                                            <form method="POST" action="../includes/edit-user.php"
                                                id="edit-user-<?= htmlspecialchars($account['user_id']); ?>"
                                                class="inline-edit-form">

                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($account['user_id']); ?>">

                                                <button type="button" class="edit-toggle-button">Edit</button>

                                            </form>

                                            <?php if ($account['user_id'] == $userId): ?>

                                                <span>Current Account</span>

                                            <?php else: ?>

                                                <form method="POST" action="../includes/delete-user.php"
                                                    onsubmit="return confirm('Are you sure you want to delete this user account?');">

                                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($account['user_id']); ?>">

                                                    <button type="submit" class="delete-button">Delete</button>

                                                </form>
                                            <?php endif; ?>
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
<script>
    $(document).ready(function() {
        $('#category_btn').on('click', function() {
            $('#category_form').slideDown();
            $('#category_name').focus();
        });

        $('#cancel_category').on('click', function() {
            $('#category_form').slideUp();
            $('#category_name').val('');
        });

        $('.edit-toggle-button').on('click', function() {
            const button = $(this);
            const form = button.closest('.inline-edit-form');
            const formId = form.attr('id');

            if (formId) {
                const inputs = $('[form="' + formId + '"]');

                if (inputs.first().prop('readonly')) {
                    inputs.prop('readonly', false);

                    inputs.first().focus();
                    button.text('Save');
                } else {
                    form.submit();
                }
                return;
            }

            const input =
                form.find('.edit-input');

            if (input.prop('readonly')) {
                input.prop('readonly', false);

                input.focus();

                button.text('Save');
            } else {
                form.submit();
            }
        });
    });
</script>

</html>