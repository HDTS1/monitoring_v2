<?php
namespace handler;
class bind {
    private static $event = array();
    
    
    public static function CMD($kluc, $data){
        $event = array_filter(self::$event, function($item) use($kluc){
            return $item["kluc"]==$kluc;
        });
        
        
        foreach ($event as $value) {
            $value["function"]($data);
        }
        
    }
    
    public static function register($kluc, $function, $replace=false){
        self::$event[]= array(
            "kluc"=>$kluc,
            "function"=>$function
        );
    }
}
