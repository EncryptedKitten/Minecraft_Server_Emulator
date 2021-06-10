<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("POST");

$json = file_get_contents('php://input');
$payload = json_decode($json, true);

$accessToken = base64_decode($payload["accessToken"]);
$accessTokenData = find_access_token($accessToken);

$blockedservers = get_blocked_servers();

//If you try and join a blocked server in online mode, it'll just say that your login token wasn't right. I have not tested the actual result of this, so I don't know what the official server would do.
if (in_array($payload["serverId"], $blockedservers))
{
	minecraft_error("token_invalid");
}

if (is_null($accessTokenData)) {
	minecraft_error("token_invalid");
}

$profile = get_profile_by_id(hex2bin($payload["selectedProfile"]));

if (is_null($profile)) {
	minecraft_error("token_invalid");
}

$user = get_user_by_id($profile["id"]);

if (is_null($user)) {
	minecraft_error("token_invalid");
}

add_server_connection($profile["profileId"], $payload["serverId"]);

http_response_code(204);

response_telemetry("", 204);
?>