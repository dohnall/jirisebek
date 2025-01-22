<?php
$time_start = microtime(true);

require_once dirname(__FILE__)."/lib/config/config.php";

WEB::start();

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "\n<!--".memory_get_usage(true)."-->";
echo "\n<!--$time-->";
