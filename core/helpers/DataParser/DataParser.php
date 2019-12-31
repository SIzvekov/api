<?php


class DataParser
{
    private static $_instance = null;

    private $_data = array();

    private function __construct()
    { //Prevent any oustide instantiation of this class
        // $this->_instance = new();
        try {
            parse_str(file_get_contents("php://input"), $POSTDATA);
            $this->_data = array_merge($_GET, $_POST, $POSTDATA);
        } catch (Exception $e) {
            echo "Error while Request parameters Parsing";
        }
    }

    private function __clone()
    {
    } //Prevent any copy of this object

    private function instance(){
        if (!is_object(self::$_instance)) {
            self::$_instance = new DataParser();
        }
        return self::$_instance;
    }

    public static function isValidGUID($guid = '')
    {
        return preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/', $guid);
    }

    public function prepareMSTarray($val = '', $array = array(), $table, $f){
        if(!$f) return null;
        static $tableInfo = array();

        if($table){
            if(isset($tableInfo[$table])) $ar = $tableInfo[$table];
            else {
                $sth = $this->dbInstance()->pdo->prepare('DESCRIBE `'.addslashes($table).'`');
                $sth->execute();
                $ar = $sth->fetchAll(PDO::FETCH_ASSOC);
                $tableInfo[$table] = $ar;
            }
            foreach ($ar as $field) {
                if($field['Field'] != $f) continue;
                if(!preg_match("/^enum/", $field['Type'])) continue;
                $a = preg_replace("/^enum/", "", $field['Type']);
                $a = preg_replace("/^\(/", "[", $a);
                $a = preg_replace("/\)$/", "]", $a);
                $a = preg_replace("/^\[\'/", "[\"", $a);
                $a = preg_replace("/\'\]$/", "\"]", $a);
                $a = preg_replace("/','/", "\",\"", $a);
                $a = json_decode($a, true);
            }
            if(is_array($a)) $array = $a;
        }

        $returnArray = array();
        if(!is_array($array)) $array = array();

        if(!sizeof($array)){
            $returnArray = array($val => true);
        }else{
            foreach ($array as $item) {
                $returnArray[$item] = ($item == $val);
            }
        }
        return $returnArray;
    }

    function getArray(){
        return DataParser::instance()->_data;
    }
}