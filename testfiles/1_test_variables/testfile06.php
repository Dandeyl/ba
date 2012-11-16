<?php
$a = 4;

function test() {
    unset($GLOBALS["a"]);
}

test();
/**
 * @assert $a == undefined
 */