<?php
/**
 * Software to check php source code for security vulnerabilities. It uses dynamic
 * source code analysing.
 * Identified security flaws:
 * - directory traversing
 *     Looks for the functions fpassthru, file, fopen, include(_once), require(_once),
 *     readfile and file_get_contents. If one of these functions are found the variables get checked.
 * - xss
 */

// PHP + general configuration
ini_set('xdebug.max_nesting_level', 7000);
ini_set('max_execution_time', 600); // 10 minutes
ini_set('memory_limit','64M'); // should be enough for almost every project out there.

define("TIME_STARTED" , microtime(true));
define("SCANNER_LOGPATH", dirname(__FILE__).'/log/');
define("SCANNER_DUMP_TREE", 0 ? true : false); // set 1: dump all nodes in the file
                                               //     0: analyse the file and all included files

// Functions and Classes
require (dirname(__FILE__).'/parser/bootstrap.php');
require (dirname(__FILE__).'/scanner/bootstrap.php');

// register subscriber
if(isset($argc)) {
    subscribe('beginParseFile', function($event, $file) {
        static $count = 0;
        echo "\n--------- ".++$count." Parsing FILE: $file ---------\n\n\n"; 
    });
    subscribe('parseError', function($event, $file, $message) {
        die('Parse error: '.$message."\n\n");
    });
}


// prepare file
$filename = isset($argv[1]) ? $argv[1] : 
                         (isset($_GET["file"]) ? $_GET["file"] : 
                         'testfiles'. DIRECTORY_SEPARATOR."simple.php");
$file =  $filename;
chdir(dirname(__FILE__));


if(!file_exists($file)) {
    die("\n\nPROTEUS: The selected file does not exist\n\n");
}

// start parsing and scanning
Scanner::startScan($file);


// dump information when scanning is done
if(!SCANNER_DUMP_TREE) {
    ScanInfo::dump();
}