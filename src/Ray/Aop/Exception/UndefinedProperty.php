<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package    Ray.Aop
 * @subpackage Exception
 * @license    http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop\Exception;

use LogicException;

/**
 * Undefined property
 *
 * @package    Ray.Aop
 * @subpackage Exception
 */
class UndefinedProperty extends LogicException implements Exception
{
}
