<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}
?>

<form methd="POST"
    action="../includes/submit-admin-request.php"
    enctype="multipart/form-data">
    <fieldset>
        <legend>Request Form</legend>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" placeholder="Enter Title Here." required><br>

        <label for="label" id="label_container">Label:</label><br>
        <input type="text" id="label" name="label" placeholder="separates each label tags with #" required><br>

        <label for="category">Category:</label>
        <select id="category" name="category_id" required>
        <?php
        $stmt = $pdo->query("SELECT category_id,category_name FROM category");

        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
            '<option value="' . htmlspecialchars($category['category_id']) . '">' .
                htmlspecialchars($category['category_name']) . '</option>';
        }
        ?>
        </select>
        <br>
        <select name="category_id" required>
            <?php
            $stmt = $pdo->query("SELECT category_id,category_name FROM category");

            while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                '<option value="' . htmlspecialchars($category['category_id']) . '">' .
                    htmlspecialchars($category['category_name']) . '</option>';
            }
            ?>
        </select>
        <br>

        <label for="priority">Priority:</label>
        <select name="priority">
            <option value="low">low</option>
            <option value="Medium">Medium</option>
            <option value="Urgent">Urgent</option>
        </select>
        <br>

        <label for="description">Describe your request/issue regarding the system:</label><br>
        <textarea id="description" name="description" rows="10" cols="100" placeholder="Enter your request/issue here." required></textarea>
        <br>

        <label for="request_file">File (optional):</label>
        <input type="file" name="request_file" id="request_file" accept=".jpg,.jpeg,.png,.pdf" placeholder="we accept .jpg,.jpeg,.png,.pdf only">
        <br><br>

        <button type="submit">Submit Request</button>

    </fieldset>
</form>
<script>
    
</script>