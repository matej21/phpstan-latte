<?php declare(strict_types = 1);

namespace AppTests\PhpStan;

use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\MacroTokens;
use Latte\PhpWriter;


class PhpStanMacros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new self($compiler);
		$me->addMacro('type', [$me, 'macroType']);

		return $me;
	}


	public function macroType(MacroNode $node, PhpWriter $writer)
	{
		$tokens = new MacroTokens($node->args . $node->modifiers);
		$node->modifiers = '';

		$type = $tokens->joinUntil(MacroTokens::T_VARIABLE);
		$tokens->nextToken();
		if (!$tokens->isCurrent(MacroTokens::T_VARIABLE)) {
			throw new CompileException("Unexpected {$tokens->currentValue()}, variable expected");
		}
		$variable = $tokens->currentValue();

		return "/** @var $type $variable */ $variable = NULL;";
	}

}
