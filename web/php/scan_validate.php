<?php
session_start();
// unlink scaninfo file
$scaninfo = dirname(__FILE__).'/../../tmp/scaninfo.php';
if(file_exists($scaninfo)) {
    unlink($scaninfo);
}


if(isset($_POST["input_code"])) {
    session_regenerate_id();
    $session = session_id();
    
    // write input to tmp directory
    $file = dirname(__FILE__).'/../../tmp/direct_input.php';
    
    file_put_contents($file, $_POST["input_code"]);
    
    $_POST["server_file"] = 'tmp/direct_input.php';
}

if(isset($_POST["server_file"])) {
    $file = dirname(__FILE__).'/../../'.$_POST["server_file"];
    if(!file_exists($file)) {
        die(json_encode(array("404", "File not found")));
    }
    elseif(filesize($file) == 0) {
        die(json_encode(array("0", "File is empty")));
    }
    
    die(json_encode(array("200", $_POST["server_file"])));
}