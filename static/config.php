<?php

$config = json_decode(file_get_contents("config.json"), true);

$servername = $config['db']['host'];
$username   = $config['db']['user'];
$password   = $config['db']['password'];
$database   = $config['db']['database'];

