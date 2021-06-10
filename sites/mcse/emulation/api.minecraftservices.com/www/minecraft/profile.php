<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$accessTokenData = check_authentication();

$user = get_user_by_id($accessTokenData["id"]);
$profile = get_selectedProfile($user);

$response = array(
	"id" => bin2hex($profile["profileId"]),
	"name" => $profile["name"],
	"skins" => array(),
	"capes" => array()
);

if(profile_has_skin($profile))
{
	$response["skins"][] = array(
		"id" => get_skin_uuid($profile["skin"]),
		"state" => "ACTIVE",
		"url" => get_skin_url($profile),
		"variant" => model_to_variant($profile["model"])
	);
}

if(profile_has_cape($profile))
{
	$response["capes"][] = array(
		"id" => get_skin_uuid($profile["cape"]),
		"state" => "ACTIVE",
		"url" => get_cape_url($profile),
	);
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>