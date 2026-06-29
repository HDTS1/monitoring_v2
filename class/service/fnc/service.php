<?php
namespace service\fnc;
class service extends \service\baseExtend {
    private $serverTest = '213.160.161.250';
    private $port=8080;
    
    //private $serverTest = 'echo.fullmedia.sk';
    //private $port=80;
    private $encryptionKey='palo999';
    
    
    
    public function __construct() {
        $setting = array(
            "server"   => getenv('DB_HOST')     ?: "db",
            "user"     => getenv('DB_USER')     ?: "hdts_user",
            "password" => getenv('DB_PASS')     ?: "",
            "databaza" => getenv('DB_NAME')     ?: "hdts_monitor",
            "port"     => intval(getenv('DB_PORT') ?: 3306),
            "charset"  => "utf8mb4"
        );

        $this->setDB($setting);
    }
    
    private function encryptMessage($message) {
        
        //return base64_encode($message);
        
        
        $encryptionKey = getenv('PROXY_KEY') ?: $this->encryptionKey ; 
        
        $cipherMethod = "AES-256-CBC";
        $ivLength = openssl_cipher_iv_length($cipherMethod);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encryptedMessage = openssl_encrypt($message, $cipherMethod, $encryptionKey, 0, $iv);
        $encryptedMessage = base64_encode($iv . $encryptedMessage);
        return $encryptedMessage;

    }

    private function decryptMessage($encryptedMessage) {
        //return base64_decode($encryptedMessage);
        
        $encryptionKey = getenv('PROXY_KEY') ?: $this->encryptionKey ; 
        
        $cipherMethod = "AES-256-CBC";
        $ivLength = openssl_cipher_iv_length($cipherMethod);
        $encryptedMessage = base64_decode($encryptedMessage);
        $iv = substr($encryptedMessage, 0, $ivLength);
        $encryptedMessage = substr($encryptedMessage, $ivLength);
        $decryptedMessage = openssl_decrypt($encryptedMessage, $cipherMethod, $encryptionKey, 0, $iv);
        return $decryptedMessage;

    }

    public function sendServerData($data, $metoda="socket",$server= null,$port= null){
        if(!$port){
            $port = getenv('PROXY_PORT') ?: $this->port;
        }
        if(!$server){
            $server = getenv('PROXY_HOST') ?: $this->serverTest;
        }

        $Request = array(
            "metoda"=>$metoda,
            "data"=>$data
        );

        $Request = json_encode($Request);
        $Request = $this->encryptMessage($Request);

        $address = "tcp://$server:$port";
        $socket = @stream_socket_client($address, $errno, $errstr, 30);

        if (!$socket) {
            return array("data"=>"server nedostupny", "result"=>false);
        } else {
            fwrite($socket, $Request);
            $vystup = "";

            while (!feof($socket)) {
                $vystup .= fgets($socket, 1024);
            }

            fclose($socket);
            $vystup = $this->decryptMessage($vystup);

            return $vystup;
       }
    }
    
    
    public function getKategory(){
        $db = $this->getDB();
        $t = $db->modelTable("service_category");
        $result = $t->cmd();
        
        
        
        return $this->output($result,true);
    }
    
