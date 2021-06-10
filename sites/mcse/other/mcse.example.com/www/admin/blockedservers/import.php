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
				};

				$.ajax({
					type: 'POST',
					url: '/api/v1/blockedservers/import',
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
	<h3>Import Blocked Servers</h3>
	<input class="btn btn-raised btn-primary" type="submit" value="Import Blocked Servers" name="submit">
</form>
EOT;

show_page("Import Blocked Servers", $contents);
?>
