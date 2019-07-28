## Версия - 1.6

## Авторы
- [MattRh](http://rubukkit.org/members/57495) (доработка js+css, php, переход на независимый скрипт)
- [book777](https://vk.com/nikolia0612) (оптимизация php, добавлене функциональности)
- [Cyber Owl](http://rubukkit.org/members/51017) (начальная идея, начальный js => jmcAPI)
- [Artmoneyse](http://rubukkit.org/members/69175) (Некоторые фиксы)

[Репозиторий](https://github.com/book777/monAJAX/)
[Обсуждение](http://rubukkit.org/threads/99529)



## Установка

1) Кинуть папку с мониторингом в корень сайта (site.ru/monAJAX/)

2) Отредактировать файл protect/config.php

	Изменить сервера - $servers
	Определиться с методом обновления мониторинга - cache_mode
	
3) Открыть через браузер build.php



## При возникновении ошибок
- дать файлам и папкам право 755
- для методов 'cache_mode' 1-2 попробовать увеличить 'webTimeout'
- если ничего выше не помогло, включить в конфиге debug и написать о проблеме https://github.com/book777/monAJAX/issues


## Лицензия
	[CC BY-ND Creative Commons Attribution-NoDerivatives](https://creativecommons.org/licenses/by-nd/4.0/)