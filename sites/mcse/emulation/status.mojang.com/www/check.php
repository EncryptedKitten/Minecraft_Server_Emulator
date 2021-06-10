<?php
include "mcse_common.php";

header('Content-Type: application/json');
$response = array(
	array("minecraft.net" => "green"),
	array("session.minecraft.net" => "green"),
	array("account.mojang.com" => "green"),
	array("authserver.mojang.com" => "green"),
	array("sessionserver.mojang.com" => "green"),
	array("api.mojang.com" => "green"),
	array("textures.minecraft.net" => "green"),
	array("mojang.com" => "green"),
);

response_telemetry("", 200);
echo json_encode($response);
?>