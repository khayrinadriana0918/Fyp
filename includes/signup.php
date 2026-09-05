<?php

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $role = $_POST["role"];
    $userIdentifier = $_POST["identify_user"];
    $email = $_POST["email"];
    $pwd = $_POST["pwd"];

    try {
        require_once __DIR__ . '/database.php';

        //make sure email don't already exist
        $check = $pdo->prepare("SELECT email FROM users WHERE email= :email");
        $check->execute([':email' => $email]);

        if ($check->rowCount() > 0) {
            exit("Email Already Registered");
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

            $query = "INSERT INTO administrator(admin_code,user_id) VALUES(:admin_code,:user_id);";

            $identifyStmt = $pdo->prepare($query);

            $identifyStmt->execute([
                ':admin_code' => $userIdentifier,
                ':user_id' => $user
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


        header("Location: ../err.html");

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
