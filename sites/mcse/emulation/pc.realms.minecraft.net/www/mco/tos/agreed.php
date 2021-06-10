<?php
include "mcse_common.php";

$profile = check_realms("POST");

realms_tos_agree($profile["id"]);

//Can't find what the response should be, so i'll just assume 204 No Content.
http_response_code(204);
response_telemetry("", 204);