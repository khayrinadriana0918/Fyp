<?php
require_once '../includes/config.php';

require_once __DIR__.'/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
  header("Location:../index.php");
  exit();
}
$user = $_SESSION['user_id'];

$query = "SELECT users.user_id,users.name,users.created_at,student.student_id 
FROM users
INNER JOIN student ON users.user_id = student.user_id
WHERE users.user_id = :user_id;";

$stmt = $pdo->prepare($query);

$stmt->execute([
    ':user_id'=> $user
]);
$userInfo = $stmt ->fetch(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="css/Homepage.css">
</head>
<?php include __DIR__ . '/includes/programme.php'; ?>
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
                <dt class="info">Student ID:</dt>
                <dd>
                    <?php
                    echo htmlspecialchars($userInfo['student_id']);
                    ?>
                </dd>
                <dt>Student Name:</dt>
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
            <button>Submit New</button>
            <button>Edit Draft</button>
            <button>See All Requests</button>
        </div>
        <div class="Submitted-Req">
            <div>
                <h2>Submitted Requests</h2>
                <div>
                    <!-- show recent submitted request-->
                </div>
            </div>
        </div>
         </div>
        <!-- middle content end -->
        <!-- filter content -->
        <form class="filters" id="req-filters" method="GET">
            <fieldset>
                <legend>Filter Requests</legend>
                <dl>
                    <dt class="sort">
                        <label for="req-search-sort-dropdown">sort by:</label>
                    </dt>
                    <dd class="sort">
                        <select name="req-search[sort_dropdown]" id="req-search-sort-dropdown">
                            <option value="date_updated">Date Updated</option>
                            <option value="date_submitted">Date Submitted</option>
                            <option value="student_name">Student Name</option>
                            <option value="student_id">Student ID</option>
                        </select>
                    </dd>
                    <dd class="tags group">
                        <dl>
                            <!-- priority tags -->
                            <dt id="toggle-priority-tags" class="filter-toggle-collapsed">
                                <button type="button" aria-expanded="false" aria-controls=priority_tags id="priority">Priority</button>
                            </dt>
                            <dd id="priority_tags" hidden class="expandable tags">
                                <ul>
                                    <li>
                                        <label for="search-priority-urgent">
                                            <input type="radio" name="req-search[priority][]" value="urgent" id="search-priority-urgent">
                                            Urgent
                                        </label>
                                    </li>
                                    <li>
                                        <label for="search-priority-medium">
                                            <input type="radio" name="req-search[priority][]" value="medium" id="search-priority-medium">
                                            Medium
                                        </label>
                                    </li>
                                    <li>
                                        <label for="search-priority-medium">
                                            <input type="radio" name="req-search[priority][]" value="medium" id="search-priority-medium">
                                            Low
                                        </label>
                                    </li>
                                </ul>
                            </dd>
                            <!-- status -->
                            <dt id="toggle-status-tags" class="filter-toggle-collapsed">
                                <button type="button" aria-expanded="false" aria-controls=status_tags id="status">Status</button>
                            </dt>
                            <dd id="status_tags" hidden class="expandable tags">
                                <ul>
                                    <li>
                                        <label for="search-status-completed">
                                            <input type="radio" name="req-search[status][]" value="completed" id="search-status-completed">
                                            Completed
                                        </label>
                                    </li>
                                    <li>
                                        <label for="search-status-wip">
                                            <input type="radio" name="req-search[status][]" value="wip" id="search-status-wip">
                                            Work in Progress
                                        </label>
                                    </li>
                                    <li>
                                        <label for="search-status-pending">
                                            <input type="radio" name="req-search[status][]" value="pending" id="search-status-pending">
                                            Pending
                                        </label>
                                    </li>
                                    <li>
                                        <label for="search-status-pending">
                                            <input type="radio" name="req-search[status][]" value="rejected" id="search-status-rejected">
                                            Rejected
                                        </label>
                                    </li>
                                </ul>
                            </dd>
                            <dt id="toggle-programme-tags" class="filter-toggle-collapsed">
                                <button type="button" aria-expanded="false" aria-controls=programme_tags id="programme">Programme</button>
                            </dt>
                            <!-- programme tags -->
                            <dt id="toggle-category-tags" class="filter-toggle-collapsed">
                                <button type="button" aria-expanded="false" aria-controls=category_tags id="category">Category</button>
                            </dt>
                            <dd id="category_tags" hidden class="expandable tags">
                                <ul>
                                    <?php foreach ($categories as $category): ?>
                                        <li>
                                            <label for="search-category-<?php echo htmlspecialchars($category['category_id']); ?>">

                                                <input
                                                    type="radio" name="req-search[category][]"
                                                    value="<?php echo htmlspecialchars($category['category_id']); ?>"
                                                    id="search-category-<?php echo htmlspecialchars($category['category_id']); ?>">

                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                            <!-- category tags -->
                            <dt id="toggle-category-tags" class="filter-toggle-collapsed">
                                <button type="button" aria-expanded="false" aria-controls=category_tags id="category">Category</button>
                            </dt>
                            <dd id="category_tags" hidden class="expandable tags">
                                <ul>
                                    <?php foreach ($categories as $category): ?>
                                        <li>
                                            <label for="search-category-<?php echo htmlspecialchars($category['category_id']); ?>">

                                                <input
                                                    type="radio" name="req-search[category][]"
                                                    value="<?php echo htmlspecialchars($category['category_id']); ?>"
                                                    id="search-category-<?php echo htmlspecialchars($category['category_id']); ?>">

                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </dd>
                        </dl>
                    </dd>
                </dl>
            </fieldset>
        </form>
    </div>
    <!-- javascript -->
    <script>
        document.getElementById('priority').addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            document.getElementById('priority_tags').hidden = expanded;
        });
        document.getElementById('status').addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            document.getElementById('status_tags').hidden = expanded;
        });
        document.getElementById('programme').addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            document.getElementById('programme_tags').hidden = expanded;
        });
    </script>
    <!-- javascript end -->
</body>

</html>