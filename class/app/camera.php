<?php
namespace app;
class camera {
    private $address = null;
    
    private function setDotazMonitor($url, $data=null){


        $data = array("url"=>$url);
        $rest = new \service\fnc\service();
        $result = $rest->sendServerData($data, "get_url");
        $result = json_decode($result,true);
        $result = @$result["body"];
        $result = json_decode($result, true);
        
        return $result;
    }
    
    
    public function __construct($address) {
        $this->address = $address;
    }
    
    public function getStreamingStatus(){
        $ip= $this->address;
        $url = "http://$ip/q/getStreamingStatus";
        
        return $this->setDotazMonitor($url);
        

    }
    
    public function getObjects(){
        $ip= $this->address;
        
        
        $cash = \app\cash::get(md5($ip)."getObjects");
        $randomNumber = rand(1, 10);
        if($randomNumber==5){
            $cash = null;
        }
        
        $cash=null;
        
        if($cash){
            return $cash;
        }

        $url = "http://$ip/command.cgi?cmd=getObjects";
        $result = $this->setDotazMonitor($url);
        \app\cash::set(md5($ip)."getObjects", $result);
        return $result;
    }
    
    public function getObject($oid, $ot=2){
        $ip= $this->address;
        $query = array(
            "cmd"=>"getObject",
            "oid"=>$oid,
            "ot"=>$ot
        );
        
        $query = http_build_query($query);
        
        $url = "http://$ip/q.json?$query";
        $result = $this->setDotazMonitor($url);
        
        
        return $result;
        
    }
    
    public function getCameraGrabs($oid, $ot=2){
        $ip= $this->address;
        $query = array(
            "oid"=>$oid,
            "ot"=>$ot
        );
        
        $query = http_build_query($query);
        
        
        
        $url = "http://$ip/q/getCameraGrabs?$query";
        $result = $this->setDotazMonitor($url);
        
        
        return $result;
    }
    
    
    public function nahlad($oid){
        $ip= $this->address;
        
        /*
        $cash = \app\cash::get(md5($ip)."img_camera_".$oid);
        $randomNumber = rand(1, 10);
        if($randomNumber==5){
            $cash = null;
        }
        
        $cash=null;
        
        if($cash){
            return $cash;
        }
        */
        
        
        $query = array(
            "oid"=>$oid,
            "fn"=>"abc.jpg"
        );
        
        $query = http_build_query($query);

        
        $url = "http://$ip/fileThumb.jpg?$query";
        $data = array("url"=>$url);
        $rest = new \service\fnc\service();
        $result = $rest->sendServerData($data, "binary");
        

        
        $base64Image = base64_encode($result);
        $base64Image = "data:image/jpeg;base64,".$base64Image;

        \app\cash::set(md5($ip)."img_camera_".$oid, $base64Image, 60*60);
        
        return $base64Image;
        
    }
    
    
    
    public function getPhoto($oid){
        
        
        $ip= $this->address;
        $file = md5($this->address.$oid).".jpg";
        
        
        
        return $file;
        
        
        $query = array(
            "oid"=>$oid
        );
        
        $query = http_build_query($query);

        $url = "http://$ip/photo.jpg?$query";
        $data = array("url"=>$url);
        $rest = new \service\fnc\service();
        $result = $rest->sendServerData($data, "binary");
        
        
        $image_data = $result;
        $source_image = imagecreatefromstring($image_data);

        if ($source_image === false) {
            die("Obrázok sa nepodarilo načítať.");
        }


        $width = imagesx($source_image);
        $height = imagesy($source_image);

        $new_width = 200;
        $new_height = (int) ($height * $new_width / $width);

        $resized_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        ob_start();
        imagejpeg($resized_image);
        $resized_image_data = ob_get_clean();
        imagedestroy($source_image);
        imagedestroy($resized_image);

        $file = md5($this->address.$oid).".jpg";
        file_put_contents(root."/source/camera/".$file, $resized_image_data);
        
        
        return $file;

    }
    
    
    public function test(){
        
        $ip = $this->address;
        
        $query = array(
            "cmd"=>"addCamera",
            "oid"=>1,
            "name"=>"Test PPPPP"
        );
        $query = http_build_query($query);
        
        $url = "http://$ip/q.json?$query";
        $data = array("url"=>$url);

        $result = $this->setDotazMonitor($url);
        
        return $result;
        
        
        
    }
    
    
}
