<?php
// show spl methods (which are invalid to bind)
$methods = (new ReflectionClass('ArrayObject'))->getMethods();
foreach ($methods as $method) {
    $result[] = $method->name;
}
echo implode("', '", $result);
