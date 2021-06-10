<?php
include "mcse_common.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$profile = check_realms("POST");

	$realm = get_realm_by_id(basename($_GET["path"]));

	$payload = json_decode(file_get_contents("php://input"), true);

	if ($realm["ownerUUID"] != $profile["profileId"]) {
		//supposed to respond with a json error, haven't added one with 403 yet.
		realms_error(403);
	}

	realms_invite_send($realm["remoteSubscriptionId"], $payload);

	$response = get_realm_json_invite($realm, $profile);

	response_telemetry(json_encode($response), 200);
	echo json_encode($response);
}
else
{
	$profile = check_realms("DELETE");

	$realm = get_realm_by_id(basename(dirname($_GET["path"], 2)));

	$uuid = hex2bin(uuid_to_hex(basename($_GET["path"])));

	if ($realm["ownerUUID"] != $profile["profileId"]) {
		//supposed to respond with a json error, haven't added one with 403 yet.
		realms_error(403);
	}

	realms_delete_invite($realm["remoteSubscriptionId"], $uuid);

	http_response_code(204);
	response_telemetry("", 204);
}