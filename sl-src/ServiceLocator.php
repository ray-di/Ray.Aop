<?php

declare(strict_types=1);

namespace Ray\ServiceLocator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;

use function sys_get_temp_dir;

final class ServiceLocator
{
    /** @var ?Reader */
    private static $reader;

    public static function setReader(Reader $reader): void
    {
        self::$reader = $reader;
    }

    public static function getReader(): Reader
    {
        if (! self::$reader) {
            self::$reader = new CacheReader(
                new DualReader(new AnnotationReader(), new AttributeReader()),
                new Cache(sys_get_temp_dir())
            );
        }

        return self::$reader;
    }
}
