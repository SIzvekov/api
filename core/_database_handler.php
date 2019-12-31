<?php

class db
{
    var $pdo = '';
    var $connectEstablished = false;
    var $cacheTTL = 0;
    var $cacheEnabled = false;
    var $currentDataBaseName = '';
    var $fetchStyle = PDO::FETCH_ASSOC;

    var $db = null;

    var $errorMessages = array();

    var $queryParams = array();

    function __construct($type = '', $host = '', $db = '', $user = '', $pass = '')
    {
        try {
            $this->pdo = new PDO($type . ':host=' . $host . ';dbname=' . $db . ';charset=utf8', $user, $pass);
            $this->connectEstablished = true;

            if(defined("CACHE_TIME")) $this->cacheTTL = CACHE_TIME;
            if(defined("DB_CACHE_ENABLE")) $this->cacheEnabled = DB_CACHE_ENABLE;

            $this->currentDataBaseName = $db;

        } catch (PDOException $e) {
            $this->errorMessages[] = 'DB Connection failed: ' . $e->getMessage();
        }
    }

    function select(){
        if(is_array($this->queryParams['fields']))
            $this->queryParams['fields'] = implode(', ', $this->queryParams['fields']);

        $fields = trim($this->queryParams['fields']) ? $this->queryParams['fields'] : '*';
        
        if($this->queryParams['table']){
            $table =  $this->queryParams['table'];    
        }else{
            $this->errorMessages[] = 'select(): table is not specified';
            return false;
        }

        if($this->queryParams['where']){
            $where = $this->queryParams['where'];    
        }else{
            $where = '1';
        }

        if($this->queryParams['order']){
            $order = $this->queryParams['order'];
        }else{
            $order = null;
        }

        if($this->queryParams['limit']){
            $limit = $this->queryParams['limit'];
        }else{
            $limit = null;
        }

        $sql = "
        SELECT ".$fields." 
        FROM ".$table." 
        WHERE ".$where."
        ".($order ? ' ORDER BY '.$order : '').
        ($limit ? ' LIMIT '.$limit : '');
echo $sql."<hr>";
        $sth = $this->pdo->prepare($sql);
        if(is_array($this->queryParams['params'])) foreach($this->queryParams['params'] as $field => $value){
            $sth->bindValue(':'.$field, $value);
        }
        $sth->execute();
        if($sth->errorCode() == '00000'){
            return array_map(function($tmp) { unset($tmp['Record_Creation_Date']); return $tmp; }, $sth->fetchAll($this->fetchStyle));            

        }else{
            $this->errorMessages[] = array_merge($sth->errorInfo(), array('query' => $sql));
        }
    }

    function insert(){    
        if($this->queryParams['table']){
            $table =  $this->queryParams['table'];
        }else{
            $this->errorMessages[] = 'insert(): table is not specified';
            return false;
        }

        if(!is_array($this->queryParams['set']) || !sizeof($this->queryParams['set'])){
            $this->errorMessages[] = 'insert(): fields list is empty';
            return false;
        }

        $sql = "INSERT INTO ".$table." SET ".implode(", ", $this->queryParams['set']);

        $sth = $this->pdo->prepare($sql);
        if(is_array($this->queryParams['params'])) foreach($this->queryParams['params'] as $field => $value){
            $sth->bindValue(':'.$field, $value);
        }
        $sth->execute();
        if($sth->errorCode() == '00000'){
            return $sth->fetchAll($this->fetchStyle);
        }else{
            $this->errorMessages[] = array_merge($sth->errorInfo(), array('query' => $sql));
        }
    }

    function update(){    
        if($this->queryParams['table']){
            $table =  $this->queryParams['table'];
        }else{
            $this->errorMessages[] = 'update(): table is not specified';
            return false;
        }

        if(!is_array($this->queryParams['set']) || !sizeof($this->queryParams['set'])){
            $this->errorMessages[] = 'update(): fields list is empty';
            return false;
        }

        if($this->queryParams['where']){
            $where = $this->queryParams['where'];    
        }else{
            $this->errorMessages[] = 'update(): condition WHERE is empty';
            return false;
        }

        $sql = "UPDATE ".$table." SET ".implode(", ", $this->queryParams['set'])." WHERE ".$where;

        $sth = $this->pdo->prepare($sql);
        if(is_array($this->queryParams['params'])) foreach($this->queryParams['params'] as $field => $value){
            $sth->bindValue(':'.$field, $value);
        }
        $sth->execute();
        if($sth->errorCode() == '00000'){
            return $sth->fetchAll($this->fetchStyle);
        }else{
            $this->errorMessages[] = array_merge($sth->errorInfo(), array('query' => $sql));
        }
    }

    function __destruct(){
        
        if(defined("DEBUGMODE") && DEBUGMODE){
            if(is_array($this->errorMessages) && sizeof($this->errorMessages)){
                print_r($this->errorMessages);
            }
        }
    }

    function dbInstance(){
        if(!is_object($this->db)){
            $this->db = new db(DB_TYPE, DB_HOST, DB_NAME, DB_USER, DB_PASS);
        }
        return $this->db;
    }
    function calcORDERcondition($params = array(), $defaultField = 'Record_Creation_Date', $defaultDir = 'ASC'){
        if(!is_array($params)) $params = array();
        if(isset($params['order']['field']) && trim($params['order']['field']))
            $orderField = trim($params['order']['field']);
        else
            $orderField = $defaultField;
        
        if(isset($params['order']['dir']) && in_array(trim(strtoupper($params['order']['dir'])), array('ASC', 'DESC')))
            $orderDir = trim(strtoupper($params['order']['dir']));
        else
            $orderDir = $defaultDir;

        return ($orderField&&$orderDir?'`'.$orderField.'` '.$orderDir:null);
    }

    function calcLIMITcondition($params = array(), $defaultOffset = 0, $defaultLimit = null){
        if(!is_array($params)) $params = array();  
      
        // limit
        if(isset($params['limit']['limit']) && intval($params['limit']['limit'])>0)
            $limitLimit = intval($params['limit']['limit']);
        else
            $limitLimit = $defaultLimit;

        // offset
        if(isset($params['limit']['offset']) && intval($params['limit']['offset'])>=0)
            $limitOffset = intval($params['limit']['offset']);
        elseif(isset($params['limit']['page']) && intval($params['limit']['page'])>=1)
            $limitOffset = (intval($params['limit']['page']) - 1) * $limitLimit;
        else
            $limitOffset = $defaultOffset;

        
        if($limitOffset && $limitLimit)
            $return = $limitOffset.", ".$limitLimit;
        elseif($limitOffset && !$limitLimit)
            $return = null;
        elseif(!$limitOffset && $limitLimit)
            $return = $limitLimit;
        elseif(!$limitOffset && !$limitLimit)
            $return = null;
        return $return;
    }

}
