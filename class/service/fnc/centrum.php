<?php
namespace service\fnc;
class centrum extends \service\baseExtend {
    private $defaultRange = 7;
    
    

    public function setPasmo(){
        $db = $this->getDB();
        $p = $db->add_sql("SET time_zone = :pasmo", "pasmo");
        $p->def("pasmo", $this->parameter["pasmo"]);
        $p = $db->add_sql("select now() as cas", "cas");
        
        $result =  $db->cmd();
        $result = $result["cas"][0];
        
        
        return $this->output($result);
        
    }
        
        
        
    
    /**
     * _notifyService — previously sent real-time events to api.fullmedia.sk.
     * That external dependency (ex-employee's server) has been removed. No-op stub.
     */
    private function _notifyService($metoda,$data){
        // No-op: external WebSocket relay removed.
        return $this->output(null, true);
    }

    
    public function casove_pasmo(){
        $timezones = \DateTimeZone::listIdentifiers();
        
        $datetime = new \DateTime();
        
        $timezones = array_map(function($item) use($datetime) {
            $x = array(
                "value"=>$item,
                "label"=>"(".$datetime->setTimezone(new \DateTimeZone($item))->format("H:i:s").")  ".$item
            );
            
            return $x;
        }, $timezones);
        
        
        return $this->output($timezones);
    }
    
    
    
    public function getTimeZone(){

        

        $datetime = new \DateTime('now', new \DateTimeZone('Europe/Bratislava'));
        $datetime->setTimezone(new \DateTimeZone('America/Denver'));
        

        
        $vystup = array(
            "zona"=>$datetime->getTimezone(),
            "time"=>$datetime->format("Y-m-d H:i:s")
                );
        
        return $this->output($vystup);
        
    }
    
    
    
    
    public function createCentrum(){

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
        $this->parameter["valid"]=1;
        
        
        $db = $this->getDB();
        $result = $db->overitZaznam("centrum", array("label"=> $this->parameter["label"]));
        if(!$result){
            $result = $db->setModel("centrum",$this->parameter);
        }

        $this->_notifyService("createCentrum",$result);
        return $this->output($result);

    }

    
    public function statistic_centrum_plc(){
        $source = $this->parameter["kluc"];
        $centrum = $this->getCentrumUser();
        $centrum = $centrum["data"];
        
        if(!@$centrum["plc"]){
            return $this->output(null);
        }
        
        
        
        $data = $centrum;
        
        $from = @$this->parameter["from"];
        $to = @$this->parameter["to"];
        
        if(!@$this->parameter["from"] || !@$this->parameter["to"]){
            $to = (new \DateTime())->format("Y-m-d");
            $from = (new \DateTime())->modify((($this->defaultRange-1) * -1)." day")->format("Y-m-d");
        }
        
        
        foreach ($data["plc"] as &$value) {
            $rest = new \plc\data();
            $value["data"]=$rest->runStatistic($value["plc"], $from, $to);

        }
        
        
        return $this->output($data);
        
    }
    
    public function getTeplota(){
        $teplota_hour = -1;
        
        
        $source = $this->parameter["kluc"];
        $centrum = $this->getCentrumUser();
        $centrum = $centrum["data"];

        
        
        if(!@$centrum["papago"]){
            return $this->output(null);
        }
        
        
        $from = new \DateTime();
        $to= (new \DateTime())->format("Y-m-d H:i:s");
        $from = $from->modify($teplota_hour." day")->format("Y-m-d H:i:s");
        
        $rest = new \service\fnc\monitor();
        $rest->parameter = array(
            "name"=>$centrum["papago"],
            "from"=>$from,
            "to"=>$to
        );

        

        $result = $rest->dataGrafTeplota();
        $result = $result["data"];
       
        
        $result["range"]= array(
            "from"=>$from,
            "to"=>$to
        );
        
        return $this->output($result);
    }
    
    
    
    public function last_centrum_plc(){
        $source = $this->parameter["kluc"];
        $centrum = $this->getCentrumUser();
        $centrum = $centrum["data"];
        
        
        if(!@$centrum["plc"]){
            return $this->output(null);
        }
        
        $data = $centrum;
        
        
        
        foreach ($data["plc"] as &$value) {
            $rest = new \plc\data();
            $value["data"]=$rest->lastRun($value["plc"]);

        }
        
        
        
        return $this->output($data);
    }
    
    
    
