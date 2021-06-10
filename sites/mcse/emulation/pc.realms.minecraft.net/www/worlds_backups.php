<?php
include "mcse_common.php";

check_realms("GET");

$response = array(
	"backups" => array()
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);