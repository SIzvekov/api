<?php

class userSession extends db
{
    var $errorMessage = array();
    var $TOKEN = null;
    var $TTL = 0;

    function __construct($token = null)
    {
    	$this->TOKEN = $token;
    	$this->TTL = intval(SESSION_TTL);
    }
    function __destruct()
    {
    }

	function create($authParams = array()){
		if(!$authParams['authType'] || $authParams['authType'] == 'username'){
            $username = $authParams['username'];
            $password = $authParams['password'];
            
            $this->dbInstance()->queryParams['table'] = 'api_users';
            $this->dbInstance()->queryParams['where'] = "`username`=:username";
            $this->dbInstance()->queryParams['params'] = array('username'=>$username);

            $result = current($this->dbInstance()->select());
			if(!$result['password']){
				$this->errorMessage[] = 'User not found';
				return null;
			}elseif(!$this->verifyPassword($password, $result['password'])){
				$this->errorMessage[] = 'Password is incorrect';
				return null;
			}else{
				// close all current sessions
				$this->dbInstance()->queryParams['table'] = 'api_sessions';
            	$this->dbInstance()->queryParams['set'] = array(
            		"`date_expire`=:now"
            	);
            	$this->dbInstance()->queryParams['where'] = "`api_users_id`=:userId && `date_expire`>=:now";

            	$this->dbInstance()->queryParams['params'] = array(
            		'userId'=>$result['id'],
					'now' => date('Y-m-d H:i:s', time())
            	);
            	$this->dbInstance()->update();


				// create new session
				$newToken = $this->generateNewSessionToken();
				$sessionExpires = date('Y-m-d H:i:s', time()+$this->TTL);
				$this->dbInstance()->queryParams['table'] = 'api_sessions';
				$this->dbInstance()->queryParams['set'] = array(
					"`id`=UUID()",
					"`api_users_id`=:userId",
					"`api_token`=:newToken",
					"`date_start`=:now",
					"`date_last_use`=:now",
					"`date_expire`=:dateExpire",
					"`user_ip`=:userIp"
				);

				$this->dbInstance()->queryParams['params'] = array(
					'userId'=>$result['id'],
					'newToken' => $newToken,
					'now' => date('Y-m-d H:i:s', time()),
					'dateExpire' => $sessionExpires,
					'userIp' => $this->userIP()
				);

				$this->dbInstance()->insert();

				return array(
					'token' => $newToken,
					'expires' => $sessionExpires,
					'sessionTTE' => $this->TTL,
				);
			}
        }

        if(!$this->TOKEN) return false;
    }

    function extend(){
    	if(!$this->TOKEN) return false;

    	$sessionStatus = $this->get();

    	if($sessionStatus['token']){
    		$sessionExpires = date('Y-m-d H:i:s', time()+$this->TTL);


    		$this->dbInstance()->queryParams['table'] = 'api_sessions';
			$this->dbInstance()->queryParams['set'] = array(
				"`date_expire`=:dateExpire"
			);
			$this->dbInstance()->queryParams['where'] = "`api_token`=:token && `user_ip` = :userIp";

			$this->dbInstance()->queryParams['params'] = array(
				'token' => $sessionStatus['token'],
				'dateExpire' => $sessionExpires,
				'userIp' => $this->userIP()
			);

			$this->dbInstance()->update();

    		return array(
				'token' => $sessionStatus['token'],
				'expires' => $sessionExpires,
				'sessionTTE' => $this->TTL,
			);
    	}else{
    		return null;
    	}
    }

    function delete(){
        if(!$this->TOKEN) return false;

        $this->dbInstance()->queryParams['table'] = 'api_sessions';
		$this->dbInstance()->queryParams['set'] = array(
			"`date_expire`=:now"
		);
		$this->dbInstance()->queryParams['where'] = "`api_token`=:token && `user_ip` = :userIp && `date_expire`>:now";

		$this->dbInstance()->queryParams['params'] = array(
			'token' => $this->TOKEN,
			'now' => date('Y-m-d H:i:s', time()),
			'userIp' => $this->userIP()
		);

		$this->dbInstance()->update();

		return true;
    }

    function get(){
        if(!$this->TOKEN) return false;

        $this->dbInstance()->queryParams['fields'] = 'api_token, date_expire';
        $this->dbInstance()->queryParams['table'] = 'api_sessions';
        $this->dbInstance()->queryParams['where'] = "`api_token`=:token && `user_ip` = :userIp && `date_expire`>:now";
        $this->dbInstance()->queryParams['params'] = array(
        	'token'=>$this->TOKEN,
        	'userIp'=>$this->userIP(),
        	'now'=>date('Y-m-d H:i:s', time()),
        );
        $result = current($this->dbInstance()->select());
        if($result['api_token'] == $this->TOKEN){

        	$result['token'] = $result['api_token'];
        	unset($result['api_token']);

        	$result['expires'] = $result['date_expire'];
        	unset($result['date_expire']);

        	$result['sessionTTE'] = $this->TTL;
        	
        	return $result;	
        }else{
        	return null;
        }
    }    

    function verifyPassword($stringPwd = '', $hashedPwd = '')
    {
    	if(!$hashedPwd) return false;
        return password_verify($stringPwd, $hashedPwd);
    }

    function userIP()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ipaddress = getenv('HTTP_CLIENT_IP');
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            } else {
                if (getenv('HTTP_X_FORWARDED')) {
                    $ipaddress = getenv('HTTP_X_FORWARDED');
                } else {
                    if (getenv('HTTP_FORWARDED_FOR')) {
                        $ipaddress = getenv('HTTP_FORWARDED_FOR');
                    } else {
                        if (getenv('HTTP_FORWARDED')) {
                            $ipaddress = getenv('HTTP_FORWARDED');
                        } else {
                            if (getenv('REMOTE_ADDR')) {
                                $ipaddress = getenv('REMOTE_ADDR');
                            } else {
                                $ipaddress = 'UNKNOWN';
                            }
                        }
                    }
                }
            }
        }

        $ipaddress = explode(",", $ipaddress);
        return trim($ipaddress[0]);
    }

    function generateNewSessionToken(){
    	return md5(time().$this->userIP());
    }
}
