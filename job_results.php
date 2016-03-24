<!DOCTYPE html>

<?php 
	require_once('assets/config.php');
?>

<html>
	<head>
		<meta charset="UTF-8">
		<meta content="VARIFI" name="Keywords">
		<meta content="VARIFI" name="Description">
		<title>VARIFI - Job Results</title>
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
		</article>   
		
		    <?php 
			if (isset($_GET['token'])) { 
			    $token = $_GET['token'];
			    $query = 'SELECT * FROM submitted_jobs WHERE job_token = :token LIMIT 1';
			    $query_params = array(':token' => $token);
			    try {
				$stmt = $db->prepare($query);
				$stmt->execute($query_params);
			    } catch (PDOException $ex) { die($ex->getMessage()); }
			    
			    if ($stmt->rowCount()) {

				$row = $stmt->fetch();

				if ($row['finished']) {
				    // Job is finished
			    
		    ?>
				      <table class="overview_table">
					  <tr><td colspan="3" class="download_h3">Amplicon coverage</td></tr>
					  <?php select_files($token, array("coverage_all_amplicons")); ?>
					  
					  <tr><td colspan="3" class="download_h3">Results</td></tr>
					  <?php select_files($token, array("FINAL_REPORT_filtered_with_COSMIC", "variants_sorted")); ?>

					  <tr><td colspan="3" class="download_h3">Plots</td></tr>
					  <?php select_files($token, array("amplicon.pdf", "gene.pdf", "score.pdf", "coverages.pdf", "chromosomes.pdf")); ?>

					  <tr><td colspan="3" class="download_h3">IGV session files</td></tr>
					  <?php select_files($token, array(".bwa-FINAL.bam", ".bt2-FINAL.bam", ".ngm-FINAL.bam", 
					      ".bwa-GATK-final.vcf.gz", ".bwa.samt.vcf.gz", ".bt2-GATK-final.vcf.gz", 
					      "bt2.samt.vcf.gz", ".ngm-GATK-final.vcf.gz", ".ngm.samt.vcf.gz", 
					      "_varunion_FINAL.vcf.gz", ".FINAL_igvsession.xml")); 
					  ?>
				      </table>
		    <?php
				} else {
				    echo '<table class="overview_table"><tr><td colspan="3">Your job is still running. You can view your job progress <a href="job_progress.php?token=' . $token . '">here</a>, or come back to this page at a later time.</td></tr></table>';
				}
			    } else {
				echo '<table class="overview_table"><tr><td colspan="3">Invalid job token!</td></tr></table>';
			    }
			}
		    ?>
            
           <?php include('footer.php'); ?>
            
	  </main>
		
	</body>
</html>
