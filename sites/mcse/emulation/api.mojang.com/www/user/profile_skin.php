<?php
include "mcse_common.php";

header('Content-Type: application/json');

check_request("DELETE");

$accessTokenData = check_authentication();

$user = get_user_by_id($accessTokenData["id"]);
$profile = get_selectedProfile($user);

response_telemetry("", 200);
reset_skin($profile);
?>