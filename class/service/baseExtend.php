<?php
namespace service;


class baseExtend   {
    public $parameter=null;
    /**
     * @var \db\sql
     */
    public  $db = null;
    public $connectionString = null;
    

    public function setDB(array $setting = array()){
        $this->connectionString=$setting;
    }


    /**
     * @return \app\db\sql
     */
    public function getDB()  {
        $db = $this->db;
        
        if(!$db){
            $db = new \db\sql($this->connectionString);
            $this->db = $db;
        }
        
        return $db;
    }
    
    
    public function getRemoteRest($url, $data){
         $data_string = json_encode(array("data"=>$data));
         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
         curl_setopt($ch, CURLOPT_VERBOSE, false);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',  'Content-Length: ' . strlen($data_string)));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $result = curl_exec($ch);
         curl_close($ch);

         $result = @json_decode($result,true);
        
         return $result; 
    }
    
    
    public function setTemplate($template, $data=null){
        
        if(!defined('sablona')){
            define("sablona", $_SERVER['DOCUMENT_ROOT']."/sablona");
        }
        
        
        if(!$data){
            $data = array("x"=>1);
        }
        
        
        $page = new \page\template();
        $page->loadTemplate($template);
        $page->loadData($data);
        $page->spracuj();
        $vystup =  $page->saveHTML($page->documentElement);
        
        return $vystup;
        
        
    }
    
    

    public function output($data, $result=true){
        $result= array(
            "vstup"=> $this->parameter,
            "result"=>$result,
            "data"=>$data
        );
        return $result;
    }
    
    
    
}
