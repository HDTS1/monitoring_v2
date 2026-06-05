<?php
namespace PHPWee;
class Minify {
    
    public static function html($html){
            return HtmlMin::minify($html);
    }

    public static function minifyHTML($html){
            return HtmlMin::minifyHTML($html);
    }      
    
    public static function minifyXML($html){
            return HtmlMin::minifyXML($html);
    }    
    
    
    public static function css($css){
            return CssMin::minify($css);
    }

    public static function js($js){
            return JsMin::minify($js);
    }
}
