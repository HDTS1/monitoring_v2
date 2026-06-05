<?php
namespace plc;




class dataDef {
    private function fnc_ascii($input){
        return $input;
    }
    
    private function fnc_int16($input){
        $result = unpack("n", $input);
        return $result[1];
    }
    
    private function fnc_int32($input){
        $result = unpack("N", $input);
        return $result[1];
    }
    
    private function fnc_word($input){
        $result = unpack("n", $input);
        $number =  $result[1];        
        $bits = '';
        for ($i = 0; $i <= 15; $i++) {
            $bit = ($number >> $i) & 1;
            $bits .= $bit;
        }
        return $bits;

    }
    
    private function fnc_int16_date($input){
            $result = unpack("n", $input);
            $number =  $result[1];   
            $number = intval($number) * 24 * 60 *60;
            $d = new \DateTime();
            $d->setTimestamp($number);
            $d->modify('+20 year');
            return $d->format("Y-m-d");
    }
    
    
    private function convertToSignedInt($value) {
        if ($value >= 0x8000) {
            $value -= 0x10000;
        }
        return $value;
    }
    
    
    private function fnc_int16_s($input){
        //$input= pack("H*","FFFE");
        $result = unpack("n", $input)[1];
        //var_dump($result);
        $result = $this->convertToSignedInt($result);
        //var_dump($result);
        return $result;
    }    
    
    private function def(){
        
        $f= array();
        
        $f[]= ["typ"=>"ascii","pole"=>"label", "dlzka"=>12];
        $f[]= ["typ"=>"int16","pole"=>"count_packet", "dlzka"=>2];
        $f[]= ["typ"=>"int32","pole"=>"total_distance", "dlzka"=>4];
        $f[]= ["typ"=>"int16","pole"=>"run_time", "dlzka"=>2];
        $f[]= ["typ"=>"int32","pole"=>"cycle_count", "dlzka"=>4];
        $f[]= ["typ"=>"word","pole"=>"alarm_word", "dlzka"=>2];
        $f[]= ["typ"=>"word","pole"=>"status_word", "dlzka"=>2];
        $f[]= ["typ"=>"int16_date","pole"=>"ptfe_datum", "dlzka"=>2];
        $f[]= ["typ"=>"int16","pole"=>"ptfe_pocitadlo_mazacich_cyklov", "dlzka"=>2];
        
        $f[]= ["typ"=>"int16_date","pole"=>"rocny_servis_datum", "dlzka"=>2];
        $f[]= ["typ"=>"int16","pole"=>"pocet_dni_do_servis", "dlzka"=>2];        
        
        $f[]= ["typ"=>"int16","pole"=>"pocet_moto_hod_do_servis", "dlzka"=>2]; 
        $f[]= ["typ"=>"int16","pole"=>"zostavajuca_vzdialenost_do_servis", "dlzka"=>2]; 
        $f[]= ["typ"=>"int16","pole"=>"zostavajuce_dni_do_obmedzenia_rychlosti", "dlzka"=>2]; 
        $f[]= ["typ"=>"int16_date","pole"=>"datum_pozadovanej_platby", "dlzka"=>2]; 
        
        $f[]= ["typ"=>"int16","pole"=>"prud_motora_max_rozbehovy", "dlzka"=>2]; 
        $f[]= ["typ"=>"int16","pole"=>"prud_motora_priemerny", "dlzka"=>2]; 
        $f[]= ["typ"=>"int16","pole"=>"rychlost_pasu", "dlzka"=>2];  
        $f[]= ["typ"=>"int16","pole"=>"ubehnuta_vzdialenost", "dlzka"=>2];
        
        
        $f[]= ["typ"=>"int16_s","pole"=>"sklon_pas", "dlzka"=>2]; 
        $f[]= ["typ"=>"int16","pole"=>"status_jazda", "dlzka"=>2]; 
        $f[]= ["typ"=>"ascii","pole"=>"event", "dlzka"=>1];
        
        return $f;
        
    }

    public function getData($message){
       $vystup = array(); 
       $i = 0;
       $pole = $this->def();
       
       foreach ($pole as $value) {
           $input = substr($message, $i, $value["dlzka"]);
           $fnc = "fnc_".$value["typ"];
           $vystup[$value["pole"]] = $this->$fnc($input);
           $i += $value["dlzka"];
       }
       
       return $vystup;
       
    }
}
