<?php
namespace plc;
class data {

    /**
     * notifyPLC — previously sent real-time PLC events to api.fullmedia.sk.
     * That external dependency (ex-employee's server) has been removed.
     * No-op stub: implement your own WebSocket notification here if needed.
     */
    public function notifyPLC($label){
        // No-op: external WebSocket relay removed.
        return null;
    }


    
    
    public function setData($table,$data){
        $db = new \db\sql();
        
        $md5 = ["label","event","cycle_count","total_distance","alarm_word","run_time"];
        $md5 = array_map(function($item) use($data){
            return $data[$item];
        }, $md5);
        $md5 = implode("", $md5);
        $md5 = md5($md5);
        
        $data["md5"]=$md5;
        $kluc = $db->setModel($table, $data);
        
        
        // Option B: Maintain a single 'plc_live' record per PLC label
        if ($table === 'plc') {
            $sqlDel = "DELETE FROM model WHERE model = 'plc_live' AND id_model IN (
                SELECT id_model FROM (
                    SELECT a.id_model FROM model a
                    JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'label'
                    WHERE a.model = 'plc_live' AND b.hodnota = :label
                ) tmp
            )";
            $pDel = $db->add_sql($sqlDel, "zmazat_live");
            $pDel->def("label", $data["label"]);
            $db->cmd();
            
            $db->setModel("plc_live", $data);
        }

