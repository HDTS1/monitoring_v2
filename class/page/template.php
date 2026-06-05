<?php
namespace page;

class fnc   {
    /**
     * 
     * @param \DOMNode $elTest
     */
    private $elTest=null;
    /**
     * @var \app\arrayObject 
     */
    private $arrayObject;
    /**
     *
     * @var \DOMXPath 
     */
    private $xPath;
    
    private function _setEval($path, $data=null ){
        if(!$path){
            return null;
        }
        $x =array();
        $path = preg_replace("/\./", "']['", $path);
        $path = "['".$path."']";
        eval('$x'.$path.'=$data;');
        return $x;
    }    
    
    private function createBlock(\DOMNode $el){
        
        $page = new \page\template();
        $page->loadXML("<root/>");
        $page->setArrayObject($this->arrayObject);
        $new = $page->importNode($el, true);
        $page->documentElement->appendChild($new);
        $page->spracuj();
        $fragment = $page->createDocumentFragment();
        foreach ($page->documentElement->childNodes as $childNode) {
          $fragment->appendChild($childNode->cloneNode(TRUE));
        }
        

        $result = $this->elTest->ownerDocument->importNode($fragment, true);
        return $result;
        
    }
    
    
    private function parseData($value){
        if(preg_match_all("/\{\{(?<pole>.+?)\}\}/s", $value, $match)){
                $pole = $match[1];
                $pole = array_unique($pole);
                
                foreach ($pole as $v) {
                    $vq = preg_quote($v);
                    $o = $this->arrayObject->getPath($v);
                    if($o){
                       $h = $o->getValue(); 
                    } else {
                        $h =null;
                    }
                    if(is_array($h)){
                        $h = json_encode($h, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |  JSON_UNESCAPED_SLASHES);
                    }
                    
                    $value = preg_replace("/\{\{".$vq."\}\}/", $h, $value);
                }
        }
        return $value;
    }
    
    
    
    private function getElementFromXpath($path):\DOMNode | null {
        $list = $this->xPath->evaluate($path, $this->elTest);
        return @$list[0];
    }
    
    public function __construct(\DOMNode $elTest,\app\arrayObject $array) {
     
        $this->elTest = $elTest;
        $this->arrayObject = $array;
        $this->xPath = new \DOMXPath($this->elTest->ownerDocument);
    }
    
    public function nodeClear(){
        $fragment = $this->elTest->ownerDocument->createDocumentFragment();
        $this->elTest->parentNode->replaceChild($fragment, $this->elTest);
        return true;

    }

    public function getPodmienka(){
        $data = $this->elTest->getAttribute("data");
        

        
        $dataObj = $this->arrayObject->getPath($data);
        if(!$dataObj){
            $data = null;
        } else {
            $data = $dataObj->getValue();
        }
     
        $podmienka = $this->getElementFromXpath("./podmienka");
        $value = $podmienka->nodeValue;
        $value = $this->parseData($value);
        $value = trim($value);


        
        
        if(!is_numeric($data)){
            if(is_array($data)){
                $data = "'array'";
            } else {
                $data = "'".$data."'";
            }
            
            
        }
        
        

        
        
        $value = preg_replace("/^value/", $data, $value);
        eval("\$value = ".$value.';');

        $result = $value;
        $value = $value ? "true" : "false";
        $objectEL = $this->getElementFromXpath("./*[@result='".$value."']");
        if($objectEL){
            $objectEL->removeAttribute("result");
            $newElement = $this->createBlock($objectEL);
            $this->elTest->parentNode->replaceChild($newElement, $this->elTest);
            return true;
        }
        
        
        $this->elTest->removeChild($podmienka);
        $fragment = $this->elTest->ownerDocument->createDocumentFragment();
        
        if($result){
            foreach ($this->elTest->childNodes as $childNode) {
              $fragment->appendChild($childNode->cloneNode(TRUE));
            }
            $newElement = $this->createBlock($fragment);
            $this->elTest->parentNode->replaceChild($newElement, $this->elTest);
            return true;
        }
        
        $this->elTest->parentNode->replaceChild($fragment, $this->elTest);
        return true;

    }
    
    
    public function renameNode($tagName){
            /*
            $dom = new DOMDocument();
            $dom->loadXml($xml);
            $xpath = new DOMXPath($dom);

            $nodeId = 'c000002'; 
            $nodes = $xpath->evaluate("//COMMUNITY[@ID='$nodeid']/URLS");

            // we change the document, iterate the nodes backwards
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
              $node = $nodes->item($i);
              // create the new node
              $newNode = $dom->createElement('URL_BACKUP');
              // copy all children to the new node
              foreach ($node->childNodes as $childNode) {
                $newNode->appendChild($childNode->cloneNode(TRUE));
              }
              // replace the node
              $node->parentNode->replaceChild($newNode, $node);
            }

            echo $dom->saveXml();
            */
        
            $newNode = $this->elTest->ownerDocument->createElement($tagName);
            foreach ($this->elTest->childNodes as $childNode) {
                $newNode->appendChild($childNode->cloneNode(TRUE));
            }
            $this->elTest->parentNode->replaceChild($newNode, $this->elTest);
            return true;
    }

    
}

