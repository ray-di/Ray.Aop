<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;

use function array_keys;
use function implode;
use function in_array;
use function is_array;
use function preg_replace;
use function token_get_all;

use const T_CLASS;
use const T_EXTENDS;
use const T_FUNCTION;
use const T_STRING;

class AopCodeGen
{
    public function generate(string $code, string $postfix, BindInterface $bind, array $traits = [InterceptTrait::class], string $replacement = 'return $this->_intercept(func_get_args(), __FUNCTION__);')
    {
        $tokens = token_get_all($code);
        $newCode = '';
        $inClass = false;
        $inMethod = false;
        $curlyBraceCount = 0;
        $methodStarted = false;
        $className = '';
        $skip = false;

        $iterator = new ArrayIterator($tokens);
        $bindings = array_keys($bind->getBindings());

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $token = $iterator->current();
            [$id, $text] = is_array($token) ? $token : [null, $token];

            if ($id === T_CLASS) {
                $inClass = true;
                $newCode .= $text . ' ';
                continue;
            }

            if ($inClass && $id === T_STRING && empty($className)) {
                $className = $text;
                $newClassName = $className . $postfix;
                $newCode .= $newClassName . ' extends ' . $className . ' ';
                continue;
            }

            if ($inClass && $id === T_EXTENDS) {
                $iterator->next();  // Skip extends keyword
                $iterator->next();  // Skip class name
                $iterator->next();  // Skip space
                continue;
            }

            if ($inClass && $text === '{' && ! $inMethod) {
                $newCode .= '{';
                if (! empty($traits)) {
                    $newCode .= ' use \\' . implode(', ', $traits) . '; ';
                }

                continue;
            }

            if ($id === T_FUNCTION) {
                $inMethod = true;
                $methodStarted = false;
                $currentMethodName = $iterator->offsetGet($iterator->key() + 2)[1];
                if (! in_array($currentMethodName, $bindings)) {
                    // テキストの最後に現れた ;, {, } 以降を削除
                    $newCode = preg_replace('/(?<=[;{}])[^;{}]*$/', '', $newCode);
                    $skip = true;
                }
            }

            if ($inMethod) {
                if ($text === '{') {
                    $curlyBraceCount++;
                    $methodStarted = true;
                } elseif ($text === '}') {
                    $curlyBraceCount--;
                }

                if ($methodStarted) {
                    if ($curlyBraceCount === 1 && $text === '{' && ! $skip) {
                        $newCode .= '{ ' . $replacement . ' ';
                        continue;
                    }

                    if ($curlyBraceCount === 0) {
                        if (! $skip) {
                            $newCode .= '}';
                        }

                        $inMethod = false;
                        $methodStarted = false;
                        $skip = false;
                        continue;
                    }

                    continue;  // We skip adding other contents inside the method
                }
            }

            if (! $skip) {
                $newCode .= $text;
            }
        }

        return $newCode;
    }
}