    /**
     * _notifyService — previously sent real-time events to api.fullmedia.sk.
     * That external dependency (ex-employee's server) has been removed. No-op stub.
     */
    private function _notifyService($metoda,$data){
        // No-op: external WebSocket relay removed.
        return $this->output(null, true);
    }
    
    
    public function testDeviceAll(){
        
        $data = array();
        /*
        $rest = new service();
        $list = $rest->listService();
        $list = $list["data"];
        $list = array_map(function($item){
            return $item["model"]["id_model"];
        }, $list);
         * 
         */
        
        $db = $this->getDB();
        $sql = "SELECT a.id_model 
        FROM model a
        WHERE a.model = 'service'
        ORDER BY RAND() LIMIT 100";
        
        $db->add_sql($sql, "zoznam");
        $list = $db->cmd();
        $list= $list["zoznam"];
        
        $list = array_map(function($item){
            return $item["id_model"];
        }, $list);
        
        foreach ($list as $value) {
            $rest = new service();
            $rest->parameter= array("kluc"=>$value);
            $data[]=$rest->testDevice();
        }

        
        
        return $this->output($data);
    }
    
    
    public function testDostupnostCentrum(){
        
        $centrum = array(
            array(
                "centrum"=>"Dortmund",
                "host"=>"100.94.62.56"
            ),
            array(
                "centrum"=>"Edmonton2",
                "host"=>"100.69.72.31"
            ),
            array(
                "centrum"=>"Kingston",
                "host"=>"100.73.100.115"
            ),
            array(
                "centrum"=>"Sandiego",
                "host"=>"100.103.113.31"
            ),
            array(
                "centrum"=>"Edmonton1",
                "host"=>"100.102.70.97"
            ),
            array(
                "centrum"=>"Piestany",
                "host"=>"100.94.232.101"
            )
        );
        
        
        $data = array();
        
        
        foreach ($centrum as $value) {
            $d = array(
                "host"=>trim($value["host"]),
                "port"=>80

            );
        
            $result = $this->sendServerData($d,"socket_dotaz");  
            
            $data[]=array("centrum"=>$value["centrum"],"host"=>$value["host"],"result"=>boolval($result));
        }
        
        return $this->output($data);
    }
    
    
    
    public function testDevice(){
        $kluc = @$this->parameter["kluc"];

        
        
        
        $db = $this->getDB();
        $result = $db->getModel($kluc);
        
        if(!$result){
            return $this->output(false,false);
        }
        
        
        $data = array(
            "host"=>trim($result["data"]["host"]),
            "port"=>trim($result["data"]["port"])
            
        );
        
        $result = $this->sendServerData($data,"socket_dotaz");


        
        
        if(is_array($result) && !$result["result"]){
            return $this->output(false,false);
        }
        
        
        $result = boolval($result);
        
        $db = $this->getDB();
                
        
        $db->setModel("testDevice", array(
            "device"=>$kluc,
            "state"=>$result
        ));
        
        $this->_notifyService("testDevice",array("id_model"=>$kluc, "state"=>$result));
        
        return $this->output($result);
    }
    
    
    
    public function getService(){
        $kluc = $this->parameter["kluc"];
        $db = $this->getDB();
        
        $result = $db->getModel($kluc);
        
        $sql = "WITH ranked_data AS (
        SELECT a.id_model, a.cas_create, b.hodnota AS device, if(c.hodnota=1,true,false) AS state,
        ROW_NUMBER() OVER(PARTITION BY b.hodnota ORDER BY a.cas_create DESC) AS rn 
        FROM model a 
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'device' 
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'state' 
        WHERE a.model = 'testDevice' AND b.hodnota = :device )
        SELECT id_model, cas_create, state FROM ranked_data WHERE rn=1";

        $db= $this->getDB();
        $p = $db->add_sql($sql, "zaznam");
        $p->def("device", $kluc);
        
        $sql = "SELECT round(AVG(if(c.hodnota ='', 0,1)) * 100) AS state
        FROM model a
        join model_data b ON a.id_model=b.id_model AND  b.kluc='device'
        join model_data c ON a.id_model=c.id_model AND  c.kluc='state'
        WHERE a.model = 'testDevice' AND b.hodnota=:kluc
        AND a.cas_create > NOW() - INTERVAL 48 HOUR";
        
        $p = $db->add_sql($sql, "dostupnost");
        $p->def("kluc", $kluc);
        
        
        $state = $db->cmd();
        
        $dostupnost = @$state["dostupnost"][0]["state"];
        $state = @$state["zaznam"][0];
        
        
        
        
        
        
        
        if(!$state){
            $state = array(
                "cas_create"=>(new \DateTime())->format("Y-m-d H:i:s"),
                "state"=>0
            );
        }

