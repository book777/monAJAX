<?php

if (ob_get_contents())// Спасибо @Artmoneyse
	ob_end_clean();
header('Content-Type: application/json');// Вывод для браузера в формате json
header('Access-Control-Allow-Origin: *');// Костыль для JS
ignore_user_abort(true);// Продолжаем работу после обрыва

require 'config.php';

ob_start();// Включаем буфер
readfile($config['json']);// Инклудим и выводим последний кеш данных
$temp = ob_get_length();
header("Content-Length: $temp");
ob_end_flush();
flush();
ob_end_clean();

/*	Выполнение в фоновом режиме \/	*/

if($_SERVER['REQUEST_TIME']-$config['timecache'] < filectime($config['json']))// Кэширование по времени
	exit;
if($_SERVER['REQUEST_TIME']-15 > @filectime('work.temp'))// Удаляем, если файл живет больше 15 сек.
	@unlink('work.temp');
if(file_exists('work.temp'))// Проверка однопоточности
	exit;
file_put_contents('work.temp', beautydate($_SERVER['REQUEST_TIME']));// Включаем пседвооднопоточность

$temp = new MinecraftServer();// Инициализируем мониторинг
foreach($servers as $server) {// Обрабатываем каждый сервер
	$resp['servers'][$server['name']] = $temp->getq($server['address'], $config['timeout']);// Получаем информацию
	$resp['online'] += $resp['servers'][$server['name']]['online'];// плюсуем данные онлайна к общему онлайну
	$resp['slots'] += $resp['servers'][$server['name']]['slots'];// плюсуем данные кол-ва слотов к общему онлайну
}
$resp['percent'] = @floor(($resp['online']/$resp['slots'])*100);// % общего онлайна

$comm = json_decode(file_get_contents('cache/common.json'), true);// Полуаем общие данные массивом

if($resp['online'] >= $comm['record']) {// Если сейчас онлайн больше
	$comm['timerec'] = beautydate($_SERVER['REQUEST_TIME']);// В общее красивую дату
	$comm['timerecday'] = date("H:i", $_SERVER['REQUEST_TIME']);// и временное время (-_- )
	$comm['record'] = $resp['online'];// и теперешний онлайн
	$resp['timerec'] = $comm['timerec'];// В ответ красивую дату
	$resp['record'] = $resp['online'];// и теперешний онлайн
} else {
	$resp['timerec'] = $comm['timerec'];// Из общего в ответ красивую дату
	$resp['record'] = $comm['record'];// Рекорд из общего в ответ
}


if($resp['online'] >= $comm['recordday']) {// Если онлайн больше временного
	$comm['recordday'] = $resp['online'];// В общее онлайн
	$comm['timerecday'] = date("H:i", $_SERVER['REQUEST_TIME']);// и временное время
}
$resp['recordday'] = $comm['recordday'];// в ответ
$resp['timerecday'] = $comm['timerecday'];// и временное время

if($comm['update'] != date('d', $_SERVER['REQUEST_TIME'])) {// Если наступил новый день
	$comm['update'] = date('d', $_SERVER['REQUEST_TIME']);// В общее теперешний день
	$comm['timerecday'] = date("H:i", $_SERVER['REQUEST_TIME']);
	$resp['timerec'] = beautydate($_SERVER['REQUEST_TIME']);// и в ответ
	$comm['recordday'] = $resp['online'];// В общее теперешний онлайн
	$resp['recordday'] = $resp['online'];// и в ответ
}

file_put_contents('cache/common.json', json_encode($comm));// Перезаписываем общую информацию
file_put_contents($config['json'], json_encode($resp));// Перезаписываем временный файл

@unlink('work.temp');// Отключаем псевдооднопоточность

function beautydate($data) {// Дата для рекордов
	$iz = [
		'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
		'Jul', 'Aug', 'Sep', 'Jct', 'Nov', 'Dec'
	];
	$v = [
		'января', 'феваля', 'марта', 'апреля', 'мая', 'июня',
		'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
	];
	$vblhod = str_replace($iz, $v, date("j M в H:i", $data));
	return $vblhod;
}

