<?php
namespace app;
class user {
    private $user = null;
    private $model_log="hdts-log-route";
    private $model_session="hdts-session";
    private $model_user = "hdts-users";
    
    private $cookieKey = null;
    private $key_session=null;
    public static $userData=null;
    
    public function __construct() {
        $this->cookieKey = token_user;
    } 

   public function getUserData(){
       $user_id = \app\session::getUserID();
       $dm = new \app\dbModel();
       $result = $dm->getDataModel($user_id);
       return $result;
   } 
    
    
    
    public function overSession(){
        $aktCas = new \DateTime();
        $aktCas = $aktCas->getTimestamp();
        
        $dm = new \app\dbModel();
        $t= $dm->setTable($this->model_session);
        $t->setFilter("user_cookie", $this->cookieKey);
        $t->setData();
        $result = $dm->getZoznam();
        $result = @$result[0];
        

        

        
        if(!$result){
            return false;
        }

        
        if($result && $aktCas > @$result["data"]["time_stamp"]){
            return false;
        }

        
        self::$userData = $result;
        
        $res= new \rest\fnc\iis();
        $user = $res->getUser();

        $res->sendSocket("beeSport","login",$user["data"]["username"]);
        
        
        if(!@$_SESSION["notify"]){
            
            $res= new \rest\fnc\iis();
            $user = $res->getUser();
            
            
            $res= new \rest\fnc\system();
            $res->parameter= array(
                "email"=>$user["data"]["username"]
            );
            $res->notify();
            
            
            
            
            
            $_SESSION["notify"]=true;
            $dm->updateDataModel($result["id_model"], array("time_stamp"=>$aktCas + (24*5*60*60),"last_time"=>$aktCas));
        }
        
        
        
        
        
        
        return true;
        
    }
    
    
    public function getUserFromEmail_old($username){
        $dm = new \app\dbModel();
        $t = $dm->setTable($this->model_user);
        $t->setData();
        $t->setFilter("username", $username);
        $result = $dm->getZoznam();
        
        if(@$result[0]){
            $this->user= $result[0];
            return $result[0];
        }
        return $result;
    }
        
    public function testPassword($password){
        $password = md5($password);
        return $password == $this->user["data"]["password"];
    }


    public function setSession(){
        $data = array(
            "user"=> $this->user["id_model"]
        );
        \app\session::setSession($data);
        return true;
    }
    
    public function setCookie(){
        \app\session::setCookieUser($this->user["id_model"]);
        return true;
    }  
    
    
    public function getUserFromEmail(){
        $data = $this->parameter;
        $kluc = null;
        
        $email = @$data["email"];
        if(empty($email)){
            return false;
        }
        
        $dm = new \app\dbModel();
        $t = $dm->setTable($this->model_user);
        $t->setFilter("email", trim($email));
        $t->setData();
        $z= $dm->getZoznam();
        
        if(!@$z[0]){
            $kluc = $dm->setDataModel($this->model_user, $data);
        } else {
            $kluc = $z[0]["id_model"];
        }
        $this->user= $kluc;
              
        
        $data = array(
                    "user_key"=>$kluc,
                    "user"=>$dm->getModel($kluc)
                );
    
        $dm = new \app\dbModel();
        $t = $dm->setTable($this->model_session);
        $t->setFilter("user", $kluc);
        $t->setFilter("cookie", $this->cookieKey);
        $z = $dm->getZoznam();
        $kluc_session = @$z[0]["id_model"];

        
        // simulacia prihlasenia
        if(!$kluc_session){
            $d = array(
                "user"=>$kluc,
                "cookie"=> $this->cookieKey
            );
            $kluc_session = $dm->setDataModel($this->model_session, $d);
        }

        
        $data["app_session"]= $kluc_session;
        $data["cookie_key"] = $this->cookieKey;
        $data["centrum_key"]= \app\centrum::getCentrumKey();
        
        return $data;
        
    }
        
    
    public function setLog($data){
        //var_dump(\app\cfg::$conf);
        // Pozastavny zapis 
        $dm = new \app\dbModel();
        $data["user"]= $this->user;
        $data["time"]= date("Y-m-d H:i:s");
        //$result = $dm->setDataModel($this->model_log, $data);
        $result = "OK";
        return $result;
        
    }
    
    public function getLog(){
        $dm = new \app\dbModel();
        $t = $dm->setTable($this->model_log);
        $t->setFilter("user", $this->user);
        $t->setData();
        $result = $dm->getZoznam();
                
        $result = array_map(function($item){
            return $item["data"];
        }, $result);
        
        return $result;
    }


    
}
