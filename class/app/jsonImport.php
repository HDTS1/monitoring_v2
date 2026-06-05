<?php
namespace app;
class jsonImport {
    
    
    private function metoda($path, $recursive=false){
        list($trieda, $metoda) = explode(".", $path);
        
        $class = "\\app\\".$trieda;
        $trieda_name= $trieda;

        
        if(!class_exists($class)){
            echo "Trieda neexistuje :".$trieda;
            exit;
        }
        
        $trieda =new $class();       
        
        if(!method_exists($trieda, $metoda)){
            echo "metoda: ".$trieda_name."::".$metoda." nie je definovana";
            exit;              
        }

        $parameter=\app\data_route::getRoute();
        
        $result = $trieda->$metoda($parameter["system"]["url"]);
        
        return $result;

    }
    
    
    
    private function testAccess($path, $recursive=false){
        list($_true,$_false) = explode("|", $path);        
        $route = \app\data_route::getRoute();
        $kluc = $route["system"]["url"][1];
        $user = new \app\user();
        $result = $user->getUserFromAccess($kluc);
        
        if($result["result"]){
            $path = $_true;
            $user->setSession();
        } else {
            $path = $_false;
            $user->deleteSession();
        }
        
        $r = $this->getFromFile($path);
        return $r;        

    }
    
    
    private function testLogin($path, $recursive=false){
        
        list($_true,$_false) = explode("|", $path);
        
        
        $user = new \app\user();
        $result = $user->getLogin();
        
        if($result){
            $path = $_true;
            $r = $this->getFromFile($path,true);
            return $r;
        }
        
        $r = $this->getFromFile($_false,true);
        return $r;


    }
    
    
    private function file($path, $recursive=false){
        $r = $this->getFromFile($path,$recursive);
        return $r;
    }
    
    
    public function import($array, $recursive=false ){
            if(is_array($array) ){
                $array = array_map(function($item){
                    return $this->import($item);
                }, $array);
            } else {

                if(preg_match("/^@import:(?<metoda>.+)@(?<parameter>.+)$/", $array, $match)){
                    $metoda = $match["metoda"];
                    $parameter = $match["parameter"];
                    if(method_exists($this, $metoda)){
                        $array = $this->$metoda($parameter,true);
                    }
                }
                
                if($recursive){
                    if(is_array($array)){
                        $this->import($array);
                    }
                }
                
                
            }
        
            return $array;

    }

    
    public function getFromFile($file, $recursive=false){
        $file = root.$file;
        
        if(!file_exists($file)){
            return array("error"=>"Subor: ".$file." neexistuje");
        }
        $data = file_get_contents($file);
        $data = json_decode($data, true);
        if($recursive){
            return $this->import($data,true);
        }
        
        return $data;
    }
}
