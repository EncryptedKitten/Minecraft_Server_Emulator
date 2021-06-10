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

$clientToken = array_key_exists("clientToken", $payload) ? $payload["clientToken"] : null;
$return = get_access_token($user["id"], $clientToken);

$response = array(
	"accessToken" => base64_encode($return[0]),
	"clientToken" => array_key_exists("clientToken", $payload) ? $payload["clientToken"] : bin2hex($return[1])
);

if (array_key_exists("agent", $payload) or $payload["requestUser"])
{
	$profiles = get_profiles_by_id($user["id"]);

	if (array_key_exists("agent", $payload))
	{
		$response["availableProfiles"] = array();

		foreach ($profiles as $profile)
		{
			$response["availableProfiles"][] = array(
				"id" => bin2hex($profile["profileId"]),
				"name" => $profile["name"]
			);
		}

		foreach ($response["availableProfiles"] as $profile)
		{
			if ($profile["id"] == bin2hex($user["selectedProfile"]))
				$response["selectedProfile"] = $profile;
		}
	}

	if ($payload["requestUser"])
	{
		$response["user"] = array(
			"id" => bin2hex($user["id"]),
			"username" => $user["username"]
		);
	}
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>