<?php
$a = 4;

function test() {
    unset($GLOBALS["a"]);
}

test();
var_dump($a);
/**
 * @assert $a == undefined
 */