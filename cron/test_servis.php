<?php

$currentDir = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $currentDir;

require_once $currentDir.'/cfg/loader.php';



echo "Test device all\n";
$rest = new \service\fnc\service();
$x = $rest->testDeviceAll();


$rest = new \service\fnc\monitor();

echo "clearTestDevice\n";
$x = $rest->clearTestDevice();


echo "Clear papago\n";
$x = $rest->clearPapago();


$rest = new \service\fnc\user();
$rest->deleteUserWait();


/*
echo "Clear PLC\n";
$x = $rest->clearPLC();
*/
