<?php

header("Access-Control-Allow-Origin: *");

$id = $_GET['id'];

$html = file_get_contents("http://zinc.docking.org/substance/$id");
$matches = [];
preg_match('/"(http:\/\/[^"]+&f=m)"/', $html, $matches);
$url = $matches[1];
// echo $url;
header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
header("Cache-Control: public"); // needed for internet explorer
header("Content-Type: application/octet-stream");
header("Accept-Ranges: bytes");
header("Content-Disposition: attachment; filename=$id.mol2");
echo file_get_contents($url);
