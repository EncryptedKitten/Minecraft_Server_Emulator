<?php
include "mcse_common.php";
global $mcse_config;

$dist_root = $mcse_config["distribution_root"];

$redownload_allowed = array(
	"https://launchermeta.mojang.com/mc/game/version_manifest.json",
	"https://launchermeta.mojang.com/mc/launcher.json",
	"https://launcher.mojang.com/download/MinecraftInstaller.msi",
	"https://launcher.mojang.com/download/Minecraft.exe",
	"https://launcher.mojang.com/download/Minecraft.dmg",
	"https://launcher.mojang.com/download/Minecraft.deb",
	"https://launcher.mojang.com/download/Minecraft.tar.gz",
	"https://s3.amazonaws.com/MinecraftDownload/",
	"https://s3.amazonaws.com/MinecraftResources/"
);

function saveget($url)
{
	global $dist_root, $redownload_allowed;
	
	echo $url . "</br>\n";

	$filename = $dist_root . str_replace("https://", "", str_replace("http://", "", $url));

	if (substr($filename, -1) == "/")
	{
		$filename .= "index.html";
	}

	if (file_exists(dirname($filename)) and !is_dir(dirname($filename)))
	{
		$fn2 = dirname($filename, 2) . "/";
		$fn3 = str_replace($fn2, "", $filename);
		//It's not a slash, its now the UTF-8 U+2215 division slash character that has no correlation to any directory path separation!
		$filename = $fn2 . str_replace("/", "∕", $fn3);
	}

	if (!file_exists($filename) or in_array($url, $redownload_allowed))
	{
		$result = curl_get($url);

		if (!file_exists(dirname($filename))) {
			mkdir(dirname($filename), 0777, true);
		}

		file_put_contents($filename, $result);
	}
	else
		$result = file_get_contents($filename);

	return $result;
}

function save_version($result2)
{
	$asset = json_decode(saveget($result2["assetIndex"]["url"]), true);

	foreach($asset["objects"] as $id => $object) {
		saveget("http://resources.download.minecraft.net/" . substr($object["hash"], 0, 2) . "/" . $object["hash"]);
	}

	foreach($result2["downloads"] as $id => $download) {
		saveget($download["url"]);
	}

	if (array_key_exists("libraries", $result2)) {
		foreach ($result2["libraries"] as $library) {
			if (array_key_exists("artifact", $result2["downloads"])) {
				saveget($library["downloads"]["artifact"]["url"]);
			}

			if (array_key_exists("classifiers", $result2["downloads"])) {
				foreach ($result2["downloads"]["classifiers"] as $id => $download) {
					saveget($download["url"]);
				}
			}
		}
	}

	foreach($result2["logging"] as $id => $download) {
		saveget($download["file"]["url"]);
	}
}

function game()
{
	$url = "https://launchermeta.mojang.com/mc/game/version_manifest.json";
	$result = json_decode(saveget($url), true);

	foreach($result["versions"] as $version)
	{
		$result2 = json_decode(saveget($version["url"]), true);
		save_version($result2);
	}
}

function launcher()
{
	$url = "https://launchermeta.mojang.com/mc/launcher.json";
	$result = json_decode(saveget($url), true);

	saveget($result["java"]["lzma"]["url"]);

	foreach (["linux", "osx", "windows"] as $item) {
		saveget($result[$item]["applink"]);

		foreach (["32", "64"] as $bit) {
			if (!is_null($result[$item][$bit])) {
				foreach (["jdk", "jre"] as $java) {
					if (!is_null($result[$item][$bit][$java])) {
						saveget($result[$item][$bit][$java]["url"]);
					}
				}
			}
		}
	}
}

function launcher_current()
{
	//From https://www.minecraft.net/en-us/download/alternative
	$urls = array(
		"https://launcher.mojang.com/download/MinecraftInstaller.msi",
		"https://launcher.mojang.com/download/Minecraft.exe",
		"https://launcher.mojang.com/download/Minecraft.dmg",
		"https://launcher.mojang.com/download/Minecraft.deb",
		"https://launcher.mojang.com/download/Minecraft.tar.gz",
	);

	foreach ($urls as $url)
	{
		saveget($url);
	}
}

