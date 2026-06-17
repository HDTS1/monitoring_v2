<?php
define("root",$_SERVER['DOCUMENT_ROOT']);
require_once root.'/cfg/loader.php';

if(!@$_GET){
    echo "false";
    exit;
}





$vystup = json_encode($_GET, JSON_INVALID_UTF8_IGNORE);
$vystup = json_decode($vystup, true);




file_put_contents(root."/papago/test.json", json_encode($vystup));
//$vystup = json_decode($vystup, true);
//$db= new \db\sql();
//$db->setModel("smeti", $vystup);



$kluc=-1;

$t = md5("dataGrafTeplotaAdmin");
// \app\cash::delete($t); // Commented out to prevent cache invalidation every 3 seconds on sensor posts.


$hodnota = @$vystup["T1V1_value"];

$date = function($d){
    $d = new \DateTime($d);
    return $d->format("Y-m-d H:i:s");
};

if($hodnota){
    $data = array(
        "name"=>$vystup["CH1_name"],
        "date_time"=> $date($vystup["date_time"]),
        "value"=>$vystup["T1V1_value"],
        "unit"=>$vystup["T1V1_units"]
    );
    
    $rest = new \service\fnc\monitor();
    $rest->parameter= $data;
    $kluc = $rest->setPapago();
}

$hodnota = @$vystup["T2V1_value"];
if($hodnota){
    $data = array(
        "name"=>$vystup["CH2_name"],
        "date_time"=> $date($vystup["date_time"]),
        "value"=>$vystup["T2V1_value"],
        "unit"=>$vystup["T1V1_units"]
    );
    
    $rest = new \service\fnc\monitor();
    $rest->parameter= $data;
    $kluc = $rest->setPapago();
}


