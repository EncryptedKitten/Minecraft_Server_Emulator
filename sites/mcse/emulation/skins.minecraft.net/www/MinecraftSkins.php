<?php
include "mcse_common.php";

check_request("GET");

$name = str_replace(".png", "", basename($_GET["path"]));

$profile = name_to_profile($name);

if (is_null($profile) or is_null($profile["skin"])) {
	header('Content-Type: application/json');
	minecraft_error("not_found");
}

header('Content-Type: image/png');

global $textures_data_path;
$file = $textures_data_path . bin2hex($profile["skin"]);

response_telemetry("", 200);
echo file_get_contents($file);
?>