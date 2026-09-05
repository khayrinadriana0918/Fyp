<?php
include('includes/database.php');
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>System</title>
</head>
<style>
    :root{
        --border-radius: 25px;
        --box-shadow: 0 10px 40px rgba(0, 0, 0, 8);
    }
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        
    }

    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        font-family: Georgia, 'Times New Roman', Times, serif
    }

    .container {
        width: 100%;
        min-height: 100vh;
        display: flex;
        background-color: #ffffff;
        flex-direction: row;
        gap: 5px;
        box-shadow: var(--box-shadow);
    }

    #left-content {
        width: 50%;
        background: linear-gradient(135deg,
                #c7d4ff,
                #ffffff);
        min-height: 100vh;
        border-radius:var(--border-radius);
    }

    #right-content {
        display: flex;
        min-height: 100vh;
        width: 50%;
        justify-content: center;
        align-items: center;
    }

    .form-box h1 {
        margin-bottom: 10px;
    }

    .form-box input {
        width: 100%;
        height: 50px;
        border: none;
        border-bottom: 1px solid #aaa;
        outline: none;
        padding: 5px 2px;
        background: transparent;
    }

    .form-box input:focus {
        border-bottom: 2px solid #222;
    }

    .container .form-box {
        padding: 20px;
        margin: 20px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px 5px #c7d4ff inset;
    }
    .form-box{
        display:none;
    }

    .form-box.active{
        display: block;
    }
    button{
        border-radius: var(--border-radius);
        padding: 5px;
    }
    @media (max-width: 800px) {

        .container {
            width: 95%;
            min-height: auto;
            flex-direction: column;
            padding: 15px;
        }

        #left-content {
            width: 100%;
            min-height: 300px;
        }

        #right-content {
            width: 100%;
            padding: 20px;
        }
    }
</style>
<script>
    function showForm(form) {
        document.getElementById("login").classList.remove("active");
        document.getElementById("signup").classList.remove("active");

        document.getElementById(form).classList.add("active");
    }
</script>

<body class="container">
    <div id="left-content">
        <p>this is left</p>
    </div>
    <div id="right-content">
        <div id="signup" class="form-box">
            <form method="post" action="includes/signup.php" onsubmit="return validateSignUp(this)">
                <h1>Signup</h1>
                <hr>
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required><br><br>

                <label for="role">You are? :</label>
                <select id="role" name="role" required>
                    <option value="system_admin">Administrator</option>
                    <option value="student">Student</option>
                    <option value="head_of_programme">Head of Programme</option>
                </select>
                <label for="identify_user" id="identifierLabel"></label>
                <input type="text" id="identify_user" name="identify_user" required><br><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br><br>

                <label for="pwd">Password (max.20 only):</label>
                <input type="password" id="pwd" name="pwd" required maxlength="20"><br><br>

                <button type="submit">Create Account</button>

                <p>
                    Already have an account?
                    <a href="#" onclick="showForm('login')">Login Here</a>

                </p>
            </form>
        </div>

        <div id="login" class="form-box active">
            <form method="post" action="includes/login.php">
                <h1>Login</h1>
                <hr>

                <label for="email">email:</label><br>
                <input type="email" id="email" name="email" required><br><br>
                <label for="pwd">Password (max.20 only):</label><br>
                <input type="password" id="pwd" name="pwd" required><br><br>
                <button type="submit">Enter</button>

                <p>
                    Don't have an account?
                    <a href="#" onclick="showForm('signup')">Create Account</a><br>
                    Forgot password?<a href="reset.php">Change Password</a>
                </p>
            </form>
        </div>
    </div>
</body>
<script>
    const role= document.getElementById("role");
    const identifierLabel=document.getElementById("identifierLabel");
    const idInput= document.getElementById("identify_user");

    role.addEventListener("change", function(){
        
        if(role.value ==="system_admin"){
            identifierLabel.textContent= "Admin Code: ";
            idInput.placeholder= "Enter Admin Code Given";
        }else if(role.value ==="head_of_programme"){
            identifierLabel.textContent= "Staff ID: ";
            idInput.placeholder= "Enter Your Staff ID";
        }else if(role.value ==="student"){
            identifierLabel.textContent= "Student ID: ";
            idInput.placeholder= "Enter Your Student ID";
        }
    });
</script>

</html>