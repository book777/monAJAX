<?php

require_once 'protect/config.php';

errorRep($config['debug']);

ob_start();// Включаем буфер
?>

<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="./template/monStyle.css">
<script src='http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js' type='text/javascript'></script>

<script type='text/javascript'>
    var monAJAX = {
        servers: [<?php foreach ($servers as $server) echo '{name:"' . $server['name'] . '"},'?>],
        createMon: function () { // Создание основы мониторинга
            var newHTML = '', name, srvOnlStr, onBar, version, pingTime, fullOnStr, fullOnBar, todRec, onlRec,
                timeTodRec, timeRec;
            srvOnlStr = '<span class=JSSrvString>.</span>';
            fullOnStr = '<span id=JSComString>.</span>';
            todRec = '<span id=DayRec>.</span>';
            onlRec = '<span id=AbsRec>.</span>';
            timeTodRec = '<span id=TodRTime>.</span>';
            timeRec = '<span id=RecDate>.</span>';
            version = '<span class=SrvVers>.</span>';
            pingTime = '<span class=SrvPing>.</span>';
            onBar = '<div class="OnlineBar loading" style="width:100%"></div>';
            fullOnBar = '<div class=OnlineBar id=CommonBar style="width:100%"></div>';
            for (var i = 0, len = this.servers.length; i < len; ++i)// Шаблон каждого сервера
            {
                name = this.servers[i]['name'];
                newHTML += "<?php echo tplRead('server.tpl')?>"
            }
            ;
            newHTML += "<?php echo tplRead('common.tpl')?>";// Шаблон общего онлайна
            $('#monAJAX').html(newHTML);// Записываем получившуюся основу
            delete newHTML
        },
        updateMon: function () {
            var len = this.servers.length,

                smoothTime =<?php echo ($config['smoothBar']) ? 1200 : 0?>,
                ellipsis = '<span class=Ellipsis>.</span>',
                maxErrLen = 14,

                // Переменные одного сервера
                status,// Статус работы сервера(выключен/включен и т.д)
                srvOnlStr = [],// Текст состояния сервера
                statusClass = [],// Статус бара заполненности сервера
                onBar = [],// Ширина бара заполненности сервера
                version = [],// Версия сервера
                pingTime = [],// Пинг сервера

                // Переменные общего онлайна
                fullOnStr,// Текст состояния всех серверов
                fullOnBar,// Ширина бара заполненности всех серверов
                todRec,// Рекорд дня
                onlRec,// Абсорютный рекорд
                timeTodRec,// Время установления рекорда этого дня
                timeRec;// Время установления абсолютного рекорда

            function servOff(title) {// Не получилось соединиться с веб-сервером
                for (i = 0; i < len; i++) {
                    statusClass[i] = 'upderr';
                    srvOnlStr[i] = '<span class=monTrouble>' + title + '</span>';
                    pingTime[i] = version[i] = '';
                    onBar[i] = 100
                }
                ;

                fullOnStr = '<span class=monTrouble>' + title + '</span>';
                fullOnBar = 100;
                todRec = onlRec = timeRec = timeTodRec = title
            };

            $.ajax({
                url: '<?php echo $config['remoteDir'] . (substr($config['remoteDir'], -1) == '/' ? '' : '/') . ($config['cache_mode'] ? 'ajax.php' : 'data/ajax.json')?>',
                beforeSend: function () {// Перед попыткой получения данных
                    $('#monAJAX .OnlineBar').removeClass('online offline upderr');
                    $('#monAJAX .OneServer').each(function () {
                        $(this).find('.JSSrvString, .SrvVers, .SrvPing').html(ellipsis);
                        $(this).find('.OnlineBar').addClass('loading').animate({'width': '100%'}, {
                            queue: false,
                            duration: smoothTime
                        })
                    });
                    $('#JSComString, #DayRec, #AbsRec, #TodRTime, #RecDate').html(ellipsis);
                    $('#CommonBar').animate({'width': '100%'}, {queue: false, duration: smoothTime});
                },
                success: function (data) {// Если получили данные
                    if (data['cache'] == 'none') {
                        servOff('Первый запуск');
                        return;
                    }
                    if (typeof data['servers'] == 'undefined' && !(data['servers'] instanceof Array)) {
                        servOff('Ошибка данных');
                        return;
                    }
                    for (var i = 0; i < len; i++) {
                        status = data['servers'][i]['status'];
                        if (status != 'online') {
                            statusClass[i] = 'offline';
                            srvOnlStr[i] = (status.length > maxErrLen)
                                ? '<span class="monTrouble monTooBig">' + status + '</span>'
                                : '<span class=monTrouble>' + status + '</span>';
                            pingTime[i] = version[i] = '';
                            onBar[i] = 100
                        } else {
                            statusClass[i] = 'online';
                            srvOnlStr[i] = data['servers'][i]['online'] + '/' + data['servers'][i]['slots'];
                            pingTime[i] = data['servers'][i]['ping'];
                            version[i] = data['servers'][i]['version'];
                            onBar[i] = data['servers'][i]['percent']
                        }
                    }
                    ;
                    fullOnStr = data['online'] + '/' + data['slots'];
                    fullOnBar = data['percent'];
                    onlRec = data['record'];
                    todRec = data['recordday'];
                    timeRec = data['timerec'];
                    timeTodRec = data['timerecday']
                },
                error: servOff('<?php echo $config['sErr']?>'),
                complete: function () {// После получения данных и их парсинга вносим в мониторинг
                    setTimeout(function () {
                        $('#monAJAX .OnlineBar').removeClass('loading');
                        $('#monAJAX .OneServer').each(function (i) {
                            $(this).find('.JSSrvString').html(srvOnlStr[i]);
                            $(this).find('.OnlineBar').addClass(statusClass[i]).animate({'width': onBar[i] + '%'}, {
                                queue: false,
                                duration: smoothTime
                            });
                            $(this).find('.SrvVers').html(version[i]);
                            $(this).find('.SrvPing').html('ping&nbsp;' + pingTime[i]);
                            if (version[i] == '') $(this).find('.Tooltip').fadeOut({duration: 0, queue: false})
                        });

                        $('#JSComString').html(fullOnStr);
                        $('#CommonBar').animate({'width': fullOnBar + '%'}, {queue: false, duration: smoothTime});
                        $('#DayRec').html(todRec);
                        $('#AbsRec').html(onlRec);
                        $('#RecDate').html(timeRec);
                        $('#TodRTime').html(timeTodRec)
                    }, smoothTime * 1.01);
                    delete data
                }
            })
        }
    }

    $(document).ready(function () {
        // Создание мониторинга
        monAJAX.createMon();
        monAJAX.updateMon();
        setInterval(function () {// Период обновления
            monAJAX.updateMon()
        }, <?php echo($config['timecache'] * 1000)?>);

        // Периодичность точек загрузки данных
        var dot_txt = [], dot_i = 0;
        dot_txt[1] = '.';
        dot_txt[2] = '..';
        dot_txt[3] = '...';
        setInterval(function () {
            dot_i < 3 ? dot_i++ : dot_i = 1;
            $('#monAJAX .Ellipsis').html(dot_txt[dot_i])
        }, 250);

        // Всплывающие подсказки
        $('#monAJAX .Tooltipped').each(function () {
            $(this).mouseenter(function () {
                if ($(this).find('.Tooltip span').html() != 'ping&nbsp;' && $(this).find('.Tooltip span').html() != '')
                    $(this).find('.Tooltip').fadeIn({duration: 75, easing: 'swing', queue: false})
            });
            $(this).mouseleave(function () {
                $(this).find('.Tooltip').fadeOut({duration: 75, easing: 'swing', queue: false})
            })
        })
    })
</script>

<?php
if (!file_put_contents($config['template'], ob_get_contents()))
    die('Не удалось создать шаблон (проверьте \'template\' в файле конфигураций)');// Сохраняем вывод в файл из буфера
?>

<div id=monAJAX></div>
