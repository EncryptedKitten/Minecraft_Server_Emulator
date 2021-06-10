<?php
$now = time();
$mcse_global_path = dirname(__FILE__, 2);
$config_path = $mcse_global_path . "/config/mcse_config.ini";
$mcse_config = parse_ini_file($config_path, true);

function site_dir()
{
	return dirname($_SERVER["DOCUMENT_ROOT"]);
}

function change_server($target_server)
{
	$site_dir = site_dir();

	$current_server = str_replace("/", "", str_replace(dirname($site_dir), "", $site_dir));

	include str_replace($current_server, $target_server, $_SERVER['SCRIPT_FILENAME']);
}

include $mcse_global_path . "/internal/other/ui_page.php";
include $mcse_global_path . "/internal/database.php";
include $mcse_global_path . "/internal/emulation/functions.php";
include $mcse_global_path . "/internal/other/mcsha1.php";
?>