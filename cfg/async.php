<?php
define("root",$_SERVER['DOCUMENT_ROOT']);
require_once root.'/cfg/loader.php';

sleep(20);

$db = new \db\sql();
$db->setModel("test", array("palo"=>"palo"));
echo "ok";