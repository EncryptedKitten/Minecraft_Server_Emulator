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
					email: formData.get("email"),
					name: formData.get("name"),
					password: formData.get("password"),
					id: formData.get("id"),
					profileId: formData.get("profileId")
				};

				$.ajax({
					type: 'POST',
					url: '/api/v1/accounts/create',
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
	<h3>Create Account</h3>

	<div class="form-group">
		<label for="email" class="bmd-label-floating">Email</label>
		<input type="text" class="form-control" id="email" name="email">
	</div>

	<div class="form-group">
		<label for="password" class="bmd-label-floating">Password</label>
		<input type="password" class="form-control" id="password" name="password">
	</div>

	<div class="form-group">
		<label for="name" class="bmd-label-floating">Username</label>
		<input type="text" class="form-control" id="name" name="name">
	</div>

	<div class="form-group">
		<label for="id" class="bmd-label-floating">ID</label>
		<input type="text" class="form-control" id="id" name="id">
	</div>

	<div class="form-group">
		<label for="profileId" class="bmd-label-floating">Profile ID</label>
		<input type="text" class="form-control" id="profileId" name="profileId">
	</div>

	<input class="btn btn-raised btn-primary" type="submit" value="Create Account" name="submit">
</form>
EOT;

show_page("Create Account", $contents);
?>
