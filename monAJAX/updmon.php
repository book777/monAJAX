<?php
require 'config.php';

function tplRead($name) {
	return str_replace(array('{', '}', "\r", "\n"), array('"+', '+"', '', ''), file_get_contents('template/'.$name));
}

ob_start();// Включаем буфер
?>
<meta charset='utf-8'>
<link rel="stylesheet" type="text/css" href="./template/monStyle.css">
<script src='//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js' type='text/javascript'></script>
<link rel=stylesheet href='<?php echo $config['dir']?>template/monStyle.css' type='text/css'>
<script type='text/javascript'>
var Monitoring = {
    servers: [<?php foreach($servers as $server) echo '{name:"'.$server['name'].'"},'?>],
    createMon: function(){ // Создание основы мониторинга
		var newHTML='',servers=this.servers,server,srvOnlStr,onBar,version,pingTime,fullOnStr,fullOnBar,todRec,onlRec,timeTodRec,timeRec;
		srvOnlStr = '<span class=JSSrvString>.</span>';
		fullOnStr = '<span id=JSComString>.</span>';
        todRec = '<span id=DayRec>.</span>';
		onlRec = '<span id=AbsRec>.</span>';
		timeTodRec = '<span id=TodRTime>.</span>';
		timeRec = '<span id=RecDate>.</span>';
		version = '<span class=SrvVers>.</span>';
		pingTime= '<span class=SrvPing>.</span>';
		onBar = '<div class="OnlineBar loading" style="width:100%"></div>';
		fullOnBar = '<div class=OnlineBar id=CommonBar style="width:100%"></div>';
		for(var i=0, len=servers.length; i<len; i++){// Шаблон каждого сервера
			server = servers[i]['name'];
			newHTML += "<?php echo tplRead('server.tpl')?>"
		};
		newHTML += "<?php echo tplRead('common.tpl')?>";// Шаблон общего онлайна
		$('#Monitoring').html(newHTML);// Записываем получившуюся основу
		delete newHTML
	},
	updateMon: function(){
		var servers=this.servers,
			i,len=servers.length,
			smoothTime=<?php echo ($config['smoothBar']) ? 1200 : 0?>,
			ellipsis='<span class=Ellipsis>.</span>',
			maxErrLen=14, 
			// Переменные одного сервера
			status,// Статус работы сервера(выключен/включен и т.д)
			server,// Имя сервера
			srvOnlStr=[],// Текст состояния сервера
			statusClass=[],// Статус бара заполненности сервера
			onBar=[],// Ширина бара заполненности сервера
			version=[],// Версия сервера
			pingTime=[],// Пинг сервера
			// Переменные общего онлайна
			fullOnStr,// Текст состояния всех серверов
			fullOnBar,// Ширина бара заполненности всех серверов
			todRec,// Рекорд дня
			onlRec,// Абсорютный рекорд
			timeTodRec,// Время установления рекорда этого дня
			timeRec;// Время установления абсолютного рекорда
		$.ajax({
			url:'<?php echo $config['dir'].($config['json_mode']?'ajax.php':'ajax.json')?>',
            beforeSend: function(){// Перед попыткой получения данных
				$('#Monitoring .OnlineBar').removeClass('online offline upderr');
                $('#Monitoring .OneServer').each(function(){
                    $(this).find('.JSSrvString, .SrvVers, .SrvPing').html(ellipsis);
					$(this).find('.OnlineBar').addClass('loading').animate({'width':'100%'},{queue:false,duration:smoothTime})
                });
				$('#JSComString, #DayRec, #AbsRec, #TodRTime, #RecDate').html(ellipsis);
				$('#CommonBar').animate({'width':'100%'},{queue:false,duration:smoothTime});
            },
            success: function(data){// Если получили данные
				for(i=0; i<len; i++){
					server = servers[i]['name'];
					status = data['servers'][server]['status'];
					if(status != 'online'){
						statusClass[i] = 'offline';
						srvOnlStr[i] = (status.length > maxErrLen) 
						? '<span class="monTrouble monTooBig">'+status+'</span>'
						: '<span class=monTrouble>'+status+'</span>';
						pingTime[i] = version[i] = '';
						onBar[i] = 100
					}else{
						statusClass[i] = 'online';
						srvOnlStr[i] = data['servers'][server]['online'] + '/' + data['servers'][server]['slots'];
						pingTime[i] = data['servers'][server]['ping'];
						version[i] = data['servers'][server]['version'];
						onBar[i] = data['servers'][server]['percent']
					}
				};	
				fullOnStr = data['online'] + '/' + data['slots'];
				fullOnBar = data['percent'];
				onlRec = data['record'];
				todRec = data['recordday'];
				timeRec = data['timerec'];
				timeTodRec = data['timerecday']
            },
            error: function(){// Не получилось соединиться с веб-сервером
				for(i=0; i<len; i++){
					statusClass[i] = 'upderr';
					srvOnlStr[i] = '<span class=monTrouble><?php echo $config['sErr']?></span>';
					pingTime[i] = version[i] = '';
					onBar[i] = 100
				};

                fullOnStr = '<span class=monTrouble><?php echo $config['sErr']?></span>';
				fullOnBar = 100;
				todRec = onlRec = timeRec = timeTodRec = '<?php echo $config['sErr']?>'
            },
            complete: function(){// После получения данных и их парсинга вносим в мониторинг
                setTimeout(function(){
					$('#Monitoring .OnlineBar').removeClass('loading');
					$('#Monitoring .OneServer').each(function(i){
						$(this).find('.JSSrvString').html(srvOnlStr[i]);
						$(this).find('.OnlineBar').addClass(statusClass[i]).animate({'width':onBar[i]+'%'},{queue:false,duration:smoothTime});
						$(this).find('.SrvVers').html(version[i]);
						$(this).find('.SrvPing').html('ping:&nbsp;'+pingTime[i]);
						if(version[i] == '') $(this).find('.Tooltip').fadeOut({duration:0,queue:false})
					});
					
					$('#JSComString').html(fullOnStr);
					$('#CommonBar').animate({'width':fullOnBar+'%'},{queue:false,duration:smoothTime});
					$('#DayRec').html(todRec);
					$('#AbsRec').html(onlRec);
					$('#RecDate').html(timeRec);
					$('#TodRTime').html(timeTodRec)
                },smoothTime*1.01);
				delete data
            }
        })
	}
}
$(document).ready(function(){
	// Создание мониторинга
    Monitoring.createMon();Monitoring.updateMon();
	setInterval(function(){// Период обновления
		Monitoring.updateMon()
	}, <?php echo ($config['timecache']*1000)?>);
	
	// Периодичность точек загрузки данных
	var dot_txt=[],dot_i=0;
	dot_txt[1]='.';dot_txt[2]='..';dot_txt[3]='...';
	setInterval(function(){
		dot_i<3 ? dot_i++ : dot_i=1;
		$('#Monitoring .Ellipsis').html(dot_txt[dot_i])
	}, 165);
	
	// Всплывающие подсказки
	$('#Monitoring .Tooltipped').each(function(){
		$(this).mouseenter(function(){
			if($(this).find('.Tooltip span').html() != 'ping:&nbsp;' && $(this).find('.Tooltip span').html() != '')
				$(this).find('.Tooltip').fadeIn({duration:75,easing:'swing',queue:false})
		});
		$(this).mouseleave(function() {
			$(this).find('.Tooltip').fadeOut({duration:75,easing:'swing',queue:false})
		})
	})
})
</script>

	<div id=Monitoring></div>
<?php
if (!file_put_contents($config['template'], ob_get_contents())) die('Не удалось создать шаблон (измените \'template\' в конфиге)');// Сохраняем вывод в файл из буфера
?>
