Ray.Aop
=======

Ray.Aop package provides method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

[![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png)](http://travis-ci.org/koriym/Ray.Aop)

Requirement
-------------

 * PHP 5.4+
 
Getting Started
===============

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

$billing = new Weaver(new RealBillingService, $bind);
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
	
	$billingService = new Weaver(new RealBillingService, $bind);
	try {
	   echo $billingService->chargeOrder();
	} catch (\RuntimeException $e) {
	   echo $e->getMessage() . "\n";
	   exit(1);
	}
```

Testing Ray.Aop
===============

Here's how to install Ray.Aop from source to run the unit tests and sample:

```
$ git clone git://github.com/koriym/Ray.Aop.git
$ wget http://getcomposer.org/composer.phar
$ php composer.phar update
$ phpunit
$ php doc/sample-01-quick-weave/main.php
// Charged.
```

Ray.Di
======
[Ray.Di](https://github.com/koriym/Ray.Di) is a Guice style annotation-driven dependency injection framework. It integrates Ray.Aop AOP functionality.

Installation
============

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add Ray.Aop with it.

	{
		"require": {
			"ray/aop": "*"
		}
	}


AOP Alliance
------------
The method interceptor API implemented by Ray.Aop is a part of a public specification called [AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html). 
