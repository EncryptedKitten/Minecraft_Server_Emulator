<?php
include "mcse_common.php";

function security_questions()
{
	$filename = site_dir() . "/internal/security_questions.json";

	return json_decode(file_get_contents($filename));
}
?>