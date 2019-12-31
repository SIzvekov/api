<?php
$this->hookClass('league');
$leagues = new league();


$this->OUTPUT_RESPONSE = array(
	'leagues' => $leagues->getAllLeagues()
);