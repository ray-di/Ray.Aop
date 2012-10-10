<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Matched result set.
 *
 * @package Ray.Aop
 * @author  Akihito Koriyama<akihito.koriyama@gmail.com>
 */
final class Matched
{
    /**
     * Matched method name
     *
     * @var string
     */
    public $methodName;

    /**
     * Binding annotation for match.
     *
     * @var object
     */
    public $annotation;
}
