<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]?? '');
    $pwd = $_POST["pwd"]?? '';

    try {
        require_once __DIR__ . '/database.php';

        if (!$pdo || !is_object($pdo)) {
            echo "<script>
                    alert('Database connection failed.');
                    window.history.back();
                  </script>";
            exit();
        }

        $query = "SELECT * FROM users WHERE email = :email;";
        $stmt = $pdo->prepare($query);

        // named parameters
        $stmt->bindParam(":email", $email);

        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Email not found
        if (!$user) {
            echo "<script>
                    alert('Email not found.');
                    window.history.back();
                  </script>";
            exit();
        }

        // Check if password is already hashed
        // Check password
        if (password_verify($pwd, $user['pwd'])) {

            // Password is already hashed

        } elseif (hash_equals($user['pwd'], $pwd)) {

            // Existing account with old plaintext password

            $passwordIsWeak =
                strlen($pwd) < 8 ||
                strlen($pwd) > 16 ||
                !preg_match('/[A-Z]/', $pwd) ||
                !preg_match('/[0-9]/', $pwd);

            if ($passwordIsWeak) {

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['password_reset'] = true;

                header("Location: ../reset.php");
                exit();
            }

            // Convert old plaintext password into a hash
            $newHash = password_hash($pwd, PASSWORD_DEFAULT);

            $updatePassword = $pdo->prepare("
        UPDATE users
        SET pwd = :pwd
        WHERE user_id = :user_id
    ");

            $updatePassword->execute([
                ':pwd' => $newHash,
                ':user_id' => $user['user_id']
            ]);
        } else {

            echo "<script>
            alert('Incorrect password.');
            window.history.back();
          </script>";
            exit();
        }
        // Successful login
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role']=== "system_admin") {
            header("Location: ../admin/admin_dashboard.php");
        }elseif($user['role']=== "head_of_programme"){
            header("Location: ../hop/hop_dashboard.php");
        }elseif ($user['role']=== "student") {
            header("Location: ../student/student_dashboard.php");
        }else{
            echo "
            <script>
            alert('User role unrecognized.');
            window.history.back();
            </script>";
            exit();
        }
        exit();
    }catch(PDOException $e){
        echo"<script>
        alert('Something went wrong. Please try again.');
        window.history.back();
        </script>";
        exit();
    }
}else{
        header("Location: ../index.php");
        exit();
    }
