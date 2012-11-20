<?php
function test() {
    global $a;
    
    echo $a++;
}

test(); // shouldnt do anything since $a is not yet defined