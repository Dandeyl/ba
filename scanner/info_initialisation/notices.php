<?php
/**
 * This class holds information about code passages that might, but don't neccessarily have to lead to security
 * vulnerabilities.
 */
abstract class Information_Notices {
    /**
     * A variable was used, that was not initialised before.
     * With register_globals == "off" this can lead to vulnerabilities.
     */
    public static function Uninitialised_Variable_Used($var_name, $scope) {
        $msg = 'Variable '.$var_name.' not initialised';
        
        throw new Analyser_Notice($msg);
    }
}