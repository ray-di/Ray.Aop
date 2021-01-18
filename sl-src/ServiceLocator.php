<?php

namespace Ray\ServiceLocator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;

final class ServiceLocator
{
    /**
     * @var ?Reader
     */
    private static $reader;

    public static function setReader(Reader $reader): void
    {
        self::$reader = $reader;
    }

    public static function getReader(): Reader
    {
        if (! self::$reader) {
            self::$reader = new DualReader(new AnnotationReader(), new AttributeReader());
        }

        return self::$reader;
    }
}
