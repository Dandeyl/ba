<?php

$a = 0;
if($a) {
    $a += 1;
}
elseif(($a+=1) == 2) {
    echo $_GET["test"];
}
/**
 * @assert $a == 1
 * @assert ScanInfo::getNumVulnerabilities == 0
 */
echo $a;