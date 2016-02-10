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

		<?php include('header.php'); ?>
		
        <main>

            <article>
                <header>
                    <h2>Job progress</h2>
                </header>
            </article>
            
            <table id="job_progress">
            <?php
                if ( isset($_GET['token']) || isset($_POST['get_progress']) ) {
                    
                    $token = (isset($_GET['token']) ? $_GET['token'] : $_POST['job_token']);
                    $query = 'SELECT progress FROM job_info WHERE job_token = :token LIMIT 1';
                    $query_params = array(':token' => mysql_real_escape_string($token));
                    try {
                        $stmt = $db->prepare($query);
                        $stmt->execute($query_params);
                    } catch (PDOException $ex) { die($ex->getMessage()); }
                
                    if ($stmt->rowCount()) {
                        $row = $stmt->fetch();
                        
                        $progress = explode("|", $row['progress']);
                        $progress_output = '';
                        
                        foreach ($progress as $m) {
                            if (strlen($m) > 0) { // Ignore empy
                                
                                if (preg_match('/time=(?<time>.*?);step=(?<step>.*?);message=(?<message>.*?);/', $m, $match)) {
                                    // Normal step progress
                                    $progress_output .= "<tr>
                                        <td class='progress_time'>" . $match['time'] . "</td>
                                        <td class='progress_step'>" . $match['step'] . "</td>
                                        <td>" . $match['message'] . "</td>
                                    </tr>";

                                } else if (preg_match('/time=(?<time>.*?);finished=(?<finished>.*?);/', $m, $match)) { // Job finished
                                    $progress_output .= "<tr class='progress_finished'>
                                        <td class='progress_time'>" . $match['time'] . "</td>
                                        <td class='progress_step'>FINISHED</td>
                                        <td><a href='job_results.php?token=" . $match['finished'] . "'>Download results</a></td>
                                    </tr>";

                                } else if (preg_match('/time=(?<time>.*?);error=(?<error>.*?);/', $m, $match)) { // Error
                                    $progress_output .= "<tr class='progress_error'>
                                        <td class='progress_time'>" . $match['time'] . "</td>
                                        <td class='progress_step'>ERROR</td>
                                        <td>" . $match['error'] . "</td>
                                    </tr>";

                                }
                            }
                        }
                        
                        echo $progress_output;
                        
                    } else {
                        print '<tr><td colspan="3" class="progress_error">Invalid job token! Please try again.</td></tr>';
                    }
                }
            ?>
            </table>
            
            <?php include('footer.php'); ?>
            
		</main>
		
	</body>
</html>
