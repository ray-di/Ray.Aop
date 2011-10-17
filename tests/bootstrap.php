<?php
// bootstrap for test

require_once dirname(__DIR__) . '/src.php';

require_once __DIR__ . '/MockMethod.php';
require_once __DIR__ . '/MockMethodInterceptor.php';
require_once __DIR__ . '/interceptors/DoubleInterceptor.php';

// debug utility
require_once __DIR__ . '/v.php';
