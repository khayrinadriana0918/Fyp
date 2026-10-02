<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

$user = $_SESSION['user_id'];
// GET LOG IN INFORMATION
$query = "
    SELECT
        users.user_id,
        users.name,
        users.created_at,
        student.student_id AS role_id,
        student.programme_id
    FROM users
    INNER JOIN student
        ON users.user_id = student.user_id
    WHERE users.user_id = :user_id
";

$stmt = $pdo->prepare($query);

$stmt->execute([
    ':user_id' => $user
]);

$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$userInfo) {
    die("User information not found.");
}
if (empty($userInfo['programme_id'])) {
    header("Location: student_dashboard.php");
    exit();
}
if (empty($userInfo['programme_id'])) {
    header("Location: student_dashboard.php");
    exit();
}
// EDIT EXISTING REQUEST
$editMode = false;
$editRequest = null;

if (isset($_GET['edit']) && $_GET['edit'] !== '') {
    $requestId = $_GET['edit'];

    $editStmt = $pdo->prepare("
    SELECT r.*
    FROM request r
    INNER JOIN student s
    ON r.student_id = s.student_id
    WHERE r.request_id= :request_id
    AND s.user_id= :user_id
    ");

    $editStmt->execute([
        ':request_id' => $requestId,
        ':user_id' => $user
    ]);

    $editRequest = $editStmt->fetch(PDO::FETCH_ASSOC);

    if (!$editRequest) {
        die("Request not found or access denied.");
    }

    $editMode = true;
}
?>
<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>Submit Request | SIMSAP</title>
    <link rel="stylesheet" href="../CSS/request.css">
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
                        Hi, <?= htmlspecialchars($userInfo['name']); ?>
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
                        <a href="student_dashboard.php">
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
                        <a href="student_hop_requests.php" class="programme-required" id="active">
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
         FORM CONTENT
    ====================================================== -->
        <main class="middle-content">
            <form id="form" class="form" method="POST"
                action="../includes/submit-student-request.php"
                enctype="multipart/form-data">
                <?php if ($editMode): ?>
                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($editRequest['request_id']); ?>">
                <?php endif; ?>
                <fieldset>
                    <legend>
                        <?= $editMode ? 'Edit Request Form' : 'Request Form'; ?>
                    </legend>
                    <div class="semester-box">
                        <label for="semester" id="semester_container">Current Semester:<span id="current_sem"></span></label><br>
                        <input type="range" min="1" max="20" value="<?= $editMode ? htmlspecialchars($editRequest['semester']) : '1'; ?>" class="slider" id="semRange" name="semester" required>

                    </div>
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?= $editMode ? htmlspecialchars($editRequest['title']) : ''; ?>" placeholder="Enter Title Here." required>
                    <br><br>

                    <div class=label-box>
                        <label for="label" id="label_container">Labels:</label><br>
                        <small>separates each label tags with space</small><br>
                        <!-- the tag labels separate and in a sphere each-->
                        <input type="text" id="label" name="label" value="<?= $editMode ? htmlspecialchars($editRequest['label']) : ''; ?>" placeholder="e.g: time-strict, bug_report">

                    </div>
                    <br>

                    <div class="category-box">
                        <label for="c_id">Category:</label>
                        <select name="c_id" required>
                            <!-- keep 'other' the last while alphabetical -->
                            <?php
                            $stmt = $pdo->query("SELECT * FROM category ORDER BY CASE
                WHEN category_name = 'Other'THEN 1 
                ELSE 0
                END,
                category_name ASC");

                            while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                $selected = '';

                                if ($editMode && $category['category_id'] == $editRequest['category_id']) {
                                    $selected = 'selected';
                                }
                                echo '<option value="' .
                                    htmlspecialchars($category['category_id']) .
                                    '" ' . $selected . '>' .
                                    htmlspecialchars($category['category_name']) .
                                    '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <br>

                    <?php if (!$editMode): ?>
                        <div class="priority-box">
                            <label for="priority">Priority:</label>
                            <select name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <br>
                    <?php endif; ?>

                    <div class="desc-box">
                        <label for="desc">Describe your request/issue regarding the system:</label><br>
                        <textarea id="desc" name="desc" rows="10" cols="100" placeholder="Enter your request/issue here." required><?= $editMode ? htmlspecialchars($editRequest['description']) : ''; ?></textarea>
                    </div>
                    <br>

                    <div class="file-box">
                        <label for="request_file">File (optional):</label><br>
                        <small>.jpg,.jpeg,.png,.pdf only</small><br>
                        <input type="file" name="request_file" id="request_file" accept=".jpg,.jpeg,.png,.pdf">

                        <?php if (
                            $editMode && !empty($editRequest['request_file'])
                        ): ?>
                            <p>Current file:
                                <?= htmlspecialchars($editRequest['request_file']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <br>

                    <!-- <button type="button" id="preview_btn">Preview Request</button> -->
                    <button type="button" id="preview_btn">Preview Request</button>

                    <p id="req_msg"></p>

                </fieldset>
            </form>
            <div id="request_preview" style="display: none;">
                <h2>Preview Request</h2>

                <p>
                    <strong>Semester:</strong>
                    <span id="preview_semester"></span>
                </p>

                <p>
                    <strong>Title:</strong>
                    <span id="preview_title"></span>
                </p>
                <p>
                    <strong>Labels:</strong>
                    <span id="preview_label"></span>
                </p>
                <p>
                    <strong>Category:</strong>
                    <span id="preview_category"></span>
                </p>
                <?php if (!$editMode): ?>
                    <p><strong>Priority:</strong> <span id="preview_priority"></span></p>
                <?php endif; ?>
                <p>
                    <strong>Description:</strong>
                </p>
                <p id="preview_description"></p>
                <p>
                    <strong>File:</strong>
                    <span id="preview_file"></span>

                    <button type="button" id="remove_file_btn" style="display: none;">
                        x
                    </button>
                </p>

                <button type="button" id="edit_btn">
                    Edit Request
                </button>
                <button type="button" id="confirm_btn">
                    Confirm Submit
                </button>

            </div>
        </main>
    </div>
</body>

</html>
<!-- semester slider -->
<script>
    const $slider = $('#semRange');
    const $output = $('#current_sem');

    $output.text($slider.val());

    $slider.on("input", function() {
        $output.text($(this).val());
    });

    // PREVIEW REQUEST
    $('#preview_btn').on('click', function() {
        // Check required fields first
        const form = document.getElementById('form');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Get information from form
        const semester = $('#semRange').val();
        const title = $('#title').val();
        const label = $('#label').val();
        const category =
            $('select[name="c_id"] option:selected').text();

        <?php if (!$editMode): ?>

            const priority =
                $('select[name="priority"]').val();

            $('#preview_priority').text(priority);

        <?php endif; ?>

        const description = $('#desc').val();

        const fileInput =
            document.getElementById('request_file');

        // Put information into preview
        $('#preview_semester').text('Semester ' + semester);
        $('#preview_title').text(title);
        $('#preview_label').text(
            label || 'No labels'
        );
        $('#preview_category').text(category);
        $('#preview_description').text(description);

        // File name
        if (fileInput.files.length > 0) {
            $('#preview_file').text(
                fileInput.files[0].name
            );
            $('#remove_file_btn').show();
        } else {
            $('#preview_file').text(
                'No file attached'
            );
            $('#remove_file_btn').hide();
        }
        //Remove file
        $('#remove_file_btn').on('click', function() {

            //clear file input
            $('#request_file').val('');

            //change preview
            $('#preview_file').text('No file attached');

            //hide remove btn
            $('#remove_file_btn').hide();
        });

        // Hide form
        $('#form').hide();

        // Show preview
        $('#request_preview').show();
    });
    // GO BACK AND EDIT

    $('#edit_btn').on('click', function() {
        $('#request_preview').hide();
        $('#form').show();

    });

    // CONFIRM AND SEND TO DATABASE

    $('#confirm_btn').on('click', function() {
        let form =
            document.getElementById('form');

        let formData =
            new FormData(form);

        $.ajax({
            url: <?= $editMode
                        ? "'../includes/update-student-request.php'"
                        : "'../includes/submit-student-request.php'"; ?>,

            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function(response) {
                if (response.success) {
                    <?php if ($editMode): ?>
                        window.location.href = 'student_requests.php';
                    <?php else: ?>

                        $('#request_preview').hide();
                        $('#form')[0].reset();
                        $('#form').show();
                        $('#req_msg').text(
                            response.message
                        );
                    <?php endif; ?>
                } else {
                    $('#req_msg').text(
                        response.message
                    );
                }
            },

            error: function() {
                $('#req_msg').text(
                    'Request failed to send.'
                );
            }
        });
    });
</script>