<?php
	require_once('../assets/config.php');
	
	if (isset($_POST['token'])) {
		
		// Start job
		$job_token = $_POST['token'];
		$command = 'nohup python VARIFI_wrapper.py -t ' . $job_token . ' > /dev/null 2>&1 & echo $!';
		exec($command, $op);
		
		// Save job p_id in DB
		$p_id = (int)$op[0];
		$query = 'UPDATE job_info SET p_id=:p_id WHERE job_token=:job_token';
		$query_params = array(':p_id' => $p_id, ':job_token' => $job_token);
		try {
			$stmt = $db->prepare($query);
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die(); }
		
	}
	
?>
