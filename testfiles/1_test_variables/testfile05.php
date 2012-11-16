<?php
$a = 4;

function test() {
    global $a;
    unset($a);
}

test();
/**
 * @assert $a == 4
 */