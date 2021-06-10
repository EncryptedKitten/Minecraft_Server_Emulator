<?php
include "mcse_common.php";

check_realms("GET");

$response = array(
	"templates" => array(),
	"page" => 1,
	"size" => 0,
	"total" => 0
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);