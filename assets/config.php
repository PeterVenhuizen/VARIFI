<?php	
    $host = "gray";
    $username = "varifi"; 
    $password = "v4r1f1us3r";  
    $dbname = "varifi"; 	

	$mysqli = new mysqli($host, $username, $password, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	} else {
        mysqli_set_charset($mysqli,"utf8");   
    }

	$config['absolute_path'] = 'http://varifi.cibiv.univie.ac.at/';

    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 

    try { 
        $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options); 
    } catch(PDOException $ex) { 
        die("Failed to connect to the database: " . $ex->getMessage()); 
    } 

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

    header('Content-Type: text/html; charset=UTF-8'); 
    session_start();
    ob_start();
