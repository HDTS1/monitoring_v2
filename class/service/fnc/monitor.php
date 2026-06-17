<?php

namespace service\fnc;

class monitor extends \service\baseExtend {
    
    public function update_md5(){
        
        $db = $this->getDB();
        $sql = "SELECT id_model FROM model WHERE model='plc' ";
        $db->add_sql($sql, "zoznam");
        $result = $db->cmd();
        $result = $result["zoznam"];
        
        $result = array_map(function($item){
            $model = $this->getDB()->getModel($item["id_model"]);
            $md5 = ["label","event","cycle_count","total_distance","alarm_word","run_time"];
            $md5 = array_map(function($item) use($model){
                return $model["data"][$item];
            }, $md5);
            
            $md5 = implode("", $md5);
            $md5 = md5($md5);

            $data["md5"]=$md5;
            $this->getDB()->updateModel($item["id_model"], $data);
            
            return $model;
            
            
        }, $result);
        
        
        
        /*
        $md5 = ["label","event","cycle_count","total_distance","alarm_word","run_time"];
        $md5 = array_map(function($item) use($data){
            return $data[$item];
        }, $md5);
        $md5 = implode("", $md5);
        $md5 = md5($md5);
        
        $data["md5"]=$md5;
        */
        
        
        return $this->output("OK");
        
    }
    
    
    
    
    
    public function getStatus(){
        
        $data = array();
        
        $popis = "
            0	Metrické jednotky na displeji trenažéra (vypnuté = imperiálne)	0-imperialne, 1-metricke	RW
            1	Vypnutá kontrola vodného chladenia	0-povolene, 1-zakazane	RW
            2	Vypnutá kontrola polohy valcov	0-povolene, 1-zakazane	RW
            3	Vypnutá kontrola teploty okolia	0-povolene, 1-zakazane	RW
            4	Vypnutá kontrola internetovej konektivity	0-povolene, 1-zakazane	RW
            5	Vypnuté automatické mazanie	0-povolene, 1-zakazane	RW
            6	Vypnuté generovanie požiadavky štvrťročnej kontroly trenažéra (robí zákazník)	0-povolene, 1-zakazane	RW
            7	Vypnuté generovanie požiadavky servisu (zruší aj aktívnu požiadavku)	0-povolene, 1-zakazane	RW
            8	Zapnutý automatický náklon pásu vyp/zap	0-vypnuty, 1-zapnuty	RW
            9	Zapnutý trenažér	0-vypnuty, 1-zapnuty	R
            10	Aktívna požiadavka servisu	0-neaktivna, 1- aktivna	R
            11	Aktívne obmedzenie rýchlosti pásu po uplynutí termínu servisu	0-neaktivna, 1- aktivna	R
            12	Zobrazený servisný indikátor na obrazovke	0-neaktivny, 1- aktivny	R
            13	Aktívna požiadavka \"Platba\"	0-neaktivna, 1- aktivna	R
            14	Aktívne obmedzenie času jazdy po uplynutí nastaveného termínu Platby 	0-neaktivne, 1- aktivne	R
            15	Aktívne obmedzenie rýchlosti pásu po uplynutí nastaveného termínu Platby	0-neaktivne, 1- aktivne	R
        ";
        
        $pole = preg_split("/\n/", trim($popis));
        
        $pole = array_map(function($item){
            $x = preg_split("/\t/", trim($item));
            list($kluc,$popis,$info, $access)=$x;
            
            return array(
                "kluc"=>trim($kluc),
                "popis"=>trim($popis),
                "info"=>trim($info),
                "access"=>trim($access)
            );
        }, $pole);
        
        return $this->output($pole);
        
    }
    
    
    
    public function popisEvent(){
    
        $popis = "
            Casovy interval	T	Time
            Zapnutie HST	O	On
            Nastal niektory Alarm	A	Alarm
            Nastala poziadavka mazania	G	Greasing
            Nastala poziadavka rocneho servisu	S	Service
            Nastalo obmedzenie rychlosti (nevykonany rocny servis)	L	Limit
            Aktivacia alebo limit udalosti Platba	P	Payment
            Priemerna hodnota prudu motora pocas jazdy vacsia ako 12A 	M	Motor
            Rychlost jazdy pasu 35km/h (maximum)	H	HighSpeed
            Prijaty prikaz na zmenu niektoreho parametra	C	Command
            Vypnutie HST	F	Off
        ";
        
        
        
        
        
        $pole = preg_split("/\n/", trim($popis));
        $pole = array_map(function($item){
            $x = preg_split("/\t/", trim($item));
            list($popis,$kluc,$znak)=$x;
            
            return array(
                "popis"=>trim($popis),
                "znak"=>trim($znak),
                "kluc"=>trim($kluc)
            );
        }, $pole);
        
        $data = array();
        
        foreach ($pole as $value) {
            $data[$value["kluc"]]= $value;
        }
        
        return $this->output($data);
    }
    
    
    
