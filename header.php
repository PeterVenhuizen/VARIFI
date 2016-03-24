		<script>
			$(document).ready(function() {
				var load = Math.ceil($('#load').attr('value'));
				$('#load').width(load);
				$('#server_load').append(load + '%');
			});
		</script>
		<header id="page_header">
			<nav>
				<ul>
					<li><a href="index.php">VARIFI</a></li>
					<li><a href="">tutorial</a></li>
					<li><a href="recent_jobs.php">recent jobs</a></li>
					<li><a href="">contact</a></li>
				</ul>
				<div id="server_load" title="Server load"><span id="load" value="<?php echo shell_exec("SGE_ROOT=/software/sge-2011.11 SGE_QMASTER_PORT=6454 SGE_EXECD_PORT=6455 /software/sge-2011.11/bin/linux-x64/qhost | grep fitch | awk '{ print $4 }'"); ?>"></span></div>
			</nav>
		</header>

