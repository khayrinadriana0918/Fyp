<?php
require_once 'config.php';

if($_SERVER["REQUEST_METHOD"]== "POST"){
    $email= $_POST["email"];
    $pwd= $_POST["pwd"];

    try {
        require_once __DIR__ . '/database.php';

        if (!$pdo || !is_object($pdo)) {
            die("Database connection failed.");
        }

        $query = "SELECT * FROM users WHERE email = :email;";
        $stmt = $pdo->prepare($query);

    
    //named parameters
        $stmt->bindParam(":email",$email);

        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            die("email not found");
        }
        if ($pwd!=$user["pwd"]) {
            die("incorrect password");
        }
        
        //success login
        $_SESSION['user_id']=$user['user_id'];
        $_SESSION['name']= $user['name'];
        $_SESSION['role']= $user['role'];
        echo "<br>";


        $pdo = null;
        $stmt = null;

        // redirect dashboard based on user role
        if($user['role'] === "system_admin"){
            header("Location: ../admin/admin_dashboard.php");
        }elseif($user['role'] === "head_of_programme"){
            header("Location: ../hop/hop_dashboard.php");
        }elseif($user['role'] === "student"){
            header("Location: ../student/student_dashboard.php");
        }else{
            die("User Role Unrecognized");   
        }

        exit();
    } catch (PDOException $e) {
        die("Query Failed: ".$e->getMessage());
    }
}else{
    header("Location: ../index.php");
}
