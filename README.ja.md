Aspect Oriented Framework for PHP
=================================

[![Latest Stable Version](https://poser.pugx.org/ray/aop/v/stable.png)](https://packagist.org/packages/ray/aop)
[![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png)](http://travis-ci.org/koriym/Ray.Aop)

**Ray.Aop** パッケージはメソッドインターセプションの機能を提供します。マッチするメソッドが実行される度に実行されるコードを記述する事ができます。トランザクション、セキュリティやログといった横断的な”アスペクト”に向いています。なぜならインターセプターが問題をオブジェクトというよりアスペクトに分けるからです。これらの用法はアスペクトオリエンティッドプログラム(AOP)と呼ばれます。

[Matcher](http://bearsunday.github.io/builds/Ray.Aop/api/class-Ray.Aop.Matchable.html) は値を受け取ったり拒否したりするシンプルなインターフェイスです。例えばRay.Aopでは２つの **Matcher** が必要です:１つはどのクラスに適用するかを決め、もう一つはそのクラスのどのメソッドに適用するかを決めます。これらを簡単に利用するためのファクトリークラスがあります。

[MethodInterceptors](http://bearsunday.github.io/builds/Ray.Aop/api/class-Ray.Aop.MethodInterceptor.html) はマッチしたメソッドが呼ばれる度に実行されます。呼び出しやメソッド、それらの引き数、インスタンスを調べる事ができます。横断的なロジックと委譲されたメソッドが実行されます。最後に返り値を調べて返します。インターセプターは沢山のメソッドに適用され沢山のコールを受け取るので、実装は効果的で透過的なものになります。


Example: Forbidding method calls on weekends
--------------------------------------------

メソッドインターセプターがRay.Aopでどのように機能するかを明らかにするために、終末にはピザの注文を禁止するようにしてみましょう。デリバリーは平日だけ受け付ける事にして、ピザの注文を週末には受け付けないようにします！この例はAOPで認証を使用するときにのパターンと構造的に似ています。

週末だけにするための[アノテーション](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html)を定義します。

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

次に、MethodInterceptorインターフェイスを実装します。元のメソッドを実行するためには **$invocation->proceed()** と実行します。 

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

設定完了しました。このコードでは「どのクラスでも」「メソッドに@NotOnWeekendsアノテーション」という条件にマッチします。

```php
<?php
$bind = new Bind;
$matcher = new Matcher;
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
		$matcher->any(),
		$matcher->annotatedWith('Ray\Aop\Sample\Annotation\NotOnWeekends'),
		$interceptors
);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$compiler = new Compiler(sys_get_temp_dir());
$billing = $compiler->newInstance('RealBillingService', [], $bind);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

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

    $compiler = new Compiler(sys_get_temp_dir());
	$billing = $compiler->newInstance('RealBillingService', [], $bind);
	try {
	   echo $billing->chargeOrder();
	} catch (\RuntimeException $e) {
	   echo $e->getMessage() . "\n";
	   exit(1);
	}
```

My matcher
----------
独自のMatcherを作成することができます。
クラス名やメソッド名に特定の文字列が含まれているかをマッチする`contains`マッチャーを作成するには、
インターフェイスとなる`contains`メソッドと実際にマッチを判断した結果を返す`isContains`メソッドの２つが必要です。

```php
use Ray\Aop\AbstractMatcher;

class MyMatcher extends AbstractMatcher
{
    /**
     * @param $contain
     *
     * @return MyMatcher
     */
    public function contains($contain)
    {
        $this->createMatcher(__FUNCTION__, $contain);

        return clone $this;

    }

    /**
     * Return isContain
     *
     * @param mixed  $name    class name string or method reflection
     * @param bool   $target  \Ray\Aop\AbstractMatcher::TARGET_CLASS | \Ray\Aop\AbstractMatcher::Target_METHOD
     * @param string $contain
     *
     * @return bool
     */
    protected function isContains($name, $target, $contain)
    {
        $result = (strpos($name, $contain) !== false);

        return $result;
    }
}
```

Limitations
-----------

この機能の背後ではメソッドのインターセプションを事前にコードを生成する事で可能にしています。Ray.Aopはダイナミックにサブクラスを生成してメソッドをオーバーライドするインターセプターを適用します。
クラスとメソッドは以下のものである必要があります。

 * クラスは *final* ではない
 * メソッドは *public*
 * メソッドは *final* ではない

AOP Alliance
------------
このメソッドインターセプターのAPIは[AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html)の部分実装です。

Testing Ray.Aop
===============

Ray.Aopをインストールしてユニットテストするためには以下のようにします。

```
$ composer create-project ray/aop Ray.Aop 1.*
$ cd Ray.Aop
$ phpunit
$ cd docs
$ php sample/01-quick-weave/main.php 
$ php sample/02-multiple-interceptors/main.php
$ php sample/03-benchmark/main.php
$ php sample/04-annotation/main.php
$ php sample/05-my-matcher/main.php 
```

Requirement
-------------

 * PHP 5.4+

Installation
============

### Installing via Composer

```bash
# Add Ray.Aop as a dependency
php composer.phar require ray/aop:*
```

AOPを統合したGuiceスタイルのDIフレームワーク[Ray.Di](https://github.com/koriym/Ray.Di)でもRay.Aopを利用する事ができます。

### ini_set

xdebugを使ってる場合は`xdebug.max_nesting_level`の値をこのように大きな値にする必要があるかもしれません。

```php
ini_set('xdebug.max_nesting_level', 2000);
```

* The most part of this documentation is taken from [Guice/AOP](https://code.google.com/p/google-guice/wiki/AOP)
