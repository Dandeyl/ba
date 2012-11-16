<?php
/**
 * Simple test with one xss vulnerability
 */
$foo = 'str';
$bar = 'ing';
$baz = $foo.$bar;
echo $baz;
