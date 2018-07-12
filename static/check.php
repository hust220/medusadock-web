<?php

header("Access-Control-Allow-Origin: *");

require_once('config.php');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = 'select id, userid, tsubmit, tprocess, tfinish, status from medusadock_tasks order by tsubmit desc';

    $stmt = $conn->prepare($query); 
    $stmt->execute(); 
    $row = $stmt->fetchall(PDO::FETCH_ASSOC);
    $result = ['tasks' => $row];
    print_r(json_encode($result));
}
catch(PDOException $e) {
    echo "Check failed: " . $e->getMessage();
}
$conn = null;

