<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;

use function implode;
use function is_array;
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

        $iterator = new ArrayIterator($tokens);

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
            }

            if ($inMethod) {
                if ($text === '{') {
                    $curlyBraceCount++;
                    $methodStarted = true;
                } elseif ($text === '}') {
                    $curlyBraceCount--;
                }

                if ($methodStarted) {
                    if ($curlyBraceCount === 1 && $text === '{') {
                        $newCode .= '{ ' . $replacement . ' ';
                        continue;
                    }

                    if ($curlyBraceCount === 0) {
                        $newCode .= '}';
                        $inMethod = false;
                        $methodStarted = false;
                        continue;
                    }

                    continue;  // We skip adding other contents inside the method
                }
            }

            $newCode .= $text;
        }

        return $newCode;
    }
}
