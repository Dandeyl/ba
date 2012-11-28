<?php 

//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"]; // Vulnerability 1
}
elseif($_GET["test"]) {
    echo $_GET["test5"]; // Vulnerability 2
}
else {
    echo $_GET["test4"]; // Vulnerability 3
}


//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"];  // Vuln. 4
}
elseif($_GET["test"]) {
    echo $_GET["test5"];
}
else {
    echo $_GET["test4"];
}

//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"];
}
elseif($_GET["test"]) {
    echo $_GET["test5"];
}
else {
    echo $_GET["test4"];
}


//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"];
}
elseif($_GET["test"]) {
    echo $_GET["test5"];
}
else {
    echo $_GET["test4"];
}


//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"];
}
elseif($_GET["test"]) {
    echo $_GET["test5"];
}
else {
    echo $_GET["test4"];
}




//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"];
}
elseif($_GET["test"]) {
    echo $_GET["test5"];
}
else {
    echo $_GET["test4"];
}



//// BEGIN IF(3)
if($_GET["test"]) {
    echo $_GET["test3"];
}
elseif($_GET["test"]) {
    echo $_GET["test5"];  // 20
}
else {
    echo $_GET["test4"];  // 21
}