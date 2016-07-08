<?php
/*
 * Version 1.5.1
 * Autors: @CyberOwl, @MattRh, @book777, @Artmoneyse
 */

/*	Нельзя давать серверам одинаковые имена (name)!	*/
$servers[]  = array('address' => 'cooll.pp.ua', 'name' => 'тесt');
$servers[]  = array('address' => '91.121.40.6:25887', 'name' => 'test1');
//$servers[]  = array('address' => '178.33.4.204:25739', 'name' => 'test2');
//$servers[]  = array('address' => 'zxczxcz.xxx:22222', 'name' => 'test4');


$config = array
(
	'template' => './template/Default/monitoring.tpl',// Путь к папке с шаблоном сайта относительно папки скрипта. Нужен для cron
	'json_mode' => false, // true - обращение идет напрямую к php. false - к его кэшированной части (потребуется подключить к cron ajax.php)

	'timecache' => 20, // Промежуток времени (в секундах) через который мониторинг будет обновляться
	'timeout' => 2, // Максимально время на ожидание ответа сервера майнкрафт
	'smoothBar' => true, // Плавное появление полосы
	'sErr' => 'Ошибка..', // Ответит браузер, если не сможет считать кэш
	'dir' => 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/', // Путь к скрипту (лучше не менять)
	'debug' => false // Отладка в случае проблем https://github.com/book777/monAJAX/issues
);

// Укажите свою местность, чтобы в рекорд записывалось правильное время
// http://php.net/manual/ru/timezones.php
date_default_timezone_set('Europe/Moscow');