class MinecraftServer {// Class written by xPaw & modded by book777
	const STATISTIC = 0x00;
	const HANDSHAKE = 0x09;
	private $socket;
	function getp($address, $timeout = 3) {
		$thetime = microtime(true);
		if(!$in = @fsockopen($address, 25565, $errno, $errstr, $timeout))
			return [
				'address' => $address,
				'status' => 'Выключен'
			];
		if(round((microtime(true)-$thetime)*1000) > $timeout * 1000)
			return [
				'address' => $address,
				'status' => 'Большой пинг'
			];
		@stream_set_timeout($in, $timeout);
		$ping = round((microtime(true)-$thetime)*1000);
		fwrite($in, "\xFE\x01");
		$data = fread($in, 4096);
		$Len = strlen($data);
		if($Len < 4 || $data[0] !== "\xFF")
			return [
				'address' => $address,
				'status' => 'Неизвестное ядро'
			];
		$data = substr($data, 3);
		$data = iconv('UTF-16BE', 'UTF-8', $data);
		if($data [1] === "\xA7" && $data[2] === "\x31") {
			$data = explode("\x00", $data);
			return  [
				'address' => $address,
				'status' => 'online',
				'online' => intval($data[4]),
				'motd' => $this->motd($data[3]),
				'type' => $data[0],
				'slots' => intval($data[5]),
				'percent' => @floor((intval($data[4])/intval($data[5]))*100),
				'version' => $data[2],
				'ping' => $ping
			];
		}
		$data = explode("\xA7", $data);
		return [
			'address' => $address,
			'status' => 'online',
			'online' => isset($data[1]) ? intval($data[1]) : 0,
			'slots' => isset($data[2]) ? intval($data[2]) : 0,
			'percent' => @floor((intval($data[1])/intval($data[2]))*100),
			'version' => '< 1.4',
			'ping' => $ping
		];
	}
	function getq($address, $timeout = 3) {
		$thetime = microtime(true);
		$this->socket = @fsockopen('udp://'.$address, 25565, $ErrNo, $ErrStr, $timeout);
		if($this->socket === false)
			return [
				'status' => 'Выключен',
				'address' => $address
			];
		stream_set_timeout($this->socket, $timeout);
		stream_set_blocking($this->socket, true);
		$Challenge = $this->GetChallenge();
		$info = ['ping' => round((microtime(true)-$thetime)*1000)];
		$data = $this->writedata(self :: STATISTIC, $Challenge.Pack('c*', 0x00, 0x00, 0x00, 0x00));
		if(!$data)
			return $this->getp($address, $timeout);// Пробуем получить данные обычным способом
		fclose($this->socket);
		$Last = '';
		$data = substr($data, 11);
		$data = explode("\x00\x00\x01player_\x00\x00", $data);
		if(count($data) !== 2)
			return [
				'status' => 'Неудачная дешифрация',
				'address' => $address
			];
		$info['names'] = explode("\x00", substr($data[1], 0, -2));
		$data = explode("\x00", $data[0]);
		$Keys = [
			'numplayers' => 'online',
			'maxplayers' => 'slots',
			'hostname' => 'motd',
			'version' => 'version',
			'gametype' => 'type',
			'game_id' => 'game',
			'plugins' => 'plugins',
			'map' => 'map'
		];
		if($info['plugins']) {
			$data = explode(': ', $info['plugins'], 2);
			$info['core'] = $data[0];
			if(sizeof($data) == 2)
				$info['plugins'] = explode('; ', $data[1]);
		} else {
			$info['core'] = $info['plugins'];
			unset($info['plugins']);
		}
		foreach($data as $Key => $Value) {
			if(~$Key & 1) {
				if(!array_key_exists($Value, $Keys)) {
					$Last = false;
					continue;
				}
				$Last = $Keys[$Value];
				$info[$Last] = '';
			}
			else if($Last != false)
				$info[$Last] = $Value;
		}
		$info += [
			'status' => 'online',
			'address' => $address,
			'online' => intval($info['online']),
			'slots' => intval($info['slots']),
			'percent' => @floor(($info['online']/$info['slots'])*100)
		];
		return $info;
	}
	private function writedata($command, $Append = '') {
		$command = Pack('c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04).$Append;
		$Length = strlen($command);
		if( $Length !== fwrite($this->socket, $command, $Length))
			return [
				'status' => 'Неудачный запрос'
			];
		$data = fread($this->socket, 4096);
		if( $data === false )
			return [
				'status' => 'Не удалось прочитать ответ'
			];
		if(strlen($data) < 5 || $data[0] != $command[2])
			return false;
		return substr($data, 5);
	}
	private function motd($text) {
		$mass = explode('§', $text);
		foreach ($mass as $val)
			$out .= substr($val, 1);
		return $out;
	}
	private function GetChallenge() {
		$data = $this->writedata(self :: HANDSHAKE);
		if($data === false)
			return [
				'status' => 'failed to receive challenge'
			];
		return Pack('N', $data);
	}
}
?>
