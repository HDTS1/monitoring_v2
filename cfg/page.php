<?php
namespace app;

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set("display_errors", 1);
session_start();

$isLocal = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
$isHttps = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

if (!$isLocal && !$isHttps) {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}

$domain = $_SERVER["HTTP_HOST"];
$domain = preg_replace("/^.*\.(.+\..+?)$/", "\$1", $domain);





$cfg = array(
    "system"=> array(
        "cookie_start"=>false
    )
);


class cfg {
    public static $conf=array();
    public static $over_cookie= false;
    
    public static function generateDoc(){
        
        $data = array(
            "route"=>self::$conf["system"],
            "seo"=>self::$conf["seo"]
        );

        $template = self::$conf["template"];
        
        
        
        
        $page = new \page\template();
        $page->loadData($data);
        $page->loadTemplate($template);
        $page->spracuj();
        $doc = $page->setHTML();
        
        $vystup =  $doc->saveHTML($doc->documentElement);
        $vystup=\PHPWee\Minify::html($vystup);
        $vystup = "<!DOCTYPE html>\r\n".$vystup;
        
        
        echo $vystup;
    }
}




$reqUrl = strtok($_SERVER["REQUEST_URI"],'?');
$x = preg_replace("/^\//", "",$reqUrl);
$x = explode("/",$x);
$x = array_map(function($item){
    if(empty($item)){
        return null;
    }
    return $item;
},$x);



define("_root", $_SERVER['DOCUMENT_ROOT']);



$cfg_user = file_get_contents(root."/cfg/page.json");
$cfg_user = json_decode($cfg_user,true);
$cfg_user["seo"]["server"]=$_SERVER["HTTP_HOST"];


$cfg = array_replace_recursive($cfg, $cfg_user);
$cfg["system"]["user"]= token_user;
$cfg["system"]["dir_start"]=_dir;
$cfg["system"]["root"]= _root;
$cfg["system"]["session"]= $_SESSION;
$cfg["system"]["url_path"]= $reqUrl;
$cfg["system"]["url"]= $x;
\app\cfg::$conf= $cfg;
\app\data_route::setRoute($cfg);
define("sablona", _root.$cfg["template_path"]);



$route = new \app\route($cfg["system"]["url_path"], token_user);
$route->getRoute();



\app\cfg::generateDoc();




