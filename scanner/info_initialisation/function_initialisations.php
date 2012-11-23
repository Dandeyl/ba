<?php

/**
 * Information about functions that are executable without changing the environment.
 * These functions can be used for dynamic source code analysing.
 */


function arr_kv() {
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

/**
 * Splits a string into an array of values. If no seperator is given, a string will be returned, an array otherweise.
 * Values "1", "true" and "yes" will be converted to true, "0" and "false" to false. "" and "null" to null.
 * @param string $param
 * @param string|null $seperator
 * @return array|string
 */
function funcini_check_value($param) {
    switch ($param) {
        case 'null':
        case '':
            $param = null;
            break;
        case '1':
        case 'true':
        case 'yes':
            $param = true;
            break;

        case '0':
        case 'false':
            $param = false;
            break;
    }
    return $param;
}


function funcini_check_array($param, $seperator) {
    $parameters = explode($seperator, $param);
    
    foreach($parameters as $idx => $p) {
        switch ($p) {
            case 'null':
            case '':
                $parameters[$idx] = null;
                break;
            case '1':
            case 'true':
            case 'yes':
                $parameters[$idx] = true;
                break;
            
            case '0':
            case 'false':
                $parameters[$idx] = false;
                break;
            case '*':
                return array();
            
        }
    }
    
    return array_filter($parameters);
}

function funcini_return_source($source) {
    switch($source) {
        case '':
        case 'null':
            return 0;
        case 'user':
            return 1;
        case 'file':
        case 'db':
        case 'os':
        case 'system':
            return 2;
    }
}



function import_csv($file) {
    $seperator = ";";
    $file = file(dirname(__FILE__).'/'.$file);
    
    foreach($file as $line) {
        $line  = trim($line); 
        $elems = explode($seperator, $line);
        
        // skip if the function hasnt been defined yet
        if($elems[0] != '1') {continue;}
        
        // export data
        list($use, $name, $module, $scope, $userdef, $executable, $return_type, 
             $params_defining_return, $return_add_securing, $return_remove_securing, $return_source,
             $vuln_for, $args_vulnerable, $func_to_check_vulnerability, $func_replacement) = $elems;
        
        // add func
        $func = new Obj_Function();
        $func->setName($name);
        $func->setUserDefined(funcini_check_value($userdef));
        $func->setExcecutable(funcini_check_value($executable));
        
        $func->setReturnByRef(false);
        $func->setReturnType($return_type);
        $func->setScope($scope);
        $func->setReturnAddSecuring(Securing::get($return_add_securing));
        $func->setReturnRemoveSecuring(Securing::get($return_remove_securing));
        $func->setReturnUserDefined(funcini_return_source($return_source));
        $func->setReturnDefinedByParams(funcini_check_array($params_defining_return, ','));
        $func->setVulnerableFor(Vulnerability::get($vuln_for));
        $func->setVulnerableParameters(funcini_check_array($args_vulnerable, ","));
        $func->setFunctionToCheckForVulnerability(funcini_check_value($func_to_check_vulnerability));
        $func->setFunctionReplacement($func_replacement);

        ScanInfo::addFunction($func);
    }
}


import_csv('functions.csv');