<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

/**
 * Pointcut
 *
 * @package Ray.Di
 */
final class Pointcut
{
    /**
     * Class matcher
     *
     * @var Matcher
     */
    public $classMatcher;

    /**
     * Method matcher
     *
     * @var Matcher
     */
    public $methodMatcher;

    /**
     * Interceptors
     *
     * @var Interceptor[]
     */
    public $interceptors = [];

    /**
     * Constructor
     *
     * @param Matcher $classMatcher
     * @param Matcher $methodMatcher
     * @param array   $interceptors
     */
    public function __construct(Matcher $classMatcher, Matcher $methodMatcher, array $interceptors)
    {
        $this->classMatcher = $classMatcher;
        $this->methodMatcher = $methodMatcher;
        $this->interceptors = $interceptors;
    }
}
