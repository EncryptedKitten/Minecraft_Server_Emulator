<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$profileId = hex2bin(basename($_GET["path"]));
$profile = get_profile_by_id($profileId);

$response = array();

if (is_null($profile))
	minecraft_error("not_allowed");

$value = array(
	"timestamp" => 0,
	"profileId" => bin2hex($profileId),
	"profileName" => $profile["name"],
	"textures" => array(),
);

if (array_key_exists("unsigned", $_GET) and ($_GET["unsigned"] == "false"))
	$value["signatureRequired"] = true;

if ($profile["skin"])
	$value["textures"]["SKIN"] = array("url" => get_skin_url($profile));

if ($profile["cape"])
	$value["textures"]["CAPE"] = array("url" => get_cape_url($profile));

$value = base64_encode(json_encode($value));

$response = array(
	"id" => bin2hex($profileId),
	"name" => $profile["name"],
	"properties" => array(
		array(
			"name" => "textures",
			"value" => $value,
		),
	),
);

//unsigned=false when requesting the skins of other players on a minecraft server, or other players in a LAN game.
if (array_key_exists("unsigned", $_GET) and ($_GET["unsigned"] == "false"))
	$response["properties"][0]["signature"] = base64_encode(yggdrasil_sign($value));

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>