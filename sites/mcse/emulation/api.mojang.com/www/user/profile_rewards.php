<?php
	header('Content-Type: application/json');

	$response = array(
		"feature" => "msamigration",
		"rollout" => false
	);

	response_telemetry(json_encode($response), 200);
	echo json_encode($response);
?>