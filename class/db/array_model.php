<?php

namespace db;
class array_model {
    public static function code(array $array, $currentPath = ''){
        
        if(!is_array($array)){
            return null;
        }
        
        $paths = [];

         foreach ($array as $key => $value) {
             $newPath = $currentPath === '' ? $key : $currentPath . '.' . $key;

             if (is_array($value)) {
                 $paths = array_merge($paths, self::code($value, $newPath));
             } else {
                 $paths[$newPath] = $value;
             }
         }

         return $paths;
    }
    
    public static function decode(array $paths){
        $array = [];

        foreach ($paths as $path => $value) {
            $keys = explode('.', $path);
            $current = &$array;

            foreach ($keys as $key) {
                if (!isset($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }

            $current = $value;
        }

        return $array;
    }
}
