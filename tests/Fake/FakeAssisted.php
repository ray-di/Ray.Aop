<?php
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\Aop;

use Ray\Aop\Annotation\AbstractAssisted;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class FakeAssisted extends AbstractAssisted
{
    /**
     * @var array<string>
     */
    public $values;
}
