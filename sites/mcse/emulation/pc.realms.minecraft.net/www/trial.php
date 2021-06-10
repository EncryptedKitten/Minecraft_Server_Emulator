<?php
include "mcse_common.php";

$profile = check_realms("GET");

$response = get_user_by_id($profile["id"])["realmsTrialUsed"] ? "False" : "True";

response_telemetry($response, 200);
echo $response;