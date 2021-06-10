<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("POST");

$json = file_get_contents('php://input');
$payload = json_decode($json, true);

$accessTokenData = find_access_token_refresh(base64_decode($payload["accessToken"]));
$clientToken = hex2bin($payload["clientToken"]);

check_client_token($accessTokenData["clientToken"], $clientToken);

$newAccessToken = refresh_access_token($accessTokenData);

$response = array(
	"accessToken" => base64_encode($newAccessToken),
	"clientToken" => $payload["clientToken"]
);

$user = get_user_by_id($accessTokenData["id"]);

$profile = get_selectedProfile($user);

$response["selectedProfile"] = array(
	"id" => bin2hex($profile["profileId"]),
	"name" => $profile["name"]
);

if ($payload["requestUser"])
{
	$response["user"] = array(
		"id" => bin2hex($user["id"]),
		"username" => $user["username"]
	);
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>