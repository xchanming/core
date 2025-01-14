<?php declare(strict_types=1);

namespace Cicada\Core\System\SalesChannel\Api;

use Cicada\Core\Framework\Log\Package;
use Cicada\Core\System\SalesChannel\SalesChannelException;

#[Package('core')]
class ResponseFields
{
    /**
     * @var array<mixed>|null
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $includes;

    /**
     * @param array<mixed>|null $includes
     */
    public function __construct(?array $includes)
    {
        $this->includes = $includes;
    }

    public function isAllowed(string $type, string $property): bool
    {
        if (!isset($this->includes[$type])) {
            return true;
        }

        if (!\is_array($this->includes[$type])) {
            throw SalesChannelException::invalidType(
                \sprintf(
                    'The includes for type "%s" must be of the type array, %s given',
                    $type,
                    \gettype($this->includes[$type])
                )
            );
        }

        return \in_array($property, $this->includes[$type], true);
    }

    public function hasNested(string $alias, string $prefix): bool
    {
        $fields = $this->includes[$alias] ?? [];

        $prefix .= '.';
        foreach ($fields as $property) {
            if (mb_strpos((string) $property, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
