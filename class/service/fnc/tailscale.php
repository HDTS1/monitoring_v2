<?php
namespace service\fnc;
class tailscale  extends \service\baseExtend {
    private $tailnet;
    private $token;
    
    public function __construct() {
        // These credentials should be set as environment variables:
        // TAILSCALE_TAILNET and TAILSCALE_TOKEN
        $this->tailnet = getenv('TAILSCALE_TAILNET') ?: '';
        $this->token   = getenv('TAILSCALE_TOKEN')   ?: '';
    }
    
    public function test(){
        //\app\cash::delete("tailscale");
        if(\app\cash::get("tailscale")){
            return $this->output(\app\cash::get("tailscale"), true);
        }
        
        
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => "https://api.tailscale.com/api/v2/tailnet/".$this->tailnet."/devices",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => [
            "Authorization: Bearer ".$this->token
          ],
        ]);

        
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          \app\cash::delete("tailscale");
          return $this->output($err, false);
        } else {
          $response = json_decode($response,true);

          
          \app\cash::set("tailscale", $response);
          return $this->output($response, true);
        }
    
    }

}
