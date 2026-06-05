<?php
namespace app;

class route_metoda {
    private $route = null;
    public function __construct(&$route) {
        $this->route=&$route;
    }
    
    private function getRola(){
            $rest = new \service\fnc\user();
            $result = $rest->getUserData();
            $rola = intval($result["data"]["data"]["rola"]);
            
            
            //$rola=0;
            
            return $rola;
    }
    
    private function root_document(){

            $rola = $this->getRola();
            
            
            
            $page = array(
                0 => "/obsah/autorize_wait",
                100 => "/obsah/user",
                1000 => "/obsah/admin"
            );

            
            $this->route["app"]= $page[$rola];
            
    }
    
    private function admin(){
            $rola = $this->getRola();
            
            if($rola != 1000){
                $this->route["app"] = "/obsah/admin_no";
            }

    }
    
    
    private function login_off(){
        $rest = new \service\fnc\user();
        $rest->logOff();
        header("Location:/");
        exit;
        
    }
    
    public function run(){
        if(@!$this->route["metoda"]){
            return true;
        }
        
        if(!is_array($this->route["metoda"])){
            echo "Metoda musi byt array !!!!";
            exit;
        }
        
        foreach ($this->route["metoda"] as $method) {
            if(!method_exists($this, $method)){
                echo "Neplatna metoda route !!!";
                exit;
            }
            
            $this->$method();
        }

        
    }
    
    
}

class route {
    private $url = null;
    private $userToken=null;
    private $route = null;
    private $layoutLogin = "login";
    private $layoutDefault = "root";
    private $layoutUser = "user_board";
    
    private $confRoute = "/cfg/route.json";
    
    public function __construct($url,$user) {
        $jObject = new \app\jsonImport();
        $this->route = $jObject->getFromFile($this->confRoute);
        $this->url= $url;
        $this->userToken=$user;
    }
    
    
    private function getToken(){
       $userRest = new \service\fnc\user();
       $userRest->parameter=array("token"=> $this->userToken);
       $user = $userRest->getUserFromToken();
       $user = $user["data"];
       
       
       
       
       
       return array("token"=>$this->userToken, "user"=>$user);
       
    }
    
    
    
    private function spracovatRoute(&$route){
        $default = array(
            "url"=>\app\cfg::$conf["system"]["url_path"],
            "access"=>false,
            "app"=>"/obsah/noRoute"
        );

        if(!$route){
            $route = $default;
        }

        $user = $this->getToken();
        $route["user"]=$user;
        
        if($route["access"]==true && !$user["user"]){
            $route["app"]= "/obsah/login";
        }
        


        if($route["access"]==true && $user["user"]){

            $access = new route_metoda($route);
            $access->run();

        }
        
        
        return true;

    }
    
    
    public function getRoute (){
        $route = array_filter($this->route, function($item){
            $pattern = "@^" . preg_replace('/\*/', '([a-zA-Z0-9\-\_=]+)', $item['url']) . "$@D";
            if(preg_match($pattern, $this->url)){
                return true;
            } else {
                return false;
            }
        });
        
        $route = array_values($route);
        $route = @$route[0];
        
        $this->spracovatRoute($route);
        \app\cfg::$conf["system"]["page"]=$route;
        
        return array(
            "route"=>$route,
            "user"=>token_user
        );

 
    }

    
}
