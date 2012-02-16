<?php
/**
 * Joinpoint
 *
 */
final class Match
{
    /**
     * Class Matcher
     *
     * @var Matcher
     */
    public $classMatcher;

    /**
     * Method Matcher
     *
     * @var Matcher
     */
    public $methodMatcher;

    /**
     * Interceptors
     *
     * @var array Interceptor[]
     */
    public $interceptors = []
}