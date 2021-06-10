<?php
include "mcse_common.php";

header('Content-Type: application/json');

check_request("GET");

$accessTokenData = check_authentication();

$name = basename($_GET["path"]);

$user = get_user_by_id($accessTokenData["id"]);

$response = array(
	"dateOfBirth" => $user["dateOfBirth"],
	"email" => $user["email"],
	"emailVerified" => ($user["emailVerified"] != 0),
	"hashed" => false,
	"id" => bin2hex($user["id"]),
	"legacyUser" => ($user["legacyUser"] != 0),
	"secured" => true,
	"username" => $user["username"],
	"verifiedByParent" => ($user["verifiedByParent"] != 0)
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>