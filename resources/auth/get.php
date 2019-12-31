<?php
$this->hookClass('userSession');
$userSession = new userSession($this->TOKEN);
$sessionInfo = $userSession->get();

if(!$sessionInfo){
	$this->OUTPUT_CODE = 498;
}else{
	$this->OUTPUT_RESPONSE = $sessionInfo;
}