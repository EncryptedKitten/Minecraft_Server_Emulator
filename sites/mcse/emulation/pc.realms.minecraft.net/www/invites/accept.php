<?php
include "mcse_common.php";

$profile = check_realms("PUT");

$invitationId = intval(basename($_GET["path"]));

realms_accept_invite($invitationId, $profile["profileId"]);

http_response_code(204);
response_telemetry("", 204);