<?php
define("root",$_SERVER['DOCUMENT_ROOT']);
require_once root.'/cfg/loader.php';

class toolService{
    private $data=null;
    private $path_class = "\\service\\fnc\\";
    
    public function __construct() {
        $this->data = array(
                    "result"=>false,
                    "data"=>null,
                    "vstup"=>null,
                    "server"=>array("name"=>$_SERVER["SERVER_NAME"], "time"=> microtime(true))
                );
    }
    
    
    private function spracovatMetodu($trieda,$metoda){
        
        $class = $this->path_class.$trieda;
        $trieda_name= $trieda;

        
        if(!class_exists($class)){
            $result = array(
                "result"=>false,
                "error"=>"trieda: ".$trieda." nie je definovana"
            );
            $this->data= array_replace_recursive($this->data, $result);
            return false;
        }
        
        $trieda =new $class(); 

        
        if(!method_exists($trieda, $metoda)){
            $result = array(
                "result"=>false,
                "error"=>"metoda: ".$trieda_name."::".$metoda." nie je definovana"
            );
            $this->data= array_replace_recursive($this->data, $result);
            return false;             
        }
        
        
        
        
        $trieda->parameter = $this->data["vstup"];
        
        
        $this->data = array_replace_recursive($this->data, $trieda->$metoda());
        return $this->data;
        
        
        
    }
    
    private function buildIN(){
        
        if(@$_GET){
            $this->data["vstup"]= $_GET;
        }
        
        $dir = basename(dirname(__FILE__));
        $reqUrl = strtok($_SERVER["REQUEST_URI"],'?');
        $reqUrl = preg_replace("/^\/".$dir."\//", "", $reqUrl);
        $reqUrl = preg_replace(["/%20/"], [""], $reqUrl);

        $parts = explode("/", $reqUrl, 3);
        $trieda = isset($parts[0]) ? $parts[0] : null;
        $metoda = isset($parts[1]) ? $parts[1] : null;
        $parameter = isset($parts[2]) ? $parts[2] : null;

        if(!$trieda | !$metoda){
            $this->data["data"] = array("result"=>false, "data"=>"Neplatna trieda alebo metoda");
            return false;
        }
        
        if($_SERVER["REQUEST_METHOD"]=="POST"){
            $js = json_decode(file_get_contents("php://input"), true);

            
            if($js){
                if(!$this->data["vstup"]) $this->data["vstup"]=array();
                $this->data["vstup"]= array_replace_recursive($this->data["vstup"], $js);
            } 

            
            
        }

        if($parameter){
            $this->data["vstup"]["url"]=$parameter;
        }
        
        $this->spracovatMetodu($trieda, $metoda);
        
    }
    
    public function output(){
        
        $this->buildIN();
        
        $this->data["server"]["time"] = microtime(true) - $this->data["server"]["time"];
        $vystup = json_encode($this->data);
        $contentLength = strlen($vystup);
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        header("Content-length: $contentLength");
        header("Content-type: application/json; charset=utf-8"); 
        echo $vystup;
    }
    
    
}

$vystup = new toolService();
$vystup->output();
exit;