class template extends \DOMDocument {
    /**
     * @var \app\arrayObject 
     */
    private $arrayObject;
    
    /**
     * @var \app\arrayObject 
     */
    private $arraySelectObject=null;
    private $pathRest = "\\service\\fnc\\";
    private $_baseURI = null;
    
    
    private function _setEval($data, $path){
        if(!$path){
            return null;
        }
        $x =array();
        $path = preg_replace("/\./", "']['", $path);
        $path = "['".$path."']";
        eval('$x'.$path.'=$data;');
        return $x;
    }
    
    public function __construct(string $version = "1.0", string $encoding = "") {
        if(!defined("sablona")){
            $cfg_user = file_get_contents(root."/cfg/page.json");
            $cfg_user = json_decode($cfg_user,true);
            define("sablona", _root.$cfg_user["template_path"]);
        }
        
        
        return parent::__construct($version, $encoding);
    }
    
    
    public function setArrayObject(\app\arrayObject $arrayObject, $path=null){
        if($path){
            $this->arrayObject = $arrayObject->getPath($path);
            return true;
        }
        
        $this->arrayObject = $arrayObject;
    }
    
    
    
    private function parseData($value){
        if(preg_match_all("/\{\{(?<pole>.+?)\}\}/s", $value, $match)){
                $pole = $match[1];
                $pole = array_unique($pole);
                
                foreach ($pole as $v) {
                    $vq = preg_quote($v);
                    $o = $this->arrayObject->getPath($v);
                    if($o){
                       $h = $o->getValue(); 
                    } else {
                        $h =null;
                    }
                    
                    
                    if(is_array($h)){
                        $h = json_encode($h, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |  JSON_UNESCAPED_SLASHES);
                    }
                    
                    $value = preg_replace("/\{\{".$vq."\}\}/", $h, $value);
                }
                
                
        }
        return $value;
            
    }
    
    private function node_number(\DOMNode $elTest){
        $source = $elTest->nodeValue;
        $source = $this->parseData($source);
        $source = floatval($source);
        
        $format = "#,##0.00";
        $defFormat = $elTest->getAttribute("format");
        if($defFormat && !empty($defFormat)){
            $format= $defFormat;
        }
        
        
        
        $fmt = new \NumberFormatter( 'sk_SK', \NumberFormatter::DECIMAL, $format );
        //$fmt->setAttribute(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '.');
        $fmt->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '.');
        //$fmt->setPattern($format);
        $value =  $fmt->format($source);
        
