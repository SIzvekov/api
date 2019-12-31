<?php
$this->hookClass('season');
$this->hookClass('league');

$leagueUrl = UrlParser::ID(1);

$league = new league($leagueUrl);

$seasonID = $league->getCurrentSeasonId();
$leagueConfig = $league->getleagueConfig();
$season = new season($seasonID);

if(
	!$league->leagueInfo['id'] || 
	($league->leagueInfo['url'] != $leagueUrl && $league->leagueInfo['id'] != $leagueUrl)
){
	$this->OUTPUT_CODE = 404;
	$this->OUTPUT_RESPONSE = $league->errorMessage;
	return;
}else{

	$this->OUTPUT_RESPONSE = array(
		'id' => '0411ea90-9094-11e9-8306-5e26a4c3e32c',
		'url' => $leagueID,
		'name' => 'Playas',
		'config' => $leagueConfig,
		'currentSeason' => array(
			'id' => $seasonID,
			'teams' => $season->getTeams()
		)
	);
}