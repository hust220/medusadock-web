<?php

header("Access-Control-Allow-Origin: *");

session_start();

require_once('config.php');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = [];
    $id = rand(1,999999999);
    $input['id'] = rand(1, 999999999);
    $input['userid'] = (isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0);

    foreach ($_POST as $k => $v) {
        $input[$k] = $conn->quote($v);
    }

    foreach ($_FILES as $k => $v) {
        $input[$k] = $conn->quote(file_get_contents($v['tmp_name']));
    }

    $query = 'insert into medusadock_tasks ('.implode(', ', array_keys($input)).') values('.implode(', ', array_values($input)).')';
    //$query = 'insert into drugdisco_tasks (id, userid, email, receptor_pdb, binding_center, known_binder, receptor_id, ) values ("' . $contents . '")';
    //$q = mysql_query($query) or die(mysql_error());

    $conn->exec($query);
    echo "Submitted successfully";
}
catch(PDOException $e) {
    echo "Submission failed: " . $e->getMessage();
}
$conn = null;

//$cp = __FILE__;
//$wp = "$cp/../daemon";
//
//if (!file_exists($wp)) {
//    mkdir($wp, 0777, true);
//}


