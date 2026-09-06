<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}
?>

<form method="POST"
    action="../includes/submit-admin-request.php"
    enctype="multipart/form-data">
    <fieldset>
        <legend>Request Form</legend>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" placeholder="Enter Title Here." required><br>

        <label for="label" id="label_container">Labels:</label><br>
        <small>separates each label tags with space</small><br>

        <div id=label-box>

            <div id="label-list"></div>

            <input type="text" id="labelInput" placeholder="Type Label Here">
        </div>
        <input type="hidden" id="label-hidden" name="label">

        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">

        <br>
        <span><label for="category_id">Category:</label>
            <select name="category_id" required>
                <?php
                $stmt = $pdo->query("SELECT category_id,category_name FROM category");

                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . htmlspecialchars($category['category_id']) . '">' .
                        htmlspecialchars($category['category_name']) . '</option>';
                }
                ?>
            </select>
            <br>

            <label for="priority">Priority:</label>
            <select name="priority">
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Urgent">Urgent</option>
            </select>
            <br>

            <label for="description">Describe your request/issue regarding the system:</label><br>
            <textarea id="description" name="description" rows="10" cols="100" placeholder="Enter your request/issue here." required></textarea>
            <br>

            <label for="request_file">File (optional):</label><br>
            <small>.jpg,.jpeg,.png,.pdf only</small><br>
            <input type="file" name="request_file" id="request_file" accept=".jpg,.jpeg,.png,.pdf">
            <br><br>

            <button type="submit">Submit Request</button>

    </fieldset>
</form>
<script>
    $(document).ready(function() {
        let labels = [];

        function updateHiddenInput() {
            $('#label-hidden').val(labels.join(' '));
        }

        function addLabel(value) {
            value = $.trim(value);

            if (value === '') {
                return;

            }

            labels.push(value);

            let tag = $('<span>').addClass('label-tag').attr('data-label', value);

            let text = $('<span>').text(value);

            let removeBtn = $('<button>').attr('type', 'button').addClass('label-remove').text('x');

            tag.append(text, removeBtn);
            $('#label-list').append(tag);
            $('#labelInput').val('');

            updateHiddenInput();
        }

        $('#labelInput').on('keydown', function(event) {
            if (event.key ===' ') {

                event.preventDefault();
                addLabel($(this).val());
            }
        });

        $('#label-list').on('click', '.label-remove', function() {

            let tag = $(this).closest('.label-tag');
            let labelToRemove = tag.attr('data-label');

            labels = labels.filter(function(label) {
                return label !== labelToRemove;
            });

            tag.remove();

            updateHiddenInput();
        });
    });
</script>