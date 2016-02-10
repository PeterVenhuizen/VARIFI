<?php

    header('Content-type: application/json');

    require_once('../assets/config.php');
    include('../assets/functions.php');

    $is_bad_token = True;
    while ($is_bad_token) {
		$token = generate_token(8);
		$n_matches = $mysqli->query("SELECT COUNT(*) AS n_matches FROM submitted_jobs WHERE job_token = '$token'")->fetch_object()->n_matches;
		if (!$n_matches) { $is_bad_token = False; }
    }

    $messages = array(
        'bed' => '',
        'read' => '',
        'email' => 'Email OK!',
        'status' => 'ERROR: Your job has not been submitted! <br> Please check the individual errors and try again.',
        'token' => $token
    );
    $submit = true;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Get DB values        
        $email = $_POST['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $messages['email'] = 'ERROR: Invalid email!'; $submit = false; }
        $ip_addr = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] );

        if (@is_uploaded_file($_FILES['upl-bed-file']['tmp_name'])) { 
            $bed_file = $messages['token'] . '.bed.' . pathinfo($_FILES['upl-bed-file']['name'], PATHINFO_EXTENSION);
        } else { $messages['bed'] = 'ERROR: A bed file is required!'; $submit = false; }
        
        if (@is_uploaded_file($_FILES['upl-read-file']['tmp_name'])) { 
            $read_file = $messages['token'] . '.read.' . pathinfo($_FILES['upl-read-file']['name'], PATHINFO_EXTENSION);
        } else { $messages['read'] = 'ERROR: A read file is required!'; $submit = false; }

        if ($submit) {

            // Add to submitted_jobs
            $query = 'INSERT INTO submitted_jobs (job_token, email, ip_addr) VALUES (:job_token, :email, :ip_addr)';
            $query_params = array(':job_token' => $messages['token'], ':email' => $email, ':ip_addr' => $ip_addr);
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($query_params);
            } catch (PDOException $ex) { die($messages['status'] .= $ex->getMessage()); }

            // Add to running_jobs
            $query = 'INSERT INTO job_info (job_token, read_file, bed_file) VALUES (:job_token, :read_file, :bed_file)';
            $query_params = array(':job_token' => $messages['token'], ':read_file' => $read_file, ':bed_file' => $bed_file);
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($query_params);
            } catch (PDOException $ex) { die($messages['status'] .= $ex->getMessage()); }
            
            // Upload files
            $path = $_SERVER['DOCUMENT_ROOT'] . '/software/varifi/uploads/';

            if (@is_uploaded_file($_FILES['upl-bed-file']['tmp_name'])) {
                if (move_uploaded_file($_FILES['upl-bed-file']['tmp_name'], $path . $bed_file)) {
                //if (move_uploaded_file($_FILES['upl-bed-file']['tmp_name'], $path . $_FILES['upl-bed-file']['name'])) {
                    $messages['bed'] = 'Bed file uploaded!';
                } else {
                    $messages['bed'] = 'ERROR: Upload failed!';
                    $messages['status'] = 'ERROR: Job submission failed. Please try again.';
                }
            } else { 
                $messages['bed'] = 'ERROR: Bad request!'; 
                $messages['status'] = 'ERROR: Job submission failed. Please try again.';
            }
            
            if (@is_uploaded_file($_FILES['upl-read-file']['tmp_name'])) {
                if ($_FILES['upl-read-file']['size'] < 400000000) {
                    if (move_uploaded_file($_FILES['upl-read-file']['tmp_name'], $path . $read_file)) {
                    //if (move_uploaded_file($_FILES['upl-read-file']['tmp_name'], $path . $_FILES['upl-read-file']['name'])) {
                        $messages['read'] = 'Read file uploaded!';  
                    } else { 
                        $messages['read'] = 'ERROR: Upload failed!'; 
                        $messages['status'] = 'ERROR: Job submission failed. Please try again.';
                    }
                } else {
                    $messages['read'] = 'ERROR: Read file is too big. Please limit input files to max 400Mb!';
                    $messages['status'] = 'ERROR: Job submission failed. Please try again.';
                }
            } else { 
                $messages['read'] = 'ERROR: Bad request!'; 
                $messages['status'] = 'ERROR: Job submission failed. Please try again.';
            }
            
            // Check if job submission was successful
	        if (strpos($messages['status'], 'ERROR') === false) {
	            $messages['status'] = 'Job successfully submitted! Your job token is <span id="job_token">' . $messages['token'] . '</span>.<br>Estimated running time is between 6-8 hours. Check <a href="job_progress.php?token=' . $message['token'] . '">this</a> page for your job progress information.';
	        }
            
        }
    } 

    echo json_encode($messages);
    
?>
