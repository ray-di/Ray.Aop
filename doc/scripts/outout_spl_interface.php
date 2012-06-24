<?php
$methods = (new ReflectionClass('ArrayObject'))->getMethods();
foreach ($methods as $method) {
    $result[] = $method->name;
}
echo implode("', '", $result);
