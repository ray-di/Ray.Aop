# Ray.Aop

## Aspect Oriented Framework

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/Ray-Di/Ray.Aop/?branch=2.x)
[![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/Ray-Di/Ray.Aop/?branch=2.x)
[![Build Status](https://travis-ci.org/ray-di/Ray.Aop.svg?branch=2.x)](https://travis-ci.org/ray-di/Ray.Aop)
[![Total Downloads](https://poser.pugx.org/ray/aop/downloads)](https://packagist.org/packages/ray/aop)

[\[Japanese\]](https://github.com/ray-di/Ray.Aop/blob/2.x/README.ja.md)

**Ray.Aop** package provides method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

A [Matcher](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) is a simple interface that either accepts or rejects a value. For Ray.AOP, you need two matchers: one that defines which classes participate, and another for the methods of those classes. To make this easy, there's factory class to satisfy the common scenarios.

[MethodInterceptors](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInterceptor.php) are executed whenever a matching method is invoked. They have the opportunity to inspect the call: the method, its arguments, and the receiving instance. They can perform their cross-cutting logic and then delegate to the underlying method. Finally, they may inspect the return value or exception and return. Since interceptors may be applied to many methods and will receive many calls, their implementation should be efficient and unintrusive.

## Example: Forbidding method calls on weekends

To illustrate how method interceptors work with Ray.Aop, we'll forbid calls to our pizza billing system on weekends. The delivery guys only work Monday thru Friday so we'll prevent pizza from being ordered when it can't be delivered! This example is structurally similar to use of AOP for authorization.

To mark select methods as weekdays-only, we define an annotation. (Ray.Aop uses Doctrine Annotations)

```php
<?php
/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class NotOnWeekends
{
}
```

...and apply it to the methods that need to be intercepted:

```php
<?php
class RealBillingService
{
    /**
     * @NotOnWeekends
     */
    public function chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

Next, we define the interceptor by implementing the org.aopalliance.intercept.MethodInterceptor interface. When we need to call through to the underlying method, we do so by calling `$invocation->proceed()`:

```php
<?php
class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $today = getdate();
        if ($today['weekday'][0] === 'S') {
            throw new \RuntimeException(
                $invocation->getMethod()->getName() . " not allowed on weekends!"
            );
        }
        return $invocation->proceed();
    }
}
```

Finally, we configure everything. In this case we match any class, but only the methods with our `@NotOnWeekends` annotation:

```php
<?php

use Ray\Aop\Sample\Annotation\NotOnWeekends;
use Ray\Aop\Sample\Annotation\RealBillingService;

$pointcut = new Pointcut(
    (new Matcher)->any(),
    (new Matcher)->annotatedWith(NotOnWeekends::class),
    [new WeekendBlocker]
);
$bind = (new Bind)->bind(RealBillingService::class, [$pointcut]);
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, [], $bind);

try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

Putting it all together, (and waiting until Saturday), we see the method is intercepted and our order is rejected:

```
chargeOrder not allowed on weekends!
```

## Explicit method name match

```php
<?php
    $bind = (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker]);
    $compiler = new Weaver($bind, $tmpDir);
    $billing = $compiler->newInstance('RealBillingService', [], $bind);
    try {
        echo $billing->chargeOrder();
    } catch (\RuntimeException $e) {
        echo $e->getMessage() . "\n";
        exit(1);
    }
```

Own matcher
-----------

You can have your own matcher.
To create `contains` matcher, You need to provide a class which have two method. One is `matchesClass` for class match.
The other one is `matchesMethod` method match. Both return the boolean result of matched.

```php
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;

class IsContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

```php
$pointcut = new Pointcut(
    (new Matcher)->any(),
    new IsContainsMatcher('charge'),
    [new WeekendBlocker]
);
$bind = (new Bind)->bind(RealBillingService::class, [$pointcut]);
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, [$arg1, $arg2]);
```

## Performance boost

Cached `Weaver` object can save the compiling, binding, annotation reading costs.

```php
$weaver = unserialize(file_get_contentes('./serializedWever'));
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, [$arg1, $arg2]);
```

## Priority

The order of interceptor invocation are determined by following rules.

 * Basically, it will be invoked in bind order.
 * `PriorityPointcut` has most priority.
 * Annotation method match is followed by `PriorityPointcut`. Invoked in annotation order as follows.

```php
/**
 * @Auth    // 1st
 * @Cache   // 2nd
 * @Log     // 3rd
 */
```

## Limitations

Behind the scenes, method interception is implemented by generating code at runtime. Ray.Aop dynamically creates a subclass that applies interceptors by overriding methods.

This approach imposes limits on what classes and methods can be intercepted:

 * Classes must be *non-final*
 * Methods must be *public*

# Interceptor

In an interceptor a `MethodInvocation` object gets passed to the `invoke` method. We can the decorate the targetted instances so that you run computations before or after any methods on the target are invoked.

```php
class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Before method invocation
        // ...
        
        // Method invocation
        $result = invocation->proceed();
        
        // After method invocation
        // ...
                
        return $result;
    }
}
```

With the `MethodInvocation` object, you can access the target method's invocation object, method's and parameters.

 * [`$invocation->proceed()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php#L41) - Invoke method
 * [`$invocation->getMethod()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php#L30) -  Get method reflection
 * [`$invocation->getThis()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php#L50) - Get object
 * [`$invocation->getArguments()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php#L25) - Get parameters
 * [`$invocation->getNamedArguments()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php#L32) - Get named parameters
An extended `ClassRefletion` and `MethodReflection` holds methos to get annotation(s).

```php
/** @var $method \Ray\Aop\ReflectionMethod */
$method = $invocation->getMethod();
/** @var $class \Ray\Aop\ReflectionClass */
$class = $invocation->getMethod()->getDeclaringClass();
```
 
 * `$method->getAnnotations()`     - Get method annotations
 * `$method->getAnnotation($name)` - Get method annotation
 * `$class->->getAnnotations()`    - Get class annotations
 * `$class->->getAnnotation($name)`     - Get class annotation
  
## AOP Alliance

The method interceptor API implemented by Ray.Aop is a part of a public specification called [AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html).

## Installation

The recommended way to install Ray.Aop is through [Composer](https://github.com/composer/composer).

```bash
# Add Ray.Aop as a dependency
$ composer require ray/aop ^2.0
```

## Testing Ray.Aop

Here's how to install Ray.Aop from source and run the unit tests and demos.

```bash
git clone https://github.com/ray-di/Ray.Aop.git
cd Ray.Aop
composer install
vendor/bin/phpunit
php demo/run.php
```

See also the DI framework [Ray.Di](https://github.com/ray-di/Ray.Di) which integrates DI and AOP.

* This documentation for the most part is taken from [Guice/AOP](https://github.com/google/guice/wiki/AOP).