    public function getCentrumPLC(){
        $source = $this->parameter["kluc"];
        $centrum = $this->getCentrumUser();
        $centrum = $centrum["data"];
        
        if(!@$centrum["plc"]){
            return $this->output(null);
        }
        
        
        
        $from = @$this->parameter["from"];
        $to = @$this->parameter["to"];
        
        if(!@$this->parameter["from"] || !@$this->parameter["to"]){
            $to = (new \DateTime())->format("Y-m-d");
            $from = (new \DateTime())->modify((($this->defaultRange-1) * -1)." day")->format("Y-m-d");
        }
        
        

        $data = $centrum;

        foreach ($data["plc"] as &$value) {
            $rest = new \plc\data();
            $d = $rest->getCycle($value["plc"], $from, $to);
            $value["data"]=$d;
            
        }
        
        
        
        
        return $this->output($data);
        
    }
    
    
    public function getCentrumUser(){
            $kluc = $this->parameter["kluc"];
            $db = $this->getDB();
            $result = $db->getModel($kluc);
            

            
            

            $centrum = @$result["data"];
            if(!$centrum){
                echo "Neplatny kluc";
                exit;
            }
            
            
            

            if(!@$this->parameter["from"] || !@$this->parameter["to"]){
                $to = (new \DateTime())->format("Y-m-d");
                $from = (new \DateTime())->modify((($this->defaultRange-1) * -1)." day")->format("Y-m-d");
            }
        
            
            $data = $centrum;
            $data["range"]=array(
                    "from"=>$from,
                    "to"=>$to
            );
            $data["id_model"]=$kluc;
            
            
            
            $data1 = array(
                "id_model"=>$kluc,
                "label"=>"PLC.US.SDI1.",
                "plc"=>[
                    array(
                        "label"=>"HST 1",
                        "plc"=>"PLC.US.SDI1."
                    ),
                    array(
                        "label"=>"HST 2",
                        "plc"=>"PLC.US.SDI2."
                    )
                ],
                "papago1" => "T.CZ.PR.1",
                "camera1"=>array(
                    "server"=>"100.73.100.115",
                    "stream"=>"0ceb5290-77b2-4d22-b13c-6018dbdaff74",
                    "id"=>6
                ),
                "range"=>array(
                    "from"=>$from,
                    "to"=>$to
                )
            );
           


            return $this->output($data);
    }
    
    
    
    public function getCentrum(){
        $kluc = $this->parameter["kluc"];
        $db = $this->getDB();
        
        $result = $db->getModel($kluc);
        return $this->output($result);
    }

    public function listCentrum(){
        $db = $this->getDB();
        $t = $db->modelTable("centrum");
        $t->addFilter("valid", 1);
        $t->setData();
        $result = $t->cmd();
        

        $collator = new \Collator('en_US');
        usort($result, function($a,$b) use($collator){
                $a1=@$a["data"]["label"];
                $b1=@$b["data"]["label"];
                return $collator->compare($a1, $b1);  
        });


        return $this->output($result);
    }
    
    
    public function nahlad(){
        $ip = @$this->parameter["ip"];
        $id = @$this->parameter["id"];

        $server = new \app\camera($ip);
        $img = $server->nahlad($id);
        return $this->output($img);
    }
    
    
    public function getCameraImage(){
        $ip = @$this->parameter["ip"];
        $id = @$this->parameter["id"];
        $server = new \app\camera($ip);
        
        $img = $server->getPhoto($id);
        //$img = $server->nahlad($id);
        
        return $this->output($img);
        
        
    }
    
    
    public function list_camera(){
        $ip = @$this->parameter["ip"];
        if(!$ip){
            return $this->output(false,false);
        }

        $server = new \app\camera($ip);
        $result = $server->getObjects();
        
        
        $data = array();
        
        foreach ($result["objectList"] as $value) {
            if($value["typeID"]==2){
                $data[]=$value;
            }
        }
        
        
        return $this->output($data);

        
    }
    
    
    private function getAwsSignatureV4($accessKey, $secretKey, $region, $service, $request, $bucket, $params=[]) {
        date_default_timezone_set('UTC');
        $host = "$bucket.s3.$region.wasabisys.com";
        $amzDate = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');

        $canonicalUri = "/";
        $canonicalQuerystring = http_build_query($params);
        $canonicalHeaders = "host:$host\nx-amz-content-sha256:UNSIGNED-PAYLOAD\nx-amz-date:$amzDate\n";
        $signedHeaders = "host;x-amz-content-sha256;x-amz-date";
        $payloadHash = "UNSIGNED-PAYLOAD";
        $canonicalRequest = "$request\n$canonicalUri\n$canonicalQuerystring\n$canonicalHeaders\n$signedHeaders\n$payloadHash";

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "$date/$region/$service/aws4_request";
        $stringToSign = "$algorithm\n$amzDate\n$credentialScope\n" . hash('sha256', $canonicalRequest);

        // Generate signing key
        $kSecret = 'AWS4' . $secretKey;
        $kDate = hash_hmac('sha256', $date, $kSecret, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "$algorithm Credential=$accessKey/$credentialScope, SignedHeaders=$signedHeaders, Signature=$signature";

        return [
            'authorizationHeader' => $authorizationHeader,
            'amzDate' => $amzDate,
        ];
    }
   
    private function listObjects($accessKey, $secretKey, $bucket, $region = 'eu-central-2') {
        
        
       $params = array(
           'list-type'=>2,
           'max-keys'=>5000,
           'prefix'=>'Agent/video/C.DE.DOR'

       ); 
        

       
       
       $query = "?".http_build_query($params); 
        
        
       $endpoint = "https://".$bucket.".s3.".$region.".wasabisys.com".$query;
       $service = 's3';

       // Vygeneruj podpis
       $signature = $this->getAwsSignatureV4($accessKey, $secretKey, $region, $service, 'GET', $bucket,$params);
       


       // Nastav HTTP hlavičky
       $headers = [
           "Authorization: " . $signature['authorizationHeader'],
           "x-amz-date: " . $signature['amzDate'],
           "x-amz-content-sha256: UNSIGNED-PAYLOAD"
       ];

       // Vykonaj GET požiadavku pomocou cURL
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $endpoint);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

       $response = curl_exec($ch);
       

       
       
       if ($response === false) {
           echo 'Error: ' . curl_error($ch);
       } else {
           
           
           return $response;
           
       }

       curl_close($ch);
       
   }
    

   
    private function downloadFileFromWasabi($bucket, $filePath, $localFileName, $accessKey, $secretKey) {
        $endpoint = 'https://s3.eu-central-2.wasabisys.com'; // URL pre Wasabi S3
        $url = "$endpoint/$bucket/$filePath";

        // Vytvorenie hlavičky pre autorizáciu
        $date = gmdate('D, d M Y H:i:s T');
        $signature = base64_encode(hash_hmac('sha1', "GET\n\n\n{$date}\n/$bucket/$filePath", $secretKey, true));
        $authHeader = "Authorization: AWS $accessKey:$signature";

        // Inicializácia cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Host: s3.wasabisys.com',
            $authHeader,
            "Date: $date"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, fopen($localFileName, 'w'));

