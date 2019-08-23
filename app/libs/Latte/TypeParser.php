<?php declare(strict_types = 1);

namespace App\Core\Latte;

use Nette\StaticClass;
use Nette\Tokenizer\Tokenizer;
use Nette\Utils\Reflection;


class TypeParser
{
	use StaticClass;

	private const T_OR = 1;
	private const T_PRIMITIVE_TYPE = 2;
	private const T_CLASS = 3;
	private const T_ITERABLE = 4;

	private const PATTERNS = [
		self::T_OR => '\|',
		self::T_PRIMITIVE_TYPE => 'mixed|array|string|scalar|int|float|numeric|bool|object|callable|null|NULL|true|TRUE|false|FALSE',
		self::T_CLASS => '\\\?[A-Za-z_][\w_]*(?:\\\[A-Za-z_][\w_]*)*',
		self::T_ITERABLE => '\[\]',
	];


	public static function parse(string $fullType, ?\ReflectionClass $rc)
	{
		$tokenizer = new Tokenizer(self::PATTERNS);
		$tokens = $tokenizer->tokenize($fullType);
		$tokens->nextToken();
		$types = [];
		while ($tokens->currentToken()) {
			if (!$tokens->isCurrent(self::T_PRIMITIVE_TYPE, self::T_CLASS)) {
				throw new \RuntimeException("Unexpected {$tokens->currentValue()}, type expected");
			}
			$type = $tokens->currentValue();
			if ($rc !== NULL && $tokens->isCurrent(self::T_CLASS)) {
				assert($type !== null);
				$type = Reflection::expandClassName($type, $rc);
			}
			$tokens->nextToken();

			$arrayDepth = 0;
			while ($tokens->isCurrent(self::T_ITERABLE) ) {
				++$arrayDepth;
				if (!$tokens->nextToken()) {
					$tokens->position++;
					break;
				}
			}
			$types[] = [$type, $arrayDepth];

			if ($tokens->currentValue() && !$tokens->isCurrent(self::T_OR)) {
				throw new \RuntimeException("Unexpected {$tokens->currentValue()}, | or end of string expected");
			}
			$tokens->nextToken();
		}
		return $types;
	}
}
