<?php

require_once 'protect/config.php';

if ($config['debug'])
    echo '<pre>Turn off debug mode (' . time() . ")\n\n";

else {
    errorRep(false);
    // Вывод последеней информации о серверах
    ob_end_clean();
    header('Content-Type: application/json');// Вывод для браузера в формате json
    header('Access-Control-Allow-Origin: *');// Костыль для JS
    ignore_user_abort(true);// Продолжаем работу после обрыва
    ob_start();// Включаем буфер

    if (!readfile('ajax.json'))// Выводим последний кэш данных
        echo json_encode(array('cache' => 'none'));

    $temp = ob_get_length();
    header("Content-Length: $temp");
    ob_end_flush();
    flush();
    if (ob_get_contents())
        ob_end_clean();

    // Ниже выполнение кода в фоновом режиме
    if ($_SERVER['REQUEST_TIME'] - $config['timecache'] < filectime('ajax.json'))// Кэширование
        exit;
}


$seed = secSeed('protect/seed.txt');

stream_context_set_default(
    array(
        'http' => array(
            'method' => 'HEAD',
            'timeout' => $config['webTimeout'],
            'header' => 'User-Agent:' . $seed,
        ),
        'https' => array(
            'method' => 'HEAD',
            'timeout' => $config['webTimeout'],
            'header' => 'User-Agent:' . $seed,
        )
    )
);

switch ($config['cache_mode']) {
    case 1:
        if ($seed)
            @file_get_contents($config['remoteDir'] . $config['ajaxBG'], false);
        break;
    case 2:
        if ($seed)
            @get_headers($config['remoteDir'] . $config['ajaxBG']);
        break;
    default:
        require_once 'protect/ajaxEngine.php';
}
