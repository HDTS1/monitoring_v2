<?php
namespace app;
class cash {
    /**
     * @var \Memcache
     */
    private static $cash = null;
    private static $on = true;
    private static $kluc="monitor_";
    
    
    private static function setMemCache(){
        $host = getenv('MEMCACHED_HOST') ?: 'memcached';
        $port = intval(getenv('MEMCACHED_PORT') ?: 11211);
        $memcache = new \Memcache();
        $memcache->addServer($host, $port);
        self::$cash = $memcache;
    } 
    
    public static function allCacheDelete(){
        if(!self::$cash) self::setMemCache();
        self::$cash->flush();
    }
    
    public static function set($kluc, $data, $expire=600){
        $kluc = self::$kluc.$kluc;
        
        if(!self::$on){
            return false;
        }
        
        if(!self::$cash) self::setMemCache();
        return self::$cash->set($kluc, $data, MEMCACHE_COMPRESSED, $expire);
    }
    
    public static function get($kluc){
        $kluc = self::$kluc.$kluc;
        
        if(!self::$cash) self::setMemCache();
        
        if(!self::$on){
            self::delete($kluc);
            return false;
        }


        $cash =  self::$cash->get($kluc);

        
        return $cash;
    }
    
    public static function delete($kluc, $timeout=0){
        $kluc = self::$kluc.$kluc;
        
        if(!self::$cash) self::setMemCache();
        return self::$cash->delete($kluc, $timeout);
    }
    
}
