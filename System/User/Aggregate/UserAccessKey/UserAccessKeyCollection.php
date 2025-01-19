<?php declare(strict_types=1);

namespace Cicada\Core\System\User\Aggregate\UserAccessKey;

use Cicada\Core\Framework\DataAbstractionLayer\EntityCollection;
use Cicada\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<UserAccessKeyEntity>
 */
#[Package('services-settings')]
class UserAccessKeyCollection extends EntityCollection
{
    public function getUserIds(): array
    {
        return $this->fmap(fn (UserAccessKeyEntity $user) => $user->getUserId());
    }

    public function filterByUserId(string $id): self
    {
        return $this->filter(fn (UserAccessKeyEntity $user) => $user->getUserId() === $id);
    }

    public function getApiAlias(): string
    {
        return 'user_access_key_collection';
    }

    protected function getExpectedClass(): string
    {
        return UserAccessKeyEntity::class;
    }
}
