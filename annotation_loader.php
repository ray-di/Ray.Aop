<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;

if (method_exists(AnnotationRegistry::class, 'registerLoader')) {
    AnnotationRegistry::registerLoader('class_exists');
}
