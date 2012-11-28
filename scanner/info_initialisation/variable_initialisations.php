<?php
/**
 * Sources of risky data
 */


$source_variables = array(
    //////// name       type     user defined   superglobal    value
    array('$_GET',     'array',     true,        true), // $HTTP_GET_VARS is listed in ./variable_references.php
    array('$_POST',    'array',     true,        true),
    array('$_COOKIE',  'array',     true,        true),
    array('$_REQUEST', 'array',     true,        true),
    array('$_FILES',   'array',     false,       true),
    array('$_SERVER',  'array',     false,       true),
    array('$_ENV',     'array',     false,       true),
    array('$GLOBALS',  'array',     false,       true),
    array('$argv',     'array',     true,        false),
    array('$argc',     'integer',   true,        false),
    
    //////// name       type     user defined   superglobal    value
    array('true',      'bool',      false,       true,         true),
    array('false',     'bool',      false,       true,         false),
    array('null',      'bool',      false,       true,         null),
    
    //////// name                               type     user defined   superglobal    value
    array('$_SERVER["HTTP_USER_AGENT"]',       'string', true,           true),
    array('$_SERVER["HTTP_ACCEPT"]',           'string', true,           true),
    array('$_SERVER["HTTP_ACCEPT_LANGUAGE"]',  'string', true,           true),
    array('$_SERVER["HTTP_ACCEPT_ENCODING"]',  'string', true,           true),
    array('$_SERVER["HTTP_ACCEPT_CHARSET"]',   'string', true,           true),
    array('$_SERVER["HTTP_KEEP_ALICE"]',       'string', true,           true),
    array('$_SERVER["HTTP_CONNECTION"]',       'string', true,           true),
    array('$_SERVER["HTTP_HOST"]',             'string', true,           true),
    array('$_SERVER["QUERY_STRING"]',          'string', true,           true),
    array('$_SERVER["REQUEST_URI"]',           'string', true,           true),
    array('$_SERVER["PATH_INFO"]',             'string', true,           true),
    array('$_SERVER["PATH_TRANSLATED"]',       'string', true,           true),
    array('$_SERVER["PHP_SELF"]',              'string', true,           true),
);

/***
 * Initialisation
 */
foreach($source_variables as $varinfo) {
    @list($name, $type,  $user_defined, $superglobal, $value) = $varinfo;
    
    $variable = new Obj_Variable($name);
    $variable->setUserDefined($user_defined);
    $variable->setSuperGlobal($superglobal);
    if($value) {
        $variable->setValue($value);
    }
    
    ScanInfo::addVar($variable);
}