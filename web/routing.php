<?php
// php -S localhost:8010 routing.php
// (file_exists(__DIR__ . '/' . $_SERVER['REQUEST_URI'])) 
echo $_SERVER['REQUEST_URI'];
if (file_exists($_SERVER['REQUEST_URI'])) 
{
    // Need a bit of a hack for /
    if ($_SERVER['REQUEST_URI'] != '/') return false;
}
// Why no DIR here? test when starting up from a different directory
include_once 'app_dev.php';
?>
