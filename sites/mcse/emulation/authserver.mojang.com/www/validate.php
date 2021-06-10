<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("POST");

$json = file_get_contents('php://input');
$payload = json_decode($json, true);

$accessTokenData = find_access_token(base64_decode($payload["accessToken"]));

$clientToken = hex2bin($payload["clientToken"]);
check_client_token($accessTokenData["clientToken"], $clientToken);

response_telemetry("", 204);
http_response_code(204);
?>