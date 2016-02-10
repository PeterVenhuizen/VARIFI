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
					<li><a href="index.php">web service</a></li>
					<li><a href="">tutorial</a></li>
					<li><a href="">contact</a></li>
				</ul>
				<div id="server_load" title="Server load"><span id="load" value="<?php $load = sys_getloadavg(); echo $load[0]; ?>"></span></div>
			</nav>
		</header>

