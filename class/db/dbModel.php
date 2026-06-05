<?php
namespace db;

class dbFilter{
    private $filter=null;
    private $parent=null;
    private $table = null;
    private $setData=false;
    
    public function __construct(dbModel $parent, $table) {
        $this->parent=$parent;
        $this->table=$table;
    }
    
    public function addFilter($kluc, $value, $regex=false){
        $this->filter[]= array(
            "kluc"=>$kluc,
            "value"=>$value
        );
    }
    
    
    public function setData(){
        $this->setData=true;
    }
    
    
    
    public function cmd(){
        if(!$this->filter){
            return $this->parent->getTable($this->table);
        }
        
        $db = $this->parent->getDB();
        $sql[] = "select distinct a.id_model from model a";
        
        foreach ($this->filter as $key => $value) {
            $x = "JOIN model_data _alias_ ON a.id_model=_alias_.id_model AND _alias_.kluc='_kluc_' AND _alias_.hodnota = '_value_'";
            
            $sql[] = preg_replace(["/_kluc_/","/_value_/","/_alias_/"], [$value["kluc"],$value["value"], "x".$key], $x);
        }
        
        $sql[] = "where model = :model";
        
        
        $sql = implode(" ", $sql);
        //return $sql;

        
        
        
        $p = $db->add_sql($sql, "zoznam");
        $p->def("model", $this->table);
        
        $result = $db->cmd();
        
        $result = $result["zoznam"];
        
        if($this->setData){
            $result = array_map(function($item){
                return $this->parent->getModel($item["id_model"]);
            }, $result);
        }
        
        return $result ;
        
        
    }
    
    
    
}

class dbModel {
    /**
     * @var \db\sql
     */
    private $db;
    
    
    public function __construct($db=null) {
        if(!$db){
            $this->db = new \db\sql();
        } else {
            $this->db = $db;
        }
    }
    
    
    public function getDB(){
        return $this->db;
    }
    
    public function setTable($table){
        $x = new dbFilter($this,$table);
        return $x;

    }
    
    
    public function setModel($model,$data){
        $data = \db\array_model::code($data);
        $db = $this->db;
        $db->add_sql("set @kluc= uuid()", "set");
        $p = $db->add_sql("insert into model(id_model,model) values(@kluc, :model)", "zapis_model");
        $p->def("model", $model);
        foreach ($data as $key => $value) {
           $p = $db->add_sql("insert into model_data(id_model,kluc,hodnota) values(@kluc, :kluc, :hodnota)", "zapis_model".$key); 
           $p->def("kluc", $key);
           $p->def("hodnota", $value);
        }
        $db->add_sql("select @kluc as kluc", "kluc");
        $result = $db->cmd();
        return $result["kluc"][0]["kluc"];
    }
    
    public function getTable($table, $data=false){
        $db = $this->db;

        $p = $db->add_sql("select id_model from model where model = :table", "zoznam");
        $p->def("table", $table);
        $result = $db->cmd();
        $result = $result["zoznam"];
        if(!$result) $result=array();
        
        $result = array_map(function($item){
            return $this->getModel($item["id_model"]);
        }, $result);
        
        return $result;
    }
    
    
    public function deleteModel($kluc){
        $db = $this->db;
        $p = $db->add_sql("delete from model where id_model = :kluc", "zaznam");
        $p->def("kluc", $kluc);
        $db->cmd();
    }
    
    public function updateModel($kluc, $data, $rewrite=false){
        $db = $this->db;

        
        if($rewrite==true){
            $sql = "delete FROM model_data WHERE id_model= :kluc";
            $p = $db->add_sql($sql, "delete");
            $p->def("kluc", $kluc);
            $db->cmd();
        }
        
        $result = $db->getModel($kluc);
        if(!$result){
            return false;
        }
        
        
        $data= array_replace_recursive($result["data"], $data);
        
        
        $dm = new \db\dbModel($this->db);
        $result = $dm->getModel($kluc);
        $data= array_replace_recursive($result["data"], $data);        
        unset($data["kluc"]);
        
        $data = \db\array_model::code($data);
        

        
        foreach ($data as $key => $value) {
           $p = $db->add_sql("replace into model_data(id_model,kluc,hodnota) values(:id_model, :kluc, :hodnota)", "zapis_model".$key); 
           $p->def("kluc", $key);
           $p->def("hodnota", $value);
           $p->def("id_model", $kluc);
        }
        

        $result = $db->cmd();
        
        return $kluc;

    }
    
    
    
    
    public function getModel($kluc){
        
        $db = $this->db;
        $p = $db->add_sql("select * from model_cash where id_model = :kluc", "zaznam");
        $p->def("kluc", $kluc);
        
        $cash = $db->cmd();
        $cash = @$cash["zaznam"][0]["data"];
        //$cash= null;
        
        
        if(!$cash){
            $p = $db->add_sql("select id_model,model,cas_create, cas_update from model where id_model = :kluc", "model");
            $p->def("kluc", $kluc);
            $p = $db->add_sql("select kluc,hodnota from model_data where id_model = :kluc", "model_data");
            $p->def("kluc", $kluc);
            $result = $db->cmd();
            
            if(!@$result["model"][0]){
                return null;
            }

            
            $data = array();
            foreach ($result["model_data"] as $value) {
                $data[$value["kluc"]]=$value["hodnota"];
            }
            
            
            $data = \db\array_model::decode($data);
            
            
            $cash["model"] = $result["model"][0];
            $cash["data"] = $data;
            
            $p = $db->add_sql("replace into model_cash(id_model,data) values(:kluc, :data)", "zapis");
            $p->def("kluc", $kluc);
            $p->def("data", base64_encode(json_encode($cash)));
            $db->cmd();
            
            
            return $cash;
            
            
        }
        
        
        $cash = json_decode(base64_decode($cash),true);
        return $cash;
    }
    
    
    

}
