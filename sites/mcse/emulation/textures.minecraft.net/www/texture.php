<?php
include "mcse_common.php";

check_request("GET");

global $textures_data_path;

$file = $textures_data_path . basename($_GET["path"]);

if (!file_exists($file))
{
	header('Content-Type: application/json');
	minecraft_error("not_found");
}

header('Content-Type: image/png');

response_telemetry("", 200);
echo file_get_contents($file);
?>