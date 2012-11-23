<?php

class ScannerFunctionReplacements {
    
    /**
    * Check if a constant was defined
    * @param string $const_name
    */
    public static function defined($const_name) {
        $var = ScanInfo::findVar($const_name, 'superglobal');
        return (bool) ($var);
    }

}