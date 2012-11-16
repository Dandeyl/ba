<?php
/**
 * Simple test with one xss vulnerability
 */
$value = $_GET["test"];
echo $value;
