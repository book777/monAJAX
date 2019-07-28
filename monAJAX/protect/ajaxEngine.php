<?php require_once __DIR__ . '/config.php';

errorRep($config['debug']);


// Пседвооднопоточность
if ($_SERVER['REQUEST_TIME'] - 15 > @filectime('work.temp'))// Удаляем, если файл живет больше 15 сек.
    @unlink(__DIR__ . '/work.temp');
if (file_exists(__DIR__ . '/work.temp'))// Проверка однопоточности
    exit;

$microStart = microtime();
file_put_contents(__DIR__ . '/work.temp', $microStart);// При частых запросах к скрипту может пропасть статистка. Это решение сильно сокращает возможность


// Начало работы с серверами Minecraft
require_once __DIR__ . '/MinecraftServer.class.php';

$temp = new MinecraftServer();

$resp['online'] = 0;
$resp['slots'] = 0;

$idT = 0;
foreach ($servers as $server) {// Обрабатываем каждый сервер
    $nameT = $idT++;
    $resp['servers'][$nameT] = $temp->getq($server['address'], $config['timeout']);// Получаем информацию
    $resp['online'] += (array_key_exists('online', $resp['servers'][$nameT]) ? $resp['servers'][$nameT]['online'] : 0);// Добавляем данные онлайна этого сервера к общему
    $resp['slots'] += (array_key_exists('slots', $resp['servers'][$nameT]) ? $resp['servers'][$nameT]['slots'] : 0);// Добавляем данные онлайна этого сервера к общему
}
unset($idT);

$resp['percent'] = @floor(($resp['online'] / $resp['slots']) * 100);// % общего онлайна

$comm = json_decode(file_get_contents(__DIR__ . '/common.json'), true);// Полуаем прошлые общие данные

if ($resp['online'] >= $comm['record']) {// Если сейчас онлайн больше абсолютного
    $comm['timerec'] = beautydate($_SERVER['REQUEST_TIME']);
    $comm['timerecday'] = date("H:i", $_SERVER['REQUEST_TIME']);
    $comm['record'] = $resp['online'];
    $resp['timerec'] = $comm['timerec'];
    $resp['record'] = $resp['online'];
} else {
    $resp['timerec'] = $comm['timerec'];
    $resp['record'] = $comm['record'];
}


if ($resp['online'] >= $comm['recordday']) {// Если онлайн больше временного
    $comm['recordday'] = $resp['online'];
    $comm['timerecday'] = date("H:i", $_SERVER['REQUEST_TIME']);
}
$resp['recordday'] = $comm['recordday'];
$resp['timerecday'] = $comm['timerecday'];

if ($comm['update'] != date('d', $_SERVER['REQUEST_TIME'])) {// Если наступил новый день
    $comm['update'] = date('d', $_SERVER['REQUEST_TIME']);// В общее теперешний день
    $comm['timerecday'] = date("H:i", $_SERVER['REQUEST_TIME']);
    $resp['timerec'] = beautydate($_SERVER['REQUEST_TIME']);// и в ответ
    $comm['recordday'] = $resp['online'];// В общее теперешний онлайн
    $resp['recordday'] = $resp['online'];// и в ответ
}
$asd = json_encode($resp);

if ($microStart != file_get_contents(__DIR__ . '/work.temp'))
    die('Bad thread');

file_put_contents(__DIR__ . '/common.json', json_encode($comm));
file_put_contents(__DIR__ . '/../ajax.json', json_encode($resp, true));

if ($config['debug'])
    print_r($resp);


@unlink(__DIR__ . '/work.temp');// Отключаем псевдооднопоточность
