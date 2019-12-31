<?php

class league extends db
{
    var $errorMessage = array();
    var $leagueInfo = array();
    
    function __construct($leagueID = null, $active = 1)
    {
        $leagueID = trim($leagueID);
    	if(!$leagueID) {
            $this->errorMessage[] = 'LeagueID is not specified';
            return array();
        }

        
        $this->dbInstance()->queryParams['table'] = 'leagues';
        
        if(DataParser::isValidGUID($leagueID)) {
            $this->dbInstance()->queryParams['where'] = "`id`=:LeagueID && `active` IN (:active)";
            $this->dbInstance()->queryParams['params'] = array(
                'LeagueID'=>$leagueID,
                'active'=>$active
            );
        }
        else {
            $this->dbInstance()->queryParams['where'] = "`url`=:LeagueID && `active` IN (:active)";
            $this->dbInstance()->queryParams['params'] = array(
                'LeagueID'=>$leagueID,
                'active'=>$active
            );
        }
        
        $leagueInfo = current($this->dbInstance()->select());

        if(!$leagueInfo['id']){
            $this->errorMessage[] = 'League with ID \''.$leagueID.'\' was not found';
            return array();
        }

        $this->leagueInfo = $leagueInfo;
        return $this->leagueInfo;
    }

    function getCurrentSeasonId(){
        $leagueID = $this->leagueInfo['id'];
        if(!$leagueID){
            $this->errorMessage[] = 'League ID is not set';
            return null;
        }
        $this->dbInstance()->queryParams['table'] = 'seasons';
        $this->dbInstance()->queryParams['where'] = "`league_id`=:LeagueID && `current` = '1'";
        $this->dbInstance()->queryParams['params'] = array(
            'LeagueID'=>$leagueID
        );
        $currentSeason = current($this->dbInstance()->select());
        if(!$currentSeason['id']){
            $this->errorMessage[] = 'League with ID \''.$leagueID.'\' does not have current season';
            return null;
        }
        return $currentSeason['id'];
    }

    function getleagueConfig(){
        $leagueID = $this->leagueInfo['id'];
        if(!$leagueID){
            $this->errorMessage[] = 'League ID is not set';
            return null;
        }
        $this->dbInstance()->queryParams['table'] = 'league_config';
        $this->dbInstance()->queryParams['fields'] = array('param_key', 'param_value');
        $this->dbInstance()->queryParams['where'] = "`league_id`=:LeagueID";
        $this->dbInstance()->queryParams['params'] = array(
            'LeagueID'=>$leagueID
        );
        $leagueConfigRaw = $this->dbInstance()->select();
        $leagueConfig = array();
        foreach ($leagueConfigRaw as $item) {
            $leagueConfig[trim($item['param_key'])] = trim($item['param_value']);
        }
        
        return $leagueConfig;
    }

    function getAllLeagues($active = null){
        $this->dbInstance()->queryParams['table'] = 'leagues';
        $this->dbInstance()->queryParams['fields'] = '*';
        
        if(!is_null($active)){
            $active = boolval($active);
            $this->dbInstance()->queryParams['where'] = "`active`=:active";
            $this->dbInstance()->queryParams['params'] = array(
                'active'=>($active?'1':'0')
            );
        }
        $leagues = $this->dbInstance()->select();
        return $leagues;
    }

    function __destruct()
    {
    }
}
