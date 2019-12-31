<?php
// PHP Error reporting settings
ini_set('display_errors', 0);
error_reporting(0);

// DataBase connection settings
@define('DB_TYPE', 'mysql');
@define('DB_HOST', 'localhost');
@define('DB_NAME', '');
@define('DB_USER', '');
@define('DB_PASS', '');


// Paths and urls
@define("DOCUMENT_SUBROOT", '');
@define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT'].( trim(DOCUMENT_SUBROOT, '/') ? '/'.trim(DOCUMENT_SUBROOT, '/') : ''));
@define("OUTPUT_HANDLER_PATH", DOCUMENT_ROOT . '/output_handler');
@define("RESOURCES_PATH", DOCUMENT_ROOT . '/resources');
@define("SYS_CLASSES_PATH", DOCUMENT_ROOT . '/core');
@define("CLASSES_PATH", DOCUMENT_ROOT . '/classes');
@define("ASSETS_PATH", DOCUMENT_ROOT . '/assets');
@define("LOG_PATH", DOCUMENT_ROOT . '/logs');
@define("CACHE_PATH", DOCUMENT_ROOT . '/cache');
@define("VENDORS_PATH", DOCUMENT_ROOT . '/vendors');


// Cache settings
@define("CACHE_ENABLE", false);
@define("DB_CACHE_ENABLE", false);
@define("CACHE_TIME", 100000000);
@define("RESOURCE_RESPONSE_DEFAULT_CAHCE_TIME", 100000000);


// HTTP settings
@define("METHOD", strtoupper($_SERVER['REQUEST_METHOD']));
@define("PROTOCOL", "http");
@define("HTTP_HOST", $_SERVER['HTTP_HOST']);
@define("CURRENT_HREF", PROTOCOL . '://' . HTTP_HOST . $_SERVER['REQUEST_URI']);
@define("HTTP_VERSION", "HTTP/1.1");
@define("RESULT_DEFAULT_STATUS_CODE", "200");
@define("RESULT_DEFAULT_STATUS_TEXT", "OK");
@define("RESULT_DEFAULT_CONTENT_TYPE", "application/json");
@define("RESULT_DEFAULT_OUTPUT_TYPE", "json");
@define("RESULT_DEFAULT_ENCODING", "UTF-8");
@define("ALLOWED_REQUEST_METHODS", 'GET,POST,PUT,DELETE');

// Session and authentication settings
@define('SRV_APIKEY', ''); // used for authentication 
@define('SESSION_TTL', '1500'); // for example 1500 means 15 minutes 00 seconds
@define("TOKEN_TIME", 43200);

// Default HTTP response codes
if (!isset($DEFAULT_HTTP_CODES)) {
    $DEFAULT_HTTP_CODES = array(
        '200' => 'OK',
        '201' => 'Created',
        '204' => 'No Content',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '412' => 'Precondition Failed',
        '497' => 'HTTP to HTTPS',
        '498' => 'Token expired/invalid',
        '499' => 'Token required',
        '500' => 'Internal Server Error',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
        '520' => 'Unknown Error',
    );
}

// SendGrid settings
@define('SENDGRID_API_KEY', '');
@define('EMAIL_LIVE_MODE', false); // if this is true then class v2/email will actually send messages. If this is false it will just store 

// Other settings
@define("DEBUGMODE", false);
@define("MONITORING", false);

@define("NESTED_KEYVALUE_DELIMITER", ">");
@define("NESTED_KEY_DELIMITER", "/");
@define("NESTED_VALUE_DELIMITER", "/");

if (!isset($NESTED_RULES)) {
    $NESTED_RULES = file('core/nestedrules.txt');
}
//////////////////////////////////////////////////////////////////////
// map of tokens used in nested rules to the table
// that has the matching api_secret, and the field
// containing the id value that the token refers to
// For Example on {:selfCompanyId:} we don't use
// accounting_accounts.id because this would not have
// the --> api_secret <-- that is unique to the user.
// There can be multiple admins. They are just users
// marked with is_admin
//////////////////////////////////////////////////////////////////////
if (!isset($NESTED_RULES_TOKENS)) {
    $NESTED_RULES_TOKENS = array(
    );
}

//////////////////////////////////////////////////////////////////////
// the user's token will match a session with one of these
// user types.  The type is written into the session when
// they log in.  They log in specifying "account", "affiliate",
// "siteAdmin". The authentication code may or may not transform the
// userType to be more specific, eg accountUser or accountAdmin.
// The value is the table that should contain the api_secret matching
// the session's api_secret
//////////////////////////////////////////////////////////////////////
if (!isset($AUTH_ALLOWED_TYPES)) {
    $AUTH_ALLOWED_TYPES = array(
    );
}

////////
// V2
////////

if (!isset($USER_TYPES_TABLES)) {
    $USER_TYPES_TABLES = array(
    );
}

//////////////////////////////////////////////////////////////////////
// for each usertype being requested, this specifies the field
// containing the "username". It is assumed there's a field called
// "password" in all cases
//////////////////////////////////////////////////////////////////////
if (!isset($AUTH_BY_LOGIN)) {
    $AUTH_BY_LOGIN = array(
    );
}
