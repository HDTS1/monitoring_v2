<?php
namespace test;
class fnc {
    public $data;
    
    public function test_array(){
      return false;  
        
    }
    
  
    public function link($value){
       if(empty(trim($value))) {
           return false;
       } else {
           $db = new \db\sql();
           $db->connect();
           $sql = "select count(*) as pocet from harley_page where link= :page";
           $p = $db->add_sql($sql, "zaznam");
           $p->def("page",trim($value));
           $result = $db->cmd();
           
           
           if($result["zaznam"][0]['pocet']>0){
               return false;
           }
           
           
           return true;
       }
    }    
    
    
    public function  gdpr($value){
        if($value!="yes"){
            return false;
        } else {
            return true;
        }        
    }
    
    
    public function no99($value){
        if($value==99){
            return false;
        } else {
            return true;
        }
    }
    
    public function vin($value){
       $value= trim($value);
       if(empty(trim($value))) {
           return false;
       } else {
           if(strlen($value)!=17){
               return false;
           }
           
           return true;
       }
    }
    
    public function hodnota($value){
       if(empty(trim($value))) {
           return false;
       } else {
           return true;
       }
    }


    
    public function email($value){
         if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }         
    }
    
    public function number($value){
        
        
        if (is_numeric($value)) {
            return true;
        } else {
            return false;
        }        
        
    }    
    
    
    public function cislo($value){

        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return true;
        } else {
            return false;
        }        
        
    }    

    
    public function datum_sk($value){
        $value = trim($value);
        $value = preg_replace("/\s/", "", $value);
        
        $format = 'j.n.Y';
        $d = \DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) == $value;

    }
    
    public function integer($value){

        if (filter_var($value, FILTER_VALIDATE_INT)) {
            return true;
        } else {
            return false;
        }        
        
    }
    
    
    
    
}
