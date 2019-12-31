<?php

class team extends db
{
    var $errorMessage = array();
    var $teamID = null;
    var $teamInfo = array();
    
    function __construct($teamID = null, $active = 1)
    {
        $teamID = trim($teamID);
    	if(!$teamID) {
            $this->errorMessage[] = 'teamID is not specified';
            return array();
        }

        $this->dbInstance()->queryParams['table'] = 'teams';
        $this->dbInstance()->queryParams['where'] = "`id`=:teamID && `active` IN (:active)";
        $this->dbInstance()->queryParams['params'] = array(
            'teamID'=>$teamID,
            'active'=>$active
        );
        $teamInfo = current($this->dbInstance()->select());

        if(!$teamInfo['id']){
            $this->errorMessage[] = 'team with ID \''.$teamID.'\' was not found';
            return array();
        }

        $this->teamID = $teamID;
        $this->teamInfo = $teamInfo;
        return $this->teamInfo;
    }

    function getTeamPlayers(){
        $teamID = $this->teamID;

        if(!$teamID){
            $this->errorMessage[] = 'Team ID is not set';
            return null;
        }

        $this->dbInstance()->queryParams['table'] = 'players, team_players';
        $this->dbInstance()->queryParams['where'] = "`team_players`.`team_id`=:teamID && `team_players`.`player_id` = `players`.`id` && `team_players`.`active` = '1' && `players`.`active` = '1'";
        $this->dbInstance()->queryParams['params'] = array(
            'teamID'=>$teamID
        );
        $this->dbInstance()->queryParams['order'] = '`team_players`.`number` ASC';
        $players = $this->dbInstance()->select();
        
        return $players;
    }

    function __destruct()
    {
    }
}