        $el = $elTest->ownerDocument->createTextNode(@$value);
        $elTest->parentNode->replaceChild($el, $elTest);
    }    
    
    
    private function node_date(\DOMNode $elTest){
        $source = $elTest->nodeValue;
        $source = $this->parseData($source);
        $format = $elTest->getAttribute("format");
        
        $d = new \DateTime($source);
        $preklad = $d->format($format);
        
        $el = $elTest->ownerDocument->createTextNode(@$preklad);
        $elTest->parentNode->replaceChild($el, $elTest);
    }
    
    
    private function node_access(\DOMNode $elTest){
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $append = true;
        
        $role = $elTest->getAttribute("role");
        $trieda = $elTest->getAttribute("trieda");
        
        if($elTest->getAttribute("append")){
            $x = $elTest->getAttribute("append");
            if($x=="false"){
                $append = false;
            }
        }
        
        
        
        
        
        $rest = new \rest\fnc\iis();
        $user = $rest->getUserRole();
        $user = $user["data"]["role"];
        
        

        
        if(intval($role)<= $user){
            
           
            $element = $elTest;
            if($element->hasChildNodes()){
                 /* @var $node \DOMElement */
                $list = $element->childNodes;
                foreach ($list as $node) {
                    /*
                     if ($node->nodeType == XML_ELEMENT_NODE && $node->nodeName=='false') {
                         continue;
                     }
                    */
                    $this->spracujBod($node);
                    if($append==true){
                        $fragment->append($node->cloneNode(true));
                    }
                }
            } 
            
            
        }
        
        
        $elTest->parentNode->replaceChild($fragment, $elTest);
        return true;
    }
    
    
    private function node_getAccess(\DOMNode $elTest){
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $source =$elTest->getAttribute("template");
        $metoda_trieda = $elTest->getAttribute("metoda");
        $obj = $this->arrayObject;
        
        $trieda = $metoda_trieda;
        list($trieda,$metoda) = explode(".", $trieda);
        $trieda =  "\\rest\\fnc\\".$trieda;
        
        if(!class_exists($trieda)){
            $fragment = $elTest->ownerDocument->createDocumentFragment();
            $fragment->appendXML("Nie je trieda: ". $trieda);
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;              
        }

        $class = new $trieda();
        
        if(!method_exists($class, $metoda)){
            $fragment = $elTest->ownerDocument->createDocumentFragment();
            $fragment->appendXML("Nie je metoda: ". $trieda."::".$metoda);
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;              
        }
        
        
        /*
         * 
         * Tu by som mohol vyskusat parametre
        if(is_array(@$data['parameter'])){
            array_walk_recursive($data['parameter'], function(&$item, $key){
                $item= $this->parseData($item);
            });
        }
        
        $class->parameter = @$data['parameter'];
         */
        
        
        $result = $class->$metoda();

        
        if($result){
            if($elTest->hasChildNodes()){
                 /* @var $node \DOMElement */
                $list = $elTest->childNodes;
                foreach ($list as $node) {
                    $this->spracujBod($node);
                    $fragment->append($node->cloneNode(true));
                }
            } 
            
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;
        }
        
        
        $page = new \page\template();
        $page->setArrayObject($obj);
        $page->loadTemplate($source);
        $page->spracuj();
        
        if($page->documentElement->nodeName=='root'){
            if($page->documentElement->hasChildNodes()){
                 /* @var $node \DOMElement */
                $list = $page->documentElement->childNodes;
                foreach ($list as $node) {
                    $n = $elTest->ownerDocument->importNode($node, true);
                    $fragment->append($n);
                }
            }
        } else {
            $n = $elTest->ownerDocument->importNode($page->documentElement, true);
            $fragment->append($n);
        }
        
        
        $elTest->parentNode->replaceChild($fragment, $elTest);
        return true;


    }
    
    
    
    
    private function node_component(\DOMNode $elTest){
        
        $s = simplexml_import_dom($elTest);
        $jsonString = json_encode($s);
        $data_component = json_decode($jsonString,true);
        
        
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $source =$elTest->getAttribute("source");
        $data_path = $elTest->getAttribute("data");
        
        $obj = $this->arrayObject;
        
        
        if($elTest->getAttribute("debug")==1){
            $obj = $obj->getPath($data_path) ;
            var_dump($obj);
            exit;
        }
        

        if($data_path){
           $obj = $obj->getPath($data_path) ;

           if(!$obj){
                $elTest->parentNode->replaceChild($fragment, $elTest);
                return true;
           }
        }

        $v = $obj->getValue();
        unset($data_component["@attributes"]);
        
        if(!$data_component){
            $data_component= null;
        }
        
        if($data_component) {
            array_walk_recursive($data_component, function(&$value, $key){
                $x = $this->parseData($value);
                
                
                $value= $x;
            });
        }
        
        $v["_component"]= $data_component;
        $obj->setValue($v);        
        
 

        /*
        if($elTest->getAttribute("debug")==1){
            var_dump($source);
            exit;
        }
        */
        
        $page = new \page\template();
        $page->setArrayObject($obj);
        $page->loadTemplate($source);
        $page->spracuj();
 

        
        
        if($page->documentElement->nodeName=='root'){
            if($page->documentElement->hasChildNodes()){
                 /* @var $node \DOMElement */
                $list = $page->documentElement->childNodes;
                foreach ($list as $node) {
                    $n = $elTest->ownerDocument->importNode($node, true);
                    $fragment->append($n);
                }
            }
        } else {
            $n = $elTest->ownerDocument->importNode($page->documentElement, true);
            $fragment->append($n);
        }
        
        
        $elTest->parentNode->replaceChild($fragment, $elTest);
        return true;
        
    }
    
    
    private function node_getRest(\DOMElement $elTest){

        
        $s = simplexml_import_dom($elTest);
        $jsonString = json_encode($s);
        $data = json_decode($jsonString,true);

        
        $trieda = @$data['trieda'];
        if($trieda){
            $trieda = $this->parseData($trieda);
        }
        
        
        
        list($trieda,$metoda) = explode(".", $trieda);
        $trieda = $this->pathRest.$trieda;

        
        if(!class_exists($trieda)){
            $fragment = $elTest->ownerDocument->createDocumentFragment();
            $fragment->appendXML("Nie je trieda: ". $trieda);
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;              
        }

        $class = new $trieda();
        
        if(!method_exists($class, $metoda)){
            $fragment = $elTest->ownerDocument->createDocumentFragment();
            $fragment->appendXML("Nie je metoda: ". $trieda."::".$metoda);
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;              
        }
        
        
        if($elTest->getAttribute("debug")==1){
            var_dump($this->arrayObject->getPath("@")->getValue());
            exit;
        }
        
        
        if(is_array(@$data['parameter'])){
            array_walk_recursive($data['parameter'], function(&$item, $key){
                $item= $this->parseData($item);
            });
        }
        
        

        
        
        $class->parameter = @$data['parameter'];
        $result = $class->$metoda();
        

        
        
        if($result["result"]==true){
            $dataObj = $this->arrayObject->getValue();
            $zapis = $result["data"];
            
            $kluc = @$data["@attributes"]["node"];

            
            if($kluc){
                $zapis = $this->_setEval($zapis, $kluc);

            }
            
            
            $dataObj= array_replace_recursive($dataObj, $zapis);
            $this->arrayObject->setValue($dataObj);
            
        }
        
        
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragment, $elTest);
        
    }    
    
    private function buildFragmentIF( \DOMNode $element, $false = false){
                $fragment = $element->ownerDocument->createDocumentFragment();
                
                if($false==true){
                    $list = $element->getElementsByTagName("false");
                    $p = @$list->item(0);
                    if(!$p){
                        return $fragment;
                    }
                    
                    $element = $p;
                }
                
                
                if($element->hasChildNodes()){
                     /* @var $node \DOMElement */
                    $list = $element->childNodes;
                    foreach ($list as $node) {
                         if ($node->nodeType == XML_ELEMENT_NODE && $node->nodeName=='false') {
                             continue;
                         }
                        
                        $this->spracujBod($node);
                        $fragment->append($node->cloneNode(true));
                    }
                } 
                
                return $fragment;

    }
    
    
    private function node_if(\DOMNode $elTest){

        $data_path = $elTest->getAttribute("data");
        $obj_data = $this->arrayObject->getPath($data_path);
        
        if(!$obj_data || !$obj_data->getValue()){
            $fragment = $this->buildFragmentIF($elTest, true);
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;            
        }
        
        
        $fragment = $this->buildFragmentIF($elTest);
        $elTest->parentNode->replaceChild($fragment, $elTest);
        return true; 
    }
    
    
    private function node_node(\DOMNode $elTest){
        $node = $elTest->getAttribute("node");
        $data = $elTest->getAttribute("data");

        $obj = $this->arrayObject->getPath($node);
        $data = $this->parseData($data);
        $objData = $this->arrayObject->getPath($data);
        $valueData = $objData ? $objData->getValue() : array();
        
        $obj->setValue($valueData);
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragment, $elTest);
        return true; 
    }
    
    
    private function node_for(\DOMNode $elTest){
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        
        $data_path = $elTest->getAttribute("data");
        $obj_data = $this->arrayObject->getPath($data_path);
        if(!$obj_data){
            $elTest->parentNode->replaceChild($fragment, $elTest);
            return true;            
            echo "Error path: ".$data_path;
            exit;
        }
 
        
        
        foreach ($obj_data->getChilds() as $key => $val) {
                $doc = new \page\template();
                $doc->loadXML("<root/>");
                
                
                
                $doc->setArrayObject($val);
                $root = $doc->documentElement;

                if($elTest->hasChildNodes()){
                     /* @var $node \DOMElement */
                    $list = $elTest->childNodes;
                    foreach ($list as $node) {
                        $n = $doc->importNode($node, true);
                        $root->appendChild($n);
                    }
                }        
                $doc->spracuj();


                if($doc->documentElement->nodeName=='root'){
                    if($doc->documentElement->hasChildNodes()){
                         /* @var $node \DOMElement */
                        $list = $doc->documentElement->childNodes;
                        foreach ($list as $node) {
                            $n = $elTest->ownerDocument->importNode($node, true);
                            $fragment->append($n);
                        }
                    }
                }
        
        }
        
        //$fragment = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragment, $elTest);
        
        return true;
        
    }
    
    private function node_test(\DOMNode $elTest){
        $x = new \page\fnc($elTest, $this->arrayObject);
        return $x->getPodmienka();
    }
    
    
    private function node_getUser(\DOMNode $elTest){
        $s = simplexml_import_dom($elTest);
        $jsonString = json_encode($s);
        $data = json_decode($jsonString,true);
        
        $session = new \app\session();
        $result = $session->getSession();

        
        if(!@$result["user"]){
            header_remove();
            header("Location:".$data["link"]);
            exit;
        }
        
        $zapis["session"]=$result;
        
        $parenData = $this->arrayObject->getValue();
        $parenData = array_replace_recursive($parenData, $zapis);
        $this->arrayObject->setValue($parenData);
        
            
        $fragmentClear = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragmentClear, $elTest);
        
        return true;
        
    }
    
    
    
    private function node_getSession(\DOMNode $elTest){

        $session = new \app\session();
        $result = $session->getSession();
        
        $zapis["session"]=$result;
        
        $parenData = $this->arrayObject->getValue();
        $parenData = array_replace_recursive($parenData, $zapis);
        $this->arrayObject->setValue($parenData);
        
        $fragmentClear = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragmentClear, $elTest);
        
        return true;
    }
    
    
    private function node_setSession(\DOMNode $elTest){
        
        $s = simplexml_import_dom($elTest);
        $jsonString = json_encode($s);
        $data = json_decode($jsonString,true);
        
        $session = new \app\session();
        $session->setSession($data);
        
        
        $fragmentClear = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragmentClear, $elTest);
        
        return true;
    }
    
    
    private function node_setting(\DOMNode $elTest){

        
        
        $s = simplexml_import_dom($elTest);
        $jsonString = json_encode($s);
        $data = json_decode($jsonString,true);

        $kluc = @$data["@attributes"]["node"];
        unset($data["@attributes"]);
        
        $d[$kluc]= $data;
        
        $parenData = $this->arrayObject->getValue();
        $parenData = array_replace_recursive($parenData, $d);
        
        array_walk_recursive($parenData, function(&$item, $key){
            $item= $this->parseData($item);
        });
        
        
        $this->arrayObject->setValue($parenData);
        
        
        $fragmentClear = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragmentClear, $elTest);
        
        return true;
        
        
    }
    
    
    private function node_seo(\DOMNode $elTest){
        $source =$elTest->getAttribute("source");
        $data_path = $elTest->getAttribute("data");
        $page = new \page\template();
        $obj = $this->arrayObject->getPath($data_path);
        $server = $obj->getPath("server");
        $server->setValue($_SERVER["SERVER_NAME"]);
        
        
        $page->setArrayObject($obj);
        $page->loadTemplate($source);
        $page->spracuj();
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $fragmentClear = $elTest->ownerDocument->createDocumentFragment();

        if($page->documentElement->nodeName=='root'){
            if($page->documentElement->hasChildNodes()){
                 /* @var $node \DOMElement */
                $list = $page->documentElement->childNodes;
                foreach ($list as $node) {
                    $n = $elTest->ownerDocument->importNode($node, true);
                    $fragment->append($n);
                }
            }
        }
        
        $xpath = new \DOMXPath($this);
        $list = $xpath->query("/html/head/seo_place");
        $head = $list->item(0);
        $head->parentNode->replaceChild($fragment,$head);
        $elTest->parentNode->replaceChild($fragmentClear, $elTest);
        
        return true;
        
    }
    
    private function copySource($source){
        
        
        if(!file_exists($source) && @\app\cfg::$conf["source"]){
            $src = str_replace(root, "", $source);
            $src =  root.\app\cfg::$conf["source"].$src;
            $dest = root.str_replace(root, "", $source);

            if(file_exists($src)){
                $dir = dirname($dest);
                if(!@dir($dir)){
                    mkdir($dir,0777,true);
                }
                
                $source_file = file_get_contents($src);
                file_put_contents($dest, $source_file);
            }
        }

    }
    
    
    private function node_link(\DOMNode $elTest){
        
        if($elTest->getAttribute("cmd")=='base64'){

            $source = @$elTest->getAttribute("href");
            if(preg_match("/^\/sablona/", $source)){
                $source = preg_replace("/^\/sablona/", sablona, $source);
            } else {
                $source= root.$source;
            }
            
            

            
            if($source){
                if(preg_match("/^\/.*$/", $source)){
                    //$this->copySource(root.$source);
                    $source = file_get_contents($source);
                } else {
                    
                    $dir = dirname($this->_baseURI);
                    $file = $dir."/".$source;
                    $this->copySource(root.$source);
                    
                    if(file_exists($file)){
                        $source = file_get_contents($file);
                    } else {
                        $source = file_get_contents($source);
                    }

                }
                
                
                
            } else {

                $source = $elTest->nodeValue;
                $source =($this->parseData($source)); // htmlspecialchars
                $elTest->nodeValue='';

            }

            $source = $this->parseData($source);
            
            $source = preg_replace(
              array('/\s*(\w)\s*{\s*/','/\s*(\S*:)(\s*)([^;]*)(\s|\n)*;(\n|\s)*/','/\n/','/\s*}\s*/'), 
              array('$1{ ','$1$3;',"",'} '),
              $source
            );            
            $source = base64_encode($source);
            $hlavicka = "data:text/css;base64,";            

            $elTest->setAttribute("rel", "stylesheet");
            $elTest->setAttribute("href", $hlavicka.$source ); 
            $elTest->removeAttribute("cmd");
            
            return true;



        }
        
        return false;
    }  
    
    
    
    private function node_script(\DOMNode $elTest){
        

        if($elTest->getAttribute("cmd")=='base64'){

            $source = @$elTest->getAttribute("src");
            
            if($source){
                if(preg_match("/^\/.*$/", $source)){
                    $this->copySource(root.$source);
                    $_key = md5(root.$source);
                    $src = \app\cash::get($_key);
                    if($src){
                        $source = $src;
                    } else {
                        $source = file_get_contents(root.$source);
                        \app\cash::set($_key, $source, 60*60);

                    }
                    
                } else {
                    
                    $dir = dirname($this->_baseURI);
                    $file = $dir."/".$source;
                    $this->copySource($file);
                    if(file_exists($file)){

                        $_key = md5($file);
                        $src = \app\cash::get($_key);
                        if($src){
                            $source = $src;
                        } else {
                            $source = file_get_contents($file);
                            \app\cash::set($_key, $source, 60*60);

                        }
                        
                        
                    } else {
                        
                        $_key = md5($source);
                        $src = \app\cash::get($_key);
                        if($src){
                            $source = $src;
                        } else {
                            $source = file_get_contents($source);
                            \app\cash::set($_key, $source, 60*60);

                        }
                        
                        
                        
                    }

                }
            } else {

                $source = $elTest->nodeValue;
                $source =($this->parseData($source)); // htmlspecialchars
                $elTest->nodeValue='';

            }

            

            

            $pattern = '/{{\((?P<fnc>[^)]+)\)(?P<value>[^}}]+)}}/';
            preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);
            $zoznam=array();
            foreach ($matches as $match) {
                $zoznam[$match['value']]=array(
                    "fnc"=>$match['fnc'],
                    "pole"=>$match['value'],
                    "string"=>$match[0]
                );
            }
            
            foreach ($zoznam as $val) {
                list($trieda,$metoda) = explode(".", $val["fnc"]);
                $trieda = "\\rest\\fnc\\".$trieda;
                $rest = new $trieda();
                $rest->parameter = array(
                    "value"=>$val["pole"],
                    "key"=>true
                );
                $result = $rest->$metoda();
                $hodnota = $result["data"];
                
                
                $source = preg_replace('/'.preg_quote($val["string"]).'/', $hodnota,$source);
            }
            
            

            
            
            // $source = \PHPWee\Minify::js($source);
            $source = base64_encode($source);
            $hlavicka = "data:application/javascript;base64,";

            $elTest->setAttribute("src",$hlavicka.$source);
            $elTest->removeAttribute("cmd");
            $elTest->removeAttribute("name");   
            
            return true;

        }
        
        return false;
    }        
    
    private function node_style(\DOMElement $elTest){
        
        if($elTest->getAttribute("cmd")=='base64'){
            
            $source = @$elTest->getAttribute("href");
            
            if($source){
                if(preg_match("/^\/.*$/", $source)){
                    
                    $_key = md5(root.$source);
                    $src = \app\cash::get($_key);
                    if($src){
                        $source = $src;
                    } else {
                        $source = file_get_contents(root.$source);
                        \app\cash::set($_key, $source, 60*60);

                    }

                    
                } else {
                    $dir = dirname($this->_baseURI);
                    $file = $dir."/".$source;
                    if(file_exists($file)){
                        $_key = md5($file);
                        $src = \app\cash::get($_key);
                        if($src){
                            $source = $src;

                        } else {
                            $source = file_get_contents($file);
                            \app\cash::set($_key, $source, 60*60);
                            
                        }

                    } else {
                        $_key = md5($source);
                        $src = \app\cash::get($_key);
                        if($src){
                            $source = $src;
                        } else {
                            $source = file_get_contents($source);
                            \app\cash::set($_key, $source, 60*60);
                            
                        }

                    }
                }
            } else {

                $source = $elTest->nodeValue;
                $elTest->nodeValue='';

            }            
            
            $source = $this->parseData($source);
            
            $source = preg_replace(
              array('/\s*(\w)\s*{\s*/','/\s*(\S*:)(\s*)([^;]*)(\s|\n)*;(\n|\s)*/','/\n/','/\s*}\s*/'), 
              array('$1{ ','$1$3;',"",'} '),
              $source
            );            
            $source = base64_encode($source);
            $hlavicka = "data:text/css;base64,";            

            $el = $elTest->ownerDocument->createElement("link");
            $el->setAttribute("rel", "stylesheet");
            $el->setAttribute("href", $hlavicka.$source ); 
            
            $elTest->parentNode->replaceChild($el, $elTest); 
            
            return true;
        }
        
        return false;
    }
    
    
    
    public function loadData($data, $path=null){
        $x = new \app\arrayObject($data);
        $this->arrayObject=$x;
        if($path){
            $this->arraySelectObject = $x->getPath($path);
            return true;
        }
        $this->arraySelectObject = $x;
    } 
    
    public function loadTemplate($source){
        
        $source = $source.".xhtml";
        $template = sablona.$source;
        

        

        if(!file_exists($template)){
            echo "Problem: ".$template;
            exit;
        }

        
        $kluc = md5($template);
        $obsah = \app\cash::get($kluc);
        
        
        if(!$obsah){
            $obsah = file_get_contents($template);
             \app\cash::set($kluc, $obsah);
        } 
        
        
        
        
        $this->_baseURI = $template;
        $this->loadXML($obsah);
        //$this->load($template);
        

        
        
    }
   

    private function after_preklad(\DOMNode $elTest){

        $xpath = new \DOMXPath($elTest->ownerDocument);
        /* @var $list \DOMNodeList */
        $list = $xpath->query("//*[@preklad='1']");
        foreach ($list as $key => $node) {
            if($node->nodeType == XML_ELEMENT_NODE){
                $node->removeAttribute("preklad");
            } 
        }
        
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragment, $elTest);
        
        return true;
        
        $buffer = array();
        $i=0;
        
        $lang = $elTest->getAttribute("lang");
        $xpath = new \DOMXPath($elTest->ownerDocument);
        /* @var $list \DOMNodeList */
        $list = $xpath->query("//*[@preklad='1']");

        
        
        if($list->length==0){
            return false;
        }
        
        /* @var $value \DOMNode */
        foreach ($list as $key => $value) {
            $x = array(
                "el"=>$value,
                "text"=>$value->nodeValue
            );
            $buffer[$i]=$x;
            $i++;
        
            $attr = $xpath->query("@*",$value);
            /* @var $value \DOMNode */
            foreach ($attr as $key_v => $attr_value) {
                if(preg_match("/^p@.+/", $attr_value->nodeValue)){
                    $attr_value->nodeValue = preg_replace("/^p@/", "", $attr_value->nodeValue);
                    $x = array(
                        "el"=>$attr_value,
                        "text"=>$attr_value->nodeValue
                    );
                    $buffer[$i]=$x;
                    $i++;
                }
            }

            
            
        };
        
        
        $text = array_map(function($item){
            return $item["text"];
            
        }, $buffer);
        
        
        $app = new \app\translate();
        $result = $app->translate($text, $lang);
        
        
        foreach ($result["translations"] as $key => $value) {
            /* @var $node \DOMNode */
            $node = $buffer[$key]["el"];
            $node->nodeValue = htmlentities($value["text"]);
            
            if($node->nodeType == XML_ELEMENT_NODE){
                $node->removeAttribute("preklad");
            } 
            
            if($node->nodeType == XML_ATTRIBUTE_NODE ){
                //$node->removeAttribute("preklad");
                //var_dump($node);
                //exit;
            }             
            
            
            
        }
        

        
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragment, $elTest);
    }
    
    
    private function after_xpath(\DOMNode $elTest){
        $path = $elTest->getAttribute("path");
        $xpath = new \DOMXPath($elTest->ownerDocument);
        /* @var $list \DOMNodeList */
        $list = $xpath->query($path);
        if($list->length==0){
            return false;
        }
        
        $elSource = $list->item(0);
        
        $data_path = $elTest->getAttribute("data");
        $obj = $this->arrayObject;
        
        
        if($elTest->getAttribute("debug")==1){
            $obj = $obj->getPath($data_path) ;
            var_dump($obj);
            exit;
        }
        

        if($data_path){
           $obj = $obj->getPath($data_path) ;
           if(!$obj){

           }
        }
        
        
        
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $doc = new \page\template();
        $doc->loadXML("<root/>");



        $doc->setArrayObject($obj);
        $root = $doc->documentElement;

        if($elTest->hasChildNodes()){
             /* @var $node \DOMElement */
            $list = $elTest->childNodes;
            foreach ($list as $node) {
                $n = $doc->importNode($node, true);
                $root->appendChild($n);
            }
        }        
        $doc->spracuj();


        if($doc->documentElement->nodeName=='root'){
            if($doc->documentElement->hasChildNodes()){
                 /* @var $node \DOMElement */
                $list = $doc->documentElement->childNodes;
                foreach ($list as $node) {
                    $n = $elTest->ownerDocument->importNode($node, true);
                    $fragment->append($n);
                }
            }
        }

        $elSource->appendChild($fragment);
        $fragment = $elTest->ownerDocument->createDocumentFragment();
        $elTest->parentNode->replaceChild($fragment, $elTest);
        
    }
    
    
    
    private function after_component(){
        $xpath = new \DOMXPath($this);
        $list = $xpath->query("//xpath | //preklad");
        
        /* @var $el \DOMElement */
        foreach ($list as $el) {
            $metoda = "after_".$el->nodeName;
            if(method_exists($this, $metoda)){
                $this->$metoda($el);
            }
        }

 
    }
    
    
    private function setValue(){

        
        $xpath = new \DOMXPath($this);
        $list = $xpath->query("//*[@value]");
        
        /* @var $itemValue \DOMNode */
        foreach ($list as $itemValue) {
            
            if($itemValue->nodeName=="select"){
                $valSelect = $itemValue->getAttribute("value");
                $options = $xpath->query(".//option[@value='".$valSelect."']", $itemValue);
                
                /* @var $ss \DOMElement */
                foreach ($options as $ss) {
                    $ss->setAttribute("selected", "selected");
                    $itemValue->removeAttribute("value");
                }


            }
            
            if($itemValue->nodeName=="textarea"){
                
                
                
                $valSelect = $itemValue->getAttribute("value");
                $valSelect = empty($valSelect) ? " " : $valSelect;
                
                $itemValue->nodeValue= $valSelect;
                $itemValue->removeAttribute("value");

            }            
            
            if($itemValue->nodeName=="input" && $itemValue->getAttribute('type')=='checkbox'){
                $valSelect = $itemValue->getAttribute("value");
                if($valSelect=="true"){
                    $itemValue->setAttribute('checked','checked');
                }

            }               
            
            
            if($itemValue->nodeName=="div" && $itemValue->getAttribute('contenteditable')=="true"){
                $valSelect = $itemValue->getAttribute("value");
                $valSelect = empty($valSelect) ? " " : $valSelect;
                $html = $itemValue->ownerDocument->createCDATASection($valSelect);
                $itemValue->appendChild($html);
                $itemValue->removeAttribute("value");
            }             

            
            
            if($itemValue->nodeName=="input" && $itemValue->getAttribute('type')=='date'){
                $valSelect = $itemValue->getAttribute("value");
                if($valSelect=="now"){
                    $d = new \DateTime();
                    $cdatum = $d->format("Y-m-d");
                    $itemValue->setAttribute('value',$cdatum);
                }
                
                $valSelect = $itemValue->getAttribute("min");
                if($valSelect=="now"){
                    $d = new \DateTime();
                    $cdatum = $d->format("Y-m-d");
                    $itemValue->setAttribute('min',$cdatum);
                }                
                
                $valSelect = $itemValue->getAttribute("max");
                if($valSelect){
                    $d = new \DateTime();
                    $d->add(new \DateInterval('P1M'));
                    $cdatum = $d->format("Y-m-d");
                    $itemValue->setAttribute('max',$cdatum);
                }                  
                

            }               
            
        }
        
        
        $this->after_component();


        
    }    
    
    
    
    public function spracujBod(\DOMNode $elTest = null){
        //if(!$elTest) $elTest= $this->documentElement; 
        if(!$elTest) return false;

        $zoznam = [];

        if ($elTest->hasChildNodes()) {
            /* @var $ch \DOMNode */
            foreach ($elTest->childNodes as $ch) {
                //if(!$ch->nodeName =='pole'){
                    $zoznam[] = $ch;
                //}
            }
        }        

        
        if ($elTest->nodeType == XML_CDATA_SECTION_NODE) {
            //$elTest->nodeValue = $parser->parseData($elTest->nodeValue);

        }
        
        if ($elTest->nodeType == XML_TEXT_NODE) {
            $elTest->nodeValue = $this->parseData($elTest->nodeValue);

        }
        
        if ($elTest->nodeType == XML_ELEMENT_NODE) {

            if ($elTest->hasAttributes()) {
                /* @var $attr \DOMAttr */
                foreach ($elTest->attributes as $attr) {

                    $v = $this->parseData($attr->value);
                    $v = stripslashes($v);
                    $attr->value  = htmlspecialchars(($v));
                }
            }

            //addcslashes
            //stripslashes
            $metoda = "node_".$elTest->nodeName;
            if(method_exists($this, $metoda)){
                $r = $this->$metoda($elTest);
                if($r==true){
                    return true;
                }
            }

        }        

        /* @var $ch \DOMNode */
        foreach ($zoznam as $el) {
           $this->spracujBod($el);
        }      
        
        
        
    }    
    


    public function spracuj(\DOMNode $elTest = null): \DOMDocument {
        if(!$elTest) $elTest= $this->documentElement;
        $this->spracujBod($elTest);
        $this->setValue();
        return $this;
    } 
    
    public function setHTML(){
        return $this;
    }

    
}
