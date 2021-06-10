<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$accessTokenData = check_authentication();

$user = get_user_by_id($accessTokenData["id"]);
$blockedProfiles = get_blocklist_by_profileId($user["selectedProfile"]);

$response = array(
	"blockedProfiles" => array()
);

foreach ($blockedProfiles as $blockedProfile)
{
	//These UUIDs are parsed by Google's GSON Library in authlib, so they must have hyphens to be parsed correctly.
	$response["blockedProfiles"][] = hex_to_uuid(bin2hex($blockedProfile["blockedId"]));
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>