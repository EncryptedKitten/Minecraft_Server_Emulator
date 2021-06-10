<?php
include "mcse_common.php";

$profile = check_realms("GET");

$invites = get_realms_invites_for_profile($profile["profileId"]);

response_telemetry(count($invites), 200);
echo count($invites);