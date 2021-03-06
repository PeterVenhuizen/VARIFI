<?php
	require_once('../assets/config.php');
	include '../assets/functions.php';
	
	if (isset($_POST['token']) || isset($_GET['token'])) {
		
		// Start job
		$job_token = (isset($_POST['token']) ? $_POST['token'] : $_GET['token']);
		$path = '/project/varifi/html/uploads/' . $job_token;

		// Remove files and folder
		rrmdir($path);
  
		// Remove from DB
		try {
			$stmt = $db->prepare("DELETE FROM job_info WHERE BINARY job_token = :job_token");
			$stmt->bindValue(':job_token', $job_token, PDO::PARAM_STR);
			$stmt->execute();
		} catch (PDOException $ex) {  }
		
		try {
			$stmt = $db->prepare("DELETE FROM submitted_jobs WHERE BINARY job_token = :job_token");
			$stmt->bindValue(':job_token', $job_token, PDO::PARAM_STR);
			$stmt->execute();
		} catch (PDOException $ex) {  }
	}
	
?>
