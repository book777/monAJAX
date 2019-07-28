<?php
require 'protect/config.php';
if (file_exists($config['template']))
    echo file_get_contents($config['template']);
else
    die('Call build.php');
?>
<div id=monAJAX></div>
