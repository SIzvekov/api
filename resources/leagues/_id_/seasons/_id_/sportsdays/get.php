<?php
$this->hookClass('season');
// $sessionInfo = $userSession->get();
$leagueID = UrlParser::ID(1);
$seasonID = UrlParser::ID(3);

if(isset(DataParser::getArray()['scheduledAfter'])){
	if(DataParser::getArray()['scheduledAfter'] == 'now') $scheduledAfter = time();
	else $scheduledAfter = intval(DataParser::getArray()['scheduledAfter']);
}

if(isset(DataParser::getArray()['scheduledBefore'])){
	if(DataParser::getArray()['scheduledBefore'] == 'now') $scheduledBefore = time();
	else $scheduledBefore = intval(DataParser::getArray()['scheduledBefore']);
}

// die(time()."<br>1563167999");

if(!isset($scheduledAfter) && !isset($scheduledBefore))
	$scheduledAfter = time();

$season = new season($seasonID);

if(!$season->seasonInfo['id'] || $season->seasonInfo['league_id'] != $leagueID){
	$this->OUTPUT_CODE = 404;
	$this->OUTPUT_RESPONSE = $season->errorMessage;
	return;
}else{

	$this->OUTPUT_RESPONSE = $season->getSportsDays(array(
		'order' => array(
			'field' => 'sort',
			'dir' => 'DESC'
		),
		'limit' => array(
			// 'page' => 1,
			// 'limit' => 3
		),
		'scheduledAfter' => $scheduledAfter,
		'scheduledBefore' => $scheduledBefore,
		'extend' => array('games')
	));
}