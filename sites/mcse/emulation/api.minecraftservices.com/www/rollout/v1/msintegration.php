<?php
	header('Content-Type: application/json');

	$response = array(
		"feature" => "msamigration",
		"rollout" => false
	);

	echo json_encode($response);
?>