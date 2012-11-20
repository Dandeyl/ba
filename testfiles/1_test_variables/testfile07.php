<?php
function test() {
    global $a;
    $a++;
}


$a = 4;
test();
echo $a; // should be 5