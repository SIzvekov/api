<?php


class UrlParser
{
    private static $_instance = null;

    private function __construct()
    { //Prevent any oustide instantiation of this class
        
    }

    private function __clone()
    {
    } //Prevent any copy of this object

    private function instance(){
        if (!is_object(self::$_instance)) {
            self::$_instance = new UrlParser();
        }
        return self::$_instance;
    }

    function parsePath($path = ''){
        $path = trim($path);
        $path = trim($path, '/');
        return explode("/", $path);
    }

    function path($key){
        $REQUEST_URI = explode("?", $_SERVER['REQUEST_URI'], 2);
        $REQUEST_URI = $REQUEST_URI[0];
        $REQUEST_URI = trim(preg_replace("/^\/".trim(DOCUMENT_SUBROOT, '/')."\//","",$REQUEST_URI), '/');

        if($key){
            $array = explode("/",$REQUEST_URI);
            return $array[$key];
        }else
            return $REQUEST_URI;
    }

    public static function ID($key)
    {
        return UrlParser::instance()->getID($key);
    }

    public function getID($key)
    {
        $ID = $this->path($key);
        if(DataParser::isValidGUID($ID))
            return $ID;
        elseif (preg_match("/^0(.+)/i", $ID)) 
            return preg_replace("/^0(.+)/i", '\\1', $ID);
        else
            return $ID;
    }
}