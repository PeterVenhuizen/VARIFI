<!DOCTYPE html>

<?php 
	require_once('assets/config.php');
	ini_set('display_errors', 1);error_reporting(E_ALL); 
?>

<html>
	<head>
		<meta charset="UTF-8">
		<meta content="VARIFI" name="Keywords">
		<meta content="VARIFI" name="Description">
		<title>VARIFI - Job Progress</title>
        <link href='https://fonts.googleapis.com/css?family=Fira+Sans' rel='stylesheet' type='text/css'>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="assets/css/reset.css">
		<link rel="stylesheet" type="text/css" href="assets/css/style.css">
		<meta name="viewport" content="initial-scale=1">
	</head>
	
	<body>    
		
		<?php 
			include('header.php'); 
			require('assets/functions.php');
		?>
		
        <main>

            <article>
                <header>
                    <h2>Job results</h2>
                </header>
                <p>
		        <?php
		            if ( isset($_GET['token']) || isset($_POST['get_progress'])) {
		                
		                $token = (isset($_GET['token']) ? $_GET['token'] : $_POST['job_token']);
		                $query = 'SELECT * FROM submitted_jobs WHERE job_token = :token LIMIT 1';
		                $query_params = array(':token' => mysql_real_escape_string($token));
		                try {
		                    $stmt = $db->prepare($query);
		                    $stmt->execute($query_params);
		                } catch (PDOException $ex) { die($ex->getMessage()); }
		                
		                if ($stmt->rowCount()) {
		                    $row = $stmt->fetch();
		                    if ($row['finished']) {
		                        // Job is finished
		                        
		                        echo 'finished<br>';
		                        
		                        $path = 'files/';
		                        $files = array();
		                        
		                        $token = 'Syj7viW0O9nRbFOjPngYRN6YjJkIA9ii';
		                        
		                        if ($handle = opendir($path)) {
		                            while (false !== ($entry = readdir($handle))) {
		                                $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
		                                if (strpos($entry, $token) !== false) {

		                                    $f = substr($entry, strpos($entry, $token) + (strlen($token)+1));
		                                    echo $f . " " . formatFileSize(filesize($path . $entry));
		                                }
		                            }
		                        }
		                        
		                        /*
		                        $path = 'downloads/';
		                        $forbidden = array(".", "..", ".DS_Store");

		                        $files = array();

		                        if ($handle = opendir($path)) {
		                            while (false !== ($entry = readdir($handle))) {
		                                $extension = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
		                                if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'zip', 'xls', 'xlsx', 'doc', 'docx', 'pdf', 'mp3'))) { $extension = 'unknown'; }			
		                                if (!in_array($entry, $forbidden)) {
		                                    echo '	<li class="download ' . $extension . '"><a href="' . 'downloads/' . $entry . '">' . $entry . '</a></li>';
		                                } 
		                            }
		                        }
		                        closedir($handle);
		                        */
		                        
		                    } else {
		                        // Job still running
		                        echo 'Your job is still running. You can view your job progress <a href="job_progress.php?token=' . $token . '">here</a>, or come back to this page at a later time.';
		                    }
		                } else {
		                    // Invalid job token
		                    echo 'invalid token';
		                }
		            }
		        ?>
                </p>
            </article>
            
            <?php include('footer.php'); ?>
            
		</main>
		
	</body>
</html>
