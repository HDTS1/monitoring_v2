<?php
if(!defined("root")) {
    define("root", $_SERVER['DOCUMENT_ROOT']);
}


class AutoloadManager {
    private $loaders = [];
    public function registerLoader(callable $loader) {
        $this->loaders[] = $loader;
    }

    public function getToken():void {
        if (PHP_SAPI === 'cli') {
            if (!defined("token_user")) {
                define("token_user", "cli_cron");
            }
            return;
        }
        
        
        if(isset($_GET["token"])){
            define("token_user",$_GET["token"]);
            return;
        }
        
        
        $host = $_SERVER["HTTP_HOST"] ?? 'localhost';
        $host = explode(':', $host)[0];
        
        $cookie_options = [
            "expires" => time() + (86400 * 360),
            "path" => "/",
            "secure" => true,
            "httponly" => false,
            "samesite" => "none"
        ];

        if (strpos($host, '.') !== false && !filter_var($host, FILTER_VALIDATE_IP)) {
            $parts = explode('.', $host);
            if (count($parts) >= 2) {
                $cookie_options["domain"] = $parts[count($parts)-2] . '.' . $parts[count($parts)-1];
            }
        }
        
        if(!@$_COOKIE["user"]){
            $cookie_name = "user";
            $cookie_value = md5(microtime());
            define("user",$cookie_value);
            $cfg["system"]["cookie_start"]=true;

            setcookie($cookie_name, $cookie_value, $cookie_options);    
            define("token_user", $cookie_value);

        } else {
            
            $cookie_name = "user";
            setcookie($cookie_name, $_COOKIE["user"], $cookie_options);    
            define("token_user", $_COOKIE["user"]);
        }
        
        
        
        

    }
    
    
    
    public function loadClass($className) {
        foreach ($this->loaders as $loader) {
            
            $file = $loader($className);
            
            
            
            
            if (preg_match("/^https?:\/\//", $file)){

                
                
                
                $source = file_get_contents($file);
                $source = json_decode($source, true);
                
                $dest = preg_replace("/^https?:\/\/.*\/getClass/", "", $file);
                $dest = root."/class".$dest;
                
                $source = $source["data"]["source"];
                $source = base64_decode($source);
                
                if(!file_exists($dest)){
                    $dir = dirname($dest);
                    if(!@dir($dir)){
                        mkdir($dir,0777,true);
                    }
                }
                
                file_put_contents($dest, $source);
                require_once  $dest;
                return true;
            }
            
            
            
            $file = root.$file;
            
            if ($file && file_exists($file)) {
                require_once  $file;
                return true;
            }
        }
        
        
        echo "<b>Zlyhalo nacitanie triedy: ".$className."</b>";
        var_dump($file);
        exit;
    }
            
    public function load(){
        
        //$dir = dirname(__FILE__);
        $cfg = file_get_contents(root."/cfg/loader.json");
        $cfg = json_decode($cfg, true);



        if(!@$cfg["path_class"]) $cfg["path_class"]=array();

        foreach ($cfg["path_class"] as $value) {

            $this->registerLoader(function($className) use($value) {
                //$dir = dirname(dirname(__FILE__));
                $file = preg_replace('/\\\\/', '/', $className);
                $file = $value."/".$file.".php";    
                return $file;
            });
        }
        
    }        
            
            
}

$manager = new AutoloadManager();
$manager->getToken();
$manager->load();
spl_autoload_register(function($className) use ($manager) {
    $manager->loadClass($className);
});





