<?php
// $this->hookClass('mui');

$sectionType = UrlParser::ID(1);
$languageCode = UrlParser::ID(2);
// $team = new mui($languageCode);

if(0){
	$this->OUTPUT_CODE = 404;
	$this->OUTPUT_RESPONSE = array();
	return;
}else{

	$this->OUTPUT_RESPONSE = array(
		'join_us_on_facebook' => 'Страница FaceBook',
		'table_of_results' => 'Таблица результатов',
		'games' => 'Игры',
		'navigation' => array(
			'home' => 'Главная',
			'teams' => 'Команды',
			'games' => 'Игры',
			'contact_us' => 'Контакты'
		),
		'error404message' => 'Страница не найдена',
		'teams' => array(
			'position' => 'Позиция',
			'noPlayers' => 'Нет игроков',
			'captain' => 'Капитан'
		),
		'footer_copy' => 'Разработано',
		'table_of_results_labels' => array(
			'team' => 'Команда',
			'gp' => 'Игр',
			'gw' => 'ИВ',
			'gt' => 'ИН',
			'gl' => 'ИП',
			'pa' => 'ПО',
			'gs' => 'ГЗ',
			'gr' => 'ГП',
			'dif' => 'Разница',
			'score' => 'Очков'
		),
		'contact_us' => 'Контакты'
	);
}