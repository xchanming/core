<?php declare(strict_types=1);

namespace Cicada\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use Cicada\Core\DevOps\Environment\EnvironmentHelper;
use Cicada\Core\Framework\Log\Package;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @internal
 *
 * @implements Rule<StaticCall>
 */
#[Package('core')]
class NoEnvironmentHelperInsideCompilerPassRule implements Rule
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    /**
     * @param StaticCall $node
     *
     * @return array<array-key, RuleError|string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $scope->getClassReflection();

        if ($class === null) {
            return [];
        }

        if (!$class->implementsInterface(CompilerPassInterface::class)) {
            return [];
        }

        if (!$node->name instanceof Identifier) {
            return [];
        }

        if ((string) $node->name !== 'getVariable' && (string) $node->name !== 'hasVariable') {
            return [];
        }

        if (!$node->class instanceof Name) {
            return [];
        }

        if ((string) $node->class !== EnvironmentHelper::class) {
            return [];
        }

        return [
            RuleErrorBuilder::message('Do not use EnvironmentHelper inside compiler passes.')
                ->identifier('cicada.envHelperCompilerPass')
                ->build(),
        ];
    }
}
