<?php
include "mcse_common.php";
global $now;
header('Content-Type: application/json');

check_request("POST");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$total = 0;
$last24h = 0;

$saleVelocityPerSeconds = 0;

foreach ($data["metricKeys"] as $sale_type)
{

	$total += get_all_sales($sale_type);
	$last24h += get_24h_sales($sale_type);

	$saleVelocityPerSeconds += $total / ($now - strtotime("2009-12-23 00:00:00"));
}

$saleVelocityPerSeconds /= sizeof($data["metricKeys"]);

$response = array(
	"total" => $total,
	"last24h" => $last24h,
	"saleVelocityPerSeconds" => $saleVelocityPerSeconds
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);
?>