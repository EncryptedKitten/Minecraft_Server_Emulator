<?php
$admin_ui = true;
include "mcse_common.php";

$contents = <<<EOT
<script>
		$(function () {
			$('#form').on('submit', function (e) {

				e.preventDefault();

				var formData = new FormData($(this)[0]);
				var submission = {
					server: formData.get("server"),
					serverHash: formData.get("serverHash")
				};

				$.ajax({
					type: 'POST',
					url: '/api/v1/blockedservers/add',
					dataType: 'json',
					data: JSON.stringify(submission),
					contentType: 'application/json',
					success: function(data) {
						document.getElementById("output").innerHTML = "<div class=\"alert alert-success\" role=\"alert\">Success</div>";
					},
					error: function(data) {
						document.getElementById("output").innerHTML = "<div class=\"alert alert-danger\" role=\"alert\">" + data.responseText + "</div>";
					}
				});
			});
		});
	</script>

	<div id=output></div>
<form id="form" method="post">
	<h3>Block Server</h3>

	<div class="form-group">
		<label for="server" class="bmd-label-floating">Server Address</label>
		<input type="text" class="form-control" id="server" name="server">
	</div>

	<div class="form-group">
		<label for="serverHash" class="bmd-label-floating">Server Hash</label>
		<input type="text" class="form-control" id="serverHash" name="serverHash">
	</div>

	<input class="btn btn-raised btn-primary" type="submit" value="Block Server" name="submit">
</form>
EOT;

show_page("Block Server", $contents);
?>
