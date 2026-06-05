<?php
namespace app;

class arrayObject {
    private $pole = null;
    private $parent = null;
    private $child= array();
    private $root = null;
    
    private function listChild(){
        $child =array();
        if(is_array($this->pole)){
            foreach ($this->pole as $key => &$value) {
                $x = new \app\arrayObject($value, $this, $this->root);
                $child[$key]= $x;
            }
        }
        return $child;
    }

    public function __construct(&$pole, $parent=null, &$root=null) {
        $this->pole= &$pole;
        $this->parent=$parent;
        $this->root = $root;
        if(!$root){
            $this->root= $this;
        }

    }
    
    
    private function _listVetva($el,&$zoznam, $path=""){
        if(is_array($el)){
            
            if(!empty($path)){
                $path= $path.".";
            }
            
            foreach ($el as $key => $value) {
                $kluc = $path.$key;
                $this->_listVetva($value, $zoznam,$kluc);
            }
        } else {
            if(empty($path)) $path="_";
            $zoznam[$path]= $el;
        }
    }
    
    public function listVetva(){
        $zoznam = array();
        $this->_listVetva($this->pole, $zoznam);
        return $zoznam;
    }

    

    /**
     * @return \app\arrayObject
     */
    public function getParent(){
        return $this->parent;
    }
    
    /**
     * @param string $path
     * @return \app\arrayObject
     */
    public function getPath($path){
        $path = trim($path);
        $x = $this;
        
        if(preg_match("/^@/", $path)){
            $x= $this->root;
            $path = preg_replace("/^@/", "", $path);
        }
        
        
        if(preg_match("/^\*/", $path)){
            $x= $this;
            $path = preg_replace("/^\*/", "", $path);
        }
        
        
        if(preg_match("/^\.+/", $path, $match)){
            $path = preg_replace("/^\.+/", "", $path);
            $p = str_split($match[0]);
            foreach ($p as $v) {
                $x = $x->getParent();
            }
        }
        
        if(empty(trim($path))){
            return $x;
        }
        
        

        
        $path = explode(".", $path);
        foreach ($path as $kluc) {
            
            $l = $x->listChild();
            $obj = @$l[$kluc];
                        
            if(!$obj){
                $x = null;
                return $obj;
            } else {
                $x = $l[$kluc];
            }
            
        }

        $result = $x;
        return $result;

    }
    
    public function getChilds(){
        $childs =array();
        if(is_array($this->pole)){
            foreach ($this->pole as $key => &$value) {
                $x = new \app\arrayObject($value, $this, $this->root);
                $childs[$key]= $x;
            }
        }
        return $childs;
    }
    
    
    public function setValue($value){
        $this->pole=$value;
    }
    
    public function getValue(){
        return $this->pole;
    }
    
}
