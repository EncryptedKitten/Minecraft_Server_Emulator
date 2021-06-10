<?php
	include "mcse_common.php";
	global $mcse_config;

	header('Content-Type: text/plain');

	check_request_realms("GET");

	$profile = check_realms_sid();
	check_realms_name($profile);

	$response = $mcse_config["realms"]["enabled"] ? "true" : "false";

	response_telemetry($response, 200);
	echo $response;