<?php
/**
 * Ray
 *
 * @package Ray.Di
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
     * Class matccher
     *
     * @var Mathcer
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