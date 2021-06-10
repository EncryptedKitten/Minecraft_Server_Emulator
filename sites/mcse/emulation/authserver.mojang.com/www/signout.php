<?php
$telemetry_no_post = TRUE;
include "mcse_common.php";

header('Content-Type: application/json');
check_request("POST");

$json = file_get_contents('php://input');
$payload = json_decode($json, true);

$user = get_user_by_username($payload["username"]);

check_password($payload["password"], $user["password"]);

$id = $user["id"];
delete_access_tokens_for_id($user["id"]);

response_telemetry("", 204);
http_response_code(204);
?>