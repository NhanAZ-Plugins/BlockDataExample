<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$pharPath = $argv[1] ?? ($projectRoot . "/build/BlockDataExample.phar");
if(!file_exists($pharPath)){
	throw new RuntimeException("Build artifact does not exist: " . $pharPath);
}

$phar = new Phar($pharPath);
$entryPaths = [];
foreach(new RecursiveIteratorIterator($phar) as $entry){
	$path = str_replace("\\", "/", $entry->getPathname());
	$markerPosition = strpos($path, ".phar/");
	if($markerPosition !== false){
		$entryPaths[] = substr($path, $markerPosition + strlen(".phar/"));
	}
}

$blockDataSourceRoots = [];
foreach($entryPaths as $entryPath){
	if(preg_match('#^(src/.+/_DevTools/BlockData_[a-f0-9]{12})/BlockData\.php$#', $entryPath, $matches) === 1){
		$blockDataSourceRoots[$matches[1]] = true;
	}
}
if(count($blockDataSourceRoots) !== 1){
	throw new RuntimeException("Build must contain exactly one DevTools-shaded BlockData source root");
}

$blockDataSourceRoot = (string) array_key_first($blockDataSourceRoots);
$requiredEntries = [
	"plugin.yml",
	"LICENSE",
	"src/BlockDataExample/Main.php",
	"{$blockDataSourceRoot}/BlockData.php",
	"{$blockDataSourceRoot}/BlockDataListener.php",
	"{$blockDataSourceRoot}/BlockDataWorld.php",
	"META-INF/virions/BlockData/LICENSE",
];
foreach($requiredEntries as $entry){
	if(!isset($phar[$entry])){
		throw new RuntimeException("Build is missing required entry: " . $entry);
	}
}

$pluginLicense = file_get_contents($projectRoot . "/LICENSE");
$normalizeNewlines = static fn(string $text) : string => str_replace(["\r\n", "\r"], "\n", $text);
if($pluginLicense === false || $normalizeNewlines($phar["LICENSE"]->getContent()) !== $normalizeNewlines($pluginLicense)){
	throw new RuntimeException("Build does not contain the plugin AGPL license text");
}

$virionSource = $argv[2] ?? ($projectRoot . "/virions/BlockData");
$virionLicense = file_get_contents($virionSource . "/LICENSE");
if($virionLicense === false || $normalizeNewlines($phar["META-INF/virions/BlockData/LICENSE"]->getContent()) !== $normalizeNewlines($virionLicense)){
	throw new RuntimeException("Build does not contain the BlockData LGPL license text");
}

if(isset($phar["src/NhanAZ/BlockData/BlockData.php"])){
	throw new RuntimeException("Build contains an unshaded BlockData source copy");
}

$pluginYml = $phar["plugin.yml"]->getContent();
foreach(["version: 1.0.1", "  inspect:", "  blockdata.command.inspect:", "  blockdata.bypass:"] as $requiredManifestFragment){
	if(!str_contains($pluginYml, $requiredManifestFragment)){
		throw new RuntimeException("Built plugin manifest is missing: " . $requiredManifestFragment);
	}
}

$mainSource = $phar["src/BlockDataExample/Main.php"]->getContent();
if(!str_contains($mainSource, "BlockData::create") || !str_contains($mainSource, "onBlockPlace") || !str_contains($mainSource, "onBlockBreak")){
	throw new RuntimeException("Built example is missing its BlockData integration");
}
if(!str_contains($mainSource, "\\_DevTools\\BlockData_")){
	throw new RuntimeException("Built example does not reference the DevTools-shaded BlockData namespace");
}

echo "DevTools build verification passed: shaded BlockData sources and license are present." . PHP_EOL;
