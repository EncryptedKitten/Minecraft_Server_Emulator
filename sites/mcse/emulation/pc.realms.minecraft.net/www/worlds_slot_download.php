<?php
include "mcse_common.php";

check_realms("GET");

realms_error(403);

response_telemetry("", 403);