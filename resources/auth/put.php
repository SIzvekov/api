<?php
$this->hookClass('userSession');
$userSession = new userSession($this->TOKEN);
$extendResult = $userSession->extend();

if(!$extendResult){
	$this->OUTPUT_CODE = 498;
}else{
	$this->OUTPUT_RESPONSE = $extendResult;
}