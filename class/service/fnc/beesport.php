<?php
namespace service\fnc;
class beesport  extends \service\baseExtend {
    
    public function __construct() {
        $default = array(
            "server"   => getenv('DB_BEESPORT_HOST') ?: getenv('DB_HOST') ?: "db",
            "user"     => getenv('DB_BEESPORT_USER') ?: "",
            "password" => getenv('DB_BEESPORT_PASS') ?: "",
            "databaza" => getenv('DB_BEESPORT_NAME') ?: "",
            "port"     => intval(getenv('DB_BEESPORT_PORT') ?: getenv('DB_PORT') ?: 3306),
            "charset"  => "utf8"
        );
        
        $this->connectionString = $default;
    }
    
    
    
    public function db(){
        $db = $this->getDB();
        
        $db->add_sql("select * from information_schema.TABLES where table_schema='ts8kpx0x' and table_type='BASE TABLE' order by TABLE_NAME", "zoznam");
        $result = $db->cmd();
        
        $result = $result["zoznam"];
        
        $result = array_map(function($item){
            $db = $this->getDB();
            
            $p = $db->add_sql("select * from information_schema.COLUMNS where table_schema='ts8kpx0x' and table_name= :table_name", "zoznam");
            $p->def("table_name", $item["TABLE_NAME"]);

            
            $p = $db->add_sql("select * from information_schema.KEY_COLUMN_USAGE where table_schema='ts8kpx0x' and table_name= :table_name", "vazba");
            $p->def("table_name", $item["TABLE_NAME"]);
            
            $result = $db->cmd();
            
            
            $item["COLUMNS"]=$result["zoznam"];
            $item["VAZBA"]=$result["vazba"];
            
            
            return $item;
        }, $result);
        
        
        
        
        
        return $this->output($result);
    }
    
    
}
