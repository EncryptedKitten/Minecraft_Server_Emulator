<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$user = $_GET["user"];
$sessionId = explode(":", rawurldecode($_GET["sessionId"]));
$serverId = $_GET["serverId"];

if (count($sessionId) != 3 or $sessionId[0] != "token")
	legacy_minecraft_error("Bad login", 401);

$accessToken = base64_decode($sessionId[1]);
$accessTokenData = find_access_token($accessToken);

$profileId = hex2bin($sessionId[2]);

$blockedservers = get_blocked_servers();

//I don't know what the official behavior is, but you won't be allowed to use online-mode identity checking on a blocked legacy server, even though their is no form of client side blocking in these pre-yggdrasil versions.
if (in_array($serverId, $blockedservers))
{
	legacy_minecraft_error("Bad login", 401);
}

if (is_null($accessTokenData)) {
	legacy_minecraft_error("Bad login", 401);
}

$profile = get_profile_by_id($profileId);

if (is_null($profile) or $profile["name"] != $user) {
	legacy_minecraft_error("Bad login", 401);
}

$user = get_user_by_id($profile["id"]);

if (is_null($user) or $user["selectedProfile"] != $profileId) {
	legacy_minecraft_error("Bad login", 401);
}

add_legacy_server_connection($profileId, $serverId);

$response = "OK";

response_telemetry($response, 200);
echo $response;