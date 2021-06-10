<?php
$telemetry_no_post = TRUE;
include "mcse_common.php";

header('Content-Type: application/json');

check_request_multiple(["GET", "POST"]);

$accessTokenData = check_authentication();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	check_ip_security($accessTokenData["id"]);
	http_response_code(204);
	response_telemetry("", 204);
}
else
{
	$json = file_get_contents('php://input');
	$payload = json_decode($json, true);

	$user = get_user_by_id($accessTokenData["id"]);

	security_check($payload, $user);

	http_response_code(204);
	response_telemetry("", 204);
}
?>