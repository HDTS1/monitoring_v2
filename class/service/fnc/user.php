<?php
namespace service\fnc;
class user  extends \service\baseExtend {
    public static $user_key = null;
    private $_rola = 0;
    
    public function __construct() {
        $setting = array(
            "server"   => getenv('DB_USERS_HOST') ?: getenv('DB_HOST') ?: "db",
            "user"     => getenv('DB_USERS_USER') ?: getenv('DB_USER') ?: "hdts_user",
            "password" => getenv('DB_USERS_PASS') ?: getenv('DB_PASS') ?: "",
            "databaza" => "hdtc_users",
            "port"     => intval(getenv('DB_USERS_PORT') ?: getenv('DB_PORT') ?: 3306),
            "charset"  => "utf8"
        );
        
        $this->setDB($setting);
    }
    
    
    public function logOff(){
        $kluc = token_user;
        $db = $this->getDB();
        $t = $db->modelTable("hdts-session");
        $t->addFilter("token", $kluc);
        
        $result = $t->cmd();
        
        foreach ($result as $value) {
            $db->deleteModel($value["id_model"]);
        }
        
        return $this->output("OK");
    }
    
    
    public function getUserData(){
        
        $db = $this->getDB();
        $result = $db->getModel(self::$user_key);
        
        return $this->output($result);
    }
    
    
    public function getUserFromToken(){

        
        $user = null;
        
        $token = $this->parameter["token"];
        $db = $this->getDB();
        $result = $db->overitZaznam("hdts-session",array("token"=>$token));
        if(!$result){
            return  $this->output($user);
        }
        
        $zaznam = $db->getModel($result);
        $user = $zaznam["data"]["user"];
        
        self::$user_key = $user;
        
        $userData = $this->getUserData();
        
        $mm = new \app\message();
        $data = array(
            "username"=>$userData["data"]["data"]["username"],
            "time"=>(new \DateTime())->format("H:i:s"),
            "url"=>$_SERVER["REQUEST_URI"]
        );
        
        
        $mm->sendSocket("user",$data);
        $db->setModel("history", $data);
        
        
        
        
        
        return  $this->output($user);

    }
    
    public function setUserSession(){
        $token = $this->parameter["token"];
        $user = $this->parameter["user"];
        $db = $this->getDB();
        
        $data = array(
            "token"=>$token,
            "user"=>$user,
            "time"=> (new \DateTime())->format("Y-m-d H:i:s")
        );
        
        
        $result = $db->setModel("hdts-session", $data);
        
        
        
        self::$user_key = $user;
        
        return $this->output($result);
        
    }
    
    
    public function setUserToken(){
        $db = $this->getDB();
        $data = $this->parameter;
        $result = $db->setModel("hdts-overovaci-kluc", $data);
        return $this->output($result);
        
    }
    
    public function overitKluc(){
        $db = $this->getDB();    
        $result = $db->getModel($this->parameter["kluc"]);
        
        
        $this->parameter["code"] = preg_replace("/\s/", "", $this->parameter["code"]);
        if($result["data"]["kluc"]!= $this->parameter["code"]){
            return $this->output(null,false);
        }

        
        $rest = new $this;
        $rest->parameter = array("token"=> $result["data"]["user_token"],"user"=>$result["data"]["user_key"]);
        $rest->setUserSession();
        
        $db->deleteModel($this->parameter["kluc"]);

        
        return $this->output($this->parameter);
        
    }
    
    
    public function setUser(){
        if(!@$this->parameter["username"]){
            return $this->output("error username",false);
        }
        $db = $this->getDB();
        $data = array(
            "username"=>$this->parameter["username"],
            "rola"=> $this->_rola
        );
        
        $result = $db->overitZaznam("hdts-user",array("username"=>$this->parameter["username"]));
        
        if(!$result){
            $result = $db->setModel("hdts-user", $data);
        }
        
        return $this->output($result);
        
    }
    
    // Uprava aby mna nezobrazovalo
    public function lastAcess(){
        $sql = "SELECT a.cas_create AS date_time, b.hodnota AS username, c.hodnota AS `url`
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='username'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='url'
        WHERE a.model='history' AND date(a.cas_create) >  CURDATE() - INTERVAL 1 DAY
        ORDER BY a.cas_create DESC LIMIT 30";
        
        
        $db = $this->getDB();
        $db->add_sql($sql, "zaznam");
        
        $result = $db->cmd();
        $result = $result["zaznam"];
        
        return $this->output($result);
        
    }


