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
		<title>VARIFI</title>
        <link href='https://fonts.googleapis.com/css?family=Fira+Sans' rel='stylesheet' type='text/css'>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="//cdn.jsdelivr.net/jquery.scrollto/2.1.2/jquery.scrollTo.min.js"></script>
		<script type="text/javascript" src="assets/js/jquery.form.min.js"></script>
		<script type="text/javascript" src="actions/submit.js"></script>
		<link rel="stylesheet" type="text/css" href="assets/css/reset.css">
		<link rel="stylesheet" type="text/css" href="assets/css/style.css">
		<link rel="stylesheet" type="text/css" href="assets/css/loading.css">
		<meta name="viewport" content="initial-scale=1">
		<script>
			$(document).ready(function() {
				
				document.getElementById("upl-bed-file").onchange = function () {
					document.getElementById("bed-file").value = this.value;
				};
				document.getElementById("upl-read-file").onchange = function () {
					document.getElementById("read-file").value = this.value;
				};
				document.getElementById("upl-hotspots-file").onchange = function () {
					document.getElementById("hotspots-file").value = this.value;
				};
				
				$('.toggle_example').click(function(e) {
					e.preventDefault();
					$(this).siblings('.file_example').toggle();
					($(this).text() === "Show example") ? $(this).text("Hide example") : $(this).text("Show example");
				});
				
				$('#more_options').click(function() {
					($(this).text() === "+ More options") ? $(this).html("<span id='more'>-</span> Less options") : $(this).html("<span id='more'>+</span> More options");
					$('.optional').toggle();
				});
						
			});
		</script>
	</head>
	
	<body>   
	   
		<?php include('header.php'); ?>
	
		<main>
        
            <article>
                <header>
                    <h2>AutoVARIFI</h2>
                </header>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel bibendum enim. Nam dapibus est sit amet commodo faucibus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Praesent id dolor id ante feugiat dapibus. Etiam vel dui leo. Sed porttitor dictum nibh, id facilisis magna elementum vel. Vestibulum aliquam et est volutpat ultricies. Donec finibus, justo eget malesuada pulvinar, lacus nulla finibus metus, quis interdum leo tortor id dolor. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <!--<img src="assets/img/workflow.png" alt="VARIFI workflow">-->
            </article>

			<section id="form_wrapper">
				<form id="submit_data" action="actions/submit.php" method="POST" enctype="multipart/form-data">
				
					<h2>Submit job</h2>
				
					<fieldset>
						<legend>Email</legend>
						
						<label for="email">Email <span class="req">*</span></label>
						<input type="text" name="email" id="email">
					</fieldset>
					
					<fieldset>
						<legend>Bed file</legend>
					
						<label for="bed-file">Bed file <span class="req">*</span></label>
						<input type="text" id="bed-file" name="bed-file" placeholder="Choose file" disabled="disabled">
						<div class="file_upload">
							<span>Browse</span>
							<input type="file" accept=".bed,.txt" name="upl-bed-file" class="upload" id="upl-bed-file">
						</div>
                        <p class="help">Upload the regions of interest in valid bed format (review bed format <a href="http://www.ensembl.org/info/website/upload/bed.html" target="_blank">here</a>).</p>
                        <button class="toggle_example">Show example</button>
                        <pre class="file_example">
1	3837	4076	JUNC00000001	9	+	3837	4076	255,0,0	2	76,81	0,158
1	4181	4584	JUNC00000002	19	+	4181	4584	255,0,0	2	95,99	0,304
1	4513	4790	JUNC00000003	7	+	4513	4790	255,0,0	2	92,85	0,192
1	5003	5271	JUNC00000004	15	+	5003	5271	255,0,0	2	92,98	0,170</pre>
                    </fieldset>
                    
                    <fieldset>
                        <legend>Read file</legend>
						<label for="read-file">Read file <span class="req">*</span></label>
						<input type="text" id="read-file" name="read-file" placeholder="Choose file" disabled="disabled">
						<div class="file_upload">
							<span>Browse</span>
							<input type="file" accept=".bam,.fastq,.gz,.fq" name="upl-read-file" class="upload" id="upl-read-file">
						</div>
                        <p class="help">Upload the amplicon sequencing data in (gunzipped) fastq or bam format. Make sure that the read file does not exceed 400Mb.</p>
                        <button class="toggle_example">Show example</button>
                        <pre class="file_example">
@SEQ_ID
GATTTGGGGTTCAAAGCAGTATCGATCAAATAGTAAATCCATTTGTTCAACTCACAGTTT
+
!''*((((***+))%%%++)(%%%%).1***-+*''))**55CCF>>>>>>CCCCCCC65</pre>
					</fieldset>
					
					<span id="more_options"><span id="more">+</span> More options</span>
					<fieldset class="optional">
						<legend>Hotspots</legend>
						<label for="hotspots">Hotspots file</label>
						<input type="text" id="hotspots-file" name="hotspots-file" placeholder="Choose file" disabled="disabled">
						<div class="file_upload">
							<span>Browse</span>
							<input type="file" accept=".bed,.txt" name="upl-hotspot-file" class="upload" id="upl-hotspots-file">
						</div>
						<p class="help">Upload a hotspots file.</p>
					</fieldset>
					
                    <input type="submit" value="Submit" name="submit_job" id="submit_job">
                    
				</form>
			</section>
            
            <!-- http://www.w3bees.com/2013/10/file-upload-with-progress-bar.html -->
            <article id="upload_progress">
                <header>
                    <h2>Processing...</h2>
                </header>
                <div class="progress">
                    <div class="bar"></div>
                    <div class="percent">0%</div>
                </div>
                
                <!-- http://cssload.net/en/spinners -->
                <div id="floatingCirclesG">
					<div class="f_circleG" id="frotateG_01"></div>
					<div class="f_circleG" id="frotateG_02"></div>
					<div class="f_circleG" id="frotateG_03"></div>
					<div class="f_circleG" id="frotateG_04"></div>
					<div class="f_circleG" id="frotateG_05"></div>
					<div class="f_circleG" id="frotateG_06"></div>
					<div class="f_circleG" id="frotateG_07"></div>
					<div class="f_circleG" id="frotateG_08"></div>
				</div>
				
				<!-- Status messages -->
                <div class="status" id="email_status"></div>
                <div class="status" id="bed_status"></div>
                <div class="status" id="read_status"></div>
                <div class="status" id="hotspot_status"></div>
                <div class="status" id="job_status"></div>
            </article>
            
            <?php include('footer.php'); ?>
            
		</main>
        
	</body>
</html>
