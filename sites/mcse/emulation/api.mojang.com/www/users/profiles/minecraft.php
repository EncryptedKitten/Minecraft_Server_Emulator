<?php
include "mcse_common.php";

header('Content-Type: application/json');

check_request("GET");

$name = basename($_GET["path"]);

$time = get_time_at();

$profile = get_profile_at_time($name, $time);

profile_exists_at_time($profile);

$response = array(
	"id" => bin2hex($profile["id"]),
	"name" => $profile["name"],
);

if ($profile["legacy"] == 1)
	$response["legacy"] = true;

if ($profile["demo"] == 1)
	$response["demo"] = true;

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>