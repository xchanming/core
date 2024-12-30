<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Field\Flag;

use Cicada\Core\Framework\Log\Package;

/**
 * In case the referenced association data will be deleted, the related data will be deleted too
 */
#[Package('core')]
class CascadeDelete extends Flag
{
    /**
     * @var bool
     *
     * @deprecated tag:v6.7.0 - Will be natively typed
     */
    protected $cloneRelevant;

    public function __construct(bool $cloneRelevant = true)
    {
        $this->cloneRelevant = $cloneRelevant;
    }

    public function parse(): \Generator
    {
        yield 'cascade_delete' => true;
    }

    public function isCloneRelevant(): bool
    {
        return $this->cloneRelevant;
    }
}
