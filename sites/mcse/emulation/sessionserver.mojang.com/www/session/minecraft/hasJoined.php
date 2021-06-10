<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$blockedservers = get_blocked_servers();

//A blocked server shouldn't be allowed to check the online mode login state of their clients.
if (in_array($_GET["serverId"], $blockedservers))
{
	minecraft_error("not_allowed");
}

$profile = get_profile_by_name($_GET["username"]);

$hasJoined = check_join($profile["profileId"], $_GET["serverId"]);

$response = array();

if (is_null($hasJoined))
	minecraft_error("not_allowed");

if (is_null($profile) or ($profile["name"] != $_GET["username"]) or ($hasJoined["selectedProfile"] != $profile["profileId"]))
	minecraft_error("not_allowed");

$user = get_user_by_id($profile["id"]);

if (is_null($user) or $user["selectedProfile"] != $profile["profileId"])
	minecraft_error("not_allowed");

//If the server has prevent-proxy-connections enabled, we need to check if they have their ip matches the one they used to connect to the Mojang servers with their join request. This doesn't do much, but I don't know how it is supposed to work, so this is a rudimentary implementation for now. It won't attempt to check lan IP joins, because the IP will obviously be different there.
if (in_array("ip", $_GET) and filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
{
	if(filter_var($_GET["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		$ipv4 = inet_pton($_GET["ip"]);

		if ($ipv4 != $hasJoined["ipv4"])
			minecraft_error("not_allowed");
	}
	else
	{
		$ipv6 = inet_pton($_GET["ip"]);

		if ($ipv6 != $hasJoined["ipv6"])
			minecraft_error("not_allowed");
	}

	minecraft_error("not_allowed");
}

$response = array(
	"id" => hex_to_uuid(bin2hex($hasJoined["selectedProfile"])),
	"name" => $profile["name"],
);

response_telemetry(json_encode($response), 200);

echo json_encode($response);
?>