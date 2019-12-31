<?php
header("Access-Control-Allow-Origin: *");

if (file_exists('override.config.php'))//takes < 1 ms, but for max speed you can make local modification to just include file without check
{
    require_once('override.config.php');
}

require_once('sys.config.php');
require_once('config.php');
require_once(SYS_CLASSES_PATH . '/_database_handler.php');
require_once(SYS_CLASSES_PATH . '/helpers/HelpersLoader.php');
require_once(SYS_CLASSES_PATH . '/_api.php');

$api = new api(UrlParser::path(), DataParser::getArray(), false, METHOD);
$api->output_headers();
echo $api->OUTPUT_RESPONSE;