        $sql = "with zaznam AS (SELECT a.id_model, ROW_NUMBER() OVER(PARTITION BY b.hodnota ORDER BY  a.cas_create DESC) AS rn
        FROM model a
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc = 'md5'
        WHERE a.model = 'plc' AND b.hodnota= :md5 )
        DELETE FROM model WHERE id_model IN(SELECT id_model FROM zaznam
        WHERE rn>1)";
        $p= $db->add_sql($sql, "zmazat");
        $p->def("md5", $md5);
        $db->cmd();
        
        
                
        
        $this->notifyPLC(@$data["label"]);
        \app\cash::delete("plc_event_".$data["label"]); 
        
        return $kluc;
    }
    
    public function getInterval($plc){
        
        $sql = "SELECT a.id_model
        FROM model a
        JOIN model_data b ON a.id_model= b.id_model AND b.kluc='valid'
        JOIN model_data c ON a.id_model= c.id_model AND c.kluc='plc'
        JOIN model_data d ON a.id_model= d.id_model AND d.hodnota ='CTI'
        WHERE a.model='setPLC' AND c.hodnota= :plc
        ORDER BY a.cas_create DESC LIMIT 1";
        
        
        $db = new \db\sql();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("plc", $plc);
        $result = $db->cmd();
        $result = @$result["zoznam"][0];
        
        if(!$result){
            return null;
        }
        
        
        $result = $db->getModel($result["id_model"]);
        $result = array_filter($result["data"]["list_cmd"], function($item){
            return $item["cmd"]=='CTI';
        });
        $result = array_values($result);
        $result = $result[0]["value"];
        
        
        return $result;
        
   
    }
    
    
    
    public function getSetting($plc){


                $rest = new \service\fnc\monitor();
                $rest->parameter= array(
                    "plc"=>$plc
                );
                $result = $rest->get_setting_plc();
                $result = $result["data"];


               return $result;

    }
    
    public function runStatistic($plc, $from, $to){
       $sql = "with zaznam AS 
        (SELECT a.cas_create, b.hodnota AS label, convert(f.hodnota, DECIMAL(4,0)) aS rychlost,
        IFNULL(c.hodnota - LAG(c.hodnota) OVER (ORDER BY a.cas_create), 0) AS pocet_cycle_count,
        IFNULL(d.hodnota - LAG(d.hodnota) OVER (ORDER BY a.cas_create), 0) AS pocet_run_time,
        IFNULL(e.hodnota - LAG(e.hodnota) OVER (ORDER BY a.cas_create), 0) AS pocet_total_distance
        FROM model a
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc='cycle_count'
        JOIN model_data d ON a.id_model = d.id_model AND d.kluc='run_time'
        JOIN model_data e ON a.id_model = e.id_model AND e.kluc='total_distance'
        JOIN model_data f ON a.id_model = f.id_model AND f.kluc='rychlost_pasu'
        WHERE a.model='plc' AND b.hodnota= :plc )
        SELECT SUM(pocet_cycle_count) AS cycle_count, SUM(pocet_run_time) AS run_time,
        SUM(pocet_total_distance) AS total_distance, convert(MAX(rychlost),DECIMAL(5,1)) AS max_rychlost_pas, convert(avg(rychlost),DECIMAL(5,1))AS avg_rychlost_pas
        FROM zaznam WHERE DATE(cas_create) BETWEEN :from AND  :to"; 
       
        $sql = "with zaznam AS (
        with zaznam AS (
        SELECT a.cas_create, b.hodnota AS label,
        c.hodnota AS cycle_count, convert(f.hodnota, DECIMAL) AS rychlost,
        LAG(c.hodnota) OVER (ORDER BY a.cas_create) AS previous
        FROM model a
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc='cycle_count'
        JOIN model_data f ON a.id_model = f.id_model AND f.kluc='rychlost_pasu'
        WHERE a.model='plc' AND b.hodnota= :plc AND DATE(a.cas_create) BETWEEN :from AND :to
        )
        SELECT *, cycle_count - previous AS pocet  
        FROM zaznam WHERE not previous IS NULL AND cycle_count - previous > 0)
        SELECT  SUM(pocet) AS cycle_count, 
        convert(MAX(rychlost), decimal(4,1)) AS max_speed, convert(AVG(rychlost), decimal(4,1)) as avg_speed, convert(min(rychlost), decimal(4,1)) as min_speed
        FROM zaznam
        HAVING cycle_count>0";
       
       
       
        $from = (new \DateTime($from))->format("Y-m-d");
        $to = (new \DateTime($to))->format("Y-m-d");
       
       
        $db = new \db\sql();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("plc", $plc);
        $p->def("from", $from);
        $p->def("to", $to);
        
        $result = $db->cmd();
        $result = @$result["zoznam"][0];

    if(!$result){
        $result =array(
            "cycle_count"=>0,
            "avg_speed"=>0,
            "min_speed"=>0,
            "max_speed"=>0
        );
    }
        
        
        /*
        $data = array(
            "cycle_count"=> $result["cycle_count"],
            "run_time"=> $result["run_time"],
            "total_distance"=> $result["total_distance"],
            "max_speed"=>$result["max_rychlost_pas"],
            "avg_speed"=>$result["avg_rychlost_pas"],
            "plc"=>$plc,
            "range"=>array(
                "from"=>$from,
                "to"=>$to
            )
        );
        */
        
        $data = array(
            "cycle_count"=> !@$result["cycle_count"] ? 0 : $result["cycle_count"],
            "run_time"=> 0,
            "total_distance"=> 0,
            "max_speed"=>$result["max_speed"],
            "avg_speed"=>$result["avg_speed"],
            "min_speed"=>$result["min_speed"],
            "plc"=>$plc,
            "range"=>array(
                "from"=>$from,
                "to"=>$to
            )
        );
        
        
        
        return $data;
       
        
        
    }
    
    
    public function lastZaznam($plc){
        $sql ="SELECT a.id_model 
        FROM model a
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='label'
        WHERE a.model = 'plc' AND b.hodnota = :plc
        ORDER BY a.cas_create DESC LIMIT 1
        ";
        
        $db = new \db\sql();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("plc", $plc);
        $result = $db->cmd();
        $result = @$result["zoznam"][0]["id_model"];
        
        $result = $db->getModel($result);
        
        
        return $result;
    }
    
    
    public function lastRun($plc){
        $sql = "SELECT a.id_model, a.cas_create, b.hodnota as label, c.hodnota as ubehnuta_vzdialenost, d.hodnota as alarm_word, e.hodnota as sklon_pas,
        f.hodnota as rychlost_pasu
        FROM model a 
        JOIN model_data b ON a.id_model=b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model=c.id_model AND c.kluc='ubehnuta_vzdialenost'
        JOIN model_data d ON a.id_model=d.id_model AND d.kluc='alarm_word'
        JOIN model_data e ON a.id_model=e.id_model AND e.kluc='sklon_pas'
        JOIN model_data f ON a.id_model=f.id_model AND f.kluc='rychlost_pasu'
        WHERE model = 'plc' AND b.hodnota=:plc
        ORDER BY a.cas_create DESC LIMIT 1";
                
        $db = new \db\sql();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("plc", $plc);
        
        $result = $db->cmd();
        $result = @$result["zoznam"][0];
        
        
        return $result;
    }
    
    
    public function getCycle($plc, $from, $to){
        $sql = "with zaznam AS 
        (SELECT a.cas_create, b.hodnota AS label,
        c.hodnota AS cycle_count,
        IFNULL(c.hodnota - LAG(c.hodnota) OVER (ORDER BY a.cas_create), 0) AS pocet
        FROM model a
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc='cycle_count'
        WHERE a.model='plc' AND b.hodnota= :plc)
        SELECT DATE(cas_create) AS `den`, SUM(pocet) AS cycle_count
        FROM zaznam
        GROUP BY `den`
        HAVING den BETWEEN :from AND  :to";
        
        // Pridane speed
        $sql = "with zaznam AS (
        with zaznam AS (
        SELECT a.cas_create, b.hodnota AS label,
        c.hodnota AS cycle_count, convert(f.hodnota, DECIMAL) AS rychlost,
        LAG(c.hodnota) OVER (ORDER BY a.cas_create) AS previous
        FROM model a
        JOIN model_data b ON a.id_model = b.id_model AND b.kluc='label'
        JOIN model_data c ON a.id_model = c.id_model AND c.kluc='cycle_count'
        JOIN model_data f ON a.id_model = f.id_model AND f.kluc='rychlost_pasu'
        WHERE a.model='plc' AND b.hodnota= :plc AND DATE(a.cas_create) BETWEEN :from AND :to
        )
        SELECT *, cycle_count - previous AS pocet  
        FROM zaznam WHERE not previous IS NULL AND cycle_count - previous > 0)
        SELECT DATE(cas_create) AS `den`, SUM(pocet) AS cycle_count, 
        MAX(rychlost) AS max_speed, AVG(rychlost) as avg_speed, min(rychlost) as min_speed
        FROM zaznam
        GROUP BY `den` HAVING cycle_count>0";
        
        
        $from = (new \DateTime($from))->format("Y-m-d");
        $to = (new \DateTime($to))->format("Y-m-d");
        
        
        $db = new \db\sql();
        $p = $db->add_sql($sql, "zoznam");
        $p->def("plc", $plc);
        $p->def("from", $from);
        $p->def("to", $to);
        
        $result = $db->cmd();
        $result = $result["zoznam"];
        
        
        
        
        $result = array(
            "data"=>$result,
            "last_zaznam"=> $this->lastZaznam($plc),
            "range"=>array(
                "from"=>$from,
                "to"=>$to
            )
        );
        
        
        return $result;

    }
    
    
}