        // Spustenie cURL požiadavky
        $response = curl_exec($ch);
        if ($response === false) {
            echo 'cURL Error: ' . curl_error($ch);
        } else {
            echo "File downloaded successfully to $localFileName";
        }

        // Zatvorenie cURL
        curl_close($ch);
    }


    
    
   public function test1(){
        $bucket    = getenv('WASABI_BUCKET') ?: 'ispyconnect-recordings';
        $filePath  = 'Agent/video/C.DE.DOR.CAM0/1_2024-10-29_15-53-40_581.mp4';
        $accessKey = getenv('WASABI_ACCESS_KEY') ?: '';
        $secretKey = getenv('WASABI_SECRET_KEY') ?: '';
        $localFileName = root.'/test.mp4';

        echo $this->downloadFileFromWasabi($bucket, $filePath, $localFileName, $accessKey, $secretKey);
        exit;
   }

   
   
   public function setCentrum(){
       $kluc = $this->parameter["id_model"];
       $data = $this->parameter["data"];
       $data["valid"]=1;

       $db = $this->getDB();
       $db->updateModel($kluc, $data, true);
       $result = $db->getModel($this->parameter["id_model"]);
       return $this->output($result);
   }
   
   
   public function listTeplomer(){
       $sql="SELECT distinct b.hodnota as name
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='name'
        WHERE a.model='papago'
        ORDER BY b.hodnota
        ";
       
       $db = $this->getDB();
       $db->add_sql($sql, "zoznam");
       $result = $db->cmd();
       $result = $result["zoznam"];
       
       
       return $this->output($result);
       
   }
   
   public function listVideoCentrum(){
       $sql="SELECT a.id_model, b.hodnota AS label, d.hodnota AS `host`
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='group'
        JOIN model_data d ON a.id_model=d.id_model AND d.kluc='host'
        WHERE a.model = 'service' AND c.hodnota='e037e669-a1bc-11ef-96e2-00163ef25df0'
        ORDER BY b.hodnota
        ";
       
       $db = $this->getDB();
       $db->add_sql($sql, "zoznam");
       $result = $db->cmd();
       $result = $result["zoznam"];
       
       
       return $this->output($result);
   }
   
   
   
   public function listHST(){
       $sql="SELECT distinct b.hodnota as name
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='label'
        WHERE a.model='plc'
        ORDER BY b.hodnota
        ";
       
       $db = $this->getDB();
       $db->add_sql($sql, "zoznam");
       $result = $db->cmd();
       $result = $result["zoznam"];
       
       
       return $this->output($result);
       
   }
   
   
   
   
   public function test(){
       
       $c = new \app\camera("100.94.232.101");
       $result = $c->test();
       
       
       var_dump($result);
       exit;
       
       
       
       
        $cash = \app\cash::get("file_video");
        if($cash){
            return $this->output($cash);
        }
        
        
        
       
       
        // Prístupové údaje
        $accessKey = getenv('WASABI_ACCESS_KEY') ?: '';
        $secretKey = getenv('WASABI_SECRET_KEY') ?: '';
        $bucket = getenv('WASABI_BUCKET') ?: 'ispyconnect-recordings';
        $key = "Agent/video/C.DE.DOR.CAM0/1_2024-10-29_15-50-54_589.mp4";
        

        $obsah = $this->listObjects($accessKey, $secretKey, $bucket);
        
        
        
        
        $dom = new \DOMDocument();
        $dom->loadXML($obsah);
        
        
        $dataRoot = simplexml_import_dom($dom->documentElement);
        $json = json_encode($dataRoot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |  JSON_UNESCAPED_SLASHES);
        $json = json_decode($json);
        
        \app\cash::set("file_video", $json, 300);
        return $this->output($json);
       
   }
   
    

   
   
   
}
