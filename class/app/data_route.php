<?php
namespace app;
class data_route {
    private static $route =null;
    
    public static function setRoute($data){
        self::$route=$data;
    }
    
    public static function getRoute(){
        return self::$route;
    }
}
