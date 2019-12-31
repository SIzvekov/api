<?php

class user extends db
{
    var $errorMessage = array();
    var $TOKEN = null;

    function __construct($token = null)
    {
    	$this->TOKEN = $token;
    }
    function __destruct()
    {
    }

	function getUserBySessionID($token = ''){
        if(!$token) return null;

        $this->dbInstance()->queryParams['fields'] = 'user.id, user.username';
        $this->dbInstance()->queryParams['table'] = 'api_sessions session, api_users user';
        $this->dbInstance()->queryParams['where'] = "`session`.`api_token`=:token && `session`.`api_users_id` = `user`.`id`";
        $this->dbInstance()->queryParams['params'] = array(
            'token'=>$token,
        );
        $result = current($this->dbInstance()->select());
        return $result;
    }    

    
}
