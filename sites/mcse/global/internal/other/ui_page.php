<?php
function get_debug()
{
	return "<div class=\"alert alert-warning\" role=\"alert\">This is a debug page. Usage of it may result in improper, invalid, and broken responses from the server.</div>";
}

function get_head()
{
	return "
	<!-- Required meta tags -->
	<meta charset=\"utf-8\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">

	<!-- Material Design for Bootstrap fonts and icons -->
	<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons\">

	<!-- Material Design for Bootstrap CSS -->
	<link rel=\"stylesheet\" href=\"https://unpkg.com/bootstrap-material-design@4.1.1/dist/css/bootstrap-material-design.min.css\" integrity=\"sha384-wXznGJNEXNG1NFsbm0ugrLFMQPWswR3lds2VeinahP8N0zJw9VWSopbjv2x7WCvX\" crossorigin=\"anonymous\">

	<!-- <script src=\"https://code.jquery.com/jquery-3.2.1.slim.min.js\" integrity=\"sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN\" crossorigin=\"anonymous\"></script> -->
	<script src=\"https://code.jquery.com/jquery-3.4.1.js\"></script>
	<script src=\"https://unpkg.com/popper.js@1.12.6/dist/umd/popper.js\" integrity=\"sha384-fA23ZRQ3G/J53mElWqVJEGJzU0sTs+SvzG8fXVWP+kJQ1lwFAOkcUOysnlKJC33U\" crossorigin=\"anonymous\"></script>
	<script src=\"https://unpkg.com/bootstrap-material-design@4.1.1/dist/js/bootstrap-material-design.js\" integrity=\"sha384-CauSuKpEqAFajSpkdjv3z9t8E7RlpJ1UP0lKM/+NdtSarroVKu069AlsRPKkFBz9\" crossorigin=\"anonymous\"></script>
	";
}

$site_config;

function get_config()
{
	global $site_config;
	if (array_key_exists("admin_ui", $GLOBALS))
		$filename = site_dir() . "/internal/admin.json";
	else
		$filename = site_dir() . "/internal/config.json";
	$site_config = json_decode(file_get_contents($filename), true);
}

function get_navbar()
{
	global $site_config;
	
	$navbar_contents = $site_config["navbar"];

	$navbar = "<ul class=\"nav nav-tabs bg-dark\">";

	foreach ($navbar_contents as $name => $navbar_item){
		$navbar .= "<div>";
		if (is_string($navbar_item)){
			$navbar .= "<li class=\"nav-item\">
			<a class=\"nav-link\" href=\"" . $navbar_item . "\">" . $name . "</a>
			</li>";
		}
		else {
			$navbar .= "<a class=\"nav-link dropdown-toggle text-light\" id=\"" . "navbar_" . strtolower($name) . "\" data-toggle=\"dropdown\"
			aria-haspopup=\"true\" aria-expanded=\"false\">" . $name . "</a>
			<div class=\"dropdown-menu dropdown-primary\" aria-labelledby=\"" . "navbar_" . strtolower($name) . "\">";

			foreach ($navbar_item as $sub_name => $sub_navbar_item) {
				$navbar .= "<a class=\"dropdown-item\" href=\"" . $sub_navbar_item . "\">" . $sub_name . "</a>";
			}

			$navbar .= "</div></li>";
		}
		$navbar .= "</div>";
	}

	$navbar .= "</ul>";

	return $navbar;
}

function get_footer()
{
	return "<script>$(document).ready(function() { $('body').bootstrapMaterialDesign(); });</script>";
}

$debug;
$head;
$navbar;
$footer;

function ui_init()
{
	global $debug, $head, $navbar, $footer;
	get_config();
	$debug = get_debug();
	$head = get_head();
	$navbar = get_navbar();
	$footer = get_footer();
}

function minify_html($html)
{
	$search = array(
	'/(\n|^)(\x20+|\t)/',
	'/(\n|^)\/\/(.*?)(\n|$)/',
	'/\n/',
	'/\<\!--.*?-->/',
	'/(\x20+|\t)/', # Delete multispace (Without \n)
	'/\>\s+\</', # strip whitespaces between tags
	'/(\"|\')\s+\>/', # strip whitespaces between quotation ("') and end tags
	'/=\s+(\"|\')/'); # strip whitespaces between = "'

	$replace = array(
	"\n",
	"\n",
	" ",
	"",
	" ",
	"><",
	"$1>",
	"=$1");

	$html = preg_replace($search,$replace,$html);
	return $html;
}

function show_page($title, $contents)
{
	global $site_config, $head, $navbar, $footer;
	ui_init();

	$title_field = ($title ? $title . " | " . $site_config["site"] : $site_config["site"]);

	$page_title_field = ($title ? "" : $site_config["site"]);

	$top = "<!DOCTYPE html>
	<html lang=\"en\">
		<head>
			" . $head . "
			<title>" . $title_field . "</title>
		</head>
		<body>" . $navbar . "
			<div style=\"margin-left:10%;margin-right:10%;margin-top:5%;margin-bottom:5%;\">
			<h1>" . $page_title_field . "</h1>";
	
	$bottom = "</div>" . $footer . "</body></html>";

	$html = $top . $contents . $bottom;
	$html = minify_html($html);
	echo $html;
}
?>