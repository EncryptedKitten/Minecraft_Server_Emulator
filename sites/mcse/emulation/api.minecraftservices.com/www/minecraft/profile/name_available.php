<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$accessTokenData = check_authentication();

$name = basename(str_replace("/available", "", $_GET["path"]));

$response = array(
	"status" => username_available($name) ? "AVAILABLE" : "DUPLICATE"
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>