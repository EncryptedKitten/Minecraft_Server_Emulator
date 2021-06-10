<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$user = $_GET["user"];
$serverId = $_GET["serverId"];

$blockedservers = get_blocked_servers();

$profile = get_profile_by_name($user);

$legacy_join = check_legacy_join($profile["profileId"], $serverId);

if (in_array($serverId, $blockedservers))
{
	legacy_minecraft_error("Bad login", 401);
}

if (is_null($legacy_join))
{
	legacy_minecraft_error("NO", 401);
}

if (is_null($profile) or $profile["name"] != $user) {
	legacy_minecraft_error("NO", 401);
}

$user = get_user_by_id($profile["id"]);

if (is_null($user) or $user["selectedProfile"] != $profile["profileId"]) {
	legacy_minecraft_error("NO", 401);
}

$response = "YES";

response_telemetry($response, 200);
echo $response;