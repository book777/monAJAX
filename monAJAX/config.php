<?php

/*			НЕЛЬЗЯ ДАВАТЬ СЕРВЕРАМ ОДИНАКОВЫЕ НАЗВАНИЯ!		(стандартный порт 25565 можно не прописывать)	*/
$servers[]  = array('address' => '37.59.30.23:22222', 'name' => 'тесt');
$servers[]  = array('address' => '148.251.80.9:1337', 'name' => 'test1');
$servers[]  = array('address' => '148.251.125.2', 'name' => 'test2');
$servers[]  = array('address' => '148.251.80.9:1356', 'name' => 'test3');
$servers[]  = array('address' => 'zxczxcz.xxx:22222', 'name' => 'test4');
$servers[]  = array('address' => '37.59.30.23:22222', 'name' => 'HiTech');
$servers[]  = array('address' => 'play.gmine.ru', 'name' => 'TechnoMagic');
$servers[]  = array('address' => 'MC.SKYMINE.SU:20850', 'name' => 'MagicRPG');
$servers[]  = array('address' => '87.98.146.38', 'name' => 'KvidoomRPG');


$config = array(

	'template' => '../templates/Default/monitoring.tpl',// Путь к папке с шаблоном сайта относительно папки скрипта. Нужен для cron-версии

	'tphp' => true,// true - обращение идет к обновляемому json'у. false - к его кэшированной части (потребуется подключить к крон ajax.php)
	'timecache' => 20,// Промежуток времени (в секундах) через который мониторинг будет обновляться
	'json' => 'cache/cache.json',// Файл кэширования информации серверов	
	'timeout' => 3,// Время ожидания ответа сервера майнкрафт
	'smoothBar' => true,// Плавное появление полосы
	'sErr' => 'Ошибка..',// Отвечает браузер, если не может считать кэш
	'dir' => 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/'// Путь к скрипту (лучше не менять)
);
#	date_default_timezone_set('Europe/Moscow');// http://php.net/manual/ru/timezones.php
?>