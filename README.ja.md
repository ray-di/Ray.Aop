# Ray.Aop

## アスペクト指向フレームワーク

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/Ray-Di/Ray.Aop/?branch=2.x)
[![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/Ray-Di/Ray.Aop/?branch=2.x)
[![Build Status](https://travis-ci.org/ray-di/Ray.Aop.svg?branch=2.x)](https://travis-ci.org/ray-di/Ray.Aop)

[\[English\]](https://github.com/ray-di/Ray.Aop/blob/2.x/README.md)

**Ray.Aop** パッケージはメソッドインターセプションの機能を提供します。マッチするメソッドが実行される度に実行されるコードを記述する事ができます。トランザクション、セキュリティやログといった横断的な”アスペクト”に向いています。なぜならインターセプターが問題をオブジェクトというよりアスペクトに分けるからです。これらの用法はアスペクト指向プログラミング(AOP)と呼ばれます。

[Matcher](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) は値を受け取ったり拒否したりするシンプルなインターフェイスです。例えばRay.Aopでは２つの **Matcher** が必要です。１つはどのクラスに適用するかを決め、もう１つはそのクラスのどのメソッドに適用するかを決めます。これらを簡単に利用するためのファクトリークラスがあります。

[MethodInterceptors](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInterceptor.php) はマッチしたメソッドが呼ばれる度に実行されます。呼び出しやメソッド、それらの引き数、インスタンスを調べる事ができます。横断的なロジックと委譲されたメソッドが実行されます。最後に返り値を調べて返します。インターセプターは沢山のメソッドに適用され沢山のコールを受け取るので、実装は効果的で透過的なものになります。

例：平日のメソッドコールを禁止する
--------------------------------

メソッドインターセプターがRay.Aopでどのように機能するかを明らかにするために、週末にはピザの注文を禁止するようにしてみましょう。デリバリーは平日だけ受け付ける事にして、ピザの注文を週末には受け付けないようにします！この例はAOPで認証を使用するときにのパターンと構造的に似ています。

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

そして、インターセプトするメソッドに適用します。

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

次に、org.aopalliance.intercept.MethodInterceptorインターフェイスを実装したインターセプターを定義します。元のメソッドを実行するためには `$invocation->proceed()` と実行します。

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

設定完了しました。このコードでは「どのクラスでも」「メソッドに`@NotOnWeekends`アノテーションがある」という条件にマッチします。

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
$billing = (new Compiler($tmpDir))->newInstance(RealBillingService::class, [], $bind);

try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

全てをまとめ（土曜日まで待って）、メソッドをコールするとインターセプターにより拒否されます。

```
chargeOrder not allowed on weekends!
```

メソッド名を指定したマッチ
---------------------------

```php
<?php
    $bind = (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker]);
    $compiler = new Compiler($tmpDir);
    $billing = $compiler->newInstance('RealBillingService', [], $bind);
    try {
        echo $billing->chargeOrder();
    } catch (\RuntimeException $e) {
        echo $e->getMessage() . "\n";
        exit(1);
    }
```

独自のマッチャー
---------------

独自のマッチャーを作成することもでます。
`contains` マッチャーを作成するためには、２つのメソッドを持つクラスを提供する必要があります。
１つはクラスのマッチを行う`matchesClass`メソッド、もう１つはメソッドのマッチを行う`matchesMethod`です。いずれもマッチしたかどうかをブールで返します。

```php
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;

class IsContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
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
$billing = (new Compiler($tmpDir))->newInstance(RealBillingService::class, [], $bind);
```

優先順位
--------

インターセプターの実行順は以下のルールで決定されます。

 * 基本的にはバインドした順に実行されます。
 * `PriorityPointcut`で定義したものが最も優先されます。
 * アノテーションでメソッドマッチするものは`PriorityPointcut`の次に優先されます。その時アノテートされた順で優先されます。

```php
/**
 * @Auth    // 1st
 * @Cache   // 2nd
 * @Log     // 3rd
 */
```

制限
----

この機能の背後ではメソッドのインターセプションを事前にコードを生成する事で可能にしています。Ray.Aopはダイナミックにサブクラスを生成してメソッドをオーバーライドするインターセプターを適用します。

クラスとメソッドは以下のものである必要があります。

 * クラスは *final* ではない
 * メソッドは *public*
 * メソッドは *final* ではない

AOPアライアンス
--------------

このメソッドインターセプターのAPIは[AOPアライアンス](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html)の部分実装です。

要件
----

* PHP 5.4+
* hhvm

インストール
------------

Ray.Aopの推奨インストール方法は、[Composer](https://github.com/composer/composer)でのインストールです。

```bash
# Ray.Aop を依存パッケージとして追加する
$ composer require ray/aop ~2.0
```

Ray.Aopのテスト
---------------

Ray.Aopをソースからインストールし、ユニットテストとデモを実行するには次のようにします。

```bash
$ composer create-project ray/aop:~2.0 Ray.Aop
$ cd Ray.Aop
$ phpunit
$ php docs/demo/run.php
```

DIとAOPを統合したDIフレームワーク[Ray.Di](https://github.com/ray-di/Ray.Di)もご覧ください。

* この文書の大部分は [Guice/AOP](https://github.com/google/guice/wiki/AOP) から借用しています。
