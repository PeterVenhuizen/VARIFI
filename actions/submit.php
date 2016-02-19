<?php

    header('Content-type: application/json');
	ini_set('display_errors', 1);error_reporting(E_ALL); 
    require_once('../assets/config.php');
    include('../assets/functions.php');

	// Make sure the job_token is unique
    $is_bad_token = True;
    while ($is_bad_token) {
		$token = generate_token(8);
		$n_matches = $mysqli->query("SELECT COUNT(*) AS n_matches FROM submitted_jobs WHERE job_token = '$token'")->fetch_object()->n_matches;
		if (!$n_matches) { $is_bad_token = False; }
    }

	// Initialize upload status messages
    $messages = array(
        'bed' => '',
        'read' => '',
        'hotspot' => '',
        'email' => '',
        'status' => 'ERROR: Your job has not been submitted! <br> Please check the individual errors and try again.',
        'token' => $token
    );
    $submit = true;
    $checks = array();

	// Proceed to uploading
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Get email
        $email = $_POST['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $messages['email'] = 'ERROR: Invalid email!'; $submit = false; }

		// Get IP or remote address
        $ip_addr = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] );

		// Get regions of interest
        if (@is_uploaded_file($_FILES['upl-bed-file']['tmp_name'])) { 
            //$bed_file = $messages['token'] . '.bed.' . pathinfo($_FILES['upl-bed-file']['name'], PATHINFO_EXTENSION);
            $bed_file = $_FILES['upl-bed-file']['name'];
        } else if ( in_array($_FILES['upl-bed-file']['error'], array(1, 2)) ) {
        	$messages['bed'] = 'ERROR: Your bed file exceeds the maximum upload limit. Please limit files to 400Mb';
        	$submit = false;
        } else { 
        	$messages['bed'] = 'ERROR: A bed file is required!';
        	$submit = false;
        }
        
        // Get read file
        if (@is_uploaded_file($_FILES['upl-read-file']['tmp_name'])) { 
            $read_file = $messages['token'] . '.' . pathinfo($_FILES['upl-read-file']['name'], PATHINFO_EXTENSION);
        } else if ( in_array($_FILES['upl-read-file']['error'], array(1, 2)) ) {
        	$messages['read'] = 'ERROR: Your read file exceeds the maximum upload limit. Please limit files to 400Mb';
        	$submit = false;
        } else { 
        	$messages['read'] = 'ERROR: A read file is required!'; 
        	$submit = false; 
        }

		// Get hotspot file
		if (@is_uploaded_file($_FILES['upl-hotspot-file']['tmp_name'])) {
			$hotspot_file = $_FILES['upl-hotspot-file']['name'];
		} else if ( in_array($_FILES['upl-hotspit-file']['error'], array(1, 2)) ) {
			$messages['hotspot'] = 'ERROR: Your hotspots file exceeds the maximum upload limit. Please limit files to 400Mb';
			$submit = false;
		} else { $hotspot_file = ''; }

        if ($submit) {

            // Add to submitted_jobs
            $query = 'INSERT INTO submitted_jobs (job_token, email, ip_addr) VALUES (:job_token, :email, :ip_addr)';
            $query_params = array(':job_token' => $messages['token'], ':email' => $email, ':ip_addr' => $ip_addr);
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($query_params);
            } catch (PDOException $ex) { die(array_push($checks, false)); }

            // Add to running_jobs
            $query = 'INSERT INTO job_info (job_token, read_file, bed_file, hotspot_file) VALUES (:job_token, :read_file, :bed_file, :hotspot_file)';
            $query_params = array(':job_token' => $messages['token'], ':read_file' => $read_file, ':bed_file' => $bed_file, ':hotspot_file' => $hotspot_file);
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($query_params);
            } catch (PDOException $ex) { die(array_push($checks, false)); }
            
            // Create upload folder
            $path = '/project/varifi/html/uploads/' . $messages['token'] . '/';
			try {
				if (!file_exists($path)) {
					mkdir($path, 0755, true);
					
					// Upload regions of interest
					if (@is_uploaded_file($_FILES['upl-bed-file']['tmp_name'])) {
						if (move_uploaded_file($_FILES['upl-bed-file']['tmp_name'], $path . $bed_file)) {
							$messages['bed'] = 'Bed file uploaded!';
						} else {
							array_push($checks, false);
							$messages['bed'] = 'ERROR: Upload failed!';
							$messages['status'] = 'ERROR: Job submission failed. Please try again.';
						}
					} else {
						array_push($checks, false);
						$messages['bed'] = 'ERROR: Bad request!';
						$messages['status'] = 'ERROR: Job submission failed. Please try again.';
					}
					
					// Upload read file
					if (@is_uploaded_file($_FILES['upl-read-file']['tmp_name'])) {
						if (!in_array($_FILES['upl-read-file']['error'], array(1, 2))) {
							if (move_uploaded_file($_FILES['upl-read-file']['tmp_name'], $path . $read_file)) {
								$messages['read'] = 'Read file uploaded!';
							} else { 
								array_push($checks, false);
								$messages['read'] = 'ERROR: Upload failed!';
								$messages['status'] = 'ERROR: Job submission failed. Please try again.';
							}
						} else {
							array_push($checks, false);
							$messages['read'] = 'ERROR: Read file is too big. Please limit input file to 400Mb!';
							$messages['status'] = 'ERROR: Job submission failed. Please try again.';
						}
					} else {
						array_push($checks, false);
						$messages['read'] = 'ERROR: Bad request!';
						$messages['status'] = 'ERROR: Job submission failed. Please try again.';
					}
					
					// Upload hotspot file
					if (@is_uploaded_file($_FILES['upl-hotspot-file']['tmp_name'])) {
						if (!move_uploaded_file($_FILES['upl-hotspot-file']['tmp_name'], $path . $hotspot_file)) {
							$messages['status'] = 'ERROR: Job submission failed. Please try again.';
						} else {
							array_push($checks, false);
							$messages['hotspot'] = 'ERROR: Bad request!';
							$messages['status'] = 'ERROR: Job submission failed. Please try again.';
						}
					}
			
				}
			} catch (ErrorException $ex) { die($ex->getMessage()); }
            
            // Check if there were not errors
			if (!in_array(false, $checks)) {
	            $messages['status'] = 'Job successfully submitted! Your job token is <span id="job_token">' . $messages['token'] . '</span>.<br>Estimated running time is between 6-8 hours. Check <a href="job_progress.php?token=' . $messages['token'] . '">this</a> page for your job progress information.<br>An confirmation message has been sent to you. If you don\'t receive this email shortly, please check your spam folder.';
	        }
            
        }
    }

    echo json_encode($messages);
    
?>
