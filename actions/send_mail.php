<?php
	require_once('../assets/config.php');
	
	if (isset($_POST['token'])) {
		
		$job_token = mysql_real_escape_string($_POST['token']);
		$query = 'SELECT email FROM submitted_jobs WHERE job_token = :job_token LIMIT 1';
		$query_params = array(':job_token' => $job_token);
	
		try { 
			$stmt = $db->prepare($query);
			$stmt->execute($query_params);
		} catch (PDOException $ex) { die(); }
		if ($stmt->rowCount() > 0) {
		
			$row = $stmt->fetch();
			$to = $row['email'];
			$subject = 'VARIFI job submission';
		
			$message = '
				<html>
					<head>
						<title>' . $subject . '</title>
					</head>
					<body>
						<p>
							Your VARIFI job was successfully submitted! Your unique job token is <b>' . $job_token . '</b>. 
                            The average VARIFI job takes 6-8 hours to complete. 
							You will be notified as soon as your job has been completed. Visit the following links to view
							job progress and access the results.
						</p>
						<p>
							View job progress <a href="' . $config["absolute_path"] . 'job_progress.php?token=' . $job_token . '">here</a>.
						</p>
						<p>
							View job results <a href="' . $config["absolute_path"] . 'job_results.php?token=' . $job_token . '">here</a>.
						</p>
						<p style="font-style: italic">
							This is an automatically generated email - please do not reply to it. If you have any questions please email <a href="mailto:peter.venhuizen@univie.ac.at?Subject=VARIFI%20questions">Peter Venhuizen</a>
						</p>
					</body>
				</html>
			';	
		
			$headers = 'MIME-Version: 1.0' . "\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

			// Additional headers
			$headers .= 'To: ' . $to . "\r\n";
			$headers .= 'From: no_reply@varifi.at' . "\r\n";
		
			mail($to, $subject, $message, $headers);
			
		}
	}
	
?>
