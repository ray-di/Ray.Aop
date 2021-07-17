<?php

declare(strict_types=1);

namespace Ray\Aop\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;


/**
 * @Annotation
 * @Target("METHOD")
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class FakeMarker
{
    /** @var int */
    public $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }
}
