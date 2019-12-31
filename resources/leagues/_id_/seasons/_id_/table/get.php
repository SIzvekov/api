<?php
$this->hookClass('season');
// $sessionInfo = $userSession->get();
$leagueID = UrlParser::ID(1);
$seasonID = UrlParser::ID(3);

$season = new season($seasonID);

if(!$season->seasonInfo['id'] || $season->seasonInfo['league_id'] != $leagueID){
	$this->OUTPUT_CODE = 404;
	$this->OUTPUT_RESPONSE = $season->errorMessage;
	return;
}else{

	$this->OUTPUT_RESPONSE = $season->getTable();
}