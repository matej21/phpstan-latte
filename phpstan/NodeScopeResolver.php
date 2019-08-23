<?php declare(strict_types = 1);

namespace AppTests\PhpStan;

use App\Model\Post;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\StatementResult;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Broker\Broker;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\FileTypeMapper;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;


class NodeScopeResolver extends \PHPStan\Analyser\NodeScopeResolver
{
	/** @var Broker */
	private $broker;

	/** @var array */
	private $latteVariables = [];


	public function __construct(
		Broker $broker,
		Parser $parser,
		FileTypeMapper $fileTypeMapper,
		FileHelper $fileHelper,
		TypeSpecifier $typeSpecifier,
		bool $polluteScopeWithLoopInitialAssignments,
		bool $polluteCatchScopeWithTryAssignments,
		bool $polluteScopeWithAlwaysIterableForeach,
		array $earlyTerminatingMethodCalls,
		bool $allowVarTagAboveStatements
	) {
		parent::__construct($broker, $parser, $fileTypeMapper, $fileHelper, $typeSpecifier, $polluteScopeWithLoopInitialAssignments,
			$polluteCatchScopeWithTryAssignments, $polluteScopeWithAlwaysIterableForeach, $earlyTerminatingMethodCalls, $allowVarTagAboveStatements);
		$this->broker = $broker;
	}


	public function processStmtNodes(Node $parentNode, array $stmts, Scope $scope, \Closure $nodeCallback): StatementResult
	{
		if ($scope->isInFirstLevelStatement() && $parentNode instanceof ClassMethod) {
//			$scope = $scope->assignVariable('posts', new ArrayType(new IntegerType(), new ObjectType(Post::class)), TrinaryLogic::createYes());

			$variables = $this->getVariables($scope->getFile(), $scope);
			foreach ($variables ?? [] as $variable => $type) {
				$scope = $scope->assignVariable($variable, $type, TrinaryLogic::createYes());
			}
		}
		return parent::processStmtNodes($parentNode, $stmts, $scope, $nodeCallback);
	}


	private function getVariables(string $filename, Scope $scope): ?array
	{
		if (array_key_exists($filename, $this->latteVariables)) {
			return $this->latteVariables[$filename];
		}
		$viewClass = $this->resolveViewName($filename);
		if ($viewClass === null) {
			$this->latteVariables[$filename] = null;
			return null;
		}
		if (!interface_exists($viewClass)) {
			return null;
		}

		$variables = [];
		$cls = $this->broker->getClass($viewClass);
		do {
			$docComment = $cls->getNativeReflection()->getDocComment() ?: '';
			preg_match_all('#@property(?:-read)?\s+[^$]+\s+\$([a-zA-Z0-9_]+)#', $docComment, $matches, PREG_PATTERN_ORDER);
			foreach ($matches[1] as $variable) {
				if (isset($variables[$variable])) {
					continue;
				}
				$variables[$variable] = $cls->getProperty($variable, $scope)->getType();
			}
		} while ($cls = $cls->getParentClass());
		$this->latteVariables[$filename] = $variables;
		return $variables;
	}


	private function resolveViewName(string $file): ?string
	{
		$file = strtr($file, '\\', '/'); // for Windows paths
		if (!preg_match('~(\w+)/(\w+)\.latte\z~', $file, $matches)) {
			return null;
		}
		$viewName = "App\\Presenters\\" . $matches[1] . ucfirst($matches[2]) . 'View';
		return $viewName;
	}

}
