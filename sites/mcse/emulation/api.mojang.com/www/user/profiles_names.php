<?php
include "mcse_common.php";

header('Content-Type: application/json');

check_request("GET");
$profile_id = hex2bin(basename(str_replace("/names", "", $_GET["path"])));

$name_history = profileId_to_name_history($profile_id);

$response_full = array();

foreach($name_history as $profile)
{
	$response = array(
		"name" => $profile["name"],
	);

	if ($profile["timestamp_start"] != 0)
		$response["changedToAt"] = $profile["timestamp_start"] * 1000;

	$response_full[] = $response;
}

response_telemetry(json_encode($response_full), 200);
echo json_encode($response_full);

?>