    public function text() {
            $apiKey = getenv('OPENAI_API_KEY') ?: '';
            $text = "Mal by som zaujem o male auto do mesta s nizkou spotrebou";
            
            
            // Definuj prompt na extrakciu kľúčových slov
            $prompt = "Extrahuj kľúčové slová z textu: \"$text\". Výstup by mal obsahovať len kľúčové slová bez diakritiky";

            // Nastavenie curl požiadavky
            $ch = curl_init('https://api.openai.com/v1/chat/completions');

            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 50, // prispôsob podľa potreby
            ];

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Vykonanie požiadavky a spracovanie odpovede
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Chyba: ' . curl_error($ch);
                return null;
            }

            curl_close($ch);

            // Dekódovanie JSON odpovede
            $responseData = json_decode($response, true);
            return $this->output($responseData);
            
 
        }


        
        
        

        
    public function getUser(){
        $kluc = $this->parameter["kluc"];
        $db = $this->getDB();
        $result = $db->getModel($kluc);
        
        return $this->output($result);
        
    }
    
    
   public function getUserAll(){

        $db = $this->getDB();
        
        $t = $db->modelTable("hdts-user");
        $result = $t->cmd();
        
        $collator = new \Collator('en_US');
        usort($result, function($a,$b) use($collator){
                $a1=@$a["data"]["username"];
                $b1=@$b["data"]["username"];
                return $collator->compare($a1, $b1);  
        });
        
        
        
        
        
        return $this->output($result);
        
        
    }
    
    public function setUserOpravnenie(){
        $db = $this->getDB();
        $db->updateModel($this->parameter["kluc"], $this->parameter["data"]);
        $result = $db->getModel($this->parameter["kluc"]);
        
        return $this->output($result);
        
    }
    
    
    
    public function last_access_url(){
        $kluc = $this->parameter["username"];
        
        
        $sql = "SELECT a.cas_create AS date_time, c.hodnota AS `url`
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='username'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='url'
        WHERE a.model='history' AND b.hodnota= :kluc
        ORDER BY a.cas_create DESC LIMIT 5";
        
        $db = $this->getDB();
        $p = $db->add_sql($sql, "zaznam");
        $p->def("kluc", $kluc);
        $result = $db->cmd();
        $result = @$result["zaznam"];
        
        return $this->output($result);
        
        
    }
    
    
    public function last_active_access(){
        $kluc = $this->parameter["kluc"];
        
        $sql = "SELECT b.hodnota AS `user`, c.hodnota AS `token`, convert(d.hodnota,datetime) AS `time`
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='user'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='token'
        JOIN model_data d ON a.id_model=d.id_model AND d.kluc='time'
        WHERE a.model='hdts-session' AND b.hodnota= :kluc
        ORDER BY a.cas_create DESC LIMIT 1";
        
        $db = $this->getDB();
        $p = $db->add_sql($sql, "zaznam");
        $p->def("kluc", $kluc);
        $result = $db->cmd();
        $result = @$result["zaznam"][0];
        
        return $this->output($result);
                
        
        
    }
    
    
    
    
    public function getUserFromRole(){
        $rola = $this->parameter["rola"];
        $db = $this->getDB();
        
        $t = $db->modelTable("hdts-user");
        $t->addFilter("rola", $rola);
        
        
        $result = $t->cmd();
        return $this->output($result);
        
        
    }
    
    
    public function deleteUserWait(){
        $filter = $this->listUserWait();
        $filter = $filter["data"];    
        $db = $this->getDB();
        
        foreach ($filter as $value) {
            $db->deleteModel($value["model"]["id_model"]);
        }
        
        
        
        return $this->output($filter);
    } 
    
    
    
    public function listUserWait(){
        
        $db = $this->getDB();
        
        $t = $db->modelTable("hdts-user");
        $t->addFilter("rola", "0");
        $t->setData();
        $result = $t->cmd();
        
        
        $filter = array_filter($result, function($item){
            $cas_registracia = new \DateTime($item["model"]["cas_create"]); 
            $time = new \DateTime();
            $time->modify("-15 day");
            
            return $cas_registracia->getTimestamp() < $time->getTimestamp();

        });
        
        $filter = array_values($filter);
        
        return $this->output($filter);
        
    }
    
}
