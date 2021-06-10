<?php
include "mcse_common.php";

header('Content-Type: application/json');
check_request("POST");

$accessTokenData = check_authentication();

$user = get_user_by_id($accessTokenData["id"]);

$profile = get_selectedProfile($user);

if($_SERVER['HTTP_CONTENT_TYPE'] == "application/json")
{
	$json = file_get_contents('php://input');
	$payload = json_decode($json, true);
	$file = curl_get($payload["url"]);
	$variant = $payload["variant"];
}
else
{
	
	if ($_FILES["file"]["size"] <= 5000000 and $_FILES['file']['type'] == "image/png")
	{
		$file = file_get_contents($_FILES["file"]["tmp_name"]);
		$variant = $_POST["variant"];
		unlink($_FILES["file"]["tmp_name"]);
	}
}

response_telemetry("", 200);
set_skin($file, $variant, $profile);
?>