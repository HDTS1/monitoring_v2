<?php
namespace app;
class translate   {
    private $api_key;
    private $server="https://api-free.deepl.com/v2/translate";
    private $lang =null;

    public function __construct() {
        $this->api_key = getenv('DEEPL_API_KEY') ?: '';
    }
    
    public function listLang(){
        
         
        $dm = new \app\dbModel();
        $t = $dm->setTable("lang_list");
        $t->setData();
        $t->setFilter("active", 1);
        $result = $dm->getZoznam();
        
        $rand = rand(0 ,20);
        
        
        if(@$result[0] && $rand !=5 ){
            return $result[0]["data"]["data"];
        }
        
        
         $url = "https://api-free.deepl.com/v2/languages?type=target"; 
         //$data_string = json_encode($d );

         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
         curl_setopt($ch, CURLOPT_VERBOSE, true);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         //curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             
             'Authorization: DeepL-Auth-Key '.$this->api_key 
             ));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = @json_decode($result,true);
        
        $data = array(
            "active"=>1,
            "data"=>$result
        );

        
        $dm = new \app\dbModel();
        $dm->setDataModel("lang_list", $data);
        
        
        return $result;
    }  
    
    
    public function translate ($text, $lang){


        if(isset($_GET["lang"])){
            $this->lang= $_GET["lang"];
            $_SESSION["lang"]=$_GET["lang"];
        }

        
        $lang =@$_SESSION["lang"];
        if($lang){
            $this->lang= $lang;
        }
        
        
        
        if(!$lang && @$_SERVER["HTTP_ACCEPT_LANGUAGE"] && !$this->lang){
        
            $parse = function($vstup){
                $vstup = preg_replace("/q=/", "", $vstup);
                return floatval($vstup);
            };
            
            $z=array();
            
            $l = explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            foreach ($l as $value) {
                
                $_langArray= explode(";", trim($value));
                $z[]= array(
                    "lang"=>$_langArray[0],
                    "q"=>!@$_langArray[1] ? 0 : $parse($_langArray[1])
                );
                
                
                
            }

            usort($z,function($a,$b){
                return $a["q"]> $b["q"];
            });

            
            $listLang = $this->listLang();
            $listLang= array_map(function($item){
                return $item["language"];
            }, $listLang);
            
            
            $this->lang="EN";
            foreach ($z as $value) {
                if(in_array(strtoupper($value["lang"]) , $listLang)){
                    $this->lang = strtoupper($value["lang"]);
                    break;
                } 
            }
           
        }
        
        
        
        
        if(!is_array($text)){
            $text= array($text);
        }
        
         $d= array(
             "text"=>$text,
             "target_lang"=> $this->lang,
             "source_lang"=> "SK"
             );

        $target = $d["target_lang"]; 
        $preklad = array(
            "translations"=>array()
        );
        
        $db = $this->db();
        $sql = "select * from cashLang where id_cash= :id_cash and lang = :lang";
        for ($index = 0; $index < count($d["text"]); $index++) {
            $kluc = md5($d["text"][$index]);
            $p = $db->add_sql($sql, "zaznam");
            $p->def("id_cash", $kluc);
            $p->def("lang", $target);
            $result = $db->cmd();
            
            $preklad["translations"][$index]= array(
                "text"=>@$result["zaznam"][0]["result"],
                "detected_source_language"=>"SK"
            );
            
        }

        $test = array_filter($preklad["translations"], function($item){
            return $item["text"]==null;
        });
        
        
        if(count($test)==0){
            return $preklad;
        }      
        
         
         
         $url = $this->server; 
         $data_string = json_encode($d );

         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
         curl_setopt($ch, CURLOPT_VERBOSE, true);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             'Content-Type:application/json',  'Content-Length: ' . strlen($data_string),
             'Authorization: DeepL-Auth-Key '.$this->api_key 
             ));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = @json_decode($result,true);

        
        
        $sql = "insert into cashLang(id_cash, lang,result) values(:id_cash, :lang, :result) ON DUPLICATE KEY UPDATE result = :result";
        
        for ($index = 0; $index < count($d["text"]); $index++) {
            $kluc = md5($d["text"][$index]);
            $preklad = $result["translations"][$index]["text"];
            $p = $db->add_sql($sql, "k".$kluc);
            $p->def("id_cash", $kluc);
            $p->def("lang", $target);
            $p->def("result", $preklad);
        }
        
        $db->cmd(); 
        
        return $result;
    }
    
    
    /**
     * 
     * @return \db\sql
     */
    private static function db(){
        
        return \rest\connectDB::db();
        /*
        $db = new \db\sql();
        $db->connect();
        return $db;
         * 
         */
    }
    
}
