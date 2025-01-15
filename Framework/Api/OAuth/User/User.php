<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Api\OAuth\User;

use Cicada\Core\Framework\Log\Package;
use League\OAuth2\Server\Entities\UserEntityInterface;

#[Package('core')]
class User implements UserEntityInterface
{
    public function __construct(private readonly string $userId)
    {
    }

    /**
     * Return the user's identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->userId;
    }
}
