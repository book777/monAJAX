<?php

function errorRep($errR)
{
    if ($errR) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(-1);
    } else {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
    }
}

function tplRead($name)
{
    return str_replace(array('{', '}', "\r", "\n", "\t"), array('"+', '+"', '', '', ''), file_get_contents('template/' . $name));
}

function wayToScript()
{
    $tmp = '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
        return 'https' . $tmp;
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
        return 'https' . $tmp;
    return 'http' . $tmp;
}

function beautydate($data)
{// Дата для рекордов
    $iz = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Jct', 'Nov', 'Dec');
    $v = array('января', 'феваля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    $vblhod = str_replace($iz, $v, date("j M в H:i", $data));
    return $vblhod;
}


function secSeed($path)
{
    if (file_exists($path) && filesize($path) > 0)
        return file_get_contents($path);
    else {
        $tmp = md5(microtime() . mt_rand());
        file_put_contents($path, $tmp);
        return false;
    }
}

?>
