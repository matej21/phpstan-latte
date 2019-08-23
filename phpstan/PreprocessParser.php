<?php declare(strict_types = 1);

namespace AppTests\PhpStan;

use Latte\Engine;
use PHPStan\Parser\Parser;


class PreprocessParser implements Parser
{

	/** @var Parser */
	private $innerParser;

	/** @var Engine */
	private $latte;


	public function __construct(Engine $latte, Parser $innerParser)
	{
		$this->innerParser = $innerParser;
		$this->latte = $latte;
	}


	public function parseFile(string $file): array
	{
		if (substr($file, -6) === '.latte') {
			$this->latte->warmupCache($file);
			$file = $this->latte->getCacheFile($file);
		}
		return $this->innerParser->parseFile($file);
	}


	public function parseString(string $sourceCode): array
	{
		return $this->innerParser->parseString($sourceCode);
	}

}
