<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$accessTokenData = check_authentication();

$name = basename($_GET["path"]);

$user = get_user_by_id($accessTokenData["id"]);
$profile = get_selectedProfile($user);

$nameChangeAllowed = name_change_allowed($profile);
if (!$nameChangeAllowed)
{
	name_change_error();
}

$username_avalible = username_available($name);
if (!$username_avalible)
{
	name_change_error();
}

change_name($name, $profile);

$response = array(
	"id" => $profile["profileId"],
	"name" => $name,
);

if(!is_null($profile["skin"]))
{
	$response["skins"][] = array(
		"id" => $user["id"],
		"state" => "ACTIVE",
		"url" => get_skin_url($profile),
		"variant" => strtoupper(model_to_variant($profile["model"]))
	);
}

if(!is_null($profile["cape"]))
{
	$response["capes"][] = array(
		"id" => $user["id"],
		"state" => "ACTIVE",
		"url" => get_cape_url($profile),
	);
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>