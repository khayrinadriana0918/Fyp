<?php
require_once 'includes/config.php';
require_once __DIR__ . "/includes/database.php";

$showResetAlert = !empty($_SESSION['password_reset']);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $newPassword = trim($_POST['pwd'] ?? '');

    if (strlen($newPassword) < 8 || strlen($newPassword) > 16) {
        $message = "Password must be between 8 and 16 characters.";
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $message = "Password must contain AT LEAST 1 uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $message = "Password must contain AT LEAST 1 number.";
    } else {

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
        UPDATE users
        SET pwd = :pwd
        WHERE email = :email
        ");

        $stmt->execute([
            ':pwd' => $hashedPassword,
            ':email' => $email
        ]);

        if ($stmt->rowCount() > 0) {
            unset($_SESSION['password_reset']);

            header("Location: index.php?reset=success");
            exit();
        } else {
            $message = " Email not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>

    <script
        src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js">
    </script>
</head>
<style>
    :root {
        --border-radius: 25px;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        min-height: 100vh;
        font-family: Georgia, 'Times New Roman', Times, serif;
    }

    /* Main page */
    .container {
        width: 100%;
        min-height: 100vh;

        display: flex;
        justify-content: center;
        align-items: center;

        background-color: #ffffff;
    }

    /* Reset form */
    .container .form-box {
        width: 400px;
        padding: 30px;

        border-radius: 10px;
        background-color: #ffffff;

        box-shadow: 0 0 10px 5px #c7d4ff inset;
    }

    /* Inputs */
    .form-box input {
        width: 100%;
        height: 50px;

        border: none;
        border-bottom: 1px solid #aaa;

        outline: none;
        padding: 5px 2px;

        background: transparent;
    }

    /* Password requirements */
    .password_tip {
        margin: 10px 0;
    }

    .valid {
        color: green;
    }

    a:link {
        color: var(--link-base);
    }

    a:visited {
        color: var(--link-visited);
    }

    a:hover {
        border: 1px dashed #aaaaaa;
        color: var(--link-hover);
    }


    .invalid {
        color: red;
    }

    /* Button */
    button {
        border-radius: var(--border-radius);
        padding: 8px 20px;
        cursor: pointer;
    }

    /* Mobile */
    @media (max-width: 800px) {
        .container .form-box {
            width: 90%;
            max-width: 400px;
            padding: 25px;
        }
    }
</style>
<!-- =============================================== -->
<!--CSS END HERE-->
<!-- =============================================== -->

<body class="container">
    <?php if ($showResetAlert): ?>
        <script>
            alert("Your current password does not meet the new password requirements. Please create a new password.");
        </script>
    <?php endif; ?>

    <div id="reset" class="form-box">
        <h2>Reset Password</h2>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            New Password:<br>
            <input type="password" id="reset_pwd" name="pwd" minlength="8" maxlength="16" pattern="(?=.*[A-Z])(?=.*[0-9]).{8,16}"
                title="Password must be 8-16 characters and contain at least one uppercase letter and one number." required>
            <div class="password_tip">
                <p>Password must contain:</p>
                <p id=length_check class="invalid">X 8-16 characters</p>
                <p id=capital_check>X <strong>AT LEAST</strong> 1 capital letters</p>
                <p id=number_check>X <strong>AT LEAST</strong> 1 number</p>
            </div><br><br>

            <button type="submit">Reset Password</button>
            <p>Go back to <a href="index.php">Login</a></p>
        </form>
    </div>
    <script>
        $('#reset_pwd').on('input', function() {

            const password = $(this).val();

            // 8-16 characters
            if (password.length >= 8 && password.length <= 16) {
                $('#length_check')
                    .text('✔ 8–16 characters')
                    .removeClass('invalid')
                    .addClass('valid');
            } else {
                $('#length_check')
                    .text('✖ 8–16 characters')
                    .removeClass('valid')
                    .addClass('invalid');
            }

            // Capital letter
            if (/[A-Z]/.test(password)) {
                $('#capital_check')
                    .text('✔ At least 1 capital letter')
                    .removeClass('invalid')
                    .addClass('valid');
            } else {
                $('#capital_check')
                    .text('✖ At least 1 capital letter')
                    .removeClass('valid')
                    .addClass('invalid');
            }

            // Number
            if (/[0-9]/.test(password)) {
                $('#number_check')
                    .text('✔ At least 1 number')
                    .removeClass('invalid')
                    .addClass('valid');
            } else {
                $('#number_check')
                    .text('✖ At least 1 number')
                    .removeClass('valid')
                    .addClass('invalid');
            }

        });
    </script>
</body>

</html>