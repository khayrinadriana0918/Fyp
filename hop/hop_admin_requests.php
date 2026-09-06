<?php
require_once '../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

//only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location:../index.php");
    exit();
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<form id="ar_form" method="POST"
    action="../includes/submit-admin-request.php"
    enctype="multipart/form-data">
    <fieldset>
        <legend>Request Form</legend>
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" placeholder="Enter Title Here." required>
        <br><br>

        <div class=label-box>
            <label for="label" id="label_container">Labels:</label><br>
            <small>separates each label tags with space or comma</small><br>
            <!-- the tag labels separate and in a sphere each-->
            <input type="text" id="label" name="label" placeholder="e.g: time-strict, bug_report">

        </div>
        <br>

        <div class="category-box">
            <label for="category_id">Category:</label>
            <select name="category_id" required>
                <!-- keep 'other' the last while alphabetical -->
                <?php
                $stmt = $pdo->query("SELECT * FROM category ORDER BY CASE
                WHEN category_name = 'Other'THEN 1 
                ELSE 0
                END,
                category_name ASC");

                while ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . htmlspecialchars($category['category_id']) . '">' .
                        htmlspecialchars($category['category_name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <br>

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

        <div class="desc-box">
            <label for="description">Describe your request/issue regarding the system:</label><br>
            <textarea id="description" name="description" rows="10" cols="100" placeholder="Enter your request/issue here." required></textarea>
        </div>
        <br>

        <div class="file-box">
            <label for="request_file">File (optional):</label><br>
            <small>.jpg,.jpeg,.png,.pdf only</small><br>
            <input type="file" name="request_file" id="request_file" accept=".jpg,.jpeg,.png,.pdf">
        </div>
        <br>

        <button type="submit">Submit Request</button>

        <p id="req_msg"></p>

    </fieldset>
</form>
<script>
    $('#ar_form').on('submit', function(event){
        event.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: '../includes/submit-admin-request.php',
            type: 'POST',
            data: formData,

            processData:false,
            contentType:false,

            dataType:'json',
            success:function(response){
                if(response.success){
                    $('#req_msg').text(response.message);
                    $('#ar_form')[0].reset();

                }else{
                    $('#req_msg').text(response.message);
                }
            },

            error: function(){
                $('#req_msg').text(
                    'Request fail to send'
                );
            }
        });
    });

</script>