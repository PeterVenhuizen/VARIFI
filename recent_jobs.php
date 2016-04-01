<!DOCTYPE html>

<?php 
	require_once('assets/config.php');

	date_default_timezone_set('Europe/Vienna');
?>

<html>
	<head>
		<meta charset="UTF-8">
		<meta content="VARIFI" name="Keywords">
		<meta content="VARIFI" name="Description">
		<title>VARIFI - Recent Jobs</title>
        <link href='https://fonts.googleapis.com/css?family=Fira+Sans' rel='stylesheet' type='text/css'>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="assets/css/reset.css">
		<link rel="stylesheet" type="text/css" href="assets/css/style.css">
		<meta name="viewport" content="initial-scale=1">
	</head>
	
	<body>    

		<?php include('header.php'); ?>
		
        <main>

			<?php 
				if (isset($_COOKIE['varifi_rj'])) {
					$tokens = explode('|', $_COOKIE['varifi_rj']);
					foreach($tokens as $t) {
						$query = 'SELECT job_token, submitted_on, available_until, finished from submitted_jobs WHERE BINARY job_token = :token';
						$query_params = array(':token' => $t);
						try {
							$stmt = $db->prepare($query);
							$stmt->execute($query_params);
						} catch (PDOException $ex) { die(); }
						
						if ($stmt->rowCount()) {
							foreach ($stmt as $row) {
								echo "	<article>
											<header><h2>" . $row['job_token'] . "</h2></header>
											<p>Your job started on <strong>" . date("j/n H:i:s", strtotime($row['submitted_on'])) . "</strong> and 
											has " . ($row['finished'] ? "finished. You can download your results from <a href='http://varifi.cibiv.univie.ac.at/job_results.php?token=" . $row['job_token'] . "'>here</a>, 
											until <strong>" . date("j/n", strtotime($row['available_until'])) . " 23:59:59</strong>." : "not yet finished. 
											You can view your job progress <a href='http://varifi.cibiv.univie.ac.at/job_progress.php?token=" . $row['job_token'] . "'>here</a>.") . "</p>
										</article>";
							}
						} else { 
							echo "	<article>
										<header><h2>Job(s) expired</h2></header>
										<p>Your stored jobs have expired. The cookie has been deleted.</p>
									</article>";
							unset($_COOKIE['varifi_rj']);
							setcookie('varifi_rj', '', time() - 3600);
						}
					}
				} else {
					echo "	<article>
								<header><h2>No recent jobs found</h2></header>
							</article>";
				}
			?>
            
            <?php include('footer.php'); ?>
            
		</main>
		
	</body>
</html>
