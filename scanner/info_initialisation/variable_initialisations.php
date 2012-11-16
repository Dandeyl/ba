<?php
/**
 * Sources of risky data
 */

// variables that can be easily 
$source_variables = array(
          // name       type     user defined   superglobal
    array('$_GET',     'array',     true,        true), // $HTTP_GET_VARS is listed in ./variable_references.php
    array('$_POST',    'array',     true,        true),
    array('$_COOKIE',  'array',     true,        true),
    array('$_REQUEST', 'array',     true,        true),
    array('$_FILES',   'array',     true,        true),
    array('$_SERVER',  'array',     true,        true),
    array('$_ENV',     'array',     true,        true),
    array('$argv',     'array',     true,        true),
);


$source_server = array(
    array('$_SERVER["HTTP_USER_AGENT"]', 'string'),
    array('$_SERVER["HTTP_ACCEPT"]', 'string'),
    array('$_SERVER["HTTP_ACCEPT_LANGUAGE"]', 'string'),
    array('$_SERVER["HTTP_ACCEPT_ENCODING"]', 'string'),
    array('$_SERVER["HTTP_ACCEPT_CHARSET"]', 'string'),
    array('$_SERVER["HTTP_KEEP_ALICE"]', 'string'),
    array('$_SERVER["HTTP_CONNECTION"]', 'string'),
    array('$_SERVER["HTTP_HOST"]', 'string'),
    array('$_SERVER["QUERY_STRING"]', 'string'),
    array('$_SERVER["REQUEST_URI"]', 'string'),
    array('$_SERVER["PATH_INFO"]', 'string'),
    array('$_SERVER["PATH_TRANSLATED"]', 'string'),
    array('$_SERVER["PHP_SELF"]', 'string'),
);

/***
 * Initialisation
 */
foreach($source_variables as $varinfo) {
    list($name, $type,  $user_defined, $superglobal) = $varinfo;
    
    $variable = new Obj_Variable($name);
    $variable->setUserDefined($user_defined);
    $variable->setSuperGlobal($superglobal);
    
    ScanInfo::addVar($variable);
}