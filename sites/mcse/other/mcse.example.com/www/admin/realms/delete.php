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
					id: formData.get("id")
				};

				$.ajax({
					type: 'POST',
					url: '/api/v1/realms/delete',
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
	<h3>Delete Realm</h3>

	<div class="form-group">
		<label for="id" class="bmd-label-floating">Remote Subscription ID</label>
		<input type="text" class="form-control" id="id" name="id">
	</div>

	<input class="btn btn-raised btn-primary" type="submit" value="Delete Realm" name="submit">
</form>
EOT;

show_page("Delete Realm", $contents);
?>
