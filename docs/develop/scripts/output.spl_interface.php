<?php
/**
 * display all spl(ArrayObject) methods
 */
$methods = (new ReflectionClass('ArrayObject'))->getMethods();
$result = [];
foreach ($methods as $method) {
    $result[] = $method->name;
}
echo implode("', '", $result);