function multimc_meta()
{
	global $dist_root;
	chdir($dist_root);
	mkdir("meta.multimc.org");
	chdir("meta.multimc.org");

	saveget("https://meta.multimc.org/index.html");
	exec("git clone https://github.com/MultiMC/meta-multimc");

	rename("meta-multimc", "v1");

	$index_json = json_decode(file_get_contents("v1/index.json"), true);

	foreach ($index_json["packages"] as $package) {
		$uid_index_json = json_decode(file_get_contents("v1/" . $package["uid"] . "/index.json"), true);

		foreach($uid_index_json["versions"] as $version) {
			$result2 = json_decode(file_get_contents("v1/" . $package["uid"] . "/" . $version["version"] . ".json"), true);
			save_version($result2);
		}
	}
}

function savexml($url)
{
	$parser = xml_parser_create();
	$xml = xml_parse($parser, saveget($url));

	foreach ($xml["ListBucketResult"]["Contents"] as $item)
	{
		if ($item["Size"] != 0)
			saveget($url . $item["Key"]);
	}
}
function s3_legacy()
{
	$MinecraftDownload = "https://s3.amazonaws.com/MinecraftDownload/";
	$MinecraftResources = "https://s3.amazonaws.com/MinecraftResources/";

	savexml($MinecraftDownload);
	savexml($MinecraftResources);
}

function github_gist_link()
{
	$github_gist_url = "https://gist.githubusercontent.com/morbeo/f228b8a6e0d08df0397df2773125beb3/raw";

	$lines = explode("\n", saveget($github_gist_url));

	foreach ($lines as $line)
	{
		$url = explode("https://", $line);

		if (count($url) > 1)
		{
			$url = "https://" . end($url);

			$result2 = json_decode(saveget($url), true);
			save_version($result2);
		}
	}
}

function ripbase($base, $content)
{
	$split2 = "\"" . $base;

	$version_links2 = explode($split2, $content);
	array_shift($version_links2);
	foreach ($version_links2 as $version_link2)
	{
		$version_url2 = $base . explode("\"", $version_link2)[0];
		$version_url2 = explode("?", $version_url2)[0];
		$version_url2 = explode("#", $version_url2)[0];
		saveget($version_url2);
	}
}

function minecraft_wiki_page($url)
{
	$base = "https://minecraft.fandom.com/wiki/Java_Edition_";
	$split = "\"/wiki/Java_Edition_";

	$version_links = explode($split, saveget($url));

	array_shift($version_links);

	foreach ($version_links as $version_link)
	{
		$version_url = $base . explode("\"", $version_link)[0];
		$version_url = explode("?", $version_url)[0];
		$version_url = explode("#", $version_url)[0];
		$version_data = saveget($version_url);

		ripbase("https://launcher.mojang.com", $version_data);
		ripbase("https://launchermeta.mojang.com", $version_data);
		ripbase("https://archive.org", $version_data);
	}
}

function minecraft_wiki()
{
	$release_versions_url = "https://minecraft.fandom.com/wiki/Java_Edition_version_history";
	$development_versions_url = "https://minecraft.fandom.com/wiki/Java_Edition_version_history/Development_versions";

	minecraft_wiki_page($release_versions_url);
	minecraft_wiki_page($development_versions_url);
}

function all()
{
	global $dist_root;
	//Yes, 1.6 has been released.
	saveget("https://assets.minecraft.net/1_6_has_been_released.flag");

	//This URL (It's listed in the 1.6 release flag) now redirects to minecraft.net, but what if you need to read about this revolutionary Minecraft 1.6 Horse Update!
	saveget("https://web.archive.org/web/20130702232237if_/https://mojang.com/2013/07/minecraft-the-horse-update/");

	game();
	launcher();
	s3_legacy();
	multimc_meta();
	github_gist_link();
	launcher_current();
	minecraft_wiki();

	if (file_exists($dist_root . "index.html"))
		unlink($dist_root . "index.html");
}

all();