<?php

class season extends db
{
    var $errorMessage = array();
    var $seasonID = null;
    var $seasonInfo = array();
    
    function __construct($seasonID = null, $active = 1)
    {
        $seasonID = trim($seasonID);
    	if(!$seasonID) {
            $this->errorMessage[] = 'SeasonID is not specified';
            return array();
        }

        $this->dbInstance()->queryParams['table'] = 'seasons';
        $this->dbInstance()->queryParams['where'] = "`id`=:seasonID && `active` IN (:active)";
        $this->dbInstance()->queryParams['params'] = array(
            'seasonID'=>$seasonID,
            'active'=>$active
        );
        $seasonInfo = current($this->dbInstance()->select());

        if(!$seasonInfo['id']){
            $this->errorMessage[] = 'Season with ID \''.$seasonID.'\' was not found';
            return array();
        }

        $this->seasonID = $seasonID;
        $this->seasonInfo = $seasonInfo;
        return $this->seasonInfo;
    }
    function __destruct()
    {
    }

    function getTeams($active = 1){
        $this->dbInstance()->queryParams['fields'] = array(
            'lst.team_id', 
            'lst.active',
            't.name as team_name'
        );
        $this->dbInstance()->queryParams['table'] = 'league_season_teams lst, teams t';
        $this->dbInstance()->queryParams['where'] = "
        `lst`.`league_id` = :leagueID && 
        `lst`.`season_id` = :seasonID && 
        `lst`.`active` = :active &&
        `t`.`id` = `lst`.`team_id`
        ";
        $this->dbInstance()->queryParams['params'] = array(
            'leagueID' => $this->seasonInfo['league_id'],
            'seasonID' => $this->seasonInfo['id'],
            'active' => $active,
        );
        $this->dbInstance()->queryParams['order'] = '`team_name` ASC';
        $result = $this->dbInstance()->select();

        return $result;
    }

    function getTable($active = 1){
        $this->dbInstance()->queryParams['fields'] = array(
            'lst.team_id', 
            'lst.gp',
            'lst.gw',
            'lst.gt',
            'lst.gl',
            'lst.pa',
            'lst.gs',
            'lst.gr',
            'lst.score',
            'lst.position',
            'lst.active',
            't.name as team_name'
        );
        $this->dbInstance()->queryParams['table'] = 'league_season_tableInfo lst, teams t';
        $this->dbInstance()->queryParams['where'] = "
        `lst`.`league_id` = :leagueID && 
        `lst`.`season_id` = :seasonID && 
        `lst`.`active` = :active &&
        `t`.`id` = `lst`.`team_id`
        ";
        $this->dbInstance()->queryParams['params'] = array(
        	'leagueID' => $this->seasonInfo['league_id'],
        	'seasonID' => $this->seasonInfo['id'],
        	'active' => $active,
        );
        $this->dbInstance()->queryParams['order'] = '`position` ASC';
        $result = $this->dbInstance()->select();

        foreach ($result as $key => $value) {
            $result[$key]['dif'] = $result[$key]['gs'] - $result[$key]['gr'];
        }
        
        return $result;
    }

    /*

    $params
        order
            field [sort]
            dir [asc]
        limit
            limit
            page
            offset
        scheduledAfter -- timestamp. Select only days which have games scheduled after that date
        scheduledBefore -- timestamp. Select only days which have games scheduled before that date
    */
    function getSportsDays($params = array()){
        $seasonID = trim($this->seasonInfo['id']);
        if(!$seasonID) {
            $this->errorMessage[] = 'SeasonID is not specified';
            return array();
        }
        $leagueID = trim($this->seasonInfo['league_id']);
        if(!$leagueID) {
            $this->errorMessage[] = 'leagueID is not specified';
            return array();
        }
        if(!is_array($params)) $params = array();
        // $this->dbInstance()->queryParams['fields'] = '*';
        $this->dbInstance()->queryParams['table'] = 'league_season_sportsday';


        if(isset($params['scheduledAfter']) && intval($params['scheduledAfter'])){
            $scheduledAfter = "&& (SELECT COUNT(`id`) FROM `league_season_sportsday_game` WHERE `sportsday_id` = `league_season_sportsday`.`id` && `datetime` > ".intval($params['scheduledAfter']).")";
        }
        if(isset($params['scheduledBefore']) && intval($params['scheduledBefore'])){
            $scheduledBefore = "&& (SELECT COUNT(`id`) FROM `league_season_sportsday_game` WHERE `sportsday_id` = `league_season_sportsday`.`id` && `datetime` < ".intval($params['scheduledBefore']).")";
        }
        $this->dbInstance()->queryParams['where'] = "
        `league_id` = :leagueID && 
        `season_id` = :seasonID " . 
        $scheduledAfter . 
        $scheduledBefore;

        $this->dbInstance()->queryParams['params'] = array(
            'leagueID' => $leagueID,
            'seasonID' => $seasonID
        );
        
        $this->dbInstance()->queryParams['order'] = $this->dbInstance()->calcORDERcondition($params, 'sort', 'DESC');
        
        // $offset = ($page - 1) * $limit;
        $this->dbInstance()->queryParams['limit'] = $this->dbInstance()->calcLIMITcondition($params);
        $result = $this->dbInstance()->select();

        if(isset($params['extend'])){
            if(!is_array($params['extend'])) $params['extend'] = array($params['extend']);
            if(in_array('games', $params['extend'])){
                foreach ($result as $key => $value) {
                    $result[$key]['games'] = $this->getSportsDayGames(
                        $result[$key]['id'], 
                        array(
                            'scheduledAfter'=>$params['scheduledAfter'],
                            'scheduledBefore'=>$params['scheduledBefore']
                        )
                    );
                }
            }
        }

        return $result;
    }

