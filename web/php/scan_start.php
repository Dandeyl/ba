<?php
require(dirname(__FILE__)."/../../scanner/prggmr/prggmr.php");

// Definitions
define("OUTPUT_FILE", dirname(__FILE__).'/../../tmp/scanresult.php');
define("INFO_FILE",   dirname(__FILE__).'/../../tmp/scaninfo.php');

$scanresult = array();
$scaninfo   = array();

// Subscribers
subscribe('parseError', function($event, $file, $message) {
    global $scanresult;
    $scanresult["parseError"] = $message;
    file_put_contents(INFO_FILE, serialize($scanresult));
});

subscribe('beginScanFile', function($event, $file) {
    static $last_file = null;
    static $arr_files = array();
    global $scaninfo;
    
    if($file != $last_file) {
        $arr_files[] = $file;
        $scaninfo["files"] = $arr_files;
        file_put_contents(INFO_FILE, serialize($scaninfo));
    }
     
});

subscribe('endOfScan', function() {
    global $scanresult; 
    
    $scanresult["vulnList"] = ScanInfo::getVulnerabilityList();
    file_put_contents(OUTPUT_FILE, serialize($scanresult));
});


// Start scanning
require(dirname(__FILE__)."/../../cmd.php");



