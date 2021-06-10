<?php
include "mcse_common.php";

$profile = check_realms("GET");

$realm = get_realm_by_id(basename(dirname($_GET["path"], 2)));

if ($realm["ownerUUID"] != $profile["profileId"] and is_null(get_realm_by_invite_id_for_profile($realm["remoteSubscriptionId"], $profile["profileId"])))
{
	//supposed to respond with a json error, haven't added one with 403 yet.
	realms_error(403);
}

$backing_server = get_realms_backing_server($realm["remoteSubscriptionId"]);

if (is_null($backing_server))
{
	//Well, the server doesn't actually exist, so you can't join it.
	realms_error(403);
}

//The server is closed.
if ($realm["state"] == "CLOSED")
	realms_error(403);

if (!get_user_by_id($profile["id"])["realmsTosAgreed"])
{
	//No Realms Terms of service agreement, no join!
	realms_error(403);
}

//The backing server address is supposed to be an IP, but DNS names seemed to work as well, at least localhost did. DNS SRV records won't though, as it directly will send the port.
$response = array(
	"address" => $backing_server["address"] . ":" . $backing_server["port"],
	"pendingUpdate" => false
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);