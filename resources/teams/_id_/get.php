<?php
$this->hookClass('team');

$teamID = UrlParser::ID(1);
$team = new team($teamID);

$teamInfo = $team->teamInfo;
$teamInfo['players'] = $team->getTeamPlayers();

if(!$team->teamInfo['id']){
	$this->OUTPUT_CODE = 404;
	$this->OUTPUT_RESPONSE = $team->errorMessage;
	return;
}else{

	$this->OUTPUT_RESPONSE = $teamInfo;
}