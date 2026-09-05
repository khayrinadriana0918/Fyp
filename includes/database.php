<?php
$dsn= "mysql:host=localhost;dbname=fyprojectdb";
$db_user= "root";
$db_pass="";

try{
    $pdo= new PDO($dsn,$db_user,$db_pass);
    $pdo-> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(PDOexception $e){
    echo"connection failed ".$e->getMessage();
}


