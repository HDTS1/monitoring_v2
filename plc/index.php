<?php
define("root",$_SERVER['DOCUMENT_ROOT']);
require_once root.'/cfg/loader.php';
$data = file_get_contents("php://input");

$json = json_decode($data, true);
$bin = hex2bin($json["data"]);

$def = new \plc\dataDef();
$data = $def->getData($bin);
$zapis = new \plc\data();


//$zapis->setData($json["zaznam"],$data);
// Uprava pre C zaznam
$data["socket_server"]= @$_SERVER["REMOTE_ADDR"];
$data["socket_interval"]=  $zapis->getInterval($data["label"]);


$zapis->setData("plc",$data);

if(@$json["zaznam"]=='plc'){
    $nastavenie= $zapis->getSetting($data["label"]) ;
    $data["result"]=$nastavenie;
}

$json = json_encode($data, JSON_PRETTY_PRINT);
echo $json;










