<?php

/**
 * Information about functions that are executable without changing the environment.
 * These functions can be used for dynamic source code analysing.
 */


function argf() {
    $args = func_get_args();
    $return = array();
    $key = null;
    foreach($args as $arg) {
        if(is_string($arg)) {
            $key = $arg;
            $return[$key] = array();
        }
        elseif(is_int($arg) && !empty($key)) {
            $return[$key][] = $arg;
        }
    }
    return $return;
}


function import_csv($file) {
    $seperator = ";";
    $newline   = "\n";
    $file = file($file);
    
    foreach($file as $line) {
        $elems = explode($seperator, $line);
        
        // skip if it hasnt been defined yet
        if(!(bool) $elems[0]) {continue;}
        
        // export data
        list($use, $name, $module, $scope, $userdef, $executable, $return_type, 
             $return_defined_by_param, $return_secured_for, 
             $param_vuln_for, $func_to_check_vulnerability) = $elems;
        
        // add func
        $func = new Obj_Function();
        $func->setName($name);
        $func->setExcecutable($executable);
        $func->setFunctionToCheckForVulnerability(explode(",", $func_to_check_vulnerability));
        $func->setReturnByRef(false);
        $func->setScope('');
        $func->setSecuringFor(explode(",", $return_secured_for));
        $func->setUserDefined($userdef);
        $func->setVulnerableFor($functions);

        ScanInfo::addFunction($func);
    }
}


import_csv('functions.csv');