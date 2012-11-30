<?php

class ScannerFunctionReplacements {
    
    /**
    * Check if a constant was defined
    * @param string $const_name
    */
    public static function defined($const_name) {
        $constants_unable_to_check = array(
            '__LINE__',
            '__FILE__',
            '__DIR__',
            '__FUNCTION__',
            '__CLASS__',
            '__TRAIT__',
            '__METHOD__',
            '__NAMESPACE__'
        );
        if(in_array($const_name, $constants_unable_to_check)) {
            return false;
        }
        
        $var = ScanInfo::findVar($const_name, 'superglobal');
        return (bool) ($var);
    }
}