        $result["dostupnost"]= $state;
        $result["d48"]=$dostupnost;
        
        
        $g = $db->getModel($result["data"]["group"]);
        $result["data"]["group_name"]=$g["data"]["label"];
        
        
        return $this->output($result);
    }
    
    
    public function listService(){
        $db = $this->getDB();
        $t = $db->modelTable("service");
        $result = $t->cmd();
        
        
        
        
        $collator = new \Collator('en_US');
        usort($result, function($a,$b) use($collator){
                $a1=@$a["data"]["label"];
                $b1=@$b["data"]["label"];
                return $collator->compare($a1, $b1);  
        });
        

        return $this->output($result);
    }
    
    
    public function createService(){
        
        $data = $this->parameter;
        if(@$data['validate']){
            $test = new \test\parameter($data);
            foreach ($data['validate'] as $t) {
                $test->addParameter($t['pole'], $t['test']);
            }
            unset($data['validate']);

            $result = $test->test();

            
            
            

            if(!$result['result']){
                return $this->output($result["data"],false);
            }
            
            
        } 

        
        $this->parameter= $data;

        
        
        if(!@$this->parameter["group"]){
            $this->parameter["group"]=null;
        }
        
        if(!@$this->parameter["id"]){
            $this->parameter["id"]= preg_replace("/\s/", "_", $this->parameter["label"]);
        }
        $this->parameter["id"] = trim(strtolower($this->parameter["id"]));
        
        
        $db = $this->getDB();
        $result = $db->overitZaznam("service", array("host"=> $this->parameter["host"],"port"=> $this->parameter["port"]));
        if(!$result){
            $result = $db->setModel("service",$this->parameter);
        }

        $this->_notifyService("createService",$result);
        return $this->output($result);

    }
    
    public function updateService(){
        $data = $this->parameter;
        $id_model = @$data["id_model"];
        if (!$id_model) {
            return $this->output("Missing ID", false);
        }
        
        if(@$data['validate']){
            $test = new \test\parameter($data);
            foreach ($data['validate'] as $t) {
                $test->addParameter($t['pole'], $t['test']);
            }
            unset($data['validate']);

            $result = $test->test();

            if(!$result['result']){
                return $this->output($result["data"],false);
            }
        } 
        
        $this->parameter= $data;
        if(!@$this->parameter["group"]){
            $this->parameter["group"]=null;
        }
        if(!@$this->parameter["id"]){
            $this->parameter["id"]= preg_replace("/\s/", "_", $this->parameter["label"]);
        }
        $this->parameter["id"] = trim(strtolower($this->parameter["id"]));
        
        unset($data["id_model"]);
        
        $db = $this->getDB();
        $db->updateModel($id_model, $data);
        
        $p = $db->add_sql("delete from model_cash where id_model = :kluc", "zapis");
        $p->def("kluc", $id_model);
        $db->cmd();
        
        $this->_notifyService("testDevice", array("id_model" => $id_model));
        
        return $this->output($id_model);
    }
    
    public function deleteService(){
        $data = $this->parameter;
        $id_model = @$data["id_model"];
        if (!$id_model) {
            return $this->output("Missing ID", false);
        }
        
        $db = $this->getDB();
        $db->deleteModel($id_model);
        
        $p = $db->add_sql("delete from model_data where id_model = :kluc", "zaznam_data");
        $p->def("kluc", $id_model);
        
        $p = $db->add_sql("delete from model_cash where id_model = :kluc", "zaznam_cash");
        $p->def("kluc", $id_model);
        
        $db->cmd();
        
        $this->_notifyService("deleteService", $id_model);
        
        return $this->output(true);
    }
    
    
    public function test(){
        
        
        exit;
        
        $mediaServer = "100.73.100.115";
        $x = $this->cameraWebm($mediaServer, 1);
        var_dump($x);
        exit;
        
        
        
       //            "ident"=>"3cbbd752-b524-4bae-81b1-8d1dd499c71c",
        $x =array(
            "ident"=>"3cbbd752-b524-4bae-81b1-8d1dd499c71c",
            "oid"=>1,
            "ot"=>2
        );
        $x = http_build_query($x);
        
        
        //$data = array("url"=>"http://100.94.62.56/q.json?cmd=start-rtmp&ident=3cbbd752-b524-4bae-81b1-8d1dd499c71c&oid=1&ot=2");
        
        //$data = array("url"=>"http://100.94.62.56/q.json?cmd=stop-rtmp&ident=3cbbd752-b524-4bae-81b1-8d1dd499c71c&oid=1&ot=2");
        
        $data = array("url"=>"http://100.94.62.56/q.json?cmd=stop-rtmp&oid=1");
        
        
        $result = $this->sendServerData($data, "get_url");
        //$result = json_decode($result,true);
        //$result = @$result["body"];
        
        //$result = json_decode($result, true);
        
        return $this->output($result);
        
        
       
        
        $data = array("url"=>"http://100.94.62.56/q.json?cmd=getstreamingstatus");
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }

        $result = json_decode($result, true);
        
        $x = $result["body"];
        $x = json_decode($x, true);
        
        return $this->output($x);
        
        
    }
    
    
    private function cameraStatus($mediaServer,$ident,$oid=null){

            $hostOnly = explode(':', $mediaServer)[0];
            $userPass = ($hostOnly === '100.96.237.26') ? 'master:93hdts76@' : '';
            $url = "http://{$userPass}{$mediaServer}/q/getStreamingStatus";

            $data = array("url"=>$url);
            $result = $this->sendServerData($data, "get_url");
            if(is_array($result)){
                return $this->output($result);
            }

            $result = json_decode($result, true);

            $x = $result["body"];
            $x = json_decode($x, true);
            
            if (isset($x["servers"]) && is_array($x["servers"])) {
                foreach ($x["servers"] as &$item) {
                    if (empty($item["ident"]) && !empty($item["type"])) {
                        $item["ident"] = $item["type"];
                    }
                }
            } else {
                return null;
            }

            $v = array_filter($x["servers"], function($item) use($ident){
                return $ident==$item["ident"];
            });

            if(!$v){
                if ($oid !== null) {
                    $cameraList = $this->cameraList($mediaServer);
                    if (isset($cameraList["list"]) && is_array($cameraList["list"])) {
                        $cam = null;
                        foreach ($cameraList["list"] as $cItem) {
                            if ($cItem["id"] == $oid) {
                                $cam = $cItem;
                                break;
                            }
                        }
                        if ($cam !== null && !empty($cam["name"])) {
                            $v = array_filter($x["servers"], function($item) use($cam){
                                return $cam["name"] == $item["type"] || $cam["name"] == $item["ident"];
                            });
                        }
                    }
                }
            }

            if(!$v){
                return null;
            }

            $v = array_values($v);
            $v = $v[0];
            
            $v['all_state']=$x;
            
            return $v;
    }
    
    private function cameraList($mediaServer){
        $hostOnly = explode(':', $mediaServer)[0];
        $userPass = ($hostOnly === '100.96.237.26') ? 'master:93hdts76@' : '';
        $url = "http://{$userPass}{$mediaServer}/q.json?cmd=getObjects";
        $data = array("url"=>$url);  
                
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }
        $result = json_decode($result, true);
        $x = $result["body"];
        $x = json_decode($x, true);
       
        $v = array_filter($x["objectList"], function($item){
            return $item["typeID"]==2;
        });
        
        
        $v = array_values($v);
        
        
        return array(
            "list"=>$v,
            "complete"=>$x
        );
        
    }
    
    
    private function cameraStart($mediaServer,$ident,$oid,$ot){
        $hostOnly = explode(':', $mediaServer)[0];
        if ($hostOnly === '100.96.237.26') {
            $url = "http://master:93hdts76@$mediaServer/q.json?cmd=start-rtmp&oid=$oid&ot=$ot&serverIndex=0";
        } else {
            $url = "http://$mediaServer/q.json?cmd=start-rtmp&ident=$ident&oid=$oid&ot=$ot";
        }

        $data = array("url"=>$url);
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }

        $result = json_decode($result, true);

        $x = $result["body"];
        $x = json_decode($x, true);
        sleep(10);
            
        $x["cmd"]=$url;
        return $x;
    }
    

    private function cameraWebm($mediaServer,$oids,$backColor="rgb(0,0,0)",$size="320x240",$viewIndex=1){

            $url = "http://$mediaServer/video.webm";

            $data = array(
                "oids"=>$oids,
                "backColor"=>$backColor,
                "size"=>$size,
                "viewIndex"=>$viewIndex
            );
            
            $data["url"] = $url."?".http_build_query($data);
            
            
            $result = $this->sendServerData($data, "binary");
            
            return $result;
            
            if(is_array($result)){
                return $this->output($result);
            }

            $result = json_decode($result, true);

            $x = $result["body"];
            $x = json_decode($x, true);
            sleep(10);
            
            $x["cmd"]=$url;
            return $x;
    }
    
    
    public function getCameraObject(){
        $mediaServer = $this->parameter["server"];
        $result = $this->cameraList($mediaServer);
        
        return $this->output($result["list"]);
        
    }
    
    public function getCameraStream(){
        
        
        $mediaServer = $this->parameter["server"];
        $hostOnly = explode(':', $mediaServer)[0];
        $userPass = ($hostOnly === '100.96.237.26') ? 'master:93hdts76@' : '';
        $data = array("url"=>"http://{$userPass}{$mediaServer}/q/getStreamingStatus");
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }
        
        $result = json_decode($result, true);
        $x = $result["body"];
        $x = json_decode($x, true);
        
        if (isset($x["servers"]) && is_array($x["servers"])) {
            foreach ($x["servers"] as &$item) {
                if (empty($item["ident"]) && !empty($item["type"])) {
                    $item["ident"] = $item["type"];
                }
            }
        }
        
        return $this->output($x);
        
    }
    
    

    
    
    
    
    public function camera(){
        $mediaServer = $this->parameter["server"];
        $ident = $this->parameter["stream"];
        $oid= $this->parameter["id"];
        $ot= 2;
        
        

        
        
        $rest = new \service\fnc\service();
        $rest->parameter= array(
            "kluc"=>"ca3fba01-9cd4-11ef-96e2-00163ef25df0"
        );
        $testDostupnost = $rest->testDevice();
        
        
        
        
        $cameraList = $this->cameraList($mediaServer);
        
        
        $c = $this->cameraStatus($mediaServer,$ident,$oid);
        if($c["status"]==false){
            $g = $this->cameraStart($mediaServer,$ident, $oid,$ot);
            return $this->output($g);
            
        }
        
        $status = $this->cameraStatus($mediaServer,$ident,$oid);
        
        
        
        return $this->output(array(
            "parameter"=> $this->parameter,
            "status"=>$status,
            "cameraList"=>$cameraList["list"],
            "objects"=>$cameraList["complete"]
        ));
        
        
        
        
        $mediaServer = "100.73.100.115";
        $ident = "0ceb5290-77b2-4d22-b13c-6018dbdaff74";
       
        
        $mediaServer = "100.73.100.115";
        $ident = "0ceb5290-77b2-4d22-b13c-6018dbdaff74";
        
        
        
        
        
        //https://streaming.48toj6g9v0y978cdn.com:5443/live/streams/king-cam0.m3u8
        
        /*
        $data = array("url"=>"http://$mediaServer/q/getStreamingStatus");
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }
        $result = json_decode($result, true);
        $x = $result["body"];
        $x = json_decode($x, true);
        return $this->output($x);
        */
        
        /*
        $mediaServer = "100.73.100.115";
        $data = array("url"=>"http://$mediaServer/q.json?cmd=getObjects");
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }
        $result = json_decode($result, true);
        $x = $result["body"];
        $x = json_decode($x, true);
         return $this->output($x);
        */ 
         
         
        $url = "http://$mediaServer/q.json?cmd=getObject&oid=1&ot=2";
        $url = "http://$mediaServer/q/getStreamingStatus";
        
        $data = array("url"=>$url);
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }

        $result = json_decode($result, true);
        
        $x = $result["body"];
        $x = json_decode($x, true);
        
        return $this->output($x);
        
        
        /*
        $data = array("url"=>"http://$mediaServer/q.json?cmd=start-rtmp&ident=$ident&oid=1&ot=2");
        $result = $this->sendServerData($data, "get_url");
        if(is_array($result)){
            return $this->output($result);
        }

        $result = json_decode($result, true);
        
        $x = $result["body"];
        $x = json_decode($x, true);
        
        return $this->output($x);
         * 
         */
        
    }
    
    
    
}
