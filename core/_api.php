<?php

class api
{
    var $requestURL = '';
	var $requestURLnorm = null;
	var $inputData = array();
	var $METHOD = null;
	var $OUTPUT_CODE = 200;
	var $OUTPUT_STATUS = 'OK';
	var $OUTPUT_RESPONSE = null;
	var $REQUEST_HASH = '';
	var $HEADERS = array();
	var $cache_personal = false;
	var $cache_ttl = 0;
	var $cache_filetime = 0;
    var $ENCODING = 'utf-8';
    var $CONTENT_TYPE = 'application/json';
    var $OUTPUT_FORMAT = 'json';
    var $DATA = array();
    var $LIMIT = null;
    var $PAGE = null;
    var $RESPONSE_PAGINATION = null;
    var $timeStart = 0;
    var $db = null;
    var $TOKEN = null;

    public function __construct(
        $requestURL = '',
        $inputData = array(),
        $subResource = true,
        $method = 'GET',
        $timeStart = 0,
        $as_root_user = false,
        $parent = null
    ){
    	$this->requestURL = $this->applyNestedRules(trim($requestURL, '/'));
    	$this->inputData = $inputData;
    	$this->METHOD = $method;
        $this->DATA = $inputData;
        
    	$this->cache_ttl = RESOURCE_RESPONSE_DEFAULT_CAHCE_TIME;
    	// define ENCODING
        $this->ENCODING = RESULT_DEFAULT_ENCODING;
		// define CONTENT_TYPE
        $this->CONTENT_TYPE = RESULT_DEFAULT_CONTENT_TYPE;

        $this->OUTPUT_FORMAT = isset($this->DATA['_output']) && $this->DATA['_output'] ? $this->DATA['_output'] : RESULT_DEFAULT_OUTPUT_TYPE;
        unset($this->DATA['_output']);

        // define LIMIT
        $this->LIMIT = intval($this->DATA['_limit']);
        unset($this->DATA['_limit']);

         // define TOKEN
        $this->TOKEN = isset($this->DATA['_token']) ? $this->DATA['_token'] : null;
        unset($this->DATA['_token']);

        // define PAGE
        $this->PAGE = intval($this->DATA['_page']) > 1 ? intval($this->DATA['_page']) : 1;
        unset($this->DATA['_page']);

        // define METHOD
        if(isset($this->DATA['_method'])){
            if($this->is_method_allowed($this->DATA['_method'])){
                $this->METHOD = strtoupper($this->DATA['_method']);
            }
            unset($this->DATA['_method']);
        }



    	if(!$timeStart){
    		$this->timeStart = time() + microtime();
    	}else{
            $this->timeStart = $timeStart;
        }
    	if(!$this->is_method_allowed($method)){
            $this->OUTPUT_CODE = '405';
    		$this->_output();
    		return false;
    	}
    	
        $this->_exec_resource();
        $this->_output();
    }
    public function __destruct(){
        if(!is_object($this->db)){
            unset($this->db);
        }
    }
    function is_method_allowed($method = ''){
    	$method = strtoupper(trim($method));
        if(!$method) return false;
    	return in_array($method, $this->allowed_request_methods()) ? true : false;

    }
    function allowed_request_methods(){
    	$ALLOWED_REQUEST_METHODS = explode(',', ALLOWED_REQUEST_METHODS);
    	array_walk($ALLOWED_REQUEST_METHODS, "trim");
    	return $ALLOWED_REQUEST_METHODS;
    }

    private function get_cachefile_name()
    {
        static $filename;
        if ($filename) {
            return $filename;
        }

        //create MD5 hash from incoming DATA , URI and token
        $hash_array = array(
        	$this->requestURL,
	    	$this->inputData,
	    	$this->METHOD
        );

        $filename = $this->REQUEST_HASH = $this->METHOD . '.' . md5(serialize($hash_array));
        return $filename;
    }

    function _exec_resource(){
        if(!$this->requestURL){
            $this->OUTPUT_CODE = 400;
            $this->OUTPUT_RESPONSE = 'Empty resource is not allowed';
            return false;
        }

        $resource_dir_path = RESOURCES_PATH."/".$this->requestURLnormalize();
        if(!is_dir($resource_dir_path)){
            $this->OUTPUT_CODE = 404;
            $this->OUTPUT_RESPONSE = 'Requested resource [' . $this->requestURL . '] does not exist';
            return false;
        }

        $resource_method_path = $resource_dir_path."/".strtolower($this->METHOD).".php";
        if(!is_file($resource_method_path)){
            $this->OUTPUT_CODE = 405;
            $this->OUTPUT_RESPONSE = "Requested method [".$this->METHOD."] for resource [".$this->requestURL."] is not allowed";
            return false;
        }

        $this->db = $this->dbInstance();
        if(!$this->db->connectEstablished){
            $this->OUTPUT_CODE = 500;
            $this->OUTPUT_RESPONSE = $this->db->errorMessages;
            return false;
        }

        if(!$this->user_has_access_to_resource($resource_method_path)){
            $this->OUTPUT_CODE = 403;
            $this->OUTPUT_RESPONSE = "Authorization required";
            return false;
        }

        ob_start();
            include($resource_method_path);
            $textOutput = trim(ob_get_contents());
        ob_clean();

        if(!is_array($this->OUTPUT_RESPONSE) && $textOutput){
            $this->OUTPUT_RESPONSE = array($this->OUTPUT_RESPONSE);
            $this->OUTPUT_RESPONSE['rawOutput'] = $textOutput;
        }
    }

