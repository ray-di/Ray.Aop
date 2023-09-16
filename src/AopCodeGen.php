<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;

use function array_keys;
use function implode;
use function in_array;
use function is_array;
use function token_get_all;

use const T_CLASS;
use const T_EXTENDS;
use const T_FUNCTION;
use const T_STRING;

class AopCodeGen
{
    public function generate(string $code, string $postfix, BindInterface $bind, array $traits = [InterceptTrait::class], string $replacement = 'return $this->_intercept(func_get_args(), __FUNCTION__);}')
    {
        $tokens = token_get_all($code);
        $inClass = false;
        $inMethod = false;
        $curlyBraceCount = 0;
        $methodStarted = false;
        $className = '';
        $firstBraceAfterClassHit = false;
        $newCode = new AopCodeGenNewCode();
        $iterator = new ArrayIterator($tokens);
        $bindings = array_keys($bind->getBindings());

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $token = $iterator->current();
            [$id, $text] = is_array($token) ? $token : [null, $token];

            if ($id === T_CLASS) {
                $inClass = true;
                $newCode->add($text . ' ');
                continue;
            }

            if ($inClass && $id === T_STRING && empty($className)) {
                $className = $text;
                $newClassName = $text . $postfix;
                $newCode->add($newClassName . ' extends ' . $className . ' ');
                continue;
            }

            if ($text === '{' && $inClass && ! $firstBraceAfterClassHit) {
                $newCode->commit();
            }

            if ($inClass && $id === T_EXTENDS) {
                $iterator->next();  // Skip extends keyword
                $iterator->next();  // Skip class name
                $iterator->next();  // Skip space
                continue;
            }

            if ($text === ';' && $inClass && ! $inMethod) {
                $newCode->clear();
                continue;
            }

            if ($inClass && $text === '{' && ! $inMethod) {
                $newCode->add('{');
                if (! empty($traits)) {
                    $newCode->add(' use \\' . implode(', ', $traits) . '; ');
                    $newCode->commit();
                }

                continue;
            }

            if ($id === T_FUNCTION) {
                $inMethod = true;
                $methodStarted = false;
                $currentMethodName = $iterator->offsetGet($iterator->key() + 2)[1];
                if (! in_array($currentMethodName, $bindings)) {
                    $newCode->clear();
                    $newCode->ignore(true);
                }
            }

            if ($inMethod) {
                if ($text === '{') {
                    $curlyBraceCount++;
                    $methodStarted = true;
                } elseif ($text === '}') {
                    $curlyBraceCount--;
                    $newCode->ignore(false);
                }

                if ($methodStarted) {
                    if ($curlyBraceCount === 1 && $text === '{') {
                        $newCode->add('{ ' . $replacement . ' ');
                        continue;
                    }

                    if ($curlyBraceCount === 0) {
                        $newCode->commit();
                        $inMethod = false;
                        $methodStarted = false;
                        continue;
                    }

                    continue;  // We skip adding other contents inside the method
                }
            }

            $newCode->add($text);
        }

        $newCode->commit();

        return $newCode->code;
    }
}
