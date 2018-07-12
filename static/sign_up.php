<?php

header("Access-Control-Allow-Origin: *");

session_start();

require_once('config.php');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = $_POST['username'];
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];
    $email = $_POST['email'];

    if ($password1 != $password2) {
        exit(json_encode(['status'=>0, 'msg'=>'Passwords are not same!']));
    }

    $password = md5($password1);

    $username_q = $conn->quote($username);
    $password_q = $conn->quote($password);
    $email_q = $conn->quote($email);

    $query = "select * from users where username=$username_q";

    $stmt = $conn->prepare($query); 
    $stmt->execute(); 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        exit(json_encode(['status'=>0, 'msg'=>'Username already exists!']));
    }

    $query = "insert into users(username, password, email) values($username_q, $password_q, $email_q)";

    $stmt = $conn->prepare($query); 
    $stmt->execute(); 

    $query = "select * from users where username=$username_q";

    $stmt = $conn->prepare($query); 
    $stmt->execute(); 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $_SESSION['user'] = $row;
        exit(json_encode(['status'=>1, 'user'=>$row]));
    }
    else {
        exit(json_encode(['status'=>0, 'msg'=>'Sign Up failed!']));
    }
}
catch(PDOException $e) {
    exit(json_encode(['status'=>0, 'msg'=>$e->getMessage()]));
}

$conn = null;

