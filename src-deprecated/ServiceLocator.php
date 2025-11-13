<?php

declare(strict_types=1);

namespace Ray\ServiceLocator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Koriym\Attributes\AttributeReader;
use Koriym\Attributes\DualReader;

use function class_exists;
use function trigger_error;
use function vsprintf;

/**
 * ServiceLocator class provides a way to set and retrieve a Reader instance.
 * It includes mechanisms to lazily initialize the Reader if it hasn't been set.
 *
 * @deprecated This class is no longer used by Ray.Aop. Please remove the calling code.
 */
final class ServiceLocator
{
    /** @var ?Reader */
    private static $reader;

    public static function setReader(Reader $reader): void
    {
        trigger_error('ray/aop does not use this class. The call code can be deleted.', E_USER_DEPRECATED);
        self::$reader = $reader;
    }

    public static function getReader(): Reader
    {
        if (! class_exists(AttributeReader::class)) {
            $message = vsprintf(
                '%s is deprecated. ' .
                'Please install "koriym/attributes" in your project if you need this class, ' .
                'or migrate to native PHP 8 attributes.',
                [self::class],
            );
            trigger_error($message, E_USER_ERROR);
        }

        if (! self::$reader) {
            self::$reader = new DualReader(new AnnotationReader(), new AttributeReader());
        }

        return self::$reader;
    }
}
