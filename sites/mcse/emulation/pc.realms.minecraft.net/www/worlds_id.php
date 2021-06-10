<?php
include "mcse_common.php";

$profile = check_realms("GET");

$realm = get_realm_by_id(basename($_GET["path"]));

if ($realm["ownerUUID"] != $profile["profileId"])
{
	//supposed to respond with a json error, haven't added one with 403 yet.
	realms_error(403);
}

$response = get_realm_json($realm, $profile);

response_telemetry(json_encode($response), 200);
echo json_encode($response);