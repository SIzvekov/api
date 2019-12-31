<?php
$this->hookClass('userSession');
$userSession = new userSession($this->TOKEN);
$sessionInfo = $userSession->get();

if(!$sessionInfo['token']){
	$this->OUTPUT_CODE = 498;
	return;
}

$this->hookClass('user');
$user = new user();
$userInfo = $user->getUserBySessionID($this->TOKEN);

if(!isset($userInfo['id']) || !$userInfo['id']){
	$this->OUTPUT_CODE = 404;
}else{
	$this->OUTPUT_RESPONSE = $userInfo;
}