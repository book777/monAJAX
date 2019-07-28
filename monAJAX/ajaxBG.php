<?php require_once 'protect/config.php';

if ($_SERVER['HTTP_USER_AGENT'] != secSeed('protect/seed.txt'))
    die('Nice try');

require_once 'protect/ajaxEngine.php';
