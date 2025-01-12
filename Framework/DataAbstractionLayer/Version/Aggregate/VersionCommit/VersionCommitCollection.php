<?php declare(strict_types=1);

namespace Cicada\Core\Framework\DataAbstractionLayer\Version\Aggregate\VersionCommit;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<VersionCommitEntity>
 */
#[Package('core')]
class VersionCommitCollection extends EntityCollection
{
    /**
     * @return array<string>
     */
    public function getUserIds(): array
    {
        return $this->fmap(fn (VersionCommitEntity $versionChange) => $versionChange->getUserId());
    }

    public function filterByUserId(string $id): self
    {
        return $this->filter(fn (VersionCommitEntity $versionChange) => $versionChange->getUserId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'dal_version_commit_collection';
    }

    protected function getExpectedClass(): string
    {
        return VersionCommitEntity::class;
    }
}
