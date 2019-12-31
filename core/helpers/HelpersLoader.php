<?php
$dir = opendir(__DIR__);
while ($file = readdir($dir)) {
	if($file == "." || $file == "..") continue;

	$subDirPath = __DIR__."/".$file;
	if(!is_dir($subDirPath)) continue;
	
	$filePath = $subDirPath."/".$file.".php";
	if(!is_file($filePath)) continue;
	include_once($filePath);
}