<?php declare(strict_types=1);

namespace Cicada\Core\DevOps\StaticAnalyze\PHPStan\Rules\Deprecation;

use Cicada\Core\Framework\Log\Package;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @implements Rule<ClassMethod>
 *
 * @internal
 */
#[Package('core')]
class DeprecatedMethodsThrowDeprecationRule implements Rule
{
    /**
     * There are some exceptions to this rule, where deprecated methods should not throw a deprecation notice.
     * This is mainly the reason if the deprecated code is still called from inside the core due to BC reasons.
     */
    private const RULE_EXCEPTIONS = [
        // Subscribers still need to be called for BC reasons, therefore they do not trigger deprecations.
        'reason:remove-subscriber',
        // Decorators still need to be called for BC reasons, therefore they do not trigger deprecations.
        'reason:remove-decorator',
        // Command methods are still called from symfony, the execute method should throw a deprecation though.
        'reason:remove-command',
        // Entities still need to be present in the DI container, therefore they do not trigger deprecations.
        'reason:remove-entity',
        // Only the route on controller will be removed
        'reason:remove-route',
        // Throwing deprecations in PHPStan rules would cause problems while executed
        'reason:remove-phpstan-rule',
        // Classes that will be internal are still called from inside the core, therefore they do not trigger deprecations.
        'reason:becomes-internal',
        // New function parameter will be added
        'reason:new-optional-parameter',
        // Parameter name is changing, which could break usage of named parameters, but should not trigger a deprecation
        'reason:parameter-name-change',
        // Classes that will be final, can only be changed with the next major
        'reason:becomes-final',
        // If the return type change, the functionality itself is not deprecated, therefore they do not trigger deprecations.
        'reason:return-type-change',
        // If there will be in the class hierarchy of a class we mark the whole class as deprecated, but the functionality itself is not deprecated, therefore they do not trigger deprecations.
        'reason:class-hierarchy-change',
        // If we change the visibility of a method we can't know from where it was called and whether the call will be valid in the future, therefore they do not trigger deprecations.
        'reason:visibility-change',
        // Exception still need to be called for BC reasons, therefore they do not trigger deprecations.
        'reason:remove-exception',
        // If a thrown exception in the method changes, we don't want to trigger deprecation warnings or throw an exception
        'reason:exception-change',
        // Getter setter that could be serialized when dispatched via bus needs to be deprecated and removed silently
        'reason:remove-getter-setter',
        // The method is used purely for blue-green deployment, therefor it will be removed from the next major without replacement
        'reason:blue-green-deployment',
        // The class is a decorating class and will be removed. Third party code should never rely on explicit decorators
        'reason:decoration-will-be-removed',
        // The constraint can still be used, just not via an annotation
        'reason:remove-constraint-annotation',
        // Container factory for deprecated service
        'reason:factory-for-deprecation',
    ];

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$scope->isInClass()) {
            return [];
        }

        $class = $scope->getClassReflection();

        if ($class->isInterface() || $this->isTestClass($class)) {
            return [];
        }

        if (!($node->isPublic() || $node->isProtected()) || $node->isAbstract() || $node->isMagic()) {
            return [];
        }

        $methodContent = $this->getMethodContent($node, $scope, $class);
        $method = $class->getMethod($node->name->name, $scope);

        $classDeprecation = $class->getDeprecatedDescription();
        if ($classDeprecation && !$this->handlesDeprecationCorrectly($classDeprecation, $methodContent)) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Class "%s" is marked as deprecated, but method "%s" does not call "Feature::triggerDeprecationOrThrow". All public methods of deprecated classes need to trigger a deprecation warning.',
                    $class->getName(),
                    $method->getName()
                ))
                    ->identifier('cicada.deprecatedClass')
                    ->build(),
            ];
        }

        $methodDeprecation = $method->getDeprecatedDescription() ?? '';

        // by default deprecations from parent methods are also available on all implementing methods
        // we will copy the deprecation to the implementing method, if they also have an affect there
        $deprecationOfParentMethod = !str_contains($method->getDocComment() ?? '', $methodDeprecation) && !str_contains($method->getDocComment() ?? '', 'inheritdoc');

        if (!$deprecationOfParentMethod && $methodDeprecation && !$this->handlesDeprecationCorrectly($methodDeprecation, $methodContent)) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Method "%s" of class "%s" is marked as deprecated, but does not call "Feature::triggerDeprecationOrThrow". All deprecated methods need to trigger a deprecation warning.',
                    $method->getName(),
                    $class->getName()
                ))
                    ->identifier('cicada.deprecatedMethod')
                    ->build(),
            ];
        }

        return [];
    }

    private function getMethodContent(Node $node, Scope $scope, ClassReflection $class): string
    {
        $filename = $class->getFileName();

        $trait = $scope->getTraitReflection();
        if ($trait) {
            $filename = $trait->getFileName();
        }

        if (!\is_string($filename)) {
            return '';
        }

        $file = new \SplFileObject($filename);
        $file->seek($node->getStartLine() - 1);

        $content = '';
        for ($i = 0; $i <= ($node->getEndLine() - $node->getStartLine()); ++$i) {
            $content .= $file->current();
            $file->next();
        }

        return $content;
    }

    private function handlesDeprecationCorrectly(string $deprecation, string $method): bool
    {
        foreach (self::RULE_EXCEPTIONS as $exception) {
            if (\str_contains($deprecation, $exception)) {
                return true;
            }
        }

        return \str_contains($method, 'Feature::triggerDeprecationOrThrow(');
    }

    private function isTestClass(ClassReflection $class): bool
    {
        $namespace = $class->getName();

        if (\str_contains($namespace, '\\Test\\')) {
            return true;
        }

        if (\str_contains($namespace, '\\Tests\\')) {
            return true;
        }

        foreach ($class->getParents() as $parentClass) {
            if ($parentClass->getName() === TestCase::class) {
                return true;
            }
        }

        return false;
    }
}
