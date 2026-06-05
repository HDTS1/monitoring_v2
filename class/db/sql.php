<?php

namespace db;

class parameter {

    private $parameter = array();

    public function def($parameter, $value, $type = \PDO::PARAM_STR) {
        $this->parameter[$parameter] = array(
            "parameter" => $parameter,
            "value" => $value,
            "type" => $type
        );
    }
    
    public function listParameter(){
        return $this->parameter;
    }
    

}

class sql {
    private $db;
    private $sql = array();
    
    private function dbConnect($connection = null){
        
        $file = root."/cfg/.default_connection.json";
        $source_default = array();
        
        if(file_exists($file)){
            $source_default = file_get_contents($file);
            $source_default = openssl_decrypt($source_default, 'aes-256-cbc', "Palo999");
            $source_default = json_decode($source_default, true);
        }

        // Environment variables take the highest priority for Docker deployments.
        $env_default = array();
        if(getenv('DB_HOST'))     $env_default['server']   = getenv('DB_HOST');
        if(getenv('DB_USER'))     $env_default['user']     = getenv('DB_USER');
        if(getenv('DB_PASS'))     $env_default['password'] = getenv('DB_PASS');
        if(getenv('DB_NAME'))     $env_default['databaza'] = getenv('DB_NAME');
        if(getenv('DB_PORT'))     $env_default['port']     = intval(getenv('DB_PORT'));

        $default = array(
            "server"   => "db",
            "user"     => "hdts_user",
            "password" => "",
            "databaza" => "hdts_monitor",
            "port"     => 3306,
            "charset"  => "utf8mb4",
            "timezone" => "'Europe/Bratislava'"
        );
        
        
        $default = array_replace_recursive($default, $source_default);
        $default = array_replace_recursive($default, $env_default);
        
        if($connection){
            $connection = array_replace_recursive($default, $connection);

        } else {
           $connection = $default; 
        }
        
        $charset="utf8";
        $dbs = $connection;
        if(@$dbs["charset"]){
            $charset = $dbs["charset"];
        }
        
        
        try{    
            @$db = new \PDO('mysql:host='.$dbs['server'].';port='.$dbs['port'].';dbname='.$dbs['databaza'].';charset='.$charset, $dbs['user'], $dbs['password']);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
        }catch (\PDOException $e){
             $message= $e->getMessage();
             die ($e->getMessage());
        }
        
        
        return $db;
    }
    
    
    public function __construct($connection = null) {
        $db = $this->dbConnect($connection);
        $this->db= $db;
        return $this;
    }
 
    public function requestSQL($val){

        $sql = $val["sql"];
        $pdo = $this->db;
        
        if(is_string($pdo)){
            return $pdo;
        }
        $stmt = $pdo->prepare($sql);

        if(!@$val["parameter"]){
            $val["parameter"]=array();
        }
        
        
        foreach ($val["parameter"]->listParameter() as  $dValue) {
            
            
            if(preg_match("/\:".$dValue["parameter"]."/", $sql)){
                $stmt->bindValue(":".$dValue["parameter"],$dValue["value"], $dValue["type"]);
            }                        
        }

        
        try {
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch(\PDOException $e) {
            return $e->getMessage();
        }
    }    
    
    
    public function add_sql($sql,$key){
        $p = new parameter();
        $this->sql[$key] = array(
            "sql" => $sql,
            "parameter" => $p
        );

        return $p;
    }
    
    public function cmd(){
        $result = array();
        
        
        foreach ($this->sql as $key => $value) {
            $result[$key]= $this->requestSQL($value);
        }

        $this->sql= array();
        return $result;
    }
    
    public function setModel($model, $data){
        $dm = new \db\dbModel($this);
        $kluc = $dm->setModel($model, $data);
        return $kluc;
    }

    public function deleteModel($kluc){
        $dm = new \db\dbModel($this);
        $dm->deleteModel($kluc);

    }
    
    public function updateModel($kluc, $data, $rewrite=false){
        $dm = new \db\dbModel($this);
        $result = $dm->updateModel($kluc, $data, $rewrite);
        return $result;
        
    }
    
    
    public function getModel($kluc){
        
        $dm = new \db\dbModel($this);
        $result = $dm->getModel($kluc);
        
        return $result;
    }
    
    public function modelTable($table){
        $dm = new \db\dbModel($this);
        $t = $dm->setTable($table);
        return $t;
    }
    
    
    public function overitZaznam($model, $filter=null){
        $dm = new \db\dbModel($this);
        $t = $dm->setTable($model);
        if($filter && is_array($filter)){
            foreach ($filter as $key => $value) {
                $t->addFilter($key, $value);
            }
        }
        
        $result = $t->cmd();
        $result = @$result[0]["id_model"];
        
        
        
        
        return $result;
    }
    
    
    
    
    
}
