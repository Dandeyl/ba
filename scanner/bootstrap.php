<?php
require(dirname(__FILE__).'/scaninfo.php');
require(dirname(__FILE__).'/scanner.php');
if(!function_exists('fire')) {
    require(dirname(__FILE__).'/prggmr/prggmr.php'); // signals
}

Scanner::Init();