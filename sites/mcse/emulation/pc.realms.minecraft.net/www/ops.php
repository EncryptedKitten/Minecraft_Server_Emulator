<?php
include "mcse_common.php";

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	$profile = check_realms("GET");
	$realm = get_realm_by_id(basename($_GET["path"]));
}
else if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	$profile = check_realms("POST");
	$profile_to_op = basename($_GET["path"]);
	$realm = get_realm_by_id(basename(dirname($_GET["path"])));
}
else if ($_SERVER['REQUEST_METHOD'] == "DELETE")
{
	$profile = check_realms("DELETE");
	$profile_to_op = basename($_GET["path"]);
	$realm = get_realm_by_id(basename(dirname($_GET["path"])));
}

if ($realm["ownerUUID"] != $profile["profileId"])
{
	//supposed to respond with a json error, haven't added one with 403 yet.
	realms_error(403);
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	realms_op($realm["remoteSubscriptionId"], hex2bin(uuid_to_hex($profile_to_op)));
}

else if ($_SERVER['REQUEST_METHOD'] == "DELETE")
{
	realms_deop($realm["remoteSubscriptionId"], hex2bin(uuid_to_hex($profile_to_op)));
}

$response = array(
	"ops" => array()
);

foreach (get_realms_ops($realm["remoteSubscriptionId"]) as $op)
{
	$response["ops"][] = get_profile_by_id($op["profileId"])["name"];
}

response_telemetry(json_encode($response), 200);
echo json_encode($response);