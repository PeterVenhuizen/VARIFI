<?php
	require_once('assets/config.php');
	date_default_timezone_set('Europe/Vienna');
	
	if (isset($_POST['token'])) {
		
		$job_token = $_POST['token'];

		// Get read file
		$read_file = $mysqli->query("SELECT read_file FROM job_info WHERE job_token = '$job_token' LIMIT 1")->fetch_object()->read_file;
		
		// Start job
		$cmd = 'SGE_ROOT=/software/sge-2011.11 ';
		$cmd .= 'SGE_QMASTER_PORT=6454 ';
		$cmd .= 'SGE_EXECD_PORT=6455 ';
		$cmd .= '/software/sge-2011.11/bin/linux-x64/qsub /project/varifi/html/actions/qsub_VARIFI.sh ' . $job_token . ' /project/varifi/html/uploads/' . $job_token . '/' . $read_file;
		#$cmd .= ' >> /project/varifi/html/qsub_error.log 2>&1';
		#exec("echo " . $cmd . " >> /project/varifi/html/qsub_error.log");
		exec($cmd);
		
		$time = date('H:i');
		$message = 'time=' . $time . ';step=SUBMITTED;message=Your job has been submitted to the computing queue.;|';
		$query = 'UPDATE job_info SET progress = :message WHERE job_token = :job_token';
		$query_params = array(':message' => $message, ':job_token' => $job_token);
		try {
			$stmt = $db->prepare($query);
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die(); }	

		
		// Set recent jobs token
		$cookie_tokens = (isset($_COOKIE['varifi_rj']) ? $job_token . '|' . $_COOKIE['varifi_rj'] : $job_token);
		setcookie('varifi_rj', $cookie_tokens, time() + (86400 * 8), '/');
		
	}
	
?>
