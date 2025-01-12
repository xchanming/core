<?php declare(strict_types=1);

namespace Cicada\Core\DevOps\StaticAnalyze\PHPStan\Rules;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Util\Hasher;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 *
 * @internal
 */
#[Package('core')]
class UseHasherRule implements Rule
{
    use InTestClassTrait;

    private const NOT_ALLOWED_FUNCTIONS = ['md5', 'md5_file', 'sha1', 'sha1_file', 'hash', 'hash_file'];
    private const HASHER_CLASS = Hasher::class;

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @return array<array-key, RuleError|string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->isInTestClass($scope) || $this->isInWebInstaller($scope) || str_contains($scope->getFile(), '/tests/')) {
            // if in a test namespace, don't care
            return [];
        }

        if (!$node instanceof FuncCall) {
            return [];
        }

        if (!$node->name instanceof Name) {
            return [];
        }

        if ($scope->getClassReflection()?->getName() === self::HASHER_CLASS) {
            return [];
        }

        $name = $node->name->toString();

        if (\in_array($name, self::NOT_ALLOWED_FUNCTIONS, true)) {
            return [
                RuleErrorBuilder::message(\sprintf('Do not use %s function, use class %s instead.', $name, self::HASHER_CLASS))
                    ->identifier('cicada.hasher')
                    ->build(),
            ];
        }

        return [];
    }

    /**
     * The webinstaller also runs on older installations, and therefore we can't enforce the usage of the Hasher class.
     */
    protected function isInWebInstaller(Scope $scope): bool
    {
        if (!$scope->isInClass()) {
            return false;
        }

        $className = $scope->getClassReflection()->getNativeReflection()->getName();

        return str_contains($className, 'Cicada\WebInstaller');
    }
}
