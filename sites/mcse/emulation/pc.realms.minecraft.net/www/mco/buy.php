<?php
include "mcse_common.php";

$profile = check_realms("GET");

$response = array(
	"statusMessage" => "This is not what the actual Mojang status message would be because this realms emulation is being provided by EncryptedKitten's Minecraft Server Emulator",
	"buyLink" => "https://github.com/EncryptedKitten/Minecraft_Server_Emulator"
);

response_telemetry(json_encode($response), 200);
echo json_encode($response);