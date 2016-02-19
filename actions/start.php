<?php
	require_once('../assets/config.php');
	
	if (isset($_POST['token'])) {
		
		// Get read file
		$read_file = $mysqli->query("SELECT read_file FROM job_info WHERE job_token = '$job_token' LIMIT 1")->fetch_object()->read_file;
		
		// Start job
		$job_token = $_POST['token'];
		#$command = 'nohup python VARIFI_wrapper.py -t ' . $job_token . ' > /dev/null 2>&1 & echo $!';
		$command = 'qsub -l hostname=fitch.cibiv.univie.ac.at qsub_VARIFI.sh ' . $job_token . ' /project/varifi/html/uploads/' . $job_token . '/' . $read_file;
		#exec($command, $op);
	
		$time = date('H:i');
		$message = 'time=' . $time . ';step=SUBMITTED;message=Your job has been submitted to the computing queue.;|';
		$query = 'UPDATE job_info SET progress = :message WHERE job_token = :job_token';
		$query_params = array(':message' => $message, ':job_token' => $job_token);
		try {
			$stmt = $db->prepare($query);
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die(); }	

		// Save job p_id in DB
		#$p_id = (int)$op[0];
		#$query = 'UPDATE job_info SET p_id=:p_id WHERE job_token=:job_token';
		#$query_params = array(':p_id' => $p_id, ':job_token' => $job_token);
		#try {
		#	$stmt = $db->prepare($query);
		#	$stmt->execute($query_params);
		#} catch (PDOException $ex) { die(); }
		
	}
	
?>
