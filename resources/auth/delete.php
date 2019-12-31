<?php
$this->hookClass('userSession');
$userSession = new userSession($this->TOKEN);
$deleteResult = $userSession->delete();

if(!$deleteResult){
	$this->OUTPUT_CODE = 498;
}else{
	$this->OUTPUT_RESPONSE = $deleteResult;
}