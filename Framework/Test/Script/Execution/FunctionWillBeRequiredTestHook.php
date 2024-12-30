<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Test\Script\Execution;

use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Script\Execution\OptionalFunctionHook;

/**
 * @internal
 */
#[\AllowDynamicProperties]
class FunctionWillBeRequiredTestHook extends OptionalFunctionHook
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly string $name,
        Context $context,
        array $data
    ) {
        parent::__construct($context);

        foreach ($data as $key => $value) {
            $this->$key = $value; /* @phpstan-ignore-line */
        }
    }

    public function getFunctionName(): string
    {
        return 'test';
    }

    public static function getServiceIds(): array
    {
        return [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function willBeRequiredInVersion(): ?string
    {
        return 'v6.5.0.0';
    }
}