    function getSportsDayGames($sportsDayId = null, $params = array()){
        $sportsDayId = trim($sportsDayId);
        if(!$sportsDayId) {
            $this->errorMessage[] = 'SportsDayId is not specified';
            return array();
        }
        $this->dbInstance()->queryParams['fields'] = '*, (SELECT name FROM `teams` WHERE id=team1_id) as team1_name, (SELECT name FROM `teams` WHERE id=team2_id) as team2_name';

        $this->dbInstance()->queryParams['table'] = 'league_season_sportsday_game';
        
        $this->dbInstance()->queryParams['params'] = array(
            'sportsDayId' => $sportsDayId
        );
        
        if(isset($params['scheduledAfter']) && intval($params['scheduledAfter'])){
            $scheduledAfter = " && `datetime` > :scheduledAfter";
            $this->dbInstance()->queryParams['params']['scheduledAfter'] = intval($params['scheduledAfter']);
        }else{
            $scheduledAfter = '';
        }
        
        if(isset($params['scheduledBefore']) && intval($params['scheduledBefore'])){
            $scheduledBefore = " && `datetime` < :scheduledBefore";
            $this->dbInstance()->queryParams['params']['scheduledBefore'] = intval($params['scheduledBefore']);
        }else{
            $scheduledBefore = '';
        }
        $this->dbInstance()->queryParams['where'] = "`sportsday_id` = :sportsDayId".$scheduledAfter.$scheduledBefore;
        $this->dbInstance()->queryParams['order'] = $this->dbInstance()->calcORDERcondition($params, 'datetime', 'DESC');
        $this->dbInstance()->queryParams['limit'] = $this->dbInstance()->calcLIMITcondition($params);
        $result = $this->dbInstance()->select();

        // game details
        $this->dbInstance()->queryParams['table'] = 'league_season_sportsday_game_details';
        $this->dbInstance()->queryParams['fields'] = "*, (SELECT CONCAT(`first_name`,' ',`last_name`) FROM `players` WHERE `id` = `player_id`) as `player_name`, (SELECT CONCAT(`first_name`,' ',`last_name`) FROM `players` WHERE `id` = `player2_id`) as `player2_name`";
        $this->dbInstance()->queryParams['where'] = "`game_id` IN (SELECT `id` FROM `league_season_sportsday_game` WHERE `sportsday_id` = :sportsDayId)";
        $this->dbInstance()->queryParams['params'] = array(
            'sportsDayId' => $sportsDayId
        );
        $this->dbInstance()->queryParams['order'] = $this->dbInstance()->calcORDERcondition(array(),'sort','ASC');
        $this->dbInstance()->queryParams['limit'] = $this->dbInstance()->calcLIMITcondition();
        $gamesDetailsRaw = $this->dbInstance()->select();
        $gamesDetails = array();
        foreach($gamesDetailsRaw as $details){
            $newDetail = $details;
            unset($newDetail['id']);
            unset($newDetail['game_id']);
            unset($newDetail['team_id']);

            $newDetail['type_mst'] = DataParser::prepareMSTarray(
                $newDetail['type'], 
                array(), 
                'league_season_sportsday_game_details', 
                'type'
            );
            $newDetail['subtype_mst'] = DataParser::prepareMSTarray(
                $newDetail['subtype'], 
                array(), 
                'league_season_sportsday_game_details', 
                'subtype'
            );
            //array('player_id', 'player2_id', 'time', 'type', 'subtype', 'note');
            $gamesDetails[$details['game_id']][$details['team_id']][] = $newDetail;
        }

        foreach($result as $key => $game){
            $result[$key]['status_mst'] = DataParser::prepareMSTarray($game['status'], array(), 'league_season_sportsday_game', 'status');
            $result[$key]['team1_details'] = $gamesDetails[$game['id']][$game['team1_id']] ? $gamesDetails[$game['id']][$game['team1_id']] : array();
            $result[$key]['team2_details'] = $gamesDetails[$game['id']][$game['team2_id']] ? $gamesDetails[$game['id']][$game['team2_id']] : array();
            
        }
        return $result;
    }
}
