<?php
$a = 0;
if($a) {
    echo $_GET["test"];
}
elseif((++$a) == 2) {
    echo $_GET["test"];
}
echo $a;