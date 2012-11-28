<?php
require(dirname(__FILE__)."/../../cmd.php");


$content = ScanInfo::exportVulnerabilityList();

$info_file = dirname(__FILE__).'/../../tmp/scaninfo.php';

file_put_contents($info_file, $content);
