<?php

require_once '../includes/config.php';

require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Request ID is missing.");
}

$requestId = $_GET['id'];

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
    WHERE ar.ar_request_id = :request_id
";

$stmt = $pdo->prepare($query);

$stmt->execute([':request_id' => $requestId]);

$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    die("Request not found");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details | SIMSAP</title>

    <link rel="stylesheet" href="../CSS/req_details.css">
</head>

<body>
    <main>
        <div class="request-top">
            <div class="requester">
                <h2>
                    <?= htmlspecialchars($request['requester_name']); ?>

                    <span>(<?= htmlspecialchars($request['requester_id']); ?>)</span>
                </h2>
            </div>
            <div class="priority">
                <span class="help-icon">?</span>

                <span>Priority:</span>

                <span class="priority-value">
                    <?= htmlspecialchars($request['ar_priority']); ?>
                </span>
            </div>
        </div>
        <!------------            
        -----top
        -------------->

        <p class="request_id">
            Request ID:
            <?= htmlspecialchars($request['ar_request_id']); ?>
        </p>
        <!------------            
        -----Middle
        -------------->
        <h1 class="request_title">
            <?= htmlspecialchars($request['ar_title']); ?>
        </h1>

        <div class="category">
            <h2>Issue Category:</h2>
            <span><?= htmlspecialchars($request['category_name']); ?></span>
        </div>

        <div class="label-status">
            <div class="labels">
                <strong>Tags: </strong>

                <?= !empty($request['ar_label'])
                    ? htmlspecialchars($request['ar_label'])
                    : 'No tags'; ?>
            </div>

            <div class="status">
                <strong>Status: </strong>

                <?= htmlspecialchars($request['ar_stats']) ?>
            </div>

            <div class="updated-date">
                Submission Date:
                <?= htmlspecialchars(
                    date(
                        'd/m/y',
                        strtotime($request['ar_submission_date'])
                    )
                ); ?>
            </div>
        </div>
        <!------------            
        -----bottom
        -------------->
        <div class="req-bottom">
            <section class="description">
                <h2>Description</h2>

                <p>
                    <?= nl2br(
                        htmlspecialchars($request['ar_description'])
                    ); ?>
                </p>
            </section>

            <div class="attachment">
                <h3>Attachment</h3>

                <?php if (!empty($request['ar_request_file'])): ?>

                    <a
                        href="../uploads/admin_hop/<?= rawurlencode($request['ar_request_file']); ?>"
                        target="_blank"
                        class="file-button">

                        <?= htmlspecialchars(
                            $request['ar_request_file']
                        ); ?>

                    </a>

                <?php else: ?>

                    <p>No attachment</p>

                <?php endif; ?>
            </div>

            <!------------            
        -----ADMIN ACTIONS
        -------------->
            <div class="admin-actions">
                <select id="request_action">
                    <option value="" selected disabled>
                        Actions...
                    </option>

                    <option value="status">
                        Change status
                    </option>

                    <option value="priority">
                        Change priority
                    </option>

                    <option value="history">
                        See change history
                    </option>
                </select>

            </div>
            <!--change status-->
            <div id="status_form" class="status-form">
                <form method="POST" action="../includes/update-request-status.php">
                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['ar_request_id']); ?>">

                    <label for="new_status">
                        Status:
                    </label>

                    <select name="status" id="new_status" required>
                        <option value="Pending"
                            <?= $request['ar_stats'] === 'Pending'
                                ? 'selected' : ''; ?>>
                            Pending
                        </option>

                        <option value="In Progress"
                            <?= $request['ar_stats'] === 'In Progress'
                                ? 'selected' : ''; ?>>
                            In Progress
                        </option>

                        <option value="Completed"
                            <?= $request['ar_stats'] === 'Completed'
                                ? 'selected' : ''; ?>>
                            Completed
                        </option>

                        <option value="Rejected"
                            <?= $request['ar_stats'] === 'Rejected'
                                ? 'selected' : ''; ?>>
                            Rejected
                        </option>

                    </select>
                </form>
            </div>
            <!--change priority-->
            <div id="status_form" class="status-form">
                <form method="POST" action="../includes/update-request-status.php">
                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['ar_request_id']); ?>">

                    <label for="new_status">
                        Status:
                    </label>

                    <select name="status" id="new_status" required>
                        <option value="Pending"
                            <?= $request['ar_stats'] === 'Pending'
                                ? 'selected' : ''; ?>>
                            Pending
                        </option>

                        <option value="In Progress"
                            <?= $request['ar_stats'] === 'In Progress'
                                ? 'selected' : ''; ?>>
                            In Progress
                        </option>

                        <option value="Completed"
                            <?= $request['ar_stats'] === 'Completed'
                                ? 'selected' : ''; ?>>
                            Completed
                        </option>

                        <option value="Rejected"
                            <?= $request['ar_stats'] === 'Rejected'
                                ? 'selected' : ''; ?>>
                            Rejected
                        </option>

                    </select>
                </form>
            </div>
            <button type="submit">Save</button>
        </div>

        <a href="admin_requests.php">Back to Requests</a>
    </main>
    <script>
        const actionSelect =
            document.getElementById('request_action');

        const statusForm =
            document.getElementById('status_form');

        actionSelect.addEventListener('change', function() {
            if (this.value === 'status') {
                statusForm.style.display = 'block';
            }
        });
    </script>
</body>

</html>