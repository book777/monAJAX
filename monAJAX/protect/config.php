<?php require_once 'additional.php';// Дополнительный код (не трогать)

/*
 * monAJAX
 *
 * Version 1.6
 * Autors: @CyberOwl, @MattRh, @book777, @Artmoneyse
 */


/* Добавить сервер можно копированием строки */
$servers[] = array('address' => 'MLegacy.ru', 'name' => 'test1');
$servers[] = array('address' => 'mc.funserv.ru', 'name' => 'test2');
$servers[] = array('address' => 'FPLAY.SU:25565', 'name' => 'test3');
$servers[] = array('address' => 'MLegacy.ru', 'name' => 'test4');
$servers[] = array('address' => '127.0.0.1:25565', 'name' => 'test5');


$config = array
(
    /* Мод работы мониторинга от 0 (самого быстрого) до 3 (медленного)
        0 - запросы идут на готовый ajax.json (готовые данные серверов).
            Этот файл будет обновляться только после подклю чения к CRON файла ajaxBG.php
            https://google.com/search?q=хостинг+подключить+cron
        1-2 Особо не отличаются. Запросы идут через "костыльный" запрос с ограничением на ответ.
            Работает довольно быстро, но не на всех хостингах. CRON не требуется
        3 - обращение идет напрямую к ajax.php. CRON не требуется
    */
    'cache_mode' => 1,


    /*	Название файла, куда ПОСЛЕ ЗАПУСКА build.php будeт сохранен готовый скрипт для вставки в заголовок сайта (<head>).
        Есть два способа вставки:
            Прямой
                Пример переменной для DLE
                    '../template/Default/monitoring.tpl'
                    Подключим {include file="monitoring.tpl"} между <head> и </head>
                ВНИМАНИЕ! В файле присутствует jquery.js. Если он уже есть на сайте, то его нужно удалить в 10-х строчках build.php.
                После подключения вставляем в нужное место на сайте:
                    <div id=monAJAX></div>

            Через iframe
                Вставляем в нужное место на сайте
                    <iframe src="https://site.ru/monAJAX/index.php" width="600" height="200" frameborder="no" scrolling="no">Здесь должен был быть мониторинг ;(</iframe>
                    Где cacheHeader.html переменая ниже
                Плюсы и минусы iframe
                    - Нужно повторно всё копировать после регенерации build.php
                    - Проблемы с SSL
                    - Постоянная подгонка width и height
                    + Нет возможных конфликтов со стилями и JS
    */
    'template' => './cacheHeader.html',


    /* Маловажные переменные */
    'webTimeout' => 0.1, /* Время в сек., через которое для методов 1-2 запрос на обновление данных будет прерван
		Увеличение может помочь, если методы 1-2 не работают */
    'sErr' => 'Ошибка..', // Ответит браузер, если не сможет считать кэш (обновится после запуска build.php)
    'smoothBar' => true, // Плавное появление полосы
    'timecache' => 20, // Промежуток времени в сек., через который мониторинг будет обновляться
    'timeout' => 2, // Максимально время на ожидание ответа от сервера майнкрафт
    'remoteDir' => wayToScript(), // Путь к скрипту вида http(s)://{site.ru}/../monAJAX/ (лучше не менять)
    'ajaxBG' => 'ajaxBG.php', // Куда обращаться скрипту в случае cache_mode 1-3
    'debug' => false // Отладка в случае проблем https://github.com/book777/monAJAX/issues
);

// Укажите свою местность, чтобы в рекорд записывалось правильное время
// http://php.net/manual/ru/timezones.php
date_default_timezone_set('Europe/Moscow');
