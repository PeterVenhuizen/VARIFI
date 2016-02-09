<?php
	require_once('../assets/config.php');	
	
	if (isset($_POST['token'])) {
		
		// Start job
		$job_token = mysql_real_escape_string($_POST['token']);
		$path = $_SERVER['DOCUMENT_ROOT'] . '/VARIFI/uploads/';
		
		// Remove files
		$query = 'SELECT read_file, bed_file, extra_file FROM job_info WHERE job_token = :job_token LIMIT 1';
		$query_params = array(':job_token' => $job_token);


		try {
			$stmt = $db->prepare($query);
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die( $ex->getMessage() ); }
	
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			unlink($path . $row['read_file']);
			unlink($path . $row['bed_file']);
			unlink($path . $row['extra_file']);
		}

		// Remove from DB
		try {
			$stmt = $db->prepare("DELETE FROM job_info WHERE job_token = :job_token");
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die( $ex->getMessage() ); }
		
		try {
			$stmt = $db->prepare("DELETE FROM submitted_jobs WHERE job_token = :job_token");
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die( $ex->getMessage() ); }
	}
	
?>
