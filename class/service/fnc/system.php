<?php
namespace service\fnc;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class system  extends \service\baseExtend {

    public function test(){
        $db = new \db\sql();
        $sql = "SELECT @@global.time_zone, @@session.time_zone";
        $db->add_sql("SET time_zone = 'America/Edmonton'", "pasmo");
        $db->add_sql($sql, "zaznam");
        $db->add_sql("select now()", "cas");
        
        $result = $db->cmd();
        
        return $this->output($result);
        
        
    }
    
    
    /**
     * notify — previously connected to wss://echo.fullmedia.sk (ex-employee's WebSocket server).
     * That external dependency has been removed. No-op stub.
     */
    public function notify(){
        // No-op: external WebSocket relay removed.
        return $this->output("disabled", true);
    }

    
    
    private function mail($data){
   
        // Vytvorenie inštancie PHPMailer
        $mail = new PHPMailer(true);
        $kluc = $data["kluc"];
        $address = $data["username"];

        try {
            // SMTP configuration from environment variables
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.purelymail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('SMTP_USER') ?: '';
            $mail->Password   = getenv('SMTP_PASS') ?: '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = intval(getenv('SMTP_PORT') ?: 587);
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Príjemca
            $fromAddress = getenv('SMTP_FROM') ?: getenv('SMTP_USER') ?: 'noreply@beesport.online';
            $mail->setFrom($fromAddress, 'Token beeSPORT');
            $mail->addAddress($address, $address);

            // Obsah e-mailu
            $mail->isHTML(true);
            $mail->Subject = 'Your verification beeSPORT';
            $mail->Body    = "<div>Your verification key for login:: <b>".$kluc."</b></div>";
            $mail->AltBody = "Your verification key for login:: ".$kluc;
            
            // Odoslanie e-mailu
            $mail->send();
        } catch (Exception $e) {
           echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
           exit;
        } 
        
        $result = [];
        $result["result"]=true;
        $result["data"]= "OK";
        return $result;
        
        
    }
    
    public function overitKluc(){
        unset($this->parameter["validate"]);
        $rest = new \service\fnc\user();
        $rest->parameter = $this->parameter;
        $result = $rest->overitKluc();

        return $this->output($result["data"],$result["result"]);

    }
    
    public function overitEmail(){
        
        unset($this->parameter["validate"]);
        
        $generateRandomKey = function ($length = 8) {
            $characters = '0123456789abcd';
            $charactersLength = strlen($characters);
            $randomKey = '';
            for ($i = 0; $i < $length; $i++) {
                $randomKey .= $characters[rand(0, $charactersLength - 1)];
            }
            
            $randomKey= strtoupper($randomKey);
            
            return $randomKey;
        };
        
        $kluc = $generateRandomKey();
        
        $rest = new \service\fnc\user();
        $rest->parameter = $this->parameter;
        $result = $rest->setUser();
        $result = $result["data"];
        
        
 
        $rest->parameter = array(
            "user_key"=>$result,
            "user_token"=>token_user,
            "kluc"=>$kluc
        );
        
        $result = $rest->setUserToken();
        $result = $result["data"];
        
        $this->mail(array(
            "username"=> $this->parameter["username"],
            "kluc"=>$kluc,
            "result"=>$result
         ));
        
        
        return $this->output($result);  
    }

    public function getTemplate(){
        $kluc =  md5(@$this->parameter["template"]."_". json_encode(@$this->parameter['data']));

        
        $cash = \app\cash::get($kluc);
        if($cash){
            return $this->output($cash);  
        }
        
        
            
        
         if(!@$this->parameter["template"] || !is_array(@$this->parameter['data'])){
            return $this->output("", false);
         }

         
         $template = $this->setTemplate($this->parameter["template"], @$this->parameter['data']);
         
         \app\cash::set($kluc, $template, 60);
         return $this->output($template);    
    } 

    
    public function getPlatba(){
        
        $conf = json_decode(file_get_contents(root."/cfg/platby.json"),true);
        
        $data = [];
        
        foreach ($conf["mesiac"] as $mesiac) {
            
            $s =  new \DateTime();
            $s->setDate($mesiac["rok"],$mesiac["mesiac"]+1 , 8);
            
            
            $mesiac["splatnost"]= $s->format("Y-m-d");
            
            
            $mesiac["platba"] = array_map(function($item) use($conf) {
                $item["subject"]=$conf["subject"][$item["subject"]];
                return $item;
            }, $mesiac["platba"]);
            
            /*
            usort($mesiac["platba"], function($a,$b){
                return ($a["rok"] *100) + $a["mesiac"] > ($b["rok"]*100) + $b["mesiac"];
            });
            */
            
            usort($mesiac["platba"], function($a,$b){
                $a = new \DateTime($a["date"]);
                $b = new \DateTime($b["date"]);
                return $a > $b;
            });
            
            
            $mesiac["zaplatene"]= @$mesiac["platba"][count($mesiac["platba"])-1]["date"];
            
            
            $z = $mesiac["zaplatene"];
            
            
            if(!$z){
                $z= new \DateTime();
                $z = $z->format("Y-m-d");
            }
            
            
            
            $rozdiel = (new \DateTime($mesiac["splatnost"])) ->diff((new \DateTime($z)));
            
            $mesiac["dif"]= intval($rozdiel->format('%r%a'));
            if($mesiac["dif"]<0 ) $mesiac["dif"]=0;
            
            $data[] = $mesiac;
        }
        

        
        
        return $this->output($data);   
    }
    

    
}
