<?php
$this->hookClass('userSession');
$userSession = new userSession();
$authResult = $userSession->create(
	array(
		'username' => $this->DATA['username'],
		'password' => $this->DATA['password'],
		'authType' => 'username',
	)
);

if(!$authResult){
	$this->OUTPUT_CODE = 401;
	$this->OUTPUT_RESPONSE = $userSession->errorMessage;
}else{
	$this->hookClass('user');
	$user = new user();
	$authResult['userInfo'] = $user->getUserBySessionID($authResult['token']);

	$this->OUTPUT_RESPONSE = $authResult;
}