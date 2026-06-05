<?php
namespace test;
class parameter {
    private $data;
    private $test;
    
    public function __construct($data) {
        $this->data=$data;
    }
    
    
    public function addParameter($pole,$fnc,$popis=null){
        $this->test[] = array(
            "pole"=>$pole,
            "fnc"=>$fnc,
            "popis"=>$popis
        );
    }
    
    public function addValidateData($data){
        if(!$data){
            return false;
        }
        
        foreach ($data as $key => $value) {
            if($value['test']){
                foreach ($value['fnc'] as $metoda) {
                    $this->addParameter($key, $metoda,$value['error']);
                }
            }
        }


        
    }
    
    
    
    
    public function test(){
        
        if(!$this->test || !$this->data){
            $result = array(
                "data"=>[],
                "result"=>true
            );
            return $result;
        }
        
        
        $result = array(
            "data"=>[],
            "result"=>true
        );
        
        
        
        $testValue = \db\array_model::code($this->data);
        
        
        
        $t = new \test\fnc();
        $t->data = $this->data;
        
        foreach ($this->test as $test) {
            $metoda = $test['fnc'];
            $v = @$testValue[$test['pole']];
            
            
            if(method_exists($t, $metoda)){
                $r = $t->$metoda($v);
                if(!$r){
                    $result['result']=false;
                }
                
                $result['data'][] = array(
                    "pole"=>$test['pole'],
                    "result"=>$r,
                    "error"=>$test['popis']
                );                
            } else {
                $result['data'][] = array(
                    "pole"=>$test['pole'],
                    "result"=>false,
                    "error"=>"nie je testovacia metoda: ".$metoda
                );
                
            }
            
            
        }
       
        
        return $result;
    }
    
}
