<?php
$array = [1,2,3];

$s = microtime(true);
foreach (range(1,1000) as $i) {
	list($a, $b, $c) = $array;
}
echo microtime(true) - $s . "\n";


$s = microtime(true);
$array = new ArrayObject($array);
foreach (range(1,1000) as $i) {
	list($a, $b, $c) = (array)$array;
}
echo microtime(true) - $s . "\n";
