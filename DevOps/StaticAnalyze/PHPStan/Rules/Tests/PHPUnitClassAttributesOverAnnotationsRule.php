<?php declare(strict_types=1);

namespace Cicada\Core\DevOps\StaticAnalyze\PHPStan\Rules\Tests;

use Cicada\Core\Framework\Log\Package;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 *
 * @internal
 */
#[Package('core')]
class PHPUnitClassAttributesOverAnnotationsRule implements Rule
{
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * @param InClassNode $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!TestRuleHelper::isTestClass($node->getClassReflection())) {
            return [];
        }

        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return [];
        }

        $annotations = [
            'backupGlobals',
            'backupStaticProperties',
            'covers',
            'coversDefaultClass',
            'coversNothing',
            'doesNotPerformAssertions',
            'group',
            'large',
            'medium',
            'preserveGlobalState',
            'requires',
            'runTestsInSeparateProcesses',
            'small',
            'testdox',
            'ticket',
            'uses',
        ];

        $pattern = '/@(' . implode('|', $annotations) . ')\s+([^\s]+)/';

        if (preg_match($pattern, $docComment->getText(), $matches)) {
            return [
                RuleErrorBuilder::message('Please use PHPUnit attribute instead of annotation for: ' . $matches[1])
                    ->identifier('cicada.phpunitAttributes')
                    ->build(),
            ];
        }

        return [];
    }
}
