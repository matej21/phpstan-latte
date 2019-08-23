<?php declare(strict_types = 1);

namespace App\Core\Latte;

use Nette\StaticClass;


class TypeHelper
{
	use StaticClass;

	public static function createExpression(string $type, int $arrayDepth)
	{
		switch (strtolower($type)) {
			case 'mixed':
				$expression = 'TRUE';
				break;
			case 'array':
			case 'iterable':
			case 'string':
			case 'int':
			case 'float':
			case 'numeric':
			case 'bool':
			case 'null':
			case 'object':
			case 'scalar':
			case 'callable':
				$expression = 'is_' . strtolower($type) . '($var)';
				break;
			case 'true':
			case 'false':
				$expression = '$var === ' . $type;
				break;
			default:
				$expression = "\$var instanceof $type";
		}
		if ($arrayDepth === 0) {
			return $expression;
		} else {
			$expression = self::createExpression($type, $arrayDepth - 1);
			return "(is_iterable(\$var) && (function (\$vars) { foreach (\$vars as \$var) { if (!$expression) return FALSE;} return TRUE; })(\$var))";
		}
	}


	public static function createAssertFunction(array $types): ?string
	{
		$assertExpressions = [];
		foreach ($types as [$type, $arrayDepth]) {
			$assertExpressions[] = self::createExpression($type, $arrayDepth);
		}
		if (!count($assertExpressions)) {
			return NULL;
		}
		return 'function ($var) { assert(' . implode(' || ', $assertExpressions) . '); }';
	}

}
