<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("GET");

$accessTokenData = check_authentication();

$user = get_user_by_id($accessTokenData["id"]);

$response = array(
	"privileges" => array(
		"onlineChat" => array(
			"enabled" => $user["onlineChatPrivilege"] == 1
		),
		"multiplayerServer" => array(
			"enabled" => $user["multiplayerServerPrivilege"] == 1
		),
		"multiplayerRealms" => array(
			"enabled" => $user["multiplayerRealmsPrivilege"] == 1
		),
		"telemetry" => array(
			"enabled" => $user["telemetryPrivilege"] == 1
		)
	)
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>