    private function textToHex($text) {
        $hex = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $hex .= dechex(ord($text[$i]));
        }
        return $hex;
    }
    
    private function hexToText($hex) {
        $text = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $text .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $text;
    }

    private function status_jazda($val){
        $x = array(
            "10"=>array(
                "value"=>10,
                "info"=>"rychlost sa pocas jazdy nemenila"
            ),
            "11"=>array(
                "value"=>11,
                "info"=>"rychlost sa pocas jazdy menila"
            )
        );
        
        if(!@$x[$val]){
            return array(
                "value"=>$val,
                "info"=>"Nie je definovana"
            );
        }

        return $x[$val];
    }

    private function event($val){
        
        /*
        Po 6tich hodinach od posledneho odoslania dat, ak je trenazer vypnuty|T|Time
        Po 1 hodine od posledneho odoslania dat, ak je trenazer zapnuty|T|Time 
         */
        
        
        $def = "       
        Zaznam na zaklade casu|T|Time
        Zapnutie HST|O|On
        Nastal niektory Alarm|A|Alarm
        Nastala poziadavka mazania|G|Greasing
        Nastala poziadavka rocneho servisu|S|Service
        Nastalo obmedzenie rychlosti (nevykonany rocny servis)|L|Limit
        Nastalo obmedzenie rychlosti (vyvolane udalostou PLATBA)|P|Payment
        Priemerna hodnota prudu motora pocas jazdy vacsia ako 12A|M|Motor
        Rychlost jazdy pasu 35km/h (maximum)|H|HighSpeed
        Prijaty prikaz na zmenu niektoreho parametra|C|Command
        ";

        $popis = explode("\r\n", $def);
        
        $data = array();
        
        foreach ($popis as $value) {
            $value = trim($value);
            if(empty($value)){
                continue;
            }
            list($popis,$znak,$info)= explode("|", $value, 3);
            
            
            $data[$znak]=array(
                "priznak"=>$znak,
                "popis"=>$popis,
                "info"=>$info
            );
            
        }

        return @$data[$val];
    }

    private function alarm_word($val){
            $def = array();
            $def["original_def"]=$val;
        
            
            
            $bits = str_split($val);
            $reversedBits = array_reverse($bits);
            
            
            
            
            $popis_def = 'nepouzite
            bezpecnostne rele vypnute
            chyba frekvencneho menica
            chyba senzora hladiny vody v nadrzi
            chyba vodneho chladenia
            chyba polohy predneho valca
            chyba polohy zadneho valca
            chyba internetovej konektivity
            vysoka okolita teplota';
            
            
            
            $popis = explode("\r\n", $popis_def);
            
            foreach ($popis as $key => $value) {
                $kluc = preg_replace("/[\s+|\/|-]/","_",trim($value));
                $kluc = preg_replace("/_+/","_",$kluc);
                $kluc = strtolower($kluc);

                $def[$kluc] = $reversedBits[$key] == 1 ? true : false ;
            }
            
                        
            
            return $def;
    }    

    private function status_word($val){
            $def = array();
            $def["original_def"]=$val;
        
            
            
            $bits = str_split($val);
            $reversedBits = array_reverse($bits);
            
            
    $popis_def = 'Jednotky metric/imperial
                Kontrola vodneho chladenia
                kontrola polohy valcov
                kontrola teploty okolia
                kontrola internetovej konektivity
                Automaticke mazanie
                Stvrtrocna kontrola trenazera
                Rocny servis trenazera
                Automaticky naklon vyp/zap
                Trenazer zap/vyp
                Poziadavka rocneho servisu trenazera
                Obmedzena rychlost bez rocneho servisu
                Servisny indikator na obrazovke
                Udalost Platba
                Platba - obmedzenie_1 
                Platba - obmedzenie_2';



                $popis = explode("\r\n", $popis_def);

                foreach ($popis as $key => $value) {
                    $kluc = preg_replace("/[\s+|\/|-]/","_",trim($value));
                    $kluc = preg_replace("/_+/","_",$kluc);
                    $kluc = strtolower($kluc);

                    $def[$kluc] = intval($reversedBits[$key])  ;
                }



                return $def;
        }
    

    public function __construct() {
        $setting = array(
            "server"   => getenv('DB_HOST')     ?: "db",
            "user"     => getenv('DB_USER')     ?: "hdts_user",
            "password" => getenv('DB_PASS')     ?: "",
            "databaza" => getenv('DB_NAME')     ?: "hdts_monitor",
            "port"     => intval(getenv('DB_PORT') ?: 3306),
            "charset"  => "utf8mb4"
        );

        $this->setDB($setting);
    }

    
    public function listPapago(){
        $sql = "SELECT a.hodnota AS teplomer, MAX(a.cas_create) AS last_zaznam
        FROM model_data a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='value'
        WHERE a.full_path RLIKE '^papago' AND a.kluc='name' AND b.hodnota > -99
        GROUP BY a.hodnota
        ORDER BY a.hodnota";
        
        $db = $this->getDB();
        

        $p = $db->add_sql($sql, "zoznam");
      
        $result = $db->cmd();
        $result = $result["zoznam"];
        
        return $this->output($result);
        
        
    }
    
    
    public function list_plc(){
        $sql="SELECT distinct b.hodnota AS plc
        FROM model a 
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
        WHERE a.model ='plc'";
        $db= $this->getDB();
        $p = $db->add_sql($sql, "zoznam");


        $result = $db->cmd();
        $result = $result["zoznam"];
        
        
        $collator = new \Collator('en_US');
        usort($result, function($a,$b) use($collator){
                $a1=@$a["plc"];
                $b1=@$b["plc"];
                return $collator->compare($a1, $b1);  
        });
        
        
        // FIX #2: batch-load all settings in one query instead of one query per PLC
        $allSettings = $this->getAllPLCSettings();
        $result = array_map(function($item) use ($allSettings) {
            $name = $item["plc"];
            $item["setting"] = $allSettings[$name] ?? ["name" => $name, "favorite" => 1];
            return $item;
        }, $result);
        
        
        
        return $this->output($result);
        
        
        
    }
    
    
    public function clearPLC(){
        
        $sql = "WITH ranked_data AS (
        SELECT a.id_model, a.cas_create, b.hodnota AS label, c.hodnota AS `event`, d.hodnota AS km,
        e.hodnota AS cycle_count, f.hodnota AS run_time, g.hodnota AS alarm_word , md5(CONCAT(c.hodnota, d.hodnota, e.hodnota, f.hodnota, g.hodnota)) AS kluc,
        LAG(md5(CONCAT(c.hodnota, d.hodnota, e.hodnota, f.hodnota, g.hodnota))) OVER(ORDER BY a.cas_create desc) AS prev_value
        FROM model a 
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc='event'
        JOIN model_data d ON a.id_model = d.id_model AND d.kluc='total_distance'
        JOIN model_data e ON a.id_model = e.id_model AND e.kluc='cycle_count'
        JOIN model_data f ON a.id_model = f.id_model AND f.kluc='run_time'
        JOIN model_data g ON a.id_model = g.id_model AND g.kluc='alarm_word'

        WHERE a.model = 'plc' AND b.hodnota= :plc AND a.cas_create > '2024-10-01'
        ORDER BY a.cas_create
        )

        DELETE FROM model
        WHERE id_model IN(SELECT id_model
        FROM ranked_data
        WHERE kluc = prev_value 
        ORDER BY cas_create desc)";
        
        
        $zoznam  = $this->list_plc();
        $zoznam = $zoznam["data"];
        $db = $this->getDB();
        
        foreach ($zoznam as $value) {
            $p = $db->add_sql($sql, "delete");
            $p->def("plc", $value["plc"]);
            $db->cmd();
            
        }

        
        
        return $this->output($zoznam);
        
    }
    
    
    public function clearTestDevice(){
        $sql = "delete FROM 
        model a
        WHERE a.model='testDevice' AND  a.cas_create < DATE_SUB(NOW(), INTERVAL 3 DAY)";
        $db = $this->getDB();
        $db->add_sql($sql, "delete");
        
        $db->cmd();
        
        
        
        return $this->output("OK");
    }
    
    
    public function clearPapago(){
        
        $rest = new $this;
        $result = $rest->listPapago();
        $result= $result["data"];
        
        $end = new \DateTime();
        $start = new \DateTime();
        $start->modify("-50 day");
        
        $date = array(
            "start"=>$start->format("Y-m-d"),
            "end"=>$end->format("Y-m-d")
        );
        
        
        $result = array_map(function($item) use($date){
                $db = $this->getDB();
            
                $sql_delete = "WITH ranked_data AS (
                        SELECT a.id_model,
                             CONVERT(d.hodnota, DATETIME) AS TIME,
                             b.hodnota AS label, 
                             FLOOR(c.hodnota) AS VALUE,
                                       LAG(FLOOR(c.hodnota)) OVER(ORDER BY CONVERT(d.hodnota, DATETIME)) AS prev_value
                         FROM model a 
                         JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'name'
                         JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'value'
                         JOIN model_data d ON a.id_model = d.id_model AND d.kluc = 'date_time'
                         WHERE a.model = 'papago' AND b.hodnota= :teplomer AND FLOOR(c.hodnota) > -99
                         AND date(d.hodnota) BETWEEN :from AND :to
                     )

                     DELETE from model WHERE id_model IN (
                     SELECT id_model
                     FROM ranked_data
                     WHERE (VALUE = prev_value AND date(time)< :to ) )"; 

                    $p = $db->add_sql($sql_delete, "delete");
                    $p->def("teplomer", $item["teplomer"]);
                    $p->def("from", $date["start"]);
                    $p->def("to",  $date["end"]);
                    
                    $db->cmd();
            
            return $item;
        },$result);
        
        
        return $this->output($result);
    }
    
    
    public function dataGrafTeplotaAdmin(){
        
        
        $kluc = md5("dataGrafTeplotaAdmin");
        $obsah = \app\cash::get($kluc);
        
        
        if($obsah ){
            return $this->output($obsah);
        }
        
        
        
        $rest = new $this;
        $result = $rest->listPapago();
        $result= $result["data"];
        
        $rozsah = array(
            "min"=>null,
            "max"=>null
        );
        
        
        $end = new \DateTime();
        $start = new \DateTime();
        $start->modify("-1 day");
        
        $date = array(
            "start"=>$start->format("Y-m-d"),
            "end"=>$end->format("Y-m-d")
        );
        
        
        $result = array_map(function($item) use($date, &$rozsah){
            
            
            $rest = new \service\fnc\monitor();
            $rest->parameter = array(
                "name"=>$item["teplomer"],
                "from"=>$date["start"],
                "to"=>$date["end"]
            );
            
            $z = $rest->dataGrafTeplota();
            $z = $z["data"]["list"];
            

            
            
            if(count($z)>1){
                $x = $z[count($z)-1];
                $x["date_time"]= (new \DateTime())->format("Y-m-d H:i:s");

                $z[]= $x;
            }

            foreach ($z as $value) {
                if(!$rozsah["min"]) $rozsah["min"] = new \DateTime($value["date_time"]);
                if(!$rozsah["max"]) $rozsah["max"] = new \DateTime($value["date_time"]);
                $akt = new \DateTime($value["date_time"]);


                if($akt<$rozsah["min"]){
                   $rozsah["min"]=$akt; 
                }

                if($akt>$rozsah["max"]){
                   $rozsah["max"]=$akt; 
                }
            }
            
            
            $item["graf"]=$z;
            return $item;
            
        }, $result);
        
        
        
        
        
        $rozsah["min"]= !$rozsah["min"] ? (new \DateTime())->format("Y-m-d")." 00:00:00" : $rozsah["min"]->format("Y-m-d H:i:s");
        $rozsah["max"]= !$rozsah["max"] ? (new \DateTime())->modify("+1 day")->format("Y-m-d")." 00:00:00" : $rozsah["max"]->format("Y-m-d H:i:s");
        
        $vystup = array(
            "list"=>$result,
            "date"=>$rozsah
        );
        
        
        \app\cash::set($kluc, $vystup, 60*5);
        

        
        return $this->output($vystup);
        
        
    }
    
    
   public function dataGrafTeplota(){
        
        $end = new \DateTime();
        $start = new \DateTime();
        $start->modify("-1 day");

        if(!@$this->parameter["name"] || !@$this->parameter["from"] || !@$this->parameter["to"] ){
            $this->parameter["name"]='T.CZ.PR.1';
            $this->parameter["from"]=$start->format("Y-m-d");
            $this->parameter["to"]=$end->format("Y-m-d");
        }

        $name = $this->parameter["name"];
        $from= $this->parameter["from"];
        $to = $this->parameter["to"];
        
        
        $sql = "WITH ranked_data AS (
                SELECT a.id_model,
                    CONVERT(d.hodnota, DATETIME) AS TIME,
                    b.hodnota AS label, 
                    FLOOR(c.hodnota) AS VALUE,
                              LAG(FLOOR(c.hodnota)) OVER(ORDER BY CONVERT(d.hodnota, DATETIME)) AS prev_value
                FROM model a 
                JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'name'
                JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'value'
                JOIN model_data d ON a.id_model = d.id_model AND d.kluc = 'date_time'
                WHERE a.model = 'papago' AND b.hodnota= :teplomer AND FLOOR(c.hodnota) > -99
                AND date(d.hodnota) BETWEEN :from AND :to
            )
            SELECT TIME AS date_time, value 
            FROM ranked_data
            WHERE (VALUE <> prev_value OR prev_value IS NULL) 

            ORDER BY TIME
        ";
        

        $sql = "with zaznam AS (SELECT b.hodnota AS `name`, c.hodnota AS `date_time`, round(d.hodnota) AS `value`,
        if(LAG(round(d.hodnota)) OVER (ORDER BY convert(c.hodnota, DATETIME))=round(d.hodnota),1,0) AS xx
        FROM model a
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'name'
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'date_time'
        JOIN model_data d ON a.id_model = d.id_model AND d.kluc = 'value'
        WHERE a.model='papago' 
        and date(c.hodnota) BETWEEN :from AND :to 
        AND d.hodnota > -99 AND b.hodnota = :teplomer
        ORDER BY convert(c.hodnota, DATETIME))
        SELECT NAME,date_time, `value` FROM zaznam
        WHERE xx=0
        ";
        
        
        
        
        
        $db = $this->getDB();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("teplomer", $name);
        $p->def("from", $from);
        $p->def("to", $to);
        
        
        
        
        $result = $db->cmd();
        $result = $result["zoznam"];
        
        
        
        $min = null;
        $max = null;
        
        
        foreach ($result as $value) {
            if(!$min) $min = new \DateTime($value["date_time"]);
            if(!$max) $max = new \DateTime($value["date_time"]);
            $akt = new \DateTime($value["date_time"]);
            
            
            if($akt<$min){
               $min=$akt; 
            }
            
            if($akt>$max){
               $max=$akt; 
            }
        }
        
        
        
        $vystup = array(
            "name"=>$name,
            "list"=>$result
        );
        
        /*
,
            "date"=>array(
                "min"=>$min->format("Y-m-d H:i:s"),
                "max"=>$max->format("Y-m-d H:i:s")
            )
         */
        

        
        return $this->output($vystup);

     
        
        
    }
    
    public function dataGrafTeplota_smeti(){
        
        $end = new \DateTime();
        $start = new \DateTime();
        $start->modify("-1 day");

        if(!@$this->parameter["name"] || !@$this->parameter["from"] || !@$this->parameter["to"] ){
            $this->parameter["name"]='T.CZ.PR.1';
            $this->parameter["from"]=$start->format("Y-m-d");
            $this->parameter["to"]=$end->format("Y-m-d");
        }

        $name = $this->parameter["name"];
        $from= $this->parameter["from"];
        $to = $this->parameter["to"];
        
        
        $sql = "WITH ranked_data AS (SELECT a.id_model, c.hodnota AS `name`, b.hodnota AS date_time, 
        CONVERT (d.hodnota , DECIMAL(6,0))  AS `value`,
        LAG(CONVERT (d.hodnota , DECIMAL(6,0)), 1, 0) OVER (ORDER BY b.hodnota) AS previous_value
        FROM   model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='date_time'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='name'
        JOIN model_data d ON a.id_model=d.id_model AND d.kluc='value'
        WHERE a.model = 'papago' AND c.hodnota = :teplomer
        AND date(b.hodnota) BETWEEN :from AND :to
        ORDER BY c.hodnota,b.hodnota)
        SELECT a.date_time, a.value
        FROM ranked_data a
        WHERE `value` != previous_value and `value`>-90 order by a.date_time
        ";
        
        
        $db = $this->getDB();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("teplomer", $name);
        $p->def("from", $from);
        $p->def("to", $to);
        
        $result = $db->cmd();
        $result = $result["zoznam"];
        
        
        
        $min = null;
        $max = null;
        
        
        foreach ($result as $value) {
            if(!$min) $min = new \DateTime($value["date_time"]);
            if(!$max) $max = new \DateTime($value["date_time"]);
            $akt = new \DateTime($value["date_time"]);
            
            
            if($akt<$min){
               $min=$akt; 
            }
            
            if($akt>$max){
               $max=$akt; 
            }
        }
        
        
        
        $vystup = array(
            "name"=>$name,
            "list"=>$result
        );
        
        /*
,
            "date"=>array(
                "min"=>$min->format("Y-m-d H:i:s"),
                "max"=>$max->format("Y-m-d H:i:s")
            )
         */
        

        
        return $this->output($vystup);

     
        
        
    }

    
    public function papago() {

        
        $db = $this->getDB();
        $t = $db->modelTable("papago");
        //$t->addFilter("name", "T.CZ.PR.1");
        $t->setData();
        
        $result = $t->cmd();
        
        usort($result, function($a, $b){
            $a1 = new \DateTime($a["data"]["date_time"]);
            $b1 = new \DateTime($b["data"]["date_time"]);
            return $a1< $b1;
        });
        
        $result = array_map(function($item){
            return $item["data"];
        }, $result);

        return $this->output($result);
    }
    
    
    /**
     * sendMessage — previously sent Pushover notifications to the ex-developer's
     * personal phone via api.pushover.net. Removed. No-op stub.
     */
    public static function sendMessage($data){
        // No-op: Pushover notifications removed.
        return;
    }
    
    
    public function nowPapago(){
        
        
        
        
        $sql = "WITH ranked_data AS (SELECT b.hodnota AS `name`, c.hodnota AS `value`,
        d.hodnota AS date_time,
        ROW_NUMBER() OVER(PARTITION BY b.hodnota ORDER BY a.cas_create DESC) AS rn
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='name'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='value'
        JOIN model_data d ON a.id_model=d.id_model AND d.kluc='date_time'
        WHERE a.model='papago')
        SELECT `name`,convert(date_time,DATETIME) AS `date_time`,CONVERT(`value`,DECIMAL(5,2)) AS `value` FROM ranked_data
        WHERE rn <=2
        ";
        
        
        $db = $this->getDB();
        $db->add_sql($sql, "zoznam");
        $result = $db->cmd();
        $result = $result["zoznam"];
        
        if(!@$this->parameter["filter"]){
            $this->parameter["filter"]= null;
        }
        
        
        if($this->parameter["filter"]){
            $result = array_filter($result, function($item){
                return preg_match("/^".preg_quote($this->parameter["filter"])."/", $item["name"]);
            });
            $result = array_values($result);
        }
        
        $data = array();
        
        foreach ($result as $value) {
            $kluc = @$data[$value["name"]];
            if(!$kluc){
               $data[$value["name"]]= $value; 
            } else {
               $data[$value["name"]]["previous"] =$value;
            }
        }
        
        $data = array_values($data);
        
        return $this->output($data);

    }
    
    
    public function plc_event(){
        
        $cash = \app\cash::get("plc_event_".$this->parameter["plc"]);
        if($cash){
            return $this->output($cash);
        }
        
        
        
        $sql = "WITH ranked_data AS (
            SELECT a.id_model, a.cas_create, b.hodnota AS label, c.hodnota AS `event`, d.hodnota AS km,
            e.hodnota AS cycle_count, f.hodnota AS run_time, g.hodnota AS alarm_word , md5(CONCAT(c.hodnota, d.hodnota, e.hodnota, f.hodnota, g.hodnota)) AS kluc,
            LAG(md5(CONCAT(c.hodnota, d.hodnota, e.hodnota, f.hodnota, g.hodnota))) OVER(ORDER BY a.cas_create desc) AS prev_value
            FROM model a 
            JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
            JOIN model_data c ON a.id_model = c.id_model AND c.kluc='event'
            JOIN model_data d ON a.id_model = d.id_model AND d.kluc='total_distance'
            JOIN model_data e ON a.id_model = e.id_model AND e.kluc='cycle_count'
            JOIN model_data f ON a.id_model = f.id_model AND f.kluc='run_time'
            JOIN model_data g ON a.id_model = g.id_model AND g.kluc='alarm_word'

            WHERE a.model = 'plc' AND b.hodnota=:plc AND a.cas_create > '2024-10-01'
            ORDER BY a.cas_create
            )
            SELECT id_model, cas_create, label, `event`, km,cycle_count, run_time, alarm_word,
            /* LAG(cas_create) OVER (ORDER BY cas_create) AS predchadzajuci_datum, */
            TIMESTAMPDIFF(MINUTE, LAG(cas_create) OVER (ORDER BY cas_create), cas_create) AS diff,
            if(alarm_word = '0000000000000000', 0,1) as alarm_state

            FROM ranked_data
            WHERE kluc <> prev_value OR prev_value IS NULL
            ORDER BY cas_create desc
            ";
        
            $db= $this->getDB();
            $p = $db->add_sql($sql, "zaznam");
            $p->def("plc", $this->parameter["plc"]);

            $result = $db->cmd();
            $result = $result["zaznam"];
            
            
            \app\cash::set("plc_event_".$this->parameter["plc"], $result, 60*10);

            return $this->output($result);
        
        
    }
    
    
    
    public function plc_event_smeti(){
        
        $cash = \app\cash::get("event_".$this->parameter["plc"]);
        if($cash){
            return $this->output($cash);
        }
        
        
        
        
        $sql ="SELECT a.id_model, a.cas_create, b.hodnota AS label, c.hodnota AS `event`, d.hodnota AS `km`,
        e.hodnota AS cycle_count, x.hodnota as run_time, f.hodnota AS `alarm`
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='event'
        JOIN model_data d ON a.id_model=d.id_model AND d.kluc='total_distance'
        JOIN model_data e ON a.id_model=e.id_model AND e.kluc='cycle_count'
        JOIN model_data x ON a.id_model=x.id_model AND x.kluc='run_time'
        JOIN model_data f ON a.id_model=f.id_model AND f.kluc ='alarm_word'
        WHERE a.model='plc'  and DATE(a.cas_create) BETWEEN '2024-10-01' AND  '2025-12-31' AND b.hodnota RLIKE :plc
        ORDER BY b.hodnota,a.cas_create desc limit 3000";
        
        //AND c.hodnota != 'T'
        
        $db= $this->getDB();
        $p = $db->add_sql($sql, "zaznam");
        $p->def("plc", $this->parameter["plc"]);
        
        $result = $db->cmd();
        $result = $result["zaznam"];
        
        

        
        
        $diff = null;
        $result = array_map(function($item) use(&$diff){
            if(!$diff){
                $t = new \DateTime($item["cas_create"]);
                $start = (new \DateTime())->getTimestamp();
                $end = $t->getTimestamp();
                $item["diff"]= round(($start-$end)/60);
            } else {
                $t = new \DateTime($item["cas_create"]);
                $start = $diff->getTimestamp();
                $end = $t->getTimestamp();
                $item["diff"]= round(($start-$end)/60);
            }

            $diff = new \DateTime($item["cas_create"]);
            $item["alarm_state"]=0;
            

            
            
            
            
            
            return $item;
        }, $result);
        
        
        $data = array();
        $porovnanie = null;
        
        foreach ($result as $value) {
            $x = array(
                $value["event"],
                $value["km"],
                $value["cycle_count"],
                $value["run_time"],
                $value["alarm"]
            );
            
            $x = md5(json_encode($x));
            
            if($x != $porovnanie){
                $data[] = $value;
            } else {
                $t = new \DateTime($value["cas_create"]);
                $n = new \DateTime();
                
                $d = $n->getTimestamp() - $n->getTimestamp();
                if($d> (60*60*24*2)){
                    $db->deleteModel($value["id_model"]);
                }
                
            }
            
            $porovnanie = $x;
            
        }
        
        
        \app\cash::set("event_".$this->parameter["plc"], $data, 60*10);
        
        return $this->output($data);
        
    }
    
    public function getPLCRecord(){
        $kluc = $this->parameter["kluc"];
        $db = $this->getDB();
        $result = $db->getModel($kluc);
        
        $result["data"]["alarm_word"]=$formattedString = chunk_split($result["data"]["alarm_word"], 4, ' ');
        $result["data"]["status_word"]=$formattedString = chunk_split($result["data"]["status_word"], 4, ' ');
        $result["data"]["base64_name"]= base64_encode($result["data"]["label"]);
        
        $kluc = $this->popisEvent();
        $kluc = $kluc["data"];
        
        $result["data"]["event_popis"] = @$kluc[trim($result["data"]["event"])];
        
        $result["setting"]= $this->getPLCSetting($result["data"]["label"]);
        
        
        return $this->output($result);
        
        
    }
    
    
    public function getPLCTrenning(){

        $plc = $this->parameter["plc"];
        $cash = \app\cash::get(md5($plc)."t");
        
        if($cash){
            return $this->output($cash);
        }
        
        
        $rest = new \plc\data();
        
        $to = (new \DateTime())->modify("0 day")->format("Y-m-d");
        $from = (new \DateTime())->modify("-7 day")->format("Y-m-d");
        

        
        $result =$rest->getCycle($plc, $from, $to);
        
        \app\cash::set(md5($plc)."t", $result, 60*10);
        return $this->output($result);
        
    }
    
    private function getPLCSetting($kluc){
        $db = $this->getDB();
        $t = $db->modelTable("plc_advance");
        $t->setData();
        $t->addFilter("name", $kluc);
        
        
        $result = $t->cmd();
        $data = @$result[0];
        
        if(!$data){
            $data= array(
                "name"=>$kluc,
                "favorite"=>1
            );
            
            $db->setModel("plc_advance", $data);
        } else {
            $data= $data["data"];
        }
                
        
        
        
        return $data;
    }

    /**
     * FIX #2 — Batch-load ALL plc_advance settings in a single SQL query.
     * Returns an associative array keyed by PLC name (e.g. 'PLC-001' => [...data...]).
     * Replaces the N+1 pattern of calling getPLCSetting() once per PLC.
     */
    private function getAllPLCSettings(): array {
        $db = $this->getDB();
        $sql = "SELECT a.id_model, b.kluc, b.hodnota
                FROM model a
                JOIN model_data b ON a.id_model = b.id_model
                WHERE a.model = 'plc_advance'
                ORDER BY a.id_model";

        $p = $db->add_sql($sql, "all_plc_settings");
        $result = $db->cmd();
        $rows = $result["all_plc_settings"];

        // Group EAV rows by id_model into flat key=>value arrays
        $models = [];
        foreach ($rows as $row) {
            $models[$row["id_model"]][$row["kluc"]] = $row["hodnota"];
        }

        // Decode dot-notation nested keys and index by name
        $map = [];
        foreach ($models as $flatData) {
            $decoded = \db\array_model::decode($flatData);
            if (isset($decoded["name"])) {
                $map[$decoded["name"]] = $decoded;
            }
        }
        return $map;
    }

    /**
     * FIX #3 — Batch-load all PLC labels that have a valid setPLC configuration.
     * Returns an associative array of label => true for fast isset() lookup.
     * Replaces the N+1 pattern of calling overitZaznam('setPLC',...) once per PLC.
     */
    private function batchLoadValidSetPLC(): array {
        $db = $this->getDB();
        $sql = "SELECT DISTINCT d.hodnota AS plc
                FROM model a
                JOIN model_data d ON a.id_model = d.id_model AND d.kluc = 'plc'
                JOIN model_data v ON a.id_model = v.id_model AND v.kluc = 'valid' AND v.hodnota = '1'
                WHERE a.model = 'setPLC'";

        $p = $db->add_sql($sql, "valid_setplc");
        $result = $db->cmd();
        $rows = $result["valid_setplc"];

        $map = [];
        foreach ($rows as $row) {
            $map[$row["plc"]] = true;
        }
        return $map;
    }
    
    public function getPLCSettingData(){
        return $this->output($this->getPLCSetting($this->parameter["kluc"]));
    }
    
    
    public function setFavoritePLC(){
            $kluc = $this->parameter["kluc"];
            $db = $this->getDB();
            $t = $db->modelTable("plc_advance");
            $t->setData();
            $t->addFilter("name", $kluc);


            $result = $t->cmd();
            $data = @$result[0]; 
            
            if($data){
                $db->updateModel($data["model"]["id_model"], array("favorite"=> $this->parameter["value"]));
                \app\cash::delete("plc_event_".$kluc); 
            }
            
            
            
            return $this->output($data);
        
    }
    
    
    
    public function plc(){
        //$kluc_mem = md5("plc_".json_encode($this->parameter));
        



        if(1==1){
        
        
        
            $sql = "WITH ranked_data AS 
            ( SELECT a.id_model, a.cas_create, b.hodnota AS `event`, c.hodnota AS label, 
            d.hodnota AS status_word, e.hodnota AS alarm_word, f.hodnota as `interval`,
            ROW_NUMBER() OVER(PARTITION BY c.hodnota ORDER BY a.cas_create DESC) AS rn 
            FROM model a 
            JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'event' 
            JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'label'
            JOIN model_data d ON a.id_model = d.id_model AND d.kluc = 'status_word'
            JOIN model_data e ON a.id_model = e.id_model AND e.kluc = 'alarm_word'
            left JOIN model_data f ON a.id_model = f.id_model AND f.kluc = 'socket_interval'

            WHERE a.model = 'plc' ORDER BY label) 
            SELECT id_model, cas_create, `event`, label ,status_word, alarm_word,`interval`, rn
            FROM ranked_data WHERE rn <= 2 AND label RLIKE '^PLC' 
            ORDER BY label, cas_create DESC";

            if(@$this->parameter["filter"]){
                $sql = "WITH ranked_data AS 
                    ( SELECT a.id_model, a.cas_create, b.hodnota AS `event`, c.hodnota AS label, 
                    d.hodnota AS status_word, e.hodnota AS alarm_word, f.hodnota as `interval`,
                    ROW_NUMBER() OVER(PARTITION BY c.hodnota ORDER BY a.cas_create DESC) AS rn 
                    FROM model a 
                    JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'event' 
                    JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'label'
                    JOIN model_data d ON a.id_model = d.id_model AND d.kluc = 'status_word'
                    JOIN model_data e ON a.id_model = e.id_model AND e.kluc = 'alarm_word'
                    left JOIN model_data f ON a.id_model = f.id_model AND f.kluc = 'socket_interval'

                    WHERE a.model = 'plc' ORDER BY label) 
                    SELECT id_model, cas_create, `event`, label ,status_word, alarm_word,`interval`, rn
                    FROM ranked_data WHERE rn <= 2 AND label RLIKE '^".preg_quote($this->parameter["filter"])."' 
                    ORDER BY label, cas_create DESC";
            }

            $db = $this->getDB();
            $p = $db->add_sql($sql,"zoznam");
            $res = $db->cmd();

            $res = $res["zoznam"];
            if(!$res){
                $res = array();
            }

            //\app\cash::set($kluc_mem, json_encode($res));

        
        } else {
            //$res = json_decode($obsah,true);
        }
        
        $data = array();

        // FIX #2 + FIX #3: pre-load both lookup tables in 2 queries before the loop
        $allSettings   = $this->getAllPLCSettings();
        $validSetPLC   = $this->batchLoadValidSetPLC();
        
        
        foreach ($res as $value) {
            
            
            
            $kluc = @$data[$value["label"]];
            if(!$kluc){
                $data[$value["label"]] = $value;
                $data[$value["label"]]["time"]["now"] = round(((new \DateTime())->getTimestamp() - (new \DateTime($value["cas_create"]))->getTimestamp())/60,2);
                
                $x = str_split($value["status_word"]);
                $a = intval($value["alarm_word"]);
                
                $data[$value["label"]]["icon"]["on_off"]=$x[9];
                $data[$value["label"]]["icon"]["alarm"]= $a > 0 ? 1 : 0;
                $data[$value["label"]]["icon"]["servis"]=$x[10];
                

                // FIX #3: use pre-loaded map instead of one DB query per PLC
                $data[$value["label"]]["icon"]["setting"] = isset($validSetPLC[$value["label"]]) ? 1 : 0;
               
                
            } else {
                $data[$value["label"]]["previous"]=$value;
                $data[$value["label"]]["time"]["diff"] = round(((new \DateTime($data[$value["label"]]["cas_create"]))->getTimestamp() - (new \DateTime($value["cas_create"]))->getTimestamp())/60,2);
            }
        }
        

        $data = array_values($data);

        // FIX #2: use pre-loaded map instead of one DB query per PLC
        $data = array_map(function($item) use ($allSettings) {
            $name = $item["label"];
            $item["setting"] = $allSettings[$name] ?? ["name" => $name, "favorite" => 1];
            return $item;
        }, $data);
        
        
        return $this->output($data);
        
    }
    
    
    public function plc1(){

        
        $kluc_mem = md5(json_encode($this->parameter));
        $obsah = \app\cash::get($kluc_mem);
        
        

        if(!$obsah){
        
        
        
            $sql = "WITH ranked_data AS 
            ( SELECT a.id_model, a.cas_create, b.hodnota AS typ, c.hodnota AS label, 
            ROW_NUMBER() OVER(PARTITION BY c.hodnota ORDER BY a.cas_create DESC) AS rn 
            FROM model a 
            JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'type' 
            JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'data.read.label'
            WHERE a.model = 'hdts-monitor' ORDER BY label) 
            SELECT id_model, cas_create, typ, label FROM ranked_data WHERE rn <= 2 AND label RLIKE '^PLC' 
            ORDER BY label, cas_create DESC";

            if(@$this->parameter["filter"]){
                $sql = "WITH ranked_data AS 
                ( SELECT a.id_model, a.cas_create, b.hodnota AS typ, c.hodnota AS label, 
                ROW_NUMBER() OVER(PARTITION BY c.hodnota ORDER BY a.cas_create DESC) AS rn 
                FROM model a 
                JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'type' 
                JOIN model_data c ON a.id_model = c.id_model AND c.kluc = 'data.read.label'
                WHERE a.model = 'hdts-monitor' ORDER BY label) 
                SELECT id_model, cas_create, typ, label FROM ranked_data WHERE rn <= 2 AND label RLIKE '^".preg_quote($this->parameter["filter"])."' 
                ORDER BY label, cas_create DESC";
            }

            $db = $this->getDB();
            $p = $db->add_sql($sql,"zoznam");
            $res = $db->cmd();

            $res = $res["zoznam"];
            if(!$res){
                $res = array();
            }

            \app\cash::set($kluc_mem, json_encode($res));

        
        } else {
            $res = json_decode($obsah,true);
        }
        
        
        
        
        
        
        
        $res = array_map(function($item){
            $db = $this->getDB();
            
            $d = $db->getModel($item["id_model"]);

            
            
            $d["create_time"]= $item["cas_create"];
            
            unset($d["data"]["data"]["HEX"]);
            $item["data"]=$d;
            
            $item["data"]["data"]["data"]["read"]["status_word"]= $this->status_word($item["data"]["data"]["data"]["read"]["status_word"]);
            $item["data"]["data"]["data"]["read"]["alarm_word"]= $this->alarm_word($item["data"]["data"]["data"]["read"]["alarm_word"]);
            $item["data"]["data"]["data"]["read"]["status_jazda"]= $this->status_jazda($item["data"]["data"]["data"]["read"]["status_jazda"]);
            $item["data"]["data"]["data"]["read"]["event"]= $this->event($item["data"]["data"]["data"]["read"]["event"]);
            
            return $item;
        }, $res);
        
        

        
        
        $result = $res;
        $data=array();
        
        if(!$result) $result=array();


        usort($result, function($a,$b) {
                $a1= new \DateTime($a["cas_create"]);
                $b1= new \DateTime($b["cas_create"]);
                return $a1>$b1; 
        });
        
        

        //return $this->output($result);
        
        
        foreach ($result as $value) {
            

            
            
            if($value["typ"]=='PLC'){
                $kluc = $value["data"]["data"]["data"]["read"]["label"];
                

                
                
                if(@$data[$kluc]){
                    $value["data"]["previous_time"]=$data[$kluc]["data"]["create_time"];
                    $datetime1 = new \DateTime($value["data"]["previous_time"]);
                    $datetime2 = new \DateTime($value["data"]["create_time"]);
                    $interval = $datetime1->diff($datetime2);
                    $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;                    
                    $value["data"]["time_difference"]=$minutes;
                    $value["data"]["previous_key"]= $value["id_model"];
                    
                }
                
                $interval = (new \DateTime($value["cas_create"]))->diff(new \DateTime());
                $minutes = ($interval->h * 60) + $interval->i;
                
                $value["data"]["time_difference_now"]=$minutes;
                
                
                
                
                $data[$kluc]=$value;
                

                
                
            } 
        }

        
        $data = array_values($data);
        usort($data, function($a,$b) {
                $a1= new \DateTime($a["data"]["create_time"]);
                $b1= new \DateTime($b["data"]["create_time"]);
                return $a1<$b1; 
        });
        
        
        $data = array_map(function($item){
            
            
            $priznak = $item["data"]["data"]["data"]["read"]["event"];

            
            $state = "first_record";
            
            if(@$item["data"]["time_difference"] ){
                $state = "record";
            }

            
            if(@$item["data"]["time_difference"] && @$item["data"]["time_difference"]<=60 && $item["data"]["time_difference_now"]<60 && $priznak["priznak"]=="T"){
                $state = "on";
            }
            
            if(@$item["data"]["time_difference"] && @$item["data"]["time_difference"]<59 && $priznak=="T"){
                $state = "ready";
            }
            

            if( $priznak["priznak"]!="T" && @$item["data"]["time_difference"]>60){
                $state = "event";
            }
            
            
            
            if(@$item["data"]["time_difference_now"] && @$item["data"]["time_difference_now"]>361){
                $state = "error";
            }
            
            $item["event"]=$priznak;
            $item["state"]=$state;
            return $item;
        }, $data);
        

        
        
        return $this->output($data);

    }
    
    public function camera(){
        
       \handler\bind::register("dataCamera", ['\service\fnc\monitor', 'sendMessage']);
        
        
        \handler\bind::CMD("dataCamera", array(
            "title"=>"Monitor",
            "message" => ""
        ));
        
        
        $db = $this->getDB();
        $t = $db->modelTable("camera");
        $result = $t->cmd();
        
        return $this->output($result);
        
    }
    
    
    public function test(){
        
            $rest = new \plc\data();
            $result = $rest->getInterval("PLC.CA.EDV1.");
            return $this->output($result);
        
        
        
        
        

            $address = 'tcp://195.210.28.152:80';
            $socket = stream_socket_client($address, $errno, $errstr, 30);

            if (!$socket) {
                echo "Chyba pri pripojení: $errstr ($errno)\n";
            } else {
                $httpRequest = "test";
                fwrite($socket, $httpRequest);
                while (!feof($socket)) {
                    echo fgets($socket, 1024);
                }
                fclose($socket);
            }


        
        
        exit;
        
        
        $db = $this->getDB();
        $result = $db->updateModel("05d3d6c3-977d-11ef-96e2-00163ef25df0", array("xxx"=>"karol"));
        var_dump($result);
        exit;
        
        
        
        
        
        $isDeviceAvailable = function ($host, $port = 8080, $timeout = 5) {
            $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
            if ($connection) {
                fclose($connection);
                return true; 
            }
            return false;
        };


        $vystup = $isDeviceAvailable("mysql80.r2.websupport.sk",3388);
        
        return $this->output($vystup);
        
        
        $server = $_SERVER["REMOTE_ADDR"];
        
        return $this->output($server);
        
        
        
        
        
        $rest = new \plc\data();
        $x = $rest->getSetting("PLC.SK.STU1.");
        var_dump($x);
        exit;
        
        
        
        return $this->output($x);
        
        
        $db = $this->getDB();
        $db->overitZaznam("");
        
        
        return $this->output($x);
        
        
        
        $word = "0000001101110111";
        $word = "1111111111111111";
        
        
        $decimal_value = bindec( $word); 
        $hex_string = dechex($decimal_value); 
        $hex_string = str_pad($hex_string, 4, '0', STR_PAD_LEFT);
        $hex_string = strtoupper($hex_string);
        
        
        return $this->output($hex_string);
        
        
  
    }
    
    
    
    
    public function setPapago(){
        
        
        $kluc = md5("dataGrafTeplotaAdmin");
        \app\cash::delete($kluc);
        
        
        $db = $this->getDB();
        $db->setModel("papago", $this->parameter);
        //$db->setModel("papago1", $this->parameter);
        
        return $this->output("OK");
    }
    
    /**
     * notifyPLC — previously sent real-time PLC events to api.fullmedia.sk.
     * That external dependency (ex-employee's server) has been removed. No-op stub.
     */
    public function notifyPLC(){
        // No-op: external WebSocket relay removed.
        return $this->output(null, true);
    }
    
    
    public function settingPLC(){
        
        $plc = base64_decode($this->parameter["base_plc"]);
        $sql = "SELECT a.id_model FROM model a 
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='label'
        WHERE model = 'plc' AND b.hodnota= :plc
        ORDER BY a.cas_create DESC LIMIT 1";
        
        $db = $this->getDB();
        $p = $db->add_sql($sql, "zaznam");
        $p->def("plc", $plc);
        $result = $db->cmd();
        $result = $result["zaznam"][0]["id_model"];
        
        $result = $db->getModel($result);
        
        $x = str_split($result["data"]["status_word"]);
        $status_word_data = array();
        
        foreach ($x as $key => $value) {
            $status_word_data[$key]=array(
                "kluc"=>$key,
                "value"=>$value
            );
        }
        
        
        $result["data"]["status_word_data"]= $status_word_data;
        
        
        
        
        return $this->output($result);
    }
    
    

    
    
    public function settingPlan(){
        $plc = base64_decode($this->parameter["base_plc"]);
        $db = $this->getDB();
        
        $zaznam = $db->overitZaznam("setPLC", array("plc"=>$plc, "valid"=>1));
        
        if($zaznam){
            
            $zaznam = $db->getModel($zaznam);
        }
        
        
        
        return $this->output($zaznam);
    }

    
    
    public function set_setting_plc(){
        
        $db = $this->getDB();
        $t = $db->modelTable("setPLC");
        $t->addFilter("plc", $this->parameter["plc"]);
        $t->addFilter("valid", 1);
        $result = $t->cmd();

        foreach ($result as $value) {
            $db->updateModel($value["id_model"], array("valid"=>0));
            //$db->deleteModel($value["id_model"]);
        }
        
        
        $this->parameter["valid"]=1;
        $result = $db->setModel("setPLC", $this->parameter);

        
        return $this->output($result);
    }
    
    public function get_setting_plc(){
        
        if(!@$this->parameter["plc"]){
            return $this->output(array());
        }
        
        $db = $this->getDB();
        $kluc = $db->overitZaznam("setPLC", array("plc"=> $this->parameter["plc"], "valid"=>1));
        
        $zaznam = $db->getModel($kluc);
        $data = array();
        
        /*
        $file = root."/plc/send_zaznam.json";
        file_put_contents($file, json_encode($zaznam));
        */
        
        foreach ($zaznam["data"]["list_cmd"] as $value) {
            
            if($value["type"]=='x'){
                $cmd =bin2hex("OK".$value["cmd"]."\n");
                $data[] = strtoupper($cmd);
            }
            
            if($value["type"]=='word'){
                $b = str_split($value["value"]);
                $b = array_reverse($b);
                $value["value"] = implode("", $b);
                // Musim spravit revertny zaznam
                
                
                $cmd =bin2hex("OK".$value["cmd"]);
                $decimal_value = bindec( $value["value"]); 
                $hex_string = dechex($decimal_value); 
                $hex_string = str_pad($hex_string, 4, '0', STR_PAD_LEFT);
                $data[] = strtoupper($cmd.$hex_string.bin2hex("\n"));
            }

            if($value["type"]=='pocet' ){
                $cmd =bin2hex("OK".$value["cmd"]);
                $hex = sprintf("%04x", intval($value["value"]));
                $data[] = strtoupper($cmd.$hex.bin2hex("\n"));
            }
            
            if($value["type"]=='interval' ){
                $cmd =bin2hex("OK".$value["cmd"]);
                $hex = sprintf("%04x", intval($value["value"]));
                $data[] = strtoupper($cmd.$hex.bin2hex("\n"));
            }
            
            
            if($value["type"]=='server' ){
                
                if (filter_var($value["value"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $octets = explode('.', $value["value"]);
                    $hex = sprintf("%02X%02X%02X%02X", $octets[0], $octets[1], $octets[2], $octets[3]);
                    $cmd =bin2hex("OK".$value["cmd"]);
                    $data[] = strtoupper($cmd.$hex.bin2hex("\n"));
                } 
            }
            
            
        }
        
        // Upravit user a urobit update
        //$db->deleteModel($kluc);
        /*
        $file = root."/plc/send_command.json";
        file_put_contents($file, json_encode($data));
        */
        
        $db->updateModel($kluc, array("valid"=>0));
        
        return $this->output($data);
        
        
        
    }
    

    public function getSystemInfo(){
        $db= $this->getDB();
        $t = $db->modelTable("system_info");
        $result = $t->cmd();

        
        $collator = new \Collator('en_US');
        usort($result, function($a,$b) use($collator){
                $a1=@$a["data"]["hostname"];
                $b1=@$b["data"]["hostname"];
                return $collator->compare($a1, $b1);  
        });
        
        
        return $this->output($result);
        
        
        
    }
    
    
    
    public function systemInfo(){
        
        $data = $this->parameter;
        
        if(!$data){
            return $this->output(false,false);
        }
        
        if(!$data["hostname"]){
           return $this->output("Nie je urceny hostname !!!",false); 
        }
        
        
        
        
        $db= $this->getDB();
        
        
        $t = $db->modelTable("system_info");
        $t->addFilter("hostname", $data["hostname"]);
        $r = $t->cmd();
        
        
        foreach ($r as $value) {
            $db->deleteModel($value["id_model"]);
        }
        
        
        
        $result = $db->setModel("system_info", $data);
        
        
        $x = array(
            "uuid"=>$result,
            "data"=>$data
        );
        
        
        return $this->output($x);
    }
    
    
    
}
