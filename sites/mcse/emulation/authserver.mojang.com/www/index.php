<?php
include "mcse_common.php";

// Supposed to be the response when accessing https://authserver.mojang.com without a page path, currently only working hen accessing it as index.php.
header('Content-Type: application/json');
$response = array(
	"Status" => "OK",
	"Runtime-Mode" => "productionMode",
	"Application-Author" => "Mojang Web Force",
	"Application-Description" => "Mojang Authentication Server.",
	"Specification-Version" => "4.15.1",
	"Application-Name" => "yggdrasil.auth.restlet.server",
	"Implementation-Version" => "4.15.1_build263",
	"Application-Owner" => "Mojang"
);

response_telemetry("", 200);
echo json_encode($response);