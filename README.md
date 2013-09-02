Ray.Aop
=======

Ray.Aop package provides method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

[Matcher](http://koriym.github.io/Ray.Aop/api/interfaces/Ray_Aop_Matchable.html) is a simple interface that either accepts or rejects a value. For Ray.AOP, you need two matchers: one that defines which classes participate, and another for the methods of those classes. To make this easy, there's factory class to satisfy the common scenarios.

[MethodInterceptors](http://koriym.github.io/Ray.Aop/api/interfaces/Ray_Aop_MethodInterceptor.html) are executed whenever a matching method is invoked. They have the opportunity to inspect the call: the method, its arguments, and the receiving instance. They can perform their cross-cutting logic and then delegate to the underlying method. Finally, they may inspect the return value or exception and return. Since interceptors may be applied to many methods and will receive many calls, their implementation should be efficient and unintrusive.

[![Latest Stable Version](https://poser.pugx.org/ray/aop/v/stable.png)](https://packagist.org/packages/ray/aop)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png)](http://travis-ci.org/koriym/Ray.Aop)


Example: Forbidding method calls on weekends
--------------------------------------------

To illustrate how method interceptors work with Ray.Aop, we'll forbid calls to our pizza billing system on weekends. The delivery guys only work Monday thru Friday so we'll prevent pizza from being ordered when it can't be delivered! This example is structurally similar to use of AOP for authorization.

To mark select methods as weekdays-only, we define an annotation .
(Ray.Aop supports Doctrine Annotation)

週末だけにするためのアノテーションを定義します。

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

インターセプトさせるメソッドに適用します。

```php
<?php
class RealBillingService
{
    /**
     * @NotOnWeekends
     */
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

Next, we define the interceptor by implementing the org.aopalliance.intercept.MethodInterceptor interface. When we need to call through to the underlying method, we do so by calling $invocation->proceed():

次に、MethodInterceptorインターフェイスを実装します。元のメソッドを実行するためには$invocation->proceed()と実行します。 

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
Finally, we configure everything.In this case we match any class, but only the methods with our @NotOnWeekends annotation:

設定完了しました。このコードでは「どのクラスでも」「メソッドに@NotOnWeekendsアノテーション」という条件にマッチします。

```php
<?php
$bind = new Bind;
$matcher = new Matcher(new Reader);
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
		$matcher->any(),
		$matcher->annotatedWith('Ray\Aop\Sample\Annotation\NotOnWeekends'),
		$interceptors
);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$billing = (new Compiler)->newInstance('RealBillingService', [], $bind);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```
Putting it all together, (and waiting until Saturday), we see the method is intercepted and our order is rejected:

全てをまとめ（土曜日まで待って）、メソッドをコールするとインターセプターにより拒否されます。

```php
<?php
RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

Call Stack:
    0.0022     228296   1. {main}() /apps/pizza/main.php:0
    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65
```

Explicit method name match
---------------------------

```php
<?php
	$bind = new Bind;
	$bind->bindInterceptors('chargeOrder', [new WeekendBlocker]);
	
	$billing = (new Compiler)->newInstance('RealBillingService', [], $bind);
	try {
	   echo $billing->chargeOrder();
	} catch (\RuntimeException $e) {
	   echo $e->getMessage() . "\n";
	   exit(1);
	}
```

Limitations
-----------

Behind the scenes, method interception is implemented by generating code at runtime. Ray.Aop dynamically creates a subclass that applies interceptors by overriding methods.

This approach imposes limits on what classes and methods can be intercepted:

 * Classes must be non-final
 * Methods must be public
 * Methods must be non-final

AOP Alliance
------------
The method interceptor API implemented by Ray.Aop is a part of a public specification called [AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html). 

Testing Ray.Aop
===============

Here's how to install Ray.Aop from source to run the unit tests and sample:

```
$ git clone git://github.com/koriym/Ray.Aop.git
$ cd Ray.Aop
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
$ php doc/sample-01-quick-weave/main.php
// Charged. | chargeOrder not allowed on weekends!
```

Requirement
-------------

 * PHP 5.4+

Installation
============

### Installing via Composer

The recommended way to install Ray.Aop is through [Composer](http://getcomposer.org).And recommended way to use Ray.Aop is thorouh [Ray.Di](https://github.com/koriym/Ray.Di).
Ray.Di is a Guice style annotation-driven dependency injection framework. It integrates Ray.Aop AOP functionality.
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php

# Add Ray.Aop as a dependency
php composer.phar require ray/di:*
```

### ini_set

You may want to set the `xdebug.max_nesting_level` ini option to a higher value:

```php
ini_set('xdebug.max_nesting_level', 2000);
```

* The most part of this documentation is taken from [Guice/AOP](https://code.google.com/p/google-guice/wiki/AOP)
