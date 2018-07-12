<?php

header("Access-Control-Allow-Origin: *");

require_once('config.php');

try {
    $id = $_GET['id'];

    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['item'])) {
        $item = $_GET['item'];

        $query = "select id, $item from medusadock_tasks where id=$id";

        $stmt = $conn->prepare($query); 
        $stmt->execute(); 
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $content = $row[$item];

        if (isset($_GET['suffix'])) {
            $suffix = $_GET['suffix'];

            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Disposition: attachment; filename=$id.$item.$suffix");
        }

        echo $content;
    }
    else {
        $query = "select id, userid, tsubmit, tprocess, tfinish, receptor_pdb, ligand_pdb, status, log, output from medusadock_tasks where id=$id";

        $stmt = $conn->prepare($query); 
        $stmt->execute(); 
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        print_r(json_encode($row));
    }
}
catch(PDOException $e) {
    echo "Check failed: " . $e->getMessage();
}
$conn = null;

