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
					id: formData.get("id"),
					name: formData.get("name"),
					motd: formData.get("motd"),
					port: formData.get("port"),
					rconPort: formData.get("rconPort"),
					address: formData.get("address"),
					rconPassword: formData.get("rconPassword")
				};

				$.ajax({
					type: 'POST',
					url: '/api/v1/realms/create',
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
	<h3>Create Realm</h3>

	<div class="form-group">
		<label for="id" class="bmd-label-floating">Profile ID</label>
		<input type="text" class="form-control" id="id" name="id">
	</div>

	<div class="form-group">
		<label for="name" class="bmd-label-floating">Name</label>
		<input type="text" class="form-control" id="name" name="name">
	</div>

	<div class="form-group">
		<label for="motd" class="bmd-label-floating">MOTD</label>
		<input type="text" class="form-control" id="motd" name="motd">
	</div>

	<div class="form-group">
		<label for="port" class="bmd-label-floating">Port</label>
		<input type="text" class="form-control" id="port" name="port">
	</div>

	<div class="form-group">
		<label for="rconPort" class="bmd-label-floating">RCON Port</label>
		<input type="text" class="form-control" id="rconPort" name="rconPort">
	</div>

	<div class="form-group">
		<label for="address" class="bmd-label-floating">Server Address</label>
		<input type="text" class="form-control" id="address" name="address">
	</div>

	<div class="form-group">
		<label for="rconPassword" class="bmd-label-floating">Password</label>
		<input type="password" class="form-control" id="rconPassword" name="rconPassword">
	</div>

	<input class="btn btn-raised btn-primary" type="submit" value="Create Realm" name="submit">
</form>
EOT;

show_page("Create Realm", $contents);
?>