    function requestURLnormalize(){
        if($this->requestURLnorm) return $this->requestURLnorm;

        $explodedUrl = explode("/",$this->requestURL);
        foreach ($explodedUrl as $key => $value) {
            if(DataParser::isValidGUID($value) or preg_match('/^0.*/', $value)){
                $explodedUrl[$key] = '_id_';
            }else{
                $explodedUrl[$key] = $value;
            }
        }
        $this->requestURLnorm = implode("/", $explodedUrl);
        return $this->requestURLnorm;
    }

    function _output(){
        global $DEFAULT_HTTP_CODES;
        $this->HEADERS[] = "ETag: " . $this->get_cachefile_name();

    	$this->OUTPUT_STATUS = $DEFAULT_HTTP_CODES[$this->OUTPUT_CODE];

    	$sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) == 'cgi') {
            $this->HEADERS[] = "Status: " . $this->OUTPUT_CODE . " " . $this->OUTPUT_STATUS;
        } else {
            $this->HEADERS[] = HTTP_VERSION . " " . $this->OUTPUT_CODE . " " . $this->OUTPUT_STATUS;
        }

        $this->HEADERS[] = 'Cache-Control: ' . ($this->cache_personal ? 'private' : 'public') . ', max-age=' . $this->cache_ttl;

        $lastModifiedTime = $this->cache_filetime ? $this->cache_filetime : time();
        $this->HEADERS[] = "Last-Modified: " . gmdate('D, d M Y H:i:s \G\M\T', $lastModifiedTime);

        ///// EXECUTE HEADERS
        $this->HEADERS[] = "Content-Type:" . $this->CONTENT_TYPE . "; charset=" . $this->ENCODING;
        $this->HEADERS[] = "Access-Control-Allow-Origin: *";

        // total time of execution 
        $execTime = $this->_get_exec_time(4);
        $this->HEADERS[] = "TTE: " . $execTime;

        $output = $this->get_output();
    }

    private function get_output()
    {

        if (!$this->OUTPUT_CODE) {
            $this->OUTPUT_CODE = RESULT_DEFAULT_STATUS_CODE;
        }
        if (!$this->OUTPUT_STATUS) {
            $this->OUTPUT_STATUS = $DEFAULT_HTTP_CODES[$this->OUTPUT_CODE];
        }

        $this->OUTPUT_RESPONSE = $this->apply_pagination($this->OUTPUT_RESPONSE, $this->LIMIT, $this->PAGE, true, false);

        $output_handler = OUTPUT_HANDLER_PATH . '/' . $this->OUTPUT_FORMAT . '.php';
        if (!is_file($output_handler)) {
            $output_handler = OUTPUT_HANDLER_PATH . '/' . RESULT_DEFAULT_OUTPUT_TYPE . '.php';
        }
        if (is_file($output_handler)) {
            ob_start();
            include($output_handler);
            $this->OUTPUT_RESPONSE = trim(ob_get_contents());
            ob_clean();
        }

        return $this->OUTPUT_RESPONSE;
    }

    function output_headers(){
        if(!is_array($this->HEADERS)) return;

        foreach($this->HEADERS as $header){
            header($header);
        }
    }

    function apply_pagination($array = array(), $limit, $page, $save_paginator_meta = true, $strict = true){
        return $array;
    }

    function user_has_access_to_resource($resourcePath){
        return true;
    }

    function _get_exec_time($round = null){
        $currentTime = time() + microtime();
        $execTime = $currentTime - $this->timeStart;
        if(!is_null($round) && is_numeric($round)){
            $round = intval($round);
            $execTime = round($execTime, $round);
        }
        return $execTime;
    }

    function dbInstance(){
        if(!is_object($this->db)){
            $this->db = new db(DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS);
        }
        return $this->db;
    }

    function hookClass($classPath = '')
    {
        $classPath = CLASSES_PATH . '/' . trim(trim($classPath), '/') . '.php';
        if (is_file($classPath)) {
            include_once $classPath;
        } else {
            return false;
        }
    }

    function applyNestedRules($url = ''){
        $nestedRules = json_decode(file_get_contents(SYS_CLASSES_PATH . '/nestedrules.json'), true);
        if(isset($nestedRules[$url]) && !is_null($nestedRules[$url])){
            $url = $nestedRules[$url];
        }
        return $url;
    }

    function json_numeric_check($array){
        if(!is_array($array)) return $this->get_type_value(null, $array);
        
        foreach($array as $k => $v){
            if(is_object($v)) {
                $v = json_decode(json_encode($v), true);  
            }

            if(!is_array($v)) $array[$k] = $this->get_type_value($k, $v);
            else $array[$k] = $this->json_numeric_check($v);
        }
        return $array;
    }
    /**
     * Returns a floating-point type variable if the string contains a numeric value
     * and the key is not in the exception list.
     * @param $key
     * @param $val
     * @return float|null|string
     */
    function get_type_value($key, $val){
        if(is_null($val)) return null;
        if(is_bool($val)) return $val;

        $doNotProcessFields = is_array(NUMERIC_CHECK_IGNORE_FIELDS) ? NUMERIC_CHECK_IGNORE_FIELDS : array();
        
        if(
            is_numeric($val) and
            (is_numeric($key) || !in_array($key, $doNotProcessFields))
        ){
            return floatval($val);
        }
        
        return strval($val);
    }
}
