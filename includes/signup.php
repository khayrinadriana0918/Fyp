<?php

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $role = $_POST["role"];
    $userIdentifier = $_POST["identify_user"];
    $email = $_POST["email"];
    $pwd = $_POST["pwd"];

// min 8 characters, max 16 characters, at least 1 number and 1  capital letter
    if (strlen($pwd)<8 || strlen($pwd)>16) {
        exit("Password");
    }elseif(!preg_match('/[A-Z]/', $pwd)){
        exit();
    }elseif(!preg_match('/[0-9]/', $pwd)){
        exit();
    }

    try {
        require_once __DIR__ . '/database.php';

        //make sure email don't already exist
        $check = $pdo->prepare("SELECT email FROM users WHERE email= :email");
        $check->execute([':email' => $email]);

        if ($check->rowCount() > 0) {
            exit("Email Already Registered");
        }
        //  ADMIN= SIGN UP WITH REGISTRATION CODE ONLY
        if ($role === "system_admin") {

        if (!preg_match('/^[A-Z]{2}[0-9]{5}$/', $userIdentifier)) {
            exit("Invalid admin registration code format.");
        }
            $codeStmt =$pdo->prepare("
            SELECT code_id
            FROM admin_registration_code
            WHERE registration_code = :registration_code
            AND is_used = 0");

            $codeStmt->execute([
                ':registration_code' => $userIdentifier
            ]);
            $validCode =$codeStmt->fetch(PDO::FETCH_ASSOC);

            if (!$validCode) {
                exit("Invalid or already used Admin registration cde.");
            }
        }
        
        $pdo->beginTransaction();

        $query = "INSERT INTO users (name,email,pwd,role) VALUES(:name, :email, :pwd, :role);";

        $stmt = $pdo->prepare($query);

        //named parameters
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":pwd", $pwd);
        $stmt->bindParam(":role", $role);

        $stmt->execute();

        $user = $pdo->lastInsertId();


        if ($role === "system_admin") {
            $adminCode= 'ADM'. $user;

            $query = "INSERT INTO administrator(admin_code,user_id) VALUES(:admin_code,:user_id);";

            $identifyStmt = $pdo->prepare($query);

            $identifyStmt->execute([
                ':admin_code' => $adminCode,
                ':user_id' => $user
            ]);
            $useCodeStmt = $pdo->prepare("
            UPDATE admin_registration_code
            SET
            is_used=1,
            used_by= :user_id,
            used_at= NOW()
            WHERE code_id = :code_id");

            $useCodeStmt->execute([
                ':user_id'=> $user,
                ':code_id'=> $validCode['code_id']
            ]);
        } else if ($role === "head_of_programme") {

            $query = "INSERT INTO head_of_programme(staff_id,user_id) VALUES(:staff_id,:user_id);";

            $identifyStmt = $pdo->prepare($query);

            $identifyStmt->execute([
                ':staff_id' => $userIdentifier,
                ':user_id' => $user
            ]);
        } else if ($role === "student") {

            $query = "INSERT INTO student(student_id,user_id) VALUES(:student_id,:user_id);";

            $identifyStmt = $pdo->prepare($query);
            $identifyStmt->execute([
                ':student_id' => $userIdentifier,
                ':user_id' => $user
            ]);
        }

        $pdo->commit();

        //store user in session
        $_SESSION['user_id'] = $user;


        header("Location: ../index.php");

        exit();
    } catch (PDOException $e) {
        if($pdo->inTransaction()){
            $pdo->rollBack();
        }
        die("Query Failed: " . $e->getMessage());
    }
} else {
    header("Location: ../index.php");
    exit();
}
