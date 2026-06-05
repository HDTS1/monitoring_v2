<?php
namespace app;
class remote {
    public function sendDataAsync1($server, $data){
        
       $data = http_build_query($data);
        
       $ch = curl_init($server); 
       curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
       curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
       //curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);    
       
       curl_exec($ch);
       curl_close($ch);
        
    }
    
    
    function sendDataAsync($url, $data) {

        // Rozdelenie URL na časti
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'];
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
        $port = isset($parsedUrl['port']) ? $parsedUrl['port'] : 443;  // HTTPS port (443)
        // Serializovanie údajov na URL-enkódovaný formát
        $postData = http_build_query($data);
        $contentLength = strlen($postData);

        // Vytvorenie kontextu pre SSL
        $options = [
            "ssl" => [
                "verify_peer" => false, // Zakáže overovanie certifikátu
                "verify_peer_name" => false, // Zakáže overovanie názvu servera
            ],
        ];
        $context = stream_context_create($options);

        // Nastavenie pripojenia na HTTPS s kontextom
        $fp = stream_socket_client("ssl://$host:$port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
        if (!$fp) {
            // Chyba pri pripojení
            echo "Chyba pri pripojení: $errstr ($errno)\n";
            return;
        }

        // Vytvorenie HTTP požiadavky
        $request = "POST $path HTTP/1.1\r\n";
        $request .= "Host: $host\r\n";
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: $contentLength\r\n";
        $request .= "Connection: Close\r\n\r\n";
        $request .= $postData;

        // Odoslanie požiadavky
        fwrite($fp, $request);

        // Čítanie odpovede zo servera]
        $response = '';
        while (!feof($fp)) {
        $response .= fgets($fp, 1024); // Čítanie po častiach (každých 1024 bajtov)
        }


        fclose($fp);
    }

}
