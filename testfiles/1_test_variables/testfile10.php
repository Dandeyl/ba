<?php
$value = '// variable has reference > current variable already exists ';
$xss_var = &$var;
$var = "value";
$xss_var = &${$var};
$value = $_GET["test"];

echo $xss_var;
