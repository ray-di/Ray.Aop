# Ray.Aop
## Aspect Oriented Programming for PHP###

[![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png)](http://travis-ci.org/koriym/Ray.Aop)

To compliment dependency injection, Ray.Aop supports method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

## Getting Started

Original class

```php
<?php
class Mock
{
    public function getDouble($a)
    {
        return $a * 2;
    }
}
```

Intercepter

```php
<?php

	class tenTimes implements MethodInterceptor
	{
	    public function invoke(MethodInvocation $invocation)
	    {
	        $result = $invocation->proceed();
	        return $result * 10;
	    }
	}
```

Weave original class and interceptor with Weaver.

```php
<?php

	// with weaver
    $bind = new Bind;
    $bind->bindInterceptors('getDouble', array(new DoubleInterceptor, new DoubleInterceptor));
    $this->weaver = new Weaver(new MockMethod, $bind);
	$mock = new Weaver(new Mock, array(new tenTimes, new tenTimes));
	echo $mock->getDouble(3); //600 =3*2*10*10

	// in original
	$mock = new Mock;
	echo $mock->getDouble(3); //6
```


# Usage

## Implicit biding
use method name

```php
<?php

    // WeekendBlocker interceptor throw Exception on weekend.
	$bind = new Bind;
	$bind->bindInterceptors('chargeOrder', array(new WeekendBlocker));
	$weavedBilling = new Weaver(new RealBilling, array(new WeekendBlocker));
	$weavedBilling->chargeOrder($args);
```

## Matcher biding
use matcher, which match 'any', 'subClassOf' and 'annotatedWith'.

```php
<?php

    // WeekendBlocker interceptor throw Exception on weekend.
	$bind = new Bind;
	$matcher = new Matcher(new Reader);
	$interceptors = [new WeekendBlocker];
	$pointcut = new Pointcut($matcher->any(), $matcher->annotatedWith('Ray\Aop\Sample\Annotation\WeekendBlock'), $interceptors);
	$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);
	$weavedBilling = new Weaver(new AnnotationRealBillingService, $bind);
	try {
	    echo $weavedBilling->chargeOrder();
	} catch (\RuntimeException $e) {
	    echo $e->getMessage() . "\n";
	    exit(1);
	}

see more detail at doc/sample-04-annotation/ for using annotation matcher.